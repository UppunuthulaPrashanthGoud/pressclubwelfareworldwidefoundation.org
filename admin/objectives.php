<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

// $pageTitle = 'Objective Management';
$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                try {
                    // Don't use htmlspecialchars when saving to database
                    $title = trim($_POST['title']);
                    $description = trim($_POST['description']);
                    $status = $_POST['status'];
                    $sort_order = (int)$_POST['sort_order'];
                    
                    // Handle image upload
                    $image = $objective_data['image'] ?? null; // Keep existing image if not changed
                    
                    if (!empty($_FILES['image_file']['name'])) {
                        $uploadDir = 'img/objectives/';
                        $uploadResult = uploadFile($_FILES['image_file'], $uploadDir);
                        
                        if ($uploadResult['success']) {
                            $image = $uploadResult['filename'];
                            
                            // Delete old image if exists and is being replaced
                            if (!empty($objective_data['image']) && file_exists('../img/objectives/' . $objective_data['image'])) {
                                unlink('../img/objectives/' . $objective_data['image']);
                            }
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                    
                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO objectives (title, description, image, status, sort_order, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$title, $description, $image, $status, $sort_order]);
                        $message = 'Objective added successfully!';
                    } else {
                        $stmt = $db->prepare("
                            UPDATE objectives SET title = ?, description = ?, image = ?, status = ?, sort_order = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $description, $image, $status, $sort_order, $id]);
                        $message = 'Objective updated successfully!';
                    }
                    
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'Error: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $delete_id = $_POST['id'];
                    
                    // Get image path before deleting
                    $stmt = $db->prepare("SELECT image FROM objectives WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $objective = $stmt->fetch();
                    
                    // Delete record
                    $stmt = $db->prepare("DELETE FROM objectives WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete associated image
                    if (!empty($objective['image']) && file_exists('../img/objectives/' . $objective['image'])) {
                        unlink('../img/objectives/' . $objective['image']);
                    }
                    
                    $message = 'Objective deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get objective data for editing
$objective_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM objectives WHERE id = ?");
    $stmt->execute([$id]);
    $objective_data = $stmt->fetch();
    
    if (!$objective_data) {
        $error = 'Objective not found!';
        $action = 'list';
    }
}

// Get objectives list
$objectives_list = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM objectives ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute();
    $objectives_list = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-bullseye me-3"></i>Objective Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Objective
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
        <!-- Objectives List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Objective List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Sort Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($objectives_list as $objective): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($objective['image'])): ?>
                                        <img src="<?php echo htmlspecialchars(SITE_URL . '/img/objectives/' . $objective['image']); ?>" alt="Objective Image" style="max-width: 50px; height: auto;">
                                    <?php else: ?>
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($objective['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(substr($objective['description'], 0, 100), ENT_QUOTES, 'UTF-8') . '...'; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $objective['sort_order']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $objective['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $objective['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $objective['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteObjective(<?php echo $objective['id']; ?>)">
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
                    <?php echo $action === 'add' ? 'Add New Objective' : 'Edit Objective'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       value="<?php echo htmlspecialchars($objective_data['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="image_file" class="form-label">Image (JPG, PNG, GIF) *</label>
                                <input type="file" class="form-control" id="image_file" name="image_file" accept="image/jpeg,image/png,image/gif">
                                <?php if (!empty($objective_data['image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL . '/img/objectives/' . htmlspecialchars($objective_data['image']); ?>" alt="Current Image" style="max-width: 100px;">
                                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($objective_data['image']); ?>">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($objective_data['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order *</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" required min="1"
                                       value="<?php echo htmlspecialchars($objective_data['sort_order'] ?? '1'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($objective_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($objective_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
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
</form>

<?php include 'includes/sidebar.php'; ?>

<script>
function deleteObjective(id) {
    if (confirm('Are you sure you want to delete this objective? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>