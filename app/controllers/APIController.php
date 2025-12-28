<?php
/**
 * API Controller
 * Handles AJAX API requests
 */

require_once ROOT_PATH . '/app/controllers/BaseController.php';
require_once ROOT_PATH . '/app/models/ModelFactory.php';
require_once ROOT_PATH . '/app/models/Coupon.php';

class APIController extends BaseController {
    
    /**
     * Get products (AJAX with pagination and barcode support)
     */
    public function products() {
        $this->requireAuth();
        
        $search = sanitize(getGet('search', ''));
        $page = (int) getGet('page', 1);
        $limit = (int) getGet('limit', 20);
        $offset = ($page - 1) * $limit;
        
        $productModel = ModelFactory::getProduct();
        
        if (!empty($search)) {
            // First try exact barcode match (_ywbc_barcode_display_value)
            $product = $productModel->getProductByBarcode($search);
            if ($product) {
                $this->json([
                    'success' => true,
                    'products' => [$product],
                    'is_barcode' => true,
                    'total' => 1
                ]);
                return;
            }
            
            // Then try exact SKU match
            $product = $productModel->getProductBySku($search);
            if ($product) {
                $this->json([
                    'success' => true,
                    'products' => [$product],
                    'is_barcode' => true,
                    'total' => 1
                ]);
                return;
            }
            
            // Otherwise search by name/SKU/barcode (partial match)
            $products = $productModel->searchProducts($search, $limit);
        } else {
            $products = $productModel->getAllProducts($limit, $offset);
        }
        
        $this->json([
            'success' => true,
            'products' => $products,
            'is_barcode' => false,
            'page' => $page,
            'limit' => $limit
        ]);
    }
    
    /**
     * Add to cart (AJAX)
     * Note: Currently unused - POS uses client-side cart management
     */
    public function addToCart() {
        $this->requireAuth();
        
        // Validate CSRF token from JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        
        $productId = (int) ($input['product_id'] ?? 0);
        $quantity = (int) ($input['quantity'] ?? 1);
        
        if ($productId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid product'], 400);
        }
        
        $productModel = ModelFactory::getProduct();
        $product = $productModel->getProduct($productId);
        
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => floatval($product['price']),
                'quantity' => $quantity,
                'sku' => $product['sku']
            ];
        }
        
        Session::set('cart', $cart);
        
        $this->json([
            'success' => true,
            'cart' => $cart
        ]);
    }
    
    /**
     * Remove from cart (AJAX)
     * Note: Currently unused - POS uses client-side cart management
     */
    public function removeFromCart() {
        $this->requireAuth();
        
        // Validate CSRF token from JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        
        $productId = (int) ($input['product_id'] ?? 0);
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::set('cart', $cart);
        }
        
        $this->json([
            'success' => true,
            'cart' => $cart
        ]);
    }
    
    /**
     * Get cart (AJAX)
     */
    public function getCart() {
        $this->requireAuth();
        
        $cart = Session::get('cart', []);
        
        $this->json([
            'success' => true,
            'cart' => $cart
        ]);
    }
    
    /**
     * Hold order (save for later)
     */
    public function holdOrder() {
        $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        
        $referenceName = sanitize($input['reference_name'] ?? '');
        $cartData = $input['cart'] ?? [];
        $customerId = (int) ($input['customer_id'] ?? 0);
        $discountPercent = (float) ($input['discount_percent'] ?? 0);
        $couponCode = sanitize($input['coupon_code'] ?? '');
        $couponDiscount = (float) ($input['coupon_discount'] ?? 0);
        $pointsRedeemed = (int) ($input['points_redeemed'] ?? 0);
        $pointsDiscount = (float) ($input['points_discount'] ?? 0);
        $notes = sanitize($input['notes'] ?? '');
        
        if (empty($referenceName)) {
            $this->json(['success' => false, 'message' => 'Reference name is required'], 400);
        }
        
        if (empty($cartData)) {
            $this->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }
        
        $db = Database::getInstance();
        
        try {
            // Prepare extended cart data with coupon and points info
            $extendedCartData = [
                'cart' => $cartData,
                'coupon_code' => $couponCode,
                'coupon_discount' => $couponDiscount,
                'points_redeemed' => $pointsRedeemed,
                'points_discount' => $pointsDiscount
            ];
            
            $stmt = $db->query("
                INSERT INTO pos_held_orders 
                (reference_name, user_id, customer_id, cart_data, discount_percent, notes, held_at, expires_at, status)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'active')
            ", [
                $referenceName,
                Session::get('user_id'),
                $customerId,
                json_encode($extendedCartData),
                $discountPercent,
                $notes
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Order held successfully',
                'order_id' => $db->lastInsertId()
            ]);
        } catch (Exception $e) {
            error_log("Error holding order: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error holding order'], 500);
        }
    }
    
    /**
     * Get held orders list
     */
    public function getHeldOrders() {
        $this->requireAuth();
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        try {
            $stmt = $db->query("
                SELECT 
                    h.*,
                    u.display_name as cashier_name,
                    c.display_name as customer_name
                FROM pos_held_orders h
                LEFT JOIN {$prefix}users u ON h.user_id = u.ID
                LEFT JOIN {$prefix}users c ON h.customer_id = c.ID
                WHERE h.status = 'active'
                AND (h.expires_at IS NULL OR h.expires_at > NOW())
                ORDER BY h.held_at DESC
            ");
            
            $orders = $stmt->fetchAll();
            
            // Decode cart_data for each order
            foreach ($orders as &$order) {
                $order['cart_data'] = json_decode($order['cart_data'], true);
            }
            
            $this->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (Exception $e) {
            error_log("Error fetching held orders: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error fetching held orders'], 500);
        }
    }
    
    /**
     * Resume held order
     */
    public function resumeHeldOrder($orderId) {
        $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        try {
            // Get held order
            $stmt = $db->query("
                SELECT 
                    h.*,
                    c.display_name as customer_name,
                    c.user_email as customer_email
                FROM pos_held_orders h
                LEFT JOIN {$prefix}users c ON h.customer_id = c.ID
                WHERE h.id = ? AND h.status = 'active'
            ", [$orderId]);
            
            $order = $stmt->fetch();
            
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Held order not found'], 404);
            }
            
            // Update status to resumed
            $db->query("
                UPDATE pos_held_orders 
                SET status = 'resumed', updated_at = NOW()
                WHERE id = ?
            ", [$orderId]);
            
            // Return cart data
            $cartData = json_decode($order['cart_data'], true);
            
            $this->json([
                'success' => true,
                'message' => 'Order resumed successfully',
                'cart' => $cartData,
                'customer_id' => $order['customer_id'],
                'customer_name' => $order['customer_name'],
                'customer_email' => $order['customer_email'],
                'discount_percent' => $order['discount_percent']
            ]);
        } catch (Exception $e) {
            error_log("Error resuming order: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error resuming order'], 500);
        }
    }
    
    /**
     * Cancel held order
     */
    public function cancelHeldOrder($orderId) {
        $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        
        $db = Database::getInstance();
        
        try {
            $stmt = $db->query("
                UPDATE pos_held_orders 
                SET status = 'cancelled', updated_at = NOW()
                WHERE id = ? AND status = 'active'
            ", [$orderId]);
            
            $this->json([
                'success' => true,
                'message' => 'Held order cancelled successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error cancelling held order: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error cancelling held order'], 500);
        }
    }
    
    /**
     * Search orders by order number
     */
    public function searchOrders() {
        $this->requireAuth();
        
        $search = sanitize(getGet('q', ''));
        if (empty($search)) {
            $this->json(['success' => false, 'message' => 'Search term required'], 400);
        }
        
        try {
            $db = Database::getInstance();
            $prefix = $db->getPrefix();
            
            // First, try to find the order - use flexible column matching
            $stmt = $db->query("
                SELECT 
                    o.id,
                    o.order_number,
                    o.customer_id,
                    o.total,
                    o.created_at
                FROM pos_orders o
                WHERE o.order_number LIKE ? OR o.id = ?
                ORDER BY o.created_at DESC
                LIMIT 5
            ", ['%' . $search . '%', (int)$search]);
            
            $orders = $stmt->fetchAll();
            
            if ($orders) {
                // Fetch line items for each order with flexible column handling
                foreach ($orders as &$order) {
                    // Try multiple column patterns to handle schema variations
                    $lineItems = [];
                    try {
                        // Try getting items with flexible column selection
                        $itemStmt = $db->query("
                            SELECT *
                            FROM pos_order_items
                            WHERE order_id = ?
                            ORDER BY id ASC
                        ", [$order['id']]);
                        
                        $items = $itemStmt->fetchAll();
                        
                        foreach ($items as $item) {
                            $productName = 'Product';
                            
                            // Check for product_name in the item itself
                            if (!empty($item['product_name'])) {
                                $productName = $item['product_name'];
                            } elseif (!empty($item['product_id'])) {
                                // Try to fetch from WordPress products table
                                try {
                                    $prodStmt = $db->query("
                                        SELECT post_title 
                                        FROM {$prefix}posts 
                                        WHERE ID = ? AND post_type = 'product'
                                        LIMIT 1
                                    ", [(int)$item['product_id']]);
                                    
                                    $prod = $prodStmt->fetch();
                                    $productName = $prod ? $prod['post_title'] : 'Product #' . $item['product_id'];
                                } catch (Exception $e) {
                                    $productName = 'Product #' . $item['product_id'];
                                }
                            }
                            
                            $lineItems[] = [
                                'product_id' => $item['product_id'] ?? 0,
                                'product_name' => $productName,
                                'quantity' => $item['quantity'] ?? 0,
                                'price' => $item['price'] ?? $item['unit_price'] ?? 0,
                                'total' => $item['line_total'] ?? ($item['price'] ?? 0) * ($item['quantity'] ?? 0)
                            ];
                        }
                    } catch (Exception $e) {
                        error_log("Error fetching order items: " . $e->getMessage());
                    }
                    
                    $order['line_items'] = $lineItems;
                    
                    // Add customer name
                    if (!empty($order['customer_id'])) {
                        try {
                            $custStmt = $db->query("
                                SELECT display_name FROM {$prefix}users WHERE ID = ? LIMIT 1
                            ", [(int)$order['customer_id']]);
                            $cust = $custStmt->fetch();
                            $order['customer_name'] = $cust ? $cust['display_name'] : 'Walk-in Customer';
                        } catch (Exception $e) {
                            $order['customer_name'] = 'Walk-in Customer';
                        }
                    } else {
                        $order['customer_name'] = 'Walk-in Customer';
                    }
                }
                
                $this->json(['success' => true, 'orders' => $orders]);
            } else {
                $this->json(['success' => false, 'message' => 'No orders found'], 404);
            }
        } catch (Exception $e) {
            error_log("Order search error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get cashier's order history
     */
    public function getMyOrders() {
        $this->requireAuth();
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        $userId = Session::get('user_id');
        $userRole = Session::get('user_role');
        
        // Get filter parameters
        $date = sanitize(getGet('date', ''));
        $status = sanitize(getGet('status', ''));
        
        try {
            // Build query
            $sql = "
                SELECT 
                    o.id,
                    o.order_number,
                    o.wc_order_id,
                    o.customer_id,
                    o.subtotal,
                    o.discount_amount,
                    o.tax_amount,
                    o.total,
                    o.payment_method,
                    o.order_status,
                    o.created_at,
                    c.display_name as customer_name,
                    (SELECT COUNT(*) FROM pos_order_items WHERE order_id = o.id) as total_items
                FROM pos_orders o
                LEFT JOIN {$prefix}users c ON o.customer_id = c.ID
                WHERE 1=1
            ";
            
            $params = [];
            
            // Cashiers can only see their own orders, admins can see all
            if ($userRole !== 'administrator' && $userRole !== 'shop_manager') {
                $sql .= " AND o.user_id = ?";
                $params[] = $userId;
            }
            
            // Filter by date if provided
            if (!empty($date)) {
                $sql .= " AND DATE(o.created_at) = ?";
                $params[] = $date;
            }
            
            // Filter by status if provided
            if (!empty($status)) {
                $sql .= " AND o.order_status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY o.created_at DESC LIMIT 100";
            
            $stmt = $db->query($sql, $params);
            $orders = $stmt->fetchAll();
            
            $this->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (Exception $e) {
            error_log("Error fetching orders: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error fetching orders'], 500);
        }
    }
    
    /**
     * Fast customer search for POS (OPTIMIZED)
     * Uses searchCustomers() method - no additional queries per customer
     */
    public function customerSearch() {
        $this->requireAuth();
        
        $search = sanitize(getGet('search', ''));
        $limit = (int) getGet('limit', 30);
        
        $customerModel = ModelFactory::getCustomer();
        
        // Use optimized searchCustomers method for POS
        $customers = $customerModel->searchCustomers($search, $limit);
        
        // Format customers for Select2 compatibility
        $formattedCustomers = [];
        foreach ($customers as $customer) {
            $name = $customer['display_name'] ?? $customer['name'] ?? 'Unknown';
            $phone = $customer['mobile'] ?? $customer['billing_phone'] ?? '';
            $email = $customer['email'] ?? '';
            
            $formattedCustomers[] = [
                'id' => $customer['id'],
                'text' => $name . ($phone ? ' - ' . $phone : ''),
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'mobile' => $phone,
                'first_name' => $customer['first_name'] ?? '',
                'last_name' => $customer['last_name'] ?? '',
                'address' => $customer['billing_address'] ?? $customer['address'] ?? '',
                'city' => $customer['billing_city'] ?? $customer['city'] ?? '',
                'state' => $customer['billing_state'] ?? $customer['state'] ?? '',
                'pincode' => $customer['billing_pincode'] ?? $customer['pincode'] ?? ''
            ];
        }
        
        $this->json([
            'success' => true,
            'results' => $formattedCustomers,
            'pagination' => [
                'more' => count($formattedCustomers) >= $limit
            ]
        ]);
    }
    
    /**
     * Get all customers with pagination and filters (ADMIN PAGE ONLY)
     */
    public function customers() {
        $this->requireAuth();
        
        $page = (int) getGet('page', 1);
        $limit = (int) getGet('limit', 20);
        $search = sanitize(getGet('search', ''));
        $status = sanitize(getGet('status', ''));
        $offset = ($page - 1) * $limit;
        
        $customerModel = ModelFactory::getCustomer();
        
        $customers = $customerModel->getAllCustomers($limit, $offset, $search, $status);
        
        // Format customers for admin page (WITHOUT expensive additional queries)
        $formattedCustomers = [];
        foreach ($customers as $customer) {
            $customerId = $customer['id'];
            $phone = $customer['mobile'] ?? '';
            $email = $customer['email'] ?? '';
            $name = $customer['name'] ?? $customer['display_name'] ?? 'Unknown';
            $firstName = $customer['first_name'] ?? '';
            $lastName = $customer['last_name'] ?? '';
            
            $formattedCustomers[] = [
                'id' => $customerId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'mobile' => $phone,
                'username' => $customer['username'] ?? '',
                'first_name' => $firstName,
                'last_name' => $lastName,
                'address' => $customer['address'] ?? '',
                'city' => $customer['city'] ?? '',
                'state' => $customer['state'] ?? '',
                'pincode' => $customer['pincode'] ?? '',
                'created_at' => $customer['created_at'] ?? ''
            ];
        }
        
        $total = $customerModel->countCustomers($search, $status);
        $totalPages = ceil($total / $limit);
        
        $this->json([
            'success' => true,
            'customers' => $formattedCustomers,
            'data' => $formattedCustomers,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $total,
                'per_page' => $limit
            ]
        ]);
    }
    
    /**
     * Get customer statistics
     */
    public function customerStats() {
        $this->requireAuth();
        
        $customerModel = ModelFactory::getCustomer();
        $stats = $customerModel->getStats();
        
        $this->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Get single customer
     */
    public function getCustomer($customerId) {
        $this->requireAuth();
        
        $customerModel = ModelFactory::getCustomer();
        $customer = $customerModel->getCustomer($customerId);
        
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found'], 404);
        }
        
        $this->json([
            'success' => true,
            'data' => $customer
        ]);
    }
    
    /**
     * Get customer with full details including order history
     */
    public function getCustomerDetails($customerId) {
        $this->requireAuth();
        
        $customerModel = ModelFactory::getCustomer();
        $customer = $customerModel->getCustomer($customerId);
        
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found'], 404);
        }
        
        $orders = $customerModel->getCustomerOrders($customerId, 10);
        $loyaltyTransactions = $customerModel->getLoyaltyTransactions($customerId, 10);
        
        $this->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'orders' => $orders,
                'loyalty_transactions' => $loyaltyTransactions
            ]
        ]);
    }
    
    /**
     * Create new customer
     */
    public function createCustomer() {
        $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Support both "name" and separate "first_name/last_name"
        if (!empty($input['name'])) {
            $nameParts = explode(' ', trim($input['name']), 2);
            $input['first_name'] = $nameParts[0];
            $input['last_name'] = $nameParts[1] ?? '';
        }
        
        if (empty($input['first_name']) || empty($input['mobile'])) {
            $this->json(['success' => false, 'message' => 'Name and mobile are required'], 400);
        }
        
        $customerModel = ModelFactory::getCustomer();
        
        $existingCustomer = $customerModel->getByMobile($input['mobile']);
        if ($existingCustomer) {
            $this->json(['success' => false, 'message' => 'Customer with this mobile number already exists'], 400);
        }
        
        try {
            $customerId = $customerModel->createCustomer($input);
            
            $customerData = [
                'id' => $customerId,
                'name' => trim(($input['first_name'] ?? '') . ' ' . ($input['last_name'] ?? '')),
                'mobile' => $input['mobile'],
                'email' => $input['email'] ?? ''
            ];
            
            $this->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer_id' => $customerId,  // Legacy format for backward compatibility
                'customer' => $customerData     // New format for enhanced functionality
            ]);
        } catch (Exception $e) {
            error_log("Error creating customer: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error creating customer'], 500);
        }
    }
    
    /**
     * Update customer
     */
    public function updateCustomer($customerId) {
        $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['first_name']) || empty($input['last_name']) || empty($input['mobile'])) {
            $this->json(['success' => false, 'message' => 'First name, last name, and mobile are required'], 400);
        }
        
        $customerModel = ModelFactory::getCustomer();
        
        $customer = $customerModel->getCustomer($customerId);
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found'], 404);
        }
        
        $existingCustomer = $customerModel->getByMobile($input['mobile']);
        if ($existingCustomer && $existingCustomer['id'] != $customerId) {
            $this->json(['success' => false, 'message' => 'Another customer with this mobile number already exists'], 400);
        }
        
        try {
            $customerModel->updateCustomer($customerId, $input);
            
            $this->json([
                'success' => true,
                'message' => 'Customer updated successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error updating customer: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error updating customer'], 500);
        }
    }
    
    /**
     * Delete customer
     */
    public function deleteCustomer($customerId) {
        $this->requireAuth();
        
        $customerModel = ModelFactory::getCustomer();
        
        $customer = $customerModel->getCustomer($customerId);
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found'], 404);
        }
        
        try {
            $customerModel->deleteCustomer($customerId);
            
            $this->json([
                'success' => true,
                'message' => 'Customer deleted successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error deleting customer: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error deleting customer'], 500);
        }
    }
    
    /**
     * Get all returns with pagination
     */
    public function returns() {
        $this->requireAuth();
        
        $page = (int) getGet('page', 1);
        $limit = (int) getGet('limit', 20);
        $search = sanitize(getGet('search', ''));
        $status = sanitize(getGet('status', ''));
        $offset = ($page - 1) * $limit;
        
        $returnModel = new ReturnOrder();
        
        $returns = $returnModel->getAllReturns($limit, $offset, $search, $status);
        $total = $returnModel->countReturns($search, $status);
        $totalPages = ceil($total / $limit);
        
        $this->json([
            'success' => true,
            'data' => $returns,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $total,
                'per_page' => $limit
            ]
        ]);
    }
    
    /**
     * Get return statistics
     */
    public function returnStats() {
        $this->requireAuth();
        
        $returnModel = new ReturnOrder();
        $stats = $returnModel->getStats();
        
        $this->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Get single return with items
     */
    public function getReturn($returnId) {
        $this->requireAuth();
        
        $returnModel = new ReturnOrder();
        $return = $returnModel->getReturn($returnId);
        
        if (!$return) {
            $this->json(['success' => false, 'message' => 'Return not found'], 404);
        }
        
        $this->json([
            'success' => true,
            'data' => $return
        ]);
    }
    
    /**
     * Update return status
     */
    public function updateReturnStatus($returnId) {
        $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['status'])) {
            $this->json(['success' => false, 'message' => 'Status is required'], 400);
        }
        
        $returnModel = new ReturnOrder();
        
        try {
            $returnModel->updateStatus($returnId, $input['status'], $input['notes'] ?? null);
            
            $this->json([
                'success' => true,
                'message' => 'Return status updated successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error updating return status: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error updating return status'], 500);
        }
    }
    
    /**
     * Sales Reports API Endpoints
     */
    
    public function reportsSummary() {
        $this->requireAuth();
        
        $startDate = getGet('start_date', date('Y-m-d') . ' 00:00:00');
        $endDate = getGet('end_date', date('Y-m-d') . ' 23:59:59');
        
        $reportModel = new SalesReport();
        $summary = $reportModel->getSalesSummary($startDate, $endDate);
        
        $this->json([
            'success' => true,
            'data' => $summary
        ]);
    }
    
    public function reportsTrend() {
        $this->requireAuth();
        
        $startDate = getGet('start_date', date('Y-m-d') . ' 00:00:00');
        $endDate = getGet('end_date', date('Y-m-d') . ' 23:59:59');
        $groupBy = getGet('group_by', 'day');
        
        $reportModel = new SalesReport();
        $trend = $reportModel->getSalesByDate($startDate, $endDate, $groupBy);
        
        $this->json([
            'success' => true,
            'data' => $trend
        ]);
    }
    
    public function reportsPaymentMethods() {
        $this->requireAuth();
        
        $startDate = getGet('start_date', date('Y-m-d') . ' 00:00:00');
        $endDate = getGet('end_date', date('Y-m-d') . ' 23:59:59');
        
        $reportModel = new SalesReport();
        $paymentMethods = $reportModel->getSalesByPaymentMethod($startDate, $endDate);
        
        $this->json([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }
    
    public function reportsTopProducts() {
        $this->requireAuth();
        
        $startDate = getGet('start_date', date('Y-m-d') . ' 00:00:00');
        $endDate = getGet('end_date', date('Y-m-d') . ' 23:59:59');
        $limit = (int) getGet('limit', 10);
        
        $reportModel = new SalesReport();
        $topProducts = $reportModel->getTopProducts($startDate, $endDate, $limit);
        
        $this->json([
            'success' => true,
            'data' => $topProducts
        ]);
    }
    
    public function reportsHourly() {
        $this->requireAuth();
        
        $startDate = getGet('start_date', date('Y-m-d') . ' 00:00:00');
        $endDate = getGet('end_date', date('Y-m-d') . ' 23:59:59');
        
        $reportModel = new SalesReport();
        $hourlySales = $reportModel->getHourlySales($startDate, $endDate);
        
        $this->json([
            'success' => true,
            'data' => $hourlySales
        ]);
    }
    
    public function reportsCategorySales() {
        $this->requireAuth();
        
        $startDate = getGet('start_date', date('Y-m-d') . ' 00:00:00');
        $endDate = getGet('end_date', date('Y-m-d') . ' 23:59:59');
        
        $reportModel = new SalesReport();
        $categorySales = $reportModel->getCategorySales($startDate, $endDate);
        
        $this->json([
            'success' => true,
            'data' => $categorySales
        ]);
    }
    
    public function reportsCustomerStats() {
        $this->requireAuth();
        
        $startDate = getGet('start_date', date('Y-m-d') . ' 00:00:00');
        $endDate = getGet('end_date', date('Y-m-d') . ' 23:59:59');
        
        $reportModel = new SalesReport();
        $customerStats = $reportModel->getCustomerStats($startDate, $endDate);
        
        $this->json([
            'success' => true,
            'data' => $customerStats
        ]);
    }
    
    public function reportsDashboard() {
        $this->requireAuth();
        
        $reportModel = new SalesReport();
        $stats = $reportModel->getDashboardStats();
        
        $this->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Get all tax rules
     */
    public function getTaxRules() {
        $this->requireAuth();
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        try {
            $stmt = $db->query("
                SELECT 
                    tr.*,
                    t.name as category_name
                FROM pos_custom_tax_rules tr
                LEFT JOIN {$prefix}terms t ON tr.category_id = t.term_id
                ORDER BY tr.priority DESC, tr.id DESC
            ");
            
            $rules = $stmt->fetchAll();
            
            // Convert string booleans to actual booleans
            foreach ($rules as &$rule) {
                $rule['is_active'] = (bool) $rule['is_active'];
            }
            
            $this->json([
                'success' => true,
                'rules' => $rules
            ]);
        } catch (Exception $e) {
            error_log("Error fetching tax rules: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error fetching tax rules'], 500);
        }
    }
    
    /**
     * Get single tax rule
     */
    public function getTaxRule($id) {
        $this->requireAuth();
        
        $db = Database::getInstance();
        
        try {
            $stmt = $db->query("
                SELECT * FROM pos_custom_tax_rules
                WHERE id = ?
            ", [$id]);
            
            $rule = $stmt->fetch();
            
            if (!$rule) {
                $this->json(['success' => false, 'message' => 'Tax rule not found'], 404);
                return;
            }
            
            $rule['is_active'] = (bool) $rule['is_active'];
            
            $this->json([
                'success' => true,
                'rule' => $rule
            ]);
        } catch (Exception $e) {
            error_log("Error fetching tax rule: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error fetching tax rule'], 500);
        }
    }
    
    /**
     * Create new tax rule
     */
    public function createTaxRule() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $db = Database::getInstance();
        
        // Validate required fields
        if (empty($input['rule_name']) || empty($input['rule_type']) || !isset($input['tax_rate'])) {
            $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }
        
        try {
            $stmt = $db->query("
                INSERT INTO pos_custom_tax_rules 
                (rule_name, rule_type, category_id, min_price, max_price, tax_rate, priority, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $input['rule_name'],
                $input['rule_type'],
                $input['category_id'] ?? null,
                $input['min_price'] ?? null,
                $input['max_price'] ?? null,
                $input['tax_rate'],
                $input['priority'] ?? 0,
                $input['is_active'] ?? true
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Tax rule created successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error creating tax rule: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error creating tax rule'], 500);
        }
    }
    
    /**
     * Update tax rule
     */
    public function updateTaxRule($id) {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $db = Database::getInstance();
        
        try {
            $stmt = $db->query("
                UPDATE pos_custom_tax_rules
                SET rule_name = ?, 
                    rule_type = ?, 
                    category_id = ?, 
                    min_price = ?, 
                    max_price = ?, 
                    tax_rate = ?, 
                    priority = ?, 
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?
            ", [
                $input['rule_name'],
                $input['rule_type'],
                $input['category_id'] ?? null,
                $input['min_price'] ?? null,
                $input['max_price'] ?? null,
                $input['tax_rate'],
                $input['priority'] ?? 0,
                $input['is_active'] ?? true,
                $id
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Tax rule updated successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error updating tax rule: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error updating tax rule'], 500);
        }
    }
    
    /**
     * Delete tax rule
     */
    public function deleteTaxRule($id) {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $db = Database::getInstance();
        
        try {
            $stmt = $db->query("
                DELETE FROM pos_custom_tax_rules
                WHERE id = ?
            ", [$id]);
            
            $this->json([
                'success' => true,
                'message' => 'Tax rule deleted successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error deleting tax rule: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error deleting tax rule'], 500);
        }
    }
    
    /**
     * Get setting value
     */
    public function getSetting($key) {
        $this->requireAuth();
        
        $db = Database::getInstance();
        
        try {
            $stmt = $db->query("
                SELECT setting_value, setting_type
                FROM pos_settings
                WHERE setting_key = ?
            ", [$key]);
            
            $setting = $stmt->fetch();
            
            if (!$setting) {
                $this->json(['success' => false, 'message' => 'Setting not found'], 404);
                return;
            }
            
            // Convert value based on type
            $value = $setting['setting_value'];
            if ($setting['setting_type'] === 'boolean') {
                $value = $value === 'true' || $value === '1' || $value === 1;
            } elseif ($setting['setting_type'] === 'number') {
                $value = (float) $value;
            }
            
            $this->json([
                'success' => true,
                'key' => $key,
                'value' => $value,
                'type' => $setting['setting_type']
            ]);
        } catch (Exception $e) {
            error_log("Error fetching setting: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error fetching setting'], 500);
        }
    }
    
    /**
     * Update setting value
     */
    public function updateSetting($key) {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        $db = Database::getInstance();
        
        // Convert value to string for storage
        $value = $input['value'];
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        
        try {
            $stmt = $db->query("
                UPDATE pos_settings
                SET setting_value = ?, updated_at = NOW()
                WHERE setting_key = ?
            ", [$value, $key]);
            
            $this->json([
                'success' => true,
                'message' => 'Setting updated successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error updating setting: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error updating setting'], 500);
        }
    }
    
    /**
     * Get all product categories from WooCommerce
     */
    public function getCategories() {
        $this->requireAuth();
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        try {
            $stmt = $db->query("
                SELECT DISTINCT
                    t.term_id,
                    t.name,
                    t.slug,
                    COUNT(tr.object_id) as product_count
                FROM {$prefix}terms t
                INNER JOIN {$prefix}term_taxonomy tt ON t.term_id = tt.term_id
                LEFT JOIN {$prefix}term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tt.taxonomy = 'product_cat'
                GROUP BY t.term_id, t.name, t.slug
                ORDER BY t.name ASC
            ");
            
            $categories = $stmt->fetchAll();
            
            $this->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error fetching categories'], 500);
        }
    }
    
    /**
     * Get receipt settings
     */
    public function getReceiptSettings() {
        $this->requireAuth();
        
        $db = Database::getInstance();
        
        try {
            $stmt = $db->query("
                SELECT setting_value 
                FROM pos_settings 
                WHERE setting_key = 'receipt_settings'
            ");
            
            $result = $stmt->fetch();
            $settings = [];
            
            if ($result && !empty($result['setting_value'])) {
                $settings = json_decode($result['setting_value'], true) ?? [];
            }
            
            $this->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (Exception $e) {
            error_log("Error fetching receipt settings: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error fetching receipt settings'], 500);
        }
    }
    
    /**
     * Update receipt settings
     */
    public function updateReceiptSettings() {
        $this->requireAuth();
        $this->requirePermission('manage_system');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $input['csrf_token'] ?? '';
        if (!verifyCsrfToken($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }
        
        unset($input['csrf_token']);
        
        $db = Database::getInstance();
        
        try {
            $settingsJson = json_encode($input);
            
            // Check if setting exists
            $stmt = $db->query("
                SELECT id FROM pos_settings 
                WHERE setting_key = 'receipt_settings'
            ");
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Update existing
                $db->query("
                    UPDATE pos_settings
                    SET setting_value = ?, updated_at = NOW()
                    WHERE setting_key = 'receipt_settings'
                ", [$settingsJson]);
            } else {
                // Insert new
                $db->query("
                    INSERT INTO pos_settings (setting_key, setting_value, setting_type, description)
                    VALUES ('receipt_settings', ?, 'json', 'Receipt customization settings')
                ", [$settingsJson]);
            }
            
            $this->json([
                'success' => true,
                'message' => 'Receipt settings updated successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error updating receipt settings: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error updating receipt settings'], 500);
        }
    }
    
    /**
     * Validate and apply coupon code
     */
    public function validateCoupon() {
        $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $code = strtoupper(trim($input['code'] ?? ''));
        $cartData = $input['cart'] ?? [];
        
        if (empty($code)) {
            $this->json(['success' => false, 'message' => 'Please enter a coupon code'], 400);
        }
        
        $couponModel = new Coupon();
        $validation = $couponModel->validateCoupon($code, $cartData);
        
        if (!$validation['valid']) {
            $this->json([
                'success' => false,
                'message' => $validation['message']
            ], 400);
        }
        
        $coupon = $validation['coupon'];
        $cartSubtotal = floatval($cartData['subtotal'] ?? 0);
        $discountAmount = $couponModel->calculateDiscount($coupon, $cartSubtotal);
        
        $this->json([
            'success' => true,
            'message' => $validation['message'],
            'coupon' => [
                'code' => $coupon['code'],
                'type' => $coupon['discount_type'],
                'amount' => $coupon['amount'],
                'discount_amount' => $discountAmount,
                'description' => $coupon['description']
            ]
        ]);
    }
    
    /**
     * Get customer loyalty points balance
     */
    public function getCustomerPoints($customerId) {
        $this->requireAuth();
        
        if (empty($customerId)) {
            $this->json(['success' => false, 'message' => 'Customer ID required'], 400);
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            $prefix = DB_PREFIX;
            
            // Get points from wp_usermeta (since we're using WordPress tables)
            $sql = "SELECT meta_value 
                   FROM {$prefix}usermeta 
                   WHERE user_id = ? 
                   AND meta_key = 'loyalty_points'
                   LIMIT 1";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$customerId]);
            $result = $stmt->fetch();
            
            $points = intval($result['meta_value'] ?? 0);
            
            // Get conversion rate from settings
            $settingsSql = "SELECT setting_value 
                           FROM pos_settings 
                           WHERE setting_key = 'loyalty_points_per_rupee' 
                           LIMIT 1";
            
            $settingsStmt = $db->query($settingsSql);
            $settingsResult = $settingsStmt->fetch();
            $pointsPerRupee = floatval($settingsResult['setting_value'] ?? 1);
            
            // Calculate rupee value (e.g., 100 points = ₹100 if 1:1 ratio)
            $rupeeValue = ($points / $pointsPerRupee);
            
            $this->json([
                'success' => true,
                'points' => $points,
                'rupee_value' => round($rupeeValue, 2),
                'conversion_rate' => $pointsPerRupee
            ]);
        } catch (Exception $e) {
            error_log("Error fetching customer points: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error fetching points balance'], 500);
        }
    }
    
    /**
     * Redeem loyalty points for discount
     */
    public function redeemPoints() {
        $this->requireAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $customerId = intval($input['customer_id'] ?? 0);
        $pointsToRedeem = intval($input['points'] ?? 0);
        $cartTotal = floatval($input['cart_total'] ?? 0);
        
        if (empty($customerId) || $pointsToRedeem <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid customer or points amount'], 400);
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            $prefix = DB_PREFIX;
            
            // Get current points balance
            $sql = "SELECT meta_value 
                   FROM {$prefix}usermeta 
                   WHERE user_id = ? 
                   AND meta_key = 'loyalty_points'
                   LIMIT 1";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$customerId]);
            $result = $stmt->fetch();
            
            $currentPoints = intval($result['meta_value'] ?? 0);
            
            if ($currentPoints < $pointsToRedeem) {
                $this->json([
                    'success' => false,
                    'message' => 'Insufficient points balance. Available: ' . $currentPoints
                ], 400);
            }
            
            // Get conversion rate from settings
            $settingsSql = "SELECT setting_value 
                           FROM pos_settings 
                           WHERE setting_key = 'loyalty_points_per_rupee' 
                           LIMIT 1";
            
            $settingsStmt = $db->query($settingsSql);
            $settingsResult = $settingsStmt->fetch();
            $pointsPerRupee = floatval($settingsResult['setting_value'] ?? 1);
            
            // Calculate discount amount
            $discountAmount = round(($pointsToRedeem / $pointsPerRupee), 2);
            
            // Validate discount doesn't exceed cart total
            if ($discountAmount > $cartTotal) {
                $maxPoints = floor($cartTotal * $pointsPerRupee);
                $this->json([
                    'success' => false,
                    'message' => 'Points redemption exceeds cart total. Maximum: ' . $maxPoints . ' points'
                ], 400);
            }
            
            $this->json([
                'success' => true,
                'message' => 'Points redeemed successfully',
                'points_redeemed' => $pointsToRedeem,
                'discount_amount' => $discountAmount,
                'remaining_points' => $currentPoints - $pointsToRedeem
            ]);
        } catch (Exception $e) {
            error_log("Error redeeming points: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error redeeming points'], 500);
        }
    }
    
    /**
     * Get applicable tax rate for a product (based on custom tax rules)
     * Accepts either product ID or barcode as parameter
     */
    public function getProductTaxRate($productId) {
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        try {
            // Try to find product by ID first, then by barcode
            $actualProductId = (int) $productId;
            
            if ($actualProductId === 0) {
                // Not a numeric ID, try to find by barcode
                $stmt = $db->query("
                    SELECT pm.post_id FROM {$prefix}postmeta pm
                    WHERE pm.meta_key = '_ywbc_barcode_display_value' AND pm.meta_value = ?
                    LIMIT 1
                ", [$productId]);
                $result = $stmt->fetch();
                if ($result) {
                    $actualProductId = (int) $result['post_id'];
                }
            }
            
            // Get product details
            $stmt = $db->query("
                SELECT p.ID, pm1.meta_value as price, pm2.meta_value as tax_class, pm3.meta_value as tax_status
                FROM {$prefix}posts p
                LEFT JOIN {$prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_price'
                LEFT JOIN {$prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_tax_class'
                LEFT JOIN {$prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_tax_status'
                WHERE p.ID = ? AND p.post_type = 'product'
                LIMIT 1
            ", [$actualProductId]);
            
            $product = $stmt->fetch();
            if (!$product) {
                $this->json(['success' => false, 'message' => 'Product not found'], 404);
                return;
            }
            
            // Get custom tax system status
            $stmt = $db->query("
                SELECT setting_value FROM pos_settings
                WHERE setting_key = 'custom_tax_enabled'
            ");
            $setting = $stmt->fetch();
            $customTaxEnabled = ($setting && ($setting['setting_value'] === 'true' || $setting['setting_value'] === '1'));
            
            $taxRate = 0.18; // Default 18%
            
            if ($customTaxEnabled) {
                // Get product categories
                $stmt = $db->query("
                    SELECT tt.term_id
                    FROM {$prefix}term_relationships tr
                    INNER JOIN {$prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.object_id = ? AND tt.taxonomy = 'product_cat'
                ", [$productId]);
                $productCategories = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                $productCategories = array_map('intval', $productCategories);
                
                if (!empty($productCategories)) {
                    // Get matching tax rule
                    $placeholders = implode(',', array_fill(0, count($productCategories), '?'));
                    $stmt = $db->query("
                        SELECT tax_rate FROM pos_custom_tax_rules
                        WHERE is_active = true AND rule_type = 'category' AND category_id IN ({$placeholders})
                        ORDER BY priority DESC, id DESC
                        LIMIT 1
                    ", $productCategories);
                    
                    $rule = $stmt->fetch();
                    if ($rule) {
                        $taxRate = floatval($rule['tax_rate']) / 100;
                    }
                }
            }
            
            $this->json([
                'success' => true,
                'product_id' => $actualProductId,
                'tax_rate' => $taxRate,
                'tax_percent' => $taxRate * 100
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting product tax rate: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error getting tax rate'], 500);
        }
    }
    
    /**
     * Get customer store credits
     */
    public function getCustomerStoreCredit($customerId) {
        $this->requireAuth();
        
        $customerId = (int) $customerId;
        if (!$customerId) {
            $this->json(['success' => false, 'message' => 'Customer ID required'], 400);
        }
        
        try {
            $db = Database::getInstance();
            
            // Get total store credit balance
            $stmt = $db->query("
                SELECT COALESCE(SUM(balance), 0) as total_balance 
                FROM pos_store_credit 
                WHERE customer_id = ? AND status = 'active'
            ", [$customerId]);
            
            $result = $stmt->fetch();
            $totalBalance = floatval($result['total_balance'] ?? 0);
            
            // Get individual credits
            $stmt = $db->query("
                SELECT id, credit_number, amount, balance, source_type, created_at
                FROM pos_store_credit
                WHERE customer_id = ? AND status = 'active'
                ORDER BY created_at DESC
            ", [$customerId]);
            
            $credits = $stmt->fetchAll();
            
            $this->json([
                'success' => true,
                'total_balance' => $totalBalance,
                'credits' => $credits
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Use store credit for payment
     */
    public function useStoreCredit() {
        $this->requireAuth();
        $data = $this->getJsonData();
        
        $customerId = (int) ($data['customer_id'] ?? 0);
        $amount = floatval($data['amount'] ?? 0);
        $orderId = (int) ($data['order_id'] ?? 0);
        
        if (!$customerId || $amount <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid customer or amount'], 400);
        }
        
        try {
            $db = Database::getInstance();
            
            // Get active store credit
            $stmt = $db->query("
                SELECT * FROM pos_store_credit 
                WHERE customer_id = ? AND status = 'active' AND balance > 0
                ORDER BY created_at ASC
                LIMIT 1
            ", [$customerId]);
            
            $credit = $stmt->fetch();
            if (!$credit || $credit['balance'] < $amount) {
                $this->json(['success' => false, 'message' => 'Insufficient store credit balance'], 400);
                return;
            }
            
            $newBalance = $credit['balance'] - $amount;
            $newStatus = $newBalance <= 0 ? 'used' : 'active';
            $stmt = $db->query("UPDATE pos_store_credit SET balance = ?, status = ? WHERE id = ?", [$newBalance, $newStatus, $credit['id']]);
            
            // Log transaction with correct column names
            $stmt = $db->query("
                INSERT INTO pos_store_credit_transactions (store_credit_id, order_id, transaction_type, amount, balance_after, description, processed_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$credit['id'], $orderId, 'use', -$amount, $newBalance, 'Store credit used for order', get_current_user_id()]);
            
            $this->json(['success' => true, 'message' => 'Store credit applied', 'remaining_balance' => $newBalance]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get order details with items
     */
    public function getOrder($orderId) {
        try {
            $db = Database::getInstance();
            $prefix = $db->getPrefix();
            
            // Get order from pos_orders table
            $stmt = $db->query("
                SELECT 
                    o.*,
                    u.display_name as cashier_name,
                    c.display_name as customer_name
                FROM pos_orders o
                LEFT JOIN {$prefix}users u ON o.user_id = u.ID
                LEFT JOIN {$prefix}users c ON o.customer_id = c.ID
                WHERE o.id = ?
                LIMIT 1
            ", [$orderId]);
            
            $order = $stmt->fetch();
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Order not found'], 404);
                return;
            }
            
            // Get order items from pos_order_items table
            $stmt = $db->query("
                SELECT 
                    oi.*,
                    p.post_title as product_name
                FROM pos_order_items oi
                LEFT JOIN {$prefix}posts p ON oi.product_id = p.ID
                WHERE oi.order_id = ?
                ORDER BY oi.id ASC
            ", [$orderId]);
            
            $items = $stmt->fetchAll();
            
            $this->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'items' => $items
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error in getOrder: " . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
