<?php 
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get campaigns from database (fallback if table doesn't exist)
$campaigns = [];
try {
    $stmt = $db->prepare("SELECT * FROM campaigns WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute(['active']);
    $campaigns = $stmt->fetchAll();
} catch (PDOException $e) {
    logError('Crowdfunding query error: ' . $e->getMessage());
    // Fallback: empty array to prevent page crash
}

include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Crowdfunding Campaigns</span></h3>

    <?php if (empty($campaigns)): ?>
        <div class="text-center text-muted py-4">
            <i class="fas fa-info-circle mb-2"></i>
            <p>कोई अभियान उपलब्ध नहीं है।</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($campaigns as $campaign): ?>
                <div class="col-lg-6">
                    <div class="card event-card shadow-sm">
                        <!-- Image Section -->
                        <div class="event-image-container">
                            <img src="<?php echo $campaign['image'] ? 'img/campaigns/' . htmlspecialchars($campaign['image']) : 'img/default-campaign.jpg'; ?>" class="event-image" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                        </div>
                        <!-- Details Section -->
                        <div class="card-body event-details">
                            <h5 class="card-title event-title"><?php echo htmlspecialchars($campaign['title']); ?></h5>
                            <div class="event-meta mb-3">
                                <span><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($campaign['location']); ?></span>
                                <span class="ms-4"><i class="fas fa-calendar-alt me-2"></i><?php echo date('F j, Y', strtotime($campaign['campaign_date'])); ?></span>
                            </div>
                            <div class="event-description mb-3">
                                <?php echo nl2br(htmlspecialchars($campaign['description'])); ?>
                            </div>
                            <!-- Progress Bar -->
                            <?php 
                            $percentage = $campaign['target_amount'] > 0 ? ($campaign['raised_amount'] / $campaign['target_amount']) * 100 : 0;
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">Raised: ₹<?php echo number_format($campaign['raised_amount']); ?></span>
                                    <span class="small">Target: ₹<?php echo number_format($campaign['target_amount']); ?></span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min($percentage, 100); ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-center mt-1">
                                    <small><?php echo number_format($percentage, 1); ?>% Complete</small>
                                </div>
                            </div>
                            <a href="donation-form.php?campaign_id=<?php echo $campaign['id']; ?>" class="btn btn-primary event-btn w-100">
                                <i class="fas fa-heart me-2"></i>Donate Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>