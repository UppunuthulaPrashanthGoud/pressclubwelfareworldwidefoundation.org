<?php
require_once __DIR__ . '/congratulations_certificate_helpers.php';

$homepageCongratulationsCertificates = [];

try {
    $homepageCongratulationsCertificates = fetchCongratulationsCertificates($db);
} catch (Exception $e) {
    logError('Congratulations certificate homepage section error: ' . $e->getMessage());
}
?>

<?php if (!empty($homepageCongratulationsCertificates)): ?>
    <div class="container-fluid my-5 congratulations-certificate-section" id="congratulations-certificates-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 congratulations-certificate-section-header">
                <h3 class="section-heading mb-0"><span>Congratulations Certificates</span></h3>
                <!-- View All removed -->
            </div>

            <?php $certificateChunks = array_chunk($homepageCongratulationsCertificates, 3); // 3 per row on desktop ?>

            <div id="congratulationsCertificateCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-inner pb-4">
                    <?php foreach ($certificateChunks as $chunkIndex => $chunk): ?>
                        <div class="carousel-item <?php echo $chunkIndex === 0 ? 'active' : ''; ?>">
                            <div class="row g-4 justify-content-center">
                                <?php foreach ($chunk as $certificate): ?>
                                    <div class="col-12 col-md-6 col-lg-4">
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
                                                            class="congratulations-certificate-image img-fluid" loading="<?php echo $chunkIndex === 0 ? 'eager' : 'lazy'; ?>">
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
                    <?php endforeach; ?>
                </div>

                <?php if (count($certificateChunks) > 1): ?>
                    <!-- Navigation Arrows -->
                    <button class="carousel-control-prev custom-carousel-btn" type="button" data-bs-target="#congratulationsCertificateCarousel" data-bs-slide="prev">
                        <span class="custom-carousel-icon"><i class="fas fa-chevron-left"></i></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next custom-carousel-btn" type="button" data-bs-target="#congratulationsCertificateCarousel" data-bs-slide="next">
                        <span class="custom-carousel-icon"><i class="fas fa-chevron-right"></i></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
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

        .congratulations-certificate-card {
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

        .congratulations-certificate-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12);
        }

        .congratulations-certificate-preview {
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

        .congratulations-certificate-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
            display: block;
            transition: transform 0.35s ease;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.08));
        }

        .congratulations-certificate-card:hover .congratulations-certificate-image {
            transform: scale(1.02);
        }

        .congratulations-certificate-pdf {
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

        .congratulations-certificate-pdf i {
            display: block;
            font-size: 4.5rem;
            margin-bottom: 0.75rem;
            color: #dc3545;
        }

        .congratulations-certificate-card .card-body {
            padding: 1.25rem 1.25rem 1.5rem !important;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }

        .congratulations-certificate-card .card-title {
            color: var(--primary-color, #0a1f44);
            font-family: 'Bakbak One', sans-serif;
            font-size: 1.15rem;
            line-height: 1.4;
            margin-bottom: 0.85rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Custom Bootstrap Carousel Arrows */
        .custom-carousel-btn {
            width: 48px;
            height: 48px;
            background: var(--gradient-primary, #0d6efd) !important;
            color: #fff !important;
            border-radius: 50% !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
            transition: all 0.3s ease;
            opacity: 1 !important;
            top: 45%;
            transform: translateY(-50%);
            position: absolute;
            z-index: 10;
            border: 2px solid #fff !important;
        }
        .carousel-control-prev.custom-carousel-btn {
            left: -22px;
        }
        .carousel-control-next.custom-carousel-btn {
            right: -22px;
        }
        .custom-carousel-btn:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
        }
        .custom-carousel-icon {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (max-width: 767.98px) {
            .congratulations-certificate-section-header {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .carousel-control-prev.custom-carousel-btn {
                left: 8px;
            }
            .carousel-control-next.custom-carousel-btn {
                right: 8px;
            }
            .congratulations-certificate-preview {
                aspect-ratio: 210 / 297;
                min-height: 360px;
            }
        }
    </style>
<?php endif; ?>