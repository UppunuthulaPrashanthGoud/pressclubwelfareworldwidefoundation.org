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
$siteConfig = [];

$db = getDbConnection();

// Handle form submission FIRST (before fetching data)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!verifyCSRF($_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
        logError($error);
    } else {
        // Get current config for file handling
        $currentConfig = getSiteConfig(true);
        
        // Sanitize inputs WITHOUT encoding special chars (for database storage)
        $site_title = sanitizeInput($_POST['site_title'], false);
        $site_subtitle = sanitizeInput($_POST['site_subtitle'], false);
        $meta_title = sanitizeInput($_POST['meta_title'], false);
        $meta_description = sanitizeInput($_POST['meta_description'], false);
        $meta_keywords = sanitizeInput($_POST['meta_keywords'], false);
        $meta_author = sanitizeInput($_POST['meta_author'], false);
        $address = sanitizeInput($_POST['address'], false);
        $phone1 = sanitizeInput($_POST['phone1'], false);
        $phone2 = !empty($_POST['phone2']) ? sanitizeInput($_POST['phone2'], false) : null;
        $email = sanitizeInput($_POST['email'], false);
        $working_hours = sanitizeInput($_POST['working_hours'], false);
        $facebook_url = !empty($_POST['facebook_url']) ? sanitizeInput($_POST['facebook_url'], false) : '#';
        $twitter_url = !empty($_POST['twitter_url']) ? sanitizeInput($_POST['twitter_url'], false) : '#';
        $instagram_url = !empty($_POST['instagram_url']) ? sanitizeInput($_POST['instagram_url'], false) : '#';
        $youtube_url = !empty($_POST['youtube_url']) ? sanitizeInput($_POST['youtube_url'], false) : '#';
        $website_url = !empty($_POST['website_url']) ? sanitizeInput($_POST['website_url'], false) : SITE_URL;
        
        // Handle map embed URL
        $map_embed_url = !empty($_POST['map_embed_url']) ? trim($_POST['map_embed_url']) : null;
        if ($map_embed_url) {
            // Extract src from iframe if full iframe code is provided
            if (preg_match('/<iframe[^>]+src=["\'](.*?)["\']/i', $map_embed_url, $matches)) {
                $map_embed_url = $matches[1];
            }
            $map_embed_url = filter_var($map_embed_url, FILTER_SANITIZE_URL);
            
            // Validate it's a proper Google Maps embed URL
            if ($map_embed_url && !preg_match('/^https:\/\/www\.google\.com\/maps\/embed\?/', $map_embed_url)) {
                $error = "Google Maps Embed URL must start with 'https://www.google.com/maps/embed?'.";
            }
        }
        
        // Keep existing file values
        $footer_logo = $currentConfig['footer_logo'] ?? null;
        $header_logo = $currentConfig['header_logo'] ?? null;
        $site_icon = $currentConfig['site_icon'] ?? null;

        // Validate required fields
        if (empty($error)) {
            if (empty($site_title) || empty($site_subtitle) || empty($meta_title) || 
                empty($meta_description) || empty($meta_keywords) || empty($meta_author) || 
                empty($address) || empty($phone1) || empty($email) || empty($working_hours)) {
                $error = "Please fill in all required fields.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email address.";
            }
        }

        // Handle file uploads
        if (empty($error)) {
            // Handle footer logo
            if (!empty($_FILES['footer_logo']['name'])) {
                $uploadResult = uploadFile($_FILES['footer_logo'], 'img/site_config');
                if ($uploadResult['success']) {
                    // Delete old file
                    if (!empty($currentConfig['footer_logo'])) {
                        $oldFilePath = __DIR__ . '/../img/site_config/' . $currentConfig['footer_logo'];
                        if (file_exists($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }
                    $footer_logo = $uploadResult['filename'];
                } else {
                    $error = "Footer Logo: " . $uploadResult['message'];
                }
            }

            // Handle header logo
            if (empty($error) && !empty($_FILES['header_logo']['name'])) {
                $uploadResult = uploadFile($_FILES['header_logo'], 'img/site_config');
                if ($uploadResult['success']) {
                    // Delete old file
                    if (!empty($currentConfig['header_logo'])) {
                        $oldFilePath = __DIR__ . '/../img/site_config/' . $currentConfig['header_logo'];
                        if (file_exists($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }
                    $header_logo = $uploadResult['filename'];
                } else {
                    $error = "Header Logo: " . $uploadResult['message'];
                }
            }

            // Handle site icon
            if (empty($error) && !empty($_FILES['site_icon']['name'])) {
                $uploadResult = uploadFile($_FILES['site_icon'], 'img/site_config');
                if ($uploadResult['success']) {
                    // Delete old file
                    if (!empty($currentConfig['site_icon'])) {
                        $oldFilePath = __DIR__ . '/../img/site_config/' . $currentConfig['site_icon'];
                        if (file_exists($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }
                    $site_icon = $uploadResult['filename'];
                } else {
                    $error = "Site Icon: " . $uploadResult['message'];
                }
            }
        }

        // Update database
        if (empty($error)) {
            try {
                $stmt = $db->prepare("
                    UPDATE site_config 
                    SET site_title = ?, site_subtitle = ?, meta_title = ?, meta_description = ?, 
                        meta_keywords = ?, meta_author = ?, address = ?, phone1 = ?, phone2 = ?, 
                        email = ?, working_hours = ?, facebook_url = ?, twitter_url = ?, 
                        instagram_url = ?, youtube_url = ?, footer_logo = ?, header_logo = ?, 
                        site_icon = ?, website_url = ?, map_embed_url = ?, updated_at = NOW() 
                    WHERE id = 1
                ");
                
                $result = $stmt->execute([
                    $site_title, $site_subtitle, $meta_title, $meta_description, 
                    $meta_keywords, $meta_author, $address, $phone1, $phone2, 
                    $email, $working_hours, $facebook_url, $twitter_url, 
                    $instagram_url, $youtube_url, $footer_logo, $header_logo, 
                    $site_icon, $website_url, $map_embed_url
                ]);
                
                if ($result) {
                    // Clear all cache levels
                    clearSiteConfigCache();
                    
                    $success = "Site configuration updated successfully.";
                    
                    // Redirect to avoid form resubmission
                    header("Location: site_config.php?success=" . urlencode($success));
                    exit;
                } else {
                    $error = "Failed to update site configuration.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
                logError('Site config update error: ' . $e->getMessage());
            }
        }
    }
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success = sanitizeInput($_GET['success'], false);
}

// Fetch current site configuration (AFTER processing form)
try {
    $siteConfig = getSiteConfig(true); // Force refresh to get latest data
} catch (Exception $e) {
    $error = "Error loading site configuration: " . $e->getMessage();
    logError('Site config fetch error: ' . $e->getMessage());
}

$pageTitle = "Site Configuration Management";
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-cog"></i> Site Configuration Management
            </h1>
            <div class="page-actions">
                <button type="button" class="btn btn-info" onclick="clearCache()">
                    <i class="fas fa-sync-alt"></i> Clear Cache
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="admin-card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Update Site Configuration
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" id="siteConfigForm">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                    
                    <!-- Basic Site Information -->
                    <h5 class="mb-3"><i class="fas fa-info-circle"></i> Basic Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="site_title" class="form-label">Site Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="site_title" name="site_title" 
                                   value="<?php echo htmlspecialchars($siteConfig['site_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="site_subtitle" class="form-label">Site Subtitle <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="site_subtitle" name="site_subtitle" 
                                   value="<?php echo htmlspecialchars($siteConfig['site_subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>
                    
                    <!-- Meta Information -->
                    <h5 class="mb-3 mt-4"><i class="fas fa-tags"></i> SEO Meta Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="meta_title" class="form-label">Meta Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                   value="<?php echo htmlspecialchars($siteConfig['meta_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="meta_author" class="form-label">Meta Author <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="meta_author" name="meta_author" 
                                   value="<?php echo htmlspecialchars($siteConfig['meta_author'] ?? 'Rawbit Foundation', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="meta_description" class="form-label">Meta Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3" required><?php echo htmlspecialchars($siteConfig['meta_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="meta_keywords" class="form-label">Meta Keywords <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="meta_keywords" name="meta_keywords" rows="3" 
                                      placeholder="keyword1, keyword2, keyword3" required><?php echo htmlspecialchars($siteConfig['meta_keywords'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <small class="text-muted">Comma-separated keywords</small>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <h5 class="mb-3 mt-4"><i class="fas fa-address-book"></i> Contact Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($siteConfig['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($siteConfig['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="phone1" class="form-label">Primary Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone1" name="phone1" 
                                   value="<?php echo htmlspecialchars($siteConfig['phone1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="phone2" class="form-label">Secondary Phone</label>
                            <input type="text" class="form-control" id="phone2" name="phone2" 
                                   value="<?php echo htmlspecialchars($siteConfig['phone2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="working_hours" class="form-label">Working Hours <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="working_hours" name="working_hours" 
                                   value="<?php echo htmlspecialchars($siteConfig['working_hours'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="website_url" class="form-label">Website URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="website_url" name="website_url" 
                                   value="<?php echo htmlspecialchars($siteConfig['website_url'] ?? SITE_URL, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>
                    
                    <!-- Logo and Icon Section -->
                    <h5 class="mb-3 mt-4"><i class="fas fa-image"></i> Logos & Icons</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="header_logo" class="form-label">Header Logo</label>
                            <input type="file" class="form-control" id="header_logo" name="header_logo" accept="image/*">
                            <?php if (!empty($siteConfig['header_logo'])): ?>
                                <div class="mt-2">
                                    <img src="<?php echo SITE_URL . '/img/site_config/' . htmlspecialchars($siteConfig['header_logo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                         alt="Header Logo" class="img-thumbnail" style="max-width: 150px; max-height: 100px;">
                                    <small class="d-block text-muted">Current Header Logo</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="footer_logo" class="form-label">Footer Logo</label>
                            <input type="file" class="form-control" id="footer_logo" name="footer_logo" accept="image/*">
                            <?php if (!empty($siteConfig['footer_logo'])): ?>
                                <div class="mt-2">
                                    <img src="<?php echo SITE_URL . '/img/site_config/' . htmlspecialchars($siteConfig['footer_logo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                         alt="Footer Logo" class="img-thumbnail" style="max-width: 150px; max-height: 100px;">
                                    <small class="d-block text-muted">Current Footer Logo</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label for="site_icon" class="form-label">Site Icon (Favicon)</label>
                            <input type="file" class="form-control" id="site_icon" name="site_icon" accept="image/*">
                            <?php if (!empty($siteConfig['site_icon'])): ?>
                                <div class="mt-2">
                                    <img src="<?php echo SITE_URL . '/img/site_config/' . htmlspecialchars($siteConfig['site_icon'], ENT_QUOTES, 'UTF-8'); ?>" 
                                         alt="Site Icon" class="img-thumbnail" style="max-width: 32px; max-height: 32px;">
                                    <small class="d-block text-muted">Current Site Icon</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Social Media Links -->
                    <h5 class="mb-3 mt-4"><i class="fas fa-share-alt"></i> Social Media Links</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="facebook_url" class="form-label">Facebook URL</label>
                            <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                                   value="<?php echo htmlspecialchars($siteConfig['facebook_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="twitter_url" class="form-label">Twitter URL</label>
                            <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                   value="<?php echo htmlspecialchars($siteConfig['twitter_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="instagram_url" class="form-label">Instagram URL</label>
                            <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                                   value="<?php echo htmlspecialchars($siteConfig['instagram_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="youtube_url" class="form-label">YouTube URL</label>
                            <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                                   value="<?php echo htmlspecialchars($siteConfig['youtube_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    
                    <!-- Google Maps -->
                    <h5 class="mb-3 mt-4"><i class="fas fa-map-marked-alt"></i> Google Maps</h5>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="map_embed_url" class="form-label">Google Maps Embed URL</label>
                            <textarea class="form-control" id="map_embed_url" name="map_embed_url" rows="3" 
                                      placeholder="https://www.google.com/maps/embed?pb=..."><?php echo htmlspecialchars($siteConfig['map_embed_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <small class="form-text text-muted">Paste the full &lt;iframe&gt; code or just the URL starting with 'https://www.google.com/maps/embed?'</small>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Configuration
                        </button>
                        <a href="index.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Prevent double form submission
const form = document.getElementById('siteConfigForm');
if (form) {
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn.disabled) {
            e.preventDefault();
            return false;
        }
        
        submitBtn.disabled = true;
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        
        // Re-enable after 10 seconds (in case of error)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }, 10000);
    });
}

// Clear cache function
function clearCache() {
    if (confirm('Are you sure you want to clear all caches?')) {
        fetch('clear_cache.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_cache&csrf_token=<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache cleared successfully!');
                location.reload();
            } else {
                alert('Error clearing cache: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error clearing cache: ' + error);
        });
    }
}

// File input preview
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = input.parentElement.querySelector('.img-thumbnail');
                if (preview) {
                    preview.src = event.target.result;
                } else {
                    const newPreview = document.createElement('div');
                    newPreview.className = 'mt-2';
                    newPreview.innerHTML = `
                        <img src="${event.target.result}" alt="Preview" 
                             class="img-thumbnail" style="max-width: 150px; max-height: 100px;">
                        <small class="d-block text-muted">New Preview</small>
                    `;
                    input.parentElement.appendChild(newPreview);
                }
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>