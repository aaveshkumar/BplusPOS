<?php
/**
 * Order Model
 * Handles order data from WooCommerce database
 */

require_once __DIR__ . '/BaseModel.php';

class Order extends BaseModel {
    protected $table = 'posts';

    /**
     * Get recent orders
     */
    public function getRecentOrders($limit = 20, $offset = 0) {
        $sql = "SELECT 
                    p.ID as order_id,
                    p.post_date as order_date,
                    p.post_status as status,
                    pm1.meta_value as total,
                    pm2.meta_value as customer_id,
                    pm3.meta_value as payment_method
                FROM {$this->prefix}posts p
                LEFT JOIN {$this->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_order_total'
                LEFT JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_customer_user'
                LEFT JOIN {$this->prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_payment_method'
                WHERE p.post_type = 'shop_order'
                ORDER BY p.post_date DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get order by ID
     */
    public function getOrder($orderId) {
        $sql = "SELECT 
                    p.ID as order_id,
                    p.post_date as order_date,
                    p.post_status as status,
                    pm1.meta_value as total,
                    pm2.meta_value as subtotal,
                    pm3.meta_value as tax,
                    pm4.meta_value as customer_id,
                    pm5.meta_value as payment_method,
                    pm6.meta_value as payment_method_title
                FROM {$this->prefix}posts p
                LEFT JOIN {$this->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_order_total'
                LEFT JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_order_subtotal'
                LEFT JOIN {$this->prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_order_tax'
                LEFT JOIN {$this->prefix}postmeta pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_customer_user'
                LEFT JOIN {$this->prefix}postmeta pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_payment_method'
                LEFT JOIN {$this->prefix}postmeta pm6 ON p.ID = pm6.post_id AND pm6.meta_key = '_payment_method_title'
                WHERE p.ID = ?
                AND p.post_type = 'shop_order'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    /**
     * Get order items
     */
    public function getOrderItems($orderId) {
        $sql = "SELECT 
                    oi.order_item_id,
                    oi.order_item_name as product_name,
                    oim1.meta_value as quantity,
                    oim2.meta_value as total,
                    oim3.meta_value as product_id,
                    oim4.meta_value as tax_amount,
                    oim5.meta_value as line_subtotal,
                    pm1.meta_value as regular_price,
                    pm2.meta_value as sale_price
                FROM {$this->prefix}woocommerce_order_items oi
                LEFT JOIN {$this->prefix}woocommerce_order_itemmeta oim1 ON oi.order_item_id = oim1.order_item_id AND oim1.meta_key = '_qty'
                LEFT JOIN {$this->prefix}woocommerce_order_itemmeta oim2 ON oi.order_item_id = oim2.order_item_id AND oim2.meta_key = '_line_total'
                LEFT JOIN {$this->prefix}woocommerce_order_itemmeta oim3 ON oi.order_item_id = oim3.order_item_id AND oim3.meta_key = '_product_id'
                LEFT JOIN {$this->prefix}woocommerce_order_itemmeta oim4 ON oi.order_item_id = oim4.order_item_id AND oim4.meta_key = '_line_tax'
                LEFT JOIN {$this->prefix}woocommerce_order_itemmeta oim5 ON oi.order_item_id = oim5.order_item_id AND oim5.meta_key = '_line_subtotal'
                LEFT JOIN {$this->prefix}postmeta pm1 ON oim3.meta_value = pm1.post_id AND pm1.meta_key = '_regular_price'
                LEFT JOIN {$this->prefix}postmeta pm2 ON oim3.meta_value = pm2.post_id AND pm2.meta_key = '_sale_price'
                WHERE oi.order_id = ?
                AND oi.order_item_type = 'line_item'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Get today's sales total
     */
    public function getTodaysSales() {
        $today = date('Y-m-d');
        
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CAST(pm.meta_value AS DECIMAL(10,2))) as total_sales
                FROM {$this->prefix}posts p
                INNER JOIN {$this->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
                WHERE p.post_type = 'shop_order'
                AND DATE(p.post_date) = ?
                AND p.post_status IN ('wc-completed', 'wc-processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today]);
        return $stmt->fetch();
    }

    /**
     * Get sales by date range
     */
    public function getSalesByDateRange($startDate, $endDate) {
        $sql = "SELECT 
                    DATE(p.post_date) as date,
                    COUNT(*) as total_orders,
                    SUM(CAST(pm.meta_value AS DECIMAL(10,2))) as total_sales
                FROM {$this->prefix}posts p
                INNER JOIN {$this->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
                WHERE p.post_type = 'shop_order'
                AND DATE(p.post_date) BETWEEN ? AND ?
                AND p.post_status IN ('wc-completed', 'wc-processing')
                GROUP BY DATE(p.post_date)
                ORDER BY DATE(p.post_date) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
}
