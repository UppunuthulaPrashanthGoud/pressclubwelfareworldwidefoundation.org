<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Ensure only admins can access
if (!isAdmin()) {
    redirectTo(ADMIN_URL . 'login.php');
}

// Initialize variables
$success = isset($_GET['success']) ? htmlspecialchars_decode(sanitizeInput($_GET['success']), ENT_QUOTES) : '';
$error = '';
$settings = [];

// Fetch current settings
$db = getDbConnection();
try {
    $stmt = $db->query("SELECT * FROM topbar_settings");
    $settings_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($settings_data as $setting) {
        // Handle null values by converting to empty string
        $settings[$setting['setting_key']] = $setting['setting_value'] !== null ? htmlspecialchars_decode($setting['setting_value'], ENT_QUOTES) : '';
    }
} catch (PDOException $e) {
    $error = "डेटाबेस त्रुटि: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    logError($error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!verifyCSRF($_POST['csrf_token'])) {
        $error = "अमान्य CSRF टोकन।";
        logError($error);
    } else {
        try {
            $show_topbar = isset($_POST['show_topbar']) ? 1 : 0;
            $topbar_text = sanitizeInput($_POST['topbar_text'], false);
            $topbar_phone = sanitizeInput($_POST['topbar_phone'], false);
            $topbar_email = sanitizeInput($_POST['topbar_email'], false);
            $topbar_background_color = sanitizeInput($_POST['topbar_background_color'], false);

            // Validate required fields
            if (empty($topbar_text)) {
                $error = "टॉपबार टेक्स्ट आवश्यक है।";
                logError($error);
            } elseif (!empty($topbar_email) && !filter_var($topbar_email, FILTER_VALIDATE_EMAIL)) {
                $error = "अमान्य ईमेल पता।";
                logError($error);
            } elseif (!empty($topbar_background_color) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $topbar_background_color)) {
                $error = "अमान्य बैकग्राउंड कलर। कृपया वैध HEX कोड (#RRGGBB) प्रदान करें।";
                logError($error);
            } else {
                $settings = [
                    'show_topbar' => $show_topbar,
                    'topbar_text' => $topbar_text,
                    'topbar_phone' => $topbar_phone,
                    'topbar_email' => $topbar_email,
                    'topbar_background_color' => $topbar_background_color
                ];

                foreach ($settings as $key => $value) {
                    $stmt = $db->prepare("INSERT INTO topbar_settings (setting_key, setting_value) VALUES (?, ?) 
                                         ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                    $stmt->execute([$key, $value, $value]);
                }

                $success = "टॉपबार सेटिंग्स सफलतापूर्वक अपडेट की गईं।";
                header("Location: topbar-settings.php?success=" . urlencode($success));
                exit;
            }
        } catch (PDOException $e) {
            $error = "डेटाबेस त्रुटि: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            logError($error);
        }
    }
}

$pageTitle = "टॉपबार सेटिंग्स प्रबंधन";
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-window-maximize me-3"></i> टॉपबार सेटिंग्स प्रबंधन
            </h1>
            <div class="page-actions">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> वापस
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
                <h5><i class="fas fa-edit"></i> टॉपबार सेटिंग्स अपडेट करें</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="show_topbar" id="show_topbar" 
                                   <?php echo (isset($settings['show_topbar']) && $settings['show_topbar'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="show_topbar">टॉपबार दिखाएं</label>
                        </div>
                        <small class="form-text text-muted">वेबसाइट के टॉप पर information bar दिखाने के लिए enable करें</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">टॉपबार टेक्स्ट <span class="text-danger">*</span></label>
                        <input type="text" name="topbar_text" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['topbar_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                               placeholder="स्वागत है सनातन धर्म जागृति विश्व परिषद में" required>
                        <small class="form-text text-muted">टॉपबार में दिखाया जाने वाला मुख्य संदेश</small>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">टॉपबार फोन नंबर</label>
                            <input type="text" name="topbar_phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['topbar_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="+91-9999999999">
                            <small class="form-text text-muted">टॉपबार में दिखाया जाने वाला फोन नंबर</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">टॉपबार ईमेल</label>
                            <input type="email" name="topbar_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['topbar_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="info@example.com">
                            <small class="form-text text-muted">टॉपबार में दिखाया जाने वाला ईमेल</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">टॉपबार बैकग्राउंड कलर <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="color" name="topbar_background_color" class="form-control form-control-color" 
                                   value="<?php echo htmlspecialchars($settings['topbar_background_color'] ?? '#291872', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="text" class="form-control" 
                                   value="<?php echo htmlspecialchars($settings['topbar_background_color'] ?? '#291872', ENT_QUOTES, 'UTF-8'); ?>" 
                                   readonly>
                        </div>
                        <small class="form-text text-muted">टॉपबार का बैकग्राउंड कलर चुनें</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">प्रीव्यू</label>
                        <div id="topbar-preview" class="border rounded p-2" 
                             style="background-color: <?php echo htmlspecialchars($settings['topbar_background_color'] ?? '#291872', ENT_QUOTES, 'UTF-8'); ?>; color: white;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="topbar-text">
                                    <?php echo htmlspecialchars($settings['topbar_text'] ?? 'स्वागत है सनातन धर्म जागृति विश्व परिषद में', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <span class="topbar-contact">
                                    <?php if (!empty($settings['topbar_phone'])): ?>
                                        <span class="me-3">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($settings['topbar_phone'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($settings['topbar_email'])): ?>
                                        <span>
                                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($settings['topbar_email'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> रद्द करें
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> सेटिंग्स सेव करें
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textInput = document.querySelector('input[name="topbar_text"]');
    const phoneInput = document.querySelector('input[name="topbar_phone"]');
    const emailInput = document.querySelector('input[name="topbar_email"]');
    const colorInput = document.querySelector('input[name="topbar_background_color"]');
    const preview = document.getElementById('topbar-preview');
    const previewText = preview.querySelector('.topbar-text');
    const previewContact = preview.querySelector('.topbar-contact');
    
    function updatePreview() {
        previewText.textContent = textInput.value || 'स्वागत है सनातन धर्म जागृति विश्व परिषद में';
        preview.style.backgroundColor = colorInput.value;
        
        let contactHtml = '';
        if (phoneInput.value) {
            contactHtml += `<span class="me-3"><i class="fas fa-phone"></i> ${phoneInput.value}</span>`;
        }
        if (emailInput.value) {
            contactHtml += `<span><i class="fas fa-envelope"></i> ${emailInput.value}</span>`;
        }
        previewContact.innerHTML = contactHtml;
    }
    
    textInput.addEventListener('input', updatePreview);
    phoneInput.addEventListener('input', updatePreview);
    emailInput.addEventListener('input', updatePreview);
    colorInput.addEventListener('input', function() {
        updatePreview();
        const colorTextInput = colorInput.nextElementSibling;
        colorTextInput.value = colorInput.value;
    });
});
</script>

<?php include 'includes/footer.php'; ?>