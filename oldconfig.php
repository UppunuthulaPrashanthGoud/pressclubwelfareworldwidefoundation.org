<?php
// config.php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'sanatandharmajagruti');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/sanatandharmajagruti');
define('SITE_NAME', 'Sanatan Dharma Jagruti');

// Database connection class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Helper functions
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isCoordinator() {
    return isLoggedIn() && isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'coordinator');
}

function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
        session_unset();
        session_destroy();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

function generateCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function uploadImage($file) {
    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size too large. Maximum 5MB allowed.');
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . date('mdYHis') . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file.');
    }
    
    return $filename;
}

function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Validate CSRF token
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        jsonResponse(false, 'Invalid CSRF token');
    }
    
    $db = Database::getInstance()->getConnection();
    
    switch ($action) {
        case 'manage_gallery':
            $operation = $_POST['operation'] ?? '';
            
            switch ($operation) {
                case 'add':
                    try {
                        $title = sanitizeInput($_POST['title']);
                        $description = sanitizeInput($_POST['description'] ?? '');
                        $status = sanitizeInput($_POST['status'] ?? 'active');
                        
                        if (empty($title)) {
                            jsonResponse(false, 'Title is required');
                        }
                        
                        $imageName = '';
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $imageName = uploadImage($_FILES['image']);
                        } else {
                            jsonResponse(false, 'Image is required');
                        }
                        
                        $stmt = $db->prepare("INSERT INTO gallery (title, description, image, status, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->execute([$title, $description, $imageName, $status]);
                        
                        jsonResponse(true, 'Gallery image added successfully');
                        
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error: ' . $e->getMessage());
                    }
                    break;
                    
                case 'edit':
                    try {
                        $id = intval($_POST['id']);
                        $title = sanitizeInput($_POST['title']);
                        $description = sanitizeInput($_POST['description'] ?? '');
                        $status = sanitizeInput($_POST['status'] ?? 'active');
                        
                        if (empty($title)) {
                            jsonResponse(false, 'Title is required');
                        }
                        
                        $imageName = '';
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $imageName = uploadImage($_FILES['image']);
                            // Delete old image
                            $stmt = $db->prepare("SELECT image FROM gallery WHERE id = ?");
                            $stmt->execute([$id]);
                            $oldImage = $stmt->fetchColumn();
                            if ($oldImage && file_exists('uploads/' . $oldImage)) {
                                unlink('uploads/' . $oldImage);
                            }
                        }
                        
                        $sql = "UPDATE gallery SET title = ?, description = ?, status = ?";
                        $params = [$title, $description, $status];
                        
                        if (!empty($imageName)) {
                            $sql .= ", image = ?";
                            $params[] = $imageName;
                        }
                        
                        $sql .= " WHERE id = ?";
                        $params[] = $id;
                        
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        
                        jsonResponse(true, 'Gallery image updated successfully');
                        
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error: ' . $e->getMessage());
                    }
                    break;
                    
                case 'list':
                    try {
                        $stmt = $db->query("SELECT * FROM gallery ORDER BY created_at DESC");
                        $gallery = $stmt->fetchAll();
                        jsonResponse(true, 'Gallery loaded successfully', $gallery);
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error loading gallery');
                    }
                    break;
                    
                case 'delete':
                    try {
                        $id = intval($_POST['id']);
                        
                        // Get image filename before deleting
                        $stmt = $db->prepare("SELECT image FROM gallery WHERE id = ?");
                        $stmt->execute([$id]);
                        $item = $stmt->fetch();
                        
                        if ($item) {
                            // Delete from database
                            $stmt = $db->prepare("DELETE FROM gallery WHERE id = ?");
                            $stmt->execute([$id]);
                            
                            // Delete image file
                            if ($item['image'] && file_exists('uploads/' . $item['image'])) {
                                unlink('uploads/' . $item['image']);
                            }
                            
                            jsonResponse(true, 'Gallery item deleted successfully');
                        } else {
                            jsonResponse(false, 'Gallery item not found');
                        }
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error deleting gallery item');
                    }
                    break;
                    
                case 'view':
                    try {
                        $id = intval($_POST['id']);
                        $stmt = $db->prepare("SELECT * FROM gallery WHERE id = ?");
                        $stmt->execute([$id]);
                        $item = $stmt->fetch();
                        
                        if ($item) {
                            jsonResponse(true, 'Gallery item loaded successfully', $item);
                        } else {
                            jsonResponse(false, 'Gallery item not found');
                        }
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error loading gallery item');
                    }
                    break;
            }
            break;
            
        case 'manage_events':
            $operation = $_POST['operation'] ?? '';
            
            switch ($operation) {
                case 'add':
                    try {
                        $title = sanitizeInput($_POST['title']);
                        $description = sanitizeInput($_POST['description']);
                        $event_date = $_POST['event_date'];
                        $event_time = $_POST['event_time'];
                        $location = sanitizeInput($_POST['location']);
                        
                        if (empty($title) || empty($description) || empty($event_date) || empty($event_time) || empty($location)) {
                            jsonResponse(false, 'All fields are required');
                        }
                        
                        $imageName = '';
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $imageName = uploadImage($_FILES['image']);
                        }
                        
                        $stmt = $db->prepare("INSERT INTO events (title, description, event_date, event_time, location, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
                        $stmt->execute([$title, $description, $event_date, $event_time, $location, $imageName]);
                        
                        jsonResponse(true, 'Event added successfully');
                        
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error: ' . $e->getMessage());
                    }
                    break;
                    
                case 'list':
                    try {
                        $stmt = $db->query("SELECT * FROM events ORDER BY event_date DESC");
                        $events = $stmt->fetchAll();
                        jsonResponse(true, 'Events loaded successfully', $events);
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error loading events');
                    }
                    break;
                    
                case 'delete':
                    try {
                        $id = intval($_POST['id']);
                        
                        // Get image filename before deleting
                        $stmt = $db->prepare("SELECT image FROM events WHERE id = ?");
                        $stmt->execute([$id]);
                        $item = $stmt->fetch();
                        
                        if ($item) {
                            // Delete from database
                            $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
                            $stmt->execute([$id]);
                            
                            // Delete image file if exists
                            if ($item['image'] && file_exists('uploads/' . $item['image'])) {
                                unlink('uploads/' . $item['image']);
                            }
                            
                            jsonResponse(true, 'Event deleted successfully');
                        } else {
                            jsonResponse(false, 'Event not found');
                        }
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error deleting event');
                    }
                    break;
            }
            break;
            
        case 'manage_users':
            if (!isAdmin()) {
                jsonResponse(false, 'Access denied');
            }
            
            $operation = $_POST['operation'] ?? '';
            
            switch ($operation) {
                case 'list':
                    try {
                        $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
                        $users = $stmt->fetchAll();
                        jsonResponse(true, 'Users loaded successfully', $users);
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error loading users');
                    }
                    break;
                    
                case 'approve':
                    try {
                        $userId = intval($_POST['user_id']);
                        $stmt = $db->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
                        $stmt->execute([$userId]);
                        jsonResponse(true, 'User approved successfully');
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error approving user');
                    }
                    break;
                    
                case 'reject':
                    try {
                        $userId = intval($_POST['user_id']);
                        $stmt = $db->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
                        $stmt->execute([$userId]);
                        jsonResponse(true, 'User rejected successfully');
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error rejecting user');
                    }
                    break;
                    
                case 'delete':
                    try {
                        $userId = intval($_POST['user_id']);
                        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        jsonResponse(true, 'User deleted successfully');
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error deleting user');
                    }
                    break;
                    
                case 'view':
                    try {
                        $userId = intval($_POST['user_id']);
                        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $user = $stmt->fetch();
                        
                        if ($user) {
                            jsonResponse(true, 'User details loaded', $user);
                        } else {
                            jsonResponse(false, 'User not found');
                        }
                    } catch (Exception $e) {
                        jsonResponse(false, 'Error loading user details');
                    }
                    break;
            }
            break;
            
        default:
            jsonResponse(false, 'Invalid action');
    }
}
?>