<!-- GALLERY SECTION -->
<div class="container-fluid py-5" style="background: #fff;">
    <div class="container">
        <h3 class="section-heading text-center mb-4"><span>Gallery</span></h3>
        
        <?php if (empty($gallery_images)): ?>
            <div class="alert alert-warning text-center">
                <p>No gallery images available.</p>
            </div>
        <?php else: ?>
            
            <div id="gallerySectionCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner">
                    <?php 
                    // Group gallery images in chunks of 3 for desktop view
                    // We use 3 instead of 2 (like ads) because gallery images are typically better viewed in a slightly denser layout
                    $chunkSize = 3;
                    $galleryChunks = array_chunk($gallery_images, $chunkSize);
                    $globalIndex = 0; // Track global index for modal opening
                    
                    foreach ($galleryChunks as $chunkIndex => $chunk): 
                    ?>
                    <div class="carousel-item <?php echo $chunkIndex === 0 ? 'active' : ''; ?>">
                        <div class="row g-3">
                            <?php foreach ($chunk as $image): ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="gallery-slide-custom" onclick="openGalleryModal(<?php echo $globalIndex; ?>)">
                                    <img src="img/gallery/<?php echo htmlspecialchars($image['image']); ?>" 
                                         class="gallery-image-custom" 
                                         alt="Gallery Image"
                                         loading="<?php echo $chunkIndex === 0 ? 'eager' : 'lazy'; ?>"
                                         onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center h-100 text-muted\'><i class=\'fas fa-image fa-2x\'></i></div>'">
                                    
                                    <div class="gallery-overlay-custom">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                $globalIndex++;
                                endforeach; 
                            ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($gallery_images) > $chunkSize): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#gallerySectionCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#gallerySectionCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                
                <div class="carousel-indicators">
                    <?php foreach ($galleryChunks as $index => $chunk): ?>
                    <button type="button" data-bs-target="#gallerySectionCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                            class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
        <?php endif; ?>
        
        <div class="text-center mt-5">
            <a href="gallery.php" class="btn btn-primary">View Full Gallery</a>
        </div>
    </div>
</div>

<!-- Modal for Gallery (Kept exactly as requested to maintain view functionality) -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="galleryModalLabel">Gallery</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                    <div class="carousel-inner">
                        <?php foreach ($gallery_images as $index => $image): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="text-center carousel-image-wrapper">
                                <img src="img/gallery/<?php echo htmlspecialchars($image['image']); ?>" 
                                     class="d-block mx-auto modal-gallery-image-section" 
                                     alt="Gallery Image">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    <div class="carousel-indicators">
                        <?php foreach ($gallery_images as $index => $image): ?>
                        <button type="button" data-bs-target="#galleryCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                                aria-label="Slide <?php echo $index + 1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" onclick="toggleAutoplay()">
                    <i class="fas fa-pause" id="playPauseIcon"></i> <span id="playPauseText">Pause</span>
                </button>
                <span class="text-white mx-3" id="imageCounter">1 of <?php echo count($gallery_images); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Styles -->
<style>
/* Styles ported from Advertisement Slider */
.gallery-slide-custom {
    position: relative;
    width: 100%;
    height: 300px; /* Slightly shorter than ads for gallery aesthetics */
    display: flex;
    align-items: center;
    justify-content: center;
    background: white; /* Keep white background for cleaner look when images don't fill space */
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.3s ease;
}

.gallery-slide-custom:hover {
    transform: translateY(-5px);
}

.gallery-image-custom {
    width: 100%;
    height: 100%;
    object-fit: contain; /* Changed from cover to contain to prevent cropping */
    border-radius: 12px;
    transition: transform 0.5s ease;
}

.gallery-slide-custom:hover .gallery-image-custom {
    transform: scale(1.05);
}

/* Overlay for gallery items */
.gallery-overlay-custom {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 12px;
}

.gallery-slide-custom:hover .gallery-overlay-custom {
    opacity: 1;
}

.gallery-overlay-custom i {
    font-size: 2rem;
    color: white;
}

/* Carousel Control Styles - Matches Ad Slider */
#gallerySectionCarousel .carousel-control-prev,
#gallerySectionCarousel .carousel-control-next {
    width: 50px;
    height: 50px;
    background: var(--gradient-primary, #0d6efd); /* Fallback to primary color if var not defined */
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.8;
    transition: all 0.3s ease;
}

#gallerySectionCarousel .carousel-control-prev:hover,
#gallerySectionCarousel .carousel-control-next:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
}

#gallerySectionCarousel .carousel-indicators {
    bottom: -40px;
}

#gallerySectionCarousel .carousel-indicators [data-bs-target] {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #dee2e6;
    border: 2px solid #6c757d;
    margin: 0 6px;
    transition: all 0.3s ease;
}

#gallerySectionCarousel .carousel-indicators [data-bs-target].active {
    background: var(--gradient-primary, #0d6efd);
    transform: scale(1.3);
}

/* Modal specific styles */
.modal-gallery-image-section {
    max-height: 75vh;
    max-width: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
}

.carousel-image-wrapper {
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1a1a1a;
    padding: 20px;
}

@media (max-width: 767.98px) {
    .gallery-slide-custom {
        height: 250px;
    }
}
</style>

<script>
let galleryImages = <?php echo json_encode($gallery_images); ?>;
let isAutoplay = true;
let galleryCarousel;

function openGalleryModal(index) {
    const modalElement = document.getElementById('galleryModal');
    const modal = new bootstrap.Modal(modalElement);
    
    // Initialize the carousel inside the modal
    galleryCarousel = new bootstrap.Carousel(document.getElementById('galleryCarousel'), {
        interval: 3000,
        ride: 'carousel'
    });
    
    // Go to the specific slide
    galleryCarousel.to(index);
    modal.show();
    
    updateImageCounter(index + 1);
}

function toggleAutoplay() {
    const playPauseIcon = document.getElementById('playPauseIcon');
    const playPauseText = document.getElementById('playPauseText');
    
    if (isAutoplay) {
        galleryCarousel.pause();
        playPauseIcon.className = 'fas fa-play';
        playPauseText.textContent = 'Play';
        isAutoplay = false;
    } else {
        galleryCarousel.cycle();
        playPauseIcon.className = 'fas fa-pause';
        playPauseText.textContent = 'Pause';
        isAutoplay = true;
    }
}

function updateImageCounter(current) {
    const counterElement = document.getElementById('imageCounter');
    if (counterElement && galleryImages) {
        counterElement.textContent = `${current} of ${galleryImages.length}`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the main Section Carousel
    const sectionCarousel = document.getElementById('gallerySectionCarousel');
    if (sectionCarousel) {
        new bootstrap.Carousel(sectionCarousel, {
            interval: 3000,
            wrap: true,
            touch: true,
            ride: 'carousel'
        });
    }

    // Modal Carousel event listener
    const modalCarouselElement = document.getElementById('galleryCarousel');
    if (modalCarouselElement) {
        modalCarouselElement.addEventListener('slide.bs.carousel', function (e) {
            updateImageCounter(e.to + 1);
        });
    }
});
</script>