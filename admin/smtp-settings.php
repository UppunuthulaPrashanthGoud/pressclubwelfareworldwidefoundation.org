<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Check admin permissions
if (!isAdmin()) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

$pageTitle = 'SMTP Settings';
$db = getDbConnection();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
        logError($error);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_smtp') {
        try {
            $host = trim(sanitizeInput($_POST['host']));
            $port = intval($_POST['port']);
            $username = trim(sanitizeInput($_POST['username']));
            $password = trim(sanitizeInput($_POST['password']));
            $encryption = trim(sanitizeInput($_POST['encryption']));
            
            // Validate inputs
            $errors = [];
            if (empty($host)) $errors[] = 'SMTP host is required.';
            if ($port <= 0 || $port > 65535) $errors[] = 'A valid port number (1-65535) is required.';
            if (empty($username)) $errors[] = 'Username is required.';
            if (empty($password)) $errors[] = 'Password is required.';
            if (!in_array($encryption, ['ssl', 'tls'])) $errors[] = 'Select a valid encryption type (SSL or TLS).';
            if (!filter_var($username, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
            
            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }
            
            // Check if record exists
            $stmt = $db->prepare("SELECT COUNT(*) FROM smtp_settings WHERE id = 1");
            $stmt->execute();
            $exists = $stmt->fetchColumn();
            
            if ($exists) {
                // Update existing record
                $stmt = $db->prepare("
                    UPDATE smtp_settings 
                    SET host = ?, port = ?, username = ?, password = ?, encryption = ? 
                    WHERE id = 1
                ");
                $stmt->execute([$host, $port, $username, $password, $encryption]);
            } else {
                // Insert new record
                $stmt = $db->prepare("
                    INSERT INTO smtp_settings (id, host, port, username, password, encryption) 
                    VALUES (1, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$host, $port, $username, $password, $encryption]);
            }
            
            $message = 'SMTP settings successfully updated!';
            
        } catch (Exception $e) {
            $error = 'Error updating SMTP settings: ' . $e->getMessage();
            logError($error);
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'test_email') {
        try {
            $test_email = trim(sanitizeInput($_POST['test_email']));
            
            if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Enter a valid email address.');
            }
            
            $subject = "SMTP Test Email - " . ORGANIZATION_NAME;
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #25313e; text-align: center;'>SMTP Configuration Test</h2>
                <p>This is a test email sent to verify that your SMTP settings are working correctly.</p>
                <p><strong>Sent Time:</strong> " . date('d-m-Y H:i:s') . "</p>
                <p><strong>Organization:</strong> " . ORGANIZATION_NAME . "</p>
                <hr style='margin: 20px 0;'>
                <p style='text-align: center; color: #666; font-size: 12px;'>
                    If you received this email, your SMTP settings have been successfully configured.
                </p>
            </div>";
            
            if (sendEmail($test_email, $subject, $body, true)) {
                $message = 'Test email successfully sent!';
            } else {
                throw new Exception('Failed to send test email.');
            }
            
        } catch (Exception $e) {
            $error = 'Error sending test email: ' . $e->getMessage();
            logError($error);
        }
    }
}

// Get current SMTP settings
$smtpSettings = getSMTPConfig();

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<style>
/* Consistent table styles with about_content.php */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table th, .table td {
    vertical-align: middle;
    font-size: 14px;
    padding: 8px;
}

.table img {
    width: 40px;
    height: 40px;
    object-fit: cover;
}

.btn-group .btn {
    padding: 4px 8px;
    font-size: 12px;
}

.badge {
    font-size: 12px;
}

/* Consistent modal styles with about_content.php */
.modal {
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
}

.modal.fade .modal-dialog {
    transition: transform 0.2s ease-out;
    transform: translate(0, -25px);
}

.modal.show .modal-dialog {
    transform: translate(0, 0);
}

.modal-backdrop {
    transition: opacity 0.15s linear;
}

.modal-backdrop.fade {
    opacity: 0;
}

.modal-backdrop.show {
    opacity: 0.5;
}

.modal-open {
    overflow: hidden;
    padding-right: 0 !important;
}

/* Test email modal specific styles */
.test-email-modal {
    z-index: 1055;
}

.test-email-modal .modal-dialog {
    margin: 1.75rem auto;
    max-width: 400px;
}

.test-email-modal .modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.test-email-modal .modal-header {
    background-color: #007bff;
    color: white;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
}

.test-email-modal .modal-header .btn-close {
    filter: invert(1);
    opacity: 0.8;
}

.test-email-modal .modal-body {
    padding: 1.5rem;
    text-align: center;
}

.test-email-modal .modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    justify-content: center;
    gap: 10px;
}

/* Responsive design */
@media (max-width: 768px) {
    .table th, .table td {
        font-size: 12px;
        padding: 6px;
    }

    .btn-group .btn {
        padding: 3px 6px;
        font-size: 10px;
    }

    .test-email-modal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
}

@media (max-width: 576px) {
    .table th, .table td {
        font-size: 10px;
        padding: 4px;
    }

    .btn-group .btn {
        padding: 2px 4px;
        font-size: 9px;
    }

    .badge {
        font-size: 10px;
    }
}
</style>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-envelope-open-text"></i> <?php echo $pageTitle; ?>
            </h1>
            <div class="page-actions">
                <a href="<?php echo SITE_URL; ?>/admin/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- SMTP Configuration Form -->
        <div class="admin-card">
            <div class="card-header">
                <i class="fas fa-cog"></i> SMTP Configuration
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_smtp">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="host" class="form-label"><strong>SMTP Host:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="host" name="host" 
                                       value="<?php echo htmlspecialchars($smtpSettings['host']); ?>" 
                                       placeholder="smtp.gmail.com" required>
                                <div class="form-text">Example: smtp.gmail.com, smtp.hostinger.com</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="port" class="form-label"><strong>Port:</strong> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="port" name="port" 
                                       value="<?php echo htmlspecialchars($smtpSettings['port']); ?>" 
                                       placeholder="587" min="1" max="65535" required>
                                <div class="form-text">465 for SSL, 587 for TLS</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label"><strong>Username (Email):</strong> <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($smtpSettings['username']); ?>" 
                                       placeholder="your-email@domain.com" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label"><strong>Password:</strong> <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       value="<?php echo htmlspecialchars($smtpSettings['password']); ?>" 
                                       placeholder="Your email password" required>
                                <div class="form-text">Use an App Password for Gmail</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="encryption" class="form-label"><strong>Encryption:</strong> <span class="text-danger">*</span></label>
                                <select class="form-select" id="encryption" name="encryption" required>
                                    <option value="ssl" <?php echo $smtpSettings['encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="tls" <?php echo $smtpSettings['encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current Configuration Display -->
        <div class="admin-card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Current Configuration
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <tbody>
                            <tr>
                                <td><strong>SMTP Host:</strong></td>
                                <td><?php echo htmlspecialchars($smtpSettings['host']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Port:</strong></td>
                                <td><?php echo htmlspecialchars($smtpSettings['port']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td><?php echo htmlspecialchars($smtpSettings['username']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Encryption:</strong></td>
                                <td><?php echo strtoupper($smtpSettings['encryption']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-lightbulb"></i> Tips:</h6>
                    <ul class="mb-0">
                        <li>Use an App Password for Gmail</li>
                        <li>Hostinger: Port 465 (SSL) or 587 (TLS)</li>
                        <li>Send a test email after changing settings</li>
                        <li>PHPMailer library must be installed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade test-email-modal" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testEmailModalLabel">
                    <i class="fas fa-paper-plane"></i> Send Test Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="test_email">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label for="test_email" class="form-label"><strong>Test Email Address:</strong></label>
                        <input type="email" class="form-control" id="test_email" name="test_email" 
                               placeholder="test@example.com" required>
                        <div class="form-text">The test message will be sent to this email</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Test Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show/hide password
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'btn btn-outline-secondary';
    toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
    toggleButton.style.position = 'absolute';
    toggleButton.style.right = '10px';
    toggleButton.style.top = '50%';
    toggleButton.style.transform = 'translateY(-50%)';
    toggleButton.style.border = 'none';
    toggleButton.style.background = 'transparent';
    
    passwordField.parentNode.style.position = 'relative';
    passwordField.parentNode.appendChild(toggleButton);
    
    toggleButton.addEventListener('click', function() {
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            passwordField.type = 'password';
            toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
        }
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Prevent form double submission
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach(btn => {
                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
                // Re-enable after 3 seconds in case of error
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 3000);
            });
        });
    });

    // Handle modal cleanup
    document.addEventListener('hidden.bs.modal', function () {
        // Remove modal backdrop
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
            backdrop.remove();
        });
        
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });

    // Initialize tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>