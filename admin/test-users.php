<?php
require_once '../config/config.php';
require_once 'includes/universal-certificate-generator.php';

// Test the user fetching functionality
echo "Testing User Fetching Functionality...\n\n";

try {
    $db = getDbConnection();
    
    // Test database connection
    echo "1. Testing Database Connection:\n";
    $stmt = $db->query("SELECT 1");
    if ($stmt) {
        echo "   ✓ Database connection successful\n\n";
    } else {
        echo "   ✗ Database connection failed\n\n";
        exit;
    }
    
    // Test UniversalCertificateGenerator methods
    echo "2. Testing UniversalCertificateGenerator:\n";
    $generator = new UniversalCertificateGenerator();
    
    // Test database connection method
    $connectionTest = $generator->testDatabaseConnection();
    echo "   Database connection test: " . ($connectionTest ? "✓ Success" : "✗ Failed") . "\n";
    
    // Test getAllUsers method
    $users = $generator->getAllUsers();
    echo "   getAllUsers(): Found " . count($users) . " users\n";
    
    // Test getAllMembers method
    $members = $generator->getAllMembers();
    echo "   getAllMembers(): Found " . count($members) . " members\n";
    
    // Test getTotalUserCount method
    $totalCount = $generator->getTotalUserCount();
    echo "   getTotalUserCount(): " . $totalCount . " total users/members\n\n";
    
    // Direct database queries
    echo "3. Direct Database Queries:\n";
    
    // Check users table
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'approved'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Users table (approved): " . $result['count'] . " records\n";
    
    // Check members table  
    $stmt = $db->query("SELECT COUNT(*) as count FROM members WHERE status = 'approved'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Members table (approved): " . $result['count'] . " records\n";
    
    // Show sample data if available
    if (count($users) > 0) {
        echo "\n4. Sample Users Data:\n";
        foreach (array_slice($users, 0, 3) as $user) {
            echo "   - " . $user['name'] . " (" . $user['mobile'] . ")\n";
        }
    }
    
    if (count($members) > 0) {
        echo "\n5. Sample Members Data:\n";
        foreach (array_slice($members, 0, 3) as $member) {
            echo "   - " . $member['name'] . " (" . $member['mobile'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n\nTest completed. Check the debug messages for more information.\n";
?>
