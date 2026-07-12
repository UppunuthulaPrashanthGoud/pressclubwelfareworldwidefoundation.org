<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_participation') {
    // CSRF validation
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRF($csrf_token)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    // Collect and sanitize form data
    $event_id = sanitizeInput($_POST['event_id'] ?? '');
    $name = sanitizeInput($_POST['name'] ?? '');
    $mobile = sanitizeInput($_POST['mobile'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $is_ngo = sanitizeInput($_POST['is_ngo'] ?? 'no'); // Changed to lowercase to match table enum
    $ngo_id = sanitizeInput($_POST['ngo_id'] ?? '');
    $donation_detail = sanitizeInput($_POST['donation_detail'] ?? '');

    // Validation
    if (empty($name) || empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid name or mobile number.']);
        exit;
    }

    // Optional NGO ID validation (only if NGO)
    if ($is_ngo === 'yes' && empty($ngo_id)) { // Changed to lowercase 'yes'
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'NGO ID is required if you are from an NGO.']);
        exit;
    }

    try {
        // Check if the user has already participated in this event
        $stmt = $db->prepare("SELECT COUNT(*) FROM participations WHERE event_id = ? AND mobile = ?");
        $stmt->execute([$event_id, $mobile]);
        if ($stmt->fetchColumn() > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'You have already registered for this event.']);
            exit;
        }

        // Insert participation data
        $stmt = $db->prepare("INSERT INTO participations (event_id, name, mobile, city, is_ngo, ngo_id, donation_detail, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$event_id, $name, $mobile, $city, $is_ngo, $ngo_id ?: null, $donation_detail ?: null]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Participation recorded successfully.']);
    } catch (PDOException $e) {
        logError('Participation error: ' . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to record participation. Please try again later.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}