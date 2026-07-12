<?php
require_once 'config/config.php';

try {
    $db = getDbConnection();
    
    // Fetch affiliation images from database
    $stmt = $db->prepare("SELECT id, image FROM affiliations WHERE status = 'active' ORDER BY sort_order ASC");
    $stmt->execute();
    $affiliations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Database error in affiliation-slider.php: " . $e->getMessage());
    $affiliations = [];
}

// Group affiliations into slides (2 per slide for desktop)
$affiliationSlides = array_chunk($affiliations, 2);
?>

<?php if (count($affiliations) > 0): ?>
<!-- Affiliation Slider Section -->
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="container">
        <div class="section-heading mb-4">
            <span>Our Recognition and Affiliations and certificate</span>
        </div>
        
        <div id="affiliationCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-inner">
                <?php foreach ($affiliationSlides as $slideIndex => $slideAffiliations): ?>
                <div class="carousel-item <?php echo $slideIndex === 0 ? 'active' : ''; ?>">
                    <div class="affiliation-slide-container">
                        <?php foreach ($slideAffiliations as $affilIndex => $affil): ?>
                        <div class="affiliation-slide">
                            <?php if (!empty($affil['image'])): ?>
                                <img src="<?php echo SITE_URL . '/img/affiliations/' . htmlspecialchars($affil['image']); ?>" 
                                     class="affiliation-image" 
                                     alt="Affiliation"
                                     loading="<?php echo ($slideIndex === 0 && $affilIndex === 0) ? 'eager' : 'lazy'; ?>">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/img/default-affiliation.jpg" 
                                     class="affiliation-image" 
                                     alt="Default Affiliation"
                                     loading="<?php echo ($slideIndex === 0 && $affilIndex === 0) ? 'eager' : 'lazy'; ?>">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($affiliationSlides) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#affiliationCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#affiliationCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            
            <div class="carousel-indicators">
                <?php foreach ($affiliationSlides as $index => $slide): ?>
                <button type="button" data-bs-target="#affiliationCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                        class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.affiliation-slide-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    width: 100%;
    padding: 0 15px;
}

.affiliation-slide {
    position: relative;
    width: 100%;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.affiliation-slide:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.2);
}

.affiliation-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 12px;
    padding: 20px;
}

#affiliationCarousel .carousel-control-prev,
#affiliationCarousel .carousel-control-next {
    width: 50px;
    height: 50px;
    background: var(--gradient-primary);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.8;
    transition: all 0.3s ease;
}

#affiliationCarousel .carousel-control-prev {
    left: -25px;
}

#affiliationCarousel .carousel-control-next {
    right: -25px;
}

#affiliationCarousel .carousel-control-prev:hover,
#affiliationCarousel .carousel-control-next:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
}

#affiliationCarousel .carousel-indicators {
    bottom: -40px;
}

#affiliationCarousel .carousel-indicators [data-bs-target] {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #dee2e6;
    border: 2px solid #6c757d;
    margin: 0 6px;
    transition: all 0.3s ease;
}

#affiliationCarousel .carousel-indicators [data-bs-target].active {
    background: var(--gradient-primary);
    transform: scale(1.3);
}

/* Tablet view - 2 columns */
@media (max-width: 991.98px) {
    .affiliation-slide {
        height: 350px;
    }
}

/* Mobile view - 1 column */
@media (max-width: 767.98px) {
    .affiliation-slide-container {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .affiliation-slide {
        height: 350px;
    }
    
    #affiliationCarousel .carousel-control-prev {
        left: 5px;
    }
    
    #affiliationCarousel .carousel-control-next {
        right: 5px;
    }
}

@media (max-width: 575.98px) {
    .affiliation-slide {
        height: 280px;
    }
    
    .affiliation-image {
        padding: 15px;
    }
    
    #affiliationCarousel .carousel-control-prev,
    #affiliationCarousel .carousel-control-next {
        width: 40px;
        height: 40px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('affiliationCarousel');
    if (carousel) {
        const bootstrapCarousel = new bootstrap.Carousel(carousel, {
            interval: 4000,
            wrap: true,
            touch: true
        });
        
        // Pause on hover
        carousel.addEventListener('mouseenter', function() {
            bootstrapCarousel.pause();
        });
        
        carousel.addEventListener('mouseleave', function() {
            bootstrapCarousel.cycle();
        });
    }
});
</script>
<?php endif; ?>