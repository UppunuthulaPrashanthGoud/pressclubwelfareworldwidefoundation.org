<?php
// Get Shankaracharya content - purely dynamic from your table structure
$shankaracharya_items = [];

try {
    $stmt = $db->prepare("SELECT * FROM shankaracharya_content ORDER BY id ASC");
    $stmt->execute();
    $shankaracharya_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Shankaracharya content database query failed: " . $e->getMessage());
}
?>

<?php if (!empty($shankaracharya_items)): ?>
<!-- Dynamic Shankaracharya Section -->
<div class="container-fluid shankaracharya-section my-5 bg-light py-5" data-aos="fade-up" data-aos-delay="100">
    <h3 class="section-heading text-center"><span>Our Devotion</span></h3>
    <div class="container">
        <?php if (count($shankaracharya_items) > 1): ?>
            <div id="shankaracharyaCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000" data-bs-pause="hover">
                <div class="carousel-indicators">
                    <?php foreach ($shankaracharya_items as $index => $item): ?>
                        <button type="button" data-bs-target="#shankaracharyaCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="true" aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                
                <div class="carousel-inner">
                    <?php foreach ($shankaracharya_items as $index => $item): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="shankaracharya-vertical-layout">
                                <div class="text-center mb-4" data-aos="fade-down" data-aos-delay="<?php echo ($index * 100); ?>">
                                    <div class="shankaracharya-image-container">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="img/shankaracharya/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 class="img-fluid shankaracharya-responsive-image" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 onload="this.classList.add('loaded')">
                                        <?php else: ?>
                                            <div class="shankaracharya-image-placeholder">
                                                <i class="fas fa-user-tie fa-5x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="shankaracharya-content" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100 + 200); ?>">
                                    <h2 class="content-title mb-4 text-center">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </h2>
                                    
                                    <div class="description-content">
                                        <div class="description-text" style="font-size: 1.1rem; line-height: 1.8; color: var(--text-color); text-align: justify;">
                                            <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button class="carousel-control-prev" type="button" data-bs-target="#shankaracharyaCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#shankaracharyaCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        <?php else: ?>
            <!-- Single item display -->
            <?php $item = $shankaracharya_items[0]; ?>
            <div class="shankaracharya-vertical-layout">
                <div class="text-center mb-4" data-aos="fade-down" data-aos-delay="100">
                    <div class="shankaracharya-image-container">
                        <?php if (!empty($item['image'])): ?>
                            <img src="img/shankaracharya/<?php echo htmlspecialchars($item['image']); ?>" 
                                 class="img-fluid shankaracharya-responsive-image loaded" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <?php else: ?>
                            <div class="shankaracharya-image-placeholder">
                                <i class="fas fa-user-tie fa-5x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="shankaracharya-content" data-aos="fade-up" data-aos-delay="200">
                    <h2 class="content-title mb-4 text-center">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </h2>
                    
                    <div class="description-content">
                        <div class="description-text" style="font-size: 1.1rem; line-height: 1.8; color: var(--text-color); text-align: justify;">
                            <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<style>
/* Enhanced Shankaracharya Section Styles with Responsive Images */
.shankaracharya-section {
    background: linear-gradient(135deg, var(--light-bg) 0%, rgba(255,255,255,0.9) 100%);
    border-radius: 15px;
    padding: 2rem;
    margin: 1rem;
    box-shadow: 0 8px 25px var(--shadow-light);
    border: 1px solid var(--border-light);
    position: relative;
    overflow: hidden;
}

.shankaracharya-section .section-heading {
    font-family: 'Teko', sans-serif;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-color);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    position: relative;
}

.shankaracharya-section .section-heading span::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: var(--secondary-color);
}

.shankaracharya-image-container {
    position: relative;
    transition: transform 0.3s ease;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Responsive Image Class - Key Fix for Mobile */
.shankaracharya-responsive-image {
    opacity: 0;
    transition: opacity 0.5s ease-in-out, transform 0.3s ease;
    border: 3px solid var(--primary-color);
    border-radius: 10px;
    box-shadow: 0 4px 15px var(--shadow-light);
    
    /* Ensure complete image display without cropping */
    width: auto !important;
    height: auto !important;
    max-width: 100% !important;
    
    /* Remove any height restrictions that cause cropping */
    max-height: none !important;
    
    /* Maintain aspect ratio */
    object-fit: contain;
    
    /* Center the image */
    display: block;
    margin: 0 auto;
}

.shankaracharya-responsive-image.loaded {
    opacity: 1;
}

.shankaracharya-image-container:hover .shankaracharya-responsive-image {
    transform: scale(1.02);
    box-shadow: 0 6px 20px var(--shadow-medium);
}

.shankaracharya-vertical-layout {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
}

.shankaracharya-image-placeholder {
    width: 100%;
    max-width: 400px;
    height: 250px;
    border-radius: 10px;
    background: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid var(--primary-color);
    animation: pulse 1.5s ease-in-out infinite alternate;
    margin: 0 auto;
}

@keyframes pulse {
    0% { opacity: 0.6; }
    100% { opacity: 1; }
}

.content-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--primary-color) !important;
    font-family: 'Teko', sans-serif;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    line-height: 1.3;
}

.description-text {
    font-family: 'Noto Sans Devanagari', sans-serif;
    background: rgba(255,255,255,0.7);
    padding: 1.5rem;
    border-radius: 10px;
    border-left: 4px solid var(--primary-color);
    box-shadow: 0 4px 15px var(--shadow-light);
    color: var(--text-color);
}

/* Carousel Controls */
.shankaracharya-section .carousel-indicators {
    bottom: -40px;
    margin-bottom: 0;
}

.shankaracharya-section .carousel-indicators button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ddd;
    border: none;
    margin: 0 5px;
    transition: all 0.3s ease;
}

.shankaracharya-section .carousel-indicators button.active {
    background: var(--secondary-color);
    transform: scale(1.2);
}

.shankaracharya-section .carousel-control-prev,
.shankaracharya-section .carousel-control-next {
    width: 5%;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.shankaracharya-section .carousel-control-prev:hover,
.shankaracharya-section .carousel-control-next:hover {
    opacity: 1;
}

.shankaracharya-section .carousel-control-prev-icon,
.shankaracharya-section .carousel-control-next-icon {
    background-color: rgba(0,0,0,0.3);
    border-radius: 50%;
    padding: 15px;
    transition: all 0.3s ease;
}

.shankaracharya-section .carousel-control-prev:hover .carousel-control-prev-icon,
.shankaracharya-section .carousel-control-next:hover .carousel-control-next-icon {
    background-color: rgba(0,0,0,0.5);
    transform: scale(1.1);
}

/* Enhanced Mobile Responsive Design */
@media (max-width: 991.98px) {
    .shankaracharya-section {
        padding: 1.5rem;
        margin: 0.5rem;
    }
    
    .shankaracharya-section .section-heading {
        font-size: 1.6rem;
        text-align: center;
    }
    
    .content-title {
        font-size: 1.6rem;
        text-align: center;
    }
    
    .shankaracharya-responsive-image {
        /* Ensure mobile images are never cropped */
        max-width: 95% !important;
    }
    
    .shankaracharya-image-placeholder {
        max-width: 300px;
        height: 200px;
    }
}

@media (max-width: 767.98px) {
    .shankaracharya-section {
        padding: 1rem;
    }
    
    .shankaracharya-section .section-heading {
        font-size: 1.4rem;
    }
    
    .content-title {
        font-size: 1.4rem;
    }
    
    .description-text {
        font-size: 1rem;
        padding: 1rem;
    }
    
    .shankaracharya-responsive-image {
        /* Mobile optimization - ensure full image visibility */
        max-width: 90% !important;
        border-width: 2px;
    }
    
    .shankaracharya-image-placeholder {
        max-width: 250px;
        height: 180px;
    }
}

@media (max-width: 575.98px) {
    .shankaracharya-section .section-heading {
        font-size: 1.2rem;
    }
    
    .content-title {
        font-size: 1.2rem;
    }
    
    .description-text {
        font-size: 0.95rem;
        padding: 0.8rem;
    }
    
    .shankaracharya-responsive-image {
        /* Small mobile - prioritize complete image display */
        max-width: 88% !important;
        border-width: 2px;
        border-radius: 8px;
    }
    
    .shankaracharya-image-placeholder {
        max-width: 220px;
        height: 160px;
    }
    
    .shankaracharya-image-container:hover .shankaracharya-responsive-image {
        /* Reduce hover effect on small screens */
        transform: scale(1.01);
    }
}

/* Extra small devices */
@media (max-width: 480px) {
    .shankaracharya-responsive-image {
        max-width: 85% !important;
        border-radius: 6px;
    }
    
    .description-text {
        font-size: 0.9rem;
        padding: 0.7rem;
        text-align: left;
    }
    
    .shankaracharya-image-placeholder {
        max-width: 200px;
        height: 140px;
    }
}

/* Landscape orientation on mobile */
@media (max-width: 767.98px) and (orientation: landscape) {
    .shankaracharya-responsive-image {
        max-width: 70% !important;
        max-height: 60vh !important;
    }
}

/* High DPI displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .shankaracharya-responsive-image {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shankaracharyaCarousel = document.getElementById('shankaracharyaCarousel');
    if (shankaracharyaCarousel) {
        const carousel = new bootstrap.Carousel(shankaracharyaCarousel, {
            interval: 6000,
            ride: 'carousel',
            pause: 'hover'
        });

        shankaracharyaCarousel.addEventListener('mouseenter', () => carousel.pause());
        shankaracharyaCarousel.addEventListener('mouseleave', () => carousel.cycle());
    }

    // Ensure images load properly on all devices
    const images = document.querySelectorAll('.shankaracharya-responsive-image');
    images.forEach(img => {
        if (img.complete) {
            img.classList.add('loaded');
        } else {
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });
            
            img.addEventListener('error', function() {
                console.log('Image failed to load:', this.src);
                // Fallback handling
                this.style.display = 'none';
                const placeholder = this.parentNode.querySelector('.shankaracharya-image-placeholder') || 
                                  document.createElement('div');
                if (!this.parentNode.querySelector('.shankaracharya-image-placeholder')) {
                    placeholder.className = 'shankaracharya-image-placeholder';
                    placeholder.innerHTML = '<i class="fas fa-user-tie fa-5x text-muted"></i>';
                    this.parentNode.appendChild(placeholder);
                }
            });
        }
    });
});
</script>