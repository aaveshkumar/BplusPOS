<?php
/**
 * Sales Report Model
 * Handles analytics, reporting, and business intelligence queries
 */

require_once __DIR__ . '/BaseModel.php';

class SalesReport extends BaseModel {
    
    /**
     * Get sales summary for date range
     */
    public function getSalesSummary($startDate, $endDate) {
        $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                SUM(subtotal) as total_subtotal,
                SUM(discount_amount) as total_discounts,
                SUM(tax_amount) as total_tax,
                AVG(total) as average_order_value,
                MIN(total) as min_order_value,
                MAX(total) as max_order_value
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetch();
    }
    
    /**
     * Get sales by date (for charts)
     */
    public function getSalesByDate($startDate, $endDate, $groupBy = 'day') {
        $dateFormat = match($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };
        
        $sql = "SELECT 
                DATE_FORMAT(order_date, ?) as period,
                COUNT(*) as order_count,
                SUM(total) as total_sales,
                SUM(subtotal) as subtotal,
                SUM(discount_amount) as discounts,
                AVG(total) as avg_order_value
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')
                GROUP BY period
                ORDER BY period ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dateFormat, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sales by payment method
     */
    public function getSalesByPaymentMethod($startDate, $endDate) {
        $sql = "SELECT 
                payment_method,
                COUNT(*) as order_count,
                SUM(total) as total_sales
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')
                GROUP BY payment_method
                ORDER BY total_sales DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get top selling products
     */
    public function getTopProducts($startDate, $endDate, $limit = 10) {
        $sql = "SELECT 
                oi.product_id,
                oi.product_name,
                oi.sku,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.total) as total_revenue,
                COUNT(DISTINCT oi.order_id) as order_count,
                AVG(oi.price) as avg_price
                FROM pos_order_items oi
                INNER JOIN pos_orders o ON oi.order_id = o.id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.order_status IN ('completed', 'processing')
                GROUP BY oi.product_id, oi.product_name, oi.sku
                ORDER BY total_revenue DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sales by cashier
     */
    public function getSalesByCashier($startDate, $endDate) {
        $sql = "SELECT 
                user_id,
                COUNT(*) as order_count,
                SUM(total) as total_sales,
                AVG(total) as avg_order_value
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')
                GROUP BY user_id
                ORDER BY total_sales DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get hourly sales distribution
     */
    public function getHourlySales($startDate, $endDate) {
        $sql = "SELECT 
                HOUR(order_date) as hour,
                COUNT(*) as order_count,
                SUM(total) as total_sales,
                AVG(total) as avg_order_value
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')
                GROUP BY hour
                ORDER BY hour ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get customer statistics
     */
    public function getCustomerStats($startDate, $endDate) {
        $sql = "SELECT 
                COUNT(DISTINCT customer_id) as unique_customers,
                COUNT(CASE WHEN customer_id > 0 THEN 1 END) as registered_customer_orders,
                COUNT(CASE WHEN customer_id = 0 THEN 1 END) as walk_in_orders,
                AVG(CASE WHEN customer_id > 0 THEN total END) as avg_registered_order,
                AVG(CASE WHEN customer_id = 0 THEN total END) as avg_walk_in_order
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetch();
    }
    
    /**
     * Get product category sales
     */
    public function getCategorySales($startDate, $endDate) {
        $prefix = $this->db->getPrefix();
        
        $sql = "SELECT 
                t.name as category_name,
                COUNT(DISTINCT oi.product_id) as product_count,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.total) as total_revenue
                FROM pos_order_items oi
                INNER JOIN pos_orders o ON oi.order_id = o.id
                LEFT JOIN {$prefix}term_relationships tr ON oi.product_id = tr.object_id
                LEFT JOIN {$prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'product_cat'
                LEFT JOIN {$prefix}terms t ON tt.term_id = t.term_id
                WHERE o.order_date BETWEEN ? AND ?
                AND o.order_status IN ('completed', 'processing')
                GROUP BY t.name
                ORDER BY total_revenue DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get discount analysis
     */
    public function getDiscountAnalysis($startDate, $endDate) {
        $sql = "SELECT 
                COUNT(*) as total_orders,
                COUNT(CASE WHEN discount_amount > 0 THEN 1 END) as orders_with_discount,
                SUM(discount_amount) as total_discount_amount,
                AVG(discount_amount) as avg_discount,
                AVG(CASE WHEN discount_amount > 0 THEN (discount_amount / subtotal) * 100 END) as avg_discount_percent
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetch();
    }
    
    /**
     * Get tax collection report
     */
    public function getTaxReport($startDate, $endDate) {
        $sql = "SELECT 
                DATE(order_date) as date,
                COUNT(*) as order_count,
                SUM(subtotal) as taxable_amount,
                SUM(tax_amount) as total_tax,
                SUM(total) as total_with_tax
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')
                GROUP BY DATE(order_date)
                ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get inventory movement report
     */
    public function getInventoryMovement($startDate, $endDate, $limit = 50) {
        $sql = "SELECT 
                oi.product_id,
                oi.product_name,
                oi.sku,
                SUM(oi.quantity) as total_sold,
                SUM(oi.total) as total_revenue,
                COUNT(DISTINCT o.id) as order_frequency
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
     * Get comparative sales (compare two periods)
     */
    public function getComparativeSales($period1Start, $period1End, $period2Start, $period2End) {
        $sql = "SELECT 
                'Period 1' as period,
                COUNT(*) as order_count,
                SUM(total) as total_sales,
                AVG(total) as avg_order_value
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')
                
                UNION ALL
                
                SELECT 
                'Period 2' as period,
                COUNT(*) as order_count,
                SUM(total) as total_sales,
                AVG(total) as avg_order_value
                FROM pos_orders
                WHERE order_date BETWEEN ? AND ?
                AND order_status IN ('completed', 'processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$period1Start, $period1End, $period2Start, $period2End]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get real-time dashboard stats
     */
    public function getDashboardStats() {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $thisMonth = date('Y-m-01');
        
        return [
            'today' => $this->getSalesSummary($today . ' 00:00:00', $today . ' 23:59:59'),
            'yesterday' => $this->getSalesSummary($yesterday . ' 00:00:00', $yesterday . ' 23:59:59'),
            'this_month' => $this->getSalesSummary($thisMonth . ' 00:00:00', date('Y-m-d H:i:s')),
            'top_products_today' => $this->getTopProducts($today . ' 00:00:00', $today . ' 23:59:59', 5)
        ];
    }
}
