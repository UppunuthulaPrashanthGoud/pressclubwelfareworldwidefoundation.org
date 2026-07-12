<?php 
require_once 'config/config.php';

// Handle AJAX form submission first, before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_complaint') {
    // Set JSON header
    header('Content-Type: application/json');
    
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRF($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page and try again.']);
            exit;
        }
        
        // Get and validate input data
        $name = trim($_POST['name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Check required fields
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            exit;
        }
        
        if (empty($mobile)) {
            echo json_encode(['success' => false, 'message' => 'Mobile number is required']);
            exit;
        }
        
        if (empty($subject)) {
            echo json_encode(['success' => false, 'message' => 'Subject is required']);
            exit;
        }
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Message is required']);
            exit;
        }
        
        // Validate mobile number
        if (!validateMobileNumber($mobile)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9']);
            exit;
        }
        
        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
            exit;
        }
        
        // Sanitize inputs
        $name = sanitizeInput($name);
        $mobile = sanitizeInput($mobile);
        $email = sanitizeInput($email);
        $subject = sanitizeInput($subject);
        $message = sanitizeInput($message);
        
        // Get database connection
        $pdo = getDbConnection();
        
        // Insert complaint into database
        $stmt = $pdo->prepare("INSERT INTO complaints (name, mobile, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $result = $stmt->execute([$name, $mobile, $email, $subject, $message]);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Your complaint has been submitted successfully! We will contact you soon.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit complaint. Please try again.']);
        }
        
    } catch (Exception $e) {
        // Log the detailed error for debugging
        logError("Complaint submission error: " . $e->getMessage() . " | Line: " . $e->getLine() . " | File: " . $e->getFile());
        echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again later.']);
    }
    
    exit; // Important: stop execution after handling AJAX request
}

// Continue with normal page rendering only if it's not an AJAX request
include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
    <h3 class="section-heading text-center"><span>Submit Your Problem</span></h3>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4">
                <form id="complaint-form" method="post" action="">
                    <input type="hidden" name="action" value="submit_complaint">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" pattern="[6-9][0-9]{9}" required maxlength="10">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" required maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Describe Your Problem <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="5" required maxlength="1000"></textarea>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-submit-grad btn-lg" id="submit-btn">
                            <i class="fas fa-paper-plane me-2"></i>Submit Problem
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('complaint-form');
    const submitBtn = document.getElementById('submit-btn');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable submit button and show loading
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            // Get form data
            const formData = new FormData(form);
            
            // Debug: Log form data
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
            
            // Submit form
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 4000,
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    });
                    form.reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Something went wrong. Please try again.'
                    });
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Something went wrong. Please check the console for details and try again.'
                });
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
</script>

<?php include 'footer.php'; ?>