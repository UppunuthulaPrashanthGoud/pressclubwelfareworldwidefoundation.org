<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Helper function to thoroughly decode HTML entities (prevents "&&amp;amp;" loops)
function full_decode($string) {
    if ($string === null || $string === '') return $string;
    $decoded = html_entity_decode((string)$string, ENT_QUOTES, 'UTF-8');
    $iterations = 0;
    while ($decoded !== $string && strpos($decoded, '&') !== false && $iterations < 5) {
        $string = $decoded;
        $decoded = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        $iterations++;
    }
    return $decoded;
}

// Initialize variables
$activeTab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'awards';
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';

try {
    $db = getDbConnection();
    
    // Verify tables existence
    $stmt = $db->query("SHOW TABLES LIKE 'awards_list'");
    if ($stmt->rowCount() == 0) {
        logError("Table 'awards_list' does not exist in database.");
        $error = "Table 'awards_list' not found in database.";
    }
    
    $stmt = $db->query("SHOW TABLES LIKE 'venues_list'");
    if ($stmt->rowCount() == 0) {
        logError("Table 'venues_list' does not exist in database.");
        $error .= " Table 'venues_list' not found in database.";
    }
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
        $itemType = isset($_POST['item_type']) ? sanitizeInput($_POST['item_type']) : $activeTab;
        
        // Determine table name based on type
        if ($itemType === 'venues') {
            $tableName = 'venues_list';
            $nameField = 'venue_name';
            $itemLabel = 'Venue';
        } elseif ($itemType === 'categories') {
            $tableName = 'categories';
            $nameField = 'category_name';
            $itemLabel = 'Category';
        } else {
            $tableName = 'awards_list';
            $nameField = 'award_name';
            $itemLabel = 'Award';
        }
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            if (empty($name)) {
                $error = ucfirst($itemLabel) . " name is required.";
            } else {
                try {
                    if ($formAction === 'add') {
                        // Check for duplicates
                        $stmt = $db->prepare("SELECT COUNT(*) FROM $tableName WHERE $nameField = :name");
                        $stmt->bindParam(':name', $name);
                        $stmt->execute();
                        
                        if ($stmt->fetchColumn() > 0) {
                            throw new Exception("This $itemLabel already exists.");
                        }
                        
                        $stmt = $db->prepare("INSERT INTO $tableName ($nameField, status, created_at) 
                                              VALUES (:name, :status, NOW())");
                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        $success = ucfirst($itemLabel) . " added successfully.";
                    } else {
                        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
                        
                        // Check if item exists
                        $stmt = $db->prepare("SELECT id FROM $tableName WHERE id = :id");
                        $stmt->bindParam(':id', $item_id);
                        $stmt->execute();
                        
                        if (!$stmt->fetch()) {
                            throw new Exception(ucfirst($itemLabel) . " not found.");
                        }
                        
                        // Check for duplicates (excluding current item)
                        $stmt = $db->prepare("SELECT COUNT(*) FROM $tableName WHERE $nameField = :name AND id != :id");
                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':id', $item_id);
                        $stmt->execute();
                        
                        if ($stmt->fetchColumn() > 0) {
                            throw new Exception("This $itemLabel already exists.");
                        }
                        
                        $stmt = $db->prepare("UPDATE $tableName SET $nameField = :name, status = :status WHERE id = :id");
                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':status', $status);
                        $stmt->bindParam(':id', $item_id);
                        $stmt->execute();
                        $success = ucfirst($itemLabel) . " updated successfully.";
                    }
                    
                    header("Location: manage-awards-venues.php?tab=$itemType&success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                    logError($error);
                }
            }
        }
        
        if ($formAction === 'delete') {
            $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
            try {
                // Check if item is being used (you can add checks for nominations here)
                $stmt = $db->prepare("DELETE FROM $tableName WHERE id = :id");
                $stmt->bindParam(':id', $item_id);
                $stmt->execute();
                
                $success = ucfirst($itemLabel) . " deleted successfully.";
                header("Location: manage-awards-venues.php?tab=$itemType&success=" . urlencode($success));
                exit;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
                logError($error);
            }
        }
        
        if ($formAction === 'toggle') {
            $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
            try {
                $stmt = $db->prepare("SELECT status FROM $tableName WHERE id = :id");
                $stmt->bindParam(':id', $item_id);
                $stmt->execute();
                $currentData = $stmt->fetch();
                
                if (!$currentData) {
                    throw new Exception(ucfirst($itemLabel) . " not found.");
                }
                
                $newStatus = $currentData['status'] === 'active' ? 'inactive' : 'active';
                $stmt = $db->prepare("UPDATE $tableName SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $newStatus);
                $stmt->bindParam(':id', $item_id);
                $stmt->execute();
                
                $success = ucfirst($itemLabel) . " status updated successfully.";
                header("Location: manage-awards-venues.php?tab=$itemType&success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
                logError($error);
            }
        }
    }
}

// Get data for edit
$itemData = null;
if ($action === 'edit' && $id > 0) {
    $tableName = ($activeTab === 'venues') ? 'venues_list' : (($activeTab === 'categories') ? 'categories' : 'awards_list');
    try {
        $stmt = $db->prepare("SELECT * FROM $tableName WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $itemData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($itemData) {
            foreach ($itemData as $k => $v) {
                $itemData[$k] = full_decode($v);
            }
        } else {
            $error = ucfirst($activeTab) . " item not found.";
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
$limit = 15;
$offset = ($page - 1) * $limit;

// Get Awards List
$awardsItems = [];
$awardsTotalPages = 0;
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM awards_list");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM awards_list ORDER BY award_name ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rawAwardsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rawAwardsItems as $item) {
        $cleaned = [];
        foreach ($item as $k => $v) {
            $cleaned[$k] = full_decode($v);
        }
        $awardsItems[] = $cleaned;
    }
    
    $awardsTotalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError("Database query error for awards_list: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
}

// Get Venues List
$venuesItems = [];
$venuesTotalPages = 0;
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM venues_list");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM venues_list ORDER BY venue_name ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rawVenuesItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rawVenuesItems as $item) {
        $cleaned = [];
        foreach ($item as $k => $v) {
            $cleaned[$k] = full_decode($v);
        }
        $venuesItems[] = $cleaned;
    }
    
    $venuesTotalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError("Database query error for venues_list: " . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
}

// Get Categories List
$categoriesItems = [];
$categoriesTotalPages = 0;
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM categories ORDER BY category_name ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rawCategoriesItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rawCategoriesItems as $item) {
        $cleaned = [];
        foreach ($item as $k => $v) {
            $cleaned[$k] = full_decode($v);
        }
        $categoriesItems[] = $cleaned;
    }
    
    $categoriesTotalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError("Database query error for categories: " . $e->getMessage());
}

// Set page title
$pageTitle = "Manage Awards & Venues";

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<style>
/* Tab styles */
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 1.5rem;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-tabs .nav-link:hover {
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom: 2px solid #007bff;
    background: transparent;
}

/* Responsive table styles */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table th, .table td {
    vertical-align: middle;
    font-size: 14px;
    padding: 12px;
}

.btn-group .btn {
    padding: 6px 12px;
    font-size: 13px;
}

.badge {
    font-size: 12px;
    padding: 5px 10px;
}

/* Modal styles */
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
    .table th, .table td {
        font-size: 12px;
        padding: 8px;
    }

    .btn-group .btn {
        padding: 4px 8px;
        font-size: 11px;
    }

    .nav-tabs .nav-link {
        padding: 0.5rem 1rem;
        font-size: 14px;
    }

    .delete-modal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
}

@media (max-width: 576px) {
    .table th, .table td {
        font-size: 11px;
        padding: 6px;
    }

    .btn-group .btn {
        padding: 3px 6px;
        font-size: 10px;
    }

    .badge {
        font-size: 10px;
        padding: 4px 8px;
    }
}
</style>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-award"></i> Manage Awards & Venues
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="manage-awards-venues.php?tab=<?php echo $activeTab; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="manage-awards-venues.php?tab=<?php echo $activeTab; ?>&action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New <?php echo ucfirst($activeTab === 'venues' ? 'Venue' : ($activeTab === 'categories' ? 'Category' : 'Award')); ?>
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
                    <?php echo ($action === 'add') ? "Add New" : "Edit"; ?> 
                    <?php echo ucfirst($activeTab === 'venues' ? 'Venue' : ($activeTab === 'categories' ? 'Category' : 'Award')); ?>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="item_type" value="<?php echo $activeTab; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="item_id" value="<?php echo $itemData['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <?php echo ucfirst($activeTab === 'venues' ? 'Venue' : ($activeTab === 'categories' ? 'Category' : 'Award')); ?> Name 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($itemData[$activeTab === 'venues' ? 'venue_name' : ($activeTab === 'categories' ? 'category_name' : 'award_name')] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo (isset($itemData['status']) && $itemData['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo (isset($itemData['status']) && $itemData['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Add" : "Update"; ?>
                            </button>
                            <a href="manage-awards-venues.php?tab=<?php echo $activeTab; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $activeTab === 'awards' ? 'active' : ''; ?>" 
                       href="?tab=awards" role="tab">
                        <i class="fas fa-trophy"></i> Awards List
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $activeTab === 'categories' ? 'active' : ''; ?>" 
                       href="?tab=categories" role="tab">
                        <i class="fas fa-list-alt"></i> Categories List
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $activeTab === 'venues' ? 'active' : ''; ?>" 
                       href="?tab=venues" role="tab">
                        <i class="fas fa-map-marker-alt"></i> Venues List
                    </a>
                </li>
            </ul>
            
            <!-- Awards Tab Content -->
            <?php if ($activeTab === 'awards'): ?>
                <div class="admin-card">
                    <div class="card-header">
                        <i class="fas fa-trophy"></i> Awards List
                    </div>
                    <div class="card-body">
                        <?php if (count($awardsItems) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="60%">Award Name</th>
                                            <th width="15%">Status</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($awardsItems as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $offset + $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($item['award_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                        <?php echo ucfirst($item['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="manage-awards-venues.php?tab=awards&action=edit&id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-sm btn-info" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle">
                                                            <input type="hidden" name="item_type" value="awards">
                                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                                <i class="fas fa-toggle-on"></i>
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="showDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['award_name'])); ?>', 'awards')" 
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
                                <i class="fas fa-info-circle"></i> No awards found.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($awardsTotalPages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?tab=awards&page=<?php echo $page - 1; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = max(1, $page - 2); $i <= min($awardsTotalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?tab=awards&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $awardsTotalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?tab=awards&page=<?php echo $page + 1; ?>">
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
            
            <!-- Venues Tab Content -->
            <?php if ($activeTab === 'venues'): ?>
                <div class="admin-card">
                    <div class="card-header">
                        <i class="fas fa-map-marker-alt"></i> Venues List
                    </div>
                    <div class="card-body">
                        <?php if (count($venuesItems) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="60%">Venue Name</th>
                                            <th width="15%">Status</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($venuesItems as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $offset + $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($item['venue_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                        <?php echo ucfirst($item['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="manage-awards-venues.php?tab=venues&action=edit&id=<?php echo $item['id']; ?>" 
                                                            class="btn btn-sm btn-info" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle">
                                                            <input type="hidden" name="item_type" value="venues">
                                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                                <i class="fas fa-toggle-on"></i>
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="showDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['venue_name'])); ?>', 'venues')" 
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
                                <i class="fas fa-info-circle"></i> No venues found.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($venuesTotalPages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?tab=venues&page=<?php echo $page - 1; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = max(1, $page - 2); $i <= min($venuesTotalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?tab=venues&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $venuesTotalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?tab=venues&page=<?php echo $page + 1; ?>">
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
            
            <!-- Categories Tab Content -->
            <?php if ($activeTab === 'categories'): ?>
                <div class="admin-card">
                    <div class="card-header">
                        <i class="fas fa-list-alt"></i> Categories List
                    </div>
                    <div class="card-body">
                        <?php if (count($categoriesItems) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="60%">Category Name</th>
                                            <th width="15%">Status</th>
                                            <th width="20%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categoriesItems as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $offset + $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $item['status'] === 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                        <?php echo ucfirst($item['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="manage-awards-venues.php?tab=categories&action=edit&id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-sm btn-info" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="action" value="toggle">
                                                            <input type="hidden" name="item_type" value="categories">
                                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                                <i class="fas fa-toggle-on"></i>
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="showDeleteModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['category_name'])); ?>', 'categories')" 
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
                                <i class="fas fa-info-circle"></i> No categories found.
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($categoriesTotalPages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?tab=categories&page=<?php echo $page - 1; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = max(1, $page - 2); $i <= min($categoriesTotalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?tab=categories&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $categoriesTotalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?tab=categories&page=<?php echo $page + 1; ?>">
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
                    <h6>Are you sure you want to delete this item?</h6>
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
                    <input type="hidden" name="item_type" id="deleteItemType" value="">
                    <input type="hidden" name="item_id" id="deleteItemId" value="">
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
// Prevent multiple modal instances and handle delete modal properly
function showDeleteModal(itemId, itemTitle, itemType) {
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
    document.getElementById('deleteItemType').value = itemType;
    
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
                if (!confirm('Are you sure you want to toggle the status?')) {
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
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
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