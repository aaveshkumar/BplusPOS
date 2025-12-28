<?php
/**
 * Dashboard Controller
 * Handles dashboard and reports
 */

require_once ROOT_PATH . '/app/controllers/BaseController.php';
require_once ROOT_PATH . '/app/models/Order.php';
require_once ROOT_PATH . '/app/models/Product.php';

class DashboardController extends BaseController {
    
    /**
     * Show dashboard
     */
    public function index() {
        $this->requireAuth();
        
        $orderModel = new Order();
        $productModel = new Product();
        
        // Get today's sales
        $todaySales = $orderModel->getTodaysSales();
        
        // Get recent orders
        $recentOrders = $orderModel->getRecentOrders(10, 0);
        
        // Get low stock products
        $lowStockProducts = $productModel->getLowStockProducts($this->config['pos']['low_stock_threshold']);
        
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'todaySales' => $todaySales,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts
        ]);
    }
    
    /**
     * Sales reports
     */
    public function sales() {
        $this->requireAuth();
        $this->requirePermission('view_reports');
        
        $startDate = getGet('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = getGet('end_date', date('Y-m-d'));
        
        $orderModel = new Order();
        $salesData = $orderModel->getSalesByDateRange($startDate, $endDate);
        
        $this->view('dashboard/sales', [
            'title' => 'Sales Report',
            'salesData' => $salesData,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Reports page
     */
    public function reports() {
        $this->requireAuth();
        $this->requirePermission('view_reports');
        
        $this->view('dashboard/reports', [
            'title' => 'Reports'
        ]);
    }
}
