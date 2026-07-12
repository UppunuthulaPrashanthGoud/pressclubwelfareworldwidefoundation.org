<?php
require_once 'config/config.php';

try {
    $db = getDbConnection();
    
    // Get upcoming events (limit to 3 for homepage)
    $currentDate = date('Y-m-d');
    $stmt = $db->prepare("SELECT * FROM events WHERE status = 'active' AND event_date >= ? ORDER BY event_date ASC, event_time ASC LIMIT 3");
    $stmt->execute([$currentDate]);
    $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    logError("Database error in upcoming-events-section.php: " . $e->getMessage());
    $upcomingEvents = [];
}
?>

<?php if (!empty($upcomingEvents)): ?>
<!-- Upcoming Events Section -->
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="container">
        <div class="section-heading mb-4">
            <span>Upcoming Events</span>
        </div>
        
        <div class="row g-4">
            <?php foreach ($upcomingEvents as $index => $event): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="event-card h-100">
                        <?php if (!empty($event['image'])): ?>
                            <div class="event-image-wrapper">
                                <img src="<?php echo SITE_URL . '/img/' . htmlspecialchars($event['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                     class="event-image">
                            </div>
                        <?php else: ?>
                            <div class="event-image-placeholder">
                                <i class="fas fa-calendar-alt fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="event-card-body">
                            <h5 class="event-card-title">
                                <a href="/events/<?php echo htmlspecialchars($event['id']); ?>">
                                    <?php echo htmlspecialchars($event['title']); ?>
                                </a>
                            </h5>
                            
                            <div class="event-card-meta">
                                <p class="mb-2">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('d M Y, g:i A', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?>
                                </p>
                            </div>
                            
                            <div class="event-card-description">
                                <?php echo nl2br(htmlspecialchars(substr($event['description'], 0, 120))); ?>...
                            </div>
                            
                            <?php 
                            $eventDateTime = strtotime($event['event_date'] . ' ' . $event['event_time']);
                            $currentDateTime = time();
                            if ($eventDateTime > $currentDateTime): 
                            ?>
                                <span class="badge badge-success mt-2">Upcoming</span>
                            <?php elseif (date('Y-m-d', $eventDateTime) == date('Y-m-d', $currentDateTime)): ?>
                                <span class="badge badge-warning mt-2">Today</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="upcoming-event.php" class="btn btn-primary btn-lg">
                <i class="fas fa-calendar-check me-2"></i>View All Events
            </a>
        </div>
    </div>
</div>

<style>
.event-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.event-image-wrapper {
    height: 200px;
    overflow: hidden;
    background: #f8f9fa;
}

.event-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-card:hover .event-image {
    transform: scale(1.05);
}

.event-image-placeholder {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.event-card-body {
    padding: 1.5rem;
}

.event-card-title {
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

.event-card-title a {
    color: var(--primary-color);
    text-decoration: none;
}

.event-card-title a:hover {
    color: var(--primary-dark);
}

.event-card-meta {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.event-card-meta i {
    color: var(--primary-color);
    margin-right: 5px;
    width: 16px;
}

.event-card-description {
    color: #495057;
    font-size: 0.95rem;
    line-height: 1.6;
}

.badge-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
}

.badge-warning {
    background: linear-gradient(135deg, #ffc107, #ff9800);
    color: #212529;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
}

@media (max-width: 767.98px) {
    .event-image-wrapper,
    .event-image-placeholder {
        height: 180px;
    }
    
    .event-card-body {
        padding: 1.25rem;
    }
    
    .event-card-title {
        font-size: 1.1rem;
    }
}
</style>
<?php endif; ?>