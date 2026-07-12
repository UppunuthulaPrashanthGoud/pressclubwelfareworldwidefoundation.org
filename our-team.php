<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/config.php';

// Database connection
$db = getDbConnection();

include 'header.php'; 
include 'navbar.php'; 
?>

<main class="container my-5">
<!-- Committees SECTION -->
<?php include 'includes/national-committee.php'; ?>
<?php include 'includes/state-committee.php'; ?>
<?php include 'includes/district-committee.php'; ?>
<!-- MEMBER COMMITTEE SECTION -->
<?php include 'includes/member-committee.php'; ?>
</main>

<?php include 'footer.php'; ?>
