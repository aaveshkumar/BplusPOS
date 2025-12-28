<?php
/**
 * Multi-Store Model
 * Handles multiple store locations and their operations
 */

require_once __DIR__ . '/BaseModel.php';

class MultiStore extends BaseModel {
    protected $table = 'pos_stores';
    
    /**
     * Get all stores
     */
    public function getAllStores() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY is_main DESC, name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get store by ID
     */
    public function getStore($storeId) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$storeId]);
        return $stmt->fetch();
    }
    
    /**
     * Create new store
     */
    public function createStore($data) {
        $sql = "INSERT INTO {$this->table} 
                (name, code, address, city, state, pincode, country, phone, email, 
                gst_number, receipt_header, receipt_footer, manager_name, manager_phone, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['code'],
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['pincode'] ?? '',
            $data['country'] ?? 'India',
            $data['phone'] ?? '',
            $data['email'] ?? '',
            $data['gst_number'] ?? '',
            $data['receipt_header'] ?? '',
            $data['receipt_footer'] ?? '',
            $data['manager_name'] ?? '',
            $data['manager_phone'] ?? ''
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update store
     */
    public function updateStore($storeId, $data) {
        $sql = "UPDATE {$this->table} SET 
                name = ?,
                address = ?,
                city = ?,
                state = ?,
                pincode = ?,
                country = ?,
                phone = ?,
                email = ?,
                gst_number = ?,
                receipt_header = ?,
                receipt_footer = ?,
                manager_name = ?,
                manager_phone = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['pincode'] ?? '',
            $data['country'] ?? 'India',
            $data['phone'] ?? '',
            $data['email'] ?? '',
            $data['gst_number'] ?? '',
            $data['receipt_header'] ?? '',
            $data['receipt_footer'] ?? '',
            $data['manager_name'] ?? '',
            $data['manager_phone'] ?? '',
            $storeId
        ]);
    }
    
    /**
     * Get store performance
     */
    public function getStorePerformance($storeId, $startDate, $endDate) {
        $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                AVG(total) as avg_order_value,
                SUM(discount_amount) as total_discounts,
                SUM(tax_amount) as total_tax
                FROM pos_orders
                WHERE store_id = ?
                AND order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$storeId, $startDate, $endDate]);
        return $stmt->fetch();
    }
    
    /**
     * Compare stores performance
     */
    public function compareStoresPerformance($startDate, $endDate) {
        $sql = "SELECT 
                s.id as store_id,
                s.name as store_name,
                s.city,
                COUNT(o.id) as total_orders,
                COALESCE(SUM(o.total), 0) as total_sales,
                COALESCE(AVG(o.total), 0) as avg_order_value,
                COUNT(DISTINCT o.customer_id) as unique_customers
                FROM {$this->table} s
                LEFT JOIN pos_orders o ON s.id = o.store_id 
                    AND o.order_date BETWEEN ? AND ?
                    AND o.order_status IN ('completed', 'processing')
                WHERE s.status = 'active'
                GROUP BY s.id, s.name, s.city
                ORDER BY total_sales DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get store inventory summary
     */
    public function getStoreInventorySummary($storeId) {
        $sql = "SELECT 
                COUNT(DISTINCT product_id) as total_products,
                SUM(stock_quantity) as total_stock,
                SUM(stock_value) as total_stock_value
                FROM pos_store_inventory
                WHERE store_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$storeId]);
        return $stmt->fetch();
    }
    
    /**
     * Transfer inventory between stores
     */
    public function transferInventory($fromStoreId, $toStoreId, $productId, $quantity, $userId) {
        $this->db->beginTransaction();
        
        try {
            $sql = "UPDATE pos_store_inventory 
                    SET stock_quantity = stock_quantity - ?
                    WHERE store_id = ? AND product_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$quantity, $fromStoreId, $productId]);
            
            $sql = "INSERT INTO pos_store_inventory (store_id, product_id, stock_quantity)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE stock_quantity = stock_quantity + ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$toStoreId, $productId, $quantity, $quantity]);
            
            $sql = "INSERT INTO pos_audit_logs (user_id, action, details)
                    VALUES (?, 'inventory_transfer', ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, json_encode([
                'from_store' => $fromStoreId,
                'to_store' => $toStoreId,
                'product_id' => $productId,
                'quantity' => $quantity
            ])]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Inventory transfer failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get store statistics
     */
    public function getStoreStats($storeId) {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m-01');
        
        return [
            'today' => $this->getStorePerformance($storeId, $today . ' 00:00:00', $today . ' 23:59:59'),
            'this_month' => $this->getStorePerformance($storeId, $thisMonth . ' 00:00:00', date('Y-m-d H:i:s')),
            'inventory' => $this->getStoreInventorySummary($storeId)
        ];
    }
    
    /**
     * Deactivate store
     */
    public function deactivateStore($storeId) {
        $sql = "UPDATE {$this->table} SET status = 'inactive' WHERE id = ? AND is_main = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$storeId]);
    }
    
    /**
     * Get main store
     */
    public function getMainStore() {
        $sql = "SELECT * FROM {$this->table} WHERE is_main = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}
