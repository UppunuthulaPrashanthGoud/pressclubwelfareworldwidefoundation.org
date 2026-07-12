<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get all works
$stmt = $db->prepare("SELECT * FROM ourworks ORDER BY created_at DESC");
$stmt->execute();
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Our Works Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>Our Works</span>
                </div>
            </div>
        </div>

        <!-- Works Section -->
        <div class="section-content works-content">
            <?php if (!empty($works)): ?>
                <div class="row g-4">
                    <?php foreach ($works as $index => $work): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="card-custom h-100">
                                <?php if (!empty($work['image'])): ?>
                                    <div class="section-image-container">
                                        <img src="<?php echo SITE_URL . '/img/ourworks/' . htmlspecialchars($work['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($work['name']); ?>" 
                                             class="img-fluid rounded-top">
                                    </div>
                                <?php else: ?>
                                    <div class="section-image-container bg-secondary text-white d-flex align-items-center justify-content-center rounded-top" style="height: 200px;">
                                        <i class="fas fa-briefcase fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($work['name']); ?></h5>
                                    <?php if (!empty($work['created_at'])): ?>
                                        <p class="card-text text-muted small">
                                            <i class="fas fa-calendar-alt me-1"></i> 
                                            <?php echo date('d M Y', strtotime($work['created_at'])); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="card-text">
                                        <?php 
                                        $content = strip_tags($work['content'] ?? '');
                                        $preview = mb_substr($content, 0, 150);
                                        echo htmlspecialchars($preview) . (mb_strlen($content) > 150 ? '...' : '');
                                        ?>
                                    </p>
                                    <a href="ourworks-details.php?id=<?php echo $work['id']; ?>" 
                                       class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="lead text-muted">No works available at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>