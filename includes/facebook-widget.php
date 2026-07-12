<?php
/**
 * Facebook Widget Component
 * Displays Facebook page embed if URL is configured
 */
?>

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