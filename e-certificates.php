<?php
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
    overflow: hidden;
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.e-certificate-list-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 30px rgba(0, 0, 0, 0.12);
}

.e-certificate-list-preview {
    height: 280px;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(255, 255, 255, 0.9));
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.e-certificate-list-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.e-certificate-list-pdf {
    color: #dc3545;
    font-weight: 700;
    text-align: center;
}

.e-certificate-list-pdf i {
    display: block;
    font-size: 4rem;
    margin-bottom: 1rem;
}

.e-certificate-list-card .card-title {
    color: var(--primary-color, #0d6efd);
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.2rem;
    line-height: 1.45;
}

@media (max-width: 767.98px) {
    .e-certificate-list-preview {
        height: 230px;
    }
}
</style>

<?php include 'footer.php'; ?>
