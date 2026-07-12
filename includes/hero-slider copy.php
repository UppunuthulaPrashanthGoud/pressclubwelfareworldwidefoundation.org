<!-- Featured News Text Box -->
<div class="main-news-header">
    <div class="main-news-text">
        <i class="fas fa-star"></i>
        Featured News
        <i class="fas fa-star"></i>
    </div>
</div>

<!-- CLEAN IMAGE SLIDER WITH LINKS -->
<div id="newsSliderCarousel" class="carousel slide main_slider" data-bs-ride="carousel" data-bs-interval="4000">
    <div class="carousel-indicators">
        <?php foreach ($news_slider as $index => $news_item): ?>
        <button type="button" data-bs-target="#newsSliderCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="true" aria-label="Slide <?php echo $index + 1; ?>"></button>
        <?php endforeach; ?>
    </div>
    
    <div class="carousel-inner">
        <?php foreach ($news_slider as $index => $news_item): ?>
        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
            <a href="news-details.php?id=<?php echo $news_item['id']; ?>" class="d-block h-100 slider-link">
                <div class="slider-image-container">
                    <?php if (!empty($news_item['image'])): ?>
                        <img src="<?php echo SITE_URL . '/uploads/news/' . $news_item['image']; ?>" 
                             class="slider-image" 
                             alt="<?php echo $news_item['title']; ?>"
                             loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                    <?php else: ?>
                        <img src="<?php echo SITE_URL; ?>/img/default-news.jpg" 
                             class="slider-image" 
                             alt="<?php echo $news_item['title']; ?>"
                             loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                    <?php endif; ?>
                    <!-- Subtle overlay for better image visibility -->
                    <div class="slider-overlay"></div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#newsSliderCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#newsSliderCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<style>
/* ENHANCED MAIN NEWS HEADER */
.main-news-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    padding: 0;
    margin: 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.main-news-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

.main-news-text {
    color: white;
    font-weight: bold;
    font-size: 18px;
    text-align: center;
    padding: 12px 20px;
    font-family: "Bakbak One", sans-serif;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    position: relative;
    z-index: 1;
}

.main-news-text i {
    color: #ffd700;
    font-size: 16px;
    animation: sparkle 2s ease-in-out infinite alternate;
}

@keyframes sparkle {
    0% { transform: scale(1) rotate(0deg); }
    100% { transform: scale(1.2) rotate(180deg); }
}

/* Enhanced News Slider - Clean Image Only - Container Adapts to Image */
.main_slider {
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px var(--shadow-darker);
    border-radius: 0;
    margin-top: 0;
}

.carousel-item {
    position: relative;
    min-height: 300px; /* Minimum height for mobile */
    max-height: 700px; /* Maximum height to prevent too tall images */
    height: auto; /* Let height adapt to image */
}

.slider-link {
    width: 100%;
    height: 100%;
    display: block;
    transition: transform 0.3s ease;
}

.slider-link:hover {
    transform: scale(1.02);
}

.slider-image-container {
    position: relative;
    width: 100%;
    height: auto; /* Let container adapt to image height */
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--light-bg);
    overflow: hidden;
}

.slider-image {
    width: 100%; /* Full width */
    height: auto; /* Auto height to maintain aspect ratio */
    max-width: 100%;
    max-height: 700px; /* Prevent extremely tall images */
    object-fit: contain; /* Show full image without cropping */
    border-radius: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
    display: block;
}

.slider-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        45deg, 
        transparent 0%, 
        rgba(0, 0, 0, 0.05) 50%, 
        transparent 100%
    );
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.slider-link:hover .slider-overlay {
    opacity: 1;
}

/* Carousel Controls - Enhanced */
.carousel-control-prev,
.carousel-control-next {
    width: 60px;
    height: 60px;
    background: var(--gradient-primary);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.8;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px var(--shadow-dark);
}

.carousel-control-prev {
    left: 20px;
}

.carousel-control-next {
    right: 20px;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 6px 20px var(--shadow-darker);
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 20px;
    height: 20px;
}

/* Carousel Indicators - Enhanced */
.carousel-indicators {
    bottom: 20px;
    margin-bottom: 0;
}

.carousel-indicators [data-bs-target] {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid var(--text-white);
    background-color: var(--overlay-medium);
    margin: 0 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px var(--shadow-dark);
}

.carousel-indicators [data-bs-target].active {
    background: var(--gradient-primary);
    transform: scale(1.3);
    box-shadow: 0 4px 10px var(--shadow-darker);
}

@media (max-width: 1199.98px) {
    .carousel-item {
        min-height: 280px;
        max-height: 600px;
    }
    
    .slider-image {
        max-height: 600px;
    }
}

@media (max-width: 991.98px) {
    .carousel-item {
        min-height: 250px;
        max-height: 500px;
    }
    
    .slider-image {
        max-height: 500px;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 50px;
        height: 50px;
    }
    
    .carousel-control-prev {
        left: 15px;
    }
    
    .carousel-control-next {
        right: 15px;
    }
    
    .main-news-text {
        font-size: 16px;
        padding: 10px 15px;
    }
}

@media (max-width: 767.98px) {
    .carousel-item {
        min-height: 200px;
        max-height: 400px;
    }
    
    .slider-image {
        max-height: 400px;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 45px;
        height: 45px;
    }
    
    .carousel-control-prev {
        left: 10px;
    }
    
    .carousel-control-next {
        right: 10px;
    }
    
    .carousel-indicators [data-bs-target] {
        width: 10px;
        height: 10px;
        margin: 0 5px;
    }
    
    .main-news-text {
        font-size: 15px;
        padding: 8px 12px;
        gap: 10px;
    }
}

@media (max-width: 575.98px) {
    .carousel-item {
        min-height: 180px;
        max-height: 350px;
    }
    
    .slider-image {
        max-height: 350px;
    }
    
    .main-news-text {
        font-size: 14px;
        padding: 8px 10px;
        gap: 8px;
    }
    
    .main-news-text i {
        font-size: 12px;
    }
}

/* OPTIMIZED: Fast loading states */
.slider-image {
    transition: opacity 0.3s ease-in-out, transform 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced carousel behavior
    const carousel = document.getElementById('newsSliderCarousel');
    if (carousel) {
        const bootstrapCarousel = new bootstrap.Carousel(carousel, {
            interval: 4000,
            wrap: true,
            touch: true
        });
        
        // Pause on hover, resume on leave
        carousel.addEventListener('mouseenter', function() {
            bootstrapCarousel.pause();
        });
        
        carousel.addEventListener('mouseleave', function() {
            bootstrapCarousel.cycle();
        });
    }
    
    // OPTIMIZED: Simplified image loading for slider images
    const sliderImages = document.querySelectorAll('.slider-image');
    
    sliderImages.forEach((img) => {
        // Set opacity to 1 immediately for visible images
        if (img.loading === 'eager' || img.getBoundingClientRect().top < window.innerHeight) {
            img.style.opacity = '1';
        }
        
        // Error handling for broken images
        img.addEventListener('error', function() {
            this.src = '<?php echo SITE_URL; ?>/img/default-news.jpg';
        });
    });
    
    // OPTIMIZED: Simple lazy loading for slider images
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '1';
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => {
            img.style.opacity = '0.7';
            img.style.transition = 'opacity 0.3s ease';
            
            img.addEventListener('load', function() {
                this.style.opacity = '1';
            });
            
            imageObserver.observe(img);
        });
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.style.opacity = '1';
        });
    }
    
    // Recalculate carousel on window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const carousel = document.getElementById('newsSliderCarousel');
            if (carousel) {
                const bootstrapCarousel = bootstrap.Carousel.getInstance(carousel);
                if (bootstrapCarousel) {
                    bootstrapCarousel.cycle();
                }
            }
        }, 100); // Faster debounce for mobile responsiveness
    });
});
</script>