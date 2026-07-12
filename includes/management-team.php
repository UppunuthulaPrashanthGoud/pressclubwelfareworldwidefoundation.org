<?php
/**
 * includes/management-team.php
 * Displays ALL management team members on the homepage (or other pages).
 * Fields: Photo, Name, Designation, Area of Working, Contact No.
 * No redirects/buttons to other pages.
 */

if (!isset($db)) {
    require_once 'config/config.php';
    $db = getDbConnection();
}

// Fetch Data if not already available
if (!isset($management_team)) {
    try {
        // Fetch ALL active management members (No LIMIT)
        $stmt = $db->prepare("
            SELECT * FROM team_members 
            WHERE status = 'active' AND member_type = 'management' 
            ORDER BY sort_order ASC, created_at DESC 
        ");
        $stmt->execute();
        $management_team = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $management_team = [];
    }
}
?>

<?php if (!empty($management_team)): ?>
<section class="management-section py-5" style="background-color: var(--light-bg);">
    <div class="container">
        <!-- Section Heading -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h3 class="section-heading text-uppercase"><span>Management Team</span></h3>
                <div style="width: 60px; height: 3px; background: var(--gold-color); margin: 10px auto;"></div>
            </div>
        </div>

        <!-- Members Grid -->
        <div class="row g-4 justify-content-center">
            <?php foreach ($management_team as $member): 
                // Handle image path
                $imgSrc = !empty($member['image']) ? 'uploads/team/' . $member['image'] : 'img/default-user.png';
                if (!file_exists($imgSrc) && !empty($member['image'])) { $imgSrc = 'img/team/' . $member['image']; }
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up">
                <div class="member-card-modern h-100">
                    
                    <!-- 1. Photo -->
                    <div class="member-photo-wrapper">
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                             alt="<?php echo htmlspecialchars($member['name']); ?>" 
                             class="member-photo"
                             onerror="this.src='img/default-user.png';">
                    </div>

                    <div class="member-info p-3 text-center">
                        <!-- 2. Name -->
                        <h5 class="member-name mb-1">
                            <?php echo htmlspecialchars($member['name']); ?>
                        </h5>
                        
                        <!-- 3. Designation -->
                        <p class="member-designation mb-2">
                            <?php echo htmlspecialchars($member['designation']); ?>
                        </p>

                        <div class="member-divider mx-auto mb-3"></div>

                        <!-- 4. Area of Working -->
                        <?php if (!empty($member['area_of_work'])): ?>
                        <div class="member-detail-box mb-2">
                            <span class="detail-label"><i class="fas fa-briefcase me-1"></i> Area of Working</span>
                            <span class="detail-value">
                                <?php echo htmlspecialchars($member['area_of_work']); ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <!-- 5. Contact No -->
                        <?php if (!empty($member['phone'])): ?>
                        <div class="member-contact mt-3">
                            <a href="tel:<?php echo htmlspecialchars($member['phone']); ?>" class="btn btn-sm btn-contact w-100">
                                <i class="fas fa-phone-alt me-2"></i> <?php echo htmlspecialchars($member['phone']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- No 'View All' button here anymore -->
    </div>
</section>

<style>
/* Modern Member Card Styles */
.member-card-modern {
    background: var(--white-bg);
    border-radius: 10px;
    box-shadow: 0 5px 15px var(--shadow-light);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    border: 1px solid var(--border-light);
    height: 100%;
}

.member-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px var(--shadow-medium);
    border-color: var(--gold-color);
}

.member-photo-wrapper {
    width: 100%;
    height: 280px;
    overflow: hidden;
    position: relative;
    border-bottom: 3px solid var(--gold-color);
}

.member-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top;
    transition: transform 0.5s ease;
}

.member-card-modern:hover .member-photo {
    transform: scale(1.05);
}

.member-name {
    color: var(--primary-color);
    font-weight: 700;
    font-family: 'Playfair Display', serif;
    letter-spacing: 0.5px;
}

.member-designation {
    color: var(--gold-color);
    font-size: 0.85rem;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
}

.member-divider {
    width: 30px;
    height: 2px;
    background-color: var(--border-light);
}

.member-detail-box {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 6px;
    border-left: 3px solid var(--primary-color);
    text-align: left;
    margin-bottom: 10px;
}

.detail-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--text-muted);
    display: block;
    margin-bottom: 3px;
    font-weight: 600;
}

.detail-value {
    font-size: 0.9rem;
    color: var(--text-color);
    font-weight: 500;
    line-height: 1.4;
    display: block;
}

.btn-contact {
    background-color: transparent;
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
    border-radius: 50px;
    padding: 6px 15px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.btn-contact:hover {
    background-color: var(--primary-color);
    color: var(--white-bg);
}
</style>
<?php endif; ?>