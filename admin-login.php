<?php
// Define config path and check existence
$configPath = __DIR__ . '/config/config.php';
if (!file_exists($configPath)) {
    die('Configuration file not found at: ' . htmlspecialchars($configPath));
}
require_once $configPath;
// Database connection using centralized function
$db = getDbConnection();
// Redirect if already logged in as admin
if (isLoggedIn() && isAdmin()) {
    header('Location: admin/index.php');
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($login_input) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Check if input is email or phone
            $field = filter_var($login_input, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
            $stmt = $db->prepare("SELECT * FROM users WHERE $field = ? AND user_type = 'admin' AND status = 'approved'");
            $stmt->execute([sanitizeInput($login_input)]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['logged_in'] = true;
                $_SESSION['last_activity'] = time();
                header('Location: admin/index.php');
                exit();
            } else {
                $error = 'Invalid admin credentials or account not approved.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again later.';
            logError('Admin login error: ' . $e->getMessage());
        }
    }
}
include 'header.php';
include 'navbar.php';
?>
<div class="container-fluid navbar-margin-pusher">
    <div class="row justify-content-center py-5">
        <div class="col-md-6 col-lg-4">
            <div class="login-card-wrapper">
                <div class="card login-card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="img/logo.png" alt="Logo" style="height: 60px;">
                            <h3 class="card-title mt-3 mb-2">Admin Login</h3>
                            <p class="text-muted">Access Admin Dashboard</p>
                        </div>
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
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
                                       placeholder="Enter password">
                            </div>
                            <button type="submit" class="btn btn-submit-grad w-100 btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login as Admin
                            </button>
                        </form>
                        <div class="text-center mt-4">
                            <p class="mb-0">
                                <a href="user-login.php" class="text-decoration-none me-3">
                                    <i class="fas fa-user me-1"></i>User Login
                                </a>
                                <a href="coordinator-login.php" class="text-decoration-none">
                                    <i class="fas fa-user-tie me-1"></i>Coordinator Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>