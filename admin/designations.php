<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

// $pageTitle = 'Designation Management';
$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;
$status_filter = $_GET['status'] ?? '';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                try {
                    $membership_type = sanitizeInput($_POST['membership_type']);
                    $designation = sanitizeInput($_POST['designation']);
                    $designation_hindi = sanitizeInput($_POST['designation_hindi']);
                    $sort_order = !empty($_POST['sort_order']) ? (int)sanitizeInput($_POST['sort_order']) : 0;
                    $status = sanitizeInput($_POST['status']);

                    // Validate required fields
                    if (empty($membership_type) || empty($designation) || empty($designation_hindi)) {
                        throw new Exception('Please fill all required fields.');
                    }

                    // Validate membership type
                    $valid_memberships = [
                        'active', 'gram_panchayat', 'block', 'tehsil',
                        'district', 'mandal', 'state', 'national'
                    ];
                    if (!in_array($membership_type, $valid_memberships)) {
                        throw new Exception('Invalid membership type.');
                    }

                    // Validate designation (ensure it's unique for the membership type)
                    $stmt = $db->prepare("
                        SELECT COUNT(*) FROM membership_designations 
                        WHERE designation = ? AND membership_type = ? AND id != ?
                    ");
                    $stmt->execute([$designation, $membership_type, $id]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception('This designation already exists for this membership type.');
                    }

                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO membership_designations (
                                membership_type, designation, designation_hindi, sort_order, status, created_at
                            ) VALUES (?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$membership_type, $designation, $designation_hindi, $sort_order, $status]);
                        $message = 'Designation successfully added!';
                    } else {
                        $stmt = $db->prepare("
                            UPDATE membership_designations 
                            SET membership_type = ?, designation = ?, designation_hindi = ?, 
                                sort_order = ?, status = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$membership_type, $designation, $designation_hindi, $sort_order, $status, $id]);
                        $message = 'Designation information updated!';
                    }

                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'Error: ' . htmlspecialchars($e->getMessage());
                }
                break;

            case 'delete':
                try {
                    $delete_id = $_POST['id'];
                    $stmt = $db->prepare("DELETE FROM membership_designations WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $message = 'Designation successfully deleted!';
                } catch (Exception $e) {
                    $error = 'Deletion error: ' . htmlspecialchars($e->getMessage());
                }
                break;
        }
    }
}

// Get designation data for editing
$designation_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM membership_designations WHERE id = ?");
    $stmt->execute([$id]);
    $designation_data = $stmt->fetch();

    if (!$designation_data) {
        $error = 'Designation not found!';
        $action = 'list';
    }
}

// Get designations list
$designations = [];
if ($action === 'list') {
    $where_clause = '';
    $params = [];

    if (!empty($status_filter)) {
        $where_clause = 'WHERE status = ?';
        $params[] = $status_filter;
    }

    $stmt = $db->prepare("SELECT * FROM membership_designations $where_clause ORDER BY membership_type, sort_order, designation");
    $stmt->execute($params);
    $designations = $stmt->fetchAll();
}

// Function to get membership type name
function getMembershipTypeName($type) {
    $names = [
        'active' => 'Active Membership',
        'gram_panchayat' => 'Gram Panchayat Level',
        'block' => 'Block Level',
        'tehsil' => 'Tehsil Level',
        'district' => 'District Level',
        'mandal' => 'Mandal Level',
        'state' => 'State Level',
        'national' => 'National Level'
    ];
    return $names[$type] ?? $type;
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-tags me-3"></i> Designation Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add New Designation
                </a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back
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
        <!-- Designations List -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list me-2"></i> Designation List</h5>
                <div class="filter-buttons">
                    <a href="?" class="btn btn-sm btn-outline-primary <?php echo empty($status_filter) ? 'active' : ''; ?>">All</a>
                    <a href="?status=active" class="btn btn-sm btn-outline-success <?php echo $status_filter === 'active' ? 'active' : ''; ?>">Active</a>
                    <a href="?status=inactive" class="btn btn-sm btn-outline-danger <?php echo $status_filter === 'inactive' ? 'active' : ''; ?>">Inactive</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Membership Type</th>
                                <th>Designation (English)</th>
                                <th>Designation (Hindi)</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($designations as $designation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(getMembershipTypeName($designation['membership_type'])); ?></td>
                                <td><?php echo htmlspecialchars($designation['designation']); ?></td>
                                <td><?php echo htmlspecialchars($designation['designation_hindi']); ?></td>
                                <td><?php echo htmlspecialchars($designation['sort_order']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $designation['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo htmlspecialchars($designation['status'] === 'active' ? 'Active' : 'Inactive'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $designation['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDesignation(<?php echo $designation['id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                    <?php echo $action === 'add' ? 'Add New Designation' : 'Edit Designation'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="membership_type" class="form-label">Membership Type *</label>
                                <select class="form-select" id="membership_type" name="membership_type" required>
                                    <option value="">Select</option>
                                    <option value="active" <?php echo ($designation_data['membership_type'] ?? '') === 'active' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('active'); ?></option>
                                    <option value="gram_panchayat" <?php echo ($designation_data['membership_type'] ?? '') === 'gram_panchayat' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('gram_panchayat'); ?></option>
                                    <option value="block" <?php echo ($designation_data['membership_type'] ?? '') === 'block' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('block'); ?></option>
                                    <option value="tehsil" <?php echo ($designation_data['membership_type'] ?? '') === 'tehsil' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('tehsil'); ?></option>
                                    <option value="district" <?php echo ($designation_data['membership_type'] ?? '') === 'district' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('district'); ?></option>
                                    <option value="mandal" <?php echo ($designation_data['membership_type'] ?? '') === 'mandal' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('mandal'); ?></option>
                                    <option value="state" <?php echo ($designation_data['membership_type'] ?? '') === 'state' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('state'); ?></option>
                                    <option value="national" <?php echo ($designation_data['membership_type'] ?? '') === 'national' ? 'selected' : ''; ?>><?php echo getMembershipTypeName('national'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="designation" class="form-label">Designation (English) *</label>
                                <input type="text" class="form-control" id="designation" name="designation" required
                                       value="<?php echo htmlspecialchars($designation_data['designation'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="designation_hindi" class="form-label">Designation (Hindi) *</label>
                                <input type="text" class="form-control" id="designation_hindi" name="designation_hindi" required
                                       value="<?php echo htmlspecialchars($designation_data['designation_hindi'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" min="0"
                                       value="<?php echo htmlspecialchars($designation_data['sort_order'] ?? '0'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($designation_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($designation_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save
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
function deleteDesignation(id) {
    if (confirm('Do you really want to delete this designation? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<style>
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
    color: #FF6F0F;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.filter-buttons .btn.active {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
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
}

@media print {
    .page-actions, .btn-group, .filter-buttons {
        display: none;
    }
}
</style>

<?php include 'includes/footer.php'; ?>