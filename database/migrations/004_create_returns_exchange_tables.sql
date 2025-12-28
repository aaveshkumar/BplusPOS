-- Returns and Exchange Management Tables

CREATE TABLE IF NOT EXISTS pos_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    return_number VARCHAR(50) NOT NULL UNIQUE,
    original_order_id INT NOT NULL,
    customer_id INT NULL,
    return_type ENUM('full_return', 'partial_return', 'exchange') NOT NULL,
    return_reason VARCHAR(255) NOT NULL,
    return_notes TEXT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    refund_amount DECIMAL(10,2) NOT NULL,
    refund_method ENUM('cash', 'card', 'upi', 'store_credit', 'exchange') NOT NULL,
    processed_by INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    approval_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_return_number (return_number),
    KEY idx_original_order (original_order_id),
    KEY idx_customer (customer_id),
    KEY idx_status (status),
    KEY idx_return_date (created_at),
    FOREIGN KEY (original_order_id) REFERENCES pos_orders(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_return_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    return_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    condition_status ENUM('new', 'opened', 'damaged', 'defective') DEFAULT 'new',
    restock BOOLEAN DEFAULT TRUE,
    exchange_product_id INT NULL,
    exchange_quantity INT NULL,
    notes TEXT NULL,
    KEY idx_return (return_id),
    KEY idx_product (product_id),
    FOREIGN KEY (return_id) REFERENCES pos_returns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_store_credit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    credit_number VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    balance DECIMAL(10,2) NOT NULL,
    source_type ENUM('return', 'refund', 'manual') DEFAULT 'return',
    source_id INT NULL,
    issued_by INT NOT NULL,
    expires_at DATE NULL,
    status ENUM('active', 'used', 'expired', 'cancelled') DEFAULT 'active',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_customer (customer_id),
    KEY idx_credit_number (credit_number),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pos_store_credit_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_credit_id INT NOT NULL,
    order_id INT NULL,
    transaction_type ENUM('issue', 'use', 'adjust', 'expire') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    balance_after DECIMAL(10,2) NOT NULL,
    description VARCHAR(255) NULL,
    processed_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_store_credit (store_credit_id),
    KEY idx_order (order_id),
    FOREIGN KEY (store_credit_id) REFERENCES pos_store_credit(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
