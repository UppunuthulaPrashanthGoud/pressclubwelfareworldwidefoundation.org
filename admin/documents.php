<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Simple PDF upload function
function uploadPDF($file, $targetDir) {
    $result = ['success' => false, 'message' => '', 'filename' => ''];

    // Create directory if not exists
    $targetPath = __DIR__ . '/../' . $targetDir;
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
    }

    // Check upload error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload error.';
        return $result;
    }

    // Check if PDF
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileExt !== 'pdf') {
        $result['message'] = 'Only PDF files are allowed.';
        return $result;
    }

    // Check file size (10MB max)
    if ($file['size'] > 10 * 1024 * 1024) {
        $result['message'] = 'File size must not exceed 10MB.';
        return $result;
    }

    // Generate unique filename
    $newFileName = uniqid() . '.pdf';
    $targetFile = $targetPath . '/' . $newFileName;

    // Move file
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $result['success'] = true;
        $result['filename'] = $newFileName;
    } else {
        $result['message'] = 'Failed to upload file.';
    }

    return $result;
}

// Initialize
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$success = $_GET['success'] ?? '';
$error = '';

try {
    $db = getDbConnection();
} catch (PDOException $e) {
    logError("Database connection error: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $documents = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } elseif (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add') {
            $title = sanitizeInput($_POST['title'] ?? '');
            
            if (empty($title)) {
                $error = "Title is required.";
            } elseif (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
                $error = "PDF file is required.";
            } else {
                try {
                    $uploadResult = uploadPDF($_FILES['pdf_file'], 'img/documents');
                    if ($uploadResult['success']) {
                        $stmt = $db->prepare("INSERT INTO documents (title, file_path, created_at) VALUES (?, ?, NOW())");
                        $stmt->execute([$title, 'img/documents/' . $uploadResult['filename']]);
                        header("Location: documents.php?success=" . urlencode("Document added successfully."));
                        exit;
                    } else {
                        $error = $uploadResult['message'];
                    }
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
        
        elseif ($formAction === 'delete') {
            $doc_id = (int)$_POST['doc_id'];
            try {
                // Get file path before deletion
                $stmt = $db->prepare("SELECT file_path FROM documents WHERE id = ?");
                $stmt->execute([$doc_id]);
                $doc = $stmt->fetch();
                
                if ($doc) {
                    // Delete from database
                    $stmt = $db->prepare("DELETE FROM documents WHERE id = ?");
                    $stmt->execute([$doc_id]);
                    
                    // Delete file
                    $filePath = __DIR__ . '/../' . $doc['file_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                    header("Location: documents.php?success=" . urlencode("Document deleted successfully."));
                    exit;
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
        
        elseif ($formAction === 'toggle') {
            $doc_id = (int)$_POST['doc_id'];
            try {
                $stmt = $db->prepare("SELECT status FROM documents WHERE id = ?");
                $stmt->execute([$doc_id]);
                $current = $stmt->fetch();
                
                if ($current) {
                    $newStatus = $current['status'] === 'active' ? 'inactive' : 'active';
                    $stmt = $db->prepare("UPDATE documents SET status = ? WHERE id = ?");
                    $stmt->execute([$newStatus, $doc_id]);
                    
                    header("Location: documents.php?success=" . urlencode("Status updated successfully."));
                    exit;
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get documents list
try {
    $stmt = $db->prepare("SELECT * FROM documents ORDER BY created_at DESC");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $documents = [];
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-file-pdf"></i> Document Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add'): ?>
                    <a href="documents.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="documents.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Document
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-plus"></i> Add New Document
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF()); ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Document Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   placeholder="e.g., Annual Report 2024">
                        </div>
                        
                        <div class="mb-3">
                            <label for="pdf_file" class="form-label">PDF File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="pdf_file" name="pdf_file" 
                                   accept=".pdf" required>
                            <div class="form-text">Only PDF files. Maximum size: 10MB</div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add
                            </button>
                            <a href="documents.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-file-pdf"></i> Document List
                </div>
                <div class="card-body">
                    <?php if (count($documents) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-pdf text-danger"></i>
                                                <?php echo htmlspecialchars($doc['title']); ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $doc['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo $doc['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($doc['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo SITE_URL . '/' . $doc['file_path']; ?>" 
                                                       class="btn btn-sm btn-info" target="_blank" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF()); ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="showDeleteModal(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>')" 
                                                            title="Delete">
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
                            <i class="fas fa-info-circle"></i> No documents found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirm
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-trash-alt text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h6>Are you sure you want to delete this document?</h6>
                <p class="text-muted"><strong id="deleteItemTitle"></strong></p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-circle"></i> This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form method="post" class="d-inline" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="doc_id" id="deleteItemId" value="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF()); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteModal(itemId, itemTitle) {
    document.getElementById('deleteItemId').value = itemId;
    document.getElementById('deleteItemTitle').textContent = itemTitle;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
</script>

<?php include 'includes/footer.php'; ?>