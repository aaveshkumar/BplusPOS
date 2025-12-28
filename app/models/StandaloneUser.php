<?php
/**
 * Standalone User Model
 * Handles POS user authentication for standalone database
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../helpers/PasswordHash.php';

class StandaloneUser extends BaseModel {
    protected $table = 'users';

    public function getUserByUsername($username) {
        $sql = "SELECT 
                    id,
                    username,
                    password,
                    email,
                    display_name as name,
                    first_name,
                    last_name,
                    role,
                    status
                FROM users
                WHERE username = ? AND status = 'active'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function verifyPassword($password, $hashedPassword) {
        $hasher = new PasswordHash(8, true);
        return $hasher->CheckPassword($password, $hashedPassword);
    }

    public function getPOSUsers() {
        $sql = "SELECT 
                    id,
                    username,
                    email,
                    display_name as name,
                    role
                FROM users
                WHERE status = 'active'
                AND role IN ('admin', 'manager', 'cashier', 'stock_manager')
                ORDER BY display_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllUsers() {
        $sql = "SELECT 
                    id,
                    username,
                    email,
                    display_name as name,
                    role as pos_role,
                    status,
                    created_at,
                    last_login
                FROM users
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createUser($data) {
        $hasher = new PasswordHash(8, true);
        $hashedPassword = $hasher->HashPassword($data['password']);
        
        $sql = "INSERT INTO users 
                (username, password, email, display_name, first_name, last_name, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['username'],
            $hashedPassword,
            $data['email'],
            $data['display_name'] ?? $data['username'],
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['role'] ?? 'cashier'
        ]);
        
        return $this->db->lastInsertId();
    }

    public function updateUser($userId, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $params[] = $data['email'];
        }
        
        if (isset($data['display_name'])) {
            $fields[] = 'display_name = ?';
            $params[] = $data['display_name'];
        }
        
        if (isset($data['password'])) {
            $hasher = new PasswordHash(8, true);
            $fields[] = 'password = ?';
            $params[] = $hasher->HashPassword($data['password']);
        }
        
        if (isset($data['role'])) {
            $fields[] = 'role = ?';
            $params[] = $data['role'];
        }
        
        if (empty($fields)) return false;
        
        $fields[] = 'updated_at = NOW()';
        $params[] = $userId;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteUser($userId) {
        $sql = "UPDATE users SET status = 'inactive' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }

    public function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
}
