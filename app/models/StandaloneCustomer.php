<?php
/**
 * Standalone Customer Model
 * Handles customer data for standalone (non-WordPress) database
 */

require_once __DIR__ . '/BaseModel.php';

class StandaloneCustomer extends BaseModel {
    protected $table = 'customers';

    public function getAllCustomers($limit = 20, $offset = 0, $search = '', $status = '') {
        $sql = "SELECT 
                    c.id,
                    c.email,
                    c.phone as mobile,
                    c.first_name,
                    c.last_name,
                    c.display_name as name,
                    c.address_1 as address,
                    c.city,
                    c.state,
                    c.postcode as pincode,
                    c.loyalty_points,
                    c.total_spent,
                    c.order_count,
                    c.created_at
                FROM customers c
                WHERE c.status = 'active'";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (c.email LIKE ? OR c.phone LIKE ? OR c.first_name LIKE ? 
                      OR c.last_name LIKE ? OR c.display_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countCustomers($search = '', $status = '') {
        $sql = "SELECT COUNT(*) as total FROM customers WHERE status = 'active'";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (email LIKE ? OR phone LIKE ? OR first_name LIKE ? 
                      OR last_name LIKE ? OR display_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function searchCustomers($query, $limit = 20) {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT 
                    c.id,
                    c.email,
                    c.phone as mobile,
                    c.first_name,
                    c.last_name,
                    c.display_name as name
                FROM customers c
                WHERE c.status = 'active'
                AND (c.email LIKE ? OR c.phone LIKE ? OR c.first_name LIKE ? 
                     OR c.last_name LIKE ? OR c.display_name LIKE ?)
                ORDER BY c.display_name ASC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll();
    }

    public function getCustomer($customerId) {
        $sql = "SELECT 
                    c.id,
                    c.email,
                    c.phone as mobile,
                    c.first_name,
                    c.last_name,
                    c.display_name as name,
                    c.address_1 as address,
                    c.city,
                    c.state,
                    c.postcode as pincode,
                    c.country,
                    c.gstin,
                    c.loyalty_points,
                    c.total_spent,
                    c.order_count
                FROM customers c
                WHERE c.id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetch();
    }

    public function getByMobile($mobile) {
        $sql = "SELECT 
                    c.id,
                    c.email,
                    c.phone as mobile,
                    c.first_name,
                    c.last_name,
                    c.display_name as name
                FROM customers c
                WHERE c.phone = ?
                AND c.status = 'active'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mobile]);
        return $stmt->fetch();
    }

    public function createCustomer($data) {
        $sql = "INSERT INTO customers 
                (email, phone, first_name, last_name, display_name,
                 address_1, city, state, postcode, country, gstin, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $displayName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['email'] ?? '',
            $data['mobile'] ?? $data['phone'] ?? '',
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $displayName ?: ($data['email'] ?? 'Customer'),
            $data['address'] ?? $data['address_1'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['pincode'] ?? $data['postcode'] ?? '',
            $data['country'] ?? 'IN',
            $data['gstin'] ?? ''
        ]);
        
        return $this->db->lastInsertId();
    }

    public function updateCustomer($customerId, $data) {
        $fields = [];
        $params = [];
        
        $fieldMap = [
            'email' => 'email',
            'mobile' => 'phone',
            'phone' => 'phone',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'display_name' => 'display_name',
            'address' => 'address_1',
            'city' => 'city',
            'state' => 'state',
            'pincode' => 'postcode',
            'gstin' => 'gstin'
        ];
        
        foreach ($fieldMap as $input => $column) {
            if (isset($data[$input])) {
                $fields[] = "{$column} = ?";
                $params[] = $data[$input];
            }
        }
        
        if (empty($fields)) return false;
        
        $fields[] = "updated_at = NOW()";
        $params[] = $customerId;
        
        $sql = "UPDATE customers SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteCustomer($customerId) {
        $sql = "UPDATE customers SET status = 'inactive' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$customerId]);
    }

    public function getLoyaltyPoints($customerId) {
        $sql = "SELECT loyalty_points as points FROM customers WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        $result = $stmt->fetch();
        return $result['points'] ?? 0;
    }

    public function addLoyaltyPoints($customerId, $points, $orderId = null) {
        $sql = "UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$points, $customerId]);
        
        $logSql = "INSERT INTO loyalty_transactions (customer_id, order_id, points, type, description)
                   VALUES (?, ?, ?, 'earned', 'Points earned from order')";
        $logStmt = $this->db->prepare($logSql);
        $logStmt->execute([$customerId, $orderId, $points]);
        
        return true;
    }

    public function updateOrderStats($customerId, $orderTotal) {
        $sql = "UPDATE customers SET 
                total_spent = total_spent + ?,
                order_count = order_count + 1,
                updated_at = NOW()
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$orderTotal, $customerId]);
    }

    public function getCustomerOrders($customerId, $limit = 10) {
        $sql = "SELECT 
                    o.id,
                    o.order_number,
                    o.total,
                    o.status,
                    o.payment_method,
                    o.created_at
                FROM orders o
                WHERE o.customer_id = ?
                ORDER BY o.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $limit]);
        return $stmt->fetchAll();
    }

    public function getStats() {
        $stats = [
            'total' => 0,
            'new_this_month' => 0,
            'vip' => 0,
            'total_points' => 0
        ];
        
        try {
            $sql = "SELECT COUNT(*) as count FROM customers WHERE status = 'active'";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $stats['total'] = $result['count'] ?? 0;
            
            $sql = "SELECT COUNT(*) as count FROM customers 
                    WHERE status = 'active'
                    AND MONTH(created_at) = MONTH(CURDATE())
                    AND YEAR(created_at) = YEAR(CURDATE())";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $stats['new_this_month'] = $result['count'] ?? 0;
            
            $sql = "SELECT COUNT(*) as count FROM customers 
                    WHERE status = 'active' AND loyalty_points >= 5000";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $stats['vip'] = $result['count'] ?? 0;
            
            $sql = "SELECT COALESCE(SUM(loyalty_points), 0) as total FROM customers WHERE status = 'active'";
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch();
            $stats['total_points'] = $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error fetching customer stats: " . $e->getMessage());
        }
        
        return $stats;
    }
}
