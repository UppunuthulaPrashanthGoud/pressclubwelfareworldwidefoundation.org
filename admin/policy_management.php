<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$policy_type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'disclaimer';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';

// Policy configuration
$policyConfig = [
    'disclaimer' => [
        'table' => 'disclaimer_content',
        'title' => 'Disclaimer',
        'icon' => 'fa-shield-alt'
    ],
    'privacy' => [
        'table' => 'privacy_policy_content',
        'title' => 'Privacy Policy',
        'icon' => 'fa-user-shield'
    ],
    'refund' => [
        'table' => 'refund_policy_content',
        'title' => 'Refund Policy',
        'icon' => 'fa-undo-alt'
    ],
    'shipping' => [
        'table' => 'shipping_policy_content',
        'title' => 'Shipping Policy',
        'icon' => 'fa-truck'
    ],
    'terms' => [
        'table' => 'terms_conditions_content',
        'title' => 'Terms & Conditions',
        'icon' => 'fa-file-contract'
    ]
];

// Validate policy type
if (!isset($policyConfig[$policy_type])) {
    $policy_type = 'disclaimer';
}

$currentTable = $policyConfig[$policy_type]['table'];
$currentTitle = $policyConfig[$policy_type]['title'];

try {
    $db = getDbConnection();
} catch (PDOException $e) {
    logError("Database connection error: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
        logError($error);
    } elseif (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $section_title = isset($_POST['section_title']) ? sanitizeInput($_POST['section_title']) : '';
            $section_content = isset($_POST['section_content']) ? $_POST['section_content'] : '';
            $section_icon = isset($_POST['section_icon']) ? sanitizeInput($_POST['section_icon']) : '';
            $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 1;
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            $type = isset($_POST['policy_type']) ? sanitizeInput($_POST['policy_type']) : $policy_type;
            
            if (empty($section_title) || empty($section_content)) {
                $error = "Section title and content are required.";
            } else {
                try {
                    $table = $policyConfig[$type]['table'];
                    
                    if ($formAction === 'add') {
                        $stmt = $db->prepare("INSERT INTO {$table} (section_title, section_content, section_icon, sort_order, status, created_at) 
                                              VALUES (:title, :content, :icon, :sort_order, :status, NOW())");
                        $stmt->bindParam(':title', $section_title);
                        $stmt->bindParam(':content', $section_content);
                        $stmt->bindParam(':icon', $section_icon);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        $success = "Section added successfully.";
                    } else {
                        $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
                        $stmt = $db->prepare("UPDATE {$table} SET section_title = :title, section_content = :content, 
                                              section_icon = :icon, sort_order = :sort_order, status = :status, updated_at = NOW() WHERE id = :id");
                        $stmt->bindParam(':title', $section_title);
                        $stmt->bindParam(':content', $section_content);
                        $stmt->bindParam(':icon', $section_icon);
                        $stmt->bindParam(':sort_order', $sort_order);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $section_id);
                        $stmt->execute();
                        $success = "Section updated successfully.";
                    }
                    header("Location: policy_management.php?type={$type}&success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                    logError($error);
                }
            }
        }
        
        if ($formAction === 'delete') {
            $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
            $type = isset($_POST['policy_type']) ? sanitizeInput($_POST['policy_type']) : $policy_type;
            try {
                $table = $policyConfig[$type]['table'];
                $stmt = $db->prepare("DELETE FROM {$table} WHERE id = :id");
                $stmt->bindParam(':id', $section_id);
                $stmt->execute();
                $success = "Section deleted successfully.";
                header("Location: policy_management.php?type={$type}&success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
                logError($error);
            }
        }
        
        if ($formAction === 'toggle') {
            $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
            $type = isset($_POST['policy_type']) ? sanitizeInput($_POST['policy_type']) : $policy_type;
            try {
                $table = $policyConfig[$type]['table'];
                $stmt = $db->prepare("SELECT status FROM {$table} WHERE id = :id");
                $stmt->bindParam(':id', $section_id);
                $stmt->execute();
                $currentData = $stmt->fetch();
                if (!$currentData) {
                    throw new Exception("Section not found.");
                }
                $newStatus = $currentData['status'] === 'active' ? 'inactive' : 'active';
                $stmt = $db->prepare("UPDATE {$table} SET status = :status, updated_at = NOW() WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $section_id);
                $stmt->execute();
                $success = "Section status updated successfully.";
                header("Location: policy_management.php?type={$type}&success=" . urlencode($success));
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
        $stmt = $db->prepare("SELECT * FROM {$currentTable} WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $section = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$section) {
            $error = "Section not found.";
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
    $stmt = $db->prepare("SELECT COUNT(*) FROM {$currentTable}");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM {$currentTable} ORDER BY sort_order ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError("Database query error: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $sections = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add New Section - {$currentTitle}" :
             (($action === 'edit') ? "Edit Section - {$currentTitle}" : "Policy Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<style>
/* Policy Management Styles */
.policy-tabs {
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 2rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.policy-tab {
    padding: 0.75rem 1.5rem;
    background: #f8f9fa;
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.policy-tab:hover {
    background: #e9ecef;
    color: #495057;
}

.policy-tab.active {
    background: #fff;
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.policy-tab i {
    font-size: 1.1rem;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table th, .table td {
    vertical-align: middle;
    font-size: 14px;
    padding: 12px 8px;
}

.btn-group .btn {
    padding: 6px 10px;
    font-size: 12px;
}

.badge {
    font-size: 12px;
    padding: 0.35em 0.65em;
}

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

.modal-open {
    overflow: hidden;
    padding-right: 0 !important;
}

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
    .policy-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
        gap: 0;
    }
    
    .policy-tab {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        white-space: nowrap;
    }
    
    .table th:nth-child(2), .table td:nth-child(2) { /* Content */
        display: none;
    }
    
    .table th, .table td {
        font-size: 12px;
        padding: 8px 6px;
    }
    
    .btn-group .btn {
        padding: 4px 8px;
        font-size: 11px;
    }
    
    .delete-modal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
}

@media (max-width: 576px) {
    .policy-tab i {
        display: none;
    }
    
    .table th, .table td {
        font-size: 11px;
        padding: 6px 4px;
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .btn-group .btn {
        width: 100%;
    }
}
</style>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-file-alt"></i> Policy Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="policy_management.php?type=<?php echo $policy_type; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                <?php else: ?>
                    <a href="policy_management.php?action=add&type=<?php echo $policy_type; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Section
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
            <!-- Policy Type Tabs -->
            <div class="policy-tabs">
                <?php foreach ($policyConfig as $key => $config): ?>
                    <a href="policy_management.php?type=<?php echo $key; ?>" 
                       class="policy-tab <?php echo $policy_type === $key ? 'active' : ''; ?>">
                        <i class="fas <?php echo $config['icon']; ?>"></i>
                        <span><?php echo $config['title']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> 
                    <?php echo ($action === 'add') ? "Add New Section - {$currentTitle}" : "Edit Section - {$currentTitle}"; ?>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="policy_type" value="<?php echo $policy_type; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="section_title" class="form-label">Section Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="section_title" name="section_title" 
                                       value="<?php echo htmlspecialchars($section['section_title'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="section_icon" class="form-label">Icon Class</label>
                                <input type="text" class="form-control" id="section_icon" name="section_icon" 
                                       value="<?php echo htmlspecialchars($section['section_icon'] ?? 'fa-info-circle'); ?>" 
                                       placeholder="e.g., fa-info-circle">
                                <small class="text-muted">Font Awesome icon class</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="section_content" class="form-label">Section Content <span class="text-danger">*</span></label>
                            <textarea class="form-control ckeditor" id="section_content" name="section_content" rows="8" required><?php echo htmlspecialchars($section['section_content'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                       value="<?php echo htmlspecialchars($section['sort_order'] ?? 1); ?>" min="1">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (isset($section['status']) && $section['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($section['status']) && $section['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Add Section" : "Update Section"; ?>
                            </button>
                            <a href="policy_management.php?type=<?php echo $policy_type; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas <?php echo $policyConfig[$policy_type]['icon']; ?>"></i> <?php echo $currentTitle; ?> Sections
                </div>
                <div class="card-body">
                    <?php if (count($sections) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Title</th>
                                        <th class="d-none d-md-table-cell" style="width: 40%;">Content Preview</th>
                                        <th style="width: 10%;">Sort</th>
                                        <th style="width: 10%;">Status</th>
                                        <th style="width: 10%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sections as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($item['section_icon'])): ?>
                                                    <i class="fas <?php echo htmlspecialchars($item['section_icon']); ?> me-2"></i>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($item['section_title']); ?>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?php echo htmlspecialchars(substr(strip_tags($item['section_content']), 0, 100)) . 
                                                     (strlen(strip_tags($item['section_content'])) > 100 ? '...' : ''); ?>
                                            </td>
                                            <td><?php echo $item['sort_order']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="policy_management.php?action=edit&type=<?php echo $policy_type; ?>&id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle">
                                                        <input type="hidden" name="policy_type" value="<?php echo $policy_type; ?>">
                                                        <input type="hidden" name="section_id" value="<?php echo $item['id']; ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="showDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['section_title'])); ?>', '<?php echo $policy_type; ?>')" 
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
                            <i class="fas fa-info-circle"></i> No sections found for <?php echo $currentTitle; ?>. 
                            <a href="policy_management.php?action=add&type=<?php echo $policy_type; ?>">Add the first section</a>.
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?type=<?php echo $policy_type; ?>&page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?type=<?php echo $policy_type; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?type=<?php echo $policy_type; ?>&page=<?php echo $page + 1; ?>">
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
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h6>Are you sure you want to delete this section?</h6>
                    <p class="text-muted mb-2"><strong id="deleteItemTitle"></strong></p>
                    <p class="text-danger small">
                        <i class="fas fa-exclamation-circle"></i> This action cannot be undone.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <form method="post" class="d-inline" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="policy_type" id="deletePolicyType" value="">
                    <input type="hidden" name="section_id" id="deleteItemId" value="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteModal(itemId, itemTitle, policyType) {
    const existingModals = document.querySelectorAll('.modal.show');
    existingModals.forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            bsModal.hide();
        }
    });
    
    document.getElementById('deleteItemId').value = itemId;
    document.getElementById('deleteItemTitle').textContent = itemTitle;
    document.getElementById('deletePolicyType').value = policyType;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'), {
        backdrop: 'static',
        keyboard: false
    });
    deleteModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    const alerts = document.querySelectorAll('.alert:not(.alert-info)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    const toggleForms = document.querySelectorAll('form input[value="toggle"]');
    toggleForms.forEach(input => {
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to toggle the status?')) {
                    e.preventDefault();
                }
            });
        }
    });
});

document.addEventListener('hidden.bs.modal', function (e) {
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        backdrop.remove();
    });
    
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(btn => {
                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
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