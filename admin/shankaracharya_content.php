<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// File upload function
function uploadFile($file, $targetDir) {
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'ico'];
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

    // Validate image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $result['message'] = 'अमान्य छवि फ़ाइल।';
        return $result;
    }

    $fileName = basename($file['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $fileExt;
    $targetFile = $targetPath . '/' . $newFileName;

    // Validate file type
    if (!in_array($fileExt, $allowedTypes)) {
        $result['message'] = 'अनुमति नहीं है फ़ाइल प्रकार। केवल ' . implode(', ', $allowedTypes) . ' अनुमत हैं।';
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
        logError("File uploaded successfully: $newFileName to $targetDir");
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
    $stmt = $db->query("SHOW TABLES LIKE 'shankaracharya_content'");
    if ($stmt->rowCount() == 0) {
        logError("Table 'shankaracharya_content' does not exist in database.");
        $error = "डेटाबेस में 'shankaracharya_content' तालिका नहीं मिली।";
        $shankaracharyaItems = [];
        $totalPages = 0;
    }
} catch (PDOException $e) {
    logError("Database connection error: " . $e->getMessage());
    $error = "डेटाबेस त्रुटि: " . $e->getMessage();
    $shankaracharyaItems = [];
    $totalPages = 0;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = "अमान्य CSRF टोकन।";
        logError($error);
    } elseif (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
            $description = isset($_POST['description']) ? $_POST['description'] : '';
            $position = isset($_POST['position']) ? sanitizeInput($_POST['position']) : '';
            $tenure_start = isset($_POST['tenure_start']) ? sanitizeInput($_POST['tenure_start']) : '';
            $birth_date = isset($_POST['birth_date']) ? sanitizeInput($_POST['birth_date']) : '';
            $birth_place = isset($_POST['birth_place']) ? sanitizeInput($_POST['birth_place']) : '';
            $specialization = isset($_POST['specialization']) ? sanitizeInput($_POST['specialization']) : '';
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 1;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            if (empty($title) || empty($description)) {
                $error = "शीर्षक और विवरण आवश्यक हैं।";
            } else {
                try {
                    $image = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['image'], 'img/shankaracharya');
                        if ($uploadResult['success']) {
                            $image = $uploadResult['filename'];
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                    
                    if ($formAction === 'add') {
                        $stmt = $db->prepare("INSERT INTO shankaracharya_content (title, description, position, tenure_start, birth_date, birth_place, specialization, image, sort_order, status, created_at) 
                                              VALUES (:title, :description, :position, :tenure_start, :birth_date, :birth_place, :specialization, :image, :sort_order, :status, NOW())");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':position', $position);
                        $stmt->bindParam(':tenure_start', $tenure_start);
                        $stmt->bindParam(':birth_date', $birth_date);
                        $stmt->bindParam(':birth_place', $birth_place);
                        $stmt->bindParam(':specialization', $specialization);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        $success = "शंकराचार्य सामग्री सफलतापूर्वक जोड़ी गई।";
                    } else {
                        $shankaracharya_id = isset($_POST['shankaracharya_id']) ? (int)$_POST['shankaracharya_id'] : 0;
                        $stmt = $db->prepare("SELECT image FROM shankaracharya_content WHERE id = :id");
                        $stmt->bindParam(':id', $shankaracharya_id);
                        $stmt->execute();
                        $currentData = $stmt->fetch();
                        if (!$currentData) {
                            throw new Exception("सामग्री नहीं मिली।");
                        }
                        if (empty($image)) {
                            $image = $currentData['image'];
                        } else {
                            if (!empty($currentData['image'])) {
                                $oldImagePath = __DIR__ . '/../img/shankaracharya/' . $currentData['image'];
                                if (file_exists($oldImagePath)) {
                                    unlink($oldImagePath);
                                    logError("Old image deleted: " . $currentData['image']);
                                }
                            }
                        }
                        $stmt = $db->prepare("UPDATE shankaracharya_content SET title = :title, description = :description, position = :position, 
                                              tenure_start = :tenure_start, birth_date = :birth_date, birth_place = :birth_place, 
                                              specialization = :specialization, image = :image, sort_order = :sort_order, status = :status, updated_at = NOW() WHERE id = :id");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':position', $position);
                        $stmt->bindParam(':tenure_start', $tenure_start);
                        $stmt->bindParam(':birth_date', $birth_date);
                        $stmt->bindParam(':birth_place', $birth_place);
                        $stmt->bindParam(':specialization', $specialization);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $shankaracharya_id);
                        $stmt->execute();
                        $success = "शंकराचार्य सामग्री सफलतापूर्वक अपडेट की गई।";
                    }
                    header("Location: shankaracharya_content.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "त्रुटि: " . $e->getMessage();
                    logError($error);
                }
            }
        }
        if ($formAction === 'delete') {
            $shankaracharya_id = isset($_POST['shankaracharya_id']) ? (int)$_POST['shankaracharya_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT image FROM shankaracharya_content WHERE id = :id");
                $stmt->bindParam(':id', $shankaracharya_id);
                $stmt->execute();
                $data = $stmt->fetch();
                $stmt = $db->prepare("DELETE FROM shankaracharya_content WHERE id = :id");
                $stmt->bindParam(':id', $shankaracharya_id);
                $stmt->execute();
                if (!empty($data['image'])) {
                    $imagePath = __DIR__ . '/../img/shankaracharya/' . $data['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                        logError("Image deleted on content deletion: " . $data['image']);
                    }
                }
                $success = "शंकराचार्य सामग्री सफलतापूर्वक हटाई गई।";
                header("Location: shankaracharya_content.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "डेटाबेस त्रुटि: " . $e->getMessage();
                logError($error);
            }
        }
        if ($formAction === 'toggle') {
            $shankaracharya_id = isset($_POST['shankaracharya_id']) ? (int)$_POST['shankaracharya_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT status FROM shankaracharya_content WHERE id = :id");
                $stmt->bindParam(':id', $shankaracharya_id);
                $stmt->execute();
                $currentData = $stmt->fetch();
                if (!$currentData) {
                    throw new Exception("सामग्री नहीं मिली।");
                }
                $newStatus = $currentData['status'] === 'active' ? 'inactive' : 'active';
                $stmt = $db->prepare("UPDATE shankaracharya_content SET status = :status, updated_at = NOW() WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $shankaracharya_id);
                $stmt->execute();
                $success = "शंकराचार्य सामग्री की स्थिति सफलतापूर्वक अपडेट की गई।";
                header("Location: shankaracharya_content.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = "त्रुटि: " . $e->getMessage();
                logError($error);
            }
        }
    }
}

// Get data for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM shankaracharya_content WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $shankaracharya = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$shankaracharya) {
            $error = "सामग्री नहीं मिली।";
            $action = 'list';
        }
    } catch (PDOException $e) {
        logError("Edit query error: " . $e->getMessage());
        $error = "डेटाबेस त्रुटि: " . $e->getMessage();
        $action = 'list';
    }
}

// Get data for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM shankaracharya_content");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT * FROM shankaracharya_content ORDER BY sort_order ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $shankaracharyaItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError("Database query error for shankaracharya_content: " . $e->getMessage());
    $error = "डेटाबेस त्रुटि: " . $e->getMessage();
    $shankaracharyaItems = [];
    $totalPages = 0;
}

// Set page title
$pageTitle = ($action === 'add') ? "नई शंकराचार्य सामग्री जोड़ें" :
             (($action === 'edit') ? "शंकराचार्य सामग्री संपादित करें" : "शंकराचार्य सामग्री प्रबंधन");

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

/* Fix modal blinking and stability */
.modal {
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
}

.modal.fade .modal-dialog {
    transition: transform 0.2s ease-out;
    transform: translate(0, -25px);
}

.modal.show .modal-dialog {
    transform: translate(0, 0);
}

.modal-backdrop {
    transition: opacity 0.15s linear;
}

.modal-backdrop.fade {
    opacity: 0;
}

.modal-backdrop.show {
    opacity: 0.5;
}

/* Prevent body scroll when modal is open */
.modal-open {
    overflow: hidden;
    padding-right: 0 !important;
}

/* Delete confirmation modal specific styles */
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
    .table th:nth-child(3), .table td:nth-child(3), /* Position */
    .table th:nth-child(4), .table td:nth-child(4), /* Birth Date */
    .table th:nth-child(5), .table td:nth-child(5) /* Sort Order */ {
        display: none; /* Hide some columns on small screens */
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

    .delete-modal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
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
                <i class="fas fa-user-tie"></i> शंकराचार्य सामग्री प्रबंधन
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="shankaracharya_content.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> वापस
                    </a>
                <?php else: ?>
                    <a href="shankaracharya_content.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> नई सामग्री जोड़ें
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
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "नई सामग्री जोड़ें" : "सामग्री संपादित करें"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="shankaracharya_id" value="<?php echo $shankaracharya['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="title" class="form-label">शीर्षक <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($shankaracharya['title'] ?? 'जगद्गुरु शंकराचार्य स्वामी निश्चलानंद सरस्वती जी महाराज'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">विवरण <span class="text-danger">*</span></label>
                            <textarea class="form-control ckeditor" id="description" name="description" rows="6" required><?php echo htmlspecialchars($shankaracharya['description'] ?? "पुरी पीठ के वर्तमान पीठाधीश्वर जगद्गुरु शंकराचार्य स्वामी निश्चलानंद सरस्वती जी महाराज हैं। यहाँ कुछ मुख्य बातें दी गई हैं: वह पुरी, ओडिशा में पूर्वाम्नाय श्री गोवर्धन पीठ के 145वें शंकराचार्य हैं।वह 1992 से इस पद पर आसीन हैं।उनका जन्म 30 जून 1943 को बिहार के मधुबनी जिले में हुआ था।वे अद्वैत वेदांत के एक प्रतिष्ठित विद्वान हैं"); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="position" class="form-label">पद</label>
                                <input type="text" class="form-control" id="position" name="position" value="<?php echo htmlspecialchars($shankaracharya['position'] ?? 'पूर्वाम्नाय श्री गोवर्धन पीठ के 145वें शंकराचार्य'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="tenure_start" class="form-label">कार्यकाल प्रारंभ</label>
                                <input type="text" class="form-control" id="tenure_start" name="tenure_start" value="<?php echo htmlspecialchars($shankaracharya['tenure_start'] ?? '1992'); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label">जन्म तिथि</label>
                                <input type="text" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($shankaracharya['birth_date'] ?? '30 जून 1943'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="birth_place" class="form-label">जन्म स्थान</label>
                                <input type="text" class="form-control" id="birth_place" name="birth_place" value="<?php echo htmlspecialchars($shankaracharya['birth_place'] ?? 'बिहार के मधुबनी जिले'); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="specialization" class="form-label">विशेषज्ञता</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo htmlspecialchars($shankaracharya['specialization'] ?? 'अद्वैत वेदांत के प्रतिष्ठित विद्वान'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="image" class="form-label">छवि</label>
                                <input type="file" class="form-control image-upload" id="image" name="image" accept="image/*">
                                <?php if ($action === 'edit' && !empty($shankaracharya['image'])): ?>
                                    <div class="mt-2 image-preview">
                                        <img src="<?php echo SITE_URL; ?>/img/shankaracharya/<?php echo htmlspecialchars($shankaracharya['image']); ?>" alt="Current Image" class="img-thumbnail" style="max-height: 200px;">
                                        <p class="text-muted mt-1">नया छवि अपलोड करने पर वर्तमान छवि बदल जाएगा।</p>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-2 image-preview"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">क्रम</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo htmlspecialchars($shankaracharya['sort_order'] ?? 1); ?>" min="1">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">स्थिति</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($shankaracharya['status']) && $shankaracharya['status'] === 'active') ? 'selected' : ''; ?>>सक्रिय</option>
                                    <option value="inactive" <?php echo (isset($shankaracharya['status']) && $shankaracharya['status'] === 'inactive') ? 'selected' : ''; ?>>निष्क्रिय</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "जोड़ें" : "अपडेट करें"; ?>
                            </button>
                            <a href="shankaracharya_content.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> रद्द करें
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-user-tie"></i> शंकराचार्य सामग्री
                </div>
                <div class="card-body">
                    <?php if (count($shankaracharyaItems) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>छवि</th>
                                        <th>शीर्षक</th>
                                        <th class="d-none d-md-table-cell">पद</th>
                                        <th class="d-none d-md-table-cell">जन्म तिथि</th>
                                        <th class="d-none d-md-table-cell">क्रम</th>
                                        <th>स्थिति</th>
                                        <th>कार्य</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shankaracharyaItems as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo SITE_URL; ?>/img/shankaracharya/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user-tie text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($item['title'], 0, 30)) . (strlen($item['title']) > 30 ? '...' : ''); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars(substr($item['position'], 0, 30)) . (strlen($item['position']) > 30 ? '...' : ''); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($item['birth_date']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo $item['sort_order']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo $item['status'] === 'active' ? 'सक्रिय' : 'निष्क्रिय'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="shankaracharya_content.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="संपादित करें">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="shankaracharya_id" value="<?php echo $item['id']; ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="स्थिति बदलें">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['title'])); ?>')" title="हटाएं">
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
                            <i class="fas fa-info-circle"></i> कोई सामग्री नहीं मिली।
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
                    <i class="fas fa-exclamation-triangle"></i> पुष्टि करें
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h6>क्या आप वाकई इस सामग्री को हटाना चाहते हैं?</h6>
                    <p class="text-muted mb-2"><strong id="deleteItemTitle"></strong></p>
                    <p class="text-danger small">
                        <i class="fas fa-exclamation-circle"></i> यह क्रिया पूर्ववत नहीं की जा सकती है।
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> रद्द करें
                </button>
                <form method="post" class="d-inline" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="shankaracharya_id" id="deleteItemId" value="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> हटाएं
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Prevent multiple modal instances and handle delete modal properly
function showDeleteModal(itemId, itemTitle) {
    // Close any existing modals first
    const existingModals = document.querySelectorAll('.modal.show');
    existingModals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    });
    
    // Set the item details in the modal
    document.getElementById('deleteItemId').value = itemId;
    document.getElementById('deleteItemTitle').textContent = itemTitle;
    
    // Show the delete modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'), {
        backdrop: 'static',
        keyboard: false
    });
    deleteModal.show();
}

// Initialize tooltips if Bootstrap is available
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Handle image preview
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
                        <p class="text-muted mt-1">नई छवि का पूर्वावलोकन</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
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
    
    // Confirm toggle status changes
    const toggleForms = document.querySelectorAll('form input[value="toggle"]');
    toggleForms.forEach(input => {
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!confirm('क्या आप वाकई स्थिति बदलना चाहते हैं?')) {
                    e.preventDefault();
                }
            });
        }
    });
});

// Handle modal cleanup
document.addEventListener('hidden.bs.modal', function (e) {
    // Remove modal backdrop if it exists
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        backdrop.remove();
    });
    
    // Remove modal-open class from body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});

// Prevent form double submission
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(btn => {
                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> प्रसंस्करण...';
                
                // Re-enable after 3 seconds in case of error
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 3000);
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
