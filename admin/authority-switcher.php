<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'Authority Management';
$success = '';
$error = '';

// Handle authority switch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'switch_authority') {
    if (!verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token';
    } else {
        $newAuthority = trim($_POST['authority_type']);
        if (in_array($newAuthority, ['president', 'secretary'])) {
            if (setCurrentAuthority($newAuthority)) {
                $success = 'Authority switched successfully to ' . ucfirst($newAuthority);
            } else {
                $error = 'Failed to switch authority';
            }
        } else {
            $error = 'Invalid authority type';
        }
    }
}

$currentAuthority = getCurrentAuthority();
$authorityNames = getAuthorityConfig($currentAuthority);

// Set UTF-8 encoding
header('Content-Type: text/html; charset=UTF-8');

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-user-tie"></i> Authority Management
            </h1>
            <div class="page-actions">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Current Authority:</strong> 
                    <?php 
                    echo htmlspecialchars($authorityNames['chairman_name'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($authorityNames['chairman_title'], ENT_QUOTES, 'UTF-8') . ')';
                    ?>
                </div>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Authority Switcher -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Switch Signing Authority
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="switch_authority">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                            
                            <div class="mb-4">
                                <label class="form-label">Select Authority for ID Card Signing:</label>
                                
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="card authority-option <?php echo $currentAuthority === 'president' ? 'border-primary' : ''; ?>">
                                            <div class="card-body text-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="authority_type" value="president" id="president" <?php echo $currentAuthority === 'president' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label w-100" for="president">
                                                        <i class="fas fa-user-tie fa-2x mb-2 text-primary"></i>
                                                        <h6><?php echo htmlspecialchars(getAuthorityConfig('president')['chairman_name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                        <p class="text-muted mb-0"><?php echo htmlspecialchars(getAuthorityConfig('president')['chairman_title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <small class="text-info">Primary Authority</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="card authority-option <?php echo $currentAuthority === 'secretary' ? 'border-primary' : ''; ?>">
                                            <div class="card-body text-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="authority_type" value="secretary" id="secretary" <?php echo $currentAuthority === 'secretary' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label w-100" for="secretary">
                                                        <i class="fas fa-user-graduate fa-2x mb-2 text-success"></i>
                                                        <h6><?php echo htmlspecialchars(getAuthorityConfig('secretary')['chairman_name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                        <p class="text-muted mb-0"><?php echo htmlspecialchars(getAuthorityConfig('secretary')['chairman_title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <small class="text-info">Secondary Authority</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-exchange-alt me-2"></i>Switch Authority
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Important Notes
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-arrow-right text-primary me-2"></i>
                                Authority changes affect all new certificates and ID cards
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-arrow-right text-primary me-2"></i>
                                Previously generated documents remain unchanged
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-arrow-right text-primary me-2"></i>
                                Only administrators can switch authorities
                            </li>
                            <li>
                                <i class="fas fa-arrow-right text-primary me-2"></i>
                                Changes take effect immediately
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Current Authority Details -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-check me-2"></i>
                            Current Authority Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="authority-avatar mb-3">
                                <i class="fas fa-user-tie fa-4x text-primary"></i>
                            </div>
                            <h4 class="mb-1"><?php echo htmlspecialchars($authorityNames['chairman_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($authorityNames['chairman_title'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <span class="badge bg-primary mt-2">Active Authority</span>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Authority Type</h6>
                                    <p class="mb-0 fw-bold"><?php echo ucfirst($currentAuthority); ?></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-1">Organization</h6>
                                <p class="mb-0 fw-bold"><?php echo ORGANIZATION_NAME_SHORT; ?></p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Last Updated:</span>
                            <span class="fw-bold"><?php echo date('d M Y, h:i A'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Authority Configuration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted">President Details:</label>
                                <div class="bg-light p-2 rounded">
                                    <strong><?php echo htmlspecialchars(getAuthorityConfig('president')['chairman_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars(getAuthorityConfig('president')['chairman_title'], ENT_QUOTES, 'UTF-8'); ?></small>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted">Secretary Details:</label>
                                <div class="bg-light p-2 rounded">
                                    <strong><?php echo htmlspecialchars(getAuthorityConfig('secretary')['chairman_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars(getAuthorityConfig('secretary')['chairman_title'], ENT_QUOTES, 'UTF-8'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.authority-option {
    transition: all 0.3s ease;
    cursor: pointer;
}

.authority-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.authority-option.border-primary {
    background-color: rgba(13, 110, 253, 0.05);
}

.authority-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.form-check-input:checked + .form-check-label {
    color: #0d6efd;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for authority cards
    document.querySelectorAll('.authority-option').forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                
                // Update visual state
                document.querySelectorAll('.authority-option').forEach(c => {
                    c.classList.remove('border-primary');
                });
                this.classList.add('border-primary');
            }
        });
    });
    
    // Confirm authority switch
    document.querySelector('form').addEventListener('submit', function(e) {
        const selectedAuthority = document.querySelector('input[name="authority_type"]:checked');
        if (selectedAuthority) {
            const authorityName = selectedAuthority.nextElementSibling.querySelector('h6').textContent;
            if (!confirm(`Are you sure you want to switch the signing authority to ${authorityName}?`)) {
                e.preventDefault();
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
