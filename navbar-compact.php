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
                        <i class="fas fa-info-circle me-1"></i>About Us
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="aboutus.php">About Organization</a></li>
                        <li><a class="dropdown-item" href="president-message.php">President's Message</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-users me-1"></i>Our Team
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="our-team.php">All Members</a></li>
                        <li><a class="dropdown-item" href="management-team.php">Management Team</a></li>
                        <li><a class="dropdown-item" href="volunteers.php">Our Volunteers</a></li>
                        <!-- <li><a class="dropdown-item" href="office-bearers.php">Office Bearers</a></li> -->
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users-apply-form.php">
                        <i class="fas fa-user-plus me-1"></i>Apply Now
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-calendar-alt me-1"></i>Events & News
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="upcoming-event.php">Upcoming Events</a></li>
                        <li><a class="dropdown-item" href="news.php">News & Updates</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-images me-1"></i>Gallery
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="gallery.php">Photo Gallery</a></li>
                        <li><a class="dropdown-item" href="our-videos.php">Video Gallery</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-hand-holding-heart me-1"></i>Support Us
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="donation-form.php">Make Donation</a></li>
                        <li><a class="dropdown-item" href="donars.php">Our Supporters</a></li>
                        <li><a class="dropdown-item" href="crowdfunding.php">Crowdfunding</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="documents.php">
                        <i class="fas fa-file-alt me-1"></i>Documents
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact-us.php">
                        <i class="fas fa-envelope me-1"></i>Contact
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
            
            <!-- About Us Accordion -->
            <div class="accordion accordion-flush" id="mobileAccordion1">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#aboutCollapse" aria-expanded="false" aria-controls="aboutCollapse">
                            <i class="fas fa-info-circle me-2"></i>About Us
                        </button>
                    </h2>
                    <div id="aboutCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion1">
                        <div class="accordion-body">
                            <a class="nav-link mobile-nav-link" href="aboutus.php">About Organization</a>
                            <a class="nav-link mobile-nav-link" href="president-message.php">President's Message</a>
                        </div>
                    </div>
                </div>
                
                <!-- Our Team Accordion -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#teamCollapse" aria-expanded="false" aria-controls="teamCollapse">
                            <i class="fas fa-users me-2"></i>Our Team
                        </button>
                    </h2>
                    <div id="teamCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion1">
                        <div class="accordion-body">
                            <a class="nav-link mobile-nav-link" href="our-team.php">All Members</a>
                            <a class="nav-link mobile-nav-link" href="management-team.php">Management Team</a>
                            <a class="nav-link mobile-nav-link" href="volunteers.php">Our Volunteers</a>
                            <!-- <a class="nav-link mobile-nav-link" href="office-bearers.php">Office Bearers</a> -->
                        </div>
                    </div>
                </div>
            </div>
            
            <a class="nav-link mobile-nav-link" href="users-apply-form.php">
                <i class="fas fa-user-plus"></i>Apply Now
            </a>
            
            <!-- Events & News Accordion -->
            <div class="accordion accordion-flush" id="mobileAccordion2">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eventsCollapse" aria-expanded="false" aria-controls="eventsCollapse">
                            <i class="fas fa-calendar-alt me-2"></i>Events & News
                        </button>
                    </h2>
                    <div id="eventsCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion2">
                        <div class="accordion-body">
                            <a class="nav-link mobile-nav-link" href="upcoming-event.php">Upcoming Events</a>
                            <a class="nav-link mobile-nav-link" href="news.php">News & Updates</a>
                        </div>
                    </div>
                </div>
                
                <!-- Gallery Accordion -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#galleryCollapse" aria-expanded="false" aria-controls="galleryCollapse">
                            <i class="fas fa-images me-2"></i>Gallery
                        </button>
                    </h2>
                    <div id="galleryCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion2">
                        <div class="accordion-body">
                            <a class="nav-link mobile-nav-link" href="gallery.php">Photo Gallery</a>
                            <a class="nav-link mobile-nav-link" href="our-videos.php">Video Gallery</a>
                        </div>
                    </div>
                </div>
                
                <!-- Support Us Accordion -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#supportCollapse" aria-expanded="false" aria-controls="supportCollapse">
                            <i class="fas fa-hand-holding-heart me-2"></i>Support Us
                        </button>
                    </h2>
                    <div id="supportCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion2">
                        <div class="accordion-body">
                            <a class="nav-link mobile-nav-link" href="donation-form.php">Make Donation</a>
                            <a class="nav-link mobile-nav-link" href="donars.php">Our Supporters</a>
                            <a class="nav-link mobile-nav-link" href="crowdfunding.php">Crowdfunding</a>
                        </div>
                    </div>
                </div>

                <!-- Login Accordion -->
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
            
            <a class="nav-link mobile-nav-link" href="documents.php">
                <i class="fas fa-file-alt"></i>Documents
            </a>
            
            <a class="nav-link mobile-nav-link" href="contact-us.php">
                <i class="fas fa-envelope"></i>Contact
            </a>
        </nav>
    </div>
</div>