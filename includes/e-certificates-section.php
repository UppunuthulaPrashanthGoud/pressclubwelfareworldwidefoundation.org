<?php
require_once __DIR__ . '/e_certificate_helpers.php';

$homepageECertificates = [];

try {
    $homepageECertificates = fetchECertificates($db, 6);
} catch (Exception $e) {
    logError('E-certificate homepage section error: ' . $e->getMessage());
}
?>

<?php if (!empty($homepageECertificates)): ?>
<div class="container-fluid my-5 e-certificate-section" id="e-certificates-section">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 e-certificate-section-header">
            <h3 class="section-heading mb-0"><span>E-Certificates</span></h3>
            <a href="<?php echo SITE_URL; ?>/e-certificates.php" class="btn btn-primary">
                <i class="fas fa-th-large me-2"></i>View All
            </a>
        </div>

        <?php $certificateChunks = array_chunk($homepageECertificates, 3); ?>

        <div id="eCertificateCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
            <div class="carousel-inner">
                <?php foreach ($certificateChunks as $chunkIndex => $chunk): ?>
                    <div class="carousel-item <?php echo $chunkIndex === 0 ? 'active' : ''; ?>">
                        <div class="row g-4">
                            <?php foreach ($chunk as $certificate): ?>
                                <div class="col-12 col-md-6 col-lg-4">
                                    <a
                                        href="<?php echo SITE_URL; ?>/e-certificate-view.php?id=<?php echo (int) $certificate['id']; ?>"
                                        class="text-decoration-none"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <div class="card-custom e-certificate-card h-100">
                                            <div class="e-certificate-preview">
                                                <?php if (eCertificateIsPdf($certificate)): ?>
                                                    <div class="e-certificate-pdf">
                                                        <i class="fas fa-file-pdf"></i>
                                                        <span>PDF Certificate</span>
                                                    </div>
                                                <?php else: ?>
                                                    <img
                                                        src="<?php echo htmlspecialchars(eCertificateGetUrl($certificate), ENT_QUOTES, 'UTF-8'); ?>"
                                                        alt="<?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        class="e-certificate-image"
                                                        loading="<?php echo $chunkIndex === 0 ? 'eager' : 'lazy'; ?>"
                                                    >
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <span class="badge <?php echo eCertificateIsPdf($certificate) ? 'bg-danger' : 'bg-success'; ?>">
                                                        <?php echo eCertificateIsPdf($certificate) ? 'PDF' : 'IMAGE'; ?>
                                                    </span>
                                                    <span class="small text-muted">
                                                        <?php echo date('d M Y', strtotime($certificate['created_at'])); ?>
                                                    </span>
                                                </div>
                                                <h5 class="card-title mb-3"><?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                                <span class="btn btn-outline-primary btn-sm">
                                                    <?php echo eCertificateIsPdf($certificate) ? 'Download' : 'Preview'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($certificateChunks) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#eCertificateCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#eCertificateCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>

                <div class="carousel-indicators">
                    <?php foreach ($certificateChunks as $index => $unusedChunk): ?>
                        <button
                            type="button"
                            data-bs-target="#eCertificateCarousel"
                            data-bs-slide-to="<?php echo $index; ?>"
                            class="<?php echo $index === 0 ? 'active' : ''; ?>"
                            aria-label="Slide <?php echo $index + 1; ?>"
                        ></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.e-certificate-section {
    background: #ffffff;
    padding: 3rem 0;
}

.e-certificate-section-header .section-heading {
    text-align: left;
}

.e-certificate-section-header .section-heading span::after {
    left: 0;
    transform: none;
}

.e-certificate-card {
    overflow: hidden;
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.e-certificate-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.12);
}

.e-certificate-preview {
    height: 260px;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(255, 255, 255, 0.85));
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.e-certificate-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.e-certificate-pdf {
    text-align: center;
    color: #dc3545;
    font-weight: 700;
}

.e-certificate-pdf i {
    display: block;
    font-size: 4rem;
    margin-bottom: 0.75rem;
}

.e-certificate-card .card-title {
    color: var(--primary-color, #0d6efd);
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.15rem;
    line-height: 1.4;
}

#eCertificateCarousel .carousel-control-prev,
#eCertificateCarousel .carousel-control-next {
    width: 48px;
    height: 48px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--gradient-primary, #0d6efd);
    border-radius: 50%;
    opacity: 0.85;
}

#eCertificateCarousel .carousel-control-prev:hover,
#eCertificateCarousel .carousel-control-next:hover {
    opacity: 1;
}

#eCertificateCarousel .carousel-indicators {
    bottom: -42px;
}

#eCertificateCarousel .carousel-indicators [data-bs-target] {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #c7d0da;
    border: none;
}

#eCertificateCarousel .carousel-indicators .active {
    background-color: var(--primary-color, #0d6efd);
}

@media (max-width: 767.98px) {
    .e-certificate-section-header {
        align-items: flex-start !important;
    }

    .e-certificate-preview {
        height: 220px;
    }

    #eCertificateCarousel .carousel-control-prev,
    #eCertificateCarousel .carousel-control-next {
        display: none;
    }
}
</style>
<?php endif; ?>
