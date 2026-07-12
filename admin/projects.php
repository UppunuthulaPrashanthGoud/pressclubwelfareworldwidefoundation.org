<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$project = [];

$db = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        // Add or Edit Project
        if ($formAction === 'add' || $formAction === 'edit') {
            $title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
            $description = isset($_POST['description']) ? $_POST['description'] : '';
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            if (empty($title) || empty($description)) {
                $error = "शीर्षक और विवरण आवश्यक हैं।";
            } else {
                try {
                    // Handle file upload
                    $image = '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['image'], 'img/projects');
                        if ($uploadResult['success']) {
                            $image = $uploadResult['filename'];
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                    
                    if ($formAction === 'add') {
                        $stmt = $db->prepare("INSERT INTO projects (title, description, image, status, created_at) 
                                              VALUES (:title, :description, :image, :status, NOW())");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        
                        $success = "प्रोजेक्ट सफलतापूर्वक जोड़ा गया।";
                    } else {
                        $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
                        
                        // Get current project data
                        $stmt = $db->prepare("SELECT image FROM projects WHERE id = :id");
                        $stmt->bindParam(':id', $project_id);
                        $stmt->execute();
                        $currentProject = $stmt->fetch();
                        
                        if (!$currentProject) {
                            throw new Exception("प्रोजेक्ट नहीं मिला।");
                        }
                        
                        // Use current image if no new image uploaded
                        if (empty($image)) {
                            $image = $currentProject['image'];
                        } else {
                            // Delete old image if a new one is uploaded
                            if (!empty($currentProject['image'])) {
                                $oldImagePath = __DIR__ . '/../img/projects/' . $currentProject['image'];
                                if (file_exists($oldImagePath)) {
                                    unlink($oldImagePath);
                                }
                            }
                        }
                        
                        $stmt = $db->prepare("UPDATE projects SET title = :title, description = :description, 
                                              image = :image, status = :status WHERE id = :id");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $project_id);
                        $stmt->execute();
                        
                        $success = "प्रोजेक्ट सफलतापूर्वक अपडेट किया गया।";
                    }
                    
                    header("Location: projects.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "त्रुटि: " . $e->getMessage();
                }
            }
        }
        
        // Delete Project
        if ($formAction === 'delete') {
            $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
            
            try {
                // Get project image
                $stmt = $db->prepare("SELECT image FROM projects WHERE id = :id");
                $stmt->bindParam(':id', $project_id);
                $stmt->execute();
                $project = $stmt->fetch();
                
                // Delete project
                $stmt = $db->prepare("DELETE FROM projects WHERE id = :id");
                $stmt->bindParam(':id', $project_id);
                $stmt->execute();
                
                // Delete image file if exists
                if (!empty($project['image'])) {
                    $imagePath = __DIR__ . '/../img/projects/' . $project['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                $success = "प्रोजेक्ट सफलतापूर्वक हटाया गया।";
                header("Location: projects.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "डेटाबेस त्रुटि: " . $e->getMessage();
            }
        }
        
        // Toggle Status
        if ($formAction === 'toggle') {
            $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
            
            try {
                $stmt = $db->prepare("SELECT status FROM projects WHERE id = :id");
                $stmt->bindParam(':id', $project_id);
                $stmt->execute();
                $currentProject = $stmt->fetch();
                
                if (!$currentProject) {
                    throw new Exception("प्रोजेक्ट नहीं मिला।");
                }
                
                $newStatus = $currentProject['status'] === 'active' ? 'inactive' : 'active';
                
                $stmt = $db->prepare("UPDATE projects SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $project_id);
                $stmt->execute();
                
                $success = "प्रोजेक्ट की स्थिति सफलतापूर्वक अपडेट की गई।";
                header("Location: projects.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = "त्रुटि: " . $e->getMessage();
            }
        }
    }
}

// Get project for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            $error = "प्रोजेक्ट नहीं मिला।";
            $action = 'list';
        }
    } catch (PDOException $e) {
        $error = "डेटाबेस त्रुटि: " . $e->getMessage();
        $action = 'list';
    }
}

// Get projects for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM projects");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM projects ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    $error = "डेटाबेस त्रुटि: " . $e->getMessage();
    $projects = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "नया प्रोजेक्ट जोड़ें" : (($action === 'edit') ? "प्रोजेक्ट संपादित करें" : "प्रोजेक्ट प्रबंधन");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus"></i> नया प्रोजेक्ट जोड़ें
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit"></i> प्रोजेक्ट संपादित करें
                <?php else: ?>
                    <i class="fas fa-project-diagram"></i> प्रोजेक्ट प्रबंधन
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="projects.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> वापस
                    </a>
                <?php else: ?>
                    <a href="projects.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> नया प्रोजेक्ट जोड़ें
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
            <!-- Add/Edit Project Form -->
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "नया प्रोजेक्ट जोड़ें" : "प्रोजेक्ट संपादित करें"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">शीर्षक <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">विवरण <span class="text-danger">*</span></label>
                            <textarea class="form-control summernote" id="description" name="description" rows="6" required><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="image" class="form-label">छवि</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <?php if ($action === 'edit' && !empty($project['image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL; ?>/img/projects/<?php echo $project['image']; ?>" alt="Current Image" class="img-thumbnail" style="max-height: 200px;">
                                        <p class="text-muted mt-1">नया छवि अपलोड करने पर वर्तमान छवि बदल जाएगा।</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">स्थिति</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($project['status']) && $project['status'] === 'active') ? 'selected' : ''; ?>>सक्रिय</option>
                                    <option value="inactive" <?php echo (isset($project['status']) && $project['status'] === 'inactive') ? 'selected' : ''; ?>>निष्क्रिय</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "जोड़ें" : "अपडेट करें"; ?>
                            </button>
                            <a href="projects.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> रद्द करें
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Projects List -->
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-project-diagram"></i> प्रोजेक्ट सूची
                </div>
                <div class="card-body">
                    <?php if (count($projects) > 0): ?>
                        <div class="row">
                            <?php foreach ($projects as $item): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="<?php echo SITE_URL; ?>/img/projects/<?php echo $item['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>" style="height: 200px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-project-diagram fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                            <p class="card-text"><?php echo htmlspecialchars(substr(strip_tags($item['description']), 0, 100)) . '...'; ?></p>
                                            <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                <?php echo $item['status'] === 'active' ? 'सक्रिय' : 'निष्क्रिय'; ?>
                                            </span>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-flex justify-content-between">
                                                <a href="projects.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="संपादित करें">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="project_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning" title="स्थिति बदलें">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $item['id']; ?>" title="हटाएं">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">पुष्टि करें</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    क्या आप वाकई प्रोजेक्ट <strong><?php echo htmlspecialchars($item['title']); ?></strong> को हटाना चाहते हैं?
                                                    <p class="text-danger mt-2">यह क्रिया पूर्ववत नहीं की जा सकती है।</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="project_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-danger">हटाएं</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> कोई प्रोजेक्ट नहीं मिला।
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

<?php include 'includes/footer.php'; ?>