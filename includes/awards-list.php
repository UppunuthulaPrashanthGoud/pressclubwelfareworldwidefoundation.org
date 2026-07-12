<?php
/**
 * awards-list.php
 * Fetches and displays the list of available awards from the database.
 */

// Ensure database connection exists
if (!isset($db)) {
    require_once 'config/config.php';
    $db = getDbConnection();
}

try {
    // Fetch active awards from the awards_list table
    $stmt = $db->prepare("SELECT award_name FROM awards_list WHERE status = 'active' ORDER BY award_name ASC");
    $stmt->execute();
    $awardsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback if table doesn't exist or error occurs
    $awardsList = [];
}
?>

<section class="awards-directory py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="section-title fw-bold">Available Awards</h2>
                <p class="text-muted">Explore our diverse range of prestigious recognition categories</p>
                <div class="title-line mx-auto"></div>
            </div>
        </div>

        <?php if (!empty($awardsList)): ?>
            <div class="row g-3">
                <?php foreach ($awardsList as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="award-item-card p-3 d-flex align-items-center h-100 shadow-sm bg-white border-start border-primary border-4 rounded">
                            <div class="award-icon me-3">
                                <i class="fas fa-medal text-primary fs-4"></i>
                            </div>
                            <div class="award-text">
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($item['award_name']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No awards found in the database.
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
    .awards-directory .section-title {
        color: #333;
        position: relative;
        padding-bottom: 15px;
    }
    .awards-directory .title-line {
        width: 80px;
        height: 3px;
        background: var(--bs-primary);
        border-radius: 2px;
    }
    .award-item-card {
        transition: all 0.3s ease;
        cursor: default;
    }
    .award-item-card:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        background-color: #f8f9ff !important;
    }
    .award-icon i {
        opacity: 0.8;
    }
</style>