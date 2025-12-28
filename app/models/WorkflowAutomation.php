<?php
/**
 * Workflow Automation Model
 * Handles automated tasks, triggers, and scheduled jobs
 */

require_once __DIR__ . '/BaseModel.php';

class WorkflowAutomation extends BaseModel {
    protected $table = 'pos_automation_rules';
    
    /**
     * Create automation rule
     */
    public function createRule($data) {
        $sql = "INSERT INTO {$this->table} 
                (rule_name, trigger_type, trigger_condition, action_type, action_config, is_active)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['rule_name'],
            $data['trigger_type'],
            json_encode($data['trigger_condition']),
            $data['action_type'],
            json_encode($data['action_config']),
            $data['is_active'] ?? true
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get all active rules
     */
    public function getActiveRules() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Process automation triggers
     */
    public function processTrigger($triggerType, $data) {
        $rules = $this->getActiveRules();
        
        foreach ($rules as $rule) {
            if ($rule['trigger_type'] === $triggerType) {
                $condition = json_decode($rule['trigger_condition'], true);
                
                if ($this->evaluateCondition($condition, $data)) {
                    $this->executeAction($rule['action_type'], json_decode($rule['action_config'], true), $data);
                    $this->logExecution($rule['id'], $data);
                }
            }
        }
    }
    
    /**
     * Evaluate trigger condition
     */
    private function evaluateCondition($condition, $data) {
        if (!isset($condition['field']) || !isset($condition['operator'])) {
            return false;
        }
        
        $value = $data[$condition['field']] ?? null;
        $compareValue = $condition['value'] ?? null;
        
        switch ($condition['operator']) {
            case 'equals':
                return $value == $compareValue;
            case 'greater_than':
                return $value > $compareValue;
            case 'less_than':
                return $value < $compareValue;
            case 'contains':
                return strpos($value, $compareValue) !== false;
            default:
                return false;
        }
    }
    
    /**
     * Execute automation action
     */
    private function executeAction($actionType, $config, $data) {
        switch ($actionType) {
            case 'send_email':
                $this->sendEmail($config, $data);
                break;
            
            case 'send_whatsapp':
                $this->sendWhatsApp($config, $data);
                break;
            
            case 'create_task':
                $this->createTask($config, $data);
                break;
            
            case 'update_customer_group':
                $this->updateCustomerGroup($config, $data);
                break;
            
            case 'award_loyalty_points':
                $this->awardLoyaltyPoints($config, $data);
                break;
            
            case 'generate_purchase_order':
                $this->generatePurchaseOrder($config, $data);
                break;
        }
    }
    
    /**
     * Auto-send receipt after payment
     */
    public function autoSendReceipt($orderId) {
        $this->processTrigger('order_completed', ['order_id' => $orderId]);
    }
    
    /**
     * Auto-award loyalty points
     */
    public function autoAwardPoints($customerId, $orderTotal) {
        $points = floor($orderTotal / 100);
        
        if ($points > 0) {
            $customerModel = new Customer();
            $customer = $customerModel->getCustomer($customerId);
            
            if ($customer) {
                $newPoints = $customer['loyalty_points'] + $points;
                $customerModel->updateCustomer($customerId, ['loyalty_points' => $newPoints]);
            }
        }
    }
    
    /**
     * Auto-upgrade customer group
     */
    public function autoUpgradeCustomerGroup($customerId) {
        $customerModel = new Customer();
        $customer = $customerModel->getCustomer($customerId);
        
        if (!$customer) return;
        
        $sql = "SELECT SUM(total) as total_spent FROM pos_orders WHERE customer_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        $result = $stmt->fetch();
        
        $totalSpent = $result['total_spent'] ?? 0;
        
        if ($totalSpent >= 100000 && $customer['customer_group'] !== 'VIP') {
            $customerModel->updateCustomer($customerId, ['customer_group' => 'VIP']);
            
            $whatsapp = new WhatsAppNotification();
            $message = "🎉 Congratulations! You've been upgraded to VIP status!\n\nEnjoy exclusive benefits and special discounts!";
            $whatsapp->sendMessage($customer['mobile'], $message, 'customer_upgrade', $customerId);
        }
    }
    
    /**
     * Auto-generate purchase order for low stock
     */
    public function autoGeneratePO($productId, $productName, $currentStock) {
        $sql = "INSERT INTO pos_purchase_orders (product_id, product_name, quantity_needed, status)
                VALUES (?, ?, ?, 'pending')";
        
        $stmt = $this->db->prepare($sql);
        $reorderQty = 100;
        $stmt->execute([$productId, $productName, $reorderQty]);
        
        $this->sendLowStockNotification($productName, $currentStock);
    }
    
    /**
     * Send low stock notification
     */
    private function sendLowStockNotification($productName, $currentStock) {
        $managerMobile = getenv('MANAGER_MOBILE') ?: '';
        
        if ($managerMobile) {
            $whatsapp = new WhatsAppNotification();
            $whatsapp->sendLowStockAlert($productName, $currentStock, $managerMobile);
        }
    }
    
    /**
     * Send email action
     */
    private function sendEmail($config, $data) {
        $to = $config['to'] ?? ($data['email'] ?? '');
        $subject = $config['subject'] ?? 'Notification';
        $message = $this->replacePlaceholders($config['message'] ?? '', $data);
        
        if ($to) {
            mail($to, $subject, $message);
        }
    }
    
    /**
     * Send WhatsApp action
     */
    private function sendWhatsApp($config, $data) {
        $mobile = $config['mobile'] ?? ($data['mobile'] ?? '');
        $message = $this->replacePlaceholders($config['message'] ?? '', $data);
        
        if ($mobile) {
            $whatsapp = new WhatsAppNotification();
            $whatsapp->sendMessage($mobile, $message, 'automation');
        }
    }
    
    /**
     * Create task action
     */
    private function createTask($config, $data) {
        $sql = "INSERT INTO pos_tasks (title, description, assigned_to, due_date, priority, status)
                VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $config['title'] ?? 'Automated Task',
            $this->replacePlaceholders($config['description'] ?? '', $data),
            $config['assigned_to'] ?? 1,
            $config['due_date'] ?? date('Y-m-d', strtotime('+7 days')),
            $config['priority'] ?? 'medium'
        ]);
    }
    
    /**
     * Update customer group action
     */
    private function updateCustomerGroup($config, $data) {
        if (isset($data['customer_id']) && isset($config['new_group'])) {
            $customerModel = new Customer();
            $customerModel->updateCustomer($data['customer_id'], ['customer_group' => $config['new_group']]);
        }
    }
    
    /**
     * Award loyalty points action
     */
    private function awardLoyaltyPoints($config, $data) {
        if (isset($data['customer_id'])) {
            $points = $config['points'] ?? floor($data['order_total'] / 100);
            
            $customerModel = new Customer();
            $customer = $customerModel->getCustomer($data['customer_id']);
            
            if ($customer) {
                $newPoints = $customer['loyalty_points'] + $points;
                $customerModel->updateCustomer($data['customer_id'], ['loyalty_points' => $newPoints]);
            }
        }
    }
    
    /**
     * Generate purchase order action
     */
    private function generatePurchaseOrder($config, $data) {
        $this->autoGeneratePO(
            $data['product_id'],
            $data['product_name'],
            $data['current_stock']
        );
    }
    
    /**
     * Replace placeholders in message
     */
    private function replacePlaceholders($message, $data) {
        foreach ($data as $key => $value) {
            $message = str_replace("{{" . $key . "}}", $value, $message);
        }
        return $message;
    }
    
    /**
     * Log automation execution
     */
    private function logExecution($ruleId, $data) {
        $sql = "INSERT INTO pos_automation_logs (rule_id, trigger_data, executed_at)
                VALUES (?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ruleId, json_encode($data)]);
    }
    
    /**
     * Get automation statistics
     */
    public function getStats() {
        $sql = "SELECT 
                COUNT(DISTINCT rule_id) as total_rules,
                COUNT(*) as total_executions,
                SUM(CASE WHEN executed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) as today_executions
                FROM pos_automation_logs";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Scheduled Jobs - Run daily
     */
    public function runDailyJobs() {
        $this->sendDailySalesReport();
        $this->checkLowStockProducts();
        $this->sendBirthdayWishes();
        $this->expireStoreCredits();
        $this->closeInactiveSessions();
    }
    
    /**
     * Send daily sales report
     */
    private function sendDailySalesReport() {
        $reportModel = new SalesReport();
        $summary = $reportModel->getSalesSummary(date('Y-m-d') . ' 00:00:00', date('Y-m-d') . ' 23:59:59');
        
        $ownerMobile = getenv('OWNER_MOBILE') ?: '';
        
        if ($ownerMobile) {
            $whatsapp = new WhatsAppNotification();
            $whatsapp->sendDailySalesSummary($ownerMobile, [
                'total_orders' => $summary['total_orders'],
                'total_sales' => $summary['total_sales'],
                'avg_order' => $summary['average_order_value'],
                'cash_sales' => $summary['total_sales'] * 0.6,
                'digital_sales' => $summary['total_sales'] * 0.4
            ]);
        }
    }
    
    /**
     * Check low stock and generate POs
     */
    private function checkLowStockProducts() {
        $inventoryModel = new Inventory();
        $lowStockProducts = $inventoryModel->getLowStockProducts(10);
        
        foreach ($lowStockProducts as $product) {
            $this->autoGeneratePO(
                $product['product_id'],
                $product['product_name'],
                $product['stock_quantity']
            );
        }
    }
    
    /**
     * Send birthday wishes to customers
     */
    private function sendBirthdayWishes() {
        $sql = "SELECT id, first_name, mobile FROM pos_customers 
                WHERE DAY(date_of_birth) = DAY(NOW()) 
                AND MONTH(date_of_birth) = MONTH(NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $customers = $stmt->fetchAll();
        
        $whatsapp = new WhatsAppNotification();
        
        foreach ($customers as $customer) {
            $whatsapp->sendBirthdayWish($customer['id']);
        }
    }
    
    /**
     * Expire old store credits
     */
    private function expireStoreCredits() {
        $sql = "UPDATE pos_store_credit 
                SET status = 'expired' 
                WHERE expires_at < NOW() 
                AND status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
    
    /**
     * Close inactive cashier sessions
     */
    private function closeInactiveSessions() {
        $sql = "UPDATE pos_sessions 
                SET status = 'closed', 
                    closed_at = NOW() 
                WHERE status = 'open' 
                AND opened_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
