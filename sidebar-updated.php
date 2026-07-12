<?php
// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: user-login.php');
    exit;
}

$user_type = $_SESSION['user_type'];
$user_name = $_SESSION['user_name'];

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="dashboard-sidebar" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header p-3">
        <div class="text-center mb-3">
            <img src="https://sanatandharmajagruti.in/webimg/1000262758_04152025102216.jpg" alt="Logo" style="height: 50px;" class="mb-2">
            <h6 class="text-white mb-0">Management Dashboard</h6>
        </div>
        
        <!-- User Info -->
        <div class="user-info bg-white bg-opacity-10 rounded p-2 mb-3">
            <div class="d-flex align-items-center">
                <div class="user-avatar bg-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                    <i class="fas fa-user text-primary"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="text-white fw-bold small text-truncate"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="text-white-50 small"><?php echo ucfirst($user_type); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <nav class="sidebar-nav px-3">
        <!-- Dashboard Overview -->
        <div class="nav-section mb-3">
            <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard Overview</span>
            </a>
        </div>

        <!-- User Management (Admin Only) -->
        <?php if (isAdmin()): ?>
        <div class="nav-section mb-3">
            <h6 class="nav-section-title text-white-50 small text-uppercase mb-2">User Management</h6>
            <a class="nav-link <?php echo $current_page == 'users-management.php' ? 'active' : ''; ?>" href="users-management.php">
                <i class="fas fa-users me-2"></i>
                <span>Users Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'pending-users-management.php' ? 'active' : ''; ?>" href="pending-users-management.php">
                <i class="fas fa-user-clock me-2"></i>
                <span>Pending Users Management</span>
                <span class="badge bg-warning ms-auto" id="pending-count">0</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'coordinators-management.php' ? 'active' : ''; ?>" href="coordinators-management.php">
                <i class="fas fa-user-tie me-2"></i>
                <span>Coordinators Management</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Content Management -->
        <div class="nav-section mb-3">
            <h6 class="nav-section-title text-white-50 small text-uppercase mb-2">Content Management</h6>
            
            <?php if (isCoordinator()): ?>
            <a class="nav-link <?php echo $current_page == 'gallery-management.php' ? 'active' : ''; ?>" href="gallery-management.php">
                <i class="fas fa-images me-2"></i>
                <span>Gallery Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'events-management.php' ? 'active' : ''; ?>" href="events-management.php">
                <i class="fas fa-calendar-alt me-2"></i>
                <span>Events Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'activities-management.php' ? 'active' : ''; ?>" href="activities-management.php">
                <i class="fas fa-newspaper me-2"></i>
                <span>Activities Management</span>
            </a>
            <?php endif; ?>

            <?php if (isAdmin()): ?>
            <a class="nav-link <?php echo $current_page == 'sliders-management.php' ? 'active' : ''; ?>" href="sliders-management.php">
                <i class="fas fa-sliders-h me-2"></i>
                <span>Sliders Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'testimonials-management.php' ? 'active' : ''; ?>" href="testimonials-management.php">
                <i class="fas fa-quote-left me-2"></i>
                <span>Testimonials Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'about-content-management.php' ? 'active' : ''; ?>" href="about-content-management.php">
                <i class="fas fa-info-circle me-2"></i>
                <span>About Content Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'objectives-management.php' ? 'active' : ''; ?>" href="objectives-management.php">
                <i class="fas fa-bullseye me-2"></i>
                <span>Objectives Management</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Financial Management (Admin Only) -->
        <?php if (isAdmin()): ?>
        <div class="nav-section mb-3">
            <h6 class="nav-section-title text-white-50 small text-uppercase mb-2">Financial Management</h6>
            <a class="nav-link <?php echo $current_page == 'donations-management.php' ? 'active' : ''; ?>" href="donations-management.php">
                <i class="fas fa-heart me-2"></i>
                <span>Donations Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'payments-management.php' ? 'active' : ''; ?>" href="payments-management.php">
                <i class="fas fa-credit-card me-2"></i>
                <span>Payments Management</span>
            </a>
        </div>
        <?php endif; ?>

        <!-- Communication Management -->
        <div class="nav-section mb-3">
            <h6 class="nav-section-title text-white-50 small text-uppercase mb-2">Communication Management</h6>
            
            <?php if (isAdmin()): ?>
            <a class="nav-link <?php echo $current_page == 'complaints-management.php' ? 'active' : ''; ?>" href="complaints-management.php">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span>Complaints Management</span>
                <span class="badge bg-danger ms-auto" id="complaints-count">0</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'contact-messages-management.php' ? 'active' : ''; ?>" href="contact-messages-management.php">
                <i class="fas fa-envelope me-2"></i>
                <span>Messages Management</span>
            </a>
            <?php endif; ?>

            <a class="nav-link <?php echo $current_page == 'notifications-management.php' ? 'active' : ''; ?>" href="notifications-management.php">
                <i class="fas fa-bell me-2"></i>
                <span>Notifications Management</span>
            </a>
        </div>

        <!-- Personal Management -->
        <div class="nav-section mb-3">
            <h6 class="nav-section-title text-white-50 small text-uppercase mb-2">Personal Management</h6>
            <a class="nav-link <?php echo $current_page == 'profile-management.php' ? 'active' : ''; ?>" href="profile-management.php">
                <i class="fas fa-user me-2"></i>
                <span>Profile Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'settings-management.php' ? 'active' : ''; ?>" href="settings-management.php">
                <i class="fas fa-cog me-2"></i>
                <span>Settings Management</span>
            </a>
        </div>

        <!-- System Management (Admin Only) -->
        <?php if (isAdmin()): ?>
        <div class="nav-section mb-3">
            <h6 class="nav-section-title text-white-50 small text-uppercase mb-2">System Management</h6>
            <a class="nav-link <?php echo $current_page == 'system-settings-management.php' ? 'active' : ''; ?>" href="system-settings-management.php">
                <i class="fas fa-server me-2"></i>
                <span>System Settings Management</span>
            </a>
            <a class="nav-link <?php echo $current_page == 'backup-management.php' ? 'active' : ''; ?>" href="backup-management.php">
                <i class="fas fa-database me-2"></i>
                <span>Backup Management</span>
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer p-3 mt-auto">
        <div class="d-grid gap-2">
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-home me-2"></i>Visit Website
            </a>
            <button class="btn btn-danger btn-sm" onclick="logout()">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </button>
        </div>
    </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

<style>
/* Enhanced Sidebar Styles for All Devices */
.dashboard-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: var(--sidebar-width);
    background: linear-gradient(135deg, var(--primary-color), #1e104f);
    color: white;
    z-index: 1050;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    overflow-y: auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
}

.dashboard-sidebar.show {
    transform: translateX(0);
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.sidebar-overlay.show {
    opacity: 1;
    visibility: visible;
}

.nav-section-title {
    font-weight: 600;
    letter-spacing: 0.5px;
    font-size: 0.75rem;
}

.nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 2px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    text-decoration: none;
    position: relative;
    min-height: 44px; /* Better touch targets */
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white !important;
    transform: translateX(5px);
}

.nav-link.active {
    background: linear-gradient(135deg, var(--secondary-color), #e74c3c);
    color: white !important;
    box-shadow: 0 4px 15px rgba(214, 36, 32, 0.3);
}

.nav-link i {
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}

.nav-link span {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.badge {
    font-size: 0.7rem;
    padding: 2px 6px;
    flex-shrink: 0;
}

/* Desktop Styles */
@media (min-width: 992px) {
    .dashboard-sidebar {
        position: fixed;
        transform: translateX(0);
        width: var(--sidebar-width);
    }
    
    .sidebar-overlay {
        display: none;
    }
}

/* Large Desktop Styles */
@media (min-width: 1200px) {
    .nav-link {
        padding: 14px 18px;
    }
    
    .nav-section-title {
        font-size: 0.8rem;
    }
}

/* Tablet Styles */
@media (min-width: 768px) and (max-width: 991.98px) {
    .dashboard-sidebar {
        width: var(--sidebar-width-mobile);
    }
}

/* Mobile Styles */
@media (max-width: 767.98px) {
    .dashboard-sidebar {
        width: var(--sidebar-width-mobile);
    }
    
    .nav-link {
        padding: 14px 15px;
        min-height: 48px; /* Larger touch targets on mobile */
    }
    
    .sidebar-header {
        padding: 15px !important;
    }
    
    .sidebar-footer {
        padding: 15px !important;
    }
}

/* Small Mobile Styles */
@media (max-width: 575.98px) {
    .dashboard-sidebar {
        width: calc(100vw - 40px);
        max-width: 320px;
    }
    
    .nav-link {
        padding: 12px;
        min-height: 44px;
    }
    
    .nav-section-title {
        font-size: 0.7rem;
    }
}

/* Scrollbar Styling */
.dashboard-sidebar::-webkit-scrollbar {
    width: 6px;
}

.dashboard-sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.dashboard-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.dashboard-sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* High DPI Support */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .nav-link {
        border-radius: 10px;
    }
    
    .dashboard-sidebar {
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
    .dashboard-sidebar,
    .nav-link,
    .sidebar-overlay {
        transition: none;
    }
}
</style>

<script>
// Enhanced Sidebar JavaScript for All Devices
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    let isDesktop = window.innerWidth >= 992;
    
    // Mobile sidebar toggle
    window.toggleSidebar = function() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        
        // Prevent body scroll when sidebar is open on mobile
        if (sidebar.classList.contains('show')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    };
    
    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    });
    
    // Close sidebar on mobile when clicking nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const newIsDesktop = window.innerWidth >= 992;
        
        if (newIsDesktop !== isDesktop) {
            isDesktop = newIsDesktop;
            
            if (isDesktop) {
                // Desktop mode
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
    });
    
    // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    // Load notification counts
    loadNotificationCounts();
});

function loadNotificationCounts() {
    // Load pending users count
    fetch('config.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_counts&csrf_token=<?php echo generateCSRF(); ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const pendingCount = document.getElementById('pending-count');
            const complaintsCount = document.getElementById('complaints-count');
            
            if (pendingCount) {
                pendingCount.textContent = data.data.pending_users || 0;
                pendingCount.style.display = data.data.pending_users > 0 ? 'inline' : 'none';
            }
            
            if (complaintsCount) {
                complaintsCount.textContent = data.data.pending_complaints || 0;
                complaintsCount.style.display = data.data.pending_complaints > 0 ? 'inline' : 'none';
            }
        }
    })
    .catch(error => console.error('Error loading counts:', error));
}

function logout() {
    Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
}
</script>
