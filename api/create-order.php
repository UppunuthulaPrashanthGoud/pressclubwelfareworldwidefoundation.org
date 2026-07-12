<?php
/**
 * API endpoint to create Razorpay orders
 */
require_once '../config/config.php';
require_once '../config/razorpay-order-api.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['amount']) || !is_numeric($input['amount'])) {
        throw new Exception('Valid amount is required');
    }
    
    $amount = floatval($input['amount']);
    $currency = $input['currency'] ?? 'INR';
    $receipt = $input['receipt'] ?? null;
    $notes = $input['notes'] ?? [];
    
    // Validate minimum amount
    if ($amount < 1) {
        throw new Exception('Amount must be at least ₹1');
    }
    
    // Add metadata to notes
    $notes['source'] = 'NDF Website';
    $notes['timestamp'] = date('Y-m-d H:i:s');
    $notes['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Create order using Razorpay API
    global $razorpayAPI;
    $order = $razorpayAPI->createOrder($amount, $currency, $receipt, $notes);
    
    // Return order details
    echo json_encode([
        'success' => true,
        'order' => [
            'id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'receipt' => $order['receipt'],
            'status' => $order['status']
        ],
        'key_id' => RAZORPAY_KEY_ID
    ]);
    
} catch (Exception $e) {
    logError('Create order API error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
