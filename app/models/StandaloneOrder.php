<?php
/**
 * Standalone Order Model
 * Handles order data for standalone database
 */

require_once __DIR__ . '/BaseModel.php';

class StandaloneOrder extends BaseModel {
    protected $table = 'orders';

    public function getRecentOrders($limit = 20, $offset = 0) {
        $sql = "SELECT 
                    o.id as order_id,
                    o.order_number,
                    o.created_at as order_date,
                    o.status,
                    o.total,
                    o.customer_id,
                    o.payment_method,
                    o.payment_method_title,
                    c.display_name as customer_name
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getOrder($orderId) {
        $sql = "SELECT 
                    o.id as order_id,
                    o.order_number,
                    o.created_at as order_date,
                    o.status,
                    o.subtotal,
                    o.discount_total,
                    o.tax_total as tax,
                    o.total,
                    o.customer_id,
                    o.user_id,
                    o.payment_method,
                    o.payment_method_title,
                    o.customer_note,
                    o.billing_first_name,
                    o.billing_last_name,
                    o.billing_email,
                    o.billing_phone,
                    o.billing_address_1,
                    o.billing_city,
                    o.billing_state,
                    o.billing_postcode
                FROM orders o
                WHERE o.id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function getOrderItems($orderId) {
        $sql = "SELECT 
                    oi.id as order_item_id,
                    oi.product_name,
                    oi.quantity,
                    oi.unit_price,
                    oi.subtotal as line_subtotal,
                    oi.tax_amount,
                    oi.total,
                    oi.product_id,
                    oi.sku,
                    p.regular_price,
                    p.sale_price
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function createOrder($data) {
        $orderNumber = $this->generateOrderNumber();
        
        $sql = "INSERT INTO orders 
                (order_number, customer_id, user_id, status, subtotal, discount_total,
                 discount_type, tax_total, total, payment_method, payment_method_title,
                 billing_first_name, billing_last_name, billing_email, billing_phone,
                 billing_address_1, billing_city, billing_state, billing_postcode,
                 customer_note, source, store_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pos', ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderNumber,
            $data['customer_id'] ?? null,
            $data['user_id'] ?? null,
            $data['status'] ?? 'completed',
            $data['subtotal'] ?? 0,
            $data['discount_total'] ?? 0,
            $data['discount_type'] ?? 'fixed',
            $data['tax_total'] ?? 0,
            $data['total'] ?? 0,
            $data['payment_method'] ?? 'cash',
            $data['payment_method_title'] ?? 'Cash',
            $data['billing_first_name'] ?? '',
            $data['billing_last_name'] ?? '',
            $data['billing_email'] ?? '',
            $data['billing_phone'] ?? '',
            $data['billing_address_1'] ?? '',
            $data['billing_city'] ?? '',
            $data['billing_state'] ?? '',
            $data['billing_postcode'] ?? '',
            $data['customer_note'] ?? '',
            $data['store_id'] ?? 1
        ]);
        
        $orderId = $this->db->lastInsertId();
        
        return ['order_id' => $orderId, 'order_number' => $orderNumber];
    }

    public function addOrderItem($orderId, $item) {
        $sql = "INSERT INTO order_items 
                (order_id, product_id, product_name, sku, quantity, unit_price,
                 subtotal, discount, tax_amount, tax_class, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderId,
            $item['product_id'] ?? null,
            $item['product_name'] ?? '',
            $item['sku'] ?? '',
            $item['quantity'] ?? 1,
            $item['unit_price'] ?? 0,
            $item['subtotal'] ?? 0,
            $item['discount'] ?? 0,
            $item['tax_amount'] ?? 0,
            $item['tax_class'] ?? 'standard',
            $item['total'] ?? 0
        ]);
        
        return $this->db->lastInsertId();
    }

    private function generateOrderNumber() {
        $prefix = 'POS';
        $date = date('Ymd');
        
        $sql = "SELECT MAX(CAST(SUBSTRING(order_number, 12) AS UNSIGNED)) as last_num 
                FROM orders WHERE order_number LIKE ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["{$prefix}{$date}%"]);
        $result = $stmt->fetch();
        
        $nextNum = ($result['last_num'] ?? 0) + 1;
        return $prefix . $date . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function updateOrderStatus($orderId, $status) {
        $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $orderId]);
    }

    public function getTodaysSales() {
        $today = date('Y-m-d');
        
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total), 0) as total_sales
                FROM orders
                WHERE DATE(created_at) = ?
                AND status IN ('completed', 'processing')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$today]);
        return $stmt->fetch();
    }

    public function getSalesByDateRange($startDate, $endDate) {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_orders,
                    SUM(total) as total_sales
                FROM orders
                WHERE DATE(created_at) BETWEEN ? AND ?
                AND status IN ('completed', 'processing')
                GROUP BY DATE(created_at)
                ORDER BY DATE(created_at) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getOrdersByCustomer($customerId, $limit = 10) {
        $sql = "SELECT 
                    o.id as order_id,
                    o.order_number,
                    o.created_at as order_date,
                    o.status,
                    o.total
                FROM orders o
                WHERE o.customer_id = ?
                ORDER BY o.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId, $limit]);
        return $stmt->fetchAll();
    }
}
