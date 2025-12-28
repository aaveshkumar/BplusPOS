<?php
/**
 * Return Order Model
 * Handles returns, exchanges, and store credit
 */

require_once __DIR__ . '/BaseModel.php';

class ReturnOrder extends BaseModel {
    protected $table = 'pos_returns';

    /**
     * Create new return
     */
    public function createReturn($data) {
        $returnNumber = 'RET-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO {$this->table} 
                (return_number, original_order_id, customer_id, return_type, return_reason, 
                return_notes, total_amount, refund_amount, refund_method, processed_by, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $returnNumber,
            $data['original_order_id'],
            $data['customer_id'] ?? null,
            $data['return_type'],
            $data['return_reason'],
            $data['return_notes'] ?? null,
            $data['total_amount'],
            $data['refund_amount'],
            $data['refund_method'],
            $data['processed_by'],
            $data['status'] ?? 'pending'
        ]);
        
        return [
            'id' => $this->db->lastInsertId(),
            'return_number' => $returnNumber
        ];
    }

    /**
     * Add return items
     */
    public function addReturnItems($returnId, $items) {
        $sql = "INSERT INTO pos_return_items 
                (return_id, product_id, product_name, sku, quantity, price, total, 
                condition_status, restock, exchange_product_id, exchange_quantity, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($items as $item) {
            $stmt->execute([
                $returnId,
                $item['product_id'],
                $item['product_name'],
                $item['sku'] ?? null,
                $item['quantity'],
                $item['price'],
                $item['total'],
                $item['condition_status'] ?? 'new',
                $item['restock'] ?? true,
                $item['exchange_product_id'] ?? null,
                $item['exchange_quantity'] ?? null,
                $item['notes'] ?? null
            ]);
        }
        
        return true;
    }

    /**
     * Get all returns with pagination
     */
    public function getAllReturns($limit = 20, $offset = 0, $search = '', $status = '') {
        $sql = "SELECT r.*, 
                CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                o.order_number as original_order_number
                FROM {$this->table} r
                LEFT JOIN pos_customers c ON r.customer_id = c.id
                LEFT JOIN pos_orders o ON r.original_order_id = o.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (r.return_number LIKE ? OR o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($status)) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count returns
     */
    public function countReturns($search = '', $status = '') {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} r
                LEFT JOIN pos_customers c ON r.customer_id = c.id
                LEFT JOIN pos_orders o ON r.original_order_id = o.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (r.return_number LIKE ? OR o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($status)) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get return by ID with items
     */
    public function getReturn($returnId) {
        $sql = "SELECT r.*, 
                CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                c.email as customer_email,
                c.mobile as customer_mobile,
                o.order_number as original_order_number
                FROM {$this->table} r
                LEFT JOIN pos_customers c ON r.customer_id = c.id
                LEFT JOIN pos_orders o ON r.original_order_id = o.id
                WHERE r.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$returnId]);
        $return = $stmt->fetch();
        
        if ($return) {
            $return['items'] = $this->getReturnItems($returnId);
        }
        
        return $return;
    }

    /**
     * Get return items
     */
    public function getReturnItems($returnId) {
        $sql = "SELECT * FROM pos_return_items WHERE return_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$returnId]);
        return $stmt->fetchAll();
    }

    /**
     * Update return status
     */
    public function updateStatus($returnId, $status, $approvalNotes = null) {
        $sql = "UPDATE {$this->table} 
                SET status = ?, approval_notes = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $approvalNotes, $returnId]);
    }

    /**
     * Create store credit
     */
    public function createStoreCredit($customerId, $amount, $sourceType, $sourceId, $issuedBy, $expiresAt = null) {
        $creditNumber = 'SC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO pos_store_credit 
                (customer_id, credit_number, amount, balance, source_type, source_id, 
                issued_by, expires_at, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $customerId,
            $creditNumber,
            $amount,
            $amount,
            $sourceType,
            $sourceId,
            $issuedBy,
            $expiresAt
        ]);
        
        $creditId = $this->db->lastInsertId();
        
        $this->logStoreCreditTransaction($creditId, null, 'issue', $amount, $amount, 'Store credit issued', $issuedBy);
        
        return [
            'id' => $creditId,
            'credit_number' => $creditNumber
        ];
    }

    /**
     * Get customer store credits
     */
    public function getCustomerStoreCredits($customerId) {
        $sql = "SELECT * FROM pos_store_credit 
                WHERE customer_id = ? AND status = 'active' AND balance > 0
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Use store credit
     */
    public function useStoreCredit($creditId, $amount, $orderId, $processedBy) {
        $sql = "SELECT * FROM pos_store_credit WHERE id = ? AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$creditId]);
        $credit = $stmt->fetch();
        
        if (!$credit || $credit['balance'] < $amount) {
            return false;
        }
        
        $newBalance = $credit['balance'] - $amount;
        $newStatus = $newBalance <= 0 ? 'used' : 'active';
        
        $sql = "UPDATE pos_store_credit SET balance = ?, status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$newBalance, $newStatus, $creditId]);
        
        $this->logStoreCreditTransaction($creditId, $orderId, 'use', -$amount, $newBalance, 'Store credit applied to order', $processedBy);
        
        return true;
    }

    /**
     * Log store credit transaction
     */
    private function logStoreCreditTransaction($creditId, $orderId, $type, $amount, $balanceAfter, $description, $processedBy) {
        $sql = "INSERT INTO pos_store_credit_transactions 
                (store_credit_id, order_id, transaction_type, amount, balance_after, description, processed_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $creditId,
            $orderId,
            $type,
            $amount,
            $balanceAfter,
            $description,
            $processedBy
        ]);
    }

    /**
     * Get return statistics
     */
    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_returns,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_returns,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_returns,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN refund_amount ELSE 0 END) as refund_amount_30d
                FROM {$this->table}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}
