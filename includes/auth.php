<?php
/**
 * ConSlot Authentication Functions
 * DCC Consultation Booking Portal
 */

global $database;

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $database;
    
    $sql = "SELECT id, name, email, role, google_id, avatar, login_method, created_at FROM users WHERE id = ? AND deleted_at IS NULL";
    $user = $database->getSingle($sql, [$_SESSION['user_id']]);
    
    return $user ?: null;
}

/**
 * Login user
 */
function login($email, $password) {
    global $database;
    
    // Check login attempts
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        if (isset($_SESSION['login_lockout']) && time() - $_SESSION['login_lockout'] < LOGIN_LOCKOUT_TIME) {
            return ['success' => false, 'message' => 'Too many failed attempts. Please try again later.'];
        } else {
            unset($_SESSION['login_attempts']);
            unset($_SESSION['login_lockout']);
        }
    }
    
    // Get user by email
    $sql = "SELECT id, name, email, password, role, login_method FROM users WHERE email = ? AND deleted_at IS NULL";
    $user = $database->getSingle($sql, [$email]);
    
    if (!$user) {
        incrementLoginAttempts();
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    // Check if user registered with Google
    if ($user['login_method'] === 'google' && empty($user['password'])) {
        return ['success' => false, 'message' => 'This account uses Google login. Please use the Google Sign-In button.'];
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        incrementLoginAttempts();
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    // Login successful
    unset($_SESSION['login_attempts']);
    unset($_SESSION['login_lockout']);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    // Log activity
    logActivity($user['id'], 'login', 'User logged in from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    return ['success' => true, 'user' => $user];
}

/**
 * Increment login attempts
 */
function incrementLoginAttempts() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 1;
    } else {
        $_SESSION['login_attempts']++;
    }
    
    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['login_lockout'] = time();
    }
}

/**
 * Logout user
 */
function logout() {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    session_unset();
    session_destroy();
    
    // Start new session for flash messages
    session_start();
}

/**
 * Register new user
 */
function register($name, $email, $password, $role = 'student') {
    global $database;
    
    // Validate input
    if (empty($name) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters long'];
    }
    
    // Check if email already exists
    if ($database->exists("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL", [$email])) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
    
    // Insert user
    $sql = "INSERT INTO users (name, email, password, role, login_method) VALUES (?, ?, ?, ?, 'email')";
    $user_id = $database->insert($sql, [$name, $email, $hashed_password, $role]);
    
    if (!$user_id) {
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
    
    // Log activity
    logActivity($user_id, 'register', 'New user registered: ' . $email);
    
    return ['success' => true, 'message' => 'Registration successful! You can now log in.'];
}

/**
 * Update user profile
 */
function updateProfile($user_id, $name, $email) {
    global $database;
    
    // Validate input
    if (empty($name) || empty($email)) {
        return ['success' => false, 'message' => 'Name and email are required'];
    }
    
    if (!validateEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    // Check if email is already used by another user
    if ($database->exists("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id])) {
        return ['success' => false, 'message' => 'Email is already used by another user'];
    }
    
    // Update user
    $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
    $result = $database->update($sql, [$name, $email, $user_id]);
    
    if (!$result) {
        return ['success' => false, 'message' => 'Profile update failed. Please try again.'];
    }
    
    // Update session
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    
    // Log activity
    logActivity($user_id, 'profile_update', 'Profile updated');
    
    return ['success' => true, 'message' => 'Profile updated successfully!'];
}

/**
 * Change password
 */
function changePassword($user_id, $current_password, $new_password) {
    global $database;
    
    // Validate input
    if (empty($current_password) || empty($new_password)) {
        return ['success' => false, 'message' => 'Both current and new passwords are required'];
    }
    
    if (strlen($new_password) < 6) {
        return ['success' => false, 'message' => 'New password must be at least 6 characters long'];
    }
    
    // Get current password hash
    $sql = "SELECT password FROM users WHERE id = ?";
    $user = $database->getSingle($sql, [$user_id]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    // Hash new password
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
    
    // Update password
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $result = $database->update($sql, [$new_hashed_password, $user_id]);
    
    if (!$result) {
        return ['success' => false, 'message' => 'Password update failed. Please try again.'];
    }
    
    // Log activity
    logActivity($user_id, 'password_change', 'Password changed');
    
    return ['success' => true, 'message' => 'Password changed successfully!'];
}

/**
 * Require login to access page
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/auth/login.php', 'warning', 'Please log in to access this page.');
    }
}

/**
 * Require specific role to access page
 */
function requireRole($required_role) {
    requireLogin();
    
    if (!hasPermission($required_role)) {
        redirect(APP_URL . '/index.php', 'danger', 'You do not have permission to access this page.');
    }
}

/**
 * Check if user can access booking
 */
function canAccessBooking($booking_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    global $database;
    global $current_user;
    
    // Admin can access all bookings
    if ($current_user['role'] === 'admin') {
        return true;
    }
    
    // Students can only access their own bookings
    $sql = "SELECT student_id FROM bookings WHERE id = ?";
    $booking = $database->getSingle($sql, [$booking_id]);
    
    return $booking && $booking['student_id'] == $current_user['id'];
}

/**
 * Check if user can manage slot
 */
function canManageSlot($slot_id) {
    if (!isLoggedIn()) {
        return false;
    }
    
    global $database;
    global $current_user;
    
    // Admin can manage all slots
    if ($current_user['role'] === 'admin') {
        return true;
    }
    
    // Check if slot belongs to the instructor
    $sql = "SELECT instructor_id FROM consultation_slots WHERE id = ?";
    $slot = $database->getSingle($sql, [$slot_id]);
    
    return $slot && $slot['instructor_id'] == $current_user['id'];
}

/**
 * Get user by ID
 */
function getUserById($user_id) {
    global $database;
    
    $sql = "SELECT id, name, email, role, google_id, avatar, login_method, created_at FROM users WHERE id = ? AND deleted_at IS NULL";
    return $database->getSingle($sql, [$user_id]);
}

/**
 * Get all users (for admin)
 */
function getAllUsers($role = null, $page = 1, $search = '') {
    global $database;
    
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    $params = [];
    
    $sql = "SELECT id, name, email, role, login_method, created_at FROM users WHERE deleted_at IS NULL";
    
    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    
    if ($search) {
        $sql .= " AND (name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = ITEMS_PER_PAGE;
    $params[] = $offset;
    
    return $database->getMultiple($sql, $params);
}

/**
 * Count users (for pagination)
 */
function countUsers($role = null, $search = '') {
    global $database;
    
    $params = [];
    $sql = "SELECT COUNT(*) as count FROM users WHERE deleted_at IS NULL";
    
    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }
    
    if ($search) {
        $sql .= " AND (name LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    return $database->count($sql, $params);
}

/**
 * Get user by email
 */
function getUserByEmail($email) {
    global $database;
    
    $sql = "SELECT id, name, email, role, google_id, avatar, login_method, created_at FROM users WHERE email = ? AND deleted_at IS NULL";
    return $database->getSingle($sql, [$email]);
}

/**
 * Get user by Google ID
 */
function getUserByGoogleId($googleId) {
    global $database;
    
    $sql = "SELECT id, name, email, role, google_id, avatar, login_method, created_at FROM users WHERE google_id = ? AND deleted_at IS NULL";
    return $database->getSingle($sql, [$googleId]);
}
?>
