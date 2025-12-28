<?php
/**
 * User Model
 * Handles POS user authentication and management
 */

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';

    /**
     * Get user by username
     */
    public function getUserByUsername($username) {
        $sql = "SELECT 
                    u.ID as id,
                    u.user_login as username,
                    u.user_pass as password,
                    u.user_email as email,
                    u.display_name as name
                FROM {$this->prefix}users u
                WHERE u.user_login = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Get user's POS role (custom meta if exists, otherwise map from WordPress role)
            $role = $this->getUserRole($user['id']);
            $user['role'] = $role;
        }
        
        return $user;
    }
    
    /**
     * Determine user's POS role based on WordPress capabilities or custom pos_role meta
     */
    private function getUserRole($userId) {
        // First check for custom pos_role meta field
        $sql = "SELECT meta_value FROM {$this->prefix}usermeta 
                WHERE user_id = ? AND meta_key = 'pos_role' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $posRole = $stmt->fetchColumn();
        
        if ($posRole && in_array($posRole, ['admin', 'cashier', 'stock_manager'])) {
            return $posRole;
        }
        
        // If no custom pos_role, map from WordPress capabilities
        $sql = "SELECT meta_value FROM {$this->prefix}usermeta 
                WHERE user_id = ? AND meta_key = 'wp_capabilities' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $capabilities = $stmt->fetchColumn();
        
        if ($capabilities) {
            $caps = @unserialize($capabilities);
            if (is_array($caps)) {
                // Map WordPress roles to POS roles
                if (isset($caps['administrator']) || isset($caps['yith_pos_manager'])) {
                    return 'admin';
                } elseif (isset($caps['shop_manager']) || isset($caps['editor'])) {
                    return 'stock_manager';
                } elseif (isset($caps['cashier']) || isset($caps['shop_worker'])) {
                    return 'cashier';
                }
            }
        }
        
        // Default: no role
        return null;
    }

    /**
     * Verify password
     * Note: WooCommerce uses WordPress password hashing (PHPass)
     */
    public function verifyPassword($password, $hashedPassword) {
        // For simplicity, we'll use a basic check
        // In production, you should use WordPress's wp_check_password equivalent
        require_once __DIR__ . '/../helpers/PasswordHash.php';
        $hasher = new PasswordHash(8, true);
        return $hasher->CheckPassword($password, $hashedPassword);
    }

    /**
     * Get all POS users
     */
    public function getPOSUsers() {
        $sql = "SELECT 
                    u.ID as id,
                    u.user_login as username,
                    u.user_email as email,
                    u.display_name as name,
                    um.meta_value as role
                FROM {$this->prefix}users u
                INNER JOIN {$this->prefix}usermeta um ON u.ID = um.user_id AND um.meta_key = 'pos_role'
                WHERE um.meta_value IN ('admin', 'cashier', 'stock_manager', 'manager')
                ORDER BY u.display_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get all WordPress users (for user management)
     */
    public function getAllUsers() {
        $sql = "SELECT 
                    u.ID as id,
                    u.user_login as username,
                    u.user_email as email,
                    u.display_name as name,
                    u.user_registered as created_at,
                    MAX(CASE WHEN um.meta_key = 'pos_role' THEN um.meta_value END) as pos_role,
                    MAX(CASE WHEN um.meta_key = '{$this->prefix}capabilities' THEN um.meta_value END) as wp_capabilities
                FROM {$this->prefix}users u
                LEFT JOIN {$this->prefix}usermeta um ON u.ID = um.user_id
                GROUP BY u.ID
                ORDER BY u.user_registered DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Create new WordPress user
     */
    public function createUser($data) {
        // Insert into users table
        $sql = "INSERT INTO {$this->prefix}users 
                (user_login, user_pass, user_email, user_nicename, display_name, user_registered)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $username = $data['username'];
        require_once __DIR__ . '/../helpers/PasswordHash.php';
        $hasher = new PasswordHash(8, true);
        $password = $hasher->HashPassword($data['password']);
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $username,
            $password,
            $data['email'],
            $username,
            $data['display_name'] ?? $username
        ]);
        
        $userId = $this->db->lastInsertId();
        
        // Set POS role if provided
        if (!empty($data['role'])) {
            $sql = "INSERT INTO {$this->prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, 'pos_role', ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $data['role']]);
        }
        
        // Set WordPress capabilities
        $capabilities = serialize(['subscriber' => true]);
        $sql = "INSERT INTO {$this->prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, '{$this->prefix}capabilities', ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $capabilities]);
        
        // Set user level
        $sql = "INSERT INTO {$this->prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, '{$this->prefix}user_level', '0')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $userId;
    }
    
    /**
     * Update user
     */
    public function updateUser($userId, $data) {
        // Update user table
        $updateFields = [];
        $params = [];
        
        if (isset($data['email'])) {
            $updateFields[] = 'user_email = ?';
            $params[] = $data['email'];
        }
        
        if (isset($data['display_name'])) {
            $updateFields[] = 'display_name = ?';
            $params[] = $data['display_name'];
        }
        
        if (isset($data['password'])) {
            require_once __DIR__ . '/../helpers/PasswordHash.php';
            $hasher = new PasswordHash(8, true);
            $updateFields[] = 'user_pass = ?';
            $params[] = $hasher->HashPassword($data['password']);
        }
        
        if (!empty($updateFields)) {
            $sql = "UPDATE {$this->prefix}users SET " . implode(', ', $updateFields) . " WHERE ID = ?";
            $params[] = $userId;
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
        
        // Update POS role if provided
        if (isset($data['role'])) {
            // Check if pos_role meta exists
            $sql = "SELECT umeta_id FROM {$this->prefix}usermeta WHERE user_id = ? AND meta_key = 'pos_role'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $sql = "UPDATE {$this->prefix}usermeta SET meta_value = ? WHERE user_id = ? AND meta_key = 'pos_role'";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$data['role'], $userId]);
            } else {
                $sql = "INSERT INTO {$this->prefix}usermeta (user_id, meta_key, meta_value) VALUES (?, 'pos_role', ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId, $data['role']]);
            }
        }
        
        return true;
    }
    
    /**
     * Delete user
     */
    public function deleteUser($userId) {
        // Delete user meta first
        $sql = "DELETE FROM {$this->prefix}usermeta WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        // Delete user
        $sql = "DELETE FROM {$this->prefix}users WHERE ID = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return true;
    }
}
