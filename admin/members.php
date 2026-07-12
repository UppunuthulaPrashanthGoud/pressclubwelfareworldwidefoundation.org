<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin or coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'सदस्य प्रबंधन';
$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$status_filter = $_GET['status'] ?? '';

$message = '';
$error = '';

// Helper function to format dates as DD-MM-YYYY
function formatDate($dateString) {
    if (!$dateString) {
        return '-';
    }
    try {
        $date = new DateTime($dateString);
        return $date->format('d-m-Y');
    } catch (Exception $e) {
        return '-';
    }
}

// Handle AJAX request for designations
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'get_designations') {
    header('Content-Type: application/json');
    
    if (!isset($_GET['membership_type']) || empty($_GET['membership_type'])) {
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
        echo json_encode(['success' => false, 'message' => 'Invalid membership type']);
        exit;
    }

    try {
        $designations = getDesignationsByMembershipType($membership_type);
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'अमान्य CSRF टोकन।';
    } else if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                try {
                    $name = sanitizeInput($_POST['name']);
                    $email = sanitizeInput($_POST['email']);
                    $mobile = sanitizeInput($_POST['mobile']);
                    $gender = sanitizeInput($_POST['gender']);
                    $dob = sanitizeInput($_POST['dob']);
                    $profession = sanitizeInput($_POST['profession']);
                    $designation = sanitizeInput($_POST['designation']);
                    $state = sanitizeInput($_POST['state']);
                    $district = sanitizeInput($_POST['district']);
                    $address = sanitizeInput($_POST['address']);
                    $pincode = sanitizeInput($_POST['pincode']);
                    $status = sanitizeInput($_POST['status']);
                    $membership_type = sanitizeInput($_POST['membership_type']);
                    $registration_id = sanitizeInput($_POST['registration_id']);
                    $valid_until = sanitizeInput($_POST['valid_until']);
                    $user_type = isAdmin() ? sanitizeInput($_POST['user_type'] ?? 'member') : 'member';
                    $working_area = sanitizeInput($_POST['working_area']);

                    // Validate designation
                    $valid_designations = array_column(getDesignationsByMembershipType($membership_type), 'designation');
                    if (!in_array($designation, $valid_designations)) {
                        throw new Exception('अमान्य पद चयन। कृपया उपलब्ध विकल्पों में से चुनें।');
                    }

                    // Validate valid_until date
                    if (!DateTime::createFromFormat('Y-m-d', $valid_until)) {
                        throw new Exception('अमान्य वैधता तिथि। कृपया सही तारीख चुनें।');
                    }
                    $today = date('Y-m-d');
                    if ($valid_until < $today) {
                        throw new Exception('वैधता तिथि आज की तारीख से पहले नहीं हो सकती।');
                    }

                    // Handle photo upload
                    $profile_image = '';
                    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../img/profiles/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }

                        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                        if (in_array($file_ext, $allowed_ext)) {
                            if ($_FILES['photo']['size'] <= 5 * 1024 * 1024) {
                                $profile_image = uniqid('user_') . '.' . $file_ext;
                                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $profile_image)) {
                                    throw new Exception('फोटो अपलोड में त्रुटि।');
                                }
                            } else {
                                throw new Exception('फोटो का साइज़ 5MB से अधिक नहीं होना चाहिए।');
                            }
                        } else {
                            throw new Exception('अनुमत नहीं फ़ाइल प्रकार। केवल JPG, JPEG, PNG, या GIF फ़ाइलें अपलोड करें।');
                        }
                    }

                    // Handle ID card photo upload
                    $id_card_photo = '';
                    if (!empty($_FILES['id_card_photo']['name']) && $_FILES['id_card_photo']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../img/profiles/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }

                        $file_ext = strtolower(pathinfo($_FILES['id_card_photo']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                        if (in_array($file_ext, $allowed_ext)) {
                            if ($_FILES['id_card_photo']['size'] <= 5 * 1024 * 1024) {
                                $id_card_photo = uniqid('idcard_') . '.' . $file_ext;
                                if (!move_uploaded_file($_FILES['id_card_photo']['tmp_name'], $upload_dir . $id_card_photo)) {
                                    throw new Exception('आईडी कार्ड फोटो अपलोड में त्रुटि।');
                                }
                            } else {
                                throw new Exception('आईडी कार्ड फोटो का साइज़ 5MB से अधिक नहीं होना चाहिए।');
                            }
                        } else {
                            throw new Exception('अनुमत नहीं फ़ाइल प्रकार। केवल JPG, JPEG, PNG, या GIF फ़ाइलें अपलोड करें।');
                        }
                    }

                    if ($_POST['action'] === 'add') {
                        // Generate registration_id if not provided
                        if (empty($registration_id)) {
                            $stmt = $db->prepare("SELECT MAX(CAST(SUBSTRING(registration_id, 5) AS UNSIGNED)) as max_num FROM users WHERE registration_id LIKE ?");
                            $stmt->execute(["NDFR%"]);
                            $result = $stmt->fetch();
                            $next_num = ($result['max_num'] ?? 0) + 1;
                            $registration_id = "NDFR" . str_pad($next_num, 5, '0', STR_PAD_LEFT);
                        }

                        $stmt = $db->prepare("
                            INSERT INTO users (
                                registration_id, name, email, mobile, gender, dob, profession, designation,
                                state, district, address, pincode, membership_type, profile_image, aadhar_image, 
                                status, user_type, created_at, valid_until, working_area
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
                        ");
                        $stmt->execute([
                            $registration_id, $name, $email, $mobile, $gender, $dob, $profession, $designation,
                            $state, $district, $address, $pincode, $membership_type, $profile_image, $id_card_photo, 
                            $status, $user_type, $valid_until, $working_area
                        ]);
                        $message = 'सदस्य सफलतापूर्वक जोड़ा गया!';
                    } else {
                        // Keep existing photos if no new photos uploaded
                        if (empty($profile_image) || empty($id_card_photo)) {
                            $stmt = $db->prepare("SELECT profile_image, aadhar_image FROM users WHERE id = ?");
                            $stmt->execute([$id]);
                            $existing = $stmt->fetch();
                            $profile_image = empty($profile_image) ? ($existing['profile_image'] ?? '') : $profile_image;
                            $id_card_photo = empty($id_card_photo) ? ($existing['aadhar_image'] ?? '') : $id_card_photo;
                        }

                        $stmt = $db->prepare("
                            UPDATE users SET 
                                registration_id = ?, name = ?, email = ?, mobile = ?, gender = ?, 
                                dob = ?, profession = ?, designation = ?, state = ?, district = ?, 
                                address = ?, pincode = ?, membership_type = ?, profile_image = ?, 
                                aadhar_image = ?, status = ?, user_type = ?, updated_at = NOW(),
                                valid_until = ?, working_area = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $registration_id, $name, $email, $mobile, $gender, $dob, $profession, $designation,
                            $state, $district, $address, $pincode, $membership_type, $profile_image, 
                            $id_card_photo, $status, $user_type, $valid_until, $working_area, $id
                        ]);
                        $message = 'सदस्य जानकारी अपडेट की गई!';
                    }

                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'त्रुटि: ' . htmlspecialchars($e->getMessage());
                }
                break;

            case 'delete':
                if (!isAdmin()) {
                    $error = 'केवल व्यवस्थापक ही उपयोगकर्ता हटा सकते हैं।';
                    break;
                }
                try {
                    $delete_id = $_POST['id'];

                    // Get photo filenames before deleting
                    $stmt = $db->prepare("SELECT profile_image, aadhar_image FROM users WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $user = $stmt->fetch();

                    // Delete user
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$delete_id]);

                    // Delete photos if they exist
                    if (!empty($user['profile_image']) && file_exists('../img/profiles/' . $user['profile_image'])) {
                        unlink('../img/profiles/' . $user['profile_image']);
                    }
                    if (!empty($user['aadhar_image']) && file_exists('../img/profiles/' . $user['aadhar_image'])) {
                        unlink('../img/profiles/' . $user['aadhar_image']);
                    }

                    $message = 'सदस्य सफलतापूर्वक हटाया गया!';
                } catch (Exception $e) {
                    $error = 'हटाने में त्रुटि: ' . htmlspecialchars($e->getMessage());
                }
                break;
        }
    }
}

// Get user data for editing
$user_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user_data = $stmt->fetch();

    if (!$user_data) {
        $error = 'सदस्य नहीं मिला!';
        $action = 'list';
    }
}

// Initialize $user_data as empty array if null to prevent null access
$user_data = $user_data ?: [];

// Get users list
$users = [];
if ($action === 'list') {
    $where_clause = '';
    $params = [];

    if (!empty($status_filter)) {
        $where_clause = 'WHERE status = ?';
        $params[] = $status_filter;
    }

    $stmt = $db->prepare("SELECT * FROM users $where_clause ORDER BY created_at DESC");
    $stmt->execute($params);
    $users = $stmt->fetchAll();
}

// Function to get proper image URL
function getImageUrl($filename) {
    if (empty($filename)) {
        return '';
    }

    $imagePath = '../img/profiles/' . $filename;
    if (file_exists($imagePath)) {
        return SITE_URL . '/img/profiles/' . $filename;
    }
    return '';
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-users me-3"></i> सदस्य प्रबंधन
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> नया सदस्य जोड़ें
                </a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> वापस जाएं
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Users List -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i> सदस्य सूची</h5>
                <div class="filter-buttons">
                    <a href="?" class="btn btn-sm btn-outline-primary <?php echo empty($status_filter) ? 'active' : ''; ?>">सभी</a>
                    <a href="?status=approved" class="btn btn-sm btn-outline-success <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">अनुमोदित</a>
                    <a href="?status=pending" class="btn btn-sm btn-outline-warning <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">लंबित</a>
                    <a href="?status=rejected" class="btn btn-sm btn-outline-danger <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">अस्वीकृत</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>फोटो</th>
                                <th>रजिस्ट्रेशन आईडी</th>
                                <th>नाम</th>
                                <th>मोबाइल</th>
                                <th>सदस्यता</th>
                                <th>पद</th>
                                <th>उपयोगकर्ता प्रकार</th>
                                <th>स्थिति</th>
                                <th>वैधता तिथि</th>
                                <th>कार्य क्षेत्र</th>
                                <th>कार्य</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $imageUrl = getImageUrl($user['profile_image']);
                                    if ($imageUrl): ?>
                                    <img src="<?php echo $imageUrl; ?>" 
                                         alt="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" 
                                         class="rounded-circle member-photo" 
                                         width="40" height="40"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="avatar-placeholder" style="display: none;">
                                        <?php echo strtoupper(substr($user['name'] ?? '', 0, 1)); ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?php echo strtoupper(substr($user['name'] ?? '', 0, 1)); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['registration_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['mobile'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(getMembershipTypeName($user['membership_type'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($user['designation'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($user['user_type'] ?? '')); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($user['status'] ?? '') === 'approved' ? 'success' : (($user['status'] ?? '') === 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo htmlspecialchars(($user['status'] ?? '') === 'approved' ? 'अनुमोदित' : (($user['status'] ?? '') === 'pending' ? 'लंबित' : 'अस्वीकृत')); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars(formatDate($user['valid_until'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($user['working_area'] ?? '-'); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary" title="संपादित करें">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (isAdmin()): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>)" title="हटाएं">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                        <a href="id-card-generator.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-info" title="आईडी कार्ड">
                                            <i class="fas fa-id-card"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> me-2"></i> 
                    <?php echo $action === 'add' ? 'नया सदस्य जोड़ें' : 'सदस्य संपादित करें'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="registration_id" class="form-label">रजिस्ट्रेशन आईडी</label>
                                <input type="text" class="form-control" id="registration_id" name="registration_id" 
                                       value="<?php echo htmlspecialchars($user_data['registration_id'] ?? ''); ?>"
                                       <?php echo $action === 'add' ? 'placeholder="खाली छोड़ें (स्वचालित जेनरेट होगा)"' : ''; ?>>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">नाम *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mobile" class="form-label">मोबाइल *</label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" required
                                       value="<?php echo htmlspecialchars($user_data['mobile'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">ईमेल</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="membership_type" class="form-label">सदस्यता प्रकार *</label>
                                <select class="form-select" id="membership_type" name="membership_type" required>
                                    <option value="">सदस्यता चुनें</option>
                                    <option value="active" <?php echo ($user_data['membership_type'] ?? '') === 'active' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('active'); ?></option>
                                    <option value="gram_panchayat" <?php echo ($user_data['membership_type'] ?? '') === 'gram_panchayat' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('gram_panchayat'); ?></option>
                                    <option value="block" <?php echo ($user_data['membership_type'] ?? '') === 'block' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('block'); ?></option>
                                    <option value="tehsil" <?php echo ($user_data['membership_type'] ?? '') === 'tehsil' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('tehsil'); ?></option>
                                    <option value="district" <?php echo ($user_data['membership_type'] ?? '') === 'district' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('district'); ?></option>
                                    <option value="mandal" <?php echo ($user_data['membership_type'] ?? '') === 'mandal' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('mandal'); ?></option>
                                    <option value="state" <?php echo ($user_data['membership_type'] ?? '') === 'state' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('state'); ?></option>
                                    <option value="national" <?php echo ($user_data['membership_type'] ?? '') === 'national' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('national'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="designation" class="form-label">पद *</label>
                                <select class="form-select" id="designation" name="designation" required>
                                    <option value="">पद चुनें</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">स्थिति *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" <?php echo ($user_data['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>लंबित</option>
                                    <option value="approved" <?php echo ($user_data['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>अनुमोदित</option>
                                    <option value="rejected" <?php echo ($user_data['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>अस्वीकृत</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_until" class="form-label">वैधता तिथि *</label>
                                <input type="date" class="form-control" id="valid_until" name="valid_until" required
                                       value="<?php echo htmlspecialchars($user_data['valid_until'] ?? date('Y-m-d', strtotime('+1 year'))); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gender" class="form-label">लिंग *</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">चुनें</option>
                                    <option value="Male" <?php echo ($user_data['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>पुरुष</option>
                                    <option value="Female" <?php echo ($user_data['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>महिला</option>
                                    <option value="Other" <?php echo ($user_data['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>अन्य</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dob" class="form-label">जन्म तिथि *</label>
                                <input type="date" class="form-control" id="dob" name="dob" required
                                       value="<?php echo htmlspecialchars($user_data['dob'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="profession" class="form-label">पेशा</label>
                                <input type="text" class="form-control" id="profession" name="profession"
                                       value="<?php echo htmlspecialchars($user_data['profession'] ?? ''); ?>">
                            </div>
                        </div>
                        <?php if (isAdmin()): ?>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_type" class="form-label">उपयोगकर्ता प्रकार *</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="member" <?php echo ($user_data['user_type'] ?? '') === 'member' ? 'selected' : ''; ?>>सदस्य</option>
                                    <option value="coordinator" <?php echo ($user_data['user_type'] ?? '') === 'coordinator' ? 'selected' : ''; ?>>समन्वयक</option>
                                    <option value="admin" <?php echo ($user_data['user_type'] ?? '') === 'admin' ? 'selected' : ''; ?>>व्यवस्थापक</option>
                                </select>
                            </div>
                        </div>
                        <?php else: ?>
                        <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user_data['user_type'] ?? 'member'); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="state" class="form-label">राज्य *</label>
                                <input type="text" class="form-control" id="state" name="state" required
                                       value="<?php echo htmlspecialchars($user_data['state'] ?? 'उत्तर प्रदेश'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="district" class="form-label">जिला *</label>
                                <input type="text" class="form-control" id="district" name="district" required
                                       value="<?php echo htmlspecialchars($user_data['district'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address" class="form-label">पता *</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pincode" class="form-label">पिन कोड *</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" required
                                       value="<?php echo htmlspecialchars($user_data['pincode'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="working_area" class="form-label">कार्य क्षेत्र</label>
                                <textarea class="form-control" id="working_area" name="working_area" rows="3"><?php echo htmlspecialchars($user_data['working_area'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="photo" class="form-label">फोटो</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                <?php if (!empty($user_data['profile_image'])): ?>
                                <div class="mt-2">
                                    <?php 
                                    $imageUrl = getImageUrl($user_data['profile_image']);
                                    if ($imageUrl): ?>
                                    <img src="<?php echo $imageUrl; ?>" 
                                         alt="Current Photo" class="img-thumbnail" style="max-width: 150px;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div class="text-muted" style="display: none;">Image not found</div>
                                    <?php else: ?>
                                    <div class="text-muted">Current photo not available</div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_card_photo" class="form-label">आईडी कार्ड फोटो</label>
                                <input type="file" class="form-control" id="id_card_photo" name="id_card_photo" accept="image/*">
                                <?php if (!empty($user_data['aadhar_image'])): ?>
                                <div class="mt-2">
                                    <?php 
                                    $idCardImageUrl = getImageUrl($user_data['aadhar_image']);
                                    if ($idCardImageUrl): ?>
                                    <img src="<?php echo $idCardImageUrl; ?>" 
                                         alt="Current ID Card Photo" class="img-thumbnail" style="max-width: 150px;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div class="text-muted" style="display: none;">Image not found</div>
                                    <?php else: ?>
                                    <div class="text-muted">Current ID card photo not available</div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> रद्द करें
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> सहेजें
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
</form>

<?php include 'includes/sidebar.php'; ?>

<script>
function deleteUser(id) {
    if (confirm('क्या आप वाकई इस उपयोगकर्ता को हटाना चाहते हैं? यह क्रिया पूर्ववत नहीं की जा सकती है।')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Update designations based on membership type
document.addEventListener('DOMContentLoaded', function() {
    const membershipSelect = document.getElementById('membership_type');
    const designationSelect = document.getElementById('designation');

    if (membershipSelect && designationSelect) {
        function updateDesignations() {
            const membershipType = membershipSelect.value;
            if (!membershipType) {
                designationSelect.innerHTML = '<option value="">पहले सदस्यता चुनें</option>';
                return;
            }

            fetch('?ajax=get_designations&membership_type=' + encodeURIComponent(membershipType))
                .then(response => response.json())
                .then(data => {
                    designationSelect.innerHTML = '<option value="">पद चुनें</option>';
                    if (data.success && data.designations) {
                        data.designations.forEach(designation => {
                            const option = document.createElement('option');
                            option.value = designation.designation;
                            option.textContent = designation.designation_hindi + ' (' + designation.designation + ')';
                            designationSelect.appendChild(option);
                        });
                        
                        // Set selected designation if editing
                        const currentDesignation = '<?php echo $user_data['designation'] ?? ''; ?>';
                        if (currentDesignation) {
                            designationSelect.value = currentDesignation;
                        }
                    } else {
                        designationSelect.innerHTML = '<option value="">कोई पद उपलब्ध नहीं</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching designations:', error);
                    designationSelect.innerHTML = '<option value="">त्रुटि: पद लोड करने में असमर्थ</option>';
                });
        }

        membershipSelect.addEventListener('change', updateDesignations);

        // Initialize designations on page load
        if (membershipSelect.value) {
            updateDesignations();
        }
    }
});
</script>

<style>
.avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

.member-photo {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border: 2px solid #dee2e6;
}

.filter-buttons .btn.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.img-thumbnail {
    max-height: 150px;
    object-fit: cover;
}

.admin-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0;
    padding: 15px 20px;
}

.card-body {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.page-title {
    color: #333;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .page-actions {
        margin-top: 10px;
    }

    .filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .table-responsive {
        font-size: 14px;
    }

    .member-photo, .avatar-placeholder {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
}

@media print {
    .page-actions, .btn-group, .filter-buttons {
        display: none;
    }
}
</style>

<?php include 'includes/footer.php'; ?>