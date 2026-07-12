<?php
// Ensure required files are included, assuming this is loaded from index.php or similar
if (!isset($db)) {
    require_once 'config/config.php';
    $db = getDbConnection();
}

try {
    // Fetch recent active honorary awards
    $stmt = $db->prepare("SELECT award_no, recipient_name, award_name, category, photo_path FROM honorary_awards WHERE status = 'active' ORDER BY award_date DESC, created_at DESC LIMIT 6");
    $stmt->execute();
    $awards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Database error in honorary-awards-slider.php: " . $e->getMessage());
    $awards = [];
}

// Group awards into slides (3 per slide for desktop)
$awardSlides = array_chunk($awards, 3);
?>

<?php if (count($awards) > 0): ?>
<!-- Honorary Awards Slider Section -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="section-heading mb-5 text-center">
            <span>Our Prestigious Awardees</span>
        </div>

        <div id="honoraryAwardsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
                <?php foreach ($awardSlides as $slideIndex => $slideAwards): ?>
                <div class="carousel-item <?php echo $slideIndex === 0 ? 'active' : ''; ?>">
                    <div class="row g-4 justify-content-center">
                        <?php foreach ($slideAwards as $award): ?>
                        <div class="col-lg-4 col-md-6 col-sm-10">
                            <!-- Premium Award Card with Navy Blue Theme -->
                            <div class="award-slider-card-v2" data-aos="fade-up" data-aos-delay="<?php echo $slideIndex * 150; ?>">
                                
                                <!-- Decorative Stars Background -->
                                <div class="stars-overlay"></div>
                                
                                <!-- Top Section with Golden Congratulations -->
                                <div class="award-card-header-v2">
                                    <div class="congrats-header">
                                        <span class="congrats-script">Congratulations</span>
                                    </div>
                                    
                                    <!-- Award Title -->
                                    <div class="award-main-title">
                                        HONORARY DOCTORATE AWARD
                                    </div>
                                </div>
                                
                                <div class="award-content-body">
                                    <!-- Golden Laurel Frame with Profile Image -->
                                    <div class="laurel-frame-container">
                                        <div class="laurel-left"></div>
                                        <div class="laurel-right"></div>
                                        <div class="profile-circle">
                                            <img src="<?php echo SITE_URL; ?>/img/awards/<?php echo htmlspecialchars($award['photo_path'] ?? 'placeholder.png'); ?>" 
                                                 alt="<?php echo htmlspecialchars($award['recipient_name']); ?>" 
                                                 class="award-profile-v2"
                                                 onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/img/placeholder.png';"
                                                 loading="lazy">
                                        </div>
                                    </div>
                                    
                                    <!-- Golden Divider -->
                                    <div class="golden-divider-ornate"></div>
                                    
                                    <!-- Recipient Name -->
                                    <h4 class="recipient-name-v2">
                                        <?php echo htmlspecialchars($award['recipient_name']); ?>
                                    </h4>
                                    
                                    <!-- Award Name -->
                                    <div class="award-title-display">
                                        <?php echo htmlspecialchars($award['award_name']); ?>
                                    </div>
                                    
                                    <!-- Category Info -->
                                    <div class="category-info-v2">
                                        <i class="fas fa-award me-1"></i> <?php echo htmlspecialchars($award['category']); ?>
                                    </div>
                                    
                                    <!-- View Details Button -->
                                    <a href="<?php echo SITE_URL; ?>/honorary-doctorate.php?award_no=<?php echo htmlspecialchars($award['award_no']); ?>" 
                                       class="btn-view-recognition">
                                        View Recognition <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Enhanced Navigation Controls -->
            <?php if (count($awardSlides) > 1): ?>
            <button class="carousel-control-prev awards-nav-v2" type="button" data-bs-target="#honoraryAwardsCarousel" data-bs-slide="prev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-control-next awards-nav-v2" type="button" data-bs-target="#honoraryAwardsCarousel" data-bs-slide="next">
                <i class="fas fa-chevron-right"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Import Great Vibes Font for Congratulations Script */
@import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@600;700&display=swap');

/* Base Card with Navy Blue Theme - Using EXACT CSS Variables from style.css */
.award-slider-card-v2 {
    background: linear-gradient(145deg, #001529 0%, #001f3f 100%);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    transition: all 0.4s ease;
    height: 100%;
    position: relative;
    border: 2px solid rgba(212, 175, 55, 0.3);
}

.award-slider-card-v2:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 50px rgba(212, 175, 55, 0.4);
    border-color: #d4af37;
}

/* Animated Stars Background */
.stars-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(2px 2px at 20px 30px, white, transparent),
        radial-gradient(2px 2px at 60px 70px, white, transparent),
        radial-gradient(1px 1px at 50px 50px, white, transparent),
        radial-gradient(1px 1px at 130px 80px, white, transparent),
        radial-gradient(2px 2px at 90px 10px, white, transparent);
    background-repeat: repeat;
    background-size: 200px 200px;
    opacity: 0.3;
    animation: twinkle 3s ease-in-out infinite;
    pointer-events: none;
}

@keyframes twinkle {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.6; }
}

/* Header Section */
.award-card-header-v2 {
    background: linear-gradient(180deg, #001529 0%, #001f3f 100%);
    padding: 30px 20px 25px;
    text-align: center;
    position: relative;
    border-bottom: 3px solid #d4af37;
}

.congrats-header {
    margin-bottom: 15px;
}

.congrats-script {
    font-family: 'Great Vibes', cursive;
    font-size: 3rem;
    color: #d4af37;
    text-shadow: 
        0 0 10px rgba(212, 175, 55, 0.8),
        0 0 20px rgba(212, 175, 55, 0.6),
        2px 2px 4px rgba(0, 0, 0, 0.5);
    display: inline-block;
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { 
        text-shadow: 0 0 10px rgba(212, 175, 55, 0.8), 
                     0 0 20px rgba(212, 175, 55, 0.6), 
                     2px 2px 4px rgba(0, 0, 0, 0.5); 
    }
    to { 
        text-shadow: 0 0 20px rgba(212, 175, 55, 1), 
                     0 0 30px rgba(212, 175, 55, 0.8), 
                     2px 2px 4px rgba(0, 0, 0, 0.5); 
    }
}

.award-main-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 2px;
    margin-bottom: 8px;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
    text-transform: uppercase;
}

.subtitle-ceremony {
    font-family: 'Playfair Display', serif;
    font-size: 0.95rem;
    color: #f4d03f;
    font-style: italic;
    letter-spacing: 1px;
}

/* Content Body */
.award-content-body {
    padding: 40px 25px 30px;
    position: relative;
    text-align: center;
}

/* Laurel Frame with Profile */
.laurel-frame-container {
    position: relative;
    width: 160px;
    height: 160px;
    margin: 0 auto 25px;
}

.laurel-left, .laurel-right {
    position: absolute;
    width: 80px;
    height: 160px;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 200"><path d="M20,20 Q30,40 25,60 Q35,80 30,100 Q40,120 35,140 Q45,160 40,180" stroke="%23d4af37" stroke-width="3" fill="none"/><ellipse cx="25" cy="30" rx="8" ry="12" fill="%23d4af37"/><ellipse cx="30" cy="60" rx="8" ry="12" fill="%23d4af37"/><ellipse cx="35" cy="90" rx="8" ry="12" fill="%23d4af37"/><ellipse cx="40" cy="120" rx="8" ry="12" fill="%23d4af37"/><ellipse cx="45" cy="150" rx="8" ry="12" fill="%23d4af37"/></svg>');
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0.9;
}

.laurel-left {
    left: -15px;
    transform: scaleX(-1);
}

.laurel-right {
    right: -15px;
}

.profile-circle {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 130px;
    height: 130px;
    border-radius: 50%;
    background: linear-gradient(145deg, #d4af37, #b8860b);
    padding: 5px;
    box-shadow: 
        0 0 20px rgba(212, 175, 55, 0.6),
        0 8px 25px rgba(0, 0, 0, 0.3);
}

.award-profile-v2 {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #001529;
}

/* Golden Ornate Divider */
.golden-divider-ornate {
    width: 120px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
    margin: 0 auto 20px;
    position: relative;
}

.golden-divider-ornate::before,
.golden-divider-ornate::after {
    content: '◆';
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: #d4af37;
    font-size: 12px;
}

.golden-divider-ornate::before {
    left: -15px;
}

.golden-divider-ornate::after {
    right: -15px;
}

/* Recipient Name */
.recipient-name-v2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: #d4af37;
    letter-spacing: 1px;
    margin-bottom: 12px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
}

/* Award Title Display */
.award-title-display {
    font-size: 1.05rem;
    font-style: italic;
    color: #f4d03f;
    margin-bottom: 15px;
    line-height: 1.5;
    font-weight: 500;
}

/* Category Info */
.category-info-v2 {
    font-size: 0.85rem;
    color: #fff;
    letter-spacing: 1px;
    margin-bottom: 25px;
    font-weight: 600;
    padding: 8px 16px;
    background: rgba(212, 175, 55, 0.2);
    border-radius: 20px;
    display: inline-block;
    border: 1px solid rgba(212, 175, 55, 0.4);
}

/* View Recognition Button */
.btn-view-recognition {
    display: inline-block;
    padding: 12px 30px;
    background: linear-gradient(135deg, #d4af37 0%, #b8860b 100%);
    color: #001f3f;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 30px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
    border: 2px solid #d4af37;
}

.btn-view-recognition:hover {
    background: linear-gradient(135deg, #b8860b 0%, #d4af37 100%);
    color: #001f3f;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.6);
}

/* Carousel Navigation */
.awards-nav-v2 {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #d4af37, #b8860b);
    color: #001f3f;
    border-radius: 50%;
    opacity: 1;
    top: 50%;
    transform: translateY(-50%);
    box-shadow: 0 5px 15px rgba(212, 175, 55, 0.5);
    border: 2px solid #d4af37;
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

.awards-nav-v2:hover {
    background: linear-gradient(135deg, #b8860b, #d4af37);
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 8px 20px rgba(212, 175, 55, 0.7);
}

.carousel-control-prev.awards-nav-v2 { left: -25px; }
.carousel-control-next.awards-nav-v2 { right: -25px; }

/* Responsive Design */
@media (max-width: 992px) {
    .awards-nav-v2 { display: none; }
    .congrats-script { font-size: 2.5rem; }
    .award-main-title { font-size: 1rem; }
}

@media (max-width: 576px) {
    .congrats-script { font-size: 2rem; }
    .recipient-name-v2 { font-size: 1.25rem; }
    .laurel-frame-container { width: 140px; height: 140px; }
    .profile-circle { width: 110px; height: 110px; }
}
</style>
<?php endif; ?>