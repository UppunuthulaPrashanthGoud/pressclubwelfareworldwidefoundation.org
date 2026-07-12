<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin and coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'कार्यालय पदाधिकारी प्रबंधन';
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
                    $email = sanitizeInput($_POST['email'] ?? '');
                    $mobile = sanitizeInput($_POST['mobile'] ?? '');
                    $address = sanitizeInput($_POST['address'] ?? '');
                    $sort_order = filter_var($_POST['sort_order'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
                    $status = sanitizeInput($_POST['status']);
                    
                    // Handle image upload
                    $photo = '';
                    if (!empty($_FILES['photo']['name'])) {
                        $upload_dir = '../uploads/office-bearers/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_ext, $allowed_ext)) {
                            $photo = uniqid('office_bearer_') . '.' . $file_ext;
                            move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo);
                        } else {
                            throw new Exception('अमान्य छवि प्रारूप! केवल JPG, JPEG, PNG, या GIF की अनुमति है।');
                        }
                    }
                    
                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO office_bearers (name, designation, photo, email, mobile, address, sort_order, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$name, $designation, $photo, $email, $mobile, $address, $sort_order, $status]);
                        $message = 'कार्यालय पदाधिकारी सफलतापूर्वक जोड़ा गया!';
                    } else {
                        // Keep existing photo if no new photo uploaded
                        if (empty($photo)) {
                            $stmt = $db->prepare("SELECT photo FROM office_bearers WHERE id = ?");
                            $stmt->execute([$id]);
                            $existing = $stmt->fetch();
                            $photo = $existing['photo'] ?? '';
                        }
                        
                        $stmt = $db->prepare("
                            UPDATE office_bearers 
                            SET name = ?, designation = ?, photo = ?, email = ?, mobile = ?, address = ?, sort_order = ?, status = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$name, $designation, $photo, $email, $mobile, $address, $sort_order, $status, $id]);
                        $message = 'कार्यालय पदाधिकारी अपडेट किया गया!';
                    }
                    
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'त्रुटि: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                try {
                    $delete_id = $_POST['id'];
                    
                    // Get photo filename before deleting
                    $stmt = $db->prepare("SELECT photo FROM office_bearers WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $bearer = $stmt->fetch();
                    
                    // Delete office bearer
                    $stmt = $db->prepare("DELETE FROM office_bearers WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete photo if it exists
                    if (!empty($bearer['photo']) && file_exists('../uploads/office-bearers/' . $bearer['photo'])) {
                        unlink('../uploads/office-bearers/' . $bearer['photo']);
                    }
                    
                    $message = 'कार्यालय पदाधिकारी सफलतापूर्वक हटाया गया!';
                } catch (Exception $e) {
                    $error = 'हटाने में त्रुटि: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get office bearer data for editing
$bearer_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM office_bearers WHERE id = ?");
    $stmt->execute([$id]);
    $bearer_data = $stmt->fetch();
    
    if (!$bearer_data) {
        $error = 'कार्यालय पदाधिकारी नहीं मिला!';
        $action = 'list';
    }
}

// Get office bearers list
$office_bearers = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM office_bearers ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute();
    $office_bearers = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-user-tie me-3"></i>कार्यालय पदाधिकारी प्रबंधन
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> नया पदाधिकारी जोड़ें
                </a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> वापस जाएं
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
        <!-- Office Bearers List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> कार्यालय पदाधिकारी सूची</h5>
            </div>
            <div class="card-body">
                <!-- Search Bar -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="bearer-search" class="form-control" placeholder="नाम या पदनाम द्वारा खोजें...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="bearers-table">
                        <thead>
                            <tr>
                                <th>छवि</th>
                                <th>नाम</th>
                                <th>पदनाम</th>
                                <th>ईमेल</th>
                                <th>मोबाइल</th>
                                <th>स्थिति</th>
                                <th>बनाया गया</th>
                                <th>कार्य</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($office_bearers as $bearer): ?>
                            <tr data-name="<?php echo strtolower(htmlspecialchars($bearer['name'])); ?>" 
                                data-designation="<?php echo strtolower(htmlspecialchars($bearer['designation'])); ?>">
                                <td>
                                    <?php if (!empty($bearer['photo'])): ?>
                                    <img src="<?php echo SITE_URL . '/uploads/office-bearers/' . $bearer['photo']; ?>" 
                                         alt="<?php echo htmlspecialchars($bearer['name']); ?>" 
                                         class="img-thumbnail" width="60" height="60">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($bearer['name']); ?></td>
                                <td><?php echo htmlspecialchars($bearer['designation']); ?></td>
                                <td><?php echo htmlspecialchars($bearer['email']); ?></td>
                                <td><?php echo htmlspecialchars($bearer['mobile']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $bearer['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $bearer['status'] === 'active' ? 'सक्रिय' : 'निष्क्रिय'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($bearer['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $bearer['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBearer(<?php echo $bearer['id']; ?>)">
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
                    <h5><i class="fas fa-exclamation-circle"></i> कोई पदाधिकारी नहीं मिले।</h5>
                </div>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> 
                    <?php echo $action === 'add' ? 'नया पदाधिकारी जोड़ें' : 'पदाधिकारी संपादित करें'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">नाम *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo htmlspecialchars($bearer_data['name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="designation" class="form-label">पदनाम *</label>
                                <input type="text" class="form-control" id="designation" name="designation" required
                                       value="<?php echo htmlspecialchars($bearer_data['designation'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">ईमेल</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($bearer_data['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mobile" class="form-label">मोबाइल नंबर</label>
                                <input type="text" class="form-control" id="mobile" name="mobile"
                                       value="<?php echo htmlspecialchars($bearer_data['mobile'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">पता</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($bearer_data['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">क्रमबद्धता</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order"
                                       value="<?php echo htmlspecialchars($bearer_data['sort_order'] ?? '0'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">स्थिति *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($bearer_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>सक्रिय</option>
                                    <option value="inactive" <?php echo ($bearer_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>निष्क्रिय</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="photo" class="form-label">छवि</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <?php if (!empty($bearer_data['photo'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo SITE_URL . '/uploads/office-bearers/' . $bearer_data['photo']; ?>" 
                                 alt="Current Photo" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="?" class="btn btn-secondary">रद्द करें</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> सहेजें
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
function deleteBearer(id) {
    if (confirm('क्या आप वाकई इस पदाधिकारी को हटाना चाहते हैं? यह क्रिया पूर्ववत नहीं की जा सकती है।')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('bearer-search');
    const bearerRows = document.querySelectorAll('#bearers-table tbody tr');
    const noResultsDiv = document.getElementById('no-results');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        bearerRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const designation = row.getAttribute('data-designation');

            if (name.includes(searchTerm) || designation.includes(searchTerm)) {
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