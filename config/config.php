<?php
/**
 * ConSlot Application Configuration
 * DCC Consultation Booking Portal
 */

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Application settings
define('APP_NAME', 'ConSlot');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/conslot');
define('APP_DESCRIPTION', 'DCC Consultation Booking Portal');

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('SESSION_NAME', 'conslot_session');

// Time settings
define('SLOT_DURATION', 30); // 30 minutes per slot
define('TIME_FORMAT', 'H:i'); // 24-hour format
define('DATE_FORMAT', 'Y-m-d'); // MySQL date format
define('DISPLAY_DATE_FORMAT', 'F j, Y'); // Display format
define('DISPLAY_TIME_FORMAT', 'g:i A'); // Display time format

// Pagination settings
define('ITEMS_PER_PAGE', 10);
define('MAX_PAGE_LINKS', 5);

// File upload settings
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Email settings (for notifications)
define('EMAIL_FROM', 'noreply@conslot.com');
define('EMAIL_FROM_NAME', 'ConSlot System');

// Security settings
define('HASH_COST', 12); // Password hashing cost
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Debug mode (set to false in production)
define('DEBUG_MODE', true);

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Manila');

// Include required files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . '/auth/login.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// Auto-check session timeout for logged-in users
if (isLoggedIn()) {
    checkSessionTimeout();
}

// Set security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
if (!DEBUG_MODE) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Global variables
$current_user = null;
if (isLoggedIn()) {
    $current_user = getCurrentUser();
}
?>
