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
$certificate = [];

// Fetch site configuration from settings table
$db = getDbConnection();
$siteConfig = [];
$configKeys = ['organization_address', 'organization_phone', 'organization_email'];
foreach ($configKeys as $key) {
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $siteConfig[$key] = $result ? htmlspecialchars($result['setting_value']) : '';
}

// Define certificate content configuration
$appreciationCertificateContent = [
    'organization_name'   => ORGANIZATION_NAME,
    'organization_name_hindi' => ORGANIZATION_NAME_HINDI,
    'header_text'        => 'Certificate Of Appreciation',
    'registration_number' => 'Registration No.: 238',
    'address'            => $siteConfig['organization_address'],
    'email'              => $siteConfig['organization_email'],
    'phone'              => $siteConfig['organization_phone'],
    'chairman_name'      => CERTIFICATE_CHAIRMAN_NAME,
    'chairman_title'     => CERTIFICATE_CHAIRMAN_TITLE,
    'template_path'      => SITE_URL . '/templates/participation-certificate-template.png',
    'signature_path'     => SITE_URL . '/img/signature.png',
    'seal_path'          => SITE_URL . '/img/seal.png',
    'logo_path'          => SITE_URL . '/img/logo.png'
];

// Function to generate a unique certificate number for appreciation
function generateAppreciationCertificateNumber() {
    global $db;
    $stmt = $db->query("SELECT certificate_no FROM appreciation_certificates ORDER BY id DESC LIMIT 1");
    $lastCertificate = $stmt->fetch();
    $newNumber = $lastCertificate ? ((int) substr($lastCertificate['certificate_no'], 4)) + 1 : 1;
    return 'APPR' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            $recipient_name = isset($_POST['recipient_name']) ? sanitizeInput($_POST['recipient_name']) : '';
            $training_name = isset($_POST['training_name']) ? sanitizeInput($_POST['training_name']) : '';
            $training_duration_number = isset($_POST['training_duration_number']) ? (int)$_POST['training_duration_number'] : 0;
            $training_duration_unit = isset($_POST['training_duration_unit']) ? sanitizeInput($_POST['training_duration_unit']) : '';
            $issue_date = isset($_POST['issue_date']) ? sanitizeInput($_POST['issue_date']) : '';
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            // Enhanced validation
            if (empty($recipient_name) || empty($training_name) || empty($training_duration_number) || empty($training_duration_unit) || empty($issue_date)) {
                $error = "All required fields must be filled.";
            } elseif ($training_duration_number <= 0) {
                $error = "Training duration must be greater than 0.";
            } elseif (!in_array($training_duration_unit, ['days', 'months', 'years'])) {
                $error = "Invalid training duration unit.";
            } elseif (!in_array($status, ['active', 'inactive'])) {
                $error = "Invalid status.";
            } else {
                try {
                    if ($formAction === 'add') {
                        $certificate_no = generateAppreciationCertificateNumber();
                        $stmt = $db->prepare("
                            INSERT INTO appreciation_certificates (certificate_no, recipient_name, training_name, training_duration_number, training_duration_unit, issue_date, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$certificate_no, $recipient_name, $training_name, $training_duration_number, $training_duration_unit, $issue_date, $status]);
                    } else {
                        $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
                        $stmt = $db->prepare("
                            UPDATE appreciation_certificates 
                            SET recipient_name = ?, training_name = ?, training_duration_number = ?, training_duration_unit = ?, issue_date = ?, status = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([$recipient_name, $training_name, $training_duration_number, $training_duration_unit, $issue_date, $status, $cert_id]);
                    }
                    
                    $success = "Appreciation certificate successfully " . ($formAction === 'add' ? 'created!' : 'updated!');
                    header("Location: appreciation_certificates.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    logError('Appreciation certificate processing error: ' . $e->getMessage());
                    $error = "Error: " . $e->getMessage();
                }
            }
        } elseif ($formAction === 'delete') {
            $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
            try {
                $stmt = $db->prepare("DELETE FROM appreciation_certificates WHERE id = ?");
                $stmt->execute([$cert_id]);
                
                $success = "Appreciation certificate successfully deleted!";
                header("Location: appreciation_certificates.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                logError('Appreciation certificate deletion error: ' . $e->getMessage());
                $error = "Deletion error: " . $e->getMessage();
            }
        }
    }
}

// Fetch certificate for editing
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM appreciation_certificates WHERE id = ?");
        $stmt->execute([$id]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$certificate) {
            header("Location: appreciation_certificates.php?error=" . urlencode("Certificate not found."));
            exit;
        }
    } catch (Exception $e) {
        logError('Error fetching certificate for edit: ' . $e->getMessage());
        header("Location: appreciation_certificates.php?error=" . urlencode("Error loading certificate."));
        exit;
    }
}

// Get appreciation certificates for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM appreciation_certificates");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT * FROM appreciation_certificates ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError('Database error in appreciation certificate listing: ' . $e->getMessage());
    $error = "Database error: " . $e->getMessage();
    $certificates = [];
    $totalPages = 0;
}

$pageTitle = ($action === 'add') ? "Add New Appreciation Certificate" : (($action === 'edit') ? "Edit Appreciation Certificate" : "Appreciation Certificate Management");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus"></i> Add New Appreciation Certificate
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit"></i> Edit Appreciation Certificate
                <?php else: ?>
                    <i class="fas fa-award"></i> Appreciation Certificate Management
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="appreciation_certificates.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                <?php else: ?>
                    <a href="appreciation_certificates.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Appreciation Certificate
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> <?php echo ($action === 'add') ? "Add New Appreciation Certificate" : "Edit Appreciation Certificate"; ?>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="cert_id" value="<?php echo $certificate['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="recipient_name" class="form-label">Recipient Name (Shri/Smt.) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" required 
                                       value="<?php echo htmlspecialchars($certificate['recipient_name'] ?? ''); ?>"
                                       placeholder="Enter full name">
                            </div>
                            <div class="col-md-6">
                                <label for="training_name" class="form-label">Training Name/Course <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="training_name" name="training_name" required
                                       value="<?php echo htmlspecialchars($certificate['training_name'] ?? ''); ?>"
                                       placeholder="E.g., Computer Training, Advanced Excel">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="training_duration_number" class="form-label">Duration <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="training_duration_number" name="training_duration_number" required min="1"
                                       value="<?php echo htmlspecialchars($certificate['training_duration_number'] ?? ''); ?>"
                                       placeholder="Enter number">
                            </div>
                            <div class="col-md-3">
                                <label for="training_duration_unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select class="form-select" id="training_duration_unit" name="training_duration_unit" required>
                                    <option value="">-- Select --</option>
                                    <option value="days" <?php echo (isset($certificate['training_duration_unit']) && $certificate['training_duration_unit'] == 'days') ? 'selected' : ''; ?>>Days</option>
                                    <option value="months" <?php echo (isset($certificate['training_duration_unit']) && $certificate['training_duration_unit'] == 'months') ? 'selected' : ''; ?>>Months</option>
                                    <option value="years" <?php echo (isset($certificate['training_duration_unit']) && $certificate['training_duration_unit'] == 'years') ? 'selected' : ''; ?>>Years</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="issue_date" class="form-label">Issue Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date" required
                                       value="<?php echo htmlspecialchars($certificate['issue_date'] ?? date('Y-m-d')); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo (isset($certificate['status']) && $certificate['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($certificate['status']) && $certificate['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "Create Certificate" : "Update Certificate"; ?>
                            </button>
                            <a href="appreciation_certificates.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-award"></i> Appreciation Certificates List
                </div>
                <div class="card-body">
                    <?php if (count($certificates) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Certificate No.</th>
                                        <th>Name</th>
                                        <th>Training Name/Course</th>
                                        <th>Duration</th>
                                        <th>Issue Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificates as $cert): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cert['certificate_no']); ?></td>
                                            <td><?php echo htmlspecialchars($cert['recipient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($cert['training_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php 
                                                $unitText = '';
                                                switch($cert['training_duration_unit']) {
                                                    case 'days': $unitText = 'Days'; break;
                                                    case 'months': $unitText = 'Months'; break;
                                                    case 'years': $unitText = 'Years'; break;
                                                }
                                                echo $cert['training_duration_number'] . ' ' . $unitText; 
                                                ?>
                                            </td>
                                            <td><?php echo date('d-m-Y', strtotime($cert['issue_date'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $cert['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo $cert['status'] == 'active' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="appreciation_certificates.php?action=edit&id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger delete-certificate-btn" data-id="<?php echo $cert['id']; ?>" data-cert-no="<?php echo htmlspecialchars($cert['certificate_no']); ?>" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success generate-appreciation-certificate" 
                                                        data-certificate='<?php echo json_encode($cert); ?>' 
                                                        title="Download">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No appreciation certificates found.
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Delete Modal -->
            <div class="modal fade" id="deleteCertificateModal" tabindex="-1" aria-labelledby="deleteCertificateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteCertificateModalLabel">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete the appreciation certificate <strong id="certificateNo"></strong>?</p>
                            <p class="text-danger mt-2">This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="post" id="deleteCertificateForm">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="cert_id" id="cert_id">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
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
    const certificateContent = <?= json_encode($appreciationCertificateContent) ?>;
    const templatePath = certificateContent.template_path || '';
    const signaturePath = certificateContent.signature_path || '';
    const sealPath = certificateContent.seal_path || '';
    const logoPath = certificateContent.logo_path || '';

    if (!templatePath || !logoPath || !signaturePath || !sealPath) {
        console.error('Missing required image paths for certificate generation');
        return;
    }

    document.querySelectorAll('.generate-appreciation-certificate').forEach(button => {
        button.addEventListener('click', async function() {
            try {
                const certData = JSON.parse(this.getAttribute('data-certificate'));
                const container = document.createElement('div');
                container.style.cssText = `width: 1404px; height: 990px; position: fixed; left: -9999px; font-family: 'Times New Roman', serif; background-image: url('${templatePath}'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: 60px; box-sizing: border-box; overflow: hidden;`;

                const formatDate = (inputDate) => {
                    if (!inputDate) return 'N/A';
                    let date = new Date(inputDate);
                    if (isNaN(date)) return 'Invalid Date';
                    let day = String(date.getDate()).padStart(2, '0');
                    let month = String(date.getMonth() + 1).padStart(2, '0');
                    let year = date.getFullYear();
                    return `${day}-${month}-${year}`;
                };

                const formattedIssueDate = formatDate(certData.issue_date);
                const trainingName = certData.training_name || 'N/A';
                const trainingDuration = certData.training_duration_number || 'N/A';

                container.innerHTML = `
                    <div style="display: flex; justify-content: flex-end; align-items: flex-start; margin-bottom: 20px; padding: 5px 10px 15px 10px;">
                        <div style="margin-right: -50px; margin-top: -40px;">
                            <img src="${logoPath}" alt="Logo" style="width: 150px; height: 150px; object-fit: contain; border: 4px solid #2c5282; border-radius: 50%; padding: 10px; background: white;">
                        </div>
                    </div>
                    <div style="text-align: center; margin: 140px 80px 50px 80px; line-height: 1.8;">
                        <p style="font-size: 38px; color: #2d3748; margin: 0 0 25px 0; font-style: italic; font-family: 'Times New Roman', serif; font-weight: 600;">It is certified to</p>
                        <p style="font-size: 35px; color: #2d3748; margin: 0 0 20px 0; font-family: 'Times New Roman', serif;">
                            <span style="position: relative; display: inline-block; min-width: 500px; text-align: center; padding: 0 10px 8px 10px; margin: 0 5px;">
                                <strong style="font-size: 48px; color: #2c5282; font-weight: bold; font-style: normal;">Shri/ Smt. ${certData.recipient_name || 'N/A'}</strong>
                                <span style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background-color: #2c5282;"></span>
                            </span>
                        </p>
                        <p style="font-size: 35px; color: #2d3748; margin: 0 0 15px 0; font-family: 'Times New Roman', serif;">
                            has completed 
                            <span style="position: relative; display: inline-block; min-width: 120px; text-align: center; padding: 0 15px 8px 15px; margin: 0 5px;">
                                <strong style="font-size: 38px; color: #c82333;">${trainingDuration}</strong>
                                <span style="position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background-color: #2c5282;"></span>
                            </span> 
                            days/ months/ years 
                            <span style="position: relative; display: inline-block; min-width: 400px; text-align: center; padding: 0 20px 8px 20px; margin: 0 5px;">
                                <strong style="font-size: 38px; color: #c82333;">${trainingName} Training</strong>
                                <span style="position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background-color: #2c5282;"></span>
                            </span>
                        </p>
                        <p style="font-size: 35px; color: #2d3748; margin: 0 0 15px 0; font-family: 'Times New Roman', serif;">through <strong style="color: #c82333;">Rawbit Foundation</strong></p>
                        <p style="font-size: 30px; color: #4a5568; margin: 15px 0 0 0; font-family: 'Times New Roman', serif; font-style: italic;">with dedication and commitment to excellence</p>
                    </div>
                    <div style="position: absolute; bottom: 60px; left: 40px; right: 40px; display: flex; justify-content: space-between; align-items: flex-end;">
                        <div style="text-align: left;">
                            <p style="font-size: 24px; color: #2d3748; margin: 0; font-weight: bold; font-family: 'Times New Roman', serif;">Date: ${formattedIssueDate}</p>
                        </div>
                        <div style="text-align: center;">
                            <div style="position: relative; display: inline-block; margin-bottom: 10px;">
                                <img src="${sealPath}" alt="Seal" style="width: 120px; height: auto; display: block; margin: 0 auto;">
                                <img src="${signaturePath}" alt="Signature" style="width: 180px; height: auto; position: absolute; top: 10px; left: 50%; transform: translateX(-50%);">
                            </div>
                            <div style="border-top: 2px solid #2c5282; width: 200px; margin: 10px auto 5px auto;"></div>
                            <p style="margin: 0; font-size: 20px; font-weight: bold; color: #000; font-family: 'Times New Roman', serif;">${certificateContent.chairman_name || 'N/A'}</p>
                            <p style="margin: 0; font-size: 18px; color: #c82333; font-family: 'Times New Roman', serif;">(${certificateContent.chairman_title || 'N/A'})</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px; color: #2d3748; font-style: italic; font-family: 'Times New Roman', serif;">Seal & Signature</p>
                        </div>
                    </div>
                `;

                document.body.appendChild(container);

                // Wait for all images to load
                const images = container.querySelectorAll('img');
                const imagePromises = Array.from(images).map(img => {
                    return new Promise((resolve) => {
                        if (img.complete) {
                            resolve();
                        } else {
                            img.onload = resolve;
                            img.onerror = () => {
                                console.warn(`Failed to load image: ${img.src}`);
                                resolve();
                            };
                        }
                    });
                });

                await Promise.all(imagePromises);
                
                // Additional delay to ensure proper rendering
                await new Promise(resolve => setTimeout(resolve, 500));

                // Generate canvas from HTML
                const canvas = await html2canvas(container, {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    allowTaint: true,
                    backgroundColor: null
                });

                // Convert to PDF
                const imgData = canvas.toDataURL('image/jpeg', 1.0);
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'px',
                    format: [1404, 990]
                });

                pdf.addImage(imgData, 'JPEG', 0, 0, 1404, 990);
                pdf.save(`${certData.certificate_no || 'certificate'}_appreciation.pdf`);

                // Clean up
                document.body.removeChild(container);
                showNotification('Certificate successfully downloaded.', 'success');

            } catch (error) {
                console.error('Certificate generation error:', error);
                showNotification('Error generating certificate. Please try again.', 'danger');
            }
        });
    });

    // Delete certificate modal functionality
    const deleteCertificateModal = document.getElementById('deleteCertificateModal');
    const certificateNoSpan = document.getElementById('certificateNo');
    const certIdInput = document.getElementById('cert_id');
    
    document.querySelectorAll('.delete-certificate-btn').forEach(button => {
        button.addEventListener('click', function() {
            const certificateId = this.getAttribute('data-id');
            const certificateNo = this.getAttribute('data-cert-no');
            if (certificateNoSpan) certificateNoSpan.textContent = certificateNo || 'N/A';
            if (certIdInput) certIdInput.value = certificateId || '';
            if (deleteCertificateModal && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(deleteCertificateModal);
                modal.show();
            }
        });
    });

    // Form validation
    const certificateForm = document.querySelector('form[method="post"]:not(#deleteCertificateForm)');
    if (certificateForm) {
        certificateForm.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalidField = null;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    if (!firstInvalidField) firstInvalidField = field;
                    field.addEventListener('input', function() {
                        this.classList.remove('is-invalid');
                    }, { once: true });
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            const durationNumber = document.getElementById('training_duration_number');
            if (durationNumber && (isNaN(parseInt(durationNumber.value)) || parseInt(durationNumber.value) <= 0)) {
                isValid = false;
                durationNumber.classList.add('is-invalid');
                if (!firstInvalidField) firstInvalidField = durationNumber;
            }
            
            if (!isValid) {
                e.preventDefault();
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                showNotification('Please fill all required fields correctly.', 'danger');
            }
        });
    }

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        if (!alert.querySelector('.btn-close')) {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) alert.parentNode.removeChild(alert);
                    }, 500);
                }
            }, 5000);
        }
    });

    // Notification function
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px; font-family: "Times New Roman", serif;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> 
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transition = 'opacity 0.5s ease';
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) notification.parentNode.removeChild(notification);
                }, 500);
            }
        }, 4000);
    }
});
</script>

<?php include 'includes/footer.php'; ?>