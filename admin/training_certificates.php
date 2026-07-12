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

// Training Certificate Types with enhanced configurations
$trainingCertificateTypes = [
    'technical_training' => [
        'name' => 'तकनीकी प्रशिक्षण प्रमाणपत्र',
        'name_english' => 'Technical Training Certificate',
        'text_template' => 'प्रमाणित किया जाता है कि {name} ने {training_name} में {duration} घंटे का प्रशिक्षण सफलतापूर्वक पूरा किया है',
        'badge_text' => 'TECHNICAL',
        'color_scheme' => '#2563eb',
        'badge_color' => '#ffd700',
        'prefix' => 'TTC'
    ],
    'skill_development' => [
        'name' => 'कौशल विकास प्रमाणपत्र',
        'name_english' => 'Skill Development Certificate',
        'text_template' => 'प्रमाणित किया जाता है कि {name} ने {training_name} कौशल विकास कार्यक्रम में {duration} घंटे का प्रशिक्षण प्राप्त किया है',
        'badge_text' => 'SKILL DEV',
        'color_scheme' => '#059669',
        'badge_color' => '#ffd700',
        'prefix' => 'SDC'
    ],
    'professional_development' => [
        'name' => 'व्यावसायिक विकास प्रमाणपत्र',
        'name_english' => 'Professional Development Certificate',
        'text_template' => 'प्रमाणित किया जाता है कि {name} ने {training_name} व्यावसायिक विकास कार्यक्रम सफलतापूर्वक पूरा किया है',
        'badge_text' => 'PROFESSIONAL',
        'color_scheme' => '#7c2d12',
        'badge_color' => '#ffd700',
        'prefix' => 'PDC'
    ],
    'safety_training' => [
        'name' => 'सुरक्षा प्रशिक्षण प्रमाणपत्र',
        'name_english' => 'Safety Training Certificate',
        'text_template' => 'प्रमाणित किया जाता है कि {name} ने {training_name} सुरक्षा प्रशिक्षण कार्यक्रम में {duration} घंटे का प्रशिक्षण पूरा किया है',
        'badge_text' => 'SAFETY',
        'color_scheme' => '#dc2626',
        'badge_color' => '#ffd700',
        'prefix' => 'STC'
    ],
    'compliance_training' => [
        'name' => 'अनुपालन प्रशिक्षण प्रमाणपत्र',
        'name_english' => 'Compliance Training Certificate',
        'text_template' => 'प्रमाणित किया जाता है कि {name} ने {training_name} अनुपालन प्रशिक्षण आवश्यकताओं को पूरा किया है',
        'badge_text' => 'COMPLIANCE',
        'color_scheme' => '#7c3aed',
        'badge_color' => '#ffd700',
        'prefix' => 'CTC'
    ],
    'leadership_training' => [
        'name' => 'नेतृत्व प्रशिक्षण प्रमाणपत्र',
        'name_english' => 'Leadership Training Certificate',
        'text_template' => 'प्रमाणित किया जाता है कि {name} ने {training_name} नेतृत्व विकास कार्यक्रम सफलतापूर्वक पूरा किया है',
        'badge_text' => 'LEADERSHIP',
        'color_scheme' => '#ea580c',
        'badge_color' => '#ffd700',
        'prefix' => 'LTC'
    ],
    'certification_course' => [
        'name' => 'प्रमाणन पाठ्यक्रम प्रमाणपत्र',
        'name_english' => 'Certification Course Certificate',
        'text_template' => 'प्रमाणित किया जाता है कि {name} ने {training_name} प्रमाणन पाठ्यक्रम में सभी आवश्यकताओं को पूरा किया है',
        'badge_text' => 'CERTIFIED',
        'color_scheme' => '#0891b2',
        'badge_color' => '#ffd700',
        'prefix' => 'CCC'
    ]
];

// Training levels/grades
$trainingGrades = [
    'A+' => ['name' => 'उत्कृष्ट (A+)', 'color' => '#059669', 'min_score' => 95],
    'A' => ['name' => 'अति उत्तम (A)', 'color' => '#0891b2', 'min_score' => 85],
    'B+' => ['name' => 'उत्तम (B+)', 'color' => '#7c2d12', 'min_score' => 75],
    'B' => ['name' => 'अच्छा (B)', 'color' => '#ea580c', 'min_score' => 65],
    'C' => ['name' => 'संतोषजनक (C)', 'color' => '#dc2626', 'min_score' => 50],
    'Pass' => ['name' => 'उत्तीर्ण (Pass)', 'color' => '#6b7280', 'min_score' => 40]
];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_GET['ajax'] === 'get_training_programs') {
        try {
            $db = getDbConnection();
            $stmt = $db->query("SELECT id, program_name, duration_hours FROM training_programs WHERE status = 'active' ORDER BY program_name");
            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'programs' => $programs]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to fetch training programs']);
        }
    }
    exit;
}

// Fetch site configuration
$db = getDbConnection();
$siteConfig = [];
$configKeys = ['organization_address', 'organization_phone', 'organization_email'];
foreach ($configKeys as $key) {
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $siteConfig[$key] = $result ? htmlspecialchars($result['setting_value']) : '';
}

// Certificate content configuration
$certificateContent = [
    'organization_name'   => ORGANIZATION_NAME,
    'organization_name_hindi' => ORGANIZATION_NAME_HINDI,
    'header_text'        => 'प्रशिक्षण एवं कौशल विकास केंद्र',
    'registration_number' => 'Registration No.: 238',
    'address'            => $siteConfig['organization_address'],
    'email'              => $siteConfig['organization_email'],
    'phone'              => $siteConfig['organization_phone'],
    'chairman_name'      => CERTIFICATE_CHAIRMAN_NAME ?? 'अध्यक्ष',
    'chairman_title'     => CERTIFICATE_CHAIRMAN_TITLE ?? 'Chairman',
    'template_path'      => SITE_URL . '/templates/training-certificate-template.png',
    'signature_path'     => SITE_URL . '/img/signature.png',
    'seal_path'          => SITE_URL . '/img/seal.png',
    'certificate_types'  => $trainingCertificateTypes,
    'training_grades'    => $trainingGrades
];

// Function to generate unique certificate number
function generateTrainingCertificateNumber($type_prefix = 'TRN') {
    global $db;
    $stmt = $db->query("SELECT certificate_no FROM training_certificates WHERE certificate_no LIKE '{$type_prefix}%' ORDER BY id DESC LIMIT 1");
    $lastCertificate = $stmt->fetch();
    $newNumber = $lastCertificate ? ((int) substr($lastCertificate['certificate_no'], strlen($type_prefix))) + 1 : 1;
    return $type_prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
        $error = 'अमान्य CSRF टोकन।';
    } else if (isset($_POST['action'])) {
        $formAction = $_POST['action'];
        
        if ($formAction === 'add' || $formAction === 'edit') {
            // Sanitize inputs
            $trainee_name = isset($_POST['trainee_name']) ? sanitizeInput($_POST['trainee_name']) : '';
            $trainee_id = isset($_POST['trainee_id']) ? sanitizeInput($_POST['trainee_id']) : '';
            $training_program_id = isset($_POST['training_program_id']) ? (int)$_POST['training_program_id'] : 0;
            $training_name = isset($_POST['training_name']) ? sanitizeInput($_POST['training_name']) : '';
            $certificate_type = isset($_POST['certificate_type']) ? sanitizeInput($_POST['certificate_type']) : 'technical_training';
            $training_start_date = isset($_POST['training_start_date']) ? sanitizeInput($_POST['training_start_date']) : '';
            $training_end_date = isset($_POST['training_end_date']) ? sanitizeInput($_POST['training_end_date']) : '';
            $duration_hours = isset($_POST['duration_hours']) ? (int)$_POST['duration_hours'] : 0;
            $trainer_name = isset($_POST['trainer_name']) ? sanitizeInput($_POST['trainer_name']) : '';
            $grade_achieved = isset($_POST['grade_achieved']) ? sanitizeInput($_POST['grade_achieved']) : '';
            $score_percentage = isset($_POST['score_percentage']) ? (float)$_POST['score_percentage'] : 0;
            $remarks = isset($_POST['remarks']) ? sanitizeInput($_POST['remarks']) : '';
            $issue_date = isset($_POST['issue_date']) ? sanitizeInput($_POST['issue_date']) : '';
            $expiry_date = isset($_POST['expiry_date']) ? sanitizeInput($_POST['expiry_date']) : '';
            $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : 'active';
            
            // Validation
            if (empty($trainee_name) || empty($training_name) || empty($training_start_date) || empty($training_end_date) || empty($issue_date)) {
                $error = "सभी आवश्यक फील्ड भरें।";
            } elseif (!array_key_exists($certificate_type, $trainingCertificateTypes)) {
                $error = "अमान्य प्रमाणपत्र प्रकार।";
            } elseif (!empty($grade_achieved) && !array_key_exists($grade_achieved, $trainingGrades)) {
                $error = "अमान्य ग्रेड।";
            } else {
                try {
                    if ($formAction === 'add') {
                        $certificate_no = generateTrainingCertificateNumber($trainingCertificateTypes[$certificate_type]['prefix']);
                        $stmt = $db->prepare("
                            INSERT INTO training_certificates (
                                certificate_no, trainee_name, trainee_id, training_program_id, training_name, 
                                certificate_type, training_start_date, training_end_date, duration_hours, 
                                trainer_name, grade_achieved, score_percentage, remarks, issue_date, 
                                expiry_date, status, created_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([
                            $certificate_no, $trainee_name, $trainee_id, $training_program_id, $training_name,
                            $certificate_type, $training_start_date, $training_end_date, $duration_hours,
                            $trainer_name, $grade_achieved, $score_percentage, $remarks, $issue_date,
                            $expiry_date, $status
                        ]);
                    } else {
                        $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
                        $stmt = $db->prepare("
                            UPDATE training_certificates SET 
                                trainee_name = ?, trainee_id = ?, training_program_id = ?, training_name = ?, 
                                certificate_type = ?, training_start_date = ?, training_end_date = ?, 
                                duration_hours = ?, trainer_name = ?, grade_achieved = ?, score_percentage = ?, 
                                remarks = ?, issue_date = ?, expiry_date = ?, status = ?, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $trainee_name, $trainee_id, $training_program_id, $training_name,
                            $certificate_type, $training_start_date, $training_end_date, $duration_hours,
                            $trainer_name, $grade_achieved, $score_percentage, $remarks, $issue_date,
                            $expiry_date, $status, $cert_id
                        ]);
                    }
                    
                    $success = "प्रशिक्षण प्रमाणपत्र सफलतापूर्वक " . ($formAction === 'add' ? 'बनाया गया!' : 'अपडेट किया गया!');
                    header("Location: training_certificates.php?success=" . urlencode($success));
                    exit;
                } catch (Exception $e) {
                    logError('Training certificate processing error: ' . $e->getMessage());
                    $error = "त्रुटि: " . $e->getMessage();
                }
            }
        } elseif ($formAction === 'delete') {
            $cert_id = isset($_POST['cert_id']) ? (int)$_POST['cert_id'] : 0;
            try {
                $stmt = $db->prepare("DELETE FROM training_certificates WHERE id = ?");
                $stmt->execute([$cert_id]);
                
                $success = "प्रशिक्षण प्रमाणपत्र सफलतापूर्वक हटा दिया गया!";
                header("Location: training_certificates.php?success=" . urlencode($success));
                exit;
            } catch (Exception $e) {
                logError('Training certificate deletion error: ' . $e->getMessage());
                $error = "हटाने में समस्या: " . $e->getMessage();
            }
        }
    }
}

// Create necessary database tables
try {
    // Training Programs table
    $createTrainingProgramsTable = "
        CREATE TABLE IF NOT EXISTS training_programs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            program_name VARCHAR(255) NOT NULL,
            program_code VARCHAR(50) UNIQUE NOT NULL,
            description TEXT,
            duration_hours INT DEFAULT 0,
            duration_days INT DEFAULT 0,
            category VARCHAR(100),
            level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
            prerequisites TEXT,
            learning_objectives TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_category (category)
        )
    ";
    $db->exec($createTrainingProgramsTable);
    
    // Training Certificates table
    $createTrainingCertificatesTable = "
        CREATE TABLE IF NOT EXISTS training_certificates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            certificate_no VARCHAR(30) UNIQUE NOT NULL,
            trainee_name VARCHAR(255) NOT NULL,
            trainee_id VARCHAR(100),
            training_program_id INT DEFAULT NULL,
            training_name VARCHAR(255) NOT NULL,
            certificate_type VARCHAR(50) DEFAULT 'technical_training',
            training_start_date DATE NOT NULL,
            training_end_date DATE NOT NULL,
            duration_hours INT DEFAULT 0,
            trainer_name VARCHAR(255),
            grade_achieved VARCHAR(10),
            score_percentage DECIMAL(5,2) DEFAULT 0.00,
            remarks TEXT,
            issue_date DATE NOT NULL,
            expiry_date DATE,
            status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (training_program_id) REFERENCES training_programs(id) ON DELETE SET NULL,
            INDEX idx_certificate_no (certificate_no),
            INDEX idx_trainee_name (trainee_name),
            INDEX idx_certificate_type (certificate_type),
            INDEX idx_status (status),
            INDEX idx_issue_date (issue_date)
        )
    ";
    $db->exec($createTrainingCertificatesTable);
    
    // Insert sample training programs if table is empty
    $stmt = $db->query("SELECT COUNT(*) FROM training_programs");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $samplePrograms = [
            ['Computer Basics', 'COMP-001', 'Basic computer skills and digital literacy', 40, 5, 'Technical'],
            ['Digital Marketing', 'DIGI-001', 'Social media marketing and online advertising', 60, 8, 'Marketing'],
            ['Data Entry & MS Office', 'DATA-001', 'Microsoft Office suite and data management', 50, 6, 'Technical'],
            ['Web Development Fundamentals', 'WEB-001', 'HTML, CSS, JavaScript basics', 120, 15, 'Technical'],
            ['Financial Literacy', 'FIN-001', 'Personal finance and banking basics', 30, 4, 'Finance'],
            ['English Communication', 'ENG-001', 'Spoken and written English skills', 80, 10, 'Language'],
            ['Entrepreneurship Development', 'ENT-001', 'Business planning and startup skills', 70, 9, 'Business']
        ];
        
        $stmt = $db->prepare("INSERT INTO training_programs (program_name, program_code, description, duration_hours, duration_days, category) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($samplePrograms as $program) {
            $stmt->execute($program);
        }
    }
    
} catch (PDOException $e) {
    logError('Database table creation error: ' . $e->getMessage());
    $error = "डेटाबेस त्रुटि: " . $e->getMessage();
}

// Get certificates for list with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM training_certificates");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();
    
    $stmt = $db->prepare("
        SELECT tc.*, tp.program_name, tp.program_code 
        FROM training_certificates tc 
        LEFT JOIN training_programs tp ON tc.training_program_id = tp.id 
        ORDER BY tc.created_at DESC LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("SELECT * FROM training_programs WHERE status = 'active' ORDER BY program_name");
    $stmt->execute();
    $trainingPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPages = ceil($totalRecords / $limit);
} catch (PDOException $e) {
    logError('Database error in training certificate listing: ' . $e->getMessage());
    $error = "डेटाबेस त्रुटि: " . $e->getMessage();
    $certificates = [];
    $trainingPrograms = [];
    $totalPages = 0;
}

// Fetch certificate for editing
if ($action === 'edit' && $id > 0) {
    try {
        $stmt = $db->prepare("SELECT * FROM training_certificates WHERE id = ?");
        $stmt->execute([$id]);
        $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$certificate) {
            $error = "प्रमाणपत्र नहीं मिला।";
            $action = 'list';
        }
    } catch (Exception $e) {
        logError('Error fetching certificate for edit: ' . $e->getMessage());
        $error = "प्रमाणपत्र लोड करने में त्रुटि।";
        $action = 'list';
    }
}

$pageTitle = ($action === 'add') ? "नया प्रशिक्षण प्रमाणपत्र जोड़ें" : (($action === 'edit') ? "प्रशिक्षण प्रमाणपत्र संपादित करें" : "प्रशिक्षण प्रमाणपत्र प्रबंधन");

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php if ($action === 'add'): ?>
                    <i class="fas fa-plus"></i> नया प्रशिक्षण प्रमाणपत्र जोड़ें
                <?php elseif ($action === 'edit'): ?>
                    <i class="fas fa-edit"></i> प्रशिक्षण प्रमाणपत्र संपादित करें
                <?php else: ?>
                    <i class="fas fa-graduation-cap"></i> प्रशिक्षण प्रमाणपत्र प्रबंधन
                <?php endif; ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <a href="training_certificates.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> वापस
                    </a>
                <?php else: ?>
                    <a href="training_certificates.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> नया प्रशिक्षण प्रमाणपत्र जोड़ें
                    </a>
                    <a href="training_programs.php" class="btn btn-info">
                        <i class="fas fa-cogs"></i> प्रशिक्षण कार्यक्रम प्रबंधन
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
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i> 
                    <?php echo ($action === 'add') ? "नया प्रशिक्षण प्रमाणपत्र जोड़ें" : "प्रशिक्षण प्रमाणपत्र संपादित करें"; ?>
                </div>
                <div class="card-body">
                    <form method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?php echo $action; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="cert_id" value="<?php echo $certificate['id']; ?>">
                        <?php endif; ?>
                        
                        <!-- Certificate Type and Basic Info -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="certificate_type" class="form-label">प्रमाणपत्र प्रकार <span class="text-danger">*</span></label>
                                <select class="form-select" id="certificate_type" name="certificate_type" required>
                                    <?php foreach ($trainingCertificateTypes as $type => $config): ?>
                                        <option value="<?php echo $type; ?>" <?php echo (isset($certificate['certificate_type']) && $certificate['certificate_type'] == $type) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($config['name'] . ' (' . $config['name_english'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="training_program_id" class="form-label">प्रशिक्षण कार्यक्रम</label>
                                <select class="form-select" id="training_program_id" name="training_program_id">
                                    <option value="">-- कार्यक्रम चुनें (वैकल्पिक) --</option>
                                    <?php foreach ($trainingPrograms as $program): ?>
                                        <option value="<?php echo $program['id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                                data-hours="<?php echo $program['duration_hours']; ?>"
                                                <?php echo (isset($certificate['training_program_id']) && $certificate['training_program_id'] == $program['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($program['program_name'] . ' (' . $program['program_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Trainee Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="trainee_name" class="form-label">प्रशिक्षणार्थी का नाम <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="trainee_name" name="trainee_name" required 
                                       value="<?php echo htmlspecialchars($certificate['trainee_name'] ?? ''); ?>"
                                       placeholder="पूरा नाम दर्ज करें">
                            </div>
                            <div class="col-md-6">
                                <label for="trainee_id" class="form-label">प्रशिक्षणार्थी ID</label>
                                <input type="text" class="form-control" id="trainee_id" name="trainee_id"
                                       value="<?php echo htmlspecialchars($certificate['trainee_id'] ?? ''); ?>"
                                       placeholder="वैकल्पिक: Student/Employee ID">
                            </div>
                        </div>

                        <!-- Training Details -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="training_name" class="form-label">प्रशिक्षण का नाम <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="training_name" name="training_name" required 
                                       value="<?php echo htmlspecialchars($certificate['training_name'] ?? ''); ?>"
                                       placeholder="प्रशिक्षण कार्यक्रम का पूरा नाम">
                            </div>
                        </div>

                        <!-- Training Duration -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="training_start_date" class="form-label">प्रशिक्षण प्रारंभ तिथि <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="training_start_date" name="training_start_date" required
                                       value="<?php echo htmlspecialchars($certificate['training_start_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="training_end_date" class="form-label">प्रशिक्षण समाप्ति तिथि <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="training_end_date" name="training_end_date" required
                                       value="<?php echo htmlspecialchars($certificate['training_end_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="duration_hours" class="form-label">अवधि (घंटे)</label>
                                <input type="number" class="form-control" id="duration_hours" name="duration_hours" min="1" max="1000"
                                       value="<?php echo htmlspecialchars($certificate['duration_hours'] ?? ''); ?>"
                                       placeholder="प्रशिक्षण की कुल घंटे">
                            </div>
                        </div>

                        <!-- Trainer and Assessment -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="trainer_name" class="form-label">प्रशिक्षक का नाम</label>
                                <input type="text" class="form-control" id="trainer_name" name="trainer_name"
                                       value="<?php echo htmlspecialchars($certificate['trainer_name'] ?? ''); ?>"
                                       placeholder="मुख्य प्रशिक्षक का नाम">
                            </div>
                            <div class="col-md-3">
                                <label for="grade_achieved" class="form-label">प्राप्त ग्रेड</label>
                                <select class="form-select" id="grade_achieved" name="grade_achieved">
                                    <option value="">-- ग्रेड चुनें --</option>
                                    <?php foreach ($trainingGrades as $grade => $config): ?>
                                        <option value="<?php echo $grade; ?>" <?php echo (isset($certificate['grade_achieved']) && $certificate['grade_achieved'] == $grade) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($config['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="score_percentage" class="form-label">स्कोर (%)</label>
                                <input type="number" class="form-control" id="score_percentage" name="score_percentage" 
                                       min="0" max="100" step="0.01"
                                       value="<?php echo htmlspecialchars($certificate['score_percentage'] ?? ''); ?>"
                                       placeholder="0-100">
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="remarks" class="form-label">टिप्पणी/विशेष उल्लेख</label>
                                <textarea class="form-control" id="remarks" name="remarks" rows="3"
                                          placeholder="प्रशिक्षण के दौरान विशेष उपलब्धियां या टिप्पणी"><?php echo htmlspecialchars($certificate['remarks'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Issue and Expiry Dates -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="issue_date" class="form-label">जारी करने की तिथि <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date" required
                                       value="<?php echo htmlspecialchars($certificate['issue_date'] ?? date('Y-m-d')); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="expiry_date" class="form-label">समाप्ति तिथि</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                                       value="<?php echo htmlspecialchars($certificate['expiry_date'] ?? ''); ?>">
                                <small class="text-muted">वैकल्पिक: यदि प्रमाणपत्र की समय सीमा है</small>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">स्थिति <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo (isset($certificate['status']) && $certificate['status'] == 'active') ? 'selected' : ''; ?>>सक्रिय</option>
                                    <option value="inactive" <?php echo (isset($certificate['status']) && $certificate['status'] == 'inactive') ? 'selected' : ''; ?>>निष्क्रिय</option>
                                    <option value="expired" <?php echo (isset($certificate['status']) && $certificate['status'] == 'expired') ? 'selected' : ''; ?>>समाप्त</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($action === 'add') ? "प्रमाणपत्र बनाएं" : "अपडेट करें"; ?>
                            </button>
                            <a href="training_certificates.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> रद्द करें
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Certificate List View -->
            <div class="admin-card">
                <div class="card-header">
                    <i class="fas fa-graduation-cap"></i> प्रशिक्षण प्रमाणपत्र सूची
                    <div class="card-header-actions">
                        <small class="text-muted">कुल प्रमाणपत्र: <?php echo $totalRecords; ?></small>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($certificates) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>प्रमाणपत्र नं.</th>
                                        <th>प्रकार</th>
                                        <th>प्रशिक्षणार्थी</th>
                                        <th>प्रशिक्षण</th>
                                        <th>अवधि</th>
                                        <th>ग्रेड</th>
                                        <th>जारी तिथि</th>
                                        <th>स्थिति</th>
                                        <th>कार्य</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificates as $cert): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($cert['certificate_no']); ?></strong>
                                                <?php if (!empty($cert['trainee_id'])): ?>
                                                    <br><small class="text-muted">ID: <?php echo htmlspecialchars($cert['trainee_id']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $trainingCertificateTypes[$cert['certificate_type']]['color_scheme']; ?>;">
                                                    <?php echo htmlspecialchars($trainingCertificateTypes[$cert['certificate_type']]['name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($cert['trainee_name']); ?></strong>
                                                <?php if (!empty($cert['trainer_name'])): ?>
                                                    <br><small class="text-muted">प्रशिक्षक: <?php echo htmlspecialchars($cert['trainer_name']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($cert['training_name']); ?></strong>
                                                <?php if (!empty($cert['program_name'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($cert['program_name']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($cert['duration_hours'] > 0): ?>
                                                    <?php echo $cert['duration_hours']; ?> घंटे
                                                <?php endif; ?>
                                                <br><small class="text-muted">
                                                    <?php echo date('d/m/Y', strtotime($cert['training_start_date'])); ?> - 
                                                    <?php echo date('d/m/Y', strtotime($cert['training_end_date'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (!empty($cert['grade_achieved'])): ?>
                                                    <span class="badge" style="background-color: <?php echo $trainingGrades[$cert['grade_achieved']]['color']; ?>;">
                                                        <?php echo htmlspecialchars($cert['grade_achieved']); ?>
                                                    </span>
                                                    <?php if ($cert['score_percentage'] > 0): ?>
                                                        <br><small><?php echo $cert['score_percentage']; ?>%</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('d-m-Y', strtotime($cert['issue_date'])); ?>
                                                <?php if (!empty($cert['expiry_date'])): ?>
                                                    <br><small class="text-muted">समाप्ति: <?php echo date('d-m-Y', strtotime($cert['expiry_date'])); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $cert['status'] == 'active' ? 'badge-success' : 
                                                        ($cert['status'] == 'expired' ? 'badge-warning' : 'badge-danger'); 
                                                ?>">
                                                    <?php 
                                                    echo $cert['status'] == 'active' ? 'सक्रिय' : 
                                                        ($cert['status'] == 'expired' ? 'समाप्त' : 'निष्क्रिय'); 
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="training_certificates.php?action=edit&id=<?php echo $cert['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="संपादित करें">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-success generate-training-certificate" 
                                                            data-certificate='<?php echo json_encode([
                                                                'certificate_no' => $cert['certificate_no'],
                                                                'trainee_name' => $cert['trainee_name'],
                                                                'trainee_id' => $cert['trainee_id'],
                                                                'training_name' => $cert['training_name'],
                                                                'certificate_type' => $cert['certificate_type'],
                                                                'training_start_date' => $cert['training_start_date'],
                                                                'training_end_date' => $cert['training_end_date'],
                                                                'duration_hours' => $cert['duration_hours'],
                                                                'trainer_name' => $cert['trainer_name'],
                                                                'grade_achieved' => $cert['grade_achieved'],
                                                                'score_percentage' => $cert['score_percentage'],
                                                                'remarks' => $cert['remarks'],
                                                                'issue_date' => $cert['issue_date'],
                                                                'expiry_date' => $cert['expiry_date']
                                                            ]); ?>' 
                                                            title="डाउनलोड करें">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-certificate-btn" 
                                                            data-id="<?php echo $cert['id']; ?>" 
                                                            data-cert-no="<?php echo htmlspecialchars($cert['certificate_no']); ?>" 
                                                            title="हटाएं">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> कोई प्रशिक्षण प्रमाणपत्र नहीं मिला।
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
                            <h5 class="modal-title" id="deleteCertificateModalLabel">पुष्टि करें</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>क्या आप वाकई प्रशिक्षण प्रमाणपत्र <strong id="certificateNo"></strong> को हटाना चाहते हैं?</p>
                            <p class="text-danger mt-2">यह क्रिया पूर्ववत नहीं की जा सकती है।</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                            <form method="post" id="deleteCertificateForm">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="cert_id" id="cert_id">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                                <button type="submit" class="btn btn-danger">हटाएं</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enhanced CSS for Training Certificates -->
<style>
.golden-badge {
    background: linear-gradient(45deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
    border: 4px solid #b8860b;
    border-radius: 50px;
    padding: 12px 20px;
    color: #8b4513;
    font-weight: bold;
    font-size: 16px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    box-shadow: 0 6px 12px rgba(0,0,0,0.3), inset 0 2px 4px rgba(255,255,255,0.5);
    position: absolute;
    z-index: 10;
    white-space: nowrap;
    text-align: center;
}

.certificate-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 8px solid #d4af37;
    border-radius: 25px;
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.4);
    position: relative;
}

.decorative-corner {
    position: absolute;
    width: 100px;
    height: 100px;
    border: 8px solid #d4af37;
}

.training-header {
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
    height: 3px;
    margin: 20px auto;
    width: 300px;
}

.grade-badge {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 20px;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.btn-group .btn {
    margin: 0 2px;
}

@media print {
    .certificate-container {
        width: 100%;
        height: auto;
        border: none;
        box-shadow: none;
    }
}
</style>

<!-- Enhanced JavaScript for Training Certificate Generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const { jsPDF } = window.jspdf;
    
    // Pass PHP certificate content to JavaScript
    const certificateContent = <?= json_encode($certificateContent) ?>;
    const certificateTypes = certificateContent.certificate_types;
    const trainingGrades = certificateContent.training_grades;

    // Function to create golden badge
    function createGoldenBadge(text, rotation = 0) {
        return `
            <div style="
                background: linear-gradient(45deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
                border: 4px solid #b8860b;
                border-radius: 50px;
                padding: 15px 25px;
                color: #8b4513;
                font-weight: bold;
                font-size: 18px;
                text-shadow: 2px 2px 3px rgba(0,0,0,0.4);
                box-shadow: 0 8px 15px rgba(0,0,0,0.3), inset 0 3px 6px rgba(255,255,255,0.6);
                transform: rotate(${rotation}deg);
                position: absolute;
                z-index: 10;
                white-space: nowrap;
                text-align: center;
                font-family: Arial, sans-serif;
                letter-spacing: 1px;
            ">
                ${text}
            </div>
        `;
    }

    // Training certificate generation
    document.querySelectorAll('.generate-training-certificate').forEach(button => {
        button.addEventListener('click', async function() {
            try {
                const certData = JSON.parse(this.getAttribute('data-certificate'));
                console.log('Training Certificate Data:', certData);

                const certificateType = certData.certificate_type || 'technical_training';
                const typeConfig = certificateTypes[certificateType] || certificateTypes.technical_training;

                // Create certificate container with built-in template design
                const container = document.createElement('div');
                container.style.cssText = `
                    width: 1404px;
                    height: 990px;
                    position: fixed;
                    left: -9999px;
                    font-family: Arial, 'Noto Sans Devanagari', sans-serif;
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 50%, #ffffff 100%);
                    border: 12px solid #d4af37;
                    border-radius: 25px;
                    box-shadow: 0 0 40px rgba(212, 175, 55, 0.5), inset 0 0 50px rgba(212, 175, 55, 0.1);
                    padding: 0;
                    position: relative;
                    overflow: hidden;
                `;

                // Add decorative background pattern
                const backgroundPattern = `
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.03; z-index: 1;">
                        <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <pattern id="diagonalHatch" patternUnits="userSpaceOnUse" width="20" height="20">
                                    <path d="M-5,5 l10,-10 M0,20 l20,-20 M15,25 l10,-10" stroke="#d4af37" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#diagonalHatch)" />
                        </svg>
                    </div>
                    <div style="position: absolute; top: 50px; left: 50px; width: 200px; height: 200px; 
                               background: radial-gradient(circle, rgba(212,175,55,0.1) 0%, transparent 70%);
                               border-radius: 50%; z-index: 1;"></div>
                    <div style="position: absolute; bottom: 50px; right: 50px; width: 300px; height: 300px; 
                               background: radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 70%);
                               border-radius: 50%; z-index: 1;"></div>
                `;

                // Format dates
                const formatDate = (inputDate) => {
                    if (!inputDate) return '';
                    let date = new Date(inputDate);
                    return date.toLocaleDateString('hi-IN', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                };

                const formattedIssueDate = formatDate(certData.issue_date);
                const formattedStartDate = formatDate(certData.training_start_date);
                const formattedEndDate = formatDate(certData.training_end_date);

                // Build dynamic certificate text
                let certificateText = typeConfig.text_template
                    .replace('{name}', certData.trainee_name)
                    .replace('{training_name}', certData.training_name)
                    .replace('{duration}', certData.duration_hours || '');

                // Add training period if available
                if (certData.training_start_date && certData.training_end_date) {
                    certificateText += ` जो ${formattedStartDate} से ${formattedEndDate} तक संचालित किया गया।`;
                }

                // Add remarks if available
                if (certData.remarks) {
                    certificateText += ` ${certData.remarks}`;
                }

                container.innerHTML = `
                    ${backgroundPattern}
                    <div style="position: relative; height: 100%; padding: 30px; z-index: 2;">
                        
                        <!-- Decorative Corners with Enhanced Design -->
                        <div style="position: absolute; top: 15px; left: 15px; width: 150px; height: 150px; 
                                   background: linear-gradient(135deg, #d4af37, #b8860b);
                                   border-radius: 25px 0 0 0; opacity: 0.8;"></div>
                        <div style="position: absolute; top: 20px; left: 20px; width: 120px; height: 120px; 
                                   border-left: 10px solid #ffffff; border-top: 10px solid #ffffff; 
                                   border-radius: 25px 0 0 0;"></div>
                        
                        <div style="position: absolute; top: 15px; right: 15px; width: 150px; height: 150px; 
                                   background: linear-gradient(225deg, #d4af37, #b8860b);
                                   border-radius: 0 25px 0 0; opacity: 0.8;"></div>
                        <div style="position: absolute; top: 20px; right: 20px; width: 120px; height: 120px; 
                                   border-right: 10px solid #ffffff; border-top: 10px solid #ffffff; 
                                   border-radius: 0 25px 0 0;"></div>
                        
                        <div style="position: absolute; bottom: 15px; left: 15px; width: 150px; height: 150px; 
                                   background: linear-gradient(45deg, #d4af37, #b8860b);
                                   border-radius: 0 0 0 25px; opacity: 0.8;"></div>
                        <div style="position: absolute; bottom: 20px; left: 20px; width: 120px; height: 120px; 
                                   border-left: 10px solid #ffffff; border-bottom: 10px solid #ffffff; 
                                   border-radius: 0 0 0 25px;"></div>
                        
                        <div style="position: absolute; bottom: 15px; right: 15px; width: 150px; height: 150px; 
                                   background: linear-gradient(315deg, #d4af37, #b8860b);
                                   border-radius: 0 0 25px 0; opacity: 0.8;"></div>
                        <div style="position: absolute; bottom: 20px; right: 20px; width: 120px; height: 120px; 
                                   border-right: 10px solid #ffffff; border-bottom: 10px solid #ffffff; 
                                   border-radius: 0 0 25px 0;"></div>

                        <!-- Left Golden Badge -->
                        <div style="position: absolute; top: 100px; left: 60px; transform: rotate(-15deg); z-index: 10;">
                            ${createGoldenBadge(typeConfig.badge_text, -15)}
                        </div>
                        
                        <!-- Right Golden Badge -->
                        <div style="position: absolute; top: 100px; right: 60px; transform: rotate(15deg); z-index: 10;">
                            ${createGoldenBadge('CERTIFIED', 15)}
                        </div>
                        
                        <!-- Organization Header -->
                        <div style="text-align: center; margin-top: 40px; z-index: 3; position: relative;">
                            <h1 style="font-size: 42px; color: #2c5282; margin: 0; font-weight: bold; 
                                       text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                                ${certificateContent.organization_name_hindi}
                            </h1>
                            <p style="font-size: 28px; color: #666; margin: 5px 0; font-style: italic;">
                                ${certificateContent.organization_name}
                            </p>
                            <p style="font-size: 24px; color: #d4af37; margin: 10px 0; font-weight: bold;">
                                ${certificateContent.header_text}
                            </p>
                        </div>
                        
                        <!-- Certificate Type Header with Enhanced Styling -->
                        <div style="text-align: center; margin: 30px 0; z-index: 3; position: relative;">
                            <div style="width: 300px; height: 4px; 
                                       background: linear-gradient(90deg, transparent, #d4af37, transparent); 
                                       margin: 0 auto; border-radius: 2px;"></div>
                            <h2 style="font-size: 38px; font-weight: bold; color: ${typeConfig.color_scheme}; 
                                       margin: 20px 0 10px 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                                       background: linear-gradient(135deg, ${typeConfig.color_scheme}, ${typeConfig.color_scheme}aa);
                                       -webkit-background-clip: text; -webkit-text-fill-color: transparent;
                                       background-clip: text;">
                                ${typeConfig.name}
                            </h2>
                            <p style="font-size: 26px; color: #666; margin: 0; font-style: italic;">
                                ${typeConfig.name_english}
                            </p>
                            <div style="width: 300px; height: 4px; 
                                       background: linear-gradient(90deg, transparent, #d4af37, transparent); 
                                       margin: 20px auto; border-radius: 2px;"></div>
                        </div>
                        
                        <!-- Trainee Name with Enhanced Styling -->
                        <div style="text-align: center; margin: 40px 0; z-index: 3; position: relative;">
                            <div style="display: inline-block; padding: 25px 50px; 
                                       background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.05)); 
                                       border: 4px solid #d4af37; border-radius: 20px;
                                       box-shadow: 0 8px 16px rgba(0,0,0,0.15), inset 0 2px 4px rgba(255,255,255,0.5);
                                       position: relative; overflow: hidden;">
                                <!-- Subtle shine effect -->
                                <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
                                           background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
                                           animation: shine 3s infinite; pointer-events: none;"></div>
                                <h3 style="font-size: 56px; font-weight: bold; color: ${typeConfig.color_scheme}; 
                                           margin: 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.1); letter-spacing: 2px;
                                           position: relative; z-index: 1;">
                                    ${certData.trainee_name}
                                </h3>
                                ${certData.trainee_id ? `<p style="font-size: 20px; color: #666; margin: 5px 0; position: relative; z-index: 1;">ID: ${certData.trainee_id}</p>` : ''}
                            </div>
                        </div>
                        
                        <!-- Training Description -->
                        <div style="text-align: center; margin: 30px auto; max-width: 85%; 
                                   background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.9)); 
                                   padding: 30px; border-radius: 20px; 
                                   box-shadow: 0 6px 12px rgba(0,0,0,0.1), inset 0 1px 3px rgba(255,255,255,0.5);
                                   border-left: 8px solid ${typeConfig.color_scheme}; z-index: 3; position: relative;">
                            <p style="font-size: 32px; color: #2d3748; line-height: 1.6; margin: 0; 
                                     font-family: 'Noto Sans Devanagari', Arial, sans-serif; font-weight: 500;">
                                ${certificateText}
                            </p>
                        </div>
                        
                        <!-- Grade and Performance Section -->
                        ${certData.grade_achieved || certData.score_percentage > 0 ? `
                        <div style="text-align: center; margin: 25px 0; z-index: 3; position: relative;">
                            ${certData.grade_achieved ? `
                                <div style="display: inline-block; padding: 18px 35px; 
                                           background: linear-gradient(135deg, ${trainingGrades[certData.grade_achieved] ? trainingGrades[certData.grade_achieved].color : '#6b7280'}, ${trainingGrades[certData.grade_achieved] ? trainingGrades[certData.grade_achieved].color + 'cc' : '#6b7280cc'}); 
                                           color: white; border-radius: 30px; font-size: 26px; font-weight: bold;
                                           box-shadow: 0 6px 12px rgba(0,0,0,0.2), inset 0 2px 4px rgba(255,255,255,0.2);
                                           text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                                    ग्रेड: ${certData.grade_achieved}
                                </div>
                            ` : ''}
                            ${certData.score_percentage > 0 ? `
                                <div style="display: inline-block; margin: 0 15px; padding: 18px 35px; 
                                           background: linear-gradient(135deg, #059669, #047857); color: white; border-radius: 30px; 
                                           font-size: 26px; font-weight: bold; 
                                           box-shadow: 0 6px 12px rgba(0,0,0,0.2), inset 0 2px 4px rgba(255,255,255,0.2);
                                           text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                                    स्कोर: ${certData.score_percentage}%
                                </div>
                            ` : ''}
                        </div>
                        ` : ''}
                        
                        <!-- Training Details Footer -->
                        <div style="display: flex; justify-content: space-between; align-items: end; margin-top: 40px; z-index: 3; position: relative;">
                            
                            <!-- Left Side - Certificate Details -->
                            <div style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(212, 175, 55, 0.05)); 
                                       padding: 25px; border-radius: 20px; border-left: 6px solid #d4af37;
                                       box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                <div style="font-size: 24px; color: #2d3748; font-weight: bold; margin-bottom: 10px;">
                                    प्रमाणपत्र संख्या: ${certData.certificate_no}
                                </div>
                                <div style="font-size: 20px; color: #666; margin-bottom: 8px;">
                                    जारी दिनांक: ${formattedIssueDate}
                                </div>
                                ${certData.expiry_date ? `
                                    <div style="font-size: 20px; color: #dc2626; margin-bottom: 8px; font-weight: 600;">
                                        समाप्ति दिनांक: ${formatDate(certData.expiry_date)}
                                    </div>
                                ` : ''}
                                ${certData.trainer_name ? `
                                    <div style="font-size: 20px; color: #666;">
                                        प्रशिक्षक: ${certData.trainer_name}
                                    </div>
                                ` : ''}
                            </div>
                            
                            <!-- Right Side - Authority Signature -->
                            <div style="text-align: center; 
                                       background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.9)); 
                                       padding: 30px; border-radius: 20px; 
                                       box-shadow: 0 8px 16px rgba(0,0,0,0.15), inset 0 2px 4px rgba(255,255,255,0.5);
                                       border: 4px solid #d4af37; position: relative;">
                                
                                <!-- Digital Signature Placeholder (since we don't have the actual image) -->
                                <div style="width: 180px; height: 60px; margin: 0 auto 15px auto; 
                                           background: linear-gradient(45deg, #2c5282, #1e40af); 
                                           border-radius: 5px; display: flex; align-items: center; justify-content: center;
                                           color: white; font-size: 16px; font-style: italic;">
                                    Digital Signature
                                </div>
                                
                                <!-- Official Seal Placeholder (Overlaid) -->
                                <div style="position: absolute; top: 15px; right: 15px; 
                                           width: 80px; height: 80px; border-radius: 50%; 
                                           background: radial-gradient(circle, #d4af37, #b8860b); 
                                           opacity: 0.8; display: flex; align-items: center; justify-content: center;
                                           color: white; font-size: 10px; font-weight: bold; text-align: center;">
                                    OFFICIAL<br>SEAL
                                </div>
                                
                                <!-- Authority Details -->
                                <div style="border-top: 3px solid #d4af37; padding-top: 15px;">
                                    <p style="margin: 0; font-size: 24px; font-weight: bold; color: #000;">
                                        ${certificateContent.chairman_name}
                                    </p>
                                    <p style="margin: 3px 0 0 0; font-size: 20px; color: ${typeConfig.color_scheme}; font-weight: 600;">
                                        ${certificateContent.chairman_title}
                                    </p>
                                    <p style="margin: 3px 0 0 0; font-size: 18px; color: #666;">
                                        ${certificateContent.organization_name_hindi}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer Information -->
                        <div style="position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); 
                                   text-align: center; font-size: 16px; color: #666; z-index: 3;
                                   background: rgba(255,255,255,0.8); padding: 10px 20px; border-radius: 10px;">
                            <div style="font-weight: bold;">${certificateContent.registration_number}</div>
                            <div>${certificateContent.address}</div>
                            <div>Email: ${certificateContent.email} | Phone: ${certificateContent.phone}</div>
                        </div>
                    </div>
                    
                    <!-- CSS Animation for shine effect -->
                    <style>
                        @keyframes shine {
                            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
                            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
                        }
                    </style>
                `;</p>
                                    <p style="margin: 3px 0 0 0; font-size: 16px; color: #666;">
                                        ${certificateContent.organization_name_hindi}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer Information -->
                        <div style="position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); 
                                   text-align: center; font-size: 16px; color: #666;">
                            <div>${certificateContent.registration_number}</div>
                            <div>${certificateContent.address}</div>
                            <div>Email: ${certificateContent.email} | Phone: ${certificateContent.phone}</div>
                        </div>
                    </div>
                `;

                // Add container to DOM
                document.body.appendChild(container);

                // Wait for images to load
                const images = container.querySelectorAll('img');
                const imagePromises = Array.from(images).map(img => {
                    return new Promise((resolve) => {
                        if (img.complete) {
                            resolve();
                        } else {
                            img.onload = resolve;
                            img.onerror = resolve;
                        }
                    });
                });
                await Promise.all(imagePromises);

                // Additional delay to ensure complete rendering
                await new Promise(resolve => setTimeout(resolve, 800));

                // Generate high-quality PDF
                const canvas = await html2canvas(container, {
                    scale: 3,
                    useCORS: true,
                    logging: false,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    width: 1404,
                    height: 990
                });

                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'px',
                    format: [1404, 990]
                });

                pdf.addImage(imgData, 'JPEG', 0, 0, 1404, 990);
                pdf.save(`${certData.certificate_no}_${certificateType}_training_certificate.pdf`);

                // Remove container
                document.body.removeChild(container);

                // Success notification
                showNotification(`${typeConfig.name} सफलतापूर्वक डाउनलोड हुआ!`, 'success');

            } catch (error) {
                console.error('Training certificate generation error:', error);
                alert('प्रशिक्षण प्रमाणपत्र बनाने में त्रुटि। कृपया पुनः प्रयास करें।');
            }
        });
    });

    // Form functionality for training program selection
    const trainingProgramSelect = document.getElementById('training_program_id');
    const trainingNameInput = document.getElementById('training_name');
    const durationHoursInput = document.getElementById('duration_hours');

    if (trainingProgramSelect && trainingNameInput) {
        trainingProgramSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value && selectedOption.dataset.name) {
                trainingNameInput.value = selectedOption.dataset.name;
                if (durationHoursInput && selectedOption.dataset.hours) {
                    durationHoursInput.value = selectedOption.dataset.hours;
                }
            }
        });
    }

    // Auto-calculate training duration
    const startDateInput = document.getElementById('training_start_date');
    const endDateInput = document.getElementById('training_end_date');

    if (startDateInput && endDateInput) {
        function updateDuration() {
            if (startDateInput.value && endDateInput.value) {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                const diffTime = endDate - startDate;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                if (diffDays > 0) {
                    // Estimate hours based on days (assuming 8 hours per day)
                    if (!durationHoursInput.value || durationHoursInput.value == 0) {
                        durationHoursInput.value = diffDays * 8;
                    }
                }
            }
        }

        startDateInput.addEventListener('change', updateDuration);
        endDateInput.addEventListener('change', updateDuration);
    }

    // Grade-based score validation
    const gradeSelect = document.getElementById('grade_achieved');
    const scoreInput = document.getElementById('score_percentage');

    if (gradeSelect && scoreInput) {
        gradeSelect.addEventListener('change', function() {
            const selectedGrade = this.value;
            if (selectedGrade && trainingGrades[selectedGrade]) {
                const minScore = trainingGrades[selectedGrade].min_score;
                if (!scoreInput.value || parseFloat(scoreInput.value) < minScore) {
                    scoreInput.value = minScore;
                }
                scoreInput.min = minScore;
            } else {
                scoreInput.min = 0;
            }
        });
    }

    // Delete certificate modal handling
    const deleteCertificateModal = document.getElementById('deleteCertificateModal');
    const deleteCertificateForm = document.getElementById('deleteCertificateForm');
    const certificateNoSpan = document.getElementById('certificateNo');
    const certIdInput = document.getElementById('cert_id');
    
    document.querySelectorAll('.delete-certificate-btn').forEach(button => {
        button.addEventListener('click', function() {
            const certificateId = this.getAttribute('data-id');
            const certificateNo = this.getAttribute('data-cert-no');
            
            if (certificateNoSpan) {
                certificateNoSpan.textContent = certificateNo;
            }
            
            if (certIdInput) {
                certIdInput.value = certificateId;
            }
            
            if (deleteCertificateModal && typeof bootstrap !== 'undefined') {
                const modal = new bootstrap.Modal(deleteCertificateModal);
                modal.show();
            }
        });
    });

    // Form validation with enhanced feedback
    const certificateForm = document.querySelector('form.needs-validation');
    if (certificateForm) {
        certificateForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                showNotification('कृपया सभी आवश्यक फील्ड सही तरीके से भरें।', 'danger');
            }
            this.classList.add('was-validated');
        });

        // Real-time validation
        const requiredFields = certificateForm.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
    }

    // Date validation (end date should be after start date)
    if (startDateInput && endDateInput) {
        endDateInput.addEventListener('change', function() {
            if (startDateInput.value && this.value) {
                if (new Date(this.value) < new Date(startDateInput.value)) {
                    this.setCustomValidity('समाप्ति तिथि प्रारंभ तिथि के बाद होनी चाहिए');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
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
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500);
                }
            }, 6000);
        }
    });

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transition = 'opacity 0.5s ease';
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 500);
            }
        }, 5000);
    }

    // Auto-refresh expired certificates status
    function checkExpiredCertificates() {
        const today = new Date();
        document.querySelectorAll('[data-expiry-date]').forEach(element => {
            const expiryDate = new Date(element.dataset.expiryDate);
            if (expiryDate < today && element.textContent !== 'समाप्त') {
                element.textContent = 'समाप्त';
                element.className = element.className.replace('badge-success', 'badge-warning');
            }
        });
    }

    // Check expired certificates on page load
    checkExpiredCertificates();
    
    // Periodically check for expired certificates (every 5 minutes)
    setInterval(checkExpiredCertificates, 300000);
});
</script>

<?php include 'includes/footer.php'; ?>