<?php
// Script to truncate all tables except razorpay_config, users, and membership_designations for Uma Foundation Charitable Trust

// Include the configuration file
require_once __DIR__ . '/config.php';

// List of tables to truncate (excluding razorpay_config, users, and membership_designations)
$tablesToTruncate = [
    'about_content',
    'bank_details',
    'blog_posts',
    'campaigns',
    'camps',
    'certificates',
    'complaints',
    'contact_info',
    'contact_messages',
    'donations',
    'events',
    'footer_settings',
    'gallery',
    'generated_id_cards',
    'news',
    'news_activities',
    'objectives',
    'participations',
    'president_message',
    'projects',
    'recent_activities',
    'settings',
    'site_config',
    'sliders',
    'team_members',
    'testimonials',
    'topbar_settings',
    'youtube_videos'
];

// Function to truncate specified tables
function truncateTables($tables) {
    $db = null;
    try {
        $db = getDbConnection();
        
        // Check if the database supports transactions
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') {
            throw new Exception("This script requires a MySQL database for transaction support.");
        }

        // Begin transaction
        if (!$db->inTransaction()) {
            $db->beginTransaction();
        }

        // Disable foreign key checks to avoid constraint issues
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($tables as $table) {
            // Sanitize table name to prevent SQL injection
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
            $db->exec("TRUNCATE TABLE `$table`");
            echo "Table `$table` truncated successfully.\n";
        }

        // Re-enable foreign key checks
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');

        // Commit transaction
        if ($db->inTransaction()) {
            $db->commit();
        }
        echo "All specified tables have been truncated successfully.\n";
        
    } catch (Exception $e) {
        // Rollback only if a transaction is active
        if ($db !== null && $db->inTransaction()) {
            $db->rollBack();
        }
        // Re-enable foreign key checks in case of error
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        logError('Error truncating tables: ' . $e->getMessage());
        echo "Error truncating tables: " . $e->getMessage() . "\n";
        throw $e; // Re-throw to allow debugging
    }
}

// Execute the truncation
try {
    truncateTables($tablesToTruncate);
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}
?>