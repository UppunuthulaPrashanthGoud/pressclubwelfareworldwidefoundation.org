<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Ensure only admins can access
if (!isAdmin()) {
    redirectTo(ADMIN_URL . 'login.php');
}

// Initialize variables
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_pricing') {
    if (!verifyCSRF($_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        // Sanitize and validate inputs
        $active = isset($_POST['active']) ? (int)sanitizeInput($_POST['active']) : 0;
        $gram_panchayat = isset($_POST['gram_panchayat']) ? (int)sanitizeInput($_POST['gram_panchayat']) : 0;
        $block = isset($_POST['block']) ? (int)sanitizeInput($_POST['block']) : 0;
        $tehsil = isset($_POST['tehsil']) ? (int)sanitizeInput($_POST['tehsil']) : 0;
        $district = isset($_POST['district']) ? (int)sanitizeInput($_POST['district']) : 0;
        $mandal = isset($_POST['mandal']) ? (int)sanitizeInput($_POST['mandal']) : 0;
        $state = isset($_POST['state']) ? (int)sanitizeInput($_POST['state']) : 0;
        $national = isset($_POST['national']) ? (int)sanitizeInput($_POST['national']) : 0;

        // Validate prices: non-negative and not excessively large
        $valid = true;
        $max_price = 1000000; // Maximum allowable price (₹10,00,000)
        foreach ([$active, $gram_panchayat, $block, $tehsil, $district, $mandal, $state, $national] as $price) {
            if ($price < 0 || $price > $max_price) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            $error = "Price cannot be negative or exceed ₹$max_price.";
        } else {
            try {
                if (updateMembershipPrices($active, $gram_panchayat, $block, $tehsil, $district, $mandal, $state, $national)) {
                    $success = "Membership prices successfully updated.";
                    // Force refresh prices after update
                    $currentPrices = getMembershipPrices(true);
                } else {
                    $error = "Error updating prices. Please try again.";
                }
            } catch (Exception $e) {
                $error = "Database error: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// Get current prices (force refresh to show latest values)
$currentPrices = getMembershipPrices(true);

// $pageTitle = "Membership Pricing Management";
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-money-bill-wave me-3"></i> Membership Pricing Management
            </h1>
            <div class="page-actions">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-edit"></i> Update Membership Prices</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="update_pricing">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="active" class="form-label">Active Membership (₹)</label>
                            <input type="number" class="form-control" id="active" name="active" 
                                   value="<?php echo htmlspecialchars($currentPrices['membership_price_active'] ?? 101); ?>" min="0" max="1000000" required>
                            <small class="text-muted">Basic active membership</small>
                        </div>
                        <div class="col-md-4">
                            <label for="gram_panchayat" class="form-label">Gram Panchayat Level (₹)</label>
                            <input type="number" class="form-control" id="gram_panchayat" name="gram_panchayat" 
                                   value="<?php echo htmlspecialchars($currentPrices['membership_price_gram_panchayat'] ?? 151); ?>" min="0" max="1000000" required>
                            <small class="text-muted">Village-level membership</small>
                        </div>
                        <div class="col-md-4">
                            <label for="block" class="form-label">Block Level (₹)</label>
                            <input type="number" class="form-control" id="block" name="block" 
                                   value="<?php echo htmlspecialchars($currentPrices['membership_price_block'] ?? 251); ?>" min="0" max="1000000" required>
                            <small class="text-muted">Block-level membership</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="tehsil" class="form-label">Tehsil Level (₹)</label>
                            <input type="number" class="form-control" id="tehsil" name="tehsil" 
                                   value="<?php echo htmlspecialchars($currentPrices['membership_price_tehsil'] ?? 350); ?>" min="0" max="1000000" required>
                            <small class="text-muted">Tehsil-level membership</small>
                        </div>
                        <div class="col-md-4">
                            <label for="district" class="form-label">District Level (₹)</label>
                            <input type="number" class="form-control" id="district" name="district" 
                                   value="<?php echo htmlspecialchars($currentPrices['membership_price_district'] ?? 501); ?>" min="0" max="1000000" required>
                            <small class="text-muted">District-level membership</small>
                        </div>
                        <div class="col-md-4">
                            <label for="mandal" class="form-label">Mandal Level (₹)</label>
                            <input type="number" class="form-control" id="mandal" name="mandal" 
                                   value="<?php echo htmlspecialchars($currentPrices['membership_price_mandal'] ?? 801); ?>" min="0" max="1000000" required>
                            <small class="text-muted">Mandal-level membership</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="state" class="form-label">State Level (₹)</label>
                            <input type="number" class="form-control" id="state" name="state" 
                                   value="<?php echo htmlspecialchars($currentPrices['membership_price_state'] ?? 999); ?>" min="0" max="1000000" required>
                            <small class="text-muted">State-level membership</small>
                        </div>
                        <div class="col-md-6">
                            <label for="national" class="form-label">National Level (₹)</label>
                            <input type="number" class="form-control" id="national" name="national" 
                                   value="<?php echo htmlspecialchars($currentPrices['membership_price_national'] ?? 1201); ?>" min="0" max="1000000" required>
                            <small class="text-muted">National-level membership</small>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Prices
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>