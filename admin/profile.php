<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

$pageTitle = 'मेरी प्रोफाइल';
$db = getDbConnection();

$message = '';
$error = '';

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

if (!$user_data) {
    $error = 'उपयोगकर्ता डेटा नहीं मिला।';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'अमान्य CSRF टोकन।';
    } else {
        switch ($_POST['action']) {
            case 'update_profile':
                try {
                    $name = sanitizeInput($_POST['name'] ?? '');
                    $email = sanitizeInput($_POST['email'] ?? '');
                    $mobile = sanitizeInput($_POST['mobile'] ?? '');
                    
                    // Validate mobile number
                    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
                        throw new Exception('मोबाइल नंबर 10 अंकों का होना चाहिए।');
                    }
                    
                    // Check if email already exists for other users
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->execute([$email, $user_id]);
                    if ($stmt->fetch()) {
                        throw new Exception('यह ईमेल पहले से ही उपयोग में है।');
                    }
                    
                    // Update user data
                    $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, mobile = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $mobile, $user_id]);
                    
                    // Update session data
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    $message = 'प्रोफाइल सफलतापूर्वक अपडेट की गई!';
                    
                    // Refresh user data
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_data = $stmt->fetch();
                    
                } catch (Exception $e) {
                    $error = 'प्रोफाइल अपडेट करने में त्रुटि: ' . $e->getMessage();
                }
                break;
                
            case 'change_password':
                try {
                    $current_password = $_POST['current_password'] ?? '';
                    $new_password = $_POST['new_password'] ?? '';
                    $confirm_password = $_POST['confirm_password'] ?? '';
                    
                    // Verify current password
                    if (!password_verify($current_password, $user_data['password'] ?? '')) {
                        throw new Exception('वर्तमान पासवर्ड गलत है।');
                    }
                    
                    // Check if new passwords match
                    if ($new_password !== $confirm_password) {
                        throw new Exception('नया पासवर्ड और पुष्टि पासवर्ड मेल नहीं खाते।');
                    }
                    
                    // Validate password strength
                    if (strlen($new_password) < 6) {
                        throw new Exception('पासवर्ड कम से कम 6 अक्षर का होना चाहिए।');
                    }
                    
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user_id]);
                    
                    $message = 'पासवर्ड सफलतापूर्वक बदला गया!';
                    
                } catch (Exception $e) {
                    $error = 'पासवर्ड बदलने में त्रुटि: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRF();

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-user me-3"></i>मेरी प्रोफाइल
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
            <!-- Profile Information -->
            <div class="col-lg-8">
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-edit"></i> प्रोफाइल जानकारी</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">नाम *</label>
                                        <input type="text" class="form-control" id="name" name="name" required
                                               value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">ईमेल *</label>
                                        <input type="email" class="form-control" id="email" name="email" required
                                               value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                                    </div>
                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mobile" class="form-label">मोबाइल *</label>
                                        <input type="tel" class="form-control" id="mobile" name="mobile" required
                                               pattern="[0-9]{10}" title="मोबाइल नंबर 10 अंकों का होना चाहिए"
                                               value="<?php echo htmlspecialchars($user_data['mobile'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="user_type" class="form-label">उपयोगकर्ता प्रकार</label>
                                        <input type="text" class="form-control" id="user_type" readonly
                                               value="<?php echo ucfirst($user_data['user_type'] ?? 'member'); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="created_at" class="form-label">खाता बनाया गया</label>
                                        <input type="text" class="form-control" readonly
                                               value="<?php echo isset($user_data['created_at']) ? date('d M Y, h:i A', strtotime($user_data['created_at'])) : 'N/A'; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> प्रोफाइल अपडेट करें
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-lock"></i> पासवर्ड बदलें</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">वर्तमान पासवर्ड *</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">नया पासवर्ड *</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">पासवर्ड की पुष्टि करें *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key"></i> पासवर्ड बदलें
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Member Information -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-id-card"></i> सदस्यता जानकारी</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($user_data['profile_image'])): ?>
                        <img src="<?php echo SITE_URL . '/uploads/profiles/' . htmlspecialchars($user_data['profile_image']); ?>" 
                             alt="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" 
                             class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                        <?php else: ?>
                        <div class="avatar-placeholder mx-auto mb-3" style="width: 150px; height: 150px; font-size: 3rem;">
                            <?php echo strtoupper(substr($user_data['name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <?php endif; ?>
                        
                        <h5><?php echo htmlspecialchars($user_data['name'] ?? 'N/A'); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($user_data['designation'] ?? 'सदस्य'); ?></p>
                        
                        <div class="member-details">
                            <div class="detail-item">
                                <strong>सदस्य ID:</strong>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($user_data['registration_id'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="detail-item mt-2">
                                <strong>स्थिति:</strong>
                                <span class="badge bg-<?php echo ($user_data['status'] ?? 'pending') === 'approved' ? 'success' : (($user_data['status'] ?? 'pending') === 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($user_data['status'] ?? 'pending'); ?>
                                </span>
                            </div>
                            <div class="detail-item mt-2">
                                <strong>सदस्यता प्रकार:</strong>
                                <span class="badge bg-info"><?php echo htmlspecialchars(getMembershipTypeName($user_data['membership_type'] ?? 'N/A')); ?></span>
                            </div>
                            <div class="detail-item mt-2">
                                <strong>जुड़ने की तारीख:</strong>
                                <small><?php echo isset($user_data['created_at']) ? date('d M Y', strtotime($user_data['created_at'])) : 'N/A'; ?></small>
                            </div>
                            <?php if ($user_data['valid_until']): ?>
                            <div class="detail-item mt-2">
                                <strong>वैधता तिथि:</strong>
                                <small><?php echo date('d M Y', strtotime($user_data['valid_until'])); ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($user_data['status'] === 'approved'): ?>
                        <div class="mt-3">
                            <a href="id-card-generator.php?user_id=<?php echo $user_id; ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> आईडी कार्ड डाउनलोड करें
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/sidebar.php'; ?>

<style>
.avatar-placeholder {
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.member-details .detail-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.member-details .detail-item:last-child {
    border-bottom: none;
}
</style>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('पासवर्ड मेल नहीं खाते');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer.php'; ?>