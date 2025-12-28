<?php
/**
 * Database Connection Manager
 * Handles PDO connections for both WordPress and Standalone databases
 */

class Database {
    private static $instance = null;
    private $connection;
    private $config;
    private $dbType;
    private $prefix;

    private function __construct() {
        $this->config = require __DIR__ . '/config.php';
        $this->dbType = $this->config['database_type'] ?? 'wordpress';
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function resetInstance() {
        self::$instance = null;
    }

    private function connect() {
        if ($this->dbType === 'standalone') {
            $db = $this->config['standalone_database'];
            $this->prefix = '';
        } else {
            $db = $this->config['database'];
            $this->prefix = $db['prefix'] ?? 'wp_';
        }
        
        try {
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ];

            $this->connection = new PDO($dsn, $db['username'], $db['password'], $options);
            
            if ($this->config['app']['environment'] === 'development') {
                error_log("Database connection established ({$this->dbType} mode)");
            }
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function getDatabaseType() {
        return $this->dbType;
    }

    public function isStandalone() {
        return $this->dbType === 'standalone';
    }

    public function isWordPress() {
        return $this->dbType === 'wordpress';
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            throw $e;
        }
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollBack();
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
