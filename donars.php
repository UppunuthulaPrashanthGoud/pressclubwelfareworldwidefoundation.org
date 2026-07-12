<?php 
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get donors from database
$stmt = $db->prepare("SELECT * FROM donations WHERE status = ? ORDER BY created_at DESC");
$stmt->execute(['completed']);
$donors = $stmt->fetchAll();

include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Our Valued Donors</span></h3>

    <!-- Search Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="supporter-search" class="form-control" placeholder="Search by name...">
            </div>
        </div>
    </div>

    <!-- Donors Grid -->
    <div id="supporters-grid" class="row g-4 justify-content-center">
        <?php foreach ($donors as $donor): ?>
            <div class="col-xl-3 col-lg-4 col-md-6 supporter-item" data-name="<?php echo strtolower(htmlspecialchars($donor['name'])); ?>">
                <div class="supporter-card">
                    <div class="supporter-img-container">
                        <img src="<?php echo $donor['photo'] ? 'img/users/' . htmlspecialchars($donor['photo']) : 'img/default-avatar.png'; ?>" alt="<?php echo htmlspecialchars($donor['name']); ?>">
                    </div>
                    <div class="supporter-body">
                        <div class="supporter-name">
                            <h5><?php echo htmlspecialchars($donor['name']); ?></h5>
                        </div>
                        <div class="supporter-details">
                            <div class="detail-row">
                                <span>Amount</span>
                                <span>₹ <?php echo htmlspecialchars(number_format($donor['amount'], 2)); ?></span>
                            </div>
                            <div class="detail-row">
                                <span>Mobile</span>
                                <span><?php echo htmlspecialchars($donor['mobile']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- No results message -->
        <div id="no-results" class="col-12 text-center text-muted mt-5 d-none">
            <h4><i class="fas fa-exclamation-circle"></i> No donors found.</h4>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('supporter-search');
    const supporterItems = document.querySelectorAll('.supporter-item');
    const noResultsDiv = document.getElementById('no-results');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        supporterItems.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(searchTerm)) {
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