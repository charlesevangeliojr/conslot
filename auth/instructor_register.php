<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../student/dashboard.html');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    
    // Get and sanitize form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $specialization = trim($_POST['specialization']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $bio = trim($_POST['bio']);
    
    // Basic validation
    $errors = [];
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($department) || empty($specialization) || empty($password) || empty($bio)) {
        $errors[] = "All fields are required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already exists";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert instructor into database
            $stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, email, password, user_type, department, specialization, bio, created_at, status) 
                VALUES (?, ?, ?, ?, 'instructor', ?, ?, ?, NOW(), 'active')
            ");
            $stmt->execute([$first_name, $last_name, $email, $hashed_password, $department, $specialization, $bio]);
            
            // Set session variables
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_type'] = 'instructor';
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;
            
            // Redirect to instructor dashboard (or student dashboard for now)
            header('Location: ../student/dashboard.html');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Registration - ConSlot</title>
    <meta name="description" content="Create your instructor account on ConSlot and start consulting with students">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #2b6e3e;
            --primary-light: #4c9a6b;
            --primary-dark: #1b4b2b;
            --secondary: #2c3e50;
            --success: #198754;
            --danger: #bb2d3b;
            --warning: #ffc107;
            --info: #0dcaf0;
            --bg-body: #f4f7fb;
            --bg-card: #ffffff;
            --bg-soft: #f8fafd;
            --border-light: #e9ecf0;
            --border-main: #d8dee6;
            --text-main: #1e2a3a;
            --text-muted: #546e7a;
            --text-light: #ffffff;
            --shadow-card: 0 12px 30px rgba(0, 20, 10, 0.06), 0 4px 10px rgba(0, 20, 10, 0.03);
            --shadow-hover: 0 20px 35px rgba(0, 40, 20, 0.12);
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        
        body {
            font-family: var(--font-sans);
            background: linear-gradient(135deg, var(--bg-body) 0%, #e8f3ed 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: white;
            border-bottom: 1px solid var(--border-light);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
        }
        
        .nav-link {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .registration-container {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card);
            max-width: 800px;
            width: 100%;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .registration-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
        }
        
        .registration-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .registration-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .registration-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.95rem;
        }
        
        .form-label i {
            margin-right: 8px;
            color: var(--primary);
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-light);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--bg-soft);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(43, 110, 62, 0.1);
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .password-input-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(43, 110, 62, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(43, 110, 62, 0.4);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }
        
        .btn-full {
            width: 100%;
            justify-content: center;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: var(--danger);
        }
        
        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border-color: var(--success);
        }
        
        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-light);
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .registration-container {
                padding: 2rem;
            }
            
            .registration-header h1 {
                font-size: 2rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="../index.html" class="logo">
                <i class="fas fa-calendar-check"></i> ConSlot
            </a>
            <div class="nav-links">
                <a href="../index.html" class="nav-link">Home</a>
                <a href="login.php" class="nav-link">Sign In</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="registration-container">
            <div class="registration-header">
                <h1>
                    <i class="fas fa-chalkboard-teacher"></i>
                    Instructor Registration
                </h1>
                <p>Join ConSlot and start consulting with students</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">
                            <i class="fas fa-user"></i>
                            First Name
                        </label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            class="form-control" 
                            placeholder="Enter your first name"
                            required
                            value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="form-label">
                            <i class="fas fa-user"></i>
                            Last Name
                        </label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            class="form-control" 
                            placeholder="Enter your last name"
                            required
                            value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                        >
                    </div>
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
                        placeholder="Enter your email address"
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="department" class="form-label">
                            <i class="fas fa-building"></i>
                            Department
                        </label>
                        <select 
                            id="department" 
                            name="department" 
                            class="form-control" 
                            required
                        >
                            <option value="">Select your department</option>
                            <option value="computer_science" <?php echo (isset($_POST['department']) && $_POST['department'] === 'computer_science') ? 'selected' : ''; ?>>Computer Science</option>
                            <option value="mathematics" <?php echo (isset($_POST['department']) && $_POST['department'] === 'mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                            <option value="physics" <?php echo (isset($_POST['department']) && $_POST['department'] === 'physics') ? 'selected' : ''; ?>>Physics</option>
                            <option value="engineering" <?php echo (isset($_POST['department']) && $_POST['department'] === 'engineering') ? 'selected' : ''; ?>>Engineering</option>
                            <option value="psychology" <?php echo (isset($_POST['department']) && $_POST['department'] === 'psychology') ? 'selected' : ''; ?>>Psychology</option>
                            <option value="chemistry" <?php echo (isset($_POST['department']) && $_POST['department'] === 'chemistry') ? 'selected' : ''; ?>>Chemistry</option>
                            <option value="biology" <?php echo (isset($_POST['department']) && $_POST['department'] === 'biology') ? 'selected' : ''; ?>>Biology</option>
                            <option value="business" <?php echo (isset($_POST['department']) && $_POST['department'] === 'business') ? 'selected' : ''; ?>>Business</option>
                            <option value="other" <?php echo (isset($_POST['department']) && $_POST['department'] === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="specialization" class="form-label">
                            <i class="fas fa-chalkboard-teacher"></i>
                            Specialization
                        </label>
                        <input 
                            type="text" 
                            id="specialization" 
                            name="specialization" 
                            class="form-control" 
                            placeholder="e.g., AI & Machine Learning, Calculus & Statistics"
                            required
                            value="<?php echo isset($_POST['specialization']) ? htmlspecialchars($_POST['specialization']) : ''; ?>"
                        >
                    </div>
                </div>
                
                <div class="form-row">
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
                                placeholder="Create a strong password"
                                required
                                minlength="6"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
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
                                minlength="6"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="bio" class="form-label">
                        <i class="fas fa-info-circle"></i>
                        Professional Bio
                    </label>
                    <textarea 
                        id="bio" 
                        name="bio" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Tell students about your expertise and teaching style..."
                        required
                    ><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Create Instructor Account
                    </button>
                    <a href="../index.html" class="btn btn-secondary btn-full">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </form>
            
            <div class="login-link">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </main>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                toggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                field.type = 'password';
                toggle.innerHTML = '<i class="fas fa-eye"></i>';
            }
        }
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>
