<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Fetch approved volunteer members
$stmt = $db->prepare("
    SELECT name, designation, image, phone, email 
    FROM team_members 
    WHERE status = 'active' AND member_type = 'volunteer' 
    ORDER BY sort_order ASC, created_at DESC
");
$stmt->execute();
$volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Our Volunteers</span></h3>

    <!-- Search Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="volunteer-search" class="form-control" placeholder="Search by name or designation...">
            </div>
        </div>
    </div>

    <!-- Volunteers Grid -->
    <div id="volunteers-grid" class="row g-4 justify-content-center">
        <?php if (empty($volunteers)): ?>
            <div class="col-12 text-center text-muted mt-5">
                <h4><i class="fas fa-users"></i> No volunteers available at the moment.</h4>
            </div>
        <?php else: ?>
            <?php foreach ($volunteers as $volunteer): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 volunteer-item" 
                     data-name="<?php echo strtolower(htmlspecialchars($volunteer['name'])); ?>" 
                     data-designation="<?php echo strtolower(htmlspecialchars($volunteer['designation'])); ?>">
                    <div class="supporter-card">
                        <div class="supporter-img-container">
                            <img src="<?php echo $volunteer['image'] ? 'uploads/team/' . htmlspecialchars($volunteer['image']) : 'img/default-avatar.png'; ?>" 
                                 alt="<?php echo htmlspecialchars($volunteer['name']); ?>">
                        </div>
                        <div class="supporter-body">
                            <div class="supporter-name">
                                <h5><?php echo htmlspecialchars($volunteer['name']); ?></h5>
                            </div>
                            <div class="supporter-details text-center">
                                <p class="mb-1 fw-bold"><?php echo htmlspecialchars($volunteer['designation']); ?></p>
                                <?php if ($volunteer['email']): ?>
                                    <p class="mb-0 small text-white-50"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($volunteer['email']); ?></p>
                                <?php endif; ?>
                                <?php if ($volunteer['phone']): ?>
                                    <p class="mb-0 small text-white-50"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($volunteer['phone']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- No results message -->
        <div id="no-results" class="col-12 text-center text-muted mt-5 d-none">
            <h4><i class="fas fa-exclamation-circle"></i> No volunteers found matching your search.</h4>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('volunteer-search');
    const volunteerItems = document.querySelectorAll('.volunteer-item');
    const noResultsDiv = document.getElementById('no-results');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        volunteerItems.forEach(item => {
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