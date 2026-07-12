<?php 
require_once 'config/config.php';
include 'header.php'; 
include 'navbar.php'; 

// Fetch site config and privacy policy sections
try {
    $db = getDbConnection();
    
    // Get site configuration
    $stmt = $db->prepare("SELECT * FROM site_config WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $site = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get active privacy policy sections
    $stmt = $db->prepare("SELECT * FROM privacy_policy_content WHERE status = 'active' ORDER BY sort_order ASC");
    $stmt->execute();
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Error fetching privacy policy content: " . $e->getMessage());
    $sections = [];
    $site = null;
}
?>

<main class="container my-5">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <div class="d-inline-block p-3 rounded-circle mb-3" style="background: var(--gradient-primary);">
            <i class="fas fa-user-shield fa-3x text-white"></i>
        </div>
        <h3 class="section-heading text-center mb-4">
            <span>Privacy Policy</span>
        </h3>
        <p class="lead text-muted">Your privacy and data security are our top priorities</p>
        <div class="mx-auto" style="width: 100px; height: 4px; background: var(--gradient-primary); border-radius: 2px;"></div>
    </div>

    <!-- Main Content Card -->
    <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
        <!-- Header -->
        <div class="card-header py-4 text-center text-white" style="background: var(--gradient-primary);">
            <h4 class="mb-1 fw-bold"><?php echo htmlspecialchars($site['site_title'] ?? 'Foundation Name'); ?></h4>
            <p class="mb-0 opacity-75">Committed to Privacy & Data Protection | <?php echo htmlspecialchars($site['email'] ?? 'contact@foundation.org'); ?></p>
        </div>

        <div class="card-body p-0">
            <?php if (count($sections) > 0): ?>
                <?php foreach ($sections as $index => $section): ?>
                    <!-- Section -->
                    <div class="p-4 p-md-5 border-bottom <?php echo $index % 2 !== 0 ? 'bg-light' : ''; ?>">
                        <div class="row align-items-start">
                            <div class="col-lg-2 col-md-3 text-center mb-4 mb-md-0">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; background: rgba(40, 167, 69, 0.1);">
                                    <i class="fas <?php echo htmlspecialchars($section['section_icon'] ?? 'fa-info-circle'); ?> fa-3x" style="color: var(--primary-color);"></i>
                                </div>
                            </div>
                            <div class="col-lg-10 col-md-9">
                                <h4 class="fw-bold mb-3" style="color: var(--primary-color);">
                                    <?php echo htmlspecialchars($section['section_title']); ?>
                                </h4>
                                <div style="line-height: 1.8; font-size: 1.1rem; color: var(--text-color);">
                                    <?php echo nl2br(htmlspecialchars($section['section_content'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-5 text-center">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Privacy policy content is being updated. Please check back soon.</p>
                </div>
            <?php endif; ?>

            <!-- Contact Section -->
            <div class="p-4 p-md-5 text-white" style="background: var(--gradient-primary);">
                <div class="text-center mb-4">
                    <i class="fas fa-envelope fa-3x mb-3 opacity-75"></i>
                    <h4 class="fw-bold mb-2">Contact Us</h4>
                    <p class="opacity-75">For questions, complaints, or requests regarding this Privacy Policy, please contact us below:</p>
                </div>
                
                <div class="row g-4 text-center">
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="fas fa-envelope fa-2x mb-3"></i>
                            <h6 class="fw-bold mb-2">Email</h6>
                            <a href="mailto:<?php echo htmlspecialchars($site['email'] ?? 'contact@foundation.org'); ?>" class="text-white text-decoration-none">
                                <?php echo htmlspecialchars($site['email'] ?? 'contact@foundation.org'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="fas fa-phone fa-2x mb-3"></i>
                            <h6 class="fw-bold mb-2">Phone</h6>
                            <a href="tel:<?php echo htmlspecialchars($site['phone1'] ?? '+911234567890'); ?>" class="text-white text-decoration-none">
                                <?php echo htmlspecialchars($site['phone1'] ?? '+91 1234567890'); ?>
                            </a>
                            <?php if (!empty($site['phone2'])): ?>
                                <br>
                                <a href="tel:<?php echo htmlspecialchars($site['phone2']); ?>" class="text-white text-decoration-none">
                                    <?php echo htmlspecialchars($site['phone2']); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
                            <h6 class="fw-bold mb-2">Address</h6>
                            <p class="mb-0 small"><?php echo htmlspecialchars($site['address'] ?? 'Address Here'); ?></p>
                        </div>
                    </div>
                </div>
                <p class="text-center mt-3 small opacity-75 mb-0">Last Updated: <?php echo date('F d, Y'); ?></p>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
