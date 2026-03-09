<?php
// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
try {
    require_once '../config/config.php';
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Login failed'
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and validate form data
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $userType = sanitize_input($_POST['user_type'] ?? ''); // 'student' or 'instructor'
    $rememberMe = isset($_POST['remember_me']) ? 1 : 0;
    
    // Validate required fields
    if (empty($email) || empty($password)) {
        $response['message'] = 'Please enter email and password';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
    } elseif (empty($userType) || !in_array($userType, ['student', 'instructor'])) {
        $response['message'] = 'Please select user type';
    } else {
        try {
            // Check user credentials based on user type
            if ($userType === 'student') {
                $loginStmt = $conn->prepare("SELECT id, first_name, last_name, email, password, status FROM students WHERE email = ?");
            } else {
                $loginStmt = $conn->prepare("SELECT id, first_name, last_name, email, password, status FROM instructors WHERE email = ?");
            }
            
            if (!$loginStmt) {
                throw new Exception("Database prepare failed: " . $conn->error);
            }
            
            $loginStmt->bind_param("s", $email);
            if (!$loginStmt->execute()) {
                throw new Exception("Database execute failed: " . $loginStmt->error);
            }
            
            $result = $loginStmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    
                    // Check if account is active
                    if ($user['status'] === 'active') {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_role'] = $userType;
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['user_email'] = $user['email'];
                        
                        // Update last login
                        if ($userType === 'student') {
                            $updateLogin = $conn->prepare("UPDATE students SET last_login = NOW() WHERE id = ?");
                        } else {
                            $updateLogin = $conn->prepare("UPDATE instructors SET last_login = NOW() WHERE id = ?");
                        }
                        
                        if ($updateLogin) {
                            $updateLogin->bind_param("i", $user['id']);
                            $updateLogin->execute();
                            $updateLogin->close();
                        }
                        
                        // Log activity
                        logActivity($userType, $user['id'], 'login', 'User logged in');
                        
                        // Handle remember me
                        if ($rememberMe) {
                            // Set cookie for 30 days
                            $cookieValue = base64_encode($user['id'] . ':' . $email . ':' . $userType);
                            setcookie('remember_me', $cookieValue, time() + (86400 * 30), '/');
                        }
                        
                        // Determine redirect based on role
                        $redirect = $userType === 'instructor' ? 'instructor/dashboard.php' : 'student/dashboard.php';
                        
                        $response = [
                            'status' => 'success',
                            'message' => 'Login successful! Redirecting to dashboard...',
                            'redirect' => $redirect,
                            'role' => $userType
                        ];
                    } else {
                        $response['message'] = 'Account is ' . $user['status'] . '. Please contact administrator.';
                    }
                } else {
                    $response['message'] = 'Invalid email or password';
                }
            } else {
                $response['message'] = 'Invalid email or password';
            }
            
            $loginStmt->close();
            
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Helper function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Helper function to log activity
function logActivity($userType, $userId, $action, $description) {
    global $conn;
    
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $logStmt = $conn->prepare("INSERT INTO activity_logs (user_type, user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        if ($logStmt) {
            $logStmt->bind_param("sissss", $userType, $userId, $action, $description, $ip, $userAgent);
            $logStmt->execute();
            $logStmt->close();
        }
    } catch (Exception $e) {
        // Don't fail login if logging fails
        error_log("Activity logging failed: " . $e->getMessage());
    }
}
?>
