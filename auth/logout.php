<?php
// Include database configuration
require_once '../config/config.php';

// Start session
session_start();

// Log activity if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userType = $_SESSION['user_role'] ?? 'unknown';
    
    // Log logout activity
    try {
        $logStmt = $conn->prepare("INSERT INTO activity_logs (user_type, user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        if ($logStmt) {
            $action = 'logout';
            $description = 'User logged out';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $logStmt->bind_param("sissss", $userType, $userId, $action, $description, $ip, $userAgent);
            $logStmt->execute();
            $logStmt->close();
        }
    } catch (Exception $e) {
        // Don't fail logout if logging fails
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

// Destroy all session data
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Redirect to homepage
header('Location: ../index.html');
exit;
?>
