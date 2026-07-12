<?php
require_once 'config/config.php';

// Get notice ID from URL
$notice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($notice_id <= 0) {
    header('Location: all-notices.php');
    exit;
}

try {
    $db = getDbConnection();
    
    // Fetch notice details
    $stmt = $db->prepare("SELECT * FROM notices WHERE id = :id AND status = 'active'");
    $stmt->bindParam(':id', $notice_id);
    $stmt->execute();
    $notice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notice) {
        header('Location: all-notices.php');
        exit;
    }
    
    // Fetch related notices (same category or recent)
    $stmt = $db->prepare("SELECT * FROM notices WHERE id != :id AND status = 'active' ORDER BY date DESC LIMIT 5");
    $stmt->bindParam(':id', $notice_id);
    $stmt->execute();
    $related_notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    logError("Database error in notice-details.php: " . $e->getMessage());
    header('Location: all-notices.php');
    exit;
}

$pageTitle = htmlspecialchars($notice['title']) . " - Notice Details";
include 'header.php';
include 'navbar.php';
?>

<style>
/* Notice Details Page Styles */
.notice-details-container {
    padding: 60px 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.notice-header {
    background: white;
    border-radius: 15px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.notice-title {
    font-size: 32px;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 20px;
    font-family: "Bakbak One", sans-serif;
}

.notice-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    padding: 20px 0;
    border-top: 2px solid #e9ecef;
    border-bottom: 2px solid #e9ecef;
    margin-bottom: 30px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #666;
}

.meta-item i {
    color: var(--primary-color);
    font-size: 18px;
}

.notice-badge {
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
    margin-bottom: 15px;
}

.notice-content {
    background: white;
    border-radius: 15px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    line-height: 1.8;
}

.notice-content h2, .notice-content h3 {
    color: var(--primary-color);
    margin-top: 30px;
    margin-bottom: 15px;
}

.notice-content p {
    margin-bottom: 15px;
    font-size: 16px;
    color: #333;
}

.content-text {
    font-size: 16px;
    line-height: 1.8;
    color: #333;
}

.action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 30px;
}

.action-btn {
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-back {
    background: #6c757d;
    color: white;
}

.btn-back:hover {
    background: #5a6268;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
}

.btn-share {
    background: #28a745;
    color: white;
}

.btn-share:hover {
    background: #218838;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.sidebar-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.sidebar-title {
    font-size: 20px;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 20px;
    font-family: "Bakbak One", sans-serif;
    display: flex;
    align-items: center;
    gap: 10px;
}

.related-notice {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 15px;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.related-notice:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(5px);
    text-decoration: none;
}

.related-notice-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    flex-shrink: 0;
}

.related-notice:hover .related-notice-icon {
    background: white;
    color: var(--primary-color);
}

.related-notice-content {
    flex: 1;
}

.related-notice-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 5px;
}

.related-notice-date {
    font-size: 12px;
    opacity: 0.7;
}

/* Share Modal */
.share-options {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
}

.share-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
}

.share-btn:hover {
    transform: scale(1.1);
    color: white;
}

.share-facebook { background: #3b5998; }
.share-twitter { background: #1da1f2; }
.share-whatsapp { background: #25d366; }
.share-linkedin { background: #0077b5; }
.share-email { background: #ea4335; }

@media (max-width: 991.98px) {
    .notice-header,
    .notice-content {
        padding: 30px 20px;
    }
    
    .notice-title {
        font-size: 24px;
    }
    
    .notice-meta {
        gap: 15px;
    }
}

@media (max-width: 767.98px) {
    .notice-details-container {
        padding: 40px 0;
    }
    
    .notice-header,
    .notice-content,
    .sidebar-section {
        padding: 20px;
        border-radius: 10px;
    }
    
    .notice-title {
        font-size: 20px;
    }
    
    .notice-meta {
        gap: 10px;
    }
    
    .meta-item {
        font-size: 12px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="container-fluid navbar-margin-pusher">
    <div class="notice-details-container">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Notice Header -->
                    <div class="notice-header">
                        <?php 
                        // Check if notice is new (within last 7 days)
                        $notice_date = strtotime($notice['date']);
                        $current_date = time();
                        $days_diff = floor(($current_date - $notice_date) / (60 * 60 * 24));
                        if ($days_diff <= 7):
                        ?>
                        <span class="notice-badge">NEW</span>
                        <?php endif; ?>
                        
                        <h1 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h1>
                        
                        <div class="notice-meta">
                            <div class="meta-item">
                                <i class="far fa-calendar-alt"></i>
                                <span><?php echo date('F d, Y', strtotime($notice['date'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="far fa-clock"></i>
                                <span><?php echo date('h:i A', strtotime($notice['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Notice Content -->
                    <div class="notice-content">
                        <div class="content-text">
                            <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="all-notices.php" class="action-btn btn-back">
                                <i class="fas fa-arrow-left"></i>
                                Back to Notices
                            </a>
                            <button class="action-btn btn-share" data-bs-toggle="modal" data-bs-target="#shareModal">
                                <i class="fas fa-share-alt"></i>
                                Share
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Related Notices -->
                    <?php if (count($related_notices) > 0): ?>
                    <div class="sidebar-section">
                        <h3 class="sidebar-title">
                            <i class="fas fa-list"></i>
                            Related Notices
                        </h3>
                        <?php foreach ($related_notices as $related): ?>
                        <a href="notice-details.php?id=<?php echo $related['id']; ?>" class="related-notice">
                            <div class="related-notice-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="related-notice-content">
                                <div class="related-notice-title">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </div>
                                <div class="related-notice-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($related['date'])); ?>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Contact Info -->
                    <div class="sidebar-section">
                        <h3 class="sidebar-title">
                            <i class="fas fa-info-circle"></i>
                            Need Help?
                        </h3>
                        <p style="font-size: 14px; color: #666;">
                            For any queries regarding this notice, please contact:
                        </p>
                        <div class="meta-item" style="margin-bottom: 10px;">
                            <i class="fas fa-phone"></i>
                            <span>+91 7454838285</span>
                        </div>
                        <div class="meta-item" style="margin-bottom: 10px;">
                            <i class="fas fa-envelope"></i>
                            <span style="font-size: 12px;">official.ndfoundation@gmail.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">
                    <i class="fas fa-share-alt"></i> Share this Notice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="share-options">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/notice-details.php?id=' . $notice_id); ?>" 
                       target="_blank" class="share-btn share-facebook" title="Share on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/notice-details.php?id=' . $notice_id); ?>&text=<?php echo urlencode($notice['title']); ?>" 
                       target="_blank" class="share-btn share-twitter" title="Share on Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($notice['title'] . ' - ' . SITE_URL . '/notice-details.php?id=' . $notice_id); ?>" 
                       target="_blank" class="share-btn share-whatsapp" title="Share on WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(SITE_URL . '/notice-details.php?id=' . $notice_id); ?>" 
                       target="_blank" class="share-btn share-linkedin" title="Share on LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="mailto:?subject=<?php echo urlencode($notice['title']); ?>&body=<?php echo urlencode('Check out this notice: ' . SITE_URL . '/notice-details.php?id=' . $notice_id); ?>" 
                       class="share-btn share-email" title="Share via Email">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>