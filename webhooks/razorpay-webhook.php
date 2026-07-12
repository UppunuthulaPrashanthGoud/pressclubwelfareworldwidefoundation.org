<?php
/**
 * Razorpay Webhook Handler
 * Handles payment status updates from Razorpay
 */
require_once '../config/config.php';
require_once '../config/razorpay-order-api.php';

// Log webhook received
logError('Razorpay webhook received: ' . file_get_contents('php://input'));

try {
    // Get webhook payload
    $payload = file_get_contents('php://input');
    $data = json_decode($payload, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON payload');
    }
    
    // Verify webhook signature (if webhook secret is configured)
    $config = getPaymentConfig();
    if (!empty($config['webhook_secret'])) {
        $expectedSignature = hash_hmac('sha256', $payload, $config['webhook_secret']);
        $receivedSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
        
        if (!hash_equals($expectedSignature, $receivedSignature)) {
            throw new Exception('Invalid webhook signature');
        }
    }
    
    // Process webhook event
    $event = $data['event'] ?? '';
    $paymentEntity = $data['payload']['payment']['entity'] ?? null;
    $orderEntity = $data['payload']['order']['entity'] ?? null;
    
    switch ($event) {
        case 'payment.captured':
            handlePaymentCaptured($paymentEntity);
            break;
            
        case 'payment.failed':
            handlePaymentFailed($paymentEntity);
            break;
            
        case 'order.paid':
            handleOrderPaid($orderEntity);
            break;
            
        default:
            logError("Unhandled webhook event: $event");
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    
} catch (Exception $e) {
    logError('Webhook processing error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function handlePaymentCaptured($payment) {
    try {
        $db = getDbConnection();
        $paymentId = $payment['id'];
        $orderId = $payment['order_id'];
        $amount = $payment['amount'] / 100; // Convert from paisa to rupees
        
        // Update donations table
        $stmt = $db->prepare("
            UPDATE donations 
            SET status = 'completed', payment_id = ?, order_id = ?
            WHERE order_id = ? OR (payment_id = ? AND status = 'pending')
        ");
        $stmt->execute([$paymentId, $orderId, $orderId, $paymentId]);
        
        // Update users table (for membership payments)
        $stmt = $db->prepare("
            UPDATE users 
            SET status = 'approved', payment_id = ?, order_id = ?, valid_until = DATE_ADD(NOW(), INTERVAL 1 YEAR)
            WHERE order_id = ? OR (payment_id = ? AND status = 'pending')
        ");
        $stmt->execute([$paymentId, $orderId, $orderId, $paymentId]);
        
        // Update camps table
        $stmt = $db->prepare("
            UPDATE camps 
            SET status = 'completed', payment_id = ?, order_id = ?
            WHERE order_id = ? OR (payment_id = ? AND status = 'pending')
        ");
        $stmt->execute([$paymentId, $orderId, $orderId, $paymentId]);
        
        logError("Payment captured successfully: $paymentId for order: $orderId");
        
    } catch (Exception $e) {
        logError('Handle payment captured error: ' . $e->getMessage());
    }
}

function handlePaymentFailed($payment) {
    try {
        $db = getDbConnection();
        $paymentId = $payment['id'];
        $orderId = $payment['order_id'];
        
        // Update all relevant tables to failed status
        $tables = ['donations', 'users', 'camps'];
        foreach ($tables as $table) {
            $stmt = $db->prepare("
                UPDATE $table 
                SET status = 'failed', payment_id = ?, order_id = ?
                WHERE order_id = ? OR payment_id = ?
            ");
            $stmt->execute([$paymentId, $orderId, $orderId, $paymentId]);
        }
        
        logError("Payment failed: $paymentId for order: $orderId");
        
    } catch (Exception $e) {
        logError('Handle payment failed error: ' . $e->getMessage());
    }
}

function handleOrderPaid($order) {
    try {
        global $razorpayAPI;
        $orderId = $order['id'];
        
        // Update order status
        $razorpayAPI->updatePaymentStatus($orderId, null, 'paid', $order);
        
        logError("Order paid: $orderId");
        
    } catch (Exception $e) {
        logError('Handle order paid error: ' . $e->getMessage());
    }
}
?>
