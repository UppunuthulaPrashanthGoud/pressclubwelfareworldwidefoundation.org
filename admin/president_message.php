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
        $result['message'] = 'File upload error.';
        return $result;
    }

    $fileName = basename($file['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $fileExt;
    $targetFile = $targetPath . '/' . $newFileName;

    // Validate file type
    if (!in_array($fileExt, $allowedTypes)) {
        $result['message'] = 'File type not allowed. Only ' . implode(', ', $allowedTypes) . ' are permitted.';
        return $result;
    }

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        $result['message'] = 'File size must not exceed 5MB.';
        return $result;
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $result['success'] = true;
        $result['filename'] = $newFileName;
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
    // Verify table existence
    $stmt = $db->query("SHOW TABLES LIKE 'president_message'");
    if ($stmt->rowCount() == 0) {
        error_log("Table 'president_message' does not exist in database.");
        $error = "Table 'president_message' not found in database.";
        $messages = [];
        $totalPages = 0;
    }
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $messages = [];
    $totalPages = 0;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $president_name = isset($_POST['president_name']) ? sanitizeInput($_POST['president_name']) : '';
            $designation = isset($_POST['designation']) ? sanitizeInput($_POST['designation']) : '';
            $message = isset($_POST['message']) ? $_POST['message'] : ''; // Message may contain HTML, so don't sanitize
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            if (empty($president_name) || empty($designation) || empty($message)) {
                $error = "President's name, designation, and message are required.";
            } else {
                try {
                    $image = null;
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['image'], 'img/president');
                        if ($uploadResult['success']) {
                            $image = $uploadResult['filename'];
                        } else {
                            throw new Exception($uploadResult['message']);
                        }
                    }
                    
                    if ($formAction === 'add') {
                        $stmt = $db->prepare("INSERT INTO president_message (president_name, designation, message, image, status, created_at) 
                                              VALUES (:president_name, :designation, :message, :image, :status, NOW())");
                        $stmt->bindParam(':president_name', $president_name);
                        $stmt->bindParam(':designation', $designation);
                        $stmt->bindParam(':message', $message);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        $success = "President's message added successfully.";
                    } else {
                        $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
                        $stmt = $db->prepare("SELECT image FROM president_message WHERE id = :id");
                        $stmt->bindParam(':id', $message_id);
                        $stmt->execute();
                        $currentData = $stmt->fetch();
                        if (!$currentData) {
                            throw new Exception("Message not found.");
                        }
                        if (empty($image)) {
                            $image = $currentData['image'];
                        } else {
                            if (!empty($currentData['image'])) {
                                $oldImagePath = __DIR__ . '/../img/president/' . $currentData['image'];
                                if (file_exists($oldImagePath)) {
                                    unlink($oldImagePath);
                                }
                            }
                        }
                        $stmt = $db->prepare("UPDATE president_message SET president_name = :president_name, designation = :designation, 
                                              message = :message, image = :image, status = :status WHERE id = :id");
                        $stmt->bindParam(':president_name', $president_name);
                        $stmt->bindParam(':designation', $designation);
                        $stmt->bindParam(':message', $message);
                        $stmt->bindParam(':image', $image);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $message_id);
                        $stmt->execute();
                        $success = "President's message updated successfully.";
                    }
                    header("Location: president_message.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
        if ($formAction === 'delete') {
            $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT image FROM president_message WHERE id = :id");
                $stmt->bindParam(':id', $message_id);
                $stmt->execute();
                $data = $stmt->fetch();
                $stmt = $db->prepare("DELETE FROM president_message WHERE id = :id");
                $stmt->bindParam(':id', $message_id);
                $stmt->execute();
                if (!empty($data['image'])) {
                    $imagePath = __DIR__ . '/../img/president/' . $data['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                $success = "President's message deleted successfully.";
                header("Location: president_message.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
        if ($formAction === 'toggle') {
            $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT status FROM president_message WHERE id = :id");
                $stmt->bindParam(':id', $message_id);
                $stmt->execute();
                $currentData = $stmt->fetch();
                if (!$currentData) {
                    throw new Exception("Message not found.");
                }
                $newStatus = $currentData['status'] === 'active' ? 'inactive' : 'active';
                $stmt = $db->prepare("UPDATE president_message SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $message_id);
                $stmt->execute();
                $success = "President's message status updated successfully.";
                header("Location: president_message.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get data for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM president_message WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$message) {
            $error = "Message not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        error_log("Edit query error: " . $e->getMessage());
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Get data for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM president_message");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    if ($totalRecords == 0) {
        error_log("No records found in president_message table.");
    }
    $stmt = $db->prepare("SELECT * FROM president_message ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched " . count($messages) . " records from president_message.");
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    error_log("Database query error for president_message: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $messages = [];
    $totalPages = 0;
}

// Set page title
// $pageTitle = ($action === 'add') ? "Add New President's Message" :
//              (($action === 'edit') ? "Edit President's Message" : "President's Message Management");

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
    font-size: 14px; /* Smaller font size for better fit */
    padding: 8px; /* Reduced padding */
}

.table img {
    width: 40px; /* Smaller thumbnails */
    height: 40px;
    object-fit: cover;
}

.btn-group .btn {
    padding: 4px 8px; /* Smaller buttons */
    font-size: 12px;
}

.badge {
    font-size: 12px;
}

@media (max-width: 768px) {
    .table th:nth-child(3), .table td:nth-child(3), /* Designation */
    .table th:nth-child(4), .table td:nth-child(4) /* Message */ {
        display: none; /* Hide Designation and Message on small screens */
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
                <i class="fas fa-comment"></i> President's Message Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="president_message.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="president_message.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Message
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars_decode($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars_decode($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "Add New Message" : "Edit Message"; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="president_name" class="form-label">President's Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="president_name" name="president_name" value="<?php echo htmlspecialchars_decode($message['president_name'] ?? 'Narayan Mishra'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="designation" class="form-label">Designation <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="designation" name="designation" value="<?php echo htmlspecialchars_decode($message['designation'] ?? 'President'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control ckeditor" id="message" name="message" rows="8" required><?php echo $message['message'] ?? ''; ?></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control image-upload" id="image" name="image" accept="image/*">
                                <?php if ($action === 'edit' && !empty($message['image'])): ?>
                                    <div class="mt-2 image-preview">
                                        <img src="<?php echo SITE_URL; ?>/img/president/<?php echo htmlspecialchars($message['image']); ?>" alt="Current Image" class="img-thumbnail" style="max-height: 200px;">
                                        <p class="text-muted mt-1">Uploading a new image will replace the current image.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-2 image-preview"></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($message['status']) && $message['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($message['status']) && $message['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Add" : "Update"; ?>
                            </button>
                            <a href="president_message.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-comment"></i> President's Messages
                </div>
                <div class="card-body">
                    <?php if (count($messages) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>President's Name</th>
                                        <th class="d-none d-md-table-cell">Designation</th>
                                        <th class="d-none d-md-table-cell">Message</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo SITE_URL; ?>/img/president/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars_decode($item['president_name']); ?>" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars_decode($item['president_name']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars_decode($item['designation']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars_decode(substr(strip_tags($item['message']), 0, 50)) . (strlen(strip_tags($item['message'])) > 50 ? '...' : ''); ?></td>
                                            <td>
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo $item['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="president_message.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="message_id" value="<?php echo $item['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger confirm-delete" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $item['id']; ?>" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="modal fade" id="deleteModal<?php echo $item['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-sm">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Confirm</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the message from <strong><?php echo htmlspecialchars_decode($item['president_name']); ?></strong>?
                                                                <p class="text-danger mt-2">This action cannot be undone.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post" class="d-inline">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="message_id" value="<?php echo $item['id']; ?>">
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
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
                            <i class="fas fa-info-circle"></i> No messages found.
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