<?php
/**
 * Google OAuth Configuration
 * ConSlot - DCC Consultation Booking Portal
 */

// Google OAuth Client Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/google-callback.php');

// Google OAuth URLs
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_INFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// Scopes required
define('GOOGLE_SCOPES', 'openid email profile');

/**
 * Get Google OAuth URL
 */
function getGoogleAuthUrl() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => GOOGLE_SCOPES,
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    return GOOGLE_AUTH_URL . '?' . http_build_query($params);
}

/**
 * Exchange authorization code for access token
 */
function getGoogleAccessToken($code) {
    $data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents(GOOGLE_TOKEN_URL, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    return json_decode($response, true);
}

/**
 * Get user information from Google
 */
function getGoogleUserInfo($accessToken) {
    $options = [
        'http' => [
            'header' => "Authorization: Bearer " . $accessToken . "\r\n"
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents(GOOGLE_USER_INFO_URL, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    return json_decode($response, true);
}

/**
 * Register or login user with Google
 */
function authenticateWithGoogle($userInfo) {
    $email = sanitize($userInfo['email']);
    $name = sanitize($userInfo['name']);
    $googleId = sanitize($userInfo['id']);
    $avatar = sanitize($userInfo['picture'] ?? '');
    
    // Check if user exists with this Google ID
    $existingUser = getUserByGoogleId($googleId);
    
    if ($existingUser) {
        // User exists, login
        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['user_role'] = $existingUser['role'];
        $_SESSION['login_method'] = 'google';
        
        return [
            'success' => true,
            'user' => $existingUser,
            'message' => 'Welcome back, ' . $existingUser['name'] . '!'
        ];
    }
    
    // Check if user exists with this email (but different auth method)
    $emailUser = getUserByEmail($email);
    
    if ($emailUser && !empty($emailUser['password'])) {
        return [
            'success' => false,
            'message' => 'An account with this email already exists. Please login with your password.'
        ];
    }
    
    // Register new user
    $result = registerWithGoogle($name, $email, $googleId, $avatar);
    
    if ($result['success']) {
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_role'] = $result['user']['role'];
        $_SESSION['login_method'] = 'google';
    }
    
    return $result;
}

/**
 * Register user with Google credentials
 */
function registerWithGoogle($name, $email, $googleId, $avatar = '') {
    global $pdo;
    
    try {
        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, google_id, avatar, role, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 'student', NOW(), NOW())
        ");
        
        $stmt->execute([$name, $email, $googleId, $avatar]);
        $userId = $pdo->lastInsertId();
        
        // Get the newly created user
        $user = getUserById($userId);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Account created successfully with Google!'
        ];
        
    } catch (PDOException $e) {
        error_log("Google registration error: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Registration failed. Please try again.'
        ];
    }
}
?>
