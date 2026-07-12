<?php
/**
 * Our Works Section Component
 * Displays organization works/initiatives in a grid layout
 */

// Get works for homepage - limit to 6
$stmt = $db->prepare("SELECT * FROM ourworks ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$homepage_works = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- OUR WORKS SECTION -->
<?php if (!empty($homepage_works)): ?>
<div class="container-fluid my-5 ourworks-full-width">
    <h3 class="section-heading text-center"><span>Our Works</span></h3>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="ourworks-container">
                    <div class="row g-4">
                        <?php foreach ($homepage_works as $index => $work): ?>
                            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                <div class="work-card">
                                    <div class="card-custom text-center h-100">
                                        <div class="mb-3">
                                            <?php if (!empty($work['image'])): ?>
                                                <div class="work-image-wrapper">
                                                    <img src="<?php echo SITE_URL; ?>/img/ourworks/<?php echo htmlspecialchars($work['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($work['name']); ?>" 
                                                         class="work-image">
                                                    <div class="work-overlay">
                                                        <i class="fas fa-briefcase"></i>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="work-image-placeholder">
                                                    <i class="fas fa-briefcase fa-3x"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="work-content">
                                            <h5><?php echo htmlspecialchars($work['name']); ?></h5>
                                            <p><?php 
                                                $content = strip_tags($work['content']);
                                                $preview = mb_substr($content, 0, 120);
                                                echo htmlspecialchars($preview) . (mb_strlen($content) > 120 ? '...' : '');
                                            ?></p>
                                            
                                            <!-- Read More Button -->
                                            <div class="mt-3">
                                                <a href="<?php echo SITE_URL; ?>/ourworks-details.php?id=<?php echo $work['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-book-open me-1"></i> Read More
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="<?php echo SITE_URL; ?>/ourworks.php" class="btn btn-primary">View All Our Works</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Our Works Section Styling */
.ourworks-full-width {
    background: #ffffff;
    padding: 3rem 0;
}

.ourworks-container .card-custom {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    height: 100%;
}

.ourworks-container .card-custom:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.work-image-wrapper {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
    border-radius: 8px;
    background: var(--light-bg, #f8f9fa);
}

.work-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.work-card:hover .work-image {
    transform: scale(1.1);
}

.work-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 123, 255, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.work-card:hover .work-overlay {
    opacity: 1;
}

.work-overlay i {
    color: white;
    font-size: 3rem;
}

.work-image-placeholder {
    width: 100%;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: var(--text-muted, #6c757d);
    border-radius: 8px;
}

.work-content {
    padding: 1rem 0.5rem;
}

.work-content h5 {
    color: var(--primary-color, #007bff);
    font-family: 'Bakbak One', sans-serif;
    font-size: 1.3rem;
    margin-bottom: 1rem;
    line-height: 1.3;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.work-content p {
    color: var(--text-color, #333);
    line-height: 1.7;
    font-size: 0.95rem;
    margin-bottom: 0;
}

.work-content .btn-sm {
    font-size: 0.85rem;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.work-content .btn-primary {
    background-color: var(--primary-color, #007bff);
    border-color: var(--primary-color, #007bff);
}

.work-content .btn-primary:hover {
    background-color: var(--primary-dark, #0056b3);
    border-color: var(--primary-dark, #0056b3);
    transform: translateY(-2px);
}

.section-heading {
    margin-bottom: 40px;
}

.section-heading span {
    position: relative;
    padding-bottom: 10px;
    font-family: 'Bakbak One', sans-serif;
    font-size: 2rem;
    color: var(--primary-color, #007bff);
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

/* Responsive Design */
@media (max-width: 991.98px) {
    .work-image-wrapper,
    .work-image-placeholder {
        height: 180px;
    }
    
    .work-content h5 {
        font-size: 1.2rem;
        min-height: 50px;
    }
}

@media (max-width: 767.98px) {
    .ourworks-full-width {
        padding: 2rem 0;
    }
    
    .work-image-wrapper,
    .work-image-placeholder {
        height: 160px;
    }
    
    .work-content h5 {
        font-size: 1.15rem;
        min-height: auto;
    }
    
    .work-content p {
        font-size: 0.9rem;
    }
    
    .section-heading span {
        font-size: 1.5rem;
    }
}

@media (max-width: 575.98px) {
    .work-image-wrapper,
    .work-image-placeholder {
        height: 150px;
    }
    
    .work-content h5 {
        font-size: 1.1rem;
    }
    
    .work-content {
        padding: 1rem 0.25rem;
    }
    
    .work-content .btn-sm {
        font-size: 0.8rem;
        padding: 0.35rem 0.8rem;
    }
}
</style>