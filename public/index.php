<?php
/**
 * B-Plus POS System - Entry Point
 * Main application bootstrap file
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Load configuration
$configFile = ROOT_PATH . '/config/config.php';
if (!file_exists($configFile)) {
    die("Configuration file not found. Please copy config.example.php to config.php and fill in your credentials.");
}

// Load helpers
require_once ROOT_PATH . '/app/helpers/functions.php';
require_once ROOT_PATH . '/app/helpers/Session.php';
require_once ROOT_PATH . '/app/helpers/Router.php';
require_once ROOT_PATH . '/app/helpers/WooCommerceAPI.php';
require_once ROOT_PATH . '/config/database.php';

// Set timezone
$config = require $configFile;
date_default_timezone_set($config['app']['timezone']);

// Start session
Session::start();

// Initialize router
$router = new Router();

// Define routes

// Auth routes
$router->get('/', 'AuthController@login');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@processLogin');
$router->get('/logout', 'AuthController@logout');

// POS routes
$router->get('/pos', 'POSController@index');
$router->post('/pos/add-to-cart', 'POSController@addToCart');
$router->post('/pos/remove-from-cart', 'POSController@removeFromCart');
$router->post('/pos/update-cart', 'POSController@updateCart');
$router->post('/pos/checkout', 'POSController@checkout');
$router->get('/pos/receipt/{orderId}', 'POSController@receipt');

// Product routes
$router->get('/products', 'ProductController@index');
$router->get('/products/search', 'ProductController@search');
$router->get('/products/{id}', 'ProductController@show');

// Customer routes
$router->get('/customers', 'CustomerController@index');
$router->get('/customers/search', 'CustomerController@search');
$router->get('/customers/{id}', 'CustomerController@show');

// Dashboard routes
$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/sales', 'DashboardController@sales');
$router->get('/dashboard/reports', 'DashboardController@reports');

// API routes (for AJAX)
$router->get('/api/products', 'APIController@products');
$router->get('/api/customers/stats', 'APIController@customerStats');
$router->get('/api/customers/search', 'APIController@customerSearch');
$router->get('/api/customers', 'APIController@customers');
$router->post('/api/customers', 'APIController@createCustomer');
$router->post('/api/customers/create', 'APIController@createCustomer');
$router->get('/api/customers/{customerId}', 'APIController@getCustomer');
$router->get('/api/customer/{customerId}', 'APIController@getCustomer');
$router->put('/api/customers/{customerId}', 'APIController@updateCustomer');
$router->put('/api/customer/{customerId}', 'APIController@updateCustomer');
$router->delete('/api/customers/{customerId}', 'APIController@deleteCustomer');
$router->delete('/api/customer/{customerId}', 'APIController@deleteCustomer');
$router->get('/api/customers/{customerId}/points', 'APIController@getCustomerPoints');
$router->get('/api/customer/{customerId}/store-credit', 'APIController@getCustomerStoreCredit');
$router->post('/api/coupons/validate', 'APIController@validateCoupon');
$router->post('/api/points/redeem', 'APIController@redeemPoints');
$router->post('/api/cart/add', 'APIController@addToCart');
$router->post('/api/cart/remove', 'APIController@removeFromCart');
$router->get('/api/cart', 'APIController@getCart');
$router->post('/api/orders/hold', 'APIController@holdOrder');
$router->get('/api/orders/held', 'APIController@getHeldOrders');
$router->post('/api/orders/resume/{orderId}', 'APIController@resumeHeldOrder');
$router->post('/api/orders/cancel/{orderId}', 'APIController@cancelHeldOrder');
$router->get('/api/orders/my-orders', 'APIController@getMyOrders');
$router->get('/api/orders/search', 'APIController@searchOrders');
$router->get('/api/tax-rules', 'APIController@getTaxRules');
$router->get('/api/tax-rules/{id}', 'APIController@getTaxRule');
$router->post('/api/tax-rules', 'APIController@createTaxRule');
$router->put('/api/tax-rules/{id}', 'APIController@updateTaxRule');
$router->delete('/api/tax-rules/{id}', 'APIController@deleteTaxRule');
$router->get('/api/settings/{key}', 'APIController@getSetting');
$router->post('/api/settings/{key}', 'APIController@updateSetting');
$router->get('/api/categories', 'APIController@getCategories');
$router->get('/api/receipt-settings', 'APIController@getReceiptSettings');
$router->post('/api/receipt-settings', 'APIController@updateReceiptSettings');
$router->get('/api/product-tax-rate/{productId}', 'APIController@getProductTaxRate');
$router->get('/api/order/{orderId}', 'APIController@getOrder');

// Admin routes
$router->get('/admin', 'AdminController@index');
$router->get('/admin/dashboard', 'AdminController@index');
$router->get('/admin/options', 'AdminController@options');
$router->get('/admin/users', 'AdminController@users');
$router->post('/admin/users/save', 'AdminController@saveUser');
$router->post('/admin/users/delete/{userId}', 'AdminController@deleteUser');
$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/settings/save', 'AdminController@saveSettings');
$router->post('/admin/send-receipt-email', 'AdminController@sendReceiptEmail');
$router->post('/admin/process-return', 'AdminController@processReturn');
$router->post('/admin/approve-return', 'AdminController@approveReturn');
$router->post('/admin/reject-return', 'AdminController@rejectReturn');
$router->post('/admin/process-refund', 'AdminController@processRefund');
$router->get('/admin/get-return-details', 'AdminController@getReturnDetails');
$router->get('/admin/get-return-receipt', 'AdminController@getReturnReceipt');
$router->get('/admin/reports', 'AdminController@reports');
$router->get('/admin/products', 'AdminController@products');
$router->get('/admin/customers', 'AdminController@customers');
$router->get('/admin/orders', 'AdminController@orders');
$router->get('/admin/returns', 'AdminController@returns');
$router->get('/admin/inventory', 'AdminController@inventory');
$router->get('/admin/barcodes', 'AdminController@barcodes');
$router->get('/admin/inventory-alerts', 'AdminController@inventoryAlerts');
$router->get('/admin/loyalty', 'AdminController@loyalty');
$router->get('/admin/sessions', 'AdminController@sessions');
$router->get('/admin/stores', 'AdminController@stores');
$router->get('/admin/sales-analytics', 'AdminController@salesAnalytics');
$router->get('/admin/tax-rules', 'AdminController@taxRules');
$router->get('/admin/gst-reports', 'AdminController@gstReports');
$router->get('/admin/whatsapp', 'AdminController@whatsapp');
$router->get('/admin/automation', 'AdminController@automation');
$router->get('/admin/discounts', 'AdminController@discounts');
$router->get('/admin/payments', 'AdminController@payments');
$router->get('/admin/receipt-settings', 'AdminController@receiptSettings');
$router->get('/admin/bi-dashboard', 'AdminController@biDashboard');

// Dispatch the request
$router->dispatch();
