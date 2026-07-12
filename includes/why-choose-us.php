<?php
/**
 * why-choose-us.php
 * Fetches and displays the reasons why people choose the foundation.
 */

if (!isset($db)) {
    require_once 'config/config.php';
    $db = getDbConnection();
}

try {
    $stmt = $db->prepare("SELECT * FROM why_choose_us WHERE status = 'active' ORDER BY sort_order ASC");
    $stmt->execute();
    $reasons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Ideally log error here
    $reasons = [];
}
?>

<?php if (!empty($reasons)): ?>
<section class="why-choose-us py-5" style="background-color: var(--white-bg); position: relative; overflow: hidden;">
    <!-- Subtle Background Pattern -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.03; background-image: radial-gradient(var(--primary-color) 0.5px, transparent 0.5px); background-size: 30px 30px; pointer-events: none;"></div>
    
    <div class="container relative-content">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="section-heading text-uppercase"><span>Why Choose Us?</span></h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">We are dedicated to celebrating human achievement and academic excellence through a rigorous and prestigious selection process.</p>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($reasons as $index => $reason): ?>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="reason-card h-100 p-4 text-center border-0 shadow-sm rounded-4 transition-all" 
                         style="background: #fff; border: 1px solid var(--border-lighter) !important;">
                        <div class="icon-box mb-4 mx-auto d-flex align-items-center justify-content-center" 
                             style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 20px; color: var(--gold-color); font-size: 1.8rem; box-shadow: 0 10px 20px var(--shadow-medium);">
                            <i class="<?php echo htmlspecialchars($reason['icon']); ?>"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color: var(--primary-color); font-size: 1.25rem;">
                            <?php echo htmlspecialchars($reason['title']); ?>
                        </h4>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">
                            <?php echo htmlspecialchars($reason['description']); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.reason-card {
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.reason-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px var(--shadow-medium) !important;
    border-color: var(--gold-color) !important;
}
.reason-card:hover .icon-box {
    transform: rotateY(180deg);
    background: var(--gradient-primary-reverse);
}
.icon-box {
    transition: all 0.6s ease;
}
</style>
<?php endif; ?>