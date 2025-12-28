<?php
/**
 * Model Factory
 * Automatically loads the correct model based on database type (WordPress or Standalone)
 */

require_once __DIR__ . '/../../config/database.php';

class ModelFactory {
    private static $instances = [];
    private static $dbType = null;

    private static function getDbType() {
        if (self::$dbType === null) {
            $db = Database::getInstance();
            self::$dbType = $db->getDatabaseType();
        }
        return self::$dbType;
    }

    public static function create($modelName) {
        $cacheKey = $modelName . '_' . self::getDbType();
        
        if (isset(self::$instances[$cacheKey])) {
            return self::$instances[$cacheKey];
        }

        $dbType = self::getDbType();
        
        if ($dbType === 'standalone') {
            $className = 'Standalone' . $modelName;
            $filePath = __DIR__ . "/Standalone{$modelName}.php";
        } else {
            $className = $modelName;
            $filePath = __DIR__ . "/{$modelName}.php";
        }
        
        if (!file_exists($filePath)) {
            $className = $modelName;
            $filePath = __DIR__ . "/{$modelName}.php";
        }
        
        if (file_exists($filePath)) {
            require_once $filePath;
            if (class_exists($className)) {
                self::$instances[$cacheKey] = new $className();
                return self::$instances[$cacheKey];
            }
        }
        
        throw new Exception("Model not found: {$modelName} (tried {$className})");
    }

    public static function getProduct() {
        return self::create('Product');
    }

    public static function getCustomer() {
        return self::create('Customer');
    }

    public static function getOrder() {
        return self::create('Order');
    }

    public static function getUser() {
        return self::create('User');
    }

    public static function isStandalone() {
        return self::getDbType() === 'standalone';
    }

    public static function isWordPress() {
        return self::getDbType() === 'wordpress';
    }

    public static function reset() {
        self::$instances = [];
        self::$dbType = null;
    }
}
