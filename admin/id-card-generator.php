<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';
require_once 'includes/universal-id-card-generator.php';

$pageTitle = 'ID Card Generator';
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';

$db = getDbConnection();
$idCardGenerator = new UniversalIdCardGenerator($db);

// Get current user info
$currentUserId = $_SESSION['user_id'];
$currentUserType = $_SESSION['user_type'] ?? 'member';

// Role-based access control
function canAccessUser($targetUserId, $currentUserId, $currentUserType, $db) {
    switch ($currentUserType) {
        case 'admin':
            return true;
        case 'coordinator':
            if ($targetUserId == $currentUserId) {
                return true;
            }
            $stmt = $db->prepare("
                SELECT working_area, district, state 
                FROM users 
                WHERE id = ? AND user_type = 'coordinator'
            ");
            $stmt->execute([$currentUserId]);
            $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$coordinator) return false;
            $stmt = $db->prepare("
                SELECT id FROM users 
                WHERE id = ? AND (
                    working_area = ? OR 
                    district = ? OR 
                    state = ?
                ) AND user_type IN ('member', 'coordinator')
            ");
            $stmt->execute([
                $targetUserId,
                $coordinator['working_area'] ?? '',
                $coordinator['district'] ?? '',
                $coordinator['state'] ?? ''
            ]);
            return $stmt->fetch() !== false;
        case 'member':
        default:
            return $targetUserId == $currentUserId;
    }
}

// Build WHERE clause for role-based filtering
function buildUserWhereClause($currentUserId, $currentUserType, $db) {
    $whereConditions = [];
    $params = [];
    
    switch ($currentUserType) {
        case 'admin':
            break;
        case 'coordinator':
            $stmt = $db->prepare("
                SELECT working_area, district, state 
                FROM users 
                WHERE id = ? AND user_type = 'coordinator'
            ");
            $stmt->execute([$currentUserId]);
            $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($coordinator) {
                $whereConditions[] = "(
                    id = :current_user_id OR 
                    working_area = :working_area OR 
                    district = :district OR 
                    state = :state
                ) AND user_type IN ('member', 'coordinator')";
                $params[':current_user_id'] = $currentUserId;
                $params[':working_area'] = $coordinator['working_area'] ?? '';
                $params[':district'] = $coordinator['district'] ?? '';
                $params[':state'] = $coordinator['state'] ?? '';
            } else {
                $whereConditions[] = "id = :current_user_id";
                $params[':current_user_id'] = $currentUserId;
            }
            break;
        case 'member':
        default:
            $whereConditions[] = "id = :current_user_id";
            $params[':current_user_id'] = $currentUserId;
            break;
    }
    
    return ['conditions' => $whereConditions, 'params' => $params];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_POST['ajax'] === 'generate_id_card') {
        if (!verifyCSRF($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
        $memberId = (int)$_POST['member_id'];
        if (!canAccessUser($memberId, $currentUserId, $currentUserType, $db)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $memberData = $idCardGenerator->getMemberData($memberId, 'id');
        if ($memberData) {
            echo json_encode([
                'success' => true,
                'data' => $memberData,
                'javascript' => $idCardGenerator->generateJavaScript($memberData)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found or not approved']);
        }
        exit;
    }
    
    if ($_POST['ajax'] === 'save_id_card') {
        if (!verifyCSRF($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
        $memberId = (int)$_POST['member_id'];
        if (!canAccessUser($memberId, $currentUserId, $currentUserType, $db)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }
        $imageData = $_POST['image_data'] ?? '';
        if (empty($imageData)) {
            echo json_encode(['success' => false, 'message' => 'No image data provided']);
            exit;
        }
        $result = $idCardGenerator->saveIdCardImage($memberId, $imageData, 'users');
        echo json_encode($result);
        exit;
    }
}

// Get users for list with pagination and filters based on role
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim(sanitizeInput($_GET['search'])) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$membership_filter = isset($_GET['membership']) ? sanitizeInput($_GET['membership']) : '';
$user_type_filter = isset($_GET['user_type']) ? sanitizeInput($_GET['user_type']) : '';

try {
    // Validate filter inputs
    $valid_statuses = ['pending', 'approved', 'rejected', ''];
    $valid_membership_types = ['active', 'gram_panchayat', 'block', 'tehsil', 'district', 'mandal', 'state', 'national', ''];
    $valid_user_types = ['member', 'coordinator', 'admin', ''];
    if (!in_array($status_filter, $valid_statuses)) {
        $status_filter = '';
    }
    if (!in_array($membership_filter, $valid_membership_types)) {
        $membership_filter = '';
    }
    if (!in_array($user_type_filter, $valid_user_types)) {
        $user_type_filter = '';
    }

    // Build role-based WHERE clause
    $roleAccess = buildUserWhereClause($currentUserId, $currentUserType, $db);
    $whereConditions = $roleAccess['conditions'];
    $params = $roleAccess['params'];

    // Add additional filters
    if (!empty($search)) {
        $whereConditions[] = "(name LIKE :search OR email LIKE :search OR mobile LIKE :search OR registration_id LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    if (!empty($status_filter)) {
        $whereConditions[] = "status = :status";
        $params[':status'] = $status_filter;
    }
    if (!empty($membership_filter)) {
        $whereConditions[] = "membership_type = :membership";
        $params[':membership'] = $membership_filter;
    }
    if (!empty($user_type_filter)) {
        $whereConditions[] = "user_type = :user_type";
        $params[':user_type'] = $user_type_filter;
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get users count
    $countQuery = "SELECT COUNT(*) FROM users $whereClause";
    $stmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();

    // Get users with pagination
    $query = "SELECT id, name, mobile, email, profile_image, designation, registration_id, status, membership_type, user_type, blood_group, valid_from, valid_until, created_at 
              FROM users $whereClause 
              ORDER BY created_at DESC 
              LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log("Database error in user query: " . $e->getMessage());
    $users = [];
    $totalPages = 0;
}

// Get user for generation
$user = null;
if ($action === 'generate' && $id > 0) {
    if (!canAccessUser($id, $currentUserId, $currentUserType, $db)) {
        $error = "Access denied. You can only generate ID cards for authorized users.";
        $action = 'list';
    } else {
        $user = $idCardGenerator->getMemberData($id, 'id');
        if (!$user) {
            $error = "User not found or not approved.";
            $action = 'list';
        }
    }
}

// Page title based on role
$rolePageTitles = [
    'admin' => 'ID Card Generator - All Users',
    'coordinator' => 'ID Card Generator - Your Area',
    'member' => 'My ID Card'
];

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <?php if ($action === 'list'): ?>
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-id-card"></i> <?php echo $rolePageTitles[$currentUserType] ?? 'ID Card Generator'; ?>
                    <span class="badge bg-<?php echo $currentUserType === 'admin' ? 'danger' : ($currentUserType === 'coordinator' ? 'warning' : 'primary'); ?> ms-2">
                        <?php echo ucfirst($currentUserType); ?>
                    </span>
                </h1>
                <div class="page-actions">
                    <!-- Removed Current Authority display -->
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
            
            <!-- Role-based access info -->
            <?php if ($currentUserType !== 'admin'): ?>
                <div class="alert alert-info">
                    <i class="fas fa-shield-alt"></i>
                    <strong>Access Level:</strong>
                    <?php if ($currentUserType === 'coordinator'): ?>
                        You can generate ID cards for users in your working area and your own ID card.
                    <?php else: ?>
                        You can only view and generate your own ID card.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters (show only for admin and coordinator) -->
            <?php if ($currentUserType !== 'member'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filters
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3" id="filterForm">
                        <input type="hidden" name="action" value="list">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="membership" class="form-select">
                                <option value="">All Membership Types</option>
                                <option value="active" <?php echo $membership_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="gram_panchayat" <?php echo $membership_filter === 'gram_panchayat' ? 'selected' : ''; ?>>Gram Panchayat</option>
                                <option value="block" <?php echo $membership_filter === 'block' ? 'selected' : ''; ?>>Block</option>
                                <option value="tehsil" <?php echo $membership_filter === 'tehsil' ? 'selected' : ''; ?>>Tehsil</option>
                                <option value="district" <?php echo $membership_filter === 'district' ? 'selected' : ''; ?>>District</option>
                                <option value="mandal" <?php echo $membership_filter === 'mandal' ? 'selected' : ''; ?>>Mandal</option>
                                <option value="state" <?php echo $membership_filter === 'state' ? 'selected' : ''; ?>>State</option>
                                <option value="national" <?php echo $membership_filter === 'national' ? 'selected' : ''; ?>>National</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="user_type" class="form-select">
                                <option value="">All User Types</option>
                                <option value="member" <?php echo $user_type_filter === 'member' ? 'selected' : ''; ?>>Member</option>
                                <option value="coordinator" <?php echo $user_type_filter === 'coordinator' ? 'selected' : ''; ?>>Coordinator</option>
                                <option value="admin" <?php echo $user_type_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="id-card-generator.php?action=list" class="btn btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Users List -->
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-users"></i> Users List
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Designation</th>
                                    <th>Registration ID</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No users found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $counter = $offset + 1; ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="<?php echo $user['id'] == $currentUserId ? 'table-warning' : ''; ?>">
                                            <td><?php echo $counter++; ?></td>
                                            <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['designation']); ?></td>
                                            <td><?php echo htmlspecialchars($user['registration_id']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['status'] === 'approved' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td class="table-actions">
                                                <a href="id-card-generator.php?action=generate&id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-id-card"></i> Generate
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="id-card-generator.php?action=list&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&membership=<?php echo urlencode($membership_filter); ?>&user_type=<?php echo urlencode($user_type_filter); ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="id-card-generator.php?action=list&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&membership=<?php echo urlencode($membership_filter); ?>&user_type=<?php echo urlencode($user_type_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="id-card-generator.php?action=list&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&membership=<?php echo urlencode($membership_filter); ?>&user_type=<?php echo urlencode($user_type_filter); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- ID Card Generation -->
            <div class="id-card-generation-container">
                <div class="row">
                    <!-- User Details -->
                    <div class="col-lg-4">
                        <div class="admin-card member-details-section">
                            <div class="card-header">
                                <i class="fas fa-user"></i> Member Details
                            </div>
                            <div class="card-body">
                                <?php if ($user): ?>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th>Name:</th>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>ID:</th>
                                            <td><?php echo htmlspecialchars($user['registration_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>User Type:</th>
                                            <td>
                                                <span class="badge bg-<?php echo $user['user_type'] === 'admin' ? 'danger' : ($user['user_type'] === 'coordinator' ? 'warning' : 'primary'); ?>">
                                                    <?php echo ucfirst($user['user_type']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Designation:</th>
                                            <td><?php echo htmlspecialchars($user['designation'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Mobile:</th>
                                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Blood Group:</th>
                                            <td><?php echo htmlspecialchars($user['blood_group'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Valid From:</th>
                                            <td><?php echo htmlspecialchars($user['valid_from'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Valid Until:</th>
                                            <td><?php echo htmlspecialchars($user['valid_until'] ?? 'N/A'); ?></td>
                                        </tr>
                                    </table>
                                    
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-primary" onclick="generateIdCard()">
                                            <i class="fas fa-magic"></i> 
                                            <?php echo $user['id'] == $currentUserId ? 'Generate My ID Card' : 'Generate ID Card'; ?>
                                        </button>
                                        <div id="saveSection" style="display: none;">
                                            <button type="button" class="btn btn-success" onclick="saveIdCard()">
                                                <i class="fas fa-save"></i> Save ID Card
                                            </button>
                                        </div>
                                        <div id="downloadSection" style="display: none;">
                                            <a id="downloadLink" href="#" class="btn btn-info">
                                                <i class="fas fa-download"></i> Download ID Card
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-danger">No user data available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ID Card Preview -->
                    <div class="col-lg-8">
                        <div class="admin-card id-card-preview-section">
                            <div class="card-header">
                                <i class="fas fa-id-card"></i> ID Card Preview
                            </div>
                            <div class="card-body">
                                <div id="loading" style="display: none;">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Generating ID Card...</p>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <canvas id="idCardCanvas" width="1011" height="1378" style="border: 1px solid #ddd; max-width: 100%; height: auto;"></canvas>
                                    </div>
                                </div>
                                <div id="saveSection" class="row mt-3" style="display: none;">
                                    <div class="col-12 text-center">
                                        <button onclick="saveIdCard()" class="btn btn-success">
                                            <i class="fas fa-save"></i> Save ID Card
                                        </button>
                                    </div>
                                </div>
                                <div id="downloadSection" class="row mt-3" style="display: none;">
                                    <div class="col-12 text-center">
                                        <a id="downloadLink" href="#" class="btn btn-primary">
                                            <i class="fas fa-download"></i> Download ID Card
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Required Libraries -->
            <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
            
            <!-- Generate JavaScript for ID Card -->
            <?php echo $idCardGenerator->generateJavaScript($user, 'idCardCanvas'); ?>
            
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables for role-based access
let memberData = <?php echo json_encode($user ?? [], JSON_UNESCAPED_UNICODE); ?>;
let currentMemberId = <?php echo $id; ?>;
let currentUserType = '<?php echo $currentUserType; ?>';
let currentUserId = <?php echo $currentUserId; ?>;

// Main ID card generation function with role-based access
async function generateUniversalIdCard() {
    if (!memberData || !memberData.name) {
        Swal.fire('Error', 'No user data available', 'error');
        return;
    }
    if (currentUserType !== 'admin' && memberData.id != currentUserId) {
        if (currentUserType === 'member') {
            Swal.fire('Error', 'You can only generate your own ID card', 'error');
            return;
        }
    }
    console.log('🎨 Generating ID card for user type:', memberData.user_type || 'member');
    console.log('👤 User:', memberData.name);
    console.log('🔐 Current user type:', currentUserType);
    
    document.getElementById('loading').style.display = 'block';
    document.getElementById('saveSection').style.display = 'none';
    document.getElementById('downloadSection').style.display = 'none';

    try {
        if (typeof UniversalIdCardGenerator !== 'undefined') {
            UniversalIdCardGenerator.memberData = memberData;
            UniversalIdCardGenerator.updateScannableData(memberData);
            console.log('✅ Updated generator with user data');
            console.log('📊 Barcode data:', UniversalIdCardGenerator.barcodeData.substring(0, 50) + '...');
            console.log('📱 QR data:', UniversalIdCardGenerator.qrData.substring(0, 50) + '...');
            
            UniversalIdCardGenerator.generate((success, result) => {
                document.getElementById('loading').style.display = 'none';
                if (success) {
                    document.getElementById('saveSection').style.display = 'block';
                    const downloadLink = document.getElementById('downloadLink');
                    downloadLink.href = result;
                    downloadLink.download = `ID_Card_${memberData.registration_id}.png`;
                    document.getElementById('downloadSection').style.display = 'block';
                    Swal.fire({
                        title: 'Success!',
                        text: 'ID Card generated successfully! Click the download button.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to generate ID card: ' + result,
                        icon: 'error'
                    });
                }
            });
        } else {
            throw new Error('Universal ID Card Generator not loaded');
        }
    } catch (error) {
        console.error('❌ Generate ID card error:', error);
        document.getElementById('loading').style.display = 'none';
        Swal.fire({
            title: 'Error',
            text: error.message || 'Failed to generate ID card',
            icon: 'error'
        });
    }
}

// Alias for compatibility
function generateIdCard() {
    generateUniversalIdCard();
}

// Save ID Card with role-based access check
function saveIdCard() {
    const canvas = document.getElementById('idCardCanvas');
    if (!canvas) {
        Swal.fire('Error', 'No ID card to save', 'error');
        return;
    }
    if (currentUserType !== 'admin' && memberData.id != currentUserId) {
        if (currentUserType === 'member') {
            Swal.fire('Error', 'You can only save your own ID card', 'error');
            return;
        }
    }
    const imageData = canvas.toDataURL('image/png');
    const formData = new FormData();
    formData.append('ajax', 'save_id_card');
    formData.append('member_id', currentMemberId);
    formData.append('image_data', imageData);
    formData.append('csrf_token', '<?php echo generateCSRF(); ?>');
    
    fetch('id-card-generator.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'ID Card saved successfully!',
                icon: 'success'
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || data.error || 'Failed to save ID card',
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('❌ Save error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Failed to save ID card',
            icon: 'error'
        });
    });
}

// Role-based UI adjustments
function adjustUIForRole() {
    const userType = currentUserType;
    if (userType === 'member') {
        const ownRows = document.querySelectorAll('tr.table-warning');
        ownRows.forEach(row => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-success ms-2';
            badge.textContent = 'Your Record';
            const nameCell = row.querySelector('td:nth-child(2) strong');
            if (nameCell) {
                nameCell.appendChild(badge);
            }
        });
    }
}

// Initialize based on user role
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Role-based ID Card Generator loaded');
    console.log('👤 Current user type:', currentUserType);
    console.log('🆔 Current user ID:', currentUserId);
    
    adjustUIForRole();
    
    <?php if ($action === 'generate' && $user): ?>
        console.log('👤 User data loaded:', memberData);
        console.log('🏷️ User type:', memberData.user_type || 'member');
        if (currentUserType !== 'admin' && memberData.id != currentUserId && currentUserType === 'member') {
            console.warn('⚠️ Member trying to access another user\'s ID card');
            Swal.fire({
                title: 'Access Denied',
                text: 'You can only generate your own ID card',
                icon: 'error'
            }).then(() => {
                window.location.href = 'id-card-generator.php';
            });
        }
        if (typeof UniversalIdCardGenerator !== 'undefined') {
            console.log('✅ Universal ID Card Generator is available');
            UniversalIdCardGenerator.memberData = memberData;
            UniversalIdCardGenerator.updateScannableData(memberData);
            console.log('✅ Scannable data initialized for user type:', memberData.user_type);
            if (typeof UniversalIdCardGenerator.testScannableData === 'function') {
                UniversalIdCardGenerator.testScannableData();
            }
        } else {
            console.error('❌ Universal ID Card Generator not loaded');
        }
    <?php endif; ?>
    
    if (currentUserType === 'coordinator') {
        const generateButtons = document.querySelectorAll('a[href*="action=generate"]');
        generateButtons.forEach(button => {
            if (!button.closest('tr.table-warning')) {
                button.title = 'Generate ID card for user in your area';
            }
        });
    }
    
    const roleMessages = {
        'admin': 'You have full access to all users.',
        'coordinator': 'You can generate ID cards for users in your working area.',
        'member': 'You can only view and generate your own ID card.'
    };
    
    if (roleMessages[currentUserType]) {
        console.log('📋 Access Level:', roleMessages[currentUserType]);
    }

    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const queryParams = new URLSearchParams(formData).toString();
            window.location.href = 'id-card-generator.php?' + queryParams;
        });
    }
});

// Add CSS for role-based styling
const roleStyles = document.createElement('style');
roleStyles.textContent = `
    .table-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    .role-badge-admin {
        background: linear-gradient(45deg, #dc3545, #fd7e14);
    }
    .role-badge-coordinator {
        background: linear-gradient(45deg, #ffc107, #fd7e14);
    }
    .role-badge-member {
        background: linear-gradient(45deg, #0d6efd, #6f42c1);
    }
    .access-denied {
        opacity: 0.5;
        pointer-events: none;
    }
    .own-record {
        border-left: 4px solid #28a745;
    }
`;
document.head.appendChild(roleStyles);
</script>

<style>
.member-details-section .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.id-card-preview-section .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.action-buttons .btn {
    margin: 5px;
    min-width: 150px;
}
.admin-card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.admin-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}
.table-actions .btn {
    margin: 2px;
}
.page-title .badge {
    font-size: 0.7em;
}
.badge.bg-admin {
    background: linear-gradient(45deg, #dc3545, #fd7e14) !important;
}
.badge.bg-coordinator {
    background: linear-gradient(45deg, #ffc107, #fd7e14) !important;
}
.badge.bg-member {
    background: linear-gradient(45deg, #0d6efd, #6f42c1) !important;
}
.alert-info {
    border-left: 4px solid #0dcaf0;
}
@media (max-width: 768px) {
    .id-card-generation-container .col-lg-4 {
        margin-bottom: 20px;
    }
    .action-buttons .btn {
        width: 100%;
        margin: 5px 0;
    }
    .table-responsive {
        font-size: 0.9em;
    }
}
#loading {
    padding: 40px;
    text-align: center;
}
.spinner-border {
    width: 3rem;
    height: 3rem;
}
#idCardCanvas {
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<?php include 'includes/footer.php'; ?>