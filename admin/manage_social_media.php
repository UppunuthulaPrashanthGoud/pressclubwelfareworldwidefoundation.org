<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Define uploadFile function - Reusing the one from manage_affiliations structure
function uploadFile($file, $targetDir) {
    $result = ['success' => false, 'message' => '', 'filename' => ''];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload error: ' . $file['error'];
        return $result;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        $result['message'] = 'Invalid file type. Only JPG, PNG, GIF, WebP, MP4, and WebM are allowed.';
        return $result;
    }

    $maxSize = 100 * 1024 * 1024; // 100MB for media files
    if ($file['size'] > $maxSize) {
        $result['message'] = 'File size exceeds 100MB limit.';
        return $result;
    }

    $targetPath = __DIR__ . '/../' . $targetDir;
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
    }

    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = uniqid('social_', true) . '.' . strtolower($fileExtension);
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
$social_item = [];

$db = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $type = isset($_POST['type']) ? sanitizeInput($_POST['type']) : 'post';
            $link = isset($_POST['link']) ? sanitizeInput($_POST['link']) : null;
            $caption = isset($_POST['caption']) ? sanitizeInput($_POST['caption']) : null;
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            try {
                $content_file = '';
                
                // 1. Handle File Upload (if applicable)
                if (isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadFile($_FILES['content_file'], 'img/social_media');
                    if ($uploadResult['success']) {
                        $content_file = $uploadResult['filename'];
                    } else {
                        throw new Exception($uploadResult['message']);
                    }
                }
                
                if ($formAction === 'add') {
                    if (empty($content_file) && empty($link) && empty($caption)) {
                         throw new Exception("You must provide either an uploaded file, a link, or a caption for the post.");
                    }
                    
                    $stmt = $db->prepare("INSERT INTO social_media (type, content_file, link, caption, sort_order, status, created_at) 
                                          VALUES (:type, :content_file, :link, :caption, :sort_order, :status, NOW())");
                    $stmt->bindParam(':type', $type);
                    $stmt->bindParam(':content_file', $content_file);
                    $stmt->bindParam(':link', $link);
                    $stmt->bindParam(':caption', $caption);
                    $stmt->bindParam(':sort_order', $sort_order);
                    $stmt->bindParam(':status', $status);
                    $stmt->execute();
                    
                    $success = "Social Media Post added successfully.";
                } else {
                    $social_id = isset($_POST['social_id']) ? (int)$_POST['social_id'] : 0;
                    
                    $stmt = $db->prepare("SELECT content_file FROM social_media WHERE id = :id");
                    $stmt->bindParam(':id', $social_id);
                    $stmt->execute();
                    $currentItem = $stmt->fetch();
                    
                    if (!$currentItem) {
                        throw new Exception("Social Media Post not found.");
                    }
                    
                    $content_file = $content_file ?: $currentItem['content_file']; // Use new file or keep old one
                    
                    if ($content_file !== $currentItem['content_file'] && !empty($currentItem['content_file'])) {
                        // Delete old file if a new one was uploaded
                        $oldFilePath = __DIR__ . '/../img/social_media/' . $currentItem['content_file'];
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    $stmt = $db->prepare("UPDATE social_media SET type = :type, content_file = :content_file, link = :link, 
                                          caption = :caption, sort_order = :sort_order, status = :status WHERE id = :id");
                    $stmt->bindParam(':type', $type);
                    $stmt->bindParam(':content_file', $content_file);
                    $stmt->bindParam(':link', $link);
                    $stmt->bindParam(':caption', $caption);
                    $stmt->bindParam(':sort_order', $sort_order);
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':id', $social_id);
                    $stmt->execute();
                    
                    $success = "Social Media Post updated successfully.";
                }
                
                header("Location: manage_social_media.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        if ($formAction === 'delete') {
            $social_id = isset($_POST['social_id']) ? (int)$_POST['social_id'] : 0;
            
            try {
                $stmt = $db->prepare("SELECT content_file FROM social_media WHERE id = :id");
                $stmt->bindParam(':id', $social_id);
                $stmt->execute();
                $social_item = $stmt->fetch();
                
                $stmt = $db->prepare("DELETE FROM social_media WHERE id = :id");
                $stmt->bindParam(':id', $social_id);
                $stmt->execute();
                
                if (!empty($social_item['content_file'])) {
                    $filePath = __DIR__ . '/../img/social_media/' . $social_item['content_file'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                $success = "Social Media Post deleted successfully.";
                header("Location: manage_social_media.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
        
        if ($formAction === 'toggle') {
            $social_id = isset($_POST['social_id']) ? (int)$_POST['social_id'] : 0;
            
            try {
                $stmt = $db->prepare("SELECT status FROM social_media WHERE id = :id");
                $stmt->bindParam(':id', $social_id);
                $stmt->execute();
                $currentItem = $stmt->fetch();
                
                if (!$currentItem) {
                    throw new Exception("Post not found.");
                }
                
                $newStatus = $currentItem['status'] === 'active' ? 'inactive' : 'active';
                
                $stmt = $db->prepare("UPDATE social_media SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $social_id);
                $stmt->execute();
                
                $success = "Post status updated successfully.";
                header("Location: manage_social_media.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Get item for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM social_media WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $social_item = $stmt->fetch();
        
        if (!$social_item) {
            $error = "Social Media Post not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Get items for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM social_media");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM social_media ORDER BY sort_order ASC, created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $social_items = $stmt->fetchAll();
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $social_items = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add Social Media Post" : (($action === 'edit') ? "Edit Social Media Post" : "Social Media Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus me-2"></i>Add Social Media Post
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit me-2"></i>Edit Social Media Post
                <?php else: ?>
                    <i class="fab fa-instagram me-2"></i>Social Media Management
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                    <a href="manage_social_media.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Post
                    </a>
                <?php else: ?>
                    <a href="manage_social_media.php" class="btn btn-secondary">
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
                    <i class="fas fa-edit"></i> <?php echo ($action === 'add') ? "Add New Post" : "Edit Post"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="social_id" value="<?php echo $social_item['id']; ?>">
                        <?php endif; ?>
                        
                        <!-- 1. Post Content Type -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Post Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="post" <?php echo (isset($social_item['type']) && $social_item['type'] === 'post') ? 'selected' : ''; ?>>General Post (Uses Link/Caption)</option>
                                <option value="image" <?php echo (isset($social_item['type']) && $social_item['type'] === 'image') ? 'selected' : ''; ?>>Image/Video Upload</option>
                            </select>
                            <div class="form-text">Choose 'Image/Video Upload' to upload a file directly, or 'General Post' to rely on the link and caption.</div>
                        </div>

                        <!-- 2. Content File Upload -->
                        <div class="mb-3">
                            <label for="content_file" class="form-label">
                                Upload Image/Video File (Optional)
                            </label>
                            <input type="file" class="form-control" id="content_file" name="content_file" 
                                   accept="image/*,video/mp4,video/webm">
                            <div class="form-text">Supported formats: JPG, PNG, GIF, WebP, MP4, WebM. Max size: 100MB. Uploading a new file will replace the old one.</div>
                            <?php if ($action === 'edit' && !empty($social_item['content_file'])): 
                                $is_video = strpos(mime_content_type(__DIR__ . '/../img/social_media/' . $social_item['content_file']), 'video') !== false;
                            ?>
                                <div class="mt-3">
                                    <label class="form-label">Current Content:</label>
                                    <div class="current-media-preview">
                                        <?php if ($is_video): ?>
                                            <video controls style="max-height: 200px; max-width: 300px;">
                                                <source src="<?php echo SITE_URL; ?>/img/social_media/<?php echo $social_item['content_file']; ?>" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        <?php else: ?>
                                            <img src="<?php echo SITE_URL; ?>/img/social_media/<?php echo $social_item['content_file']; ?>" 
                                                 alt="Post Content" class="img-thumbnail d-block" 
                                                 style="max-height: 200px; max-width: 300px;">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 3. Social Media Link -->
                        <div class="mb-3">
                            <label for="link" class="form-label">Social Media Post URL (Optional)</label>
                            <input type="url" class="form-control" id="link" name="link" 
                                   value="<?php echo htmlspecialchars($social_item['link'] ?? ''); ?>" 
                                   placeholder="e.g., https://instagram.com/p/...">
                            <div class="form-text">Direct link to the original post on social media.</div>
                        </div>

                        <!-- 4. Caption / Description -->
                        <div class="mb-3">
                            <label for="caption" class="form-label">Caption / Description</label>
                            <textarea class="form-control" id="caption" name="caption" rows="3" 
                                      placeholder="A brief description of the post or event."><?php echo htmlspecialchars($social_item['caption'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- 5. Sort Order and Status -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo htmlspecialchars($social_item['sort_order'] ?? 0); ?>" 
                                       min="0" placeholder="0">
                                <div class="form-text">Lower numbers appear first</div>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($social_item['status']) && $social_item['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($social_item['status']) && $social_item['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php echo ($action === 'add') ? "Add Post" : "Update Post"; ?>
                            </button>
                            <a href="manage_social_media.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fab fa-instagram"></i> Social Media Posts</span>
                    <span class="badge bg-secondary"><?php echo $totalRecords; ?> Total</span>
                </div>
                <div class="card-body">
                    <?php if (count($social_items) > 0): ?>
                        <div class="row">
                            <?php foreach ($social_items as $item): ?>
                                <div class="col-md-4 col-lg-3 mb-4">
                                    <div class="card h-100 social-media-card">
                                        <div class="card-img-top-container text-center p-3" style="height: 200px; overflow: hidden; background: #eee;">
                                        <?php if (!empty($item['content_file'])): 
                                            $file_path = __DIR__ . '/../img/social_media/' . $item['content_file'];
                                            $mime_type = file_exists($file_path) ? mime_content_type($file_path) : 'image/jpeg'; // Fallback
                                            if (strpos($mime_type, 'video') !== false): ?>
                                                <i class="fas fa-video fa-5x text-secondary mt-4"></i>
                                                <small class="d-block text-muted">Video File</small>
                                            <?php else: ?>
                                                <img src="<?php echo SITE_URL; ?>/img/social_media/<?php echo $item['content_file']; ?>" 
                                                     alt="Post Content" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                            <?php endif; ?>
                                        <?php elseif (!empty($item['link'])): ?>
                                            <i class="fas fa-share-alt fa-5x text-info mt-4"></i>
                                            <small class="d-block text-muted">External Link</small>
                                        <?php else: ?>
                                            <i class="fas fa-pen fa-5x text-warning mt-4"></i>
                                            <small class="d-block text-muted">Text Post</small>
                                        <?php endif; ?>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text small text-truncate" title="<?php echo htmlspecialchars($item['caption']); ?>">
                                                <?php echo htmlspecialchars($item['caption'] ?: 'No Caption'); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                                <small class="text-muted">Sort: <?php echo $item['sort_order']; ?></small>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex justify-content-between">
                                                <a href="manage_social_media.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if (!empty($item['link'])): ?>
                                                    <a href="<?php echo htmlspecialchars($item['link']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="View Post Link">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="social_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
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
                            <i class="fas fa-info-circle me-2"></i> No social media posts found.
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
<?php if ($action === 'list' && count($social_items) > 0): ?>
    <?php foreach ($social_items as $item): ?>
        <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete the post with caption: 
                        <span class="fw-bold text-primary"><?php echo htmlspecialchars(substr($item['caption'], 0, 50)); ?>...</span>?
                        <p class="text-danger mt-2">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="social_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>