<?php
/**
 * Admin Logout
 * TeWuNeed E-Commerce Website
 */

require_once '../includes/functions.php';

// Clear admin session
if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_role']);
}

header('Location: login.php');
exit;
?>
