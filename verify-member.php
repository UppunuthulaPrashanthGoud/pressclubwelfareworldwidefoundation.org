<?php
require_once 'config/config.php';

// Set page title
$page_meta_title = 'Official Verification - ' . (defined('SITE_NAME') ? SITE_NAME : 'Honorary Doctorate Awards Foundation');

// Initialize variables
$member = null;
$error = '';
$search_query = '';
$search_performed = false;
$result_type = 'member'; // member, award, certificate

// Handle Search
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['id'])) {
    $search_query = trim($_POST['registration_id'] ?? $_GET['id'] ?? '');
    
    if (!empty($search_query)) {
        $search_performed = true;
        try {
            $db = getDbConnection();
            
            // 1. First check USERS table (Members)
            $stmt = $db->prepare("SELECT * FROM users WHERE registration_id = ? LIMIT 1");
            $stmt->execute([$search_query]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $member = $user;
                $result_type = 'member';
            } else {
                // 2. Check HONORARY AWARDS table
                $stmt = $db->prepare("SELECT * FROM honorary_awards WHERE award_no = ? OR registration_no = ? LIMIT 1");
                $stmt->execute([$search_query, $search_query]);
                $award = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($award) {
                    $result_type = 'award';
                    // Map award data to common structure
                    $member = [
                        'name' => $award['recipient_name'],
                        'registration_id' => $award['award_no'],
                        'designation' => $award['award_name'], // Award Name as designation
                        'profile_image' => $award['photo_path'],
                        'membership_type' => 'Honorary Award',
                        'district' => $award['city'],
                        'state' => $award['state'],
                        'created_at' => $award['award_date'],
                        'valid_from' => $award['award_date'],
                        'valid_until' => null, // Lifetime
                        'status' => $award['status'],
                        'sdw_name' => '', 
                        'sdw_type' => ''
                    ];
                } else {
                    // 3. Check CERTIFICATES table
                    $stmt = $db->prepare("SELECT * FROM certificates WHERE certificate_no = ? LIMIT 1");
                    $stmt->execute([$search_query]);
                    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($cert) {
                        $result_type = 'certificate';
                        // Map certificate data to common structure
                        $member = [
                            'name' => $cert['recipient_name'],
                            'registration_id' => $cert['certificate_no'],
                            'designation' => $cert['post_name'], // Post/Designation
                            'profile_image' => $cert['photo_path'],
                            'membership_type' => $cert['certificate_type'],
                            'district' => 'N/A',
                            'state' => 'N/A',
                            'created_at' => $cert['issue_date'],
                            'valid_from' => $cert['issue_date'],
                            'valid_until' => $cert['end_date'],
                            'status' => $cert['status'],
                            'sdw_name' => '',
                            'sdw_type' => ''
                        ];
                        
                        // Try to enrich location from user_id if linked
                        if (!empty($cert['user_id'])) {
                            $uStmt = $db->prepare("SELECT state, district, sdw_name, sdw_type FROM users WHERE id = ?");
                            $uStmt->execute([$cert['user_id']]);
                            $uData = $uStmt->fetch(PDO::FETCH_ASSOC);
                            if ($uData) {
                                $member['state'] = $uData['state'];
                                $member['district'] = $uData['district'];
                                $member['sdw_name'] = $uData['sdw_name'];
                                $member['sdw_type'] = $uData['sdw_type'];
                            }
                        }
                    }
                }
            }
            
            if (!$member) {
                $error = 'No official record found with ID: ' . htmlspecialchars($search_query);
            }
            
        } catch (Exception $e) {
            $error = 'System error: Unable to verify at this time.';
            error_log($e->getMessage());
        }
    } else {
        $error = 'Please enter a valid Registration or Certificate ID.';
    }
}

include 'header.php';
include 'navbar.php';
?>

<!-- Add Google Font for the heading -->
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<main class="verify-page-wrapper py-5">
    <div class="container">
        
        <!-- Search Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-4">
                    <h2 class="section-heading text-uppercase"><span>Official Verification</span></h2>
                    <p class="text-muted">Enter Member ID, Award Number, or Certificate ID to verify status.</p>
                </div>
                
                <div class="card search-card shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="verify-member.php" class="search-form">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white text-primary border-end-0">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="registration_id" class="form-control border-start-0 ps-0" 
                                       placeholder="Ex: GHDAF12345" 
                                       value="<?php echo htmlspecialchars($search_query); ?>" required>
                                <button type="submit" class="btn btn-search">
                                    Verify Record <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Section -->
        <?php if ($search_performed && empty($error) && $member): ?>
            <div class="row justify-content-center" data-aos="fade-up">
                <div class="col-lg-9">
                    <div class="verification-card prestige-border">
                        <!-- Card Top Bar -->
                        <div class="ver-card-header">
                            <div class="row align-items-center">
                                <div class="col-sm-2 text-center text-sm-start mb-2 mb-sm-0">
                                    <i class="fas fa-shield-alt fa-3x text-gold"></i>
                                </div>
                                <div class="col-sm-8 text-center">
                                    <h3 class="header-main-title">OFFICIAL VERIFICATION RECORD</h3>
                                    <p class="mb-0 subtitle-text">PRESS CLUB WELFARE WORLDWIDE FOUNDATION Honorary Doctorate Awards Foundation</p>
                                </div>
                                <div class="col-sm-2 text-center text-sm-end mt-2 mt-sm-0">
                                    <div class="verified-seal">
                                        <i class="fas fa-check-circle"></i>
                                        <span>VERIFIED</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="ver-card-body">
                            <!-- Watermark -->
                            <div class="ver-watermark">GHDAF</div>

                            <div class="row g-4 relative-content">
                                <!-- Photo Section -->
                                <div class="col-md-4 text-center">
                                    <div class="ver-photo-frame">
                                        <?php 
                                        $photoPath = 'img/default-user.png';
                                        
                                        if (!empty($member['profile_image'])) {
                                            $possiblePaths = [
                                                'img/profiles/' . $member['profile_image'],
                                                'img/users/' . $member['profile_image'],
                                                'uploads/profiles/' . $member['profile_image'], // Legacy fallback
                                                'img/' . $member['profile_image'] // Direct in img
                                            ];
                                            
                                            foreach ($possiblePaths as $path) {
                                                if (file_exists($path)) {
                                                    $photoPath = $path;
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($photoPath); ?>" 
                                             alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                             onerror="this.src='img/default-user.png'">
                                    </div>
                                    <div class="mt-3">
                                        <div class="reg-id-box">
                                            <small class="d-block text-uppercase text-gold">
                                                <?php echo ($result_type === 'award') ? 'Award Number' : (($result_type === 'certificate') ? 'Certificate ID' : 'Registration ID'); ?>
                                            </small>
                                            <span class="reg-id-val"><?php echo htmlspecialchars($member['registration_id']); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Details Section -->
                                <div class="col-md-8">
                                    <h2 class="member-display-name mb-1">
                                        <?php echo htmlspecialchars($member['name']); ?>
                                    </h2>
                                    <div class="mb-4">
                                        <span class="designation-pill">
                                            <i class="fas fa-award me-2"></i><?php echo htmlspecialchars($member['designation'] ?? 'Official Member'); ?>
                                        </span>
                                    </div>

                                    <div class="details-grid">
                                        <div class="row g-3">
                                            <?php if(!empty($member['sdw_name'])): ?>
                                            <div class="col-6">
                                                <label>Parent/Spouse Name</label>
                                                <p><?php echo htmlspecialchars(($member['sdw_type'] ?? '') . ' ' . $member['sdw_name']); ?></p>
                                            </div>
                                            <?php endif; ?>

                                            <div class="col-6">
                                                <label>Type</label>
                                                <p class="text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $member['membership_type'])); ?></p>
                                            </div>
                                            
                                            <?php if($member['district'] !== 'N/A'): ?>
                                            <div class="col-6">
                                                <label>Location</label>
                                                <p><?php echo htmlspecialchars($member['district'] . ', ' . $member['state']); ?></p>
                                            </div>
                                            <?php endif; ?>

                                            <div class="col-6">
                                                <label>Issued Date</label>
                                                <p><?php echo date('d M Y', strtotime($member['created_at'])); ?></p>
                                            </div>
                                            
                                            <div class="col-6">
                                                <label>Validity</label>
                                                <p>
                                                    <?php 
                                                    if (!empty($member['valid_until']) && $member['valid_until'] != '0000-00-00') {
                                                        echo date('d M Y', strtotime($member['valid_until']));
                                                    } else {
                                                        echo '<span class="text-success">Lifetime / Permanent</span>';
                                                    }
                                                    ?>
                                                </p>
                                            </div>

                                            <div class="col-6">
                                                <label>Status</label>
                                                <div class="status-indicator mt-1">
                                                    <?php if($member['status'] === 'approved' || $member['status'] === 'active'): ?>
                                                        <span class="status-badge approved"><i class="fas fa-check-circle me-1"></i> ACTIVE / VALID</span>
                                                    <?php elseif($member['status'] === 'pending'): ?>
                                                        <span class="status-badge pending"><i class="fas fa-hourglass-half me-1"></i> PENDING</span>
                                                    <?php else: ?>
                                                        <span class="status-badge rejected"><i class="fas fa-times-circle me-1"></i> INACTIVE</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="ver-card-footer">
                            <div class="d-flex justify-content-between align-items-center px-4">
                                <span class="footer-msg"><i class="fas fa-lock me-2"></i>Official Digital Record</span>
                                <span class="timestamp">Verified on: <?php echo date('d-m-Y H:i'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-5 no-print">
                        <button onclick="window.print()" class="btn btn-print shadow-sm">
                            <i class="fas fa-print me-2"></i> Download / Print
                        </button>
                    </div>
                </div>
            </div>

        <?php elseif ($search_performed && !empty($error)): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="error-box text-center shadow-sm">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3 text-gold"></i>
                        <h4 class="mb-2">Verification Failed</h4>
                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($error); ?></p>
                        <a href="verify-member.php" class="btn btn-link text-primary mt-3">Try again</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<style>
    /* Verification Page Theme Styles */
    :root {
        --prestige-navy: #002147;
        --prestige-gold: #d4af37;
        --prestige-dark: #001530;
    }

    .verify-page-wrapper {
        background-color: #f8f9fc;
        min-height: 80vh;
        font-family: 'Poppins', sans-serif;
    }

    .text-gold { color: var(--prestige-gold) !important; }

    /* Search Component */
    .search-card {
        border: none;
        border-top: 4px solid var(--prestige-gold);
        border-radius: 12px;
    }

    .btn-search {
        background: var(--prestige-navy);
        color: white;
        border: none;
        padding-left: 30px;
        padding-right: 30px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-search:hover {
        background: var(--prestige-gold);
        color: #000;
    }

    /* Premium Verification Card */
    .verification-card {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 33, 71, 0.1);
        position: relative;
    }

    .prestige-border {
        border: 1px solid rgba(212, 175, 55, 0.3);
    }

    .ver-card-header {
        background: var(--prestige-navy);
        padding: 25px;
        color: #fff;
        border-bottom: 5px solid var(--prestige-gold);
    }

    .header-main-title {
        font-family: 'Playfair Display', serif;
        letter-spacing: 2px;
        font-size: 1.5rem;
        margin-bottom: 5px;
    }

    .verified-seal {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        color: #28a745;
        background: #fff;
        padding: 5px 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.7rem;
    }

    .verified-seal i { font-size: 1.2rem; }

    .ver-card-body {
        padding: 50px 40px;
        position: relative;
    }

    .ver-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-15deg);
        font-size: 10rem;
        font-weight: 900;
        color: var(--prestige-navy);
        opacity: 0.03;
        z-index: 1;
        pointer-events: none;
    }

    .relative-content { position: relative; z-index: 2; }

    .ver-photo-frame {
        width: 170px;
        height: 200px;
        border: 5px solid #fff;
        outline: 2px solid var(--prestige-gold);
        padding: 0;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        margin: 0 auto;
        overflow: hidden;
    }

    .ver-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .reg-id-box {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 8px;
        border-left: 4px solid var(--prestige-gold);
    }

    .reg-id-val {
        font-weight: 700;
        color: var(--prestige-navy);
        font-size: 1.2rem;
    }

    .member-display-name {
        color: var(--prestige-navy);
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        font-size: 2.2rem;
    }

    .designation-pill {
        background: #eef2f7;
        color: var(--prestige-navy);
        padding: 6px 15px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid #d1d9e6;
    }

    .details-grid label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
        display: block;
        margin-bottom: 2px;
    }

    .details-grid p {
        font-size: 1rem;
        color: #333;
        font-weight: 600;
        margin-bottom: 0;
    }

    /* Status Badges */
    .status-badge {
        padding: 5px 15px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 0.8rem;
        display: inline-block;
    }

    .status-badge.approved { background: #d4edda; color: #155724; }
    .status-badge.pending { background: #fff3cd; color: #856404; }
    .status-badge.rejected { background: #f8d7da; color: #721c24; }

    .ver-card-footer {
        background: #f1f4f8;
        padding: 15px;
        border-top: 1px solid #e1e8f0;
    }

    .footer-msg { font-size: 0.8rem; color: #666; font-weight: 500; }
    .timestamp { font-size: 0.75rem; color: #999; }

    .btn-print {
        background: #fff;
        border: 2px solid var(--prestige-navy);
        color: var(--prestige-navy);
        padding: 12px 35px;
        font-weight: 700;
        border-radius: 50px;
        transition: all 0.3s;
    }

    .btn-print:hover {
        background: var(--prestige-navy);
        color: #fff;
    }

    .error-box {
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        border-bottom: 5px solid var(--prestige-gold);
    }

    /* Print Specifics */
    @media print {
        .no-print, header, footer, .navbar, .search-card, .section-heading, .section-heading + p {
            display: none !important;
        }
        .verify-page-wrapper { background: #fff; padding: 0; }
        .verification-card { box-shadow: none; border: 2px solid #002147; }
        .ver-photo-frame { outline: 1px solid #d4af37; }
    }

    @media (max-width: 768px) {
        .ver-card-body { padding: 30px 20px; }
        .member-display-name { font-size: 1.6rem; text-align: center; }
        .designation-pill { display: block; text-align: center; }
        .ver-photo-frame { width: 140px; height: 170px; }
        .details-grid { text-align: center; }
    }
</style>

<?php include 'footer.php'; ?>