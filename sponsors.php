<?php
session_start();
require_once 'config/config.php';

try {
    // Database connection
    $db = getDbConnection();
    
    // Fetch active sponsors
    $stmt = $db->prepare("SELECT id, name, designation, photo, sort_order 
                          FROM sponsors 
                          WHERE status = 'active' 
                          ORDER BY sort_order ASC, id DESC");
    $stmt->execute();
    $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sanitize sponsor data for safe display
    foreach ($sponsors as $key => $sponsor) {
        $sponsors[$key]['name'] = htmlspecialchars($sponsor['name']);
        $sponsors[$key]['designation'] = htmlspecialchars($sponsor['designation']);
        $sponsors[$key]['photo'] = htmlspecialchars($sponsor['photo'] ?? '');
    }
} catch (Exception $e) {
    error_log("Error in sponsors.php: " . $e->getMessage());
    $sponsors = [];
}

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Sponsors Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>Our Sponsors</span>
                </div>
            </div>
        </div>

        <!-- Sponsors Grid Section -->
        <div class="section-content sponsors-content">
            <?php if (!empty($sponsors)): ?>
                <div class="row g-4">
                    <?php foreach ($sponsors as $index => $sponsor): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="sponsor-card-full h-100">
                                <div class="sponsor-image-wrapper">
                                    <?php if (!empty($sponsor['photo'])): ?>
                                        <img src="<?php echo SITE_URL . '/img/sponsors/' . $sponsor['photo']; ?>" 
                                             alt="<?php echo $sponsor['name']; ?>" 
                                             class="sponsor-photo-full"
                                             loading="<?php echo $index < 8 ? 'eager' : 'lazy'; ?>"
                                             onerror="this.src='<?php echo SITE_URL; ?>/img/default-sponsor.jpg'">
                                    <?php else: ?>
                                        <img src="<?php echo SITE_URL; ?>/img/default-sponsor.jpg" 
                                             alt="<?php echo $sponsor['name']; ?>" 
                                             class="sponsor-photo-full"
                                             loading="<?php echo $index < 8 ? 'eager' : 'lazy'; ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="sponsor-details">
                                    <h5 class="sponsor-name-full"><?php echo $sponsor['name']; ?></h5>
                                    <p class="sponsor-designation-full"><?php echo $sponsor['designation']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="lead text-muted">No sponsors available at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Sponsors Content Styling - Matching Slider Design */
.sponsors-content {
    padding: 2rem 0;
}

.sponsor-card-full {
    background: #F3722C;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(243, 114, 44, 0.3);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.sponsor-card-full:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(243, 114, 44, 0.4);
}

.sponsor-image-wrapper {
    margin-bottom: 15px;
    width: 100%;
    display: flex;
    justify-content: center;
}

.sponsor-photo-full {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #ffffff;
}

.sponsor-details {
    width: 100%;
}

.sponsor-name-full {
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
    margin-bottom: 5px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.sponsor-designation-full {
    font-size: 14px;
    color: #ffffff;
    opacity: 0.95;
    margin: 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
}

/* Responsive Design */
@media (max-width: 767.98px) {
    .sponsor-photo-full {
        width: 100px;
        height: 100px;
    }
    
    .sponsor-name-full {
        font-size: 16px;
    }
    
    .sponsor-designation-full {
        font-size: 13px;
    }
}

@media (max-width: 575.98px) {
    .sponsor-photo-full {
        width: 90px;
        height: 90px;
    }
    
    .sponsor-name-full {
        font-size: 15px;
    }
    
    .sponsor-designation-full {
        font-size: 12px;
    }
}
</style>

<?php include 'footer.php'; ?>