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
    header('Location: admin/index.php');
    exit;
}

// Handle AJAX login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'user_login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'user' AND status = 'approved'");
        $stmt->execute([sanitizeInput($email)]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();
            
            echo json_encode(['success' => true, 'message' => 'Login successful']);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid user credentials or account not approved.']);
            exit;
        }
    } catch (PDOException $e) {
        logError('User login error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
        exit;
    }
}

// Generate CSRF token for the form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
                            <img src="img/logo.png" alt="Logo" style="height: 60px;">
                            <h3 class="card-title mt-3 mb-2">User Login</h3>
                            <p class="text-muted">Access Your Dashboard</p>
                        </div>
                        
                        <form id="user-login-form" method="post">
                            <input type="hidden" name="action" value="user_login">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="mb-3">
                                <label for="user-email" class="form-label fw-bold">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control form-control-lg" id="user-email" name="email" required
                                       placeholder="Enter your email">
                            </div>
                            
                            <div class="mb-4">
                                <label for="user-password" class="form-label fw-bold">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control form-control-lg" id="user-password" name="password" required
                                       placeholder="Enter your password">
                            </div>
                            
                            <div id="user-error-message" class="alert alert-danger d-none" role="alert"></div>

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
    const form = document.getElementById('user-login-form');
    const submitButton = form.querySelector('button[type="submit"]');
    const errorMessageDiv = document.getElementById('user-error-message');

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
                    window.location.href = '<?php echo SITE_URL; ?>/admin/index.php';
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