<?php
/**
 * Inventory Model
 * Handles stock management, low stock alerts, and inventory tracking
 */

require_once __DIR__ . '/BaseModel.php';

class Inventory extends BaseModel {
    
    /**
     * Get low stock products
     */
    public function getLowStockProducts($threshold = null) {
        $prefix = $this->db->getPrefix();
        
        if ($threshold === null) {
            $settingsSql = "SELECT setting_value FROM pos_settings WHERE setting_key = 'low_stock_threshold' LIMIT 1";
            $stmt = $this->db->prepare($settingsSql);
            $stmt->execute();
            $setting = $stmt->fetch();
            $threshold = $setting ? (int)$setting['setting_value'] : 10;
        }
        
        $sql = "SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                pm1.meta_value as sku,
                CAST(pm2.meta_value AS SIGNED) as stock_quantity,
                CAST(pm3.meta_value AS DECIMAL(10,2)) as price,
                p.post_status as status
                FROM {$prefix}posts p
                LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_sku'
                LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_stock'
                LEFT JOIN {$prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_price'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND CAST(pm2.meta_value AS SIGNED) <= ?
                AND CAST(pm2.meta_value AS SIGNED) > 0
                ORDER BY CAST(pm2.meta_value AS SIGNED) ASC
                LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts() {
        $prefix = $this->db->getPrefix();
        
        $sql = "SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                pm1.meta_value as sku,
                CAST(pm2.meta_value AS DECIMAL(10,2)) as price
                FROM {$prefix}posts p
                LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_sku'
                LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_price'
                LEFT JOIN {$prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_stock'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND (CAST(pm3.meta_value AS SIGNED) <= 0 OR pm3.meta_value IS NULL OR pm3.meta_value = '')
                LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get inventory summary
     */
    public function getInventorySummary() {
        $prefix = $this->db->getPrefix();
        
        $sql = "SELECT 
                COUNT(DISTINCT p.ID) as total_products,
                SUM(CASE WHEN CAST(pm.meta_value AS SIGNED) > 0 THEN 1 ELSE 0 END) as in_stock_count,
                SUM(CASE WHEN CAST(pm.meta_value AS SIGNED) <= 0 OR pm.meta_value IS NULL THEN 1 ELSE 0 END) as out_of_stock_count,
                SUM(CAST(pm.meta_value AS SIGNED)) as total_stock_quantity
                FROM {$prefix}posts p
                LEFT JOIN {$prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_stock'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $summary = $stmt->fetch();
        
        $lowStockCount = count($this->getLowStockProducts());
        $summary['low_stock_count'] = $lowStockCount;
        
        return $summary;
    }
    
    /**
     * Update product stock
     */
    public function updateStock($productId, $quantity, $operation = 'set') {
        $prefix = $this->db->getPrefix();
        
        if ($operation === 'add') {
            $sql = "UPDATE {$prefix}postmeta 
                    SET meta_value = CAST(meta_value AS SIGNED) + ?
                    WHERE post_id = ? AND meta_key = '_stock'";
        } elseif ($operation === 'subtract') {
            $sql = "UPDATE {$prefix}postmeta 
                    SET meta_value = GREATEST(0, CAST(meta_value AS SIGNED) - ?)
                    WHERE post_id = ? AND meta_key = '_stock'";
        } else {
            $sql = "UPDATE {$prefix}postmeta 
                    SET meta_value = ?
                    WHERE post_id = ? AND meta_key = '_stock'";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $productId]);
    }
    
    /**
     * Get stock history for a product
     */
    public function getStockHistory($productId, $limit = 50) {
        $sql = "SELECT 
                al.action,
                al.details,
                al.user_id,
                al.created_at
                FROM pos_audit_logs al
                WHERE al.action LIKE 'stock_%'
                AND JSON_EXTRACT(al.details, '$.product_id') = ?
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get inventory valuation
     */
    public function getInventoryValuation() {
        $prefix = $this->db->getPrefix();
        
        $sql = "SELECT 
                SUM(CAST(pm1.meta_value AS SIGNED) * CAST(pm2.meta_value AS DECIMAL(10,2))) as total_value,
                COUNT(DISTINCT p.ID) as product_count
                FROM {$prefix}posts p
                LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_stock'
                LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_price'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND CAST(pm1.meta_value AS SIGNED) > 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Get fast moving products
     */
    public function getFastMovingProducts($days = 30, $limit = 20) {
        $startDate = date('Y-m-d', strtotime("-{$days} days")) . ' 00:00:00';
        $endDate = date('Y-m-d H:i:s');
        
        $sql = "SELECT 
                oi.product_id,
                oi.product_name,
                oi.sku,
                SUM(oi.quantity) as total_sold,
                COUNT(DISTINCT oi.order_id) as order_frequency,
                AVG(oi.price) as avg_price
                FROM pos_order_items oi
                INNER JOIN pos_orders o ON oi.order_id = o.id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.order_status IN ('completed', 'processing')
                GROUP BY oi.product_id, oi.product_name, oi.sku
                ORDER BY total_sold DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get slow moving products
     */
    public function getSlowMovingProducts($days = 90, $limit = 20) {
        $prefix = $this->db->getPrefix();
        $startDate = date('Y-m-d', strtotime("-{$days} days")) . ' 00:00:00';
        $endDate = date('Y-m-d H:i:s');
        
        $sql = "SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                pm1.meta_value as sku,
                CAST(pm2.meta_value AS SIGNED) as stock_quantity,
                COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM {$prefix}posts p
                LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_sku'
                LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_stock'
                LEFT JOIN pos_order_items oi ON p.ID = oi.product_id
                LEFT JOIN pos_orders o ON oi.order_id = o.id AND o.order_date BETWEEN ? AND ?
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND CAST(pm2.meta_value AS SIGNED) > 0
                GROUP BY p.ID, p.post_title, pm1.meta_value, pm2.meta_value
                HAVING total_sold <= 5
                ORDER BY total_sold ASC, stock_quantity DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $limit]);
        return $stmt->fetchAll();
    }
}
