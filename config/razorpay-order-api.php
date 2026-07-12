<?php
/**
 * Razorpay Order API Integration
 * This file handles order creation and payment processing
 */

require_once 'config.php';

class RazorpayOrderAPI {
    private $keyId;
    private $keySecret;
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
        $config = $this->getPaymentConfig();
        $this->keyId = $config['razorpay_key_id'];
        $this->keySecret = $config['razorpay_key_secret'];
    }
    
    private function getPaymentConfig() {
        try {
            $environment = $this->detectEnvironment();
            $stmt = $this->db->prepare("SELECT * FROM razorpay_config WHERE environment = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$environment]);
            $config = $stmt->fetch();
            
            if (!$config) {
                throw new Exception("No active Razorpay configuration found for environment: $environment");
            }
            
            return $config;
        } catch (Exception $e) {
            logError('Razorpay config error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function detectEnvironment() {
        return (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) ? 'local' : 'live';
    }
    
    /**
     * Create Razorpay Order
     */
    public function createOrder($amount, $currency = 'INR', $receipt = null, $notes = []) {
        try {
            // Validate amount
            if ($amount < 1) {
                throw new Exception('Amount must be at least ₹1');
            }
            
            // Convert amount to paisa (smallest currency unit)
            $amountInPaisa = intval($amount * 100);
            
            // Generate receipt if not provided
            if (!$receipt) {
                $receipt = 'rcpt_' . time() . '_' . rand(1000, 9999);
            }
            
            // Prepare order data
            $orderData = [
                'amount' => $amountInPaisa,
                'currency' => $currency,
                'receipt' => $receipt,
                'payment_capture' => 1, // Auto capture payment
                'notes' => $notes
            ];
            
            // Make API call to Razorpay
            $response = $this->makeApiCall('orders', 'POST', $orderData);
            
            if (!$response || !isset($response['id'])) {
                throw new Exception('Failed to create Razorpay order');
            }
            
            // Store order in database
            $this->storeOrder($response);
            
            return $response;
            
        } catch (Exception $e) {
            logError('Razorpay order creation error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Verify payment signature
     */
    public function verifyPaymentSignature($orderId, $paymentId, $signature) {
        try {
            $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
            return hash_equals($expectedSignature, $signature);
        } catch (Exception $e) {
            logError('Payment signature verification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get payment details
     */
    public function getPaymentDetails($paymentId) {
        try {
            return $this->makeApiCall("payments/$paymentId", 'GET');
        } catch (Exception $e) {
            logError('Get payment details error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get order details
     */
    public function getOrderDetails($orderId) {
        try {
            return $this->makeApiCall("orders/$orderId", 'GET');
        } catch (Exception $e) {
            logError('Get order details error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Store order in database
     */
    private function storeOrder($orderData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO razorpay_orders (
                    order_id, amount, currency, receipt, status, 
                    created_at, razorpay_data
                ) VALUES (?, ?, ?, ?, ?, NOW(), ?)
                ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                razorpay_data = VALUES(razorpay_data)
            ");
            
            $stmt->execute([
                $orderData['id'],
                $orderData['amount'],
                $orderData['currency'],
                $orderData['receipt'],
                $orderData['status'],
                json_encode($orderData)
            ]);
            
        } catch (Exception $e) {
            logError('Store order error: ' . $e->getMessage());
            // Don't throw here as order creation was successful
        }
    }
    
    /**
     * Make API call to Razorpay
     */
    private function makeApiCall($endpoint, $method = 'GET', $data = null) {
        $url = "https://api.razorpay.com/v1/$endpoint";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->keyId . ':' . $this->keySecret,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: NDF-Website/1.0'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMsg = isset($decodedResponse['error']['description']) 
                ? $decodedResponse['error']['description'] 
                : "HTTP Error: $httpCode";
            throw new Exception($errorMsg);
        }
        
        return $decodedResponse;
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $paymentId, $status, $additionalData = []) {
        try {
            $stmt = $this->db->prepare("
                UPDATE razorpay_orders 
                SET payment_id = ?, status = ?, payment_data = ?, updated_at = NOW()
                WHERE order_id = ?
            ");
            
            $stmt->execute([
                $paymentId,
                $status,
                json_encode($additionalData),
                $orderId
            ]);
            
        } catch (Exception $e) {
            logError('Update payment status error: ' . $e->getMessage());
        }
    }
}

// Create global instance
$razorpayAPI = new RazorpayOrderAPI();
?>
