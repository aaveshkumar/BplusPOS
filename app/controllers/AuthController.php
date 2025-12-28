<?php
/**
 * Authentication Controller
 * Handles user login and logout
 */

require_once ROOT_PATH . '/app/controllers/BaseController.php';
require_once ROOT_PATH . '/app/models/User.php';

class AuthController extends BaseController {
    
    /**
     * Show login page
     */
    public function login() {
        // If already logged in, redirect to POS
        if (isLoggedIn()) {
            $this->redirect('/pos');
        }
        
        $this->view('auth/login', [
            'title' => 'Login - ' . $this->config['app']['name']
        ]);
    }
    
    /**
     * Process login
     */
    public function processLogin() {
        if (!isPost()) {
            $this->redirect('/login');
        }
        
        // Validate CSRF token
        $this->validateCsrf();
        
        $username = sanitize(getPost('username'));
        $password = getPost('password'); // Don't sanitize password
        
        if (empty($username) || empty($password)) {
            Session::setFlash('error', 'Please enter username and password', 'danger');
            $this->redirect('/login');
        }
        
        $userModel = new User();
        $user = $userModel->getUserByUsername($username);
        
        if (!$user) {
            Session::setFlash('error', 'Invalid username or password', 'danger');
            $this->redirect('/login');
        }
        
        // Verify password
        if (!$userModel->verifyPassword($password, $user['password'])) {
            Session::setFlash('error', 'Invalid username or password', 'danger');
            $this->redirect('/login');
        }
        
        // Check if user has POS role
        if (empty($user['role']) || !in_array($user['role'], ['admin', 'cashier', 'stock_manager'])) {
            Session::setFlash('error', 'You do not have permission to access the POS system', 'danger');
            $this->redirect('/login');
        }
        
        // Set session
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('user', [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
        
        // Regenerate session ID for security
        Session::regenerate();
        
        // Log login
        logMessage("User {$username} logged in", 'info');
        
        // Redirect to POS
        $this->redirect('/pos');
    }
    
    /**
     * Logout
     */
    public function logout() {
        $username = Session::get('username', 'Unknown');
        
        // Log logout
        logMessage("User {$username} logged out", 'info');
        
        // Destroy session
        Session::destroy();
        
        // Redirect to login
        $this->redirect('/login');
    }
}
