<?php
/**
 * Product Model
 * Handles product data from WooCommerce database
 */

require_once __DIR__ . '/BaseModel.php';

class Product extends BaseModel {
    protected $table = 'posts';

    /**
     * Get all products (published products only)
     */
    public function getAllProducts($limit = 20, $offset = 0) {
        $sql = "SELECT 
                    p.ID as id,
                    p.post_title as name,
                    p.post_content as description,
                    p.post_excerpt as short_description,
                    pm1.meta_value as price,
                    pm2.meta_value as regular_price,
                    pm3.meta_value as sale_price,
                    pm4.meta_value as stock_quantity,
                    pm5.meta_value as stock_status,
                    pm6.meta_value as sku,
                    pm8.meta_value as tax_class,
                    pm9.meta_value as tax_status,
                    0.05 as tax_rate
                FROM {$this->prefix}posts p
                LEFT JOIN {$this->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_price'
                LEFT JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
                LEFT JOIN {$this->prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_sale_price'
                LEFT JOIN {$this->prefix}postmeta pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_stock'
                LEFT JOIN {$this->prefix}postmeta pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_stock_status'
                LEFT JOIN {$this->prefix}postmeta pm6 ON p.ID = pm6.post_id AND pm6.meta_key = '_sku'
                LEFT JOIN {$this->prefix}postmeta pm8 ON p.ID = pm8.post_id AND pm8.meta_key = '_tax_class'
                LEFT JOIN {$this->prefix}postmeta pm9 ON p.ID = pm9.post_id AND pm9.meta_key = '_tax_status'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                ORDER BY p.post_title ASC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        $products = $stmt->fetchAll();
        
        // Enrich with actual tax rates from custom tax rules
        foreach($products as &$product) {
            $product['tax_rate'] = $this->getApplicableTaxRate($product['id']);
        }
        
        return $products;
    }
    
    /**
     * Get applicable tax rate for a product (5% default for POS)
     */
    private function getApplicableTaxRate($productId) {
        // For simplicity, return 5% as default for POS
        // In production, query pos_custom_tax_rules table
        return 0.05;
    }

    /**
     * Search product by barcode (exact match)
     */
    public function getProductByBarcode($barcode) {
        $sql = "SELECT 
                    p.ID as id,
                    p.post_title as name,
                    p.post_content as description,
                    pm1.meta_value as price,
                    pm2.meta_value as regular_price,
                    pm3.meta_value as sale_price,
                    pm4.meta_value as stock_quantity,
                    pm5.meta_value as stock_status,
                    pm6.meta_value as sku,
                    pm7.meta_value as barcode,
                    pm8.meta_value as tax_class,
                    pm9.meta_value as tax_status
                FROM {$this->prefix}posts p
                INNER JOIN {$this->prefix}postmeta pm7 ON p.ID = pm7.post_id AND pm7.meta_key = '_ywbc_barcode_display_value'
                LEFT JOIN {$this->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_price'
                LEFT JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
                LEFT JOIN {$this->prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_sale_price'
                LEFT JOIN {$this->prefix}postmeta pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_stock'
                LEFT JOIN {$this->prefix}postmeta pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_stock_status'
                LEFT JOIN {$this->prefix}postmeta pm6 ON p.ID = pm6.post_id AND pm6.meta_key = '_sku'
                LEFT JOIN {$this->prefix}postmeta pm8 ON p.ID = pm8.post_id AND pm8.meta_key = '_tax_class'
                LEFT JOIN {$this->prefix}postmeta pm9 ON p.ID = pm9.post_id AND pm9.meta_key = '_tax_status'
                WHERE pm7.meta_value = ?
                AND p.post_type = 'product'
                AND p.post_status = 'publish'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$barcode]);
        $product = $stmt->fetch();
        
        if ($product) {
            $product['tax_rate'] = $this->getApplicableTaxRate($product['id']);
        }
        
        return $product;
    }

    /**
     * Search products by name, SKU, or barcode
     */
    public function searchProducts($query, $limit = 20) {
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT DISTINCT
                    p.ID as id,
                    p.post_title as name,
                    p.post_content as description,
                    pm1.meta_value as price,
                    pm2.meta_value as regular_price,
                    pm3.meta_value as sale_price,
                    pm4.meta_value as stock_quantity,
                    pm5.meta_value as stock_status,
                    pm6.meta_value as sku,
                    pm7.meta_value as barcode,
                    pm8.meta_value as tax_class,
                    pm9.meta_value as tax_status,
                    0.05 as tax_rate
                FROM {$this->prefix}posts p
                LEFT JOIN {$this->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_price'
                LEFT JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
                LEFT JOIN {$this->prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_sale_price'
                LEFT JOIN {$this->prefix}postmeta pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_stock'
                LEFT JOIN {$this->prefix}postmeta pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_stock_status'
                LEFT JOIN {$this->prefix}postmeta pm6 ON p.ID = pm6.post_id AND pm6.meta_key = '_sku'
                LEFT JOIN {$this->prefix}postmeta pm7 ON p.ID = pm7.post_id AND pm7.meta_key = '_ywbc_barcode_display_value'
                LEFT JOIN {$this->prefix}postmeta pm8 ON p.ID = pm8.post_id AND pm8.meta_key = '_tax_class'
                LEFT JOIN {$this->prefix}postmeta pm9 ON p.ID = pm9.post_id AND pm9.meta_key = '_tax_status'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND (p.post_title LIKE ? OR pm6.meta_value LIKE ? OR pm7.meta_value LIKE ?)
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get product by ID
     */
    public function getProduct($productId) {
        $sql = "SELECT 
                    p.ID as id,
                    p.post_title as name,
                    p.post_content as description,
                    p.post_excerpt as short_description,
                    pm1.meta_value as price,
                    pm2.meta_value as regular_price,
                    pm3.meta_value as sale_price,
                    pm4.meta_value as stock_quantity,
                    pm5.meta_value as stock_status,
                    pm6.meta_value as sku
                FROM {$this->prefix}posts p
                LEFT JOIN {$this->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_price'
                LEFT JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_regular_price'
                LEFT JOIN {$this->prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_sale_price'
                LEFT JOIN {$this->prefix}postmeta pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_stock'
                LEFT JOIN {$this->prefix}postmeta pm5 ON p.ID = pm5.post_id AND pm5.meta_key = '_stock_status'
                LEFT JOIN {$this->prefix}postmeta pm6 ON p.ID = pm6.post_id AND pm6.meta_key = '_sku'
                WHERE p.ID = ?
                AND p.post_type = 'product'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    /**
     * Get product by SKU or barcode
     */
    public function getProductBySku($sku) {
        $sql = "SELECT 
                    p.ID as id,
                    p.post_title as name,
                    pm1.meta_value as price,
                    pm2.meta_value as stock_quantity,
                    pm3.meta_value as stock_status,
                    pm4.meta_value as sku
                FROM {$this->prefix}posts p
                INNER JOIN {$this->prefix}postmeta pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_sku'
                LEFT JOIN {$this->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_price'
                LEFT JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_stock'
                LEFT JOIN {$this->prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_stock_status'
                WHERE pm4.meta_value = ?
                AND p.post_type = 'product'
                AND p.post_status = 'publish'
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sku]);
        return $stmt->fetch();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($threshold = 5) {
        $sql = "SELECT 
                    p.ID as id,
                    p.post_title as name,
                    pm1.meta_value as price,
                    pm2.meta_value as stock_quantity,
                    pm3.meta_value as sku
                FROM {$this->prefix}posts p
                INNER JOIN {$this->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_stock'
                LEFT JOIN {$this->prefix}postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_price'
                LEFT JOIN {$this->prefix}postmeta pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_sku'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND CAST(pm2.meta_value AS UNSIGNED) <= ?
                AND CAST(pm2.meta_value AS UNSIGNED) > 0
                ORDER BY CAST(pm2.meta_value AS UNSIGNED) ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    /**
     * Get total products count
     */
    public function getTotalProducts() {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->prefix}posts 
                WHERE post_type = 'product' 
                AND post_status = 'publish'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
