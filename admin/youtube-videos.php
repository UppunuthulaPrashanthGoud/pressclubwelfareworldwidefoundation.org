<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin and coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'YouTube Video Management';
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
                    $title = sanitizeInput($_POST['title']);
                    $description = sanitizeInput($_POST['description'] ?? '');
                    $video_id = sanitizeInput($_POST['video_id']);
                    
                    // Extract video ID from URL if provided (supports standard, youtu.be, and Shorts URLs)
                    if (preg_match('/[?&]v=([^&]+)/', $video_id, $matches) || 
                        preg_match('/youtu\.be\/([^?]+)/', $video_id, $matches) || 
                        preg_match('/youtube\.com\/shorts\/([^?]+)/', $video_id, $matches)) {
                        $video_id = $matches[1];
                    }
                    
                    $status = sanitizeInput($_POST['status']);
                    
                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO youtube_videos (title, description, video_id, status, created_at) 
                            VALUES (?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$title, $description, $video_id, $status]);
                        $message = 'YouTube video/short added successfully!';
                    } else {
                        $stmt = $db->prepare("
                            UPDATE youtube_videos SET title = ?, description = ?, video_id = ?, status = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $description, $video_id, $status, $id]);
                        $message = 'YouTube video/short updated successfully!';
                    }
                    
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'Error: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $delete_id = $_POST['id'];
                    $stmt = $db->prepare("DELETE FROM youtube_videos WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $message = 'YouTube video/short deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting video/short: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get video data for editing
$video_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM youtube_videos WHERE id = ?");
    $stmt->execute([$id]);
    $video_data = $stmt->fetch();
    
    if (!$video_data) {
        $error = 'Video/short not found!';
        $action = 'list';
    }
}

// Get videos list
$videos_list = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM youtube_videos ORDER BY created_at DESC");
    $stmt->execute();
    $videos_list = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<style>
.img-thumbnail {
    display: block !important;
    max-width: 60px;
    max-height: 60px;
    object-fit: cover;
}
</style>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fab fa-youtube me-3"></i>YouTube Video/Shorts Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Video/Short
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
        <!-- Videos/Shorts List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fab fa-youtube"></i> Videos/Shorts List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Video ID</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos_list as $video): ?>
                            <tr>
                                <td>
                                    <!-- Debug: video_id=<?php echo htmlspecialchars($video['video_id']); ?> -->
                                    <?php if (!empty($video['video_id'])): ?>
                                        <img src="https://img.youtube.com/vi/<?php echo htmlspecialchars($video['video_id']); ?>/hqdefault.jpg" 
                                             alt="<?php echo htmlspecialchars($video['title']); ?>" 
                                             class="img-thumbnail" 
                                             width="60" 
                                             height="60"
                                             onerror="this.src='<?php echo SITE_URL; ?>/img/default-video.jpg'">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="fas fa-video text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($video['title']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr(strip_tags($video['description'] ?? ''), 0, 100)) . (strlen($video['description'] ?? '') > 100 ? '...' : ''); ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($video['video_id']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $video['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $video['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d M Y', strtotime($video['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $video['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteVideo(<?php echo $video['id']; ?>)">
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
                    <?php echo $action === 'add' ? 'Add New YouTube Video/Short' : 'Edit YouTube Video/Short'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required
                               value="<?php echo htmlspecialchars($video_data['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="video_id" class="form-label">YouTube Video/Short ID *</label>
                        <input type="text" class="form-control" id="video_id" name="video_id" required
                               value="<?php echo htmlspecialchars($video_data['video_id'] ?? ''); ?>"
                               placeholder="Example: dQw4w9WgXcQ">
                        <small class="form-text text-muted">Enter the Video/Short ID from the YouTube URL (https://www.youtube.com/watch?v=VIDEO_ID or https://www.youtube.com/shorts/VIDEO_ID)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($video_data['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" <?php echo ($video_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($video_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <?php if (!empty($video_data['video_id'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Video/Short Preview</label>
                        <div class="video-thumbnail">
                            <iframe width="100%" height="200" 
                                    src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_data['video_id']); ?>" 
                                    frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                    
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
function deleteVideo(id) {
    if (confirm('Are you sure you want to delete this YouTube video/short? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>