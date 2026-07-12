<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$slider = [];

// Define a fallback for SITE_URL
$siteUrl = defined('SITE_URL') ? SITE_URL : '/';

// Database connection
$db = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        // Add or Edit Slider
        if ($formAction === 'add' || $formAction === 'edit') {
            $title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
            $link = isset($_POST['link']) ? sanitizeInput($_POST['link']) : '';
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;
            
            // Validate required fields
            if (empty($title)) {
                $error = "Title is required.";
            } else {
                try {
                    // Handle file upload
                    $image = '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['image'], 'img/sliders');
                        if ($uploadResult['success']) {
                            $image = $uploadResult['filename'];
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                    
                    if ($formAction === 'add') {
                        // Check if image is provided for new slider
                        if (empty($image)) {
                            throw new Exception("Image is required for new slider.");
                        }
                        
                        // Insert new slider
                        $stmt = $db->prepare("INSERT INTO sliders (title, image, link, sort_order, status, created_at) 
                                              VALUES (:title, :image, :link, :sort_order, :status, NOW())");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':link', $link);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        
                        $success = "Slider added successfully.";
                    } else {
                        // Update existing slider
                        $slider_id = isset($_POST['slider_id']) ? (int)$_POST['slider_id'] : 0;
                        
                        // Get current slider data
                        $stmt = $db->prepare("SELECT image FROM sliders WHERE id = :id");
                        $stmt->bindParam(':id', $slider_id);
                        $stmt->execute();
                        $currentSlider = $stmt->fetch();
                        
                        if (!$currentSlider) {
                            throw new Exception("Slider not found.");
                        }
                        
                        // Use current image if no new image uploaded
                        if (empty($image)) {
                            $image = $currentSlider['image'];
                        }
                        
                        // Update slider
                        $stmt = $db->prepare("UPDATE sliders SET title = :title, image = :image, link = :link, 
                                              sort_order = :sort_order, status = :status, updated_at = NOW() WHERE id = :id");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':link', $link);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $slider_id);
                        $stmt->execute();
                        
                        $success = "Slider updated successfully.";
                    }
                    
                    // Redirect to slider list
                    header("Location: sliders.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
        
        // Delete Slider
        if ($formAction === 'delete') {
            $slider_id = isset($_POST['slider_id']) ? (int)$_POST['slider_id'] : 0;
            
            try {
                // Get slider image
                $stmt = $db->prepare("SELECT image FROM sliders WHERE id = :id");
                $stmt->bindParam(':id', $slider_id);
                $stmt->execute();
                $slider = $stmt->fetch();
                
                // Delete slider
                $stmt = $db->prepare("DELETE FROM sliders WHERE id = :id");
                $stmt->bindParam(':id', $slider_id);
                $stmt->execute();
                
                // Delete image file if exists
                if (!empty($slider['image'])) {
                    $imagePath = __DIR__ . '/../img/sliders/' . $slider['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $success = "Slider deleted successfully.";
                
                // Redirect to slider list
                header("Location: sliders.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
        
        // Toggle Status
        if ($formAction === 'toggle') {
            $slider_id = isset($_POST['slider_id']) ? (int)$_POST['slider_id'] : 0;
            
            try {
                // Get current status
                $stmt = $db->prepare("SELECT status FROM sliders WHERE id = :id");
                $stmt->bindParam(':id', $slider_id);
                $stmt->execute();
                $currentSlider = $stmt->fetch();
                
                if (!$currentSlider) {
                    throw new Exception("Slider not found.");
                }
                
                // Toggle status
                $newStatus = $currentSlider['status'] ? 0 : 1;
                
                $stmt = $db->prepare("UPDATE sliders SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $slider_id);
                $stmt->execute();
                
                $success = "Slider status updated successfully.";
                
                // Redirect to slider list
                header("Location: sliders.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Get slider for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM sliders WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $slider = $stmt->fetch();
        
        if (!$slider) {
            $error = "Slider not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Get sliders for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    // Count total records
    $stmt = $db->prepare("SELECT COUNT(*) FROM sliders");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    // Get sliders
    $stmt = $db->prepare("SELECT * FROM sliders ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $sliders = $stmt->fetchAll();
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $sliders = [];
    $totalPages = 0;
}

// Set page title based on action
$pageTitle = ($action === 'add') ? "Add Slider" : (($action === 'edit') ? "Edit Slider" : "Manage Sliders");

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $pageTitle; ?></h1>
                <?php if ($action === 'list'): ?>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="sliders.php?action=add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add New Slider
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" data-aos="fade-up">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" data-aos="fade-up">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Add/Edit Slider Form -->
                <div class="card admin-card" data-aos="fade-up">
                    <div class="card-header admin-card-header">
                        <i class="fas fa-edit"></i> <?php echo ($action === 'add') ? "Add New Slider" : "Edit Slider"; ?>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="slider_id" value="<?php echo $slider['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($slider['title'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="link" class="form-label">Link (Optional)</label>
                                    <input type="url" class="form-control" id="link" name="link" value="<?php echo htmlspecialchars($slider['link'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($slider['sort_order'] ?? 0); ?>" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="1" <?php echo (isset($slider['status']) && $slider['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo (isset($slider['status']) && $slider['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Image <?php echo ($action === 'add') ? '<span class="text-danger">*</span>' : ''; ?></label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" <?php echo ($action === 'add') ? 'required' : ''; ?>>
                                <?php if ($action === 'edit' && !empty($slider['image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo $siteUrl; ?>/img/sliders/<?php echo $slider['image']; ?>" alt="Current Image" class="img-thumbnail" style="max-height: 200px;">
                                        <p class="text-muted mt-1">Current image will be replaced if you upload a new one.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i><?php echo ($action === 'add') ? "Add Slider" : "Update Slider"; ?>
                                </button>
                                <a href="sliders.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Sliders List -->
                <div class="card admin-card" data-aos="fade-up">
                    <div class="card-header admin-card-header">
                        <i class="fas fa-sliders"></i> Sliders
                    </div>
                    <div class="card-body">
                        <?php if (count($sliders) > 0): ?>
                            <div class="row">
                                <?php 
                                $delay = 100;
                                foreach ($sliders as $item): 
                                ?>
                                    <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                                        <div class="card h-100">
                                            <img src="<?php echo $siteUrl; ?>/img/sliders/<?php echo $item['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                                <?php if (!empty($item['link'])): ?>
                                                    <p class="card-text small text-muted"><strong>Link:</strong> <a href="<?php echo htmlspecialchars($item['link']); ?>" target="_blank"><?php echo htmlspecialchars($item['link']); ?></a></p>
                                                <?php endif; ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge <?php echo $item['status'] ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo $item['status'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                    <small class="text-muted">Sort Order: <?php echo $item['sort_order']; ?></small>
                                                </div>
                                                <small class="text-muted"><?php echo date('d M Y', strtotime($item['created_at'])); ?></small>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="d-flex justify-content-between">
                                                    <a href="sliders.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="slider_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-toggle-on"></i> Toggle
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $item['id']; ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                    
                                                    <!-- Delete Confirmation Modal -->
                                                    <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $item['id']; ?>">Confirm Delete</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete the slider: <strong><?php echo htmlspecialchars($item['title']); ?></strong>?
                                                                    <p class="text-danger mt-2">This action cannot be undone.</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <form method="post">
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <input type="hidden" name="slider_id" value="<?php echo $item['id']; ?>">
                                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                $delay += 100;
                                endforeach; 
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info" data-aos="fade-up">
                                <i class="fas fa-info-circle me-2"></i> No sliders found.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.admin-content {
    min-height: calc(100vh - 100px);
    background-color: #f8f9fa;
}

.admin-card {
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    background-color: #fff;
}

.admin-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.admin-card-header {
    background-color: var(--primary-color);
    color: white;
    font-weight: 600;
    border-bottom: none;
    padding: 15px 20px;
}

.form-control, .form-select {
    border-radius: var(--border-radius);
    border: 1px solid #ddd;
    transition: var(--transition);
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 5px rgba(255, 119, 34, 0.3);
}

.form-label {
    font-weight: 600;
    color: var(--dark-color);
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    transition: var(--transition);
}

.btn-primary:hover {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #138496;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #e0a800;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #c82333;
}

.alert-success, .alert-danger, .alert-info {
    border-radius: var(--border-radius);
}

@media (max-width: 767.98px) {
    .admin-content {
        min-height: calc(100vh - 80px);
    }

    .admin-card-header {
        font-size: 1.1rem;
    }
}
</style>