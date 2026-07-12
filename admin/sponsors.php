<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

function uploadFile($file, $targetDir) {
    $result = ['success' => false, 'message' => '', 'filename' => ''];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload error: ' . $file['error'];
        return $result;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        $result['message'] = 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.';
        return $result;
    }

    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        $result['message'] = 'File size exceeds 5MB limit.';
        return $result;
    }

    $targetPath = __DIR__ . '/../' . $targetDir;
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
    }

    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = uniqid('sponsor_', true) . '.' . strtolower($fileExtension);
    $targetFile = $targetPath . '/' . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $result['success'] = true;
        $result['filename'] = $uniqueName;
    } else {
        $result['message'] = 'Failed to move uploaded file.';
    }

    return $result;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$sponsor_item = [];

$db = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
            $designation = isset($_POST['designation']) ? sanitizeInput($_POST['designation']) : '';
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            try {
                // Validate required fields
                if (empty($name)) {
                    throw new Exception("Name is required.");
                }
                if (empty($designation)) {
                    throw new Exception("Designation is required.");
                }
                
                $photo = '';
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['photo'], 'img/sponsors');
                    if ($uploadResult['success']) {
                        $photo = $uploadResult['filename'];
                    } else {
                        throw new Exception($uploadResult['message']);
                    }
                }
                
                if ($formAction === 'add') {
                    if (empty($photo)) {
                        throw new Exception("Photo is required for new sponsor.");
                    }
                    
                    $stmt = $db->prepare("INSERT INTO sponsors (name, designation, photo, sort_order, status, created_at) 
                                          VALUES (:name, :designation, :photo, :sort_order, :status, NOW())");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':designation', $designation);
                    $stmt->bindParam(':photo', $photo);
                    $stmt->bindParam(':sort_order', $sort_order);
                    $stmt->bindParam(':status', $status);
                    $stmt->execute();
                    
                    $success = "Sponsor added successfully.";
                } else {
                    $sponsor_id = isset($_POST['sponsor_id']) ? (int)$_POST['sponsor_id'] : 0;
                    
                    $stmt = $db->prepare("SELECT photo FROM sponsors WHERE id = :id");
                    $stmt->bindParam(':id', $sponsor_id);
                    $stmt->execute();
                    $currentItem = $stmt->fetch();
                    
                    if (!$currentItem) {
                        throw new Exception("Sponsor not found.");
                    }
                    
                    if (empty($photo)) {
                        $photo = $currentItem['photo'];
                    } else {
                        if (!empty($currentItem['photo'])) {
                            $oldPhotoPath = __DIR__ . '/../img/sponsors/' . $currentItem['photo'];
                            if (file_exists($oldPhotoPath)) {
                                unlink($oldPhotoPath);
                            }
                        }
                    }
                    
                    $stmt = $db->prepare("UPDATE sponsors SET name = :name, designation = :designation, 
                                          photo = :photo, sort_order = :sort_order, status = :status WHERE id = :id");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':designation', $designation);
                    $stmt->bindParam(':photo', $photo);
                    $stmt->bindParam(':sort_order', $sort_order);
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':id', $sponsor_id);
                    $stmt->execute();
                    
                    $success = "Sponsor updated successfully.";
                }
                
                header("Location: sponsors.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        if ($formAction === 'delete') {
            $sponsor_id = isset($_POST['sponsor_id']) ? (int)$_POST['sponsor_id'] : 0;
            
            try {
                $stmt = $db->prepare("SELECT photo FROM sponsors WHERE id = :id");
                $stmt->bindParam(':id', $sponsor_id);
                $stmt->execute();
                $sponsor_item = $stmt->fetch();
                
                $stmt = $db->prepare("DELETE FROM sponsors WHERE id = :id");
                $stmt->bindParam(':id', $sponsor_id);
                $stmt->execute();
                
                if (!empty($sponsor_item['photo'])) {
                    $photoPath = __DIR__ . '/../img/sponsors/' . $sponsor_item['photo'];
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }
                
                $success = "Sponsor deleted successfully.";
                header("Location: sponsors.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
        
        if ($formAction === 'toggle') {
            $sponsor_id = isset($_POST['sponsor_id']) ? (int)$_POST['sponsor_id'] : 0;
            
            try {
                $stmt = $db->prepare("SELECT status FROM sponsors WHERE id = :id");
                $stmt->bindParam(':id', $sponsor_id);
                $stmt->execute();
                $currentItem = $stmt->fetch();
                
                if (!$currentItem) {
                    throw new Exception("Sponsor not found.");
                }
                
                $newStatus = $currentItem['status'] === 'active' ? 'inactive' : 'active';
                
                $stmt = $db->prepare("UPDATE sponsors SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $sponsor_id);
                $stmt->execute();
                
                $success = "Sponsor status updated successfully.";
                header("Location: sponsors.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM sponsors WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $sponsor_item = $stmt->fetch();
        
        if (!$sponsor_item) {
            $error = "Sponsor not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM sponsors");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM sponsors ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $sponsor_items = $stmt->fetchAll();
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $sponsor_items = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add Sponsor" : (($action === 'edit') ? "Edit Sponsor" : "Sponsor Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus me-2"></i>Add Sponsor
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit me-2"></i>Edit Sponsor
                <?php else: ?>
                    <i class="fas fa-users me-2"></i>Sponsor Management
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                    <a href="sponsors.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Sponsor
                    </a>
                <?php else: ?>
                    <a href="sponsors.php" class="btn btn-secondary">
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
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-edit"></i> <?php echo ($action === 'add') ? "Add New Sponsor" : "Edit Sponsor"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="sponsor_id" value="<?php echo $sponsor_item['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($sponsor_item['name'] ?? ''); ?>" 
                                       required placeholder="Enter sponsor name">
                            </div>
                            <div class="col-md-6">
                                <label for="designation" class="form-label">
                                    Designation <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="designation" name="designation" 
                                       value="<?php echo htmlspecialchars($sponsor_item['designation'] ?? ''); ?>" 
                                       required placeholder="Enter designation">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="photo" class="form-label">
                                    Photo <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : ''; ?>
                                </label>
                                <input type="file" class="form-control" id="photo" name="photo" 
                                       accept="image/jpeg,image/png,image/gif,image/webp" 
                                       <?php echo $action === 'add' ? 'required' : ''; ?>>
                                <div class="form-text">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</div>
                                <?php if ($action === 'edit' && !empty($sponsor_item['photo'])): ?>
                                    <div class="mt-3">
                                        <label class="form-label">Current Photo:</label>
                                        <div class="current-image-preview">
                                            <img src="<?php echo SITE_URL; ?>/img/sponsors/<?php echo $sponsor_item['photo']; ?>" 
                                                 alt="<?php echo htmlspecialchars($sponsor_item['name']); ?>" 
                                                 class="img-thumbnail d-block rounded-circle" 
                                                 style="width: 150px; height: 150px; object-fit: cover;">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo htmlspecialchars($sponsor_item['sort_order'] ?? 0); ?>" 
                                       min="0" placeholder="0">
                                <div class="form-text">Lower numbers appear first</div>
                                
                                <label for="status" class="form-label mt-3">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($sponsor_item['status']) && $sponsor_item['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($sponsor_item['status']) && $sponsor_item['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php echo ($action === 'add') ? "Add Sponsor" : "Update Sponsor"; ?>
                            </button>
                            <a href="sponsors.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-users"></i> Sponsors</span>
                    <span class="badge bg-secondary"><?php echo $totalRecords; ?> Total</span>
                </div>
                <div class="card-body">
                    <?php if (count($sponsor_items) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Sort Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sponsor_items as $item): ?>
                                        <tr>
                                            <td>
                                                <img src="<?php echo SITE_URL; ?>/img/sponsors/<?php echo $item['photo']; ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="rounded-circle" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            </td>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['designation']); ?></td>
                                            <td><?php echo $item['sort_order']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="sponsors.php?action=edit&id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="sponsor_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal<?php echo $item['id']; ?>" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
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
                            <i class="fas fa-info-circle me-2"></i> No sponsors found.
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="card-footer">
                    <nav aria-label="Pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i>
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
        <?php endif; ?>
    </div>
</div>

<!-- Delete Modals -->
<?php if ($action === 'list' && count($sponsor_items) > 0): ?>
    <?php foreach ($sponsor_items as $item): ?>
        <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this sponsor?</p>
                        <div class="text-center my-3">
                            <img src="<?php echo SITE_URL; ?>/img/sponsors/<?php echo $item['photo']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="rounded-circle" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                            <h5 class="mt-2"><?php echo htmlspecialchars($item['name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($item['designation']); ?></p>
                        </div>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="sponsor_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
