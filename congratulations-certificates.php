<?php
header("Location: index.php");
exit();
session_start();
require_once 'config/config.php';
require_once __DIR__ . '/includes/congratulations_certificate_helpers.php';

$db = getDbConnection();
$congratulationsCertificates = fetchCongratulationsCertificates($db);

$page_meta_title = 'Congratulations Certificates';
$page_meta_description = 'Browse all published congratulations-certificates.';
$page_url = SITE_URL . '/congratulations-certificates.php';

include 'header.php';
include 'navbar.php';
?>

<div class="container-fluid navbar-margin-pusher congratulations-certificates-page">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 page-header-inline">
            <div class="section-heading mb-0">
                <span>Congratulations Certificates</span>
            </div>
            <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Home
            </a>
        </div>

        <?php if (empty($congratulationsCertificates)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No congratulations-certificates are available right now.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($congratulationsCertificates as $index => $certificate): ?>
                    <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 80; ?>">
                        <a href="<?php echo SITE_URL; ?>/congratulations-certificate-view.php?id=<?php echo (int) $certificate['id']; ?>"
                            class="text-decoration-none" target="_blank" rel="noopener">
                            <div class="card-custom congratulations-certificate-list-card h-100">
                                <div class="congratulations-certificate-list-preview">
                                    <?php if (congratulationsCertificateIsPdf($certificate)): ?>
                                        <div class="congratulations-certificate-list-pdf">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>PDF Download</span>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars(congratulationsCertificateGetUrl($certificate), ENT_QUOTES, 'UTF-8'); ?>"
                                            alt="<?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                            class="congratulations-certificate-list-image" loading="lazy">
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span
                                            class="badge <?php echo congratulationsCertificateIsPdf($certificate) ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo congratulationsCertificateIsPdf($certificate) ? 'PDF' : 'IMAGE'; ?>
                                        </span>
                                        <span
                                            class="small text-muted"><?php echo date('d M Y', strtotime($certificate['created_at'])); ?></span>
                                    </div>
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <p class="mb-0 text-muted">
                                        <?php echo congratulationsCertificateIsPdf($certificate) ? 'Click to download the PDF certificate.' : 'Click to open the full-size certificate image.'; ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .page-header-inline .section-heading {
        text-align: left;
    }

    .page-header-inline .section-heading span::after {
        left: 0;
        transform: none;
    }

    .congratulations-certificate-list-card {
        padding: 0 !important;
        overflow: hidden;
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #fff;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    }

    .congratulations-certificate-list-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12);
    }

    .congratulations-certificate-list-preview {
        width: 100%;
        aspect-ratio: 210 / 297;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }

    .congratulations-certificate-list-image {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        display: block;
        transition: transform 0.35s ease;
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08));
    }

    .congratulations-certificate-list-card:hover .congratulations-certificate-image {
        transform: scale(1.02);
    }

    .congratulations-certificate-list-card .card-body {
        padding: 1.25rem 1.25rem 1.5rem !important;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .congratulations-certificate-list-pdf {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #dc3545;
        font-weight: 700;
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(255, 255, 255, 0.95));
    }

    .congratulations-certificate-list-pdf i {
        display: block;
        font-size: 4.5rem;
        margin-bottom: 1rem;
        color: #dc3545;
    }

    .congratulations-certificate-list-card .card-title {
        color: var(--primary-color, #0a1f44);
        font-family: 'Bakbak One', sans-serif;
        font-size: 1.2rem;
        line-height: 1.45;
        margin-bottom: 0.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 767.98px) {
        .congratulations-certificate-list-preview {
            aspect-ratio: 210 / 297;
            min-height: 360px;
        }
    }
</style>

<?php include 'footer.php'; ?>