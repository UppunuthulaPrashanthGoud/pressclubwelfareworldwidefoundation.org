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
                    
                    <!-- Indicators -->
                    <div class="carousel-indicators" style="bottom: -15px;">
                        <?php foreach ($certificateChunks as $index => $unusedChunk): ?>
                            <button type="button" data-bs-target="#congratulationsCertificateCarousel"
                                data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"
                                aria-label="Slide <?php echo $index + 1; ?>" style="background-color: var(--primary-color, #0a1f44); width: 12px; height: 12px; border-radius: 50%;"></button>
                        <?php endforeach; ?>
                    </div>
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
        
        /* Custom Bootstrap Carousel Arrows */
        .custom-carousel-btn {
            width: 45px;
            height: 45px;
            background: var(--gradient-primary, #0d6efd) !important;
            color: #fff !important;
            border-radius: 50% !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            opacity: 1 !important;
            top: 50%;
            transform: translateY(-50%);
            position: absolute;
            z-index: 10;
        }
        .carousel-control-prev.custom-carousel-btn {
            left: -20px;
        }
        .carousel-control-next.custom-carousel-btn {
            right: -20px;
        }
        .custom-carousel-btn:hover {
            transform: translateY(-50%) scale(1.1);
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
                left: 10px;
            }
            .carousel-control-next.custom-carousel-btn {
                right: 10px;
            }
        }
    </style>
<?php endif; ?>
