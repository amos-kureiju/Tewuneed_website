<?php
/**
 * Logout Handler
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

// Clear user session
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
}

// Optionally clear entire session
// session_destroy();

setFlash('success', 'Anda berhasil logout. Sampai jumpa kembali!');
header('Location: ' . SITE_URL . '/index.php');
exit;
?>
