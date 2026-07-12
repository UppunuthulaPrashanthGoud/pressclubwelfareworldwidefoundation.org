<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get activity by ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $db->prepare("SELECT id, title, description, image, activity_date, created_at FROM recent_activities WHERE id = ? AND status = 'active'");
$stmt->execute([$id]);
$activity = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activity) {
    header("Location: index.php");
    exit;
}

// Sanitize activity data
$activity['title'] = htmlspecialchars($activity['title']);
$activity['description'] = htmlspecialchars($activity['description'] ?? 'No description available.');

// Fetch recent activities for sidebar (limit to 5, exclude current)
$stmt = $db->prepare("SELECT id, title, image, activity_date FROM recent_activities WHERE status = 'active' AND id != ? ORDER BY activity_date DESC LIMIT 5");
$stmt->execute([$id]);
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($recentActivities as &$act) {
    $act['title'] = htmlspecialchars($act['title']);
}

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Activity Details Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>Recent Activities</span>
                </div>
            </div>
        </div>

        <!-- Activity Details Content -->
        <div class="activity-details-content">
            <div class="row">
                <!-- Main Activity -->
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="card-custom mb-4">
                        <!-- Activity Image with Adaptive Container -->
                        <?php if (!empty($activity['image'])): ?>
                            <div class="activity-image-container adaptive">
                                <img src="<?php echo SITE_URL . '/img/activities/' . htmlspecialchars($activity['image']); ?>" 
                                     alt="<?php echo $activity['title']; ?>" 
                                     class="img-fluid activity-image rounded-top">
                            </div>
                        <?php else: ?>
                            <div class="activity-image-container adaptive placeholder-image bg-secondary text-white d-flex align-items-center justify-content-center rounded-top">
                                <i class="fas fa-image fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h1 class="card-title mb-3"><?php echo $activity['title']; ?></h1>
                            <p class="text-muted small mb-4">
                                <i class="fas fa-calendar-alt me-1"></i> 
                                <?php echo date('d M Y', strtotime($activity['activity_date'])); ?>
                            </p>
                            <div class="content-html"><?php echo nl2br($activity['description']); ?></div>
                            <div class="mt-4">
                                <a href="index.php#recent-activities" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-1"></i> View All Activities
                                </a>
                                <!-- Social Share Buttons -->
                                <div class="social-share mt-3">
                                    <span class="me-2 fw-bold">Share:</span>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/activity-details.php?id=' . $activity['id']); ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-primary me-2">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/activity-details.php?id=' . $activity['id']); ?>&text=<?php echo urlencode($activity['title']); ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-info me-2">
                                        <i class="fab fa-x-twitter"></i>
                                    </a>
                                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($activity['title'] . ' - ' . SITE_URL . '/activity-details.php?id=' . $activity['id']); ?>" 
                                       target="_blank"
                                       class="btn btn-sm btn-success">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar with Recent Activities -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-custom sidebar sticky-sidebar">
                        <h5 class="sidebar-title">Other Recent Activities</h5>
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $act): ?>
                                <div class="recent-activity-item mb-3">
                                    <div class="row g-2">
                                        <?php if (!empty($act['image'])): ?>
                                            <div class="col-4">
                                                <div class="sidebar-image-container">
                                                    <img src="<?php echo SITE_URL . '/img/activities/' . htmlspecialchars($act['image']); ?>" 
                                                         alt="<?php echo $act['title']; ?>" 
                                                         class="img-fluid sidebar-image rounded">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="<?php echo !empty($act['image']) ? 'col-8' : 'col-12'; ?>">
                                            <h6 class="activity-title mb-1">
                                                <a href="activity-details.php?id=<?php echo $act['id']; ?>">
                                                    <?php echo $act['title']; ?>
                                                </a>
                                            </h6>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-calendar-alt me-1"></i> 
                                                <?php echo date('d M Y', strtotime($act['activity_date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No other activities available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Activity Details Content */
.activity-details-content {
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
    transform: translateY(-5px);
    box-shadow: 0 8px 20px var(--shadow-dark);
}

/* Activity Image Container - Exact Same Adaptive Approach */
.activity-image-container {
    overflow: hidden;
    position: relative;
    width: 100%;
}

.activity-image-container.adaptive {
    height: auto;
    min-height: 350px;
    background-color: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px 10px 0 0;
}

.activity-image {
    width: 100%;
    height: auto;
    max-width: 100%;
    object-fit: contain !important;
    object-position: center;
    transition: transform 0.3s ease;
    border-radius: 10px 10px 0 0;
}

.placeholder-image {
    height: 350px;
    min-height: 350px;
    background-color: var(--light-bg);
    color: var(--text-muted);
}

.card-custom:hover .activity-image {
    transform: scale(1.02);
}

.card-custom .card-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.card-custom .content-html {
    line-height: 1.8;
    font-size: 1.1rem;
    color: var(--text-color);
}

.card-custom .content-html p {
    margin-bottom: 1rem;
}

/* Social Share Buttons */
.social-share {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.social-share .btn {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.social-share .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Sidebar Styles */
.sidebar {
    padding: 1.5rem;
}

.sticky-sidebar {
    position: sticky;
    top: 100px;
}

.sidebar-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.4rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--primary-light);
    padding-bottom: 0.5rem;
}

.recent-activity-item {
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--border-light);
}

.recent-activity-item:last-child {
    border-bottom: none;
}

/* Sidebar Image Container */
.sidebar-image-container {
    overflow: hidden;
    border-radius: 8px;
    height: 80px;
    background-color: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.sidebar-image-container:hover .sidebar-image {
    transform: scale(1.1);
}

.recent-activity-item .activity-title {
    font-family: 'Teko', sans-serif;
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
    line-height: 1.3;
}

.recent-activity-item .activity-title a {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.recent-activity-item .activity-title a:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .activity-details-content {
        padding: 1.5rem 0;
    }

    .activity-image-container.adaptive {
        min-height: 300px;
    }
    
    .placeholder-image {
        height: 300px;
        min-height: 300px;
    }

    .card-custom .card-title {
        font-size: 1.8rem;
    }
    
    .sticky-sidebar {
        position: relative;
        top: 0;
        margin-top: 2rem;
    }
}

@media (max-width: 767.98px) {
    .activity-details-content {
        padding: 1rem 0;
    }

    .activity-image-container.adaptive {
        min-height: 250px;
    }
    
    .placeholder-image {
        height: 250px;
        min-height: 250px;
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
    
    .sidebar-image-container {
        height: 70px;
    }
}

@media (max-width: 575.98px) {
    .section-heading span {
        font-size: 1.5rem;
    }
    
    .activity-image-container.adaptive {
        min-height: 200px;
    }
    
    .placeholder-image {
        height: 200px;
        min-height: 200px;
    }

    .card-custom .card-title {
        font-size: 1.3rem;
    }

    .card-custom .btn-primary {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }

    .sidebar-title {
        font-size: 1.2rem;
    }

    .recent-activity-item .activity-title {
        font-size: 1rem;
    }
    
    .sidebar-image-container {
        height: 60px;
    }
    
    .social-share .btn {
        width: 34px;
        height: 34px;
    }
}
</style>

<?php include 'footer.php'; ?>