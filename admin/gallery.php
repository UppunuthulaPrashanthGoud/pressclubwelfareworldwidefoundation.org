<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin and coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'Gallery Management';
$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token or use POST-Redirect-GET pattern
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        try {
            $upload_dir = '../img/gallery/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $uploaded_files = $_FILES['images'];
            $uploaded_count = 0;
            
            for ($i = 0; $i < count($uploaded_files['name']); $i++) {
                if ($uploaded_files['error'][$i] === UPLOAD_ERR_OK) {
                    $file_ext = strtolower(pathinfo($uploaded_files['name'][$i], PATHINFO_EXTENSION));
                    
                    if (in_array($file_ext, $allowed_ext)) {
                        $image = uniqid('gallery_') . '.' . $file_ext;
                        if (move_uploaded_file($uploaded_files['tmp_name'][$i], $upload_dir . $image)) {
                            $stmt = $db->prepare("INSERT INTO gallery (image) VALUES (?)");
                            $stmt->execute([$image]);
                            $uploaded_count++;
                        }
                    }
                }
            }
            
            // Redirect to prevent form resubmission
            header("Location: ?success=add&count=" . $uploaded_count);
            exit;
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        try {
            $delete_id = intval($_POST['id']);
            
            // Verify the ID is valid
            if ($delete_id <= 0) {
                throw new Exception('Invalid image ID');
            }
            
            // Get image filename before deleting
            $stmt = $db->prepare("SELECT image FROM gallery WHERE id = ?");
            $stmt->execute([$delete_id]);
            $gallery = $stmt->fetch();
            
            if ($gallery) {
                // Delete gallery item from database
                $stmt = $db->prepare("DELETE FROM gallery WHERE id = ?");
                $stmt->execute([$delete_id]);
                
                // Delete physical file if it exists
                if (!empty($gallery['image']) && file_exists('../img/gallery/' . $gallery['image'])) {
                    unlink('../img/gallery/' . $gallery['image']);
                }
                
                // Redirect to prevent form resubmission
                header("Location: ?success=delete");
                exit;
            } else {
                throw new Exception('Image not found');
            }
        } catch (Exception $e) {
            $error = 'Error deleting image: ' . $e->getMessage();
        }
    }
}

// Handle success messages from redirects
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'add') {
        $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
        $message = $count . ' image(s) added successfully!';
    } elseif ($_GET['success'] === 'delete') {
        $message = 'Image deleted successfully!';
    }
}

// Get gallery list
$gallery_list = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM gallery ORDER BY id DESC");
    $stmt->execute();
    $gallery_list = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-images me-3"></i>Gallery Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Images
                </a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
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
        <!-- Gallery Grid -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-th"></i> Gallery Images (<?php echo count($gallery_list); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($gallery_list)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-images fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No images in gallery yet. Click "Add New Images" to upload.</p>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($gallery_list as $item): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="gallery-item" data-id="<?php echo $item['id']; ?>">
                            <div class="gallery-image">
                                <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo SITE_URL . '/img/gallery/' . $item['image']; ?>" 
                                     alt="Gallery Image" 
                                     class="img-fluid"
                                     onerror="this.parentElement.innerHTML='<div class=\'no-image\'><i class=\'fas fa-image\'></i></div>'">
                                <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                                <?php endif; ?>
                                <div class="gallery-overlay">
                                    <div class="gallery-actions">
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-btn" 
                                                data-id="<?php echo $item['id']; ?>"
                                                onclick="deleteGallery(<?php echo $item['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($action === 'add'): ?>
        <!-- Add Form -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> Add New Images</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="images" class="form-label">Images * (Select multiple images)</label>
                        <input type="file" 
                               class="form-control" 
                               id="images" 
                               name="images[]" 
                               accept="image/jpeg,image/jpg,image/png,image/gif" 
                               multiple 
                               required>
                        <div class="form-text">Supported formats: JPG, JPEG, PNG, GIF</div>
                    </div>
                    
                    <div id="imagePreview" class="row mb-3"></div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Upload Images
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Form (Hidden) - This form is submitted only once per delete action -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId" value="">
</form>

<?php include 'includes/sidebar.php'; ?>

<style>
.gallery-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: #fff;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.gallery-image {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: #f8f9fa;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 2rem;
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-actions {
    display: flex;
    gap: 10px;
}

.delete-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#imagePreview .preview-item {
    position: relative;
    margin-bottom: 10px;
}

#imagePreview img {
    border-radius: 8px;
    border: 2px solid #e9ecef;
}
</style>

<script>
// Prevent multiple form submissions
let isDeleting = false;

function deleteGallery(id) {
    // Prevent multiple deletions
    if (isDeleting) {
        console.log('Already deleting an image, please wait...');
        return;
    }
    
    if (!id || id <= 0) {
        alert('Invalid image ID');
        return;
    }
    
    if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
        isDeleting = true;
        
        // Disable all delete buttons to prevent multiple clicks
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.disabled = true;
        });
        
        // Set the ID and submit the form
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Image preview for upload
document.getElementById('images')?.addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    const files = e.target.files;
    if (files.length > 0) {
        for (let i = 0; i < Math.min(files.length, 10); i++) {
            const file = files[i];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-lg-2 col-md-3 col-sm-4 col-6';
                    col.innerHTML = `
                        <div class="preview-item">
                            <img src="${e.target.result}" class="img-fluid" alt="Preview">
                        </div>
                    `;
                    preview.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        }
        
        if (files.length > 10) {
            preview.innerHTML += '<div class="col-12"><small class="text-muted">Showing preview of first 10 images...</small></div>';
        }
    }
});

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

<?php include 'includes/footer.php'; ?>