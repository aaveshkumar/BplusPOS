<?php

class Coupon {
    private $db;
    private $prefix;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->prefix = DB_PREFIX;
    }
    
    /**
     * Get WooCommerce coupon by code
     */
    public function getCouponByCode($code) {
        try {
            $sql = "SELECT 
                        p.ID as id,
                        p.post_title as code,
                        p.post_status as status,
                        p.post_excerpt as description
                    FROM {$this->prefix}posts p
                    WHERE p.post_type = 'shop_coupon'
                    AND p.post_title = ?
                    AND p.post_status = 'publish'
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$code]);
            $coupon = $stmt->fetch();
            
            if (!$coupon) {
                return null;
            }
            
            // Get coupon meta data
            $metaSql = "SELECT meta_key, meta_value 
                       FROM {$this->prefix}postmeta 
                       WHERE post_id = ?";
            
            $metaStmt = $this->db->prepare($metaSql);
            $metaStmt->execute([$coupon['id']]);
            $metaRows = $metaStmt->fetchAll();
            
            // Parse meta data
            $meta = [];
            foreach ($metaRows as $row) {
                $meta[$row['meta_key']] = maybe_unserialize($row['meta_value']);
            }
            
            // Build coupon object
            return [
                'id' => $coupon['id'],
                'code' => strtoupper($coupon['code']),
                'description' => $coupon['description'],
                'discount_type' => $meta['discount_type'] ?? 'fixed_cart',
                'amount' => floatval($meta['coupon_amount'] ?? 0),
                'expiry_date' => $meta['date_expires'] ?? null,
                'minimum_amount' => floatval($meta['minimum_amount'] ?? 0),
                'maximum_amount' => floatval($meta['maximum_amount'] ?? 0),
                'usage_limit' => intval($meta['usage_limit'] ?? 0),
                'usage_limit_per_user' => intval($meta['usage_limit_per_user'] ?? 0),
                'usage_count' => intval($meta['usage_count'] ?? 0),
                'individual_use' => ($meta['individual_use'] ?? 'no') === 'yes',
                'free_shipping' => ($meta['free_shipping'] ?? 'no') === 'yes',
                'product_ids' => $meta['product_ids'] ?? [],
                'excluded_product_ids' => $meta['exclude_product_ids'] ?? [],
                'product_categories' => $meta['product_categories'] ?? [],
                'excluded_product_categories' => $meta['excluded_product_categories'] ?? [],
                'email_restrictions' => $meta['customer_email'] ?? []
            ];
        } catch (Exception $e) {
            error_log("Error fetching coupon: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate coupon for current cart
     */
    public function validateCoupon($code, $cartData = []) {
        $coupon = $this->getCouponByCode($code);
        
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid coupon code'];
        }
        
        // Check expiry date
        if (!empty($coupon['expiry_date'])) {
            $expiryTimestamp = is_numeric($coupon['expiry_date']) 
                ? $coupon['expiry_date'] 
                : strtotime($coupon['expiry_date']);
            
            if ($expiryTimestamp && $expiryTimestamp < time()) {
                return ['valid' => false, 'message' => 'Coupon has expired'];
            }
        }
        
        // Check usage limit
        if ($coupon['usage_limit'] > 0 && $coupon['usage_count'] >= $coupon['usage_limit']) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached'];
        }
        
        // Check minimum amount
        $cartSubtotal = floatval($cartData['subtotal'] ?? 0);
        if ($coupon['minimum_amount'] > 0 && $cartSubtotal < $coupon['minimum_amount']) {
            return [
                'valid' => false, 
                'message' => 'Minimum order amount ₹' . number_format($coupon['minimum_amount'], 2) . ' required'
            ];
        }
        
        // Check maximum amount
        if ($coupon['maximum_amount'] > 0 && $cartSubtotal > $coupon['maximum_amount']) {
            return [
                'valid' => false, 
                'message' => 'Maximum order amount ₹' . number_format($coupon['maximum_amount'], 2) . ' exceeded'
            ];
        }
        
        // Check per-user usage limit (if customer is selected)
        if ($coupon['usage_limit_per_user'] > 0 && !empty($cartData['customer_id'])) {
            $usageCount = $this->getCouponUsageByCustomer($coupon['id'], $cartData['customer_id']);
            if ($usageCount >= $coupon['usage_limit_per_user']) {
                return ['valid' => false, 'message' => 'You have already used this coupon'];
            }
        }
        
        // Check product restrictions
        if (!empty($coupon['product_ids']) || !empty($coupon['excluded_product_ids'])) {
            $cartProductIds = array_column($cartData['items'] ?? [], 'product_id');
            
            // Check if any cart products are in included list
            if (!empty($coupon['product_ids'])) {
                $hasValidProduct = count(array_intersect($cartProductIds, $coupon['product_ids'])) > 0;
                if (!$hasValidProduct) {
                    return ['valid' => false, 'message' => 'Coupon not applicable to cart items'];
                }
            }
            
            // Check if any cart products are in excluded list
            if (!empty($coupon['excluded_product_ids'])) {
                $hasExcludedProduct = count(array_intersect($cartProductIds, $coupon['excluded_product_ids'])) > 0;
                if ($hasExcludedProduct) {
                    return ['valid' => false, 'message' => 'Coupon not applicable to some cart items'];
                }
            }
        }
        
        // Coupon is valid
        return [
            'valid' => true,
            'coupon' => $coupon,
            'message' => 'Coupon applied successfully'
        ];
    }
    
    /**
     * Calculate coupon discount amount
     */
    public function calculateDiscount($coupon, $cartSubtotal) {
        $discountAmount = 0;
        
        switch ($coupon['discount_type']) {
            case 'fixed_cart':
                $discountAmount = min($coupon['amount'], $cartSubtotal);
                break;
                
            case 'percent':
                $discountAmount = ($cartSubtotal * $coupon['amount']) / 100;
                break;
                
            case 'fixed_product':
                // Not implemented for now
                $discountAmount = 0;
                break;
        }
        
        return round($discountAmount, 2);
    }
    
    /**
     * Get coupon usage count for a customer
     */
    public function getCouponUsageByCustomer($couponId, $customerId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                   FROM pos_coupon_usage 
                   WHERE coupon_code = (
                       SELECT post_title FROM {$this->prefix}posts WHERE ID = ?
                   )
                   AND customer_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$couponId, $customerId]);
            $result = $stmt->fetch();
            
            return intval($result['count'] ?? 0);
        } catch (Exception $e) {
            error_log("Error checking coupon usage: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Record coupon usage
     */
    public function recordCouponUsage($couponCode, $orderId, $customerId, $userId, $discountAmount) {
        try {
            $sql = "INSERT INTO pos_coupon_usage 
                   (coupon_code, order_id, customer_id, user_id, discount_amount, used_at) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $couponCode,
                $orderId,
                $customerId,
                $userId,
                $discountAmount
            ]);
            
            // Update WooCommerce coupon usage count
            $this->incrementCouponUsageCount($couponCode);
            
            return true;
        } catch (Exception $e) {
            error_log("Error recording coupon usage: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Increment WooCommerce coupon usage count
     */
    private function incrementCouponUsageCount($couponCode) {
        try {
            // Get coupon post ID
            $sql = "SELECT ID FROM {$this->prefix}posts 
                   WHERE post_type = 'shop_coupon' 
                   AND post_title = ? 
                   LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$couponCode]);
            $coupon = $stmt->fetch();
            
            if (!$coupon) {
                return false;
            }
            
            // Check if usage_count meta exists
            $checkSql = "SELECT meta_id, meta_value 
                        FROM {$this->prefix}postmeta 
                        WHERE post_id = ? 
                        AND meta_key = 'usage_count'";
            
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$coupon['ID']]);
            $meta = $checkStmt->fetch();
            
            if ($meta) {
                // Update existing
                $newCount = intval($meta['meta_value']) + 1;
                $updateSql = "UPDATE {$this->prefix}postmeta 
                             SET meta_value = ? 
                             WHERE meta_id = ?";
                
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->execute([$newCount, $meta['meta_id']]);
            } else {
                // Insert new
                $insertSql = "INSERT INTO {$this->prefix}postmeta 
                             (post_id, meta_key, meta_value) 
                             VALUES (?, 'usage_count', 1)";
                
                $insertStmt = $this->db->prepare($insertSql);
                $insertStmt->execute([$coupon['ID']]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error incrementing coupon usage: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Helper function to unserialize data safely
 */
function maybe_unserialize($data) {
    if (is_serialized($data)) {
        return @unserialize($data);
    }
    return $data;
}

function is_serialized($data) {
    if (!is_string($data)) {
        return false;
    }
    $data = trim($data);
    if ('N;' === $data) {
        return true;
    }
    if (strlen($data) < 4) {
        return false;
    }
    if (':' !== $data[1]) {
        return false;
    }
    $lastc = substr($data, -1);
    if (';' !== $lastc && '}' !== $lastc) {
        return false;
    }
    $token = $data[0];
    switch ($token) {
        case 's':
        case 'a':
        case 'O':
            return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
        case 'b':
        case 'i':
        case 'd':
            $end = substr($data, 2, -1);
            return (bool) preg_match("/^{$token}:[0-9.E-]+\$/", $data);
    }
    return false;
}
