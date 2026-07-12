<?php
// ajax-handler.php
require_once 'config/config.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Validate CSRF token
if (!validateCSRF($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, 'Invalid CSRF token', null, 403);
}

$action = $_POST['action'] ?? '';
$db = Database::getInstance()->getConnection();

switch ($action) {
    case 'user_login':
        handleUserLogin($db);
        break;
        
    case 'manage_gallery':
        requireAuth('admin');
        handleGalleryManagement($db);
        break;
        
    case 'manage_events':
        requireAuth('coordinator');
        handleEventManagement($db);
        break;
        
    case 'manage_users':
        requireAuth('admin');
        handleUserManagement($db);
        break;
        
    default:
        jsonResponse(false, 'Invalid action', null, 400);
}

function handleUserLogin($db) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        jsonResponse(false, 'Please fill in all fields.');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Invalid email format.');
    }
    
    try {
        $stmt = $db->prepare("
            SELECT id, name, email, password, user_type, status, login_attempts, last_login_attempt 
            FROM users 
            WHERE email = ? AND user_type = 'user'
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            jsonResponse(false, 'Invalid credentials.');
        }
        
        // Check if account is locked due to failed attempts
        if ($user['login_attempts'] >= 5) {
            $lockoutTime = strtotime($user['last_login_attempt']) + (15 * 60); // 15 minutes
            if (time() < $lockoutTime) {
                jsonResponse(false, 'Account temporarily locked due to failed login attempts. Try again later.');
            }
        }
        
        if ($user['status'] !== 'approved') {
            jsonResponse(false, 'Account not approved or inactive.');
        }
        
        if (!password_verify($password, $user['password'])) {
            // Increment failed login attempts
            $stmt = $db->prepare("
                UPDATE users 
                SET login_attempts = login_attempts + 1, last_login_attempt = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);
            
            jsonResponse(false, 'Invalid credentials.');
        }
        
        // Successful login - reset attempts and set session
        $stmt = $db->prepare("
            UPDATE users 
            SET login_attempts = 0, last_login = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$user['id']]);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
        
        logActivity('User Login', 'Successful login');
        
        jsonResponse(true, 'Login successful');
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        jsonResponse(false, 'Login failed. Please try again.');
    }
}

function handleGalleryManagement($db) {
    $operation = $_POST['operation'] ?? '';
    
    switch ($operation) {
        case 'add':
            addGalleryItem($db);
            break;
            
        case 'edit':
            editGalleryItem($db);
            break;
            
        case 'list':
            listGalleryItems($db);
            break;
            
        case 'delete':
            deleteGalleryItem($db);
            break;
            
        case 'view':
            viewGalleryItem($db);
            break;
            
        default:
            jsonResponse(false, 'Invalid gallery operation');
    }
}

function addGalleryItem($db) {
    try {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? 'active');
        
        if (empty($title)) {
            jsonResponse(false, 'Title is required');
        }
        
        if (!in_array($status, ['active', 'inactive'])) {
            $status = 'active';
        }
        
        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = uploadImage($_FILES['image']);
        } else {
            jsonResponse(false, 'Image is required');
        }
        
        $stmt = $db->prepare("
            INSERT INTO gallery (title, description, image, status, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $description, $imageName, $status, $_SESSION['user_id']]);
        
        logActivity('Gallery Add', "Added gallery item: $title");
        jsonResponse(true, 'Gallery image added successfully');
        
    } catch (Exception $e) {
        error_log("Gallery add error: " . $e->getMessage());
        jsonResponse(false, 'Error: ' . $e->getMessage());
    }
}

function editGalleryItem($db) {
    try {
        $id = intval($_POST['id'] ?? 0);
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? 'active');
        
        if ($id <= 0) {
            jsonResponse(false, 'Invalid gallery ID');
        }
        
        if (empty($title)) {
            jsonResponse(false, 'Title is required');
        }
        
        if (!in_array($status, ['active', 'inactive'])) {
            $status = 'active';
        }
        
        // Check if gallery item exists
        $stmt = $db->prepare("SELECT * FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $existingItem = $stmt->fetch();
        
        if (!$existingItem) {
            jsonResponse(false, 'Gallery item not found');
        }
        
        $imageName = $existingItem['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $newImageName = uploadImage($_FILES['image']);
            
            // Delete old image
            if ($existingItem['image']) {
                deleteImage($existingItem['image']);
            }
            
            $imageName = $newImageName;
        }
        
        $stmt = $db->prepare("
            UPDATE gallery 
            SET title = ?, description = ?, image = ?, status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $imageName, $status, $id]);
        
        logActivity('Gallery Edit', "Edited gallery item: $title (ID: $id)");
        jsonResponse(true, 'Gallery image updated successfully');
        
    } catch (Exception $e) {
        error_log("Gallery edit error: " . $e->getMessage());
        jsonResponse(false, 'Error: ' . $e->getMessage());
    }
}

function listGalleryItems($db) {
    try {
        $stmt = $db->prepare("
            SELECT g.*, u.name as created_by_name 
            FROM gallery g 
            LEFT JOIN users u ON g.created_by = u.id 
            ORDER BY g.created_at DESC
        ");
        $stmt->execute();
        $gallery = $stmt->fetchAll();
        
        jsonResponse(true, 'Gallery loaded successfully', $gallery);
        
    } catch (Exception $e) {
        error_log("Gallery list error: " . $e->getMessage());
        jsonResponse(false, 'Error loading gallery');
    }
}

function deleteGalleryItem($db) {
    try {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            jsonResponse(false, 'Invalid gallery ID');
        }
        
        $stmt = $db->prepare("SELECT * FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            jsonResponse(false, 'Gallery item not found');
        }
        
        $stmt = $db->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete associated image
        if ($item['image']) {
            deleteImage($item['image']);
        }
        
        logActivity('Gallery Delete', "Deleted gallery item: {$item['title']} (ID: $id)");
        jsonResponse(true, 'Gallery item deleted successfully');
        
    } catch (Exception $e) {
        error_log("Gallery delete error: " . $e->getMessage());
        jsonResponse(false, 'Error deleting gallery item');
    }
}

function viewGalleryItem($db) {
    try {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            jsonResponse(false, 'Invalid gallery ID');
        }
        
        $stmt = $db->prepare("
            SELECT g.*, u.name as created_by_name 
            FROM gallery g 
            LEFT JOIN users u ON g.created_by = u.id 
            WHERE g.id = ?
        ");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if ($item) {
            jsonResponse(true, 'Gallery item loaded successfully', $item);
        } else {
            jsonResponse(false, 'Gallery item not found');
        }
        
    } catch (Exception $e) {
        error_log("Gallery view error: " . $e->getMessage());
        jsonResponse(false, 'Error loading gallery item');
    }
}

function handleEventManagement($db) {
    $operation = $_POST['operation'] ?? '';
    
    switch ($operation) {
        case 'add':
            addEvent($db);
            break;
            
        case 'list':
            listEvents($db);
            break;
            
        case 'delete':
            deleteEvent($db);
            break;
            
        default:
            jsonResponse(false, 'Invalid event operation');
    }
}

function addEvent($db) {
    try {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $location = sanitizeInput($_POST['location'] ?? '');
        
        if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($location)) {
            jsonResponse(false, 'All fields are required');
        }
        
        // Validate date format
        if (!DateTime::createFromFormat('Y-m-d', $event_date)) {
            jsonResponse(false, 'Invalid date format');
        }
        
        // Validate time format
        if (!DateTime::createFromFormat('H:i', $event_time)) {
            jsonResponse(false, 'Invalid time format');
        }
        
        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = uploadImage($_FILES['image']);
        }
        
        $stmt = $db->prepare("
            INSERT INTO events (title, description, event_date, event_time, location, image, status, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'active', ?, NOW())
        ");
        $stmt->execute([$title, $description, $event_date, $event_time, $location, $imageName, $_SESSION['user_id']]);
        
        logActivity('Event Add', "Added event: $title");
        jsonResponse(true, 'Event added successfully');
        
    } catch (Exception $e) {
        error_log("Event add error: " . $e->getMessage());
        jsonResponse(false, 'Error: ' . $e->getMessage());
    }
}

function listEvents($db) {
    try {
        $stmt = $db->prepare("
            SELECT e.*, u.name as created_by_name 
            FROM events e 
            LEFT JOIN users u ON e.created_by = u.id 
            ORDER BY e.event_date DESC
        ");
        $stmt->execute();
        $events = $stmt->fetchAll();
        
        jsonResponse(true, 'Events loaded successfully', $events);
        
    } catch (Exception $e) {
        error_log("Events list error: " . $e->getMessage());
        jsonResponse(false, 'Error loading events');
    }
}

function deleteEvent($db) {
    try {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            jsonResponse(false, 'Invalid event ID');
        }
        
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            jsonResponse(false, 'Event not found');
        }
        
        $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete associated image
        if ($item['image']) {
            deleteImage($item['image']);
        }
        
        logActivity('Event Delete', "Deleted event: {$item['title']} (ID: $id)");
        jsonResponse(true, 'Event deleted successfully');
        
    } catch (Exception $e) {
        error_log("Event delete error: " . $e->getMessage());
        jsonResponse(false, 'Error deleting event');
    }
}

function handleUserManagement($db) {
    $operation = $_POST['operation'] ?? '';
    
    switch ($operation) {
        case 'list':
            listUsers($db);
            break;
            
        case 'approve':
            approveUser($db);
            break;
            
        case 'reject':
            rejectUser($db);
            break;
            
        case 'delete':
            deleteUser($db);
            break;
            
        case 'view':
            viewUser($db);
            break;
            
        default:
            jsonResponse(false, 'Invalid user operation');
    }
}

function listUsers($db) {
    try {
        $stmt = $db->prepare("
            SELECT id, name, email, phone, address, user_type, status, created_at, last_login 
            FROM users 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        jsonResponse(true, 'Users loaded successfully', $users);
        
    } catch (Exception $e) {
        error_log("Users list error: " . $e->getMessage());
        jsonResponse(false, 'Error loading users');
    }
}

function approveUser($db) {
    try {
        $userId = intval($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            jsonResponse(false, 'Invalid user ID');
        }
        
        $stmt = $db->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            logActivity('User Approve', "Approved user ID: $userId");
            jsonResponse(true, 'User approved successfully');
        } else {
            jsonResponse(false, 'User not found');
        }
        
    } catch (Exception $e) {
        error_log("User approve error: " . $e->getMessage());
        jsonResponse(false, 'Error approving user');
    }
}

function rejectUser($db) {
    try {
        $userId = intval($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            jsonResponse(false, 'Invalid user ID');
        }
        
        $stmt = $db->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            logActivity('User Reject', "Rejected user ID: $userId");
            jsonResponse(true, 'User rejected successfully');
        } else {
            jsonResponse(false, 'User not found');
        }
        
    } catch (Exception $e) {
        error_log("User reject error: " . $e->getMessage());
        jsonResponse(false, 'Error rejecting user');
    }
}

function deleteUser($db) {
    try {
        $userId = intval($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            jsonResponse(false, 'Invalid user ID');
        }
        
        // Don't allow deleting the current admin
        if ($userId == $_SESSION['user_id']) {
            jsonResponse(false, 'Cannot delete your own account');
        }
        
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            logActivity('User Delete', "Deleted user ID: $userId");
            jsonResponse(true, 'User deleted successfully');
        } else {
            jsonResponse(false, 'User not found');
        }
        
    } catch (Exception $e) {
        error_log("User delete error: " . $e->getMessage());
        jsonResponse(false, 'Error deleting user');
    }
}

function viewUser($db) {
    try {
        $userId = intval($_POST['user_id'] ?? 0);
        
        if ($userId <= 0) {
            jsonResponse(false, 'Invalid user ID');
        }
        
        $stmt = $db->prepare("
            SELECT id, name, email, phone, address, user_type, status, created_at, last_login 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            jsonResponse(true, 'User details loaded', $user);
        } else {
            jsonResponse(false, 'User not found');
        }
        
    } catch (Exception $e) {
        error_log("User view error: " . $e->getMessage());
        jsonResponse(false, 'Error loading user details');
    }
}
?>