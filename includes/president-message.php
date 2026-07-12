<?php
/**
 * President's Message Component
 * Displays trustee's message with image and content
 */
?>

<!-- PRESIDENT'S MESSAGE SECTION -->
<?php if ($president_message): ?>
<div class="container-fluid my-5 bg-light py-5">
    <h3 class="section-heading text-center"><span>FOUNDER’S DESK</span></h3>
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

<style>
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
</style>