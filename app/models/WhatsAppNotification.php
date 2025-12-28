<?php
/**
 * WhatsApp Notification Model
 * Handles WhatsApp Business API integration for customer notifications
 */

require_once __DIR__ . '/BaseModel.php';

class WhatsAppNotification extends BaseModel {
    protected $table = 'pos_whatsapp_logs';
    private $apiEndpoint;
    private $apiToken;
    private $phoneNumberId;
    
    public function __construct() {
        parent::__construct();
        
        $this->apiEndpoint = getenv('WHATSAPP_API_ENDPOINT') ?: '';
        $this->apiToken = getenv('WHATSAPP_API_TOKEN') ?: '';
        $this->phoneNumberId = getenv('WHATSAPP_PHONE_NUMBER_ID') ?: '';
    }
    
    /**
     * Send order confirmation message
     */
    public function sendOrderConfirmation($orderId) {
        $orderModel = new Order();
        $order = $orderModel->getOrder($orderId);
        
        if (!$order || empty($order['customer_mobile'])) {
            return false;
        }
        
        $mobile = $this->formatMobile($order['customer_mobile']);
        
        $message = "*Order Confirmed!* ✅\n\n";
        $message .= "Order #: *{$order['order_number']}*\n";
        $message .= "Date: " . date('d-M-Y h:i A', strtotime($order['order_date'])) . "\n";
        $message .= "Total: ₹*" . number_format($order['total'], 2) . "*\n\n";
        $message .= "Thank you for your purchase!\n";
        $message .= "Visit us again soon! 🛍️";
        
        return $this->sendMessage($mobile, $message, 'order_confirmation', $orderId);
    }
    
    /**
     * Send payment receipt via WhatsApp
     */
    public function sendReceipt($orderId) {
        $orderModel = new Order();
        $order = $orderModel->getOrder($orderId);
        $items = $orderModel->getOrderItems($orderId);
        
        if (!$order || empty($order['customer_mobile'])) {
            return false;
        }
        
        $mobile = $this->formatMobile($order['customer_mobile']);
        
        $message = "*📃 Payment Receipt*\n\n";
        $message .= "Order #: {$order['order_number']}\n";
        $message .= "Date: " . date('d-M-Y h:i A', strtotime($order['order_date'])) . "\n";
        $message .= "━━━━━━━━━━━━━━━━\n\n";
        
        foreach ($items as $item) {
            $message .= "• {$item['product_name']}\n";
            $message .= "  Qty: {$item['quantity']} × ₹{$item['price']} = ₹" . number_format($item['total'], 2) . "\n";
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━\n";
        $message .= "Subtotal: ₹" . number_format($order['subtotal'], 2) . "\n";
        if ($order['discount_amount'] > 0) {
            $message .= "Discount: -₹" . number_format($order['discount_amount'], 2) . "\n";
        }
        $message .= "Tax: ₹" . number_format($order['tax_amount'], 2) . "\n";
        $message .= "*Total: ₹" . number_format($order['total'], 2) . "*\n\n";
        $message .= "Payment: " . strtoupper($order['payment_method']) . "\n";
        $message .= "Status: ✅ PAID\n\n";
        $message .= "Thank you for shopping with us! 🙏";
        
        return $this->sendMessage($mobile, $message, 'receipt', $orderId);
    }
    
    /**
     * Send low stock alert to manager
     */
    public function sendLowStockAlert($productName, $currentStock, $managerMobile) {
        $mobile = $this->formatMobile($managerMobile);
        
        $message = "⚠️ *Low Stock Alert*\n\n";
        $message .= "Product: *{$productName}*\n";
        $message .= "Current Stock: *{$currentStock} units*\n\n";
        $message .= "Action required: Please reorder this product.";
        
        return $this->sendMessage($mobile, $message, 'low_stock_alert');
    }
    
    /**
     * Send daily sales summary
     */
    public function sendDailySalesSummary($mobile, $summary) {
        $mobile = $this->formatMobile($mobile);
        
        $message = "📊 *Daily Sales Summary*\n";
        $message .= date('d-M-Y') . "\n\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "Total Orders: *{$summary['total_orders']}*\n";
        $message .= "Total Sales: *₹" . number_format($summary['total_sales'], 2) . "*\n";
        $message .= "Avg Order: ₹" . number_format($summary['avg_order'], 2) . "\n";
        $message .= "Cash: ₹" . number_format($summary['cash_sales'], 2) . "\n";
        $message .= "Card/UPI: ₹" . number_format($summary['digital_sales'], 2) . "\n";
        $message .= "━━━━━━━━━━━━━━━━\n\n";
        $message .= "Great work today! 💪";
        
        return $this->sendMessage($mobile, $message, 'daily_summary');
    }
    
    /**
     * Send return status update
     */
    public function sendReturnStatusUpdate($returnId, $status) {
        $returnModel = new ReturnOrder();
        $return = $returnModel->getReturn($returnId);
        
        if (!$return || empty($return['customer_mobile'])) {
            return false;
        }
        
        $mobile = $this->formatMobile($return['customer_mobile']);
        
        $statusEmoji = match($status) {
            'approved' => '✅',
            'rejected' => '❌',
            'completed' => '🎉',
            default => 'ℹ️'
        };
        
        $message = "{$statusEmoji} *Return Status Update*\n\n";
        $message .= "Return #: {$return['return_number']}\n";
        $message .= "Status: *" . strtoupper($status) . "*\n\n";
        
        if ($status === 'approved') {
            $message .= "Your return has been approved.\n";
            $message .= "Refund Amount: ₹" . number_format($return['refund_amount'], 2) . "\n";
            $message .= "Method: " . strtoupper($return['refund_method']) . "\n";
        } elseif ($status === 'rejected') {
            $message .= "Your return has been rejected.\n";
            if (!empty($return['approval_notes'])) {
                $message .= "Reason: {$return['approval_notes']}\n";
            }
        }
        
        $message .= "\nThank you!";
        
        return $this->sendMessage($mobile, $message, 'return_status', $returnId);
    }
    
    /**
     * Send promotional message
     */
    public function sendPromotion($mobile, $title, $message, $offerDetails = '') {
        $mobile = $this->formatMobile($mobile);
        
        $fullMessage = "🎁 *{$title}*\n\n";
        $fullMessage .= $message . "\n\n";
        
        if (!empty($offerDetails)) {
            $fullMessage .= "━━━━━━━━━━━━━━━━\n";
            $fullMessage .= $offerDetails . "\n";
            $fullMessage .= "━━━━━━━━━━━━━━━━\n\n";
        }
        
        $fullMessage .= "Visit us today! 🛍️";
        
        return $this->sendMessage($mobile, $fullMessage, 'promotion');
    }
    
    /**
     * Send birthday wishes
     */
    public function sendBirthdayWish($customerId) {
        $customerModel = new Customer();
        $customer = $customerModel->getCustomer($customerId);
        
        if (!$customer || empty($customer['mobile'])) {
            return false;
        }
        
        $mobile = $this->formatMobile($customer['mobile']);
        $name = $customer['first_name'];
        
        $message = "🎂 *Happy Birthday {$name}!* 🎉\n\n";
        $message .= "Wishing you a fantastic day filled with joy!\n\n";
        $message .= "🎁 Special Gift: Use code *BIRTHDAY10* for 10% off your next purchase!\n\n";
        $message .= "Celebrate with us! 🥳";
        
        return $this->sendMessage($mobile, $message, 'birthday', $customerId);
    }
    
    /**
     * Core WhatsApp message sending function
     */
    private function sendMessage($mobile, $message, $type = 'general', $referenceId = null) {
        if (empty($this->apiEndpoint) || empty($this->apiToken)) {
            $this->logMessage($mobile, $message, $type, 'failed', 'API credentials not configured', $referenceId);
            return false;
        }
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $mobile,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];
        
        $ch = curl_init($this->apiEndpoint . '/' . $this->phoneNumberId . '/messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $success = ($httpCode >= 200 && $httpCode < 300);
        $status = $success ? 'sent' : 'failed';
        
        $this->logMessage($mobile, $message, $type, $status, $response, $referenceId);
        
        return $success;
    }
    
    /**
     * Log WhatsApp message
     */
    private function logMessage($mobile, $message, $type, $status, $response, $referenceId = null) {
        $sql = "INSERT INTO {$this->table} 
                (mobile_number, message_text, message_type, status, api_response, reference_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $mobile,
            $message,
            $type,
            $status,
            $response,
            $referenceId
        ]);
    }
    
    /**
     * Format mobile number (add country code if missing)
     */
    private function formatMobile($mobile) {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        if (strlen($mobile) == 10) {
            return '91' . $mobile;
        }
        
        return $mobile;
    }
    
    /**
     * Get message logs
     */
    public function getMessageLogs($limit = 50, $offset = 0) {
        $sql = "SELECT * FROM {$this->table} 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get statistics
     */
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_messages,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as today_count
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}
