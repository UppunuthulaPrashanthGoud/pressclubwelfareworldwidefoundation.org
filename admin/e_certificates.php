<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';
require_once '../includes/e_certificate_helpers.php';

if (!isAdmin() && !isCoordinator()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit;
}

$pageTitle = 'E-Certificate Management';
$db = getDbConnection();
ensureECertificatesTable($db);

$action = $_GET['action'] ?? 'list';
$id = (int) ($_GET['id'] ?? 0);
$success = sanitizeInput($_GET['success'] ?? '', false);
$error = '';
$formData = [
    'id' => 0,
    'title' => '',
    'file_path' => '',
    'file_type' => 'image',
];

if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM e_certificates WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $existingCertificate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingCertificate) {
        $formData = $existingCertificate;
    } else {
        $error = 'The selected e-certificate could not be found.';
        $action = 'list';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $formAction = $_POST['action'] ?? '';

        if ($formAction === 'add' || $formAction === 'edit') {
            $certificateId = (int) ($_POST['certificate_id'] ?? 0);
            $title = sanitizeInput($_POST['title'] ?? '', false);

            $currentCertificate = [
                'id' => 0,
                'title' => '',
                'file_path' => '',
                'file_type' => 'image',
            ];

            if ($formAction === 'edit') {
                $stmt = $db->prepare("SELECT * FROM e_certificates WHERE id = ? LIMIT 1");
                $stmt->execute([$certificateId]);
                $currentCertificate = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                if (empty($currentCertificate)) {
                    $error = 'The selected e-certificate could not be found.';
                }
            }

            $formData = array_merge($currentCertificate, [
                'id' => $certificateId,
                'title' => $title,
            ]);
            $action = $formAction === 'edit' ? 'edit' : 'add';

            if ($title === '') {
                $error = 'Title is required.';
            }

            $newFileUploaded = isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] !== UPLOAD_ERR_NO_FILE;

            if (empty($error) && $formAction === 'add' && !$newFileUploaded) {
                $error = 'Please upload an image or PDF file.';
            }

            if (empty($error) && $newFileUploaded) {
                if ($_FILES['certificate_file']['error'] !== UPLOAD_ERR_OK) {
                    $error = 'The file upload failed. Please try again.';
                } else {
                    $uploadResult = uploadFile(
                        $_FILES['certificate_file'],
                        'img/e_certificates',
                        ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
                        10 * 1024 * 1024
                    );

                    if (!$uploadResult['success']) {
                        $error = $uploadResult['message'];
                    } else {
                        $newPath = $uploadResult['path'];
                        $newType = eCertificateDetermineType($uploadResult['filename']);
                        $formData['file_path'] = $newPath;
                        $formData['file_type'] = $newType;

                        if (
                            $formAction === 'edit' &&
                            !empty($currentCertificate['file_path']) &&
                            $currentCertificate['file_path'] !== $newPath
                        ) {
                            $oldFilePath = eCertificateGetDiskPath($currentCertificate);
                            if (is_file($oldFilePath)) {
                                @unlink($oldFilePath);
                            }
                        }
                    }
                }
            } elseif ($formAction === 'edit') {
                $formData['file_path'] = $currentCertificate['file_path'] ?? '';
                $formData['file_type'] = $currentCertificate['file_type'] ?? 'image';
            }

            if (empty($error)) {
                if ($formAction === 'add') {
                    $stmt = $db->prepare("
                        INSERT INTO e_certificates (title, file_path, file_type, created_at, updated_at)
                        VALUES (?, ?, ?, NOW(), NOW())
                    ");
                    $stmt->execute([
                        $formData['title'],
                        $formData['file_path'],
                        $formData['file_type'],
                    ]);

                    header("Location: e_certificates.php?success=" . urlencode('E-certificate added successfully.'));
                    exit;
                }

                $stmt = $db->prepare("
                    UPDATE e_certificates
                    SET title = ?, file_path = ?, file_type = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $formData['title'],
                    $formData['file_path'],
                    $formData['file_type'],
                    $certificateId,
                ]);

                header("Location: e_certificates.php?success=" . urlencode('E-certificate updated successfully.'));
                exit;
            }
        } elseif ($formAction === 'delete') {
            $certificateId = (int) ($_POST['certificate_id'] ?? 0);

            $stmt = $db->prepare("SELECT * FROM e_certificates WHERE id = ? LIMIT 1");
            $stmt->execute([$certificateId]);
            $certificate = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($certificate) {
                $stmt = $db->prepare("DELETE FROM e_certificates WHERE id = ?");
                $stmt->execute([$certificateId]);

                $filePath = eCertificateGetDiskPath($certificate);
                if (is_file($filePath)) {
                    @unlink($filePath);
                }

                header("Location: e_certificates.php?success=" . urlencode('E-certificate deleted successfully.'));
                exit;
            }

            $error = 'The selected e-certificate could not be found.';
        }
    }
}

$eCertificates = fetchECertificates($db);

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-certificate"></i> E-Certificate Management
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                    <a href="e_certificates.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add E-Certificate
                    </a>
                <?php else: ?>
                    <a href="e_certificates.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'edit' ? 'pen' : 'plus'; ?>"></i>
                    <?php echo $action === 'edit' ? 'Edit E-Certificate' : 'Add New E-Certificate'; ?>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'edit' : 'add'; ?>">
                        <input type="hidden" name="certificate_id" value="<?php echo (int) ($formData['id'] ?? 0); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="title"
                                name="title"
                                value="<?php echo htmlspecialchars($formData['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required
                                placeholder="Enter e-certificate title"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="certificate_file" class="form-label">
                                File Upload <?php if ($action === 'add'): ?><span class="text-danger">*</span><?php endif; ?>
                            </label>
                            <input
                                type="file"
                                class="form-control"
                                id="certificate_file"
                                name="certificate_file"
                                accept=".jpg,.jpeg,.png,.webp,.pdf"
                                <?php echo $action === 'add' ? 'required' : ''; ?>
                            >
                            <div class="form-text">
                                Accepted formats: JPG, JPEG, PNG, WEBP, PDF. Maximum size: 10MB.
                                <?php if ($action === 'edit'): ?>
                                    Leave this empty to keep the current file.
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($action === 'edit' && !empty($formData['file_path'])): ?>
                            <div class="current-certificate-preview mb-4">
                                <div class="preview-header">
                                    <span class="fw-semibold">Current File</span>
                                    <a
                                        href="<?php echo SITE_URL; ?>/e-certificate-view.php?id=<?php echo (int) $formData['id']; ?>"
                                        class="btn btn-sm btn-outline-primary"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <i class="fas fa-eye"></i> Open Current File
                                    </a>
                                </div>
                                <?php if (eCertificateIsPdf($formData)): ?>
                                    <div class="current-pdf-preview">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>Current file is a PDF document.</span>
                                    </div>
                                <?php else: ?>
                                    <img
                                        src="<?php echo htmlspecialchars(eCertificateGetUrl($formData), ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars($formData['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                        class="img-thumbnail current-certificate-image"
                                    >
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $action === 'edit' ? 'Update E-Certificate' : 'Save E-Certificate'; ?>
                            </button>
                            <a href="e_certificates.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-list"></i> E-Certificate List
                </div>
                <div class="card-body">
                    <?php if (!empty($eCertificates)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Preview</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($eCertificates as $certificate): ?>
                                        <tr>
                                            <td style="width: 120px;">
                                                <?php if (eCertificateIsPdf($certificate)): ?>
                                                    <div class="table-pdf-icon">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <img
                                                        src="<?php echo htmlspecialchars(eCertificateGetUrl($certificate), ENT_QUOTES, 'UTF-8'); ?>"
                                                        alt="<?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        class="img-thumbnail table-certificate-thumb"
                                                    >
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="badge <?php echo eCertificateIsPdf($certificate) ? 'bg-danger' : 'bg-success'; ?>">
                                                    <?php echo eCertificateIsPdf($certificate) ? 'PDF' : 'Image'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y, h:i A', strtotime($certificate['updated_at'] ?? $certificate['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a
                                                        href="<?php echo SITE_URL; ?>/e-certificate-view.php?id=<?php echo (int) $certificate['id']; ?>"
                                                        class="btn btn-sm btn-info"
                                                        target="_blank"
                                                        rel="noopener"
                                                        title="<?php echo eCertificateIsPdf($certificate) ? 'Download PDF' : 'Preview Image'; ?>"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a
                                                        href="e_certificates.php?action=edit&id=<?php echo (int) $certificate['id']; ?>"
                                                        class="btn btn-sm btn-warning"
                                                        title="Edit"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this e-certificate?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="certificate_id" value="<?php echo (int) $certificate['id']; ?>">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRF(), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> No e-certificates have been added yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.current-certificate-preview {
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
    background: #f8f9fa;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.current-certificate-image {
    max-width: 220px;
    max-height: 220px;
    object-fit: cover;
}

.current-pdf-preview,
.table-pdf-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: 90px;
    min-width: 90px;
    border-radius: 0.75rem;
    background: rgba(220, 53, 69, 0.12);
    color: #dc3545;
    font-weight: 600;
}

.current-pdf-preview {
    width: 100%;
    padding: 1.5rem;
    justify-content: flex-start;
}

.table-pdf-icon i,
.current-pdf-preview i {
    font-size: 2rem;
}

.table-certificate-thumb {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 0.5rem;
}

@media (max-width: 767.98px) {
    .preview-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
