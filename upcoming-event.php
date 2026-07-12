<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get upcoming events from database (events with future dates or today's events)
$currentDate = date('Y-m-d');
$stmt = $db->prepare("SELECT * FROM events WHERE status = ? AND event_date >= ? ORDER BY event_date ASC, event_time ASC");
$stmt->execute(['active', $currentDate]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sanitize event data
$uniqueEvents = []; // To prevent duplicates (just in case)
$eventIds = []; // Track IDs to ensure uniqueness
foreach ($events as &$event) {
    if (!in_array($event['id'], $eventIds)) {
        $event['title'] = htmlspecialchars($event['title']);
        $event['description'] = htmlspecialchars($event['description']);
        $event['location'] = htmlspecialchars($event['location']);
        $uniqueEvents[] = $event;
        $eventIds[] = $event['id'];
    }
}
$events = $uniqueEvents; // Use sanitized and unique events

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Events Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>Upcoming Events</span>
                </div>
            </div>
        </div>

        <!-- Events Section -->
        <div class="event-content">
            <?php if (!empty($events)): ?>
                <div class="row g-4">
                    <?php foreach ($events as $index => $event): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="card-custom event-card h-100">
                                <?php if (!empty($event['image'])): ?>
                                    <div class="event-image-container adaptive">
                                        <img src="<?php echo SITE_URL . '/img/' . htmlspecialchars($event['image']); ?>" 
                                             alt="<?php echo $event['title']; ?>" 
                                             class="img-fluid rounded-top event-image">
                                    </div>
                                <?php else: ?>
                                    <div class="event-image-container placeholder-image bg-secondary text-white d-flex align-items-center justify-content-center rounded-top">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body event-details">
                                    <h5 class="card-title event-title"><?php echo $event['title']; ?></h5>
                                    <div class="event-meta">
                                        <p class="card-text text-muted small mb-2">
                                            <span><i class="fas fa-map-marker-alt me-1"></i> <?php echo $event['location']; ?></span> | 
                                            <span><i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y, g:i A', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?></span>
                                        </p>
                                    </div>
                                    <div class="event-description">
                                        <p class="card-text"><?php echo nl2br($event['description']); ?></p>
                                    </div>
                                    
                                    <!-- Event Status Badge -->
                                    <div class="mt-3">
                                        <?php 
                                        $eventDateTime = strtotime($event['event_date'] . ' ' . $event['event_time']);
                                        $currentDateTime = time();
                                        if ($eventDateTime > $currentDateTime): 
                                        ?>
                                            <span class="badge bg-success">Upcoming</span>
                                        <?php elseif (date('Y-m-d', $eventDateTime) == date('Y-m-d', $currentDateTime)): ?>
                                            <span class="badge bg-warning">Today</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="card-custom">
                        <div class="card-body">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                            <p class="lead text-muted">No upcoming events available at this time.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Event Content Section - Using main style.css variables */
.event-content {
    padding: 2rem 0;
}

/* Event Card - Enhanced with main theme colors */
.event-card {
    border: none;
    border-radius: 10px;
    background: var(--white-bg);
    box-shadow: 0 4px 12px var(--shadow-medium);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    position: relative;
}

.event-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px var(--shadow-dark);
}

/* Universal Adaptive Image Container - Works for ALL image sizes - PRESERVED */
.event-image-container {
    overflow: hidden;
    position: relative;
    width: 100%;
}

/* Default - All images are now adaptive to prevent any cropping - PRESERVED */
.event-image-container.adaptive {
    height: auto;
    min-height: 200px;
    background-color: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px 10px 0 0;
}

/* Universal Event Image Styling - No cropping for any image - PRESERVED */
.event-image {
    width: 100%;
    height: auto;
    max-width: 100%;
    object-fit: contain; /* Never crops - shows full image */
    object-position: center;
    transition: transform 0.3s ease;
    border-radius: 10px 10px 0 0;
}

/* Placeholder for missing images - PRESERVED */
.placeholder-image {
    height: 200px;
    min-height: 200px;
    background-color: var(--light-bg);
    color: var(--text-muted);
}

/* Hover effects - PRESERVED */
.event-card:hover .event-image {
    transform: scale(1.02);
}

/* Event Details - Using main theme */
.event-details {
    padding: 1.5rem;
}

/* Event Title - Using main theme */
.event-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

/* Event Meta - Enhanced styling */
.event-meta {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.event-meta span {
    display: inline-block;
    margin-right: 10px;
}

.event-meta span i {
    color: var(--primary-dark);
    margin-right: 4px;
}

/* Event Description - Enhanced */
.event-description {
    max-height: 120px;
    overflow-y: auto;
    padding-right: 10px;
    font-size: 1rem;
    line-height: 1.6;
    color: var(--text-color);
}

/* Custom scrollbar for description */
.event-description::-webkit-scrollbar {
    width: 6px;
}

.event-description::-webkit-scrollbar-track {
    background: var(--light-bg);
    border-radius: 3px;
}

.event-description::-webkit-scrollbar-thumb {
    background: var(--border-light);
    border-radius: 3px;
}

.event-description::-webkit-scrollbar-thumb:hover {
    background: var(--primary-light);
}

.event-description p {
    color: var(--text-color);
    margin-bottom: 0.75rem;
}

/* Badge styling - Using main theme */
.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
    border-radius: 15px;
    font-weight: 600;
}

.badge.bg-success {
    background-color: var(--success-color) !important;
}

.badge.bg-warning {
    background-color: var(--gold-color) !important;
    color: var(--text-color) !important;
}

/* No events state - Enhanced */
.event-content .text-center .card-custom {
    background: var(--white-bg);
    border: 2px dashed var(--border-light);
    padding: 3rem 2rem;
}

.event-content .text-center .fa-calendar-times {
    color: var(--text-muted);
}

/* Responsive Adjustments - All adaptive - PRESERVED LOGIC */
@media (max-width: 991.98px) {
    .event-content {
        padding: 1.5rem 0;
    }

    .event-image-container.adaptive {
        min-height: 180px;
    }
    
    .event-title {
        font-size: 1.2rem;
    }
}

@media (max-width: 767.98px) {
    .event-content {
        padding: 1rem 0;
    }

    .event-image-container.adaptive {
        min-height: 160px;
    }

    .event-title {
        font-size: 1.15rem;
    }

    .event-meta {
        font-size: 0.85rem;
    }
    
    .event-description {
        font-size: 0.95rem;
        max-height: 100px;
    }
    
    .event-details {
        padding: 1.25rem;
    }
}

@media (max-width: 575.98px) {
    .section-heading span {
        font-size: 1.5rem;
    }

    .event-title {
        font-size: 1.1rem;
    }
    
    .event-image-container.adaptive {
        min-height: 140px;
    }
    
    .event-details {
        padding: 1rem;
    }
    
    .event-content .text-center .card-custom {
        padding: 2rem 1rem;
    }
}

/* Background for adaptive containers - PRESERVED */
.event-image-container.adaptive {
    background-color: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php include 'footer.php'; ?>