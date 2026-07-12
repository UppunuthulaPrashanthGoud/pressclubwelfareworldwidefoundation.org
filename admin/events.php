<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin and coordinator can access this page
if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'Event Management';
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
                    $title = sanitizeInput($_POST['title']);
                    $description = $_POST['description'];
                    $event_date = sanitizeInput($_POST['event_date']);
                    $event_time = sanitizeInput($_POST['event_time']);
                    $location = sanitizeInput($_POST['location']);
                    $status = sanitizeInput($_POST['status']);
                    
                    // Handle image upload
                    $image = '';
                    if (!empty($_FILES['image']['name'])) {
                        $upload_dir = '../img/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_ext, $allowed_ext)) {
                            $image = uniqid('event_') . '.' . $file_ext;
                            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
                        }
                    }
                    
                    if ($_POST['action'] === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO events (title, description, event_date, event_time, location, image, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$title, $description, $event_date, $event_time, $location, $image, $status]);
                        $message = 'Event added successfully!';
                    } else {
                        // Keep existing image if no new image uploaded
                        if (empty($image)) {
                            $stmt = $db->prepare("SELECT image FROM events WHERE id = ?");
                            $stmt->execute([$id]);
                            $existing = $stmt->fetch();
                            $image = $existing['image'] ?? '';
                        }
                        
                        $stmt = $db->prepare("
                            UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, image = ?, status = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $description, $event_date, $event_time, $location, $image, $status, $id]);
                        $message = 'Event updated successfully!';
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
                    $stmt = $db->prepare("SELECT image FROM events WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $event = $stmt->fetch();
                    
                    // Delete event
                    $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete image if it exists
                    if (!empty($event['image']) && file_exists('../img/' . $event['image'])) {
                        unlink('../img/' . $event['image']);
                    }
                    
                    $message = 'Event deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting event: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get event data for editing
$event_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $event_data = $stmt->fetch();
    
    if (!$event_data) {
        $error = 'Event not found!';
        $action = 'list';
    }
}

// Get events list
$events_list = [];
if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM events ORDER BY event_date DESC");
    $stmt->execute();
    $events_list = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-calendar me-3"></i>Event Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Event
                </a>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
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
        <!-- Events List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Events List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Date/Time</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events_list as $event): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($event['image'])): ?>
                                    <img src="<?php echo SITE_URL . '/img/' . $event['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                         class="img-thumbnail" width="60" height="60">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-calendar text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr(strip_tags($event['description']), 0, 80)) . '...'; ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?php echo date('d M Y', strtotime($event['event_date'])); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($event['event_time'])); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $event['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $event['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=edit&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEvent(<?php echo $event['id']; ?>)">
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
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> 
                    <?php echo $action === 'add' ? 'Add New Event' : 'Edit Event'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required
                               value="<?php echo htmlspecialchars($event_data['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($event_data['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="date" class="form-control" id="event_date" name="event_date" required
                                       value="<?php echo htmlspecialchars($event_data['event_date'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="event_time" class="form-label">Time *</label>
                                <input type="time" class="form-control" id="event_time" name="event_time" required
                                       value="<?php echo htmlspecialchars($event_data['event_time'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo ($event_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($event_data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Location *</label>
                        <input type="text" class="form-control" id="location" name="location" required
                               value="<?php echo htmlspecialchars($event_data['location'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if (!empty($event_data['image'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo SITE_URL . '/img/' . $event_data['image']; ?>" 
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
function deleteEvent(id) {
    if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>