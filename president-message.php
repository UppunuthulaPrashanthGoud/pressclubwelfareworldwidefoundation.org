<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get president message
$stmt = $db->prepare("SELECT * FROM president_message WHERE status = 'active' ORDER BY id DESC LIMIT 1");
$stmt->execute();
$president_message = $stmt->fetch();

include 'header.php';
include 'navbar.php';
?>

<main class="container my-5">
    <?php if ($president_message): ?>
        <h3 class="section-heading text-center"><span>FOUNDER'S DESK</span></h3>

        <div class="row president-message mt-4">
            <!-- President Image Section -->
            <div class="col-lg-4 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="president-image-card card-custom">
                    <?php if ($president_message['image']): ?>
                        <img src="img/president/<?php echo htmlspecialchars($president_message['image']); ?>" 
                             alt="<?php echo htmlspecialchars($president_message['president_name']); ?>" 
                             class="img-fluid president-photo">
                    <?php else: ?>
                        <img src="img/default-president.jpg" 
                             alt="President" 
                             class="img-fluid president-photo">
                    <?php endif; ?>
                    <div class="president-info">
                        <h5 class="president-name"><?php echo htmlspecialchars_decode($president_message['president_name']); ?></h5>
                        <p class="president-designation"><?php echo htmlspecialchars_decode($president_message['designation']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Message Content Section -->
            <div class="col-lg-8" data-aos="fade-left">
                <div class="message-content-card card-custom">
                    <div class="message-quote-icon">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <div class="content-html">
                        <?php echo $president_message['message']; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Certificate Gallery Section -->
        <?php if (!empty($president_message['gallery_images'])): ?>
        <div class="row mt-5" data-aos="fade-up">
            <div class="col-12">
                <h4 class="text-center mb-4 certificates-heading">
                    <i class="fas fa-certificate" style="color: var(--primary-color);"></i> Certificates & Achievements
                </h4>
                
                <!-- Masonry Grid Gallery -->
                <div class="certificates-gallery-grid" id="certificatesGalleryGrid">
                    <?php 
                    $galleryImages = explode(',', $president_message['gallery_images']);
                    foreach ($galleryImages as $index => $img): 
                    ?>
                        <div class="certificate-gallery-item" 
                             data-aos="zoom-in" 
                             data-aos-delay="<?php echo $index * 50; ?>"
                             onclick="openCertificateModal(<?php echo $index; ?>)">
                            <img src="img/president/certificates/<?php echo htmlspecialchars(trim($img)); ?>" 
                                 alt="Certificate <?php echo $index + 1; ?>" 
                                 class="certificate-gallery-image"
                                 loading="lazy"
                                 onload="resizeCertificateGridItem(this.parentElement)">
                            <div class="certificate-gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                                <p class="mt-2">Click to view</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-info-circle fa-3x mb-3"></i>
            <p>No message available from the founder's desk.</p>
        </div>
    <?php endif; ?>
</main>

<!-- Certificate Modal -->
<div class="modal fade" id="certificateModal" tabindex="-1" aria-labelledby="certificateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="certificateModalLabel">Certificates & Achievements</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="certificateCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                    <div class="carousel-inner">
                        <?php 
                        if (!empty($president_message['gallery_images'])) {
                            $galleryImages = explode(',', $president_message['gallery_images']);
                            foreach ($galleryImages as $index => $img): 
                        ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="text-center carousel-certificate-wrapper">
                                <img src="img/president/certificates/<?php echo htmlspecialchars(trim($img)); ?>" 
                                     class="d-block mx-auto modal-certificate-image" 
                                     alt="Certificate <?php echo $index + 1; ?>">
                            </div>
                        </div>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#certificateCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#certificateCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    <div class="carousel-indicators">
                        <?php 
                        if (!empty($president_message['gallery_images'])) {
                            $galleryImages = explode(',', $president_message['gallery_images']);
                            foreach ($galleryImages as $index => $img): 
                        ?>
                        <button type="button" data-bs-target="#certificateCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                                aria-label="Slide <?php echo $index + 1; ?>"></button>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" onclick="toggleCertificateAutoplay()">
                    <i class="fas fa-pause" id="certificatePlayPauseIcon"></i> 
                    <span id="certificatePlayPauseText">Pause</span>
                </button>
                <span class="text-white mx-3" id="certificateCounter">
                    1 of <?php echo !empty($president_message['gallery_images']) ? count(explode(',', $president_message['gallery_images'])) : 0; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
/* President Message Styles - Following Site Design Consistency */
.president-message {
    margin-top: 30px;
}

.president-image-card {
    padding: 0;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.president-image-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px var(--shadow-dark);
}

.president-photo {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
}

.president-info {
    padding: 20px;
    text-align: center;
    background: var(--gradient-primary);
    color: var(--text-white);
}

.president-name {
    margin: 0 0 5px 0;
    font-size: 1.3rem;
    font-weight: 600;
    font-family: "Bakbak One", sans-serif;
}

.president-designation {
    margin: 0;
    font-size: 0.95rem;
    opacity: 0.9;
}

.message-content-card {
    position: relative;
    height: 100%;
}

.message-quote-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 3rem;
    color: var(--primary-light);
    opacity: 0.2;
}

.message-content-card .content-html {
    line-height: 1.8;
    color: var(--text-color);
    font-size: 1rem;
}

.message-content-card .content-html p {
    margin-bottom: 1rem;
    text-align: justify;
}

.message-content-card .content-html ul,
.message-content-card .content-html ol {
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.message-content-card .content-html li {
    margin-bottom: 0.5rem;
}

.message-content-card .content-html h1,
.message-content-card .content-html h2,
.message-content-card .content-html h3,
.message-content-card .content-html h4 {
    color: var(--primary-color);
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-family: "Bakbak One", sans-serif;
}

.message-content-card .content-html section {
    padding: 0 !important;
}

.message-content-card .content-html strong {
    color: var(--primary-dark);
}

.message-content-card .content-html em {
    color: var(--text-light);
}

/* Certificates Heading */
.certificates-heading {
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 2rem;
    font-family: "Bakbak One", sans-serif;
}

/* Masonry Grid Layout for Certificates */
.certificates-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    grid-auto-rows: 10px;
    gap: 20px;
    margin-top: 30px;
}

.certificate-gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: var(--white-bg);
    box-shadow: 0 2px 10px var(--shadow-light);
    border: 1px solid var(--border-light);
}

.certificate-gallery-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 4px 15px var(--shadow-dark);
}

.certificate-gallery-image {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 8px;
}

.certificate-gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--primary-color-overlay);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 8px;
    color: var(--text-white);
}

.certificate-gallery-item:hover .certificate-gallery-overlay {
    opacity: 1;
}

.certificate-gallery-overlay i {
    font-size: 3rem;
}

.certificate-gallery-overlay p {
    font-size: 1rem;
    margin: 0;
    font-weight: 500;
}

/* Modal Styles */
.modal-certificate-image {
    max-height: 75vh;
    max-width: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
}

.carousel-certificate-wrapper {
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--dark-bg);
    padding: 20px;
}

.carousel-item {
    min-height: 300px;
}

/* Responsive Styles */
@media (max-width: 992px) {
    .certificates-gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 18px;
    }
    
    .president-image-card {
        margin-bottom: 30px;
    }
}

@media (max-width: 768px) {
    .certificates-gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 15px;
    }
    
    .message-content-card {
        padding: 20px;
    }
    
    .message-quote-icon {
        font-size: 2rem;
        top: 15px;
        right: 15px;
    }
    
    .certificates-heading {
        font-size: 1.5rem;
    }
    
    .certificate-gallery-overlay i {
        font-size: 2.5rem;
    }
}

@media (max-width: 576px) {
    .certificates-gallery-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .message-content-card .content-html {
        font-size: 0.95rem;
    }
    
    .president-name {
        font-size: 1.1rem;
    }
    
    .president-designation {
        font-size: 0.9rem;
    }
    
    .certificate-gallery-overlay i {
        font-size: 2rem;
    }
    
    .certificate-gallery-overlay p {
        font-size: 0.9rem;
    }
}
</style>

<script>
let certificateImages = <?php echo json_encode(!empty($president_message['gallery_images']) ? array_map('trim', explode(',', $president_message['gallery_images'])) : []); ?>;
let isCertificateAutoplay = true;
let certificateCarousel;

// Masonry grid layout function for certificates
function resizeCertificateGridItem(item) {
    const grid = document.getElementById('certificatesGalleryGrid');
    if (!grid) return;
    
    const rowHeight = parseInt(window.getComputedStyle(grid).getPropertyValue('grid-auto-rows'));
    const rowGap = parseInt(window.getComputedStyle(grid).getPropertyValue('gap'));
    const img = item.querySelector('img');
    
    if (img.complete) {
        const rowSpan = Math.ceil((img.getBoundingClientRect().height + rowGap) / (rowHeight + rowGap));
        item.style.gridRowEnd = 'span ' + rowSpan;
    } else {
        img.addEventListener('load', function() {
            const rowSpan = Math.ceil((img.getBoundingClientRect().height + rowGap) / (rowHeight + rowGap));
            item.style.gridRowEnd = 'span ' + rowSpan;
        });
    }
}

function resizeAllCertificateGridItems() {
    const allItems = document.querySelectorAll('.certificate-gallery-item');
    allItems.forEach(item => {
        resizeCertificateGridItem(item);
    });
}

function openCertificateModal(index) {
    const modalElement = document.getElementById('certificateModal');
    const modal = new bootstrap.Modal(modalElement);
    
    certificateCarousel = new bootstrap.Carousel(document.getElementById('certificateCarousel'), {
        interval: 3000,
        ride: 'carousel'
    });
    
    certificateCarousel.to(index);
    modal.show();
    
    updateCertificateCounter(index + 1);
}

function toggleCertificateAutoplay() {
    const playPauseIcon = document.getElementById('certificatePlayPauseIcon');
    const playPauseText = document.getElementById('certificatePlayPauseText');
    
    if (isCertificateAutoplay) {
        certificateCarousel.pause();
        playPauseIcon.className = 'fas fa-play';
        playPauseText.textContent = 'Play';
        isCertificateAutoplay = false;
    } else {
        certificateCarousel.cycle();
        playPauseIcon.className = 'fas fa-pause';
        playPauseText.textContent = 'Pause';
        isCertificateAutoplay = true;
    }
}

function updateCertificateCounter(current) {
    const counterElement = document.getElementById('certificateCounter');
    if (counterElement && certificateImages) {
        counterElement.textContent = `${current} of ${certificateImages.length}`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize masonry layout for certificates
    resizeAllCertificateGridItems();
    
    // Recalculate on window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            resizeAllCertificateGridItems();
        }, 250);
    });
    
    // Carousel event listener
    const carouselElement = document.getElementById('certificateCarousel');
    if (carouselElement) {
        carouselElement.addEventListener('slide.bs.carousel', function (e) {
            updateCertificateCounter(e.to + 1);
        });
    }
    
    // Image load error handling
    const images = document.querySelectorAll('.certificate-gallery-item img');
    images.forEach((img) => {
        img.onerror = function() {
            console.log('[Certificate Gallery] Image failed to load:', img.src);
            img.parentElement.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-image fa-3x mb-2"></i><p>Certificate not found</p></div>';
        };
    });
});
</script>

<?php include 'footer.php'; ?>