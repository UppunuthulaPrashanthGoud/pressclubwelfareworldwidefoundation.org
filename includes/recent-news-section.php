<?php
// Fetch recent news articles (limited to 6 for the home page)
$stmt = $db->prepare("
    SELECT id, title, content, excerpt, image, author, created_at
    FROM news
    WHERE status = 'active'
    ORDER BY created_at DESC, id DESC
    LIMIT 6
");
$stmt->execute();
$recentNewsArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare for display
foreach ($recentNewsArticles as &$article) {
    $article['title_safe']  = htmlspecialchars($article['title'] ?? '', ENT_QUOTES, 'UTF-8');
    $article['author_safe'] = htmlspecialchars($article['author'] ?: 'CGMA Team', ENT_QUOTES, 'UTF-8');

    if (!empty($article['excerpt'])) {
        $article['excerpt_safe'] = htmlspecialchars($article['excerpt'], ENT_QUOTES, 'UTF-8');
    } else {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($article['content'] ?? '')));
        $article['excerpt_safe'] = htmlspecialchars(mb_strimwidth($plain, 0, 150, '...', 'UTF-8'), ENT_QUOTES, 'UTF-8');
    }

    $article['image_src'] = news_image_src($article['image'] ?? null);
    $article['date_label'] = date('d M Y', strtotime($article['created_at']));
}
unset($article);
?>

<!-- Recent News Section -->
<div class="container-fluid my-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>Recent News</span>
                </div>
            </div>
        </div>

        <div class="section-content news-content">
            <?php if (!empty($recentNewsArticles)): ?>
                <div class="row g-4">
                    <?php foreach ($recentNewsArticles as $index => $article): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo (int)$index * 100; ?>">
                            <div class="card-custom h-100">
                                <div class="section-image-container">
                                    <img src="<?php echo $article['image_src']; ?>"
                                         alt="<?php echo $article['title_safe']; ?>"
                                         class="img-fluid rounded-top"
                                         loading="<?php echo $index < 2 ? 'eager' : 'lazy'; ?>">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $article['title_safe']; ?></h5>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-user me-1"></i> <?php echo $article['author_safe']; ?> |
                                        <i class="fas fa-calendar-alt me-1"></i> <?php echo $article['date_label']; ?>
                                    </p>
                                    <p class="card-text"><?php echo $article['excerpt_safe']; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="news-details.php?id=<?php echo (int)$article['id']; ?>"
                                           class="btn btn-primary">Read More</a>
                                        
                                        <!-- Quick Share Button -->
                                        <div class="quick-share">
                                            <button class="btn btn-outline-primary btn-sm" onclick="shareNews(<?php echo (int)$article['id']; ?>, '<?php echo addslashes($article['title_safe']); ?>')">
                                                <i class="fas fa-share-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="lead text-muted">No recent news available at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Recent News Section Styles */
.news-content { padding: 2rem 0; }

.card-custom {
    border: none;
    border-radius: 10px;
    background: var(--white-bg);
    box-shadow: 0 4px 12px var(--shadow-medium);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-custom:hover { transform: translateY(-8px); box-shadow: 0 8px 20px var(--shadow-dark); }

.section-image-container { overflow: hidden; height: 200px; }
.section-image-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
.card-custom:hover .section-image-container img { transform: scale(1.05); }

.card-custom .card-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
}
.card-custom .card-text { line-height: 1.6; font-size: 1rem; color: var(--text-color); }
.card-custom .card-text.text-muted { font-size: 0.9rem; }

.card-custom .btn-primary {
    background: var(--gradient-primary);
    border: none;
    font-family: 'Teko', sans-serif;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    color: var(--text-white);
}
.card-custom .btn-primary:hover { background: var(--gradient-primary-reverse); transform: translateY(-2px); }

/* Quick Share Styles */
.quick-share .btn-outline-primary {
    border-color: var(--primary-color);
    color: var(--primary-color);
}
.quick-share .btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: white;
}

/* Responsive */
@media (max-width: 991.98px) {
    .news-content { padding: 1.5rem 0; }
    .section-image-container { height: 180px; }
    .card-custom .card-title { font-size: 1.4rem; }
}
@media (max-width: 767.98px) {
    .news-content { padding: 1rem 0; }
    .section-image-container { height: 160px; }
    .card-custom .card-title { font-size: 1.3rem; }
    .card-custom .card-text { font-size: 0.95rem; }
    .card-custom .card-text.text-muted { font-size: 0.85rem; }
}
@media (max-width: 575.98px) {
    .section-heading span { font-size: 1.5rem; }
    .card-custom .card-title { font-size: 1.2rem; }
    .card-custom .btn-primary { font-size: 0.9rem; padding: 0.4rem 0.8rem; }
    .section-image-container { height: 140px; }
}

/* Image Loading States */
.section-image-container img {
    transition: opacity 0.3s ease-in-out, transform 0.3s ease;
}
.section-image-container img[loading="lazy"] {
    opacity: 0.7;
}
.section-image-container img.loaded {
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // OPTIMIZED: Image loading handler
    const newsImages = document.querySelectorAll('.section-image-container img');
    
    newsImages.forEach((img) => {
        // Handle eager loading images
        if (img.loading === 'eager' || img.getBoundingClientRect().top < window.innerHeight) {
            img.style.opacity = '1';
            img.classList.add('loaded');
        }
        
        // Image load event
        img.addEventListener('load', function() {
            this.style.opacity = '1';
            this.classList.add('loaded');
        });
        
        // Error handling for broken images
        img.addEventListener('error', function() {
            this.src = '<?php echo rtrim(SITE_URL, '/'); ?>/img/news-placeholder.jpg';
            this.style.opacity = '1';
        });
    });
    
    // OPTIMIZED: Lazy loading for news images
    const lazyImages = document.querySelectorAll('.section-image-container img[loading="lazy"]');
    
    if ('IntersectionObserver' in window && lazyImages.length > 0) {
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '1';
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '100px'
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.style.opacity = '1';
            img.classList.add('loaded');
        });
    }
});
</script>