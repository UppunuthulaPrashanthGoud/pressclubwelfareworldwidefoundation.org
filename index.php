<?php
session_start();
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Fetch site configuration
$site_config = getSiteConfig();
$facebook_url = $site_config['facebook_url'] ?? '';
$site_url = $site_config['website_url'] ?? SITE_URL; // Fallback to defined SITE_URL if needed

// Get sliders
$stmt = $db->prepare("SELECT * FROM sliders WHERE status = 'active' ORDER BY sort_order ASC LIMIT 10");
$stmt->execute();
$sliders = $stmt->fetchAll();

// Get about content
$stmt = $db->prepare("SELECT * FROM about_content WHERE status = 'active' ORDER BY sort_order ASC LIMIT 1");
$stmt->execute();
$about_content = $stmt->fetch();

// Get president message
$stmt = $db->prepare("SELECT * FROM president_message WHERE status = 'active' LIMIT 1");
$stmt->execute();
$president_message = $stmt->fetch();

// Get objectives
$stmt = $db->prepare("SELECT * FROM objectives WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6");
$stmt->execute();
$objectives = $stmt->fetchAll();

// Get youtube videos
$stmt = $db->prepare("SELECT * FROM youtube_videos WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$youtube_videos = $stmt->fetchAll();

// Get gallery images
$stmt = $db->prepare("SELECT * FROM gallery ORDER BY id DESC LIMIT 6");
$stmt->execute();
$gallery_images = $stmt->fetchAll();

// Get testimonials
$stmt = $db->prepare("SELECT * FROM testimonials WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$testimonials = $stmt->fetchAll();

// Get recent users with their specific designation
$stmt = $db->prepare("
    SELECT u.*, md.designation_hindi 
    FROM users u 
    LEFT JOIN membership_designations md 
        ON u.membership_type = md.membership_type 
        AND u.designation = md.designation 
    WHERE u.status = 'approved' 
        AND (md.status = 'active' OR md.status IS NULL)
    ORDER BY u.created_at DESC 
    LIMIT 16
");
$stmt->execute();
$recent_users = $stmt->fetchAll();

// Get management team
$stmt = $db->prepare("SELECT * FROM team_members WHERE member_type = 'management' AND status = 'active' ORDER BY sort_order ASC, created_at DESC LIMIT 16");
$stmt->execute();
$management_team = $stmt->fetchAll();

// Get recent activities
$stmt = $db->prepare("SELECT * FROM recent_activities WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_activities = $stmt->fetchAll();

// Get recent news
$stmt = $db->prepare("SELECT * FROM news WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$news = $stmt->fetchAll();

// Get upcoming events
$currentDate = date('Y-m-d');
$stmt = $db->prepare("SELECT * FROM events WHERE status = ? AND event_date >= ? ORDER BY event_date ASC, event_time ASC LIMIT 3");
$stmt->execute(['active', $currentDate]);
$upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
include 'navbar.php'; 
?>

<main>
    <?php include 'includes/hero-slider.php'; ?>
    <?php include 'includes/gallery-section.php'; ?>
    <?php include 'includes/about-section.php'; ?>

    <!-- MANAGEMENT TEAM SECTION -->
    <?php include 'includes/management-team.php'; ?>

    <?php include 'includes/upcoming-events.php'; ?>

    <!-- NEW: AWARDS LIST SECTION (Moved above the slider) -->
    <?php include 'includes/awards-list.php'; ?>

        <!-- National COMMITTEE SECTION -->
    <?php include 'includes/national-committee.php'; ?>

    <!-- HONORARY AWARDS SLIDER -->
    <?php include 'includes/honorary-awards-slider.php'; ?>
     
        <!-- NEW: WHY CHOOSE US SECTION -->
    <?php include 'includes/why-choose-us.php'; ?>
    <!-- ADVERTISEMENT SLIDER -->
    <?php include 'includes/advertisement-slider.php'; ?>

    <!-- STATE COMMITTEE SECTION -->
    <?php include 'includes/state-committee.php'; ?>

    <!-- CERTIFICATE SLIDER -->
    <?php include 'includes/affiliation-slider.php'; ?>

    <!-- MEMBER COMMITTEE SECTION -->
    <?php include 'includes/member-committee.php'; ?>

    <?php include 'includes/social-media-slider.php'; ?>
    
    <!-- SPONSOR SLIDER -->
    <?php include 'includes/sponsor-slider.php'; ?>
    
    <!-- Video SECTION -->
    <?php include 'includes/videos-section.php'; ?>

    <!-- PARTNER SLIDER -->
    <?php include 'includes/partner-slider.php'; ?>
    
    <?php include 'includes/e-certificates-section.php'; ?>

    
    <?php include 'includes/news-section.php'; ?>
</main>

<style>
/* Keeping your custom styling for the homepage */
.about-section .section-content .content-html { line-height: 1.6; }
.ideals-section { position: relative; overflow: hidden; }
.ideals-title { font-family: "Bakbak One", sans-serif; font-size: 2.2rem; font-weight: 700; color: var(--primary-color, #007bff); }
</style>

<?php include 'footer.php'; ?>
