<?php
/**
 * Videos Section Component
 * Displays YouTube videos in a responsive grid
 */
?>

<!-- OUR VIDEOS SECTION -->
<?php if (!empty($youtube_videos)): ?>
<div class="container-fluid my-5 bg-light py-5">
    <h3 class="section-heading text-center"><span>Our Videos</span></h3>
    <div class="container">
        <div class="row">
            <?php foreach ($youtube_videos as $index => $video): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card-custom">
                        <div class="video-thumbnail mb-3">
                            <iframe width="100%" height="200" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video['video_id']); ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                        <h6><?php echo htmlspecialchars($video['title']); ?></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars($video['description']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="our-videos.php" class="btn btn-danger">
                <i class="fab fa-youtube"></i> View All Videos
            </a>
        </div>
    </div>
</div>
<?php endif; ?>