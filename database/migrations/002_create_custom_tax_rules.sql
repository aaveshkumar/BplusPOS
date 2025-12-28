-- Custom Tax Rules Table Migration
-- Creates table for conditional tax rules based on categories and price ranges

CREATE TABLE IF NOT EXISTS `pos_custom_tax_rules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rule_name` VARCHAR(255) NOT NULL COMMENT 'Descriptive name for the tax rule',
    `rule_type` ENUM('category', 'price_range') NOT NULL COMMENT 'Type of tax rule',
    `category_id` BIGINT UNSIGNED NULL COMMENT 'WooCommerce category ID (for category-based rules)',
    `min_price` DECIMAL(10,2) NULL COMMENT 'Minimum price for range-based rules',
    `max_price` DECIMAL(10,2) NULL COMMENT 'Maximum price for range-based rules',
    `tax_rate` DECIMAL(5,2) NOT NULL COMMENT 'Tax rate percentage (e.g., 18.00 for 18%)',
    `priority` INT DEFAULT 0 COMMENT 'Higher priority rules are applied first',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'Whether the rule is active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rule_type` (`rule_type`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Custom conditional tax rules';

-- Add custom tax enabled setting to pos_settings
INSERT INTO `pos_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) 
VALUES ('custom_tax_enabled', 'false', 'boolean', 'Enable custom conditional tax rules')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;
