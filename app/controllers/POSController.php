<?php
/**
 * POS Controller
 * Handles main POS interface and operations
 */

require_once ROOT_PATH . '/app/controllers/BaseController.php';
require_once ROOT_PATH . '/app/models/ModelFactory.php';
require_once ROOT_PATH . '/app/models/Coupon.php';
require_once ROOT_PATH . '/app/helpers/WooCommerceAPI.php';

class POSController extends BaseController {
    
    /**
     * Show POS interface
     */
    public function index() {
        $this->requireAuth();
        $this->requirePermission('create_orders');
        
        $productModel = ModelFactory::getProduct();
        $products = $productModel->getAllProducts(20, 0);
        
        // Get cart from session
        $cart = Session::get('cart', []);
        
        $this->view('pos/index', [
            'title' => 'POS - ' . $this->config['app']['name'],
            'products' => $products,
            'cart' => $cart,
            'user' => getCurrentUser()
        ]);
    }
    
    /**
     * Add product to cart
     */
    public function addToCart() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        // Validate CSRF token
        $this->validateCsrf();
        
        $productId = (int) getPost('product_id');
        $quantity = (int) getPost('quantity', 1);
        
        if ($productId <= 0 || $quantity <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid product or quantity'], 400);
        }
        
        $productModel = ModelFactory::getProduct();
        $product = $productModel->getProduct($productId);
        
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }
        
        // Get current cart
        $cart = Session::get('cart', []);
        
        // Add or update product in cart
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
            'message' => 'Product added to cart',
            'cart' => $cart,
            'cart_count' => array_sum(array_column($cart, 'quantity'))
        ]);
    }
    
    /**
     * Remove product from cart
     */
    public function removeFromCart() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        // Validate CSRF token
        $this->validateCsrf();
        
        $productId = (int) getPost('product_id');
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::set('cart', $cart);
        }
        
        $this->json([
            'success' => true,
            'message' => 'Product removed from cart',
            'cart' => $cart,
            'cart_count' => array_sum(array_column($cart, 'quantity'))
        ]);
    }
    
    /**
     * Update cart item quantity
     */
    public function updateCart() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        // Validate CSRF token
        $this->validateCsrf();
        
        $productId = (int) getPost('product_id');
        $quantity = (int) getPost('quantity');
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            if ($quantity <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $quantity;
            }
            Session::set('cart', $cart);
        }
        
        $this->json([
            'success' => true,
            'message' => 'Cart updated',
            'cart' => $cart,
            'cart_count' => array_sum(array_column($cart, 'quantity'))
        ]);
    }
    
    /**
     * Checkout and create order
     */
    public function checkout() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        // Validate CSRF token
        $this->validateCsrf();
        
        // Get JSON payload (JavaScript sends application/json)
        $jsonPayload = file_get_contents('php://input');
        $postData = json_decode($jsonPayload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['success' => false, 'message' => 'Invalid JSON payload'], 400);
        }
        
        // Get cart from POST data
        $cart = $postData['cart'] ?? [];
        
        if (empty($cart)) {
            $this->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }
        
        // Validate input data
        $customerId = (int) ($postData['customer_id'] ?? 0);
        $paymentMethodUI = sanitize($postData['payment_method'] ?? 'cash');
        $discountPercent = floatval($postData['discount_percent'] ?? 0);
        $splitPayments = $postData['split_payments'] ?? [];
        $couponCode = sanitize($postData['coupon_code'] ?? '');
        $pointsRedeemed = intval($postData['points_redeemed'] ?? 0);
        $storeCreditApplied = (bool) ($postData['store_credit_applied'] ?? false);
        $storeCreditAmount = floatval($postData['store_credit_amount'] ?? 0);
        
        // Server-side coupon and points discount calculation (never trust client values)
        $couponDiscount = 0;
        $pointsDiscount = 0;
        $storeCreditDiscount = 0;
        
        // Validate and map payment method to WooCommerce gateway ID
        if (!in_array($paymentMethodUI, ['cash', 'card', 'upi', 'split', 'store_credit'])) {
            $this->json(['success' => false, 'message' => 'Invalid payment method'], 400);
        }
        
        // Map POS payment method to WooCommerce gateway ID
        $paymentGateways = $this->config['pos']['payment_gateways'] ?? [
            'cash' => 'cod',
            'card' => 'bacs',
            'upi' => 'cod',
            'store_credit' => 'store_credit'
        ];
        $paymentMethod = $paymentGateways[$paymentMethodUI] ?? 'cod';
        
        // Validate discount
        if ($discountPercent < 0 || $discountPercent > 100) {
            $this->json(['success' => false, 'message' => 'Invalid discount percentage'], 400);
        }
        
        // Tax rate mapping
        $taxRates = [
            'standard' => 0.18,
            'reduced-rate' => 0.05,
            'zero-rate' => 0.00,
            '' => 0.18
        ];
        
        // SERVER-SIDE cart validation - Never trust client prices or discounts
        $productModel = ModelFactory::getProduct();
        $subtotal = 0;
        $totalItemDiscounts = 0;
        $cartItems = []; // Store item details for tax calculation after all discounts
        $lineItems = [];
        
        // Check if user has permission for per-item discounts
        $currentUser = getCurrentUser();
        $canApplyItemDiscounts = in_array($currentUser['role'], ['admin', 'manager']);
        
        foreach ($cart as $item) {
            if (!isset($item['id']) || !isset($item['quantity'])) {
                $this->json(['success' => false, 'message' => 'Invalid cart item data'], 400);
            }
            
            $productId = (int) $item['id'];
            $quantity = (int) $item['quantity'];
            
            // CLIENT-SUPPLIED ITEM DISCOUNT IS IGNORED FOR SECURITY
            // Only allow if user has permission
            $clientItemDiscount = floatval($item['item_discount'] ?? 0);
            $itemDiscount = ($canApplyItemDiscounts && $clientItemDiscount > 0) ? $clientItemDiscount : 0;
            
            if ($quantity <= 0 || $quantity > 999) {
                $this->json(['success' => false, 'message' => 'Invalid quantity for product ' . $productId], 400);
            }
            
            // ALWAYS fetch authoritative price from database - NEVER trust client
            $product = $productModel->getProduct($productId);
            if (!$product) {
                $this->json(['success' => false, 'message' => "Product {$productId} not found"], 404);
            }
            
            // Use SERVER price only - reject if client sent different price
            $serverPrice = floatval($product['price']);
            $clientPrice = floatval($item['price'] ?? 0);
            
            // Validate client price matches server (with 0.01 tolerance for rounding)
            if (abs($serverPrice - $clientPrice) > 0.01) {
                $this->json([
                    'success' => false, 
                    'message' => "Price mismatch for product {$productId}. Please refresh the cart.",
                    'server_price' => $serverPrice,
                    'client_price' => $clientPrice
                ], 400);
            }
            
            $price = $serverPrice; // Always use server price
            $taxClass = $product['tax_class'] ?? '';
            $taxStatus = $product['tax_status'] ?? 'taxable';
            
            // Validate item discount doesn't exceed price
            if ($itemDiscount > $price) {
                $this->json(['success' => false, 'message' => 'Item discount cannot exceed product price'], 400);
            }
            
            // Calculate per-item pricing (pre-discount totals)
            $itemSubtotal = $price * $quantity;
            $itemDiscountTotal = $itemDiscount * $quantity;
            $priceAfterItemDiscount = $price - $itemDiscount;
            
            $subtotal += $itemSubtotal;
            $totalItemDiscounts += $itemDiscountTotal;
            
            // Store item details for tax calculation AFTER all discounts
            $cartItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'original_price' => $price,
                'price_after_item_discount' => $priceAfterItemDiscount,
                'line_total_after_item_discount' => $priceAfterItemDiscount * $quantity,
                'tax_class' => $taxClass,
                'tax_status' => $taxStatus
            ];
            
            $lineItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity
            ];
        }
        
        // Server-side validation and calculation of coupon discount
        if (!empty($couponCode)) {
            $couponModel = new Coupon();
            $cartForValidation = [
                'subtotal' => $subtotal - $totalItemDiscounts,
                'items' => array_map(function($item) {
                    return ['product_id' => $item['id']];
                }, $cart),
                'customer_id' => $customerId
            ];
            
            $validation = $couponModel->validateCoupon($couponCode, $cartForValidation);
            if ($validation['valid']) {
                $couponDiscount = $couponModel->calculateDiscount(
                    $validation['coupon'], 
                    $cartForValidation['subtotal']
                );
            } else {
                $this->json(['success' => false, 'message' => 'Coupon validation failed: ' . $validation['message']], 400);
            }
        }
        
        // Server-side validation and calculation of points discount
        // Points balance will be validated and locked within the main order transaction
        if ($pointsRedeemed > 0 && $customerId > 0) {
            $tempDb = Database::getInstance()->getConnection();
            $prefix = DB_PREFIX;
            
            // Pre-check points balance (will be locked and rechecked in main transaction)
            $stmt = $tempDb->prepare("
                SELECT meta_value 
                FROM {$prefix}usermeta 
                WHERE user_id = ? 
                AND meta_key = 'loyalty_points'
                LIMIT 1
            ");
            $stmt->execute([$customerId]);
            $result = $stmt->fetch();
            $currentPoints = intval($result['meta_value'] ?? 0);
            
            if ($pointsRedeemed > $currentPoints) {
                $this->json(['success' => false, 'message' => 'Insufficient loyalty points balance'], 400);
            }
            
            // Get conversion rate
            $settingsStmt = $tempDb->query("
                SELECT setting_value 
                FROM pos_settings 
                WHERE setting_key = 'loyalty_points_per_rupee' 
                LIMIT 1
            ");
            $settingsResult = $settingsStmt->fetch();
            $pointsPerRupee = floatval($settingsResult['setting_value'] ?? 1);
            
            // Calculate points discount
            $pointsDiscount = round(($pointsRedeemed / $pointsPerRupee), 2);
        }
        
        // Validate store credit if payment method is store_credit
        if ($paymentMethodUI === 'store_credit' && $storeCreditApplied) {
            if (!$customerId) {
                $this->json(['success' => false, 'message' => 'Customer required for store credit payment'], 400);
            }
            
            $tempDb = Database::getInstance()->getConnection();
            $stmt = $tempDb->prepare("
                SELECT COALESCE(SUM(balance), 0) as total_balance 
                FROM pos_store_credit 
                WHERE customer_id = ? AND status = 'active'
            ");
            $stmt->execute([$customerId]);
            $creditResult = $stmt->fetch();
            $availableCredit = floatval($creditResult['total_balance'] ?? 0);
            
            if ($availableCredit <= 0) {
                $this->json(['success' => false, 'message' => 'No store credit available for this customer'], 400);
            }
        }
        
        // Calculate discounts in order: line discounts → coupon → loyalty points → manual percentage → store credit
        $subtotalAfterItemDiscounts = $subtotal - $totalItemDiscounts;
        
        // Apply coupon discount
        $subtotalAfterCoupon = $subtotalAfterItemDiscounts - $couponDiscount;
        
        // Apply points discount
        $subtotalAfterPoints = $subtotalAfterCoupon - $pointsDiscount;
        
        // Apply global percentage discount
        $globalDiscount = ($discountPercent > 0) ? calculateDiscount($subtotalAfterPoints, $discountPercent) : 0;
        $subtotalBeforeCredit = $subtotalAfterPoints - $globalDiscount;
        
        // Apply store credit as final discount (if payment method is store_credit)
        if ($paymentMethodUI === 'store_credit' && $storeCreditApplied) {
            $storeCreditDiscount = min($storeCreditAmount, max(0, $subtotalBeforeCredit));
        }
        
        $finalTotal = $subtotalBeforeCredit - $storeCreditDiscount;
        
        $totalDiscounts = $totalItemDiscounts + $couponDiscount + $pointsDiscount + $globalDiscount + $storeCreditDiscount;
        
        // Ensure total is never negative
        $total = max(0, $finalTotal);
        
        // Calculate total cart-level discounts to distribute proportionally
        $cartLevelDiscounts = $couponDiscount + $pointsDiscount + $globalDiscount + $storeCreditDiscount;
        
        // Calculate tax on FINAL discounted prices
        // Tax is already included in MRP, so we extract it from the discounted amount
        $totalTax = 0;
        $itemTaxDetails = [];
        
        foreach ($cartItems as $cartItem) {
            if ($cartItem['tax_status'] !== 'taxable') {
                continue;
            }
            
            // Calculate proportional share of cart-level discounts for this item
            // Guard against division by zero when cart is fully discounted
            $itemProportion = 0;
            if ($subtotalAfterItemDiscounts > 0) {
                $itemProportion = $cartItem['line_total_after_item_discount'] / $subtotalAfterItemDiscounts;
            } elseif (count($cartItems) > 0) {
                // Equal distribution when subtotal is zero
                $itemProportion = 1 / count($cartItems);
            }
            $itemCartDiscount = $cartLevelDiscounts * $itemProportion;
            
            // Final discounted line total for this item - clamp to zero minimum
            $finalLineTotal = max(0, $cartItem['line_total_after_item_discount'] - $itemCartDiscount);
            $finalUnitPrice = ($cartItem['quantity'] > 0 && $finalLineTotal > 0) 
                ? ($finalLineTotal / $cartItem['quantity']) 
                : 0;
            
            // Get tax rate using DISCOUNTED price for price-range rules
            $defaultTaxRate = $taxRates[$cartItem['tax_class']] ?? $taxRates['standard'];
            $taxRate = $this->getApplicableTaxRate(
                $cartItem['product_id'], 
                $cartItem['original_price'],  // Original price for category rules
                $finalUnitPrice,               // Discounted price for price-range rules
                $defaultTaxRate
            );
            
            // Extract tax from tax-inclusive final price (skip if line total is zero or negative)
            $itemTax = 0;
            if ($finalLineTotal > 0 && $taxRate > 0) {
                $taxInfo = $this->extractTaxFromPrice($finalLineTotal, $taxRate);
                $itemTax = $taxInfo['tax_amount'];
            }
            
            $totalTax += $itemTax;
            
            $itemTaxDetails[] = [
                'product_id' => $cartItem['product_id'],
                'quantity' => $cartItem['quantity'],
                'original_price' => $cartItem['original_price'],
                'final_line_total' => round($finalLineTotal, 2),
                'tax_rate' => $taxRate,
                'tax_amount' => round($itemTax, 2)
            ];
        }
        
        // Tax % = (tax_amount / line_total) × 100 - verify this matches expected
        $effectiveTaxPercent = ($total > 0) ? round(($totalTax / $total) * 100, 2) : 0;
        
        // Validate split payments if applicable
        if ($paymentMethodUI === 'split' && !empty($splitPayments)) {
            if (!is_array($splitPayments)) {
                $this->json(['success' => false, 'message' => 'Invalid split payments format'], 400);
            }
            
            $splitTotal = 0;
            foreach ($splitPayments as $payment) {
                if (!isset($payment['amount']) || !isset($payment['method'])) {
                    $this->json(['success' => false, 'message' => 'Invalid split payment data'], 400);
                }
                $splitTotal += floatval($payment['amount']);
            }
            
            // Validate split payment total matches order total (with 0.01 tolerance)
            if (abs($splitTotal - $total) > 0.01) {
                $this->json([
                    'success' => false, 
                    'message' => 'Split payment total does not match order total',
                    'split_total' => $splitTotal,
                    'order_total' => $total
                ], 400);
            }
        }
        
        // Create order via WooCommerce API
        $wcApi = new WooCommerceAPI();
        
        $orderData = [
            'payment_method' => $paymentMethod, // WooCommerce gateway ID
            'payment_method_title' => ucfirst($paymentMethodUI) . ' Payment', // Friendly title
            'set_paid' => true,
            'customer_id' => $customerId,
            'line_items' => $lineItems,
            'meta_data' => [
                [
                    'key' => '_pos_order',
                    'value' => 'yes'
                ],
                [
                    'key' => '_pos_payment_method',
                    'value' => $paymentMethodUI
                ],
                [
                    'key' => '_cashier_id',
                    'value' => Session::get('user_id')
                ],
                [
                    'key' => '_cashier_name',
                    'value' => Session::get('username')
                ],
                [
                    'key' => '_pos_discount_percent',
                    'value' => $discountPercent
                ]
            ]
        ];
        
        // Add discount as a fee line (negative amount)  
        if ($globalDiscount > 0) {
            $orderData['fee_lines'] = [
                [
                    'name' => "POS Discount ({$discountPercent}%)",
                    'total' => strval(-$globalDiscount),  // WooCommerce expects string
                    'tax_class' => '',
                    'tax_status' => 'none',
                    'total_tax' => '0'
                ]
            ];
        }
        
        $order = $wcApi->createOrder($orderData);
        
        if (!$order || !isset($order['id'])) {
            // Preserve cart on failure
            logMessage("Failed to create order via WooCommerce API. Cart preserved.", 'error');
            $this->json([
                'success' => false, 
                'message' => 'Failed to create order. Please check your WooCommerce API connection and try again.',
                'error_details' => 'Could not communicate with WooCommerce. Please verify your API credentials.'
            ], 500);
        }
        
        // Save to POS tables
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            // Generate unique order number
            $orderNumber = 'POS-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert into pos_orders
            $db->query("
                INSERT INTO pos_orders 
                (order_number, user_id, customer_id, wc_order_id, order_status, payment_method, 
                subtotal, discount_amount, tax_amount, total, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ", [
                $orderNumber,
                Session::get('user_id'),
                $customerId,
                $order['id'],
                'completed',
                $paymentMethodUI,
                $subtotal,
                $totalDiscounts,
                $totalTax,
                $total
            ]);
            
            $posOrderId = $db->lastInsertId();
            
            // Insert order items into pos_order_items
            foreach ($cart as $item) {
                $productId = (int) $item['id'];
                $quantity = (int) $item['quantity'];
                
                $product = $productModel->getProduct($productId);
                $price = floatval($product['price']);
                $itemTotal = $price * $quantity;
                
                $db->query("
                    INSERT INTO pos_order_items 
                    (order_id, product_id, product_name, product_sku, quantity, unit_price, line_total, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ", [
                    $posOrderId,
                    $productId,
                    $product['name'],
                    $product['sku'] ?? '',
                    $quantity,
                    $price,
                    $itemTotal
                ]);
            }
            
            // Insert payment records into pos_payments
            if ($paymentMethodUI === 'split' && !empty($splitPayments)) {
                // Handle split payments - multiple payment records
                foreach ($splitPayments as $payment) {
                    $paymentAmount = floatval($payment['amount'] ?? 0);
                    $paymentMethod = sanitize($payment['method'] ?? 'cash');
                    $transactionId = sanitize($payment['reference'] ?? '');
                    
                    if ($paymentAmount > 0) {
                        $db->query("
                            INSERT INTO pos_payments 
                            (order_id, payment_method, amount, transaction_id, payment_status, payment_date, created_at)
                            VALUES (?, ?, ?, ?, 'completed', NOW(), NOW())
                        ", [
                            $posOrderId,
                            $paymentMethod,
                            $paymentAmount,
                            $transactionId
                        ]);
                    }
                }
            } else {
                // Single payment
                $db->query("
                    INSERT INTO pos_payments 
                    (order_id, payment_method, amount, payment_status, payment_date, created_at)
                    VALUES (?, ?, ?, 'completed', NOW(), NOW())
                ", [
                    $posOrderId,
                    $paymentMethodUI,
                    $total
                ]);
            }
            
            // Save coupon usage if coupon was applied
            if (!empty($couponCode) && $couponDiscount > 0) {
                $couponModel = new Coupon();
                $couponModel->recordCouponUsage(
                    $couponCode,
                    $posOrderId,
                    $customerId,
                    Session::get('user_id'),
                    $couponDiscount
                );
            }
            
            // Update loyalty points if points were redeemed
            if ($pointsRedeemed > 0 && $customerId > 0) {
                $prefix = DB_PREFIX;
                
                // Get current points
                $stmt = $db->query("
                    SELECT meta_value 
                    FROM {$prefix}usermeta 
                    WHERE user_id = ? 
                    AND meta_key = 'loyalty_points'
                    LIMIT 1
                ", [$customerId]);
                $result = $stmt->fetch();
                $currentPoints = intval($result['meta_value'] ?? 0);
                
                // Calculate new balance
                $newBalance = max(0, $currentPoints - $pointsRedeemed);
                
                // Update or insert loyalty points in wp_usermeta
                $checkStmt = $db->query("
                    SELECT umeta_id 
                    FROM {$prefix}usermeta 
                    WHERE user_id = ? 
                    AND meta_key = 'loyalty_points'
                ", [$customerId]);
                
                if ($checkStmt->fetch()) {
                    // Update existing
                    $db->query("
                        UPDATE {$prefix}usermeta 
                        SET meta_value = ? 
                        WHERE user_id = ? 
                        AND meta_key = 'loyalty_points'
                    ", [$newBalance, $customerId]);
                } else {
                    // Insert new
                    $db->query("
                        INSERT INTO {$prefix}usermeta 
                        (user_id, meta_key, meta_value) 
                        VALUES (?, 'loyalty_points', ?)
                    ", [$customerId, $newBalance]);
                }
                
                // Record loyalty transaction in pos_loyalty_transactions (if table exists)
                try {
                    $db->query("
                        INSERT INTO pos_loyalty_transactions 
                        (customer_id, order_id, points_change, balance_after, transaction_type, description, created_at)
                        VALUES (?, ?, ?, ?, 'redeem', 'Points redeemed for order', NOW())
                    ", [
                        $customerId,
                        $posOrderId,
                        -$pointsRedeemed,
                        $newBalance
                    ]);
                } catch (Exception $e) {
                    // Table might not exist in old schemas, log but don't fail
                    error_log("Could not record loyalty transaction: " . $e->getMessage());
                }
            }
            
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            error_log("Error saving POS order: " . $e->getMessage());
            // Don't fail the checkout since WC order is already created
        }
        
        // Clear cart only after successful order creation
        Session::remove('cart');
        
        // Log transaction
        logMessage("Order #{$order['id']} created by " . Session::get('username') . " - Total: " . formatCurrency($total), 'info');
        
        $this->json([
            'success' => true,
            'message' => 'Order completed successfully',
            'order_id' => $order['id'],
            'total' => $total,
            'receipt_url' => '/pos/receipt/' . $order['id']
        ]);
    }
    
    /**
     * Show receipt
     */
    public function receipt($orderId) {
        $this->requireAuth();
        
        $orderModel = ModelFactory::getOrder();
        $order = $orderModel->getOrder($orderId);
        
        if (!$order) {
            http_response_code(404);
            echo "Order not found";
            exit;
        }
        
        $orderItems = $orderModel->getOrderItems($orderId);
        
        // Load receipt settings from database
        $db = Database::getInstance();
        $stmt = $db->query("SELECT setting_value FROM pos_settings WHERE setting_key = 'receipt_settings'");
        $settingsRow = $stmt->fetch();
        
        $receiptSettings = [];
        if ($settingsRow && !empty($settingsRow['setting_value'])) {
            $receiptSettings = json_decode($settingsRow['setting_value'], true) ?? [];
        }
        
        // Merge with config defaults
        $config = $this->config;
        $config['pos']['receipt'] = array_merge($config['pos']['receipt'] ?? [], $receiptSettings);
        
        // Use partial to render without sidebar/layout
        $this->partial('pos/receipt', [
            'title' => 'Receipt - Order #' . $orderId,
            'order' => $order,
            'items' => $orderItems,
            'config' => $config
        ]);
    }
    
    public function emailReceipt() {
        $this->requireAuth();
        header('Content-Type: application/json');
        
        $postData = json_decode(file_get_contents('php://input'), true);
        
        $orderId = (int) ($postData['order_id'] ?? 0);
        $email = filter_var($postData['email'] ?? '', FILTER_VALIDATE_EMAIL);
        
        if (!$email) {
            $this->json(['success' => false, 'message' => 'Invalid email address'], 400);
        }
        
        $orderModel = ModelFactory::getOrder();
        $order = $orderModel->getOrder($orderId);
        
        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
        }
        
        $orderItems = $orderModel->getOrderItems($orderId);
        
        // Load receipt settings from database
        $db = Database::getInstance();
        $stmt = $db->query("SELECT setting_value FROM pos_settings WHERE setting_key = 'receipt_settings'");
        $settingsRow = $stmt->fetch();
        
        $receiptSettings = [];
        if ($settingsRow && !empty($settingsRow['setting_value'])) {
            $receiptSettings = json_decode($settingsRow['setting_value'], true) ?? [];
        }
        
        // Merge with config defaults
        $config = $this->config;
        $config['pos']['receipt'] = array_merge($config['pos']['receipt'] ?? [], $receiptSettings);
        
        $items = $orderItems;
        
        ob_start();
        include __DIR__ . '/../views/pos/receipt.php';
        $receiptHtml = ob_get_clean();
        
        $subject = 'Receipt - ' . ($order['order_number'] ?? $order['order_id']);
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . ($this->config['pos']['receipt']['email'] ?? $this->config['app']['email'] ?? 'noreply@bplus-pos.com'),
            'Reply-To: ' . ($this->config['pos']['receipt']['email'] ?? $this->config['app']['email'] ?? 'noreply@bplus-pos.com')
        ];
        
        $success = mail($email, $subject, $receiptHtml, implode("\r\n", $headers));
        
        if ($success) {
            $this->json(['success' => true, 'message' => 'Receipt sent successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to send email'], 500);
        }
    }
    
    /**
     * Process payment and create order
     */
    public function processPayment() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['cart']) || !is_array($input['cart'])) {
            $this->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }
        
        if (empty($input['payment_method'])) {
            $this->json(['success' => false, 'message' => 'Payment method is required'], 400);
        }
        
        try {
            $orderModel = ModelFactory::getOrder();
            
            $customerId = (int) ($input['customer_id'] ?? 0);
            $paymentMethod = sanitize($input['payment_method']);
            $globalDiscount = floatval($input['global_discount'] ?? 0);
            $subtotal = 0;
            $totalItemDiscounts = 0;
            $taxAmount = 0;
            
            foreach ($input['cart'] as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $itemDiscount = floatval($item['discount'] ?? 0);
                $itemTaxRate = floatval($item['tax_rate'] ?? 0);
                
                $subtotal += $itemSubtotal;
                $totalItemDiscounts += $itemDiscount;
                
                $itemNetAmount = $itemSubtotal - $itemDiscount;
                $itemTax = ($itemNetAmount * $itemTaxRate) / 100;
                $taxAmount += $itemTax;
            }
            
            $netSubtotal = $subtotal - $totalItemDiscounts;
            $discountAmount = $totalItemDiscounts + (($netSubtotal * $globalDiscount) / 100);
            $finalSubtotal = $subtotal - $discountAmount;
            $total = $finalSubtotal + $taxAmount;
            
            $orderData = [
                'customer_id' => $customerId,
                'user_id' => Session::get('user_id'),
                'store_id' => 1,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'order_status' => 'completed',
                'customer_name' => $input['customer_name'] ?? 'Walk-in Customer',
                'customer_email' => $input['customer_email'] ?? '',
                'customer_mobile' => $input['customer_mobile'] ?? ''
            ];
            
            $orderId = $orderModel->createOrder($orderData);
            
            $orderItems = [];
            foreach ($input['cart'] as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $itemDiscount = floatval($item['discount'] ?? 0);
                $itemTaxRate = floatval($item['tax_rate'] ?? 0);
                
                $itemNetAmount = $itemSubtotal - $itemDiscount;
                $itemTax = ($itemNetAmount * $itemTaxRate) / 100;
                $itemTotal = $itemNetAmount + $itemTax;
                
                $orderItems[] = [
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'sku' => $item['sku'] ?? '',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount_amount' => $itemDiscount,
                    'tax_rate' => $itemTaxRate,
                    'tax_amount' => $itemTax,
                    'total' => $itemTotal,
                    'hsn_code' => $item['hsn_code'] ?? ''
                ];
            }
            
            $orderModel->addOrderItems($orderId, $orderItems);
            
            $order = $orderModel->getOrder($orderId);
            
            Session::delete('cart');
            
            $this->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'order_id' => $orderId,
                'order_number' => $order['order_number']
            ]);
            
        } catch (Exception $e) {
            error_log("Process payment error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error processing payment: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Hold current order (save for later)
     */
    public function holdOrder() {
        $this->requireAuth();
        
        if (!isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['cart']) || !is_array($input['cart'])) {
            $this->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }
        
        try {
            $reference = $input['reference'] ?? 'Hold-' . time();
            
            $sql = "INSERT INTO pos_held_orders (user_id, reference_name, cart_data, customer_id, notes)
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                Session::get('user_id'),
                $reference,
                json_encode($input['cart']),
                $input['customer_id'] ?? 0,
                $input['notes'] ?? ''
            ]);
            
            Session::delete('cart');
            
            $this->json([
                'success' => true,
                'message' => 'Order held successfully',
                'reference' => $reference
            ]);
            
        } catch (Exception $e) {
            error_log("Hold order error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error holding order'], 500);
        }
    }
    
    /**
     * Check if custom tax system is enabled
     */
    private function isCustomTaxEnabled() {
        static $customTaxEnabled = null;
        
        if ($customTaxEnabled === null) {
            $db = Database::getInstance();
            try {
                $stmt = $db->query("
                    SELECT setting_value 
                    FROM pos_settings 
                    WHERE setting_key = 'custom_tax_enabled'
                ");
                $setting = $stmt->fetch();
                $customTaxEnabled = ($setting && ($setting['setting_value'] === 'true' || $setting['setting_value'] === '1'));
            } catch (Exception $e) {
                error_log("Error checking custom tax setting: " . $e->getMessage());
                $customTaxEnabled = false;
            }
        }
        
        return $customTaxEnabled;
    }
    
    /**
     * Get applicable tax rate for a product based on custom tax rules
     * Optimized for multiple product categories - uses array_intersect for efficient matching
     * @param int $productId - The product ID
     * @param float $originalPrice - The original price (MRP) for category rules
     * @param float $discountedPrice - The discounted price for price-range rules
     * @param float $defaultTaxRate - Default tax rate if no rule matches
     * @return float - Tax rate as decimal (e.g., 0.18 for 18%)
     */
    private function getApplicableTaxRate($productId, $originalPrice, $discountedPrice = null, $defaultTaxRate = 0.18) {
        // If custom tax is not enabled, return default tax rate
        if (!$this->isCustomTaxEnabled()) {
            return $defaultTaxRate;
        }
        
        // Use discounted price for price-range rules, fallback to original if not provided
        $priceForRangeCheck = $discountedPrice ?? $originalPrice;
        
        $db = Database::getInstance();
        $prefix = $db->getPrefix();
        
        try {
            // Get product categories - products can have multiple categories
            $stmt = $db->query("
                SELECT tt.term_id
                FROM {$prefix}term_relationships tr
                INNER JOIN {$prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE tr.object_id = ? AND tt.taxonomy = 'product_cat'
            ", [$productId]);
            $productCategories = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // If product has no categories, return default
            if (empty($productCategories)) {
                error_log("Product {$productId} has no categories, using default tax rate {$defaultTaxRate}");
                return $defaultTaxRate;
            }
            
            // Convert category IDs to integers for strict comparison
            $productCategories = array_map('intval', $productCategories);
            
            // Get all active tax rules ordered by priority
            $stmt = $db->query("
                SELECT id, rule_type, category_id, min_price, max_price, tax_rate, priority
                FROM pos_custom_tax_rules
                WHERE is_active = true
                ORDER BY priority DESC, id DESC
            ");
            $taxRules = $stmt->fetchAll();
            
            // If no rules exist, return default
            if (empty($taxRules)) {
                error_log("No tax rules found for product {$productId}, using default {$defaultTaxRate}");
                return $defaultTaxRate;
            }
            
            error_log("Product {$productId} categories: " . json_encode($productCategories) . ", checking " . count($taxRules) . " rules");
            
            // Find first matching rule (highest priority wins)
            foreach ($taxRules as $rule) {
                // Check category-based rules (use original price for category matching)
                if ($rule['rule_type'] === 'category' && $rule['category_id'] !== null) {
                    // Ensure category_id is integer for strict comparison
                    $ruleCategory = intval($rule['category_id']);
                    
                    // Check if ANY of product's categories match this rule's category
                    if (in_array($ruleCategory, $productCategories, true)) {
                        $matchedRate = floatval($rule['tax_rate']) / 100;
                        error_log("TAX RULE MATCHED for product {$productId}: Rule#{$rule['id']} category_id={$ruleCategory} in [" . implode(',', $productCategories) . "], applying {$rule['tax_rate']}%");
                        return $matchedRate;
                    }
                }
                
                // Check price range-based rules (use DISCOUNTED price for range matching)
                if ($rule['rule_type'] === 'price_range') {
                    $minPrice = $rule['min_price'] ?? null;
                    $maxPrice = $rule['max_price'] ?? null;
                    
                    $matchesMin = ($minPrice === null || $priceForRangeCheck >= floatval($minPrice));
                    $matchesMax = ($maxPrice === null || $priceForRangeCheck <= floatval($maxPrice));
                    
                    if ($matchesMin && $matchesMax) {
                        error_log("Tax rule matched for product {$productId}: Rule#{$rule['id']} applies {$rule['tax_rate']}% (price {$priceForRangeCheck} in range [{$minPrice}, {$maxPrice}])");
                        return floatval($rule['tax_rate']) / 100; // Convert percentage to decimal
                    }
                }
            }
            
            // No rule matched - log product categories for debugging
            $categoriesJson = json_encode($productCategories);
            error_log("No tax rule matched for product {$productId} with categories: {$categoriesJson}, using default {$defaultTaxRate}");
            return $defaultTaxRate;
            
        } catch (Exception $e) {
            error_log("Error getting applicable tax rate for product {$productId}: " . $e->getMessage());
            return $defaultTaxRate;
        }
    }
    
    /**
     * Extract tax from tax-inclusive price
     * Formula: Tax Amount = Price Incl Tax - (Price Incl Tax / (1 + Tax Rate))
     * @param float $priceInclTax - Price including tax (MRP)
     * @param float $taxRate - Tax rate as decimal (e.g., 0.18 for 18%)
     * @return array - ['base_price' => float, 'tax_amount' => float, 'tax_rate' => float]
     */
    private function extractTaxFromPrice($priceInclTax, $taxRate) {
        if ($taxRate <= 0) {
            return [
                'base_price' => $priceInclTax,
                'tax_amount' => 0,
                'tax_rate' => 0
            ];
        }
        
        $basePrice = $priceInclTax / (1 + $taxRate);
        $taxAmount = $priceInclTax - $basePrice;
        
        return [
            'base_price' => round($basePrice, 2),
            'tax_amount' => round($taxAmount, 2),
            'tax_rate' => $taxRate
        ];
    }
}
