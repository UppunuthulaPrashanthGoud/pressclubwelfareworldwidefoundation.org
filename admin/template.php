<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';

$db = getDbConnection();

// Optimized template configuration with caching
$templates = [
    'id_card' => [
        'name' => 'ID Card Template',
        'file_path' => '../templates/id_card.png',
        'preview_path' => SITE_URL . '/templates/id_card.png',
        'type' => 'image/png',
        'description' => 'Template for generating member ID cards',
        'label' => 'ID Card Template',
        'icon' => 'id-card',
        'priority' => false
    ],
    'certificate' => [
        'name' => 'Certificate Template',
        'file_path' => '../templates/certificate-new.png',
        'preview_path' => SITE_URL . '/templates/certificate-new.png',
        'type' => 'image/png',
        'description' => 'Template for generating certificates',
        'label' => 'Certificate Template',
        'icon' => 'certificate',
        'priority' => false
    ],
    'award_letter' => [
        'name' => 'Award Letter Template',
        'file_path' => '../templates/award-letter-template.png',
        'preview_path' => SITE_URL . '/templates/award-letter-template.png',
        'type' => 'image/png',
        'description' => 'Template for generating award letters',
        'label' => 'Award Letter Template',
        'icon' => 'envelope-open-text',
        'priority' => false
    ],
    'honorary_award' => [
        'name' => 'Honorary Award Template',
        'file_path' => '../templates/honorary-award-new.png',
        'preview_path' => SITE_URL . '/templates/honorary-award-new.png',
        'type' => 'image/png',
        'description' => 'Template for generating honorary doctorate awards',
        'label' => 'Honorary Award Template',
        'icon' => 'award',
        'priority' => false
    ],
    'congratulations_certificate' => [
        'name' => 'Congratulations Certificate Template',
        'file_path' => '../templates/awards-congratulations-certificate.png',
        'preview_path' => SITE_URL . '/templates/awards-congratulations-certificate.png',
        'type' => 'image/png',
        'description' => 'Template for generating congratulations certificates',
        'label' => 'Congratulations Certificate Template',
        'icon' => 'certificate',
        'priority' => false
    ],
    // 'participation_certificate' => [
    //     'name' => 'Participation Certificate Template',
    //     'file_path' => '../templates/participation-certificate-template.png',
    //     'preview_path' => SITE_URL . '/templates/participation-certificate-template.png',
    //     'type' => 'image/png',
    //     'description' => 'Template for generating participation certificates',
    //     'label' => 'Participation Certificate Template',
    //     'icon' => 'certificate',
    //     'priority' => false
    // ],
    'logo' => [
        'name' => 'Organization Logo',
        'file_path' => '../img/logo.png',
        'preview_path' => SITE_URL . '/img/logo.png',
        'type' => 'image/png',
        'description' => 'Logo used in receipts and documents',
        'label' => 'Organization Logo',
        'icon' => 'image',
        'priority' => true
    ],
    'signature' => [
        'name' => 'Authorized Signature',
        'file_path' => '../img/signature.png',
        'preview_path' => SITE_URL . '/img/signature.png',
        'type' => 'image/png',
        'description' => 'Signature used in receipts and certificates',
        'label' => 'Authorized Signature',
        'icon' => 'signature',
        'priority' => true
    ],
    'qr_code' => [
        'name' => 'QR Code',
        'file_path' => '../img/qr_code.png',
        'preview_path' => SITE_URL . '/img/qr_code.png',
        'type' => 'image/png',
        'description' => 'QR code for payments',
        'label' => 'QR Code',
        'icon' => 'qrcode',
        'priority' => false
    ],
    'letterhead' => [
        'name' => 'Letterhead',
        'file_path' => '../img/letterhead.png',
        'preview_path' => SITE_URL . '/img/letterhead.png',
        'type' => 'image/png',
        'description' => 'Official letterhead for documents',
        'label' => 'Letterhead',
        'icon' => 'file-alt',
        'priority' => false,
        'hidden' => true
    ],
    'watermark' => [
        'name' => 'Watermark',
        'file_path' => '../img/watermark.png',
        'preview_path' => SITE_URL . '/img/watermark.png',
        'type' => 'image/png',
        'description' => 'Document watermark',
        'label' => 'Watermark',
        'icon' => 'tint',
        'priority' => false,
        'hidden' => true
    ],
    'seal' => [
        'name' => 'Seal/Stamp',
        'file_path' => '../img/seal.png',
        'preview_path' => SITE_URL . '/img/seal.png',
        'type' => 'image/png',
        'description' => 'Official seal or stamp',
        'label' => 'Seal/Stamp',
        'icon' => 'stamp',
        'priority' => false
    ]
];

// Cache file information to avoid repeated file system calls
$fileCache = [];
function getFileInfo($filePath) {
    global $fileCache;
    
    if (!isset($fileCache[$filePath])) {
        $fileCache[$filePath] = [
            'exists' => file_exists($filePath),
            'size' => file_exists($filePath) ? filesize($filePath) : 0,
            'modified' => file_exists($filePath) ? filemtime($filePath) : 0
        ];
    }
    
    return $fileCache[$filePath];
}

// Handle template upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_template') {
    $template_type = sanitizeInput($_POST['template_type'] ?? '');
    
    if (!array_key_exists($template_type, $templates)) {
        $error = "Invalid template type.";
    } elseif (isset($_FILES['template_file']) && $_FILES['template_file']['error'] === UPLOAD_ERR_OK) {
        try {
            $file = $_FILES['template_file'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['png', 'jpg', 'jpeg'];
            
            // Validate file extension
            if (!in_array($file_ext, $allowed_extensions)) {
                throw new Exception("Only PNG, JPG, and JPEG files are allowed.");
            }
            
            // Validate file size (max 5MB)
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                throw new Exception("File size must not exceed 5MB.");
            }
            
            // Validate image dimensions (optional)
            $image_info = getimagesize($file['tmp_name']);
            if (!$image_info) {
                throw new Exception("The uploaded file is not a valid image.");
            }
            
            // Create directories if they don't exist
            $upload_dir = dirname($templates[$template_type]['file_path']);
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("Unable to create template directory.");
                }
            }
            
            $file_path = $templates[$template_type]['file_path'];
            
            // Create backup of existing template
            if (file_exists($file_path)) {
                $backup_path = $file_path . '.backup.' . date('Y-m-d-H-i-s');
                copy($file_path, $backup_path);
                unlink($file_path);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                throw new Exception("Failed to upload template file.");
            }
            
            // Set proper permissions
            chmod($file_path, 0644);
            
            // Clear cache for this file
            unset($fileCache[$file_path]);
            
            $success = "Template successfully uploaded!";
            header("Location: template.php?success=" . urlencode($success));
            exit;
            
        } catch (Exception $e) {
            $error = "Error uploading template: " . $e->getMessage();
        }
    } else {
        $upload_error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The file exceeds the maximum size set in PHP configuration.',
            UPLOAD_ERR_FORM_SIZE => 'The file exceeds the maximum size set in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
        ];
        
        $upload_error = $_FILES['template_file']['error'] ?? UPLOAD_ERR_NO_FILE;
        $error = $upload_error_messages[$upload_error] ?? "Unknown error occurred during file upload.";
    }
}

$pageTitle = "Template Management";
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <!-- Loading indicator -->
        <div id="loading-indicator" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Templates are loading...</p>
        </div>
        
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-file-image"></i> Template Management
            </h1>
            <div class="page-actions">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important:</strong> Logo and signature files are used for receipt and certificate generation.
                </div>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-image me-2"></i>
                    Template and File Management
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php 
                    // Sort templates by priority (logo and signature first)
                    uasort($templates, function($a, $b) {
                        return $b['priority'] <=> $a['priority'];
                    });
                    
                    foreach ($templates as $type => $template): 
                        if (isset($template['hidden']) && $template['hidden']) continue;
                        $fileInfo = getFileInfo($template['file_path']);
                    ?>
                        <div class="col-lg-6 col-md-12 mb-4">
                            <div class="card h-100 border-<?php echo $template['priority'] ? 'primary' : 'secondary'; ?> template-card" data-type="<?php echo $type; ?>">
                                <div class="card-header bg-<?php echo $template['priority'] ? 'primary' : 'secondary'; ?> text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-<?php echo $template['icon']; ?> me-2"></i>
                                        <?php echo htmlspecialchars($template['label']); ?>
                                    </h6>
                                    <?php if ($template['priority']): ?>
                                        <small class="opacity-75">Required for receipt and certificate generation</small>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="template-preview mb-3">
                                        <?php if ($fileInfo['exists']): ?>
                                            <div class="text-center">
                                                <img src="<?php echo htmlspecialchars($template['preview_path']) . '?v=' . $fileInfo['modified']; ?>" 
                                                     alt="<?php echo htmlspecialchars($template['name']); ?>" 
                                                     class="img-fluid rounded border shadow-sm template-image" 
                                                     style="max-height: 200px; max-width: 100%; object-fit: contain;"
                                                     loading="lazy">
                                            </div>
                                            <div class="mt-2 text-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Last Updated: <?php echo date('d/m/Y H:i', $fileInfo['modified']); ?>
                                                </small>
                                                <br>
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    File Available
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center bg-light rounded p-4">
                                                <i class="fas fa-<?php echo $template['icon']; ?> fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">No <?php echo $template['label']; ?> available</p>
                                                <?php if ($template['priority']): ?>
                                                    <small class="text-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Required for receipt and certificate generation
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <form method="post" enctype="multipart/form-data" class="upload-form">
                                        <input type="hidden" name="action" value="upload_template">
                                        <input type="hidden" name="template_type" value="<?php echo $type; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-upload me-1"></i>
                                                Upload New File
                                            </label>
                                            <input type="file" 
                                                   class="form-control file-input" 
                                                   name="template_file" 
                                                   accept="image/png,image/jpeg,image/jpg" 
                                                   required>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                PNG, JPG, JPEG (Max 5MB)
                                                <?php if ($type === 'logo'): ?>
                                                    <br><strong>Recommendation:</strong> 200x200px or larger is preferred
                                                <?php elseif ($type === 'signature' || $type === 'signature1'): ?>
                                                    <br><strong>Recommendation:</strong> PNG format with transparent background is preferred
                                                <?php elseif ($type === 'participation_certificate'): ?>
                                                    <br><strong>Recommendation:</strong> PNG format with 1404x990px dimensions is preferred
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-<?php echo $template['priority'] ? 'primary' : 'secondary'; ?> upload-btn">
                                                <i class="fas fa-upload me-2"></i>Upload
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <?php if ($fileInfo['exists']): ?>
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-hdd me-1"></i>
                                                    File Size: <?php echo round($fileInfo['size'] / 1024, 2); ?> KB
                                                </small>
                                                <a href="<?php echo $template['preview_path']; ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-external-link-alt me-1"></i>View
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- File Status Summary - Optimized -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-clipboard-check me-2"></i>
                                    File Status Summary
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-unstyled mb-0">
                                            <?php 
                                            $logoInfo = getFileInfo('../img/logo.png');
                                            $signatureInfo = getFileInfo('../img/signature.png');
                                            ?>
                                            <li class="mb-2">
                                                <i class="fas fa-<?php echo $logoInfo['exists'] ? 'check-circle text-success' : 'times-circle text-danger'; ?> me-2"></i>
                                                <strong>Logo File:</strong> 
                                                <?php echo $logoInfo['exists'] ? 'Available' : 'Unavailable'; ?>
                                                <small class="text-muted">(/img/logo.png)</small>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-<?php echo $signatureInfo['exists'] ? 'check-circle text-success' : 'times-circle text-danger'; ?> me-2"></i>
                                                <strong>Signature File:</strong> 
                                                <?php echo $signatureInfo['exists'] ? 'Available' : 'Unavailable'; ?>
                                                <small class="text-muted">(/img/signature.png)</small>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled mb-0">
                                            <?php 
                                            $idCardInfo = getFileInfo('../templates/id_card.png');
                                            $certInfo = getFileInfo('../templates/certificate-new.png');
                                            $awardLetterInfo = getFileInfo('../templates/award-letter-template.png');
                                            $honoraryAwardInfo = getFileInfo('../templates/honorary-award-new.png');
                                            $congratsCertInfo = getFileInfo('../templates/awards-congratulations-certificate.png');
                                            $partCertInfo = getFileInfo('../templates/participation-certificate-template.png');
                                            ?>
                                            <li class="mb-2">
                                                <i class="fas fa-<?php echo $idCardInfo['exists'] ? 'check-circle text-success' : 'times-circle text-warning'; ?> me-2"></i>
                                                <strong>ID Card Template:</strong> 
                                                <?php echo $idCardInfo['exists'] ? 'Available' : 'Unavailable'; ?>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-<?php echo $certInfo['exists'] ? 'check-circle text-success' : 'times-circle text-warning'; ?> me-2"></i>
                                                <strong>Certificate Template:</strong> 
                                                <?php echo $certInfo['exists'] ? 'Available' : 'Unavailable'; ?>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-<?php echo $awardLetterInfo['exists'] ? 'check-circle text-success' : 'times-circle text-warning'; ?> me-2"></i>
                                                <strong>Award Letter Template:</strong> 
                                                <?php echo $awardLetterInfo['exists'] ? 'Available' : 'Unavailable'; ?>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-<?php echo $honoraryAwardInfo['exists'] ? 'check-circle text-success' : 'times-circle text-warning'; ?> me-2"></i>
                                                <strong>Honorary Award Template:</strong> 
                                                <?php echo $honoraryAwardInfo['exists'] ? 'Available' : 'Unavailable'; ?>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-<?php echo $congratsCertInfo['exists'] ? 'check-circle text-success' : 'times-circle text-warning'; ?> me-2"></i>
                                                <strong>Congratulations Certificate:</strong> 
                                                <?php echo $congratsCertInfo['exists'] ? 'Available' : 'Unavailable'; ?>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-<?php echo $partCertInfo['exists'] ? 'check-circle text-success' : 'times-circle text-warning'; ?> me-2"></i>
                                                <strong>Participation Certificate Template:</strong> 
                                                <?php echo $partCertInfo['exists'] ? 'Available' : 'Unavailable'; ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <?php if (!$logoInfo['exists'] || !$signatureInfo['exists']): ?>
                                    <div class="alert alert-warning mt-3 mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Warning:</strong> Logo and signature files are required for receipt and certificate generation. Please upload them.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success mt-3 mb-0">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Congratulations!</strong> All required files are available. Receipt and certificate generation will work correctly.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.template-preview img {
    transition: transform 0.3s ease;
}

.template-preview img:hover {
    transform: scale(1.05);
}

.card.border-primary {
    border-width: 2px !important;
}

.card.border-secondary {
    border-width: 1px !important;
}

.template-card {
    transition: box-shadow 0.3s ease;
}

.template-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.template-image {
    cursor: pointer;
}

.upload-form {
    position: relative;
}

.upload-btn:disabled {
    opacity: 0.6;
}

/* Loading states */
.loading .upload-btn {
    pointer-events: none;
}

.loading .upload-btn i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Performance optimizations */
.template-image {
    will-change: transform;
}

.card-body {
    contain: layout;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show loading indicator initially
    const loadingIndicator = document.getElementById('loading-indicator');
    
    // File input change handlers for preview
    document.querySelectorAll('.file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const card = this.closest('.template-card');
                const preview = card.querySelector('.template-preview');
                
                reader.onload = function(e) {
                    const existingImg = preview.querySelector('img');
                    if (existingImg) {
                        existingImg.src = e.target.result;
                    } else {
                        const placeholder = preview.querySelector('.bg-light');
                        if (placeholder) {
                            placeholder.innerHTML = `
                                <img src="${e.target.result}" 
                                     class="img-fluid rounded border shadow-sm template-image" 
                                     style="max-height: 200px; max-width: 100%; object-fit: contain;">
                                <div class="mt-2 text-center">
                                    <small class="text-info">
                                        <i class="fas fa-clock me-1"></i>
                                        Preview - Ready for upload
                                    </small>
                                </div>
                            `;
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Form submission handlers
    document.querySelectorAll('.upload-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('.upload-btn');
            const icon = btn.querySelector('i');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
            
            // Show loading indicator
            loadingIndicator.style.display = 'block';
        });
    });
    
    // Image lazy loading optimization
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('.template-image').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Hide loading indicator after page load
    window.addEventListener('load', function() {
        setTimeout(() => {
            loadingIndicator.style.display = 'none';
        }, 500);
    });
});
</script>

<?php include 'includes/footer.php'; ?>