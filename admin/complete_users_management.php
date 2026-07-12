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

// Get user for view or edit (moved before POST to load user data for edit)
if (($action === 'view' || $action === 'edit') && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
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
            } elseif (empty($name) || empty($sdw_type) || empty($sdw_name) || empty($mobile) || empty($gender) || empty($dob) || empty($aadhar) || empty($state) || empty($district) || empty($address) || empty($pincode) || empty($membership_type) || empty($designation) || empty($user_type)) {
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
                        if ($formAction === 'add' || ($formAction === 'edit' && isset($user['email']) && $user['email'] !== $email)) {
                            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                            $stmt->execute([$email, $formAction === 'edit' ? $id : 0]);
                            if ($stmt->fetch()) {
                                $error = "Email already exists.";
                            }
                        }
                    }
                    
                    if (empty($error) && ($formAction === 'add' || ($formAction === 'edit' && isset($user['mobile']) && $user['mobile'] !== $mobile))) {
                        $stmt = $db->prepare("SELECT id FROM users WHERE mobile = ? AND id != ?");
                        $stmt->execute([$mobile, $formAction === 'edit' ? $id : 0]);
                        if ($stmt->fetch()) {
                            $error = "Mobile number already exists.";
                        }
                    }

                    if (empty($error)) {
                        $profile_image = $formAction === 'edit' ? ($user['profile_image'] ?? null) : null;
                        $aadhar_front = $formAction === 'edit' ? ($user['aadhar_front'] ?? null) : null;
                        $aadhar_back = $formAction === 'edit' ? ($user['aadhar_back'] ?? null) : null;
                        $press_card = $formAction === 'edit' ? ($user['press_card'] ?? null) : null;
                        $payment_proof = $formAction === 'edit' ? ($user['payment_proof'] ?? null) : null;
                        
                        $files = [
                            'profile_image' => 'uploads/profiles',
                            'aadhar_front' => 'uploads/profiles',
                            'aadhar_back' => 'uploads/profiles',
                            'press_card' => 'uploads/profiles',
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
                            // Generate sequential registration ID
                            $stmt = $db->prepare("SELECT registration_id FROM users WHERE registration_id LIKE 'PCWWF/%' ORDER BY id DESC LIMIT 1");
                            $stmt->execute();
                            $lastRegId = $stmt->fetchColumn();
                            
                            if ($lastRegId) {
                                $lastNumber = (int) str_replace('PCWWF/', '', $lastRegId);
                                $newNumber = $lastNumber + 1;
                            } else {
                                $newNumber = 1;
                            }
                            
                            $registration_id = 'PCWWF/' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
                            
                            $stmt = $db->prepare("INSERT INTO users (name, sdw_type, sdw_name, mobile, email, password, gender, dob, profession, blood_group, aadhar, state, district, address, working_area, pincode, membership_type, designation, profile_image, aadhar_front, aadhar_back, press_card, payment_id, payment_proof, payment_method, registration_id, status, user_type, created_at, valid_until, valid_from, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
                            $stmt->execute([
                                $name, $sdw_type, $sdw_name, $mobile, $email, $password, $gender, $dob, 
                                $profession, $blood_group, $aadhar, $state, $district, $address, $working_area, 
                                $pincode, $membership_type, $designation, $profile_image, $aadhar_front, 
                                $aadhar_back, $press_card, $payment_id, $payment_proof, $payment_method, 
                                $registration_id, $status, $user_type, $valid_until, $valid_from, $_SESSION['user_id']
                            ]);
                            
                            // Send registration email
                            if (!empty($email)) {
                                require_once 'includes/email-templates.php';
                                $userData = [
                                    'registration_id' => $registration_id,
                                    'name' => $name,
                                    'email' => $email,
                                    'membership_type' => $membership_type,
                                    'status' => $status,
                                    'created_at' => date('Y-m-d H:i:s')
                                ];
                                $emailSubject = 'Registration Confirmation - ' . ORGANIZATION_NAME;
                                $emailBody = getRegistrationEmailTemplate($userData);
                                $emailSent = sendEmail($email, $emailSubject, $emailBody, true);
                                
                                // Log email
                                try {
                                    $logStatus = $emailSent ? 'sent' : 'failed';
                                    $stmt = $db->prepare("INSERT INTO email_logs (user_id, recipient_email, subject, status, sent_at, created_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                                    $stmt->execute([$_SESSION['user_id'], $email, $emailSubject, $logStatus]);
                                } catch (Exception $e) {
                                    error_log("Email log error: " . $e->getMessage());
                                }
                                
                                // Create notification
                                try {
                                    $notifTitle = "Registration Successful";
                                    $notifMessage = "Welcome! Your registration has been received. Your Registration ID is: $registration_id";
                                    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES ((SELECT id FROM users WHERE registration_id = ?), ?, ?, 'success', NOW())");
                                    $stmt->execute([$registration_id, $notifTitle, $notifMessage]);
                                } catch (Exception $e) {
                                    error_log("Notification error: " . $e->getMessage());
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
                                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, gender = ?, dob = ?, sdw_type = ?, sdw_name = ?, profession = ?, blood_group = ?, aadhar = ?, state = ?, district = ?, address = ?, working_area = ?, pincode = ?, membership_type = ?, designation = ?, profile_image = COALESCE(?, profile_image), aadhar_front = COALESCE(?, aadhar_front), aadhar_back = COALESCE(?, aadhar_back), press_card = COALESCE(?, press_card), payment_proof = COALESCE(?, payment_proof), payment_id = ?, payment_method = ?, status = ?, user_type = ?, valid_until = ?, valid_from = ?, password = ? WHERE id = ?");
                                $stmt->execute([
                                    $name, $email, $mobile, $gender, $dob, $sdw_type, $sdw_name, $profession, 
                                    $blood_group, $aadhar, $state, $district, $address, $working_area, $pincode, 
                                    $membership_type, $designation, $profile_image, $aadhar_front, $aadhar_back, 
                                    $press_card, $payment_proof, $payment_id, $payment_method, $status, $user_type, 
                                    $valid_until, $valid_from, $password, $id
                                ]);
                            } else {
                                $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, gender = ?, dob = ?, sdw_type = ?, sdw_name = ?, profession = ?, blood_group = ?, aadhar = ?, state = ?, district = ?, address = ?, working_area = ?, pincode = ?, membership_type = ?, designation = ?, profile_image = COALESCE(?, profile_image), aadhar_front = COALESCE(?, aadhar_front), aadhar_back = COALESCE(?, aadhar_back), press_card = COALESCE(?, press_card), payment_proof = COALESCE(?, payment_proof), payment_id = ?, payment_method = ?, status = ?, user_type = ?, valid_until = ?, valid_from = ? WHERE id = ?");
                                $stmt->execute([
                                    $name, $email, $mobile, $gender, $dob, $sdw_type, $sdw_name, $profession, 
                                    $blood_group, $aadhar, $state, $district, $address, $working_area, $pincode, 
                                    $membership_type, $designation, $profile_image, $aadhar_front, $aadhar_back, 
                                    $press_card, $payment_proof, $payment_id, $payment_method, $status, $user_type, 
                                    $valid_until, $valid_from, $id
                                ]);
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
                // Fetch user data before updating
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$userData) {
                    throw new Exception("User not found");
                }
                
                // Update user status
                $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->execute([$status, $user_id]);
                
                // Send email notification if email exists
                if (!empty($userData['email'])) {
                    require_once 'includes/email-templates.php';
                    
                    if ($status === 'approved') {
                        $emailSubject = 'Registration Approved - ' . ORGANIZATION_NAME;
                        $emailBody = getApprovalEmailTemplate($userData);
                        $notifTitle = "Registration Approved";
                        $notifMessage = "Congratulations! Your registration has been approved. You can now download your ID card.";
                        $notifType = "success";
                    } else {
                        $emailSubject = 'Registration Status Update - ' . ORGANIZATION_NAME;
                        $emailBody = getRejectionEmailTemplate($userData, $rejection_reason);
                        $notifTitle = "Registration Status Update";
                        $notifMessage = "Your registration has been reviewed. " . (!empty($rejection_reason) ? "Reason: " . $rejection_reason : "Please contact us for more details.");
                        $notifType = "warning";
                    }
                    
                    $emailSent = sendEmail($userData['email'], $emailSubject, $emailBody, true);
                    
                    // Log email sending
                    try {
                        $logStatus = $emailSent ? 'sent' : 'failed';
                        $stmt = $db->prepare("INSERT INTO email_logs (user_id, recipient_email, subject, status, sent_at, created_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                        $stmt->execute([$_SESSION['user_id'], $userData['email'], $emailSubject, $logStatus]);
                    } catch (Exception $e) {
                        error_log("Email log error: " . $e->getMessage());
                    }
                    
                    // Create in-app notification
                    try {
                        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([$user_id, $notifTitle, $notifMessage, $notifType]);
                    } catch (Exception $e) {
                        error_log("Notification error: " . $e->getMessage());
                    }
                    
                    if ($emailSent) {
                        $success = "User " . ($status === 'approved' ? 'approved' : 'rejected') . " successfully and notification email sent.";
                    } else {
                        $success = "User " . ($status === 'approved' ? 'approved' : 'rejected') . " successfully but email notification failed.";
                    }
                } else {
                    $success = "User " . ($status === 'approved' ? 'approved' : 'rejected') . " successfully.";
                }
                
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
                $stmt = $db->prepare("SELECT profile_image, aadhar_front, aadhar_back, press_card, payment_proof, id_card_photo FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_files = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                $files = [
                    'profile_image' => 'uploads/profiles',
                    'aadhar_front' => 'uploads/profiles',
                    'aadhar_back' => 'uploads/profiles',
                    'press_card' => 'uploads/profiles',
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
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filters
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Search by name, email, mobile, ID..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo ($status_filter === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo ($status_filter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="membership">
                                <option value="">All Memberships</option>
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
                        <div class="col-md-2">
                            <select class="form-select" name="user_type">
                                <option value="">All User Types</option>
                                <option value="member" <?php echo ($user_type_filter === 'member') ? 'selected' : ''; ?>>Member</option>
                                <option value="coordinator" <?php echo ($user_type_filter === 'coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                                <option value="admin" <?php echo ($user_type_filter === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <?php if ($currentUserType === 'admin'): ?>
                        <div class="col-md-2">
                            <select class="form-select" name="created_by">
                                <option value="">All Coordinators</option>
                                <?php foreach ($coordinators as $coordinator): ?>
                                    <option value="<?php echo htmlspecialchars($coordinator['created_by']); ?>" <?php echo ($created_by_filter === $coordinator['created_by']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($coordinator['created_by_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="admin-card mt-4">
                <div class="card-header">
                    <i class="fas fa-users"></i> Users List (<?php echo $totalRecords; ?> Total)
                </div>
                <div class="card-body">
                    <?php if (isset($noUsersMessage)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <?php echo $noUsersMessage; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Reg ID</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>Membership</th>
                                        <th>Designation</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $userItem): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($userItem['registration_id'] ?? 'N/A'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($userItem['name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($userItem['mobile'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($userItem['email'] ?? 'N/A'); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $userItem['membership_type'] ?? ''))); ?></span></td>
                                        <td><?php echo htmlspecialchars(html_entity_decode($userItem['designation'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?></td>
                                        <td>
                                            <span class="badge <?php 
                                                switch($userItem['status'] ?? '') {
                                                    case 'approved': echo 'bg-success'; break;
                                                    case 'rejected': echo 'bg-danger'; break;
                                                    default: echo 'bg-warning';
                                                }
                                            ?>">
                                                <?php echo htmlspecialchars(ucfirst($userItem['status'] ?? '')); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($userItem['created_by_name'] ?? 'System'); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="users-management.php?action=view&id=<?php echo $userItem['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="users-management.php?action=edit&id=<?php echo $userItem['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="id-card-generator.php?action=generate&id=<?php echo $userItem['id']; ?>" class="btn btn-sm btn-secondary" title="Generate ID Card">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                                <button onclick="deleteUser(<?php echo $userItem['id']; ?>, '<?php echo addslashes($userItem['name']); ?>')" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <?php if ($userItem['status'] === 'pending'): ?>
                                                <div class="btn-group mt-1" role="group">
                                                    <button onclick="approveUser(<?php echo $userItem['id']; ?>, '<?php echo addslashes($userItem['name']); ?>')" class="btn btn-sm btn-success" title="Approve">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button onclick="showRejectModal(<?php echo $userItem['id']; ?>, '<?php echo addslashes($userItem['name']); ?>')" class="btn btn-sm btn-warning" title="Reject">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-3">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&membership=<?php echo urlencode($membership_filter); ?>&user_type=<?php echo urlencode($user_type_filter); ?>&created_by=<?php echo urlencode($created_by_filter ?? ''); ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&membership=<?php echo urlencode($membership_filter); ?>&user_type=<?php echo urlencode($user_type_filter); ?>&created_by=<?php echo urlencode($created_by_filter ?? ''); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&membership=<?php echo urlencode($membership_filter); ?>&user_type=<?php echo urlencode($user_type_filter); ?>&created_by=<?php echo urlencode($created_by_filter ?? ''); ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Delete Form (Hidden) -->
            <form id="deleteForm" method="POST" style="display: none;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteUserId" name="user_id" value="">
            </form>
            
            <!-- Approve Form (Hidden) -->
            <form id="approveForm" method="POST" style="display: none;">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" id="approveUserId" name="user_id" value="">
            </form>
            
            <!-- Rejection Modal -->
            <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="rejectModalLabel">
                                <i class="fas fa-exclamation-triangle"></i> Reject User Registration
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="rejectForm" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" id="rejectUserId" name="user_id" value="">
                                <p>Are you sure you want to reject registration for <strong id="rejectUserName"></strong>?</p>
                                <div class="mb-3">
                                    <label for="rejection_reason" class="form-label">Reason for Rejection (Optional)</label>
                                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Enter reason for rejection..."></textarea>
                                    <small class="text-muted">This reason will be sent to the user via email notification.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-ban"></i> Reject User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $action === 'add' ? 'Add New User' : 'Edit User'; ?>
                </h1>
                <div class="page-actions">
                    <a href="users-management.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-user-edit"></i> User Information Form
                </div>
                <div class="card-body">
                    <form id="userForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <?php endif; ?>
                        
                        <!-- Personal Information -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-user"></i> Personal Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="sdw_type" class="form-label">Relation Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="sdw_type" name="sdw_type" required>
                                    <option value="">Select Relation</option>
                                    <option value="S/O" <?php echo (isset($user['sdw_type']) && $user['sdw_type'] === 'S/O') ? 'selected' : ''; ?>>Son Of (S/O)</option>
                                    <option value="D/O" <?php echo (isset($user['sdw_type']) && $user['sdw_type'] === 'D/O') ? 'selected' : ''; ?>>Daughter Of (D/O)</option>
                                    <option value="W/O" <?php echo (isset($user['sdw_type']) && $user['sdw_type'] === 'W/O') ? 'selected' : ''; ?>>Wife Of (W/O)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="sdw_name" class="form-label">Father's/Husband's Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sdw_name" name="sdw_name" value="<?php echo htmlspecialchars($user['sdw_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" required>
                                <small class="text-muted">10 digits only</small>
                            </div>
                            <div class="col-md-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="password" class="form-label">Password <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : '(Leave blank to keep current)'; ?></label>
                                <input type="password" class="form-control" id="password" name="password" <?php echo $action === 'add' ? 'required' : ''; ?>>
                            </div>
                            <div class="col-md-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo (isset($user['gender']) && $user['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($user['gender']) && $user['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo (isset($user['gender']) && $user['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <label for="aadhar" class="form-label">Aadhar Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="aadhar" name="aadhar" pattern="[0-9]{12}" maxlength="12" value="<?php echo htmlspecialchars($user['aadhar'] ?? ''); ?>" required>
                                <small class="text-muted">12 digits only</small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Address Information -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-map-marker-alt"></i> Address Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <select class="form-select" id="state" name="state" required>
                                    <option value="">Select State</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="district" class="form-label">District <span class="text-danger">*</span></label>
                                <select class="form-select" id="district" name="district" required>
                                    <option value="">Select District</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pincode" name="pincode" pattern="[0-9]{6}" maxlength="6" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" required>
                                <small class="text-muted">6 digits only</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="address" class="form-label">Full Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="working_area" class="form-label">Working Area / Organization</label>
                                <textarea class="form-control" id="working_area" name="working_area" rows="3"><?php echo htmlspecialchars($user['working_area'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Membership Information -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-id-card"></i> Membership Information</h5>
                        <div class="row mb-3">
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
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" <?php echo (isset($user['status']) && $user['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo (isset($user['status']) && $user['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo (isset($user['status']) && $user['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="">Select Payment Method</option>
                                    <option value="offline" <?php echo (isset($user['payment_method']) && $user['payment_method'] === 'offline') ? 'selected' : ''; ?>>Offline (Bank Transfer)</option>
                                    <option value="cash" <?php echo (isset($user['payment_method']) && $user['payment_method'] === 'cash') ? 'selected' : ''; ?>>Cash</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="payment_id" class="form-label">Payment ID / Transaction ID</label>
                                <input type="text" class="form-control" id="payment_id" name="payment_id" value="<?php echo htmlspecialchars($user['payment_id'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="valid_from" class="form-label">Valid From</label>
                                <input type="date" class="form-control" id="valid_from" name="valid_from" value="<?php echo htmlspecialchars($user['valid_from'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="valid_until" class="form-label">Valid Until</label>
                                <input type="date" class="form-control" id="valid_until" name="valid_until" value="<?php echo htmlspecialchars($user['valid_until'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Document Uploads -->
                        <h5 class="mb-3 text-primary"><i class="fas fa-file-upload"></i> Document Uploads</h5>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="profile_image" class="form-label">Profile Photo</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                <?php if ($action === 'edit' && !empty($user['profile_image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label for="aadhar_front" class="form-label">Aadhar Card (Front)</label>
                                <input type="file" class="form-control" id="aadhar_front" name="aadhar_front" accept="image/*">
                                <?php if ($action === 'edit' && !empty($user['aadhar_front'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['aadhar_front']); ?>" alt="Aadhar Front" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label for="aadhar_back" class="form-label">Aadhar Card (Back)</label>
                                <input type="file" class="form-control" id="aadhar_back" name="aadhar_back" accept="image/*">
                                <?php if ($action === 'edit' && !empty($user['aadhar_back'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['aadhar_back']); ?>" alt="Aadhar Back" class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label for="press_card" class="form-label">Professional ID / Document</label>
                                <input type="file" class="form-control" id="press_card" name="press_card" accept="image/*,application/pdf">
                                <?php if ($action === 'edit' && !empty($user['press_card'])): ?>
                                    <div class="mt-2">
                                        <?php
                                        $press_card_ext = strtolower(pathinfo($user['press_card'], PATHINFO_EXTENSION));
                                        if (in_array($press_card_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['press_card']); ?>" alt="ID Card" class="img-thumbnail" style="max-height: 100px;">
                                        <?php else: ?>
                                            <a href="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['press_card']); ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-file-pdf"></i> View Document
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="payment_proof" class="form-label">Payment Proof</label>
                                <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*,application/pdf">
                                <?php if ($action === 'edit' && !empty($user['payment_proof'])): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo SITE_URL; ?>/uploads/payments/<?php echo htmlspecialchars($user['payment_proof']); ?>" target="_blank" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-receipt"></i> View Payment Proof
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between">
                            <a href="users-management.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Add User' : 'Update User'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        <?php elseif ($action === 'view'): ?>
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user"></i> View User Details
                </h1>
                <div class="page-actions">
                    <a href="users-management.php?action=edit&id=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    <a href="users-management.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="admin-card">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-id-card"></i> Profile
                        </div>
                        <div class="card-body text-center">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" class="img-fluid rounded mb-3" style="max-width: 200px;">
                            <?php else: ?>
                                <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center mb-3" style="width: 200px; height: 200px; margin: 0 auto;">
                                    <i class="fas fa-user fa-5x"></i>
                                </div>
                            <?php endif; ?>
                            <h4><?php echo htmlspecialchars($user['name'] ?? ''); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($user['registration_id'] ?? ''); ?></p>
                            <span class="badge <?php 
                                switch($user['status'] ?? '') {
                                    case 'approved': echo 'bg-success'; break;
                                    case 'rejected': echo 'bg-danger'; break;
                                    default: echo 'bg-warning';
                                }
                            ?> p-2">
                                <?php echo htmlspecialchars(ucfirst($user['status'] ?? '')); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="admin-card">
                        <div class="card-header">
                            <i class="fas fa-info-circle"></i> User Information
                        </div>
                        <div class="card-body">
                            <h5 class="text-primary mb-3"><i class="fas fa-user"></i> Personal Information</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Full Name</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Father's/Husband's Name</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars(($user['sdw_type'] ?? '') . ' ' . ($user['sdw_name'] ?? 'N/A')); ?></dd>

                                <dt class="col-sm-4">Mobile</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['mobile'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Email</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Gender</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['gender'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Date of Birth</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['dob'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Blood Group</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['blood_group'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Profession</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['profession'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Aadhar Number</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['aadhar'] ?? 'N/A'); ?></dd>
                            </dl>
                            
                            <hr class="my-3">
                            
                            <h5 class="text-primary mb-3"><i class="fas fa-map-marker-alt"></i> Address Information</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Address</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">District</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['district'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">State</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['state'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Pincode</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['pincode'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Working Area</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['working_area'] ?? 'N/A'); ?></dd>
                            </dl>
                            
                            <hr class="my-3">
                            
                            <h5 class="text-primary mb-3"><i class="fas fa-id-card"></i> Membership Information</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Registration ID</dt>
                                <dd class="col-sm-8"><strong><?php echo htmlspecialchars($user['registration_id'] ?? 'N/A'); ?></strong></dd>

                                <dt class="col-sm-4">Membership Type</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['membership_type'] ?? 'N/A'))); ?></dd>

                                <dt class="col-sm-4">Designation</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars(html_entity_decode($user['designation'] ?? 'N/A', ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?></dd>

                                <dt class="col-sm-4">User Type</dt>
                                <dd class="col-sm-8"><span class="badge bg-info"><?php echo htmlspecialchars(ucfirst($user['user_type'] ?? 'N/A')); ?></span></dd>

                                <dt class="col-sm-4">Payment Method</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['payment_method'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Payment ID</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['payment_id'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Valid From</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['valid_from'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Valid Until</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['valid_until'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Created At</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['created_at'] ?? 'N/A'); ?></dd>

                                <dt class="col-sm-4">Created By</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['created_by_name'] ?? 'System'); ?></dd>
                            </dl>
                            
                            <hr class="my-3">
                            
                            <h5 class="text-primary mb-3"><i class="fas fa-file-image"></i> Documents</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Aadhar Front:</strong><br>
                                    <?php if (!empty($user['aadhar_front'])): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['aadhar_front']); ?>" alt="Aadhar Front" class="img-thumbnail mt-2" style="max-width: 100%;">
                                    <?php else: ?>
                                        <span class="text-muted">Not uploaded</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Aadhar Back:</strong><br>
                                    <?php if (!empty($user['aadhar_back'])): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['aadhar_back']); ?>" alt="Aadhar Back" class="img-thumbnail mt-2" style="max-width: 100%;">
                                    <?php else: ?>
                                        <span class="text-muted">Not uploaded</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Professional ID:</strong><br>
                                    <?php if (!empty($user['press_card'])): ?>
                                        <?php
                                        $press_card_ext = strtolower(pathinfo($user['press_card'], PATHINFO_EXTENSION));
                                        if (in_array($press_card_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['press_card']); ?>" alt="ID Card" class="img-thumbnail mt-2" style="max-width: 100%;">
                                        <?php else: ?>
                                            <a href="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['press_card']); ?>" target="_blank" class="btn btn-sm btn-info mt-2">
                                                <i class="fas fa-file-pdf"></i> View Document
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not uploaded</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Payment Proof:</strong><br>
                                    <?php if (!empty($user['payment_proof'])): ?>
                                        <a href="<?php echo SITE_URL; ?>/uploads/payments/<?php echo htmlspecialchars($user['payment_proof']); ?>" target="_blank" class="btn btn-sm btn-secondary mt-2">
                                            <i class="fas fa-receipt"></i> View Payment Proof
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not uploaded</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../js/cities.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const membershipTypeSelect = document.getElementById('membership_type');
    const designationSelect = document.getElementById('designation');
    const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district');
    
    // Initialize state dropdown using cities.js
    if (stateSelect) {
        print_state('state');
        
        // Set selected state if editing
        <?php if (($action === 'edit' || $action === 'add') && !empty($user['state'])): ?>
            stateSelect.value = '<?php echo addslashes($user['state']); ?>';
            
            // Trigger district population
            const stateIndex = stateSelect.selectedIndex;
            if (stateIndex > 0) {
                print_city('district', stateIndex);
                
                // Set selected district after districts are loaded
                <?php if (!empty($user['district'])): ?>
                    setTimeout(function() {
                        districtSelect.value = '<?php echo addslashes($user['district']); ?>';
                    }, 100);
                <?php endif; ?>
            }
        <?php endif; ?>
        
        // State change handler
        stateSelect.addEventListener('change', function() {
            const selectedIndex = this.selectedIndex;
            if (selectedIndex > 0) {
                print_city('district', selectedIndex);
            } else {
                districtSelect.innerHTML = '<option value="">Select District</option>';
            }
        });
    }
    
    // Designation management
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
                        console.error('Error loading designations:', data.message || 'No data found');
                    }
                })
                .catch(error => {
                    console.error('Error loading designations:', error);
                    alert('Failed to load designations. Please try again.');
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
                alert('Mobile number must be exactly 10 digits.');
                return false;
            }
            if (!/^[0-9]{12}$/.test(aadhar)) {
                event.preventDefault();
                alert('Aadhar number must be exactly 12 digits.');
                return false;
            }
            if (!/^[0-9]{6}$/.test(pincode)) {
                event.preventDefault();
                alert('Pincode must be exactly 6 digits.');
                return false;
            }
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                event.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            if (!designation) {
                event.preventDefault();
                alert('Please select a valid designation.');
                return false;
            }
            if (!state) {
                event.preventDefault();
                alert('Please select a state.');
                return false;
            }
            if (!district) {
                event.preventDefault();
                alert('Please select a district.');
                return false;
            }
        });
    }
});

// Delete user function
function deleteUser(id, name) {
    if (confirm(`Are you sure you want to delete user "${name}"?\n\nThis action cannot be undone and will permanently delete all user data including uploaded documents.`)) {
        document.getElementById('deleteUserId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Approve user function
function approveUser(id, name) {
    if (confirm(`Are you sure you want to APPROVE registration for "${name}"?\n\nAn approval email notification will be sent to the user.`)) {
        document.getElementById('approveUserId').value = id;
        document.getElementById('approveForm').submit();
    }
}

// Show reject modal function
function showRejectModal(id, name) {
    document.getElementById('rejectUserId').value = id;
    document.getElementById('rejectUserName').textContent = name;
    
    // Clear previous reason
    document.getElementById('rejection_reason').value = '';
    
    // Using Bootstrap 5 modal
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    rejectModal.show();
}
</script>

<?php include 'includes/footer.php'; ?>
                                