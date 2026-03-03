<?php
/**
 * ConSlot Registration Page
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
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('danger', 'Invalid request. Please try again.');
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
        'name' => $name,
        'email' => $email
    ];
    
    redirect(APP_URL . '/auth/register.php');
}

// Get stored form data
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

$page_title = 'Register';
$page_description = 'Create your ConSlot account';

include __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p>Join <?php echo APP_NAME; ?> to book consultations</p>
        </div>
        
        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="fas fa-user"></i>
                    Full Name
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control" 
                    value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                    placeholder="Enter your full name"
                    required
                    autocomplete="name"
                >
            </div>
            
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
                    value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
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
                        placeholder="Create a password"
                        required
                        autocomplete="new-password"
                        minlength="6"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label">
                    <i class="fas fa-lock"></i>
                    Confirm Password
                </label>
                <div class="password-input-group">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        placeholder="Confirm your password"
                        required
                        autocomplete="new-password"
                        minlength="6"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span class="checkmark"></span>
                    I agree to the <a href="#" onclick="showTerms(); return false;">Terms of Service</a> and <a href="#" onclick="showPrivacy(); return false;">Privacy Policy</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-user-plus"></i>
                Create Account
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Already have an account? 
                <a href="<?php echo APP_URL; ?>/auth/login.php">Sign in</a>
            </p>
        </div>
    </div>
</div>

<div class="auth-features">
    <div class="feature-card">
        <i class="fas fa-shield-alt"></i>
        <h3>Secure Platform</h3>
        <p>Your data is protected with industry-standard security</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-bell"></i>
        <h3>Instant Notifications</h3>
        <p>Get real-time updates about your bookings</p>
    </div>
    
    <div class="feature-card">
        <i class="fas fa-history"></i>
        <h3>Booking History</h3>
        <p>Track all your consultation appointments</p>
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

// Password strength checker
function checkPasswordStrength(password) {
    const strengthDiv = document.getElementById('passwordStrength');
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    const strengthLevels = [
        { class: 'weak', text: 'Weak password', color: '#dc3545' },
        { class: 'fair', text: 'Fair password', color: '#ffc107' },
        { class: 'good', text: 'Good password', color: '#28a745' },
        { class: 'strong', text: 'Strong password', color: '#007bff' },
        { class: 'very-strong', text: 'Very strong password', color: '#6f42c1' }
    ];
    
    const level = strengthLevels[Math.min(strength - 1, strengthLevels.length - 1)];
    
    if (password.length > 0) {
        strengthDiv.innerHTML = `<div class="strength-bar ${level.class}" style="width: ${(strength / 5) * 100}%; background-color: ${level.color};"></div>
                                 <span class="strength-text" style="color: ${level.color};">${level.text}</span>`;
        strengthDiv.style.display = 'block';
    } else {
        strengthDiv.style.display = 'none';
    }
}

// Password confirmation checker
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const confirmGroup = document.getElementById('confirm_password').closest('.form-group');
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
            confirmGroup.classList.remove('error');
            confirmGroup.classList.add('success');
        } else {
            confirmGroup.classList.remove('success');
            confirmGroup.classList.add('error');
        }
    } else {
        confirmGroup.classList.remove('error', 'success');
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    passwordField.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        checkPasswordMatch();
    });
    
    confirmPasswordField.addEventListener('input', checkPasswordMatch);
    
    // Auto-focus name field
    document.getElementById('name').focus();
});

function showTerms() {
    alert('Terms of Service:\n\n1. Use the platform for legitimate consultation purposes\n2. Respect instructors\' time and schedules\n3. Provide accurate information\n4. Follow academic integrity guidelines\n5. Report any issues or concerns promptly');
}

function showPrivacy() {
    alert('Privacy Policy:\n\n1. We collect only necessary information for booking purposes\n2. Your personal data is encrypted and secure\n3. We do not share your information with third parties\n4. You can request data deletion at any time\n5. We comply with data protection regulations');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
