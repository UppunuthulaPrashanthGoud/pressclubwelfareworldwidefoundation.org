<?php
// Fetch all works for dropdown (kept for compatibility in case $db is used elsewhere)
if (isset($db)) {
    try {
        $works_stmt = $db->prepare("SELECT id, name FROM ourworks ORDER BY name ASC");
        $works_stmt->execute();
        $all_works = $works_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $all_works = [];
    }
}
?>

<!-- MAIN NAVIGATION BAR -->
<style>
/* Base container using your Navy Gradient from style.css */
.main-navbar-container {
    background: var(--gradient-navbar);
    border-bottom: 3px solid var(--secondary-color);
    box-shadow: 0 2px 10px var(--shadow-medium);
    position: relative;
    z-index: 1040; /* Ensures menu stays above hero slider */
}

/* Optimization to prevent menu wrapping on desktop */
.main-navbar-container .nav-link {
    padding: 0.5rem 0.45rem !important; /* Tight spacing to fit 13 items */
    font-size: 0.82rem !important;      
    white-space: nowrap;
    text-transform: uppercase;
    font-weight: 600;
    color: var(--text-white) !important; 
    transition: all 0.2s ease;
}

/* Specific spacing for icons */
.main-navbar-container .nav-link i {
    font-size: 0.8rem;
    margin-right: 3px;
    color: var(--secondary-light); /* Gold icons for prestige look */
}

/* High contrast hover state using Gold */
.main-navbar-container .nav-link:hover,
.main-navbar-container .dropdown:hover > .nav-link {
    background: rgba(255, 255, 255, 0.1); 
    color: var(--secondary-light) !important; /* Gold text on hover */
    transform: translateY(-1px);
}

/* Dropdown visibility fix */
.main-navbar-container .dropdown-menu {
    background-color: var(--white-bg) !important;
    border: none;
    border-top: 3px solid var(--secondary-color);
    box-shadow: 0 8px 25px var(--shadow-darker);
    border-radius: 0 0 8px 8px;
    padding: 10px 0;
    z-index: 1050 !important;
}

/* Ensure dropdown text is visible (Dark text on White BG) */
.main-navbar-container .dropdown-item {
    color: var(--text-color) !important;
    font-weight: 500;
    padding: 8px 20px;
    transition: all 0.3s ease;
}

/* Dropdown item hover - Navy background with White text */
.main-navbar-container .dropdown-item:hover {
    background: var(--gradient-primary);
    color: var(--text-white) !important;
    padding-left: 25px;
}

/* Container width optimization */
.main-navbar-container .container-fluid {
    padding-left: 10px;
    padding-right: 10px;
}

/* Custom scrollbar matching the theme */
.main-navbar-container .dropdown-menu::-webkit-scrollbar {
    width: 6px;
}
.main-navbar-container .dropdown-menu::-webkit-scrollbar-track {
    background: var(--light-bg);
}
.main-navbar-container .dropdown-menu::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}
</style>

<nav class="main-navbar-container">
    <div class="container-fluid">
        <!-- Desktop Navigation -->
        <div class="d-none d-lg-block">
            <ul class="nav justify-content-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="aboutus.php">
                        <i class="fas fa-info-circle"></i>About Us
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-users"></i>Member
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item" href="our-team.php">Our Team</a></li>
                        <li><a class="dropdown-item" href="users-apply-form.php">Member Registration</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="upcoming-event.php">
                        <i class="fas fa-calendar-alt"></i>Event
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="donation-form.php">
                        <i class="fas fa-hand-holding-heart"></i>Donation
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="news.php">
                        <i class="fas fa-newspaper"></i>News
                    </a>
                </li>
                
                <!-- Nominations (Apply Now) -->
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="nominations-apply.php" style="color: #2ecc71 !important;">
                        <i class="fas fa-file-signature"></i>Nominations
                    </a>
                </li>
                
                <!-- Awards Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-trophy"></i>Awards
                    </a>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item" href="awards-list.php">Awards List</a></li>
                        <li><a class="dropdown-item" href="honorary-doctorate.php">Honorary Doctorate</a></li>
                        <li><a class="dropdown-item" href="verify-member.php">Verification</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item fw-bold" href="nominations-apply.php">Apply for Nomination</a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="users-apply-form.php">
                        <i class="fas fa-user-plus"></i>Join Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact-us.php">
                        <i class="fas fa-envelope"></i>Contact Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gallery.php">
                        <i class="fas fa-images"></i>Gallery
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="sponsors.php">
                        <i class="fas fa-handshake"></i>Sponsored
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-sign-in-alt"></i>Login
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="user-login.php"><i class="fas fa-user me-2"></i>User Login</a></li>
                        <li><a class="dropdown-item" href="coordinator-login.php"><i class="fas fa-user-tie me-2"></i>Coordinator Login</a></li>
                        <li><a class="dropdown-item" href="admin-login.php"><i class="fas fa-user-shield me-2"></i>Admin Login</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        
        <!-- Mobile Navigation Toggle -->
        <div class="d-lg-none py-2 px-1">
            <button class="btn btn-primary mobile-nav-toggle border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Navigation Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-white" id="mobileMenuLabel">
            <i class="fas fa-shield-alt text-gold me-2"></i>Menu
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="mobile-nav">
            <a class="nav-link mobile-nav-link" href="index.php"><i class="fas fa-home me-3"></i>Home</a>
            <a class="nav-link mobile-nav-link" href="aboutus.php"><i class="fas fa-info-circle me-3"></i>About Us</a>
            
            <div class="accordion accordion-flush" id="mobileAccordion">
                <div class="accordion-item bg-transparent">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#membersCollapse">
                            <i class="fas fa-users me-3"></i>Member
                        </button>
                    </h2>
                    <div id="membersCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                        <div class="accordion-body p-0 ps-4">
                            <a class="nav-link mobile-nav-link" href="our-team.php">Our Team</a>
                            <a class="nav-link mobile-nav-link" href="users-apply-form.php">Member Registration</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <a class="nav-link mobile-nav-link" href="upcoming-event.php"><i class="fas fa-calendar-alt me-3"></i>Event</a>
            <a class="nav-link mobile-nav-link" href="donation-form.php"><i class="fas fa-hand-holding-heart me-3"></i>Donation</a>
            <a class="nav-link mobile-nav-link" href="news.php"><i class="fas fa-newspaper me-3"></i>News</a>
            
            <div class="accordion accordion-flush" id="awardsAccordion">
                <div class="accordion-item bg-transparent">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#awardsCollapse">
                            <i class="fas fa-trophy me-3"></i>Awards
                        </button>
                    </h2>
                    <div id="awardsCollapse" class="accordion-collapse collapse" data-bs-parent="#awardsAccordion">
                        <div class="accordion-body p-0 ps-4">
                            <a class="nav-link mobile-nav-link" href="awards-list.php">Awards List</a>
                            <a class="nav-link mobile-nav-link" href="honorary-doctorate.php">Honorary Doctorate</a>
                            <a class="nav-link mobile-nav-link" href="verify-member.php">Verification</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <a class="nav-link mobile-nav-link" href="users-apply-form.php"><i class="fas fa-user-plus me-3"></i>Join Us</a>
            <a class="nav-link mobile-nav-link" href="contact-us.php"><i class="fas fa-envelope me-3"></i>Contact Us</a>
            <a class="nav-link mobile-nav-link" href="gallery.php"><i class="fas fa-images me-3"></i>Gallery</a>
            <a class="nav-link mobile-nav-link" href="sponsors.php"><i class="fas fa-handshake me-3"></i>Sponsored</a>
            
            <div class="accordion accordion-flush" id="loginAccordion">
                <div class="accordion-item bg-transparent">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-transparent text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#loginCollapse">
                            <i class="fas fa-sign-in-alt me-3"></i>Login
                        </button>
                    </h2>
                    <div id="loginCollapse" class="accordion-collapse collapse" data-bs-parent="#loginAccordion">
                        <div class="accordion-body p-0 ps-4">
                            <a class="nav-link mobile-nav-link" href="user-login.php">User Login</a>
                            <a class="nav-link mobile-nav-link" href="coordinator-login.php">Coordinator Login</a>
                            <a class="nav-link mobile-nav-link" href="admin-login.php">Admin Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</div>