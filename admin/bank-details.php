<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Ensure only admins can access
if (!isAdmin()) {
    redirectTo(ADMIN_URL . 'login.php');
}

$pdo = getDbConnection();
$success = isset($_GET['success']) ? htmlspecialchars_decode(sanitizeInput($_GET['success']), ENT_QUOTES) : '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
        logError($error);
    } else {
        try {
            $environment = sanitizeInput($_POST['environment'], false);
            $bank_name = sanitizeInput(trim($_POST['bank_name']), false);
            $account_name = sanitizeInput(trim($_POST['account_name']), false);
            $account_number = sanitizeInput(trim($_POST['account_number']), false);
            $ifsc_code = sanitizeInput(trim($_POST['ifsc_code']), false);

            // Validate required fields
            if (empty($bank_name) || empty($account_name) || empty($account_number) || empty($ifsc_code)) {
                $error = 'Please fill all required fields.';
                logError($error);
            } elseif (!in_array($environment, ['local', 'live'])) {
                $error = 'Invalid environment selection.';
                logError($error);
            } else {
                // Handle QR code upload
                $qr_code_image = '';
                $stmt = $pdo->prepare("SELECT qr_code_image FROM bank_details WHERE environment = ?");
                $stmt->execute([$environment]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($current) {
                    $qr_code_image = $current['qr_code_image'];
                }

                if (isset($_FILES['qr_code_image']) && $_FILES['qr_code_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = uploadFile($_FILES['qr_code_image'], 'img/');
                    if ($upload_result['success']) {
                        // Delete old QR code
                        if ($qr_code_image && file_exists('../img/' . $qr_code_image)) {
                            unlink('../img/' . $qr_code_image);
                        }
                        $qr_code_image = $upload_result['filename'];
                    } else {
                        $error = $upload_result['message'];
                        logError($error);
                    }
                }

                if (empty($error)) {
                    // Check if record exists
                    if ($current) {
                        $stmt = $pdo->prepare("UPDATE bank_details SET bank_name = ?, account_name = ?, account_number = ?, ifsc_code = ?, qr_code_image = ? WHERE environment = ?");
                        $result = $stmt->execute([$bank_name, $account_name, $account_number, $ifsc_code, $qr_code_image, $environment]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO bank_details (environment, bank_name, account_name, account_number, ifsc_code, qr_code_image) VALUES (?, ?, ?, ?, ?, ?)");
                        $result = $stmt->execute([$environment, $bank_name, $account_name, $account_number, $ifsc_code, $qr_code_image]);
                    }

                    if ($result) {
                        $success = 'Bank details updated successfully.';
                        header("Location: bank-details.php?success=" . urlencode($success));
                        exit;
                    } else {
                        $error = 'Error updating bank details.';
                        logError($error);
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            logError($error);
        }
    }
}

// Get bank details
try {
    $stmt = $pdo->query("SELECT * FROM bank_details ORDER BY environment");
    $bank_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Database error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    logError($error);
    $bank_details = [];
}

// Convert to associative array for easier access
$bank_data = [];
foreach ($bank_details as $detail) {
    $bank_data[$detail['environment']] = $detail;
}

// $pageTitle = 'Bank Details Management';
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-university me-3"></i> Bank Details Management
            </h1>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-edit"></i> Update Bank Details</h5>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="bankTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="local-tab" data-bs-toggle="tab" data-bs-target="#local" type="button" role="tab">
                            <i class="fas fa-laptop"></i> Local Environment
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="live-tab" data-bs-toggle="tab" data-bs-target="#live" type="button" role="tab">
                            <i class="fas fa-globe"></i> Live Environment
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="bankTabsContent">
                    <!-- Local Environment -->
                    <div class="tab-pane fade show active" id="local" role="tabpanel">
                        <form method="POST" enctype="multipart/form-data" class="mt-4">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="environment" value="local">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_name" class="form-control" required 
                                               value="<?php echo isset($bank_data['local']) ? htmlspecialchars($bank_data['local']['bank_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                        <input type="text" name="account_name" class="form-control" required 
                                               value="<?php echo isset($bank_data['local']) ? htmlspecialchars($bank_data['local']['account_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" name="account_number" class="form-control" required 
                                               value="<?php echo isset($bank_data['local']) ? htmlspecialchars($bank_data['local']['account_number'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                        <input type="text" name="ifsc_code" class="form-control" required 
                                               value="<?php echo isset($bank_data['local']) ? htmlspecialchars($bank_data['local']['ifsc_code'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">QR Code</label>
                                <input type="file" name="qr_code_image" class="form-control" accept="image/*">
                                <?php if (isset($bank_data['local']) && !empty($bank_data['local']['qr_code_image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL . '/img/' . htmlspecialchars($bank_data['local']['qr_code_image'], ENT_QUOTES, 'UTF-8'); ?>" 
                                             alt="Current QR Code" class="img-thumbnail" style="max-width: 150px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Live Environment -->
                    <div class="tab-pane fade" id="live" role="tabpanel">
                        <form method="POST" enctype="multipart/form-data" class="mt-4">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="environment" value="live">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_name" class="form-control" required 
                                               value="<?php echo isset($bank_data['live']) ? htmlspecialchars($bank_data['live']['bank_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                        <input type="text" name="account_name" class="form-control" required 
                                               value="<?php echo isset($bank_data['live']) ? htmlspecialchars($bank_data['live']['account_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" name="account_number" class="form-control" required 
                                               value="<?php echo isset($bank_data['live']) ? htmlspecialchars($bank_data['live']['account_number'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                        <input type="text" name="ifsc_code" class="form-control" required 
                                               value="<?php echo isset($bank_data['live']) ? htmlspecialchars($bank_data['live']['ifsc_code'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">QR Code</label>
                                <input type="file" name="qr_code_image" class="form-control" accept="image/*">
                                <?php if (isset($bank_data['live']) && !empty($bank_data['live']['qr_code_image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo SITE_URL . '/img/' . htmlspecialchars($bank_data['live']['qr_code_image'], ENT_QUOTES, 'UTF-8'); ?>" 
                                             alt="Current QR Code" class="img-thumbnail" style="max-width: 150px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>