<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    if (empty($token) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user has permission
 */
function hasPermission($permission) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $config = require __DIR__ . '/../../config/config.php';
    $role = $user['role'] ?? 'cashier';
    
    $permissions = $config['roles'][$role]['permissions'] ?? [];
    
    // Admin has all permissions
    if (in_array('all', $permissions)) return true;
    
    return in_array($permission, $permissions);
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Get base URL
 */
function baseUrl($path = '') {
    $config = require __DIR__ . '/../../config/config.php';
    return rtrim($config['app']['url'], '/') . '/' . ltrim($path, '/');
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    $config = require __DIR__ . '/../../config/config.php';
    return $config['pos']['currency_symbol'] . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Log message
 */
function logMessage($message, $level = 'info') {
    $config = require __DIR__ . '/../../config/config.php';
    
    if (!$config['logging']['enabled']) return;
    
    $logFile = $config['logging']['path'] . '/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Generate random string
 */
function generateRandomString($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get request method
 */
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * Is POST request
 */
function isPost() {
    return getRequestMethod() === 'POST';
}

/**
 * Is GET request
 */
function isGet() {
    return getRequestMethod() === 'GET';
}

/**
 * Get POST data
 */
function getPost($key = null, $default = null) {
    if ($key === null) {
        return $_POST;
    }
    return $_POST[$key] ?? $default;
}

/**
 * Get GET data
 */
function getGet($key = null, $default = null) {
    if ($key === null) {
        return $_GET;
    }
    return $_GET[$key] ?? $default;
}

/**
 * Calculate discount
 */
function calculateDiscount($price, $discountPercent) {
    return $price * ($discountPercent / 100);
}

/**
 * Calculate tax
 */
function calculateTax($price, $taxPercent) {
    return $price * ($taxPercent / 100);
}

/**
 * Generate barcode (simple numeric)
 */
function generateBarcode() {
    return str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
}

/**
 * Get query string parameter (alias for getGet)
 */
function getQuery($key = null, $default = null) {
    return getGet($key, $default);
}

/**
 * Get table name based on database type
 */
function getTableName($table) {
    $isStandalone = getenv('DATABASE_TYPE') === 'standalone';
    
    $tableMap = [
        'settings' => $isStandalone ? 'settings' : 'pos_settings',
        'orders' => $isStandalone ? 'orders' : 'pos_orders',
        'order_items' => $isStandalone ? 'order_items' : 'pos_order_items',
        'sessions' => $isStandalone ? 'sessions' : 'pos_sessions',
        'returns' => $isStandalone ? 'returns' : 'pos_returns',
        'store_credits' => $isStandalone ? 'store_credits' : 'pos_store_credits',
        'store_credit_transactions' => $isStandalone ? 'store_credit_transactions' : 'pos_store_credit_transactions',
    ];
    
    return $tableMap[$table] ?? $table;
}
