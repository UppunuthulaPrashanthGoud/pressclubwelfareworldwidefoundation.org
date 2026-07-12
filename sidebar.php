<?php
// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <img src="images/logo.png" alt="Logo" class="sidebar-logo me-2">
            <div>
                <h5 class="mb-0 text-white">Sanatan Dharma</h5>
                <small class="text-light opacity-75">Jagruti Vahini Parishad</small>
            </div>
        </div>
        <button class="btn btn-link text-white d-lg-none" onclick="toggleSidebar()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-content">
        <!-- User Info -->
        <div class="user-info">
            <div class="d-flex align-items-center">
                <div class="user-avatar bg-light rounded-circle d-flex align-items-center justify-content-center me-3">
                    <i class="fas fa-user text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-white"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <small class="text-light opacity-75"><?php echo ucfirst($_SESSION['user_type']); ?></small>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Admin Only Sections -->
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'users-management.php' ? 'active' : ''; ?>" href="users-management.php">
                        <i class="fas fa-users me-2"></i>
                        <span>Users Management</span>
                        <?php
                        // Get pending users count
                        $db = Database::getInstance()->getConnection();
                        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE status = 'pending'");
                        $stmt->execute();
                        $pending_count = $stmt->fetchColumn();
                        if ($pending_count > 0):
                        ?>
                            <span class="badge bg-warning ms-auto"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'donations-management.php' ? 'active' : ''; ?>" href="donations-management.php">
                        <i class="fas fa-heart me-2"></i>
                        <span>Donations Management</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Coordinator and Admin Sections -->
                <?php if (isCoordinator()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'gallery-management.php' ? 'active' : ''; ?>" href="gallery-management.php">
                        <i class="fas fa-images me-2"></i>
                        <span>Gallery Management</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'events-management.php' ? 'active' : ''; ?>" href="events-management.php">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <span>Events Management</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'activities-management.php' ? 'active' : ''; ?>" href="activities-management.php">
                        <i class="fas fa-tasks me-2"></i>
                        <span>Activities Management</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Divider -->
                <li class="nav-divider"></li>

                <!-- Profile & Settings -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showSection('profile', 'My Profile')">
                        <i class="fas fa-user-circle me-2"></i>
                        <span>My Profile</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showSection('settings', 'Settings')">
                        <i class="fas fa-cog me-2"></i>
                        <span>Settings</span>
                    </a>
                </li>

                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link text-danger" href="#" onclick="logout()">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="sidebar-footer">
        <div class="text-center">
            <small class="text-light opacity-75">
                © <?php echo date('Y'); ?> Sanatan Dharma Jagruti
            </small>
        </div>
    </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay d-lg-none" id="sidebar-overlay" onclick="toggleSidebar()"></div>

<style>
:root {
    --sidebar-width: 280px;
    --sidebar-bg: linear-gradient(135deg, #291872, #d62420);
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--sidebar-bg);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: between;
    align-items: center;
}

.sidebar-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
}

.user-info {
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.user-avatar {
    width: 40px;
    height: 40px;
}

.sidebar-nav .nav-link {
    color: rgba(255,255,255,0.8);
    padding: 0.75rem 1.5rem;
    border-radius: 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    text-decoration: none;
}

.sidebar-nav .nav-link:hover {
    color: white;
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
}

.sidebar-nav .nav-link.active {
    color: white;
    background: rgba(255,255,255,0.2);
    border-right: 3px solid #ffd700;
}

.sidebar-nav .nav-link i {
    width: 20px;
    text-align: center;
}

.nav-divider {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 1rem 1.5rem;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
    display: none;
}

/* Mobile Styles */
@media (max-width: 991.98px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .sidebar-overlay.show {
        display: block;
    }
}

/* Custom Scrollbar */
.sidebar-content::-webkit-scrollbar {
    width: 6px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (window.innerWidth <= 991.98) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
}

function showSection(section, title) {
    // This function would be implemented in the main dashboard
    if (typeof window.showSection === 'function') {
        window.showSection(section, title);
    }
}

function logout() {
    Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, logout'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
}

// Close sidebar on window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 991.98) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    }
});

// Update active navigation based on current page
function updateActiveNav(section) {
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Add active class to current section if it exists
    const currentLink = document.querySelector(`[onclick*="${section}"]`);
    if (currentLink) {
        currentLink.classList.add('active');
    }
}
</script>
