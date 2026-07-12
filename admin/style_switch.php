<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Check if user has permission
if (!isAdmin()) {
    header('Location: ' . SITE_URL . '/admin/');
    exit();
}

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';

// Function to get available CSS files
function getAvailableStyles() {
    $cssFiles = [];
    $cssDir = __DIR__ . '/../css/';
    if (is_dir($cssDir)) {
        $files = scandir($cssDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
                $cssFiles[] = $file;
            }
        }
    }
    return $cssFiles;
}

// Handle style switch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'switch') {
    try {
        $newStyle = isset($_POST['style']) ? sanitizeInput($_POST['style']) : '';
        $cssFiles = getAvailableStyles();
        
        if (in_array($newStyle, $cssFiles)) {
            // Save the new style to the database
            $db = getDbConnection();
            $stmt = $db->prepare("UPDATE site_config SET active_style = :active_style WHERE id = 1");
            $stmt->execute(['active_style' => $newStyle]);
            
            $success = "Style switched to " . htmlspecialchars($newStyle) . " successfully.";
            header("Location: style_switch.php?success=" . urlencode($success));
            exit;
        } else {
            $error = "Invalid style selected.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get current active style from the database
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT active_style FROM site_config WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $activeStyle = $config['active_style'] ?? 'style.css';
} catch (PDOException $e) {
    $activeStyle = 'style.css'; // Fallback to default
    $error = "Error fetching style: " . $e->getMessage();
}

$pageTitle = "Style Management";
include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="admin-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-paint-brush me-2"></i> Style Management
                </h1>
            </div>
            
            <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-edit me-2"></i> Switch Style
                </div>
                <div class="card-body">
                    <form method="POST" class="style-switch-form">
                        <input type="hidden" name="action" value="switch">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="style" class="form-label">Select Style</label>
                                    <select class="form-select" id="style" name="style" required>
                                        <?php
                                        $cssFiles = getAvailableStyles();
                                        foreach ($cssFiles as $file) {
                                            $selected = $activeStyle === $file ? 'selected' : '';
                                            $displayName = ($file === 'style.css') ? 'Default Style' : htmlspecialchars($file);
                                            echo "<option value=\"$file\" $selected>$displayName</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="page-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Apply Style
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>