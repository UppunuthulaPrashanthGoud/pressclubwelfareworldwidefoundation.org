<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/config.php';

// Check for custom meta data from including pages
$page_meta_title = isset($page_meta_title) ? $page_meta_title : '';
$page_meta_description = isset($page_meta_description) ? $page_meta_description : '';
$page_meta_image = isset($page_meta_image) ? $page_meta_image : '';
$page_url = isset($page_url) ? $page_url : '';

// Fetch site configuration from the database
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM site_config WHERE id = 1");
    $stmt->execute();
    $siteConfig = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($siteConfig) {
        $site_title = $siteConfig['site_title'];
        $site_subtitle = $siteConfig['site_subtitle'];
        $meta_title = !empty($page_meta_title) ? $page_meta_title : $siteConfig['meta_title'];
        $meta_description = !empty($page_meta_description) ? $page_meta_description : $siteConfig['meta_description'];
        $meta_keywords = $siteConfig['meta_keywords'];
        $meta_author = $siteConfig['meta_author'];
        $site_address = $siteConfig['address'];
        $site_phone1 = $siteConfig['phone1'];
        $site_phone2 = $siteConfig['phone2'];
        $site_email = $siteConfig['email'];
        $site_working_hours = $siteConfig['working_hours'];
        $site_facebook_url = $siteConfig['facebook_url'];
        $site_twitter_url = $siteConfig['twitter_url'];
        $site_instagram_url = $siteConfig['instagram_url'];
        $site_youtube_url = $siteConfig['youtube_url'];
        $site_header_logo = isset($siteConfig['header_logo']) && !empty($siteConfig['header_logo']) ? 'img/site_config/' . $siteConfig['header_logo'] : '';
        $site_footer_logo = 'img/site_config/' . $siteConfig['footer_logo'];
        $site_icon = 'img/site_config/' . $siteConfig['site_icon'];
        $active_style = $siteConfig['active_style'] ?? 'style.css';
        $website_url = !empty($page_url) ? $page_url : SITE_URL;
        $og_image = !empty($page_meta_image) ? $page_meta_image : $site_footer_logo;
    } else {
        // If no config found, set empty values
        $site_title = '';
        $site_subtitle = '';
        $meta_title = !empty($page_meta_title) ? $page_meta_title : '';
        $meta_description = !empty($page_meta_description) ? $page_meta_description : '';
        $meta_keywords = '';
        $meta_author = '';
        $site_address = '';
        $site_phone1 = '';
        $site_phone2 = '';
        $site_email = '';
        $site_working_hours = '';
        $site_facebook_url = '';
        $site_twitter_url = '';
        $site_instagram_url = '';
        $site_youtube_url = '';
        $site_header_logo = '';
        $site_footer_logo = '';
        $site_icon = '';
        $active_style = 'style.css';
        $website_url = !empty($page_url) ? $page_url : '';
        $og_image = !empty($page_meta_image) ? $page_meta_image : '';
    }
} catch (PDOException $e) {
    error_log("Error fetching site config: " . $e->getMessage());
    // Set empty values on database error
    $site_title = '';
    $site_subtitle = '';
    $meta_title = !empty($page_meta_title) ? $page_meta_title : '';
    $meta_description = !empty($page_meta_description) ? $page_meta_description : '';
    $meta_keywords = '';
    $meta_author = '';
    $site_address = '';
    $site_phone1 = '';
    $site_phone2 = '';
    $site_email = '';
    $site_working_hours = '';
    $site_facebook_url = '';
    $site_twitter_url = '';
    $site_instagram_url = '';
    $site_youtube_url = '';
    $site_header_logo = '';
    $site_footer_logo = '';
    $site_icon = '';
    $active_style = 'style.css';
    $website_url = !empty($page_url) ? $page_url : SITE_URL;
    $og_image = !empty($page_meta_image) ? $page_meta_image : '';
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($meta_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="<?php echo htmlspecialchars($meta_author); ?>">
    <?php if (!empty($site_icon)): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($site_icon); ?>">
    <?php endif; ?>
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($meta_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <?php if (!empty($og_image)): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <?php endif; ?>
    <?php if (!empty($website_url)): ?>
    <meta property="og:url" content="<?php echo htmlspecialchars($website_url); ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_title); ?>">
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($meta_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <?php if (!empty($og_image)): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <?php endif; ?>
    <?php if (!empty($site_twitter_url) && $site_twitter_url !== '#'): ?>
        <meta name="twitter:site" content="<?php echo htmlspecialchars(str_replace('https://twitter.com/', '@', $site_twitter_url)); ?>">
    <?php endif; ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bakbak+One&family=Teko:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- AOS (Animate on Scroll) CSS -->
    <link rel="stylesheet" href="https://unpkg.aos@next/dist/aos.css" />

    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <!-- Dynamic Custom CSS -->
    <link rel="stylesheet" href="css/<?php echo htmlspecialchars($active_style); ?>">
    
    <!-- Custom Mobile Nav and Logo CSS -->
    <style>
        .mobile-nav-link {
            display: block;
            padding: 10px 0;
            text-decoration: none;
            color: #fff !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        .mobile-nav-link:hover {
            color: #ffd700 !important;
            padding-left: 10px;
        }
        .mobile-nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .accordion-button {
            color: #fff;
            background-color: transparent;
            border: none;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .accordion-button:not(.collapsed) {
            color: #ffd700;
            background-color: transparent;
            box-shadow: none;
        }
        .accordion-button:focus {
            z-index: 3;
            border-color: transparent;
            outline: 0;
            box-shadow: none;
        }
        .accordion-button::after {
            filter: invert(1);
        }
        .accordion-body {
            padding-left: 20px;
        }
        .accordion-item {
            background-color: transparent;
            border: none;
        }
        .offcanvas {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Google Translate Widget Styles */
        .google-translate-container {
            margin-top: 8px;
            padding: 5px 0;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .google-translate-container .skiptranslate {
            display: block !important;
        }
        
        /* Style the Google Translate widget */
        #google_translate_element {
            position: relative;
        }
        
        #google_translate_element .goog-te-gadget {
            font-family: inherit;
            font-size: 11px;
            color: #fff;
        }
        
        #google_translate_element .goog-te-gadget-simple {
            background-color: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
            padding: 4px 6px;
            font-size: 11px;
            line-height: 1.2;
            display: inline-block;
            cursor: pointer;
            zoom: 1;
            min-width: 100px;
        }
        
        #google_translate_element .goog-te-gadget-simple:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        #google_translate_element .goog-te-gadget-simple .goog-te-menu-value {
            color: #fff;
            font-size: 11px;
        }
        
        #google_translate_element .goog-te-gadget-simple .goog-te-menu-value span {
            color: #fff !important;
        }
        
        #google_translate_element .goog-te-gadget-simple .goog-te-menu-value:before {
            content: '\f0ac';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 5px;
            color: #fff;
            font-size: 10px;
        }
        
        /* Hide Google Translate banner */
        .goog-te-banner-frame.skiptranslate {
            display: none !important;
        }
        
        body {
            top: 0px !important;
        }

        /* Logo Full Width Styling */
        .header-logo img {
            width: 100%;
            height: auto;
            max-height: 150px; /* Adjust based on your logo size */
            object-fit: contain; /* Ensures logo fits without distortion */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .google-translate-container {
                text-align: center;
                margin-top: 10px;
            }
            
            #google_translate_element .goog-te-gadget-simple {
                min-width: 80px;
                font-size: 10px;
                padding: 3px 5px;
            }

            .header-logo img {
                max-height: 100px; /* Smaller height for mobile */
            }
        }
    </style>

    <!-- Google Translate Script -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                includedLanguages: 'en,hi,bn,te,ta,ml,kn,gu,mr,pa,or,as,ur,ne',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false,
                gaTrack: true,
                gaId: 'UA-XXXXX-X'
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</head>
<body>

<!-- FLOATING ACTION BUTTONS -->
<div class="floating-buttons" data-aos="fade-up">
    <a href="complain-form.php" class="btn floating-btn-problem">
        <i class="fas fa-exclamation-triangle me-2"></i>Get Support
    </a>
    <?php if (!empty($site_phone1)): ?>
    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $site_phone1); ?>" target="_blank" class="btn floating-btn-whatsapp">
        <i class="fab fa-whatsapp"></i>
    </a>
    <?php endif; ?>
</div>
<a href="donation-form.php" class="btn floating-btn-donate">
    <i class="fas fa-heart me-2"></i>Donate
</a>

<!-- TOP HEADER BAR -->
<div class="header-section">
    <div class="container">
        <div class="row align-items-center">
            <!-- Logo Section -->
            <div class="col-xl-10 col-lg-8 col-md-9 col-sm-12 text-center text-md-start">
                <?php if (!empty($site_header_logo)): ?>
                <a class="header-logo" href="index.php">
                    <img src="<?php echo htmlspecialchars($site_header_logo); ?>" alt="<?php echo htmlspecialchars($site_title); ?> Logo" class="img-fluid">
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Social Icons & Contact Section -->
            <div class="col-xl-2 col-lg-4 col-md-3 col-sm-12">
                <div class="header-right-section">
                    <!-- Contact Info -->
                    <div class="header-contact mb-2">
                        <?php if (!empty($site_phone1)): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <span><?php echo htmlspecialchars($site_phone1); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($site_email)): ?>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($site_email); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Social Icons -->
                    <div class="header-social-icons">
                        <?php if (!empty($site_facebook_url) && $site_facebook_url !== '#'): ?>
                            <a href="<?php echo htmlspecialchars($site_facebook_url); ?>" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($site_twitter_url) && $site_twitter_url !== '#'): ?>
                            <a href="<?php echo htmlspecialchars($site_twitter_url); ?>" target="_blank" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($site_instagram_url) && $site_instagram_url !== '#'): ?>
                            <a href="<?php echo htmlspecialchars($site_instagram_url); ?>" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($site_youtube_url) && $site_youtube_url !== '#'): ?>
                            <a href="<?php echo htmlspecialchars($site_youtube_url); ?>" target="_blank" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Google Translate Widget -->
                    <div class="google-translate-container">
                        <div id="google_translate_element"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header Bottom Bar with Quick Links -->
<div class="header-bottom-bar">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="header-quick-links">
                    <?php if (!empty($site_address)): ?>
                    <div class="quick-link-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($site_address); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($site_working_hours)): ?>
                    <div class="quick-link-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo htmlspecialchars($site_working_hours); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="quick-link-item">
                        <i class="fas fa-users"></i>
                        <span>Join Our Mission</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>