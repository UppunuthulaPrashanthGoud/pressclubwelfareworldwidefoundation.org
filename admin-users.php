<?php
session_start();
require_once 'config.php';
require_once 'check_admin.php';

$success = '';
$error = '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'approve':
                $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                echo json_encode(['success' => true, 'message' => 'User approved successfully']);
                break;
                
            case 'reject':
                $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                echo json_encode(['success' => true, 'message' => 'User rejected successfully']);
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                break;
                
            case 'update':
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, user_type = ?, status = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['email'], 
                    $_POST['phone'],
                    $_POST['user_type'],
                    $_POST['status'],
                    $_POST['user_id']
                ]);
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM users $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Get users
$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

include 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 dashboard-sidebar p-3">
            <h5 class="text-white mb-4">
                <i class="fa fa-user-shield"></i> Admin Panel
            </h5>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fa fa-dashboard"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="admin-users.php">
                        <i class="fa fa-users"></i> Users Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin-gallery.php">
                        <i class="fa fa-images"></i> Gallery Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin-events.php">
                        <i class="fa fa-calendar"></i> Events Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin-donations.php">
                        <i class="fa fa-heart"></i> Donations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin-complaints.php">
                        <i class="fa fa-exclamation-triangle"></i> Complaints
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin-projects.php">
                        <i class="fa fa-project-diagram"></i> Projects
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin-sliders.php">
                        <i class="fa fa-sliders-h"></i> Slider Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fa fa-sign-out"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 dashboard-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fa fa-users"></i> Users Management</h2>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fa fa-home"></i> Back to Website
                </a>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search users..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-3 text-end">
                            <span class="badge bg-info fs-6">Total: <?php echo $total_users; ?> users</span>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr id="user-<?php echo $user['id']; ?>">
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <?php if ($user['photo']): ?>
                                            <img src="<?php echo htmlspecialchars($user['photo']); ?>" 
                                                 alt="Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fa fa-user text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['user_type'] === 'admin' ? 'danger' : ($user['user_type'] === 'coordinator' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($user['user_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'approved' ? 'success' : ($user['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <?php if ($user['status'] === 'pending'): ?>
                                            <button class="btn btn-sm btn-success" onclick="approveUser(<?php echo $user['id']; ?>)">
                                                <i class="fa fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="rejectUser(<?php echo $user['id']; ?>)">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Users pagination">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_type" class="form-label">User Type</label>
                        <select class="form-select" id="edit_user_type" name="user_type" required>
                            <option value="user">User</option>
                            <option value="coordinator">Coordinator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function approveUser(userId) {
    if (confirm('Are you sure you want to approve this user?')) {
        fetch('admin-users.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=approve&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function rejectUser(userId) {
    if (confirm('Are you sure you want to reject this user?')) {
        fetch('admin-users.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=reject&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch('admin-users.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=delete&user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`user-${userId}`).remove();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function editUser(userId) {
    // Get user data from the table row
    const row = document.getElementById(`user-${userId}`);
    const cells = row.getElementsByTagName('td');
    
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_name').value = cells[2].textContent;
    document.getElementById('edit_email').value = cells[3].textContent;
    document.getElementById('edit_phone').value = cells[4].textContent;
    document.getElementById('edit_user_type').value = cells[5].textContent.toLowerCase();
    document.getElementById('edit_status').value = cells[6].textContent.toLowerCase();
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update');
    
    fetch('admin-users.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});
</script>

</body>
</html>
