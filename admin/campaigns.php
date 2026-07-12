<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin and coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

// $pageTitle = 'Crowdfunding Campaign Management';
$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$message = '';
$error = '';

// Function to format date as DD-MM-YYYY
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                try {
                    $title = sanitizeInput($_POST['title']);
                    $description = $_POST['description']; // Rich text content
                    $location = sanitizeInput($_POST['location']);
                    $campaign_date = sanitizeInput($_POST['campaign_date']);
                    $target_amount = floatval($_POST['target_amount']);
                    $raised_amount = floatval($_POST['raised_amount'] ?? 0);
                    $status = sanitizeInput($_POST['status']);

                    // Validate inputs
                    if (empty($title) || empty($description) || empty($location) || empty($campaign_date) || $target_amount <= 0) {
                        throw new Exception('All required fields must be filled, and the target amount must be positive.');
                    }
                    if ($raised_amount < 0) {
                        throw new Exception('Raised amount cannot be negative.');
                    }
                    if ($raised_amount > $target_amount) {
                        throw new Exception('Raised amount cannot exceed the target amount.');
                    }

                    // Validate campaign_date
                    if (!DateTime::createFromFormat('Y-m-d', $campaign_date)) {
                        throw new Exception('Invalid campaign date. Please select a valid date.');
                    }

                    // Sanitize description (allow basic HTML tags)
                    $description = strip_tags($description, '<p><strong><em><br><ul><li><a>');

                    // Handle image upload
                    $image = '';
                    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../img/campaigns/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                            chmod($upload_dir, 0755);
                        }

                        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                        if (in_array($file_ext, $allowed_ext)) {
                            if ($_FILES['image']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                                $image = uniqid('campaign_') . '.' . $file_ext;
                                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image)) {
                                    throw new Exception('Error uploading image.');
                                }
                                chmod($upload_dir . $image, 0644);
                            } else {
                                throw new Exception('Image size must not exceed 5MB.');
                            }
                        } else {
                            throw new Exception('Invalid file type. Only JPG, JPEG, PNG, or GIF files are allowed.');
                        }
                    }

                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO campaigns (title, description, image, location, campaign_date, target_amount, raised_amount, status, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        $stmt->execute([$title, $description, $image, $location, $campaign_date, $target_amount, $raised_amount, $status]);
                        $message = 'Campaign added successfully!';
                    } else {
                        // Keep existing image if no new image uploaded
                        if (empty($image)) {
                            $stmt = $db->prepare("SELECT image FROM campaigns WHERE id = ?");
                            $stmt->execute([$id]);
                            $existing = $stmt->fetch();
                            $image = $existing['image'] ?? '';
                        }

                        $stmt = $db->prepare("
                            UPDATE campaigns SET title = ?, description = ?, image = ?, location = ?, campaign_date = ?, target_amount = ?, raised_amount = ?, status = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $description, $image, $location, $campaign_date, $target_amount, $raised_amount, $status, $id]);
                        $message = 'Campaign updated successfully!';
                    }

                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'Error: ' . htmlspecialchars($e->getMessage());
                }
                break;

            case 'delete':
                try {
                    $delete_id = $_POST['id'];

                    // Get image filename before deleting
                    $stmt = $db->prepare("SELECT image FROM campaigns WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $campaign = $stmt->fetch();

                    // Delete campaign
                    $stmt = $db->prepare("DELETE FROM campaigns WHERE id = ?");
                    $stmt->execute([$delete_id]);

                    // Delete image if it exists
                    if (!empty($campaign['image']) && file_exists('../img/campaigns/' . $campaign['image'])) {
                        unlink('../img/campaigns/' . $campaign['image']);
                    }

                    $message = 'Campaign deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting: ' . htmlspecialchars($e->getMessage());
                }
                break;
        }
    }
}

// Get campaign data for editing
$campaign_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE id = ?");
    $stmt->execute([$id]);
    $campaign_data = $stmt->fetch();

    if (!$campaign_data) {
        $error = 'Campaign not found!';
        $action = 'list';
    }
}

// Get campaign list
$campaign_list = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM campaigns ORDER BY created_at DESC");
    $stmt->execute();
    $campaign_list = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-hand-holding-heart me-3"></i> Crowdfunding Campaign Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Campaign
                </a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Campaign List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Campaign List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th>Target</th>
                                <th>Raised Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaign_list as $campaign): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($campaign['image'])): ?>
                                    <img src="<?php echo SITE_URL . '/img/campaigns/' . htmlspecialchars($campaign['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($campaign['title']); ?>" 
                                         class="img-thumbnail" width="60" height="60">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($campaign['title']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr(strip_tags($campaign['description']), 0, 100)) . '...'; ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($campaign['location']); ?></td>
                                <td><?php echo htmlspecialchars(formatDate($campaign['campaign_date'])); ?></td>
                                <td>₹<?php echo number_format($campaign['target_amount']); ?></td>
                                <td>₹<?php echo number_format($campaign['raised_amount']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $campaign['status'] === 'active' ? 'success' : ($campaign['status'] === 'inactive' ? 'secondary' : 'info'); ?>">
                                        <?php echo $campaign['status'] === 'active' ? 'Active' : ($campaign['status'] === 'inactive' ? 'Inactive' : 'Completed'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCampaign(<?php echo $campaign['id']; ?>)">
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
                <h5><i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> 
                    <?php echo $action === 'add' ? 'Add New Campaign' : 'Edit Campaign'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required
                               value="<?php echo htmlspecialchars($campaign_data['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="10" required><?php echo htmlspecialchars($campaign_data['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required
                                       value="<?php echo htmlspecialchars($campaign_data['location'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaign_date" class="form-label">Campaign Date *</label>
                                <input type="date" class="form-control" id="campaign_date" name="campaign_date" required
                                       value="<?php echo htmlspecialchars($campaign_data['campaign_date'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_amount" class="form-label">Target Amount (₹) *</label>
                                <input type="number" class="form-control" id="target_amount" name="target_amount" required step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($campaign_data['target_amount'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="raised_amount" class="form-label">Raised Amount (₹)</label>
                                <input type="number" class="form-control" id="raised_amount" name="raised_amount" step="0.01" min="0"
                                       value="<?php echo htmlspecialchars($campaign_data['raised_amount'] ?? '0'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($campaign_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($campaign_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="completed" <?php echo ($campaign_data['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if (!empty($campaign_data['image'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo SITE_URL . '/img/campaigns/' . htmlspecialchars($campaign_data['image']); ?>" 
                                 alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
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
function deleteCampaign(id) {
    if (confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>