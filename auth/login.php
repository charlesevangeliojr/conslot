<?php
/**
 * ConSlot Login Page
 * DCC Consultation Booking Portal
 */

require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $redirect_url = $current_user['role'] === 'admin' ? APP_URL . '/admin/dashboard.php' : APP_URL . '/student/dashboard.php';
    redirect($redirect_url);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Invalid request. Please try again.');
    } else {
        // Attempt login
        $result = login($email, $password);
        
        if ($result['success']) {
            // Set remember me cookie if checked
            if ($remember) {
                setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/'); // 30 days
            }
            
            // Redirect based on role
            $redirect_url = $result['user']['role'] === 'admin' ? APP_URL . '/admin/dashboard.php' : APP_URL . '/student/dashboard.php';
            redirect($redirect_url, 'success', 'Welcome back, ' . $result['user']['name'] . '!');
        } else {
            setFlashMessage('danger', $result['message']);
        }
    }
    
    // Redirect to prevent form resubmission
    redirect(APP_URL . '/auth/login.php');
}

// Get remembered email if exists
$remembered_email = $_COOKIE['remember_email'] ?? '';

$page_title = 'Login';
$page_description = 'Login to your ConSlot account';

include __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to your <?php echo APP_NAME; ?> account</p>
        </div>
        
        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i>
                    Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($remembered_email); ?>"
                    placeholder="Enter your email"
                    required
                    autocomplete="email"
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <div class="password-input-group">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" <?php echo $remembered_email ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Remember me
                </label>
                
                <a href="<?php echo APP_URL; ?>/auth/forgot-password.php" class="forgot-link">
                    Forgot password?
                </a>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Don't have an account? 
                <a href="<?php echo APP_URL; ?>/auth/register.php">Sign up</a>
            </p>
        </div>
    </div>
</div>

<div class="auth-features">
    <div class="feature-card">
        <i class="fas fa-clock"></i>
        <h3>Easy Booking</h3>
        <p>Book consultation slots with just a few clicks</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-calendar-alt"></i>
        <h3>Smart Scheduling</h3>
        <p>Intelligent time management for fair distribution</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-users"></i>
        <h3>Role-based Access</h3>
        <p>Secure access for instructors and students</p>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

// Auto-focus email field
document.addEventListener('DOMContentLoaded', function() {
    const emailField = document.getElementById('email');
    if (!emailField.value) {
        emailField.focus();
    } else {
        document.getElementById('password').focus();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
