<?php
session_start();
require_once 'config/config.php';

try {
    $db = getDbConnection();
    
    // Pagination setup
    $notices_per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page);
    $offset = ($page - 1) * $notices_per_page;
    
    // Fetch total number of notices
    $stmt = $db->prepare("SELECT COUNT(*) FROM notices WHERE status = 'active'");
    $stmt->execute();
    $total_notices = $stmt->fetchColumn();
    $total_pages = ceil($total_notices / $notices_per_page);
    
    // Fetch notices for the current page
    $stmt = $db->prepare("SELECT * FROM notices WHERE status = 'active' ORDER BY date DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $notices_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    logError("Database error in all-notices.php: " . $e->getMessage());
    $notices = [];
    $total_pages = 1;
}

$pageTitle = "All Notices - Latest Updates";
include 'header.php';
include 'navbar.php';
?>

<style>
/* All Notices Page Styles */
.all-notices-container {
    padding: 60px 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.notices-header {
    background: white;
    border-radius: 15px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    text-align: center;
}

.notices-title {
    font-size: 32px;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0;
    font-family: "Bakbak One", sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.notices-title i {
    color: #ffd700;
    font-size: 24px;
}

.notice-item {
    display: flex;
    align-items: center;
    padding: 20px;
    margin-bottom: 20px;
    background: white;
    border-radius: 10px;
    border-left: 4px solid var(--primary-color);
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    cursor: pointer;
    position: relative;
}

.notice-item:hover {
    transform: translateX(5px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    border-left-color: var(--primary-dark);
    color: inherit;
    text-decoration: none;
}

.notice-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    flex-shrink: 0;
}

.notice-icon i {
    color: white;
    font-size: 24px;
}

.notice-content {
    flex: 1;
    min-width: 0;
}

.notice-title {
    font-weight: 600;
    font-size: 16px;
    color: #2c3e50;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.new-badge {
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    color: white;
    font-size: 10px;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: bold;
    animation: pulse 2s ease-in-out infinite;
    flex-shrink: 0;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.notice-date {
    font-size: 12px;
    color: #7f8c8d;
    display: flex;
    align-items: center;
    gap: 5px;
}

.notice-arrow {
    color: var(--primary-color);
    font-size: 16px;
    opacity: 0;
    transition: all 0.3s ease;
    margin-left: 15px;
    flex-shrink: 0;
}

.notice-item:hover .notice-arrow {
    opacity: 1;
    transform: translateX(5px);
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.page-item {
    display: inline-flex;
    align-items: center;
}

.page-link {
    padding: 10px 15px;
    border-radius: 8px;
    background: white;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    text-decoration: none;
}

.page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    border-color: var(--primary-dark);
}

.page-item.disabled .page-link {
    background: #e9ecef;
    color: #6c757d;
    border-color: #e9ecef;
    cursor: not-allowed;
    pointer-events: none;
}

.sidebar-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
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

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.meta-item i {
    color: var(--primary-color);
    font-size: 18px;
}

@media (max-width: 991.98px) {
    .all-notices-container {
        padding: 40px 0;
    }
    
    .notices-header {
        padding: 30px 20px;
    }
    
    .notices-title {
        font-size: 24px;
    }
    
    .notices-title i {
        font-size: 20px;
    }
    
    .notice-item {
        padding: 15px;
        margin-bottom: 15px;
    }
}

@media (max-width: 767.98px) {
    .all-notices-container {
        padding: 30px 0;
    }
    
    .notices-header {
        padding: 20px;
        border-radius: 10px;
    }
    
    .notices-title {
        font-size: 20px;
    }
    
    .notice-icon {
        width: 40px;
        height: 40px;
    }
    
    .notice-icon i {
        font-size: 20px;
    }
    
    .notice-title {
        font-size: 14px;
    }
    
    .notice-date {
        font-size: 11px;
    }
}
</style>

<!-- Full-Width Notices Section -->
<div class="container-fluid navbar-margin-pusher">
    <div class="all-notices-container">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Notices Header -->
                    <div class="notices-header">
                        <h1 class="notices-title">
                            <i class="fas fa-bullhorn"></i>
                            All Notices
                            <i class="fas fa-bullhorn"></i>
                        </h1>
                    </div>

                    <!-- Notices List -->
                    <?php if (count($notices) > 0): ?>
                        <?php foreach ($notices as $notice): ?>
                            <?php 
                            // Check if notice is new (within last 7 days)
                            $notice_date = strtotime($notice['date']);
                            $current_date = time();
                            $days_diff = floor(($current_date - $notice_date) / (60 * 60 * 24));
                            ?>
                            <a href="notice-details.php?id=<?php echo $notice['id']; ?>" class="notice-item">
                                <div class="notice-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="notice-content">
                                    <div class="notice-title">
                                        <?php echo htmlspecialchars($notice['title']); ?>
                                        <?php if ($days_diff <= 7): ?>
                                            <span class="new-badge">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notice-date">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('M d, Y', strtotime($notice['date'])); ?>
                                    </div>
                                </div>
                                <div class="notice-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Notices pagination">
                            <ul class="pagination">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            No notices found.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sidebar-section">
                        <h3 class="sidebar-title">
                            <i class="fas fa-info-circle"></i>
                            Need Help?
                        </h3>
                        <p style="font-size: 14px; color: #666;">
                            For any queries regarding notices, please contact:
                        </p>
                        <div class="meta-item">
                            <i class="fas fa-phone"></i>
                            <span>+91 7454838285</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <span style="font-size: 12px;">official.ndfoundation@gmail.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Ensure all notice links work properly
document.addEventListener('DOMContentLoaded', function() {
    const noticeItems = document.querySelectorAll('.notice-item');
    
    noticeItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            // Allow the default link behavior
            const href = this.getAttribute('href');
            if (href && !e.ctrlKey && !e.metaKey) {
                window.location.href = href;
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>