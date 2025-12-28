<?php
/**
 * WooCommerce to Standalone Database Migration Script
 * Copies products, categories, customers, orders, and other data from WooCommerce
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

echo "<h1>B-Plus POS - WooCommerce Data Migration</h1>\n";
echo "<pre>\n";

$config = require __DIR__ . '/../config/config.php';

$wcHost = $config['database']['host'];
$wcPort = $config['database']['port'] ?? 3306;
$wcDatabase = $config['database']['database'];
$wcUsername = $config['database']['username'];
$wcPassword = $config['database']['password'];
$wcPrefix = $config['database']['prefix'] ?? 'wp_';

$saHost = getenv('STANDALONE_DB_HOST');
$saPort = getenv('STANDALONE_DB_PORT') ?: '3306';
$saDatabase = getenv('STANDALONE_DB_NAME');
$saUsername = getenv('STANDALONE_DB_USER');
$saPassword = getenv('STANDALONE_DB_PASSWORD');

echo "Source: WooCommerce @ {$wcHost}\n";
echo "Target: Standalone @ {$saHost}\n\n";

function getWcConnection($wcHost, $wcPort, $wcDatabase, $wcUsername, $wcPassword) {
    $wcDsn = "mysql:host={$wcHost};port={$wcPort};dbname={$wcDatabase};charset=utf8mb4";
    return new PDO($wcDsn, $wcUsername, $wcPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout=600"
    ]);
}

function getSaConnection($saHost, $saPort, $saDatabase, $saUsername, $saPassword) {
    $saDsn = "mysql:host={$saHost};port={$saPort};dbname={$saDatabase};charset=utf8mb4";
    return new PDO($saDsn, $saUsername, $saPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

try {
    echo "Connecting to databases...\n";
    $wcDb = getWcConnection($wcHost, $wcPort, $wcDatabase, $wcUsername, $wcPassword);
    $saDb = getSaConnection($saHost, $saPort, $saDatabase, $saUsername, $saPassword);
    echo "Connected!\n\n";
    
    $stats = [
        'categories' => 0,
        'products' => 0,
        'variations' => 0,
        'customers' => 0,
        'users' => 0,
        'orders' => 0,
        'order_items' => 0,
        'tax_rates' => 0,
    ];
    
    echo "========================================\n";
    echo "STEP 1: Checking existing categories\n";
    echo "========================================\n";
    
    $existingCats = $saDb->query("SELECT id, name FROM categories")->fetchAll();
    if (count($existingCats) > 0) {
        echo "Categories already migrated (" . count($existingCats) . " found), skipping...\n";
        $stats['categories'] = count($existingCats);
    } else {
        $sql = "SELECT t.term_id as id, t.name, t.slug, tt.description, tt.parent
                FROM {$wcPrefix}terms t
                INNER JOIN {$wcPrefix}term_taxonomy tt ON t.term_id = tt.term_id
                WHERE tt.taxonomy = 'product_cat'
                ORDER BY tt.parent ASC, t.name ASC";
        $categories = $wcDb->query($sql)->fetchAll();
        
        foreach ($categories as $cat) {
            $stmt = $saDb->prepare("INSERT INTO categories (name, slug, description, parent_id, status) 
                                    VALUES (?, ?, ?, ?, 'active')
                                    ON DUPLICATE KEY UPDATE name = VALUES(name)");
            $stmt->execute([$cat['name'], $cat['slug'], $cat['description'], $cat['parent']]);
            $stats['categories']++;
        }
        echo "Categories migrated: {$stats['categories']}\n";
    }
    
    echo "\n========================================\n";
    echo "STEP 2: Migrating Products (batch mode)\n";
    echo "========================================\n";
    
    $wcDb = getWcConnection($wcHost, $wcPort, $wcDatabase, $wcUsername, $wcPassword);
    
    $countSql = "SELECT COUNT(*) FROM {$wcPrefix}posts WHERE post_type = 'product' AND post_status IN ('publish', 'draft')";
    $totalProducts = $wcDb->query($countSql)->fetchColumn();
    echo "Total products to migrate: {$totalProducts}\n";
    
    $batchSize = 50;
    $offset = 0;
    $productMap = [];
    
    while ($offset < $totalProducts) {
        $wcDb = getWcConnection($wcHost, $wcPort, $wcDatabase, $wcUsername, $wcPassword);
        
        $sql = "SELECT 
                    p.ID as id,
                    p.post_title as name,
                    p.post_name as slug,
                    p.post_content as description,
                    p.post_excerpt as short_description,
                    p.post_status as status,
                    p.post_date as created_at
                FROM {$wcPrefix}posts p
                WHERE p.post_type = 'product'
                AND p.post_status IN ('publish', 'draft')
                ORDER BY p.ID ASC
                LIMIT {$batchSize} OFFSET {$offset}";
        
        $products = $wcDb->query($sql)->fetchAll();
        if (empty($products)) break;
        
        foreach ($products as $product) {
            $meta = [];
            $metaSql = "SELECT meta_key, meta_value FROM {$wcPrefix}postmeta WHERE post_id = ?";
            $metaStmt = $wcDb->prepare($metaSql);
            $metaStmt->execute([$product['id']]);
            foreach ($metaStmt->fetchAll() as $row) {
                $meta[$row['meta_key']] = $row['meta_value'];
            }
            
            $existsCheck = $saDb->prepare("SELECT id FROM products WHERE slug = ?");
            $existsCheck->execute([$product['slug']]);
            if ($existsCheck->fetchColumn()) {
                $stats['products']++;
                continue;
            }
            
            $stmt = $saDb->prepare("INSERT INTO products 
                (name, slug, sku, barcode, description, short_description, 
                 regular_price, sale_price, price, cost_price,
                 stock_quantity, stock_status, manage_stock, low_stock_threshold,
                 tax_status, tax_class, status, product_type, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $regularPrice = floatval($meta['_regular_price'] ?? 0);
            $salePrice = !empty($meta['_sale_price']) ? floatval($meta['_sale_price']) : null;
            $price = floatval($meta['_price'] ?? $regularPrice);
            
            $stmt->execute([
                $product['name'],
                $product['slug'],
                $meta['_sku'] ?? null,
                $meta['_ywbc_barcode_display_value'] ?? null,
                $product['description'],
                $product['short_description'],
                $regularPrice,
                $salePrice,
                $price,
                floatval($meta['_cost_price'] ?? 0),
                intval($meta['_stock'] ?? 0),
                $meta['_stock_status'] ?? 'instock',
                ($meta['_manage_stock'] ?? 'no') === 'yes' ? 1 : 0,
                intval($meta['_low_stock_amount'] ?? 5),
                $meta['_tax_status'] ?? 'taxable',
                $meta['_tax_class'] ?? 'standard',
                $product['status'],
                $meta['_product_type'] ?? 'simple',
                $product['created_at']
            ]);
            
            $productMap[$product['id']] = $saDb->lastInsertId();
            $stats['products']++;
        }
        
        $offset += $batchSize;
        echo "  Migrated {$stats['products']} / {$totalProducts} products...\n";
        flush();
    }
    
    echo "Products migrated: {$stats['products']}\n\n";
    
    echo "========================================\n";
    echo "STEP 3: Migrating Customers (batch mode)\n";
    echo "========================================\n";
    
    $wcDb = getWcConnection($wcHost, $wcPort, $wcDatabase, $wcUsername, $wcPassword);
    
    $sql = "SELECT 
                u.ID as id,
                u.user_email as email,
                u.display_name,
                u.user_registered as created_at
            FROM {$wcPrefix}users u
            INNER JOIN {$wcPrefix}usermeta um ON u.ID = um.user_id
            WHERE um.meta_key = '{$wcPrefix}capabilities'
            AND um.meta_value LIKE '%customer%'
            LIMIT 500";
    
    try {
        $customers = $wcDb->query($sql)->fetchAll();
        
        foreach ($customers as $cust) {
            $existsCheck = $saDb->prepare("SELECT id FROM customers WHERE email = ?");
            $existsCheck->execute([$cust['email']]);
            if ($existsCheck->fetchColumn()) {
                $stats['customers']++;
                continue;
            }
            
            $meta = [];
            $metaSql = "SELECT meta_key, meta_value FROM {$wcPrefix}usermeta WHERE user_id = ?";
            $metaStmt = $wcDb->prepare($metaSql);
            $metaStmt->execute([$cust['id']]);
            foreach ($metaStmt->fetchAll() as $row) {
                $meta[$row['meta_key']] = $row['meta_value'];
            }
            
            $stmt = $saDb->prepare("INSERT INTO customers 
                (email, phone, first_name, last_name, display_name,
                 address_1, city, state, postcode, country, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $cust['email'],
                $meta['billing_phone'] ?? null,
                $meta['first_name'] ?? '',
                $meta['last_name'] ?? '',
                $cust['display_name'],
                $meta['billing_address_1'] ?? '',
                $meta['billing_city'] ?? '',
                $meta['billing_state'] ?? '',
                $meta['billing_postcode'] ?? '',
                $meta['billing_country'] ?? 'IN',
                $cust['created_at']
            ]);
            
            $stats['customers']++;
        }
    } catch (PDOException $e) {
        echo "Warning: Could not fetch customers: " . $e->getMessage() . "\n";
    }
    
    echo "Customers migrated: {$stats['customers']}\n\n";
    
    echo "========================================\n";
    echo "STEP 4: Migrating POS Users\n";
    echo "========================================\n";
    
    $wcDb = getWcConnection($wcHost, $wcPort, $wcDatabase, $wcUsername, $wcPassword);
    
    $sql = "SELECT 
                u.ID as id,
                u.user_login as username,
                u.user_pass as password,
                u.user_email as email,
                u.display_name
            FROM {$wcPrefix}users u
            INNER JOIN {$wcPrefix}usermeta um ON u.ID = um.user_id
            WHERE um.meta_key = '{$wcPrefix}capabilities'
            AND (um.meta_value LIKE '%administrator%' 
                 OR um.meta_value LIKE '%shop_manager%'
                 OR um.meta_value LIKE '%cashier%')";
    
    try {
        $users = $wcDb->query($sql)->fetchAll();
        
        foreach ($users as $user) {
            $existsCheck = $saDb->prepare("SELECT id FROM users WHERE username = ?");
            $existsCheck->execute([$user['username']]);
            if ($existsCheck->fetchColumn()) {
                $stats['users']++;
                echo "  User already exists: {$user['username']}\n";
                continue;
            }
            
            $capsSql = "SELECT meta_value FROM {$wcPrefix}usermeta 
                        WHERE user_id = ? AND meta_key = '{$wcPrefix}capabilities'";
            $capsStmt = $wcDb->prepare($capsSql);
            $capsStmt->execute([$user['id']]);
            $caps = $capsStmt->fetchColumn();
            
            if (strpos($caps, 'administrator') !== false) {
                $posRole = 'admin';
            } elseif (strpos($caps, 'shop_manager') !== false) {
                $posRole = 'manager';
            } else {
                $posRole = 'cashier';
            }
            
            $stmt = $saDb->prepare("INSERT INTO users 
                (username, password, email, display_name, role, status)
                VALUES (?, ?, ?, ?, ?, 'active')");
            
            $stmt->execute([
                $user['username'],
                $user['password'],
                $user['email'],
                $user['display_name'],
                $posRole
            ]);
            
            $stats['users']++;
            echo "  Migrated user: {$user['username']} ({$posRole})\n";
        }
    } catch (PDOException $e) {
        echo "Warning: Could not fetch users: " . $e->getMessage() . "\n";
    }
    
    echo "Users migrated: {$stats['users']}\n\n";
    
    echo "========================================\n";
    echo "STEP 5: Migrating Tax Rates\n";
    echo "========================================\n";
    
    $wcDb = getWcConnection($wcHost, $wcPort, $wcDatabase, $wcUsername, $wcPassword);
    
    try {
        $sql = "SELECT * FROM {$wcPrefix}woocommerce_tax_rates LIMIT 100";
        $taxRates = $wcDb->query($sql)->fetchAll();
        
        foreach ($taxRates as $tax) {
            $stmt = $saDb->prepare("INSERT IGNORE INTO tax_rates 
                (name, rate, tax_class, country, state, city, postcode, priority, compound, shipping)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $tax['tax_rate_name'] ?? 'Tax',
                floatval($tax['tax_rate']),
                $tax['tax_rate_class'] ?? 'standard',
                $tax['tax_rate_country'] ?? 'IN',
                $tax['tax_rate_state'] ?? '*',
                $tax['tax_rate_city'] ?? '*',
                $tax['tax_rate_postcode'] ?? '*',
                intval($tax['tax_rate_priority'] ?? 1),
                intval($tax['tax_rate_compound'] ?? 0),
                intval($tax['tax_rate_shipping'] ?? 1)
            ]);
            
            $stats['tax_rates']++;
        }
    } catch (PDOException $e) {
        echo "No WooCommerce tax rates table found, using defaults.\n";
    }
    
    echo "Tax rates migrated: {$stats['tax_rates']}\n\n";
    
    echo "========================================\n";
    echo "STEP 6: Migrating Recent Orders\n";
    echo "========================================\n";
    
    $wcDb = getWcConnection($wcHost, $wcPort, $wcDatabase, $wcUsername, $wcPassword);
    
    try {
        $sql = "SELECT 
                    p.ID as id,
                    p.post_date as created_at,
                    p.post_status as status
                FROM {$wcPrefix}posts p
                WHERE p.post_type = 'shop_order'
                ORDER BY p.ID DESC
                LIMIT 200";
        $orders = $wcDb->query($sql)->fetchAll();
        
        foreach ($orders as $order) {
            $existsCheck = $saDb->prepare("SELECT id FROM orders WHERE order_number = ?");
            $existsCheck->execute([$order['id']]);
            if ($existsCheck->fetchColumn()) {
                $stats['orders']++;
                continue;
            }
            
            $meta = [];
            $metaSql = "SELECT meta_key, meta_value FROM {$wcPrefix}postmeta WHERE post_id = ?";
            $metaStmt = $wcDb->prepare($metaSql);
            $metaStmt->execute([$order['id']]);
            foreach ($metaStmt->fetchAll() as $row) {
                $meta[$row['meta_key']] = $row['meta_value'];
            }
            
            $status = str_replace('wc-', '', $order['status']);
            
            $stmt = $saDb->prepare("INSERT INTO orders 
                (order_number, customer_id, status, subtotal, discount_total, tax_total, total,
                 payment_method, payment_method_title, 
                 billing_first_name, billing_last_name, billing_email, billing_phone,
                 billing_address_1, billing_city, billing_state, billing_postcode,
                 source, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'online', ?)");
            
            $stmt->execute([
                $order['id'],
                intval($meta['_customer_user'] ?? 0) ?: null,
                $status,
                floatval($meta['_order_subtotal'] ?? 0),
                floatval($meta['_cart_discount'] ?? 0),
                floatval($meta['_order_tax'] ?? 0),
                floatval($meta['_order_total'] ?? 0),
                $meta['_payment_method'] ?? 'cod',
                $meta['_payment_method_title'] ?? 'Cash',
                $meta['_billing_first_name'] ?? '',
                $meta['_billing_last_name'] ?? '',
                $meta['_billing_email'] ?? '',
                $meta['_billing_phone'] ?? '',
                $meta['_billing_address_1'] ?? '',
                $meta['_billing_city'] ?? '',
                $meta['_billing_state'] ?? '',
                $meta['_billing_postcode'] ?? '',
                $order['created_at']
            ]);
            
            $newOrderId = $saDb->lastInsertId();
            $stats['orders']++;
            
            $itemsSql = "SELECT 
                            oi.order_item_id,
                            oi.order_item_name as product_name
                         FROM {$wcPrefix}woocommerce_order_items oi
                         WHERE oi.order_id = ? AND oi.order_item_type = 'line_item'";
            $itemsStmt = $wcDb->prepare($itemsSql);
            $itemsStmt->execute([$order['id']]);
            $items = $itemsStmt->fetchAll();
            
            foreach ($items as $item) {
                $itemMeta = [];
                $imSql = "SELECT meta_key, meta_value FROM {$wcPrefix}woocommerce_order_itemmeta WHERE order_item_id = ?";
                $imStmt = $wcDb->prepare($imSql);
                $imStmt->execute([$item['order_item_id']]);
                foreach ($imStmt->fetchAll() as $row) {
                    $itemMeta[$row['meta_key']] = $row['meta_value'];
                }
                
                $insertItem = $saDb->prepare("INSERT INTO order_items 
                    (order_id, product_id, product_name, quantity, 
                     unit_price, subtotal, tax_amount, total)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                $qty = intval($itemMeta['_qty'] ?? 1);
                $subtotal = floatval($itemMeta['_line_subtotal'] ?? 0);
                $total = floatval($itemMeta['_line_total'] ?? 0);
                $tax = floatval($itemMeta['_line_tax'] ?? 0);
                $unitPrice = $qty > 0 ? $subtotal / $qty : 0;
                
                $insertItem->execute([
                    $newOrderId,
                    null,
                    $item['product_name'],
                    $qty,
                    $unitPrice,
                    $subtotal,
                    $tax,
                    $total
                ]);
                
                $stats['order_items']++;
            }
        }
    } catch (PDOException $e) {
        echo "Warning: Could not migrate orders: " . $e->getMessage() . "\n";
    }
    
    echo "Orders migrated: {$stats['orders']}\n";
    echo "Order items migrated: {$stats['order_items']}\n\n";
    
    echo "========================================\n";
    echo "MIGRATION COMPLETE!\n";
    echo "========================================\n";
    echo "Summary:\n";
    foreach ($stats as $type => $count) {
        echo "  {$type}: {$count}\n";
    }
    echo "========================================\n";
    
    echo "\nVerifying standalone database...\n";
    $tables = $saDb->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $count = $saDb->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        echo "  - {$table}: {$count} rows\n";
    }
    
} catch (PDOException $e) {
    echo "\nDatabase Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n</pre>";
echo "<p><strong>Migration complete!</strong></p>";
echo "<p><a href='/'>Go to POS</a></p>";
