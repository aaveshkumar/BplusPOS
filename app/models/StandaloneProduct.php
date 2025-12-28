<?php
/**
 * Standalone Product Model
 * Handles product data for standalone (non-WordPress) database
 */

require_once __DIR__ . '/BaseModel.php';

class StandaloneProduct extends BaseModel {
    protected $table = 'products';

    public function getAllProducts($limit = 20, $offset = 0) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.description,
                    p.short_description,
                    p.price,
                    p.regular_price,
                    p.sale_price,
                    p.stock_quantity,
                    p.stock_status,
                    p.sku,
                    p.barcode,
                    p.tax_class,
                    p.tax_status,
                    p.category_id,
                    c.name as category_name,
                    p.image_url,
                    0.18 as tax_rate
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'publish'
                ORDER BY p.name ASC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getProductByBarcode($barcode) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.description,
                    p.price,
                    p.regular_price,
                    p.sale_price,
                    p.stock_quantity,
                    p.stock_status,
                    p.sku,
                    p.barcode,
                    p.tax_class,
                    p.tax_status,
                    0.18 as tax_rate
                FROM products p
                WHERE p.barcode = ?
                AND p.status = 'publish'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$barcode]);
        return $stmt->fetch();
    }

    public function searchProducts($query, $limit = 20) {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.description,
                    p.price,
                    p.regular_price,
                    p.sale_price,
                    p.stock_quantity,
                    p.stock_status,
                    p.sku,
                    p.barcode,
                    p.tax_class,
                    p.tax_status,
                    0.18 as tax_rate
                FROM products p
                WHERE p.status = 'publish'
                AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)
                ORDER BY p.name ASC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll();
    }

    public function getProduct($productId) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.description,
                    p.short_description,
                    p.price,
                    p.regular_price,
                    p.sale_price,
                    p.stock_quantity,
                    p.stock_status,
                    p.sku,
                    p.barcode,
                    p.tax_class,
                    p.tax_status,
                    p.category_id,
                    p.image_url
                FROM products p
                WHERE p.id = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    public function getProductBySku($sku) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.stock_quantity,
                    p.stock_status,
                    p.sku
                FROM products p
                WHERE p.sku = ?
                AND p.status = 'publish'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sku]);
        return $stmt->fetch();
    }

    public function getLowStockProducts($threshold = 5) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.stock_quantity,
                    p.sku
                FROM products p
                WHERE p.status = 'publish'
                AND p.manage_stock = 1
                AND p.stock_quantity <= ?
                AND p.stock_quantity > 0
                ORDER BY p.stock_quantity ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    public function getTotalProducts() {
        $sql = "SELECT COUNT(*) as total FROM products WHERE status = 'publish'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function updateStock($productId, $quantity, $operation = 'decrease') {
        if ($operation === 'decrease') {
            $sql = "UPDATE products SET stock_quantity = stock_quantity - ?, 
                    stock_status = CASE WHEN stock_quantity - ? <= 0 THEN 'outofstock' ELSE stock_status END,
                    updated_at = NOW()
                    WHERE id = ?";
        } else {
            $sql = "UPDATE products SET stock_quantity = stock_quantity + ?,
                    stock_status = CASE WHEN stock_quantity + ? > 0 THEN 'instock' ELSE stock_status END,
                    updated_at = NOW()
                    WHERE id = ?";
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantity, $quantity, $productId]);
    }

    public function createProduct($data) {
        $sql = "INSERT INTO products 
                (name, slug, sku, barcode, description, short_description,
                 regular_price, sale_price, price, stock_quantity, stock_status,
                 manage_stock, tax_status, tax_class, category_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'publish')";
        
        $slug = strtolower(str_replace(' ', '-', $data['name']));
        $price = !empty($data['sale_price']) ? $data['sale_price'] : $data['regular_price'];
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $slug,
            $data['sku'] ?? null,
            $data['barcode'] ?? null,
            $data['description'] ?? '',
            $data['short_description'] ?? '',
            $data['regular_price'] ?? 0,
            $data['sale_price'] ?? null,
            $price,
            $data['stock_quantity'] ?? 0,
            $data['stock_status'] ?? 'instock',
            $data['manage_stock'] ?? 1,
            $data['tax_status'] ?? 'taxable',
            $data['tax_class'] ?? 'standard',
            $data['category_id'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function updateProduct($productId, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'sku', 'barcode', 'description', 'short_description',
                         'regular_price', 'sale_price', 'price', 'stock_quantity', 
                         'stock_status', 'tax_class', 'category_id'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) return false;
        
        $fields[] = "updated_at = NOW()";
        $params[] = $productId;
        
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
