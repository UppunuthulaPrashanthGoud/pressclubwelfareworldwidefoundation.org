<?php
/**
 * Team Carousels Component
 * Displays management team and member carousels
 */
?>

<div class="card-custom mb-4" data-aos="fade-up">
    <h3 class="section-heading"><span>Management Team</span></h3>
    <div id="managementSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-inner">
            <?php if (!empty($management_team)): ?>
                <?php foreach ($management_team as $index => $member): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="member-card">
                        <a href="management-team.php">
                            <img src="uploads/team/<?php echo htmlspecialchars($member['image'] ?: 'default-avatar.png'); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                        </a>
                        <h5><a href="management-team.php"><?php echo htmlspecialchars($member['name']); ?></a></h5>
                        <p><?php echo htmlspecialchars($member['designation']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <div class="member-card">
                        <img src="uploads/team/default-avatar.png" alt="No Member">
                        <h5>No members available</h5>
                        <p>Management</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <a href="management-team.php" class="view-all-btn">View All</a>
</div>

<div class="card-custom" data-aos="fade-up">
    <h3 class="section-heading"><span>Members</span></h3>
    <div id="userSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-inner">
            <?php if (!empty($recent_users)): ?>
                <?php foreach ($recent_users as $index => $user): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="member-card">
                        <a href="our-team.php">
                            <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_image'] ?: 'default-avatar.png'); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                        </a>
                        <h5><a href="our-team.php"><?php echo htmlspecialchars($user['name']); ?></a></h5>
                        <p><?php echo htmlspecialchars($user['designation_hindi'] ?? ucfirst(str_replace('_', ' ', $user['membership_type']))); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <div class="member-card">
                        <img src="uploads/profiles/default-avatar.png" alt="No User">
                        <h5>No members available</h5>
                        <p>Member</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <a href="our-team.php" class="view-all-btn">View All</a>
</div>

<script>
// Initialize sliders for Management Team and Members
document.addEventListener('DOMContentLoaded', function() {
    // Management Slider
    const managementSlider = document.getElementById('managementSlider');
    if (managementSlider) {
        const managementCarousel = new bootstrap.Carousel(managementSlider, {
            interval: 4000,
            ride: 'carousel',
            pause: 'hover'
        });

        managementSlider.addEventListener('mouseenter', () => managementCarousel.pause());
        managementSlider.addEventListener('mouseleave', () => managementCarousel.cycle());
    }

    // User Slider
    const userSlider = document.getElementById('userSlider');
    if (userSlider) {
        const userCarousel = new bootstrap.Carousel(userSlider, {
            interval: 4000,
            ride: 'carousel',
            pause: 'hover'
        });

        userSlider.addEventListener('mouseenter', () => userCarousel.pause());
        userSlider.addEventListener('mouseleave', () => userCarousel.cycle());
    }
});
</script>