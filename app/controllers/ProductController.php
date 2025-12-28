<?php
/**
 * Product Controller
 * Handles product viewing and searching
 */

require_once ROOT_PATH . '/app/controllers/BaseController.php';
require_once ROOT_PATH . '/app/models/ModelFactory.php';

class ProductController extends BaseController {
    
    /**
     * List all products
     */
    public function index() {
        $this->requireAuth();
        
        $page = (int) getGet('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $productModel = ModelFactory::getProduct();
        $products = $productModel->getAllProducts($limit, $offset);
        $totalProducts = $productModel->getTotalProducts();
        $totalPages = ceil($totalProducts / $limit);
        
        $this->view('products/index', [
            'title' => 'Products',
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }
    
    /**
     * Search products
     */
    public function search() {
        $this->requireAuth();
        
        $query = sanitize(getGet('q', ''));
        
        if (empty($query)) {
            $this->json(['success' => false, 'products' => []], 400);
        }
        
        $productModel = ModelFactory::getProduct();
        $products = $productModel->searchProducts($query, 20);
        
        $this->json([
            'success' => true,
            'products' => $products
        ]);
    }
    
    /**
     * Show single product
     */
    public function show($id) {
        $this->requireAuth();
        
        $productModel = ModelFactory::getProduct();
        $product = $productModel->getProduct($id);
        
        if (!$product) {
            http_response_code(404);
            echo "Product not found";
            exit;
        }
        
        $this->json([
            'success' => true,
            'product' => $product
        ]);
    }
}
