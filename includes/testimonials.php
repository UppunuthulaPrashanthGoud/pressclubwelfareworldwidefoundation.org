<?php
/**
 * Testimonials Component
 * Displays member testimonials with ratings and images
 */
?>

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