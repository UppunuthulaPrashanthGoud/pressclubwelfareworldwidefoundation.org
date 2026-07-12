<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

$pageTitle = 'Dashboard';
$db = getDbConnection();

// Get user-specific statistics based on role
$userRole = $_SESSION['user_type'] ?? 'member';
$userId = $_SESSION['user_id'] ?? 0;

try {
    // Common statistics for all users
    
    if (isAdmin()) {
        // Admin sees everything
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'approved'");
        $stats['total_members'] = $stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM donations WHERE status = 'completed'");
        $result = $stmt->fetch();
        error_log("Total Donations: " . $result['total']); // Log for debugging
        $stats['total_donations'] = $result['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM events WHERE status = 'active'");
        $stats['total_events'] = $stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM news WHERE status = 'active'");
        $stats['total_news'] = $stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'pending'");
        $stats['pending_approvals'] = $stmt->fetch()['total'];
        
        // Recent activities for admin
        $stmt = $db->prepare("SELECT * FROM recent_activities WHERE status = 'active' ORDER BY activity_date DESC LIMIT 5");
        $stmt->execute();
        $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif (isCoordinator()) {
        // Coordinator sees limited data
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'approved'");
        $stats['total_members'] = $stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM events WHERE status = 'active'");
        $stats['total_events'] = $stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM news WHERE status = 'active'");
        $stats['total_news'] = $stmt->fetch()['total'];
        
        // Recent activities for coordinator
        $stmt = $db->prepare("SELECT * FROM recent_activities WHERE status = 'active' ORDER BY activity_date DESC LIMIT 3");
        $stmt->execute();
        $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif (isMember()) {
        // Regular member sees only their own data
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? OR email = ?");
        $stmt->execute([$userId, $_SESSION['user_email']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats['membership_status'] = $user_data['status'] ?? 'pending';
        $stats['member_id'] = $user_data['registration_id'] ?? 'N/A';
        
        $recent_activities = [];
    }
    
    // Recent members (admin and coordinator only)
    if (isAdmin() || isCoordinator()) {
        $stmt = $db->prepare("SELECT * FROM users WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $recent_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $recent_members = [];
    }
    
} catch(PDOException $e) {
    $error = "Error loading data: " . $e->getMessage();
    error_log($error); // Log the error
    $stats = [];
    $recent_activities = [];
    $recent_members = [];
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-tachometer-alt me-3"></i>Dashboard
                <small class="text-muted">(<?php echo ucfirst($userRole); ?>)</small>
            </h1>
            <div class="page-actions">
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- Role-based Statistics Cards -->
        <div class="stats-grid">
            <?php if (isAdmin()): ?>
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_members'] ?? 0); ?></h3>
                        <p>Total Members</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>₹<?php echo number_format($stats['total_donations'] ?? 0, 2); ?></h3>
                        <p>Total Donations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_events'] ?? 0); ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_news'] ?? 0); ?></h3>
                        <p>News</p>
                    </div>
                </div>

                <?php if (($stats['pending_approvals'] ?? 0) > 0): ?>
                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['pending_approvals']); ?></h3>
                        <p>Pending Approvals</p>
                    </div>
                </div>
                <?php endif; ?>

            <?php elseif (isCoordinator()): ?>
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_members'] ?? 0); ?></h3>
                        <p>Total Members</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_events'] ?? 0); ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_news'] ?? 0); ?></h3>
                        <p>News</p>
                    </div>
                </div>

            <?php elseif (isMember()): ?>
                <!-- Member Dashboard -->
                <div class="stat-card">
                    <div class="stat-icon <?php echo ($stats['membership_status'] ?? '') === 'approved' ? 'success' : 'warning'; ?>">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo htmlspecialchars($stats['member_id'] ?? 'N/A'); ?></h3>
                        <p>Member ID</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon <?php echo ($stats['membership_status'] ?? '') === 'approved' ? 'success' : 'warning'; ?>">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo ucfirst($stats['membership_status'] ?? 'Pending'); ?></h3>
                        <p>Membership Status</p>
                    </div>
                </div>

            <?php endif; ?>
        </div>

        <!-- Role-based Dashboard Sections -->
        <div class="row">
            <?php if (isAdmin() || isCoordinator()): ?>
            <!-- Recent Activities -->
            <div class="col-lg-6">
                <div class="admin-card">
                    <div class="card-header">
                        <i class="fas fa-clock"></i>
                        Recent Activities
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <?php if (!empty($recent_activities)): ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-bell text-primary"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h6><?php echo htmlspecialchars($activity['title']); ?></h6>
                                            <p class="text-muted small mb-0">
                                                <?php echo date('d M Y', strtotime($activity['activity_date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted">No activities found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Members -->
            <div class="col-lg-6">
                <div class="admin-card">
                    <div class="card-header">
                        <i class="fas fa-users"></i>
                        Recent Members
                    </div>
                    <div class="card-body">
                        <div class="member-list">
                            <?php if (!empty($recent_members)): ?>
                                <?php foreach ($recent_members as $member): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <?php if (!empty($member['profile_image'])): ?>
                                                <img src="<?php echo SITE_URL . '/uploads/profiles/' . htmlspecialchars($member['profile_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                                     class="rounded-circle" width="40" height="40">
                                            <?php else: ?>
                                                <div class="avatar-placeholder">
                                                    <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="member-info">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($member['name']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo date('d M Y', strtotime($member['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted">No new members found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php elseif (isMember()): ?>
            <!-- Member Profile Section -->
            <div class="col-lg-12">
                <div class="admin-card">
                    <div class="card-header">
                        <i class="fas fa-user"></i>
                        My Profile
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                                <p><strong>Membership Status:</strong> 
                                    <span class="badge bg-<?php echo ($stats['membership_status'] ?? '') === 'approved' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($stats['membership_status'] ?? 'pending'); ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <?php if (isset($user_data) && $user_data): ?>
                                <p><strong>Member ID:</strong> <?php echo htmlspecialchars($user_data['registration_id'] ?? 'N/A'); ?></p>
                                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($user_data['mobile'] ?? 'N/A'); ?></p>
                                <p><strong>Designation:</strong> <?php echo htmlspecialchars($user_data['profession'] ?? 'Member'); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($user_data['profile_image'])): ?>
                            <div class="col-md-12 text-center mt-3">
                                <img src="<?php echo SITE_URL . '/uploads/profiles/' . htmlspecialchars($user_data['profile_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" 
                                     class="rounded-circle" width="100" height="100">
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Role-based Quick Actions -->
        <!-- <div class="admin-card">
            <div class="card-header">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (isAdmin()): ?>
                        <div class="col-md-3 mb-3">
                            <a href="members.php?action=add" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-plus mb-2"></i><br>
                                Add New Member
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="events.php?action=add" class="btn btn-outline-success w-100">
                                <i class="fas fa-calendar-plus mb-2"></i><br>
                                Add New Event
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="news.php?action=add" class="btn btn-outline-info w-100">
                                <i class="fas fa-newspaper mb-2"></i><br>
                                Add New News
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="settings.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-cog mb-2"></i><br>
                                Settings
                            </a>
                        </div>
                    <?php elseif (isCoordinator()): ?>
                        <div class="col-md-4 mb-3">
                            <a href="events.php?action=add" class="btn btn-outline-success w-100">
                                <i class="fas fa-calendar-plus mb-2"></i><br>
                                Add New Event
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="news.php?action=add" class="btn btn-outline-info w-100">
                                <i class="fas fa-newspaper mb-2"></i><br>
                                Add New News
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="members.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-users mb-2"></i><br>
                                View Members
                            </a>
                        </div>
                    <?php elseif (isMember()): ?>
                        <div class="col-md-6 mb-3">
                            <a href="profile.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-edit mb-2"></i><br>
                                Update Profile
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="id-card-generator.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-id-card mb-2"></i><br>
                                Download ID Card
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> -->

<?php include 'includes/sidebar.php'; ?>

<style>
.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    margin-right: 15px;
    margin-top: 5px;
}

.activity-content h6 {
    margin: 0;
    font-size: 0.9rem;
}

.member-list {
    max-height: 300px;
    overflow-y: auto;
}

.avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover,
.btn-outline-warning:hover {
    transform: translateY(-2px);
}
</style>

<?php include 'includes/footer.php'; ?>