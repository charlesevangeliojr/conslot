<?php
/**
 * ConSlot Login Page
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
    
    // Store form data to repopulate after redirect
    $_SESSION['form_data'] = [
        'email' => $email,
        'remember' => $remember
    ];
    
    // Redirect to index.html to show login modal with error
    redirect(APP_URL . '/index.html');
}

// Get remembered email if exists
$remembered_email = $_COOKIE['remember_email'] ?? '';

$page_title = 'Login';
$page_description = 'Login to your ConSlot account';

include __DIR__ . '/../includes/header.php';
?>

<style>
/* Modern Login Page Design */
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
    padding: 2rem 1rem;
}

.auth-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
    animation: floatBackground 20s ease-in-out infinite;
}

@keyframes floatBackground {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(1deg); }
}

.auth-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 3rem;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.2);
    max-width: 450px;
    width: 100%;
    position: relative;
    z-index: 10;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.auth-logo {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
    animation: float 3s ease-in-out infinite;
    position: relative;
    overflow: hidden;
}

.auth-logo::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.auth-logo i {
    font-size: 2.5rem;
    color: white;
    position: relative;
    z-index: 2;
}

.auth-header h1 {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    animation: fadeInUp 0.8s ease-out 0.2s both;
}

.auth-header p {
    color: #64748b;
    font-size: 1.1rem;
    font-weight: 500;
    animation: fadeInUp 0.8s ease-out 0.4s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-group {
    margin-bottom: 1.75rem;
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.95rem;
}

.form-label i {
    width: 20px;
    height: 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
}

.form-control {
    width: 100%;
    padding: 1rem 1.25rem;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9fafb;
    color: #1f2937;
    font-weight: 500;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.form-control::placeholder {
    color: #9ca3af;
    font-weight: 400;
}

.password-input-group {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 1.25rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.password-toggle:hover {
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    animation: fadeInUp 0.8s ease-out 0.8s both;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
    color: #4b5563;
    cursor: pointer;
    font-weight: 500;
}

.checkbox-label input[type="checkbox"] {
    width: 1.25rem;
    height: 1.25rem;
    accent-color: #667eea;
    cursor: pointer;
}

.forgot-link {
    color: #667eea;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
}

.forgot-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #667eea;
    transition: width 0.3s ease;
}

.forgot-link:hover::after {
    width: 100%;
}

.btn-primary {
    width: 100%;
    padding: 1.25rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 16px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.8s ease-out 1s both;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}

.btn-primary:active {
    transform: translateY(-1px);
}

.auth-divider {
    display: flex;
    align-items: center;
    margin: 2rem 0;
    gap: 1rem;
    animation: fadeInUp 0.8s ease-out 1.2s both;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
}

.auth-divider span {
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 0 1rem;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 12px;
}

.google-signin {
    margin-bottom: 2rem;
    animation: fadeInUp 0.8s ease-out 1.4s both;
}

.btn-google {
    width: 100%;
    padding: 1.125rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    color: #374151;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.btn-google:hover {
    background: #f8fafc;
    border-color: #667eea;
    color: #1f2937;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.btn-google i {
    color: #4285f4;
    font-size: 1.25rem;
}

.auth-footer {
    text-align: center;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
    animation: fadeInUp 0.8s ease-out 1.6s both;
}

.auth-footer p {
    color: #6b7280;
    font-size: 0.95rem;
    margin: 0;
}

.auth-footer a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
}

.auth-footer a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #667eea;
    transition: width 0.3s ease;
}

.auth-footer a:hover::after {
    width: 100%;
}

/* Floating shapes for background */
.auth-container::after {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    top: 10%;
    right: 10%;
    animation: float 15s ease-in-out infinite;
}

.auth-container::before {
    content: '';
    position: absolute;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(118, 75, 162, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    bottom: 10%;
    left: 10%;
    animation: float 12s ease-in-out infinite reverse;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .auth-container {
        padding: 1rem;
    }
    
    .auth-card {
        padding: 2rem 1.5rem;
        margin: 1rem 0;
    }
    
    .auth-header h1 {
        font-size: 2rem;
    }
    
    .form-control {
        padding: 0.875rem 1rem;
    }
    
    .btn-primary {
        padding: 1rem;
        font-size: 1rem;
    }
}
</style>

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
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>
        
        <div class="auth-divider">
            <span>OR</span>
        </div>
        
        <div class="google-signin">
            <a href="<?php echo getGoogleAuthUrl(); ?>" class="btn btn-google btn-full">
                <i class="fab fa-google"></i>
                Sign in with Google
            </a>
        </div>
        
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
