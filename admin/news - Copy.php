<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin and coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'समाचार प्रबंधन';
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
                    $content = $_POST['content']; // Rich text content
                    $status = sanitizeInput($_POST['status']);
                    $excerpt = sanitizeInput($_POST['excerpt'] ?? '');
                    $author = sanitizeInput($_POST['author'] ?? $_SESSION['user_name']);
                    
                    // Handle image upload
                    $image = '';
                    if (!empty($_FILES['image']['name'])) {
                        $upload_dir = '../img/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_ext, $allowed_ext)) {
                            $image = uniqid('news_') . '.' . $file_ext;
                            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
                        }
                    }
                    
                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO news (title, content, excerpt, image, author, status, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");
                        $stmt->execute([$title, $content, $excerpt, $image, $author, $status]);
                        $message = 'समाचार सफलतापूर्वक जोड़ा गया!';
                    } else {
                        // Keep existing image if no new image uploaded
                        if (empty($image)) {
                            $stmt = $db->prepare("SELECT image FROM news WHERE id = ?");
                            $stmt->execute([$id]);
                            $existing = $stmt->fetch();
                            $image = $existing['image'] ?? '';
                        }
                        
                        $stmt = $db->prepare("
                            UPDATE news SET title = ?, content = ?, excerpt = ?, image = ?, author = ?, status = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $content, $excerpt, $image, $author, $status, $id]);
                        $message = 'समाचार अपडेट किया गया!';
                    }
                    
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'त्रुटि: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $delete_id = $_POST['id'];
                    
                    // Get image filename before deleting
                    $stmt = $db->prepare("SELECT image FROM news WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $news = $stmt->fetch();
                    
                    // Delete news
                    $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete image if it exists
                    if (!empty($news['image']) && file_exists('../img/' . $news['image'])) {
                        unlink('../img/' . $news['image']);
                    }
                    
                    $message = 'समाचार सफलतापूर्वक हटाया गया!';
                } catch (Exception $e) {
                    $error = 'हटाने में त्रुटि: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get news data for editing
$news_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news_data = $stmt->fetch();
    
    if (!$news_data) {
        $error = 'समाचार नहीं मिला!';
        $action = 'list';
    }
}

// Get news list - using only columns that exist in your table
$news_list = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM news ORDER BY created_at DESC");
    $stmt->execute();
    $news_list = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-newspaper me-3"></i>समाचार प्रबंधन
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> नया समाचार जोड़ें
                </a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> वापस जाएं
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
        <!-- News List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> समाचार सूची</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>छवि</th>
                                <th>शीर्षक</th>
                                <th>स्थिति</th>
                                <th>लेखक</th>
                                <th>बनाया गया</th>
                                <th>कार्य</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($news_list as $news): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($news['image'])): ?>
                                    <img src="<?php echo SITE_URL . '/img/' . $news['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                         class="img-thumbnail" width="60" height="60">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($news['title']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr(strip_tags($news['content']), 0, 100)) . '...'; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $news['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $news['status'] === 'active' ? 'सक्रिय' : 'निष्क्रिय'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($news['author'] ?? 'Unknown'); ?></td>
                                <td>
                                    <?php echo date('d M Y', strtotime($news['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $news['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNews(<?php echo $news['id']; ?>)">
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
                    <?php echo $action === 'add' ? 'नया समाचार जोड़ें' : 'समाचार संपादित करें'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">शीर्षक *</label>
                        <input type="text" class="form-control" id="title" name="title" required
                               value="<?php echo htmlspecialchars($news_data['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">संक्षिप्त विवरण</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($news_data['excerpt'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">सामग्री *</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($news_data['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="author" class="form-label">लेखक</label>
                                <input type="text" class="form-control" id="author" name="author"
                                       value="<?php echo htmlspecialchars($news_data['author'] ?? $_SESSION['user_name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">स्थिति *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($news_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>सक्रिय</option>
                                    <option value="inactive" <?php echo ($news_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>निष्क्रिय</option>
                                    <option value="draft" <?php echo ($news_data['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>ड्राफ्ट</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">छवि</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if (!empty($news_data['image'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo SITE_URL . '/img/' . $news_data['image']; ?>" 
                                 alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">रद्द करें</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> सहेजें
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
function deleteNews(id) {
    if (confirm('क्या आप वाकई इस समाचार को हटाना चाहते हैं? यह क्रिया पूर्ववत नहीं की जा सकती है।')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
