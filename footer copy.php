<?php
require_once 'config/config.php'; // Include config for database connection and SITE_URL

// Initialize database connection
$db = null;
try {
    $db = getDbConnection();
} catch (PDOException $e) {
    logError("Failed to establish database connection in footer: " . $e->getMessage());
}

// Initialize variables
$address = null;
$phone1 = null;
$phone2 = null;
$email = null;
$working_hours = null;
$footer_logo = null;
$facebook_url = null;
$twitter_url = null;
$instagram_url = null;
$youtube_url = null;
$footer_content = null;

// Fetch site configuration from site_config
if ($db instanceof PDO) {
    try {
        $stmt = $db->prepare("SELECT address, phone1, phone2, email, working_hours, footer_logo, facebook_url, twitter_url, instagram_url, youtube_url FROM site_config WHERE id = 1 LIMIT 1");
        $stmt->execute();
        $site_config = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($site_config) {
            $address = $site_config['address'];
            $phone1 = $site_config['phone1'];
            $phone2 = $site_config['phone2'];
            $email = $site_config['email'];
            $working_hours = $site_config['working_hours'];
            $footer_logo = !empty($site_config['footer_logo']) ? SITE_URL . '/img/site_config/' . $site_config['footer_logo'] : null;
            $facebook_url = $site_config['facebook_url'];
            $twitter_url = $site_config['twitter_url'];
            $instagram_url = $site_config['instagram_url'];
            $youtube_url = $site_config['youtube_url'];
        }
    } catch (Exception $e) {
        logError("Error fetching site_config in footer: " . $e->getMessage());
    }
}

// Fetch footer content from footer_settings for about_ndf
if ($db instanceof PDO) {
    try {
        $stmt = $db->prepare("SELECT content FROM footer_settings WHERE section_name = 'about_ndf' AND status = 'active' LIMIT 1");
        $stmt->execute();
        $footer_text = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($footer_text) {
            $footer_content = htmlspecialchars_decode($footer_text['content'], ENT_QUOTES);
        }
    } catch (Exception $e) {
        logError("Error fetching footer text from footer_settings: " . $e->getMessage());
    }
}
?>

<footer class="footer-section">
    <div class="container">
        <!-- Footer CTA -->
        <div class="footer-cta pt-5 pb-4">
            <div class="row g-4">
                <div class="col-xl-3 col-md-6 text-center">
                    <div class="single-cta">
                        <div class="cta-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="cta-text">
                            <h4>Our Address</h4>
                            <span><?php echo $address ? htmlspecialchars($address, ENT_QUOTES, 'UTF-8') : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 text-center">
                    <div class="single-cta">
                        <div class="cta-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="cta-text">
                            <h4>Call Us</h4>
                            <span><?php echo $phone1 ? htmlspecialchars($phone1, ENT_QUOTES, 'UTF-8') : 'N/A'; ?><?php echo $phone2 ? ' / ' . htmlspecialchars($phone2, ENT_QUOTES, 'UTF-8') : ''; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 text-center">
                    <div class="single-cta">
                        <div class="cta-icon">
                            <i class="far fa-envelope-open"></i>
                        </div>
                        <div class="cta-text">
                            <h4>Mail Us</h4>
                            <span><?php echo $email ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 text-center">
                    <div class="single-cta">
                        <div class="cta-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="cta-text">
                            <h4>Working Hours</h4>
                            <span><?php echo $working_hours ? htmlspecialchars($working_hours, ENT_QUOTES, 'UTF-8') : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Content -->
        <div class="footer-content pt-5 pb-5">
            <div class="row g-4">
                <!-- Organization Info -->
                <div class="col-xl-4 col-lg-4 mb-4">
                    <div class="footer-widget">
                        <div class="footer-logo mb-4">
                            <a href="index.php">
                                <img src="<?php echo $footer_logo ? htmlspecialchars($footer_logo, ENT_QUOTES, 'UTF-8') : '#'; ?>" class="img-fluid" alt="National Development Foundation (NDF)">
                            </a>
                        </div>
                        <div class="footer-text mb-4">
                            <?php echo $footer_content ?: 'N/A'; ?>
                        </div>
                        <div class="footer-social-icon">
                            <span>Follow Us</span>
                            <div class="social-links">
                                <?php if (!empty($facebook_url)): ?>
                                    <a href="<?php echo htmlspecialchars($facebook_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="facebook-bg">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($twitter_url)): ?>
                                    <a href="<?php echo htmlspecialchars($twitter_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="twitter-bg">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($instagram_url)): ?>
                                    <a href="<?php echo htmlspecialchars($instagram_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="instagram-bg">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($youtube_url)): ?>
                                    <a href="<?php echo htmlspecialchars($youtube_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="youtube-bg">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-xl-2 col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <div class="footer-widget-heading">
                            <h3>Quick Links</h3>
                        </div>
                        <ul class="footer-links">
                            <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                            <li><a href="aboutus.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                            <li><a href="our-team.php"><i class="fas fa-chevron-right"></i> Our Team</a></li>
                            <li><a href="upcoming-event.php"><i class="fas fa-chevron-right"></i> Events</a></li>
                            <li><a href="gallery.php"><i class="fas fa-chevron-right"></i> Gallery</a></li>
                            <li><a href="contact-us.php"><i class="fas fa-chevron-right"></i> Contact</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Services -->
                <div class="col-xl-2 col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <div class="footer-widget-heading">
                            <h3>Our Services</h3>
                        </div>
                        <ul class="footer-links">
                            <li><a href="users-apply-form.php"><i class="fas fa-chevron-right"></i> Membership</a></li>
                            <li><a href="donation_form.php"><i class="fas fa-chevron-right"></i> Donations</a></li>
                            <!-- <li><a href="our-project.php"><i class="fas fa-chevron-right"></i> Projects</a></li> -->
                            <li><a href="crowdfunding.php"><i class="fas fa-chevron-right"></i> Crowdfunding</a></li>
                            <li><a href="complain-form.php"><i class="fas fa-chevron-right"></i> Complaints</a></li>
                            <!-- <li><a href="id-card-download.php"><i class="fas fa-chevron-right"></i> ID Card</a></li> -->
                        </ul>
                    </div>
                </div>
                
                <!-- Facebook Widget -->
                <div class="col-xl-4 col-lg-4 col-md-12 mb-4">
                    <div class="footer-widget">
                        <div class="footer-widget-heading">
                            <h3>Connect With Us</h3>
                        </div>
                        <div class="facebook-widget">
                            <?php if (!empty($facebook_url)): ?>
                                <iframe src="https://www.facebook.com/plugins/page.php?href=<?php echo urlencode($facebook_url); ?>&tabs=timeline&width=340&height=250&small_header=true&hide_cover=false" 
                                        width="100%" height="250" style="border:none;overflow:hidden" scrolling="no" frameborder="0" 
                                        allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                                </iframe>
                            <?php else: ?>
                                <p>N/A</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Copyright Area -->
    <div class="copyright-area">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-6 col-lg-6 text-center text-lg-start">
                    <div class="copyright-text">
                        <p>Copyright &copy; <?php echo date('Y'); ?>, All Rights Reserved 
                            <a href="index.php">Kisanex Foundation</a>
                        </p>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6 text-center text-lg-end">
                    <div class="footer-menu">
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item"><a href="term_condition.php">Terms & Conditions</a></li>
                            <li class="list-inline-item"><a href="privacy-policy.php">Privacy Policy</a></li>
                            <li class="list-inline-item"><a href="disclaimer.php">Disclaimer</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">
                        <i class="fas fa-heart text-danger"></i> 
                        Designed & Developed by 
                        <a href="https://www.onlinegrowthhub.in" target="_blank" class="text-decoration-none">
                            <strong>Online Growth Hub</strong>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS (Animate on Scroll) JS -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<!-- Owl Carousel JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Include Animate.css for animations -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" integrity="sha512-c42qTSw/wPZ3/5LBzD+Bw5f7bSF2oxou6wEb+I/lqeaKV5FDIfMvvRp772y4jcJLKuGUOpbJMdg/BTl50fJYAw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script>
// Initialize AOS
AOS.init({
    duration: 1000,
    once: true,
});

// Initialize Owl Carousel
$(document).ready(function() {
    $("#news-slider").owlCarousel({
        items: 4,
        itemsDesktop: [1199, 3],
        itemsDesktopSmall: [980, 2],
        itemsMobile: [600, 1],
        navigation: true,
        navigationText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        pagination: true,
        autoPlay: true
    });
    
    // Alternative Google Translate initialization
    if (typeof googleTranslateElementInit !== 'function') {
        window.googleTranslateElementInit = function() {
            try {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'en,hi,bn,te,ta,ml,kn,gu,mr,pa,or,as,ur,ne,si',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    autoDisplay: false,
                    multilanguagePage: true
                }, 'google_translate_element');
            } catch (e) {
                console.log('Google Translate initialization error:', e);
            }
        };
        
        // Load Google Translate script
        var gtScript = document.createElement('script');
        gtScript.type = 'text/javascript';
        gtScript.async = true;
        gtScript.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        gtScript.onerror = function() {
            console.log('Failed to load Google Translate script');
            // Retry after 2 seconds
            setTimeout(function() {
                var retryScript = document.createElement('script');
                retryScript.type = 'text/javascript';
                retryScript.async = true;
                retryScript.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                document.getElementsByTagName('head')[0].appendChild(retryScript);
            }, 2000);
        };
        document.getElementsByTagName('head')[0].appendChild(gtScript);
    }
});

// Mobile offcanvas auto-close on link click
document.addEventListener('DOMContentLoaded', function() {
    const offcanvasElement = document.getElementById('mobileNavOffcanvas');
    if (offcanvasElement) {
        const navLinks = offcanvasElement.querySelectorAll('a[data-bs-dismiss="offcanvas"]');
        
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                if (offcanvas) {
                    offcanvas.hide();
                }
            });
        });
    }
});
</script>

</body>
</html>