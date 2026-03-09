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
    'message' => 'Registration failed'
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and validate form data
    $firstName = sanitize_input($_POST['first_name'] ?? '');
    $middleName = sanitize_input($_POST['middle_name'] ?? '');
    $lastName = sanitize_input($_POST['last_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'student');
    $terms = isset($_POST['terms']) ? 1 : 0;
    
    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $response['message'] = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirmPassword) {
        $response['message'] = 'Passwords do not match';
    } elseif (!$terms) {
        $response['message'] = 'You must agree to the terms and conditions';
    } else {
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into appropriate table based on role
            if ($role === 'student') {
                // Check if email already exists in students table
                $checkEmail = $conn->prepare("SELECT id FROM students WHERE email = ?");
                if (!$checkEmail) {
                    throw new Exception("Database prepare failed: " . $conn->error);
                }
                
                $checkEmail->bind_param("s", $email);
                if (!$checkEmail->execute()) {
                    throw new Exception("Database execute failed: " . $checkEmail->error);
                }
                $result = $checkEmail->get_result();
                
                if ($result->num_rows > 0) {
                    $response['message'] = 'Email address already exists';
                } else {
                    // Insert new student
                    $insertUser = $conn->prepare("INSERT INTO students (first_name, middle_name, last_name, email, password, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    if (!$insertUser) {
                        throw new Exception("Database prepare failed: " . $conn->error);
                    }
                    
                    $insertUser->bind_param("sssss", $firstName, $middleName, $lastName, $email, $hashedPassword);
                    
                    if ($insertUser->execute()) {
                        $userId = $insertUser->insert_id;
                        
                        // Log activity
                        logActivity('student', $userId, 'registration', "New student registered: $firstName $lastName");
                        
                        // Set session for immediate login
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['user_role'] = 'student';
                        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                        $_SESSION['user_email'] = $email;
                        
                        $response = [
                            'status' => 'success',
                            'message' => 'Student registration successful! Redirecting to dashboard...',
                            'redirect' => '../dashboard.php'
                        ];
                    } else {
                        throw new Exception("Database insert failed: " . $insertUser->error);
                    }
                    
                    $insertUser->close();
                }
                
                $checkEmail->close();
                
            } elseif ($role === 'instructor') {
                // Check if email already exists in instructors table
                $checkEmail = $conn->prepare("SELECT id FROM instructors WHERE email = ?");
                if (!$checkEmail) {
                    throw new Exception("Database prepare failed: " . $conn->error);
                }
                
                $checkEmail->bind_param("s", $email);
                if (!$checkEmail->execute()) {
                    throw new Exception("Database execute failed: " . $checkEmail->error);
                }
                $result = $checkEmail->get_result();
                
                if ($result->num_rows > 0) {
                    $response['message'] = 'Email address already exists';
                } else {
                    // Insert new instructor
                    $insertUser = $conn->prepare("INSERT INTO instructors (first_name, middle_name, last_name, email, password, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    if (!$insertUser) {
                        throw new Exception("Database prepare failed: " . $conn->error);
                    }
                    
                    $insertUser->bind_param("sssss", $firstName, $middleName, $lastName, $email, $hashedPassword);
                    
                    if ($insertUser->execute()) {
                        $userId = $insertUser->insert_id;
                        
                        // Log activity
                        logActivity('instructor', $userId, 'registration', "New instructor registered: $firstName $lastName");
                        
                        // Set session for immediate login
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['user_role'] = 'instructor';
                        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                        $_SESSION['user_email'] = $email;
                        
                        $response = [
                            'status' => 'success',
                            'message' => 'Instructor registration successful! Redirecting to dashboard...',
                            'redirect' => '../dashboard.php'
                        ];
                    } else {
                        throw new Exception("Database insert failed: " . $insertUser->error);
                    }
                    
                    $insertUser->close();
                }
                
                $checkEmail->close();
                
            } else {
                $response['message'] = 'Invalid user role specified';
            }
            
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
        // Don't fail registration if logging fails
        error_log("Activity logging failed: " . $e->getMessage());
    }
}
?>
