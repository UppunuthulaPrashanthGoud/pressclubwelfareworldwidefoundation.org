<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';
require_once 'includes/text_utils.php';

// Only admin can access this page
if (!isAdmin()) {
    header("Location: " . ADMIN_URL . "index.php");
    exit;
}

// Initialize variables
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = isset($_GET['success']) ? sanitizeInput($_GET['success']) : '';
$error = '';
$award = [];

// Handle AJAX request for user details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'get_user_details') {
    header('Content-Type: application/json');
    $user_id = (int)$_GET['user_id'];
    $db = getDbConnection();
    try {
        $stmt = $db->prepare("SELECT name, email, mobile, gender, profession, address, district AS city, state, pincode, registration_id, profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => !!$user, 'user' => $user]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch user details']);
    }
    exit;
}

// Database connection
$db = getDbConnection();

// --- FETCH MASTER DATA (AWARDS & VENUES) ---
$masterAwards = [];
try {
    $stmtAw = $db->query("SELECT award_name FROM awards_list WHERE status = 'active' ORDER BY award_name ASC");
    $masterAwards = $stmtAw->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $masterAwards = ['HONORARY DOCTORATE AWARD', 'LIFETIME ACHIEVEMENT AWARD']; 
}

$masterVenues = [];
try {
    $stmtVen = $db->query("SELECT venue_name FROM venues_list WHERE status = 'active' ORDER BY venue_name ASC");
    $masterVenues = $stmtVen->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $masterVenues = ['Online / Virtual Ceremony'];
}
// -------------------------------------------

// Fetch categories from DB
$masterCategories = [];
try {
    $stmtCat = $db->query("SELECT category_name FROM categories WHERE status = 'active' ORDER BY category_name ASC");
    $masterCategories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Fallback if table doesn't exist yet
    $masterCategories = ['Education Excellence', 'Social Service', 'Others']; 
}

// Award configuration
$awardContent = [
    'organization_name' => ORGANIZATION_NAME,
    'template_path' => SITE_URL . '/templates/awards-congratulations-certificate.png', // New template path
    'website' => 'http://honorarydoctorateawards.org/'
];

// Function to generate unique award number
function generateAwardNumber() {
    global $db;
    $stmt = $db->query("SELECT award_no FROM honorary_awards ORDER BY id DESC LIMIT 1");
    $lastAward = $stmt->fetch();
    $newNumber = $lastAward ? ((int) substr($lastAward['award_no'], 3)) + 1 : 1;
    return 'HA' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
}

// Handle form submission (Same as Honorary Awards)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            // ... (Same validation and saving logic as honorary_awards.php) ...
            // Using the same table 'honorary_awards'
            
             // --- Core Award Fields ---
            $recipient_name = isset($_POST['recipient_name']) ? sanitizeInput($_POST['recipient_name']) : '';
            $award_name = isset($_POST['award_name']) ? sanitizeInput($_POST['award_name']) : '';
            $category = isset($_POST['category']) ? sanitizeInput($_POST['category']) : '';
            $content = isset($_POST['content']) ? sanitizeInput($_POST['content']) : '';
            $venue = isset($_POST['venue']) ? sanitizeInput($_POST['venue']) : '';
            $award_date = isset($_POST['award_date']) ? sanitizeInput($_POST['award_date']) : '';
            $registration_no = isset($_POST['registration_no']) ? sanitizeInput($_POST['registration_no']) : '';
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            // --- Expanded Nomination Fields ---
            $mobile = isset($_POST['mobile']) ? sanitizeInput($_POST['mobile']) : '';
            $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
            $gender = isset($_POST['gender']) ? sanitizeInput($_POST['gender']) : '';
            $profession = isset($_POST['profession']) ? sanitizeInput($_POST['profession']) : '';
            $qualification = isset($_POST['qualification']) ? sanitizeInput($_POST['qualification']) : '';
            $address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : '';
            $state = isset($_POST['state']) ? sanitizeInput($_POST['state']) : '';
            $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
            $pincode = isset($_POST['pincode']) ? sanitizeInput($_POST['pincode']) : '';

            // Validation
            if (empty($recipient_name) || empty($award_name) || empty($category) || empty($content) || empty($venue) || empty($award_date) || empty($registration_no)) {
                $error = "Please fill all required fields.";
            } else {
                try {
                    $photo_path = '';
                    
                    if ($formAction === 'edit') {
                        $award_id = isset($_POST['award_id']) ? (int)$_POST['award_id'] : 0;
                        $stmt = $db->prepare("SELECT photo_path FROM honorary_awards WHERE id = ?");
                        $stmt->execute([$award_id]);
                        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);
                        $photo_path = $currentData['photo_path'] ?? '';
                    }

                    if (!empty($_POST['auto_fill_user_photo'])) {
                        $photo_path = '../uploads/profiles/' . sanitizeInput($_POST['auto_fill_user_photo']);
                    }

                    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['photo'], 'img/awards');
                        if ($uploadResult['success']) {
                            $photo_path = $uploadResult['filename'];
                        }
                    } 
                    
                    if ($formAction === 'add') {
                        $award_no = generateAwardNumber();
                        $stmt = $db->prepare("
                            INSERT INTO honorary_awards (
                                award_no, recipient_name, mobile, email, gender, profession, qualification, 
                                address, state, city, pincode,
                                award_name, category, content, venue, award_date, registration_no, photo_path, status, created_at
                            ) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([
                            $award_no, $recipient_name, $mobile, $email, $gender, $profession, $qualification,
                            $address, $state, $city, $pincode,
                            $award_name, $category, $content, $venue, $award_date, $registration_no, $photo_path, $status
                        ]);
                    } else {
                        $award_id = isset($_POST['award_id']) ? (int)$_POST['award_id'] : 0;
                        $stmt = $db->prepare("
                            UPDATE honorary_awards 
                            SET recipient_name = ?, mobile = ?, email = ?, gender = ?, profession = ?, qualification = ?, 
                                address = ?, state = ?, city = ?, pincode = ?,
                                award_name = ?, category = ?, content = ?, venue = ?, award_date = ?, registration_no = ?, photo_path = ?, status = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $recipient_name, $mobile, $email, $gender, $profession, $qualification,
                            $address, $state, $city, $pincode,
                            $award_name, $category, $content, $venue, $award_date, $registration_no, $photo_path, $status, $award_id
                        ]);
                    }
                    
                    $success = "Award successfully saved!";
                    header("Location: awards-congratulations.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        } elseif ($formAction === 'delete') {
             // Delete logic
            $award_id = isset($_POST['award_id']) ? (int)$_POST['award_id'] : 0;
            $db->prepare("DELETE FROM honorary_awards WHERE id = ?")->execute([$award_id]);
            header("Location: awards-congratulations.php?success=Deleted");
            exit;
        }
    }
}

// Initialize and Fetch Logic
$award = [
    'recipient_name' => '', 'mobile' => '', 'email' => '', 'gender' => '',
    'profession' => '', 'qualification' => '', 'address' => '', 'state' => '', 'city' => '', 'pincode' => '',
    'award_name' => '', 'category' => '', 'content' => '', 'venue' => '',
    'award_date' => date('Y-m-d'), 'registration_no' => '', 'photo_path' => '', 'status' => 'active'
];

if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM honorary_awards WHERE id = ?");
    $stmt->execute([$id]);
    $existingAward = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existingAward) $award = $existingAward;
}

// List Logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;
$stmt = $db->prepare("SELECT COUNT(*) FROM honorary_awards");
$stmt->execute();
$totalRecords = $stmt->fetchColumn();
$stmt = $db->prepare("SELECT * FROM honorary_awards ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute();
$awards = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPages = ceil($totalRecords / $limit);

$pageTitle = "Congratulations Certificates";
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-certificate"></i> Congratulations Certificates</h1>
             <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="awards-congratulations.php" class="btn btn-secondary">Back</a>
                <?php else: ?>
                    <a href="awards-congratulations.php?action=add" class="btn btn-primary">Add New</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Form (Simplified for brevity, matches honorary_awards logic) -->
            <div class="admin-card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        <?php if ($action === 'edit'): ?><input type="hidden" name="award_id" value="<?php echo $award['id']; ?>"><?php endif; ?>
                        
                        <!-- Hidden fields for auto-filled data not stored in actual UI inputs -->
                        <input type="hidden" id="mobile" name="mobile" value="<?php echo htmlspecialchars($award['mobile']); ?>">
                        <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($award['email']); ?>">
                        <input type="hidden" id="gender" name="gender" value="<?php echo htmlspecialchars($award['gender']); ?>">
                        <input type="hidden" id="profession" name="profession" value="<?php echo htmlspecialchars($award['profession']); ?>">
                        <input type="hidden" id="qualification" name="qualification" value="<?php echo htmlspecialchars($award['qualification']); ?>">
                        <input type="hidden" id="address" name="address" value="<?php echo htmlspecialchars($award['address']); ?>">
                        <input type="hidden" id="city" name="city" value="<?php echo htmlspecialchars($award['city']); ?>">
                        <input type="hidden" id="state" name="state" value="<?php echo htmlspecialchars($award['state']); ?>">
                        <input type="hidden" id="pincode" name="pincode" value="<?php echo htmlspecialchars($award['pincode']); ?>">
                        <input type="hidden" id="auto_fill_user_photo" name="auto_fill_user_photo" value="">

                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label for="auto_fill_user" class="form-label text-info fw-bold"><i class="fas fa-magic"></i> Link & Auto-fill from Registered User</label>
                                <select class="form-select border-info border-2 shadow-sm" id="auto_fill_user" aria-describedby="autoFillHelp">
                                    <option value="">-- Select an approved user to auto-fill details --</option>
                                    <?php
                                    try {
                                        $userStmt = $db->query("SELECT id, name, registration_id FROM users WHERE status = 'approved' ORDER BY name");
                                        $usersList = $userStmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($usersList as $u) {
                                            echo "<option value='{$u['id']}'>" . htmlspecialchars($u['name'] . " (" . $u['registration_id'] . ")") . "</option>";
                                        }
                                    } catch (Exception $e) {}
                                    ?>
                                </select>
                                <div id="autoFillHelp" class="form-text mt-2 text-muted">
                                    <i class="fas fa-info-circle text-info"></i> <strong>Why select a user?</strong> Choosing a registered user will automatically fetch and fill their personal info, contact details, address, and profile photo. This ensures the certificate details exactly match their official system profile and saves you manual typing time.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="recipient_name">Recipient Name</label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" value="<?php echo htmlspecialchars($award['recipient_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Award Name</label>
                                <select class="form-select" name="award_name" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach($masterAwards as $aw): ?>
                                        <option value="<?php echo htmlspecialchars($aw); ?>" <?php echo $award['award_name'] == $aw ? 'selected' : ''; ?>><?php echo htmlspecialchars($aw); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="category">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">-- Select Category --</option>
                                    <?php 
                                    $currentCategoryName = $award['category'] ?? '';
                                    foreach($masterCategories as $catName): 
                                        $selected = ($currentCategoryName === $catName) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo htmlspecialchars($catName); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($catName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                             <div class="col-md-6">
                                <label class="form-label" for="registration_no">Registration No</label>
                                <input type="text" class="form-control" id="registration_no" name="registration_no" value="<?php echo htmlspecialchars($award['registration_no']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Venue</label>
                                <select class="form-select" name="venue" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach($masterVenues as $vn): ?>
                                        <option value="<?php echo htmlspecialchars($vn); ?>" <?php echo $award['venue'] == $vn ? 'selected' : ''; ?>><?php echo htmlspecialchars($vn); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="award_date" value="<?php echo htmlspecialchars($award['award_date']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                             <div class="col-md-6">
                                <label class="form-label">Photo</label>
                                <input type="file" class="form-control" name="photo" accept="image/*">
                                <div class="mt-2 image-preview" id="photo_preview_container">
                                    <?php if (!empty($award['photo_path']) && strpos($award['photo_path'], '../uploads/profiles/') === 0 && file_exists(__DIR__ . '/../' . substr($award['photo_path'], 3))): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars(substr($award['photo_path'], 20)); ?>?v=<?php echo time(); ?>" alt="Award Photo" class="img-thumbnail" style="max-height: 150px;">
                                        <p class="text-muted mt-1">Current Award Photo (from user profile)</p>
                                    <?php elseif (!empty($award['photo_path']) && file_exists(__DIR__ . '/../img/awards/' . $award['photo_path'])): ?>
                                        <img src="<?php echo SITE_URL; ?>/img/awards/<?php echo htmlspecialchars($award['photo_path']); ?>?v=<?php echo time(); ?>" alt="Award Photo" class="img-thumbnail" style="max-height: 150px;">
                                        <p class="text-muted mt-1">Current Award Photo</p>
                                    <?php else: ?>
                                        <p class="text-muted" id="no_photo_text">No photo uploaded yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Content</label>
                                <textarea class="form-control" name="content" required><?php echo htmlspecialchars($award['content']); ?></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
             <div class="admin-card">
                <div class="card-body">
                    <div class="table-responsive w-100" style="overflow-x: auto; -webkit-overflow-scrolling: touch; min-width: 0;">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-nowrap">Name</th>
                                    <th class="text-nowrap">Award</th>
                                    <th class="text-nowrap">Category</th>
                                    <th class="text-nowrap text-center" style="min-width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($awards as $awd): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($awd['recipient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($awd['award_name']); ?></td>
                                        <td><span class="badge badge-info bg-info text-dark"><?php echo htmlspecialchars($awd['category']); ?></span></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2 flex-nowrap">
                                                <a href="?action=edit&id=<?php echo $awd['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <!-- GENERATE EXCLUSIVE CONGRATULATIONS CERTIFICATE -->
                                                <button class="btn btn-sm btn-success generate-congrats" 
                                                        data-award='<?php echo json_encode($awd); ?>'
                                                        title="Download Cert">
                                                    <i class="fas fa-download"></i> Cert
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
             </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const { jsPDF } = window.jspdf;
    const awardContent = <?php echo json_encode($awardContent); ?>;
    
    function formatDate(inputDate) {
         try {
            let date = new Date(inputDate);
            if (isNaN(date.getTime())) return inputDate;
            let day = String(date.getDate()).padStart(2, '0');
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            let monthName = months[date.getMonth()];
            let year = date.getFullYear();
            return `${day} ${monthName} ${year}`;
        } catch (e) { return inputDate; }
    }

    document.querySelectorAll('.generate-congrats').forEach(button => {
        button.addEventListener('click', async function() {
            try {
                let awardData = JSON.parse(this.getAttribute('data-award'));
                console.log('Generating Congratulations Certificate...', awardData);
                
                const formattedDate = formatDate(awardData.award_date);

                // --- DYNAMIC FONT SIZING LOGIC ---
                // Calculate size based on name length to prevent overflow
                const nameStr = awardData.recipient_name || '';
                const nameLen = nameStr.length;
                let nameFontSize = 120; // Default size for typical names
                if (nameLen > 22) {
                    // Proportionally scale down the font size if the name is too long
                    nameFontSize = Math.max(50, Math.floor(120 * (22 / nameLen)));
                }

                // Dynamic sizing for category as well just in case
                const catStr = awardData.category || '';
                const catLen = catStr.length;
                let catFontSize = 80; // Default size
                if (catLen > 30) {
                    catFontSize = Math.max(40, Math.floor(80 * (30 / catLen)));
                }
                // ---------------------------------
                
                const container = document.createElement('div');
                container.style.cssText = `
                    width: 3375px;
                    height: 3375px;
                    position: fixed;
                    left: -9999px;
                    font-family: 'Times New Roman', serif;
                    background-color: white;
                    padding: 0;
                    margin: 0;
                    background-image: url('${awardContent.template_path}');
                    background-size: 100% 100%;
                    background-repeat: no-repeat;
                `;
                
                let photoPath = awardData.photo_path ? (awardData.photo_path.startsWith('../uploads/profiles/') ? '<?php echo SITE_URL; ?>/uploads/profiles/' + awardData.photo_path.substring(20) : '<?php echo SITE_URL; ?>/img/awards/' + awardData.photo_path) + '?v=' + Date.now() : '';
                
                container.innerHTML = `
                <div style="position: relative; width: 100%; height: 100%; font-family: 'Times New Roman', serif;">
                    
                    <!-- Photo Section -->
                    ${photoPath ? `
                        <div style="position: absolute; left: 1145px; top: 1229px; width: 1134px; height: 1136px; border-radius: 50%; overflow: hidden; z-index: 1;">
                            <img src="${photoPath}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    ` : ''}
                    
                    <!-- Name Section (Curved) -->
                    <div style="position: absolute; left: 54%; transform: translateX(-50%); top: 2300px; width: 2500px; height: 300px; z-index: 2; overflow: visible;">
                        <svg viewBox="0 0 2500 300" width="100%" height="100%" style="overflow: visible;">
                            <path id="curvePath" d="M0,100 Q1250,250 2500,100" fill="transparent"/>
                            <text width="2500" font-family="'Times New Roman', serif" font-weight="bold" font-size="${nameFontSize}" fill="#fff" text-anchor="middle" style="text-transform: uppercase; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                                <textPath href="#curvePath" startOffset="50%">${awardData.recipient_name}</textPath>
                            </text>
                        </svg>
                    </div>

                    <!-- Category Section -->
                    <div style="position: absolute; left: 54%; transform: translateX(-50%); top: 2605px; width: 2500px; text-align: center; z-index: 2;">
                        <h3 style="margin: 0; font-size: ${catFontSize}px; font-weight: bold; color: #fff; text-transform: uppercase;">
                            ${awardData.category}
                        </h3>
                    </div>

                    <!-- Venue and Date -->
                    <div style="position: absolute; left: 602.5px; top: 2754.6px; width: 2000px; text-align: left; font-size: 50px; color: #000; font-weight: bold; z-index: 2;">
                        <p style="margin: 0;">Venue: ${awardData.venue}</p>
                        <p style="margin: 0;">Date: ${formattedDate}</p>
                    </div>
                </div>
                `;
                
                document.body.appendChild(container);
                
                // Wait for images
                const images = container.querySelectorAll('img');
                await Promise.all(Array.from(images).map(img => {
                    if (img.complete) return Promise.resolve();
                    return new Promise(resolve => {
                        img.onload = resolve;
                        img.onerror = resolve;
                    });
                }));
                
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                const canvas = await html2canvas(container, {
                    scale: 1, // Large canvas, scale 1 is likely enough
                    useCORS: true,
                    width: 3375,
                    height: 3375
                });
                
                const imgData = canvas.toDataURL('image/jpeg', 0.90);
                
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'px',
                    format: [3375, 3375]
                });
                
                pdf.addImage(imgData, 'JPEG', 0, 0, 3375, 3375);
                pdf.save(`Congratulations_${awardData.award_no}.pdf`);
                
                document.body.removeChild(container);
            } catch (err) {
                console.error(err);
                alert('Error generating certificate');
            }
        });
    });
    // Auto fill fields on user selection via AJAX
    const autoFillUserSelect = document.getElementById('auto_fill_user');
    
    if (autoFillUserSelect) {
        autoFillUserSelect.addEventListener('change', function() {
            const userId = autoFillUserSelect.value;
            if (userId) {
                fetch(`awards-congratulations.php?ajax=get_user_details&user_id=${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.user) {
                            const u = data.user;
                            const setVal = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
                            setVal('recipient_name', u.name);
                            setVal('mobile', u.mobile);
                            setVal('email', u.email);
                            setVal('gender', u.gender);
                            setVal('profession', u.profession);
                            setVal('address', u.address);
                            setVal('city', u.city);
                            setVal('state', u.state);
                            setVal('pincode', u.pincode);
                            setVal('registration_no', u.registration_id);
                            
                            if (u.profile_image) {
                                document.getElementById('auto_fill_user_photo').value = u.profile_image;
                                const previewContainer = document.getElementById('photo_preview_container');
                                if (previewContainer) {
                                    previewContainer.innerHTML = `<img src="<?php echo SITE_URL; ?>/uploads/profiles/${u.profile_image}" class="img-thumbnail" style="max-height: 150px;" alt="User Photo"> <p class="text-muted mt-1 small">Photo auto-filled from user profile.</p>`;
                                }
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching user details:', error);
                    });
            }
        });
    }
});
</script>
<?php include 'includes/footer.php'; ?>