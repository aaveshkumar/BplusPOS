<?php
/**
 * Customer Controller
 * Handles customer viewing and searching
 */

require_once ROOT_PATH . '/app/controllers/BaseController.php';
require_once ROOT_PATH . '/app/models/Customer.php';

class CustomerController extends BaseController {
    
    /**
     * List all customers
     */
    public function index() {
        $this->requireAuth();
        
        $customerModel = new Customer();
        $customers = $customerModel->getAllCustomers(50, 0);
        
        $this->view('customers/index', [
            'title' => 'Customers',
            'customers' => $customers
        ]);
    }
    
    /**
     * Search customers
     */
    public function search() {
        $this->requireAuth();
        
        $query = sanitize(getGet('q', ''));
        
        if (empty($query)) {
            $this->json(['success' => false, 'customers' => []], 400);
        }
        
        $customerModel = new Customer();
        $customers = $customerModel->searchCustomers($query, 20);
        
        $this->json([
            'success' => true,
            'customers' => $customers
        ]);
    }
    
    /**
     * Show single customer
     */
    public function show($id) {
        $this->requireAuth();
        
        $customerModel = new Customer();
        $customer = $customerModel->getCustomer($id);
        
        if (!$customer) {
            $this->json(['success' => false, 'message' => 'Customer not found'], 404);
        }
        
        // Get customer orders
        $orders = $customerModel->getCustomerOrders($id, 10);
        
        $this->json([
            'success' => true,
            'customer' => $customer,
            'orders' => $orders
        ]);
    }
}
