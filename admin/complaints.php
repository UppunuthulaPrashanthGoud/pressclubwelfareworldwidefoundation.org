<?php
require_once 'includes/auth_check.php';
require_once '../config/config.php';

// Check if user has permission
if (!isAdmin()) {
    header('Location: ' . SITE_URL . '/admin/');
    exit();
}

$pdo = getDbConnection();
$message = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    try {
        $complaint_id = (int)$_POST['complaint_id'];
        $status = in_array($_POST['status'], ['pending', 'in_progress', 'resolved']) ? $_POST['status'] : 'pending';
        
        $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
        $stmt->execute([$status, $complaint_id]);
        
        $message = 'Complaint status updated successfully.';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_complaint') {
    try {
        $complaint_id = (int)$_POST['complaint_id'];
        
        $stmt = $pdo->prepare("DELETE FROM complaints WHERE id = ?");
        $stmt->execute([$complaint_id]);
        
        $message = 'Complaint deleted successfully.';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch all complaints
$stmt = $pdo->query("SELECT * FROM complaints ORDER BY created_at DESC");
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Complaints';
include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="admin-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-exclamation-circle me-2"></i> Manage Complaints
                </h1>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i> Complaints List
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($complaints)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No complaints found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($complaints as $complaint): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($complaint['id']); ?></td>
                                            <td><?php echo htmlspecialchars($complaint['name']); ?></td>
                                            <td><?php echo htmlspecialchars($complaint['mobile']); ?></td>
                                            <td><?php echo htmlspecialchars($complaint['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $complaint['status'] == 'resolved' ? 'badge-success' : 
                                                        ($complaint['status'] == 'in_progress' ? 'badge-info' : 'badge-warning'); ?>">
                                                    <?php echo ucfirst(htmlspecialchars($complaint['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y H:i', strtotime($complaint['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm view-complaint" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#complaintModal"
                                                        data-id="<?php echo htmlspecialchars($complaint['id']); ?>"
                                                        data-name="<?php echo htmlspecialchars($complaint['name']); ?>"
                                                        data-mobile="<?php echo htmlspecialchars($complaint['mobile']); ?>"
                                                        data-email="<?php echo htmlspecialchars($complaint['email'] ?? 'N/A'); ?>"
                                                        data-subject="<?php echo htmlspecialchars($complaint['subject']); ?>"
                                                        data-message="<?php echo htmlspecialchars($complaint['message']); ?>"
                                                        data-status="<?php echo htmlspecialchars($complaint['status']); ?>"
                                                        data-created="<?php echo htmlspecialchars($complaint['created_at']); ?>">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-complaint" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal"
                                                        data-id="<?php echo htmlspecialchars($complaint['id']); ?>"
                                                        data-subject="<?php echo htmlspecialchars($complaint['subject']); ?>">
                                                    <i class="fas fa-trash me-1"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complaint Details Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="complaintModalLabel">
                    <i class="fas fa-exclamation-circle me-2"></i> Complaint Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <p><strong>ID:</strong> <span id="modal-id"></span></p>
                        <p><strong>Name:</strong> <span id="modal-name"></span></p>
                        <p><strong>Mobile:</strong> <span id="modal-mobile"></span></p>
                        <p><strong>Email:</strong> <span id="modal-email"></span></p>
                    </div>
                    <div class="col-12 col-md-6">
                        <p><strong>Subject:</strong> <span id="modal-subject"></span></p>
                        <p><strong>Status:</strong> <span id="modal-status"></span></p>
                        <p><strong>Created At:</strong> <span id="modal-created"></span></p>
                    </div>
                    <div class="col-12">
                        <p><strong>Message:</strong></p>
                        <p id="modal-message" class="border p-3 rounded"></p>
                    </div>
                </div>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="complaint_id" id="modal-complaint-id">
                    <div class="mb-3">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-select" id="modal-status-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this complaint?</p>
                <p><strong>Subject:</strong> <span id="delete-subject"></span></p>
                <p class="text-danger"><i class="fas fa-exclamation-circle me-1"></i> This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="delete_complaint">
                    <input type="hidden" name="complaint_id" id="delete-complaint-id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// View complaint modal
document.querySelectorAll('.view-complaint').forEach(button => {
    button.addEventListener('click', function() {
        const modal = document.getElementById('complaintModal');
        modal.querySelector('#modal-id').textContent = this.dataset.id;
        modal.querySelector('#modal-name').textContent = this.dataset.name;
        modal.querySelector('#modal-mobile').textContent = this.dataset.mobile;
        modal.querySelector('#modal-email').textContent = this.dataset.email;
        modal.querySelector('#modal-subject').textContent = this.dataset.subject;
        modal.querySelector('#modal-message').textContent = this.dataset.message;
        modal.querySelector('#modal-status').textContent = this.dataset.status.charAt(0).toUpperCase() + this.dataset.status.slice(1);
        modal.querySelector('#modal-created').textContent = new Date(this.dataset.created).toLocaleString();
        modal.querySelector('#modal-complaint-id').value = this.dataset.id;
        modal.querySelector('#modal-status-select').value = this.dataset.status;
    });
});

// Delete complaint modal
document.querySelectorAll('.delete-complaint').forEach(button => {
    button.addEventListener('click', function() {
        const modal = document.getElementById('deleteModal');
        modal.querySelector('#delete-subject').textContent = this.dataset.subject;
        modal.querySelector('#delete-complaint-id').value = this.dataset.id;
    });
});

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>