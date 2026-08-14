<?php
// Ensure database connection exists
if (!isset($db)) {
    require_once 'config/config.php';
    $db = getDbConnection();
}

$verifiedMembers = [];
try {
    // Fetch latest 12 verified members who have a profile picture
    $stmt = $db->prepare("SELECT name, designation, membership_type, profile_image, created_at 
                          FROM users 
                          WHERE status = 'approved' 
                          AND profile_image IS NOT NULL 
                          AND profile_image != '' 
                          ORDER BY created_at DESC LIMIT 12");
    $stmt->execute();
    $verifiedMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError('Verified Members section error: ' . $e->getMessage());
}
?>

<?php if (!empty($verifiedMembers)): ?>
<div class="container-fluid my-5 verified-members-section" id="verified-members-section">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 verified-members-section-header">
            <h3 class="section-heading mb-0"><span>Congratulations to our Verified Members</span></h3>
            <a href="<?php echo SITE_URL; ?>/our-team.php" class="btn btn-primary">
                <i class="fas fa-users me-2"></i>View All
            </a>
        </div>

        <?php $memberChunks = array_chunk($verifiedMembers, 4); // 4 per row on desktop ?>

        <div id="verifiedMembersCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-inner pb-4">
                <?php foreach ($memberChunks as $chunkIndex => $chunk): ?>
                    <div class="carousel-item <?php echo $chunkIndex === 0 ? 'active' : ''; ?>">
                        <div class="row g-4">
                            <?php foreach ($chunk as $member): ?>
                                <div class="col-12 col-sm-6 col-lg-3">
                                    <div class="card-custom verified-member-card text-center h-100 p-3">
                                        <div class="verified-member-image-wrapper mx-auto mb-3">
                                            <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($member['profile_image'], ENT_QUOTES, 'UTF-8'); ?>"
                                                 alt="<?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                 class="verified-member-image img-fluid rounded-circle"
                                                 loading="<?php echo $chunkIndex === 0 ? 'eager' : 'lazy'; ?>"
                                                 onerror="this.src='<?php echo SITE_URL; ?>/assets/img/default-avatar.png';">
                                            <div class="verified-badge" title="Verified Member">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                        <div class="card-body p-0">
                                            <h5 class="card-title fw-bold text-primary mb-1">
                                                <?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </h5>
                                            <p class="text-muted small mb-2 text-uppercase fw-semibold">
                                                <?php echo htmlspecialchars(html_entity_decode($member['designation'], ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?>
                                            </p>
                                            <span class="badge bg-gold text-dark mb-3 px-3 py-2 rounded-pill shadow-sm">
                                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $member['membership_type']))); ?>
                                            </span>
                                            <p class="small text-muted mb-0">
                                                Joined: <?php echo date('d M Y', strtotime($member['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($memberChunks) > 1): ?>
                <div class="carousel-indicators" style="bottom: -15px;">
                    <?php foreach ($memberChunks as $index => $unusedChunk): ?>
                        <button type="button" data-bs-target="#verifiedMembersCarousel"
                            data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"
                            aria-label="Slide <?php echo $index + 1; ?>" style="background-color: var(--primary-color, #0a1f44); width: 12px; height: 12px; border-radius: 50%;"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .verified-members-section {
        background: #fdfdfd;
        padding: 4rem 0;
    }
    .verified-members-section-header .section-heading {
        text-align: left;
    }
    .verified-members-section-header .section-heading span::after {
        left: 0;
        transform: none;
    }
    .verified-member-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .verified-member-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 80px;
        background: var(--gradient-primary, linear-gradient(135deg, #0a1f44, #1a365d));
        z-index: 0;
    }
    .verified-member-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    .verified-member-image-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        margin-top: 20px;
        z-index: 1;
    }
    .verified-member-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        background: #fff;
    }
    .verified-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #fff;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #28a745;
        font-size: 1.2rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .verified-member-card .card-body {
        position: relative;
        z-index: 1;
    }
    .bg-gold {
        background: var(--secondary-color, #e6b325) !important;
    }
    @media (max-width: 767.98px) {
        .verified-members-section-header {
            flex-direction: column;
            align-items: flex-start !important;
        }
    }
</style>
<?php endif; ?>