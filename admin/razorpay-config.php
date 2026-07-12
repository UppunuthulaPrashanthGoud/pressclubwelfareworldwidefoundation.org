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
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        try {
            $environment = $_POST['environment'];
            $razorpay_key_id = trim($_POST['razorpay_key_id']);
            $razorpay_key_secret = trim($_POST['razorpay_key_secret']);
            $webhook_secret = trim($_POST['webhook_secret']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Check if record exists for this environment
            $stmt = $pdo->prepare("SELECT id FROM razorpay_config WHERE environment = ?");
            $stmt->execute([$environment]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing record
                $stmt = $pdo->prepare("UPDATE razorpay_config SET 
                    razorpay_key_id = ?, razorpay_key_secret = ?, webhook_secret = ?, is_active = ? 
                    WHERE environment = ?");
                $stmt->execute([$razorpay_key_id, $razorpay_key_secret, $webhook_secret, $is_active, $environment]);
            } else {
                // Insert new record
                $stmt = $pdo->prepare("INSERT INTO razorpay_config 
                    (environment, razorpay_key_id, razorpay_key_secret, webhook_secret, is_active) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$environment, $razorpay_key_id, $razorpay_key_secret, $webhook_secret, $is_active]);
            }
            
            $message = 'Razorpay configuration updated successfully.';
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Get current configurations
$stmt = $pdo->query("SELECT * FROM razorpay_config ORDER BY environment");
$configs = $stmt->fetchAll();

$local_config = [];
$live_config = [];

foreach ($configs as $config) {
    if ($config['environment'] === 'local') {
        $local_config = $config;
    } else {
        $live_config = $config;
    }
}

$pageTitle = 'Razorpay Configuration';
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Configuration - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="admin-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-credit-card me-3"></i>Razorpay Configuration
                </h1>
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

            <div class="row">
                <!-- Local Environment Configuration -->
                <div class="col-lg-6">
                    <div class="admin-card">
                        <div class="card-header">
                            <i class="fas fa-laptop"></i>
                            Local Environment (Test)
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="environment" value="local">
                                
                                <div class="mb-3">
                                    <label class="form-label">Razorpay Key ID</label>
                                    <input type="text" name="razorpay_key_id" class="form-control" 
                                           value="<?php echo htmlspecialchars($local_config['razorpay_key_id'] ?? ''); ?>" 
                                           placeholder="rzp_test_xxxxxxxxxx" required>
                                    <small class="form-text text-muted">Test Key ID starts with rzp_test_</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Razorpay Key Secret</label>
                                    <input type="password" name="razorpay_key_secret" class="form-control" 
                                           value="<?php echo htmlspecialchars($local_config['razorpay_key_secret'] ?? ''); ?>" 
                                           placeholder="Test Key Secret" required>
                                    <small class="form-text text-muted">Keep this secret and do not share with anyone</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Webhook Secret</label>
                                    <input type="text" name="webhook_secret" class="form-control" 
                                           value="<?php echo htmlspecialchars($local_config['webhook_secret'] ?? ''); ?>" 
                                           placeholder="Webhook Secret (Optional)">
                                    <small class="form-text text-muted">For webhook verification</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" 
                                               <?php echo (isset($local_config['is_active']) && $local_config['is_active']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label">
                                            Enable
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Local Config
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Live Environment Configuration -->
                <div class="col-lg-6">
                    <div class="admin-card">
                        <div class="card-header">
                            <i class="fas fa-globe"></i>
                            Live Environment (Production)
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="environment" value="live">
                                
                                <div class="mb-3">
                                    <label class="form-label">Razorpay Key ID</label>
                                    <input type="text" name="razorpay_key_id" class="form-control" 
                                           value="<?php echo htmlspecialchars($live_config['razorpay_key_id'] ?? ''); ?>" 
                                           placeholder="rzp_live_xxxxxxxxxx" required>
                                    <small class="form-text text-muted">Live Key ID starts with rzp_live_</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Razorpay Key Secret</label>
                                    <input type="password" name="razorpay_key_secret" class="form-control" 
                                           value="<?php echo htmlspecialchars($live_config['razorpay_key_secret'] ?? ''); ?>" 
                                           placeholder="Live Key Secret" required>
                                    <small class="form-text text-muted">Keep this secret and do not share with anyone</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Webhook Secret</label>
                                    <input type="text" name="webhook_secret" class="form-control" 
                                           value="<?php echo htmlspecialchars($live_config['webhook_secret'] ?? ''); ?>" 
                                           placeholder="Webhook Secret (Optional)">
                                    <small class="form-text text-muted">For webhook verification</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" 
                                               <?php echo (isset($live_config['is_active']) && $live_config['is_active']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label">
                                            Enable
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save Live Config
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    Setup Instructions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">For Test Environment:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Go to Razorpay Dashboard</li>
                                <li><i class="fas fa-check text-success"></i> Switch to Test Mode</li>
                                <li><i class="fas fa-check text-success"></i> Copy Test keys from API Keys section</li>
                                <li><i class="fas fa-check text-success"></i> Paste into Local configuration</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">For Live Environment:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-exclamation-triangle text-warning"></i> Complete KYC verification</li>
                                <li><i class="fas fa-exclamation-triangle text-warning"></i> Switch to Live Mode</li>
                                <li><i class="fas fa-exclamation-triangle text-warning"></i> Generate Live API Keys</li>
                                <li><i class="fas fa-exclamation-triangle text-warning"></i> Test carefully in Production</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Security Warning:</strong> Never expose API Keys in public repositories or frontend code.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>