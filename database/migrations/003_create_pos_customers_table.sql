-- POS Customers Table Migration
-- Stores local customer data for POS system with loyalty, preferences, and full contact details

CREATE TABLE IF NOT EXISTS pos_customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wc_customer_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NULL,
    mobile VARCHAR(20) NOT NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(20) NULL,
    dob DATE NULL,
    anniversary DATE NULL,
    loyalty_points INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    total_orders INT DEFAULT 0,
    status ENUM('active', 'inactive', 'vip') DEFAULT 'active',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_mobile (mobile),
    KEY idx_email (email),
    KEY idx_status (status),
    KEY idx_loyalty_points (loyalty_points),
    KEY idx_wc_customer_id (wc_customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index for fast search
CREATE INDEX idx_customer_search ON pos_customers (first_name, last_name, email, mobile);

-- Customer loyalty transactions table
CREATE TABLE IF NOT EXISTS pos_loyalty_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_id INT NULL,
    points_change INT NOT NULL,
    balance_after INT NOT NULL,
    transaction_type ENUM('earn', 'redeem', 'adjust', 'expire') NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_customer (customer_id),
    KEY idx_order (order_id),
    FOREIGN KEY (customer_id) REFERENCES pos_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
