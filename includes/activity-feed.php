<?php
/**
 * Activity Feed Component
 * Displays latest activities with images and social sharing
 */
?>

<!-- Updated Activity Section with Better Image Handling and Description -->
<div class="card-custom" data-aos="fade-up">
    <h3 class="section-heading"><span>Latest Activities</span></h3>
    <div class="text-center p-3" style="max-height: 700px; overflow-y: auto;">
        <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-item mb-4">
                    <div class="activity-image-container">
                        <a href="activity-details.php?id=<?php echo $activity['id']; ?>">
                            <img src="img/activities/<?php echo htmlspecialchars($activity['image'] ?: 'default-activity.jpg'); ?>" 
                                 class="img-fluid" 
                                 alt="<?php echo htmlspecialchars($activity['title']); ?>"
                                 onload="this.classList.add('loaded')"
                                 onerror="this.src='img/activities/default-activity.jpg'">
                        </a>
                    </div>
                    <div class="activity-content">
                        <h6 class="mb-2">
                            <a href="activity-details.php?id=<?php echo $activity['id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($activity['title']); ?>
                            </a>
                        </h6>
                        <div class="content-html">
                            <p class="text-muted">
                                <?php echo $activity['description'] ?: 'No description available.'; ?>
                            </p>
                        </div>
                        <div class="social-share">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($site_url . 'activity-details.php?id=' . $activity['id']); ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-primary me-1" 
                               title="Share on Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($site_url . 'activity-details.php?id=' . $activity['id']); ?>&text=<?php echo urlencode($activity['title']); ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-info me-1" 
                               title="Share on Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($activity['title'] . ' - ' . $site_url . 'activity-details.php?id=' . $activity['id']); ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-success" 
                               title="Share on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-muted py-4">
                <i class="fas fa-info-circle mb-2"></i>
                <p>No recent activities available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.activity-item .activity-content .content-html {
    line-height: 1.6;
}
.activity-item .activity-content .content-html ul,
.activity-item .activity-content .content-html ol {
    margin-left: 2rem;
    margin-bottom: 1rem;
}
.activity-item .activity-content .content-html li {
    margin-bottom: 0.5rem;
}
</style>