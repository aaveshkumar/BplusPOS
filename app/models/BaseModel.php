<?php
/**
 * Base Model
 * Parent class for all models with common database operations
 */

class BaseModel {
    protected $db;
    protected $table;
    protected $prefix;

    public function __construct() {
        $dbInstance = Database::getInstance();
        $this->db = $dbInstance->getConnection();
        $this->prefix = $dbInstance->getPrefix();
    }

    /**
     * Find record by ID
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->prefix}{$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Find all records
     */
    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->prefix}{$this->table}";
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Find by condition
     */
    public function findWhere($conditions, $limit = null) {
        $where = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $where[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM {$this->prefix}{$this->table} WHERE " . implode(' AND ', $where);
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $limit === 1 ? $stmt->fetch() : $stmt->fetchAll();
    }

    /**
     * Execute custom query
     */
    protected function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Count records
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->prefix}{$this->table}";
        
        if (!empty($conditions)) {
            $where = [];
            $params = [];
            
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            
            $sql .= " WHERE " . implode(' AND ', $where);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
