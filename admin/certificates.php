<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';
require_once 'includes/text_utils.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . ADMIN_URL . "index.php");
    exit;
}

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$certificate = [];

// Handle AJAX request for designations
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'get_designations') {
    header('Content-Type: application/json');
    
    if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    $user_id = (int)$_GET['user_id'];
    $db = getDbConnection();
    try {
        $stmt = $db->prepare("SELECT membership_type FROM users WHERE id = ? AND status = 'approved'");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found or not approved']);
            exit;
        }

        $membership_type = $user['membership_type'];
        $stmt = $db->prepare("SELECT designation, designation_hindi FROM membership_designations WHERE membership_type = ? AND status = 'active' ORDER BY sort_order");
        $stmt->execute([$membership_type]);
        $designations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'designations' => $designations
        ]);
    } catch (Exception $e) {
        logError('Error fetching designations: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch designations']);
    }
    exit;
}

// Handle AJAX request for user photo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'get_user_photo') {
    header('Content-Type: application/json');
    $user_id = (int)$_GET['user_id'];
    $db = getDbConnection();
    try {
        $stmt = $db->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => !!$user,
            'photo' => $user['profile_image'] ?? ''
        ]);
    } catch (Exception $e) {
        logError('Error fetching user photo: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch user photo']);
    }
    exit;
}

// Fetch site configuration from settings table
$db = getDbConnection();
$siteConfig = [];
$configKeys = ['organization_address', 'organization_phone', 'organization_email'];
foreach ($configKeys as $key) {
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $siteConfig[$key] = $result ? htmlspecialchars($result['setting_value']) : '';
}

// Define signatories using constants from config.php
$signatories = [
    'Chairman' => [
        'name' => CERTIFICATE_CHAIRMAN_NAME,
        'title' => CERTIFICATE_CHAIRMAN_TITLE,
        'signature_path' => SITE_URL . '/img/signature.png'
    ],
    'Secretary' => [
        'name' => CERTIFICATE_SECRETARY_NAME,
        'title' => CERTIFICATE_SECRETARY_TITLE,
        'signature_path' => SITE_URL . '/img/signature1.png'
    ]
];

// Fetch designations and photos for initial dropdown
$designations = [];
$profile_photo = '';
$certificate_photo = '';
if ($action === 'add' && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $stmt = $db->prepare("SELECT u.membership_type, u.profile_image, u.name FROM users u WHERE u.id = ? AND status = 'approved'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $membership_type = $user['membership_type'];
        $profile_photo = $user['profile_image'] ?? '';
        $stmt = $db->prepare("SELECT designation, designation_hindi FROM membership_designations WHERE membership_type = ? AND status = 'active' ORDER BY sort_order");
        $stmt->execute([$membership_type]);
        $designations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "User not found.";
        $action = 'list';
    }
} elseif ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT c.*, u.membership_type, u.profile_image AS profile_photo FROM certificates c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
    $stmt->execute([$id]);
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($certificate) {
        $membership_type = $certificate['membership_type'];
        $profile_photo = $certificate['profile_photo'] ?? '';
        $certificate_photo = $certificate['photo_path'] ?? '';
        $stmt = $db->prepare("SELECT designation, designation_hindi FROM membership_designations WHERE membership_type = ? AND status = 'active' ORDER BY sort_order");
        $stmt->execute([$membership_type]);
        $designations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "Certificate not found.";
        $action = 'list';
    }
}

// Initialize certificate array for new certificates
if ($action === 'add') {
    $certificate = [
        'certificate_type' => '',
        'recipient_name' => '',
        'post_name' => '',
        'event_or_reason' => '',
        'issue_date' => '',
        'end_date' => '',
        'photo_path' => '',
        'user_id' => '',
        'status' => 'active'
    ];
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
        $user_id = (int)$_GET['user_id'];
        $stmt = $db->prepare("SELECT name FROM users WHERE id = ? AND status = 'approved'");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $certificate['recipient_name'] = $user['name'];
            $certificate['user_id'] = $user_id;
            $certificate['certificate_type'] = isset($_GET['certificate_type']) && $_GET['certificate_type'] === 'Birthday Wishes' ? 'Birthday Wishes' : '';
            $certificate['event_or_reason'] = $certificate['certificate_type'] === 'Birthday Wishes' ? 'Birthday Wishes' : '';
            $certificate['issue_date'] = date('Y-m-d'); // Default to today
        } else {
            $error = "User not found.";
            $action = 'list';
        }
    }
} elseif ($action === 'edit' && $id > 0) {
    if (!$certificate) {
        $error = "Certificate not found.";
        $action = 'list';
    }
}

// Update the certificate content configuration to use constants
$certificateContent = [
    'organization_name'   => ORGANIZATION_NAME,
    'organization_name_hindi' => ORGANIZATION_NAME_HINDI, // Keep if needed, or translate if required
    'header_text'        => 'A Dedicated Effort Towards Change',
    'registration_number' => 'Registration No.: 238',
    'address'            => $siteConfig['organization_address'],
    'email'              => $siteConfig['organization_email'],
    'phone'              => $siteConfig['organization_phone'],
    'chairman_name'      => CERTIFICATE_CHAIRMAN_NAME,
    'chairman_title'     => CERTIFICATE_CHAIRMAN_TITLE,
    'template_path'      => SITE_URL . '/templates/certificate-template.png',
    'signature_path'     => SITE_URL . '/img/signature.png',
    'secretary_signature_path' => SITE_URL . '/img/signature1.png',
    'secretary_name'     => CERTIFICATE_SECRETARY_NAME,
    'secretary_title'    => CERTIFICATE_SECRETARY_TITLE,
    'seal_path'          => SITE_URL . '/img/seal.png'
];

// Function to generate a unique certificate number dynamically using ORGANIZATION_NAME_SHORT
function generateCertificateNumber() {
    global $db;
    $stmt = $db->query("SELECT certificate_no FROM certificates ORDER BY id DESC LIMIT 1");
    $lastCertificate = $stmt->fetch();
    
    $newNumber = 1;
    if ($lastCertificate && !empty($lastCertificate['certificate_no'])) {
        // Safely extract the numeric part to make it robust against prefix changes
        $numericPart = preg_replace('/[^0-9]/', '', $lastCertificate['certificate_no']);
        $newNumber = ((int)$numericPart) + 1;
    }
    
    $prefix = defined('ORGANIZATION_NAME_SHORT') ? ORGANIZATION_NAME_SHORT : 'WPEWF';
    return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $certificate_type = isset($_POST['certificate_type']) ? sanitizeInput($_POST['certificate_type']) : '';
            $recipient_name = isset($_POST['recipient_name']) ? sanitizeInput($_POST['recipient_name']) : '';
            $post_name = isset($_POST['post_name']) ? sanitizeInput($_POST['post_name']) : '';
            $event_or_reason = isset($_POST['event_or_reason']) ? sanitizeInput($_POST['event_or_reason']) : '';
            $issue_date = isset($_POST['issue_date']) ? sanitizeInput($_POST['issue_date']) : '';
            $end_date = isset($_POST['end_date']) ? sanitizeInput($_POST['end_date']) : null;
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            // Valid certificate types
            $validCertificateTypes = ['Appointment', 'Appreciation', 'Participation', 'Achievement', 'Birthday Wishes'];
            
            // Validate designation based on user's membership_type
            $stmt = $db->prepare("SELECT membership_type FROM users WHERE id = ? AND status = 'approved'");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $validDesignations = [];
            if ($user && $certificate_type !== 'Birthday Wishes') {
                $membership_type = $user['membership_type'];
                $stmt = $db->prepare("SELECT designation FROM membership_designations WHERE membership_type = ? AND status = 'active'");
                $stmt->execute([$membership_type]);
                $validDesignations = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'designation');
            }
            
            // Enhanced validation
            if (!in_array($certificate_type, $validCertificateTypes)) {
                $error = "Invalid certificate type.";
            } elseif (empty($recipient_name) || ($certificate_type !== 'Birthday Wishes' && empty($post_name)) || empty($event_or_reason) || empty($issue_date) || empty($user_id)) {
                $error = "Please fill all required fields.";
            } elseif (!$user) {
                $error = "User not found or not approved.";
            } elseif ($certificate_type !== 'Birthday Wishes' && !in_array($post_name, $validDesignations)) {
                $error = "Invalid designation. Please select according to user's membership level.";
            } elseif (!in_array($status, ['active', 'inactive'])) {
                $error = "Invalid status.";
            } else {
                try {
                    $photo_path = '';
                    if ($formAction === 'edit') {
                        $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
                        $stmt = $db->prepare("SELECT photo_path FROM certificates WHERE id = ?");
                        $stmt->execute([$cert_id]);
                        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);
                        $photo_path = $currentData['photo_path'] ?? '';
                    }

                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['photo'], 'img/certificates');
                        if ($uploadResult['success']) {
                            // Delete old photo if exists
                            if ($formAction === 'edit' && !empty($photo_path)) {
                                $oldPhotoPath = __DIR__ . '/../img/certificates/' . $photo_path;
                                if (file_exists($oldPhotoPath)) {
                                    unlink($oldPhotoPath);
                                }
                            }
                            $photo_path = $uploadResult['filename'];
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                    
                    if ($formAction === 'add') {
                        $certificate_no = generateCertificateNumber();
                        $stmt = $db->prepare("
                            INSERT INTO certificates (certificate_type, certificate_no, recipient_name, post_name, event_or_reason, issue_date, end_date, photo_path, status, user_id, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$certificate_type, $certificate_no, $recipient_name, $post_name, $event_or_reason, $issue_date, $end_date, $photo_path, $status, $user_id]);
                    } else {
                        $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
                        $stmt = $db->prepare("
                            UPDATE certificates 
                            SET certificate_type = ?, recipient_name = ?, post_name = ?, 
                                event_or_reason = ?, issue_date = ?, end_date = ?, 
                                photo_path = ?, status = ?, user_id = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $success = $stmt->execute([$certificate_type, $recipient_name, $post_name, $event_or_reason, $issue_date, $end_date, $photo_path, $status, $user_id, $cert_id]);
                        if (!$success) {
                            throw new Exception("Failed to update certificate in database.");
                        }
                    }
                    
                    $success = "Certificate successfully " . ($formAction === 'add' ? 'created!' : 'updated!');
                    header("Location: certificates.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    logError('Certificate processing error: ' . $e->getMessage());
                    $error = "Error: " . $e->getMessage();
                }
            }
        } elseif ($formAction === 'delete') {
            $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT photo_path FROM certificates WHERE id = ?");
                $stmt->execute([$cert_id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $db->prepare("DELETE FROM certificates WHERE id = ?");
                $stmt->execute([$cert_id]);
                
                if (!empty($data['photo_path'])) {
                    $photoPath = __DIR__ . '/../img/certificates/' . $data['photo_path'];
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }
                $success = "Certificate successfully deleted!";
                header("Location: certificates.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                logError('Certificate deletion error: ' . $e->getMessage());
                $error = "Deletion error: " . $e->getMessage();
            }
        }
    }
}

// Get certificates for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM certificates");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT c.*, u.name as user_name, u.profile_image AS profile_photo, u.dob FROM certificates c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("SELECT designation, designation_hindi FROM membership_designations WHERE status = 'active'");
    $stmt->execute();
    $allDesignations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalRecords / $limit);
    
    // Fetch users with birthdays today
    $today = date('Y-m-d'); // For testing, use '2025-09-14'; in production, use CURRENT_DATE
    $stmt = $db->prepare("SELECT id, name, email, mobile, dob FROM users WHERE DAY(dob) = DAY(CURRENT_DATE) AND MONTH(dob) = MONTH(CURRENT_DATE) AND status = 'approved' ORDER BY name");
    $stmt->execute();
    $birthdayUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError('Database error in certificate listing: ' . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $certificates = [];
    $allDesignations = [];
    $birthdayUsers = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add New Certificate" : (($action === 'edit') ? "Edit Certificate" : "Certificate Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus"></i> Add New Certificate
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit"></i> Edit Certificate
                <?php else: ?>
                    <i class="fas fa-certificate"></i> Certificate Management
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="certificates.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="certificates.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Certificate
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "Add New Certificate" : "Edit Certificate"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="cert_id" value="<?php echo $certificate['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="certificate_type" class="form-label">Certificate Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="certificate_type" name="certificate_type" required>
                                    <option value="">-- Select Certificate Type --</option>
                                    <option value="Appointment" <?php echo (isset($certificate['certificate_type']) && $certificate['certificate_type'] == 'Appointment') ? 'selected' : ''; ?>>Appointment Certificate</option>
                                    <option value="Appreciation" <?php echo (isset($certificate['certificate_type']) && $certificate['certificate_type'] == 'Appreciation') ? 'selected' : ''; ?>>Appreciation Certificate</option>
                                    <option value="Participation" <?php echo (isset($certificate['certificate_type']) && $certificate['certificate_type'] == 'Participation') ? 'selected' : ''; ?>>Participation Certificate</option>
                                    <option value="Achievement" <?php echo (isset($certificate['certificate_type']) && $certificate['certificate_type'] == 'Achievement') ? 'selected' : ''; ?>>Achievement Certificate</option>
                                    <option value="Birthday Wishes" <?php echo (isset($certificate['certificate_type']) && $certificate['certificate_type'] == 'Birthday Wishes') ? 'selected' : ''; ?>>Birthday Wishes</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="recipient_name" class="form-label">Recipient Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" required 
                                       value="<?php echo htmlspecialchars($certificate['recipient_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                                <?php if ($action === 'edit'): ?>
                                    <input type="hidden" name="user_id" value="<?php echo $certificate['user_id']; ?>">
                                    <input type="text" class="form-control" disabled value="<?php echo htmlspecialchars($certificate['recipient_name'] ?? ''); ?>">
                                <?php else: ?>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">-- Select User --</option>
                                        <?php
                                        $userStmt = $db->query("SELECT id, name FROM users WHERE status = 'approved' ORDER BY name");
                                        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($users as $user) {
                                            $selected = (isset($certificate['user_id']) && $certificate['user_id'] == $user['id']) ? 'selected' : '';
                                            echo "<option value='{$user['id']}' $selected>" . htmlspecialchars($user['name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="post_name" class="form-label">Designation <?php echo ($certificate['certificate_type'] !== 'Birthday Wishes') ? '<span class="text-danger">*</span>' : ''; ?></label>
                                <select class="form-select" id="post_name" name="post_name" <?php echo ($certificate['certificate_type'] !== 'Birthday Wishes') ? 'required' : ''; ?>>
                                    <option value="">-- Select User First --</option>
                                    <?php foreach ($designations as $designation): ?>
                                        <option value="<?php echo htmlspecialchars($designation['designation']); ?>" 
                                                <?php echo (isset($certificate['post_name']) && $certificate['post_name'] == $designation['designation']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($designation['designation_hindi'] . ' (' . $designation['designation'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="photo" class="form-label">Upload Photo</label>
                                <input type="file" class="form-control image-upload" id="photo" name="photo" accept="image/*">
                                <div class="mt-2 image-preview">
                                    <?php if (!empty($certificate_photo) && file_exists(__DIR__ . '/../img/certificates/' . $certificate_photo)): ?>
                                        <img src="<?php echo SITE_URL; ?>/img/certificates/<?php echo htmlspecialchars($certificate_photo); ?>?v=<?php echo time(); ?>" alt="Certificate Photo" class="img-thumbnail" style="max-height: 150px;">
                                        <p class="text-muted mt-1">Uploading a new photo will replace the current one.</p>
                                    <?php elseif (!empty($profile_photo) && file_exists(__DIR__ . '/../uploads/profiles/' . $profile_photo)): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($profile_photo); ?>?v=<?php echo time(); ?>" alt="Profile Photo" class="img-thumbnail" style="max-height: 150px;">
                                        <p class="text-muted mt-1">Profile photo (will be replaced if new photo uploaded).</p>
                                    <?php else: ?>
                                        <p class="text-muted">No photo available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="event_or_reason" class="form-label">Subject / Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="event_or_reason" name="event_or_reason" required rows="4"><?php echo htmlspecialchars($certificate['event_or_reason'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date" required
                                       value="<?php echo htmlspecialchars($certificate['issue_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?php echo htmlspecialchars($certificate['end_date'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo (isset($certificate['status']) && $certificate['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($certificate['status']) && $certificate['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Create Certificate" : "Update"; ?>
                            </button>
                            <a href="certificates.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card mb-4">
                <div class="card-header">
                    <i class="fas fa-users"></i> User List
                </div>
                <div class="card-body">
                    <?php if (count($birthdayUsers) > 0): ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-birthday-cake"></i> Today's Birthdays: 
                            <?php 
                            $birthdayNames = array_map(function($user) { return htmlspecialchars($user['name']); }, $birthdayUsers);
                            echo implode(', ', $birthdayNames); 
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle"></i> No birthdays today.
                        </div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Birth Date</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $userStmt = $db->query("SELECT id, name, email, mobile, dob FROM users WHERE status = 'approved' ORDER BY name");
                                $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($users as $user):
                                    $isBirthdayToday = in_array($user['id'], array_column($birthdayUsers, 'id'));
                                ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($user['name']); ?>
                                            <?php if ($isBirthdayToday): ?>
                                                <span class="badge bg-success ms-2" title="Today is birthday">
                                                    <i class="fas fa-birthday-cake"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $user['dob'] ? date('d-m-Y', strtotime($user['dob'])) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                        <td>
                                            <a href="certificates.php?action=add&user_id=<?php echo $user['id']; ?><?php echo $isBirthdayToday ? '&certificate_type=Birthday Wishes' : ''; ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="<?php echo $isBirthdayToday ? 'Create Birthday Wishes Certificate' : 'Create Certificate'; ?>">
                                                <i class="fas fa-plus"></i> 
                                                <?php echo $isBirthdayToday ? 'Birthday Wishes' : 'Create Certificate'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-certificate"></i> Certificate List
                </div>
                <div class="card-body">
                    <?php if (count($certificates) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Certificate No.</th>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>Birth Date</th>
                                        <th>Designation</th>
                                        <th>Subject</th>
                                        <th>Issue Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificates as $cert): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cert['certificate_no']); ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php 
                                                    $types = [
                                                        'Appointment' => 'Appointment Certificate',
                                                        'Appreciation' => 'Appreciation Certificate',
                                                        'Participation' => 'Participation Certificate',
                                                        'Achievement' => 'Achievement Certificate',
                                                        'Birthday Wishes' => 'Birthday Wishes'
                                                    ];
                                                    echo htmlspecialchars($types[$cert['certificate_type']] ?? $cert['certificate_type']);
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($cert['recipient_name']); ?></td>
                                            <td><?php echo $cert['dob'] ? date('d-m-Y', strtotime($cert['dob'])) : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($cert['post_name'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($cert['event_or_reason']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($cert['issue_date'])); ?></td>
                                            <td><?php echo $cert['end_date'] ? date('d-m-Y', strtotime($cert['end_date'])) : '-'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $cert['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $cert['status'] == 'active' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="certificates.php?action=edit&id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger delete-certificate-btn" data-id="<?php echo $cert['id']; ?>" data-cert-no="<?php echo htmlspecialchars($cert['certificate_no']); ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success generate-certificate" 
                                                        data-certificate='<?php echo json_encode([
                                                            'certificate_no' => $cert['certificate_no'],
                                                            'certificate_type' => $cert['certificate_type'],
                                                            'recipient_name' => $cert['recipient_name'],
                                                            'post_name' => $cert['post_name'],
                                                            'event_or_reason' => $cert['event_or_reason'],
                                                            'issue_date' => $cert['issue_date'],
                                                            'end_date' => $cert['end_date'],
                                                            'effective_photo_path' => $cert['photo_path'] ?: ($cert['profile_photo'] ?? ''),
                                                            'photo_source' => $cert['photo_path'] ? 'certificates' : 'profiles',
                                                            'designation_hindi' => array_filter($allDesignations, function($d) use ($cert) { return $d['designation'] == $cert['post_name']; })[0]['designation_hindi'] ?? $cert['post_name']
                                                        ]); ?>' 
                                                        title="Download">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No certificates found.
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="modal fade" id="deleteCertificateModal" tabindex="-1" aria-labelledby="deleteCertificateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteCertificateModalLabel">Confirm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Do you really want to delete certificate <strong id="certificateNo"></strong>?</p>
                            <p class="text-danger mt-2">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="post" id="deleteCertificateForm">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="cert_id" id="cert_id">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const { jsPDF } = window.jspdf;
    const certificateContent = <?php echo json_encode($certificateContent); ?>;
    
    function stripHtmlTags(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || '';
    }

    function cleanTextForCertificate(text) {
        const tmp = document.createElement('div');
        tmp.innerHTML = text;
        let cleanText = tmp.textContent || tmp.innerText || '';
        cleanText = cleanText.replace(/\s+/g, ' ').trim();
        return cleanText;
    }

    function getCertificateContent(certType, data) {
        const formattedIssueDate = formatDate(data.issue_date);
        const formattedEndDate = data.end_date ? formatDate(data.end_date) : '-';
        
        let content = '';
        
        switch(certType) {
            case 'Appointment':
                content = `
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        We are pleased to inform you that you have been appointed to the important position of <b style="color: #ec5252;">${data.designation_hindi}</b>. This appointment is evidence of your qualification, dedication, and loyalty to the organization.
                    </p>
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        This appointment will be effective from <b style="color: #ec5252;">${formattedIssueDate}</b> to ${data.end_date ? `<b style="color: #ec5252;">${formattedEndDate}</b>` : 'indefinite period'}. It is expected that you will discharge your responsibilities with full honesty and dedication.
                    </p>`;
                break;
                
            case 'Appreciation':
                content = `
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        We extend our heartfelt thanks and appreciation for the excellent work and dedicated service you have performed as <b style="color: #ec5252;">${data.designation_hindi}</b>.
                    </p>
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        Your contribution is invaluable to the organization's progress. This appreciation letter is being provided on <b style="color: #ec5252;">${formattedIssueDate}</b>.
                    </p>`;
                break;
                
            case 'Participation':
                content = `
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        You have actively participated in the organization's activities as <b style="color: #ec5252;">${data.designation_hindi}</b>. Your presence and contribution are commendable.
                    </p>
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        Your participation has played an important role in making the program successful. This participation certificate is being provided on <b style="color: #ec5252;">${formattedIssueDate}</b>.
                    </p>`;
                break;
                
            case 'Achievement':
                content = `
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        This certificate is being provided for your outstanding achievements and qualifications as <b style="color: #ec5252;">${data.designation_hindi}</b>.
                    </p>
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        Your achievements are a matter of pride for the organization and a source of inspiration for other members. This achievement certificate is being provided on <b style="color: #ec5252;">${formattedIssueDate}</b>.
                    </p>`;
                break;
                
            case 'Birthday Wishes':
                content = `
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        Heartfelt birthday wishes to <b style="color: #ec5252;">${data.recipient_name}</b> on their special day! 
                    </p>
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        We wish you longevity, happiness, prosperity, and good health. This greeting letter is being provided on <b style="color: #ec5252;">${formattedIssueDate}</b> from the ${certificateContent.organization_name} family.
                    </p>`;
                break;
                
            default:
                content = `
                    <p style="margin-bottom: 35px; line-height: 1.8; font-size: 22px;">
                        This certificate is being provided for the work you have done as <b style="color: #ec5252;">${data.designation_hindi || 'Member'}</b>.
                    </p>`;
        }
        
        return content;
    }

    function formatDate(inputDate) {
        try {
            let date = new Date(inputDate);
            if (isNaN(date.getTime())) {
                const parts = inputDate.split('-');
                if (parts.length === 3) {
                    date = new Date(parts[0], parts[1]-1, parts[2]);
                }
            }
            
            if (isNaN(date.getTime())) {
                console.error('Invalid date format:', inputDate);
                return inputDate;
            }
            
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = date.getFullYear();
            return `${day}-${month}-${year}`;
        } catch (e) {
            console.error('Date formatting error:', e);
            return inputDate;
        }
    }

    document.querySelectorAll('.generate-certificate').forEach(button => {
        button.addEventListener('click', async function() {
            try {
                console.log('Starting certificate generation...');
                
                let certData;
                try {
                    certData = JSON.parse(this.getAttribute('data-certificate'));
                    console.log('Certificate data parsed:', certData);
                } catch (parseError) {
                    throw new Error('Invalid certificate data: ' + parseError.message);
                }

                if (!certData.certificate_type || !certData.recipient_name || !certData.issue_date) {
                    throw new Error('Missing required certificate data');
                }

                const formattedIssueDate = formatDate(certData.issue_date);
                console.log('Formatted issue date:', formattedIssueDate);

                const certificateTypes = {
                    'Appointment': 'Appointment Certificate',
                    'Appreciation': 'Appreciation Certificate',
                    'Participation': 'Participation Certificate',
                    'Achievement': 'Achievement Certificate',
                    'Birthday Wishes': 'Birthday Wishes'
                };
                
                const certificateTitle = certificateTypes[certData.certificate_type] || 'Certificate';
                console.log('Certificate title:', certificateTitle);

                const certificateMainContent = getCertificateContent(certData.certificate_type, certData);
                console.log('Certificate content generated');

                const container = document.createElement('div');
                container.style.cssText = `
                    width: 1236px;
                    height: 1600px;
                    position: fixed;
                    left: -9999px;
                    font-family: Arial, 'Noto Sans Devanagari', sans-serif;
                    background-color: white;
                    padding: 0;
                    background-image: url('${certificateContent.template_path}');
                    background-size: cover;
                    background-repeat: no-repeat;
                    background-position: center;
                `;
                
                let photoPath = '';
                if (certData.effective_photo_path) {
                    photoPath = certData.photo_source === 'certificates' 
                        ? '<?php echo SITE_URL; ?>/img/certificates/' + certData.effective_photo_path + '?v=' + Date.now()
                        : '<?php echo SITE_URL; ?>/uploads/profiles/' + certData.effective_photo_path + '?v=' + Date.now();
                }
                console.log('Photo path:', photoPath);

                container.innerHTML = `
                <div style="position: relative; height: 100%; padding: 100px 50px;">
                    <div style="text-align: center; position: absolute; top: 320px; left: 50%; transform: translateX(-50%);">
                        <h1 style="font-size: 48px; font-weight: bold; color: #C82333; text-transform: uppercase; margin-bottom: 10px;">
                            ${certificateTitle}
                        </h1>
                    </div>
                    
                    <div style="position: absolute; top: 250px; left: 55px; font-size: 18px; color: #000;">
                        <p>Certificate No.: ${certData.certificate_no}</p>
                    </div>
                    
                    <div style="position: absolute; top: 250px; right: 55px; font-size: 18px; color: #000;">
                        <p>Date: ${formattedIssueDate}</p>
                    </div>
                    
                    <div style="position: absolute; top: 420px; left: 100px; text-align: left; line-height: 1.6; font-family: 'Noto Sans Devanagari', sans-serif; color: #233d63; width: 1000px;">
                        <h3 style="font-size: 24px; margin: 0; font-weight: bold; color: #233d63;">Mr./Mrs.</h3>
                        <p style="font-size: 28px; font-weight: bold; margin: 10px 0; color: #ec5252;">${certData.recipient_name}</p>
                        <p style="font-size: 22px; color: #233d63; margin: 15px auto; text-align: center; width: fit-content;">
                            <b>Subject:</b> ${cleanTextForCertificate(certData.event_or_reason)}
                        </p>
                        <p style="font-size: 24px; margin: 15px 0; color: #444;">Dear,</p>
                    </div>

                    ${photoPath ? `
                        <div style="position: absolute; right: 100px; top: 360px; width: 150px; height: 180px; border-radius: 10px; overflow: hidden; border: 3px solid #233d63;">
                            <img src="${photoPath}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 7px;">
                        </div>
                    ` : ''}
                    
                    <div style="position: absolute; top: 600px; left: 50%; width: 85%; transform: translateX(-50%); font-size: 22px; text-align: justify; color: #233d63; font-family: 'Noto Sans Devanagari', Arial, sans-serif; padding: 30px; letter-spacing: 0.5px; line-height: 1.8;">
                        ${certificateMainContent}
                        <p style="text-align: center; font-weight: 700; color: #233d63; line-height: 1.6; margin-top: 40px; font-size: 24px;">
                            Best Wishes!<br>
                            <span style="color: #ec5252; font-size: 26px;">${certificateContent.organization_name}</span><br>
                            <span style="font-size: 20px; color: #666;">Welcome from the family.</span>
                        </p>
                    </div>
                    
                    <div style="position: absolute; bottom: 200px; right: 80px; text-align: center;">
                      <img src="${certificateContent.signature_path}" alt="Chairman Signature" style="width: 180px; height: auto; margin-bottom: 10px;">
                      <div style="position: relative;">
                        <!-- Seal centered over signature: left: 30px (180px signature width - 120px seal width) / 2 -->
                        <img src="${certificateContent.seal_path}" alt="Chairman Seal" style="width: 120px; height: auto; position: absolute; top: -100px; left: 30px; opacity: 0.9; z-index: 1;">
                      </div>
                        <p style="margin: 5px 0; font-size: 18px; font-weight: bold; color: #000;">
                            ${certificateContent.chairman_name}
                        </p>
                        <p style="margin: 0; font-size: 16px; font-weight: bold; color: #C82333;">
                            ${certificateContent.chairman_title}
                        </p>
                        <p style="margin: 0; font-size: 14px; color: #666;">
                            ${certificateContent.organization_name}
                        </p>
                    </div>
                </div>
                `;
                
                document.body.appendChild(container);
                console.log('Certificate container created and appended');
                
                const images = container.querySelectorAll('img');
                console.log('Waiting for', images.length, 'images to load');
                
                const imagePromises = Array.from(images).map(img => {
                    return new Promise((resolve) => {
                        if (img.complete) {
                            console.log('Image already loaded:', img.src);
                            resolve();
                        } else {
                            img.onload = () => {
                                console.log('Image loaded:', img.src);
                                resolve();
                            };
                            img.onerror = (err) => {
                                console.error('Image load error:', img.src, err);
                                resolve();
                            };
                        }
                    });
                });
                
                await Promise.all(imagePromises);
                console.log('All images loaded or failed');
                
                console.log('Starting html2canvas conversion');
                const canvas = await html2canvas(container, {
                    scale: 2,
                    useCORS: true,
                    logging: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    width: 1236,
                    height: 1600
                });
                console.log('Canvas generated');
                
                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                console.log('Image data generated');
                
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'px',
                    format: [1236, 1600]
                });
                
                pdf.addImage(imgData, 'JPEG', 0, 0, 1236, 1600);
                console.log('PDF created');
                
                const filename = `${certData.certificate_no}_${certData.recipient_name.replace(/[^a-zA-Z0-9\u0900-\u097F]/g, '_')}.pdf`;
                pdf.save(filename);
                console.log('PDF saved as', filename);
                
                document.body.removeChild(container);
                console.log('Certificate generation completed successfully');
                
                showNotification('Certificate downloaded successfully!', 'success');
                
            } catch (error) {
                console.error('Certificate generation error:', error);
                
                let errorMessage = 'Error generating certificate. Please try again.';
                if (error.message.includes('Failed to execute')) {
                    errorMessage += ' (Some resources failed to load)';
                } else if (error.message.includes('Invalid certificate data')) {
                    errorMessage += ' (Invalid certificate data)';
                } else if (error.message.includes('Missing required')) {
                    errorMessage += ' (Missing required information)';
                }
                
                showNotification(errorMessage, 'danger');
                
                const containers = document.querySelectorAll('div[style*="left: -9999px"]');
                containers.forEach(container => {
                    if (container.parentNode) {
                        container.parentNode.removeChild(container);
                    }
                });
            }
        });
    });

    const deleteCertificateModal = document.getElementById('deleteCertificateModal');
    const deleteCertificateForm = document.getElementById('deleteCertificateForm');
    const certificateNoSpan = document.getElementById('certificateNo');
    const certIdInput = document.getElementById('cert_id');
    
    document.querySelectorAll('.delete-certificate-btn').forEach(button => {
        button.addEventListener('click', function() {
            const certificateId = this.getAttribute('data-id');
            const certificateNo = this.getAttribute('data-cert-no');
            
            if (certificateNoSpan) {
                certificateNoSpan.textContent = certificateNo;
            }
            
            if (certIdInput) {
                certIdInput.value = certificateId;
            }
            
            if (deleteCertificateModal && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(deleteCertificateModal);
                modal.show();
            }
        });
    });
    
    if (deleteCertificateForm) {
        deleteCertificateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const certificateNo = certificateNoSpan ? certificateNoSpan.textContent : 'this certificate';
            if (confirm(`Do you really want to delete certificate ${certificateNo}? This action cannot be undone.`)) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                
                fetch('certificates.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        throw new Error('Network response was not ok');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deleting. Please try again.', 'danger');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            }
        });
    }
    
    const userSelect = document.getElementById('user_id');
    const postSelect = document.getElementById('post_name');
    const recipientNameField = document.getElementById('recipient_name');
    const certificateTypeSelect = document.getElementById('certificate_type');
    
    if (userSelect && postSelect && certificateTypeSelect) {
        function updateDesignationRequirement() {
            const isBirthdayWishes = certificateTypeSelect.value === 'Birthday Wishes';
            postSelect.required = !isBirthdayWishes;
            const label = postSelect.parentElement.querySelector('.form-label');
            if (label) {
                label.innerHTML = `Designation ${isBirthdayWishes ? '' : '<span class="text-danger">*</span>'}`;
            }
        }

        certificateTypeSelect.addEventListener('change', updateDesignationRequirement);
        
        userSelect.addEventListener('change', function() {
            const userId = this.value;
            
            postSelect.innerHTML = '<option value="">-- Loading... --</option>';
            
            if (userId && certificateTypeSelect.value !== 'Birthday Wishes') {
                fetch(`certificates.php?ajax=get_designations&user_id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        postSelect.innerHTML = '<option value="">-- Select Designation --</option>';
                        
                        if (data.success && data.designations) {
                            data.designations.forEach(designation => {
                                const option = document.createElement('option');
                                option.value = designation.designation;
                                option.textContent = `${designation.designation_hindi} (${designation.designation})`;
                                postSelect.appendChild(option);
                            });
                        } else {
                            postSelect.innerHTML = '<option value="">-- No designations available --</option>';
                            showNotification('No designations available. Please check user status.', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching designations:', error);
                        postSelect.innerHTML = '<option value="">-- Error: Try again --</option>';
                        showNotification('Error loading designations. Please try again.', 'danger');
                    });
            } else {
                postSelect.innerHTML = '<option value="">-- Select User First --</option>';
            }
            
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            if (recipientNameField && selectedOption.text !== '-- Select User --') {
                recipientNameField.value = selectedOption.text;
            }
            
            fetch(`certificates.php?ajax=get_user_photo&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const preview = document.querySelector('.image-preview');
                    if (data.success && data.photo) {
                        preview.innerHTML = `
                            <img src="<?php echo SITE_URL; ?>/uploads/profiles/${data.photo}?v=${Date.now()}" alt="Profile Photo" class="img-thumbnail" style="max-height: 150px;">
                            <p class="text-muted mt-1">Profile photo (will be replaced if new photo uploaded).</p>
                        `;
                    } else {
                        preview.innerHTML = '<p class="text-muted">No photo available.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching user photo:', error);
                    document.querySelector('.image-preview').innerHTML = '<p class="text-muted">Error loading photo.</p>';
                });
        });

        <?php if ($action === 'add' && isset($_GET['user_id']) && is_numeric($_GET['user_id'])): ?>
            userSelect.value = '<?php echo (int)$_GET['user_id']; ?>';
            userSelect.dispatchEvent(new Event('change'));
        <?php endif; ?>

        updateDesignationRequirement();
    }
    
    const imageUploadInputs = document.querySelectorAll('.image-upload');
    imageUploadInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            const preview = this.parentElement.querySelector('.image-preview');
            
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let existingImg = preview.querySelector('img');
                    if (!existingImg) {
                        existingImg = document.createElement('img');
                        existingImg.className = 'img-thumbnail';
                        existingImg.style.maxHeight = '150px';
                        preview.innerHTML = '';
                        preview.appendChild(existingImg);
                    }
                    existingImg.src = e.target.result;
                    existingImg.alt = 'Selected Image';
                    
                    const textP = document.createElement('p');
                    textP.className = 'text-muted mt-1';
                    textP.textContent = 'New photo selected.';
                    preview.appendChild(textP);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    const certificateForm = document.querySelector('form[method="post"]:not(#deleteCertificateForm)');
    if (certificateForm) {
        certificateForm.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalidField = null;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                    
                    field.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, { once: true });
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                showNotification('Please fill all required fields.', 'danger');
            }
        });
    }
    
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        if (!alert.querySelector('.btn-close')) {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500);
                }
            }, 5000);
        }
    });

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transition = 'opacity 0.5s ease';
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 500);
            }
        }, 4000);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>