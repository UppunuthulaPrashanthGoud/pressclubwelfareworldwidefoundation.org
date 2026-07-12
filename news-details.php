<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get news article by ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: news.php");
    exit;
}

$stmt = $db->prepare("SELECT id, title, content, excerpt, image, author, created_at FROM news WHERE id = ? AND status = 'active'");
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header("Location: news.php");
    exit;
}

// Sanitize article data
$article['title'] = htmlspecialchars($article['title']);
$article['author'] = htmlspecialchars($article['author'] ?? 'NDF Team');
$article['excerpt'] = htmlspecialchars($article['excerpt'] ?? strip_tags(substr($article['content'], 0, 150)) . '...');

// Prepare page-specific meta data for header.php
$page_meta_title = $article['title'] . ' - News';
$page_meta_description = $article['excerpt'];
$page_meta_image = !empty($article['image']) ? SITE_URL . '/img/' . $article['image'] : '';
$page_url = SITE_URL . '/news-details.php?id=' . $article['id'];

// Fetch recent news for sidebar (limit to 5)
$stmt = $db->prepare("SELECT id, title, image, created_at FROM news WHERE status = 'active' AND id != ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$id]);
$recentNews = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($recentNews as &$news) {
    $news['title'] = htmlspecialchars($news['title']);
}

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width News Details Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>News & Updates</span>
                </div>
            </div>
        </div>

        <!-- News Details Content -->
        <div class="news-details-content">
            <div class="row">
                <!-- Main Article -->
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="card-custom mb-4">
                        <?php if (!empty($article['image'])): ?>
                            <div class="news-image-container">
                                <img src="img/<?php echo htmlspecialchars($article['image']); ?>" 
                                     alt="<?php echo $article['title']; ?>" 
                                     class="img-fluid rounded-top news-main-image">
                            </div>
                        <?php else: ?>
                            <div class="news-image-container bg-secondary text-white d-flex align-items-center justify-content-center rounded-top" style="height: 300px;">
                                <i class="fas fa-image fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h1 class="card-title mb-3"><?php echo $article['title']; ?></h1>
                            <p class="text-muted small mb-4">
                                <i class="fas fa-user me-1"></i> <?php echo $article['author']; ?> | 
                                <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($article['created_at'])); ?>
                            </p>
                            <div class="content-html"><?php echo $article['content']; ?></div>
                            <div class="mt-4">
                                <a href="news.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-1"></i> View All News
                                </a>
                                <!-- Social Share Buttons -->
                                <div class="social-share mt-3">
                                    <span class="me-2">Share:</span>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($page_url); ?>" 
                                       class="btn btn-sm btn-primary me-2" style="background: var(--facebook-color);" target="_blank">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($page_url); ?>&text=<?php echo urlencode($article['title']); ?>" 
                                       class="btn btn-sm btn-primary me-2" style="background: var(--twitter-color);" target="_blank">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://wa.me/?text=<?php echo urlencode($article['title'] . ' ' . $page_url); ?>" 
                                       class="btn btn-sm btn-success" style="background: var(--success-color);" target="_blank">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar with Recent News -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-custom sidebar">
                        <h5 class="sidebar-title">Recent News</h5>
                        <?php if (!empty($recentNews)): ?>
                            <?php foreach ($recentNews as $news): ?>
                                <div class="recent-news-item mb-3">
                                    <div class="row">
                                        <?php if (!empty($news['image'])): ?>
                                            <div class="col-4">
                                                <div class="recent-news-image-container">
                                                    <img src="img/<?php echo htmlspecialchars($news['image']); ?>" 
                                                         alt="<?php echo $news['title']; ?>" 
                                                         class="img-fluid rounded recent-news-image">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="<?php echo !empty($news['image']) ? 'col-8' : 'col-12'; ?>">
                                            <h6 class="news-title">
                                                <a href="news-details.php?id=<?php echo $news['id']; ?>">
                                                    <?php echo $news['title']; ?>
                                                </a>
                                            </h6>
                                            <p class="text-muted small">
                                                <i class="fas fa-calendar-alt me-1"></i> 
                                                <?php echo date('d M Y', strtotime($news['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent news available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* News Details Content */
.news-details-content {
    padding: 2rem 0;
}

.card-custom {
    border: none;
    border-radius: 10px;
    background: var(--white-bg);
    box-shadow: 0 4px 12px var(--shadow-medium);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-custom:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px var(--shadow-dark);
}

/* Main News Image - No cropping, adaptive height */
.news-image-container {
    overflow: hidden;
    /* Removed fixed height to allow adaptive sizing */
    max-height: 500px; /* Optional: set a maximum height if needed */
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa; /* Light background for images with transparency */
}

.news-main-image {
    width: 100%;
    height: auto; /* Maintain aspect ratio */
    max-height: 500px; /* Optional: limit maximum height */
    object-fit: contain; /* Show full image without cropping */
    transition: transform 0.3s ease;
}

.card-custom:hover .news-main-image {
    transform: scale(1.02); /* Reduced scale for better effect with contain */
}

.card-custom .card-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
}

.card-custom .content-html {
    line-height: 1.8;
    font-size: 1.1rem;
    color: var(--text-color);
}

.card-custom .content-html p {
    margin-bottom: 1rem;
}

.card-custom .btn-primary {
    background: var(--gradient-primary);
    border: none;
    font-family: 'Teko', sans-serif;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    color: var(--text-white);
}

.card-custom .btn-primary:hover {
    background: var(--gradient-primary-reverse);
    transform: translateY(-2px);
}

/* Social Share Buttons */
.social-share .btn {
    padding: 0.5rem;
    font-size: 1rem;
    border-radius: 5px;
}

.social-share .btn:hover {
    transform: translateY(-2px);
}

.social-share .btn-primary {
    background: var(--primary-color);
}

.social-share .btn-primary:hover {
    background: var(--primary-dark);
}

/* Sidebar Styles */
.sidebar {
    padding: 1.5rem;
}

.sidebar-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-light);
    padding-bottom: 0.5rem;
}

/* Recent News Images - Also no cropping but with size constraints */
.recent-news-image-container {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 0.375rem;
    background: #f8f9fa;
}

.recent-news-image {
    max-width: 100%;
    max-height: 80px;
    width: auto;
    height: auto;
    object-fit: contain; /* Show full image without cropping */
}

.recent-news-item .news-title {
    font-family: 'Teko', sans-serif;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.recent-news-item .news-title a {
    color: var(--primary-color);
    text-decoration: none;
}

.recent-news-item .news-title a:hover {
    color: var(--gold-color);
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .news-details-content {
        padding: 1.5rem 0;
    }

    .news-image-container {
        max-height: 400px;
    }

    .news-main-image {
        max-height: 400px;
    }

    .card-custom .card-title {
        font-size: 1.8rem;
    }
}

@media (max-width: 767.98px) {
    .news-details-content {
        padding: 1rem 0;
    }

    .news-image-container {
        max-height: 300px;
    }

    .news-main-image {
        max-height: 300px;
    }

    .card-custom .card-title {
        font-size: 1.5rem;
    }

    .card-custom .content-html {
        font-size: 1rem;
    }

    .sidebar {
        margin-top: 2rem;
    }

    .recent-news-image-container {
        height: 60px;
    }

    .recent-news-image {
        max-height: 60px;
    }
}

@media (max-width: 575.98px) {
    .section-heading span {
        font-size: 1.5rem;
    }

    .news-image-container {
        max-height: 250px;
    }

    .news-main-image {
        max-height: 250px;
    }

    .card-custom .card-title {
        font-size: 1.3rem;
    }

    .card-custom .btn-primary {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
    }

    .sidebar-title {
        font-size: 1.3rem;
    }

    .recent-news-item .news-title {
        font-size: 1rem;
    }

    .recent-news-image-container {
        height: 50px;
    }

    .recent-news-image {
        max-height: 50px;
    }
}

/* Additional styles for very wide images */
@media (max-width: 575.98px) {
    .news-main-image {
        max-width: 100%;
    }
}

/* For images that are very tall */
.news-image-container.tall-image {
    max-height: 600px;
}

/* For images that are very wide */
.news-image-container.wide-image {
    max-height: 300px;
}
</style>

<?php include 'footer.php'; ?>