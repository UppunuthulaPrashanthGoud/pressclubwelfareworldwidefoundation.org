<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Fetch active office bearers
$stmt = $db->prepare("
    SELECT name, designation, photo, email, mobile, address 
    FROM office_bearers 
    WHERE status = 'active' 
    ORDER BY sort_order ASC, created_at DESC
");
$stmt->execute();
$office_bearers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Office Bearers</span></h3>

    <!-- Search Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="bearer-search" class="form-control" placeholder="Search by name or designation...">
            </div>
        </div>
    </div>

    <!-- Office Bearers Grid -->
    <div id="bearers-grid" class="row g-4 justify-content-center">
        <?php foreach ($office_bearers as $bearer): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 bearer-item" 
                 data-name="<?php echo strtolower(htmlspecialchars($bearer['name'])); ?>" 
                 data-designation="<?php echo strtolower(htmlspecialchars($bearer['designation'])); ?>">
                <div class="supporter-card">
                    <div class="supporter-img-container">
                        <img src="<?php echo $bearer['photo'] ? 'uploads/office-bearers/' . htmlspecialchars($bearer['photo']) : 'img/default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($bearer['name']); ?>">
                    </div>
                    <div class="supporter-body">
                        <div class="supporter-name">
                            <h5><?php echo htmlspecialchars($bearer['name']); ?></h5>
                        </div>
                        <div class="supporter-details text-center">
                            <p class="mb-1 fw-bold"><?php echo htmlspecialchars($bearer['designation']); ?></p>
                            
                            <?php if ($bearer['email']): ?>
                                <p class="mb-1 small text-white-50">
                                    <i class="fas fa-envelope me-1"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($bearer['email']); ?>" class="text-white-50 text-decoration-none">
                                        <?php echo htmlspecialchars($bearer['email']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($bearer['mobile']): ?>
                                <p class="mb-1 small text-white-50">
                                    <i class="fas fa-phone me-1"></i>
                                    <a href="tel:<?php echo htmlspecialchars($bearer['mobile']); ?>" class="text-white-50 text-decoration-none">
                                        <?php echo htmlspecialchars($bearer['mobile']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($bearer['address']): ?>
                                <p class="mb-0 small text-white-50">
                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($bearer['address']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- No results message -->
        <div id="no-results" class="col-12 text-center text-muted mt-5 d-none">
            <h4><i class="fas fa-exclamation-circle"></i> No office bearers found.</h4>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('bearer-search');
    const bearerItems = document.querySelectorAll('.bearer-item');
    const noResultsDiv = document.getElementById('no-results');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        bearerItems.forEach(item => {
            const name = item.getAttribute('data-name');
            const designation = item.getAttribute('data-designation');

            if (name.includes(searchTerm) || designation.includes(searchTerm)) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noResultsDiv.classList.remove('d-none');
        } else {
            noResultsDiv.classList.add('d-none');
        }
    });
});
</script>

<?php include 'footer.php'; ?>