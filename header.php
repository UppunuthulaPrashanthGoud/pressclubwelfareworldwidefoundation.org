<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/config.php';

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
        $site_header_logo = isset($siteConfig['header_logo']) && !empty($siteConfig['header_logo']) ? SITE_URL . '/img/site_config/' . $siteConfig['header_logo'] : '';
        $site_footer_logo = SITE_URL . '/img/site_config/' . $siteConfig['footer_logo'];
        $site_icon = SITE_URL . '/img/site_config/' . $siteConfig['site_icon'];
        $active_style = $siteConfig['active_style'] ?? 'style.css';
        $website_url = !empty($page_url) ? $page_url : SITE_URL;
        
        if (!empty($page_meta_image)) {
            $og_image = $page_meta_image;
        } elseif (!empty($site_header_logo)) {
            $og_image = $site_header_logo;
        } elseif (!empty($site_footer_logo)) {
            $og_image = $site_footer_logo;
        } else {
            $og_image = '';
        }
    } else {
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
} catch (PDOException $e) {
    error_log("Error fetching site config: " . $e->getMessage());
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
    
    <base href="<?php echo rtrim(SITE_URL, '/') . '/'; ?>">
    
    <title><?php echo htmlspecialchars($meta_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="<?php echo htmlspecialchars($meta_author); ?>">
    
    <?php if (!empty($site_icon)): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($site_icon); ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo htmlspecialchars($site_icon); ?>">
    <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($site_icon); ?>">
    <?php endif; ?>
    
    <meta property="og:title" content="<?php echo htmlspecialchars($meta_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_title); ?>">
    <?php if (!empty($website_url)): ?>
    <meta property="og:url" content="<?php echo htmlspecialchars($website_url); ?>">
    <?php endif; ?>
    <?php if (!empty($og_image)): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($site_title); ?>">
    <?php endif; ?>
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($meta_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <?php if (!empty($og_image)): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta name="twitter:image:alt" content="<?php echo htmlspecialchars($site_title); ?>">
    <?php endif; ?>
    <?php if (!empty($site_twitter_url) && $site_twitter_url !== '#'): ?>
        <meta name="twitter:site" content="<?php echo htmlspecialchars(str_replace('https://twitter.com/', '@', $site_twitter_url)); ?>">
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <link rel="stylesheet" href="css/<?php echo htmlspecialchars($active_style); ?>">
    
    <style>
        .header-section {
            position: relative;
            padding: 0;
            margin: 0;
            background: var(--white-bg);
        }

        .header-section .container {
            padding: 0;
            max-width: 100%;
        }

        .header-logo {
            display: block;
            width: 100%;
            line-height: 0;
        }

        .header-logo img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }

        .mobile-nav-link {
            display: block;
            padding: 10px 0;
            text-decoration: none;
            color: var(--text-white) !important;
            border-bottom: 1px solid var(--overlay-light);
            transition: all 0.3s ease;
        }
        .mobile-nav-link:hover {
            color: var(--gold-color) !important;
            padding-left: 10px;
        }
        .mobile-nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .accordion-button {
            color: var(--text-white);
            background-color: transparent;
            border: none;
            padding: 15px 0;
            border-bottom: 1px solid var(--overlay-light);
        }
        .accordion-button:not(.collapsed) {
            color: var(--gold-color);
            background-color: transparent;
            box-shadow: none;
        }
        .accordion-button::after {
            filter: invert(1);
        }
        .offcanvas {
            background: var(--gradient-navbar);
        }

        .google-translate-container {
            display: inline-block;
            margin-left: 15px;
        }

        #google_translate_element .goog-te-gadget-simple {
            background-color: rgba(255,255,255,0.95);
            border: 1px solid var(--border-light);
            border-radius: 4px;
            padding: 6px 10px;
            box-shadow: 0 2px 5px var(--shadow-light);
        }

        .contact-item a:hover {
            color: var(--primary-color) !important;
            transform: translateY(-1px);
        }

        /* Header Bottom Bar Styling - Aligned with Navy Theme */
        .header-bottom-bar {
            background: var(--gradient-navbar);
            padding: 12px 0;
            border-top: 1px solid var(--overlay-light);
        }

        .header-quick-links {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 25px;
        }

        .quick-link-item {
            display: flex;
            align-items: center;
            color: var(--text-white);
            font-size: 14px;
            font-weight: 500;
        }

        .quick-link-item i {
            margin-right: 8px;
            color: var(--gold-color);
            font-size: 16px;
        }

        .quick-link-item a {
            color: var(--text-white);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .quick-link-item a:hover {
            color: var(--gold-color);
        }

        .header-social-links {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: var(--overlay-medium);
            border: 1px solid var(--overlay-light);
            border-radius: 50%;
            color: var(--text-white);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .header-social-links a:hover {
            background: var(--white-bg);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-dark);
        }

        .floating-btn-phone {
            background: linear-gradient(45deg, #dc3545, #c82333) !important;
            border-radius: 50% !important;
            width: 60px !important;
            height: 60px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }

        .floating-btn-phone:hover {
            background: linear-gradient(45deg, #c82333, #bd2130) !important;
            transform: translateX(-5px) scale(1.1);
        }

        @media (max-width: 768px) {
            .header-quick-links { gap: 15px; }
            .quick-link-item { font-size: 12px; }
            .header-social-links a { width: 28px; height: 28px; font-size: 12px; }
        }
    </style>

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

<div class="floating-buttons" data-aos="fade-up">
    <a href="nominations-apply.php" class="btn floating-btn-problem">
        <i class="fas fa-user-plus me-2"></i>Nominations Form
    </a>
    <?php if (!empty($site_phone1)): ?>
    <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $site_phone1); ?>" class="btn floating-btn-phone" title="Call <?php echo htmlspecialchars($site_phone1); ?>">
        <i class="fas fa-phone"></i>
    </a>
    <?php endif; ?>
    <?php if (!empty($site_phone1)): ?>
    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $site_phone1); ?>" target="_blank" class="btn floating-btn-whatsapp" title="WhatsApp <?php echo htmlspecialchars($site_phone1); ?>">
        <i class="fab fa-whatsapp"></i>
    </a>
    <?php endif; ?>
</div>
<a href="donation-form.php" class="btn floating-btn-donate">
    <i class="fas fa-heart me-2"></i>Donate
</a>

<div class="header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <?php if (!empty($site_header_logo)): ?>
                <a class="header-logo" href="index.php">
                    <img src="<?php echo htmlspecialchars($site_header_logo); ?>" alt="<?php echo htmlspecialchars($site_title); ?> Logo" class="img-fluid">
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="header-bottom-bar">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="header-quick-links">
                    <?php if (!empty($site_phone1)): ?>
                    <div class="quick-link-item">
                        <i class="fas fa-phone-alt"></i>
                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $site_phone1); ?>" title="Call <?php echo htmlspecialchars($site_phone1); ?>">
                            <span><?php echo htmlspecialchars($site_phone1); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($site_email)): ?>
                    <div class="quick-link-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo htmlspecialchars($site_email); ?>" title="Email <?php echo htmlspecialchars($site_email); ?>">
                            <span><?php echo htmlspecialchars($site_email); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="header-social-links">
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
                    
                    <div class="google-translate-container">
                        <div id="google_translate_element"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>