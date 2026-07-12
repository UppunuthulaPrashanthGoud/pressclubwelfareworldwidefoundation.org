<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// File upload function
function uploadFile($file, $targetDir) {
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $result = ['success' => false, 'message' => '', 'filename' => ''];

    $targetPath = __DIR__ . '/../' . $targetDir;
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload error.';
        return $result;
    }

    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $result['message'] = 'Invalid image file.';
        return $result;
    }

    $fileName = basename($file['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $fileExt;
    $targetFile = $targetPath . '/' . $newFileName;

    if (!in_array($fileExt, $allowedTypes)) {
        $result['message'] = 'File type not allowed. Only ' . implode(', ', $allowedTypes) . ' are permitted.';
        return $result;
    }

    if ($file['size'] > $maxFileSize) {
        $result['message'] = 'File size must not exceed 5MB.';
        return $result;
    }

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $result['success'] = true;
        $result['filename'] = $newFileName;
        logError("File uploaded successfully: $newFileName to $targetDir");
    } else {
        $result['message'] = 'Failed to upload file.';
    }

    return $result;
}

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';

try {
    $db = getDbConnection();
    $stmt = $db->query("SHOW TABLES LIKE 'ourworks'");
    if ($stmt->rowCount() == 0) {
        logError("Table 'ourworks' does not exist in database.");
        $error = "Table 'ourworks' not found in database.";
        $works = [];
        $totalPages = 0;
    }
} catch (PDOException $e) {
    logError("Database connection error: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $works = [];
    $totalPages = 0;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
        logError($error);
    } elseif (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
            $content = isset($_POST['content']) ? $_POST['content'] : '';
            
            if (empty($name) || empty($content)) {
                $error = "Name and content are required.";
            } else {
                try {
                    $image = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['image'], 'img/ourworks');
                        if ($uploadResult['success']) {
                            $image = $uploadResult['filename'];
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                    
                    if ($formAction === 'add') {
                        $stmt = $db->prepare("INSERT INTO ourworks (name, content, image, created_at) 
                                              VALUES (:name, :content, :image, NOW())");
                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':content', $content);
                        $stmt->bindParam(':image', $image);
                        $stmt->execute();
                        $success = "Work added successfully.";
                    } else {
                        $work_id = isset($_POST['work_id']) ? (int)$_POST['work_id'] : 0;
                        $stmt = $db->prepare("SELECT image FROM ourworks WHERE id = :id");
                        $stmt->bindParam(':id', $work_id);
                        $stmt->execute();
                        $currentData = $stmt->fetch();
                        if (!$currentData) {
                            throw new Exception("Work not found.");
                        }
                        if (empty($image)) {
                            $image = $currentData['image'];
                        } else {
                            if (!empty($currentData['image'])) {
                                $oldImagePath = __DIR__ . '/../img/ourworks/' . $currentData['image'];
                                if (file_exists($oldImagePath)) {
                                    unlink($oldImagePath);
                                    logError("Old image deleted: " . $currentData['image']);
                                }
                            }
                        }
                        $stmt = $db->prepare("UPDATE ourworks SET name = :name, content = :content, 
                                              image = :image, updated_at = NOW() WHERE id = :id");
                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':content', $content);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':id', $work_id);
                        $stmt->execute();
                        $success = "Work updated successfully.";
                    }
                    header("Location: ourworks.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                    logError($error);
                }
            }
        }
        if ($formAction === 'delete') {
            $work_id = isset($_POST['work_id']) ? (int)$_POST['work_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT image FROM ourworks WHERE id = :id");
                $stmt->bindParam(':id', $work_id);
                $stmt->execute();
                $data = $stmt->fetch();
                $stmt = $db->prepare("DELETE FROM ourworks WHERE id = :id");
                $stmt->bindParam(':id', $work_id);
                $stmt->execute();
                if (!empty($data['image'])) {
                    $imagePath = __DIR__ . '/../img/ourworks/' . $data['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                        logError("Image deleted on work deletion: " . $data['image']);
                    }
                }
                $success = "Work deleted successfully.";
                header("Location: ourworks.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
                logError($error);
            }
        }
    }
}

// Get data for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM ourworks WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $work = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$work) {
            $error = "Work not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        logError("Edit query error: " . $e->getMessage());
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Get data for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM ourworks");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT * FROM ourworks ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $works = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError("Database query error for ourworks: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $works = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add New Work" :
             (($action === 'edit') ? "Edit Work" : "Manage Our Works");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<style>
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table th, .table td {
    vertical-align: middle;
    font-size: 14px;
    padding: 8px;
}

.table img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
}

.btn-group .btn {
    padding: 4px 8px;
    font-size: 12px;
}

.delete-modal {
    z-index: 1055;
}

.delete-modal .modal-dialog {
    margin: 1.75rem auto;
    max-width: 400px;
}

.delete-modal .modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.delete-modal .modal-header {
    background-color: #dc3545;
    color: white;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
}

.delete-modal .modal-header .btn-close {
    filter: invert(1);
    opacity: 0.8;
}

.delete-modal .modal-body {
    padding: 1.5rem;
    text-align: center;
}

.delete-modal .modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    justify-content: center;
    gap: 10px;
}

@media (max-width: 768px) {
    .table th:nth-child(3), .table td:nth-child(3) {
        display: none;
    }

    .table th, .table td {
        font-size: 12px;
        padding: 6px;
    }

    .btn-group .btn {
        padding: 3px 6px;
        font-size: 10px;
    }

    .table img {
        width: 40px;
        height: 40px;
    }
}
</style>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-briefcase"></i> Manage Our Works
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="ourworks.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="ourworks.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Work
                    </a>
                <?php endif; ?>
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
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "Add New Work" : "Edit Work"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="work_id" value="<?php echo $work['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Work Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($work['name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control ckeditor" id="content" name="content" rows="10" required><?php echo htmlspecialchars($work['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Work Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control image-upload" id="image" name="image" accept="image/*" <?php echo $action === 'add' ? 'required' : ''; ?>>
                            <?php if ($action === 'edit' && !empty($work['image'])): ?>
                                <div class="mt-2 image-preview">
                                    <img src="<?php echo SITE_URL; ?>/img/ourworks/<?php echo htmlspecialchars($work['image']); ?>" alt="Current Image" class="img-thumbnail" style="max-height: 200px;">
                                    <p class="text-muted mt-1">Uploading a new image will replace the current image.</p>
                                </div>
                            <?php else: ?>
                                <div class="mt-2 image-preview"></div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Add" : "Update"; ?>
                            </button>
                            <a href="ourworks.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-briefcase"></i> Works List
                </div>
                <div class="card-body">
                    <?php if (count($works) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th class="d-none d-md-table-cell">Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($works as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo SITE_URL; ?>/img/ourworks/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars(substr(strip_tags($item['content']), 0, 100)) . (strlen(strip_tags($item['content'])) > 100 ? '...' : ''); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="ourworks.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name'])); ?>')" title="Delete">
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
                            <i class="fas fa-info-circle"></i> No works found.
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade delete-modal" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h6>Are you sure you want to delete this work?</h6>
                    <p class="text-muted mb-2"><strong id="deleteItemTitle"></strong></p>
                    <p class="text-danger small">
                        <i class="fas fa-exclamation-circle"></i> This action cannot be undone.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form method="post" class="d-inline" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="work_id" id="deleteItemId" value="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
CKEDITOR.replace('content', {
    height: 400,
    removePlugins: 'elementspath',
    resize_enabled: false
});

function showDeleteModal(itemId, itemTitle) {
    const existingModals = document.querySelectorAll('.modal.show');
    existingModals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    });
    
    document.getElementById('deleteItemId').value = itemId;
    document.getElementById('deleteItemTitle').textContent = itemTitle;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'), {
        backdrop: 'static',
        keyboard: false
    });
    deleteModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    const imageUpload = document.getElementById('image');
    const imagePreview = document.querySelector('.image-preview');
    
    if (imageUpload && imagePreview) {
        imageUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                        <p class="text-muted mt-1">Preview of new image</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

document.addEventListener('hidden.bs.modal', function (e) {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        backdrop.remove();
    });
    
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
</script>

<?php include 'includes/footer.php'; ?>
