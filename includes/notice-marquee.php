<?php
// Ensure database connection exists
if (!isset($db)) {
    require_once 'config/config.php';
    $db = getDbConnection();
}

try {
    $stmt = $db->prepare("SELECT notice_text FROM notice_board WHERE status = 'active' ORDER BY created_at DESC");
    $stmt->execute();
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $notices = [];
}
?>
<?php if (!empty($notices)): ?>
<div class="notice-marquee-container" style="background: var(--primary-color, #0a1f44); color: #fff; padding: 10px 0; border-bottom: 2px solid var(--secondary-color, #e6b325); overflow: hidden; display: flex; align-items: center;">
    <div class="notice-label" style="background: var(--secondary-color, #e6b325); color: #000; padding: 5px 15px; font-weight: bold; text-transform: uppercase; z-index: 10; margin-left: 15px; border-radius: 4px; white-space: nowrap;">
        <i class="fas fa-bell me-2"></i> Notice Board
    </div>
    <div class="marquee-content" style="flex: 1; overflow: hidden; margin-left: 15px;">
        <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();" style="font-weight: 500; font-size: 1.1rem; padding-top: 3px;">
            <?php foreach ($notices as $notice): ?>
                <span class="notice-item" style="margin-right: 50px;">
                    <i class="fas fa-star" style="color: var(--secondary-color, #e6b325); font-size: 0.8rem; margin-right: 10px; vertical-align: middle;"></i>
                    <?php echo htmlspecialchars($notice['notice_text']); ?>
                </span>
            <?php endforeach; ?>
        </marquee>
    </div>
</div>
<?php endif; ?>