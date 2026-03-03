<?php
/**
 * ConSlot Logout Handler
 * DCC Consultation Booking Portal
 */

require_once __DIR__ . '/../config/config.php';

// Logout the user
logout();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_email'])) {
    setcookie('remember_email', '', time() - 3600, '/');
}

// Redirect to login page with success message
redirect(APP_URL . '/auth/login.php', 'success', 'You have been logged out successfully.');
?>
