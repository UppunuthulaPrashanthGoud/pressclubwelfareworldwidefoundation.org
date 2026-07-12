<?php
/**
 * Hero Slider Component
 * Displays the main carousel slider with images from database
 */
?>

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
            <img src="img/sliders/<?php echo htmlspecialchars($slider['image']); ?>" class="d-block w-100" alt="NGO Program Slider Image">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="opacity:0.1;background: var(--primary-color);"></div>
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