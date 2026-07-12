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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'add') {
                $info_type = trim($_POST['info_type']);
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $icon = trim($_POST['icon']);
                $sort_order = (int)$_POST['sort_order'];
                $status = in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'active';

                $stmt = $pdo->prepare("INSERT INTO contact_info (info_type, title, content, icon, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$info_type, $title, $content, $icon, $sort_order, $status]);
                $message = 'Contact info added successfully.';
            } elseif ($_POST['action'] == 'update') {
                $id = (int)$_POST['id'];
                $info_type = trim($_POST['info_type']);
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $icon = trim($_POST['icon']);
                $sort_order = (int)$_POST['sort_order'];
                $status = in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'active';

                $stmt = $pdo->prepare("UPDATE contact_info SET info_type = ?, title = ?, content = ?, icon = ?, sort_order = ?, status = ? WHERE id = ?");
                $stmt->execute([$info_type, $title, $content, $icon, $sort_order, $status, $id]);
                $message = 'Contact info updated successfully.';
            } elseif ($_POST['action'] == 'delete') {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM contact_info WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Contact info deleted successfully.';
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch all contact info
$stmt = $pdo->query("SELECT * FROM contact_info ORDER BY sort_order ASC, created_at DESC");
$contact_infos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manage Contact Info';
include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="admin-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-info-circle me-2"></i> Manage Contact Info
                </h1>
                <div class="page-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                        <i class="fas fa-plus me-2"></i> Add New
                    </button>
                </div>
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
                    <i class="fas fa-list me-2"></i> Contact Info List
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Content</th>
                                    <th>Icon</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($contact_infos)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No contact info found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($contact_infos as $info): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($info['id']); ?></td>
                                            <td><?php echo htmlspecialchars($info['info_type']); ?></td>
                                            <td><?php echo htmlspecialchars($info['title']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($info['content'])); ?></td>
                                            <td><i class="<?php echo htmlspecialchars($info['icon']); ?>"></i> <?php echo htmlspecialchars($info['icon']); ?></td>
                                            <td><?php echo htmlspecialchars($info['sort_order']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $info['status'] == 'active' ? 'badge-success' : 'badge-warning'; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($info['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y H:i', strtotime($info['created_at'])); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($info['updated_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm edit-contact" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editContactModal"
                                                        data-id="<?php echo htmlspecialchars($info['id']); ?>"
                                                        data-info-type="<?php echo htmlspecialchars($info['info_type']); ?>"
                                                        data-title="<?php echo htmlspecialchars($info['title']); ?>"
                                                        data-content="<?php echo htmlspecialchars($info['content']); ?>"
                                                        data-icon="<?php echo htmlspecialchars($info['icon']); ?>"
                                                        data-sort-order="<?php echo htmlspecialchars($info['sort_order']); ?>"
                                                        data-status="<?php echo htmlspecialchars($info['status']); ?>">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>
                                                <button class="btn btn-danger btn-sm delete-contact" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteContactModal"
                                                        data-id="<?php echo htmlspecialchars($info['id']); ?>"
                                                        data-title="<?php echo htmlspecialchars($info['title']); ?>">
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

<!-- Add Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContactModalLabel">
                    <i class="fas fa-plus me-2"></i> Add Contact Info
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" class="contact-info-form">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Info Type</label>
                                <input type="text" name="info_type" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea name="content" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Icon (Font Awesome Class)</label>
                                <input type="text" name="icon" class="form-control" placeholder="e.g., fas fa-map-marker-alt">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Add Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Contact Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContactModalLabel">
                    <i class="fas fa-edit me-2"></i> Edit Contact Info
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" class="contact-info-form">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Info Type</label>
                                <input type="text" name="info_type" id="edit-info-type" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" id="edit-title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea name="content" id="edit-content" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Icon (Font Awesome Class)</label>
                                <input type="text" name="icon" id="edit-icon" class="form-control" placeholder="e.g., fas fa-map-marker-alt">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="edit-sort-order" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit-status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Contact Modal -->
<div class="modal fade" id="deleteContactModal" tabindex="-1" aria-labelledby="deleteContactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteContactModalLabel">
                    <i class="fas fa-trash me-2"></i> Delete Contact Info
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="delete-title"></strong>?</p>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.querySelectorAll('.edit-contact').forEach(button => {
    button.addEventListener('click', function() {
        const modal = document.getElementById('editContactModal');
        modal.querySelector('#edit-id').value = this.dataset.id;
        modal.querySelector('#edit-info-type').value = this.dataset.infoType;
        modal.querySelector('#edit-title').value = this.dataset.title;
        modal.querySelector('#edit-content').value = this.dataset.content;
        modal.querySelector('#edit-icon').value = this.dataset.icon;
        modal.querySelector('#edit-sort-order').value = this.dataset.sortOrder;
        modal.querySelector('#edit-status').value = this.dataset.status;
    });
});

document.querySelectorAll('.delete-contact').forEach(button => {
    button.addEventListener('click', function() {
        const modal = document.getElementById('deleteContactModal');
        modal.querySelector('#delete-id').value = this.dataset.id;
        modal.querySelector('#delete-title').textContent = this.dataset.title;
    });
});
</script>