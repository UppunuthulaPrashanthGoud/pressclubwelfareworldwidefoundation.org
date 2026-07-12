<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'सामान्य सेटिंग्स';
$db = getDbConnection();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'])) {
        $error = "अमान्य CSRF टोकन।";
    } else {
        try {
            $settings = [
                'site_name' => sanitizeInput($_POST['site_name']),
                'site_description' => sanitizeInput($_POST['site_description']),
                'contact_email' => sanitizeInput($_POST['contact_email']),
                'contact_phone' => sanitizeInput($_POST['contact_phone']),
                'address' => sanitizeInput($_POST['address']),
                'facebook_url' => sanitizeInput($_POST['facebook_url']),
                'twitter_url' => sanitizeInput($_POST['twitter_url']),
                'instagram_url' => sanitizeInput($_POST['instagram_url']),
                'youtube_url' => sanitizeInput($_POST['youtube_url']),
                'membership_price_active' => sanitizeInput($_POST['membership_price_active']),
                'membership_price_gram_panchayat' => sanitizeInput($_POST['membership_price_gram_panchayat']),
                'membership_price_block' => sanitizeInput($_POST['membership_price_block']),
                'membership_price_tehsil' => sanitizeInput($_POST['membership_price_tehsil']),
                'membership_price_district' => sanitizeInput($_POST['membership_price_district']),
                'membership_price_mandal' => sanitizeInput($_POST['membership_price_mandal']),
                'membership_price_state' => sanitizeInput($_POST['membership_price_state']),
                'membership_price_national' => sanitizeInput($_POST['membership_price_national']),
                'auto_approve_members' => isset($_POST['auto_approve_members']) ? 1 : 0,
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
            ];

            // Validate membership prices
            foreach (['active', 'gram_panchayat', 'block', 'tehsil', 'district', 'mandal', 'state', 'national'] as $type) {
                $key = "membership_price_$type";
                if (!is_numeric($settings[$key]) || $settings[$key] < 0) {
                    throw new Exception("सदस्यता शुल्क के लिए मान्य गैर-नकारात्मक संख्या दर्ज करें: " . getMembershipTypeName($type));
                }
            }

            // Validate email
            if (!filter_var($settings['contact_email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("अमान्य संपर्क ईमेल पता।");
            }

            // Handle logo upload
            if (!empty($_FILES['site_logo']['name'])) {
                $upload_dir = '../img/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $file_ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($file_ext, $allowed_ext)) {
                    $logo_name = 'logo.' . $file_ext;
                    if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_dir . $logo_name)) {
                        $settings['site_logo'] = $logo_name;
                    } else {
                        throw new Exception("लोगो अपलोड करने में विफल।");
                    }
                } else {
                    throw new Exception("अनुमति नहीं है फ़ाइल प्रकार। केवल jpg, jpeg, png, gif अनुमत हैं।");
                }
            }

            // Update settings in database
            foreach ($settings as $key => $value) {
                if ($key === 'site_logo' && empty($value)) continue;

                $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }

            // Update membership_config.php
            $success = updateMembershipPrices(
                $settings['membership_price_active'],
                $settings['membership_price_gram_panchayat'],
                $settings['membership_price_block'],
                $settings['membership_price_tehsil'],
                $settings['membership_price_district'],
                $settings['membership_price_mandal'],
                $settings['membership_price_state'],
                $settings['membership_price_national']
            );

            if (!$success) {
                throw new Exception("सदस्यता कॉन्फिगरेशन फ़ाइल अपडेट करने में विफल।");
            }

            $message = 'सेटिंग्स और सदस्यता शुल्क सफलतापूर्वक अपडेट किए गए!';

        } catch (Exception $e) {
            $error = 'सेटिंग्स अपडेट करने में त्रुटि: ' . htmlspecialchars($e->getMessage());
            logError($error);
        }
    }
}

// Get current settings
$current_settings = [];
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $current_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $error = 'सेटिंग्स लोड करने में त्रुटि: ' . htmlspecialchars($e->getMessage());
    logError($error);
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-cog me-3"></i>सामान्य सेटिंग्स
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

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF()); ?>">
            <div class="row">
                <!-- Site Information -->
                <div class="col-lg-6">
                    <div class="admin-card">
                        <div class="card-header">
                            <h5><i class="fas fa-globe"></i> साइट जानकारी</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">साइट नाम <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="site_name" name="site_name" required
                                       value="<?php echo htmlspecialchars($current_settings['site_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="site_description" class="form-label">साइट विवरण</label>
                                <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($current_settings['site_description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="site_logo" class="form-label">साइट लोगो</label>
                                <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                                <?php if (!empty($current_settings['site_logo'])): ?>
                                <div class="mt-2">
                                    <img src="<?php echo SITE_URL . '/img/' . htmlspecialchars($current_settings['site_logo']); ?>" 
                                         alt="Current Logo" class="img-thumbnail" style="max-width: 150px;">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-6">
                    <div class="admin-card">
                        <div class="card-header">
                            <h5><i class="fas fa-phone"></i> संपर्क जानकारी</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">संपर्क ईमेल <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email" required
                                       value="<?php echo htmlspecialchars($current_settings['contact_email'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="contact_phone" class="form-label">संपर्क फोन <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="contact_phone" name="contact_phone" required
                                       value="<?php echo htmlspecialchars($current_settings['contact_phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">पता <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($current_settings['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="col-lg-6">
                    <div class="admin-card">
                        <div class="card-header">
                            <h5><i class="fas fa-share-alt"></i> सोशल मीडिया</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="facebook_url" class="form-label">Facebook URL</label>
                                <input type="url" class="form-control" id="facebook_url" name="facebook_url"
                                       value="<?php echo htmlspecialchars($current_settings['facebook_url'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="twitter_url" class="form-label">Twitter URL</label>
                                <input type="url" class="form-control" id="twitter_url" name="twitter_url"
                                       value="<?php echo htmlspecialchars($current_settings['twitter_url'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="instagram_url" class="form-label">Instagram URL</label>
                                <input type="url" class="form-control" id="instagram_url" name="instagram_url"
                                       value="<?php echo htmlspecialchars($current_settings['instagram_url'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="youtube_url" class="form-label">YouTube URL</label>
                                <input type="url" class="form-control" id="youtube_url" name="youtube_url"
                                       value="<?php echo htmlspecialchars($current_settings['youtube_url'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membership Settings -->
                <div class="col-lg-6">
                    <div class="admin-card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> सदस्यता शुल्क</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="membership_price_active" class="form-label"><?php echo getMembershipTypeName('active'); ?> सदस्यता शुल्क (₹) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="membership_price_active" name="membership_price_active" min="0" step="1" required
                                               value="<?php echo htmlspecialchars($current_settings['membership_price_active'] ?? ACTIVE_MEMBERSHIP_PRICE); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="membership_price_gram_panchayat" class="form-label"><?php echo getMembershipTypeName('gram_panchayat'); ?> सदस्यता शुल्क (₹) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="membership_price_gram_panchayat" name="membership_price_gram_panchayat" min="0" step="1" required
                                               value="<?php echo htmlspecialchars($current_settings['membership_price_gram_panchayat'] ?? GRAM_PANCHAYAT_MEMBERSHIP_PRICE); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="membership_price_block" class="form-label"><?php echo getMembershipTypeName('block'); ?> सदस्यता शुल्क (₹) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="membership_price_block" name="membership_price_block" min="0" step="1" required
                                               value="<?php echo htmlspecialchars($current_settings['membership_price_block'] ?? BLOCK_MEMBERSHIP_PRICE); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="membership_price_tehsil" class="form-label"><?php echo getMembershipTypeName('tehsil'); ?> सदस्यता शुल्क (₹) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="membership_price_tehsil" name="membership_price_tehsil" min="0" step="1" required
                                               value="<?php echo htmlspecialchars($current_settings['membership_price_tehsil'] ?? TEHSIL_MEMBERSHIP_PRICE); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="membership_price_district" class="form-label"><?php echo getMembershipTypeName('district'); ?> सदस्यता शुल्क (₹) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="membership_price_district" name="membership_price_district" min="0" step="1" required
                                               value="<?php echo htmlspecialchars($current_settings['membership_price_district'] ?? DISTRICT_MEMBERSHIP_PRICE); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="membership_price_mandal" class="form-label"><?php echo getMembershipTypeName('mandal'); ?> सदस्यता शुल्क (₹) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="membership_price_mandal" name="membership_price_mandal" min="0" step="1" required
                                               value="<?php echo htmlspecialchars($current_settings['membership_price_mandal'] ?? MANDAL_MEMBERSHIP_PRICE); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="membership_price_state" class="form-label"><?php echo getMembershipTypeName('state'); ?> सदस्यता शुल्क (₹) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="membership_price_state" name="membership_price_state" min="0" step="1" required
                                               value="<?php echo htmlspecialchars($current_settings['membership_price_state'] ?? STATE_MEMBERSHIP_PRICE); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="membership_price_national" class="form-label"><?php echo getMembershipTypeName('national'); ?> सदस्यता शुल्क (₹) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="membership_price_national" name="membership_price_national" min="0" step="1" required
                                               value="<?php echo htmlspecialchars($current_settings['membership_price_national'] ?? NATIONAL_MEMBERSHIP_PRICE); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="col-lg-12">
                    <div class="admin-card">
                        <div class="card-header">
                            <h5><i class="fas fa-cogs"></i> सिस्टम सेटिंग्स</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="auto_approve_members" name="auto_approve_members"
                                               <?php echo ($current_settings['auto_approve_members'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="auto_approve_members">
                                            सदस्यों को स्वचालित अनुमोदन
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications"
                                               <?php echo ($current_settings['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email_notifications">
                                            ईमेल सूचनाएं
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode"
                                               <?php echo ($current_settings['maintenance_mode'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="maintenance_mode">
                                            रखरखाव मोड
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> सेटिंग्स सहेजें
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/footer.php'; ?>