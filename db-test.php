<?php
// Quick DB Test - Delete this file after testing!
$host = 'localhost';
$dbname = 'u526627089_jwmngo';
$user = 'u526627089_jwmngo';
$pass = 'G7m$kP9v!nT2qR8x';

echo "<h2>Database Connection Test</h2>";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    echo "<p style='color: green;'>✅ Connection successful! Database is accessible.</p>";
    // Optional: List tables to confirm schema
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Host: $host | DB: $dbname | User: $user</p>";
    // Common errors:
    // - "Access denied" = Wrong username/password
    // - "Unknown database" = DB doesn't exist
    // - "Can't connect to MySQL" = Host/server issue
}
?>