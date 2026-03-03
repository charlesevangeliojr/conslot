<?php
/**
 * ConSlot Registration Backend
 * DCC Consultation Booking Portal
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/google_oauth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $redirect_url = $current_user['role'] === 'admin' ? APP_URL . '/admin/dashboard.php' : APP_URL . '/student/dashboard.php';
    redirect($redirect_url);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $middle_name = sanitize($_POST['middle_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Combine name parts
    $name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
    $name = preg_replace('/\s+/', ' ', $name); // Replace multiple spaces with single space
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Invalid request. Please try again.');
    } elseif (empty($first_name) || empty($last_name)) {
        setFlashMessage('danger', 'First name and last name are required.');
    } elseif ($password !== $confirm_password) {
        setFlashMessage('danger', 'Passwords do not match.');
    } else {
        // Attempt registration
        $result = register($name, $email, $password);
        
        if ($result['success']) {
            redirect(APP_URL . '/auth/login.php', 'success', $result['message']);
        } else {
            setFlashMessage('danger', $result['message']);
        }
    }
    
    // Store form data to repopulate after redirect
    $_SESSION['form_data'] = [
        'first_name' => $first_name,
        'middle_name' => $middle_name,
        'last_name' => $last_name,
        'email' => $email
    ];
    
    redirect(APP_URL . '/index.html');
}

// Get stored form data
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Redirect to HTML form
header('Location: register.html');
exit();
?>
