<?php
require_once 'config/config.php';

// --- Admin User Details ---
$email = "admin@example.com";
$password = "admin123"; // Change this to a strong password
$fullName = "Main Administrator";

// Hash the password securely
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        die("Admin user with this email already exists!");
    }

    // Insert the new admin user
    $sql = "INSERT INTO admins (full_name, email, password_hash) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fullName, $email, $password_hash]);

    echo "Admin user created successfully!<br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Password: " . htmlspecialchars($password) . "<br>";
    echo "<strong>IMPORTANT: Please delete this file (create_admin.php) immediately for security.</strong>";

} catch (PDOException $e) {
    die("ERROR: Could not execute $sql. " . $e->getMessage());
}

// Close connection
unset($pdo);
?>