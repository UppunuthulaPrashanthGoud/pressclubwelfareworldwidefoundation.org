<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

$award_data = null;
$search_query = '';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_query = isset($_POST['award_id']) ? sanitizeInput(trim($_POST['award_id'])) : '';

    if (!empty($search_query)) {
        try {
            // Check in honorary_awards table using award_no or registration_no
            $stmt = $db->prepare("
                SELECT recipient_name, award_name, category, award_date, venue, award_no, registration_no, photo_path 
                FROM honorary_awards 
                WHERE (award_no = :query OR registration_no = :query) 
                AND status = 'active' 
                LIMIT 1
            ");
            $stmt->bindParam(':query', $search_query);
            $stmt->execute();
            $award_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($award_data) {
                $success_message = "Verification Successful! Details below are confirmed as authentic.";
            } else {
                $error_message = "Verification failed. No active award found matching the ID: " . htmlspecialchars($search_query);
            }

        } catch (PDOException $e) {
            $error_message = "A database error occurred during verification. Please try again.";
            logError("Verification Error: " . $e->getMessage());
        }
    } else {
        $error_message = "Please enter an Award Number or Registration Number to verify.";
    }
}

$pageTitle = "Award Verification";

include 'header.php';
include 'navbar.php';
?>

<style>
.verification-section {
    background: #f8f9fa;
    padding: 80px 0;
    min-height: 80vh;
}

.verification-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 30px;
}

.verification-form .form-control {
    border-radius: 50px;
    height: 50px;
    padding: 0 20px;
    font-size: 1.1rem;
}

.verification-form .btn {
    border-radius: 50px;
    height: 50px;
    font-weight: 600;
}

.result-card {
    margin-top: 30px;
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid #dee2e6;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.result-header {
    background-color: var(--bs-primary);
    color: white;
    padding: 15px;
    text-align: center;
    font-size: 1.25rem;
    font-weight: 700;
}

.result-body {
    padding: 30px;
    text-align: center;
}

.result-photo-container {
    width: 120px;
    height: 120px;
    margin: 0 auto 15px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--bs-success);
}

.result-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.result-name {
    font-size: 2rem;
    font-weight: 700;
    color: #343a40;
    margin-bottom: 5px;
}

.result-award {
    font-size: 1.2rem;
    font-style: italic;
    color: var(--bs-primary);
    margin-bottom: 10px;
}

.result-detail-list {
    list-style: none;
    padding: 0;
    text-align: left;
    max-width: 400px;
    margin: 20px auto 0;
}

.result-detail-list li {
    padding: 8px 0;
    border-bottom: 1px dashed #e9ecef;
    display: flex;
    justify-content: space-between;
}

.result-detail-list li:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 500;
    color: #6c757d;
}

.detail-value {
    font-weight: 600;
    color: #495057;
}

</style>

<section class="verification-section">
    <div class="container">
        <h3 class="section-heading text-center mb-5">
            <span>Verify Your Award / Certificate</span>
        </h3>

        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="verification-card" data-aos="fade-up">
                    <form method="post" class="verification-form">
                        <div class="input-group">
                            <input type="text" class="form-control" name="award_id" 
                                   placeholder="Enter Award Number or Registration Number" 
                                   value="<?php echo htmlspecialchars($search_query); ?>" 
                                   required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-2"></i> Verify
                            </button>
                        </div>
                        <div class="form-text mt-3 text-center">
                            Please enter the unique ID found on your official award document.
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="alert alert-success mt-4 text-center d-flex align-items-center justify-content-center" data-aos="fade-in">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    
                    <?php if ($award_data): ?>
                    <div class="result-card" data-aos="zoom-in">
                        <div class="result-header">
                            Verified Recipient Details
                        </div>
                        <div class="result-body">
                            <div class="result-photo-container">
                                <img src="<?php echo SITE_URL; ?>/img/awards/<?php echo htmlspecialchars($award_data['photo_path'] ?? 'placeholder.png'); ?>" 
                                     alt="<?php echo htmlspecialchars($award_data['recipient_name']); ?> Photo" 
                                     class="result-photo"
                                     onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/img/placeholder.png';">
                            </div>
                            
                            <h4 class="result-name"><?php echo htmlspecialchars($award_data['recipient_name']); ?></h4>
                            <p class="result-award"><?php echo htmlspecialchars($award_data['award_name']); ?></p>

                            <ul class="result-detail-list">
                                <li>
                                    <span class="detail-label"><i class="fas fa-hashtag me-2"></i> Award No.</span>
                                    <span class="detail-value text-primary"><?php echo htmlspecialchars($award_data['award_no']); ?></span>
                                </li>
                                <li>
                                    <span class="detail-label"><i class="fas fa-id-badge me-2"></i> Reg. No.</span>
                                    <span class="detail-value text-primary"><?php echo htmlspecialchars($award_data['registration_no']); ?></span>
                                </li>
                                <li>
                                    <span class="detail-label"><i class="fas fa-calendar-alt me-2"></i> Award Date</span>
                                    <span class="detail-value"><?php echo date('F j, Y', strtotime($award_data['award_date'])); ?></span>
                                </li>
                                <li>
                                    <span class="detail-label"><i class="fas fa-building me-2"></i> Venue</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($award_data['venue']); ?></span>
                                </li>
                                <li>
                                    <span class="detail-label"><i class="fas fa-tag me-2"></i> Category</span>
                                    <span class="detail-value text-uppercase"><?php echo htmlspecialchars($award_data['category']); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif (!empty($error_message)): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="alert alert-danger mt-4 text-center d-flex align-items-center justify-content-center" data-aos="fade-in">
                        <i class="fas fa-times-circle fa-2x me-3"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>