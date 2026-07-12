<?php
require_once 'config/config.php';
require_once 'admin/includes/receipt-generator.php';

// Get parameters from URL
$donation_id = $_GET['donation_id'] ?? 0;
$user_id = $_GET['user_id'] ?? 0;
$camp_id = $_GET['camp_id'] ?? 0;
$type = $_GET['type'] ?? '';
$download = isset($_GET['download']);

// Database connection
$db = getDbConnection();

try {
    if ($donation_id) {
        // Get donation data
        $stmt = $db->prepare("SELECT * FROM donations WHERE id = ?");
        $stmt->execute([$donation_id]);
        $data = $stmt->fetch();

        if (!$data) {
            die('Donation not found');
        }

        // Generate donation receipt
        $options = [
            'type' => 'donation',
            'auto_print' => false,
            'show_buttons' => true,
            'download' => $download
        ];

        $receipt_html = generateUniversalReceipt($data, $options);
        
    } elseif ($user_id && $type === 'registration') {
        // Get user data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $data = $stmt->fetch();

        if (!$data) {
            die('User not found');
        }

        // Generate registration receipt
        $options = [
            'type' => 'registration',
            'auto_print' => false,
            'show_buttons' => true,
            'download' => $download
        ];

        $receipt_html = generateUniversalReceipt($data, $options);
        
    } elseif ($camp_id) {
        // Get camp data
        $stmt = $db->prepare("SELECT * FROM camps WHERE id = ?");
        $stmt->execute([$camp_id]);
        $data = $stmt->fetch();

        if (!$data) {
            die('Camp registration not found');
        }

        // Generate camp receipt
        $options = [
            'type' => 'camp',
            'auto_print' => false,
            'show_buttons' => true,
            'download' => $download
        ];

        $receipt_html = generateUniversalReceipt($data, $options);
        
    } else {
        die('Invalid request parameters');
    }

    // Set headers for download if requested
    if ($download) {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="receipt_' . ($donation_id ?: $user_id ?: $camp_id) . '.html"');
    }

    // Output the receipt HTML
    echo $receipt_html;

} catch (Exception $e) {
    logError('Receipt generation error: ' . $e->getMessage());
    die('Error generating receipt. Please try again later.');
}
?>
