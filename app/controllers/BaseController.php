<?php
/**
 * Base Controller
 * Parent class for all controllers with common methods
 */

class BaseController {
    protected $config;

    public function __construct() {
        $this->config = require ROOT_PATH . '/config/config.php';
    }

    /**
     * Render view
     */
    protected function view($viewPath, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Include layout header
        include ROOT_PATH . '/app/views/layouts/header.php';
        
        // Include the view
        $viewFile = ROOT_PATH . '/app/views/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            die("View not found: {$viewPath}");
        }
        
        // Include layout footer
        include ROOT_PATH . '/app/views/layouts/footer.php';
    }

    /**
     * Render partial view (without layout)
     */
    protected function partial($viewPath, $data = []) {
        extract($data);
        
        $viewFile = ROOT_PATH . '/app/views/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            die("View not found: {$viewPath}");
        }
    }

    /**
     * JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect
     */
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }

    /**
     * Check authentication
     */
    protected function requireAuth() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    /**
     * Check permission
     */
    protected function requirePermission($permission) {
        $this->requireAuth();
        
        if (!hasPermission($permission)) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf() {
        // Get token from POST data or JSON
        $token = getPost('csrf_token');
        
        // If not in POST, check JSON body
        if (empty($token)) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $token = $data['csrf_token'] ?? null;
        }
        
        // If still no token, check headers
        if (empty($token)) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
        
        // Skip CSRF for authenticated API requests (already logged in)
        if (empty($token) && isLoggedIn()) {
            return; // Allow authenticated users
        }
        
        if (!verifyCsrfToken($token)) {
            http_response_code(403);
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }
    }
}
