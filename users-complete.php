<?php
$page_title = "Users Management";
$page_subtitle = "Complete CRUD operations for all users";
include 'dashboard-header.php';

// Check admin access
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

// Get all users with pagination and filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = sanitizeInput($_GET['search'] ?? '');
$status = sanitizeInput($_GET['status'] ?? '');
$user_type = sanitizeInput($_GET['user_type'] ?? '');
$membership = sanitizeInput($_GET['membership'] ?? '');

$db = Database::getInstance()->getConnection();

// Build query
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR mobile LIKE ? OR registration_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

if ($user_type) {
    $sql .= " AND user_type = ?";
    $params[] = $user_type;
}

if ($membership) {
    $sql .= " AND membership_type = ?";
    $params[] = $membership;
}

// Get total count
$count_sql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$stmt = $db->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get users with pagination
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <h3 class="mb-1">Users Management</h3>
        <p class="text-muted mb-0">Complete CRUD operations for all users (<?php echo $total_records; ?> total)</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-success" onclick="exportUsers()">
            <i class="fas fa-download me-2"></i>Export
        </button>
        <button class="btn btn-info" onclick="importUsers()">
            <i class="fas fa-upload me-2"></i>Import
        </button>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i>Add User
        </button>
    </div>
</div>

<!-- Advanced Search and Filter -->
<div class="data-table mb-4">
    <div class="table-header">
        <h5 class="mb-0">Advanced Search & Filter</h5>
    </div>
    <div class="p-3">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search users...">
                    <label>Search (Name, Email, Mobile, ID)</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-floating">
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <label>Status</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-floating">
                    <select class="form-select" name="user_type">
                        <option value="">All Types</option>
                        <option value="user" <?php echo $user_type === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="coordinator" <?php echo $user_type === 'coordinator' ? 'selected' : ''; ?>>Coordinator</option>
                        <option value="admin" <?php echo $user_type === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                    <label>User Type</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <select class="form-select" name="membership">
                        <option value="">All Memberships</option>
                        <option value="free_membership" <?php echo $membership === 'free_membership' ? 'selected' : ''; ?>>Free</option>
                        <option value="active_membership" <?php echo $membership === 'active_membership' ? 'selected' : ''; ?>>Active</option>
                        <option value="management_membership" <?php echo $membership === 'management_membership' ? 'selected' : ''; ?>>Management</option>
                        <option value="senior_membership" <?php echo $membership === 'senior_membership' ? 'selected' : ''; ?>>Senior</option>
                    </select>
                    <label>Membership</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 h-100">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="data-table">
    <div class="table-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="mb-0">All Users</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-light" onclick="selectAll()">
                <i class="fas fa-check-square me-1"></i>Select All
            </button>
            <button class="btn btn-sm btn-outline-light" onclick="clearSelection()">
                <i class="fas fa-square me-1"></i>Clear All
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="users-table">
            <thead class="table-light">
                <tr>
                    <th width="40">
                        <input type="checkbox" class="form-check-input" id="select-all">
                    </th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Membership</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Joined</th>
                    <th width="200">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr data-id="<?php echo $user['id']; ?>">
                    <td>
                        <input type="checkbox" class="form-check-input select-item" value="<?php echo $user['id']; ?>">
                    </td>
                    <td><?php echo $user['id']; ?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                <?php if ($user['profile_image']): ?>
                                    <img src="uploads/<?php echo $user['profile_image']; ?>" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user text-white small"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                <?php if ($user['registration_id']): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['registration_id']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                    <td>
                        <span class="badge bg-info">
                            <?php echo str_replace('_', ' ', ucwords($user['membership_type'])); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $user['status'] === 'approved' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-primary">
                            <?php echo ucfirst($user['user_type']); ?>
                        </span>
                    </td>
                    <td>
                        <small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                    </td>
                    <td class="table-actions">
                        <div class="btn-group" role="group">
                            <button class="btn-action btn-view" onclick="viewUser(<?php echo $user['id']; ?>)" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-action btn-edit" onclick="editUser(<?php echo $user['id']; ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($user['status'] === 'pending'): ?>
                                <button class="btn-action btn-approve" onclick="approveUser(<?php echo $user['id']; ?>)" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn-action btn-reject" onclick="rejectUser(<?php echo $user['id']; ?>)" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            <?php endif; ?>
                            <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Users pagination" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
        </li>
        
        <?php 
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        
        if ($start > 1): ?>
            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a></li>
            <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if ($end < $total_pages): ?>
            <?php if ($end < $total_pages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
            <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>"><?php echo $total_pages; ?></a></li>
        <?php endif; ?>
        
        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Bulk Actions Bar -->
<div class="fixed-bottom bg-white border-top p-3" id="bulk-actions" style="display: none;">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span id="selected-count">0 users selected</span>
            <div class="btn-group flex-wrap">
                <button class="btn btn-success btn-sm" onclick="bulkApprove()">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
                <button class="btn btn-warning btn-sm" onclick="bulkReject()">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
                <button class="btn btn-info btn-sm" onclick="bulkChangeType()">
                    <i class="fas fa-user-cog me-1"></i>Change Type
                </button>
                <button class="btn btn-danger btn-sm" onclick="bulkDelete()">
                    <i class="fas fa-trash me-1"></i>Delete
                </button>
                <button class="btn btn-secondary btn-sm" onclick="clearSelection()">
                    <i class="fas fa-times me-1"></i>Clear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="user-form" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="manage_users">
                    <input type="hidden" name="operation" id="form-operation" value="add">
                    <input type="hidden" name="user_id" id="form-user-id" value="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    
                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Personal Information</h6>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="name" id="form-name" required>
                                <label>Full Name *</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" name="email" id="form-email">
                                <label>Email</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="tel" class="form-control" name="mobile" id="form-mobile" required>
                                <label>Mobile *</label>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" name="gender" id="form-gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <label>Gender *</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-floating mb-3">
                                        <input type="date" class="form-control" name="dob" id="form-dob" required>
                                        <label>Date of Birth *</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">System Information</h6>
                            <div class="form-floating mb-3">
                                <select class="form-select" name="user_type" id="form-user-type" required>
                                    <option value="">Select Type</option>
                                    <option value="user">User</option>
                                    <option value="coordinator">Coordinator</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <label>User Type *</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-select" name="status" id="form-status">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                                <label>Status</label>
                            </div>
                            <div class="form-floating mb-3">
                                <select class="form-select" name="membership_type" id="form-membership">
                                    <option value="free_membership">Free Membership</option>
                                    <option value="active_membership">Active Membership</option>
                                    <option value="management_membership">Management Membership</option>
                                    <option value="senior_membership">Senior Membership</option>
                                </select>
                                <label>Membership Type</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Profile Image</label>
                                <input type="file" class="form-control" name="profile_image" accept="image/*" onchange="previewImage(this, 'profile-preview')">
                                <img id="profile-preview" class="mt-2 rounded" style="max-height: 100px; display: none;">
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Address Information</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="state" id="form-state" required>
                                        <label>State *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="district" id="form-district" required>
                                        <label>District *</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="pincode" id="form-pincode" required>
                                        <label>Pincode *</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" name="address" id="form-address" style="height: 80px" required></textarea>
                                        <label>Full Address *</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-save me-2"></i>Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="user-details-content">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editFromView()">
                    <i class="fas fa-edit me-2"></i>Edit User
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEditId = null;
let selectedUsers = new Set();

document.addEventListener('DOMContentLoaded', function() {
    setupFormHandler();
    setupBulkSelection();
    setupSelectAll();
});

function setupFormHandler() {
    document.getElementById('user-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-btn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        submitBtn.disabled = true;
        
        const formData = new FormData(this);
        
        fetch('config.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Something went wrong');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
}

function setupBulkSelection() {
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('select-item')) {
            const userId = e.target.value;
            if (e.target.checked) {
                selectedUsers.add(userId);
            } else {
                selectedUsers.delete(userId);
            }
            updateBulkActions();
        }
    });
}

function setupSelectAll() {
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.select-item');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            if (this.checked) {
                selectedUsers.add(cb.value);
            } else {
                selectedUsers.delete(cb.value);
            }
        });
        updateBulkActions();
    });
}

function updateBulkActions() {
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');
    
    if (selectedUsers.size > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = `${selectedUsers.size} user${selectedUsers.size > 1 ? 's' : ''} selected`;
    } else {
        bulkActions.style.display = 'none';
    }
}

function selectAll() {
    document.getElementById('select-all').checked = true;
    document.getElementById('select-all').dispatchEvent(new Event('change'));
}

function clearSelection() {
    selectedUsers.clear();
    document.querySelectorAll('.select-item, #select-all').forEach(cb => cb.checked = false);
    updateBulkActions();
}

function openAddModal() {
    currentEditId = null;
    document.getElementById('modal-title').textContent = 'Add New User';
    document.getElementById('form-operation').value = 'add';
    document.getElementById('form-user-id').value = '';
    document.getElementById('user-form').reset();
    document.getElementById('profile-preview').style.display = 'none';
    
    new bootstrap.Modal(document.getElementById('userModal')).show();
}

function editUser(id) {
    currentEditId = id;
    document.getElementById('modal-title').textContent = 'Edit User';
    document.getElementById('form-operation').value = 'update';
    document.getElementById('form-user-id').value = id;
    
    // Load user data
    const formData = new FormData();
    formData.append('action', 'manage_users');
    formData.append('operation', 'get');
    formData.append('user_id', id);
    formData.append('csrf_token', '<?php echo generateCSRF(); ?>');
    
    fetch('config.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.data;
            
            // Fill form fields
            document.getElementById('form-name').value = user.name;
            document.getElementById('form-email').value = user.email || '';
            document.getElementById('form-mobile').value = user.mobile;
            document.getElementById('form-gender').value = user.gender;
            document.getElementById('form-dob').value = user.dob;
            document.getElementById('form-user-type').value = user.user_type;
            document.getElementById('form-status').value = user.status;
            document.getElementById('form-membership').value = user.membership_type;
            document.getElementById('form-state').value = user.state;
            document.getElementById('form-district').value = user.district;
            document.getElementById('form-pincode').value = user.pincode;
            document.getElementById('form-address').value = user.address;
            
            // Show profile image if exists
            if (user.profile_image) {
                const preview = document.getElementById('profile-preview');
                preview.src = 'uploads/' + user.profile_image;
                preview.style.display = 'block';
            }
            
            new bootstrap.Modal(document.getElementById('userModal')).show();
        } else {
            showError('Failed to load user data');
        }
    })
    .catch(error => {
        showError('Failed to load user data');
    });
}

function viewUser(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
    const content = document.getElementById('user-details-content');
    
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.show();
    
    const formData = new FormData();
    formData.append('action', 'manage_users');
    formData.append('operation', 'get');
    formData.append('user_id', id);
    formData.append('csrf_token', '<?php echo generateCSRF(); ?>');
    
    fetch('config.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.data;
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        ${user.profile_image ? 
                            `<img src="uploads/${user.profile_image}" class="img-fluid rounded-circle mb-3" style="max-width: 200px;">` :
                            `<div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 200px; height: 200px;">
                                <i class="fas fa-user fa-5x text-white"></i>
                            </div>`
                        }
                        <h4>${user.name}</h4>
                        <p class="text-muted">${user.email || 'No email'}</p>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Personal Information</h6>
                                <p><strong>Mobile:</strong> ${user.mobile}</p>
                                <p><strong>Gender:</strong> ${user.gender}</p>
                                <p><strong>Date of Birth:</strong> ${user.dob}</p>
                                <p><strong>Registration ID:</strong> ${user.registration_id || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">System Information</h6>
                                <p><strong>Status:</strong> <span class="badge bg-${user.status === 'approved' ? 'success' : user.status === 'pending' ? 'warning' : 'danger'}">${user.status}</span></p>
                                <p><strong>User Type:</strong> <span class="badge bg-primary">${user.user_type}</span></p>
                                <p><strong>Membership:</strong> <span class="badge bg-info">${user.membership_type.replace('_', ' ')}</span></p>
                                <p><strong>Joined:</strong> ${formatDate(user.created_at)}</p>
                            </div>
                            <div class="col-12">
                                <h6 class="text-primary">Address Information</h6>
                                <p><strong>State:</strong> ${user.state}</p>
                                <p><strong>District:</strong> ${user.district}</p>
                                <p><strong>Pincode:</strong> ${user.pincode}</p>
                                <p><strong>Address:</strong><br>${user.address}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            currentEditId = id;
        } else {
            content.innerHTML = '<div class="alert alert-danger">Failed to load user details</div>';
        }
    })
    .catch(error => {
        content.innerHTML = '<div class="alert alert-danger">Failed to load user details</div>';
    });
}

function editFromView() {
    bootstrap.Modal.getInstance(document.getElementById('viewUserModal')).hide();
    setTimeout(() => editUser(currentEditId), 300);
}

function approveUser(id) {
    confirmDelete('Are you sure you want to approve this user?', function() {
        performUserAction('approve', [id]);
    });
}

function rejectUser(id) {
    confirmDelete('Are you sure you want to reject this user?', function() {
        performUserAction('reject', [id]);
    });
}

function deleteUser(id) {
    confirmDelete('Are you sure you want to delete this user? This action cannot be undone.', function() {
        performUserAction('delete', [id]);
    });
}

function bulkApprove() {
    if (selectedUsers.size === 0) return;
    confirmDelete(`Are you sure you want to approve ${selectedUsers.size} selected users?`, function() {
        performUserAction('bulk_approve', Array.from(selectedUsers));
    });
}

function bulkReject() {
    if (selectedUsers.size === 0) return;
    confirmDelete(`Are you sure you want to reject ${selectedUsers.size} selected users?`, function() {
        performUserAction('bulk_reject', Array.from(selectedUsers));
    });
}

function bulkChangeType() {
    if (selectedUsers.size === 0) return;
    
    Swal.fire({
        title: 'Change User Type',
        input: 'select',
        inputOptions: {
            'user': 'User',
            'coordinator': 'Coordinator',
            'admin': 'Admin'
        },
        inputPlaceholder: 'Select user type',
        showCancelButton: true,
        confirmButtonText: 'Change Type'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            performUserAction('bulk_change_type', Array.from(selectedUsers), { user_type: result.value });
        }
    });
}

function bulkDelete() {
    if (selectedUsers.size === 0) return;
    confirmDelete(`Are you sure you want to delete ${selectedUsers.size} selected users? This action cannot be undone.`, function() {
        performUserAction('bulk_delete', Array.from(selectedUsers));
    });
}

function performUserAction(operation, ids, extraData = {}) {
    const formData = new FormData();
    formData.append('action', 'manage_users');
    formData.append('operation', operation);
    formData.append('user_ids', JSON.stringify(ids));
    formData.append('csrf_token', '<?php echo generateCSRF(); ?>');
    
    // Add extra data
    Object.keys(extraData).forEach(key => {
        formData.append(key, extraData[key]);
    });
    
    fetch('config.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            clearSelection();
            setTimeout(() => location.reload(), 1000);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        showError('Something went wrong');
    });
}

function exportUsers() {
    const formData = new FormData();
    formData.append('action', 'manage_users');
    formData.append('operation', 'export');
    formData.append('csrf_token', '<?php echo generateCSRF(); ?>');
    
    fetch('config.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'users_export_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        showError('Export failed');
    });
}

function importUsers() {
    Swal.fire({
        title: 'Import Users',
        html: `
            <input type="file" id="import-file" accept=".csv" class="form-control mb-3">
            <div class="text-start">
                <small class="text-muted">
                    <strong>CSV Format:</strong><br>
                    name,email,mobile,gender,dob,user_type,status,membership_type,state,district,pincode,address
                </small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Import',
        preConfirm: () => {
            const file = document.getElementById('import-file').files[0];
            if (!file) {
                Swal.showValidationMessage('Please select a CSV file');
                return false;
            }
            return file;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'manage_users');
            formData.append('operation', 'import');
            formData.append('import_file', result.value);
            formData.append('csrf_token', '<?php echo generateCSRF(); ?>');
            
            fetch('config.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Import failed');
            });
        }
    });
}
</script>

<?php include 'dashboard-footer.php'; ?>
