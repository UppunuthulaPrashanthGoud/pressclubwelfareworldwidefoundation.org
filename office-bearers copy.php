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
            <div class="col-xl-4 col-lg-6 col-md-6 bearer-item" 
                 data-name="<?php echo strtolower(htmlspecialchars($bearer['name'])); ?>" 
                 data-designation="<?php echo strtolower(htmlspecialchars($bearer['designation'])); ?>">
                <div class="office-bearer-card">
                    <div class="bearer-photo">
                        <img src="<?php echo $bearer['photo'] ? 'uploads/office-bearers/' . htmlspecialchars($bearer['photo']) : 'img/default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($bearer['name']); ?>">
                    </div>
                    <div class="bearer-info">
                        <h4 class="bearer-name"><?php echo htmlspecialchars($bearer['name']); ?></h4>
                        <p class="bearer-designation"><?php echo htmlspecialchars($bearer['designation']); ?></p>
                        
                        <div class="bearer-details">
                            <?php if ($bearer['email']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($bearer['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($bearer['email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($bearer['mobile']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-phone"></i>
                                    <a href="tel:<?php echo htmlspecialchars($bearer['mobile']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($bearer['mobile']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($bearer['address']): ?>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($bearer['address']); ?></span>
                                </div>
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

<style>
.office-bearer-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 30px 20px;
    text-align: center;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.office-bearer-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.bearer-photo {
    margin-bottom: 20px;
}

.bearer-photo img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.office-bearer-card:hover .bearer-photo img {
    border-color: rgba(255, 255, 255, 0.8);
    transform: scale(1.05);
}

.bearer-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.bearer-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 8px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.bearer-designation {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 20px;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.bearer-details {
    text-align: left;
}

.detail-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    font-size: 0.95rem;
    opacity: 0.9;
    transition: opacity 0.3s ease;
}

.detail-item:hover {
    opacity: 1;
}

.detail-item i {
    width: 20px;
    margin-right: 12px;
    color: rgba(255, 255, 255, 0.8);
    flex-shrink: 0;
}

.detail-item a {
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.detail-item a:hover {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: underline;
}

.detail-item span {
    line-height: 1.4;
    word-break: break-word;
}

@media (max-width: 768px) {
    .office-bearer-card {
        padding: 25px 15px;
    }
    
    .bearer-photo img {
        width: 100px;
        height: 100px;
    }
    
    .bearer-name {
        font-size: 1.3rem;
    }
    
    .bearer-designation {
        font-size: 1rem;
    }
}

/* Animation for cards appearing */
.bearer-item {
    animation: fadeInUp 0.6s ease forwards;
    opacity: 0;
    transform: translateY(30px);
}

.bearer-item:nth-child(1) { animation-delay: 0.1s; }
.bearer-item:nth-child(2) { animation-delay: 0.2s; }
.bearer-item:nth-child(3) { animation-delay: 0.3s; }
.bearer-item:nth-child(4) { animation-delay: 0.4s; }
.bearer-item:nth-child(5) { animation-delay: 0.5s; }
.bearer-item:nth-child(6) { animation-delay: 0.6s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

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