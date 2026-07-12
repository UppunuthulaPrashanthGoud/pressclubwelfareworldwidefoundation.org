<?php
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_user') {
    header('Content-Type: application/json');

    if (!verifyCSRF($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }

    // --- Validate Required Fields ---
    $required_fields = ['name', 'contact_no', 'category', 'membership_type', 'award', 'email', 'password', 'state', 'city', 'pincode'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Please fill the required field: " . ucfirst(str_replace('_', ' ', $field))]);
            exit;
        }
    }

    // --- Sanitize & Assign Inputs ---
    $name = sanitizeInput($_POST['name']);
    $mobile = sanitizeInput($_POST['contact_no']);
    $category = sanitizeInput($_POST['category']);
    $membership_type = sanitizeInput($_POST['membership_type']);
    $award = sanitizeInput($_POST['award']);
    $venue = sanitizeInput($_POST['venue']);
    $profession = sanitizeInput($_POST['profession']);
    $about = sanitizeInput($_POST['about']);
    $talent = sanitizeInput($_POST['talent']);
    $email = sanitizeInput($_POST['email']);
    $address = sanitizeInput($_POST['address']);
    $state = sanitizeInput($_POST['state']);
    $city = sanitizeInput($_POST['city']);
    $pincode = sanitizeInput($_POST['pincode']);
    $gender = sanitizeInput($_POST['gender']);
    $qualification = sanitizeInput($_POST['qualification']);
    $password = password_hash(sanitizeInput($_POST['password']), PASSWORD_DEFAULT);
    
    // Checkboxes (store as 1 or 0)
    $is_online = isset($_POST['online']) ? 1 : 0;
    $is_offline = isset($_POST['offline']) ? 1 : 0;
    $is_ecert = isset($_POST['e_cert']) ? 1 : 0;

    // --- System Fields ---
    $user_type = 'member'; 
    $status = 'pending'; 
    $created_at = date('Y-m-d H:i:s');

    // --- Validation Checks ---
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit;
    }
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        echo json_encode(['success' => false, 'message' => 'Contact number must be 10 digits']);
        exit;
    }

    // Check for duplicates in both nominations and users tables
    try {
        // Check nominations table
        $stmt = $db->prepare("SELECT COUNT(*) FROM nominations WHERE mobile = ? OR email = ?");
        $stmt->execute([$mobile, $email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Mobile number or Email already registered in nominations']);
            exit;
        }
        
        // Check users table
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE mobile = ? OR email = ?");
        $stmt->execute([$mobile, $email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Mobile number or Email already registered as member']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database check failed.']);
        exit;
    }

    // --- File Upload Logic ---
    $uploaded_files = [];
    $file_fields = ['profile_pic', 'document_one', 'document_two', 'document_three', 'document_four'];
    
    foreach ($file_fields as $field) {
        if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadFile($_FILES[$field], 'uploads/documents'); 
            if ($uploadResult['success']) {
                $uploaded_files[$field] = $uploadResult['filename'];
            } else {
                echo json_encode(['success' => false, 'message' => "Error uploading $field: " . $uploadResult['message']]);
                exit;
            }
        } else {
            $uploaded_files[$field] = null; 
        }
    }

    // --- Generate Registration ID (GHDAF + 5 digits) ---
    do {
        $registration_id = 'GHDAF' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $stmt = $db->prepare("SELECT COUNT(*) FROM nominations WHERE registration_id = ? UNION SELECT COUNT(*) FROM users WHERE registration_id = ?");
        $stmt->execute([$registration_id, $registration_id]);
    } while ($stmt->fetchColumn() > 0);

    // --- Insert into nominations table ---
    try {
        $sql = "INSERT INTO nominations (
            registration_id, name, mobile, email, password,
            category, membership_type, award, venue, 
            profession, about, talent, 
            gender, qualification,
            address, state, district, pincode, 
            is_online, is_offline, is_ecert,
            profile_image, document_one, document_two, document_three, document_four,
            user_type, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $registration_id, $name, $mobile, $email, $password,
            $category, $membership_type, $award, $venue,
            $profession, $about, $talent,
            $gender, $qualification,
            $address, $state, $city, $pincode,
            $is_online, $is_offline, $is_ecert,
            $uploaded_files['profile_pic'], $uploaded_files['document_one'], 
            $uploaded_files['document_two'], $uploaded_files['document_three'], 
            $uploaded_files['document_four'],
            $user_type, $status, $created_at
        ]);

        $nomination_id = $db->lastInsertId();

        // --- ALSO Register in users table as member ---
        $user_sql = "INSERT INTO users (
            registration_id, name, mobile, email, password,
            profession, gender, qualification,
            address, state, district, pincode,
            membership_type, user_type, status, created_at,
            profile_image
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $user_stmt = $db->prepare($user_sql);
        $user_stmt->execute([
            $registration_id, $name, $mobile, $email, $password,
            $profession, $gender, $qualification,
            $address, $state, $city, $pincode,
            $membership_type, $user_type, $status, $created_at,
            $uploaded_files['profile_pic']
        ]);

        $user_id = $db->lastInsertId();

        echo json_encode([
            'success' => true,
            'data' => [
                'registration_id' => $registration_id,
                'nomination_id' => $nomination_id,
                'user_id' => $user_id
            ]
        ]);

    } catch (PDOException $e) {
        logError('Nomination registration error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Registration failed. Database error: ' . $e->getMessage()]);
    }
    exit;
}

include 'header.php';
include 'navbar.php';
?>

<!-- Include cities.js for state and district dropdowns -->
<script src="<?php echo SITE_URL; ?>/js/cities.js"></script>

<style>
    /* Custom Styles */
    .mt-20 { margin-top: 20px; }
    .mb-20 { margin-bottom: 20px; }
    .mt-40 { margin-top: 40px; }
    .mb-40 { margin-bottom: 40px; }
    .form-container-box {
        border: 2px solid #291872; 
        border-radius: 10px; 
        background-color: #edeef0;
        padding: 30px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
    .btn-primary {
        background-color: #291872;
        border-color: #291872;
        padding: 10px 30px;
        font-size: 18px;
    }
    .btn-primary:hover {
        background-color: #4a2cb3;
        border-color: #4a2cb3;
    }
</style>

<main class="container-fluid">
    <div class="text-center mt-4">
        <h2>GYANDAAN HONORARY DOCTORATE AWARDS FOUNDATION</h2>
        <h4 class="text-muted">Award Nomination Registration Form</h4>
    </div>
    
    <div class="container mb-40 mt-40 col-md-8 mx-auto form-container-box">
        <form id="productForm" class="form-horizontal mt-20 mb-20" enctype="multipart/form-data">
            <input type="hidden" name="action" value="register_user">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="Enter Full Name">
                    </div>
                    <div class="form-group mb-3">
                        <label for="contact_no">Contact No <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="contact_no" name="contact_no" required placeholder="10 Digit Mobile Number">
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6 mb-3">
                            <label for="registration_no">Registration No</label>
                            <input type="text" class="form-control" id="registration_no" value="Auto-Generated" readonly style="background-color: #e9ecef;">
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label for="category">Category <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="category" name="category" required placeholder="e.g. Education, Social Work">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="membership_type">Select Membership <span class="text-danger">*</span></label>
                        <select class="form-control" id="membership_type" name="membership_type" required>
                            <option value="" selected disabled>Select Membership Type</option>
                            <option value="active">Active Membership</option>
                            <option value="gram_panchayat">Gram Panchayat Level</option>
                            <option value="block">Block Level</option>
                            <option value="tehsil">Tehsil Level</option>
                            <option value="district">District Level</option>
                            <option value="mandal">Mandal Level</option>
                            <option value="state">State Level</option>
                            <option value="national">National Level</option>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="online" name="online">
                                <label class="form-check-label" for="online">Online</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="offline" name="offline">
                                <label class="form-check-label" for="offline">Offline</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="e_cert" name="e_cert">
                                <label class="form-check-label" for="e_cert">E Certificate</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="award">Select Award <span class="text-danger">*</span></label>
                        <select name="award" id="award" class="form-control" required>
                            <option value="">Select Award Category</option>
                            <option value="HONORARY DOCTORATE AWARD">HONORARY DOCTORATE AWARD</option>
                            <option value="LIFETIME ACHIEVEMENT AWARD">LIFETIME ACHIEVEMENT AWARD</option>
                            <option value="NATIONAL BEST TEACHER AWARD">NATIONAL BEST TEACHER AWARD</option>
                            <option value="NATIONAL BEST SOCIAL WORKER AWARD">NATIONAL BEST SOCIAL WORKER AWARD</option>
                            <option value="INTERNATIONAL EDUCATIONAL AWARD">INTERNATIONAL EDUCATIONAL AWARD</option>
                            <option value="WOMEN EMPOWERMENT AWARD">WOMEN EMPOWERMENT AWARD</option>
                            <option value="YOUNG ACHIEVER AWARD">YOUNG ACHIEVER AWARD</option>
                            <option value="EXCELLENCE IN RESEARCH AWARD">EXCELLENCE IN RESEARCH AWARD</option>
                            <option value="DISTINGUISHED SERVICE AWARD">DISTINGUISHED SERVICE AWARD</option>
                            <option value="OUTSTANDING LEADERSHIP AWARD">OUTSTANDING LEADERSHIP AWARD</option>
                            <option value="BEST PRINCIPAL AWARD">BEST PRINCIPAL AWARD</option>
                            <option value="ACADEMIC EXCELLENCE AWARD">ACADEMIC EXCELLENCE AWARD</option>
                            <option value="SOCIAL IMPACT AWARD">SOCIAL IMPACT AWARD</option>
                            <option value="GLOBAL PEACE AWARD">GLOBAL PEACE AWARD</option>
                            <option value="CULTURAL ICON AWARD">CULTURAL ICON AWARD</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="venue">Preferred Venue <span class="text-danger">*</span></label>
                        <select name="venue" id="venue" class="form-control" required>
                            <option value="">Select Venue</option>
                            <option value="New Delhi - National Convention Center">New Delhi - National Convention Center</option>
                            <option value="Mumbai - Grand Hall">Mumbai - Grand Hall</option>
                            <option value="Online / Virtual Ceremony">Online / Virtual Ceremony</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="profession">Your Profession</label>
                        <input type="text" class="form-control" id="profession" name="profession">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="about">About You / Brief Bio</label>
                        <textarea class="form-control" id="about" name="about" rows="2"></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="talent">Key Achievements / Talent Details</label>
                        <textarea class="form-control" id="talent" name="talent" rows="2"></textarea>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="address">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" required rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <!-- Converted to Dropdowns for cities.js -->
                        <div class="form-group col-md-6 mb-3">
                            <label for="state">State <span class="text-danger">*</span></label>
                            <select class="form-control" id="state" name="state" required>
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label for="city">City / District <span class="text-danger">*</span></label>
                            <select class="form-control" id="city" name="city" required>
                                <option value="">Select District</option>
                            </select>
                        </div>
                        
                        <div class="form-group col-md-6 mb-3">
                            <label for="pincode">Pincode <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="pincode" name="pincode" required>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label for="gender">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-control" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="qualification">Education Qualification</label>
                        <input type="text" class="form-control" id="qualification" name="qualification">
                    </div>

                    <div class="row">
                        <!-- Profile Picture field -->
                        <div class="form-group col-md-6 mb-3">
                            <label for="profile_pic">Profile Picture</label>
                            <input type="file" class="form-control-file form-control" id="profile_pic" name="profile_pic">
                        </div>

                        <!-- Document One field -->
                        <div class="form-group col-md-6 mb-3">
                            <label for="document_one">ID Proof</label>
                            <input type="file" class="form-control-file form-control" id="document_one" name="document_one">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Document Two field -->
                        <div class="form-group col-md-6 mb-3">
                            <label for="document_two">Educational Cert.</label>
                            <input type="file" class="form-control-file form-control" id="document_two" name="document_two">
                        </div>

                        <!-- Document Three field -->
                        <div class="form-group col-md-6 mb-3">
                            <label for="document_three">Achievement Proof 1</label>
                            <input type="file" class="form-control-file form-control" id="document_three" name="document_three">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Document Four field -->
                        <div class="form-group col-md-6 mb-3">
                            <label for="document_four">Achievement Proof 2</label>
                            <input type="file" class="form-control-file form-control" id="document_four" name="document_four">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password">Create Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Min 6 characters">
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-lg" id="saveBtn">Submit Nomination</button>
                    <div id="submit_loader" class="mt-2" style="display:none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center p-4">
            <div class="modal-header border-0 justify-content-center">
                <h5 class="modal-title text-success" id="successModalLabel">
                    <i class="fas fa-check-circle fa-3x"></i>
                </h5>
            </div>
            <div class="modal-body">
                <h3>Nomination Submitted Successfully!</h3>
                <p>Thank you for nominating for Gyandaan Honorary Doctorate Awards Foundation.</p>
                <div class="alert alert-primary mt-3">
                    Your Registration ID is: <br>
                    <strong id="display_reg_id" style="font-size: 24px;"></strong>
                </div>
                <p class="text-muted small mt-2">
                    Our team will review your nomination and contact you shortly.
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('productForm');
    const saveBtn = document.getElementById('saveBtn');
    const loader = document.getElementById('submit_loader');
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('city');
    
    // Initialize cities.js logic
    if (typeof print_state === 'function' && stateSelect && citySelect) {
        // Populate states
        print_state('state');
        
        // Add change event listener for state
        stateSelect.addEventListener('change', function() {
            const selectedState = this.value;
            // Assuming state_arr is global from cities.js
            const stateIndex = (typeof state_arr !== 'undefined') ? state_arr.indexOf(selectedState) : -1;
            
            if (stateIndex !== -1) {
                print_city('city', stateIndex + 1);
            } else {
                citySelect.innerHTML = '<option value="">Select District</option>';
            }
        });
    }
    
    // Using Bootstrap 5 Modal
    var successModalEl = document.getElementById('successModal');
    var successModal = null;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        successModal = new bootstrap.Modal(successModalEl);
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Basic client-side validation
        const pwd = document.getElementById('password').value;
        if(pwd.length < 6) {
            Swal.fire('Error', 'Password must be at least 6 characters.', 'error');
            return;
        }

        const formData = new FormData(form);

        // UI Loading State
        saveBtn.style.display = 'none';
        loader.style.display = 'block';

        fetch('nominations-apply.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            saveBtn.style.display = 'inline-block';
            loader.style.display = 'none';

            if (data.success) {
                // Show success modal with ID
                document.getElementById('display_reg_id').textContent = data.data.registration_id;
                
                if(successModal) {
                    successModal.show();
                } else {
                    Swal.fire({
                        title: 'Nomination Submitted!',
                        text: "Your Registration ID is: " + data.data.registration_id,
                        icon: 'success'
                    });
                }

                form.reset();
                // Reset Dropdowns
                if(stateSelect) {
                    print_state('state');
                    citySelect.innerHTML = '<option value="">Select District</option>';
                }

            } else {
                Swal.fire('Error', data.message || 'Nomination submission failed.', 'error');
            }
        })
        .catch(error => {
            saveBtn.style.display = 'inline-block';
            loader.style.display = 'none';
            console.error('Error:', error);
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
        });
    });
});
</script>

<?php include 'footer.php'; ?>