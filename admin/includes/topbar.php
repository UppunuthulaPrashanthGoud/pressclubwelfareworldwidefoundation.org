<div class="admin-topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h4 class="topbar-title mb-0">
            <?php echo $pageTitle ?? 'Admin Panel'; ?>
        </h4>
    </div>
    
    <div class="topbar-right">
        <div class="dropdown">
            <button class="user-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar-small">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
                <span class="user-name d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <span class="badge bg-primary ms-2"><?php echo ucfirst($_SESSION['user_type']); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/profile.php">
                    <i class="fas fa-user me-2"></i>Profile
                </a></li>
                <?php if (isAdmin()): ?>
                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/settings.php">
                    <i class="fas fa-cog me-2"></i>Settings
                </a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/logout.php" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a></li>
            </ul>
        </div>
    </div>
</div>