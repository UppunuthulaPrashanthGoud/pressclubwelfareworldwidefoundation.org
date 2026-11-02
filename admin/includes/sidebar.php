<?php
require_once __DIR__ . '/auth_check.php';

$currentPage = basename($_SERVER['PHP_SELF']);

// Define submenu mappings to auto-expand parent menus
$contentPages = [
    'news.php', 'notice_board.php', 'events.php', 'gallery.php', 'sliders.php',
    'youtube-videos.php', 'recent_activities.php', 'campaigns.php', 'e_certificates.php',
    'congratulations_certificates.php', 'ourworks.php', 'sponsors.php',
    'about_content.php', 'about_us_content.php', 'why_choose_us.php',
    'advertisements.php', 'partners.php', 'manage_social_media.php',
    'manage_affiliations.php', 'team-members.php'
];

$memberPages = [
    'users-management.php', 'membership_pricing.php', 'designations.php',
    'nominations-management.php', 'manage-awards-venues.php'
];

$donationPages = [
    'donations.php', 'bank-details.php', 'razorpay-config.php'
];

$toolsPages = [
    'certificates.php', 'awards_letters.php', 'awards-congratulations.php',
    'honorary_awards.php', 'id-card-generator.php', 'template.php'
];

$commPages = [
    'contact-messages.php', 'complaints.php', 'contact-info.php'
];

$policyPages = [
    'policy_management.php'
];

$settingsPages = [
    'site_config.php', 'topbar-settings.php', 'footer-settings.php',
    'style_switch.php', 'settings.php', 'smtp-settings.php'
];

$isContentActive = in_array($currentPage, $contentPages);
$isMemberActive = in_array($currentPage, $memberPages);
$isDonationActive = in_array($currentPage, $donationPages);
$isToolsActive = in_array($currentPage, $toolsPages);
$isCommActive = in_array($currentPage, $commPages);
$isPolicyActive = in_array($currentPage, $policyPages);
$isSettingsActive = in_array($currentPage, $settingsPages);
?>

<style>
.admin-sidebar .dropdown-item.active {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
    border-left: 3px solid #ffc107 !important;
    font-weight: 600 !important;
}
.admin-sidebar .dropdown-item.active i {
    color: #ffc107 !important;
}
.admin-sidebar .menu-item.has-dropdown.active > .dropdown-toggle {
    color: #ffffff !important;
}
</style>

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
            <a href="<?php echo SITE_URL; ?>/admin/" class="menu-link <?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </div>
        
<?php if (isAdmin() || isCoordinator()): ?>
        <!-- Content Management - Admin and Coordinator -->
        <div class="menu-item has-dropdown <?php echo $isContentActive ? 'active' : ''; ?>">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle <?php echo $isContentActive ? 'expanded' : ''; ?>" data-target="contentMenu">
                <i class="fas fa-folder"></i>
                <span class="menu-text">Content Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu <?php echo $isContentActive ? 'show' : ''; ?>" id="contentMenu">
                <a href="<?php echo SITE_URL; ?>/admin/news.php" class="dropdown-item <?php echo $currentPage == 'news.php' ? 'active' : ''; ?>">
                    <i class="fas fa-newspaper"></i>
                    <span>News Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/notice_board.php" class="dropdown-item <?php echo $currentPage == 'notice_board.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Notice Board</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/events.php" class="dropdown-item <?php echo $currentPage == 'events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i>
                    <span>Event Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/gallery.php" class="dropdown-item <?php echo $currentPage == 'gallery.php' ? 'active' : ''; ?>">
                    <i class="fas fa-images"></i>
                    <span>Gallery Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/sliders.php" class="dropdown-item <?php echo $currentPage == 'sliders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-sliders-h"></i>
                    <span>Slider Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/youtube-videos.php" class="dropdown-item <?php echo $currentPage == 'youtube-videos.php' ? 'active' : ''; ?>">
                    <i class="fab fa-youtube"></i>
                    <span>YouTube Videos</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/campaigns.php" class="dropdown-item <?php echo $currentPage == 'campaigns.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Campaign Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/e_certificates.php" class="dropdown-item <?php echo $currentPage == 'e_certificates.php' ? 'active' : ''; ?>">
                    <i class="fas fa-certificate"></i>
                    <span>E-Certificates</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/congratulations_certificates.php" class="dropdown-item <?php echo $currentPage == 'congratulations_certificates.php' ? 'active' : ''; ?>">
                    <i class="fas fa-certificate"></i>
                    <span>Congratulations Certificates</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/ourworks.php" class="dropdown-item <?php echo $currentPage == 'ourworks.php' ? 'active' : ''; ?>">
                    <i class="fas fa-briefcase"></i>
                    <span>Our Works</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/sponsors.php" class="dropdown-item <?php echo $currentPage == 'sponsors.php' ? 'active' : ''; ?>">
                    <i class="fas fa-handshake"></i>
                    <span>Sponsors</span>
                </a>
                <?php if (isAdmin()): ?>
                <a href="<?php echo SITE_URL; ?>/admin/about_content.php" class="dropdown-item <?php echo $currentPage == 'about_content.php' ? 'active' : ''; ?>">
                    <i class="fas fa-info-circle"></i>
                    <span>About Us</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/why_choose_us.php" class="dropdown-item <?php echo $currentPage == 'why_choose_us.php' ? 'active' : ''; ?>">
                    <i class="fas fa-question-circle"></i> <span>Why Choose Us</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/advertisements.php" class="dropdown-item <?php echo $currentPage == 'advertisements.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ad"></i>
                    <span>Advertisements</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/partners.php" class="dropdown-item <?php echo $currentPage == 'partners.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hands-helping"></i>
                    <span>Partners</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/manage_social_media.php" class="dropdown-item <?php echo $currentPage == 'manage_social_media.php' ? 'active' : ''; ?>">
                    <i class="fab fa-instagram"></i>
                    <span>Social Media Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/manage_affiliations.php" class="dropdown-item <?php echo $currentPage == 'manage_affiliations.php' ? 'active' : ''; ?>">
                    <i class="fas fa-award"></i>
                    <span>Affiliations</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/team-members.php" class="dropdown-item <?php echo $currentPage == 'team-members.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Team Members</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (isAdmin()): ?>
        <!-- Member Management - Admin Only -->
        <div class="menu-item has-dropdown <?php echo $isMemberActive ? 'active' : ''; ?>">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle <?php echo $isMemberActive ? 'expanded' : ''; ?>" data-target="memberMenu">
                <i class="fas fa-users"></i>
                <span class="menu-text">Member Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu <?php echo $isMemberActive ? 'show' : ''; ?>" id="memberMenu">
                <a href="<?php echo SITE_URL; ?>/admin/users-management.php" class="dropdown-item <?php echo $currentPage == 'users-management.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i>
                    <span>All Members</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/membership_pricing.php" class="dropdown-item <?php echo $currentPage == 'membership_pricing.php' ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Membership Pricing Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/designations.php" class="dropdown-item <?php echo $currentPage == 'designations.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    <span>Designation Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/nominations-management.php" class="dropdown-item <?php echo $currentPage == 'nominations-management.php' ? 'active' : ''; ?>">
                    <i class="fas fa-vote-yea"></i>
                    <span>Nominations Management</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/manage-awards-venues.php" class="dropdown-item <?php echo $currentPage == 'manage-awards-venues.php' ? 'active' : ''; ?>">
                    <i class="fas fa-list-ul"></i>
                    <span>Award & Venue Master</span>
                </a>
            </div>
        </div>
        
        <!-- Donation Management - Admin Only -->
        <div class="menu-item has-dropdown <?php echo $isDonationActive ? 'active' : ''; ?>">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle <?php echo $isDonationActive ? 'expanded' : ''; ?>" data-target="donationMenu">
                <i class="fas fa-heart"></i>
                <span class="menu-text">Donation Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu <?php echo $isDonationActive ? 'show' : ''; ?>" id="donationMenu">
                <a href="<?php echo SITE_URL; ?>/admin/donations.php" class="dropdown-item <?php echo $currentPage == 'donations.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Donation List</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/bank-details.php" class="dropdown-item <?php echo $currentPage == 'bank-details.php' ? 'active' : ''; ?>">
                    <i class="fas fa-university"></i>
                    <span>Bank Details</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/razorpay-config.php" class="dropdown-item <?php echo $currentPage == 'razorpay-config.php' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Razorpay Config</span>
                </a>
            </div>
        </div>
        
        <!-- Documents - Admin Only -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/documents.php" class="menu-link <?php echo $currentPage == 'documents.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Documents</span>
            </a>
        </div>
        
        <!-- Tools - Admin Only -->
        <div class="menu-item has-dropdown <?php echo $isToolsActive ? 'active' : ''; ?>">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle <?php echo $isToolsActive ? 'expanded' : ''; ?>" data-target="toolsMenu">
                <i class="fas fa-tools"></i>
                <span class="menu-text">Tools</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu <?php echo $isToolsActive ? 'show' : ''; ?>" id="toolsMenu">
                <a href="<?php echo SITE_URL; ?>/admin/certificates.php" class="dropdown-item <?php echo $currentPage == 'certificates.php' ? 'active' : ''; ?>">
                    <i class="fas fa-certificate"></i>
                    <span>Certificates</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/awards_letters.php" class="dropdown-item <?php echo $currentPage == 'awards_letters.php' ? 'active' : ''; ?>">
                    <i class="fas fa-award"></i>
                    <span>Award Letters</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/awards-congratulations.php" class="dropdown-item <?php echo $currentPage == 'awards-congratulations.php' ? 'active' : ''; ?>">
                    <i class="fas fa-trophy"></i>
                    <span>Congratulations Awards</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/honorary_awards.php" class="dropdown-item <?php echo $currentPage == 'honorary_awards.php' ? 'active' : ''; ?>">
                    <i class="fas fa-medal"></i>
                    <span>Honorary Awards</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/id-card-generator.php" class="dropdown-item <?php echo $currentPage == 'id-card-generator.php' ? 'active' : ''; ?>">
                    <i class="fas fa-id-card"></i>
                    <span>ID Card Generator</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/template.php" class="dropdown-item <?php echo $currentPage == 'template.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-image"></i>
                    <span>Template Management</span>
                </a>
            </div>
        </div>
        
        <!-- Communication - Admin Only -->
        <div class="menu-item has-dropdown <?php echo $isCommActive ? 'active' : ''; ?>">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle <?php echo $isCommActive ? 'expanded' : ''; ?>" data-target="communicationMenu">
                <i class="fas fa-envelope"></i>
                <span class="menu-text">Communication Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu <?php echo $isCommActive ? 'show' : ''; ?>" id="communicationMenu">
                <a href="<?php echo SITE_URL; ?>/admin/contact-messages.php" class="dropdown-item <?php echo $currentPage == 'contact-messages.php' ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i>
                    <span>Contact Messages</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/complaints.php" class="dropdown-item <?php echo $currentPage == 'complaints.php' ? 'active' : ''; ?>">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Complaints</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/contact-info.php" class="dropdown-item <?php echo $currentPage == 'contact-info.php' ? 'active' : ''; ?>">
                    <i class="fas fa-address-book"></i>
                    <span>Contact Information</span>
                </a>
            </div>
        </div>
        
        <?php if (isAdmin()): ?>
        <!-- Policy Management - Admin Only -->
        <div class="menu-item has-dropdown <?php echo $isPolicyActive ? 'active' : ''; ?>">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle <?php echo $isPolicyActive ? 'expanded' : ''; ?>" data-target="policyMenu">
                <i class="fas fa-file-alt"></i>
                <span class="menu-text">Policy Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu <?php echo $isPolicyActive ? 'show' : ''; ?>" id="policyMenu">
                <a href="<?php echo SITE_URL; ?>/admin/policy_management.php" class="dropdown-item <?php echo $currentPage == 'policy_management.php' ? 'active' : ''; ?>">
                    <i class="fas fa-scroll"></i>
                    <span>All Policies</span>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Settings - Admin Only -->
        <div class="menu-item has-dropdown <?php echo $isSettingsActive ? 'active' : ''; ?>">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle <?php echo $isSettingsActive ? 'expanded' : ''; ?>" data-target="settingsMenu">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Settings</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu <?php echo $isSettingsActive ? 'show' : ''; ?>" id="settingsMenu">
                <a href="<?php echo SITE_URL; ?>/admin/site_config.php" class="dropdown-item <?php echo $currentPage == 'site_config.php' ? 'active' : ''; ?>">
                    <i class="fas fa-globe"></i>
                    <span>Site Configuration</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/footer-settings.php" class="dropdown-item <?php echo $currentPage == 'footer-settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-window-minimize"></i>
                    <span>Footer Settings</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/smtp-settings.php" class="dropdown-item <?php echo $currentPage == 'smtp-settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope-open-text"></i>
                    <span>SMTP Settings</span>
                </a>
            </div>
        </div>
        
        <?php elseif (isCoordinator()): ?>
        <!-- Limited Member Access - Coordinator -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/users-management.php" class="menu-link <?php echo $currentPage == 'users-management.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span class="menu-text">View Members</span>
            </a>
        </div>
        
        <!-- Donation Management - Coordinator -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/donations.php" class="menu-link <?php echo $currentPage == 'donations.php' ? 'active' : ''; ?>">
                <i class="fas fa-hand-holding-heart"></i>
                <span class="menu-text">My Donations</span>
            </a>
        </div>
        
        <!-- Tools for Coordinator -->
        <div class="menu-item has-dropdown <?php echo ($currentPage == 'id-card-generator.php') ? 'active' : ''; ?>">
            <a href="javascript:void(0)" class="menu-link dropdown-toggle <?php echo ($currentPage == 'id-card-generator.php') ? 'expanded' : ''; ?>" data-target="coordinatorToolsMenu">
                <i class="fas fa-tools"></i>
                <span class="menu-text">Tools</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </a>
            <div class="dropdown-menu <?php echo ($currentPage == 'id-card-generator.php') ? 'show' : ''; ?>" id="coordinatorToolsMenu">
                <a href="<?php echo SITE_URL; ?>/admin/id-card-generator.php" class="dropdown-item <?php echo $currentPage == 'id-card-generator.php' ? 'active' : ''; ?>">
                    <i class="fas fa-id-card"></i>
                    <span>ID Card Generator</span>
                </a>
            </div>
        </div>
        
        <?php else: ?>
        <!-- User Menu Items -->
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="menu-link <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span class="menu-text">My Profile</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/id-card-generator.php" class="menu-link <?php echo $currentPage == 'id-card-generator.php' ? 'active' : ''; ?>">
                <i class="fas fa-id-card"></i>
                <span class="menu-text">My ID Card</span>
            </a>
        </div>
        
        <div class="menu-item">
            <a href="<?php echo SITE_URL; ?>/admin/donations.php" class="menu-link <?php echo $currentPage == 'donations.php' ? 'active' : ''; ?>">
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
    // Auto-expand any parent dropdown containing an active item
    const activeSubItem = document.querySelector('.dropdown-menu .dropdown-item.active');
    if (activeSubItem) {
        const parentMenu = activeSubItem.closest('.dropdown-menu');
        if (parentMenu) {
            parentMenu.classList.add('show');
            const targetId = parentMenu.id;
            const toggle = document.querySelector(`.dropdown-toggle[data-target="${targetId}"]`);
            if (toggle) {
                toggle.classList.add('expanded');
                const parentMenuItem = toggle.closest('.menu-item');
                if (parentMenuItem) parentMenuItem.classList.add('active');
            }
        }
    }

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
            
            document.querySelectorAll('.dropdown-toggle.expanded').forEach(function(t) {
                t.classList.remove('expanded');
            });
            
            // Toggle current dropdown
            if (!isCurrentlyOpen) {
                targetMenu.classList.add('show');
                this.classList.add('expanded');
                const parentMenuItem = this.closest('.menu-item');
                if (parentMenuItem) parentMenuItem.classList.add('active');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.has-dropdown')) {
            // Keep active dropdown open, close others
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                if (!menu.querySelector('.dropdown-item.active')) {
                    menu.classList.remove('show');
                    const targetId = menu.id;
                    const toggle = document.querySelector(`.dropdown-toggle[data-target="${targetId}"]`);
                    if (toggle) toggle.classList.remove('expanded');
                }
            });
        }
    });
});
</script>
