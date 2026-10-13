<?php
header("Location: index.php");
exit();
session_start();
require_once 'config/config.php';
require_once __DIR__ . '/includes/e_certificate_helpers.php';

$db = getDbConnection();
$eCertificates = fetchECertificates($db);

$page_meta_title = 'E-Certificates';
$page_meta_description = 'Browse all published e-certificates.';
$page_url = SITE_URL . '/e-certificates.php';

include 'header.php';
include 'navbar.php';
?>

<div class="container-fluid navbar-margin-pusher e-certificates-page">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 page-header-inline">
            <div class="section-heading mb-0">
                <span>E-Certificates</span>
            </div>
            <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Home
            </a>
        </div>

        <?php if (empty($eCertificates)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No e-certificates are available right now.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($eCertificates as $index => $certificate): ?>
                    <div class="col-12 col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 80; ?>">
                        <a
                            href="<?php echo SITE_URL; ?>/e-certificate-view.php?id=<?php echo (int) $certificate['id']; ?>"
                            class="text-decoration-none"
                            target="_blank"
                            rel="noopener"
                        >
                            <div class="card-custom e-certificate-list-card h-100">
                                <div class="e-certificate-list-preview">
                                    <?php if (eCertificateIsPdf($certificate)): ?>
                                        <div class="e-certificate-list-pdf">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>PDF Download</span>
                                        </div>
                                    <?php else: ?>
                                        <img
                                            src="<?php echo htmlspecialchars(eCertificateGetUrl($certificate), ENT_QUOTES, 'UTF-8'); ?>"
                                            alt="<?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                            class="e-certificate-list-image"
                                            loading="lazy"
                                        >
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge <?php echo eCertificateIsPdf($certificate) ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo eCertificateIsPdf($certificate) ? 'PDF' : 'IMAGE'; ?>
                                        </span>
                                        <span class="small text-muted"><?php echo date('d M Y', strtotime($certificate['created_at'])); ?></span>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <p class="mb-0 text-muted">
                                        <?php echo eCertificateIsPdf($certificate) ? 'Click to download the PDF certificate.' : 'Click to open the full-size certificate image.'; ?>
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

.e-certificate-list-card {
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

.e-certificate-list-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12);
}

.e-certificate-list-preview {
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

.e-certificate-list-image {
    width: 100% !important;
    height: 100% !important;
    object-fit: contain !important;
    display: block;
    transition: transform 0.35s ease;
    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08));
}

.e-certificate-list-card:hover .e-certificate-list-image {
    transform: scale(1.02);
}

.e-certificate-list-card .card-body {
    padding: 1.25rem 1.25rem 1.5rem !important;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.e-certificate-list-pdf {
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

.e-certificate-list-pdf i {
    display: block;
    font-size: 4.5rem;
    margin-bottom: 1rem;
    color: #dc3545;
}

.e-certificate-list-card .card-title {
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
    .e-certificate-list-preview {
        aspect-ratio: 210 / 297;
        min-height: 360px;
    }
}
</style>

<?php include 'footer.php'; ?>