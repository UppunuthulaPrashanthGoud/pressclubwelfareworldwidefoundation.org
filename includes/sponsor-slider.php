<?php
require_once 'config/config.php';

try {
    $db = getDbConnection();
    
    // Fetch sponsor data from database
    $stmt = $db->prepare("SELECT id, name, designation, photo FROM sponsors WHERE status = 'active' ORDER BY sort_order ASC");
    $stmt->execute();
    $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Database error in sponsor-slider.php: " . $e->getMessage());
    $sponsors = [];
}
?>

<?php if (count($sponsors) > 0): ?>
<!-- Sponsor Slider Section -->
<div class="container-fluid py-5" style="background: #ffffff;">
    <div class="container">
        <div class="section-heading mb-4">
            <span>Our Sponsors</span>
        </div>
        
        <div id="sponsorCarousel" class="sponsor-carousel-wrapper">
            <div class="sponsor-carousel">
                <?php foreach ($sponsors as $index => $sponsor): ?>
                <div class="sponsor-item">
                    <div class="sponsor-card">
                        <?php if (!empty($sponsor['photo'])): ?>
                            <img src="<?php echo SITE_URL . '/img/sponsors/' . htmlspecialchars($sponsor['photo']); ?>" 
                                 class="sponsor-photo" 
                                 alt="<?php echo htmlspecialchars($sponsor['name']); ?>"
                                 loading="<?php echo $index < 4 ? 'eager' : 'lazy'; ?>"
                                 onerror="this.src='<?php echo SITE_URL; ?>/img/default-sponsor.jpg'">
                        <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/img/default-sponsor.jpg" 
                                 class="sponsor-photo" 
                                 alt="<?php echo htmlspecialchars($sponsor['name']); ?>"
                                 loading="<?php echo $index < 4 ? 'eager' : 'lazy'; ?>">
                        <?php endif; ?>
                        <div class="sponsor-info">
                            <h5 class="sponsor-name"><?php echo htmlspecialchars($sponsor['name']); ?></h5>
                            <p class="sponsor-designation"><?php echo htmlspecialchars($sponsor['designation']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($sponsors) > 4): ?>
            <button class="sponsor-control sponsor-prev" type="button" id="sponsorPrev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="sponsor-control sponsor-next" type="button" id="sponsorNext">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.sponsor-carousel-wrapper {
    position: relative;
    overflow: hidden;
    padding: 20px 50px;
}

.sponsor-carousel {
    display: flex;
    gap: 20px;
    transition: transform 0.5s ease;
}

.sponsor-item {
    flex: 0 0 calc(25% - 15px);
    min-width: calc(25% - 15px);
}

.sponsor-card {
    background: #F3722C;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(243, 114, 44, 0.3);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.sponsor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(243, 114, 44, 0.4);
}

.sponsor-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
    border: 4px solid #ffffff;
}

.sponsor-info {
    width: 100%;
}

.sponsor-name {
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 5px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.sponsor-designation {
    font-size: 14px;
    color: #ffffff;
    opacity: 0.95;
    margin: 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
}

.sponsor-control {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 45px;
    height: 45px;
    background: var(--gradient-primary, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
    border: none;
    border-radius: 50%;
    cursor: pointer;
    opacity: 0.8;
    transition: all 0.3s ease;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sponsor-control:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
}

.sponsor-prev {
    left: 0;
}

.sponsor-next {
    right: 0;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    filter: brightness(0) invert(1);
}

@media (max-width: 991.98px) {
    .sponsor-item {
        flex: 0 0 calc(33.333% - 14px);
        min-width: calc(33.333% - 14px);
    }
}

@media (max-width: 767.98px) {
    .sponsor-item {
        flex: 0 0 calc(50% - 10px);
        min-width: calc(50% - 10px);
    }
    
    .sponsor-carousel-wrapper {
        padding: 20px 40px;
    }
    
    .sponsor-control {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 575.98px) {
    .sponsor-item {
        flex: 0 0 100%;
        min-width: 100%;
    }
    
    .sponsor-carousel-wrapper {
        padding: 20px 35px;
    }
    
    .sponsor-control {
        width: 35px;
        height: 35px;
    }
    
    .sponsor-photo {
        width: 100px;
        height: 100px;
    }
}
</style>

<script>
(function() {
    const carousel = document.querySelector('#sponsorCarousel .sponsor-carousel');
    const prevBtn = document.getElementById('sponsorPrev');
    const nextBtn = document.getElementById('sponsorNext');
    
    if (!carousel) return;
    
    let currentIndex = 0;
    let autoplayInterval;
    let itemsPerView = 4;
    
    function updateItemsPerView() {
        const width = window.innerWidth;
        if (width < 576) {
            itemsPerView = 1;
        } else if (width < 768) {
            itemsPerView = 2;
        } else if (width < 992) {
            itemsPerView = 3;
        } else {
            itemsPerView = 4;
        }
    }
    
    function getMaxIndex() {
        const totalItems = carousel.children.length;
        return Math.max(0, totalItems - itemsPerView);
    }
    
    function updateCarousel() {
        const itemWidth = carousel.children[0].offsetWidth;
        const gap = 20;
        const offset = currentIndex * (itemWidth + gap);
        carousel.style.transform = `translateX(-${offset}px)`;
        
        if (prevBtn && nextBtn) {
            prevBtn.style.display = currentIndex === 0 ? 'none' : 'flex';
            nextBtn.style.display = currentIndex >= getMaxIndex() ? 'none' : 'flex';
        }
    }
    
    function moveNext() {
        const maxIndex = getMaxIndex();
        if (currentIndex < maxIndex) {
            currentIndex++;
            updateCarousel();
        } else {
            currentIndex = 0;
            updateCarousel();
        }
    }
    
    function movePrev() {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    }
    
    function startAutoplay() {
        stopAutoplay();
        autoplayInterval = setInterval(moveNext, 3000);
    }
    
    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
        }
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            movePrev();
            stopAutoplay();
            startAutoplay();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            moveNext();
            stopAutoplay();
            startAutoplay();
        });
    }
    
    carousel.parentElement.addEventListener('mouseenter', stopAutoplay);
    carousel.parentElement.addEventListener('mouseleave', startAutoplay);
    
    window.addEventListener('resize', function() {
        updateItemsPerView();
        currentIndex = Math.min(currentIndex, getMaxIndex());
        updateCarousel();
    });
    
    updateItemsPerView();
    updateCarousel();
    startAutoplay();
})();
</script>
<?php endif; ?>