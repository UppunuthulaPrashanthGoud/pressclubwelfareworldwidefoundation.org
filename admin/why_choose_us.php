<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';

try {
    $db = getDbConnection();
    // Verify table existence
    $stmt = $db->query("SHOW TABLES LIKE 'why_choose_us'");
    if ($stmt->rowCount() == 0) {
        logError("Table 'why_choose_us' does not exist in database.");
        $error = "Table 'why_choose_us' not found in database. Please import the SQL file.";
        $reasons = [];
        $totalPages = 0;
    }
} catch (PDOException $e) {
    logError("Database connection error: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $reasons = [];
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
            $title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
            $description = isset($_POST['description']) ? sanitizeInput($_POST['description']) : ''; // Plain text description
            $icon = isset($_POST['icon']) ? sanitizeInput($_POST['icon']) : 'fas fa-check-circle';
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 1;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            if (empty($title) || empty($description)) {
                $error = "Title and description are required.";
            } else {
                try {
                    if ($formAction === 'add') {
                        $stmt = $db->prepare("INSERT INTO why_choose_us (title, description, icon, sort_order, status, created_at) 
                                              VALUES (:title, :description, :icon, :sort_order, :status, NOW())");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':icon', $icon);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        $success = "Reason added successfully.";
                    } else {
                        $reason_id = isset($_POST['reason_id']) ? (int)$_POST['reason_id'] : 0;
                        $stmt = $db->prepare("UPDATE why_choose_us SET title = :title, description = :description, 
                                              icon = :icon, sort_order = :sort_order, status = :status, updated_at = NOW() WHERE id = :id");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':icon', $icon);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $reason_id);
                        $stmt->execute();
                        $success = "Reason updated successfully.";
                    }
                    header("Location: why_choose_us.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                    logError($error);
                }
            }
        }
        if ($formAction === 'delete') {
            $reason_id = isset($_POST['reason_id']) ? (int)$_POST['reason_id'] : 0;
            try {
                $stmt = $db->prepare("DELETE FROM why_choose_us WHERE id = :id");
                $stmt->bindParam(':id', $reason_id);
                $stmt->execute();
                $success = "Reason deleted successfully.";
                header("Location: why_choose_us.php?success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
                logError($error);
            }
        }
        if ($formAction === 'toggle') {
            $reason_id = isset($_POST['reason_id']) ? (int)$_POST['reason_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT status FROM why_choose_us WHERE id = :id");
                $stmt->bindParam(':id', $reason_id);
                $stmt->execute();
                $currentData = $stmt->fetch();
                if (!$currentData) {
                    throw new Exception("Content not found.");
                }
                $newStatus = $currentData['status'] === 'active' ? 'inactive' : 'active';
                $stmt = $db->prepare("UPDATE why_choose_us SET status = :status, updated_at = NOW() WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $reason_id);
                $stmt->execute();
                $success = "Status updated successfully.";
                header("Location: why_choose_us.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
                logError($error);
            }
        }
    }
}

// Get data for edit
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM why_choose_us WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $reason = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reason) {
            $error = "Content not found.";
            $action = 'list';
        }
    } catch (PDOException $e) {
        logError("Edit query error: " . $e->getMessage());
        $error = "Database error: " . $e->getMessage();
        $action = 'list';
    }
}

// Get data for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM why_choose_us");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT * FROM why_choose_us ORDER BY sort_order ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reasons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    // If table doesn't exist yet, avoid crash
    $reasons = [];
    $totalPages = 0;
}

// Set page title
$pageTitle = ($action === 'add') ? "Add New Reason" :
             (($action === 'edit') ? "Edit Reason" : "Manage Why Choose Us");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<style>
/* Responsive table styles */
.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.table th, .table td { vertical-align: middle; font-size: 14px; padding: 8px; }
.btn-group .btn { padding: 4px 8px; font-size: 12px; }
.badge { font-size: 12px; }
.icon-preview { font-size: 20px; color: var(--primary-color); }

/* Common Admin Styles */
.modal { backdrop-filter: none; -webkit-backdrop-filter: none; }
.modal.fade .modal-dialog { transition: transform 0.2s ease-out; transform: translate(0, -25px); }
.modal.show .modal-dialog { transform: translate(0, 0); }
.modal-backdrop { transition: opacity 0.15s linear; }
.modal-backdrop.fade { opacity: 0; }
.modal-backdrop.show { opacity: 0.5; }
.modal-open { overflow: hidden; padding-right: 0 !important; }

/* Delete Modal */
.delete-modal { z-index: 1055; }
.delete-modal .modal-dialog { margin: 1.75rem auto; max-width: 400px; }
.delete-modal .modal-content { border: none; border-radius: 8px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
.delete-modal .modal-header { background-color: #dc3545; color: white; border-bottom: none; border-radius: 8px 8px 0 0; }
.delete-modal .modal-header .btn-close { filter: invert(1); opacity: 0.8; }
.delete-modal .modal-body { padding: 1.5rem; text-align: center; }
.delete-modal .modal-footer { border-top: 1px solid #dee2e6; padding: 1rem 1.5rem; justify-content: center; gap: 10px; }

@media (max-width: 768px) {
    .table th:nth-child(2), .table td:nth-child(2), /* Description */
    .table th:nth-child(4), .table td:nth-child(4) /* Sort Order */ { display: none; }
    .table th, .table td { font-size: 12px; padding: 6px; }
    .btn-group .btn { padding: 3px 6px; font-size: 10px; }
    .delete-modal .modal-dialog { margin: 1rem; max-width: calc(100% - 2rem); }
}
</style>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-question-circle"></i> Manage "Why Choose Us"
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="why_choose_us.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="why_choose_us.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Reason
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
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> 
                    <?php echo ($action === 'add') ? "Add New Reason" : "Edit Reason"; ?>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="reason_id" value="<?php echo $reason['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($reason['title'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="icon" class="form-label">Icon Class (FontAwesome) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="<?php echo htmlspecialchars($reason['icon'] ?? 'fas fa-check-circle'); ?>"></i></span>
                                    <input type="text" class="form-control" id="icon" name="icon" 
                                           value="<?php echo htmlspecialchars($reason['icon'] ?? 'fas fa-check-circle'); ?>" required
                                           placeholder="e.g., fas fa-globe">
                                </div>
                                <small class="text-muted">Use FontAwesome classes (e.g., fas fa-shield-alt)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($reason['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo (int)($reason['sort_order'] ?? 1); ?>" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($reason['status']) && $reason['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($reason['status']) && $reason['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions text-end mt-4">
                            <a href="why_choose_us.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Reason
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- LIST VIEW -->
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-list"></i> Reasons List
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Icon</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reasons)): ?>
                                    <?php foreach ($reasons as $index => $item): ?>
                                        <tr>
                                            <td><?php echo $offset + $index + 1; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($item['description'], 0, 50)) . '...'; ?></td>
                                            <td><i class="<?php echo htmlspecialchars($item['icon']); ?> icon-preview"></i></td>
                                            <td><?php echo $item['sort_order']; ?></td>
                                            <td>
                                                <form method="post" class="d-inline status-form">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="reason_id" value="<?php echo $item['id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" class="badge <?php echo $item['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?> border-0" style="cursor: pointer;">
                                                        <?php echo ucfirst($item['status']); ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <a href="why_choose_us.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                                            data-id="<?php echo $item['id']; ?>" 
                                                            data-title="<?php echo htmlspecialchars($item['title']); ?>"
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle me-1"></i> No reasons found. Add one to get started.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="card-footer">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="why_choose_us.php?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="why_choose_us.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="why_choose_us.php?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade delete-modal" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Are you sure you want to delete this reason?</p>
                <p class="fw-bold text-danger mb-0" id="deleteItemTitle"></p>
                <p class="small text-muted mt-2">This action cannot be undone.</p>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="reason_id" id="deleteItemId">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete modal data
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemTitle').textContent = '"' + title + '"';
        });
    });

    // Handle status toggle confirmation
    const statusForms = document.querySelectorAll('.status-form');
    statusForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to change the status of this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Cleanup modals
    document.addEventListener('hidden.bs.modal', function () {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
    });
});
</script>

<?php include 'includes/footer.php'; ?>