<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Fetch active news articles
$stmt = $db->prepare("SELECT id, title, content, excerpt, image, author, created_at 
                      FROM news 
                      WHERE status = 'active' 
                      ORDER BY created_at DESC");
$stmt->execute();
$newsArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sanitize content for safe display (no foreach by reference)
foreach ($newsArticles as $key => $article) {
    // Title
    $newsArticles[$key]['title'] = htmlspecialchars($article['title']);

    // Excerpt: use provided excerpt if available, otherwise build from content
    if (!empty($article['excerpt'])) {
        $newsArticles[$key]['excerpt'] = htmlspecialchars($article['excerpt']);
    } else {
        $cleanContent = strip_tags($article['content'] ?? '');
        // safe multibyte substring
        $newsArticles[$key]['excerpt'] = htmlspecialchars(mb_substr($cleanContent, 0, 150)) . '...';
    }

    // Author default in Hindi
    $newsArticles[$key]['author'] = htmlspecialchars($article['author'] ?? 'टीम किसानएक्स');

    // Normalize image name (no change to design — just ensure it's safe to echo)
    $newsArticles[$key]['image'] = htmlspecialchars($article['image'] ?? '');
}

// Optional: remove exact-duplicate titles if your DB has duplicate rows
$seenTitles = [];
$uniqueNews = [];
foreach ($newsArticles as $article) {
    $titleKey = trim(mb_strtolower($article['title']));
    if (isset($seenTitles[$titleKey])) {
        // skip duplicate title
        continue;
    }
    $seenTitles[$titleKey] = true;
    $uniqueNews[] = $article;
}

// Use $uniqueNews for rendering to avoid showing the same titled item twice
$newsArticles = $uniqueNews;

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width News Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>News & Updates</span>
                </div>
            </div>
        </div>

        <!-- News Articles Section -->
        <div class="section-content news-content">
            <?php if (!empty($newsArticles)): ?>
                <div class="row g-4">
                    <?php foreach ($newsArticles as $index => $article): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="card-custom h-100">
                                <?php if (!empty($article['image'])): ?>
                                    <div class="section-image-container">
                                        <img src="<?php echo SITE_URL . '/img/' . $article['image']; ?>" 
                                             alt="<?php echo $article['title']; ?>" 
                                             class="img-fluid rounded-top">
                                    </div>
                                <?php else: ?>
                                    <div class="section-image-container bg-secondary text-white d-flex align-items-center justify-content-center rounded-top" style="height: 200px;">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $article['title']; ?></h5>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-user me-1"></i> <?php echo $article['author']; ?> | 
                                        <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($article['created_at'])); ?>
                                    </p>
                                    <p class="card-text"><?php echo $article['excerpt']; ?></p>
                                    <a href="news-details.php?id=<?php echo $article['id']; ?>" 
                                       class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="lead text-muted">No news available at this time.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- CTA Section (if needed, scoped to avoid footer conflicts) -->
        <?php if (file_exists('cta-section.php')): ?>
            <div class="cta-section">
                <?php include 'cta-section.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Scoped CTA Section Styles */
.cta-section .card-custom {
    padding: 2rem;
}

.cta-section .card-custom h5 {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.cta-section .card-custom p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.cta-section .btn {
    font-family: 'Teko', sans-serif;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    border: none;
}

.cta-section .btn-primary {
    background: var(--gradient-primary);
    color: var(--text-white);
}

.cta-section .btn-primary:hover {
    background: var(--gradient-primary-reverse);
}

.cta-section .btn-success {
    background: var(--success-color);
    color: var(--text-white);
}

.cta-section .btn-success:hover {
    background: var(--success-dark);
    transform: translateY(-2px);
}

/* Scoped Responsive Adjustments for CTA */
@media (max-width: 991.98px) {
    .cta-section {
        padding: 3rem 0;
    }
}

@media (max-width: 767.98px) {
    .cta-section {
        padding: 2rem 0;
    }

    .cta-section .card-custom {
        padding: 1.5rem;
    }
}

@media (max-width: 575.98px) {
    .cta-section .card-custom h5 {
        font-size: 1.1rem;
    }

    .cta-section .btn {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
}
</style>

<?php include 'footer.php'; ?>
