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
    $uniqueName = uniqid('partner_', true) . '.' . strtolower($fileExtension);
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
$partner_item = [];

$db = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            // NEW: Sanitize and fetch the website field
            $website = isset($_POST['website']) ? sanitizeInput($_POST['website']) : null;
            
            try {
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['image'], 'img/partners');
                    if ($uploadResult['success']) {
                        $image = $uploadResult['filename'];
                    } else {
                        throw new Exception($uploadResult['message']);
                    }
                }
                
                if ($formAction === 'add') {
                    if (empty($image)) {
                        throw new Exception("Image is required for new partner.");
                    }
                    
                    // NEW: Include website column in INSERT statement
                    $stmt = $db->prepare("INSERT INTO partners (image, website, sort_order, status, created_at) 
                                          VALUES (:image, :website, :sort_order, :status, NOW())");
                    $stmt->bindParam(':image', $image);
                    $stmt->bindParam(':website', $website);
                    $stmt->bindParam(':sort_order', $sort_order);
                    $stmt->bindParam(':status', $status);
                    $stmt->execute();
                    
                    $success = "Partner added successfully.";
                } else {
                    $partner_id = isset($_POST['partner_id']) ? (int)$_POST['partner_id'] : 0;
                    
                    $stmt = $db->prepare("SELECT image FROM partners WHERE id = :id");
                    $stmt->bindParam(':id', $partner_id);
                    $stmt->execute();
                    $currentItem = $stmt->fetch();
                    
                    if (!$currentItem) {
                        throw new Exception("Partner not found.");
                    }
                    
                    if (empty($image)) {
                        $image = $currentItem['image'];
                    } else {
                        if (!empty($currentItem['image'])) {
                            $oldImagePath = __DIR__ . '/../img/partners/' . $currentItem['image'];
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                    }
                    
                    // NEW: Include website column in UPDATE statement
                    $stmt = $db->prepare("UPDATE partners SET image = :image, website = :website, sort_order = :sort_order, 
                                          status = :status WHERE id = :id");
                    $stmt->bindParam(':image', $image);
                    $stmt->bindParam(':website', $website);
                    $stmt->bindParam(':sort_order', $sort_order);
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':id', $partner_id);
                    $stmt->execute();
                    
                    $success = "Partner updated successfully.";
                }
                
                header("Location: partners.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        if ($formAction === 'delete') {
            $partner_id = isset($_POST['partner_id']) ? (int)$_POST['partner_id'] : 0;
            
            try {
                $stmt = $db->prepare("SELECT image FROM partners WHERE id = :id");
                $stmt->bindParam(':id', $partner_id);
                $stmt->execute();
                $partner_item = $stmt->fetch();
                
                $stmt = $db->prepare("DELETE FROM partners WHERE id = :id");
                $stmt->bindParam(':id', $partner_id);
                $stmt->execute();
                
                if (!empty($partner_item['image'])) {
                    $imagePath = __DIR__ . '/../img/partners/' . $partner_item['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $success = "Partner deleted successfully.";
                header("Location: partners.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
        
        if ($formAction === 'toggle') {
            $partner_id = isset($_POST['partner_id']) ? (int)$_POST['partner_id'] : 0;
            
            try {
                $stmt = $db->prepare("SELECT status FROM partners WHERE id = :id");
                $stmt->bindParam(':id', $partner_id);
                $stmt->execute();
                $currentItem = $stmt->fetch();
                
                if (!$currentItem) {
                    throw new Exception("Partner not found.");
                }
                
                $newStatus = $currentItem['status'] === 'active' ? 'inactive' : 'active';
                
                $stmt = $db->prepare("UPDATE partners SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $partner_id);
                $stmt->execute();
                
                $success = "Partner status updated successfully.";
                header("Location: partners.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM partners WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $partner_item = $stmt->fetch();
        
        if (!$partner_item) {
            $error = "Partner not found.";
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
    $stmt = $db->prepare("SELECT COUNT(*) FROM partners");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM partners ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $partner_items = $stmt->fetchAll();
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $partner_items = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add Partner" : (($action === 'edit') ? "Edit Partner" : "Partner Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus me-2"></i>Add Partner
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit me-2"></i>Edit Partner
                <?php else: ?>
                    <i class="fas fa-handshake me-2"></i>Partner Management
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                    <a href="partners.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Partner
                    </a>
                <?php else: ?>
                    <a href="partners.php" class="btn btn-secondary">
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
                    <i class="fas fa-edit"></i> <?php echo ($action === 'add') ? "Add New Partner" : "Edit Partner"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="partner_id" value="<?php echo $partner_item['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="image" class="form-label">
                                    Logo/Image <?php echo $action === 'add' ? '<span class="text-danger">*</span>' : ''; ?>
                                </label>
                                <input type="file" class="form-control" id="image" name="image" 
                                       accept="image/jpeg,image/png,image/gif,image/webp" 
                                       <?php echo $action === 'add' ? 'required' : ''; ?>>
                                <div class="form-text">Supported formats: JPG, PNG, GIF, WebP. Max size: 5MB</div>
                                <?php if ($action === 'edit' && !empty($partner_item['image'])): ?>
                                    <div class="mt-3">
                                        <label class="form-label">Current Image:</label>
                                        <div class="current-image-preview">
                                            <img src="<?php echo SITE_URL; ?>/img/partners/<?php echo $partner_item['image']; ?>" 
                                                 alt="Partner Logo" class="img-thumbnail d-block" 
                                                 style="max-height: 200px; max-width: 300px;">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo htmlspecialchars($partner_item['sort_order'] ?? 0); ?>" 
                                       min="0" placeholder="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                        </div>

                        <!-- NEW: Website Link Input Field -->
                        <div class="mb-3">
                            <label for="website" class="form-label">Partner Website URL</label>
                            <input type="url" class="form-control" id="website" name="website" 
                                   value="<?php echo htmlspecialchars($partner_item['website'] ?? ''); ?>" 
                                   placeholder="https://example.com" pattern="https?://.+" title="Must start with http:// or https://">
                            <div class="form-text">Optional. If provided, the logo will link to this URL.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo (isset($partner_item['status']) && $partner_item['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo (isset($partner_item['status']) && $partner_item['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php echo ($action === 'add') ? "Add Partner" : "Update Partner"; ?>
                            </button>
                            <a href="partners.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-handshake"></i> Partners</span>
                    <span class="badge bg-secondary"><?php echo $totalRecords; ?> Total</span>
                </div>
                <div class="card-body">
                    <?php if (count($partner_items) > 0): ?>
                        <div class="row">
                            <?php foreach ($partner_items as $item): ?>
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <div class="card h-100">
                                        <img src="<?php echo SITE_URL; ?>/img/partners/<?php echo $item['image']; ?>" 
                                             class="card-img-top" alt="Partner Logo" style="height: 200px; object-fit: contain; padding: 15px; background: #f8f9fa;">
                                        <div class="card-body">
                                            <!-- NEW: Display Website URL if available -->
                                            <?php if (!empty($item['website'])): ?>
                                            <p class="card-text text-truncate small mb-2">
                                                <i class="fas fa-globe me-1"></i> 
                                                <a href="<?php echo htmlspecialchars($item['website']); ?>" target="_blank" class="text-primary">
                                                    <?php echo htmlspecialchars($item['website']); ?>
                                                </a>
                                            </p>
                                            <?php endif; ?>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                                <small class="text-muted">Sort: <?php echo $item['sort_order']; ?></small>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex justify-content-between">
                                                <a href="partners.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="partner_id" value="<?php echo $item['id']; ?>">
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
                            <i class="fas fa-info-circle me-2"></i> No partners found.
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
<?php if ($action === 'list' && count($partner_items) > 0): ?>
    <?php foreach ($partner_items as $item): ?>
        <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this partner?
                        <p class="text-danger mt-2">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="partner_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>