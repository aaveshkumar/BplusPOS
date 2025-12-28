<?php
/**
 * Admin Controller
 * Handles admin dashboard, settings, and system management
 */

require_once ROOT_PATH . '/app/controllers/BaseController.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Order.php';
require_once ROOT_PATH . '/app/models/Product.php';
require_once ROOT_PATH . '/app/models/Customer.php';

class AdminController extends BaseController {
    
    /**
     * Admin Dashboard - Main overview
     */
    public function index() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'user' => getCurrentUser()
        ]);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats() {
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        $stats = [
            'today_sales' => 0,
            'today_orders' => 0,
            'month_sales' => 0,
            'month_orders' => 0,
            'total_customers' => 0,
            'total_products' => 0,
            'low_stock_count' => 0,
            'active_sessions' => 0,
            'recent_orders' => [],
            'top_products' => [],
            'sales_chart_data' => []
        ];
        
        try {
            // Today's sales
            $stmt = $db->query("
                SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total
                FROM pos_orders
                WHERE DATE(created_at) = CURDATE()
                AND order_status = 'completed'
            ");
            $result = $stmt->fetch();
            $stats['today_orders'] = $result['count'] ?? 0;
            $stats['today_sales'] = $result['total'] ?? 0;
            
            // This month's sales
            $stmt = $db->query("
                SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total
                FROM pos_orders
                WHERE MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())
                AND order_status = 'completed'
            ");
            $result = $stmt->fetch();
            $stats['month_orders'] = $result['count'] ?? 0;
            $stats['month_sales'] = $result['total'] ?? 0;
            
            // Total customers (from WooCommerce users)
            $stmt = $db->query("
                SELECT COUNT(*) as count
                FROM {$prefix}users u
                INNER JOIN {$prefix}usermeta um ON u.ID = um.user_id
                WHERE um.meta_key = '{$prefix}capabilities'
                AND um.meta_value LIKE '%customer%'
            ");
            $result = $stmt->fetch();
            $stats['total_customers'] = $result['count'] ?? 0;
            
            // Total products
            $productModel = new Product();
            $stats['total_products'] = $productModel->getTotalProducts();
            
            // Low stock products
            $lowStockProducts = $productModel->getLowStockProducts(10);
            $stats['low_stock_count'] = count($lowStockProducts);
            
            // Active cashier sessions
            $stmt = $db->query("
                SELECT COUNT(*) as count
                FROM pos_sessions
                WHERE status = 'open'
            ");
            $result = $stmt->fetch();
            $stats['active_sessions'] = $result['count'] ?? 0;
            
            // Recent orders (last 10)
            $stmt = $db->query("
                SELECT 
                    o.*,
                    u.display_name as cashier_name
                FROM pos_orders o
                LEFT JOIN {$prefix}users u ON o.user_id = u.ID
                ORDER BY o.created_at DESC
                LIMIT 10
            ");
            $stats['recent_orders'] = $stmt->fetchAll();
            
            // Top selling products (this month)
            $stmt = $db->query("
                SELECT 
                    oi.product_name,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.line_total) as revenue
                FROM pos_order_items oi
                INNER JOIN pos_orders o ON oi.order_id = o.id
                WHERE MONTH(o.created_at) = MONTH(CURDATE())
                AND YEAR(o.created_at) = YEAR(CURDATE())
                AND o.order_status = 'completed'
                GROUP BY oi.product_id, oi.product_name
                ORDER BY total_sold DESC
                LIMIT 5
            ");
            $stats['top_products'] = $stmt->fetchAll();
            
            // Sales chart data (last 7 days)
            $stmt = $db->query("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as orders,
                    COALESCE(SUM(total), 0) as sales
                FROM pos_orders
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND order_status = 'completed'
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ");
            $stats['sales_chart_data'] = $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error fetching dashboard stats: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Users Management
     */
    public function users() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        $userModel = new User();
        $users = $userModel->getAllUsers();
        
        $this->view('admin/users', [
            'title' => 'User Management',
            'users' => $users
        ]);
    }
    
    /**
     * Add/Edit User
     */
    public function saveUser() {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!isPost()) {
            redirect('/admin/users');
        }
        
        $this->validateCsrf();
        
        $userId = (int) getPost('user_id', 0);
        $username = sanitize(getPost('username'));
        $email = sanitize(getPost('email'));
        $displayName = sanitize(getPost('display_name'));
        $role = sanitize(getPost('role'));
        $password = getPost('password');
        
        // Validate
        if (empty($username) || empty($email) || empty($role)) {
            Session::setFlash('error', 'Username, email, and role are required');
            redirect('/admin/users');
        }
        
        $userModel = new User();
        
        try {
            if ($userId > 0) {
                // Update existing user
                $success = $userModel->updateUser($userId, [
                    'email' => $email,
                    'display_name' => $displayName,
                    'role' => $role
                ]);
                
                // Update password if provided
                if (!empty($password)) {
                    $userModel->updatePassword($userId, $password);
                }
                
                $message = 'User updated successfully';
            } else {
                // Create new user
                if (empty($password)) {
                    Session::setFlash('error', 'Password is required for new user');
                    redirect('/admin/users');
                }
                
                $userId = $userModel->createUser([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'display_name' => $displayName,
                    'role' => $role
                ]);
                
                $message = 'User created successfully';
            }
            
            Session::setFlash('success', $message);
        } catch (Exception $e) {
            Session::setFlash('error', 'Error saving user: ' . $e->getMessage());
        }
        
        redirect('/admin/users');
    }
    
    /**
     * Delete User
     */
    public function deleteUser($userId) {
        $this->requireAuth();
        $this->requirePermission('manage_users');
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $this->validateCsrf();
        
        // Prevent deleting yourself
        if ($userId == Session::get('user_id')) {
            $this->json(['success' => false, 'message' => 'Cannot delete your own account'], 400);
        }
        
        $userModel = new User();
        
        try {
            $userModel->deleteUser($userId);
            $this->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error deleting user'], 500);
        }
    }
    
    /**
     * System Settings
     */
    public function settings() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        // Get all settings
        $settings = $this->getSettings();
        
        $this->view('admin/settings', [
            'title' => 'System Settings',
            'settings' => $settings
        ]);
    }
    
    /**
     * Get all settings
     */
    private function getSettings() {
        $db = Database::getInstance();
        
        $stmt = $db->query("SELECT * FROM pos_settings");
        $rows = $stmt->fetchAll();
        
        $settings = [];
        foreach ($rows as $row) {
            $value = $row['setting_value'];
            
            // Convert based on type
            if ($row['setting_type'] === 'number') {
                $value = (float) $value;
            } elseif ($row['setting_type'] === 'boolean') {
                $value = ($value === 'true' || $value === '1');
            } elseif ($row['setting_type'] === 'json') {
                $value = json_decode($value, true);
            }
            
            $settings[$row['setting_key']] = [
                'value' => $value,
                'type' => $row['setting_type'],
                'description' => $row['description']
            ];
        }
        
        return $settings;
    }
    
    /**
     * Save Settings
     */
    public function saveSettings() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        if (!isPost()) {
            redirect('/admin/settings');
        }
        
        $this->validateCsrf();
        
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            $settingsToUpdate = [
                'tax_rate' => getPost('tax_rate'),
                'low_stock_threshold' => getPost('low_stock_threshold'),
                'receipt_print_auto' => getPost('receipt_print_auto', 'false'),
                'session_timeout' => getPost('session_timeout'),
                'offline_mode_enabled' => getPost('offline_mode_enabled', 'false'),
                'loyalty_points_per_rupee' => getPost('loyalty_points_per_rupee')
            ];
            
            foreach ($settingsToUpdate as $key => $value) {
                if ($value !== null) {
                    $stmt = $db->query(
                        "UPDATE pos_settings SET setting_value = ? WHERE setting_key = ?",
                        [$value, $key]
                    );
                }
            }
            
            $db->commit();
            Session::setFlash('success', 'Settings saved successfully');
        } catch (Exception $e) {
            $db->rollback();
            Session::setFlash('error', 'Error saving settings: ' . $e->getMessage());
        }
        
        redirect('/admin/settings');
    }
    
    /**
     * Reports Dashboard
     */
    public function reports() {
        $this->requireAuth();
        $this->requirePermission('view_reports');
        
        $this->view('admin/reports', [
            'title' => 'Sales Reports'
        ]);
    }
    
    /**
     * Product Management
     */
    public function products() {
        $this->requireAuth();
        $this->requirePermission('manage_products');
        
        $productModel = new Product();
        $products = $productModel->getAllProducts(50, 0);
        
        $this->view('admin/products', [
            'title' => 'Product Management',
            'products' => $products
        ]);
    }
    
    /**
     * Customer Management
     */
    public function customers() {
        $this->requireAuth();
        $this->requirePermission('manage_customers');
        
        $customerModel = new Customer();
        $customers = $customerModel->getAllCustomers(50, 0);
        
        $this->view('admin/customers', [
            'title' => 'Customer Management',
            'customers' => $customers
        ]);
    }
    
    /**
     * Order Management
     */
    public function orders() {
        $this->requireAuth();
        $this->requirePermission('view_orders');
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        // Get recent orders
        $stmt = $db->query("
            SELECT 
                o.*,
                u.display_name as cashier_name,
                c.display_name as customer_name
            FROM pos_orders o
            LEFT JOIN {$prefix}users u ON o.user_id = u.ID
            LEFT JOIN {$prefix}users c ON o.customer_id = c.ID
            ORDER BY o.created_at DESC
            LIMIT 100
        ");
        $orders = $stmt->fetchAll();
        
        // Fetch order items for each order
        foreach ($orders as &$order) {
            $stmt = $db->query("
                SELECT 
                    oi.*,
                    p.post_title as product_name,
                    pm.meta_value as regular_price
                FROM pos_order_items oi
                LEFT JOIN {$prefix}posts p ON oi.product_id = p.ID
                LEFT JOIN {$prefix}postmeta pm ON oi.product_id = pm.post_id AND pm.meta_key = '_regular_price'
                WHERE oi.order_id = ?
                ORDER BY oi.id ASC
            ", [$order['id']]);
            $order['items'] = $stmt->fetchAll();
        }
        
        // Get store settings for receipt
        $storeSettings = $this->getReceiptSettings();
        
        $this->view('admin/orders', [
            'title' => 'Order Management',
            'orders' => $orders,
            'storeSettings' => $storeSettings
        ]);
    }
    
    /**
     * Get receipt settings from pos_settings table
     */
    private function getReceiptSettings() {
        $db = Database::getInstance();
        
        $stmt = $db->query("
            SELECT setting_key, setting_value 
            FROM pos_settings 
            WHERE setting_key IN ('store_name', 'store_address', 'store_phone', 'store_email', 'store_gstin', 'receipt_footer', 'receipt_terms')
        ");
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Set defaults
        return [
            'store_name' => $settings['store_name'] ?? 'B-Plus POS',
            'store_address' => $settings['store_address'] ?? '',
            'store_phone' => $settings['store_phone'] ?? '',
            'store_email' => $settings['store_email'] ?? '',
            'store_gstin' => $settings['store_gstin'] ?? '',
            'receipt_footer' => $settings['receipt_footer'] ?? 'Thank you for your business!',
            'receipt_terms' => $settings['receipt_terms'] ?? ''
        ];
    }
    
    /**
     * Send Receipt Email
     */
    public function sendReceiptEmail() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = (int) ($data['order_id'] ?? 0);
        $email = sanitize($data['email'] ?? '');
        
        if (!$orderId || !$email) {
            $this->json(['success' => false, 'message' => 'Missing order ID or email'], 400);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Invalid email address'], 400);
        }
        
        try {
            $db = Database::getInstance();
            $prefix = $db->getPrefix();
            
            // Get order details
            $stmt = $db->query("
                SELECT o.*, u.display_name as cashier_name, c.display_name as customer_name
                FROM pos_orders o
                LEFT JOIN {$prefix}users u ON o.user_id = u.ID
                LEFT JOIN {$prefix}users c ON o.customer_id = c.ID
                WHERE o.id = ?
            ", [$orderId]);
            $order = $stmt->fetch();
            
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Order not found'], 404);
            }
            
            // Get order items
            $stmt = $db->query("
                SELECT oi.* FROM pos_order_items oi
                WHERE oi.order_id = ?
            ", [$orderId]);
            $items = $stmt->fetchAll();
            $order['items'] = $items;
            
            // Get store settings
            $stmt = $db->query("
                SELECT setting_key, setting_value FROM pos_settings 
                WHERE setting_key IN ('store_name', 'store_address', 'store_phone', 'store_email', 'store_gstin')
            ");
            $storeSettings = [];
            foreach ($stmt->fetchAll() as $row) {
                $storeSettings[$row['setting_key']] = $row['setting_value'];
            }
            
            // Prepare receipt HTML for email
            $receiptHtml = $this->generateReceiptHtmlForEmail($order, $items, $storeSettings);
            
            // Send email
            $subject = "Receipt #" . $order['order_number'] . " - B-Plus POS";
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "From: " . ($storeSettings['store_email'] ?? 'noreply@bpluspro.com') . "\r\n";
            
            $mailResult = mail($email, $subject, $receiptHtml, $headers);
            
            if ($mailResult) {
                $this->json(['success' => true, 'message' => 'Email sent successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to send email'], 500);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Generate receipt HTML for email
     */
    private function generateReceiptHtmlForEmail($order, $items, $storeSettings) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
        $html .= 'body{font-family:Arial,sans-serif;background:#f5f5f5}';
        $html .= '.container{max-width:600px;margin:0 auto;background:#fff;padding:20px;border-radius:8px}';
        $html .= '.header{text-align:center;border-bottom:2px solid #667eea;padding-bottom:15px;margin-bottom:15px}';
        $html .= '.header h1{margin:0;color:#333;font-size:24px}';
        $html .= '.receipt-info{display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:20px;font-size:13px}';
        $html .= '.table{width:100%;border-collapse:collapse;margin-bottom:20px}';
        $html .= '.table th{background:#f0f0f0;padding:10px;text-align:left;font-weight:600;border-bottom:2px solid #667eea}';
        $html .= '.table td{padding:10px;border-bottom:1px solid #eee}';
        $html .= '.summary{background:#f9f9f9;padding:15px;border-radius:4px;margin-bottom:15px}';
        $html .= '.summary-row{display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px}';
        $html .= '.total-row{font-size:18px;font-weight:bold;border-top:2px solid #667eea;padding-top:10px;margin-top:10px;color:#667eea}';
        $html .= '.footer{text-align:center;margin-top:20px;padding-top:15px;border-top:1px solid #ddd;font-size:12px;color:#999}';
        $html .= '</style></head><body>';
        
        $html .= '<div class="container">';
        $html .= '<div class="header"><h1>' . htmlspecialchars($storeSettings['store_name'] ?? 'B-Plus POS') . '</h1></div>';
        
        $html .= '<div class="receipt-info">';
        $html .= '<div><strong>Receipt #:</strong><br>' . htmlspecialchars($order['order_number']) . '</div>';
        $html .= '<div><strong>Date:</strong><br>' . date('d-M-Y h:i A', strtotime($order['created_at'])) . '</div>';
        if ($order['customer_name']) $html .= '<div><strong>Customer:</strong><br>' . htmlspecialchars($order['customer_name']) . '</div>';
        $html .= '<div><strong>Cashier:</strong><br>' . htmlspecialchars($order['cashier_name'] ?? 'N/A') . '</div>';
        $html .= '</div>';
        
        $html .= '<table class="table"><thead><tr><th>Item</th><th style="text-align:right">Qty</th><th style="text-align:right">Price</th><th style="text-align:right">Total</th></tr></thead><tbody>';
        foreach ($items as $item) {
            $qty = $item['quantity'];
            $price = $item['price'];
            $total = $item['total'];
            $tax = $item['tax_amount'];
            $html .= '<tr><td>' . htmlspecialchars($item['product_name'] ?? 'Product') . '</td>';
            $html .= '<td style="text-align:right">' . $qty . '</td>';
            $html .= '<td style="text-align:right">₹' . number_format($price, 2) . '</td>';
            $html .= '<td style="text-align:right">₹' . number_format($total + $tax, 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';
        
        $html .= '<div class="summary">';
        $html .= '<div class="summary-row"><span>Subtotal:</span><span>₹' . number_format($order['subtotal'], 2) . '</span></div>';
        if ($order['discount_amount'] > 0) {
            $html .= '<div class="summary-row"><span>Discount:</span><span>-₹' . number_format($order['discount_amount'], 2) . '</span></div>';
        }
        $html .= '<div class="summary-row"><span>Tax:</span><span>₹' . number_format($order['tax_amount'], 2) . '</span></div>';
        $html .= '<div class="summary-row total-row"><span>TOTAL:</span><span>₹' . number_format($order['total'], 2) . '</span></div>';
        $html .= '</div>';
        
        $html .= '<div class="summary">';
        $html .= '<strong>Payment Method:</strong> ' . strtoupper(htmlspecialchars($order['payment_method'] ?? 'CASH'));
        $html .= '</div>';
        
        $html .= '<div class="footer">';
        $html .= '<p>Thank you for your business!</p>';
        $html .= '<p>Powered by B-Plus POS</p>';
        $html .= '</div>';
        
        $html .= '</div></body></html>';
        return $html;
    }
    
    /**
     * Process Return/Exchange
     */
    public function processReturn() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        try {
            $returnNumber = 'RET-' . date('YmdHis') . '-' . rand(1000, 9999);
            
            // Get order items to update stock
            $stmt = $db->query("
                SELECT product_id, quantity FROM pos_order_items WHERE order_id = ?
            ", [$data['order_id']]);
            $orderItems = $stmt->fetchAll();
            
            // For RETURN: Increase stock of returned products
            if ($data['return_type'] === 'return') {
                foreach ($orderItems as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    
                    // Get current stock
                    $stmt = $db->query("
                        SELECT meta_value FROM {$prefix}postmeta 
                        WHERE post_id = ? AND meta_key = '_stock'
                    ", [$productId]);
                    $stockRow = $stmt->fetch();
                    $currentStock = $stockRow ? (int)$stockRow['meta_value'] : 0;
                    $newStock = $currentStock + $quantity;
                    
                    // Update stock in postmeta
                    $stmt = $db->query("
                        SELECT meta_id FROM {$prefix}postmeta 
                        WHERE post_id = ? AND meta_key = '_stock'
                    ", [$productId]);
                    $metaRow = $stmt->fetch();
                    
                    if ($metaRow) {
                        $db->query("
                            UPDATE {$prefix}postmeta SET meta_value = ? 
                            WHERE post_id = ? AND meta_key = '_stock'
                        ", [$newStock, $productId]);
                    } else {
                        $db->query("
                            INSERT INTO {$prefix}postmeta (post_id, meta_key, meta_value) 
                            VALUES (?, '_stock', ?)
                        ", [$productId, $newStock]);
                    }
                }
            }
            // For EXCHANGE: Increase stock of returned products + Decrease stock of new product
            else if ($data['return_type'] === 'exchange') {
                // Increase stock for returned products
                foreach ($orderItems as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    
                    $stmt = $db->query("
                        SELECT meta_value FROM {$prefix}postmeta 
                        WHERE post_id = ? AND meta_key = '_stock'
                    ", [$productId]);
                    $stockRow = $stmt->fetch();
                    $currentStock = $stockRow ? (int)$stockRow['meta_value'] : 0;
                    $newStock = $currentStock + $quantity;
                    
                    $stmt = $db->query("
                        SELECT meta_id FROM {$prefix}postmeta 
                        WHERE post_id = ? AND meta_key = '_stock'
                    ", [$productId]);
                    $metaRow = $stmt->fetch();
                    
                    if ($metaRow) {
                        $db->query("
                            UPDATE {$prefix}postmeta SET meta_value = ? 
                            WHERE post_id = ? AND meta_key = '_stock'
                        ", [$newStock, $productId]);
                    } else {
                        $db->query("
                            INSERT INTO {$prefix}postmeta (post_id, meta_key, meta_value) 
                            VALUES (?, '_stock', ?)
                        ", [$productId, $newStock]);
                    }
                }
                
                // Decrease stock for new replacement product (if provided)
                if (!empty($data['replacement_product_id'])) {
                    $replacementId = (int)$data['replacement_product_id'];
                    
                    $stmt = $db->query("
                        SELECT meta_value FROM {$prefix}postmeta 
                        WHERE post_id = ? AND meta_key = '_stock'
                    ", [$replacementId]);
                    $stockRow = $stmt->fetch();
                    $currentStock = $stockRow ? (int)$stockRow['meta_value'] : 0;
                    $newStock = $currentStock - 1; // Assuming 1 replacement item
                    
                    $stmt = $db->query("
                        SELECT meta_id FROM {$prefix}postmeta 
                        WHERE post_id = ? AND meta_key = '_stock'
                    ", [$replacementId]);
                    $metaRow = $stmt->fetch();
                    
                    if ($metaRow) {
                        $db->query("
                            UPDATE {$prefix}postmeta SET meta_value = ? 
                            WHERE post_id = ? AND meta_key = '_stock'
                        ", [$newStock, $replacementId]);
                    } else {
                        $db->query("
                            INSERT INTO {$prefix}postmeta (post_id, meta_key, meta_value) 
                            VALUES (?, '_stock', ?)
                        ", [$replacementId, $newStock]);
                    }
                }
            }
            
            // Insert return record
            $stmt = $db->query("
                INSERT INTO pos_returns (
                    order_id, return_type, return_reason, refund_amount, 
                    refund_method, return_number, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'completed', NOW())
            ", [
                $data['order_id'],
                $data['return_type'],
                $data['return_reason'],
                $data['refund_amount'],
                $data['refund_method'],
                $returnNumber
            ]);
            
            $returnId = $db->lastInsertId();
            
            // Get order details to find customer
            $stmt = $db->query("SELECT customer_id FROM pos_orders WHERE id = ?", [$data['order_id']]);
            $order = $stmt->fetch();
            
            // If refund method is store_credit, create store credit entry
            if ($data['refund_method'] === 'store_credit' && $order && $order['customer_id'] > 0) {
                $creditNumber = 'SC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                // Create store credit
                $db->query("
                    INSERT INTO pos_store_credit 
                    (customer_id, credit_number, amount, balance, source_type, source_id, issued_by, status, created_at)
                    VALUES (?, ?, ?, ?, 'refund', ?, ?, 'active', NOW())
                ", [
                    $order['customer_id'],
                    $creditNumber,
                    $data['refund_amount'],
                    $data['refund_amount'],
                    $data['order_id'],
                    Session::get('user_id') ?: 0
                ]);
                
                $creditId = $db->lastInsertId();
                
                // Log store credit transaction
                $db->query("
                    INSERT INTO pos_store_credit_transactions 
                    (store_credit_id, order_id, transaction_type, amount, balance_after, description, processed_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ", [
                    $creditId,
                    $data['order_id'],
                    'issue',
                    $data['refund_amount'],
                    $data['refund_amount'],
                    'Store credit issued from return/refund - ' . $data['return_reason'],
                    Session::get('user_id') ?: 0
                ]);
            }
            
            $this->json(['success' => true, 'message' => 'Return processed successfully', 'return_number' => $returnNumber, 'return_id' => $returnId]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Approve Return
     */
    public function approveReturn() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $returnId = (int) ($data['return_id'] ?? 0);
        
        if (!$returnId) {
            $this->json(['success' => false, 'message' => 'Return ID required'], 400);
        }
        
        try {
            $db = Database::getInstance();
            $db->query("UPDATE pos_returns SET status = 'approved' WHERE id = ?", [$returnId]);
            $this->json(['success' => true, 'message' => 'Return approved']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Reject Return
     */
    public function rejectReturn() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $returnId = (int) ($data['return_id'] ?? 0);
        $reason = sanitize($data['reason'] ?? '');
        
        if (!$returnId) {
            $this->json(['success' => false, 'message' => 'Return ID required'], 400);
        }
        
        try {
            $db = Database::getInstance();
            $db->query("UPDATE pos_returns SET status = 'rejected', notes = ? WHERE id = ?", [$reason, $returnId]);
            $this->json(['success' => true, 'message' => 'Return rejected']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Process Refund
     */
    public function processRefund() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $returnId = (int) ($data['return_id'] ?? 0);
        
        if (!$returnId) {
            $this->json(['success' => false, 'message' => 'Return ID required'], 400);
        }
        
        try {
            $db = Database::getInstance();
            
            // Get return details
            $stmt = $db->query("SELECT * FROM pos_returns WHERE id = ?", [$returnId]);
            $return = $stmt->fetch();
            
            if (!$return) {
                $this->json(['success' => false, 'message' => 'Return not found'], 400);
                return;
            }
            
            // Get order details to find customer
            $stmt = $db->query("SELECT customer_id FROM pos_orders WHERE id = ?", [$return['order_id']]);
            $order = $stmt->fetch();
            
            // If refund method is store_credit, create store credit entry
            if ($return['refund_method'] === 'store_credit' && $order && $order['customer_id'] > 0) {
                $creditNumber = 'SC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                // Create store credit
                $db->query("
                    INSERT INTO pos_store_credit 
                    (customer_id, credit_number, amount, balance, source_type, source_id, issued_by, status, created_at)
                    VALUES (?, ?, ?, ?, 'refund', ?, ?, 'active', NOW())
                ", [
                    $order['customer_id'],
                    $creditNumber,
                    $return['refund_amount'],
                    $return['refund_amount'],
                    $return['order_id'],
                    Session::get('user_id')
                ]);
                
                $creditId = $db->lastInsertId();
                
                // Log store credit transaction
                $db->query("
                    INSERT INTO pos_store_credit_transactions 
                    (store_credit_id, order_id, transaction_type, amount, balance_after, description, processed_by, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ", [
                    $creditId,
                    $return['order_id'],
                    'issue',
                    $return['refund_amount'],
                    $return['refund_amount'],
                    'Store credit issued from return/refund',
                    Session::get('user_id')
                ]);
            }
            
            $db->query("UPDATE pos_returns SET status = 'completed' WHERE id = ?", [$returnId]);
            $this->json(['success' => true, 'message' => 'Refund processed successfully']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get Return Receipt for Display/Print
     */
    public function getReturnReceipt() {
        $this->requireAuth();
        
        $returnId = (int) getQuery('id', 0);
        if (!$returnId) {
            $this->json(['success' => false, 'message' => 'Return ID required'], 400);
        }
        
        try {
            $db = Database::getInstance();
            $prefix = $db->getPrefix();
            
            $stmt = $db->query("
                SELECT r.*, o.order_number as original_order_number, o.total as order_total,
                       c.display_name as customer_name
                FROM pos_returns r
                LEFT JOIN pos_orders o ON r.order_id = o.id
                LEFT JOIN {$prefix}users c ON o.customer_id = c.ID
                WHERE r.id = ?
            ", [$returnId]);
            $return = $stmt->fetch();
            
            if (!$return) {
                $this->json(['success' => false, 'message' => 'Return not found'], 404);
            }
            
            // Get store settings
            $stmt = $db->query("
                SELECT setting_key, setting_value FROM pos_settings 
                WHERE setting_key IN ('store_name', 'store_address', 'store_phone', 'store_email')
            ");
            $settings = [];
            foreach ($stmt->fetchAll() as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            $return = array_merge($return, $settings);
            
            $this->json(['success' => true, 'receipt' => $return]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get Return Details
     */
    public function getReturnDetails() {
        $this->requireAuth();
        
        $returnId = (int) getQuery('id', 0);
        if (!$returnId) {
            $this->json(['success' => false, 'message' => 'Return ID required'], 400);
        }
        
        try {
            $db = Database::getInstance();
            $prefix = $db->getPrefix();
            
            $stmt = $db->query("
                SELECT r.*, o.order_number as original_order_number, c.display_name as customer_name
                FROM pos_returns r
                LEFT JOIN pos_orders o ON r.order_id = o.id
                LEFT JOIN {$prefix}users c ON o.customer_id = c.ID
                WHERE r.id = ?
            ", [$returnId]);
            $return = $stmt->fetch();
            
            if ($return) {
                $this->json(['success' => true, 'return' => $return]);
            } else {
                $this->json(['success' => false, 'message' => 'Return not found'], 404);
            }
            $return = $stmt->fetch();
            
            if ($return) {
                $this->json(['success' => true, 'return' => $return]);
            } else {
                $this->json(['success' => false, 'message' => 'Return not found'], 404);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Returns Management
     */
    public function returns() {
        $this->requireAuth();
        $this->requirePermission('view_orders');
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        // Get all returns
        $stmt = $db->query("
            SELECT 
                r.*,
                o.order_number as original_order_number,
                c.display_name as customer_name
            FROM pos_returns r
            LEFT JOIN pos_orders o ON r.order_id = o.id
            LEFT JOIN {$prefix}users c ON r.customer_id = c.ID
            ORDER BY r.created_at DESC
            LIMIT 100
        ");
        $returns = $stmt->fetchAll();
        
        // Get statistics
        $stats = [
            'total_returns' => 0,
            'pending_returns' => 0,
            'approved_returns' => 0,
            'completed_returns' => 0,
            'rejected_returns' => 0,
            'total_refund_amount' => 0,
            'refund_amount_30d' => 0
        ];
        
        try {
            // Total returns
            $stmt = $db->query("SELECT COUNT(*) as count FROM pos_returns");
            $result = $stmt->fetch();
            $stats['total_returns'] = $result['count'] ?? 0;
            
            // By status
            $stmt = $db->query("
                SELECT status, COUNT(*) as count, SUM(refund_amount) as total_amount
                FROM pos_returns
                GROUP BY status
            ");
            while ($row = $stmt->fetch()) {
                $stats[$row['status'] . '_returns'] = $row['count'];
                if ($row['status'] === 'completed') {
                    $stats['total_refund_amount'] = $row['total_amount'] ?? 0;
                }
            }
            
            // 30-day refunds
            $stmt = $db->query("
                SELECT COALESCE(SUM(refund_amount), 0) as amount
                FROM pos_returns
                WHERE status = 'completed'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $result = $stmt->fetch();
            $stats['refund_amount_30d'] = $result['amount'] ?? 0;
            
        } catch (Exception $e) {
            // Silently handle if table doesn't exist
        }
        
        $this->view('admin/returns', [
            'title' => 'Returns Management',
            'returns' => $returns,
            'stats' => $stats
        ]);
    }
    
    /**
     * Cashier Sessions
     */
    public function sessions() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        // Get all sessions
        $stmt = $db->query("
            SELECT 
                s.*,
                u.display_name as cashier_name
            FROM pos_sessions s
            LEFT JOIN {$prefix}users u ON s.user_id = u.ID
            ORDER BY s.session_start DESC
            LIMIT 50
        ");
        $sessions = $stmt->fetchAll();
        
        $this->view('admin/sessions', [
            'title' => 'Cashier Sessions',
            'sessions' => $sessions
        ]);
    }
    
    /**
     * Inventory Management
     */
    public function inventory() {
        $this->requireAuth();
        $this->requirePermission('manage_products');
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        // Get products with stock information
        $stmt = $db->query("
            SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                p.post_status,
                pm1.meta_value as sku,
                pm2.meta_value as stock_quantity,
                pm3.meta_value as stock_status,
                pm4.meta_value as regular_price,
                pm5.meta_value as manage_stock,
                pm6.meta_value as low_stock_amount
            FROM {$prefix}posts p
            LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_sku'
            LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_stock'
            LEFT JOIN {$prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_stock_status'
            LEFT JOIN {$prefix}postmeta pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_regular_price'
            LEFT JOIN {$prefix}postmeta pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_manage_stock'
            LEFT JOIN {$prefix}postmeta pm6 ON p.ID = pm6.post_id AND pm6.meta_key = '_low_stock_amount'
            WHERE p.post_type = 'product'
            AND p.post_status IN ('publish', 'draft')
            ORDER BY p.post_title ASC
            LIMIT 200
        ");
        $products = $stmt->fetchAll();
        
        // Get inventory statistics
        $stats = [
            'total_products' => 0,
            'in_stock' => 0,
            'out_of_stock' => 0,
            'low_stock' => 0,
            'total_stock_value' => 0
        ];
        
        try {
            // Total products
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM {$prefix}posts 
                WHERE post_type = 'product' 
                AND post_status = 'publish'
            ");
            $result = $stmt->fetch();
            $stats['total_products'] = $result['count'] ?? 0;
            
            // Stock status counts
            $stmt = $db->query("
                SELECT 
                    pm.meta_value as stock_status,
                    COUNT(*) as count
                FROM {$prefix}posts p
                INNER JOIN {$prefix}postmeta pm ON p.ID = pm.post_id
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND pm.meta_key = '_stock_status'
                GROUP BY pm.meta_value
            ");
            while ($row = $stmt->fetch()) {
                if ($row['stock_status'] === 'instock') {
                    $stats['in_stock'] = $row['count'];
                } elseif ($row['stock_status'] === 'outofstock') {
                    $stats['out_of_stock'] = $row['count'];
                }
            }
            
            // Low stock count
            $stmt = $db->query("
                SELECT COUNT(DISTINCT p.ID) as count
                FROM {$prefix}posts p
                INNER JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_stock'
                INNER JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_low_stock_amount'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND CAST(pm1.meta_value AS DECIMAL) <= CAST(pm2.meta_value AS DECIMAL)
                AND CAST(pm1.meta_value AS DECIMAL) > 0
            ");
            $result = $stmt->fetch();
            $stats['low_stock'] = $result['count'] ?? 0;
            
        } catch (Exception $e) {
            // Silently handle errors
        }
        
        $this->view('admin/inventory', [
            'title' => 'Inventory Management',
            'products' => $products,
            'stats' => $stats
        ]);
    }
    
    /**
     * Barcode Management
     */
    public function barcodes() {
        $this->requireAuth();
        $this->requirePermission('manage_products');
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        // Get products for barcode generation
        $stmt = $db->query("
            SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                pm1.meta_value as sku,
                pm2.meta_value as regular_price,
                pm3.meta_value as stock_quantity
            FROM {$prefix}posts p
            LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_sku'
            LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
            LEFT JOIN {$prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_stock'
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            ORDER BY p.post_title ASC
            LIMIT 100
        ");
        $products = $stmt->fetchAll();
        
        $this->view('admin/barcodes', [
            'title' => 'Barcode Management',
            'products' => $products
        ]);
    }
    
    /**
     * Inventory Alerts
     */
    public function inventoryAlerts() {
        $this->requireAuth();
        $this->requirePermission('manage_products');
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        // Get low stock products
        $stmt = $db->query("
            SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                pm1.meta_value as sku,
                pm2.meta_value as stock_quantity,
                pm3.meta_value as low_stock_amount,
                pm4.meta_value as stock_status,
                pm5.meta_value as regular_price
            FROM {$prefix}posts p
            LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_sku'
            LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_stock'
            LEFT JOIN {$prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_low_stock_amount'
            LEFT JOIN {$prefix}postmeta pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_stock_status'
            LEFT JOIN {$prefix}postmeta pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_regular_price'
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND pm4.meta_value != 'outofstock'
            HAVING (CAST(stock_quantity AS DECIMAL) <= CAST(low_stock_amount AS DECIMAL) 
                    OR stock_quantity IS NULL 
                    OR stock_quantity = '' 
                    OR CAST(stock_quantity AS DECIMAL) <= 5)
            ORDER BY CAST(stock_quantity AS DECIMAL) ASC
            LIMIT 100
        ");
        $lowStockProducts = $stmt->fetchAll();
        
        // Get out of stock products
        $stmt = $db->query("
            SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                pm1.meta_value as sku,
                pm2.meta_value as regular_price
            FROM {$prefix}posts p
            LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_sku'
            LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
            INNER JOIN {$prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_stock_status'
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND pm3.meta_value = 'outofstock'
            ORDER BY p.post_title ASC
            LIMIT 100
        ");
        $outOfStockProducts = $stmt->fetchAll();
        
        $this->view('admin/inventory-alerts', [
            'title' => 'Inventory Alerts',
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts
        ]);
    }
    
    /**
     * Loyalty Programs
     */
    public function loyalty() {
        $this->requireAuth();
        $this->requirePermission('manage_customers');
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        // Ensure loyalty tables exist
        $this->ensureLoyaltyTables();
        
        // Get loyalty statistics
        $stats = [];
        
        try {
            // Total loyalty members
            $stmt = $db->query("SELECT COUNT(*) as count FROM pos_loyalty_points");
            $result = $stmt->fetch();
            $stats['total_members'] = $result['count'] ?? 0;
            
            // Total points issued
            $stmt = $db->query("SELECT COALESCE(SUM(total_earned), 0) as total FROM pos_loyalty_points");
            $result = $stmt->fetch();
            $stats['total_points_issued'] = $result['total'] ?? 0;
            
            // Total points redeemed
            $stmt = $db->query("SELECT COALESCE(SUM(total_redeemed), 0) as total FROM pos_loyalty_points");
            $result = $stmt->fetch();
            $stats['total_points_redeemed'] = $result['total'] ?? 0;
            
            // Active points
            $stmt = $db->query("SELECT COALESCE(SUM(points), 0) as total FROM pos_loyalty_points");
            $result = $stmt->fetch();
            $stats['active_points'] = $result['total'] ?? 0;
            
            // Tier breakdown
            $stmt = $db->query("
                SELECT tier, COUNT(*) as count 
                FROM pos_loyalty_points 
                GROUP BY tier
            ");
            $tiers = $stmt->fetchAll();
            $stats['tier_breakdown'] = [];
            foreach ($tiers as $tier) {
                $stats['tier_breakdown'][$tier['tier']] = $tier['count'];
            }
            
        } catch (Exception $e) {
            error_log("Error fetching loyalty stats: " . $e->getMessage());
        }
        
        // Get top loyalty customers
        $stmt = $db->query("
            SELECT 
                lp.*,
                u.display_name as customer_name,
                u.user_email as customer_email
            FROM pos_loyalty_points lp
            LEFT JOIN {$prefix}users u ON lp.customer_id = u.ID
            ORDER BY lp.points DESC
            LIMIT 50
        ");
        $topCustomers = $stmt->fetchAll();
        
        // Get recent transactions
        $stmt = $db->query("
            SELECT 
                lt.*,
                u.display_name as customer_name
            FROM pos_loyalty_transactions lt
            LEFT JOIN {$prefix}users u ON lt.customer_id = u.ID
            ORDER BY lt.created_at DESC
            LIMIT 100
        ");
        $recentTransactions = $stmt->fetchAll();
        
        $this->view('admin/loyalty', [
            'title' => 'Loyalty Programs',
            'stats' => $stats,
            'topCustomers' => $topCustomers,
            'recentTransactions' => $recentTransactions
        ]);
    }
    
    /**
     * Ensure loyalty tables exist
     */
    private function ensureLoyaltyTables() {
        $db = Database::getInstance();
        
        try {
            // Create loyalty points table
            $db->query("
                CREATE TABLE IF NOT EXISTS pos_loyalty_points (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id BIGINT NOT NULL,
                    points INT DEFAULT 0,
                    total_earned INT DEFAULT 0,
                    total_redeemed INT DEFAULT 0,
                    tier VARCHAR(20) DEFAULT 'bronze',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_customer (customer_id),
                    INDEX idx_customer (customer_id),
                    INDEX idx_tier (tier)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            // Create loyalty transactions table
            $db->query("
                CREATE TABLE IF NOT EXISTS pos_loyalty_transactions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id BIGINT NOT NULL,
                    transaction_type ENUM('earned', 'redeemed', 'expired', 'adjusted') NOT NULL,
                    points INT NOT NULL,
                    order_id INT NULL,
                    description VARCHAR(255),
                    created_by BIGINT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_customer (customer_id),
                    INDEX idx_type (transaction_type),
                    INDEX idx_order (order_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
        } catch (Exception $e) {
            error_log("Error creating loyalty tables: " . $e->getMessage());
        }
    }
    
    /**
     * Stores Management
     */
    public function stores() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $db = Database::getInstance();
        
        $stmt = $db->query("SELECT * FROM pos_stores ORDER BY id ASC");
        $stores = $stmt->fetchAll();
        
        $this->view('admin/stores', [
            'title' => 'Store Management',
            'stores' => $stores
        ]);
    }
    
    /**
     * System Options & Features List
     */
    public function options() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        // Define all system features with their access levels
        $features = [
            [
                'id' => 1,
                'category' => 'Core Operations',
                'name' => 'POS Terminal',
                'url' => '/pos',
                'description' => 'Point-of-sale interface with cart, payments, and receipts',
                'admin' => true,
                'manager' => true,
                'cashier' => true,
                'stock_manager' => true,
                'icon' => 'fa-cash-register'
            ],
            [
                'id' => 2,
                'category' => 'Core Operations',
                'name' => 'Dashboard',
                'url' => '/dashboard',
                'description' => 'Sales overview and performance metrics',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-chart-line'
            ],
            [
                'id' => 3,
                'category' => 'Core Operations',
                'name' => 'Products',
                'url' => '/products',
                'description' => 'Product catalog with stock and pricing',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => true,
                'icon' => 'fa-box'
            ],
            [
                'id' => 4,
                'category' => 'Core Operations',
                'name' => 'Customers',
                'url' => '/customers',
                'description' => 'Customer database and purchase history',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-users'
            ],
            [
                'id' => 5,
                'category' => 'Order Management',
                'name' => 'Orders & Transactions',
                'url' => '/admin/orders',
                'description' => 'Complete order history and management',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-shopping-bag'
            ],
            [
                'id' => 6,
                'category' => 'Order Management',
                'name' => 'Returns Management',
                'url' => '/admin/returns',
                'description' => 'Process returns and refunds',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-undo'
            ],
            [
                'id' => 7,
                'category' => 'Inventory',
                'name' => 'Inventory Management',
                'url' => '/admin/inventory',
                'description' => 'Stock levels and inventory tracking',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => true,
                'icon' => 'fa-warehouse'
            ],
            [
                'id' => 8,
                'category' => 'Inventory',
                'name' => 'Barcode Management',
                'url' => '/admin/barcodes',
                'description' => 'Generate and print product barcodes',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-barcode'
            ],
            [
                'id' => 9,
                'category' => 'Inventory',
                'name' => 'Inventory Alerts',
                'url' => '/admin/inventory-alerts',
                'description' => 'Low stock and reorder notifications',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => true,
                'icon' => 'fa-bell'
            ],
            [
                'id' => 10,
                'category' => 'Analytics & Reports',
                'name' => 'Sales Analytics',
                'url' => '/admin/sales-analytics',
                'description' => 'Detailed sales reports and trends',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-chart-bar'
            ],
            [
                'id' => 11,
                'category' => 'Analytics & Reports',
                'name' => 'Business Intelligence Dashboard',
                'url' => '/admin/bi-dashboard',
                'description' => 'AI-powered insights, forecasting, and customer analytics',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-brain'
            ],
            [
                'id' => 12,
                'category' => 'Analytics & Reports',
                'name' => 'GST Reports & E-Invoicing',
                'url' => '/admin/gst-reports',
                'description' => 'Tax computation and GST compliance',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-file-invoice'
            ],
            [
                'id' => 13,
                'category' => 'Tax & Compliance',
                'name' => 'Custom Tax Rules',
                'url' => '/admin/tax-rules',
                'description' => 'Configure conditional taxes based on categories and price ranges',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-percentage'
            ],
            [
                'id' => 14,
                'category' => 'Multi-Store',
                'name' => 'Multi-Store Management',
                'url' => '/admin/stores',
                'description' => 'Manage multiple store locations',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-store'
            ],
            [
                'id' => 15,
                'category' => 'Communications',
                'name' => 'WhatsApp Integration',
                'url' => '/admin/whatsapp',
                'description' => 'Send notifications via WhatsApp',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-whatsapp'
            ],
            [
                'id' => 16,
                'category' => 'Communications',
                'name' => 'Email Integration',
                'url' => '/admin/email-settings',
                'description' => 'Configure email templates and settings',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-envelope'
            ],
            [
                'id' => 17,
                'category' => 'Automation',
                'name' => 'Workflow Automation',
                'url' => '/admin/automation',
                'description' => 'Create automated business rules and triggers',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-robot'
            ],
            [
                'id' => 18,
                'category' => 'Promotions',
                'name' => 'Discounts & Coupons',
                'url' => '/admin/discounts',
                'description' => 'Manage discount rules and promotions',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-tags'
            ],
            [
                'id' => 19,
                'category' => 'Promotions',
                'name' => 'Loyalty Programs',
                'url' => '/admin/loyalty',
                'description' => 'Customer loyalty and rewards management',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-gift'
            ],
            [
                'id' => 20,
                'category' => 'Configuration',
                'name' => 'Payment Methods',
                'url' => '/admin/payments',
                'description' => 'Configure payment gateways and options',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-credit-card'
            ],
            [
                'id' => 21,
                'category' => 'Configuration',
                'name' => 'Receipt Customization',
                'url' => '/admin/receipt-settings',
                'description' => 'Customize receipt templates and branding',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-receipt'
            ],
            [
                'id' => 22,
                'category' => 'Configuration',
                'name' => 'User Management',
                'url' => '/admin/users',
                'description' => 'Manage users and role assignments',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-user-cog'
            ],
            [
                'id' => 23,
                'category' => 'Configuration',
                'name' => 'System Settings',
                'url' => '/admin/settings',
                'description' => 'General system configuration',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-cog'
            ],
            [
                'id' => 24,
                'category' => 'Integration',
                'name' => 'WooCommerce Sync',
                'url' => '/admin/sync',
                'description' => 'Synchronize with WooCommerce',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-sync'
            ],
            [
                'id' => 25,
                'category' => 'Integration',
                'name' => 'Offline Mode Settings',
                'url' => '/admin/offline',
                'description' => 'Progressive web app and offline configuration',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-wifi'
            ],
            [
                'id' => 26,
                'category' => 'System',
                'name' => 'Audit Trail',
                'url' => '/admin/audit-logs',
                'description' => 'System logs and user activity tracking',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-history'
            ],
            [
                'id' => 27,
                'category' => 'System',
                'name' => 'Database Management',
                'url' => '/admin/database',
                'description' => 'Database backup and maintenance',
                'admin' => true,
                'manager' => false,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-database'
            ],
            [
                'id' => 28,
                'category' => 'System',
                'name' => 'Export/Import',
                'url' => '/admin/export',
                'description' => 'Data export and import tools',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-file-export'
            ],
            [
                'id' => 29,
                'category' => 'System',
                'name' => 'Notifications Center',
                'url' => '/admin/notifications',
                'description' => 'System-wide notifications',
                'admin' => true,
                'manager' => true,
                'cashier' => true,
                'stock_manager' => true,
                'icon' => 'fa-bell'
            ],
            [
                'id' => 30,
                'category' => 'Customer Management',
                'name' => 'Store Credits Management',
                'url' => '/admin/store-credits',
                'description' => 'Monitor and manage all store credits across customers',
                'admin' => true,
                'manager' => true,
                'cashier' => false,
                'stock_manager' => false,
                'icon' => 'fa-gift'
            ],
        ];
        
        // Group features by category
        $groupedFeatures = [];
        foreach ($features as $feature) {
            $groupedFeatures[$feature['category']][] = $feature;
        }
        
        $this->view('admin/options', [
            'title' => 'System Options & Features',
            'features' => $features,
            'groupedFeatures' => $groupedFeatures
        ]);
    }
    
    /**
     * Sales Analytics (already exists in sales-analytics.php view)
     */
    public function salesAnalytics() {
        $this->requireAuth();
        $this->requirePermission('view_reports');
        
        $this->view('admin/sales-analytics', [
            'title' => 'Sales Analytics'
        ]);
    }
    
    /**
     * GST Reports
     */
    public function gstReports() {
        $this->requireAuth();
        $this->requirePermission('view_reports');
        
        $this->view('admin/gst-reports', [
            'title' => 'GST Reports'
        ]);
    }
    
    /**
     * Tax Rules Management
     */
    public function taxRules() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $this->view('admin/tax-rules', [
            'title' => 'Custom Tax Rules'
        ]);
    }
    
    /**
     * WhatsApp Notifications
     */
    public function whatsapp() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $this->view('admin/whatsapp', [
            'title' => 'WhatsApp Notifications'
        ]);
    }
    
    /**
     * Workflow Automation
     */
    public function automation() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $this->view('admin/automation', [
            'title' => 'Workflow Automation'
        ]);
    }
    
    /**
     * Discount Management
     */
    public function discounts() {
        $this->requireAuth();
        $this->requirePermission('manage_products');
        
        $this->view('admin/discounts', [
            'title' => 'Discount Management'
        ]);
    }
    
    /**
     * Payment Methods Configuration
     */
    public function payments() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $this->view('admin/payments', [
            'title' => 'Payment Methods'
        ]);
    }
    
    /**
     * Business Intelligence Dashboard (already exists in bi-dashboard.php view)
     */
    public function biDashboard() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $this->view('admin/bi-dashboard', [
            'title' => 'Business Intelligence'
        ]);
    }
    
    /**
     * Receipt Customization
     */
    public function receiptSettings() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $this->view('admin/receipt-settings', [
            'title' => 'Receipt Customization'
        ]);
    }
}
