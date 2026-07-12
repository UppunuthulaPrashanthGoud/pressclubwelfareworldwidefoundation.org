<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

$pageTitle = 'User Management';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$user = [];

$db = getDbConnection();

$currentUserType = $_SESSION['user_type'] ?? 'member';
if (!in_array($currentUserType, ['admin', 'coordinator'])) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

// AJAX handler for fetching designations
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'get_designations') {
    header('Content-Type: application/json');
    $membership_type = sanitizeInput($_GET['membership_type']);
    
    if ($currentUserType === 'coordinator') {
        $valid_memberships = ['active'];
    } else {
        $valid_memberships = ['active', 'gram_panchayat', 'block', 'tehsil', 'district', 'mandal', 'state', 'national'];
    }
    
    if (!in_array($membership_type, $valid_memberships)) {
        echo json_encode(['success' => false, 'message' => 'Invalid membership type']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("SELECT designation, designation_hindi FROM membership_designations WHERE membership_type = ? AND status = 'active' ORDER BY sort_order");
        $stmt->execute([$membership_type]);
        $designations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($designations as &$des) {
            $des['designation'] = html_entity_decode($des['designation'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $des['designation_hindi'] = html_entity_decode($des['designation_hindi'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        echo json_encode(['success' => true, 'designations' => $designations]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading designations: ' . $e->getMessage()]);
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $name = sanitizeInput($_POST['name']);
            $sdw_type = sanitizeInput($_POST['sdw_type']);
            $sdw_name = sanitizeInput($_POST['sdw_name']);
            $mobile = sanitizeInput($_POST['mobile']);
            $email = sanitizeInput($_POST['email']);
            $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
            $gender = sanitizeInput($_POST['gender']);
            $dob = sanitizeInput($_POST['dob']);
            $profession = sanitizeInput($_POST['profession']);
            $blood_group = sanitizeInput($_POST['blood_group']);
            $aadhar = sanitizeInput($_POST['aadhar']);
            $state = sanitizeInput($_POST['state']);
            $district = sanitizeInput($_POST['district']);
            $address = sanitizeInput($_POST['address']);
            $working_area = sanitizeInput($_POST['working_area']);
            $pincode = sanitizeInput($_POST['pincode']);
            $membership_type = sanitizeInput($_POST['membership_type']);
            $designation = html_entity_decode(sanitizeInput($_POST['designation'], false), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $status = sanitizeInput($_POST['status']);
            $user_type = ($currentUserType === 'coordinator') ? 'member' : sanitizeInput($_POST['user_type']);
            $payment_id = isset($_POST['payment_id']) ? sanitizeInput($_POST['payment_id']) : null;
            $payment_method = isset($_POST['payment_method']) ? sanitizeInput($_POST['payment_method']) : null;
            $valid_until = isset($_POST['valid_until']) ? sanitizeInput($_POST['valid_until']) : null;
            $valid_from = isset($_POST['valid_from']) ? sanitizeInput($_POST['valid_from']) : null;
            
            if ($currentUserType === 'coordinator' && $membership_type !== 'active') {
                $error = "Coordinators can only create active membership users.";
            }
            
            // Validation
            elseif (empty($name) || empty($sdw_type) || empty($sdw_name) || empty($mobile) || empty($gender) || empty($dob) || empty($aadhar) || empty($state) || empty($district) || empty($address) || empty($pincode) || empty($membership_type) || empty($designation) || empty($user_type)) {
                $error = "All required fields must be filled.";
            } elseif ($formAction === 'add' && empty($_POST['password'])) {
                $error = "Password is required when adding a new user.";
            } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
                $error = "Mobile number must be 10 digits.";
            } elseif (!preg_match('/^[0-9]{12}$/', $aadhar)) {
                $error = "Aadhar number must be 12 digits.";
            } elseif (!preg_match('/^[0-9]{6}$/', $pincode)) {
                $error = "Pincode must be 6 digits.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
                $error = "Invalid email format.";
            } else {
                try {
                    // Validate designation
                    $stmt = $db->prepare("SELECT designation FROM membership_designations WHERE membership_type = ? AND status = 'active'");
                    $stmt->execute([$membership_type]);
                    $db_designations = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    $decoded_designations = array_map(function($d) { 
                        return html_entity_decode($d, ENT_QUOTES | ENT_HTML5, 'UTF-8'); 
                    }, $db_designations);
                    
                    if (!in_array($designation, $decoded_designations)) {
                        throw new Exception("Invalid designation for the selected membership type.");
                    }

                    // Check for duplicate email or mobile
                    if (!empty($email)) {
                        if ($formAction === 'add') {
                            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                            $stmt->execute([$email]);
                        } else {
                            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                            $stmt->execute([$email, $id]);
                        }
                        if ($stmt->fetch()) {
                            $error = "Email already exists.";
                        }
                    }
                    
                    if (empty($error)) {
                        if ($formAction === 'add') {
                            $stmt = $db->prepare("SELECT id FROM users WHERE mobile = ?");
                            $stmt->execute([$mobile]);
                        } else {
                            $stmt = $db->prepare("SELECT id FROM users WHERE mobile = ? AND id != ?");
                            $stmt->execute([$mobile, $id]);
                        }
                        if ($stmt->fetch()) {
                            $error = "Mobile number already exists.";
                        }
                    }

                    if (empty($error)) {
                        // Get user data for edit mode
                        $oldStatus = null;
                        if ($formAction === 'edit') {
                            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                            $stmt->execute([$id]);
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);
                            $oldStatus = $user['status'] ?? null;
                        }
                        
                        $profile_image = $formAction === 'edit' ? ($user['profile_image'] ?? null) : null;
                        $aadhar_front = $formAction === 'edit' ? ($user['aadhar_front'] ?? null) : null;
                        $aadhar_back = $formAction === 'edit' ? ($user['aadhar_back'] ?? null) : null;
                        $payment_proof = $formAction === 'edit' ? ($user['payment_proof'] ?? null) : null;
                        
                        $files = [
                            'profile_image' => 'uploads/profiles',
                            'aadhar_front' => 'uploads/profiles',
                            'aadhar_back' => 'uploads/profiles',
                            'payment_proof' => 'uploads/payments'
                        ];
                        
                        foreach ($files as $field => $path) {
                            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                                $uploadResult = uploadFile($_FILES[$field], $path);
                                if ($uploadResult['success']) {
                                    $$field = $uploadResult['filename'];
                                } else {
                                    throw new Exception($uploadResult['message']);
                                }
                            }
                        }
                        
                        if ($formAction === 'add') {
                            $registration_id = 'WPEWF' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                            $stmt = $db->prepare("INSERT INTO users (name, sdw_type, sdw_name, mobile, email, password, gender, dob, profession, blood_group, aadhar, state, district, address, working_area, pincode, membership_type, designation, profile_image, aadhar_front, aadhar_back, payment_id, payment_proof, payment_method, registration_id, status, user_type, created_at, valid_until, valid_from, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
                            $stmt->execute([
                                $name, $sdw_type, $sdw_name, $mobile, $email, $password, $gender, $dob, 
                                $profession, $blood_group, $aadhar, $state, $district, $address, $working_area, 
                                $pincode, $membership_type, $designation, $profile_image, $aadhar_front, 
                                $aadhar_back, $payment_id, $payment_proof, $payment_method, $registration_id, 
                                $status, $user_type, $valid_until, $valid_from, $_SESSION['user_id']
                            ]);
                            
                            // Send registration email
                            if (!empty($email)) {
                                try {
                                    require_once 'includes/email-templates.php';
                                    
                                    $userData = [
                                        'registration_id' => $registration_id,
                                        'name' => $name,
                                        'email' => $email,
                                        'membership_type' => $membership_type,
                                        'status' => $status,
                                        'created_at' => date('Y-m-d H:i:s')
                                    ];
                                    
                                    $emailBody = getRegistrationEmailTemplate($userData);
                                    $emailSubject = "Registration Confirmation - " . ORGANIZATION_NAME;
                                    
                                    $emailSent = sendEmail($email, $emailSubject, $emailBody, true);
                                    
                                    if ($emailSent) {
                                        logError("Registration confirmation email sent successfully to: $email for registration ID: $registration_id");
                                    } else {
                                        logError("Failed to send registration confirmation email to: $email for registration ID: $registration_id");
                                    }
                                } catch (Exception $e) {
                                    logError('Error sending registration email: ' . $e->getMessage());
                                }
                            }
                            
                            $success = "User added successfully!";
                        } else {
                            if ($currentUserType === 'coordinator') {
                                $stmt = $db->prepare("SELECT created_by FROM users WHERE id = ?");
                                $stmt->execute([$id]);
                                $userCreator = $stmt->fetchColumn();
                                
                                if ($userCreator != $_SESSION['user_id']) {
                                    throw new Exception("You don't have permission to edit this user.");
                                }
                            }
                            
                            if (!empty($password)) {
                                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, gender = ?, dob = ?, sdw_type = ?, sdw_name = ?, profession = ?, blood_group = ?, aadhar = ?, state = ?, district = ?, address = ?, working_area = ?, pincode = ?, membership_type = ?, designation = ?, profile_image = COALESCE(?, profile_image), aadhar_front = COALESCE(?, aadhar_front), aadhar_back = COALESCE(?, aadhar_back), payment_proof = COALESCE(?, payment_proof), payment_id = ?, payment_method = ?, status = ?, user_type = ?, valid_until = ?, valid_from = ?, password = ? WHERE id = ?");
                                $stmt->execute([
                                    $name, $email, $mobile, $gender, $dob, $sdw_type, $sdw_name, $profession, 
                                    $blood_group, $aadhar, $state, $district, $address, $working_area, $pincode, 
                                    $membership_type, $designation, $profile_image, $aadhar_front, $aadhar_back, 
                                    $payment_proof, $payment_id, $payment_method, $status, $user_type, 
                                    $valid_until, $valid_from, $password, $id
                                ]);
                            } else {
                                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, gender = ?, dob = ?, sdw_type = ?, sdw_name = ?, profession = ?, blood_group = ?, aadhar = ?, state = ?, district = ?, address = ?, working_area = ?, pincode = ?, membership_type = ?, designation = ?, profile_image = COALESCE(?, profile_image), aadhar_front = COALESCE(?, aadhar_front), aadhar_back = COALESCE(?, aadhar_back), payment_proof = COALESCE(?, payment_proof), payment_id = ?, payment_method = ?, status = ?, user_type = ?, valid_until = ?, valid_from = ? WHERE id = ?");
                                $stmt->execute([
                                    $name, $email, $mobile, $gender, $dob, $sdw_type, $sdw_name, $profession, 
                                    $blood_group, $aadhar, $state, $district, $address, $working_area, $pincode, 
                                    $membership_type, $designation, $profile_image, $aadhar_front, $aadhar_back, 
                                    $payment_proof, $payment_id, $payment_method, $status, $user_type, 
                                    $valid_until, $valid_from, $id
                                ]);
                            }
                            
                            // Send email if status changed
                            if (!empty($email) && $oldStatus !== $status) {
                                try {
                                    require_once 'includes/email-templates.php';
                                    
                                    $userData = [
                                        'registration_id' => $user['registration_id'],
                                        'name' => $name,
                                        'email' => $email,
                                        'membership_type' => $membership_type,
                                        'status' => $status,
                                        'created_at' => $user['created_at']
                                    ];
                                    
                                    if ($status === 'approved') {
                                        $emailBody = getApprovalEmailTemplate($userData);
                                        $emailSubject = "Registration Approved - " . ORGANIZATION_NAME;
                                    } elseif ($status === 'rejected') {
                                        $emailBody = getRejectionEmailTemplate($userData, 'Your application did not meet our current membership criteria.');
                                        $emailSubject = "Registration Status Update - " . ORGANIZATION_NAME;
                                    } else {
                                        $emailBody = null;
                                    }
                                    
                                    if ($emailBody) {
                                        $emailSent = sendEmail($email, $emailSubject, $emailBody, true);
                                        
                                        if ($emailSent) {
                                            logError("Status change email sent successfully to: $email for registration ID: {$user['registration_id']}");
                                        } else {
                                            logError("Failed to send status change email to: $email for registration ID: {$user['registration_id']}");
                                        }
                                    }
                                } catch (Exception $e) {
                                    logError('Error sending status change email: ' . $e->getMessage());
                                }
                            }
                            
                            $success = "User updated successfully!";
                        }
                        
                        header("Location: users-management.php?success=" . urlencode($success));
                        exit;
                    }
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
        
        if ($formAction === 'approve' || $formAction === 'reject') {
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $status = $formAction === 'approve' ? 'approved' : 'rejected';
            $rejection_reason = isset($_POST['rejection_reason']) ? sanitizeInput($_POST['rejection_reason']) : '';
            
            try {
                // Get user data before updating
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    throw new Exception("User not found.");
                }
                
                // Update status
                $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$status, $user_id]);
                
                // Send email notification
                if (!empty($userData['email'])) {
                    try {
                        require_once 'includes/email-templates.php';
                        
                        $emailData = [
                            'registration_id' => $userData['registration_id'],
                            'name' => $userData['name'],
                            'email' => $userData['email'],
                            'membership_type' => $userData['membership_type'],
                            'status' => $status,
                            'created_at' => $userData['created_at']
                        ];
                        
                        if ($status === 'approved') {
                            $emailBody = getApprovalEmailTemplate($emailData);
                            $emailSubject = "Registration Approved - " . ORGANIZATION_NAME;
                        } else {
                            $emailBody = getRejectionEmailTemplate($emailData, $rejection_reason);
                            $emailSubject = "Registration Status Update - " . ORGANIZATION_NAME;
                        }
                        
                        $emailSent = sendEmail($userData['email'], $emailSubject, $emailBody, true);
                        
                        if ($emailSent) {
                            logError("Status change email sent successfully to: {$userData['email']} for registration ID: {$userData['registration_id']}");
                        } else {
                            logError("Failed to send status change email to: {$userData['email']} for registration ID: {$userData['registration_id']}");
                        }
                    } catch (Exception $e) {
                        logError('Error sending status change email: ' . $e->getMessage());
                    }
                }
                
                $success = "User " . ($status === 'approved' ? 'approved' : 'rejected') . " successfully.";
                header("Location: users-management.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        if ($formAction === 'delete') {
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            
            try {
                $stmt = $db->prepare("SELECT profile_image, aadhar_front, aadhar_back, payment_proof, id_card_photo FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_files = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                $files = [
                    'profile_image' => 'uploads/profiles',
                    'aadhar_front' => 'uploads/profiles',
                    'aadhar_back' => 'uploads/profiles',
                    'payment_proof' => 'uploads/payments',
                    'id_card_photo' => 'uploads/id_cards'
                ];
                
                foreach ($files as $field => $path) {
                    if (!empty($user_files[$field])) {
                        $filePath = __DIR__ . '/../' . $path . '/' . $user_files[$field];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
                
                $success = "User deleted successfully.";
                header("Location: users-management.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Get user for view or edit
if (($action === 'view' || $action === 'edit') && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $error = "User not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Get users for list with pagination and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$membership_filter = isset($_GET['membership']) ? sanitizeInput($_GET['membership']) : '';
$user_type_filter = isset($_GET['user_type']) ? sanitizeInput($_GET['user_type']) : '';
$created_by_filter = isset($_GET['created_by']) && $_GET['created_by'] !== '' ? (int)$_GET['created_by'] : null;

try {
    $whereConditions = [];
    $params = [];
    
    if ($currentUserType === 'coordinator') {
        $whereConditions[] = "u.created_by = ?";
        $params[] = $_SESSION['user_id'];
    }
    
    if (!empty($search)) {
        $whereConditions[] = "(u.name LIKE ? OR u.email LIKE ? OR u.mobile LIKE ? OR u.registration_id LIKE ? OR u.designation LIKE ?)";
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($status_filter)) {
        $whereConditions[] = "u.status = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($membership_filter)) {
        $whereConditions[] = "u.membership_type = ?";
        $params[] = $membership_filter;
    }
    
    if (!empty($user_type_filter)) {
        $whereConditions[] = "u.user_type = ?";
        $params[] = $user_type_filter;
    }
    
    if ($currentUserType === 'admin' && !is_null($created_by_filter)) {
        $whereConditions[] = "u.created_by = ?";
        $params[] = $created_by_filter;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $countQuery = "SELECT COUNT(*) FROM users u $whereClause";
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();
    
    if ($totalRecords == 0 && $currentUserType === 'coordinator') {
        $users = [];
        $noUsersMessage = "You haven't created any users yet. Click 'Add New User' to create your first user.";
    } elseif ($totalRecords == 0) {
        $users = [];
        $noUsersMessage = "No users found matching your criteria.";
    } else {
        $query = "SELECT u.*, c.name AS created_by_name FROM users u LEFT JOIN users c ON u.created_by = c.id $whereClause ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $db->prepare($query);
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $totalPages = ceil($totalRecords / $limit);

    // Fetch list of coordinators for filter dropdown (only for admins)
    $coordinators = [];
    if ($currentUserType === 'admin') {
        $stmt = $db->prepare("SELECT DISTINCT u.created_by, c.name AS created_by_name FROM users u LEFT JOIN users c ON u.created_by = c.id WHERE u.created_by IS NOT NULL AND c.user_type = 'coordinator' AND c.name IS NOT NULL ORDER BY c.name");
        $stmt->execute();
        $coordinators = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $users = [];
    $totalPages = 0;
    $noUsersMessage = "Error loading users: " . htmlspecialchars($e->getMessage());
}

// Fetch designations for edit mode
$designations = [];
if ($action === 'edit' && !empty($user['membership_type'])) {
    try {
        $stmt = $db->prepare("SELECT designation, designation_hindi FROM membership_designations WHERE membership_type = ? AND status = 'active' ORDER BY sort_order");
        $stmt->execute([$user['membership_type']]);
        $designations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($designations as &$des) {
            $des['designation'] = html_entity_decode($des['designation'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $des['designation_hindi'] = html_entity_decode($des['designation_hindi'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    } catch (Exception $e) {
        $error = "Error loading designations: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<script src="<?php echo SITE_URL; ?>/admin/assets/js/cities.js"></script>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <?php if ($action === 'list'): ?>
            <div class="page-header">
                <h1 class="page-title">User Management</h1>
                <div class="page-actions">
                    <a href="users-management.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
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
            
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filters
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo ($status_filter === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo ($status_filter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="membership">
                                <option value="">All Membership Types</option>
                                <?php if ($currentUserType === 'admin'): ?>
                                    <option value="active" <?php echo ($membership_filter === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="gram_panchayat" <?php echo ($membership_filter === 'gram_panchayat') ? 'selected' : ''; ?>>Gram Panchayat</option>
                                    <option value="block" <?php echo ($membership_filter === 'block') ? 'selected' : ''; ?>>Block</option>
                                    <option value="tehsil" <?php echo ($membership_filter === 'tehsil') ? 'selected' : ''; ?>>Tehsil</option>
                                    <option value="district" <?php echo ($membership_filter === 'district') ? 'selected' : ''; ?>>District</option>
                                    <option value="mandal" <?php echo ($membership_filter === 'mandal') ? 'selected' : ''; ?>>Mandal</option>
                                    <option value="state" <?php echo ($membership_filter === 'state') ? 'selected' : ''; ?>>State</option>
                                    <option value="national" <?php echo ($membership_filter === 'national') ? 'selected' : ''; ?>>National</option>
                                <?php else: ?>
                                    <option value="active" <?php echo ($membership_filter === 'active') ? 'selected' : ''; ?>>Active</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="user_type">
                                <option value="">All User Types</option>
                                <option value="member" <?php echo ($user_type_filter === 'member') ? 'selected' : ''; ?>>Member</option>
                                <option value="coordinator" <?php echo ($user_type_filter === 'coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                                <option value="admin" <?php echo ($user_type_filter === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <?php if ($currentUserType === 'admin'): ?>
                        <div class="col-md-3">
                            <select class="form-select" name="created_by">
                                <option value="">All Coordinators</option>
                                <?php foreach ($coordinators as $coordinator): ?>
                                    <option value="<?php echo htmlspecialchars($coordinator['created_by']); ?>" <?php echo ($created_by_filter === $coordinator['created_by']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($coordinator['created_by_name'] . ' (ID: ' . $coordinator['created_by'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="admin-card mt-4">
                <div class="card-header">
                    <i class="fas fa-users"></i> Users List
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Membership Type</th>
                                <th>Designation</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($noUsersMessage)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($noUsersMessage); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php elseif (empty($users)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No users found matching your criteria.
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['id']); ?></td>
                                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td><?php echo htmlspecialchars($u['mobile']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $u['membership_type']))); ?></td>
                                        <td><?php echo htmlspecialchars(html_entity_decode($u['designation'], ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?></td>
                                        <td>
                                            <span class="badge <?php echo $u['status'] === 'approved' ? 'bg-success' : ($u['status'] === 'rejected' ? 'bg-danger' : 'bg-warning'); ?>">
                                                <?php echo ucfirst($u['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($u['created_by_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <a href="users-management.php?action=view&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="users-management.php?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="id-card-generator.php?action=generate&id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary" title="Generate ID Card">
                                                <i class="fas fa-id-card"></i>
                                            </a>
                                            <?php if ($u['status'] === 'pending'): ?>
                                                <button onclick="approveUser(<?php echo $u['id']; ?>, '<?php echo addslashes($u['name']); ?>')" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button onclick="rejectUser(<?php echo $u['id']; ?>, '<?php echo addslashes($u['name']); ?>')" class="btn btn-sm btn-warning" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button onclick="deleteUser(<?php echo $u['id']; ?>, '<?php echo addslashes($u['name']); ?>')" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Pagination">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&membership=<?php echo urlencode($membership_filter); ?>&user_type=<?php echo urlencode($user_type_filter); ?>&created_by=<?php echo urlencode($created_by_filter ?? ''); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <form id="deleteForm" method="POST" style="display: none;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteUserId" name="user_id" value="">
            </form>
            
            <form id="approveForm" method="POST" style="display: none;">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" id="approveUserId" name="user_id" value="">
            </form>
            
            <form id="rejectForm" method="POST" style="display: none;">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" id="rejectUserId" name="user_id" value="">
                <input type="hidden" id="rejectReason" name="rejection_reason" value="">
            </form>
            
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <div class="page-header">
                <h1 class="page-title"><?php echo $action === 'add' ? 'Add New User' : 'Edit User'; ?></h1>
                <div class="page-actions">
                    <a href="users-management.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-user-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> User Form
                </div>
                <div class="card-body">
                    <form id="userForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="sdw_type" class="form-label">S/D/W Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="sdw_type" name="sdw_type" required>
                                    <option value="">Select S/D/W Type</option>
                                    <option value="S/O" <?php echo (isset($user['sdw_type']) && $user['sdw_type'] === 'S/O') ? 'selected' : ''; ?>>S/O</option>
                                    <option value="D/O" <?php echo (isset($user['sdw_type']) && $user['sdw_type'] === 'D/O') ? 'selected' : ''; ?>>D/O</option>
                                    <option value="W/O" <?php echo (isset($user['sdw_type']) && $user['sdw_type'] === 'W/O') ? 'selected' : ''; ?>>W/O</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="sdw_name" class="form-label">S/D/W Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sdw_name" name="sdw_name" value="<?php echo htmlspecialchars($user['sdw_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mobile" name="mobile" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="password" class="form-label">Password <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : '(Leave blank to keep current)'; ?></label>
                                <input type="password" class="form-control" id="password" name="password" <?php echo $action === 'add' ? 'required' : ''; ?>>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo (isset($user['gender']) && $user['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($user['gender']) && $user['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo (isset($user['gender']) && $user['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="profession" class="form-label">Profession</label>
                                <select class="form-select" id="profession" name="profession">
                                    <option value="">Select Profession</option>
                                    <option value="Government Job" <?php echo (isset($user['profession']) && $user['profession'] === 'Government Job') ? 'selected' : ''; ?>>Government Job</option>
                                    <option value="Private Job" <?php echo (isset($user['profession']) && $user['profession'] === 'Private Job') ? 'selected' : ''; ?>>Private Job</option>
                                    <option value="Farmer" <?php echo (isset($user['profession']) && $user['profession'] === 'Farmer') ? 'selected' : ''; ?>>Farmer</option>
                                    <option value="Self Business" <?php echo (isset($user['profession']) && $user['profession'] === 'Self Business') ? 'selected' : ''; ?>>Self Business</option>
                                    <option value="Student" <?php echo (isset($user['profession']) && $user['profession'] === 'Student') ? 'selected' : ''; ?>>Student</option>
                                    <option value="Job" <?php echo (isset($user['profession']) && $user['profession'] === 'Job') ? 'selected' : ''; ?>>Job</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="blood_group" class="form-label">Blood Group</label>
                                <select class="form-select" id="blood_group" name="blood_group">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+" <?php echo (isset($user['blood_group']) && $user['blood_group'] === 'A+') ? 'selected' : ''; ?>>A+</option>
                                    <option value="A-" <?php echo (isset($user['blood_group']) && $user['blood_group'] === 'A-') ? 'selected' : ''; ?>>A-</option>
                                    <option value="B+" <?php echo (isset($user['blood_group']) && $user['blood_group'] === 'B+') ? 'selected' : ''; ?>>B+</option>
                                    <option value="B-" <?php echo (isset($user['blood_group']) && $user['blood_group'] === 'B-') ? 'selected' : ''; ?>>B-</option>
                                    <option value="O+" <?php echo (isset($user['blood_group']) && $user['blood_group'] === 'O+') ? 'selected' : ''; ?>>O+</option>
                                    <option value="O-" <?php echo (isset($user['blood_group']) && $user['blood_group'] === 'O-') ? 'selected' : ''; ?>>O-</option>
                                    <option value="AB+" <?php echo (isset($user['blood_group']) && $user['blood_group'] === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                    <option value="AB-" <?php echo (isset($user['blood_group']) && $user['blood_group'] === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="aadhar" class="form-label">Aadhar Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="aadhar" name="aadhar" pattern="[0-9]{12}" value="<?php echo htmlspecialchars($user['aadhar'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <select class="form-select" id="state" name="state" required>
                                    <option value="">Select State</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="district" class="form-label">District <span class="text-danger">*</span></label>
                                <select class="form-select" id="district" name="district" required>
                                    <option value="">Select District</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="working_area" class="form-label">Working Area</label>
                                <textarea class="form-control" id="working_area" name="working_area" rows="3"><?php echo htmlspecialchars($user['working_area'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pincode" name="pincode" pattern="[0-9]{6}" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="membership_type" class="form-label">Membership Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="membership_type" name="membership_type" required>
                                    <option value="">Select Membership Type</option>
                                    <?php if ($currentUserType === 'admin'): ?>
                                        <option value="active" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="gram_panchayat" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'gram_panchayat') ? 'selected' : ''; ?>>Gram Panchayat</option>
                                        <option value="block" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'block') ? 'selected' : ''; ?>>Block</option>
                                        <option value="tehsil" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'tehsil') ? 'selected' : ''; ?>>Tehsil</option>
                                        <option value="district" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'district') ? 'selected' : ''; ?>>District</option>
                                        <option value="mandal" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'mandal') ? 'selected' : ''; ?>>Mandal</option>
                                        <option value="state" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'state') ? 'selected' : ''; ?>>State</option>
                                        <option value="national" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'national') ? 'selected' : ''; ?>>National</option>
                                    <?php else: ?>
                                        <option value="active" <?php echo (isset($user['membership_type']) && $user['membership_type'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="designation" class="form-label">Designation <span class="text-danger">*</span></label>
                                <select class="form-select" id="designation" name="designation" required>
                                    <option value="">Select Designation</option>
                                    <?php if (!empty($designations)): ?>
                                        <?php foreach ($designations as $des): ?>
                                            <option value="<?php echo htmlspecialchars($des['designation']); ?>" 
                                                <?php echo (isset($user['designation']) && html_entity_decode($user['designation'], ENT_QUOTES | ENT_HTML5, 'UTF-8') === $des['designation']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($des['designation']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <?php if ($currentUserType === 'admin'): ?>
                            <div class="col-md-4">
                                <label for="user_type" class="form-label">User Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="member" <?php echo (isset($user['user_type']) && $user['user_type'] === 'member') ? 'selected' : ''; ?>>Member</option>
                                    <option value="coordinator" <?php echo (isset($user['user_type']) && $user['user_type'] === 'coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                                    <option value="admin" <?php echo (isset($user['user_type']) && $user['user_type'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="user_type" value="member">
                            <?php endif; ?>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" <?php echo (isset($user['status']) && $user['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo (isset($user['status']) && $user['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo (isset($user['status']) && $user['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="">Select Payment Method</option>
                                    <option value="online" <?php echo (isset($user['payment_method']) && $user['payment_method'] === 'online') ? 'selected' : ''; ?>>Online (Razorpay)</option>
                                    <option value="offline" <?php echo (isset($user['payment_method']) && $user['payment_method'] === 'offline') ? 'selected' : ''; ?>>Offline (Bank Transfer)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="payment_id" class="form-label">Payment ID</label>
                                <input type="text" class="form-control" id="payment_id" name="payment_id" value="<?php echo htmlspecialchars($user['payment_id'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="valid_from" class="form-label">Valid From</label>
                                <input type="date" class="form-control" id="valid_from" name="valid_from" value="<?php echo htmlspecialchars($user['valid_from'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="valid_until" class="form-label">Valid Until</label>
                                <input type="date" class="form-control" id="valid_until" name="valid_until" value="<?php echo htmlspecialchars($user['valid_until'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="profile_image" class="form-label">Profile Photo</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                <?php if ($action === 'edit' && !empty($user['profile_image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label for="aadhar_front" class="form-label">Aadhar Card (Front)</label>
                                <input type="file" class="form-control" id="aadhar_front" name="aadhar_front" accept="image/*">
                                <?php if ($action === 'edit' && !empty($user['aadhar_front'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['aadhar_front']); ?>" alt="Current Aadhaar Front" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label for="aadhar_back" class="form-label">Aadhar Card (Back)</label>
                                <input type="file" class="form-control" id="aadhar_back" name="aadhar_back" accept="image/*">
                                <?php if ($action === 'edit' && !empty($user['aadhar_back'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['aadhar_back']); ?>" alt="Current Aadhaar Back" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label for="payment_proof" class="form-label">Payment Proof</label>
                                <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*,application/pdf">
                                <?php if ($action === 'edit' && !empty($user['payment_proof'])): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo SITE_URL; ?>/uploads/payments/<?php echo htmlspecialchars($user['payment_proof']); ?>" target="_blank">View Current Proof</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Add' : 'Update'; ?>
                            </button>
                            <a href="users-management.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($action === 'view'): ?>
            <div class="page-header">
                <h1 class="page-title">View User</h1>
                <div class="page-actions">
                    <a href="users-management.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-user"></i> User Details
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['name'] ?? ''); ?></dd>

                        <dt class="col-sm-3">S/D/W Type</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['sdw_type'] ?? ''); ?></dd>

                        <dt class="col-sm-3">S/D/W Name</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['sdw_name'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Mobile</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['mobile'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['email'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Gender</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['gender'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Date of Birth</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['dob'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Profession</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['profession'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Blood Group</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['blood_group'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Aadhar Number</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['aadhar'] ?? ''); ?></dd>

                        <dt class="col-sm-3">State</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['state'] ?? ''); ?></dd>

                        <dt class="col-sm-3">District</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['district'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Address</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['address'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Working Area</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['working_area'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Pincode</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['pincode'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Membership Type</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['membership_type'] ?? ''))); ?></dd>

                        <dt class="col-sm-3">Designation</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars(html_entity_decode($user['designation'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?></dd>

                        <dt class="col-sm-3">User Type</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['user_type'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['status'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Payment Method</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['payment_method'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Payment ID</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['payment_id'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Valid From</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['valid_from'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Valid Until</dt>
                        <dd class="col-sm-9"><?php echo htmlspecialchars($user['valid_until'] ?? ''); ?></dd>

                        <dt class="col-sm-3">Profile Image</dt>
                        <dd class="col-sm-9">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" style="max-width: 200px;">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-3">Aadhar Front</dt>
                        <dd class="col-sm-9">
                            <?php if (!empty($user['aadhar_front'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['aadhar_front']); ?>" alt="Aadhar Front" style="max-width: 200px;">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-3">Aadhar Back</dt>
                        <dd class="col-sm-9">
                            <?php if (!empty($user['aadhar_back'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['aadhar_back']); ?>" alt="Aadhar Back" style="max-width: 200px;">
                            <?php else: ?>
                                No image
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-3">Payment Proof</dt>
                        <dd class="col-sm-9">
                            <?php if (!empty($user['payment_proof'])): ?>
                                <a href="<?php echo SITE_URL; ?>/uploads/payments/<?php echo htmlspecialchars($user['payment_proof']); ?>" target="_blank">View Payment Proof</a>
                            <?php else: ?>
                                No proof
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const membershipTypeSelect = document.getElementById('membership_type');
    const designationSelect = document.getElementById('designation');
    const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district');
    
    // Initialize state and district dropdowns
    if (stateSelect && districtSelect) {
        // Populate states
        print_state('state');
        
        <?php if ($action === 'edit' && !empty($user['state'])): ?>
        // Set selected state for edit mode
        stateSelect.value = '<?php echo addslashes($user['state']); ?>';
        
        // Trigger district population
        const stateIndex = state_arr.indexOf('<?php echo addslashes($user['state']); ?>');
        if (stateIndex !== -1) {
            print_city('district', stateIndex + 1);
            setTimeout(function() {
                districtSelect.value = '<?php echo addslashes($user['district']); ?>';
            }, 100);
        }
        <?php endif; ?>
        
        // Add change event listener for state
        stateSelect.addEventListener('change', function() {
            const selectedState = this.value;
            const stateIndex = state_arr.indexOf(selectedState);
            
            if (stateIndex !== -1) {
                print_city('district', stateIndex + 1);
            } else {
                districtSelect.innerHTML = '<option value="">Select District</option>';
            }
        });
    }
    
    // Handle designation loading based on membership type
    if (membershipTypeSelect && designationSelect) {
        function fetchDesignations(membershipType, selectedDesignation = '') {
            if (!membershipType) {
                designationSelect.innerHTML = '<option value="">Select Designation</option>';
                return;
            }
            
            fetch(`users-management.php?ajax=get_designations&membership_type=${encodeURIComponent(membershipType)}&t=${new Date().getTime()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    designationSelect.innerHTML = '<option value="">Select Designation</option>';
                    if (data.success && data.designations && data.designations.length > 0) {
                        data.designations.forEach(designation => {
                            const option = document.createElement('option');
                            option.value = designation.designation;
                            option.textContent = designation.designation;
                            if (designation.designation === selectedDesignation) {
                                option.selected = true;
                            }
                            designationSelect.appendChild(option);
                        });
                    } else {
                        alert('Error loading designations: ' + (data.message || 'No data found'));
                    }
                })
                .catch(error => {
                    alert('Error loading designations: ' + error.message);
                });
        }
        
        membershipTypeSelect.addEventListener('change', function() {
            fetchDesignations(this.value);
        });
        
        <?php if ($action === 'edit' && !empty($user['membership_type'])): ?>
            fetchDesignations('<?php echo addslashes($user['membership_type']); ?>', '<?php echo addslashes(html_entity_decode($user['designation'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>');
        <?php endif; ?>
    }

    // Client-side form validation
    const form = document.getElementById('userForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            const mobile = document.getElementById('mobile').value;
            const aadhar = document.getElementById('aadhar').value;
            const pincode = document.getElementById('pincode').value;
            const email = document.getElementById('email').value;
            const designation = document.getElementById('designation').value;
            const state = document.getElementById('state') ? document.getElementById('state').value : '';
            const district = document.getElementById('district') ? document.getElementById('district').value : '';

            if (!/^[0-9]{10}$/.test(mobile)) {
                event.preventDefault();
                alert('Mobile number must be 10 digits.');
                return;
            }
            if (!/^[0-9]{12}$/.test(aadhar)) {
                event.preventDefault();
                alert('Aadhar number must be 12 digits.');
                return;
            }
            if (!/^[0-9]{6}$/.test(pincode)) {
                event.preventDefault();
                alert('Pincode must be 6 digits.');
                return;
            }
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                event.preventDefault();
                alert('Invalid email format.');
                return;
            }
            if (!designation) {
                event.preventDefault();
                alert('Please select a valid designation.');
                return;
            }
            if (!state) {
                event.preventDefault();
                alert('Please select a state.');
                return;
            }
            if (!district) {
                event.preventDefault();
                alert('Please select a district.');
                return;
            }
        });
    }
});

function deleteUser(id, name) {
    if (confirm(`Are you sure you want to delete user "${name}"? This action cannot be undone.`)) {
        document.getElementById('deleteUserId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

function approveUser(id, name) {
    if (confirm(`Are you sure you want to approve user "${name}"? An approval email will be sent to the user.`)) {
        document.getElementById('approveUserId').value = id;
        document.getElementById('approveForm').submit();
    }
}

function rejectUser(id, name) {
    const reason = prompt(`Please enter the reason for rejecting "${name}":`);
    if (reason !== null && reason.trim() !== '') {
        document.getElementById('rejectUserId').value = id;
        document.getElementById('rejectReason').value = reason.trim();
        document.getElementById('rejectForm').submit();
    } else if (reason !== null) {
        alert('Please provide a reason for rejection.');
    }
}
</script>

<?php include 'includes/footer.php'; ?>