<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// File upload function
function uploadFile($file, $targetDir) {
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $result = ['success' => false, 'message' => '', 'filename' => ''];

    // Create target directory if it doesn't exist
    $targetPath = __DIR__ . '/../' . $targetDir;
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
    }

    // Check if file was uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'फ़ाइल अपलोड में त्रुटि।';
        return $result;
    }

    $fileName = basename($file['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $fileExt;
    $targetFile = $targetPath . '/' . $newFileName;

    // Validate file type
    if (!in_array($fileExt, $allowedTypes)) {
        $result['message'] = 'अनुमति नही है फ़ाइल प्रकार। केवल ' . implode(', ', $allowedTypes) . ' अनुमत हैं।';
        return $result;
    }

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        $result['message'] = 'फ़ाइल का आकार 5MB से अधिक नहीं होना चाहिए।';
        return $result;
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $result['success'] = true;
        $result['filename'] = $newFileName;
    } else {
        $result['message'] = 'फ़ाइल अपलोड करने में विफल।';
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
    // Verify table existence
    $stmt = $db->query("SHOW TABLES LIKE 'about_us_content'");
    if ($stmt->rowCount() == 0) {
        error_log("Table 'about_us_content' does not exist in database.");
        $error = "डेटाबेस में 'about_us_content' तालिका नहीं मिली।";
        $sectionItems = [];
        $totalPages = 0;
    }
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $error = "डेटाबेस त्रुटि: " . $e->getMessage();
    $sectionItems = [];
    $totalPages = 0;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $section_title = isset($_POST['section_title']) ? sanitizeInput($_POST['section_title']) : '';
            $content = isset($_POST['content']) ? $_POST['content'] : '';
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            if (empty($section_title) || empty($content)) {
                $error = "सेक्शन शीर्षक और सामग्री आवश्यक हैं।";
            } else {
                try {
                    $image = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['image'], 'img/about');
                        if ($uploadResult['success']) {
                            $image = $uploadResult['filename'];
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                    
                    if ($formAction === 'add') {
                        $stmt = $db->prepare("INSERT INTO about_us_content (section_title, content, image, sort_order, status, created_at) 
                                              VALUES (:section_title, :content, :image, :sort_order, :status, NOW())");
                        $stmt->bindParam(':section_title', $section_title);
                        $stmt->bindParam(':content', $content);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        $success = "हमारे बारे में सेक्शन सफलतापूर्वक जोड़ा गया।";
                    } else {
                        $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
                        $stmt = $db->prepare("SELECT image FROM about_us_content WHERE id = :id");
                        $stmt->bindParam(':id', $section_id);
                        $stmt->execute();
                        $currentData = $stmt->fetch();
                        if (!$currentData) {
                            throw new Exception("सेक्शन नहीं मिला।");
                        }
                        if (empty($image)) {
                            $image = $currentData['image'];
                        } else {
                            if (!empty($currentData['image'])) {
                                $oldImagePath = __DIR__ . '/../img/about/' . $currentData['image'];
                                if (file_exists($oldImagePath)) {
                                    unlink($oldImagePath);
                                }
                            }
                        }
                        $stmt = $db->prepare("UPDATE about_us_content SET section_title = :section_title, content = :content, 
                                              image = :image, sort_order = :sort_order, status = :status, updated_at = NOW() WHERE id = :id");
                        $stmt->bindParam(':section_title', $section_title);
                        $stmt->bindParam(':content', $content);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $section_id);
                        $stmt->execute();
                        $success = "हमारे बारे में सेक्शन सफलतापूर्वक अपडेट किया गया।";
                    }
                    header("Location: about_us_content.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "त्रुटि: " . $e->getMessage();
                }
            }
        }
        if ($formAction === 'delete') {
            $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT image FROM about_us_content WHERE id = :id");
                $stmt->bindParam(':id', $section_id);
                $stmt->execute();
                $data = $stmt->fetch();
                $stmt = $db->prepare("DELETE FROM about_us_content WHERE id = :id");
                $stmt->bindParam(':id', $section_id);
                $stmt->execute();
                if (!empty($data['image'])) {
                    $imagePath = __DIR__ . '/../img/about/' . $data['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $success = "हमारे बारे में सेक्शन सफलतापूर्वक हटाया गया।";
                header("Location: about_us_content.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "डेटाबेस त्रुटि: " . $e->getMessage();
            }
        }
        if ($formAction === 'toggle') {
            $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT status FROM about_us_content WHERE id = :id");
                $stmt->bindParam(':id', $section_id);
                $stmt->execute();
                $currentData = $stmt->fetch();
                if (!$currentData) {
                    throw new Exception("सेक्शन नहीं मिला।");
                }
                $newStatus = $currentData['status'] === 'active' ? 'inactive' : 'active';
                $stmt = $db->prepare("UPDATE about_us_content SET status = :status, updated_at = NOW() WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $section_id);
                $stmt->execute();
                $success = "हमारे बारे में सेक्शन की स्थिति सफलतापूर्वक अपडेट की गई।";
                header("Location: about_us_content.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = "त्रुटि: " . $e->getMessage();
            }
        }
    }
}

// Get data for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM about_us_content WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $section = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$section) {
            $error = "सेक्शन नहीं मिला।";
            $action = 'list';
        }
    } catch (PDOException $e) {
        error_log("Edit query error: " . $e->getMessage());
        $error = "डेटाबेस त्रुटि: " . $e->getMessage();
        $action = 'list';
    }
}

// Get data for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM about_us_content");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    if ($totalRecords == 0) {
        error_log("No records found in about_us_content table.");
    }
    $stmt = $db->prepare("SELECT * FROM about_us_content ORDER BY sort_order ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $sectionItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched " . count($sectionItems) . " records from about_us_content.");
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    error_log("Database query error for about_us_content: " . $e->getMessage());
    $error = "डेटाबेस त्रुटि: " . $e->getMessage();
    $sectionItems = [];
    $totalPages = 0;
}

// Set page title
$pageTitle = ($action === 'add') ? "नया सेक्शन जोड़ें" :
             (($action === 'edit') ? "सेक्शन संपादित करें" : "हमारे बारे में सेक्शन प्रबंधन");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<style>
/* Responsive table styles */
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
    width: 40px;
    height: 40px;
    object-fit: cover;
}

.btn-group .btn {
    padding: 4px 8px;
    font-size: 12px;
}

.badge {
    font-size: 12px;
}

@media (max-width: 768px) {
    .table th:nth-child(3), .table td:nth-child(3), /* Content */
    .table th:nth-child(4), .table td:nth-child(4) /* Sort Order */ {
        display: none; /* Hide Content and Sort Order on small screens */
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
        width: 30px;
        height: 30px;
    }
}

@media (max-width: 576px) {
    .table th, .table td {
        font-size: 10px;
        padding: 4px;
    }

    .btn-group .btn {
        padding: 2px 4px;
        font-size: 9px;
    }

    .badge {
        font-size: 10px;
    }
}
</style>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-list"></i> हमारे बारे में सेक्शन प्रबंधन
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="about_us_content.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> वापस
                    </a>
                <?php else: ?>
                    <a href="about_us_content.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> नया सेक्शन जोड़ें
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
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "नया सेक्शन जोड़ें" : "सेक्शन संपादित करें"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="section_title" class="form-label">सेक्शन शीर्षक <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="section_title" name="section_title" value="<?php echo htmlspecialchars($section['section_title'] ?? 'हमारा मिशन'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">सामग्री <span class="text-danger">*</span></label>
                            <textarea class="form-control ckeditor" id="content" name="content" rows="8" required><?php echo htmlspecialchars($section['content'] ?? "राष्ट्रीय विकास फाउंडेशन (NDF), पंजीकरण संख्या 202400777016680 के साथ, एक गैर-लाभकारी संगठन है जो समाज के उत्थान के लिए समर्पित है। श्री ब्रजेंद्र कुमार द्वारा स्थापित और श्री नारायण मिश्रा (राष्ट्रपति) और श्री मोहित मिश्रा (उपाध्यक्ष/सचिव) के नेतृत्व में, हम शिक्षा और कौशल विकास, स्वास्थ्य और स्वच्छता, ग्रामीण विकास और पर्यावरण, महिला सशक्तिकरण, तथा भ्रष्टाचार और सामाजिक न्याय के क्षेत्र में कार्य करते हैं। हमारा उद्देश्य ग्राम मुदाई, मैनपुरी, उत्तर प्रदेश 205301 से संचालित होकर एक समृद्ध, न्यायपूर्ण, और पर्यावरण के प्रति जागरूक भारत का निर्माण करना है।\n\nसंपर्क करें: 7454838285 | ईमेल: official.ndfoundation@gmail.com | वेबसाइट: ndfoundation.in"); ?></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="image" class="form-label">छवि</label>
                                <input type="file" class="form-control image-upload" id="image" name="image" accept="image/*">
                                <?php if ($action === 'edit' && !empty($section['image'])): ?>
                                    <div class="mt-2 image-preview">
                                        <img src="<?php echo SITE_URL; ?>/img/about/<?php echo htmlspecialchars($section['image']); ?>" alt="Current Image" class="img-thumbnail" style="max-height: 200px;">
                                        <p class="text-muted mt-1">नया छवि अपलोड करने पर वर्तमान छवि बदल जाएगा।</p>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-2 image-preview"></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label for="sort_order" class="form-label">क्रम</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($section['sort_order'] ?? 0); ?>" min="0">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">स्थिति</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($section['status']) && $section['status'] === 'active') ? 'selected' : ''; ?>>सक्रिय</option>
                                    <option value="inactive" <?php echo (isset($section['status']) && $section['status'] === 'inactive') ? 'selected' : ''; ?>>निष्क्रिय</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "जोड़ें" : "अपडेट करें"; ?>
                            </button>
                            <a href="about_us_content.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> रद्द करें
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-list"></i> सेक्शन
                </div>
                <div class="card-body">
                    <?php if (count($sectionItems) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>छवि</th>
                                        <th>सेक्शन शीर्षक</th>
                                        <th class="d-none d-md-table-cell">सामग्री</th>
                                        <th class="d-none d-md-table-cell">क्रम</th>
                                        <th>स्थिति</th>
                                        <th>कार्य</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sectionItems as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo SITE_URL; ?>/img/about/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['section_title']); ?>" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['section_title']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars(substr(strip_tags($item['content']), 0, 50)) . (strlen(strip_tags($item['content'])) > 50 ? '...' : ''); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo $item['sort_order']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo $item['status'] === 'active' ? 'सक्रिय' : 'निष्क्रिय'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="about_us_content.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="संपादित करें">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="section_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="स्थिति बदलें">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $item['id']; ?>" title="हटाएं">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-sm">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">पुष्टि करें</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                क्या आप वाकई सेक्शन <strong><?php echo htmlspecialchars($item['section_title']); ?></strong> को हटाना चाहते हैं?
                                                                <p class="text-danger mt-2">यह क्रिया पूर्ववत नहीं की जा सकती है।</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                                                                <form method="post" class="d-inline">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="section_id" value="<?php echo $item['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger">हटाएं</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> कोई सेक्शन नहीं मिला।
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