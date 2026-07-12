<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Define uploadFile function
function uploadFile($file, $targetDir) {
    $result = ['success' => false, 'message' => '', 'filename' => ''];

    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload error: ' . $file['error'];
        return $result;
    }

    // Validate file type (allow only images)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        $result['message'] = 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.';
        return $result;
    }

    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $maxSize) {
        $result['message'] = 'File size exceeds 5MB limit.';
        return $result;
    }

    // Create target directory if it doesn't exist
    $targetPath = __DIR__ . '/../' . $targetDir;
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
    }

    // Generate a unique filename to avoid conflicts
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = uniqid('slider_', true) . '.' . strtolower($fileExtension);
    $targetFile = $targetPath . '/' . $uniqueName;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $result['success'] = true;
        $result['filename'] = $uniqueName;
    } else {
        $result['message'] = 'Failed to move uploaded file.';
    }

    return $result;
}

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$slider_item = [];

$db = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        // Add or Edit Slider Item
        if ($formAction === 'add' || $formAction === 'edit') {
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            try {
                // Handle file upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['image'], 'img/sliders');
                    if ($uploadResult['success']) {
                        $image = $uploadResult['filename'];
                    } else {
                        $error = $uploadResult['message'];
                        throw new Exception($uploadResult['message']);
                    }
                }
                
                if ($formAction === 'add') {
                    if (empty($image)) {
                        throw new Exception("Image is required for new slider item.");
                    }
                    
                    $stmt = $db->prepare("INSERT INTO sliders (image, sort_order, status, created_at) 
                                          VALUES (:image, :sort_order, :status, NOW())");
                    $stmt->bindParam(':image', $image);
                    $stmt->bindParam(':sort_order', $sort_order);
                    $stmt->bindParam(':status', $status);
                    $stmt->execute();
                    
                    $success = "Slider item added successfully.";
                } else {
                    $slider_id = isset($_POST['slider_id']) ? (int)$_POST['slider_id'] : 0;
                    
                    // Get current slider data
                    $stmt = $db->prepare("SELECT image FROM sliders WHERE id = :id");
                    $stmt->bindParam(':id', $slider_id);
                    $stmt->execute();
                    $currentItem = $stmt->fetch();
                    
                    if (!$currentItem) {
                        throw new Exception("Slider item not found.");
                    }
                    
                    // Use current image if no new image uploaded
                    if (empty($image)) {
                        $image = $currentItem['image'];
                    } else {
                        // Delete old image if a new one is uploaded
                        if (!empty($currentItem['image'])) {
                            $oldImagePath = __DIR__ . '/../img/sliders/' . $currentItem['image'];
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                    }
                    
                    $stmt = $db->prepare("UPDATE sliders SET image = :image, sort_order = :sort_order, 
                                          status = :status WHERE id = :id");
                    $stmt->bindParam(':image', $image);
                    $stmt->bindParam(':sort_order', $sort_order);
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':id', $slider_id);
                    $stmt->execute();
                    
                    $success = "Slider item updated successfully.";
                }
                
                header("Location: sliders.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Delete Slider Item
        if ($formAction === 'delete') {
            $slider_id = isset($_POST['slider_id']) ? (int)$_POST['slider_id'] : 0;
            
            try {
                // Get slider image
                $stmt = $db->prepare("SELECT image FROM sliders WHERE id = :id");
                $stmt->bindParam(':id', $slider_id);
                $stmt->execute();
                $slider_item = $stmt->fetch();
                
                // Delete slider item
                $stmt = $db->prepare("DELETE FROM sliders WHERE id = :id");
                $stmt->bindParam(':id', $slider_id);
                $stmt->execute();
                
                // Delete image file if exists
                if (!empty($slider_item['image'])) {
                    $imagePath = __DIR__ . '/../img/sliders/' . $slider_item['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $success = "Slider item deleted successfully.";
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
                $stmt = $db->prepare("SELECT status FROM sliders WHERE id = :id");
                $stmt->bindParam(':id', $slider_id);
                $stmt->execute();
                $currentItem = $stmt->fetch();
                
                if (!$currentItem) {
                    throw new Exception("Slider item not found.");
                }
                
                $newStatus = $currentItem['status'] === 'active' ? 'inactive' : 'active';
                
                $stmt = $db->prepare("UPDATE sliders SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $slider_id);
                $stmt->execute();
                
                $success = "Slider item status updated successfully.";
                header("Location: sliders.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Get slider item for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM sliders WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $slider_item = $stmt->fetch();
        
        if (!$slider_item) {
            $error = "Slider item not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Get slider items for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM sliders");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM sliders ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $slider_items = $stmt->fetchAll();
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $slider_items = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add Slider Item" : (($action === 'edit') ? "Edit Slider Item" : "Slider Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus me-2"></i>Add Slider Item
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit me-2"></i>Edit Slider Item
                <?php else: ?>
                    <i class="fas fa-sliders-h me-2"></i>Slider Management
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                    <a href="sliders.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Item
                    </a>
                <?php else: ?>
                    <a href="sliders.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-edit"></i> <?php echo ($action === 'add') ? "Add New Slider Item" : "Edit Slider Item"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="slider_id" value="<?php echo $slider_item['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="image" class="form-label">
                                    Image <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : ''; ?>
                                </label>
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/jpeg,image/png,image/gif,image/webp" 
                                       <?php echo $action === 'add' ? 'required' : ''; ?>>
                                <div class="form-text">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</div>
                                <?php if ($action === 'edit' && !empty($slider_item['image'])): ?>
                                    <div class="mt-3">
                                        <label class="form-label">Current Image:</label>
                                        <div class="current-image-preview">
                                            <img src="<?php echo SITE_URL; ?>/img/sliders/<?php echo $slider_item['image']; ?>" 
                                                 alt="Slider Image" class="img-thumbnail d-block" 
                                                 style="max-height: 200px; max-width: 300px;">
                                            <small class="text-muted mt-2 d-block">
                                                Current image will be replaced if you upload a new one.
                                            </small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo htmlspecialchars($slider_item['sort_order'] ?? 0); ?>" 
                                       min="0" placeholder="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo (isset($slider_item['status']) && $slider_item['status'] === 'active') ? 'selected' : ''; ?>>
                                    Active
                                </option>
                                <option value="inactive" <?php echo (isset($slider_item['status']) && $slider_item['status'] === 'inactive') ? 'selected' : ''; ?>>
                                    Inactive
                                </option>
                            </select>
                            <div class="form-text">Only active sliders will be displayed on the website</div>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php echo ($action === 'add') ? "Add Slider Item" : "Update Slider Item"; ?>
                            </button>
                            <a href="sliders.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Slider List -->
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-sliders-h"></i> Slider Items</span>
                    <span class="badge bg-secondary"><?php echo $totalRecords; ?> Total</span>
                </div>
                <div class="card-body">
                    <?php if (count($slider_items) > 0): ?>
                        <div class="row">
                            <?php foreach ($slider_items as $item): ?>
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <div class="card h-100">
                                        <img src="<?php echo SITE_URL; ?>/img/sliders/<?php echo $item['image']; ?>" 
                                             class="card-img-top" alt="Slider Image" style="height: 200px; object-fit: cover;">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                                <small class="text-muted">Sort Order: <?php echo $item['sort_order']; ?></small>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex justify-content-between">
                                                <a href="sliders.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="slider_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal<?php echo $item['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No slider items found.
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="card-footer">
                    <nav aria-label="Slider pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php 
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            if ($startPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Modals -->
<?php if ($action === 'list' && count($slider_items) > 0): ?>
    <?php foreach ($slider_items as $item): ?>
        <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this slider image?
                        <p class="text-danger mt-2">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="slider_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>