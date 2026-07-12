<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Ensure the request is valid
if (!isset($_GET['membership_type']) || empty($_GET['membership_type'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Membership type is required']);
    exit;
}

$membership_type = sanitizeInput($_GET['membership_type']);

// Validate membership type
$valid_memberships = [
    'active', 'gram_panchayat', 'block', 'tehsil',
    'district', 'mandal', 'state', 'national'
];
if (!in_array($membership_type, $valid_memberships)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid membership type']);
    exit;
}

try {
    // Fetch designations for the given membership type
    $stmt = $db->prepare("
        SELECT designation, designation_hindi 
        FROM membership_designations 
        WHERE membership_type = ? AND status = 'active' 
        ORDER BY sort_order
    ");
    $stmt->execute([$membership_type]);
    $designations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'designations' => $designations
    ]);
} catch (PDOException $e) {
    logError('Error fetching designations: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to fetch designations']);
}
?>