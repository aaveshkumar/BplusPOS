<?php
/**
 * Create Cashier User
 * Creates a POS cashier user account
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/PasswordHash.php';

// Database connection
$db = Database::getInstance()->getConnection();

// User details
$username = 'poscashier';
$password = '123123';
$email = 'poscashier@bplus-pos.local';
$displayName = 'POS Cashier';

// Hash password - using WordPress compatible phpass hash
function wp_hash_password($password) {
    $hasher = new PasswordHash(8, true);
    
    // Generate random salt
    $random = '';
    for ($i = 0; $i < 6; $i++) {
        $random .= chr(mt_rand(0, 255));
    }
    $random = base64_encode($random);
    $salt = substr(str_replace('+', '.', $random), 0, 8);
    
    // Create WordPress style hash
    $count_log2 = 8;
    $hash = md5($salt . $password, true);
    $count = 1 << $count_log2;
    do {
        $hash = md5($hash . $password, true);
    } while (--$count);
    
    $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $output = '$P$';
    $output .= $itoa64[min($count_log2, 30)];
    $output .= $salt;
    
    // Encode64
    $i = 0;
    do {
        $value = ord($hash[$i++]);
        $output .= $itoa64[$value & 0x3f];
        if ($i < 16)
            $value |= ord($hash[$i]) << 8;
        $output .= $itoa64[($value >> 6) & 0x3f];
        if ($i++ >= 16)
            break;
        if ($i < 16)
            $value |= ord($hash[$i]) << 16;
        $output .= $itoa64[($value >> 12) & 0x3f];
        if ($i++ >= 16)
            break;
        $output .= $itoa64[($value >> 18) & 0x3f];
    } while ($i < 16);
    
    return $output;
}

$hashedPassword = wp_hash_password($password);

// Get table prefix from config
$config = require __DIR__ . '/../config/config.php';
$prefix = $config['database']['prefix'] ?? 'wp_';

try {
    // Check if user already exists
    $checkSql = "SELECT ID FROM {$prefix}users WHERE user_login = ?";
    $stmt = $db->prepare($checkSql);
    $stmt->execute([$username]);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "✅ User '{$username}' already exists with ID: {$existingUser['ID']}\n";
        echo "Updating password and role...\n";
        
        $userId = $existingUser['ID'];
        
        // Update password
        $updateSql = "UPDATE {$prefix}users SET user_pass = ? WHERE ID = ?";
        $stmt = $db->prepare($updateSql);
        $stmt->execute([$hashedPassword, $userId]);
        
    } else {
        // Insert new user
        $insertSql = "INSERT INTO {$prefix}users 
                      (user_login, user_pass, user_email, user_nicename, display_name, user_registered)
                      VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $db->prepare($insertSql);
        $stmt->execute([
            $username,
            $hashedPassword,
            $email,
            $username,
            $displayName
        ]);
        
        $userId = $db->lastInsertId();
        echo "✅ User created successfully with ID: {$userId}\n";
    }
    
    // Set POS role in usermeta
    // First, delete existing pos_role if any
    $deleteSql = "DELETE FROM {$prefix}usermeta WHERE user_id = ? AND meta_key = 'pos_role'";
    $stmt = $db->prepare($deleteSql);
    $stmt->execute([$userId]);
    
    // Insert new pos_role
    $insertMetaSql = "INSERT INTO {$prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, 'pos_role', 'cashier')";
    $stmt = $db->prepare($insertMetaSql);
    $stmt->execute([$userId]);
    
    // Also set WordPress capabilities (for compatibility)
    $capabilities = serialize(['cashier' => true]);
    
    // Delete existing wp_capabilities if any
    $deleteSql = "DELETE FROM {$prefix}usermeta WHERE user_id = ? AND meta_key = '{$prefix}capabilities'";
    $stmt = $db->prepare($deleteSql);
    $stmt->execute([$userId]);
    
    // Insert new capabilities
    $insertCapSql = "INSERT INTO {$prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, '{$prefix}capabilities', ?)";
    $stmt = $db->prepare($insertCapSql);
    $stmt->execute([$userId, $capabilities]);
    
    echo "✅ Role set to: Cashier\n";
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "  USER ACCOUNT CREATED SUCCESSFULLY! 🎉\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\n";
    echo "Login Credentials:\n";
    echo "  Username: {$username}\n";
    echo "  Password: {$password}\n";
    echo "  Role: Cashier\n";
    echo "\n";
    echo "You can now login at:\n";
    echo "  http://your-domain.com/login\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error creating user: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
