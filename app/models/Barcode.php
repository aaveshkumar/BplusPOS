<?php
/**
 * Barcode Model
 * Handles barcode generation, management, and printing
 */

require_once __DIR__ . '/BaseModel.php';

class Barcode extends BaseModel {
    protected $table = 'pos_product_barcodes';
    
    /**
     * Add barcode to product
     */
    public function addBarcode($productId, $barcode, $type = 'EAN13', $isPrimary = false) {
        if ($isPrimary) {
            $sql = "UPDATE {$this->table} SET is_primary = 0 WHERE product_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
        }
        
        $sql = "INSERT INTO {$this->table} (product_id, barcode, barcode_type, is_primary)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE is_primary = VALUES(is_primary)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$productId, $barcode, $type, $isPrimary ? 1 : 0]);
    }
    
    /**
     * Get product barcodes
     */
    public function getProductBarcodes($productId) {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY is_primary DESC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get product by barcode
     */
    public function getProductByBarcode($barcode) {
        $sql = "SELECT product_id FROM {$this->table} WHERE barcode = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$barcode]);
        return $stmt->fetch();
    }
    
    /**
     * Delete barcode
     */
    public function deleteBarcode($barcodeId) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$barcodeId]);
    }
    
    /**
     * Generate EAN13 barcode
     */
    public function generateEAN13($prefix = '890') {
        $code = $prefix . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        
        $oddSum = 0;
        $evenSum = 0;
        
        for ($i = 0; $i < 12; $i++) {
            if ($i % 2 == 0) {
                $oddSum += (int)$code[$i];
            } else {
                $evenSum += (int)$code[$i];
            }
        }
        
        $total = $oddSum + ($evenSum * 3);
        $checkDigit = (10 - ($total % 10)) % 10;
        
        return $code . $checkDigit;
    }
    
    /**
     * Generate Code128 barcode
     */
    public function generateCode128($prefix = 'SKU') {
        return $prefix . date('Ymd') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Validate EAN13
     */
    public function validateEAN13($barcode) {
        if (strlen($barcode) != 13 || !ctype_digit($barcode)) {
            return false;
        }
        
        $oddSum = 0;
        $evenSum = 0;
        
        for ($i = 0; $i < 12; $i++) {
            if ($i % 2 == 0) {
                $oddSum += (int)$barcode[$i];
            } else {
                $evenSum += (int)$barcode[$i];
            }
        }
        
        $total = $oddSum + ($evenSum * 3);
        $checkDigit = (10 - ($total % 10)) % 10;
        
        return $checkDigit == (int)$barcode[12];
    }
    
    /**
     * Get barcode SVG
     */
    public function getBarcodeSVG($barcode, $type = 'EAN13', $width = 2, $height = 50) {
        if ($type === 'EAN13') {
            return $this->generateEAN13SVG($barcode, $width, $height);
        } elseif ($type === 'CODE128') {
            return $this->generateCode128SVG($barcode, $width, $height);
        }
        
        return null;
    }
    
    /**
     * Generate EAN13 SVG (simplified)
     */
    private function generateEAN13SVG($barcode, $width = 2, $height = 50) {
        $patterns = [
            '0' => '0001101', '1' => '0011001', '2' => '0010011', '3' => '0111101',
            '4' => '0100011', '5' => '0110001', '6' => '0101111', '7' => '0111011',
            '8' => '0110111', '9' => '0001011'
        ];
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . (95 * $width) . '" height="' . ($height + 20) . '">';
        $x = 0;
        
        $svg .= '<rect x="' . $x . '" y="0" width="' . $width . '" height="' . $height . '" fill="black"/>';
        $x += $width * 2;
        
        for ($i = 0; $i < 13; $i++) {
            $digit = $barcode[$i];
            $pattern = $patterns[$digit];
            
            for ($j = 0; $j < 7; $j++) {
                if ($pattern[$j] === '1') {
                    $svg .= '<rect x="' . $x . '" y="0" width="' . $width . '" height="' . $height . '" fill="black"/>';
                }
                $x += $width;
            }
        }
        
        $svg .= '<text x="' . (95 * $width / 2) . '" y="' . ($height + 15) . '" text-anchor="middle" font-family="monospace" font-size="12">' . $barcode . '</text>';
        $svg .= '</svg>';
        
        return $svg;
    }
    
    /**
     * Generate Code128 SVG (simplified)
     */
    private function generateCode128SVG($barcode, $width = 2, $height = 50) {
        $totalWidth = strlen($barcode) * 11 * $width;
        
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $totalWidth . '" height="' . ($height + 20) . '">';
        $svg .= '<rect x="0" y="0" width="' . $totalWidth . '" height="' . $height . '" fill="white"/>';
        
        $x = 0;
        for ($i = 0; $i < strlen($barcode); $i++) {
            $svg .= '<rect x="' . $x . '" y="0" width="' . ($width * 2) . '" height="' . $height . '" fill="black"/>';
            $x += $width * 11;
        }
        
        $svg .= '<text x="' . ($totalWidth / 2) . '" y="' . ($height + 15) . '" text-anchor="middle" font-family="monospace" font-size="12">' . htmlspecialchars($barcode) . '</text>';
        $svg .= '</svg>';
        
        return $svg;
    }
    
    /**
     * Get products needing barcodes
     */
    public function getProductsWithoutBarcodes($limit = 50) {
        $prefix = $this->db->getPrefix();
        
        $sql = "SELECT 
                p.ID as product_id,
                p.post_title as product_name,
                pm.meta_value as sku
                FROM {$prefix}posts p
                LEFT JOIN {$prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_sku'
                LEFT JOIN {$this->table} pb ON p.ID = pb.product_id
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND pb.id IS NULL
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Bulk generate barcodes for products
     */
    public function bulkGenerateBarcodes($productIds, $type = 'EAN13', $prefix = '890') {
        $generated = [];
        
        foreach ($productIds as $productId) {
            $barcode = ($type === 'EAN13') ? $this->generateEAN13($prefix) : $this->generateCode128('SKU');
            
            if ($this->addBarcode($productId, $barcode, $type, true)) {
                $generated[] = [
                    'product_id' => $productId,
                    'barcode' => $barcode,
                    'type' => $type
                ];
            }
        }
        
        return $generated;
    }
}
