<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin and coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    redirectTo(SITE_URL . '/admin/index.php');
}

$pageTitle = 'Recent Activities Management';
$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

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
                    $title = sanitizeInput($_POST['title']);
                    $description = $_POST['description']; // Rich text content
                    $activity_date = sanitizeInput($_POST['activity_date']);
                    $status = sanitizeInput($_POST['status']);
                    
                    // Validate activity_date format (YYYY-MM-DD)
                    if (!DateTime::createFromFormat('Y-m-d', $activity_date)) {
                        throw new Exception('Invalid date format. Please use YYYY-MM-DD format.');
                    }

                    // Handle image upload
                    $image = '';
                    if (!empty($_FILES['image']['name'])) {
                        $upload_dir = '../img/activities/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (!in_array($file_ext, $allowed_ext)) {
                            throw new Exception('File type not allowed. Only JPG, JPEG, PNG, or GIF files can be uploaded.');
                        }
                        
                        $image = uniqid('activity_') . '.' . $file_ext;
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image)) {
                            throw new Exception('Error uploading image.');
                        }
                    }
                    
                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO recent_activities (title, description, image, activity_date, status, created_at)
                            VALUES (?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$title, $description, $image, $activity_date, $status]);
                        $message = 'Activity successfully added!';
                    } else {
                        // Keep existing image if no new image uploaded
                        if (empty($image)) {
                            $stmt = $db->prepare("SELECT image FROM recent_activities WHERE id = ?");
                            $stmt->execute([$id]);
                            $existing = $stmt->fetch();
                            $image = $existing['image'] ?? '';
                        }
                        
                        $stmt = $db->prepare("
                            UPDATE recent_activities SET title = ?, description = ?, image = ?, activity_date = ?, status = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $description, $image, $activity_date, $status, $id]);
                        $message = 'Activity updated successfully!';
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
                    $stmt = $db->prepare("SELECT image FROM recent_activities WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $activity = $stmt->fetch();
                    
                    // Delete activity
                    $stmt = $db->prepare("DELETE FROM recent_activities WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete image if it exists
                    if (!empty($activity['image']) && file_exists('../img/activities/' . $activity['image'])) {
                        unlink('../img/activities/' . $activity['image']);
                    }
                    
                    $message = 'Activity successfully deleted!';
                } catch (Exception $e) {
                    $error = 'Deletion error: ' . htmlspecialchars($e->getMessage());
                }
                break;
        }
    }
}

// Get activity data for editing
$activity_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM recent_activities WHERE id = ?");
    $stmt->execute([$id]);
    $activity_data = $stmt->fetch();
    
    if (!$activity_data) {
        $error = 'Activity not found!';
        $action = 'list';
    }
}

// Get activities list
$activities_list = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM recent_activities ORDER BY activity_date DESC");
    $stmt->execute();
    $activities_list = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-calendar-check me-3"></i> Recent Activities Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add New Activity
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
        <!-- Activities List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i> Activity List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities_list as $activity): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($activity['image'])): ?>
                                    <img src="<?php echo SITE_URL . '/img/activities/' . $activity['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($activity['title']); ?>" 
                                         class="img-thumbnail" width="60" height="60">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr(strip_tags($activity['description']), 0, 100)) . '...'; ?>
                                    </small>
                                </td>
                                <td><?php echo date('d M Y', strtotime($activity['activity_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $activity['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $activity['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($activity['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $activity['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteActivity(<?php echo $activity['id']; ?>)" title="Delete">
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
                    <?php echo $action === 'add' ? 'Add New Activity' : 'Edit Activity'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required
                               value="<?php echo htmlspecialchars($activity_data['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="10" required><?php echo htmlspecialchars($activity_data['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="activity_date" class="form-label">Activity Date *</label>
                                <input type="date" class="form-control" id="activity_date" name="activity_date" required
                                       value="<?php echo htmlspecialchars($activity_data['activity_date'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($activity_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($activity_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if (!empty($activity_data['image'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo SITE_URL . '/img/activities/' . $activity_data['image']; ?>" 
                                 alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                        <?php endif; ?>
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
function deleteActivity(id) {
    if (confirm('Do you really want to delete this activity? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<style>
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
</style>

<?php include 'includes/footer.php'; ?>