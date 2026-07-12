<?php
/**
 * Media Sections Component
 * Displays videos, gallery, and news sections
 */
?>

<!-- NEWS SECTION -->
<?php if (!empty($news)): ?>
<div class="container-fluid my-5 bg-light py-5">
    <h3 class="section-heading text-center"><span>News</span></h3>
    <div class="container">
        <div class="row">
            <?php foreach ($news as $index => $news_item): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card-custom h-100">
                        <div class="mb-3">
                            <?php if ($news_item['image']): ?>
                                <img src="img/<?php echo htmlspecialchars($news_item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($news_item['title']); ?>" 
                                     class="img-fluid rounded shadow-sm"
                                     style="width: 100%; height: 200px; object-fit: cover;"
                                     onerror="this.src='img/default-news.jpg'">
                            <?php else: ?>
                                <img src="img/default-news.jpg" 
                                     alt="Default News" 
                                     class="img-fluid rounded shadow-sm"
                                     style="width: 100%; height: 200px; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                        <h6><a href="news-details.php?id=<?php echo $news_item['id']; ?>"><?php echo htmlspecialchars($news_item['title']); ?></a></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars(substr(strip_tags($news_item['content']), 0, 100)) . '...'; ?></p>
                        <a href="news-details.php?id=<?php echo $news_item['id']; ?>" class="btn btn-sm btn-primary">Read More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="news.php" class="btn btn-primary">View All News</a>
        </div>
    </div>
</div>
<?php endif; ?>