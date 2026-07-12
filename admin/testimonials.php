<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'Testimonial Management';
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
                    $name = sanitizeInput($_POST['name']);
                    $designation = sanitizeInput($_POST['designation']);
                    $message_text = sanitizeInput($_POST['message']); // Changed to match column name
                    $rating = (int)$_POST['rating'];
                    $status = sanitizeInput($_POST['status']);
                    
                    // Handle image upload
                    $photo = '';
                    if (!empty($_FILES['photo']['name'])) {
                        $upload_dir = '../img/testimonials/'; // Updated to specific directory
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_ext, $allowed_ext)) {
                            $photo = uniqid('testimonial_') . '.' . $file_ext;
                            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo)) {
                                throw new Exception('Failed to upload file.');
                            }
                        } else {
                            throw new Exception('Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.');
                        }
                    }
                    
                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO testimonials (name, designation, message, rating, image, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$name, $designation, $message_text, $rating, $photo, $status]);
                        $message = 'Testimonial added successfully!';
                    } else {
                        // Keep existing photo if no new photo uploaded
                        if (empty($photo)) {
                            $stmt = $db->prepare("SELECT image FROM testimonials WHERE id = ?");
                            $stmt->execute([$id]);
                            $existing = $stmt->fetch();
                            $photo = $existing['image'] ?? '';
                        }
                        
                        $stmt = $db->prepare("
                            UPDATE testimonials SET name = ?, designation = ?, message = ?, rating = ?, image = ?, status = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$name, $designation, $message_text, $rating, $photo, $status, $id]);
                        $message = 'Testimonial updated successfully!';
                    }
                    
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'Error: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $delete_id = $_POST['id'];
                    
                    // Get photo filename before deleting
                    $stmt = $db->prepare("SELECT image FROM testimonials WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $testimonial = $stmt->fetch();
                    
                    // Delete testimonial
                    $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete photo if it exists
                    if (!empty($testimonial['image']) && file_exists('../img/testimonials/' . $testimonial['image'])) {
                        unlink('../img/testimonials/' . $testimonial['image']);
                    }
                    
                    $message = 'Testimonial deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get testimonial data for editing
$testimonial_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $testimonial_data = $stmt->fetch();
    
    if (!$testimonial_data) {
        $error = 'Testimonial not found!';
        $action = 'list';
    }
}

// Get testimonials list
$testimonials_list = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM testimonials ORDER BY created_at DESC");
    $stmt->execute();
    $testimonials_list = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-star me-3"></i>Testimonial Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary" style="background: var(--gradient-primary); border: none;">
                    <i class="fas fa-plus"></i> Add New Testimonial
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
        <!-- Testimonials List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Testimonial List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Testimonial</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testimonials_list as $testimonial): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($testimonial['image'])): ?>
                                    <img src="<?php echo SITE_URL . '/img/testimonials/' . htmlspecialchars($testimonial['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($testimonial['name']); ?>" 
                                         class="img-thumbnail" width="60" height="60">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($testimonial['name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($testimonial['designation'] ?? ''); ?></td>
                                <td>
                                    <?php 
                                    $message_text = $testimonial['message'] ?? '';
                                    echo htmlspecialchars($message_text ? substr($message_text, 0, 100) . '...' : 'No message available'); 
                                    ?>
                                </td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= ($testimonial['rating'] ?? 0) ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $testimonial['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $testimonial['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $testimonial['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTestimonial(<?php echo $testimonial['id']; ?>)">
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
                    <?php echo $action === 'add' ? 'Add New Testimonial' : 'Edit Testimonial'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo htmlspecialchars($testimonial_data['name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="designation" class="form-label">Designation *</label>
                                <input type="text" class="form-control" id="designation" name="designation" required
                                       value="<?php echo htmlspecialchars($testimonial_data['designation'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Testimonial *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($testimonial_data['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating *</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="">Select Rating</option>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($testimonial_data['rating'] ?? '') == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Star
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($testimonial_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($testimonial_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                <?php if (!empty($testimonial_data['image'])): ?>
                                <div class="mt-2">
                                    <img src="<?php echo SITE_URL . '/img/testimonials/' . htmlspecialchars($testimonial_data['image']); ?>" 
                                         alt="Current Photo" class="img-thumbnail" style="max-width: 100px;">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary" style="background: var(--gradient-primary); border: none;">
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
<?php include 'includes/footer.php'; ?>

<script>
function deleteTestimonial(id) {
    if (confirm('Are you sure you want to delete this testimonial? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>