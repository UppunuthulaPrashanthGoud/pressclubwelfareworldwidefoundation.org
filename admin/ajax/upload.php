<?php
require_once '../../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_image') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        try {
            $uploadResult = uploadFile($_FILES['file'], 'img/uploads');
            
            if ($uploadResult['success']) {
                echo json_encode([
                    'success' => true,
                    'url' => SITE_URL . '/img/uploads/' . $uploadResult['filename']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $uploadResult['message']
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No file uploaded or upload error'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?>
