<!-- MAIN NAVIGATION BAR -->
<style>
.main-navbar-container .nav-link {
    padding: 0.5rem 0.6rem !important;
    font-size: 0.95rem;
    white-space: nowrap;
}

.main-navbar-container .nav-link i {
    font-size: 0.9rem;
}

.main-navbar-container .dropdown-menu {
    font-size: 0.95rem;
}

.main-navbar-container .container-fluid {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
</style>

<nav class="main-navbar-container">
    <div class="container-fluid px-2">
        <!-- Desktop Navigation -->
        <div class="d-none d-lg-block">
            <ul class="nav justify-content-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-users me-1"></i>Members
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="our-team.php">Our Team</a></li>
                        <li><a class="dropdown-item" href="management-team.php">Management Team</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="aboutus.php">
                        <i class="fas fa-info-circle me-1"></i>About Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="upcoming-event.php">
                        <i class="fas fa-calendar-alt me-1"></i>Event
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="donation-form.php">
                        <i class="fas fa-hand-holding-heart me-1"></i>Donation
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="our-network.php">
                        <i class="fas fa-network-wired me-1"></i>Our Network
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="news.php">
                        <i class="fas fa-newspaper me-1"></i>News
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users-apply-form.php">
                        <i class="fas fa-user-plus me-1"></i>Join Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact-us.php">
                        <i class="fas fa-envelope me-1"></i>Contact Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gallery.php">
                        <i class="fas fa-images me-1"></i>Gallery
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="sponsors.php">
                        <i class="fas fa-handshake me-1"></i>Sponsored
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="user-login.php">User Login</a></li>
                        <li><a class="dropdown-item" href="coordinator-login.php">Coordinator Login</a></li>
                        <li><a class="dropdown-item" href="admin-login.php">Admin Login</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        
        <!-- Mobile Navigation -->
        <div class="d-lg-none">
            <button class="btn btn-primary mobile-nav-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Navigation Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-white" id="mobileMenuLabel">
            <img src="img/logo.png" alt="Logo" style="height: 30px; margin-right: 10px;">
            Menu
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="mobile-nav">
            <a class="nav-link mobile-nav-link" href="index.php">
                <i class="fas fa-home"></i>Home
            </a>
            
            <!-- Members Accordion -->
            <div class="accordion accordion-flush" id="mobileAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#membersCollapse" aria-expanded="false" aria-controls="membersCollapse">
                            <i class="fas fa-users me-2"></i>Members
                        </button>
                    </h2>
                    <div id="membersCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                        <div class="accordion-body">
                            <a class="nav-link mobile-nav-link" href="our-team.php">Our Team</a>
                            <a class="nav-link mobile-nav-link" href="management-team.php">Management Team</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <a class="nav-link mobile-nav-link" href="aboutus.php">
                <i class="fas fa-info-circle"></i>About Us
            </a>
            
            <a class="nav-link mobile-nav-link" href="upcoming-event.php">
                <i class="fas fa-calendar-alt"></i>Event
            </a>
            
            <a class="nav-link mobile-nav-link" href="donation-form.php">
                <i class="fas fa-hand-holding-heart"></i>Donation
            </a>
            
            <a class="nav-link mobile-nav-link" href="our-network.php">
                <i class="fas fa-network-wired"></i>Our Network
            </a>
            
            <a class="nav-link mobile-nav-link" href="news.php">
                <i class="fas fa-newspaper"></i>News
            </a>
            
            <a class="nav-link mobile-nav-link" href="users-apply-form.php">
                <i class="fas fa-user-plus"></i>Join Us
            </a>
            
            <a class="nav-link mobile-nav-link" href="contact-us.php">
                <i class="fas fa-envelope"></i>Contact Us
            </a>
            
            <a class="nav-link mobile-nav-link" href="gallery.php">
                <i class="fas fa-images"></i>Gallery
            </a>
            
            <a class="nav-link mobile-nav-link" href="sponsors.php">
                <i class="fas fa-handshake"></i>Sponsored
            </a>
            
            <!-- Login Accordion -->
            <div class="accordion accordion-flush" id="mobileAccordion2">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#loginCollapse" aria-expanded="false" aria-controls="loginCollapse">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </h2>
                    <div id="loginCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion2">
                        <div class="accordion-body">
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