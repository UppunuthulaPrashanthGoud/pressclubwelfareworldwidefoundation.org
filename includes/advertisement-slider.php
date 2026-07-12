<?php
require_once 'config/config.php';

try {
    $db = getDbConnection();
    
    // Fetch advertisement images from database
    $stmt = $db->prepare("SELECT id, image FROM advertisements WHERE status = 'active' ORDER BY sort_order ASC");
    $stmt->execute();
    $advertisements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Database error in advertisement-slider.php: " . $e->getMessage());
    $advertisements = [];
}
?>

<?php if (count($advertisements) > 0): ?>
<!-- Advertisement Slider Section -->
<div class="container-fluid py-5" style="background: #f8f9fa;">
    <div class="container">
        <div class="section-heading mb-4">
            <span>Our Advertisements</span>
        </div>
        
        <div id="advertisementCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-inner">
                <?php 
                // Group advertisements in pairs for desktop view
                $adChunks = array_chunk($advertisements, 2);
                foreach ($adChunks as $chunkIndex => $chunk): 
                ?>
                <div class="carousel-item <?php echo $chunkIndex === 0 ? 'active' : ''; ?>">
                    <div class="row g-3">
                        <?php foreach ($chunk as $ad): ?>
                        <div class="col-12 col-md-6">
                            <div class="advertisement-slide">
                                <?php if (!empty($ad['image'])): ?>
                                    <img src="<?php echo SITE_URL . '/img/advertisements/' . htmlspecialchars($ad['image']); ?>" 
                                         class="advertisement-image" 
                                         alt="Advertisement"
                                         loading="<?php echo $chunkIndex === 0 ? 'eager' : 'lazy'; ?>"
                                         onerror="this.src='<?php echo SITE_URL; ?>/img/default-ad.jpg'">
                                <?php else: ?>
                                    <img src="<?php echo SITE_URL; ?>/img/default-ad.jpg" 
                                         class="advertisement-image" 
                                         alt="Default Advertisement"
                                         loading="<?php echo $chunkIndex === 0 ? 'eager' : 'lazy'; ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($advertisements) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#advertisementCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#advertisementCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            
            <div class="carousel-indicators">
                <?php foreach ($adChunks as $index => $chunk): ?>
                <button type="button" data-bs-target="#advertisementCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                        class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.advertisement-slide {
    position: relative;
    width: 100%;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.advertisement-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 12px;
}

#advertisementCarousel .carousel-control-prev,
#advertisementCarousel .carousel-control-next {
    width: 50px;
    height: 50px;
    background: var(--gradient-primary);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.8;
    transition: all 0.3s ease;
}

#advertisementCarousel .carousel-control-prev:hover,
#advertisementCarousel .carousel-control-next:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
}

#advertisementCarousel .carousel-indicators {
    bottom: -30px;
}

#advertisementCarousel .carousel-indicators [data-bs-target] {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #dee2e6;
    border: 2px solid #6c757d;
    margin: 0 6px;
    transition: all 0.3s ease;
}

#advertisementCarousel .carousel-indicators [data-bs-target].active {
    background: var(--gradient-primary);
    transform: scale(1.3);
}

@media (max-width: 767.98px) {
    .advertisement-slide {
        height: 300px;
    }
}

@media (max-width: 575.98px) {
    .advertisement-slide {
        height: 250px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('advertisementCarousel');
    if (carousel) {
        const bootstrapCarousel = new bootstrap.Carousel(carousel, {
            interval: 3000,
            wrap: true,
            touch: true,
            ride: 'carousel'
        });
        
        // Ensure auto-slide starts
        bootstrapCarousel.cycle();
    }
});
</script>
<?php endif; ?>