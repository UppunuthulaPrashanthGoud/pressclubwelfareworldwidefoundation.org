<?php
/**
 * Objectives Slider Component
 * Displays organization objectives in a responsive slider
 */
?>

<!-- OUR OBJECTIVES SECTION -->
<?php if (!empty($objectives)): ?>
<div class="container-fluid my-5">
    <h3 class="section-heading text-center"><span>Our Objectives</span></h3>
    <div class="container">
        <div class="objectives-slider-container">
            <div class="objectives-slider" id="objectivesSlider">
                <?php foreach ($objectives as $index => $objective): ?>
                    <div class="objective-slide">
                        <div class="card-custom text-center h-100">
                            <div class="mb-3">
                                <?php if (!empty($objective['image'])): ?>
                                    <img src="img/objectives/<?php echo htmlspecialchars($objective['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($objective['title']); ?>" 
                                         class="objective-image"
                                         onerror="this.src='img/objectives/default-objective.jpg'">
                                <?php else: ?>
                                    <img src="img/objectives/default-objective.jpg" 
                                         alt="Default Objective" 
                                         class="objective-image">
                                <?php endif; ?>
                            </div>
                            <h5><?php echo htmlspecialchars($objective['title']); ?></h5>
                            <p><?php echo htmlspecialchars($objective['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
                
            <div class="slider-controls">
                <button class="slider-btn prev-btn" onclick="changeObjectiveSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slider-btn next-btn" onclick="changeObjectiveSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="slider-indicators">
                <?php 
                $totalSlides = count($objectives);
                $maxIndicators = ceil($totalSlides / 3);
                for ($i = 0; $i < $maxIndicators; $i++): 
                ?>
                    <button class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" onclick="goToObjectiveSlide(<?php echo $i; ?>)"></button>
                <?php endfor; ?>
            </div>
        </div>
        
        <!--<div class="text-center mt-4">-->
        <!--    <a href="our-objectives.php" class="btn btn-primary">View All Objectives</a>-->
        <!--</div>-->
    </div>
</div>
<?php endif; ?>

<style>
.objectives-slider-container {
    position: relative;
    max-width: 100%;
    margin: 0 auto;
    overflow: hidden;
}

.objectives-slider {
    display: flex;
    transition: transform 0.5s ease-in-out;
    width: 100%;
}

.objective-slide {
    min-width: 33.333%;
    padding: 0 15px;
    box-sizing: border-box;
}

@media (max-width: 992px) {
    .objective-slide {
        min-width: 50%;
    }
}

@media (max-width: 768px) {
    .objective-slide {
        min-width: 100%;
    }
}

.objectives-slider-container .card-custom {
    background: #fff;
    border-radius: 10px;
    padding: 30px 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    height: 100%;
    border: none;
}

.objectives-slider-container .card-custom:hover {
    transform: translateY(-5px);
}

.objective-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    display: block;
    margin: 0 auto;
    border-radius: 50%;
    border: 3px solid #f8f9fa;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.objective-image:hover {
    transform: scale(1.1);
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
    background: var(--secondary-color, #007bff);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    pointer-events: auto;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.slider-btn i {
    font-size: 16px;
}

.slider-btn:hover {
    background: var(--primary-color, #0056b3);
    transform: scale(1.1);
}

.slider-indicators {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: none;
    background: #ddd;
    cursor: pointer;
    transition: all 0.3s ease;
}

.indicator.active {
    background: var(--secondary-color, #007bff);
}

.indicator:hover {
    background: var(--primary-color, #0056b3);
}

.section-heading {
    margin-bottom: 40px;
}

.section-heading span {
    position: relative;
    padding-bottom: 10px;
}

.section-heading span::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: var(--secondary-color, #007bff);
}
</style>

<script>
let currentObjectiveSlide = 0;
const totalObjectiveSlides = <?php echo count($objectives); ?>;
const objectiveSlidesPerView = window.innerWidth > 992 ? 3 : (window.innerWidth > 768 ? 2 : 1);
const maxObjectiveSlide = Math.max(0, totalObjectiveSlides - objectiveSlidesPerView);
let objectiveAutoSlideInterval;

function updateObjectiveSlider() {
    const slider = document.getElementById('objectivesSlider');
    if (!slider) return;
    
    const slideWidth = 100 / objectiveSlidesPerView;
    slider.style.transform = `translateX(-${currentObjectiveSlide * slideWidth}%)`;
    
    const indicators = document.querySelectorAll('.objectives-slider-container .indicator');
    indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === Math.floor(currentObjectiveSlide / objectiveSlidesPerView));
    });
}

function changeObjectiveSlide(direction) {
    currentObjectiveSlide += direction;
    
    if (currentObjectiveSlide < 0) {
        currentObjectiveSlide = maxObjectiveSlide;
    } else if (currentObjectiveSlide > maxObjectiveSlide) {
        currentObjectiveSlide = 0;
    }
    
    updateObjectiveSlider();
    resetObjectiveAutoSlide();
}

function goToObjectiveSlide(index) {
    currentObjectiveSlide = Math.min(index * objectiveSlidesPerView, maxObjectiveSlide);
    updateObjectiveSlider();
    resetObjectiveAutoSlide();
}

function nextObjectiveSlide() {
    changeObjectiveSlide(1);
}

function startObjectiveAutoSlide() {
    objectiveAutoSlideInterval = setInterval(nextObjectiveSlide, 4000);
}

function resetObjectiveAutoSlide() {
    clearInterval(objectiveAutoSlideInterval);
    startObjectiveAutoSlide();
}

// Initialize objectives slider
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('objectivesSlider')) {
        updateObjectiveSlider();
        startObjectiveAutoSlide();
        
        const sliderContainer = document.querySelector('.objectives-slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', () => clearInterval(objectiveAutoSlideInterval));
            sliderContainer.addEventListener('mouseleave', startObjectiveAutoSlide);
        }
    }
});

window.addEventListener('resize', function() {
    const newSlidesPerView = window.innerWidth > 992 ? 3 : (window.innerWidth > 768 ? 2 : 1);
    if (newSlidesPerView !== objectiveSlidesPerView) {
        setTimeout(updateObjectiveSlider, 100);
    }
});
</script>