<?php
/**
 * WooCommerce REST API Client
 * Handles all write operations to WooCommerce via REST API
 */

class WooCommerceAPI {
    private $siteUrl;
    private $consumerKey;
    private $consumerSecret;
    private $apiVersion;
    private $verifySsl;

    public function __construct() {
        $config = require __DIR__ . '/../../config/config.php';
        $wc = $config['woocommerce'];
        
        $this->siteUrl = rtrim($wc['site_url'], '/');
        $this->consumerKey = $wc['consumer_key'];
        $this->consumerSecret = $wc['consumer_secret'];
        $this->apiVersion = $wc['api_version'];
        $this->verifySsl = $wc['verify_ssl'];
    }

    /**
     * Make API request
     * 
     * @param string $endpoint API endpoint (e.g., 'products', 'orders')
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param array $data Request data
     * @return array|false
     */
    private function request($endpoint, $method = 'GET', $data = []) {
        $url = "{$this->siteUrl}/wp-json/{$this->apiVersion}/{$endpoint}";
        
        $ch = curl_init();
        
        // Set basic auth
        curl_setopt($ch, CURLOPT_USERPWD, $this->consumerKey . ':' . $this->consumerSecret);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
        
        // Set method and data
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log("WooCommerce API Error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 400) {
            error_log("WooCommerce API HTTP Error {$httpCode}: " . print_r($result, true));
            return false;
        }
        
        return $result;
    }

    /**
     * Create a new order
     * 
     * @param array $orderData Order data
     * @return array|false
     */
    public function createOrder($orderData) {
        return $this->request('orders', 'POST', $orderData);
    }

    /**
     * Update order
     * 
     * @param int $orderId
     * @param array $orderData
     * @return array|false
     */
    public function updateOrder($orderId, $orderData) {
        return $this->request("orders/{$orderId}", 'PUT', $orderData);
    }

    /**
     * Get order details
     * 
     * @param int $orderId
     * @return array|false
     */
    public function getOrder($orderId) {
        return $this->request("orders/{$orderId}", 'GET');
    }

    /**
     * Update product stock
     * 
     * @param int $productId
     * @param int $quantity
     * @return array|false
     */
    public function updateStock($productId, $quantity) {
        return $this->request("products/{$productId}", 'PUT', [
            'stock_quantity' => $quantity
        ]);
    }

    /**
     * Create refund
     * 
     * @param int $orderId
     * @param array $refundData
     * @return array|false
     */
    public function createRefund($orderId, $refundData) {
        return $this->request("orders/{$orderId}/refunds", 'POST', $refundData);
    }

    /**
     * Apply coupon
     * 
     * @param string $code
     * @return array|false
     */
    public function getCoupon($code) {
        return $this->request("coupons?code={$code}", 'GET');
    }

    /**
     * Create customer
     * 
     * @param array $customerData
     * @return array|false
     */
    public function createCustomer($customerData) {
        return $this->request('customers', 'POST', $customerData);
    }

    /**
     * Update customer
     * 
     * @param int $customerId
     * @param array $customerData
     * @return array|false
     */
    public function updateCustomer($customerId, $customerData) {
        return $this->request("customers/{$customerId}", 'PUT', $customerData);
    }
}
