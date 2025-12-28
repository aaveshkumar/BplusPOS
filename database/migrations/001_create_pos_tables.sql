-- B-Plus POS Database Schema
-- Creates tables for advanced POS functionality
-- Run this migration to add POS-specific tables to WooCommerce database

-- =====================================================
-- 1. POS Sessions Table (Cashier Shifts)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_sessions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'WordPress user ID (cashier)',
    `store_id` BIGINT UNSIGNED DEFAULT 1 COMMENT 'Store/outlet ID for multi-store support',
    `session_start` DATETIME NOT NULL,
    `session_end` DATETIME NULL,
    `opening_cash` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Starting cash in drawer',
    `closing_cash` DECIMAL(10,2) NULL COMMENT 'Ending cash in drawer',
    `expected_cash` DECIMAL(10,2) NULL COMMENT 'Expected cash based on sales',
    `cash_difference` DECIMAL(10,2) NULL COMMENT 'Cash over/short',
    `total_sales` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Total sales amount',
    `total_orders` INT UNSIGNED DEFAULT 0 COMMENT 'Number of orders processed',
    `status` ENUM('open', 'closed', 'suspended') DEFAULT 'open',
    `notes` TEXT NULL COMMENT 'Session notes',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_store_id` (`store_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_session_start` (`session_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cashier shift/session tracking';

-- =====================================================
-- 2. POS Orders Table (Local order tracking)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_orders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `wc_order_id` BIGINT UNSIGNED NULL COMMENT 'WooCommerce order ID after sync',
    `order_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'POS order number',
    `session_id` BIGINT UNSIGNED NULL COMMENT 'Cashier session ID',
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Cashier user ID',
    `customer_id` BIGINT UNSIGNED NULL COMMENT 'WooCommerce customer ID',
    `store_id` BIGINT UNSIGNED DEFAULT 1,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
    `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
    `tax_rate` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Tax rate percentage',
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('pending', 'paid', 'partial', 'refunded', 'failed') DEFAULT 'pending',
    `order_status` ENUM('pending', 'processing', 'completed', 'held', 'cancelled', 'refunded') DEFAULT 'pending',
    `order_type` ENUM('sale', 'return', 'exchange', 'void') DEFAULT 'sale',
    `payment_method` VARCHAR(50) NULL COMMENT 'Primary payment method',
    `customer_name` VARCHAR(255) NULL,
    `customer_email` VARCHAR(255) NULL,
    `customer_phone` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `synced_to_wc` TINYINT(1) DEFAULT 0 COMMENT 'Synced to WooCommerce',
    `synced_at` DATETIME NULL,
    `offline_created` TINYINT(1) DEFAULT 0 COMMENT 'Created in offline mode',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_wc_order_id` (`wc_order_id`),
    INDEX `idx_session_id` (`session_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_customer_id` (`customer_id`),
    INDEX `idx_store_id` (`store_id`),
    INDEX `idx_payment_status` (`payment_status`),
    INDEX `idx_order_status` (`order_status`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`session_id`) REFERENCES `pos_sessions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='POS order records';

-- =====================================================
-- 3. POS Order Items Table
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_order_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL COMMENT 'WooCommerce product ID',
    `variation_id` BIGINT UNSIGNED NULL COMMENT 'Product variation ID if applicable',
    `product_name` VARCHAR(255) NOT NULL,
    `product_sku` VARCHAR(100) NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
    `line_total` DECIMAL(10,2) NOT NULL,
    `cost_price` DECIMAL(10,2) NULL COMMENT 'Product cost for profit calculation',
    `notes` TEXT NULL COMMENT 'Item-specific notes',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_product_id` (`product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `pos_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Line items for POS orders';

-- =====================================================
-- 4. POS Payments Table (Multi-payment support)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `session_id` BIGINT UNSIGNED NULL,
    `payment_method` VARCHAR(50) NOT NULL COMMENT 'cash, card, upi, wallet, etc',
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) DEFAULT 'INR',
    `transaction_id` VARCHAR(100) NULL COMMENT 'External transaction reference',
    `payment_status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
    `gateway_response` TEXT NULL COMMENT 'Payment gateway response JSON',
    `notes` TEXT NULL,
    `payment_date` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_session_id` (`session_id`),
    INDEX `idx_payment_method` (`payment_method`),
    INDEX `idx_payment_date` (`payment_date`),
    FOREIGN KEY (`order_id`) REFERENCES `pos_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`session_id`) REFERENCES `pos_sessions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payment records with split payment support';

-- =====================================================
-- 5. Held Orders Table (Save for later)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_held_orders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `reference_name` VARCHAR(100) NOT NULL COMMENT 'Customer name or identifier',
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Cashier who held the order',
    `store_id` BIGINT UNSIGNED DEFAULT 1,
    `customer_id` BIGINT UNSIGNED NULL,
    `cart_data` LONGTEXT NOT NULL COMMENT 'JSON cart data',
    `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
    `notes` TEXT NULL,
    `held_at` DATETIME NOT NULL,
    `expires_at` DATETIME NULL COMMENT 'Auto-delete after this date',
    `status` ENUM('active', 'resumed', 'cancelled') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_store_id` (`store_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_held_at` (`held_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Orders saved for later (hold function)';

-- =====================================================
-- 6. Audit Logs Table (Security & accountability)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `session_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'login, logout, sale, refund, price_override, etc',
    `entity_type` VARCHAR(50) NULL COMMENT 'order, product, customer, session',
    `entity_id` BIGINT UNSIGNED NULL,
    `old_value` TEXT NULL COMMENT 'Before change (JSON)',
    `new_value` TEXT NULL COMMENT 'After change (JSON)',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_session_id` (`session_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Security and activity audit trail';

-- =====================================================
-- 7. Customer Extended Table (Loyalty & preferences)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_customers_extended` (
    `customer_id` BIGINT UNSIGNED NOT NULL PRIMARY KEY COMMENT 'WooCommerce user ID',
    `loyalty_points` INT UNSIGNED DEFAULT 0,
    `loyalty_tier` ENUM('none', 'bronze', 'silver', 'gold', 'platinum') DEFAULT 'none',
    `customer_group` VARCHAR(50) NULL COMMENT 'Wholesale, retail, VIP, etc',
    `credit_limit` DECIMAL(10,2) DEFAULT 0.00,
    `current_credit` DECIMAL(10,2) DEFAULT 0.00,
    `tax_exempt` TINYINT(1) DEFAULT 0,
    `tax_id` VARCHAR(50) NULL COMMENT 'GST number or tax ID',
    `preferred_payment_method` VARCHAR(50) NULL,
    `total_purchases` DECIMAL(10,2) DEFAULT 0.00,
    `total_orders` INT UNSIGNED DEFAULT 0,
    `average_order_value` DECIMAL(10,2) DEFAULT 0.00,
    `last_purchase_date` DATETIME NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_loyalty_tier` (`loyalty_tier`),
    INDEX `idx_customer_group` (`customer_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Extended customer data for POS';

-- =====================================================
-- 8. POS Stores Table (Multi-store support)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_stores` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `store_name` VARCHAR(255) NOT NULL,
    `store_code` VARCHAR(50) UNIQUE NOT NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `postal_code` VARCHAR(20) NULL,
    `country` VARCHAR(100) DEFAULT 'India',
    `phone` VARCHAR(50) NULL,
    `email` VARCHAR(255) NULL,
    `tax_id` VARCHAR(50) NULL COMMENT 'GST number',
    `receipt_header` TEXT NULL,
    `receipt_footer` TEXT NULL,
    `receipt_logo_url` VARCHAR(500) NULL,
    `settings` LONGTEXT NULL COMMENT 'Store-specific settings JSON',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Store/outlet management';

-- Insert default store
INSERT INTO `pos_stores` (`id`, `store_name`, `store_code`, `status`) 
VALUES (1, 'Main Store', 'MAIN001', 'active')
ON DUPLICATE KEY UPDATE `store_name` = `store_name`;

-- =====================================================
-- 9. Product Barcode Table (Barcode management)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_product_barcodes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `barcode` VARCHAR(100) NOT NULL UNIQUE,
    `barcode_type` ENUM('EAN13', 'EAN8', 'UPC', 'CODE128', 'QR', 'CUSTOM') DEFAULT 'EAN13',
    `is_primary` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_product_id` (`product_id`),
    INDEX `idx_barcode` (`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product barcode mappings';

-- =====================================================
-- 10. Coupons Usage Log (Track coupon usage)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_coupon_usage` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `coupon_code` VARCHAR(100) NOT NULL,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Cashier who applied coupon',
    `discount_amount` DECIMAL(10,2) NOT NULL,
    `used_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_coupon_code` (`coupon_code`),
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_customer_id` (`customer_id`),
    INDEX `idx_used_at` (`used_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Coupon usage tracking';

-- =====================================================
-- 11. POS Settings Table (System configuration)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pos_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` LONGTEXT NULL,
    `setting_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    `description` TEXT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='POS system settings';

-- Insert default settings
INSERT INTO `pos_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('tax_rate', '18.00', 'number', 'Default GST/tax rate percentage'),
('receipt_print_auto', 'false', 'boolean', 'Auto-print receipt after checkout'),
('low_stock_threshold', '10', 'number', 'Alert when stock falls below this'),
('session_timeout', '8', 'number', 'Auto-close session after hours'),
('offline_mode_enabled', 'true', 'boolean', 'Enable offline mode support'),
('loyalty_points_per_rupee', '1', 'number', 'Loyalty points earned per rupee spent')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;

-- =====================================================
-- Migration Complete
-- =====================================================
-- All POS tables created successfully
-- Next steps:
-- 1. Run this migration: mysql -u user -p database < 001_create_pos_tables.sql
-- 2. Verify tables: SHOW TABLES LIKE 'pos_%';
-- 3. Check table structure: DESCRIBE pos_orders;
