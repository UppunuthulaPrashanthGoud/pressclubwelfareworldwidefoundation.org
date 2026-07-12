<?php
require_once 'config/config.php';

try {
    $db = getDbConnection();
    
    // Fetch active social media posts from database
    $stmt = $db->prepare("SELECT id, type, content_file, link, caption FROM social_media WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC");
    $stmt->execute();
    $social_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Database error in social-media-slider.php: " . $e->getMessage());
    $social_posts = [];
}

// Group posts into slides (1 post per slide for focused view)
$postSlides = array_chunk($social_posts, 1); 
?>

<?php if (count($social_posts) > 0): ?>
<!-- Social Media Post Slider Section -->
<div class="container py-5">
    <div class="section-heading mb-5 text-center">
        <span>Media</span>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div id="socialMediaCarousel" class="carousel slide social-media-carousel" data-bs-ride="carousel" data-bs-interval="5000">
                <div class="carousel-inner">
                    <?php foreach ($postSlides as $slideIndex => $slidePosts): 
                        $post = $slidePosts[0];
                        
                        // Check if file is video
                        $is_video = false;
                        if (!empty($post['content_file'])) {
                            $file_path = __DIR__ . '/img/social_media/' . $post['content_file'];
                            // Simple check based on extension for frontend use, as mime_content_type requires a full path
                            if (preg_match('/\.(mp4|webm)$/i', $post['content_file'])) {
                                $is_video = true;
                            }
                        }
                    ?>
                    <div class="carousel-item <?php echo $slideIndex === 0 ? 'active' : ''; ?>">
                        <div class="social-post-card">
                            <div class="post-media-container">
                                <?php if (!empty($post['content_file'])): ?>
                                    <?php if ($is_video): ?>
                                        <!-- Video Content -->
                                        <video class="post-media video-js vjs-default-skin" controls preload="auto" poster="">
                                            <source src="<?php echo SITE_URL . '/img/social_media/' . htmlspecialchars($post['content_file']); ?>" 
                                                    type="video/<?php echo $is_video ? 'mp4' : 'jpeg'; ?>">
                                            Sorry, your browser doesn't support embedded videos.
                                        </video>
                                    <?php else: ?>
                                        <!-- Image Content -->
                                        <img src="<?php echo SITE_URL . '/img/social_media/' . htmlspecialchars($post['content_file']); ?>" 
                                             class="post-media" 
                                             alt="<?php echo htmlspecialchars($post['caption'] ?? 'Social media post'); ?>"
                                             loading="<?php echo ($slideIndex === 0) ? 'eager' : 'lazy'; ?>">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Placeholder or Icon for Link/Text Post -->
                                    <div class="post-media-placeholder">
                                        <i class="fas fa-hashtag fa-4x text-muted mb-3"></i>
                                        <p class="text-muted">External Post / Text Content</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="post-content p-4">
                                <p class="post-caption">
                                    <?php echo nl2br(htmlspecialchars($post['caption'] ?? 'Check out our latest update!')); ?>
                                </p>
                                <?php if (!empty($post['link'])): ?>
                                <a href="<?php echo htmlspecialchars($post['link']); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="btn btn-sm btn-primary mt-3 post-link-btn">
                                    <i class="fas fa-external-link-alt me-2"></i> View Original Post
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($postSlides) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#socialMediaCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#socialMediaCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                
                <div class="carousel-indicators">
                    <?php foreach ($postSlides as $index => $slide): ?>
                    <button type="button" data-bs-target="#socialMediaCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                            class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.social-media-carousel {
    padding-bottom: 50px; /* Space for indicators */
}

.social-post-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    height: auto; /* Allow content to dictate height */
}

.social-post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

.post-media-container {
    width: 100%;
    height: 350px; /* Fixed height for media focus */
    background-color: #f1f1f1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.post-media {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Fill container while preserving aspect ratio */
}

.post-media-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
    text-align: center;
}

.post-caption {
    font-size: 1rem;
    color: #333;
    margin-bottom: 0;
    white-space: pre-wrap; /* Preserves line breaks from textarea */
}

.post-link-btn {
    border-radius: 50px;
    font-weight: 600;
    padding: 8px 20px;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}

/* Carousel Control Styling */
.social-media-carousel .carousel-control-prev,
.social-media-carousel .carousel-control-next {
    width: 40px;
    height: 40px;
    background: rgba(13, 110, 253, 0.8); /* Primary color with transparency */
    border-radius: 50%;
    top: 30%; /* Position controls near the media part */
    transform: translateY(-50%);
    opacity: 0.9;
}

.social-media-carousel .carousel-control-prev:hover,
.social-media-carousel .carousel-control-next:hover {
    opacity: 1;
    background: var(--bs-primary);
}

.social-media-carousel .carousel-indicators {
    bottom: 0px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .post-media-container {
        height: 300px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('socialMediaCarousel');
    if (carousel) {
        const bootstrapCarousel = new bootstrap.Carousel(carousel, {
            interval: 5000,
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