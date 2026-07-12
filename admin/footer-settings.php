<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Ensure only admins can access
if (!isAdmin()) {
    redirectTo(ADMIN_URL . 'login.php');
}

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? htmlspecialchars_decode(sanitizeInput($_GET['success']), ENT_QUOTES) : '';
$error = '';

try {
    $db = getDbConnection();
    // Verify table existence
    $stmt = $db->query("SHOW TABLES LIKE 'footer_settings'");
    if ($stmt->rowCount() == 0) {
        logError("Table 'footer_settings' does not exist in database.");
        $error = "Table 'footer_settings' not found in the database.";
        $footerItems = [];
        $totalPages = 0;
    }
} catch (PDOException $e) {
    logError("Database connection error: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $footerItems = [];
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
            $section_name = isset($_POST['section_name']) ? sanitizeInput($_POST['section_name']) : '';
            $title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
            $content = isset($_POST['content']) ? $_POST['content'] : ''; // Content is HTML, so don't sanitize
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';

            if (empty($section_name) || empty($title)) {
                $error = "Section name and title are required.";
                logError($error);
            } else {
                try {
                    if ($formAction === 'add') {
                        $stmt = $db->prepare("INSERT INTO footer_settings (section_name, title, content, sort_order, status, created_at) 
                                              VALUES (:section_name, :title, :content, :sort_order, :status, NOW())");
                        $stmt->bindParam(':section_name', $section_name);
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':content', $content);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        $success = "Footer setting successfully added.";
                    } else {
                        $footer_id = isset($_POST['footer_id']) ? (int)$_POST['footer_id'] : 0;
                        $stmt = $db->prepare("SELECT id FROM footer_settings WHERE id = :id");
                        $stmt->bindParam(':id', $footer_id);
                        $stmt->execute();
                        if (!$stmt->fetch()) {
                            throw new Exception("Setting not found.");
                        }
                        $stmt = $db->prepare("UPDATE footer_settings SET section_name = :section_name, title = :title, content = :content, 
                                              sort_order = :sort_order, status = :status, updated_at = NOW() WHERE id = :id");
                        $stmt->bindParam(':section_name', $section_name);
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':content', $content);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $footer_id);
                        $stmt->execute();
                        $success = "Footer setting successfully updated.";
                    }
                    header("Location: footer-settings.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                    logError($error);
                }
            }
        }
        if ($formAction === 'delete') {
            $footer_id = isset($_POST['footer_id']) ? (int)$_POST['footer_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT id FROM footer_settings WHERE id = :id");
                $stmt->bindParam(':id', $footer_id);
                $stmt->execute();
                if (!$stmt->fetch()) {
                    throw new Exception("Setting not found.");
                }
                $stmt = $db->prepare("DELETE FROM footer_settings WHERE id = :id");
                $stmt->bindParam(':id', $footer_id);
                $stmt->execute();
                $success = "Footer setting successfully deleted.";
                header("Location: footer-settings.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                logError($error);
            }
        }
        if ($formAction === 'toggle') {
            $footer_id = isset($_POST['footer_id']) ? (int)$_POST['footer_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT status FROM footer_settings WHERE id = :id");
                $stmt->bindParam(':id', $footer_id);
                $stmt->execute();
                $currentData = $stmt->fetch();
                if (!$currentData) {
                    throw new Exception("Setting not found.");
                }
                $newStatus = $currentData['status'] === 'active' ? 'inactive' : 'active';
                $stmt = $db->prepare("UPDATE footer_settings SET status = :status, updated_at = NOW() WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $footer_id);
                $stmt->execute();
                $success = "Footer setting status successfully updated.";
                header("Location: footer-settings.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                logError($error);
            }
        }
    }
}

// Get data for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM footer_settings WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $footer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$footer) {
            $error = "Setting not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        logError("Edit query error: " . $e->getMessage());
        $error = "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $action = 'list';
    }
}

// Get data for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM footer_settings");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT * FROM footer_settings ORDER BY sort_order ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $footerItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError("Database query error for footer_settings: " . $e->getMessage());
    $error = "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    $footerItems = [];
    $totalPages = 0;
}

// Set page title
$pageTitle = ($action === 'add') ? "Add New Footer Setting" :
             (($action === 'edit') ? "Edit Footer Setting" : "Manage Footer Settings");

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
                <i class="fas fa-window-maximize"></i> Manage Footer Settings
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="footer-settings.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="footer-settings.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Setting
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "Add New Setting" : "Edit Setting"; ?>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="footer_id" value="<?php echo $footer['id']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="section_name" class="form-label">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="section_name" name="section_name" 
                                   value="<?php echo htmlspecialchars($footer['section_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            <small class="form-text text-muted">Name of the footer section (e.g., About Us, Contact)</small>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($footer['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            <small class="form-text text-muted">Title of the footer section</small>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control ckeditor" id="content" name="content" rows="6"><?php echo htmlspecialchars($footer['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <small class="form-text text-muted">Content of the footer section (HTML supported)</small>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo htmlspecialchars($footer['sort_order'] ?? 0, ENT_QUOTES, 'UTF-8'); ?>" min="0">
                                <small class="form-text text-muted">Order of the section (lower number appears first)</small>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($footer['status']) && $footer['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($footer['status']) && $footer['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Add" : "Update"; ?>
                            </button>
                            <a href="footer-settings.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-window-maximize"></i> Footer Settings
                </div>
                <div class="card-body">
                    <?php if (count($footerItems) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dataTable">
                                <thead>
                                    <tr>
                                        <th>Section Name</th>
                                        <th>Title</th>
                                        <th class="d-none d-md-table-cell">Content</th>
                                        <th class="d-none d-md-table-cell">Sort Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($footerItems as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['section_name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars(substr(strip_tags($item['content'] ?? ''), 0, 50)) . (strlen(strip_tags($item['content'] ?? '')) > 50 ? '...' : ''); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo $item['sort_order']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo $item['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="footer-settings.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="footer_id" value="<?php echo $item['id']; ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
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
                                                                Are you sure you want to delete the setting <strong><?php echo htmlspecialchars($item['title']); ?></strong>?
                                                                <p class="text-danger mt-2">This action cannot be undone.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form method="post" class="d-inline">
                                                                    <input type="hidden" name="action" value="delete">
                                                                    <input type="hidden" name="footer_id" value="<?php echo $item['id']; ?>">
                                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
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
                            <i class="fas fa-info-circle"></i> No footer settings found.
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