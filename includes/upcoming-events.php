<?php
/**
 * Upcoming Events Component - Card Layout
 * Uses the same design and CSS classes as upcoming-event.php
 */
?>

<div class="container-fluid py-5" style="background: var(--light-bg);">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading">
                    <span>Upcoming Events</span>
                </div>
            </div>
        </div>

        <!-- Events Section -->
        <div class="event-content">
            <?php if (!empty($upcoming_events)): ?>
                <div class="row g-4">
                    <?php foreach ($upcoming_events as $index => $event): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="card-custom event-card h-100">
                                <?php if (!empty($event['image'])): ?>
                                    <div class="event-image-container adaptive">
                                        <img src="<?php echo SITE_URL . '/img/' . htmlspecialchars($event['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                             class="img-fluid rounded-top event-image">
                                    </div>
                                <?php else: ?>
                                    <div class="event-image-container placeholder-image bg-secondary text-white d-flex align-items-center justify-content-center rounded-top">
                                        <i class="fas fa-image fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body event-details">
                                    <h5 class="card-title event-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <div class="event-meta">
                                        <p class="card-text text-muted small mb-2">
                                            <span><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($event['location'] ?? 'Location not specified'); ?></span> | 
                                            <span><i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($event['event_date'])); ?><?php if (!empty($event['event_time'])): ?>, <?php echo date('g:i A', strtotime($event['event_time'])); ?><?php endif; ?></span>
                                        </p>
                                    </div>
                                    <?php if (!empty($event['description'])): ?>
                                        <div class="event-description">
                                            <p class="card-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Event Status Badge -->
                                    <div class="mt-3">
                                        <?php 
                                        $eventDateTime = strtotime($event['event_date'] . ' ' . ($event['event_time'] ?? '00:00:00'));
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

                <!-- View All Button -->
                <div class="text-center mt-4" data-aos="fade-up">
                    <a href="upcoming-event.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-check me-2"></i>View All Events
                    </a>
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