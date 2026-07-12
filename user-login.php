<?php
// Define config path and check existence
$configPath = __DIR__ . '/config/config.php';
if (!file_exists($configPath)) {
    die('Configuration file not found at: ' . htmlspecialchars($configPath));
}
require_once $configPath;

// Database connection using centralized function
$db = getDbConnection();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

// Handle AJAX login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'user_login') {
    $login_input = sanitizeInput(trim($_POST['login_input'] ?? ''));
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRF($csrf_token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    
    if (empty($login_input) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit;
    }
    
    try {
        // Check if input is email or phone
        $field = filter_var($login_input, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        $stmt = $db->prepare("SELECT * FROM users WHERE $field = ? AND user_type = 'member' AND status = 'approved'");
        $stmt->execute([$login_input]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['status'] = $user['status'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            // Handle redirect after login
            $redirect_url = $_SESSION['redirect_after_login'] ?? (SITE_URL . '/admin/index.php');
            unset($_SESSION['redirect_after_login']);
            
            echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => $redirect_url]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials or account not approved.']);
            exit;
        }
    } catch (PDOException $e) {
        logError('Member login error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Login failed. Please try again later.']);
        exit;
    }
}

// Generate CSRF token for the form
$csrf_token = generateCSRF();

include 'header.php';
include 'navbar.php';
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="login-card-wrapper">
                <div class="card login-card shadow-lg p-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <img src="<?php echo SITE_URL; ?>/img/logo.png" alt="Logo" style="height: 60px;">
                            <h3 class="card-title mt-3 mb-2">Member Login</h3>
                            <p class="text-muted">Access Your Member Dashboard</p>
                        </div>
                        
                        <form id="member-login-form" method="post">
                            <input type="hidden" name="action" value="user_login">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            
                            <div class="mb-3">
                                <label for="login_input" class="form-label fw-bold">
                                    <i class="fas fa-user me-2"></i>Email or Phone
                                </label>
                                <input type="text" class="form-control form-control-lg" id="login_input" name="login_input" required
                                       placeholder="Enter email or phone number">
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required
                                       placeholder="Enter your password">
                            </div>
                            
                            <div id="error-message" class="alert alert-danger d-none" role="alert"></div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-submit-grad btn-lg">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <p class="mb-2">Don't have an account? <a href="user-registration.php" class="text-decoration-none">Register here</a></p>
                                <p class="mb-0">
                                    <a href="coordinator-login.php" class="text-decoration-none me-3">
                                        <i class="fas fa-user-tie me-1"></i>Coordinator Login
                                    </a>
                                    <a href="admin-login.php" class="text-decoration-none">
                                        <i class="fas fa-user-shield me-1"></i>Admin Login
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('member-login-form');
    const submitButton = form.querySelector('button[type="submit"]');
    const errorMessageDiv = document.getElementById('error-message');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const spinner = submitButton.querySelector('.spinner-border');
        const buttonText = submitButton.querySelector('i');
        
        spinner.classList.remove('d-none');
        buttonText.classList.add('d-none');
        submitButton.disabled = true;
        errorMessageDiv.classList.add('d-none');

        const formData = new FormData(form);
        
        fetch('<?php echo SITE_URL; ?>/user-login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            spinner.classList.add('d-none');
            buttonText.classList.remove('d-none');
            submitButton.disabled = false;
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirect || '<?php echo SITE_URL; ?>/admin/index.php';
                });
            } else {
                errorMessageDiv.textContent = data.message;
                errorMessageDiv.classList.remove('d-none');
            }
        })
        .catch(error => {
            spinner.classList.add('d-none');
            buttonText.classList.remove('d-none');
            submitButton.disabled = false;
            errorMessageDiv.textContent = 'Something went wrong. Please try again later.';
            errorMessageDiv.classList.remove('d-none');
            console.error('Fetch error:', error);
        });
    });
});
</script>

<?php include 'footer.php'; ?>