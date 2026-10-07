<?php
require_once __DIR__ . '/congratulations_certificate_helpers.php';

$homepageCongratulationsCertificates = [];

try {
    $homepageCongratulationsCertificates = fetchCongratulationsCertificates($db, 6);
} catch (Exception $e) {
    logError('E-certificate homepage section error: ' . $e->getMessage());
}
?>

<?php if (!empty($homepageCongratulationsCertificates)): ?>
    <div class="container-fluid my-5 congratulations-certificate-section" id="congratulations-certificates-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 congratulations-certificate-section-header">
                <h3 class="section-heading mb-0"><span>Congratulations Certificates</span></h3>
                <!-- View All removed -->
            </div>

            <div class="congratulations-certificate-slider">
                <?php foreach ($homepageCongratulationsCertificates as $certificate): ?>
                    <div class="item p-2">
                        <a href="<?php echo SITE_URL; ?>/congratulations-certificate-view.php?id=<?php echo (int) $certificate['id']; ?>"
                            class="text-decoration-none" target="_blank" rel="noopener">
                            <div class="card-custom congratulations-certificate-card h-100">
                                <div class="congratulations-certificate-preview">
                                    <?php if (congratulationsCertificateIsPdf($certificate)): ?>
                                        <div class="congratulations-certificate-pdf">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>PDF Certificate</span>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars(congratulationsCertificateGetUrl($certificate), ENT_QUOTES, 'UTF-8'); ?>"
                                            alt="<?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                            class="congratulations-certificate-image" loading="lazy">
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="badge <?php echo congratulationsCertificateIsPdf($certificate) ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo congratulationsCertificateIsPdf($certificate) ? 'PDF' : 'IMAGE'; ?>
                                        </span>
                                        <span class="small text-muted">
                                            <?php echo date('d M Y', strtotime($certificate['created_at'])); ?>
                                        </span>
                                    </div>
                                    <h5 class="card-title mb-3">
                                        <?php echo htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h5>
                                    <span class="btn btn-outline-primary btn-sm">
                                        <?php echo congratulationsCertificateIsPdf($certificate) ? 'Download' : 'Preview'; ?>
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
        .congratulations-certificate-section {
            background: #ffffff;
            padding: 3rem 0;
        }

        .congratulations-certificate-section-header .section-heading {
            text-align: left;
        }

        .congratulations-certificate-section-header .section-heading span::after {
            left: 0;
            transform: none;
        }
        
        .congratulations-certificate-slider {
            position: relative;
        }

        /* FIX for Owl Carousel 1.3.3 vs 2.3.4 conflict */
        .congratulations-certificate-slider.owl-carousel {
            display: block !important;
        }
        
        .congratulations-certificate-slider .owl-wrapper:after {
            content: ".";
            display: block;
            clear: both;
            visibility: hidden;
            line-height: 0;
            height: 0;
        }

        .congratulations-certificate-card {
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
        }

        .congratulations-certificate-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.12);
        }

        .congratulations-certificate-preview {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(255, 255, 255, 0.85));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            height: 220px;
        }

        .congratulations-certificate-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
        }

        .congratulations-certificate-pdf {
            text-align: center;
            color: #dc3545;
            font-weight: 700;
        }

        .congratulations-certificate-pdf i {
            display: block;
            font-size: 4rem;
            margin-bottom: 0.75rem;
        }

        .congratulations-certificate-card .card-title {
            color: var(--primary-color, #0d6efd);
            font-family: 'Bakbak One', sans-serif;
            font-size: 1.15rem;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Owl Carousel 1.3.3 Navigation Styling */
        .congratulations-certificate-slider .owl-controls .owl-buttons {
            position: absolute;
            top: 50%;
            width: 100%;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            pointer-events: none;
            margin-top: -30px;
        }

        .congratulations-certificate-slider .owl-controls .owl-buttons div {
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
        
        .congratulations-certificate-slider .owl-controls .owl-buttons div:hover {
            transform: scale(1.1);
        }

        .congratulations-certificate-slider .owl-controls .owl-buttons .owl-prev {
            margin-left: -20px;
        }

        .congratulations-certificate-slider .owl-controls .owl-buttons .owl-next {
            margin-right: -20px;
        }
        
        @media (max-width: 767.98px) {
            .congratulations-certificate-section-header {
                align-items: flex-start !important;
            }
            .congratulations-certificate-slider .owl-controls .owl-buttons .owl-prev {
                margin-left: -5px;
            }
            .congratulations-certificate-slider .owl-controls .owl-buttons .owl-next {
                margin-right: -5px;
            }
        }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        if(typeof $ !== 'undefined' && $.fn.owlCarousel) {
            $('.congratulations-certificate-slider').owlCarousel({
                items: 3,
                itemsDesktop: [1199, 3],
                itemsDesktopSmall: [992, 2],
                itemsTablet: [768, 2],
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
