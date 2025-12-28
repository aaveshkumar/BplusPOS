<?php
/**
 * Customer Model
 * Handles customer data from WooCommerce database
 */

require_once __DIR__ . '/BaseModel.php';

class Customer extends BaseModel {
    
    /**
     * Get all WooCommerce customers with pagination and filters
     * Excludes administrators, editors, shop managers, and cashiers
     */
    public function getAllCustomers($limit = 20, $offset = 0, $search = '', $status = '') {
        $sql = "SELECT 
                    u.ID as id,
                    u.user_login as username,
                    u.user_email as email,
                    u.display_name as name,
                    MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) as first_name,
                    MAX(CASE WHEN um.meta_key = 'last_name' THEN um.meta_value END) as last_name,
                    MAX(CASE WHEN um.meta_key = 'billing_phone' THEN um.meta_value END) as mobile,
                    MAX(CASE WHEN um.meta_key = 'billing_address_1' THEN um.meta_value END) as address,
                    MAX(CASE WHEN um.meta_key = 'billing_city' THEN um.meta_value END) as city,
                    MAX(CASE WHEN um.meta_key = 'billing_state' THEN um.meta_value END) as state,
                    MAX(CASE WHEN um.meta_key = 'billing_postcode' THEN um.meta_value END) as pincode,
                    u.user_registered as created_at
                FROM {$this->prefix}users u
                LEFT JOIN {$this->prefix}usermeta um ON u.ID = um.user_id
                LEFT JOIN {$this->prefix}usermeta cap ON u.ID = cap.user_id 
                    AND cap.meta_key = '{$this->prefix}capabilities'
                WHERE (cap.meta_value IS NULL OR (
                    cap.meta_value NOT LIKE '%administrator%' AND 
                    cap.meta_value NOT LIKE '%editor%' AND 
                    cap.meta_value NOT LIKE '%shop_manager%' AND 
                    cap.meta_value NOT LIKE '%cashier%'
                ))";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (
                u.user_login LIKE ? OR 
                u.user_email LIKE ? OR 
                u.display_name LIKE ? OR 
                EXISTS (
                    SELECT 1 FROM {$this->prefix}usermeta search_meta 
                    WHERE search_meta.user_id = u.ID 
                    AND search_meta.meta_value LIKE ?
                )
            )";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $sql .= " GROUP BY u.ID
                  ORDER BY u.user_registered DESC 
                  LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count total customers with filters
     * Excludes administrators, editors, shop managers, and cashiers
     */
    public function countCustomers($search = '', $status = '') {
        $sql = "SELECT COUNT(DISTINCT u.ID) as total 
                FROM {$this->prefix}users u
                LEFT JOIN {$this->prefix}usermeta um ON u.ID = um.user_id
                LEFT JOIN {$this->prefix}usermeta cap ON u.ID = cap.user_id AND cap.meta_key = '{$this->prefix}capabilities'
                WHERE (cap.meta_value IS NULL OR (
                    cap.meta_value NOT LIKE '%administrator%' AND 
                    cap.meta_value NOT LIKE '%editor%' AND 
                    cap.meta_value NOT LIKE '%shop_manager%' AND 
                    cap.meta_value NOT LIKE '%cashier%'
                ))";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (
                u.user_login LIKE ? OR 
                u.user_email LIKE ? OR 
                u.display_name LIKE ? OR 
                EXISTS (
                    SELECT 1 FROM {$this->prefix}usermeta search_meta 
                    WHERE search_meta.user_id = u.ID 
                    AND search_meta.meta_value LIKE ?
                )
            )";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Search customers for POS (quick search - OPTIMIZED for speed)
     * Excludes administrators, editors, shop managers, and cashiers
     * Uses indexed columns for maximum performance
     */
    public function searchCustomers($query, $limit = 20) {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT 
                    u.ID as id,
                    u.user_login as username,
                    u.user_email as email,
                    u.display_name as name,
                    MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) as first_name,
                    MAX(CASE WHEN um.meta_key = 'last_name' THEN um.meta_value END) as last_name,
                    MAX(CASE WHEN um.meta_key = 'billing_phone' THEN um.meta_value END) as mobile
                FROM {$this->prefix}users u
                LEFT JOIN {$this->prefix}usermeta um ON u.ID = um.user_id
                LEFT JOIN {$this->prefix}usermeta cap ON u.ID = cap.user_id 
                    AND cap.meta_key = '{$this->prefix}capabilities'
                WHERE (cap.meta_value IS NULL OR (
                    cap.meta_value NOT LIKE '%administrator%' AND 
                    cap.meta_value NOT LIKE '%editor%' AND 
                    cap.meta_value NOT LIKE '%shop_manager%' AND 
                    cap.meta_value NOT LIKE '%cashier%'
                ))
                AND (
                    u.user_email LIKE ? OR 
                    u.display_name LIKE ? OR
                    EXISTS (
                        SELECT 1 FROM {$this->prefix}usermeta search_meta 
                        WHERE search_meta.user_id = u.ID 
                        AND search_meta.meta_value LIKE ?
                    )
                )
                GROUP BY u.ID
                ORDER BY 
                    CASE 
                        WHEN u.user_email LIKE ? THEN 1
                        WHEN u.display_name LIKE ? THEN 2
                        ELSE 3
                    END,
                    u.display_name ASC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $searchTerm, $searchTerm, $searchTerm,
            $searchTerm, $searchTerm, $limit
        ]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get customer by ID
     */
    public function getCustomer($customerId) {
        $sql = "SELECT 
                    u.ID as id,
                    u.user_login as username,
                    u.user_email as email,
                    u.display_name as name,
                    MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) as first_name,
                    MAX(CASE WHEN um.meta_key = 'last_name' THEN um.meta_value END) as last_name,
                    MAX(CASE WHEN um.meta_key = 'billing_phone' THEN um.meta_value END) as mobile,
                    MAX(CASE WHEN um.meta_key = 'billing_address_1' THEN um.meta_value END) as address,
                    MAX(CASE WHEN um.meta_key = 'billing_city' THEN um.meta_value END) as city,
                    MAX(CASE WHEN um.meta_key = 'billing_state' THEN um.meta_value END) as state,
                    MAX(CASE WHEN um.meta_key = 'billing_postcode' THEN um.meta_value END) as pincode,
                    MAX(CASE WHEN um.meta_key = 'billing_country' THEN um.meta_value END) as country,
                    0 as loyalty_points
                FROM {$this->prefix}users u
                LEFT JOIN {$this->prefix}usermeta um ON u.ID = um.user_id
                WHERE u.ID = ?
                GROUP BY u.ID
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }

    /**
     * Get customer by mobile number
     */
    public function getByMobile($mobile) {
        $sql = "SELECT 
                    u.ID as id,
                    u.user_login as username,
                    u.user_email as email,
                    u.display_name as name,
                    MAX(CASE WHEN um.meta_key = 'billing_phone' THEN um.meta_value END) as mobile
                FROM {$this->prefix}users u
                LEFT JOIN {$this->prefix}usermeta um ON u.ID = um.user_id
                WHERE um.meta_key = 'billing_phone' AND um.meta_value = ?
                GROUP BY u.ID
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mobile]);
        return $stmt->fetch();
    }

    /**
     * Create new customer in WooCommerce
     */
    public function createCustomer($data) {
        // Insert into users table
        $sql = "INSERT INTO {$this->prefix}users 
                (user_login, user_pass, user_email, user_nicename, display_name, user_registered)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $username = $data['email'] ?? 'customer_' . time();
        $password = wp_hash_password($data['password'] ?? '123456');
        $displayName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $username,
            $password,
            $data['email'] ?? '',
            $username,
            $displayName ?: $username
        ]);
        
        $customerId = $this->db->lastInsertId();
        
        // Insert user meta
        $metaData = [
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'billing_phone' => $data['mobile'] ?? '',
            'billing_address_1' => $data['address'] ?? '',
            'billing_city' => $data['city'] ?? '',
            'billing_state' => $data['state'] ?? '',
            'billing_postcode' => $data['pincode'] ?? '',
            'billing_country' => $data['country'] ?? 'IN',
            'billing_email' => $data['email'] ?? '',
            'billing_first_name' => $data['first_name'] ?? '',
            'billing_last_name' => $data['last_name'] ?? ''
        ];
        
        foreach ($metaData as $key => $value) {
            if (!empty($value)) {
                $sql = "INSERT INTO {$this->prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$customerId, $key, $value]);
            }
        }
        
        // Set customer role
        $capabilities = serialize(['customer' => true]);
        $sql = "INSERT INTO {$this->prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, '{$this->prefix}capabilities', ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $capabilities]);
        
        return $customerId;
    }

    /**
     * Update customer
     */
    public function updateCustomer($customerId, $data) {
        // Update user table
        if (isset($data['email']) || isset($data['display_name'])) {
            $updateFields = [];
            $params = [];
            
            if (isset($data['email'])) {
                $updateFields[] = 'user_email = ?';
                $params[] = $data['email'];
            }
            
            if (isset($data['display_name'])) {
                $updateFields[] = 'display_name = ?';
                $params[] = $data['display_name'];
            }
            
            if (!empty($updateFields)) {
                $sql = "UPDATE {$this->prefix}users SET " . implode(', ', $updateFields) . " WHERE ID = ?";
                $params[] = $customerId;
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }
        }
        
        // Update user meta
        $metaData = [
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'billing_phone' => $data['mobile'] ?? null,
            'billing_address_1' => $data['address'] ?? null,
            'billing_city' => $data['city'] ?? null,
            'billing_state' => $data['state'] ?? null,
            'billing_postcode' => $data['pincode'] ?? null
        ];
        
        foreach ($metaData as $key => $value) {
            if ($value !== null) {
                // Check if meta exists
                $sql = "SELECT umeta_id FROM {$this->prefix}usermeta WHERE user_id = ? AND meta_key = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$customerId, $key]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update
                    $sql = "UPDATE {$this->prefix}usermeta SET meta_value = ? WHERE user_id = ? AND meta_key = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$value, $customerId, $key]);
                } else {
                    // Insert
                    $sql = "INSERT INTO {$this->prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$customerId, $key, $value]);
                }
            }
        }
        
        return true;
    }

    /**
     * Delete customer
     */
    public function deleteCustomer($customerId) {
        // Delete user meta first
        $sql = "DELETE FROM {$this->prefix}usermeta WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        
        // Delete user
        $sql = "DELETE FROM {$this->prefix}users WHERE ID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        
        return true;
    }

    /**
     * Get customer's loyalty points
     */
    public function getLoyaltyPoints($customerId) {
        try {
            $sql = "SELECT points FROM pos_loyalty_points WHERE customer_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$customerId]);
            $result = $stmt->fetch();
            return $result['points'] ?? 0;
        } catch (Exception $e) {
            // Table might not exist yet
            return 0;
        }
    }
    
    /**
     * Get customer's total orders
     */
    public function getCustomerOrderCount($customerId) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->prefix}posts p
                INNER JOIN {$this->prefix}postmeta pm ON p.ID = pm.post_id
                WHERE p.post_type = 'shop_order'
                AND pm.meta_key = '_customer_user'
                AND pm.meta_value = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get customer's total spent
     */
    public function getCustomerTotalSpent($customerId) {
        $sql = "SELECT SUM(CAST(pm2.meta_value AS DECIMAL(10,2))) as total
                FROM {$this->prefix}posts p
                INNER JOIN {$this->prefix}postmeta pm ON p.ID = pm.post_id
                INNER JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id
                WHERE p.post_type = 'shop_order'
                AND p.post_status IN ('wc-completed', 'wc-processing')
                AND pm.meta_key = '_customer_user'
                AND pm.meta_value = ?
                AND pm2.meta_key = '_order_total'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get customer statistics
     * Excludes administrators, editors, shop managers, and cashiers
     */
    public function getStats() {
        $stats = [
            'total' => 0,
            'new_this_month' => 0,
            'vip' => 0,
            'total_points' => 0
        ];
        
        try {
            // Total customers (exclude staff roles)
            $sql = "SELECT COUNT(DISTINCT u.ID) as count 
                    FROM {$this->prefix}users u
                    LEFT JOIN {$this->prefix}usermeta cap ON u.ID = cap.user_id 
                        AND cap.meta_key = '{$this->prefix}capabilities'
                    WHERE cap.meta_value IS NULL OR (
                        cap.meta_value NOT LIKE '%administrator%' AND 
                        cap.meta_value NOT LIKE '%editor%' AND 
                        cap.meta_value NOT LIKE '%shop_manager%' AND 
                        cap.meta_value NOT LIKE '%cashier%'
                    )";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $stats['total'] = $result['count'] ?? 0;
            
            // New customers this month (exclude staff roles)
            $sql = "SELECT COUNT(DISTINCT u.ID) as count 
                    FROM {$this->prefix}users u
                    LEFT JOIN {$this->prefix}usermeta cap ON u.ID = cap.user_id 
                        AND cap.meta_key = '{$this->prefix}capabilities'
                    WHERE (cap.meta_value IS NULL OR (
                        cap.meta_value NOT LIKE '%administrator%' AND 
                        cap.meta_value NOT LIKE '%editor%' AND 
                        cap.meta_value NOT LIKE '%shop_manager%' AND 
                        cap.meta_value NOT LIKE '%cashier%'
                    ))
                    AND MONTH(u.user_registered) = MONTH(CURDATE())
                    AND YEAR(u.user_registered) = YEAR(CURDATE())";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $stats['new_this_month'] = $result['count'] ?? 0;
            
            // VIP customers (with loyalty points >= 5000, exclude staff roles)
            $sql = "SELECT COUNT(DISTINCT lp.customer_id) as count 
                    FROM pos_loyalty_points lp
                    INNER JOIN {$this->prefix}users u ON lp.customer_id = u.ID
                    LEFT JOIN {$this->prefix}usermeta cap ON u.ID = cap.user_id 
                        AND cap.meta_key = '{$this->prefix}capabilities'
                    WHERE lp.points >= 5000
                    AND (cap.meta_value IS NULL OR (
                        cap.meta_value NOT LIKE '%administrator%' AND 
                        cap.meta_value NOT LIKE '%editor%' AND 
                        cap.meta_value NOT LIKE '%shop_manager%' AND 
                        cap.meta_value NOT LIKE '%cashier%'
                    ))";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $stats['vip'] = $result['count'] ?? 0;
            
            // Total loyalty points (exclude staff roles)
            $sql = "SELECT COALESCE(SUM(lp.points), 0) as total 
                    FROM pos_loyalty_points lp
                    INNER JOIN {$this->prefix}users u ON lp.customer_id = u.ID
                    LEFT JOIN {$this->prefix}usermeta cap ON u.ID = cap.user_id 
                        AND cap.meta_key = '{$this->prefix}capabilities'
                    WHERE cap.meta_value IS NULL OR (
                        cap.meta_value NOT LIKE '%administrator%' AND 
                        cap.meta_value NOT LIKE '%editor%' AND 
                        cap.meta_value NOT LIKE '%shop_manager%' AND 
                        cap.meta_value NOT LIKE '%cashier%'
                    )";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $stats['total_points'] = $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error fetching customer stats: " . $e->getMessage());
        }
        
        return $stats;
    }
}

/**
 * WordPress-style password hashing function
 */
function wp_hash_password($password) {
    $random = '';
    for ($i = 0; $i < 6; $i++) {
        $random .= chr(mt_rand(0, 255));
    }
    $random = base64_encode($random);
    $salt = substr(str_replace('+', '.', $random), 0, 8);
    
    $count_log2 = 8;
    $hash = md5($salt . $password, true);
    $count = 1 << $count_log2;
    do {
        $hash = md5($hash . $password, true);
    } while (--$count);
    
    $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $output = '$P$';
    $output .= $itoa64[min($count_log2, 30)];
    $output .= $salt;
    
    $i = 0;
    do {
        $value = ord($hash[$i++]);
        $output .= $itoa64[$value & 0x3f];
        if ($i < 16)
            $value |= ord($hash[$i]) << 8;
        $output .= $itoa64[($value >> 6) & 0x3f];
        if ($i++ >= 16)
            break;
        if ($i < 16)
            $value |= ord($hash[$i]) << 16;
        $output .= $itoa64[($value >> 12) & 0x3f];
        if ($i++ >= 16)
            break;
        $output .= $itoa64[($value >> 18) & 0x3f];
    } while ($i < 16);
    
    return $output;
}
