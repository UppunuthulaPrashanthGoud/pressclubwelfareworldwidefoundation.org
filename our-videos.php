<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Pagination settings
$videosPerPage = 6; // Number of videos per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $videosPerPage;

// Fetch total number of active videos for pagination
$countStmt = $db->prepare("SELECT COUNT(*) FROM youtube_videos WHERE status = 'active'");
$countStmt->execute();
$totalVideos = $countStmt->fetchColumn();
$totalPages = ceil($totalVideos / $videosPerPage);

// Fetch active videos for the current page
$stmt = $db->prepare("SELECT id, title, description, video_id, created_at FROM youtube_videos WHERE status = 'active' ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $videosPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sanitize content for safe display
foreach ($videos as &$video) {
    $video['title'] = htmlspecialchars($video['title']);
    $video['description'] = htmlspecialchars($video['description'] ? strip_tags($video['description']) : 'कोई विवरण उपलब्ध नहीं है।');
}

$pageTitle = 'Our videos';
include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Videos Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>Our videos</span>
                </div>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($videos)): ?>
                <?php foreach ($videos as $index => $video): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card-custom">
                            <div class="video-thumbnail mb-3">
                                <?php if (!empty($video['video_id'])): ?>
                                    <iframe width="100%" height="200" 
                                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video['video_id']); ?>" 
                                            frameborder="0" allowfullscreen></iframe>
                                <?php else: ?>
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 100%; height: 200px;">
                                        <i class="fas fa-video fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h6><?php echo $video['title']; ?></h6>
                            <p class="text-muted small"><?php echo $video['description']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <p class="lead text-muted">No videos available at this time.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($videos) && $totalPages > 1): ?>
            <div class="text-center mt-4">
                <nav aria-label="Video pagination">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <!-- Page Numbers -->
                        <?php
                        $range = 2; // Number of pages to show before and after current page
                        $start = max(1, $page - $range);
                        $end = min($totalPages, $page + $range);
                        
                        // Show first page and ellipsis if needed
                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                            if ($start > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        // Show page numbers in range
                        for ($i = $start; $i <= $end; $i++) {
                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                            echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        
                        // Show last page and ellipsis if needed
                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        <!-- Next Button -->
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Scoped Videos Section Styles */
.card-custom {
    padding: 0;
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

.video-thumbnail iframe {
    width: 100%;
    height: 200px;
    border-radius: 8px 8px 0 0;
}

.video-thumbnail {
    margin-bottom: 1rem;
}

.card-custom h6 {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
    padding: 0 1rem;
}

.text-muted {
    line-height: 1.6;
    font-size: 0.9rem;
    padding: 0 1rem;
}

.btn-danger {
    background: var(--danger-color);
    color: var(--text-white);
    font-family: 'Teko', sans-serif;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    border: none;
}

.btn-danger:hover {
    background: var(--danger-dark);
    transform: translateY(-2px);
}

/* Pagination Styles */
.pagination .page-link {
    color: var(--primary-color);
    border: none;
    margin: 0 5px;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-family: 'Teko', sans-serif;
    font-size: 1.1rem;
}

.pagination .page-item.active .page-link {
    background: var(--danger-color);
    color: var(--text-white);
}

.pagination .page-item.disabled .page-link {
    color: var(--text-muted);
    cursor: not-allowed;
}

.pagination .page-link:hover {
    background: var(--danger-light);
    color: var(--text-white);
}

/* Responsive Adjustments */
@media (max-width: 767.98px) {
    .card-custom {
        padding: 0;
    }

    .video-thumbnail iframe {
        height: 180px;
    }

    .card-custom h6 {
        font-size: 1.1rem;
    }

    .text-muted {
        font-size: 0.85rem;
    }

    .pagination .page-link {
        padding: 0.4rem 0.8rem;
        font-size: 1rem;
    }
}

@media (max-width: 575.98px) {
    .card-custom h6 {
        font-size: 1rem;
    }

    .text-muted {
        font-size: 0.8rem;
    }

    .pagination .page-link {
        padding: 0.3rem 0.6rem;
        font-size: 0.9rem;
    }
}
</style>

<?php include 'footer.php'; ?>