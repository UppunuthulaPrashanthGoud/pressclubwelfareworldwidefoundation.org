<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

$awards = [];
$error_message = '';

try {
    // Fetch all active honorary awards (similar to what the slider component does)
    $stmt = $db->prepare("SELECT award_no, recipient_name, award_name, category, photo_path FROM honorary_awards WHERE status = 'active' ORDER BY award_date DESC, created_at DESC");
    $stmt->execute();
    $awards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "A database error occurred while fetching the awards list.";
    logError("Database Error: " . $e->getMessage());
}

// Group awards into slides (3 per slide for desktop)
$awardSlides = array_chunk($awards, 3);
$pageTitle = "All Honorary Awardees";

include 'header.php';
include 'navbar.php';
?>

<style>
/* Base Styles for the Page and Slider */
.award-list-section {
    background-color: #f4f7f6;
    padding: 60px 0;
    min-height: 80vh; /* Ensure it takes up vertical space */
}

/* Slider Card Styles (Based on latest card design) */
.award-slider-card {
    background: #ffffff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
}

.award-slider-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.2);
}

/* Header Wave Styling */
.award-header-wave {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 150px; 
    background-color: var(--bs-primary);
    /* Clip path to mimic the subtle curve */
    clip-path: polygon(0 0, 100% 0, 100% 70%, 50% 100%, 0 70%);
    z-index: 1;
}

.award-content-wrap {
    position: relative;
    z-index: 2;
    padding: 1rem;
    /* Pushing content down to account for image overlap */
    padding-top: 50px !important;
}

/* Image Wrap for circular photo */
.award-image-wrap {
    width: 150px;
    height: 150px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    border: 5px solid white;
    box-shadow: 0 0 0 5px rgba(255, 255, 255, 0.5);
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 3;
}

.award-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.award-details {
    flex-grow: 1;
    text-align: center;
    /* Spacing to start details below the image wrap */
    margin-top: 100px; 
}

.award-details .recipient-name {
    font-weight: 700;
    color: var(--bs-dark);
    font-size: 1.5rem;
    margin-bottom: 5px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
}

.award-details .award-detail-label {
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d !important;
}

.award-details .award-name {
    font-style: italic;
    color: var(--bs-primary);
    font-size: 1.1rem;
    font-weight: 600;
}

.award-details .award-category {
    font-size: 0.9rem;
    font-weight: 600;
    padding: 5px 10px;
    display: inline-block;
    background-color: var(--bs-success);
    color: white;
    border-radius: 5px;
}
/* Slider Controls */
#awardsListCarousel .carousel-control-prev,
#awardsListCarousel .carousel-control-next {
    width: 40px;
    height: 40px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    opacity: 0.7;
    top: 40%;
}
</style>

<section class="award-list-section">
    <div class="container">
        <h3 class="section-heading text-center mb-5">
            <span>Our Honorary Awardees</span>
        </h3>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (empty($awards)): ?>
            <div class="alert alert-info text-center">No active honorary awards found.</div>
        <?php else: ?>
            <div id="awardsListCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                <div class="carousel-inner">
                    <?php foreach ($awardSlides as $slideIndex => $slideAwards): ?>
                    <div class="carousel-item <?php echo $slideIndex === 0 ? 'active' : ''; ?>">
                        <div class="row g-4 justify-content-center">
                            <?php foreach ($slideAwards as $award): ?>
                            <div class="col-lg-4 col-md-6 col-sm-10">
                                <div class="award-slider-card simple-card" data-aos="zoom-in" data-aos-delay="<?php echo $slideIndex * 100; ?>">
                                    
                                    <!-- Background Wave Header -->
                                    <div class="award-header-wave"></div>
                                    
                                    <div class="award-content-wrap">
                                        <div class="award-image-wrap">
                                            <!-- Correct image path: img/awards/ -->
                                            <img src="<?php echo SITE_URL; ?>/img/awards/<?php echo htmlspecialchars($award['photo_path'] ?? 'placeholder.png'); ?>" 
                                                 alt="<?php echo htmlspecialchars($award['recipient_name']); ?>" 
                                                 class="award-photo"
                                                 onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/img/placeholder.png';"
                                                 loading="lazy">
                                        </div>
                                        
                                        <div class="award-details text-center">
                                            
                                            <!-- Name -->
                                            <p class="award-detail-label text-muted mb-0 mt-3">Name:</p>
                                            <h5 class="recipient-name" title="<?php echo htmlspecialchars($award['recipient_name']); ?>">
                                                <?php echo htmlspecialchars($award['recipient_name']); ?>
                                            </h5>
                                            
                                            <!-- Category -->
                                            <p class="award-detail-label text-muted mb-0 mt-2">Category:</p>
                                            <p class="award-category badge bg-success text-uppercase">
                                                <?php echo htmlspecialchars($award['category']); ?>
                                            </p>
                                            
                                            <!-- Award Name -->
                                            <p class="award-detail-label text-muted mb-0 mt-2">Award:</p>
                                            <p class="award-name mb-3">
                                                <?php echo htmlspecialchars($award['award_name']); ?>
                                            </p>
                                            
                                            <!-- IMPORTANT: No "View Details" button or redirection link here -->
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Controls -->
                <?php if (count($awardSlides) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#awardsListCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#awardsListCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <div class="carousel-indicators">
                    <?php foreach ($awardSlides as $index => $slide): ?>
                    <button type="button" data-bs-target="#awardsListCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                            class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>