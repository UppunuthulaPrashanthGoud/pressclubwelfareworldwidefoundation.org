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
$award = [];

// Handle AJAX request for user details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'get_user_details') {
    header('Content-Type: application/json');
    $user_id = (int)$_GET['user_id'];
    $db = getDbConnection();
    try {
        $stmt = $db->prepare("SELECT name, email, mobile, gender, profession, address, district AS city, state, pincode, registration_id, profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => !!$user,
            'user' => $user
        ]);
    } catch (Exception $e) {
        logError('Error fetching user details: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch user details']);
    }
    exit;
}

// Database connection
$db = getDbConnection();

// --- FETCH MASTER DATA (AWARDS & VENUES) ---
$masterAwards = [];
try {
    $stmtAw = $db->query("SELECT award_name FROM awards_list WHERE status = 'active' ORDER BY award_name ASC");
    $masterAwards = $stmtAw->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Fallback if table doesn't exist yet
    $masterAwards = ['HONORARY DOCTORATE AWARD', 'LIFETIME ACHIEVEMENT AWARD']; 
}

$masterVenues = [];
try {
    $stmtVen = $db->query("SELECT venue_name FROM venues_list WHERE status = 'active' ORDER BY venue_name ASC");
    $masterVenues = $stmtVen->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $masterVenues = ['Online / Virtual Ceremony'];
}
// -------------------------------------------

// Fetch site configuration
$siteConfig = [];
$configKeys = ['organization_address', 'organization_phone', 'organization_email'];
foreach ($configKeys as $key) {
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $siteConfig[$key] = $result ? htmlspecialchars($result['setting_value']) : '';
}

// Award configuration
$awardContent = [
    'organization_name' => ORGANIZATION_NAME,
    'registration_number' => 'U88900DL2025NPL455426',
    'registered_office' => 'DELHI',
    'template_path' => SITE_URL . '/templates/honorary-award-new.png',
    'authority_signature' => SITE_URL . '/img/signature.png',
    'iso_logo' => SITE_URL . '/img/iso-logo.png',
    'helpline' => '24x7 - 9040898333',
    'website' => 'http://honorarydoctorateawards.org/'
];

// Fetch categories from DB
$masterCategories = [];
try {
    $stmtCat = $db->query("SELECT category_name FROM categories WHERE status = 'active' ORDER BY category_name ASC");
    $masterCategories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Fallback if table doesn't exist yet
    $masterCategories = ['Education Excellence', 'Social Service', 'Healthcare', 'Literature', 'Arts & Culture', 'Science & Technology', 'Business Leadership', 'Sports', 'Environmental Conservation', 'Other']; 
}

// Function to generate unique award number
function generateAwardNumber() {
    global $db;
    $stmt = $db->query("SELECT award_no FROM honorary_awards ORDER BY id DESC LIMIT 1");
    $lastAward = $stmt->fetch();
    $newNumber = $lastAward ? ((int) substr($lastAward['award_no'], 3)) + 1 : 1;
    return 'HA' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
}

// Function to generate unique registration number using short name from config
function generateRegistrationNumber() {
    global $db;
    
    // Get the organization short name from config
    $shortName = ORGANIZATION_NAME_SHORT; // e.g., "WPEWF"
    $currentYear = date('Y'); // e.g., "2026"
    
    // Get the last registration number for this year
    $pattern = $shortName . '/' . $currentYear . '/%';
    $stmt = $db->prepare("SELECT registration_no FROM honorary_awards WHERE registration_no LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$pattern]);
    $lastRegistration = $stmt->fetch();
    
    // Extract the sequential number and increment it
    if ($lastRegistration) {
        $parts = explode('/', $lastRegistration['registration_no']);
        $lastNumber = isset($parts[2]) ? (int)$parts[2] : 0;
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    // Format: WPEWF/2026/0001
    return $shortName . '/' . $currentYear . '/' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            // --- Core Award Fields ---
            $recipient_name = isset($_POST['recipient_name']) ? sanitizeInput($_POST['recipient_name']) : '';
            $award_name = isset($_POST['award_name']) ? sanitizeInput($_POST['award_name']) : '';
            $category = isset($_POST['category']) ? sanitizeInput($_POST['category']) : '';
            $content = isset($_POST['content']) ? sanitizeInput($_POST['content']) : '';
            $venue = isset($_POST['venue']) ? sanitizeInput($_POST['venue']) : '';
            $award_date = isset($_POST['award_date']) ? sanitizeInput($_POST['award_date']) : '';
            $registration_no = isset($_POST['registration_no']) ? sanitizeInput($_POST['registration_no']) : '';
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            // --- Expanded Nomination Fields ---
            $mobile = isset($_POST['mobile']) ? sanitizeInput($_POST['mobile']) : '';
            $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
            $gender = isset($_POST['gender']) ? sanitizeInput($_POST['gender']) : '';
            $profession = isset($_POST['profession']) ? sanitizeInput($_POST['profession']) : '';
            $qualification = isset($_POST['qualification']) ? sanitizeInput($_POST['qualification']) : '';
            $address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : '';
            $state = isset($_POST['state']) ? sanitizeInput($_POST['state']) : '';
            $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
            $pincode = isset($_POST['pincode']) ? sanitizeInput($_POST['pincode']) : '';

            // Validation
            if (empty($recipient_name) || empty($award_name) || empty($category) || empty($content) || empty($venue) || empty($award_date)) {
                $error = "Please fill all required fields.";
            } elseif (!in_array($status, ['active', 'inactive'])) {
                $error = "Invalid status.";
            } else {
                try {
                    $photo_path = '';
                    $oldPhotoPath = '';
                    
                    if ($formAction === 'edit') {
                        $award_id = isset($_POST['award_id']) ? (int)$_POST['award_id'] : 0;
                        $stmt = $db->prepare("SELECT photo_path FROM honorary_awards WHERE id = ?");
                        $stmt->execute([$award_id]);
                        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);
                        $photo_path = $currentData['photo_path'] ?? '';
                    }

                    if (!empty($_POST['auto_fill_user_photo'])) {
                        $photo_path = '../uploads/profiles/' . sanitizeInput($_POST['auto_fill_user_photo']);
                    }

                    // 1. Handle New Upload
                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['photo'], 'img/awards');
                        if ($uploadResult['success']) {
                            if ($formAction === 'edit' && !empty($photo_path)) {
                                $oldPhoto = __DIR__ . '/../img/awards/' . $photo_path;
                                if (file_exists($oldPhoto)) unlink($oldPhoto);
                            }
                            $photo_path = $uploadResult['filename'];
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    } 
                    // 2. Handle Copy from Nomination if no new file uploaded and this is a new award from nomination
                    elseif ($formAction === 'add' && !empty($_POST['nomination_photo_source'])) {
                        $sourceFile = sanitizeInput($_POST['nomination_photo_source']);
                        $sourcePath = __DIR__ . '/../uploads/documents/' . $sourceFile;
                        
                        if (file_exists($sourcePath)) {
                            $newFilename = 'award_' . time() . '_' . $sourceFile;
                            $destPath = __DIR__ . '/../img/awards/' . $newFilename;
                            
                            if (copy($sourcePath, $destPath)) {
                                $photo_path = $newFilename;
                            }
                        }
                    }
                    
                    if ($formAction === 'add') {
                        $award_no = generateAwardNumber();
                        // Auto-generate registration number if empty
                        if (empty($registration_no)) {
                            $registration_no = generateRegistrationNumber();
                        }
                        $stmt = $db->prepare("
                            INSERT INTO honorary_awards (
                                award_no, recipient_name, mobile, email, gender, profession, qualification, 
                                address, state, city, pincode,
                                award_name, category, content, venue, award_date, registration_no, photo_path, status, created_at
                            ) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([
                            $award_no, $recipient_name, $mobile, $email, $gender, $profession, $qualification,
                            $address, $state, $city, $pincode,
                            $award_name, $category, $content, $venue, $award_date, $registration_no, $photo_path, $status
                        ]);
                    } else {
                        $award_id = isset($_POST['award_id']) ? (int)$_POST['award_id'] : 0;
                        $stmt = $db->prepare("
                            UPDATE honorary_awards 
                            SET recipient_name = ?, mobile = ?, email = ?, gender = ?, profession = ?, qualification = ?, 
                                address = ?, state = ?, city = ?, pincode = ?,
                                award_name = ?, category = ?, content = ?, venue = ?, award_date = ?, registration_no = ?, photo_path = ?, status = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $success = $stmt->execute([
                            $recipient_name, $mobile, $email, $gender, $profession, $qualification,
                            $address, $state, $city, $pincode,
                            $award_name, $category, $content, $venue, $award_date, $registration_no, $photo_path, $status, $award_id
                        ]);
                        if (!$success) {
                            throw new Exception("Failed to update award in database.");
                        }
                    }
                    
                    $success = "Award successfully " . ($formAction === 'add' ? 'created!' : 'updated!');
                    header("Location: honorary_awards.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    logError('Award processing error: ' . $e->getMessage());
                    $error = "Error: " . $e->getMessage();
                }
            }
        } elseif ($formAction === 'delete') {
            $award_id = isset($_POST['award_id']) ? (int)$_POST['award_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT photo_path FROM honorary_awards WHERE id = ?");
                $stmt->execute([$award_id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $db->prepare("DELETE FROM honorary_awards WHERE id = ?");
                $stmt->execute([$award_id]);
                
                if (!empty($data['photo_path'])) {
                    $photoPath = __DIR__ . '/../img/awards/' . $data['photo_path'];
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }
                $success = "Award successfully deleted!";
                header("Location: honorary_awards.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                logError('Award deletion error: ' . $e->getMessage());
                $error = "Deletion error: " . $e->getMessage();
            }
        }
    }
}

// Initialize award array
$award = [
    'recipient_name' => '', 'mobile' => '', 'email' => '', 'gender' => '',
    'profession' => '', 'qualification' => '', 'address' => '', 'state' => '', 'city' => '', 'pincode' => '',
    'award_name' => '', 'category' => '', 'content' => '', 'venue' => '',
    'award_date' => date('Y-m-d'), 'registration_no' => generateRegistrationNumber(), 'photo_path' => '', 'status' => 'active'
];
$nomination_photo_source = '';

// --- NEW: FETCH FROM NOMINATION IF ID PROVIDED ---
if ($action === 'add' && isset($_GET['nomination_id'])) {
    $nomId = (int)$_GET['nomination_id'];
    try {
        $stmt = $db->prepare("SELECT * FROM nominations WHERE id = ?");
        $stmt->execute([$nomId]);
        $nomData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($nomData) {
            $award['recipient_name'] = $nomData['name'];
            $award['mobile'] = $nomData['mobile'];
            $award['email'] = $nomData['email'];
            $award['gender'] = $nomData['gender'];
            $award['profession'] = $nomData['profession'];
            $award['qualification'] = $nomData['qualification'];
            $award['address'] = $nomData['address'];
            $award['state'] = $nomData['state'];
            $award['city'] = $nomData['district'];
            $award['pincode'] = $nomData['pincode'];
            $award['award_name'] = $nomData['award'];
            $award['category'] = $nomData['category'];
            $award['venue'] = $nomData['venue'];
            $award['registration_no'] = $nomData['registration_id'];
            
            // Construct Citation Content
            $citation = "For their outstanding contribution and dedication.";
            if (!empty($nomData['about'])) {
                $citation = strip_tags($nomData['about']);
            }
            if (!empty($nomData['talent'])) {
                $citation .= "\n\nKey Achievements:\n" . strip_tags($nomData['talent']);
            }
            $award['content'] = $citation;

            // Handle Photo Logic (Store filename to copy on save)
            if (!empty($nomData['profile_image'])) {
                $nomination_photo_source = $nomData['profile_image'];
            }
        }
    } catch (Exception $e) {
        $error = "Could not fetch nomination data: " . $e->getMessage();
    }
}

// Fetch award for edit
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM honorary_awards WHERE id = ?");
    $stmt->execute([$id]);
    $existingAward = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existingAward) {
        $award = $existingAward;
    } else {
        $error = "Award not found.";
        $action = 'list';
    }
}

// Get awards for list
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM honorary_awards");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM honorary_awards ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute();
    $awards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError('Database error in award listing: ' . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $awards = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add New Award" : (($action === 'edit') ? "Edit Award" : "Honorary Doctorate Awards Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus"></i> Add New Award
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit"></i> Edit Award
                <?php else: ?>
                    <i class="fas fa-award"></i> Honorary Doctorate Awards Management
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="honorary_awards.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="honorary_awards.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Award
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
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "Award Details" : "Edit Award"; ?>
                    <?php if(isset($_GET['nomination_id'])): ?>
                        <span class="badge bg-info text-dark float-end">Generated from Nomination #<?php echo (int)$_GET['nomination_id']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="award_id" value="<?php echo $award['id']; ?>">
                        <?php endif; ?>
                        
                        <!-- Used to copy image from nominations folder if new photo not uploaded -->
                        <?php if (!empty($nomination_photo_source)): ?>
                            <input type="hidden" name="nomination_photo_source" value="<?php echo htmlspecialchars($nomination_photo_source); ?>">
                        <?php endif; ?>
                        
                        <!-- Personal Details -->
                        <h5 class="text-primary mb-3"><i class="fas fa-user"></i> Personal Details</h5>
                        <input type="hidden" name="auto_fill_user_photo" id="auto_fill_user_photo" value="">
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label for="auto_fill_user" class="form-label text-info fw-bold"><i class="fas fa-magic"></i> Link & Auto-fill from Registered User</label>
                                <select class="form-select border-info border-2 shadow-sm" id="auto_fill_user" aria-describedby="autoFillHelp">
                                    <option value="">-- Select an approved user to auto-fill details --</option>
                                    <?php
                                    try {
                                        $userStmt = $db->query("SELECT id, name, registration_id FROM users WHERE status = 'approved' ORDER BY name");
                                        $usersList = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($usersList as $u) {
                                            echo "<option value='{$u['id']}'>" . htmlspecialchars($u['name'] . " (" . $u['registration_id'] . ")") . "</option>";
                                        }
                                    } catch (Exception $e) {}
                                    ?>
                                </select>
                                <div id="autoFillHelp" class="form-text mt-2 text-muted">
                                    <i class="fas fa-info-circle text-info"></i> <strong>Why select a user?</strong> Choosing a registered user will automatically fetch and fill their personal info, contact details, address, and profile photo. This ensures the award details exactly match their official system profile and saves you manual typing time.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="recipient_name" class="form-label">Recipient Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" required 
                                       value="<?php echo htmlspecialchars($award['recipient_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">-- Select --</option>
                                    <option value="Male" <?php echo (isset($award['gender']) && $award['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($award['gender']) && $award['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo (isset($award['gender']) && $award['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <h5 class="text-primary mb-3 mt-4"><i class="fas fa-address-book"></i> Contact Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" 
                                       value="<?php echo htmlspecialchars($award['mobile'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($award['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Full Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($award['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($award['state'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">City/District</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($award['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" 
                                       value="<?php echo htmlspecialchars($award['pincode'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Professional Info -->
                        <h5 class="text-primary mb-3 mt-4"><i class="fas fa-briefcase"></i> Professional Info</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="profession" class="form-label">Profession</label>
                                <input type="text" class="form-control" id="profession" name="profession" 
                                       value="<?php echo htmlspecialchars($award['profession'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="qualification" class="form-label">Qualification</label>
                                <input type="text" class="form-control" id="qualification" name="qualification" 
                                       value="<?php echo htmlspecialchars($award['qualification'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Award Details -->
                        <h5 class="text-primary mb-3 mt-4"><i class="fas fa-trophy"></i> Award & Event Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="award_name" class="form-label">Award Name <span class="text-danger">*</span></label>
                                <!-- Changed from Text Input to Select -->
                                <select class="form-select" id="award_name" name="award_name" required>
                                    <option value="">-- Select Award --</option>
                                    <?php 
                                    $currentAwardName = $award['award_name'] ?? '';
                                    foreach($masterAwards as $awName): 
                                        $selected = ($currentAwardName === $awName) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo htmlspecialchars($awName); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($awName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">-- Select Category --</option>
                                    <?php 
                                    $currentCategoryName = $award['category'] ?? '';
                                    foreach($masterCategories as $catName): 
                                        $selected = ($currentCategoryName === $catName) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo htmlspecialchars($catName); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($catName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="registration_no" class="form-label">
                                    Registration No. 
                                    <?php if ($action === 'add'): ?>
                                        <span class="badge bg-success">Auto-Generated</span>
                                    <?php else: ?>
                                        <span class="text-danger">*</span>
                                    <?php endif; ?>
                                </label>
                                <input type="text" class="form-control" id="registration_no" name="registration_no" 
                                       <?php echo ($action === 'add') ? 'readonly' : 'required'; ?>
                                       value="<?php echo htmlspecialchars($award['registration_no'] ?? ''); ?>">
                                <?php if ($action === 'add'): ?>
                                    <small class="text-muted">Format: <?php echo ORGANIZATION_NAME_SHORT; ?>/YYYY/0001</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="venue" class="form-label">Venue <span class="text-danger">*</span></label>
                                <!-- Changed from Text Input to Select -->
                                <select class="form-select" id="venue" name="venue" required>
                                    <option value="">-- Select Venue --</option>
                                    <?php 
                                    $currentVenueName = $award['venue'] ?? '';
                                    foreach($masterVenues as $venName): 
                                        $selected = ($currentVenueName === $venName) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo htmlspecialchars($venName); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($venName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="award_date" class="form-label">Award Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="award_date" name="award_date" required
                                       value="<?php echo htmlspecialchars($award['award_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo (isset($award['status']) && $award['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($award['status']) && $award['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="photo" class="form-label">Upload Photo <span class="text-danger">*</span></label>
                                <input type="file" class="form-control image-upload" id="photo" name="photo" accept="image/*" <?php echo ($action === 'add' && empty($nomination_photo_source)) ? 'required' : ''; ?>>
                                <div class="mt-2 image-preview" id="photo_preview_container">
                                    <?php if (!empty($award['photo_path']) && strpos($award['photo_path'], '../uploads/profiles/') === 0 && file_exists(__DIR__ . '/../' . substr($award['photo_path'], 3))): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars(substr($award['photo_path'], 20)); ?>?v=<?php echo time(); ?>" alt="Award Photo" class="img-thumbnail" style="max-height: 150px;">
                                        <p class="text-muted mt-1">Current Award Photo (from user profile)</p>
                                    <?php elseif (!empty($award['photo_path']) && file_exists(__DIR__ . '/../img/awards/' . $award['photo_path'])): ?>
                                        <img src="<?php echo SITE_URL; ?>/img/awards/<?php echo htmlspecialchars($award['photo_path']); ?>?v=<?php echo time(); ?>" alt="Award Photo" class="img-thumbnail" style="max-height: 150px;">
                                        <p class="text-muted mt-1">Current Award Photo</p>
                                    <?php elseif (!empty($nomination_photo_source)): ?>
                                        <div class="alert alert-info py-2">
                                            <small><i class="fas fa-info-circle"></i> Profile photo from nomination will be copied automatically if you don't upload a new one.</small>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted" id="no_photo_text">No photo uploaded yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="content" class="form-label">Content / Citation <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" required rows="6" placeholder="Enter the citation text that will appear on the certificate..."><?php echo htmlspecialchars($award['content'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Create Award" : "Update Award"; ?>
                            </button>
                            <a href="honorary_awards.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-award"></i> Awards List
                </div>
                <div class="card-body">
                    <?php if (count($awards) > 0): ?>
                        <div class="table-responsive w-100" style="overflow-x: auto; -webkit-overflow-scrolling: touch; min-width: 0;">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-nowrap">Award No.</th>
                                        <th class="text-nowrap">Recipient Name</th>
                                        <th class="text-nowrap">Award Name</th>
                                        <th class="text-nowrap">Category</th>
                                        <th class="text-nowrap">Venue</th>
                                        <th class="text-nowrap">Date</th>
                                        <th class="text-nowrap">Status</th>
                                        <th class="text-nowrap text-center" style="min-width: 140px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($awards as $awd): ?>
                                        <tr>
                                            <td class="text-nowrap"><?php echo htmlspecialchars($awd['award_no']); ?></td>
                                            <td><?php echo htmlspecialchars($awd['recipient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($awd['award_name']); ?></td>
                                            <td><span class="badge badge-info bg-info text-dark"><?php echo htmlspecialchars($awd['category']); ?></span></td>
                                            <td><?php echo htmlspecialchars($awd['venue']); ?></td>
                                            <td class="text-nowrap"><?php echo date('d-m-Y', strtotime($awd['award_date'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $awd['status'] == 'active' ? 'badge-success bg-success' : 'badge-danger bg-danger'; ?>">
                                                    <?php echo $awd['status'] == 'active' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2 flex-nowrap">
                                                    <a href="honorary_awards.php?action=edit&id=<?php echo $awd['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger delete-award-btn" data-id="<?php echo $awd['id']; ?>" data-award-no="<?php echo htmlspecialchars($awd['award_no']); ?>" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success generate-award" 
                                                            data-award='<?php echo json_encode([
                                                                'award_no' => $awd['award_no'],
                                                                'recipient_name' => $awd['recipient_name'],
                                                                'award_name' => $awd['award_name'],
                                                                'category' => $awd['category'],
                                                                'content' => $awd['content'],
                                                                'venue' => $awd['venue'],
                                                                'award_date' => $awd['award_date'],
                                                                'registration_no' => $awd['registration_no'],
                                                                'photo_path' => $awd['photo_path']
                                                            ]); ?>' 
                                                            title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No awards found.
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
            
            <!-- Delete Modal -->
            <div class="modal fade" id="deleteAwardModal" tabindex="-1" aria-labelledby="deleteAwardModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteAwardModalLabel">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Do you really want to delete award <strong id="awardNo"></strong>?</p>
                            <p class="text-danger mt-2">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="post" id="deleteAwardForm">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="award_id" id="award_id">
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
    const awardContent = <?php echo json_encode($awardContent); ?>;
    
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
                return inputDate;
            }
            
            let day = String(date.getDate()).padStart(2, '0');
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            let monthName = months[date.getMonth()];
            let year = date.getFullYear();
            return `${day} ${monthName} ${year}`;
        } catch (e) {
            return inputDate;
        }
    }

    // Generate Award PDF
    document.querySelectorAll('.generate-award').forEach(button => {
        button.addEventListener('click', async function() {
            try {
                console.log('Starting award generation...');
                
                let awardData;
                try {
                    awardData = JSON.parse(this.getAttribute('data-award'));
                    console.log('Award data parsed:', awardData);
                } catch (parseError) {
                    throw new Error('Invalid award data: ' + parseError.message);
                }

                if (!awardData.recipient_name || !awardData.award_name) {
                    throw new Error('Missing required award data');
                }

                const formattedDate = formatDate(awardData.award_date);
                
                const container = document.createElement('div');
                container.style.cssText = `
                    width: 1419px;
                    height: 2125px;
                    position: fixed;
                    left: -9999px;
                    font-family: 'Times New Roman', serif;
                    background-color: white;
                    padding: 0;
                    margin: 0;
                    background-image: url('${awardContent.template_path}');
                    background-size: 100% 100%;
                    background-repeat: no-repeat;
                    background-position: 0 0;
                `;
                
                let photoPath = awardData.photo_path ? (awardData.photo_path.startsWith('../uploads/profiles/') ? '<?php echo SITE_URL; ?>/uploads/profiles/' + awardData.photo_path.substring(20) : '<?php echo SITE_URL; ?>/img/awards/' + awardData.photo_path) + '?v=' + Date.now() : '';
                
                container.innerHTML = `
                <div style="position: relative; width: 100%; height: 100%; font-family: 'Times New Roman', serif;">
                    
                    <!-- Award Name -->
                    <div style="position: absolute; left: 52%; transform: translateX(-50%); top: 103.9px; width: 1000px; text-align: center; z-index: 2;">
                        <h2 style="margin: 0; font-size: 48px; font-weight: bold; color: #000; text-transform: uppercase;">
                            ${awardData.award_name}
                        </h2>
                    </div>

                    <!-- Reg No. -->
                    <div style="position: absolute; left: 455px; top: 485px; font-size: 24px; font-weight: bold; color: #000;">
                        <p style="margin: 0;">${awardData.registration_no}</p>
                    </div>
                    
                    <!-- SL No. -->
                    <div style="position: absolute; left: 945px; top: 430px; font-size: 24px; font-weight: bold; color: #000;">
                         <p style="margin: 0;">${awardData.award_no}</p>
                    </div>
                    
                    <!-- Photo Section -->
                    ${photoPath ? `
                        <div style="position: absolute; left: 511px; top: 683px; width: 357px; height: 341px; overflow: hidden; z-index: 1; border-radius: 23px;">
                            <img src="${photoPath}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    ` : ''}
                    
                    <!-- Name Section -->
                    <div style="position: absolute; left: 50%; transform: translateX(-50%); top: 1035px; width: 1000px; text-align: center; z-index: 2;">
                        <h2 style="margin: 0; font-size: 60px; font-weight: bold; color: #fff; text-transform: uppercase; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            ${awardData.recipient_name}
                        </h2>
                    </div>

                    <!-- Category Section -->
                    <div style="position: absolute; left: 50%; transform: translateX(-50%); top: 1170px; width: 1000px; text-align: center; z-index: 2;">
                        <h3 style="margin: 0; font-size: 40px; font-weight: bold; color: #fff; text-transform: uppercase; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                            ${awardData.category}
                        </h3>
                    </div>

                    <!-- Content Section -->
                    <div style="position: absolute; left: 50%; transform: translateX(-50%); top: 1250px; width: 1100px; text-align: justify; z-index: 2; color: #000; font-size: 24px; line-height: 1.5;">
                        <p style="margin: 0;">${awardData.content}</p>
                    </div>

                    <!-- Venue and Date -->
                    <div style="position: absolute; left: 157.9px; top: 1875.1px; width: 850px; font-size: 24px; color: #000; font-weight: bold; line-height: 1.4;">
                        <p style="margin: 0;">Venue: ${awardData.venue}</p>
                        <p style="margin: 5px 0 0 0;">Date: ${formattedDate}</p>
                    </div>
                    
                </div>
                `;
                
                document.body.appendChild(container);
                console.log('Award container created and appended');
                
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
                
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                console.log('Starting html2canvas conversion');
                const canvas = await html2canvas(container, {
                    scale: 2,
                    useCORS: true,
                    logging: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    width: 1419,
                    height: 2125
                });
                console.log('Canvas generated');
                
                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                console.log('Image data generated');
                
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'px',
                    format: [1419, 2125]
                });
                
                pdf.addImage(imgData, 'JPEG', 0, 0, 1419, 2125);
                console.log('PDF created');
                
                const filename = `${awardData.award_no}_${awardData.recipient_name.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;
                pdf.save(filename);
                console.log('PDF saved as', filename);
                
                document.body.removeChild(container);
                console.log('Award generation completed successfully');
                
                showNotification('Award downloaded successfully!', 'success');
                
            } catch (error) {
                console.error('Award generation error:', error);
                
                let errorMessage = 'Error generating award. Please try again.';
                if (error.message.includes('Failed to execute')) {
                    errorMessage += ' (Some resources failed to load)';
                } else if (error.message.includes('Invalid award data')) {
                    errorMessage += ' (Invalid award data)';
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

    // Delete award modal
    const deleteAwardModal = document.getElementById('deleteAwardModal');
    const deleteAwardForm = document.getElementById('deleteAwardForm');
    const awardNoSpan = document.getElementById('awardNo');
    const awardIdInput = document.getElementById('award_id');
    
    document.querySelectorAll('.delete-award-btn').forEach(button => {
        button.addEventListener('click', function() {
            const awardId = this.getAttribute('data-id');
            const awardNo = this.getAttribute('data-award-no');
            
            if (awardNoSpan) {
                awardNoSpan.textContent = awardNo;
            }
            
            if (awardIdInput) {
                awardIdInput.value = awardId;
            }
            
            if (deleteAwardModal && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(deleteAwardModal);
                modal.show();
            }
        });
    });
    
    if (deleteAwardForm) {
        deleteAwardForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const awardNo = awardNoSpan ? awardNoSpan.textContent : 'this award';
            if (confirm(`Do you really want to delete award ${awardNo}? This action cannot be undone.`)) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                
                fetch('honorary_awards.php', {
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
    
    // Image preview
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
    
    // Form validation
    const awardForm = document.querySelector('form[method="post"]:not(#deleteAwardForm)');
    if (awardForm) {
        awardForm.addEventListener('submit', function(e) {
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
    
    // Auto-dismiss alerts
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

// Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Auto fill fields on user selection via AJAX
    const autoFillUserSelect = document.getElementById('auto_fill_user');
    
    if (autoFillUserSelect) {
        autoFillUserSelect.addEventListener('change', function() {
            const userId = autoFillUserSelect.value;
            if (userId) {
                fetch(`honorary_awards.php?ajax=get_user_details&user_id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.user) {
                            const u = data.user;
                            const setVal = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
                            setVal('recipient_name', u.name);
                            setVal('mobile', u.mobile);
                            setVal('email', u.email);
                            setVal('gender', u.gender);
                            setVal('profession', u.profession);
                            setVal('address', u.address);
                            setVal('city', u.city);
                            setVal('state', u.state);
                            setVal('pincode', u.pincode);
                            setVal('registration_no', u.registration_id);
                            
                            // Photo handling
                            if (u.profile_image) {
                                document.getElementById('auto_fill_user_photo').value = u.profile_image;
                                const previewContainer = document.getElementById('photo_preview_container');
                                if (previewContainer) {
                                    previewContainer.innerHTML = `<img src="<?php echo SITE_URL; ?>/uploads/profiles/${u.profile_image}" class="img-thumbnail" style="max-height: 150px;" alt="User Photo"> <p class="text-muted mt-1 small">Photo auto-filled from user profile.</p>`;
                                }
                            }
                            
                            showNotification('User details auto-filled.', 'info');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching user details:', error);
                        showNotification('Error fetching user details.', 'danger');
                    });
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>