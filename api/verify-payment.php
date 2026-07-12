<?php
/**
 * API endpoint to verify Razorpay payments
 */
require_once '../config/config.php';
require_once '../config/razorpay-order-api.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $orderId = $input['razorpay_order_id'];
    $paymentId = $input['razorpay_payment_id'];
    $signature = $input['razorpay_signature'];
    
    // Verify payment signature
    global $razorpayAPI;
    $isValid = $razorpayAPI->verifyPaymentSignature($orderId, $paymentId, $signature);
    
    if (!$isValid) {
        throw new Exception('Invalid payment signature');
    }
    
    // Get payment details from Razorpay
    $paymentDetails = $razorpayAPI->getPaymentDetails($paymentId);
    
    // Update payment status in database
    $razorpayAPI->updatePaymentStatus($orderId, $paymentId, 'paid', $paymentDetails);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'payment' => [
            'id' => $paymentId,
            'order_id' => $orderId,
            'status' => $paymentDetails['status'] ?? 'captured',
            'amount' => $paymentDetails['amount'] ?? 0,
            'method' => $paymentDetails['method'] ?? 'unknown'
        ]
    ]);
    
} catch (Exception $e) {
    logError('Verify payment API error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
