<?php
/**
 * Setup Standalone Database
 * Creates all necessary tables for the POS system on a non-WordPress database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>B-Plus POS - Standalone Database Setup</h1>\n";
echo "<pre>\n";

$host = getenv('STANDALONE_DB_HOST') ?: '193.203.184.150';
$port = getenv('STANDALONE_DB_PORT') ?: '3306';
$database = getenv('STANDALONE_DB_NAME') ?: 'u647904474_bplusshop';
$username = getenv('STANDALONE_DB_USER') ?: 'u647904474_bplusshop';
$password = getenv('STANDALONE_DB_PASSWORD') ?: '';

echo "Connecting to database: {$database}@{$host}:{$port}\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Connected successfully!\n\n";
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $tables = [
        'categories' => "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            description TEXT,
            parent_id INT DEFAULT 0,
            image_url VARCHAR(500),
            display_order INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_parent (parent_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'products' => "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            sku VARCHAR(100),
            barcode VARCHAR(100),
            description TEXT,
            short_description TEXT,
            regular_price DECIMAL(10,2) DEFAULT 0.00,
            sale_price DECIMAL(10,2),
            cost_price DECIMAL(10,2) DEFAULT 0.00,
            price DECIMAL(10,2) DEFAULT 0.00,
            stock_quantity INT DEFAULT 0,
            stock_status ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock',
            manage_stock TINYINT(1) DEFAULT 1,
            low_stock_threshold INT DEFAULT 5,
            weight DECIMAL(10,2),
            length DECIMAL(10,2),
            width DECIMAL(10,2),
            height DECIMAL(10,2),
            tax_status ENUM('taxable', 'shipping', 'none') DEFAULT 'taxable',
            tax_class VARCHAR(100) DEFAULT 'standard',
            category_id INT,
            image_url VARCHAR(500),
            gallery_images TEXT,
            status ENUM('publish', 'draft', 'trash') DEFAULT 'publish',
            product_type ENUM('simple', 'variable', 'grouped', 'external') DEFAULT 'simple',
            featured TINYINT(1) DEFAULT 0,
            virtual TINYINT(1) DEFAULT 0,
            downloadable TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_sku (sku),
            INDEX idx_barcode (barcode),
            INDEX idx_slug (slug),
            INDEX idx_category (category_id),
            INDEX idx_status (status),
            INDEX idx_stock_status (stock_status),
            INDEX idx_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'product_variations' => "CREATE TABLE IF NOT EXISTS product_variations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            parent_id INT NOT NULL,
            sku VARCHAR(100),
            barcode VARCHAR(100),
            regular_price DECIMAL(10,2) DEFAULT 0.00,
            sale_price DECIMAL(10,2),
            price DECIMAL(10,2) DEFAULT 0.00,
            stock_quantity INT DEFAULT 0,
            stock_status ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock',
            attributes TEXT,
            image_url VARCHAR(500),
            status ENUM('publish', 'draft', 'trash') DEFAULT 'publish',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_parent (parent_id),
            INDEX idx_sku (sku),
            INDEX idx_barcode (barcode)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            display_name VARCHAR(255),
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            phone VARCHAR(50),
            role ENUM('admin', 'manager', 'cashier', 'stock_manager') DEFAULT 'cashier',
            status ENUM('active', 'inactive') DEFAULT 'active',
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_role (role),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'customers' => "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255),
            phone VARCHAR(50),
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            display_name VARCHAR(255),
            company VARCHAR(255),
            address_1 VARCHAR(255),
            address_2 VARCHAR(255),
            city VARCHAR(100),
            state VARCHAR(100),
            postcode VARCHAR(20),
            country VARCHAR(10) DEFAULT 'IN',
            gstin VARCHAR(50),
            loyalty_points INT DEFAULT 0,
            total_spent DECIMAL(12,2) DEFAULT 0.00,
            order_count INT DEFAULT 0,
            notes TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_phone (phone),
            INDEX idx_name (first_name, last_name),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'tax_rates' => "CREATE TABLE IF NOT EXISTS tax_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            rate DECIMAL(6,4) NOT NULL,
            tax_class VARCHAR(100) DEFAULT 'standard',
            country VARCHAR(10) DEFAULT 'IN',
            state VARCHAR(100) DEFAULT '*',
            city VARCHAR(100) DEFAULT '*',
            postcode VARCHAR(20) DEFAULT '*',
            priority INT DEFAULT 1,
            compound TINYINT(1) DEFAULT 0,
            shipping TINYINT(1) DEFAULT 1,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_class (tax_class),
            INDEX idx_country_state (country, state),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'orders' => "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            customer_id INT,
            user_id INT,
            status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded', 'on-hold') DEFAULT 'pending',
            subtotal DECIMAL(12,2) DEFAULT 0.00,
            discount_total DECIMAL(12,2) DEFAULT 0.00,
            discount_type ENUM('fixed', 'percent') DEFAULT 'fixed',
            tax_total DECIMAL(12,2) DEFAULT 0.00,
            shipping_total DECIMAL(12,2) DEFAULT 0.00,
            total DECIMAL(12,2) DEFAULT 0.00,
            payment_method VARCHAR(50),
            payment_method_title VARCHAR(100),
            transaction_id VARCHAR(255),
            customer_note TEXT,
            internal_note TEXT,
            billing_first_name VARCHAR(100),
            billing_last_name VARCHAR(100),
            billing_email VARCHAR(255),
            billing_phone VARCHAR(50),
            billing_address_1 VARCHAR(255),
            billing_city VARCHAR(100),
            billing_state VARCHAR(100),
            billing_postcode VARCHAR(20),
            billing_country VARCHAR(10) DEFAULT 'IN',
            store_id INT DEFAULT 1,
            register_id INT,
            session_id INT,
            source ENUM('pos', 'online', 'api') DEFAULT 'pos',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_id),
            INDEX idx_user (user_id),
            INDEX idx_status (status),
            INDEX idx_created (created_at),
            INDEX idx_order_number (order_number)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT,
            variation_id INT,
            product_name VARCHAR(255) NOT NULL,
            sku VARCHAR(100),
            quantity INT DEFAULT 1,
            unit_price DECIMAL(10,2) DEFAULT 0.00,
            subtotal DECIMAL(10,2) DEFAULT 0.00,
            discount DECIMAL(10,2) DEFAULT 0.00,
            tax_amount DECIMAL(10,2) DEFAULT 0.00,
            tax_class VARCHAR(100),
            total DECIMAL(10,2) DEFAULT 0.00,
            meta_data TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order (order_id),
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'coupons' => "CREATE TABLE IF NOT EXISTS coupons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            discount_type ENUM('fixed_cart', 'percent', 'fixed_product') DEFAULT 'fixed_cart',
            amount DECIMAL(10,2) DEFAULT 0.00,
            minimum_amount DECIMAL(10,2),
            maximum_amount DECIMAL(10,2),
            usage_limit INT,
            usage_count INT DEFAULT 0,
            usage_limit_per_user INT,
            exclude_sale_items TINYINT(1) DEFAULT 0,
            product_ids TEXT,
            excluded_product_ids TEXT,
            category_ids TEXT,
            excluded_category_ids TEXT,
            date_expires DATETIME,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'loyalty_points' => "CREATE TABLE IF NOT EXISTS loyalty_points (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            points INT DEFAULT 0,
            points_value DECIMAL(10,2) DEFAULT 0.00,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE INDEX idx_customer (customer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'loyalty_transactions' => "CREATE TABLE IF NOT EXISTS loyalty_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            order_id INT,
            points INT NOT NULL,
            type ENUM('earned', 'redeemed', 'adjusted', 'expired') DEFAULT 'earned',
            description VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_customer (customer_id),
            INDEX idx_order (order_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'stores' => "CREATE TABLE IF NOT EXISTS stores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(50) UNIQUE,
            address VARCHAR(500),
            city VARCHAR(100),
            state VARCHAR(100),
            postcode VARCHAR(20),
            country VARCHAR(10) DEFAULT 'IN',
            phone VARCHAR(50),
            email VARCHAR(255),
            gstin VARCHAR(50),
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'pos_sessions' => "CREATE TABLE IF NOT EXISTS pos_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            register_id INT,
            store_id INT DEFAULT 1,
            opening_cash DECIMAL(10,2) DEFAULT 0.00,
            closing_cash DECIMAL(10,2),
            expected_cash DECIMAL(10,2),
            cash_difference DECIMAL(10,2),
            total_sales DECIMAL(12,2) DEFAULT 0.00,
            total_orders INT DEFAULT 0,
            status ENUM('open', 'closed') DEFAULT 'open',
            opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            closed_at TIMESTAMP NULL,
            notes TEXT,
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'payment_methods' => "CREATE TABLE IF NOT EXISTS payment_methods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            code VARCHAR(50) NOT NULL UNIQUE,
            description VARCHAR(255),
            icon VARCHAR(100),
            enabled TINYINT(1) DEFAULT 1,
            display_order INT DEFAULT 0,
            settings TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_enabled (enabled)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'settings' => "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value LONGTEXT,
            setting_group VARCHAR(50) DEFAULT 'general',
            autoload TINYINT(1) DEFAULT 1,
            INDEX idx_key (setting_key),
            INDEX idx_group (setting_group)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'inventory_logs' => "CREATE TABLE IF NOT EXISTS inventory_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            variation_id INT,
            user_id INT,
            quantity_change INT NOT NULL,
            quantity_before INT,
            quantity_after INT,
            type ENUM('sale', 'return', 'adjustment', 'purchase', 'transfer') DEFAULT 'adjustment',
            reference_id INT,
            reference_type VARCHAR(50),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_product (product_id),
            INDEX idx_type (type),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'barcodes' => "CREATE TABLE IF NOT EXISTS barcodes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT,
            variation_id INT,
            barcode_value VARCHAR(100) NOT NULL,
            barcode_type ENUM('EAN13', 'EAN8', 'UPC', 'CODE128', 'CODE39', 'QR') DEFAULT 'EAN13',
            generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE INDEX idx_barcode (barcode_value),
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            echo "Created table: {$tableName}\n";
            $successCount++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "Table already exists: {$tableName}\n";
                $successCount++;
            } else {
                echo "Error creating {$tableName}: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
    echo "\nInserting default data...\n";
    
    try {
        $pdo->exec("INSERT IGNORE INTO stores (name, code, status) VALUES ('Main Store', 'MAIN', 'active')");
        echo "Inserted default store\n";
        
        $pdo->exec("INSERT IGNORE INTO payment_methods (name, code, enabled, display_order) VALUES 
            ('Cash', 'cash', 1, 1),
            ('Card', 'card', 1, 2),
            ('UPI', 'upi', 1, 3),
            ('Store Credit', 'store_credit', 1, 4)");
        echo "Inserted payment methods\n";
        
        $pdo->exec("INSERT IGNORE INTO tax_rates (name, rate, tax_class, country) VALUES 
            ('GST 5%', 5.0000, 'reduced-rate', 'IN'),
            ('GST 12%', 12.0000, 'standard', 'IN'),
            ('GST 18%', 18.0000, 'standard', 'IN'),
            ('GST 28%', 28.0000, 'luxury', 'IN')");
        echo "Inserted tax rates\n";
        
        $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value, setting_group) VALUES 
            ('store_name', 'B-Plus POS', 'general'),
            ('currency_symbol', '₹', 'general'),
            ('currency_code', 'INR', 'general'),
            ('default_tax_rate', '18', 'tax'),
            ('receipt_footer', 'Thank you for your business!', 'receipt'),
            ('low_stock_threshold', '5', 'inventory'),
            ('loyalty_points_per_rupee', '1', 'loyalty'),
            ('loyalty_points_value', '0.10', 'loyalty')");
        echo "Inserted settings\n";
        
    } catch (PDOException $e) {
        echo "Error inserting default data: " . $e->getMessage() . "\n";
        $errorCount++;
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n========================================\n";
    echo "Setup completed!\n";
    echo "Tables created: {$successCount}\n";
    echo "Errors: {$errorCount}\n";
    echo "========================================\n";
    
    echo "\nVerifying tables...\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        echo "  - {$table} ({$count} rows)\n";
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n</pre>";
echo "<p><strong>Database setup complete!</strong></p>";
echo "<p><a href='/migrate-woocommerce.php'>Next: Migrate WooCommerce Data</a></p>";
