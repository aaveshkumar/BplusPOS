<?php
/**
 * B-Plus POS Setup Wizard
 * 
 * Interactive setup wizard to configure B-Plus POS for any WordPress website
 * Access this file in your browser: http://localhost:8000/setup.php
 * 
 * Features:
 * - Checks system requirements
 * - Guides through credential input
 * - Tests database and API connections
 * - Generates config.php automatically
 * - Creates necessary database tables
 */

// Prevent access if config.php already exists (security)
if (file_exists(__DIR__ . '/config/config.php') && isset($_GET['force']) !== 'true') {
    http_response_code(403);
    die('<h1>Setup Already Complete</h1><p>B-Plus POS is already configured. If you need to reconfigure, delete config/config.php first.</p>');
}

define('ROOT_PATH', __DIR__);

// Simple session management
session_start();

// Get current step
$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'check_requirements') {
        $_SESSION['step'] = 2;
        header('Location: setup.php?step=2');
        exit;
    }
    
    if ($action === 'collect_credentials') {
        // Collect and validate credentials
        $credentials = [
            'local_db_host' => $_POST['local_db_host'] ?? 'localhost',
            'local_db_user' => $_POST['local_db_user'] ?? '',
            'local_db_pass' => $_POST['local_db_pass'] ?? '',
            'local_db_name' => $_POST['local_db_name'] ?? 'bplus_pos',
            'wc_db_host' => $_POST['wc_db_host'] ?? 'localhost',
            'wc_db_user' => $_POST['wc_db_user'] ?? '',
            'wc_db_pass' => $_POST['wc_db_pass'] ?? '',
            'wc_db_name' => $_POST['wc_db_name'] ?? '',
            'wc_api_url' => $_POST['wc_api_url'] ?? '',
            'wc_consumer_key' => $_POST['wc_consumer_key'] ?? '',
            'wc_consumer_secret' => $_POST['wc_consumer_secret'] ?? '',
            'wp_table_prefix' => $_POST['wp_table_prefix'] ?? 'wp_',
        ];
        
        $_SESSION['credentials'] = $credentials;
        $_SESSION['step'] = 3;
        header('Location: setup.php?step=3');
        exit;
    }
    
    if ($action === 'test_connections') {
        $credentials = $_SESSION['credentials'] ?? [];
        
        // Test Local Database
        try {
            $pdo = new PDO(
                "mysql:host={$credentials['local_db_host']};port=3306",
                $credentials['local_db_user'],
                $credentials['local_db_pass']
            );
            $_SESSION['local_db_test'] = 'success';
        } catch (Exception $e) {
            $_SESSION['local_db_test'] = 'error: ' . $e->getMessage();
        }
        
        // Test WooCommerce Database
        try {
            $pdo = new PDO(
                "mysql:host={$credentials['wc_db_host']};dbname={$credentials['wc_db_name']};port=3306",
                $credentials['wc_db_user'],
                $credentials['wc_db_pass']
            );
            $_SESSION['wc_db_test'] = 'success';
        } catch (Exception $e) {
            $_SESSION['wc_db_test'] = 'error: ' . $e->getMessage();
        }
        
        // Test API Connection
        $auth = base64_encode($credentials['wc_consumer_key'] . ':' . $credentials['wc_consumer_secret']);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $credentials['wc_api_url'] . '/products?per_page=1',
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $credentials['wc_consumer_key'] . ':' . $credentials['wc_consumer_secret'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 || $http_code === 401) {
            $_SESSION['api_test'] = 'success';
        } else {
            $_SESSION['api_test'] = "error: HTTP $http_code";
        }
        
        $_SESSION['step'] = 4;
        header('Location: setup.php?step=4');
        exit;
    }
    
    if ($action === 'finalize_setup') {
        $credentials = $_SESSION['credentials'] ?? [];
        
        // Generate config.php
        $config_content = generateConfigFile($credentials);
        
        // Write config.php
        $config_path = ROOT_PATH . '/config/config.php';
        if (file_put_contents($config_path, $config_content)) {
            // Create database tables
            try {
                createDatabaseTables($credentials);
                $_SESSION['setup_complete'] = true;
            } catch (Exception $e) {
                $_SESSION['setup_error'] = $e->getMessage();
            }
        } else {
            $_SESSION['setup_error'] = 'Could not write config.php. Check folder permissions.';
        }
        
        $_SESSION['step'] = 5;
        header('Location: setup.php?step=5');
        exit;
    }
}

// Retrieve stored data
$credentials = $_SESSION['credentials'] ?? [];
$local_db_test = $_SESSION['local_db_test'] ?? '';
$wc_db_test = $_SESSION['wc_db_test'] ?? '';
$api_test = $_SESSION['api_test'] ?? '';
$setup_complete = $_SESSION['setup_complete'] ?? false;
$setup_error = $_SESSION['setup_error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B-Plus POS Setup Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .setup-content {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            counter-reset: step;
            list-style: none;
            padding: 0;
        }
        .step-indicator li {
            position: relative;
            flex: 1;
            text-align: center;
        }
        .step-indicator li::before {
            content: counter(step);
            counter-increment: step;
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
        }
        .step-indicator li.active::before {
            background: #667eea;
            color: white;
        }
        .step-indicator li.completed::before {
            background: #10b981;
            color: white;
            content: '✓';
        }
        .step-indicator li::after {
            content: '';
            position: absolute;
            top: -9px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e9ecef;
        }
        .step-indicator li:last-child::after {
            display: none;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 30px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .test-result {
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .test-result.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .test-result.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .requirement-item {
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .requirement-item.pass {
            background: #d1fae5;
            color: #065f46;
        }
        .requirement-item.fail {
            background: #fee2e2;
            color: #991b1b;
        }
        .success-message {
            background: #d1fae5;
            border: 2px solid #10b981;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .error-message {
            background: #fee2e2;
            border: 2px solid #ef4444;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .cred-section {
            border: 1px solid #e9ecef;
            padding: 20px;
            border-radius: 8px;
            background: #f9fafb;
        }
        .cred-section h5 {
            color: #667eea;
            margin-bottom: 15px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1><i class="fas fa-rocket"></i> B-Plus POS Setup Wizard</h1>
            <p>Connect to your WooCommerce WordPress website in minutes</p>
        </div>
        
        <div class="setup-content">
            <?php if ($step == 1): ?>
                <!-- Step 1: System Requirements -->
                <h3 class="mb-4">Step 1: System Requirements</h3>
                
                <div class="step-indicator">
                    <li class="active">Requirements</li>
                    <li>Credentials</li>
                    <li>Test Connection</li>
                    <li>Configure</li>
                    <li>Complete</li>
                </div>
                
                <h5 class="mt-5 mb-3">Checking your system...</h5>
                
                <?php
                $checks = [
                    'PHP Version ≥ 7.4' => version_compare(PHP_VERSION, '7.4.0') >= 0,
                    'MySQL PDO Extension' => extension_loaded('pdo_mysql'),
                    'cURL Extension' => extension_loaded('curl'),
                    'OpenSSL Extension' => extension_loaded('openssl'),
                    'JSON Extension' => extension_loaded('json'),
                    'Writable /config Directory' => is_writable(ROOT_PATH . '/config'),
                    'Writable /storage Directory' => is_writable(ROOT_PATH . '/storage'),
                ];
                
                $all_pass = true;
                foreach ($checks as $check => $result):
                    $all_pass = $all_pass && $result;
                ?>
                    <div class="requirement-item <?php echo $result ? 'pass' : 'fail'; ?>">
                        <i class="fas <?php echo $result ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <span><?php echo $check; ?></span>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($all_pass): ?>
                    <form method="POST" class="mt-5">
                        <input type="hidden" name="action" value="check_requirements">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right"></i> Next: Enter Credentials
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-danger mt-4">
                        <h5>❌ Setup Cannot Continue</h5>
                        <p>Your server is missing required extensions. Please contact your hosting provider.</p>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($step == 2): ?>
                <!-- Step 2: Credentials Input -->
                <h3 class="mb-4">Step 2: Enter Your Credentials</h3>
                
                <div class="step-indicator">
                    <li class="completed">Requirements</li>
                    <li class="active">Credentials</li>
                    <li>Test Connection</li>
                    <li>Configure</li>
                    <li>Complete</li>
                </div>
                
                <form method="POST" class="mt-5">
                    <input type="hidden" name="action" value="collect_credentials">
                    
                    <div class="credentials-grid">
                        <!-- Local POS Database -->
                        <div class="cred-section">
                            <h5><i class="fas fa-database"></i> Local POS Database</h5>
                            
                            <div class="form-group">
                                <label class="form-label">Host</label>
                                <input type="text" name="local_db_host" class="form-control" value="<?php echo htmlspecialchars($credentials['local_db_host'] ?? 'localhost'); ?>">
                                <small class="text-muted">Usually 'localhost'</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" name="local_db_user" class="form-control" value="<?php echo htmlspecialchars($credentials['local_db_user'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <input type="password" name="local_db_pass" class="form-control" value="<?php echo htmlspecialchars($credentials['local_db_pass'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Database Name</label>
                                <input type="text" name="local_db_name" class="form-control" value="<?php echo htmlspecialchars($credentials['local_db_name'] ?? 'bplus_pos'); ?>">
                            </div>
                        </div>
                        
                        <!-- WooCommerce Database -->
                        <div class="cred-section">
                            <h5><i class="fas fa-globe"></i> WordPress/WooCommerce Database</h5>
                            
                            <div class="form-group">
                                <label class="form-label">Host</label>
                                <input type="text" name="wc_db_host" class="form-control" value="<?php echo htmlspecialchars($credentials['wc_db_host'] ?? 'localhost'); ?>">
                                <small class="text-muted">From cPanel or hosting panel</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" name="wc_db_user" class="form-control" value="<?php echo htmlspecialchars($credentials['wc_db_user'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <input type="password" name="wc_db_pass" class="form-control" value="<?php echo htmlspecialchars($credentials['wc_db_pass'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Database Name</label>
                                <input type="text" name="wc_db_name" class="form-control" value="<?php echo htmlspecialchars($credentials['wc_db_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- WooCommerce REST API -->
                    <div class="cred-section mt-4">
                        <h5><i class="fas fa-key"></i> WooCommerce REST API</h5>
                        
                        <div class="form-group">
                            <label class="form-label">Site URL</label>
                            <input type="url" name="wc_api_url" class="form-control" placeholder="https://yourdomain.com/wp-json/wc/v3" value="<?php echo htmlspecialchars($credentials['wc_api_url'] ?? ''); ?>" required>
                            <small class="text-muted">From WooCommerce Settings → API</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Consumer Key</label>
                            <input type="text" name="wc_consumer_key" class="form-control" value="<?php echo htmlspecialchars($credentials['wc_consumer_key'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Consumer Secret</label>
                            <input type="password" name="wc_consumer_secret" class="form-control" value="<?php echo htmlspecialchars($credentials['wc_consumer_secret'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">WordPress Table Prefix</label>
                            <input type="text" name="wp_table_prefix" class="form-control" value="<?php echo htmlspecialchars($credentials['wp_table_prefix'] ?? 'wp_'); ?>">
                            <small class="text-muted">Check wp-config.php (usually 'wp_')</small>
                        </div>
                    </div>
                    
                    <div class="mt-5">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right"></i> Test Connections
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step == 3): ?>
                <!-- Step 3: Test Connections -->
                <h3 class="mb-4">Step 3: Testing Connections</h3>
                
                <div class="step-indicator">
                    <li class="completed">Requirements</li>
                    <li class="completed">Credentials</li>
                    <li class="active">Test Connection</li>
                    <li>Configure</li>
                    <li>Complete</li>
                </div>
                
                <form method="POST" class="mt-5">
                    <input type="hidden" name="action" value="test_connections">
                    
                    <h5 class="mb-4">Testing your credentials...</h5>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-flask"></i> Run Connection Tests
                    </button>
                </form>
                
            <?php elseif ($step == 4): ?>
                <!-- Step 4: Connection Test Results -->
                <h3 class="mb-4">Step 4: Connection Test Results</h3>
                
                <div class="step-indicator">
                    <li class="completed">Requirements</li>
                    <li class="completed">Credentials</li>
                    <li class="completed">Test Connection</li>
                    <li class="active">Configure</li>
                    <li>Complete</li>
                </div>
                
                <div class="mt-5">
                    <h5 class="mb-3">Test Results:</h5>
                    
                    <div class="test-result <?php echo strpos($local_db_test, 'success') !== false ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo strpos($local_db_test, 'success') !== false ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <span>Local POS Database: <?php echo htmlspecialchars($local_db_test ?: 'Not tested'); ?></span>
                    </div>
                    
                    <div class="test-result <?php echo strpos($wc_db_test, 'success') !== false ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo strpos($wc_db_test, 'success') !== false ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <span>WordPress Database: <?php echo htmlspecialchars($wc_db_test ?: 'Not tested'); ?></span>
                    </div>
                    
                    <div class="test-result <?php echo strpos($api_test, 'success') !== false ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo strpos($api_test, 'success') !== false ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <span>WooCommerce REST API: <?php echo htmlspecialchars($api_test ?: 'Not tested'); ?></span>
                    </div>
                    
                    <?php 
                    $all_tests_pass = (
                        strpos($local_db_test, 'success') !== false &&
                        strpos($wc_db_test, 'success') !== false &&
                        strpos($api_test, 'success') !== false
                    );
                    ?>
                    
                    <?php if ($all_tests_pass): ?>
                        <form method="POST" class="mt-5">
                            <input type="hidden" name="action" value="finalize_setup">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-cog"></i> Complete Setup
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger mt-4">
                            <h5>❌ Some Tests Failed</h5>
                            <p>Please check your credentials and try again. Review the error messages above.</p>
                            <a href="setup.php?step=2" class="btn btn-outline-danger mt-3">Back to Credentials</a>
                        </div>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($step == 5): ?>
                <!-- Step 5: Setup Complete -->
                <div class="step-indicator">
                    <li class="completed">Requirements</li>
                    <li class="completed">Credentials</li>
                    <li class="completed">Test Connection</li>
                    <li class="completed">Configure</li>
                    <li class="completed">Complete</li>
                </div>
                
                <?php if ($setup_complete): ?>
                    <div class="success-message mt-5">
                        <h3><i class="fas fa-check-circle"></i> Setup Complete!</h3>
                        <p class="mt-3">B-Plus POS has been successfully configured and connected to your WordPress store.</p>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <h5>Next Steps:</h5>
                        <ol>
                            <li>Delete this setup.php file for security</li>
                            <li>Go to the POS: <a href="/public">http://localhost:8000/public</a></li>
                            <li>Login with default credentials: admin / admin</li>
                            <li><strong>Change your password immediately!</strong></li>
                        </ol>
                    </div>
                    
                    <div class="mt-5">
                        <a href="/public" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-right"></i> Go to B-Plus POS
                        </a>
                    </div>
                <?php else: ?>
                    <div class="error-message mt-5">
                        <h3><i class="fas fa-times-circle"></i> Setup Failed</h3>
                        <p><?php echo htmlspecialchars($setup_error ?: 'An unknown error occurred'); ?></p>
                    </div>
                    
                    <div class="mt-5">
                        <a href="setup.php?step=2" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Try Again
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
/**
 * Generate config.php content from credentials
 */
function generateConfigFile($credentials) {
    $config = <<<'PHP'
<?php
/**
 * B-Plus POS Configuration
 * 
 * Generated by Setup Wizard
 * Never commit this file to version control
 */

return [
    // Application Settings
    'app' => [
        'name' => 'B-Plus POS',
        'version' => '1.0.0',
        'environment' => 'production',
        'timezone' => 'Asia/Kolkata',
        'url' => 'http://localhost:5000',
    ],

    // Session Configuration
    'session' => [
        'name' => 'bplus_pos_session',
        'lifetime' => 3600,
        'path' => __DIR__ . '/../storage/sessions',
        'secure' => false,
        'httponly' => true,
    ],

    // Local POS Database
    'database' => [
        'host' => '%LOCAL_DB_HOST%',
        'port' => 3306,
        'database' => '%LOCAL_DB_NAME%',
        'username' => '%LOCAL_DB_USER%',
        'password' => '%LOCAL_DB_PASS%',
        'charset' => 'utf8mb4',
        'prefix' => 'pos_',
    ],

    // WooCommerce Database
    'woocommerce_db' => [
        'host' => '%WC_DB_HOST%',
        'port' => 3306,
        'database' => '%WC_DB_NAME%',
        'username' => '%WC_DB_USER%',
        'password' => '%WC_DB_PASS%',
        'charset' => 'utf8mb4',
        'prefix' => '%WP_TABLE_PREFIX%',
    ],

    // WooCommerce REST API
    'woocommerce' => [
        'site_url' => '%WC_API_URL%',
        'consumer_key' => '%WC_CONSUMER_KEY%',
        'consumer_secret' => '%WC_CONSUMER_SECRET%',
        'api_version' => 'wc/v3',
        'verify_ssl' => true,
    ],

    // POS Settings
    'pos' => [
        'items_per_page' => 20,
        'low_stock_threshold' => 5,
        'default_tax_rate' => 18,
        'currency_symbol' => '₹',
        'currency_code' => 'INR',
        'receipt_footer' => 'Thank you for your business!',
        'payment_gateways' => [
            'cash' => 'cod',
            'card' => 'bacs',
            'upi' => 'cod',
        ],
    ],

    // User Roles
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => ['all'],
        ],
        'stock_manager' => [
            'name' => 'Stock Manager',
            'permissions' => ['manage_products', 'view_inventory', 'view_reports'],
        ],
        'manager' => [
            'name' => 'Manager',
            'permissions' => ['manage_orders', 'view_reports', 'manage_customers'],
        ],
        'cashier' => [
            'name' => 'Cashier',
            'permissions' => ['process_orders', 'view_inventory'],
        ],
    ],
];
PHP;

    // Replace placeholders
    $config = str_replace('%LOCAL_DB_HOST%', $credentials['local_db_host'], $config);
    $config = str_replace('%LOCAL_DB_NAME%', $credentials['local_db_name'], $config);
    $config = str_replace('%LOCAL_DB_USER%', $credentials['local_db_user'], $config);
    $config = str_replace('%LOCAL_DB_PASS%', $credentials['local_db_pass'], $config);
    $config = str_replace('%WC_DB_HOST%', $credentials['wc_db_host'], $config);
    $config = str_replace('%WC_DB_NAME%', $credentials['wc_db_name'], $config);
    $config = str_replace('%WC_DB_USER%', $credentials['wc_db_user'], $config);
    $config = str_replace('%WC_DB_PASS%', $credentials['wc_db_pass'], $config);
    $config = str_replace('%WC_API_URL%', $credentials['wc_api_url'], $config);
    $config = str_replace('%WC_CONSUMER_KEY%', $credentials['wc_consumer_key'], $config);
    $config = str_replace('%WC_CONSUMER_SECRET%', $credentials['wc_consumer_secret'], $config);
    $config = str_replace('%WP_TABLE_PREFIX%', $credentials['wp_table_prefix'], $config);
    
    return $config;
}

/**
 * Create necessary database tables
 */
function createDatabaseTables($credentials) {
    // Connect to local database
    $pdo = new PDO(
        "mysql:host={$credentials['local_db_host']};port=3306",
        $credentials['local_db_user'],
        $credentials['local_db_pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$credentials['local_db_name']}`");
    
    // Use database
    $pdo->exec("USE `{$credentials['local_db_name']}`");
    
    // Create tables (simplified schema - full schema should be in database/schema.sql)
    $tables = [
        "CREATE TABLE IF NOT EXISTS pos_orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            customer_id INT,
            total DECIMAL(10, 2),
            tax DECIMAL(10, 2),
            status VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS pos_order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT,
            quantity INT,
            price DECIMAL(10, 2),
            tax DECIMAL(10, 2),
            FOREIGN KEY (order_id) REFERENCES pos_orders(id)
        )",
        "CREATE TABLE IF NOT EXISTS pos_store_credit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            amount DECIMAL(10, 2),
            balance DECIMAL(10, 2),
            status VARCHAR(20),
            source_type VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS pos_returns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            return_number VARCHAR(50) UNIQUE,
            order_id INT,
            customer_id INT,
            return_type VARCHAR(20),
            return_reason TEXT,
            refund_amount DECIMAL(10, 2),
            refund_method VARCHAR(50),
            status VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
    ];
    
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
}
?>
