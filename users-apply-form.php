<?php
require_once 'config/config.php';
// require_once 'config/razorpay-order-api.php'; // COMMENTED: Uncomment when enabling online payments

// Database connection
$db = getDbConnection();

// Handle AJAX request for designations
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'get_designations') {
    // Clear any previous output to prevent JSON errors
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    if (!isset($_GET['membership_type']) || empty($_GET['membership_type'])) {
        echo json_encode(['success' => false, 'message' => 'Membership type is required']);
        exit;
    }

    // Use trim to get raw value, we validate against whitelist below
    $membership_type = trim($_GET['membership_type']);
    
    $valid_memberships = ['active', 'gram_panchayat', 'block', 'tehsil', 'district', 'mandal', 'state', 'national'];
    
    if (!in_array($membership_type, $valid_memberships)) {
        echo json_encode(['success' => false, 'message' => 'Invalid membership type']);
        exit;
    }

    try {
        // FIXED: Direct query to fetch designations from membership_designations table
        // We select both English and Hindi designations
        $stmt = $db->prepare("SELECT designation, designation_hindi FROM membership_designations WHERE membership_type = ? AND status = 'active' ORDER BY sort_order ASC");
        $stmt->execute([$membership_type]);
        $designations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Use JSON_UNESCAPED_UNICODE to handle Hindi characters correctly
        echo json_encode(['success' => true, 'designations' => $designations], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        // Log the actual error for admin, show generic message to user
        if (function_exists('logError')) {
            logError('Error fetching designations: ' . $e->getMessage());
        }
        echo json_encode(['success' => false, 'message' => 'Failed to fetch designations']);
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_user') {
    header('Content-Type: application/json');

    if (!verifyCSRF($_POST['csrf_token'])) {
        logError('Invalid CSRF token in user apply form submission');
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    // Validate required fields
    $required_fields = ['membership_charge', 'payment_method', 'Name', 'Gender', 'Dob', 'sdw_type', 'sdw_name', 'Mobile', 'Aadhar_no', 'State', 'City', 'Address', 'Pincode', 'designation', 'Password', 'Email', 'Working_area'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            logError("Missing required field: $field");
            echo json_encode(['success' => false, 'message' => "Please fill the '$field' field"]);
            exit;
        }
    }

    $membership_type = sanitizeInput($_POST['membership_charge']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    $name = sanitizeInput($_POST['Name']);
    $gender = sanitizeInput($_POST['Gender']);
    $dob = sanitizeInput($_POST['Dob']);
    $sdw_type = sanitizeInput($_POST['sdw_type']);
    $sdw_name = sanitizeInput($_POST['sdw_name']);
    $profession = !empty($_POST['Profession']) ? sanitizeInput($_POST['Profession']) : null;
    $blood_group = !empty($_POST['Blood_group']) ? sanitizeInput($_POST['Blood_group']) : null;
    $mobile = sanitizeInput($_POST['Mobile']);
    $aadhar_no = sanitizeInput($_POST['Aadhar_no']);
    $state = sanitizeInput($_POST['State']);
    $city = sanitizeInput($_POST['City']);
    $address = sanitizeInput($_POST['Address']);
    $pincode = sanitizeInput($_POST['Pincode']);
    $email = sanitizeInput($_POST['Email']);
    $password = password_hash(sanitizeInput($_POST['Password']), PASSWORD_DEFAULT);
    $designation = sanitizeInput($_POST['designation']);
    $working_area = sanitizeInput($_POST['Working_area']);
    $order_id = !empty($_POST['order_id']) ? sanitizeInput($_POST['order_id']) : null;
    $payment_id = !empty($_POST['payment_id']) ? sanitizeInput($_POST['payment_id']) : null;
    // MODIFIED: Always pending for offline payments
    $status = 'pending'; // Previously: ($payment_method === 'online') ? 'approved' : 'pending';
    $user_type = 'member';
    // MODIFIED: No valid_until for pending registrations
    $valid_until = null; // Previously: ($payment_method === 'online') ? date('Y-m-d', strtotime('+1 year')) : null;
    $valid_from = date('Y-m-d');
    $profile_image = null;
    $aadhar_front = null;
    $aadhar_back = null;
    $payment_proof = null;

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logError("Invalid email format: $email");
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit;
    }

    // Validate mobile, Aadhaar, and pincode
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        logError("Invalid mobile number: $mobile");
        echo json_encode(['success' => false, 'message' => 'Mobile number must be 10 digits']);
        exit;
    }
    if (!preg_match('/^[0-9]{12}$/', $aadhar_no)) {
        logError("Invalid Aadhaar number: $aadhar_no");
        echo json_encode(['success' => false, 'message' => 'Aadhaar number must be 12 digits']);
        exit;
    }
    if (!preg_match('/^[0-9]{6}$/', $pincode)) {
        logError("Invalid pincode: $pincode");
        echo json_encode(['success' => false, 'message' => 'Pincode must be 6 digits']);
        exit;
    }

    // Check for duplicate Aadhaar, mobile, or email
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE aadhar = ? OR mobile = ? OR email = ?");
        $stmt->execute([$aadhar_no, $mobile, $email]);
        if ($stmt->fetchColumn() > 0) {
            logError("Duplicate Aadhaar ($aadhar_no), mobile ($mobile), or email ($email)");
            echo json_encode(['success' => false, 'message' => 'Aadhaar, mobile number, or email already registered']);
            exit;
        }
    } catch (PDOException $e) {
        logError('Error checking duplicates: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to validate Aadhaar/mobile/email. Please try again.']);
        exit;
    }

    // Validate membership type
    $valid_memberships = ['active', 'gram_panchayat', 'block', 'tehsil', 'district', 'mandal', 'state', 'national'];
    if (!in_array($membership_type, $valid_memberships)) {
        logError("Invalid membership type: $membership_type");
        echo json_encode(['success' => false, 'message' => 'Invalid membership type']);
        exit;
    }

    // Validate designation
    // FIXED: Direct query to fetch valid designations for validation
    try {
        $stmt = $db->prepare("SELECT designation FROM membership_designations WHERE membership_type = ? AND status = 'active'");
        $stmt->execute([$membership_type]);
        $valid_designations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array($designation, $valid_designations)) {
            logError("Invalid designation: $designation for membership type: $membership_type");
            echo json_encode(['success' => false, 'message' => 'Invalid designation']);
            exit;
        }
    } catch (PDOException $e) {
        logError('Error validating designation: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to validate designation.']);
        exit;
    }

    // Handle file uploads with enhanced validation
    $required_files = [
        'Profile' => 'profile_image',
        'Aadhar_front' => 'aadhar_front',
        'Aadhar_back' => 'aadhar_back'
    ];
    foreach ($required_files as $file_field => $db_field) {
        if (!empty($_FILES[$file_field]['name']) && $_FILES[$file_field]['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES[$file_field]['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            if (!in_array($extension, $allowed_extensions)) {
                logError("Invalid file extension for $file_field: $extension");
                echo json_encode(['success' => false, 'message' => "Invalid file type for $file_field. Only JPG, PNG, GIF, and PDF allowed."]);
                exit;
            }
            $uploadResult = uploadFile($_FILES[$file_field], 'uploads/profiles');
            if ($uploadResult['success']) {
                $$db_field = $uploadResult['filename'];
            } else {
                logError("File upload failed for $file_field: " . $uploadResult['message']);
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                exit;
            }
        } else {
            logError("Missing or invalid file upload for $file_field");
            echo json_encode(['success' => false, 'message' => "$file_field is required"]);
            exit;
        }
    }

    // Handle payment proof for offline payments
    if ($payment_method === 'offline') {
        if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            if (!in_array($extension, $allowed_extensions)) {
                logError("Invalid file extension for payment_proof: $extension");
                echo json_encode(['success' => false, 'message' => "Invalid file type for payment proof. Only JPG, PNG, GIF, and PDF allowed."]);
                exit;
            }
            $uploadResult = uploadFile($_FILES['payment_proof'], 'uploads/payments');
            if ($uploadResult['success']) {
                $payment_proof = $uploadResult['filename'];
            } else {
                logError('Payment proof upload failed: ' . $uploadResult['message']);
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                exit;
            }
        } else {
            logError('Missing payment proof for offline payment');
            echo json_encode(['success' => false, 'message' => 'Payment proof is required for offline payments']);
            exit;
        }
    }

    // --- UPDATED REGISTRATION ID GENERATION LOGIC ---
    $unique = false;
    $registration_id = '';
    $attempts = 0;
    
    while (!$unique && $attempts < 10) {
        // Generate ID in format PCWWF/XXXXX
        $randNum = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $registration_id = 'PCWWF/' . $randNum;
        
        // Check availability
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE registration_id = ?");
        $checkStmt->execute([$registration_id]);
        
        if ($checkStmt->fetchColumn() == 0) {
            $unique = true;
        }
        $attempts++;
    }
    
    if (!$unique) {
        // Fallback to timestamp if random generation collision persists
        $registration_id = 'PCWWF/' . time(); 
    }
    // ----------------------------------------------

    try {
        $stmt = $db->prepare("
            INSERT INTO users (
                name, email, password, mobile, gender, dob, sdw_type, sdw_name, 
                profession, designation, blood_group, aadhar, state, district, address, 
                pincode, membership_type, profile_image, aadhar_front, 
                aadhar_back, order_id, payment_id, payment_proof, payment_method, 
                registration_id, status, user_type, valid_from, valid_until, working_area, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $name, $email, $password, $mobile, $gender, $dob, $sdw_type, $sdw_name,
            $profession, $designation, $blood_group, $aadhar_no, $state, $city, $address,
            $pincode, $membership_type, $profile_image, $aadhar_front,
            $aadhar_back, $order_id, $payment_id, $payment_proof, $payment_method,
            $registration_id, $status, $user_type, $valid_from, $valid_until, $working_area
        ]);

        $user_id = $db->lastInsertId();
        
        // Send registration confirmation email
        if (!empty($email)) {
            try {
                require_once 'admin/includes/email-templates.php';
                
                $userData = [
                    'registration_id' => $registration_id,
                    'name' => $name,
                    'email' => $email,
                    'membership_type' => $membership_type,
                    'status' => $status,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $emailBody = getRegistrationEmailTemplate($userData);
                $emailSubject = "Registration Confirmation - " . ORGANIZATION_NAME;
                
                $emailSent = sendEmail($email, $emailSubject, $emailBody, true);
                
                if ($emailSent) {
                    logError("Registration confirmation email sent successfully to: $email for registration ID: $registration_id");
                } else {
                    logError("Failed to send registration confirmation email to: $email for registration ID: $registration_id");
                }
            } catch (Exception $e) {
                logError('Error sending registration email: ' . $e->getMessage());
            }
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'registration_id' => $registration_id,
                'user_id' => $user_id,
                // MODIFIED: No receipt URL for pending registrations
                'receipt_url' => null // Previously: $status === 'approved' ? SITE_URL . '/generate_receipt.php?user_id=' . $user_id . '&type=registration' : null
            ]
        ]);
    } catch (PDOException $e) {
        logError('User registration error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to save registration. Please try again.']);
    }
    exit;
}

include 'header.php';
include 'navbar.php';
?>

<!-- Include cities.js for state and district dropdowns -->
<script src="<?php echo SITE_URL; ?>/js/cities.js"></script>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Registration Form</span></h3>

    <div class="card p-3 p-md-5 form-container">
        <form id="user-apply-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="register_user">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
            <input type="hidden" name="order_id" id="order_id">

            <!-- Membership Selection -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-3">
                    <label for="membership_charge" class="form-label fw-bold">Select Membership <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-9">
                    <select class="form-select" id="membership_charge" name="membership_charge" required>
                        <option value="" selected disabled>Select Membership Type</option>
                        <option value="active">Active Membership - ₹ <?php echo ACTIVE_MEMBERSHIP_PRICE; ?></option>
                        <option value="gram_panchayat">Gram Panchayat Level - ₹ <?php echo GRAM_PANCHAYAT_MEMBERSHIP_PRICE; ?></option>
                        <option value="block">Block Level - ₹ <?php echo BLOCK_MEMBERSHIP_PRICE; ?></option>
                        <option value="tehsil">Tehsil Level - ₹ <?php echo TEHSIL_MEMBERSHIP_PRICE; ?></option>
                        <option value="district">District Level - ₹ <?php echo DISTRICT_MEMBERSHIP_PRICE; ?></option>
                        <option value="mandal">Mandal Level - ₹ <?php echo MANDAL_MEMBERSHIP_PRICE; ?></option>
                        <option value="state">State Level - ₹ <?php echo STATE_MEMBERSHIP_PRICE; ?></option>
                        <option value="national">National Level - ₹ <?php echo NATIONAL_MEMBERSHIP_PRICE; ?></option>
                    </select>
                </div>
            </div>

            <!-- Designation Selection -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-3">
                    <label for="designation" class="form-label fw-bold">Designation <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-9">
                    <select class="form-select" id="designation" name="designation" required>
                        <option value="" selected disabled>Select Designation</option>
                    </select>
                </div>
            </div>
            <hr class="my-4">

            <!-- Payment Method - MODIFIED: Only Offline Option -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-3">
                    <label for="payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                </div>
                <div class="col-md-9">
                    <select class="form-select" id="payment_method" name="payment_method" required>
                        <!-- COMMENTED: Uncomment when enabling online payments
                        <option value="online">Online (Razorpay)</option>
                        -->
                        <option value="offline" selected>Offline (Bank Transfer)</option>
                    </select>
                </div>
            </div>
            <hr class="my-4">

            <!-- Personal Details -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="name" class="form-label">Name <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="text" id="name" name="Name" class="form-control" placeholder="Full Name" required></div>
                <div class="col-md-2 form-field-title"><label for="gender" class="form-label">Gender <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <select id="gender" name="Gender" class="form-select" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="date" id="dob" name="Dob" class="form-control" required></div>
                <div class="col-md-2 form-field-title"><label for="sdw_name" class="form-label">S/o, D/o, W/o <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <div class="input-group">
                        <select class="form-select" style="max-width: 80px;" name="sdw_type" required>
                            <option value="S/O">S/O</option>
                            <option value="D/O">D/O</option>
                            <option value="W/O">W/O</option>
                        </select>
                        <input type="text" id="sdw_name" name="sdw_name" class="form-control" placeholder="Father's/Husband's Name" required>
                    </div>
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="password" class="form-label">Password <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="password" id="password" name="Password" class="form-control" placeholder="Create a password" required></div>
                <div class="col-md-2 form-field-title"><label for="email" class="form-label">Email <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="email" id="email" name="Email" class="form-control" placeholder="Email Address" required></div>
            </div>

            <!-- Working Area -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="working_area" class="form-label">Working Area <span class="text-danger">*</span></label></div>
                <div class="col-md-10"><input type="text" id="working_area" name="Working_area" class="form-control" placeholder="Working Area (e.g., Bewar (Mainpuri))" required></div>
            </div>
            <hr class="my-4">

            <!-- Professional Details -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="profession" class="form-label">Profession</label></div>
                <div class="col-md-4">
                    <select id="profession" name="Profession" class="form-select">
                        <option value="">Select Profession</option>
                        <option value="Government Job">Government Job</option>
                        <option value="Private Job">Private Job</option>
                        <option value="Farmer">Farmer</option>
                        <option value="Self Business">Self Business</option>
                        <option value="Student">Student</option>
                    </select>
                </div>
                <div class="col-md-2 form-field-title"><label for="blood_group" class="form-label">Blood Group</label></div>
                <div class="col-md-4">
                    <select id="blood_group" name="Blood_group" class="form-select">
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                    </select>
                </div>
            </div>
            <hr class="my-4">

            <!-- Contact & Address -->
            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="mobile" class="form-label">Mobile No. <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="tel" id="mobile" name="Mobile" class="form-control" placeholder="10-digit mobile number" pattern="[0-9]{10}" required></div>
                <div class="col-md-2 form-field-title"><label for="aadhar" class="form-label">Aadhaar No. <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="text" id="aadhar" name="Aadhar_no" class="form-control" placeholder="12-digit Aadhaar number" pattern="[0-9]{12}" required></div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="state" class="form-label">State <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <select id="state" name="State" class="form-select" required>
                        <option value="">Select State</option>
                    </select>
                </div>
                <div class="col-md-2 form-field-title"><label for="district" class="form-label">District <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <select id="district" name="City" class="form-select" required>
                        <option value="">Select District</option>
                    </select>
                </div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="address" class="form-label">Address <span class="text-danger">*</span></label></div>
                <div class="col-md-10"><textarea id="address" name="Address" required placeholder="Full Address" class="form-control" rows="3"></textarea></div>
            </div>

            <div class="row mb-4 align-items-center">
                <div class="col-md-2 form-field-title"><label for="pincode" class="form-label">Pin Code <span class="text-danger">*</span></label></div>
                <div class="col-md-4"><input type="text" id="pincode" name="Pincode" class="form-control" placeholder="6-digit Pincode" pattern="[0-9]{6}" required></div>
            </div>
            <hr class="my-4">

            <!-- Image uploads -->
            <div class="row mb-4 text-center">
                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold">Profile Picture <span class="text-danger">*</span></h6>
                    <div class="image-uploader">
                        <input type="file" id="profile_pic_input" name="Profile" class="d-none" accept="image/jpeg,image/png,image/gif" required>
                        <label for="profile_pic_input" class="uploader-empty">
                            <i class="fa fa-plus fa-2x"></i>
                            <p>Upload Image</p>
                        </label>
                        <div class="uploader-preview d-none">
                            <img src="#" alt="Preview">
                            <div class="uploader-controls">
                                <button type="button" class="btn btn-light btn-sm view-btn"><i class="fa fa-eye"></i></button>
                                <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold">Aadhaar Card (Front) <span class="text-danger">*</span></h6>
                    <div class="image-uploader">
                        <input type="file" id="aadhaar_input" name="Aadhar_front" class="d-none" accept="image/jpeg,image/png,image/gif" required>
                        <label for="aadhaar_input" class="uploader-empty">
                            <i class="fa fa-plus fa-2x"></i>
                            <p>Upload Aadhaar (Front)</p>
                        </label>
                        <div class="uploader-preview d-none">
                            <img src="#" alt="Preview">
                            <div class="uploader-controls">
                                <button type="button" class="btn btn-light btn-sm view-btn"><i class="fa fa-eye"></i></button>
                                <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold">Aadhaar Card (Back) <span class="text-danger">*</span></h6>
                    <div class="image-uploader">
                        <input type="file" id="aadhar_back_input" name="Aadhar_back" class="d-none" accept="image/jpeg,image/png,image/gif,application/pdf" required>
                        <label for="aadhar_back_input" class="uploader-empty">
                            <i class="fa fa-plus fa-2x"></i>
                            <p>Upload Aadhaar (Back)</p>
                        </label>
                        <div class="uploader-preview d-none">
                            <img src="#" alt="Preview">
                            <div class="uploader-controls">
                                <button type="button" class="btn btn-light btn-sm view-btn"><i class="fa fa-eye"></i></button>
                                <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4">

            <!-- Payment Information -->
            <div class="payment-info-section p-4 rounded mb-4">
                <h4 class="text-center mb-3">Membership Rules & Bank Details</h4>
                <p><strong>Bank Name:</strong> <?php echo htmlspecialchars(BANK_NAME); ?><br>
                <strong>A/c Name:</strong> <?php echo htmlspecialchars(ACCOUNT_NAME); ?><br>
                <strong>A/c No.:</strong> <?php echo htmlspecialchars(ACCOUNT_NUMBER); ?><br>
                <strong>IFSC Code:</strong> <?php echo htmlspecialchars(IFSC_CODE); ?></p>
                <?php if (!empty(QR_CODE_IMAGE)): ?>
                    <div class="text-center mb-3">
                        <h6>Scan to Pay (Offline)</h6>
                        <img src="<?php echo QR_CODE_IMAGE; ?>" alt="QR Code" style="max-width: 150px;">
                    </div>
                <?php endif; ?>
                <hr>
                <h6>Membership Fees:</h6>
                <ul>
                    <li><strong>Active Membership:</strong> ₹ <?php echo ACTIVE_MEMBERSHIP_PRICE; ?>/-</li>
                    <li><strong>Gram Panchayat Level:</strong> ₹ <?php echo GRAM_PANCHAYAT_MEMBERSHIP_PRICE; ?>/-</li>
                    <li><strong>Block Level:</strong> ₹ <?php echo BLOCK_MEMBERSHIP_PRICE; ?>/-</li>
                    <li><strong>Tehsil Level:</strong> ₹ <?php echo TEHSIL_MEMBERSHIP_PRICE; ?>/-</li>
                    <li><strong>District Level:</strong> ₹ <?php echo DISTRICT_MEMBERSHIP_PRICE; ?>/-</li>
                    <li><strong>Mandal Level:</strong> ₹ <?php echo MANDAL_MEMBERSHIP_PRICE; ?>/-</li>
                    <li><strong>State Level:</strong> ₹ <?php echo STATE_MEMBERSHIP_PRICE; ?>/-</li>
                    <li><strong>National Level:</strong> ₹ <?php echo NATIONAL_MEMBERSHIP_PRICE; ?>/-</li>
                </ul>
                <p class="mt-3 small"><strong>Note:</strong> After submitting the form and completing payment, you will receive a registration ID. You can use this ID to download your I-Card and appointment letter from the website after 24 hours. For offline payments, please upload proof of payment to verify your transaction.</p>
            </div>
            
            <?php 
            // Include Razorpay payment button
            $type = 'membership';
            $razorpay_url = 'https://pages.razorpay.com/pl_MOeFYdD5xa3fOS/view';
            include 'includes/razorpay-payment-button.php'; 
            ?>

            <!-- Offline Payment Proof - MODIFIED: Always visible -->
            <div class="row mb-4 align-items-center offline-payment-proof">
                <div class="col-md-2 form-field-title"><label for="payment_proof" class="form-label">Payment Proof <span class="text-danger">*</span></label></div>
                <div class="col-md-4">
                    <div class="image-uploader">
                        <input type="file" id="payment_proof" name="payment_proof" class="d-none" accept="image/jpeg,image/png,image/gif,application/pdf" required>
                        <label for="payment_proof" class="uploader-empty">
                            <i class="fa fa-plus fa-2x"></i>
                            <p>Upload Payment Proof</p>
                        </label>
                        <div class="uploader-preview d-none">
                            <img src="#" alt="Preview">
                            <div class="uploader-controls">
                                <button type="button" class="btn btn-light btn-sm view-btn"><i class="fa fa-eye"></i></button>
                                <button type="button" class="btn btn-danger btn-sm delete-btn"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-submit-grad btn-lg">Submit Application</button>
            </div>
        </form>
    </div>
</main>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Registration Successful!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <p>Thank you for registering. Your documents are being verified.</p>
                <p class="fw-bold"><strong>Please Note Your Registration ID:</strong></p>
                <h3 id="registrationId" class="text-success fw-bold"></h3>
                <p class="text-danger small">This ID is required to download your I-Card.</p>
                <p id="offlineMessage" class="text-info small">For offline payments, your registration will be confirmed after payment verification.</p>
                <!-- COMMENTED: Receipt section - Uncomment when enabling online payments
                <div id="receiptSection" class="mt-4 d-none">
                    <div class="alert alert-success">
                        <i class="fas fa-receipt me-2"></i>
                        <strong>Your registration receipt is ready!</strong>
                    </div>
                    <button type="button" id="downloadReceiptBtn" class="btn btn-success me-2">
                        <i class="fas fa-download me-1"></i>Download Receipt
                    </button>
                    <button type="button" id="viewReceiptBtn" class="btn btn-outline-success">
                        <i class="fas fa-eye me-1"></i>View Receipt
                    </button>
                </div>
                -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-0">
                <img src="#" id="fullImagePreview" class="img-fluid w-100" alt="Image Preview">
            </div>
        </div>
    </div>
</div>

<!-- Spinner Loader -->
<div class="loader-overlay d-none">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<!-- COMMENTED: Razorpay script - Uncomment when enabling online payments
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('user-apply-form');
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    const paymentMethodSelect = document.getElementById('payment_method');
    const membershipSelect = document.getElementById('membership_charge');
    const designationSelect = document.getElementById('designation');
    const offlinePaymentProof = document.querySelector('.offline-payment-proof');
    const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district');

    // Initialize State and District Dropdowns using cities.js
    if (typeof print_state === 'function' && stateSelect && districtSelect) {
        // Populate states
        print_state('state');
        
        // Add change event listener for state
        stateSelect.addEventListener('change', function() {
            const selectedState = this.value;
            const stateIndex = state_arr.indexOf(selectedState);
            
            if (stateIndex !== -1) {
                print_city('district', stateIndex + 1);
            } else {
                districtSelect.innerHTML = '<option value="">Select District</option>';
            }
        });
    }

    // Real-time validation for mobile, Aadhaar, and pincode
    document.getElementById('mobile').addEventListener('input', function() {
        if (!/^[0-9]{10}$/.test(this.value)) {
            this.setCustomValidity('Mobile number must be 10 digits');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('aadhar').addEventListener('input', function() {
        if (!/^[0-9]{12}$/.test(this.value)) {
            this.setCustomValidity('Aadhaar number must be 12 digits');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('pincode').addEventListener('input', function() {
        if (!/^[0-9]{6}$/.test(this.value)) {
            this.setCustomValidity('Pincode must be 6 digits');
        } else {
            this.setCustomValidity('');
        }
    });

    // Additional client-side validation for password
    document.getElementById('password').addEventListener('input', function() {
        if (this.value.length < 6) {
            this.setCustomValidity('Password must be at least 6 characters');
        } else {
            this.setCustomValidity('');
        }
    });

    // Email validation
    document.getElementById('email').addEventListener('input', function() {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(this.value)) {
            this.setCustomValidity('Please enter a valid email address');
        } else {
            this.setCustomValidity('');
        }
    });

    // Image Uploader Logic
    document.querySelectorAll('.image-uploader').forEach(uploader => {
        const input = uploader.querySelector('input[type="file"]');
        const emptyState = uploader.querySelector('.uploader-empty');
        const previewState = uploader.querySelector('.uploader-preview');
        const previewImg = previewState.querySelector('img');
        const viewBtn = previewState.querySelector('.view-btn');
        const deleteBtn = previewState.querySelector('.delete-btn');

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    emptyState.classList.add('d-none');
                    previewState.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        viewBtn.addEventListener('click', () => {
            document.getElementById('fullImagePreview').src = previewImg.src;
            imagePreviewModal.show();
        });

        deleteBtn.addEventListener('click', () => {
            input.value = '';
            previewImg.src = '';
            previewState.classList.add('d-none');
            emptyState.classList.remove('d-none');
        });
    });

    /* COMMENTED: Payment method toggle - Not needed for offline only
    // Toggle payment proof field based on payment method
    function togglePaymentProof() {
        const paymentMethod = paymentMethodSelect.value;
        if (paymentMethod === 'offline') {
            offlinePaymentProof.classList.remove('d-none');
            document.getElementById('payment_proof').required = true;
        } else {
            offlinePaymentProof.classList.add('d-none');
            document.getElementById('payment_proof').required = false;
            const paymentProofInput = document.getElementById('payment_proof');
            const emptyState = paymentProofInput.closest('.image-uploader').querySelector('.uploader-empty');
            const previewState = paymentProofInput.closest('.image-uploader').querySelector('.uploader-preview');
            paymentProofInput.value = '';
            previewState.classList.add('d-none');
            emptyState.classList.remove('d-none');
        }
    }
    */

    // Update designations based on membership type
    function updateDesignations() {
        const membershipType = membershipSelect.value;
        if (!membershipType) {
            designationSelect.innerHTML = '<option value="" selected disabled>First select membership type</option>';
            return;
        }

        // FIXED: Explicitly use users-apply-form.php in URL to avoid routing issues
        fetch('users-apply-form.php?ajax=get_designations&membership_type=' + encodeURIComponent(membershipType))
            .then(response => response.json())
            .then(data => {
                designationSelect.innerHTML = '<option value="" selected disabled>Select Designation</option>';
                if (data.success && data.designations && data.designations.length > 0) {
                    data.designations.forEach(designation => {
                        const option = document.createElement('option');
                        option.value = designation.designation;
                        // Handle potential null hindi values
                        const labelHindi = designation.designation_hindi || designation.designation;
                        option.textContent = labelHindi + ' (' + designation.designation + ')';
                        designationSelect.appendChild(option);
                    });
                } else {
                    designationSelect.innerHTML = '<option value="" selected disabled>No designations available</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching designations:', error);
                designationSelect.innerHTML = '<option value="" selected disabled>Error loading designations</option>';
            });
    }

    // MODIFIED: Only update designations on membership change
    membershipSelect.addEventListener('change', () => {
        updateDesignations();
    });

    // Initialize designations on page load
    updateDesignations();

    // Form Submission Logic - MODIFIED: Only offline payment
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate email is filled
        if (!document.getElementById('email').value.trim()) {
            Swal.fire('Error', 'Email is required.', 'error');
            return;
        }

        // Validate state and district
        if (!stateSelect.value) {
            Swal.fire('Error', 'Please select a state.', 'error');
            return;
        }

        if (!districtSelect.value) {
            Swal.fire('Error', 'Please select a district.', 'error');
            return;
        }

        const membershipType = document.getElementById('membership_charge').value;
        const paymentMethod = document.getElementById('payment_method').value;
        const amountMap = {
            'active': <?php echo ACTIVE_MEMBERSHIP_PRICE; ?>,
            'gram_panchayat': <?php echo GRAM_PANCHAYAT_MEMBERSHIP_PRICE; ?>,
            'block': <?php echo BLOCK_MEMBERSHIP_PRICE; ?>,
            'tehsil': <?php echo TEHSIL_MEMBERSHIP_PRICE; ?>,
            'district': <?php echo DISTRICT_MEMBERSHIP_PRICE; ?>,
            'mandal': <?php echo MANDAL_MEMBERSHIP_PRICE; ?>,
            'state': <?php echo STATE_MEMBERSHIP_PRICE; ?>,
            'national': <?php echo NATIONAL_MEMBERSHIP_PRICE; ?>
        };
        const amount = amountMap[membershipType] || 0;

        // Validate required files
        const requiredFiles = ['profile_pic_input', 'aadhaar_input', 'aadhar_back_input'];
        for (let inputId of requiredFiles) {
            if (!document.getElementById(inputId).files.length) {
                Swal.fire('Error', 'Please upload all required documents.', 'error');
                return;
            }
        }

        // Validate designation
        if (!document.getElementById('designation').value) {
            Swal.fire('Error', 'Please select a designation.', 'error');
            return;
        }

        // Validate payment proof for offline payments
        if (!document.getElementById('payment_proof').files.length) {
            Swal.fire('Error', 'Please upload payment proof for offline payment.', 'error');
            return;
        }

        // MODIFIED: Only offline payment submission
        submitFormData('pending');

        /* COMMENTED: Online payment handling - Uncomment when enabling online payments
        // Trigger Razorpay for online payments
        if (paymentMethod === 'online') {
            createOrderAndPay(amount);
        } else {
            // Submit via AJAX for offline payments
            submitFormData('pending');
        }
        */
    });

    /* COMMENTED: Razorpay functions - Uncomment when enabling online payments
    
    // Create Razorpay Order and initiate payment
    function createOrderAndPay(amount) {
        const loader = document.querySelector('.loader-overlay');
        loader.classList.remove('d-none');

        // Create order via API
        fetch('<?php echo SITE_URL; ?>/api/create-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                amount: amount,
                currency: 'INR',
                receipt: 'membership_' + Date.now(),
                notes: {
                    type: 'membership',
                    member_name: document.getElementById('name').value,
                    member_mobile: document.getElementById('mobile').value,
                    membership_type: document.getElementById('membership_charge').value
                }
            })
        })
        .then(response => response.json())
        .then(data => {
            loader.classList.add('d-none');
            
            if (data.success) {
                // Store order ID
                document.getElementById('order_id').value = data.order.id;
                
                // Trigger Razorpay with order
                triggerRazorpayWithOrder(data.order, data.key_id);
            } else {
                Swal.fire('Error', data.message || 'Failed to create order', 'error');
            }
        })
        .catch(error => {
            loader.classList.add('d-none');
            console.error('Order creation error:', error);
            Swal.fire('Error', 'Failed to create order. Please try again.', 'error');
        });
    }

    function triggerRazorpayWithOrder(order, keyId) {
        const options = {
            "key": keyId,
            "amount": order.amount,
            "currency": order.currency,
            "name": "<?php echo SITE_NAME; ?>",
            "description": "Membership Fee",
            "image": "<?php echo SITE_URL; ?>/img/logo.png",
            "order_id": order.id,
            "handler": function(response) {
                // Verify payment first
                verifyPaymentAndSubmit(response);
            },
            "prefill": {
                "name": document.getElementById('name').value,
                "email": document.getElementById('email').value,
                "contact": document.getElementById('mobile').value
            },
            "theme": { "color": "#291872" },
            "modal": {
                "ondismiss": function() {
                    console.log('Payment modal closed');
                }
            }
        };
        
        const rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function(response) {
            console.error('Payment failed:', response.error);
            Swal.fire('Payment Failed', response.error.description, 'error');
        });
        rzp1.open();
    }

    function verifyPaymentAndSubmit(razorpayResponse) {
        const loader = document.querySelector('.loader-overlay');
        loader.classList.remove('d-none');

        // Verify payment signature
        fetch('<?php echo SITE_URL; ?>/api/verify-payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                razorpay_order_id: razorpayResponse.razorpay_order_id,
                razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                razorpay_signature: razorpayResponse.razorpay_signature
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Payment verified, now submit registration data
                submitFormData('approved', razorpayResponse.razorpay_payment_id);
            } else {
                loader.classList.add('d-none');
                Swal.fire('Payment Verification Failed', data.message, 'error');
            }
        })
        .catch(error => {
            loader.classList.add('d-none');
            console.error('Payment verification error:', error);
            Swal.fire('Error', 'Payment verification failed. Please contact support.', 'error');
        });
    }
    
    */ // END OF COMMENTED RAZORPAY FUNCTIONS

    function submitFormData(status, paymentId = null) {
        const loader = document.querySelector('.loader-overlay');
        if (!loader.classList.contains('d-none')) {
            // Loader already shown from verification
        } else {
            loader.classList.remove('d-none');
        }

        const formData = new FormData(form);
        if (paymentId) {
            formData.append('payment_id', paymentId);
        }
        formData.append('status', status);

        fetch('users-apply-form.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            loader.classList.add('d-none');
            if (data.success) {
                document.getElementById('registrationId').textContent = data.data.registration_id;
                
                // MODIFIED: Always show offline message
                document.getElementById('offlineMessage').classList.remove('d-none');
                
                /* COMMENTED: Receipt section handling - Uncomment when enabling online payments
                if (status === 'pending') {
                    document.getElementById('offlineMessage').classList.remove('d-none');
                } else {
                    document.getElementById('offlineMessage').classList.add('d-none');
                }
                
                // Show receipt section for approved registrations
                if (status === 'approved' && data.data.receipt_url) {
                    const receiptSection = document.getElementById('receiptSection');
                    const downloadBtn = document.getElementById('downloadReceiptBtn');
                    const viewBtn = document.getElementById('viewReceiptBtn');
                    
                    receiptSection.classList.remove('d-none');
                    
                    // Set up receipt buttons
                    downloadBtn.onclick = function() {
                        window.open(data.data.receipt_url + '&download=1', '_blank');
                    };
                    
                    viewBtn.onclick = function() {
                        window.open(data.data.receipt_url, '_blank');
                    };
                    
                    // Auto-open receipt after 2 seconds
                    setTimeout(() => {
                        window.open(data.data.receipt_url, '_blank');
                    }, 2000);
                }
                */
                
                successModal.show();
                form.reset();
                document.querySelectorAll('.image-uploader .uploader-preview').forEach(preview => {
                    preview.classList.add('d-none');
                    preview.querySelector('img').src = '';
                });
                document.querySelectorAll('.image-uploader .uploader-empty').forEach(empty => {
                    empty.classList.remove('d-none');
                });
                updateDesignations();
                
                // Reset state and district dropdowns
                if (stateSelect) {
                    print_state('state');
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                }
            } else {
                Swal.fire('Error', data.message || 'Failed to submit form. Please try again.', 'error');
            }
        })
        .catch(error => {
            loader.classList.add('d-none');
            Swal.fire('Error', 'An error occurred while submitting the form. Please try again.', 'error');
            console.error('Form submission error:', error);
        });
    }
});
</script>

<style>
.form-container {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    max-width: 1000px;
    margin: auto;
}

.section-heading {
    position: relative;
    margin-bottom: 2rem;
    color: #291872;
    font-weight: 700;
}

.section-heading span {
    background: #fff;
    padding: 0 15px;
}

.section-heading::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    border-top: 2px solid #291872;
    z-index: -1;
}

.form-field-title {
    font-weight: 600;
    color: #333;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 10px;
}

.form-control:focus, .form-select:focus {
    border-color: #291872;
    box-shadow: 0 0 5px rgba(41, 24, 114, 0.3);
}

.image-uploader {
    position: relative;
    border: 2px dashed #ced4da;
    border-radius: 10px;
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.uploader-empty {
    cursor: pointer;
    width: 100%;
}

.uploader-empty i {
    color: #291872;
}

.uploader-preview img {
    max-width: 100%;
    max-height: 110px;
    object-fit: cover;
    border-radius: 8px;
}

.uploader-controls {
    position: absolute;
    bottom: 5px;
    right: 5px;
    display: flex;
    gap: 5px;
}

.uploader-controls button {
    padding: 5px;
}

.payment-info-section {
    background: #e9ecef;
    border-radius: 10px;
}

.btn-submit-grad {
    background: linear-gradient(90deg, #291872, #764ba2);
    color: #fff;
    padding: 12px 30px;
    border-radius: 25px;
    border: none;
    transition: all 0.3s ease;
}

.btn-submit-grad:hover {
    background: linear-gradient(90deg, #764ba2, #291872);
}

.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

@media (max-width: 768px) {
    .form-field-title {
        margin-bottom: 10px;
    }

    .row.mb-4 {
        flex-direction: column;
    }

    .col-md-2, .col-md-4, .col-md-10 {
        width: 100%;
    }

    .image-uploader {
        padding: 15px;
        min-height: 120px;
    }

    .uploader-preview img {
        max-height: 80px;
    }

    .btn-submit-grad {
        width: 100%;
    }
}
</style>

<?php include 'footer.php'; ?>