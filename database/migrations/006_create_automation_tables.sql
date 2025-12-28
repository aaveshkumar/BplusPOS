-- Workflow Automation Tables

CREATE TABLE IF NOT EXISTS pos_automation_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_name VARCHAR(100) NOT NULL,
    trigger_type ENUM('order_completed', 'low_stock', 'customer_registered', 'daily_schedule', 'customer_spend_threshold', 'product_returned') NOT NULL,
    trigger_condition JSON NOT NULL,
    action_type ENUM('send_email', 'send_whatsapp', 'create_task', 'update_customer_group', 'award_loyalty_points', 'generate_purchase_order') NOT NULL,
    action_config JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_trigger (trigger_type),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_automation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_id INT NOT NULL,
    trigger_data JSON NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_rule (rule_id),
    KEY idx_date (executed_at),
    FOREIGN KEY (rule_id) REFERENCES pos_automation_rules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    assigned_to INT NOT NULL,
    due_date DATE NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    KEY idx_assigned (assigned_to),
    KEY idx_status (status),
    KEY idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity_needed INT NOT NULL,
    supplier_id INT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expected_delivery DATE NULL,
    status ENUM('pending', 'ordered', 'received', 'cancelled') DEFAULT 'pending',
    notes TEXT NULL,
    KEY idx_product (product_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default automation rules
INSERT INTO pos_automation_rules (rule_name, trigger_type, trigger_condition, action_type, action_config, is_active) VALUES
('Auto Send Receipt', 'order_completed', '{"field": "order_status", "operator": "equals", "value": "completed"}', 'send_whatsapp', '{"message": "Thank you for your purchase! Order #{{order_number}} confirmed."}', TRUE),
('Auto Award Points', 'order_completed', '{"field": "order_total", "operator": "greater_than", "value": 0}', 'award_loyalty_points', '{"points_per_100": 1}', TRUE),
('Low Stock Alert', 'low_stock', '{"field": "stock_quantity", "operator": "less_than", "value": 10}', 'generate_purchase_order', '{"reorder_quantity": 100}', TRUE),
('VIP Upgrade', 'customer_spend_threshold', '{"field": "total_spent", "operator": "greater_than", "value": 100000}', 'update_customer_group', '{"new_group": "VIP"}', TRUE);
