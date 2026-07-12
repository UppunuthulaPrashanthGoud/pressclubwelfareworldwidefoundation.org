<?php
/**
 * About Section Component
 * Displays about us section with image and description
 */
?>

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
</style>