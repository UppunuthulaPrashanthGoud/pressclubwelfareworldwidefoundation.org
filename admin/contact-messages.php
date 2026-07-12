<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Ensure only admins can access
if (!isAdmin()) {
    redirectTo(ADMIN_URL . 'login.php');
}

$pdo = getDbConnection();
$success = isset($_GET['success']) ? htmlspecialchars_decode(sanitizeInput($_GET['success']), ENT_QUOTES) : '';
$error = '';

// Handle form submissions (update status or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
        logError($error);
    } else {
        try {
            if ($_POST['action'] === 'update_status') {
                $id = (int)$_POST['id'];
                $status = sanitizeInput($_POST['status'], false);

                // Validate status
                if (!in_array($status, ['pending', 'resolved'])) {
                    $error = 'Invalid status selection.';
                    logError($error);
                } else {
                    // Check if updated_at column exists
                    $checkColumn = $pdo->query("SHOW COLUMNS FROM contact_messages LIKE 'updated_at'");
                    if ($checkColumn->rowCount() > 0) {
                        // Column exists, use it
                        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?");
                    } else {
                        // Column doesn't exist, skip it
                        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
                    }
                    $stmt->execute([$status, $id]);
                    $success = 'Message status successfully updated.';
                    header("Location: contact-messages.php?success=" . urlencode($success));
                    exit;
                }
            } elseif ($_POST['action'] === 'delete') {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Message successfully deleted.';
                header("Location: contact-messages.php?success=" . urlencode($success));
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            logError($error);
        }
    }
}

// Get all contact messages
try {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Database error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    logError($error);
    $messages = [];
}

// $pageTitle = 'Contact Message Management';
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-envelope me-3"></i> Contact Message Management
            </h1>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Contact Messages</h5>
                <div class="card-actions">
                    <span class="badge bg-primary">Total: <?php echo count($messages); ?></span>
                    <?php
                    $pending = array_filter($messages, function($m) { return $m['status'] === 'pending'; });
                    $resolved = array_filter($messages, function($m) { return $m['status'] === 'resolved'; });
                    ?>
                    <span class="badge bg-warning">Pending: <?php echo count($pending); ?></span>
                    <span class="badge bg-success">Resolved: <?php echo count($resolved); ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Topic</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 text-muted"></i><br>
                                        No messages found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <tr class="<?php echo $message['status'] === 'resolved' ? 'table-light' : ''; ?>">
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($message['id'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($message['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div class="mb-1">
                                                    <i class="fas fa-phone text-muted me-1"></i>
                                                    <a href="tel:<?php echo htmlspecialchars($message['mobile'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($message['mobile'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                </div>
                                                <div>
                                                    <i class="fas fa-envelope text-muted me-1"></i>
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($message['topic'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $description = htmlspecialchars($message['description'], ENT_QUOTES, 'UTF-8');
                                            $shortDescription = strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
                                            ?>
                                            <div>
                                                <?php echo $shortDescription; ?>
                                                <?php if (strlen($description) > 50): ?>
                                                    <br>
                                                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" 
                                                            onclick="viewMessage(<?php echo $message['id']; ?>)">
                                                        <i class="fas fa-expand-alt me-1"></i> Read Full
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <div>
                                                    <i class="fas fa-calendar text-muted me-1"></i>
                                                    <?php echo htmlspecialchars(date('d-m-Y', strtotime($message['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div>
                                                    <i class="fas fa-clock text-muted me-1"></i>
                                                    <?php echo htmlspecialchars(date('H:i', strtotime($message['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <?php if (isset($message['updated_at']) && $message['updated_at']): ?>
                                                <div class="mt-1">
                                                    <small class="text-info">
                                                        <i class="fas fa-edit me-1"></i>
                                                        Updated: <?php echo date('d-m-Y H:i', strtotime($message['updated_at'])); ?>
                                                    </small>
                                                </div>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$message['id']; ?>">
                                                <select name="status" class="form-select form-select-sm status-select">
                                                    <option value="pending" <?php echo $message['status'] === 'pending' ? 'selected' : ''; ?>>
                                                        Pending
                                                    </option>
                                                    <option value="resolved" <?php echo $message['status'] === 'resolved' ? 'selected' : ''; ?>>
                                                        Resolved
                                                    </option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$message['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        title="Delete Message">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($messages)): ?>
                <div class="mt-4">
                    <div class="row">
                        <div class="col-md-8">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Found <?php echo count($messages); ?> messages. 
                                Pending: <?php echo count($pending); ?>, 
                                Resolved: <?php echo count($resolved); ?>
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Message Detail Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">
                    <i class="fas fa-envelope me-2"></i>
                    Message Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="messageModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Store messages data for modal
const messagesData = <?php echo json_encode($messages); ?>;

function viewMessage(id) {
    const message = messagesData.find(m => m.id == id);
    if (!message) return;
    
    const modalBody = document.getElementById('messageModalBody');
    const modalLabel = document.getElementById('messageModalLabel');
    
    modalLabel.innerHTML = `<i class="fas fa-envelope me-2"></i>Message Details - ${escapeHtml(message.name)}`;
    
    modalBody.innerHTML = `
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="card-title text-muted mb-1">Name</h6>
                        <p class="mb-0">${escapeHtml(message.name)}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="card-title text-muted mb-1">Mobile</h6>
                        <p class="mb-0">
                            <a href="tel:${escapeHtml(message.mobile)}" class="text-decoration-none">
                                ${escapeHtml(message.mobile)}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="card-title text-muted mb-1">Email</h6>
                        <p class="mb-0">
                            <a href="mailto:${escapeHtml(message.email)}" class="text-decoration-none">
                                ${escapeHtml(message.email)}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="card-title text-muted mb-1">Topic</h6>
                        <p class="mb-0">
                            <span class="badge bg-info fs-6">
                                ${escapeHtml(message.topic)}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="card-title text-muted mb-1">Date</h6>
                        <p class="mb-0">
                            ${formatDate(message.created_at)}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="card-title text-muted mb-1">Status</h6>
                        <p class="mb-0">
                            ${message.status === 'pending' ? 
                                '<span class="badge bg-warning fs-6">Pending</span>' : 
                                '<span class="badge bg-success fs-6">Resolved</span>'}
                        </p>
                    </div>
                </div>
            </div>
            ${message.updated_at ? `
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body p-3">
                        <h6 class="card-title text-muted mb-1">Last Updated</h6>
                        <p class="mb-0">
                            <i class="fas fa-edit text-info me-1"></i>
                            ${formatDate(message.updated_at)}
                        </p>
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-comment-alt me-2"></i>
                    Full Message
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-line;">
                    ${escapeHtml(message.description)}
                </p>
            </div>
        </div>
        
        <div class="mt-3">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Message ID: ${message.id}
            </small>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('messageModal'));
    modal.show();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Confirmation for status changes
    var statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(function(select) {
        const originalValue = select.value;
        select.addEventListener('change', function(e) {
            const confirmed = confirm('Are you sure you want to change the status of this message?');
            if (confirmed) {
                this.form.submit();
            } else {
                this.value = originalValue;
            }
        });
    });
});

// Print styles
var printStyles = `
<style type="text/css" media="print">
    .btn, .modal, .page-actions, .btn-close { display: none !important; }
    .admin-content { margin: 0 !important; padding: 0 !important; }
    .main-content { padding: 10px !important; }
    .table { font-size: 12px; }
    @page { margin: 1cm; }
</style>
`;
document.head.insertAdjacentHTML('beforeend', printStyles);
</script>

<?php include 'includes/footer.php'; ?>