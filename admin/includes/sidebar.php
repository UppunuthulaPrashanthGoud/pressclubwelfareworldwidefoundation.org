<?php
require_once __DIR__ . '/auth_check.php';
?>

<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="<?php echo SITE_URL; ?>/admin/" class="sidebar-logo">
            <i class="fas fa-home text-warning"></i>
            <span class="ms-2">Admin Panel</span>
        </a>
        <button class="sidebar-close d-lg-none" id="sidebarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="sidebar-menu">
        <!-- Dashboard - Available to all -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </div>
        
<?php if (isAdmin() || isCoordinator()): ?>
        <!-- Content Management - Admin and Coordinator -->
        <div class="menu-item has-dropdown">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle" data-target="contentMenu">
                <i class="fas fa-folder"></i>
                <span class="menu-text">Content Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu" id="contentMenu">
                <a href="<?php echo SITE_URL; ?>/admin/news.php" class="dropdown-item">
                    <i class="fas fa-newspaper"></i>
                    <span>News Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/events.php" class="dropdown-item">
                    <i class="fas fa-calendar"></i>
                    <span>Event Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/gallery.php" class="dropdown-item">
                    <i class="fas fa-images"></i>
                    <span>Gallery Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/sliders.php" class="dropdown-item">
                    <i class="fas fa-sliders-h"></i>
                    <span>Slider Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/youtube-videos.php" class="dropdown-item">
                    <i class="fab fa-youtube"></i>
                    <span>YouTube Videos</span>
                </a>
                <!-- <a href="<?php echo SITE_URL; ?>/admin/recent_activities.php" class="dropdown-item">
                    <i class="fas fa-clock"></i>
                    <span>Recent Activities</span>
                </a> -->
                <a href="<?php echo SITE_URL; ?>/admin/campaigns.php" class="dropdown-item">
                    <i class="fas fa-bullhorn"></i>
                    <span>Campaign Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/ourworks.php" class="dropdown-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Our Works</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/sponsors.php" class="dropdown-item">
                    <i class="fas fa-handshake"></i>
                    <span>Sponsors</span>
                </a>
                <?php if (isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>/admin/about_content.php" class="dropdown-item">
                    <i class="fas fa-info-circle"></i>
                    <span>About Us</span>
                </a>
                <a href="why_choose_us.php" class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'why_choose_us.php' ? 'active' : ''; ?>">
                    <i class="fas fa-question-circle"></i> Why Choose Us
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/advertisements.php" class="dropdown-item">
                    <i class="fas fa-ad"></i>
                    <span>Advertisements</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/sponsors.php" class="dropdown-item">
                    <i class="fas fa-handshake"></i>
                    <span>Sponsors</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/partners.php" class="dropdown-item">
                    <i class="fas fa-hands-helping"></i>
                    <span>Partners</span>
                </a>
                <!-- ADDED SOCIAL MEDIA MANAGEMENT -->
                <a href="<?php echo SITE_URL; ?>/admin/manage_social_media.php" class="dropdown-item">
                    <i class="fab fa-instagram"></i>
                    <span>Social Media Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/manage_affiliations.php" class="dropdown-item">
                    <i class="fas fa-award"></i>
                    <span>Affiliations</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/team-members.php" class="dropdown-item">
                    <i class="fas fa-users"></i>
                    <span>Team Members</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isAdmin()): ?>
        <!-- Member Management - Admin Only -->
        <div class="menu-item has-dropdown">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle" data-target="memberMenu">
                <i class="fas fa-users"></i>
                <span class="menu-text">Member Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu" id="memberMenu">
                <a href="<?php echo SITE_URL; ?>/admin/users-management.php" class="dropdown-item">
                    <i class="fas fa-users-cog"></i>
                    <span>All Members</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/membership_pricing.php" class="dropdown-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Membership Pricing Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/designations.php" class="dropdown-item">
                    <i class="fas fa-tags"></i>
                    <span>Designation Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/nominations-management.php" class="dropdown-item">
                    <i class="fas fa-vote-yea"></i>
                    <span>Nominations Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/manage-awards-venues.php" class="dropdown-item">
                    <i class="fas fa-list-ul"></i>
                    <span>Award & Venue Master</span>
                </a>
            </div>
        </div>
        
        <!-- Donation Management - Admin Only -->
        <div class="menu-item has-dropdown">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle" data-target="donationMenu">
                <i class="fas fa-heart"></i>
                <span class="menu-text">Donation Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu" id="donationMenu">
                <a href="<?php echo SITE_URL; ?>/admin/donations.php" class="dropdown-item">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Donation List</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/bank-details.php" class="dropdown-item">
                    <i class="fas fa-university"></i>
                    <span>Bank Details</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/razorpay-config.php" class="dropdown-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Razorpay Config</span>
                </a>
            </div>
        </div>
        <!-- Documents - Admin Only -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/documents.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'documents.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Documents</span>
            </a>
        </div>
        
        <!-- Tools - Admin Only -->
        <div class="menu-item has-dropdown">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle" data-target="toolsMenu">
                <i class="fas fa-tools"></i>
                <span class="menu-text">Tools</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu" id="toolsMenu">
                <a href="<?php echo SITE_URL; ?>/admin/certificates.php" class="dropdown-item">
                    <i class="fas fa-certificate"></i>
                    <span>Certificates</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/awards_letters.php" class="dropdown-item">
                    <i class="fas fa-award"></i>
                    <span>Award Letters</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/awards-congratulations.php" class="dropdown-item">
                    <i class="fas fa-trophy"></i>
                    <span>Congratulations Awards</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/honorary_awards.php" class="dropdown-item">
                    <i class="fas fa-medal"></i>
                    <span>Honorary Awards</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/id-card-generator.php" class="dropdown-item">
                    <i class="fas fa-id-card"></i>
                    <span>ID Card Generator</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/template.php" class="dropdown-item">
                    <i class="fas fa-file-image"></i>
                    <span>Template Management</span>
                </a>
            </div>
        </div>
        <!-- Communication - Admin Only -->
        <div class="menu-item has-dropdown">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle" data-target="communicationMenu">
                <i class="fas fa-envelope"></i>
                <span class="menu-text">Communication Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu" id="communicationMenu">
                <a href="<?php echo SITE_URL; ?>/admin/contact-messages.php" class="dropdown-item">
                    <i class="fas fa-inbox"></i>
                    <span>Contact Messages</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/complaints.php" class="dropdown-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Complaints</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/contact-info.php" class="dropdown-item">
                    <i class="fas fa-address-book"></i>
                    <span>Contact Information</span>
                </a>
            </div>
        </div>
        <?php if (isAdmin()): ?>
        <!-- Policy Management - Admin Only -->
        <div class="menu-item has-dropdown">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle" data-target="policyMenu">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Policy Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu" id="policyMenu">
                <a href="<?php echo SITE_URL; ?>/admin/policy_management.php" class="dropdown-item">
                    <i class="fas fa-scroll"></i>
                    <span>All Policies</span>
                </a>
            </div>
        </div>
        <?php endif; ?>
        <!-- Settings - Admin Only -->
        <div class="menu-item has-dropdown">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle" data-target="settingsMenu">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu" id="settingsMenu">
                <a href="<?php echo SITE_URL; ?>/admin/site_config.php" class="dropdown-item">
                    <i class="fas fa-globe"></i>
                    <span>Site Configuration</span>
                </a>
                <!--<a href="<?php echo SITE_URL; ?>/admin/topbar-settings.php" class="dropdown-item">-->
                <!--    <i class="fas fa-window-maximize"></i>-->
                <!--    <span>Topbar Settings</span>-->
                <!--</a>-->
                <a href="<?php echo SITE_URL; ?>/admin/footer-settings.php" class="dropdown-item">
                    <i class="fas fa-window-minimize"></i>
                    <span>Footer Settings</span>
                </a>
                <!--<a href="<?php echo SITE_URL; ?>/admin/style_switch.php" class="dropdown-item">-->
                <!--    <i class="fas fa-paint-brush"></i>-->
                <!--    <span>Style Management</span>-->
                <!--</a>-->
                <!--<a href="<?php echo SITE_URL; ?>/admin/settings.php" class="dropdown-item">-->
                <!--    <i class="fas fa-sliders-h"></i>-->
                <!--    <span>General Settings</span>-->
                <!--</a>-->
                <a href="<?php echo SITE_URL; ?>/admin/smtp-settings.php" class="dropdown-item">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>SMTP Settings</span>
                </a>
            </div>
        </div>
        
        <?php elseif (isCoordinator()): ?>
        <!-- Limited Member Access - Coordinator -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/users-management.php" class="menu-link">
                <i class="fas fa-users"></i>
                <span class="menu-text">View Members</span>
            </a>
        </div>
        
        <!-- Donation Management - Coordinator -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/donations.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'donations.php' ? 'active' : ''; ?>">
                <i class="fas fa-hand-holding-heart"></i>
                <span class="menu-text">My Donations</span>
            </a>
        </div>
        
        <!-- Tools for Coordinator -->
        <div class="menu-item has-dropdown">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle" data-target="coordinatorToolsMenu">
                <i class="fas fa-tools"></i>
                <span class="menu-text">Tools</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu" id="coordinatorToolsMenu">
                <a href="<?php echo SITE_URL; ?>/admin/id-card-generator.php" class="dropdown-item">
                    <i class="fas fa-id-card"></i>
                    <span>ID Card Generator</span>
                </a>
            </div>
        </div>
        
        <?php else: ?>
        <!-- User Menu Items -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="menu-link">
                <i class="fas fa-user"></i>
                <span class="menu-text">My Profile</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/id-card-generator.php" class="menu-link">
                <i class="fas fa-id-card"></i>
                <span class="menu-text">My ID Card</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/donations.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'donations.php' ? 'active' : ''; ?>">
                <i class="fas fa-hand-holding-heart"></i>
                <span class="menu-text">My Donations</span>
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Logout - Available to all -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="menu-link" onclick="return confirm('Are you sure you want to log out?')">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </a>
        </div>
    </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all dropdown toggles
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const targetId = this.getAttribute('data-target');
            const targetMenu = document.getElementById(targetId);
            
            if (!targetMenu) return;
            
            const isCurrentlyOpen = targetMenu.classList.contains('show');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
            
            document.querySelectorAll('.dropdown-toggle.expanded').forEach(function(toggle) {
                toggle.classList.remove('expanded');
            });
            
            // Toggle current dropdown
            if (!isCurrentlyOpen) {
                targetMenu.classList.add('show');
                this.classList.add('expanded');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.has-dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.dropdown-toggle.expanded').forEach(function(toggle) {
                toggle.classList.remove('expanded');
            });
        }
    });
});
</script>