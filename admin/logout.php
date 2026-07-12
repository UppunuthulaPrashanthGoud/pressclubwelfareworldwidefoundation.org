<?php
require_once '../config/config.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header("Location: " . SITE_URL . "/admin-login.php");
exit;
?>
