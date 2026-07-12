<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

/**
 * Team Member Management Admin Page
 * Fixed: Updated SQL queries to use 'description' column instead of 'area_of_work' 
 * to match the provided database schema.
 */

// Only admin and coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                try {
                    $name = sanitizeInput($_POST['name']);
                    $designation = sanitizeInput($_POST['designation']);
                    // Variable name kept as area_of_work for logic, but stored in 'description' column
                    $area_of_work = sanitizeInput($_POST['area_of_work'] ?? '');
                    $phone = sanitizeInput($_POST['phone'] ?? '');
                    $sort_order = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
                    $member_type = sanitizeInput($_POST['member_type']);
                    $status = sanitizeInput($_POST['status']);
                    
                    // Handle image upload
                    $image = '';
                    if (!empty($_FILES['image']['name'])) {
                        $upload_dir = '../uploads/team/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_ext, $allowed_ext)) {
                            $image = uniqid('team_') . '.' . $file_ext;
                            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
                        } else {
                            throw new Exception('Invalid image format! Only JPG, JPEG, PNG, or GIF are allowed.');
                        }
                    }
                    
                    if ($_POST['action'] === 'add') {
                        // Fixed: Using 'description' column name
                        $stmt = $db->prepare("
                            INSERT INTO team_members (name, designation, description, image, phone, sort_order, member_type, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$name, $designation, $area_of_work, $image, $phone, $sort_order, $member_type, $status]);
                        $message = 'Team member added successfully!';
                    } else {
                        // Keep existing image if no new image uploaded
                        if (empty($image)) {
                            $stmt = $db->prepare("SELECT image FROM team_members WHERE id = ?");
                            $stmt->execute([$id]);
                            $existing = $stmt->fetch();
                            $image = $existing['image'] ?? '';
                        }
                        
                        // Fixed: Using 'description' column name
                        $stmt = $db->prepare("
                            UPDATE team_members 
                            SET name = ?, designation = ?, description = ?, image = ?, phone = ?, sort_order = ?, member_type = ?, status = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$name, $designation, $area_of_work, $image, $phone, $sort_order, $member_type, $status, $id]);
                        $message = 'Team member updated successfully!';
                    }
                    
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'Error: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $delete_id = $_POST['id'];
                    
                    // Get image filename before deleting
                    $stmt = $db->prepare("SELECT image FROM team_members WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $member = $stmt->fetch();
                    
                    // Delete team member
                    $stmt = $db->prepare("DELETE FROM team_members WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete image if it exists
                    if (!empty($member['image']) && file_exists('../uploads/team/' . $member['image'])) {
                        unlink('../uploads/team/' . $member['image']);
                    }
                    
                    $message = 'Team member deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get team member data for editing
$member_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->execute([$id]);
    $member_data = $stmt->fetch();
    
    if (!$member_data) {
        $error = 'Team member not found!';
        $action = 'list';
    }
}

// Get team members list
$team_members = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM team_members ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute();
    $team_members = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-users me-3"></i>Team Member Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Team Member
                </a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Team Members List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Team Member List</h5>
            </div>
            <div class="card-body">
                <!-- Search Bar -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="member-search" class="form-control" placeholder="Search by name, designation, or area of working...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="members-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Area of Working</th>
                                <th>Contact No.</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($team_members as $member): ?>
                            <tr data-name="<?php echo strtolower(htmlspecialchars($member['name'])); ?>" 
                                data-designation="<?php echo strtolower(htmlspecialchars($member['designation'])); ?>"
                                data-area="<?php echo strtolower(htmlspecialchars($member['description'] ?? '')); ?>">
                                <td>
                                    <?php if (!empty($member['image'])): ?>
                                    <img src="<?php echo SITE_URL . '/uploads/team/' . $member['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                         class="img-thumbnail" width="60" height="60">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['designation']); ?></td>
                                <td><?php echo htmlspecialchars($member['description'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($member['phone'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($member['member_type']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $member['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $member['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteMember(<?php echo $member['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- No results message -->
                <div id="no-results" class="text-center text-muted mt-3 d-none">
                    <h5><i class="fas fa-exclamation-circle"></i> No team members found.</h5>
                </div>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> 
                    <?php echo $action === 'add' ? 'Add New Team Member' : 'Edit Team Member'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo htmlspecialchars($member_data['name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="designation" class="form-label">Designation *</label>
                                <input type="text" class="form-control" id="designation" name="designation" required
                                       value="<?php echo htmlspecialchars($member_data['designation'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="area_of_work" class="form-label">Area of Working</label>
                                <input type="text" class="form-control" id="area_of_work" name="area_of_work"
                                       value="<?php echo htmlspecialchars($member_data['description'] ?? ''); ?>"
                                       placeholder="e.g., Social Welfare & Human Rights">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Contact No.</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($member_data['phone'] ?? ''); ?>"
                                       placeholder="e.g., +91 9876543210">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order"
                                       value="<?php echo htmlspecialchars($member_data['sort_order'] ?? '0'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="member_type" class="form-label">Member Type *</label>
                                <select class="form-select" id="member_type" name="member_type" required>
                                    <option value="management" <?php echo ($member_data['member_type'] ?? '') === 'management' ? 'selected' : ''; ?>>Management</option>
                                    <option value="volunteer" <?php echo ($member_data['member_type'] ?? '') === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" <?php echo ($member_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($member_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if (!empty($member_data['image'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo SITE_URL . '/uploads/team/' . $member_data['image']; ?>" 
                                 alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<?php include 'includes/sidebar.php'; ?>

<script>
function deleteMember(id) {
    if (confirm('Are you sure you want to delete this team member? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('member-search');
    const memberRows = document.querySelectorAll('#members-table tbody tr');
    const noResultsDiv = document.getElementById('no-results');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        memberRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const designation = row.getAttribute('data-designation');
            const area = row.getAttribute('data-area');

            if (name.includes(searchTerm) || designation.includes(searchTerm) || area.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noResultsDiv.classList.remove('d-none');
        } else {
            noResultsDiv.classList.add('d-none');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>