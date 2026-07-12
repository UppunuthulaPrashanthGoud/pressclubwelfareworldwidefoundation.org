<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Check if database connection is successful
if (!$db) {
    error_log("Database connection failed in index.php");
    die("Database connection error. Please try again later.");
}

// Fetch site configuration
$site_config = getSiteConfig();
$facebook_url = $site_config['facebook_url'] ?? '';
$site_url = $site_config['website_url'] ?? SITE_URL;

// Get about content
$stmt = $db->prepare("SELECT * FROM about_content WHERE status = 'active' ORDER BY sort_order ASC LIMIT 1");
$stmt->execute();
$about_content = $stmt->fetch();

// Get recent news for slider
$stmt = $db->prepare("SELECT id, title, image, created_at FROM news WHERE status = 'active' ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$news_slider = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get news for ticker (more recent news items)
$stmt = $db->prepare("SELECT id, title, created_at FROM news WHERE status = 'active' ORDER BY created_at DESC LIMIT 20");
$stmt->execute();
$news_ticker = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sanitize news slider data
foreach ($news_slider as &$news_item) {
    $news_item['title'] = htmlspecialchars($news_item['title'], ENT_QUOTES, 'UTF-8');
    $news_item['image'] = !empty($news_item['image']) ? htmlspecialchars($news_item['image'], ENT_QUOTES, 'UTF-8') : '';
}

// Sanitize news ticker data
foreach ($news_ticker as &$ticker_item) {
    $ticker_item['title'] = htmlspecialchars($ticker_item['title'], ENT_QUOTES, 'UTF-8');
}

// Get about content with error handling
$stmt = $db->prepare("SELECT * FROM about_content WHERE status = 'active' ORDER BY sort_order ASC");
$stmt->execute();
$aboutSections = $stmt->fetchAll();

// Get youtube videos
$stmt = $db->prepare("SELECT * FROM youtube_videos WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$youtube_videos = $stmt->fetchAll();

// Get gallery images
$stmt = $db->prepare("SELECT * FROM gallery ORDER BY id DESC LIMIT 6");
$stmt->execute();
$gallery_images = $stmt->fetchAll();

include 'header.php'; 
include 'navbar.php'; 
?>

<!-- NEWS TICKER SECTION -->
<?php include 'includes/news-ticker.php'; ?>

<!-- HERO SLIDER SECTION -->
<?php include 'includes/hero-slider.php'; ?>

<!-- Committees SECTION -->
<?php include 'includes/national-committee.php'; ?>
<?php include 'includes/state-committee.php'; ?>
<?php include 'includes/district-committee.php'; ?>
<?php include 'includes/member-committee.php'; ?>

<!-- ABOUT SECTION -->
<?php include 'includes/about-section.php'; ?>

<!-- Media SECTION -->
 
<?php include 'includes/gallery-section.php'; ?>
<?php include 'includes/videos-section.php'; ?>

<?php include 'footer.php'; ?>