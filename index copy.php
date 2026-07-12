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
    LIMIT 5
");
$stmt->execute();
$recent_users = $stmt->fetchAll();

// Get management team
$stmt = $db->prepare("SELECT * FROM team_members WHERE member_type = 'management' AND status = 'active' ORDER BY sort_order ASC, created_at DESC LIMIT 5");
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
$stmt = $db->prepare("SELECT * FROM events WHERE status = ? AND event_date >= ? ORDER BY event_date ASC, event_time ASC LIMIT 5");
$stmt->execute(['active', $currentDate]);
$upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
include 'navbar.php'; 
?>

<main>
    <?php include 'includes/hero-slider.php'; ?>

    <?php include 'includes/about-section.php'; ?>
    
    <?php include 'includes/president-message.php'; ?>

    <?php include 'includes/objectives-slider.php'; ?>

    <?php include 'includes/team-members-section.php'; ?>

    <?php include 'includes/management-team-section.php'; ?>

    <?php include 'includes/videos-section.php'; ?>

    <?php include 'includes/gallery-section.php'; ?>

    <?php include 'includes/news-section.php'; ?>

    <?php include 'includes/testimonials.php'; ?>
</main>

<style>
.about-section .section-content .content-html {
    line-height: 1.6;
}
.about-section .section-content .content-html p {
    margin-bottom: 1rem;
}
.about-section .section-content .content-html ul,
.about-section .section-content .content-html ol {
    margin-left: 2rem;
    margin-bottom: 1rem;
}
.about-section .section-content .content-html li {
    margin-bottom: 0.5rem;
}
.president-message .message-content .content-html {
    line-height: 1.6;
}
.president-message .message-content .content-html p {
    margin-bottom: 1rem;
}
.president-message .message-content .content-html ul,
.president-message .message-content .content-html ol {
    margin-left: 2rem;
    margin-bottom: 1rem;
}
.president-message .message-content .content-html li {
    margin-bottom: 0.5rem;
}
.activity-item .activity-content .content-html {
    line-height: 1.6;
}
.activity-item .activity-content .content-html ul,
.activity-item .activity-content .content-html ol {
    margin-left: 2rem;
    margin-bottom: 1rem;
}
.activity-item .activity-content .content-html li {
    margin-bottom: 0.5rem;
}

.ideals-section {
    position: relative;
    overflow: hidden;
}

.ideals-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="pattern" patternUnits="userSpaceOnUse" width="20" height="20"><circle cx="10" cy="10" r="1" fill="rgba(0,123,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23pattern)"/></svg>');
    pointer-events: none;
}

.ideals-image-container {
    position: relative;
    display: inline-block;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,123,255,0.3);
    transition: all 0.3s ease;
}

.ideals-image-container::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.3) 50%, transparent 60%);
    transform: rotate(45deg);
    animation: idealsImageShimmer 3s infinite;
    pointer-events: none;
    z-index: 2;
}

.ideals-image-container:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 15px 40px rgba(0,123,255,0.4);
}

@keyframes idealsImageShimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.ideals-image {
    width: 100%;
    max-width: 350px;
    height: auto;
    border-radius: 15px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.ideals-title {
    font-family: "Bakbak One", sans-serif;
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--primary-color, #007bff);
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    position: relative;
}

.ideals-title::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color, #007bff), var(--secondary-color, #6c757d));
    border-radius: 2px;
}

.parents-names {
    background: rgba(255,255,255,0.8);
    padding: 15px;
    border-radius: 10px;
    border: 1px solid rgba(0,123,255,0.2);
    backdrop-filter: blur(5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.parent-info {
    font-size: 1.1rem;
    font-weight: 500;
    font-family: "Teko", sans-serif;
    color: var(--text-color, #333);
    transition: all 0.3s ease;
    padding: 5px 0;
}

.parent-info:hover {
    color: var(--primary-color, #007bff);
    transform: translateX(5px);
}

.parent-name {
    font-weight: 700;
    color: var(--primary-color, #007bff);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.ideals-description .lead {
    font-size: 1.15rem;
    line-height: 1.7;
    font-weight: 400;
    text-align: justify;
}

.ideals-message blockquote {
    font-size: 1.1rem;
    line-height: 1.6;
    margin: 0;
    position: relative;
}

.ideals-message blockquote::before {
    content: '"';
    font-size: 4rem;
    color: var(--primary-color, #007bff);
    position: absolute;
    left: -20px;
    top: -15px;
    font-family: Georgia, serif;
    opacity: 0.3;
}

@media (max-width: 991.98px) {
    .ideals-title {
        font-size: 1.8rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .ideals-title::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .ideals-image {
        max-width: 280px;
        margin: 0 auto 1rem;
        display: block;
    }
    
    .parent-info {
        font-size: 1rem;
        text-align: center;
    }
    
    .ideals-description .lead {
        font-size: 1rem;
        text-align: center;
    }
}

@media (max-width: 767.98px) {
    .ideals-section {
        padding: 2rem 0 !important;
    }
    
    .ideals-title {
        font-size: 1.6rem;
    }
    
    .ideals-image {
        max-width: 250px;
    }
    
    .parents-names {
        padding: 12px;
        margin-bottom: 1rem;
    }
    
    .parent-info {
        font-size: 0.95rem;
        margin-bottom: 8px;
    }
    
    .ideals-description .lead {
        font-size: 0.95rem;
    }
    
    .ideals-message blockquote {
        font-size: 1rem;
        padding: 12px;
    }
}

@media (max-width: 575.98px) {
    .ideals-title {
        font-size: 1.4rem;
    }
    
    .ideals-image {
        max-width: 220px;
    }
    
    .parent-info {
        font-size: 0.9rem;
    }
    
    .ideals-description .lead {
        font-size: 0.9rem;
    }
    
    .ideals-message blockquote::before {
        font-size: 3rem;
        left: -15px;
        top: -10px;
    }
}

@media (prefers-reduced-motion: no-preference) {
    .ideals-content {
        opacity: 0;
        transform: translateX(30px);
        animation: slideInFromRight 0.8s ease-out 0.3s forwards;
    }
    
    .ideals-image-container {
        opacity: 0;
        transform: translateX(-30px);
        animation: slideInFromLeft 0.8s ease-out forwards;
    }
}

@keyframes slideInFromRight {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInFromLeft {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@media print {
    .ideals-section {
        background: white !important;
        box-shadow: none !important;
    }
    
    .ideals-image-container::before {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const idealsSection = document.querySelector('.ideals-section');
    if (idealsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.2 });
        
        observer.observe(idealsSection);
    }
    
    const idealsImage = document.querySelector('.ideals-image');
    if (idealsImage) {
        idealsImage.addEventListener('click', function() {
            if (this.requestFullscreen) {
                this.requestFullscreen();
            } else if (this.webkitRequestFullscreen) {
                this.webkitRequestFullscreen();
            } else if (this.msRequestFullscreen) {
                this.msRequestFullscreen();
            }
        });
        idealsImage.style.cursor = 'pointer';
        idealsImage.title = 'Click to view full screen';
    }
});

function toggleReadMore() {
    const moreText = document.getElementById('more-text');
    const readMoreBtn = document.getElementById('read-more-btn');
    
    if (moreText && readMoreBtn) {
        if (moreText.style.display === 'none') {
            moreText.style.display = 'inline';
            readMoreBtn.textContent = ' Read Less';
        } else {
            moreText.style.display = 'none';
            readMoreBtn.textContent = '... Read More';
        }
    }
}
</script>

<?php include 'footer.php'; ?>