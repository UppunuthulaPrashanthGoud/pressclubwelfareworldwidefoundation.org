<?php
/**
 * Enhanced auth_check.php with role-based data access control
 */
require_once '../config/config.php';

// Basic helper functions
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
}

if (!function_exists('isCoordinator')) {
    function isCoordinator() {
        return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'coordinator';
    }
}

if (!function_exists('isMember')) {
    function isMember() {
        return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'member';
    }
}

if (!function_exists('checkSessionTimeout')) {
    function checkSessionTimeout($timeout = 3600) { // 1 hour default
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            session_unset();
            session_destroy();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }
}

// Enhanced function to get accessible user IDs based on role
if (!function_exists('getAccessibleUserIds')) {
    function getAccessibleUserIds($db = null) {
        if (!$db) {
            $db = getDbConnection();
        }
        
        $userType = $_SESSION['user_type'] ?? 'member';
        $userId = $_SESSION['user_id'] ?? 0;
        
        switch ($userType) {
            case 'admin':
                // Admin can access all users
                return 'ALL';
                
            case 'coordinator':
                // Coordinator can access their own data and members in their working area
                $stmt = $db->prepare("
                    SELECT working_area, district, state 
                    FROM users 
                    WHERE id = ? 
                    LIMIT 1
                ");
                $stmt->execute([$userId]);
                $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$coordinator) {
                    return [$userId]; // Fallback to own ID only
                }
                
                // Get users in same working area/district/state + own record
                $stmt = $db->prepare("
                    SELECT id 
                    FROM users 
                    WHERE (
                        id = ? OR 
                        working_area = ? OR 
                        district = ? OR 
                        state = ?
                    ) AND user_type IN ('member', 'coordinator')
                ");
                $stmt->execute([
                    $userId,
                    $coordinator['working_area'],
                    $coordinator['district'],
                    $coordinator['state']
                ]);
                
                return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
                
            case 'member':
            default:
                // Member can only access their own data
                return [$userId];
        }
    }
}

// Enhanced function to check if user can access specific record
if (!function_exists('canAccessUser')) {
    function canAccessUser($targetUserId, $db = null) {
        $accessibleIds = getAccessibleUserIds($db);
        
        if ($accessibleIds === 'ALL') {
            return true;
        }
        
        return in_array($targetUserId, $accessibleIds);
    }
}

// Enhanced function to build WHERE clause for role-based access
if (!function_exists('buildAccessWhereClause')) {
    function buildAccessWhereClause($db = null, $tableAlias = '') {
        $accessibleIds = getAccessibleUserIds($db);
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        
        if ($accessibleIds === 'ALL') {
            return ['clause' => '', 'params' => []];
        }
        
        if (empty($accessibleIds)) {
            return ['clause' => " AND {$prefix}id = -1", 'params' => []]; // No access
        }
        
        $placeholders = str_repeat('?,', count($accessibleIds) - 1) . '?';
        return [
            'clause' => " AND {$prefix}id IN ($placeholders)",
            'params' => $accessibleIds
        ];
    }
}

// Check if user is logged in
if (!isLoggedIn()) {
    // Store the requested URL for redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Determine which login page to redirect to based on the requested page
    $login_page = SITE_URL . "/user-login.php";
    
    // Check if it's an admin-specific page
    $admin_pages = ['settings.php', 'users-management.php', 'site_config.php'];
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if (in_array($current_page, $admin_pages)) {
        $login_page = SITE_URL . "/admin-login.php";
    }
    
    header("Location: " . $login_page);
    exit;
}

// Role-based page access control
$page_permissions = [
    // Admin only pages
    'settings.php' => ['admin'],
    'site_config.php' => ['admin'],
    'bank_details.php' => ['admin'],
    'razorpay_config.php' => ['admin'],
    'authority-switcher.php' => ['admin'],
    
    // Admin and Coordinator pages
    'users-management.php' => ['admin', 'coordinator'], // Updated to include coordinator
    'members.php' => ['admin', 'coordinator'],
    'events.php' => ['admin', 'coordinator'],
    'news.php' => ['admin', 'coordinator'],
    'gallery.php' => ['admin', 'coordinator'],
    
    // All authenticated users can access these
    'index.php' => ['admin', 'coordinator', 'member'],
    'profile.php' => ['admin', 'coordinator', 'member'],
    'id-card-generator.php' => ['admin', 'coordinator', 'member'],
    'certificates.php' => ['admin', 'coordinator', 'member'],
    'donations.php' => ['admin', 'coordinator', 'member'],
    'campaigns.php' => ['admin', 'coordinator', 'member'],
];

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['user_type'] ?? 'member';

// Check if current page has specific permissions
if (isset($page_permissions[$current_page])) {
    $allowed_roles = $page_permissions[$current_page];
    
    if (!in_array($user_role, $allowed_roles)) {
        // Redirect to appropriate dashboard based on role
        $redirect_url = SITE_URL . "/admin/index.php";
        
        // Show access denied message
        $_SESSION['access_denied'] = "आपको इस पृष्ठ तक पहुंचने की अनुमति नहीं है।";
        
        header("Location: " . $redirect_url);
        exit;
    }
}

// Check session timeout (1 hour default)
if (!checkSessionTimeout(3600)) {
    session_destroy();
    header("Location: " . SITE_URL . "/user-login.php?timeout=1");
    exit;
}

// Function to check if user has specific permission
function hasPermission($permission) {
    $user_role = $_SESSION['user_type'] ?? 'member';
    
    $permissions = [
        'admin' => ['create', 'read', 'update', 'delete', 'manage_users', 'manage_settings', 'switch_authority'],
        'coordinator' => ['create', 'read', 'update'],
        'member' => ['read']
    ];
    
    return isset($permissions[$user_role]) && in_array($permission, $permissions[$user_role]);
}

// Set user context for templates
$GLOBALS['current_user'] = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'role' => $_SESSION['user_type'] ?? 'member',
    'can_switch_authority' => hasPermission('switch_authority'),
];
?>