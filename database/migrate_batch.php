<?php
/**
 * Batch Migration Script - Resumable CLI migration from WooCommerce to Standalone
 * Usage: php database/migrate_batch.php [entity] [batch_size]
 * Entities: customers, orders, products
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

$entity = $argv[1] ?? 'all';
$batchSize = (int)($argv[2] ?? 500);

$wpHost = "srv1642.hstgr.io";
$wpDb = "u647904474_AMSJh";
$wpUser = "u647904474_tTClQ";
$wpPass = "QXDqjf4Isk";

$saHost = getenv('STANDALONE_DB_HOST') ?: '193.203.184.150';
$saPort = getenv('STANDALONE_DB_PORT') ?: '3306';
$saDb = getenv('STANDALONE_DB_NAME') ?: 'u647904474_bplusshop';
$saUser = getenv('STANDALONE_DB_USER') ?: 'u647904474_bplusshop';
$saPass = getenv('STANDALONE_DB_PASSWORD') ?: 'Bplusshop@1234#';

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => true,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $wpPdo = new PDO("mysql:host=$wpHost;dbname=$wpDb;charset=utf8mb4", $wpUser, $wpPass, $pdoOptions);
    $saPdo = new PDO("mysql:host=$saHost;port=$saPort;dbname=$saDb;charset=utf8mb4", $saUser, $saPass, $pdoOptions);
    echo "Connected to both databases\n\n";
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

function migrateCustomers($wpPdo, $saPdo, $batchSize) {
    echo "=== MIGRATING CUSTOMERS ===\n";
    
    $stmt = $saPdo->query("SELECT COALESCE(MAX(wp_user_id), 0) as last_id FROM customers WHERE wp_user_id IS NOT NULL");
    $lastId = (int)$stmt->fetch()['last_id'];
    echo "Resuming from wp_user_id > $lastId\n";
    
    $stmt = $wpPdo->prepare("
        SELECT 
            u.ID, u.user_login, u.user_email, u.display_name, u.user_registered,
            MAX(CASE WHEN um.meta_key = 'first_name' THEN um.meta_value END) as first_name,
            MAX(CASE WHEN um.meta_key = 'last_name' THEN um.meta_value END) as last_name,
            MAX(CASE WHEN um.meta_key = 'billing_phone' THEN um.meta_value END) as phone,
            MAX(CASE WHEN um.meta_key = 'billing_address_1' THEN um.meta_value END) as address_1,
            MAX(CASE WHEN um.meta_key = 'billing_city' THEN um.meta_value END) as city,
            MAX(CASE WHEN um.meta_key = 'billing_state' THEN um.meta_value END) as state,
            MAX(CASE WHEN um.meta_key = 'billing_postcode' THEN um.meta_value END) as postcode,
            MAX(CASE WHEN um.meta_key = 'billing_country' THEN um.meta_value END) as country,
            MAX(CASE WHEN um.meta_key = 'loyalty_program_balance' THEN um.meta_value END) as loyalty_points
        FROM wp_users u
        LEFT JOIN wp_usermeta um ON u.ID = um.user_id
        WHERE u.ID > ?
        GROUP BY u.ID
        ORDER BY u.ID
        LIMIT ?
    ");
    $stmt->bindValue(1, $lastId, PDO::PARAM_INT);
    $stmt->bindValue(2, $batchSize, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "No more customers to migrate\n";
        return 0;
    }
    
    $insertStmt = $saPdo->prepare("
        INSERT INTO customers 
        (wp_user_id, email, phone, first_name, last_name, display_name, address_1, city, state, postcode, country, loyalty_points, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
        ON DUPLICATE KEY UPDATE email = VALUES(email)
    ");
    
    $migrated = 0;
    foreach ($users as $user) {
        try {
            $insertStmt->execute([
                $user['ID'],
                $user['user_email'],
                $user['phone'] ?: '',
                $user['first_name'] ?: '',
                $user['last_name'] ?: '',
                $user['display_name'] ?: $user['user_login'],
                $user['address_1'] ?: '',
                $user['city'] ?: '',
                $user['state'] ?: '',
                $user['postcode'] ?: '',
                $user['country'] ?: 'IN',
                intval($user['loyalty_points'] ?: 0),
                $user['user_registered']
            ]);
            $migrated++;
        } catch (Exception $e) {
            // Skip errors
        }
    }
    
    echo "Migrated $migrated customers this batch\n";
    return $migrated;
}

function migrateOrders($wpPdo, $saPdo, $batchSize) {
    echo "=== MIGRATING ORDERS ===\n";
    
    $stmt = $saPdo->query("SELECT COALESCE(MAX(wp_order_id), 0) as last_id FROM orders WHERE wp_order_id IS NOT NULL");
    $lastId = (int)$stmt->fetch()['last_id'];
    echo "Resuming from wp_order_id > $lastId\n";
    
    // First add wp_order_id column if not exists
    try {
        $saPdo->exec("ALTER TABLE orders ADD COLUMN wp_order_id INT NULL AFTER id");
    } catch (Exception $e) {}
    try {
        $saPdo->exec("ALTER TABLE orders ADD COLUMN wp_customer_id INT NULL AFTER customer_id");
    } catch (Exception $e) {}
    
    $stmt = $wpPdo->prepare("
        SELECT 
            o.id, o.order_number, o.customer_id, o.user_id, o.subtotal, o.tax_total,
            o.discount_total, o.total, o.order_status, o.payment_method, o.notes,
            o.created_at
        FROM pos_orders o
        WHERE o.id > ?
        ORDER BY o.id
        LIMIT ?
    ");
    $stmt->bindValue(1, $lastId, PDO::PARAM_INT);
    $stmt->bindValue(2, $batchSize, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orders)) {
        echo "No more orders to migrate\n";
        return 0;
    }
    
    $insertOrderStmt = $saPdo->prepare("
        INSERT INTO orders 
        (wp_order_id, order_number, customer_id, wp_customer_id, user_id, subtotal, tax, discount, total, status, payment_method, notes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $insertItemStmt = $saPdo->prepare("
        INSERT INTO order_items 
        (order_id, product_id, product_name, sku, quantity, unit_price, line_total, tax_amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $migrated = 0;
    foreach ($orders as $order) {
        try {
            // Find matching standalone customer
            $custStmt = $saPdo->prepare("SELECT id FROM customers WHERE wp_user_id = ?");
            $custStmt->execute([$order['customer_id']]);
            $customer = $custStmt->fetch();
            $customerId = $customer ? $customer['id'] : null;
            
            $insertOrderStmt->execute([
                $order['id'],
                $order['order_number'],
                $customerId,
                $order['customer_id'],
                $order['user_id'],
                $order['subtotal'],
                $order['tax_total'],
                $order['discount_total'],
                $order['total'],
                $order['order_status'],
                $order['payment_method'],
                $order['notes'],
                $order['created_at']
            ]);
            
            $newOrderId = $saPdo->lastInsertId();
            
            // Migrate order items
            $itemStmt = $wpPdo->prepare("SELECT * FROM pos_order_items WHERE order_id = ?");
            $itemStmt->execute([$order['id']]);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($items as $item) {
                $insertItemStmt->execute([
                    $newOrderId,
                    $item['product_id'],
                    $item['product_name'],
                    $item['sku'] ?? '',
                    $item['quantity'],
                    $item['unit_price'],
                    $item['line_total'],
                    $item['tax_amount'] ?? 0
                ]);
            }
            
            $migrated++;
        } catch (Exception $e) {
            echo "Order error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "Migrated $migrated orders this batch\n";
    return $migrated;
}

function migrateProducts($wpPdo, $saPdo, $batchSize) {
    echo "=== MIGRATING PRODUCTS ===\n";
    
    $stmt = $saPdo->query("SELECT COALESCE(MAX(wp_product_id), 0) as last_id FROM products WHERE wp_product_id IS NOT NULL");
    $lastId = (int)$stmt->fetch()['last_id'];
    echo "Resuming from wp_product_id > $lastId\n";
    
    // Add wp_product_id if not exists
    try {
        $saPdo->exec("ALTER TABLE products ADD COLUMN wp_product_id INT NULL AFTER id");
    } catch (Exception $e) {}
    
    $stmt = $wpPdo->prepare("
        SELECT 
            p.ID, p.post_title as name, p.post_content as description, p.post_status as status, p.post_date as created_at,
            MAX(CASE WHEN pm.meta_key = '_sku' THEN pm.meta_value END) as sku,
            MAX(CASE WHEN pm.meta_key = '_regular_price' THEN pm.meta_value END) as regular_price,
            MAX(CASE WHEN pm.meta_key = '_sale_price' THEN pm.meta_value END) as sale_price,
            MAX(CASE WHEN pm.meta_key = '_price' THEN pm.meta_value END) as price,
            MAX(CASE WHEN pm.meta_key = '_stock' THEN pm.meta_value END) as stock,
            MAX(CASE WHEN pm.meta_key = '_stock_status' THEN pm.meta_value END) as stock_status,
            MAX(CASE WHEN pm.meta_key = '_manage_stock' THEN pm.meta_value END) as manage_stock,
            MAX(CASE WHEN pm.meta_key = '_ywbc_barcode_display_value' THEN pm.meta_value END) as barcode
        FROM wp_posts p
        LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id
        WHERE p.post_type IN ('product', 'product_variation')
        AND p.ID > ?
        GROUP BY p.ID
        ORDER BY p.ID
        LIMIT ?
    ");
    $stmt->bindValue(1, $lastId, PDO::PARAM_INT);
    $stmt->bindValue(2, $batchSize, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($products)) {
        echo "No more products to migrate\n";
        return 0;
    }
    
    $insertStmt = $saPdo->prepare("
        INSERT INTO products 
        (wp_product_id, name, description, sku, barcode, price, regular_price, sale_price, stock_quantity, manage_stock, stock_status, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name = VALUES(name), price = VALUES(price)
    ");
    
    $migrated = 0;
    foreach ($products as $product) {
        try {
            $insertStmt->execute([
                $product['ID'],
                $product['name'],
                $product['description'] ?: '',
                $product['sku'] ?: '',
                $product['barcode'] ?: '',
                floatval($product['price'] ?: 0),
                floatval($product['regular_price'] ?: 0),
                floatval($product['sale_price'] ?: 0),
                intval($product['stock'] ?: 0),
                $product['manage_stock'] === 'yes' ? 1 : 0,
                $product['stock_status'] ?: 'instock',
                $product['status'] === 'publish' ? 'active' : 'inactive',
                $product['created_at']
            ]);
            $migrated++;
        } catch (Exception $e) {
            // Skip errors
        }
    }
    
    echo "Migrated $migrated products this batch\n";
    return $migrated;
}

function showStatus($saPdo) {
    echo "\n=== CURRENT STATUS ===\n";
    $tables = ['products', 'categories', 'customers', 'orders', 'order_items'];
    foreach ($tables as $table) {
        try {
            $stmt = $saPdo->query("SELECT COUNT(*) as cnt FROM $table");
            echo "$table: " . $stmt->fetch()['cnt'] . " records\n";
        } catch (Exception $e) {
            echo "$table: ERROR\n";
        }
    }
}

// Run migration based on entity
switch ($entity) {
    case 'customers':
        do {
            $count = migrateCustomers($wpPdo, $saPdo, $batchSize);
        } while ($count > 0);
        break;
    case 'orders':
        migrateOrders($wpPdo, $saPdo, $batchSize);
        break;
    case 'products':
        do {
            $count = migrateProducts($wpPdo, $saPdo, $batchSize);
        } while ($count > 0);
        break;
    case 'all':
    default:
        echo "Migrating all entities...\n\n";
        do { $count = migrateCustomers($wpPdo, $saPdo, $batchSize); } while ($count > 0);
        migrateOrders($wpPdo, $saPdo, 100);
        do { $count = migrateProducts($wpPdo, $saPdo, $batchSize); } while ($count > 0);
        break;
}

showStatus($saPdo);
echo "\nMigration complete!\n";
