<?php
require_once 'config.php';

echo "<h2>Database Check</h2>";

$db = Database::getInstance()->getConnection();

// Check if admin user exists
$stmt = $db->prepare("SELECT * FROM users WHERE email = 'admin@sanatandharmajagruti.com'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "<p><strong>Admin user found:</strong></p>";
    echo "<ul>";
    echo "<li>Name: " . htmlspecialchars($admin['name']) . "</li>";
    echo "<li>Email: " . htmlspecialchars($admin['email']) . "</li>";
    echo "<li>User Type: " . htmlspecialchars($admin['user_type']) . "</li>";
    echo "<li>Status: " . htmlspecialchars($admin['status']) . "</li>";
    echo "<li>Password Hash: " . (empty($admin['password']) ? 'MISSING!' : 'Present') . "</li>";
    echo "</ul>";
    
    // Test password verification
    if (!empty($admin['password'])) {
        $testPassword = 'admin123';
        $isValid = password_verify($testPassword, $admin['password']);
        echo "<p><strong>Password Test:</strong> " . ($isValid ? 'VALID' : 'INVALID') . "</p>";
    }
} else {
    echo "<p><strong>Admin user NOT found!</strong></p>";
    
    // Create admin user
    echo "<p>Creating admin user...</p>";
    try {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, mobile, gender, dob, sdw_type, sdw_name, aadhar, state, district, address, pincode, membership_type, user_type, status, registration_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            'Admin User',
            'admin@sanatandharmajagruti.com',
            $hashedPassword,
            '9999999999',
            'Male',
            '1990-01-01',
            'S/O',
            'Admin Father',
            '123456789012',
            'Delhi',
            'New Delhi',
            'Admin Address',
            '110001',
            'senior_membership',
            'admin',
            'approved',
            'ADMIN001'
        ]);
        
        if ($result) {
            echo "<p><strong>Admin user created successfully!</strong></p>";
        } else {
            echo "<p><strong>Failed to create admin user!</strong></p>";
        }
    } catch (Exception $e) {
        echo "<p><strong>Error creating admin user:</strong> " . $e->getMessage() . "</p>";
    }
}

// Check table structure
echo "<h3>Users Table Structure:</h3>";
$stmt = $db->prepare("DESCRIBE users");
$stmt->execute();
$columns = $stmt->fetchAll();

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
foreach ($columns as $column) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>Login Credentials:</h3>";
echo "<p><strong>Email:</strong> admin@sanatandharmajagruti.com</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "<p><a href='user-login.php'>Go to Login Page</a></p>";
?>
