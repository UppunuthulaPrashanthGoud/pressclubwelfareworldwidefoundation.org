<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Get active events from database
$stmt = $db->prepare("SELECT * FROM events WHERE status = ? ORDER BY event_date DESC");
$stmt->execute(['active']);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sanitize event data
foreach ($events as &$event) {
    $event['title'] = htmlspecialchars($event['title']);
    $event['description'] = htmlspecialchars($event['description']);
    $event['location'] = htmlspecialchars($event['location']);
}

include 'header.php';
include 'navbar.php';
?>

<!-- Full-Width Events Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>आगामी कार्यक्रम</span>
                </div>
            </div>
        </div>

        <!-- Events Section -->
        <div class="event-content">
            <?php if (!empty($events)): ?>
                <div class="row g-4">
                    <?php foreach ($events as $index => $event): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="card-custom h-100">
                                <?php if (!empty($event['image'])): ?>
                                    <div class="event-image-container">
                                        <img src="<?php echo SITE_URL . '/img/' . htmlspecialchars($event['image']); ?>" 
                                             alt="<?php echo $event['title']; ?>" 
                                             class="img-fluid rounded-top">
                                    </div>
                                <?php else: ?>
                                    <div class="event-image-container bg-secondary text-white d-flex align-items-center justify-content-center rounded-top" style="height: 200px;">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $event['title']; ?></h5>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-map-marker-alt me-1"></i> <?php echo $event['location']; ?> | 
                                        <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y, g:i A', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?>
                                    </p>
                                    <p class="card-text"><?php echo substr($event['description'], 0, 150) . (strlen($event['description']) > 150 ? '...' : ''); ?></p>
                                    <a href="participate-form.php?event_id=<?php echo $event['id']; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-ticket-alt me-1"></i> सीट बुक करें
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="lead text-muted">इस समय कोई आगामी कार्यक्रम उपलब्ध नहीं है।</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Event Content Section */
.event-content {
    padding: 2rem 0;
}

.card-custom {
    border: none;
    border-radius: 10px;
    background: var(--white-bg);
    box-shadow: 0 4px 12px var(--shadow-medium);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-custom:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px var(--shadow-dark);
}

.event-image-container {
    overflow: hidden;
    height: 200px;
}

.event-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.card-custom:hover .event-image-container img {
    transform: scale(1.05);
}

.card-custom .card-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 0.75rem;
}

.card-custom .card-text {
    color: var(--text-muted);
    font-size: 1rem;
    line-height: 1.6;
}

.card-custom .btn-primary {
    background: var(--gradient-primary);
    border: none;
    font-family: 'Teko', sans-serif;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    color: var(--text-white);
}

.card-custom .btn-primary:hover {
    background: var(--gradient-primary-reverse);
    transform: translateY(-2px);
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .event-content {
        padding: 1.5rem 0;
    }

    .event-image-container {
        height: 180px;
    }
}

@media (max-width: 767.98px) {
    .event-content {
        padding: 1rem 0;
    }

    .event-image-container {
        height: 160px;
    }

    .card-custom .card-title {
        font-size: 1.2rem;
    }

    .card-custom .card-text {
        font-size: 0.95rem;
    }
}

@media (max-width: 575.98px) {
    .section-heading span {
        font-size: 1.5rem;
    }

    .card-custom .card-title {
        font-size: 1.1rem;
    }

    .card-custom .btn-primary {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
    }
}
</style>

<?php include 'footer.php'; ?>