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
                <!-- View All removed -->
            </div>

            <div class="e-certificate-slider">
                <?php foreach ($homepageECertificates as $certificate): ?>
                    <div class="item p-2">
                        <a href="<?php echo SITE_URL; ?>/e-certificate-view.php?id=<?php echo (int) $certificate['id']; ?>"
                            class="text-decoration-none" target="_blank" rel="noopener">
                            <div class="card-custom e-certificate-card h-100">
                                <div class="e-certificate-preview">
                                    <?php if (eCertificateIsPdf($certificate)): ?>
                                        <div class="e-certificate-pdf">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>PDF Certificate</span>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars(eCertificateGetUrl($certificate), ENT_QUOTES, 'UTF-8'); ?>"
                                            alt="<?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                            class="e-certificate-image" loading="lazy">
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
                                    <h5 class="card-title mb-3">
                                        <?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h5>
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
        
        .e-certificate-slider {
            position: relative;
        }

        /* FIX for Owl Carousel 1.3.3 vs 2.3.4 conflict */
        .e-certificate-slider.owl-carousel {
            display: block !important;
        }
        
        .e-certificate-slider .owl-wrapper:after {
            content: ".";
            display: block;
            clear: both;
            visibility: hidden;
            line-height: 0;
            height: 0;
        }

        .e-certificate-card {
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
        }

        .e-certificate-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.12);
        }

        .e-certificate-preview {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(255, 255, 255, 0.85));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            height: 220px;
        }

        .e-certificate-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Owl Carousel 1.3.3 Navigation Styling */
        .e-certificate-slider .owl-controls .owl-buttons {
            position: absolute;
            top: 50%;
            width: 100%;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            pointer-events: none;
            margin-top: -30px;
        }

        .e-certificate-slider .owl-controls .owl-buttons div {
            pointer-events: auto;
            width: 45px;
            height: 45px;
            background: var(--gradient-primary, #0d6efd) !important;
            color: #fff !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            opacity: 1 !important;
            padding: 0;
            margin: 0;
        }
        
        .e-certificate-slider .owl-controls .owl-buttons div:hover {
            transform: scale(1.1);
        }

        .e-certificate-slider .owl-controls .owl-buttons .owl-prev {
            margin-left: -20px;
        }

        .e-certificate-slider .owl-controls .owl-buttons .owl-next {
            margin-right: -20px;
        }
        
        @media (max-width: 767.98px) {
            .e-certificate-section-header {
                align-items: flex-start !important;
            }
            .e-certificate-slider .owl-controls .owl-buttons {
                width: calc(100% - 20px);
                margin-left: 10px;
                margin-right: 10px;
            }
            .e-certificate-slider .owl-controls .owl-buttons .owl-prev,
            .e-certificate-slider .owl-controls .owl-buttons .owl-next {
                margin: 0;
            }
        }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        if(typeof $ !== 'undefined' && $.fn.owlCarousel) {
            $('.e-certificate-slider').owlCarousel({
                items: 3,
                itemsDesktop: [1199, 3],
                itemsDesktopSmall: [992, 2],
                itemsTablet: [768, 1],
                itemsMobile: [576, 1],
                navigation: true,
                pagination: true,
                autoPlay: 4000,
                stopOnHover: true,
                navigationText: [
                    '<i class="fas fa-chevron-left"></i>',
                    '<i class="fas fa-chevron-right"></i>'
                ]
            });
        }
    });
    </script>
<?php endif; ?>
