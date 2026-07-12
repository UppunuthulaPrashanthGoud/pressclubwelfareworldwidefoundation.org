<?php
require_once 'config/config.php';

try {
    $db = getDbConnection();
    
    // Fetch partner images and the new website column from database
    $stmt = $db->prepare("SELECT id, image, website FROM partners WHERE status = 'active' ORDER BY sort_order ASC");
    $stmt->execute();
    $partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // logError("Database error in partner-slider.php: " . $e->getMessage());
    $partners = [];
}
?>

<?php if (count($partners) > 0): ?>
<!-- Partner Slider Section - Continuous Scroll -->
<div class="container-fluid py-5" style="background: white;">
    <div class="container">
        <div class="section-heading mb-4">
            <span>Our Network</span>
        </div>
        
        <!-- Custom Wrapper for continuous scroll -->
        <div id="partnerScrollWrapper" class="partner-scroll-wrapper">
            <div id="partnerCarousel" class="partner-carousel">
                <?php 
                // Duplicate partners for infinite loop effect
                // This array merging ensures we have at least three copies for smooth looping
                $displayPartners = array_merge($partners, $partners, $partners);
                
                foreach ($displayPartners as $partnerIndex => $partner): 
                ?>
                <div class="partner-item">
                    <div class="partner-slide">
                        <!-- Wrap image in an anchor tag if website link is available -->
                        <?php if (!empty($partner['website'])): ?>
                            <a href="<?php echo htmlspecialchars($partner['website']); ?>" target="_blank" rel="noopener noreferrer" 
                               title="Visit Partner Website" class="partner-link">
                        <?php endif; ?>
                        
                        <?php if (!empty($partner['image'])): ?>
                            <img src="<?php echo SITE_URL . '/img/partners/' . htmlspecialchars($partner['image']); ?>" 
                                 class="partner-image" 
                                 alt="Partner Logo"
                                 loading="lazy"
                                 onerror="this.src='<?php echo SITE_URL; ?>/img/default-partner.jpg'">
                        <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/img/default-partner.jpg" 
                                 class="partner-image" 
                                 alt="Default Partner"
                                 loading="lazy">
                        <?php endif; ?>
                        
                        <?php if (!empty($partner['website'])): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Continuous scrolling wrapper */
.partner-scroll-wrapper {
    overflow: hidden;
    position: relative;
    padding: 20px 0;
    width: 100%;
}

.partner-carousel {
    display: flex;
    /* Adjusting scroll time for potentially more content */
    animation: scroll-loop 35s linear infinite; 
    width: max-content;
}

/* Pause animation on hover */
.partner-scroll-wrapper:hover .partner-carousel {
    animation-play-state: paused;
}

.partner-item {
    /* Increased base width from 280px to 320px for larger items */
    flex: 0 0 320px; 
    min-width: 320px;
    margin-right: 20px;
}

.partner-slide {
    width: 100%;
    /* REMOVED max-height to allow logos to scale vertically based on available width. */
    min-height: 80px; /* Ensures minimum card height for better uniformity */
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    /* Increased padding from 20px to 30px to create a larger, uniform frame around the logo */
    padding: 30px; 
    position: relative;
}

.partner-slide:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

/* Partner link fills entire card */
.partner-link {
    display: block;
    /* Use flex on the link to help the image center/fill */
    display: flex; 
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    text-decoration: none;
    position: relative;
}

/* Image settings to ensure no cropping and adapt size */
.partner-image {
    /* Allows image to scale based on aspect ratio */
    width: auto; 
    height: auto; 
    
    /* These ensure the image occupies max space without exceeding the container or being cropped */
    max-width: 100%;
    max-height: 100%;

    /* This is the key property to prevent cropping, which was already correct */
    object-fit: contain; 
    object-position: center; 
    
    padding: 0;
    margin: 0;
    display: block;
}

/* Keyframes for continuous horizontal scroll */
@keyframes scroll-loop {
    0% {
        transform: translateX(0);
    }
    100% {
        /* Scroll one third (one set of duplicated items) */
        transform: translateX(calc(-33.333% - 20px)); 
    }
}

/* Responsive adjustments */
@media (max-width: 1199.98px) {
    .partner-item {
        /* Adjusted width */
        flex: 0 0 280px;
        min-width: 280px;
    }
    .partner-slide {
        /* Adjusted padding */
        padding: 25px; 
    }
}

@media (max-width: 991.98px) {
    .partner-item {
        /* Adjusted width */
        flex: 0 0 250px;
        min-width: 250px;
        margin-right: 15px;
    }
    
    .partner-slide {
        /* Adjusted padding */
        padding: 20px;
    }
}

@media (max-width: 767.98px) {
    .partner-item {
        /* Adjusted width */
        flex: 0 0 220px;
        min-width: 220px;
    }
    
    .partner-slide {
        /* Adjusted padding */
        padding: 15px;
    }
    
    .partner-carousel {
        animation: scroll-loop 30s linear infinite;
    }
}

@media (max-width: 575.98px) {
    .partner-item {
        /* Adjusted width */
        flex: 0 0 190px;
        min-width: 190px;
        margin-right: 12px;
    }
    
    .partner-slide {
        /* Adjusted padding */
        padding: 10px;
    }
    
    .partner-carousel {
        animation: scroll-loop 25s linear infinite;
    }
}

/* Smooth animation start */
@media (prefers-reduced-motion: reduce) {
    .partner-carousel {
        animation: none;
    }
}
</style>

<script>
// Optional: Add click tracking or analytics here if needed
document.addEventListener('DOMContentLoaded', function() {
    const partnerLinks = document.querySelectorAll('.partner-link');
    
    partnerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Optional: Track partner clicks
            console.log('Partner clicked:', this.href);
        });
    });
    
    // Accessibility: Pause on focus
    const carousel = document.querySelector('.partner-carousel');
    const wrapper = document.querySelector('.partner-scroll-wrapper');
    
    if (carousel && wrapper) {
        wrapper.addEventListener('focusin', function() {
            carousel.style.animationPlayState = 'paused';
        });
        
        wrapper.addEventListener('focusout', function() {
            carousel.style.animationPlayState = 'running';
        });
    }
});
</script>
<?php endif; ?>