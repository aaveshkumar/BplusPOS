-- WhatsApp Integration Tables

CREATE TABLE IF NOT EXISTS pos_whatsapp_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mobile_number VARCHAR(20) NOT NULL,
    message_text TEXT NOT NULL,
    message_type ENUM('order_confirmation', 'receipt', 'low_stock_alert', 'daily_summary', 'return_status', 'promotion', 'birthday', 'general') DEFAULT 'general',
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    api_response TEXT NULL,
    reference_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_mobile (mobile_number),
    KEY idx_status (status),
    KEY idx_type (message_type),
    KEY idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_whatsapp_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL UNIQUE,
    template_type VARCHAR(50) NOT NULL,
    template_text TEXT NOT NULL,
    variables JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_name (template_name),
    KEY idx_type (template_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_whatsapp_opt_in (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    opt_in_status BOOLEAN DEFAULT TRUE,
    opt_in_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    opt_out_date TIMESTAMP NULL,
    KEY idx_customer (customer_id),
    KEY idx_mobile (mobile_number),
    KEY idx_status (opt_in_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default templates
INSERT INTO pos_whatsapp_templates (template_name, template_type, template_text, variables) VALUES
('order_confirmation', 'order', '*Order Confirmed!* ✅\n\nOrder #: *{{order_number}}*\nDate: {{order_date}}\nTotal: ₹*{{total}}*\n\nThank you for your purchase!', '["order_number", "order_date", "total"]'),
('low_stock_alert', 'inventory', '⚠️ *Low Stock Alert*\n\nProduct: *{{product_name}}*\nCurrent Stock: *{{stock}} units*\n\nAction required: Please reorder.', '["product_name", "stock"]'),
('birthday_wish', 'customer', '🎂 *Happy Birthday {{name}}!* 🎉\n\nWishing you a fantastic day!\n\n🎁 Special Gift: Use code *BIRTHDAY10* for 10% off!\n\nCelebrate with us! 🥳', '["name"]');
