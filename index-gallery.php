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
$stmt = $db->prepare("SELECT * FROM gallery WHERE status = 'active' ORDER BY created_at DESC LIMIT 6");
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
    <!-- NEWS TICKER -->
    <!-- <div class="news-ticker-container" data-aos="fade-down">
        <div class="news-ticker-label">
            <i class="fas fa-bullhorn me-2"></i>Latest Activities
        </div>
        <div class="news-ticker-content">
            <?php if (!empty($recent_activities)): ?>
                <marquee direction="left" scrollamount="5" scrolldelay="50" onmouseover="this.stop();" onmouseout="this.start();">
                    <?php foreach ($recent_activities as $activity): ?>
                        <span class="news-ticker-item">
                            <a href="activity-details.php?id=<?php echo $activity['id']; ?>" class="text-decoration-none">
                                <strong><?php echo htmlspecialchars($activity['title']); ?></strong>: 
                                <?php echo htmlspecialchars(substr(strip_tags($activity['description']), 0, 100)) . (strlen(strip_tags($activity['description'])) > 100 ? '...' : ''); ?>
                            </a>
                        </span>
                    <?php endforeach; ?>
                </marquee>
            <?php else: ?>
                <div class="text-muted py-2 text-center">
                    <i class="fas fa-info-circle me-1"></i>No recent activities available.
                </div>
            <?php endif; ?>
        </div>
    </div> -->

    <!-- HERO SLIDER -->
    <div id="carouselExampleIndicators" class="carousel slide main_slider" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($sliders as $index => $slider): ?>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?php echo $index; ?>" 
                    class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="true" aria-label="Slide <?php echo $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($sliders as $index => $slider): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="img/sliders/<?php echo htmlspecialchars($slider['image']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($slider['title']); ?>">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="opacity:0.1;background: var(--primary-color);"></div>
                <?php if ($slider['title']): ?>
                <div class="carousel-caption d-md-block">
                    <h5><?php echo htmlspecialchars($slider['title']); ?></h5>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- MAIN GRID CONTAINER -->
    <div class="container-fluid main-grid-container my-4">
        <div class="row g-4">
            <!-- Center Column - Mobile First Order -->
            <div class="col-lg-6 order-1 order-lg-2">
                <div class="quick-action-buttons d-flex flex-wrap justify-content-center gap-3 mb-4" data-aos="fade-up">
                    <a href="users-apply-form.php" class="btn btn-action"><i class="fa fa-user-plus"></i> Become a Member</a>
                    <!-- <a href="id-card-download.php" class="btn btn-action"><i class="fa fa-id-card"></i> ID Card</a> -->
                    <a href="donation-form.php" class="btn btn-action"><i class="fa fa-money-bill"></i> Donate</a>
                    <a href="upcoming-event.php" class="btn btn-action"><i class="fa fa-tasks"></i> Upcoming Events</a>
                    <a href="management-team.php" class="btn btn-action"><i class="fa fa-users"></i> Management Team</a>
                    <a href="crowdfunding.php" class="btn btn-action"><i class="fas fa-hand-holding-heart"></i> Crowdfunding</a>
                </div>

                <!-- Updated Activity Section with Better Image Handling and Description -->
                <div class="card-custom" data-aos="fade-up">
                    <h3 class="section-heading"><span>Latest Activities</span></h3>
                    <div class="text-center p-3" style="max-height: 700px; overflow-y: auto;">
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item mb-4">
                                    <div class="activity-image-container">
                                        <a href="activity-details.php?id=<?php echo $activity['id']; ?>">
                                            <img src="img/activities/<?php echo htmlspecialchars($activity['image'] ?: 'default-activity.jpg'); ?>" 
                                                 class="img-fluid" 
                                                 alt="<?php echo htmlspecialchars($activity['title']); ?>"
                                                 onload="this.classList.add('loaded')"
                                                 onerror="this.src='img/activities/default-activity.jpg'">
                                        </a>
                                    </div>
                                    <div class="activity-content">
                                        <h6 class="mb-2">
                                            <a href="activity-details.php?id=<?php echo $activity['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($activity['title']); ?>
                                            </a>
                                        </h6>
                                        <div class="content-html">
                                            <p class="text-muted">
                                                <?php echo $activity['description'] ?: 'No description available.'; ?>
                                            </p>
                                        </div>
                                        <div class="social-share">
                                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($site_url . '/activity-details.php?id=' . $activity['id']); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary me-1" 
                                               title="Share on Facebook">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($site_url . '/activity-details.php?id=' . $activity['id']); ?>&text=<?php echo urlencode($activity['title']); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-info me-1" 
                                               title="Share on Twitter">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($activity['title'] . ' - ' . $site_url . '/activity-details.php?id=' . $activity['id']); ?>" 
                                               target="_blank" 
                                               class="btn btn-sm btn-success" 
                                               title="Share on WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted py-4">
                                <i class="fas fa-info-circle mb-2"></i>
                                <p>No recent activities available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Left Column - Mobile Second Order -->
            <div class="col-lg-3 order-2 order-lg-1">
                <div class="card-custom mb-4" data-aos="fade-up">
                    <h3 class="section-heading"><span>Upcoming Events</span></h3>
                    <div class="upcoming-events-box">
                        <?php if (!empty($upcoming_events)): ?>
                            <marquee direction="up" height="320" scrollamount="2" scrolldelay="100">
                                <?php foreach ($upcoming_events as $event): ?>
                                    <div class="event-item mb-3">
                                        <h6 class="event-title mb-2">
                                            <a href="event-details.php?id=<?php echo $event['id']; ?>" class="event-link">
                                                <?php echo htmlspecialchars($event['title']); ?>
                                            </a>
                                        </h6>
                                        <div class="event-image-container-vertical mb-3">
                                            <?php if (!empty($event['image'])): ?>
                                                <img src="<?php echo SITE_URL . '/img/' . htmlspecialchars($event['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($event['title']); ?>"
                                                     class="event-thumbnail-vertical"
                                                     onload="this.classList.add('loaded')"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                <div class="event-thumb-placeholder-vertical" style="display: none;">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="event-thumb-placeholder-vertical">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="event-details-vertical">
                                            <p class="event-date mb-1">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('d M Y', strtotime($event['event_date'])); ?>
                                                <?php if (!empty($event['event_time'])): ?>
                                                    <span class="ms-1"><?php echo date('H:i', strtotime($event['event_time'])); ?></span>
                                                <?php endif; ?>
                                            </p>
                                            <p class="event-location mb-0">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($event['location'] ?? 'Location not specified'); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </marquee>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-calendar-times fa-3x text-muted"></i>
                                </div>
                                <p class="text-muted">No upcoming events available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="upcoming-event.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i>View All Events
                        </a>
                    </div>
                </div>
                <?php if (!empty($facebook_url) && $facebook_url !== '#'): ?>
                <div class="card-custom" data-aos="fade-up">
                    <h4 class="text-center mb-3">
                        <i class="fab fa-facebook text-primary"></i> Follow Us
                    </h4>
                    <iframe src="https://www.facebook.com/plugins/page.php?href=<?php echo urlencode($facebook_url); ?>&tabs=timeline&width=340&height=400&small_header=false&hide_cover=false" 
                            width="100%" height="400" style="border:none;overflow:hidden" scrolling="no" frameborder="0" 
                            allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
                </div>
                <?php endif; ?>
            </div>

            <style>
            /* NEWS TICKER STYLES */
            .news-ticker-container {
                background: var(--light-bg);
                border-radius: 8px;
                padding: 0.5rem;
                margin-bottom: 1rem;
                border: 1px solid var(--border-light);
                box-shadow: 0 2px 5px var(--shadow-light);
                display: flex;
                align-items: center;
                max-width: 100%;
                overflow: hidden;
                font-family: "Teko", sans-serif;
            }

            .news-ticker-label {
                background: var(--gradient-primary);
                color: var(--text-white);
                padding: 0.5rem 1rem;
                font-size: 1rem;
                font-weight: 600;
                border-radius: 5px 0 0 5px;
                display: flex;
                align-items: center;
                flex-shrink: 0;
                white-space: nowrap;
            }

            .news-ticker-content {
                flex-grow: 1;
                padding: 0.5rem 1rem;
                overflow: hidden;
            }

            .news-ticker-content marquee {
                width: 100%;
                line-height: 1.5;
            }

            .news-ticker-item {
                display: inline-block;
                margin-right: 2rem;
                font-size: 0.9rem;
                color: var(--text-color);
                white-space: nowrap;
            }

            .news-ticker-item a {
                color: var(--text-color);
                text-decoration: none;
                transition: all 0.3s ease;
            }

            .news-ticker-item a:hover {
                color: var(--primary-color);
                text-decoration: underline;
            }

            .news-ticker-item strong {
                font-weight: 600;
                color: var(--primary-color);
            }

            /* RESPONSIVE DESIGN */
            @media (max-width: 991.98px) {
                .news-ticker-container {
                    margin: 0 1rem 1rem;
                }
                .news-ticker-label {
                    font-size: 0.9rem;
                    padding: 0.4rem 0.8rem;
                }
                .news-ticker-item {
                    font-size: 0.85rem;
                }
            }

            @media (max-width: 767.98px) {
                .news-ticker-container {
                    flex-direction: column;
                    align-items: stretch;
                }
                .news-ticker-label {
                    border-radius: 5px 5px 0 0;
                    text-align: center;
                    padding: 0.4rem;
                }
                .news-ticker-content {
                    padding: 0.5rem;
                }
                .news-ticker-item {
                    font-size: 0.8rem;
                }
            }

            @media (max-width: 575.98px) {
                .news-ticker-label {
                    font-size: 0.85rem;
                }
                .news-ticker-item {
                    font-size: 0.75rem;
                }
            }

            /* VERTICAL EVENTS LAYOUT */
            .upcoming-events-box {
                background: var(--light-bg);
                border-radius: 8px;
                padding: 1rem;
                border: 1px solid var(--border-light);
                box-shadow: 0 2px 10px var(--shadow-light);
                max-height: 380px;
                overflow: hidden;
            }

            .event-item {
                background: var(--white-bg);
                border-radius: 8px;
                padding: 1rem;
                box-shadow: 0 2px 10px var(--shadow-light);
                margin-bottom: 18px !important;
                transition: all 0.3s ease;
                border: 1px solid var(--border-light);
                position: relative;
                overflow: hidden;
                text-align: center;
            }

            .event-item::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, var(--overlay-light), transparent);
                transition: left 0.6s ease;
            }

            .event-item:hover::before {
                left: 100%;
            }

            .event-item:hover {
                transform: translateY(-3px);
                box-shadow: 0 5px 15px var(--shadow-medium);
                border-color: var(--primary-color);
            }

            .event-image-container-vertical {
                width: 100%;
                height: auto;
                min-height: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--light-bg);
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid var(--border-lighter);
                position: relative;
                box-shadow: 0 2px 5px var(--shadow-light);
                transition: all 0.3s ease;
                padding: 8px;
                margin: 0 auto;
            }

            .event-image-container-vertical:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px var(--shadow-medium);
                border-color: var(--primary-color);
            }

            .event-thumbnail-vertical {
                width: 100%;
                height: auto;
                max-height: 200px;
                object-fit: contain;
                object-position: center;
                border-radius: 5px;
                transition: all 0.3s ease;
                opacity: 0;
                background: transparent;
                display: block;
            }

            .event-thumbnail-vertical.loaded {
                opacity: 1;
                animation: imageRevealVertical 0.6s ease-out forwards;
            }

            @keyframes imageRevealVertical {
                0% {
                    opacity: 0;
                    transform: scale(0.95);
                }
                100% {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .event-thumbnail-vertical:hover {
                transform: scale(1.02);
            }

            .event-thumb-placeholder-vertical {
                width: 100%;
                height: auto;
                min-height: 80px;
                background: var(--gradient-primary);
                border-radius: 5px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--text-white);
                font-size: 2rem;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
                padding: 20px;
            }

            .event-thumb-placeholder-vertical::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: linear-gradient(45deg, transparent 30%, var(--overlay-light) 50%, transparent 70%);
                transform: rotate(45deg);
                animation: shimmerVertical 3s infinite;
            }

            @keyframes shimmerVertical {
                0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
                100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            }

            .event-thumb-placeholder-vertical:hover {
                transform: scale(1.02);
                background: var(--gradient-primary-reverse);
                box-shadow: 0 4px 10px var(--shadow-dark);
            }

            .upcoming-events-box .event-title {
                font-size: 1.1rem;
                font-weight: 600;
                line-height: 1.4;
                margin-bottom: 15px !important;
                color: var(--text-color);
                font-family: "Bakbak One", sans-serif;
                text-align: center;
                min-height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .upcoming-events-box .event-title a {
                color: var(--text-color);
                text-decoration: none;
                transition: all 0.3s ease;
                display: block;
                position: relative;
                padding: 5px 0;
            }

            .upcoming-events-box .event-title a::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                width: 0;
                height: 2px;
                background: var(--gradient-primary);
                transition: all 0.3s ease;
                transform: translateX(-50%);
            }

            .upcoming-events-box .event-title a:hover {
                color: var(--primary-color);
                transform: scale(1.02);
            }

            .upcoming-events-box .event-title a:hover::after {
                width: 80%;
            }

            .event-details-vertical {
                text-align: center;
                margin-top: 15px;
            }

            .upcoming-events-box .event-date, 
            .upcoming-events-box .event-location {
                font-size: 0.9rem;
                color: var(--text-light);
                margin-bottom: 8px !important;
                line-height: 1.4;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 500;
                transition: all 0.3s ease;
                padding: 5px 10px;
                border-radius: 5px;
                background: var(--light-bg);
                font-family: "Teko", sans-serif;
            }

            .upcoming-events-box .event-date:hover,
            .upcoming-events-box .event-location:hover {
                color: var(--text-color);
                background: var(--border-lighter);
                transform: translateY(-1px);
            }

            .upcoming-events-box .event-date i, 
            .upcoming-events-box .event-location i {
                font-size: 0.85rem;
                width: 20px;
                flex-shrink: 0;
                color: var(--primary-color);
                margin-right: 8px;
                transition: all 0.3s ease;
            }

            .upcoming-events-box .event-date:hover i,
            .upcoming-events-box .event-location:hover i {
                color: var(--primary-dark);
                transform: scale(1.1);
            }

            .upcoming-events-box marquee {
                line-height: 1.5;
                padding: 8px 0;
            }

            .upcoming-events-box + .text-center .btn-primary {
                background: var(--gradient-primary);
                border: none;
                border-radius: 5px;
                padding: 8px 15px;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 2px 5px var(--shadow-medium);
                color: var(--text-white);
                font-family: "Teko", sans-serif;
                font-size: 1rem;
            }

            .upcoming-events-box + .text-center .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 10px var(--shadow-dark);
                background: var(--gradient-primary-reverse);
                color: var(--text-white);
            }

            .upcoming-events-box + .text-center .btn-primary:active {
                transform: translateY(0);
            }

            .upcoming-events-box .event-thumbnail-vertical:not(.loaded) {
                background: linear-gradient(90deg, var(--border-lighter) 25%, var(--border-light) 50%, var(--border-lighter) 75%);
                background-size: 200% 100%;
                animation: loadingVertical 2s infinite ease-in-out;
            }

            @keyframes loadingVertical {
                0% { 
                    background-position: 200% 0; 
                    opacity: 0.7;
                }
                50% { 
                    opacity: 0.9; 
                }
                100% { 
                    background-position: -200% 0; 
                    opacity: 0.7;
                }
            }

            @media (max-width: 991.98px) {
                .upcoming-events-box {
                    margin-bottom: 25px;
                    padding: 1rem;
                    max-height: 360px;
                }
                
                .event-item {
                    padding: 1rem;
                    margin-bottom: 15px !important;
                }
                
                .event-image-container-vertical {
                    min-height: 70px;
                    padding: 6px;
                }
                
                .event-thumbnail-vertical {
                    max-height: 180px;
                }
                
                .event-thumb-placeholder-vertical {
                    min-height: 70px;
                    font-size: 1.8rem;
                    padding: 18px;
                }
                
                .upcoming-events-box .event-title {
                    font-size: 1rem;
                    min-height: 45px;
                }
            }

            @media (max-width: 767.98px) {
                .upcoming-events-box {
                    max-height: 320px;
                    padding: 1rem;
                }
                
                .upcoming-events-box marquee {
                    height: 290px !important;
                }
                
                .event-image-container-vertical {
                    min-height: 60px;
                    padding: 5px;
                    width: 100%;
                    max-width: 100%;
                    margin: 0 auto;
                }
                
                .event-thumbnail-vertical {
                    width: 100%;
                    height: auto;
                    max-height: 160px;
                    object-fit: contain;
                }
                
                .event-thumbnail-vertical[data-loaded="true"] {
                    width: 100%;
                    height: auto;
                }
                
                .event-thumb-placeholder-vertical {
                    min-height: 60px;
                    font-size: 1.6rem;
                    padding: 16px;
                }
                
                .event-item {
                    padding: 0.8rem;
                    margin-bottom: 12px !important;
                }
                
                .upcoming-events-box .event-title {
                    font-size: 0.95rem;
                    min-height: 40px;
                }
                
                .upcoming-events-box .event-date, 
                .upcoming-events-box .event-location {
                    font-size: 0.8rem;
                    padding: 4px 8px;
                }
                
                .upcoming-events-box .event-date i, 
                .upcoming-events-box .event-location i {
                    width: 16px;
                    font-size: 0.75rem;
                }
            }

            @media (max-width: 575.98px) {
                .event-image-container-vertical {
                    min-height: 50px;
                    padding: 4px;
                }
                
                .event-thumbnail-vertical {
                    max-height: 140px;
                }
                
                .event-thumbnail-vertical {
                    width: 100% !important;
                    height: auto !important;
                }
                
                .event-thumb-placeholder-vertical {
                    min-height: 50px;
                    font-size: 1.4rem;
                    padding: 14px;
                }
                
                .upcoming-events-box .event-title {
                    font-size: 0.9rem;
                    min-height: 35px;
                }
                
                .upcoming-events-box .event-date, 
                .upcoming-events-box .event-location {
                    font-size: 0.75rem;
                    padding: 3px 6px;
                }
                
                .event-item {
                    padding: 0.6rem;
                    margin-bottom: 10px !important;
                }
            }

            .event-thumbnail-vertical[data-aspect="wide"] {
                width: 100%;
                height: auto;
            }

            .event-thumbnail-vertical[data-aspect="tall"] {
                width: auto;
                height: 100%;
                max-width: 100%;
            }

            .event-thumbnail-vertical[data-aspect="square"] {
                width: 100%;
                height: auto;
                max-height: 100%;
            }

            .upcoming-events-box::-webkit-scrollbar {
                width: 6px;
            }

            .upcoming-events-box::-webkit-scrollbar-track {
                background: var(--light-bg);
                border-radius: 3px;
            }

            .upcoming-events-box::-webkit-scrollbar-thumb {
                background: var(--gradient-primary);
                border-radius: 3px;
                transition: background 0.3s ease;
            }

            .upcoming-events-box::-webkit-scrollbar-thumb:hover {
                background: var(--gradient-primary-reverse);
            }
            </style>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const images = document.querySelectorAll('.event-thumbnail-vertical');
                
                images.forEach(function(img) {
                    img.addEventListener('load', function() {
                        const aspectRatio = this.naturalWidth / this.naturalHeight;
                        const container = this.closest('.event-image-container-vertical');
                        
                        if (aspectRatio > 1.5) {
                            this.setAttribute('data-aspect', 'wide');
                            this.style.width = '100%';
                            this.style.height = 'auto';
                        } else if (aspectRatio < 0.7) {
                            this.setAttribute('data-aspect', 'tall');
                            this.style.height = '100%';
                            this.style.width = 'auto';
                            container.style.justifyContent = 'center';
                        } else {
                            this.setAttribute('data-aspect', 'square');
                            this.style.width = '100%';
                            this.style.height = 'auto';
                        }
                        
                        this.setAttribute('data-loaded', 'true');
                        this.classList.add('loaded');
                        
                        if (window.innerWidth <= 767) {
                            if (aspectRatio > 1.5) {
                                this.style.width = '100%';
                                this.style.height = 'auto';
                            } else {
                                this.style.width = '100%';
                                this.style.height = 'auto';
                                this.style.objectFit = 'contain';
                            }
                        }
                    });
                    
                    img.addEventListener('error', function() {
                        const container = this.closest('.event-image-container-vertical');
                        container.style.minHeight = '80px';
                    });
                });
                
                images.forEach(function(img) {
                    if (img.complete && img.naturalWidth > 0) {
                        img.dispatchEvent(new Event('load'));
                    }
                });
                
                window.addEventListener('resize', function() {
                    if (window.innerWidth <= 767) {
                        document.querySelectorAll('.event-thumbnail-vertical.loaded').forEach(function(img) {
                            const aspectRatio = img.naturalWidth / img.naturalHeight;
                            if (aspectRatio > 1.5) {
                                img.style.width = '100%';
                                img.style.height = 'auto';
                            } else {
                                img.style.width = '100%';
                                img.style.height = 'auto';
                                img.style.objectFit = 'contain';
                            }
                        });
                    }
                });
            });
            </script>

            <!-- Right Column - Mobile Third Order -->
            <div class="col-lg-3 order-3 order-lg-3">
                <div class="card-custom mb-4" data-aos="fade-up">
                    <h3 class="section-heading"><span>Management Team</span></h3>
                    <div id="managementSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                        <div class="carousel-inner">
                            <?php if (!empty($management_team)): ?>
                                <?php foreach ($management_team as $index => $member): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <div class="member-card">
                                        <a href="management-team.php">
                                            <img src="uploads/team/<?php echo htmlspecialchars($member['image'] ?: 'default-avatar.png'); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                                        </a>
                                        <h5><a href="management-team.php"><?php echo htmlspecialchars($member['name']); ?></a></h5>
                                        <p><?php echo htmlspecialchars($member['designation']); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="carousel-item active">
                                    <div class="member-card">
                                        <img src="uploads/team/default-avatar.png" alt="No Member">
                                        <h5>No members available</h5>
                                        <p>Management</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="management-team.php" class="view-all-btn">View All</a>
                </div>

                <div class="card-custom" data-aos="fade-up">
                    <h3 class="section-heading"><span>Members</span></h3>
                    <div id="userSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                        <div class="carousel-inner">
                            <?php if (!empty($recent_users)): ?>
                                <?php foreach ($recent_users as $index => $user): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <div class="member-card">
                                        <a href="our-team.php">
                                            <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_image'] ?: 'default-avatar.png'); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                        </a>
                                        <h5><a href="our-team.php"><?php echo htmlspecialchars($user['name']); ?></a></h5>
                                        <p><?php echo htmlspecialchars($user['designation_hindi'] ?? ucfirst(str_replace('_', ' ', $user['membership_type']))); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="carousel-item active">
                                    <div class="member-card">
                                        <img src="uploads/profiles/default-avatar.png" alt="No User">
                                        <h5>No members available</h5>
                                        <p>Member</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="our-team.php" class="view-all-btn">View All</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ABOUT US SECTION -->
    <?php if (!empty($about_content)): ?>
    <div class="container-fluid my-5 about-full-width">
        <h3 class="section-heading text-center"><span>About Us</span></h3>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="about-us-container">
                        <div class="about-section" data-aos="fade-up">
                            <?php if (!empty($about_content['image'])): ?>
                                <div class="section-image">
                                    <img src="img/about/<?php echo htmlspecialchars($about_content['image']); ?>" alt="<?php echo htmlspecialchars($about_content['title']); ?>" class="img-fluid rounded">
                                </div>
                            <?php endif; ?>
                            <div class="section-content">
                                <h4><?php echo htmlspecialchars($about_content['title']); ?></h4>
                                <div class="content-html"><?php echo $about_content['description']; ?></div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="aboutus.php" class="btn btn-primary">Read More About Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

<!-- PRESIDENT'S MESSAGE SECTION -->
<?php if ($president_message): ?>
<div class="container-fluid my-5 bg-light py-5">
    <h3 class="section-heading text-center"><span>Trustee's Message</span></h3>
    <div class="container">
        <div class="row align-items-center president-message">
            <div class="col-lg-4 text-center" data-aos="fade-right">
                <?php if ($president_message['image']): ?>
                    <img src="img/president/<?php echo htmlspecialchars($president_message['image']); ?>" alt="President" class="img-fluid rounded-circle shadow" style="width: 250px; height: 250px; object-fit: cover;">
                <?php else: ?>
                    <img src="img/default-president.jpg" alt="President" class="img-fluid rounded-circle shadow" style="width: 250px; height: 250px; object-fit: cover;">
                <?php endif; ?>
                <h5 class="mt-3"><?php echo htmlspecialchars_decode($president_message['president_name']); ?></h5>
                <p class="text-muted"><?php echo htmlspecialchars_decode($president_message['designation']); ?></p>
            </div>
            <div class="col-lg-8" data-aos="fade-left">
                <div class="card-custom message-content">
                    <div class="content-html"><?php echo $president_message['message']; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- OUR OBJECTIVES SECTION -->
    <?php if (!empty($objectives)): ?>
    <div class="container-fluid my-5">
        <h3 class="section-heading text-center"><span>Our Objectives</span></h3>
        <div class="container">
            <div class="objectives-slider-container">
                <div class="objectives-slider" id="objectivesSlider">
                    <?php foreach ($objectives as $index => $objective): ?>
                        <div class="objective-slide">
                            <div class="card-custom text-center h-100">
                                <div class="mb-3">
                                    <?php if (!empty($objective['image'])): ?>
                                        <img src="img/objectives/<?php echo htmlspecialchars($objective['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($objective['title']); ?>" 
                                             class="objective-image"
                                             onerror="this.src='img/objectives/default-objective.jpg'">
                                    <?php else: ?>
                                        <img src="img/objectives/default-objective.jpg" 
                                             alt="Default Objective" 
                                             class="objective-image">
                                    <?php endif; ?>
                                </div>
                                <h5><?php echo htmlspecialchars($objective['title']); ?></h5>
                                <p><?php echo htmlspecialchars($objective['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                    
                <div class="slider-controls">
                    <button class="slider-btn prev-btn" onclick="changeObjectiveSlide(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="slider-btn next-btn" onclick="changeObjectiveSlide(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="slider-indicators">
                    <?php 
                    $totalSlides = count($objectives);
                    $maxIndicators = ceil($totalSlides / 3);
                    for ($i = 0; $i < $maxIndicators; $i++): 
                    ?>
                        <button class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" onclick="goToObjectiveSlide(<?php echo $i; ?>)"></button>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!--<div class="text-center mt-4">-->
            <!--    <a href="our-objectives.php" class="btn btn-primary">View All Objectives</a>-->
            <!--</div>-->
        </div>
    </div>

    <style>
    .objectives-slider-container {
        position: relative;
        max-width: 100%;
        margin: 0 auto;
        overflow: hidden;
    }

    .objectives-slider {
        display: flex;
        transition: transform 0.5s ease-in-out;
        width: 100%;
    }

    .objective-slide {
        min-width: 33.333%;
        padding: 0 15px;
        box-sizing: border-box;
    }

    @media (max-width: 992px) {
        .objective-slide {
            min-width: 50%;
        }
    }

    @media (max-width: 768px) {
        .objective-slide {
            min-width: 100%;
        }
    }

    .objectives-slider-container .card-custom {
        background: #fff;
        border-radius: 10px;
        padding: 30px 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        height: 100%;
        border: none;
    }

    .objectives-slider-container .card-custom:hover {
        transform: translateY(-5px);
    }

    .objective-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        display: block;
        margin: 0 auto;
        border-radius: 50%;
        border: 3px solid #f8f9fa;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .objective-image:hover {
        transform: scale(1.1);
    }

    .slider-controls {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 100%;
        display: flex;
        justify-content: space-between;
        pointer-events: none;
        z-index: 10;
    }

    .slider-btn {
        background: var(--secondary-color, #007bff);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        pointer-events: auto;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .slider-btn i {
        font-size: 16px;
    }

    .slider-btn:hover {
        background: var(--primary-color, #0056b3);
        transform: scale(1.1);
    }

    .slider-indicators {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
    }

    .indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: none;
        background: #ddd;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .indicator.active {
        background: var(--secondary-color, #007bff);
    }

    .indicator:hover {
        background: var(--primary-color, #0056b3);
    }

    .section-heading {
        margin-bottom: 40px;
    }

    .section-heading span {
        position: relative;
        padding-bottom: 10px;
    }

    .section-heading span::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: var(--secondary-color, #007bff);
    }

    @media (max-width: 768px) {
        .quick-action-buttons {
            gap: 2px !important;
        }
        
        .quick-action-buttons .btn {
            font-size: 12px;
            padding: 8px 12px;
        }
        
        .quick-action-buttons .btn i {
            font-size: 14px;
        }
    }
    </style>

    <script>
    let currentObjectiveSlide = 0;
    const totalObjectiveSlides = <?php echo count($objectives); ?>;
    const objectiveSlidesPerView = window.innerWidth > 992 ? 3 : (window.innerWidth > 768 ? 2 : 1);
    const maxObjectiveSlide = Math.max(0, totalObjectiveSlides - objectiveSlidesPerView);
    let objectiveAutoSlideInterval;

    function updateObjectiveSlider() {
        const slider = document.getElementById('objectivesSlider');
        if (!slider) return;
        
        const slideWidth = 100 / objectiveSlidesPerView;
        slider.style.transform = `translateX(-${currentObjectiveSlide * slideWidth}%)`;
        
        const indicators = document.querySelectorAll('.objectives-slider-container .indicator');
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === Math.floor(currentObjectiveSlide / objectiveSlidesPerView));
        });
    }

    function changeObjectiveSlide(direction) {
        currentObjectiveSlide += direction;
        
        if (currentObjectiveSlide < 0) {
            currentObjectiveSlide = maxObjectiveSlide;
        } else if (currentObjectiveSlide > maxObjectiveSlide) {
            currentObjectiveSlide = 0;
        }
        
        updateObjectiveSlider();
        resetObjectiveAutoSlide();
    }

    function goToObjectiveSlide(index) {
        currentObjectiveSlide = Math.min(index * objectiveSlidesPerView, maxObjectiveSlide);
        updateObjectiveSlider();
        resetObjectiveAutoSlide();
    }

    function nextObjectiveSlide() {
        changeObjectiveSlide(1);
    }

    function startObjectiveAutoSlide() {
        objectiveAutoSlideInterval = setInterval(nextObjectiveSlide, 4000);
    }

    function resetObjectiveAutoSlide() {
        clearInterval(objectiveAutoSlideInterval);
        startObjectiveAutoSlide();
    }

    // Initialize sliders for Management Team and Members
    document.addEventListener('DOMContentLoaded', function() {
        // Objectives Slider
        if (document.getElementById('objectivesSlider')) {
            updateObjectiveSlider();
            startObjectiveAutoSlide();
            
            const sliderContainer = document.querySelector('.objectives-slider-container');
            if (sliderContainer) {
                sliderContainer.addEventListener('mouseenter', () => clearInterval(objectiveAutoSlideInterval));
                sliderContainer.addEventListener('mouseleave', startObjectiveAutoSlide);
            }
        }

        // Management Slider
        const managementSlider = document.getElementById('managementSlider');
        if (managementSlider) {
            const managementCarousel = new bootstrap.Carousel(managementSlider, {
                interval: 4000,
                ride: 'carousel',
                pause: 'hover'
            });

            managementSlider.addEventListener('mouseenter', () => managementCarousel.pause());
            managementSlider.addEventListener('mouseleave', () => managementCarousel.cycle());
        }

        // User Slider
        const userSlider = document.getElementById('userSlider');
        if (userSlider) {
            const userCarousel = new bootstrap.Carousel(userSlider, {
                interval: 4000,
                ride: 'carousel',
                pause: 'hover'
            });

            userSlider.addEventListener('mouseenter', () => userCarousel.pause());
            userSlider.addEventListener('mouseleave', () => userCarousel.cycle());
        }
    });

    window.addEventListener('resize', function() {
        const newSlidesPerView = window.innerWidth > 992 ? 3 : (window.innerWidth > 768 ? 2 : 1);
        if (newSlidesPerView !== objectiveSlidesPerView) {
            setTimeout(updateObjectiveSlider, 100);
        }
    });
    </script>
    <?php endif; ?>

    <!-- OUR VIDEOS SECTION -->
    <?php if (!empty($youtube_videos)): ?>
    <div class="container-fluid my-5 bg-light py-5">
        <h3 class="section-heading text-center"><span>Our Videos</span></h3>
        <div class="container">
            <div class="row">
                <?php foreach ($youtube_videos as $index => $video): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card-custom">
                            <div class="video-thumbnail mb-3">
                                <iframe width="100%" height="200" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video['video_id']); ?>" frameborder="0" allowfullscreen></iframe>
                            </div>
                            <h6><?php echo htmlspecialchars($video['title']); ?></h6>
                            <p class="text-muted small"><?php echo htmlspecialchars($video['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="our-videos.php" class="btn btn-danger">
                    <i class="fab fa-youtube"></i> View All Videos
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- GALLERY SECTION -->
    <div class="container-fluid my-5">
        <h3 class="section-heading text-center"><span>Gallery</span></h3>
        <?php if (empty($gallery_images)): ?>
            <div class="alert alert-warning text-center">
                <p>No gallery images available.</p>
            </div>
        <?php else: ?>
            <div class="container">
                <div class="row">
                    <?php foreach ($gallery_images as $index => $image): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="gallery-item">
                            <div class="gallery-image-container" style="cursor: pointer;" onclick="openGalleryModal(<?php echo $index; ?>)">
                                <!-- Removed object-fit: cover and fixed height to prevent cropping -->
                                <img src="img/gallery/<?php echo htmlspecialchars($image['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                     class="img-fluid rounded shadow-sm"
                                     style="width: 100%; height: auto;">
                                <div class="gallery-overlay">
                                    <div class="overlay-content">
                                        <h6 class="text-white mb-0"><?php echo htmlspecialchars($image['title']); ?></h6>
                                        <i class="fas fa-search-plus text-white mt-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="gallery.php" class="btn btn-primary">View Full Gallery</a>
        </div>
    </div>

    <!-- Added Gallery Modal for lightbox viewing -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="galleryModalLabel">Gallery</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                        <div class="carousel-inner">
                            <?php foreach ($gallery_images as $index => $image): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <div class="text-center">
                                    <img src="img/gallery/<?php echo htmlspecialchars($image['image']); ?>" 
                                         class="d-block mx-auto img-fluid" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                                         style="max-height: 70vh; width: auto;">
                                    <!-- Moved title and description below image instead of overlay -->
                                </div>
                                <div class="text-center mt-3 px-4">
                                    <h5 class="text-white mb-2"><?php echo htmlspecialchars($image['title']); ?></h5>
                                    <?php if ($image['description']): ?>
                                        <p class="text-light"><?php echo nl2br(htmlspecialchars($image['description'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        <div class="carousel-indicators">
                            <?php foreach ($gallery_images as $index => $image): ?>
                            <button type="button" data-bs-target="#galleryCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                    <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                                    aria-label="Slide <?php echo $index + 1; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary" onclick="toggleAutoplay()">
                        <i class="fas fa-pause" id="playPauseIcon"></i> <span id="playPauseText">Pause</span>
                    </button>
                    <span class="text-white mx-3" id="imageCounter">1 of <?php echo count($gallery_images); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- NEWS SECTION -->
    <?php if (!empty($news)): ?>
    <div class="container-fluid my-5 bg-light py-5">
        <h3 class="section-heading text-center"><span>News</span></h3>
        <div class="container">
            <div class="row">
                <?php foreach ($news as $index => $news_item): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card-custom h-100">
                            <div class="mb-3">
                                <?php if ($news_item['image']): ?>
                                    <img src="img/<?php echo htmlspecialchars($news_item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($news_item['title']); ?>" 
                                         class="img-fluid rounded shadow-sm"
                                         style="width: 100%; height: 200px; object-fit: cover;"
                                         onerror="this.src='img/default-news.jpg'">
                                <?php else: ?>
                                    <img src="img/default-news.jpg" 
                                         alt="Default News" 
                                         class="img-fluid rounded shadow-sm"
                                         style="width: 100%; height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <h6><a href="news-details.php?id=<?php echo $news_item['id']; ?>"><?php echo htmlspecialchars($news_item['title']); ?></a></h6>
                            <p class="text-muted small"><?php echo htmlspecialchars(substr(strip_tags($news_item['content']), 0, 100)) . '...'; ?></p>
                            <a href="news-details.php?id=<?php echo $news_item['id']; ?>" class="btn btn-sm btn-primary">Read More</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="news.php" class="btn btn-primary">View All News</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php if (!empty($testimonials)): ?>
<div class="container-fluid my-5 bg-light py-5">
    <h3 class="section-heading text-center"><span>Members' Testimonials</span></h3>
    <div class="container">
        <div class="row">
            <?php foreach ($testimonials as $index => $testimonial): ?>
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card-custom h-100">
                        <div class="text-center mb-3">
                            <div class="mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="fst-italic">"<?php echo htmlspecialchars($testimonial['message']); ?>"</p>
                            <div class="d-flex align-items-center justify-content-center">
                                <?php if ($testimonial['image']): ?>
                                    <img src="img/testimonials/<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="img/default-testimonial.jpg" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($testimonial['designation']); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
</main>

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


let galleryImages = <?php echo json_encode($gallery_images); ?>;
let isAutoplay = true;
let galleryCarousel;

function openGalleryModal(index) {
    const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
    galleryCarousel = new bootstrap.Carousel(document.getElementById('galleryCarousel'), {
        interval: 3000,
        ride: 'carousel'
    });
    
    // Go to specific slide
    galleryCarousel.to(index);
    modal.show();
    
    // Update counter
    updateImageCounter(index + 1);
}

function toggleAutoplay() {
    const playPauseIcon = document.getElementById('playPauseIcon');
    const playPauseText = document.getElementById('playPauseText');
    
    if (isAutoplay) {
        galleryCarousel.pause();
        playPauseIcon.className = 'fas fa-play';
        playPauseText.textContent = 'Play';
        isAutoplay = false;
    } else {
        galleryCarousel.cycle();
        playPauseIcon.className = 'fas fa-pause';
        playPauseText.textContent = 'Pause';
        isAutoplay = true;
    }
}

function updateImageCounter(current) {
    document.getElementById('imageCounter').textContent = `${current} of ${galleryImages.length}`;
}

// Update counter when carousel slides
document.getElementById('galleryCarousel').addEventListener('slide.bs.carousel', function (e) {
    updateImageCounter(e.to + 1);
});

</script>

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
.about-section .section-image img {
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
    max-height: 400px !important;
    object-fit: contain !important;
    object-position: center !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 10px var(--shadow-light) !important;
}

@media (max-width: 991.98px) {
    .about-section .section-image img {
        max-height: 350px !important;
        height: auto !important;
        object-fit: contain !important;
    }
}

@media (max-width: 767.98px) {
    .about-section .section-image img {
        max-height: 300px !important;
        height: auto !important;
        margin-bottom: 1.5rem;
        object-fit: contain !important;
    }
}

@media (max-width: 575.98px) {
    .about-section .section-image img {
        max-height: 250px !important;
        height: auto !important;
        object-fit: contain !important;
    }
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

<?php include 'footer.php'; ?>
