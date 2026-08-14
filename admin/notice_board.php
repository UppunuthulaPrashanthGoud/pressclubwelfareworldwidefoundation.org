<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin and coordinator can access
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'Notice Board Management';
$db = getDbConnection();

// Auto-create table if it doesn't exist
try {
    $db->exec("CREATE TABLE IF NOT EXISTS notice_board (
        id INT AUTO_INCREMENT PRIMARY KEY,
        notice_text TEXT NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
} catch (Exception $e) {}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
        $notice_text = trim($_POST['notice_text'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (empty($notice_text)) {
            $error = 'Notice text is required.';
        } else {
            try {
                if ($_POST['action'] === 'add') {
                    $stmt = $db->prepare("INSERT INTO notice_board (notice_text, status) VALUES (?, ?)");
                    $stmt->execute([$notice_text, $status]);
                    header("Location: ?success=add");
                    exit;
                } else {
                    $edit_id = intval($_POST['id']);
                    $stmt = $db->prepare("UPDATE notice_board SET notice_text = ?, status = ? WHERE id = ?");
                    $stmt->execute([$notice_text, $status, $edit_id]);
                    header("Location: ?success=edit");
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        try {
            $delete_id = intval($_POST['id']);
            $stmt = $db->prepare("DELETE FROM notice_board WHERE id = ?");
            $stmt->execute([$delete_id]);
            header("Location: ?success=delete");
            exit;
        } catch (Exception $e) {
            $error = 'Error deleting notice: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'add') $message = 'Notice added successfully!';
    elseif ($_GET['success'] === 'edit') $message = 'Notice updated successfully!';
    elseif ($_GET['success'] === 'delete') $message = 'Notice deleted successfully!';
}

// Get data
$item = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM notice_board WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) $action = 'list';
}

$notices = [];
if ($action === 'list') {
    $stmt = $db->query("SELECT * FROM notice_board ORDER BY id DESC");
    $notices = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-bullhorn me-3"></i>Notice Board Management</h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Notice</a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <div class="admin-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Notice Text</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notices as $notice): ?>
                            <tr>
                                <td><?php echo $notice['id']; ?></td>
                                <td><?php echo nl2br(htmlspecialchars(substr($notice['notice_text'], 0, 100))) . (strlen($notice['notice_text']) > 100 ? '...' : ''); ?></td>
                                <td><span class="badge bg-<?php echo $notice['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($notice['status']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($notice['created_at'])); ?></td>
                                <td>
                                    <a href="?action=edit&id=<?php echo $notice['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this notice?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $notice['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <div class="admin-card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Notice Text (Max 10000 characters) *</label>
                        <textarea class="form-control" name="notice_text" rows="5" maxlength="10000" required><?php echo isset($item['notice_text']) ? htmlspecialchars($item['notice_text']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="active" <?php echo (isset($item['status']) && $item['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($item['status']) && $item['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Notice</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/footer.php'; ?>