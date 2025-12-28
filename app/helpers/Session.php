<?php
/**
 * Session Manager
 * Handles secure session management
 */

class Session {
    private static $started = false;

    /**
     * Start session
     */
    public static function start() {
        if (self::$started) {
            return;
        }

        $config = require __DIR__ . '/../../config/config.php';
        $sessionConfig = $config['session'];

        // Configure session
        ini_set('session.cookie_httponly', $sessionConfig['httponly']);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', $sessionConfig['secure']);
        ini_set('session.gc_maxlifetime', $sessionConfig['lifetime']);

        // Set session save path
        if (!empty($sessionConfig['path'])) {
            if (!is_dir($sessionConfig['path'])) {
                mkdir($sessionConfig['path'], 0777, true);
            }
            session_save_path($sessionConfig['path']);
        }

        session_name($sessionConfig['name']);
        session_start();

        self::$started = true;

        // Regenerate session ID periodically for security
        if (($sessionConfig['session_regenerate'] ?? true) && !isset($_SESSION['last_regeneration'])) {
            self::regenerate();
        }

        // Check for session timeout
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $sessionConfig['lifetime']) {
                self::destroy();
                return;
            }
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Set session value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    /**
     * Destroy session
     */
    public static function destroy() {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        self::$started = false;
    }

    /**
     * Set flash message
     */
    public static function setFlash($key, $message, $type = 'info') {
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Get flash message
     */
    public static function getFlash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }
        return null;
    }

    /**
     * Check if flash message exists
     */
    public static function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }
}
