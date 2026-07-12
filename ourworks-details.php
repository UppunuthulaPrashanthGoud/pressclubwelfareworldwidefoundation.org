<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get work by ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: ourworks.php");
    exit;
}

$stmt = $db->prepare("SELECT * FROM ourworks WHERE id = ?");
$stmt->execute([$id]);
$work = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$work) {
    header("Location: ourworks.php");
    exit;
}

// Sanitize work data
$work['name'] = htmlspecialchars($work['name']);

// Prepare page-specific meta data for header.php
$page_meta_title = $work['name'] . ' - Our Works';
$page_meta_description = strip_tags(substr($work['content'], 0, 150)) . '...';
$page_meta_image = !empty($work['image']) ? SITE_URL . '/img/ourworks/' . $work['image'] : '';
$page_url = SITE_URL . '/ourworks-details.php?id=' . $work['id'];

// Fetch other works for sidebar (limit to 5)
$stmt = $db->prepare("SELECT id, name, image, created_at FROM ourworks WHERE id != ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$id]);
$otherWorks = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($otherWorks as &$item) {
    $item['name'] = htmlspecialchars($item['name']);
}

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Work Details Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>Our Works</span>
                </div>
            </div>
        </div>

        <!-- Work Details Content -->
        <div class="news-details-content">
            <div class="row">
                <!-- Main Work Content -->
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="card-custom mb-4">
                        <?php if (!empty($work['image'])): ?>
                            <div class="news-image-container">
                                <img src="<?php echo SITE_URL . '/img/ourworks/' . htmlspecialchars($work['image']); ?>" 
                                     alt="<?php echo $work['name']; ?>" 
                                     class="img-fluid rounded-top news-main-image">
                            </div>
                        <?php else: ?>
                            <div class="news-image-container bg-secondary text-white d-flex align-items-center justify-content-center rounded-top" style="height: 300px;">
                                <i class="fas fa-briefcase fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h1 class="card-title mb-3"><?php echo $work['name']; ?></h1>
                            <?php if (!empty($work['created_at'])): ?>
                                <p class="text-muted small mb-4">
                                    <i class="fas fa-calendar-alt me-1"></i> 
                                    <?php echo date('d M Y', strtotime($work['created_at'])); ?>
                                </p>
                            <?php endif; ?>
                            <div class="content-html"><?php echo !empty($work['content']) ? $work['content'] : '<p>No content available.</p>'; ?></div>
                            <div class="mt-4">
                                <a href="ourworks.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-1"></i> View All Our Works
                                </a>
                                <!-- Social Share Buttons -->
                                <div class="social-share mt-3">
                                    <span class="me-2">Share:</span>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($page_url); ?>" 
                                       class="btn btn-sm btn-primary me-2" style="background: #3b5998;" target="_blank">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($page_url); ?>&text=<?php echo urlencode($work['name']); ?>" 
                                       class="btn btn-sm btn-primary me-2" style="background: #1da1f2;" target="_blank">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://wa.me/?text=<?php echo urlencode($work['name'] . ' ' . $page_url); ?>" 
                                       class="btn btn-sm btn-success" style="background: #25d366;" target="_blank">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar with Recent Works -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-custom sidebar">
                        <h5 class="sidebar-title">Recent Works</h5>
                        <?php if (!empty($otherWorks)): ?>
                            <?php foreach ($otherWorks as $item): ?>
                                <div class="recent-news-item mb-3">
                                    <div class="row">
                                        <?php if (!empty($item['image'])): ?>
                                            <div class="col-4">
                                                <div class="recent-news-image-container">
                                                    <img src="<?php echo SITE_URL . '/img/ourworks/' . htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo $item['name']; ?>" 
                                                         class="img-fluid rounded recent-news-image">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="<?php echo !empty($item['image']) ? 'col-8' : 'col-12'; ?>">
                                            <h6 class="news-title">
                                                <a href="ourworks-details.php?id=<?php echo $item['id']; ?>">
                                                    <?php echo $item['name']; ?>
                                                </a>
                                            </h6>
                                            <p class="text-muted small">
                                                <i class="fas fa-calendar-alt me-1"></i> 
                                                <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent works available.</p>
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
    max-height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.news-main-image {
    width: 100%;
    height: auto;
    max-height: 500px;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.card-custom:hover .news-main-image {
    transform: scale(1.02);
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
    object-fit: contain;
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
</style>

<?php include 'footer.php'; ?>