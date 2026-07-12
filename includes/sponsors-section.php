<?php
// Get sponsors for homepage - limit to 4
$stmt = $db->prepare("SELECT * FROM sponsers ORDER BY created_at DESC LIMIT 4");
$stmt->execute();
$homepage_sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($homepage_sponsors)): ?>
<!-- SPONSORS SLIDER SECTION -->
<div class="container-fluid my-5">
    <h3 class="section-heading text-center"><span>Our Valued Sponsors</span></h3>
    <p class="lead text-center text-muted mb-5">We extend our heartfelt gratitude to our sponsors who support our mission</p>
    
    <div class="container">
        <div class="sponsors-slider-container">
            <div class="sponsors-slider" id="sponsorsSlider">
                <?php foreach ($homepage_sponsors as $index => $sponsor): ?>
                    <div class="sponsor-slide">
                        <div class="card-custom h-100">
                            <div class="row g-0 h-100">
                                <?php if (!empty($sponsor['image'])): ?>
                                    <div class="col-md-4">
                                        <div class="position-relative h-100" style="min-height: 200px;">
                                            <img src="<?php echo SITE_URL . '/img/sponsors/' . htmlspecialchars($sponsor['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($sponsor['name']); ?>" 
                                                 class="w-100 h-100"
                                                 style="object-fit: cover;">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="<?php echo !empty($sponsor['image']) ? 'col-md-8' : 'col-12'; ?>">
                                    <div class="card-body h-100 d-flex flex-column p-4">
                                        <h5 class="card-title fw-bold mb-3" style="color: var(--primary-color); font-family: 'Bakbak One', sans-serif; font-size: 1.3rem;">
                                            <?php echo htmlspecialchars($sponsor['name']); ?>
                                        </h5>
                                        <div class="card-text text-muted" style="line-height: 1.7; font-size: 0.95rem;">
                                            <?php 
                                            $content = strip_tags($sponsor['content']);
                                            $preview = mb_substr($content, 0, 150);
                                            echo htmlspecialchars($preview) . (mb_strlen($content) > 150 ? '...' : '');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
                
            <div class="slider-controls">
                <button class="slider-btn prev-btn" onclick="changeSponsorSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slider-btn next-btn" onclick="changeSponsorSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="slider-indicators">
                <?php 
                $totalSlides = count($homepage_sponsors);
                $maxIndicators = ceil($totalSlides / 3);
                for ($i = 0; $i < $maxIndicators; $i++): 
                ?>
                    <button class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" onclick="goToSponsorSlide(<?php echo $i; ?>)"></button>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>/sponsors.php" class="btn btn-lg px-5 py-3 rounded-pill shadow-sm" 
               style="background: var(--primary-color); color: white; font-weight: 600; transition: all 0.3s ease; border: none;">
                <i class="fas fa-handshake me-2"></i> View All Sponsors
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.sponsors-slider-container {
    position: relative;
    max-width: 100%;
    margin: 0 auto;
    overflow: hidden;
}

.sponsors-slider {
    display: flex;
    transition: transform 0.5s ease-in-out;
    width: 100%;
}

.sponsor-slide {
    min-width: 33.333%;
    padding: 0 15px;
    box-sizing: border-box;
}

@media (max-width: 992px) {
    .sponsor-slide {
        min-width: 50%;
    }
}

@media (max-width: 768px) {
    .sponsor-slide {
        min-width: 100%;
    }
}

.sponsors-slider-container .card-custom {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    border: none;
}

.sponsors-slider-container .card-custom:hover {
    transform: translateY(-8px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175);
}

.sponsors-slider-container .card-body {
    padding: 1.5rem;
}

.sponsors-slider-container .card-title {
    font-size: 1.3rem;
    margin-bottom: 0.75rem;
}

.slider-controls {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    pointer-events: none;
    z-index: 10;
}

.slider-btn {
    background: var(--primary-color, #007bff);
    color: white;
    border: none;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    cursor: pointer;
    pointer-events: auto;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 12px rgba(0,0,0,0.2);
}

.slider-btn i {
    font-size: 18px;
}

.slider-btn:hover {
    background: var(--primary-dark, #0056b3);
    transform: scale(1.12);
    box-shadow: 0 0.5rem 1.5rem rgba(0,123,255,.4);
}

.slider-indicators {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 24px;
}

.indicator {
    width: 13px;
    height: 13px;
    border-radius: 50%;
    border: none;
    background: #ddd;
    cursor: pointer;
    transition: all 0.3s ease;
}

.indicator.active {
    background: var(--primary-color, #007bff);
    transform: scale(1.2);
}

.indicator:hover {
    background: var(--primary-dark, #0056b3);
}

.section-heading {
    margin-bottom: 16px;
    font-family: 'Bakbak One', sans-serif;
    color: var(--primary-color);
}

.section-heading span {
    position: relative;
    padding-bottom: 12px;
    font-size: 2.2rem;
}

.section-heading span::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: var(--primary-color);
    border-radius: 2px;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .sponsors-slider-container .card-body {
        padding: 1.25rem !important;
    }
    
    .section-heading span {
        font-size: 1.9rem !important;
    }
    
    .sponsors-slider-container .card-title {
        font-size: 1.15rem !important;
    }
}

@media (max-width: 575.98px) {
    .section-heading span {
        font-size: 1.6rem !important;
    }
    
    .sponsors-slider-container .card-body {
        padding: 1rem !important;
    }
    
    .sponsors-slider-container .card-title {
        font-size: 1.1rem !important;
    }
    
    .slider-btn {
        width: 38px;
        height: 38px;
    }
    
    .slider-btn i {
        font-size: 16px;
    }
}
</style>

<script>
let currentSponsorSlide = 0;
const totalSponsorSlides = <?php echo count($homepage_sponsors); ?>;
const sponsorSlidesPerView = window.innerWidth > 992 ? 3 : (window.innerWidth > 768 ? 2 : 1);
const maxSponsorSlide = Math.max(0, totalSponsorSlides - sponsorSlidesPerView);
let sponsorAutoSlideInterval;

function updateSponsorSlider() {
    const slider = document.getElementById('sponsorsSlider');
    if (!slider) return;
    
    const slideWidth = 100 / sponsorSlidesPerView;
    slider.style.transform = `translateX(-${currentSponsorSlide * slideWidth}%)`;
    
    const indicators = document.querySelectorAll('.sponsors-slider-container .indicator');
    indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === Math.floor(currentSponsorSlide / sponsorSlidesPerView));
    });
}

function changeSponsorSlide(direction) {
    currentSponsorSlide += direction;
    
    if (currentSponsorSlide < 0) {
        currentSponsorSlide = maxSponsorSlide;
    } else if (currentSponsorSlide > maxSponsorSlide) {
        currentSponsorSlide = 0;
    }
    
    updateSponsorSlider();
    resetSponsorAutoSlide();
}

function goToSponsorSlide(index) {
    currentSponsorSlide = Math.min(index * sponsorSlidesPerView, maxSponsorSlide);
    updateSponsorSlider();
    resetSponsorAutoSlide();
}

function nextSponsorSlide() {
    changeSponsorSlide(1);
}

function startSponsorAutoSlide() {
    sponsorAutoSlideInterval = setInterval(nextSponsorSlide, 4000);
}

function resetSponsorAutoSlide() {
    clearInterval(sponsorAutoSlideInterval);
    startSponsorAutoSlide();
}

// Initialize sponsors slider
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('sponsorsSlider')) {
        updateSponsorSlider();
        startSponsorAutoSlide();
        
        const sliderContainer = document.querySelector('.sponsors-slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', () => clearInterval(sponsorAutoSlideInterval));
            sliderContainer.addEventListener('mouseleave', startSponsorAutoSlide);
        }
    }
});

window.addEventListener('resize', function() {
    const newSlidesPerView = window.innerWidth > 992 ? 3 : (window.innerWidth > 768 ? 2 : 1);
    if (newSlidesPerView !== sponsorSlidesPerView) {
        setTimeout(updateSponsorSlider, 100);
    }
});
</script>