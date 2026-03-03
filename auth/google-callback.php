<?php
/**
 * Google OAuth Callback Handler
 * ConSlot - DCC Consultation Booking Portal
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/google_oauth.php';

// Check if authorization code is provided
if (!isset($_GET['code'])) {
    redirect(APP_URL . '/auth/login.php?error=google_auth_failed');
    exit();
}

$code = $_GET['code'];

// Exchange authorization code for access token
$tokenData = getGoogleAccessToken($code);

if (!$tokenData || isset($tokenData['error'])) {
    redirect(APP_URL . '/auth/login.php?error=google_token_failed');
    exit();
}

$accessToken = $tokenData['access_token'];

// Get user information from Google
$userInfo = getGoogleUserInfo($accessToken);

if (!$userInfo || isset($userInfo['error'])) {
    redirect(APP_URL . '/auth/login.php?error=google_user_info_failed');
    exit();
}

// Authenticate user with Google
$result = authenticateWithGoogle($userInfo);

if ($result['success']) {
    // Redirect based on role
    $redirect_url = $result['user']['role'] === 'admin' ? APP_URL . '/admin/dashboard.php' : APP_URL . '/student/dashboard.php';
    redirect($redirect_url, 'success', $result['message']);
} else {
    redirect(APP_URL . '/auth/login.php?error=' . urlencode($result['message']));
}
?>
