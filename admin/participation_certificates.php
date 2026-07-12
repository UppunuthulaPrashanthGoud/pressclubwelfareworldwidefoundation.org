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
    ]
];

// Update the participation certificate content configuration
$participationCertificateContent = [
    'organization_name'   => ORGANIZATION_NAME,
    'organization_name_hindi' => ORGANIZATION_NAME_HINDI,
    'header_text'        => 'बदलाव की ओर एक समर्पित प्रयास',
    'registration_number' => 'Registration No.: 238',
    'address'            => $siteConfig['organization_address'],
    'email'              => $siteConfig['organization_email'],
    'phone'              => $siteConfig['organization_phone'],
    'chairman_name'      => CERTIFICATE_CHAIRMAN_NAME,
    'chairman_title'     => CERTIFICATE_CHAIRMAN_TITLE,
    'template_path'      => SITE_URL . '/templates/participation-certificate-template.png',
    'signature_path'     => SITE_URL . '/img/signature.png',
    'seal_path'          => SITE_URL . '/img/seal.png'
];

// Function to generate a unique certificate number for participation
function generateParticipationCertificateNumber() {
    global $db;
    $stmt = $db->query("SELECT certificate_no FROM participation_certificates ORDER BY id DESC LIMIT 1");
    $lastCertificate = $stmt->fetch();
    $newNumber = $lastCertificate ? ((int) substr($lastCertificate['certificate_no'], 4)) + 1 : 1;
    return 'PART' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'अमान्य CSRF टोकन।';
    } else if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $recipient_name = isset($_POST['recipient_name']) ? sanitizeInput($_POST['recipient_name']) : '';
            $designation = isset($_POST['designation']) ? sanitizeInput($_POST['designation']) : '';
            $event_name = isset($_POST['event_name']) ? sanitizeInput($_POST['event_name']) : '';
            $event_date = isset($_POST['event_date']) ? sanitizeInput($_POST['event_date']) : '';
            $location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
            $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : '';
            $issue_date = isset($_POST['issue_date']) ? sanitizeInput($_POST['issue_date']) : '';
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            // Validate user and get membership type
            $stmt = $db->prepare("SELECT membership_type FROM users WHERE id = ? AND status = 'approved'");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $validDesignations = [];
            if ($user) {
                $membership_type = $user['membership_type'];
                $stmt = $db->prepare("SELECT designation FROM membership_designations WHERE membership_type = ? AND status = 'active'");
                $stmt->execute([$membership_type]);
                $validDesignations = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'designation');
            }
            
            // Enhanced validation
            if (empty($recipient_name) || empty($designation) || empty($event_name) || empty($event_date) || empty($issue_date) || empty($user_id)) {
                $error = "सभी आवश्यक फ़ील्ड भरें।";
            } elseif (!$user) {
                $error = "उपयोगकर्ता नहीं मिला या अनुमोदित नहीं है।";
            } elseif (!in_array($designation, $validDesignations)) {
                $error = "अमान्य पद (Designation)। कृपया उपयोगकर्ता के स्तर के अनुसार चुनें।";
            } elseif (!in_array($status, ['active', 'inactive'])) {
                $error = "अमान्य स्थिति।";
            } else {
                try {
                    if ($formAction === 'add') {
                        $certificate_no = generateParticipationCertificateNumber();
                        $stmt = $db->prepare("
                            INSERT INTO participation_certificates (certificate_no, recipient_name, designation, event_name, event_date, location, description, issue_date, status, user_id, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$certificate_no, $recipient_name, $designation, $event_name, $event_date, $location, $description, $issue_date, $status, $user_id]);
                    } else {
                        $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
                        $stmt = $db->prepare("
                            UPDATE participation_certificates 
                            SET recipient_name = ?, designation = ?, event_name = ?, event_date = ?, 
                                location = ?, description = ?, issue_date = ?, status = ?, user_id = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$recipient_name, $designation, $event_name, $event_date, $location, $description, $issue_date, $status, $user_id, $cert_id]);
                    }
                    
                    $success = "सहभागिता प्रमाणपत्र सफलतापूर्वक " . ($formAction === 'add' ? 'बनाया गया!' : 'अपडेट किया गया!');
                    header("Location: participation_certificates.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    logError('Participation certificate processing error: ' . $e->getMessage());
                    $error = "त्रुटि: " . $e->getMessage();
                }
            }
        } elseif ($formAction === 'delete') {
            $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
            try {
                $stmt = $db->prepare("DELETE FROM participation_certificates WHERE id = ?");
                $stmt->execute([$cert_id]);
                
                $success = "सहभागिता प्रमाणपत्र सफलतापूर्वक हटा दिया गया!";
                header("Location: participation_certificates.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                logError('Participation certificate deletion error: ' . $e->getMessage());
                $error = "हटाने में समस्या: " . $e->getMessage();
            }
        }
    }
}

// Get participation certificates for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    // Create table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS participation_certificates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            certificate_no VARCHAR(20) UNIQUE NOT NULL,
            recipient_name VARCHAR(255) NOT NULL,
            designation VARCHAR(100) NOT NULL,
            event_name VARCHAR(255) NOT NULL,
            event_date DATE NOT NULL,
            location VARCHAR(255),
            description TEXT,
            issue_date DATE NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status),
            INDEX idx_certificate_no (certificate_no)
        )
    ";
    $db->exec($createTableSQL);
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM participation_certificates");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT pc.*, u.name as user_name, u.profile_image as profile_photo FROM participation_certificates pc LEFT JOIN users u ON pc.user_id = u.id ORDER BY pc.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("SELECT designation, designation_hindi FROM membership_designations WHERE status = 'active'");
    $stmt->execute();
    $allDesignations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError('Database error in participation certificate listing: ' . $e->getMessage());
    $error = "डेटाबेस त्रुटि: " . $e->getMessage();
    $certificates = [];
    $allDesignations = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "नया सहभागिता प्रमाणपत्र जोड़ें" : (($action === 'edit') ? "सहभागिता प्रमाणपत्र संपादित करें" : "सहभागिता प्रमाणपत्र प्रबंधन");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus"></i> नया सहभागिता प्रमाणपत्र जोड़ें
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit"></i> सहभागिता प्रमाणपत्र संपादित करें
                <?php else: ?>
                    <i class="fas fa-certificate"></i> सहभागिता प्रमाणपत्र प्रबंधन
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="participation_certificates.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> वापस
                    </a>
                <?php else: ?>
                    <a href="participation_certificates.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> नया सहभागिता प्रमाणपत्र जोड़ें
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
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "नया सहभागिता प्रमाणपत्र जोड़ें" : "सहभागिता प्रमाणपत्र संपादित करें"; ?>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="cert_id" value="<?php echo $certificate['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">उपयोगकर्ता <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">-- उपयोगकर्ता चुनें --</option>
                                    <?php
                                    $userStmt = $db->query("SELECT id, name FROM users WHERE status = 'approved' ORDER BY name");
                                    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($users as $user) {
                                        $selected = (isset($certificate['user_id']) && $certificate['user_id'] == $user['id']) ? 'selected' : '';
                                        echo "<option value='{$user['id']}' $selected>" . htmlspecialchars($user['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="recipient_name" class="form-label">प्राप्तकर्ता का नाम <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" required 
                                       value="<?php echo htmlspecialchars($certificate['recipient_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="designation" class="form-label">पद (Designation) <span class="text-danger">*</span></label>
                                <select class="form-select" id="designation" name="designation" required>
                                    <option value="">-- पहले उपयोगकर्ता चुनें --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="event_name" class="form-label">कार्यक्रम/घटना का नाम <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="event_name" name="event_name" required 
                                       value="<?php echo htmlspecialchars($certificate['event_name'] ?? ''); ?>"
                                       placeholder="जैसे: राष्ट्रीय सेमिनार, कार्यशाला, सम्मेलन आदि">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="event_date" class="form-label">कार्यक्रम की तिथि <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required
                                       value="<?php echo htmlspecialchars($certificate['event_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">स्थान</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       value="<?php echo htmlspecialchars($certificate['location'] ?? ''); ?>"
                                       placeholder="कार्यक्रम का स्थान">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="description" class="form-label">विवरण</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                          placeholder="कार्यक्रम/सहभागिता का विस्तृत विवरण"><?php echo htmlspecialchars($certificate['description'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="issue_date" class="form-label">जारी करने की तिथि <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date" required
                                       value="<?php echo htmlspecialchars($certificate['issue_date'] ?? date('Y-m-d')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">स्थिति <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo (isset($certificate['status']) && $certificate['status'] == 'active') ? 'selected' : ''; ?>>सक्रिय</option>
                                    <option value="inactive" <?php echo (isset($certificate['status']) && $certificate['status'] == 'inactive') ? 'selected' : ''; ?>>निष्क्रिय</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "सहभागिता प्रमाणपत्र बनाएं" : "अपडेट करें"; ?>
                            </button>
                            <a href="participation_certificates.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> रद्द करें
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-certificate"></i> सहभागिता प्रमाणपत्र सूची
                </div>
                <div class="card-body">
                    <?php if (count($certificates) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>प्रमाणपत्र नं.</th>
                                        <th>नाम</th>
                                        <th>पद</th>
                                        <th>कार्यक्रम</th>
                                        <th>कार्यक्रम तिथि</th>
                                        <th>स्थान</th>
                                        <th>जारी तिथि</th>
                                        <th>स्थिति</th>
                                        <th>कार्य</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificates as $cert): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cert['certificate_no']); ?></td>
                                            <td><?php echo htmlspecialchars($cert['recipient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($cert['designation'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($cert['event_name']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($cert['event_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($cert['location'] ?: '-'); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($cert['issue_date'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $cert['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $cert['status'] == 'active' ? 'सक्रिय' : 'निष्क्रिय'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="participation_certificates.php?action=edit&id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-primary" title="संपादित करें">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger delete-certificate-btn" data-id="<?php echo $cert['id']; ?>" data-cert-no="<?php echo htmlspecialchars($cert['certificate_no']); ?>" title="हटाएं">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success generate-participation-certificate" 
                                                        data-certificate='<?php echo json_encode([
                                                            'certificate_no' => $cert['certificate_no'],
                                                            'recipient_name' => $cert['recipient_name'],
                                                            'designation' => $cert['designation'],
                                                            'event_name' => $cert['event_name'],
                                                            'event_date' => $cert['event_date'],
                                                            'location' => $cert['location'],
                                                            'description' => $cert['description'],
                                                            'issue_date' => $cert['issue_date'],
                                                            'profile_photo' => $cert['profile_photo'] ?? '',
                                                            'designation_hindi' => array_filter($allDesignations, function($d) use ($cert) { return $d['designation'] == $cert['designation']; })[0]['designation_hindi'] ?? $cert['designation']
                                                        ]); ?>' 
                                                        title="डाउनलोड करें">
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
                            <i class="fas fa-info-circle"></i> कोई सहभागिता प्रमाणपत्र नहीं मिला।
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
            <div class="modal fade" id="deleteCertificateModal" tabindex="-1" aria-labelledby="deleteCertificateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteCertificateModalLabel">पुष्टि करें</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>क्या आप वाकई सहभागिता प्रमाणपत्र <strong id="certificateNo"></strong> को हटाना चाहते हैं?</p>
                            <p class="text-danger mt-2">यह क्रिया पूर्ववत नहीं की जा सकती है।</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                            <form method="post" id="deleteCertificateForm">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="cert_id" id="cert_id">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                <button type="submit" class="btn btn-danger">हटाएं</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include the participation certificate generation script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const { jsPDF } = window.jspdf;
    
    // Pass PHP participationCertificateContent to JavaScript
    const certificateContent = <?= json_encode($participationCertificateContent) ?>;
    const templatePath = certificateContent.template_path;
    const chairmanSignaturePath = certificateContent.signature_path;

    document.querySelectorAll('.generate-participation-certificate').forEach(button => {
        button.addEventListener('click', async function() {
            try {
                const certData = JSON.parse(this.getAttribute('data-certificate'));
                console.log('Certificate Data:', certData);

                // Create certificate container
                const container = document.createElement('div');
                container.style.cssText = `
                    width: 1404px;
                    height: 990px;
                    position: fixed;
                    left: -9999px;
                    font-family: Arial, 'Noto Sans Devanagari', sans-serif;
                    background-color: white;
                    padding: 0;
                    background-image: url('${templatePath}');
                    background-size: cover;
                    background-repeat: no-repeat;
                    background-position: center;
                `;

                // Format date for display
                const formatDate = (inputDate) => {
                    let date = new Date(inputDate);
                    let day = String(date.getDate()).padStart(2, '0');
                    let month = String(date.getMonth() + 1).padStart(2, '0');
                    let year = date.getFullYear();
                    return `${day}-${month}-${year}`;
                };

                const formattedIssueDate = formatDate(certData.issue_date);
                const formattedEventDate = formatDate(certData.event_date);

                // Build participation certificate content
                let participationText = `${certData.recipient_name} ने ${certData.designation_hindi} के रूप में "${certData.event_name}" में सक्रिय सहभागिता की है`;
                
                if (certData.location) {
                    participationText += ` जो ${certData.location} में आयोजित किया गया`;
                }
                
                participationText += ` दिनांक ${formattedEventDate} को।`;
                
                if (certData.description) {
                    participationText += ` ${certData.description}`;
                }

                container.innerHTML = `
                    <div style="position: relative; height: 100%; padding: 0;">
                        <!-- Recipient Name -->
                        <div style="
                            position: absolute;
                            top: 480px;
                            left: 50%;
                            transform: translateX(-50%);
                            text-align: center;
                            font-size: 48px;
                            font-weight: bold;
                            color: #2c5282;
                            font-family: 'Noto Sans Devanagari', Arial, sans-serif;
                        ">
                            ${certData.recipient_name}
                        </div>
                        
                        <!-- Description -->
                        <div style="
                            position: absolute;
                            top: 560px;
                            left: 50%;
                            width: 90%;
                            transform: translateX(-50%);
                            text-align: center;
                            font-size: 32px;
                            color: #2d3748;
                            font-family: 'Noto Sans Devanagari', Arial, sans-serif;
                            line-height: 1.4;
                            padding: 20px;
                        ">
                            ${participationText}
                        </div>
                        
                        <!-- Date and Certificate Number - Left Side -->
                        <div style="
                            position: absolute;
                            bottom: 150px;
                            left: 80px;
                            font-size: 22px;
                            color: #2d3748;
                            font-weight: bold;
                            line-height: 1.6;
                        ">
                            <div>${certData.certificate_no}</div>
                            <div>Date: ${formattedIssueDate}</div>
                        </div>
                        
                        <!-- Chairman Signature - Right Side, Moved Up -->
                        <div style="
                            position: absolute;
                            bottom: 100px;
                            right: 100px;
                            text-align: center;
                        ">
                            <img src="${certificateContent.signature_path}" alt="Chairman Signature" style="width: 150px; height: auto; margin-bottom: 5px;">
                            <div style="position: relative;">
                                <img src="${certificateContent.seal_path}" alt="Seal" style="width: 100px; height: auto; position: absolute; top: -80px; left: 25px; opacity: 0.8;">
                            </div>
                            <p style="margin: 0; font-size: 18px; font-weight: bold; color: #000;">
                                ${certificateContent.chairman_name}
                            </p>
                            <p style="margin: 0; font-size: 16px; color: #C82333;">
                                ${certificateContent.chairman_title}
                            </p>
                        </div>
                    </div>
                `;

                // Add container to DOM
                document.body.appendChild(container);

                // Wait for images to load
                const images = container.querySelectorAll('img');
                const imagePromises = Array.from(images).map(img => {
                    return new Promise((resolve) => {
                        if (img.complete) {
                            resolve();
                        } else {
                            img.onload = resolve;
                            img.onerror = resolve; // Continue even if error
                        }
                    });
                });
                await Promise.all(imagePromises);

                // Generate PDF
                const canvas = await html2canvas(container, {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    allowTaint: true,
                    backgroundColor: null
                });

                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                
                // Create PDF with the template's aspect ratio
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'px',
                    format: [1404, 990]
                });

                pdf.addImage(imgData, 'JPEG', 0, 0, 1404, 990);
                pdf.save(`${certData.certificate_no}_participation.pdf`);

                // Remove container from DOM
                document.body.removeChild(container);

            } catch (error) {
                console.error('Certificate generation error:', error);
                alert('प्रमाण पत्र बनाने में त्रुटि। कृपया पुनः प्रयास करें।');
            }
        });
    });

    // Handle user selection and designation loading
    const userSelect = document.getElementById('user_id');
    const designationSelect = document.getElementById('designation');
    const recipientNameField = document.getElementById('recipient_name');
    
    if (userSelect && designationSelect) {
        userSelect.addEventListener('change', function() {
            const userId = this.value;
            
            designationSelect.innerHTML = '<option value="">-- लोड हो रहा है... --</option>';
            
            if (userId) {
                fetch(`participation_certificates.php?ajax=get_designations&user_id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        designationSelect.innerHTML = '<option value="">-- पद चुनें --</option>';
                        
                        if (data.success && data.designations) {
                            data.designations.forEach(designation => {
                                const option = document.createElement('option');
                                option.value = designation.designation;
                                option.textContent = `${designation.designation_hindi} (${designation.designation})`;
                                designationSelect.appendChild(option);
                            });
                        } else {
                            designationSelect.innerHTML = '<option value="">-- कोई पद उपलब्ध नहीं --</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching designations:', error);
                        designationSelect.innerHTML = '<option value="">-- त्रुटि: पुनः प्रयास करें --</option>';
                    });
            } else {
                designationSelect.innerHTML = '<option value="">-- पहले उपयोगकर्ता चुनें --</option>';
            }
            
            // Update recipient name
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            if (recipientNameField && selectedOption.text !== '-- उपयोगकर्ता चुनें --') {
                recipientNameField.value = selectedOption.text;
            }
        });
    }

    // Delete certificate modal handling
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

    // Form validation
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
                
                showNotification('कृपया सभी आवश्यक फ़ील्ड भरें।', 'danger');
            }
        });
    }

    // Auto-hide alerts
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
</script>

<?php include 'includes/footer.php'; ?>