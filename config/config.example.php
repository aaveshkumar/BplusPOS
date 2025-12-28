<?php
/**
 * B-Plus POS System - Configuration Template
 * 
 * INSTRUCTIONS:
 * 1. Copy this file to config.php
 * 2. Fill in your WooCommerce database and API credentials
 * 3. Never commit config.php to version control
 */

return [
    // Application Settings
    'app' => [
        'name' => 'B-Plus POS',
        'version' => '1.0.0',
        'environment' => 'development', // development or production
        'timezone' => 'Asia/Kolkata',
        'url' => 'http://localhost:5000',
    ],

    // Session Configuration
    'session' => [
        'name' => 'bplus_pos_session',
        'lifetime' => 3600, // 1 hour in seconds
        'path' => __DIR__ . '/../storage/sessions',
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true,
    ],

    // Remote WooCommerce MySQL Database (for READ operations)
    'database' => [
        'host' => 'your-mysql-host.com',
        'port' => 3306,
        'database' => 'your_woocommerce_db',
        'username' => 'your_db_username',
        'password' => 'your_db_password',
        'charset' => 'utf8mb4',
        'prefix' => 'wp_', // WordPress table prefix (usually wp_)
    ],

    // WooCommerce REST API Credentials (for WRITE operations)
    'woocommerce' => [
        'site_url' => 'https://your-site.com',
        'consumer_key' => 'ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'consumer_secret' => 'cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'api_version' => 'wc/v3',
        'verify_ssl' => true,
    ],

    // POS Settings
    'pos' => [
        'items_per_page' => 20,
        'low_stock_threshold' => 5,
        'default_tax_rate' => 18, // GST rate in percentage
        'currency_symbol' => '₹',
        'currency_code' => 'INR',
        'receipt_footer' => 'Thank you for your business!',
        // Map POS payment methods to WooCommerce gateway IDs
        'payment_gateways' => [
            'cash' => 'cod',  // Cash on Delivery
            'card' => 'bacs', // Direct Bank Transfer (or use 'stripe' if Stripe is configured)
            'upi' => 'cod',   // Map to COD or configure a UPI gateway
        ],
    ],

    // User Roles and Permissions
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => ['all'],
        ],
        'stock_manager' => [
            'name' => 'Stock Manager',
            'permissions' => ['manage_products', 'view_inventory', 'view_reports'],
        ],
        'cashier' => [
            'name' => 'Cashier',
            'permissions' => ['create_orders', 'view_products', 'manage_customers'],
        ],
    ],

    // Security
    'security' => [
        'password_min_length' => 8,
        'session_regenerate' => true,
        'csrf_protection' => true,
    ],

    // Logging
    'logging' => [
        'enabled' => true,
        'path' => __DIR__ . '/../storage/logs',
        'level' => 'info', // debug, info, warning, error
    ],
];
