<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get projects from database
$stmt = $db->prepare("SELECT * FROM projects WHERE status IN ('active', 'upcoming') ORDER BY created_at DESC");
$stmt->execute();
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sanitize project data
foreach ($projects as &$project) {
    $project['title'] = htmlspecialchars($project['title']);
    $project['description'] = htmlspecialchars($project['description']);
}

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Projects Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>हमारे प्रोजेक्ट्स</span>
                </div>
            </div>
        </div>

        <!-- Projects Section -->
        <div class="project-content">
            <?php if (!empty($projects)): ?>
                <div class="row g-4">
                    <?php foreach ($projects as $index => $project): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="card-custom h-100">
                                <?php if (!empty($project['image'])): ?>
                                    <div class="project-image-container">
                                        <img src="<?php echo SITE_URL . '/img/projects/' . htmlspecialchars($project['image']); ?>" 
                                             alt="<?php echo $project['title']; ?>" 
                                             class="img-fluid rounded-top">
                                    </div>
                                <?php else: ?>
                                    <div class="project-image-container bg-secondary text-white d-flex align-items-center justify-content-center rounded-top" style="height: 200px;">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $project['title']; ?></h5>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-calendar-alt me-1"></i> 
                                        <?php echo $project['start_date'] ? date('d M Y', strtotime($project['start_date'])) : 'तिथि उपलब्ध नहीं'; ?> 
                                        <?php echo $project['end_date'] ? ' - ' . date('d M Y', strtotime($project['end_date'])) : ''; ?> | 
                                        <i class="fas fa-flag me-1"></i> <?php echo $project['status'] === 'active' ? 'सक्रिय' : ($project['status'] === 'upcoming' ? 'आगामी' : 'पूर्ण'); ?>
                                    </p>
                                    <p class="card-text"><?php echo nl2br($project['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="lead text-muted">इस समय कोई प्रोजेक्ट उपलब्ध नहीं है।</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Project Content Section */
.project-content {
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

.project-image-container {
    overflow: hidden;
    height: 200px;
}

.project-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.card-custom:hover .project-image-container img {
    transform: scale(1.05);
}

.card-custom .card-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
}

.card-custom .card-text {
    color: var(--text-muted);
    font-size: 1rem;
    line-height: 1.6;
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .project-content {
        padding: 1.5rem 0;
    }

    .project-image-container {
        height: 180px;
    }
}

@media (max-width: 767.98px) {
    .project-content {
        padding: 1rem 0;
    }

    .project-image-container {
        height: 160px;
    }

    .card-custom .card-title {
        font-size: 1.2rem;
    }

    .card-custom .card-text {
        font-size: 0.95rem;
    }
}

@media (max-width: 575.98px) {
    .section-heading span {
        font-size: 1.5rem;
    }

    .card-custom .card-title {
        font-size: 1.1rem;
    }
}
</style>

<?php include 'footer.php'; ?>