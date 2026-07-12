<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get gallery images
$stmt = $db->prepare("SELECT * FROM gallery ORDER BY id DESC");
$stmt->execute();
$gallery_images = $stmt->fetchAll();

include 'header.php';
include 'navbar.php';
?>

<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    grid-auto-rows: 10px;
    gap: 20px;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.gallery-item img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 8px;
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-overlay i {
    font-size: 2.5rem;
    color: white;
}

/* Modal image styling */
.modal-gallery-image {
    max-height: 75vh;
    max-width: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
}

.carousel-item {
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1a1a1a;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
}

@media (max-width: 576px) {
    .gallery-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}
</style>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Gallery</span></h3>

    <div class="gallery-grid" id="galleryGrid">
        <?php foreach ($gallery_images as $index => $image): ?>
            <div class="gallery-item" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>" onclick="openGalleryModal(<?php echo $index; ?>)">
                <img src="img/gallery/<?php echo htmlspecialchars($image['image']); ?>" 
                     alt="Gallery Image"
                     loading="lazy"
                     onload="resizeGridItem(this.parentElement)">
                <div class="gallery-overlay">
                    <i class="fas fa-search-plus"></i>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($gallery_images)): ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-info-circle mb-3" style="font-size: 3rem;"></i>
                    <p class="h5">No gallery images available.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Gallery Modal -->
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
                            <div class="text-center p-3">
                                <img src="img/gallery/<?php echo htmlspecialchars($image['image']); ?>" 
                                     class="modal-gallery-image d-block mx-auto" 
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

<script>
let galleryImages = <?php echo json_encode($gallery_images); ?>;
let isAutoplay = true;
let galleryCarousel;

// Masonry grid layout function
function resizeGridItem(item) {
    const grid = document.getElementById('galleryGrid');
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

function resizeAllGridItems() {
    const allItems = document.querySelectorAll('.gallery-item');
    allItems.forEach(item => {
        resizeGridItem(item);
    });
}

function openGalleryModal(index) {
    const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
    galleryCarousel = new bootstrap.Carousel(document.getElementById('galleryCarousel'), {
        interval: 3000,
        ride: 'carousel'
    });
    
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
    document.getElementById('imageCounter').textContent = `${current} of ${galleryImages.length}`;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize masonry layout
    resizeAllGridItems();
    
    // Recalculate on window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            resizeAllGridItems();
        }, 250);
    });
    
    // Carousel event listener
    const carouselElement = document.getElementById('galleryCarousel');
    if (carouselElement) {
        carouselElement.addEventListener('slide.bs.carousel', function (e) {
            updateImageCounter(e.to + 1);
        });
    }

    // Image load tracking
    const images = document.querySelectorAll('.gallery-item img');
    images.forEach((img, index) => {
        img.onerror = function() {
            console.log('[Gallery] Image failed to load:', img.src);
            img.parentElement.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-image fa-3x mb-2"></i><p>Image not found</p></div>';
        };
    });
});
</script>

<?php include 'footer.php'; ?>