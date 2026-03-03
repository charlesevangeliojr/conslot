<?php
/**
 * ConSlot Header Template
 * DCC Consultation Booking Portal
 */

// Check if this is included from the main config
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : APP_DESCRIPTION; ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>/assets/images/favicon.ico">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="<?php echo APP_URL; ?>/index.php" class="brand-link">
                        <i class="fas fa-calendar-check"></i>
                        <span><?php echo APP_NAME; ?></span>
                    </a>
                </div>
                
                <div class="nav-menu">
                    <?php if (isLoggedIn()): ?>
                        <div class="nav-links">
                            <a href="<?php echo APP_URL; ?>/index.php" class="nav-link">
                                <i class="fas fa-home"></i>
                                <span>Home</span>
                            </a>
                            
                            <?php if ($current_user['role'] === 'admin'): ?>
                                <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="nav-link">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/admin/manage_slots.php" class="nav-link">
                                    <i class="fas fa-clock"></i>
                                    <span>Manage Slots</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/admin/manage_users.php" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <span>Manage Users</span>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo APP_URL; ?>/student/dashboard.php" class="nav-link">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/student/book_slot.php" class="nav-link">
                                    <i class="fas fa-calendar-plus"></i>
                                    <span>Book Slot</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/student/my_bookings.php" class="nav-link">
                                    <i class="fas fa-list"></i>
                                    <span>My Bookings</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nav-user">
                            <div class="user-dropdown">
                                <button class="user-dropdown-btn" onclick="toggleUserDropdown()">
                                    <i class="fas fa-user-circle"></i>
                                    <span><?php echo htmlspecialchars($current_user['name']); ?></span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                
                                <div class="user-dropdown-menu" id="userDropdown">
                                    <div class="user-info">
                                        <div class="user-name"><?php echo htmlspecialchars($current_user['name']); ?></div>
                                        <div class="user-email"><?php echo htmlspecialchars($current_user['email']); ?></div>
                                        <div class="user-role">
                                            <span class="role-badge role-<?php echo $current_user['role']; ?>">
                                                <?php echo ucfirst($current_user['role']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="dropdown-divider"></div>
                                    
                                    <a href="<?php echo APP_URL; ?>/profile.php" class="dropdown-item">
                                        <i class="fas fa-user"></i>
                                        Profile
                                    </a>
                                    
                                    <a href="<?php echo APP_URL; ?>/settings.php" class="dropdown-item">
                                        <i class="fas fa-cog"></i>
                                        Settings
                                    </a>
                                    
                                    <div class="dropdown-divider"></div>
                                    
                                    <a href="<?php echo APP_URL; ?>/auth/logout.php" class="dropdown-item logout">
                                        <i class="fas fa-sign-out-alt"></i>
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="nav-links">
                            <a href="<?php echo APP_URL; ?>/index.php" class="nav-link">
                                <i class="fas fa-home"></i>
                                <span>Home</span>
                            </a>
                            <a href="<?php echo APP_URL; ?>/auth/login.php" class="nav-link">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Login</span>
                            </a>
                            <a href="<?php echo APP_URL; ?>/auth/register.php" class="nav-link">
                                <i class="fas fa-user-plus"></i>
                                <span>Consult Now</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile menu toggle -->
                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Flash Messages -->
    <div class="flash-messages">
        <?php echo displayFlashMessages(); ?>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <?php if (isset($page_title) && $page_title !== 'Home'): ?>
        <div class="page-header">
            <div class="container">
                <h1 class="page-title"><?php echo htmlspecialchars($page_title); ?></h1>
                <?php if (isset($page_subtitle)): ?>
                <p class="page-subtitle"><?php echo htmlspecialchars($page_subtitle); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="container">
            <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
            <nav class="breadcrumbs">
                <ol class="breadcrumb">
                    <li><a href="<?php echo APP_URL; ?>/index.php"><i class="fas fa-home"></i></a></li>
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <?php if ($index < count($breadcrumbs) - 1): ?>
                            <li><a href="<?php echo $crumb['url']; ?>"><?php echo htmlspecialchars($crumb['title']); ?></a></li>
                        <?php else: ?>
                            <li class="active"><?php echo htmlspecialchars($crumb['title']); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Global JavaScript variables
        const APP_URL = '<?php echo APP_URL; ?>';
        const CSRF_TOKEN = '<?php echo generateCSRFToken(); ?>';
        
        // User dropdown toggle
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const navbar = document.querySelector('.navbar');
            navbar.classList.toggle('mobile-menu-open');
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const userDropdown = document.querySelector('.user-dropdown');
            if (!userDropdown.contains(event.target)) {
                document.getElementById('userDropdown').classList.remove('show');
            }
        });
        
        // AJAX helper function
        function ajaxRequest(url, method, data, callback) {
            const xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        callback(response);
                    } catch (e) {
                        callback({success: false, message: 'Invalid server response'});
                    }
                } else {
                    callback({success: false, message: 'Request failed'});
                }
            };
            
            xhr.onerror = function() {
                callback({success: false, message: 'Network error'});
            };
            
            const params = typeof data === 'string' ? data : Object.keys(data).map(key => encodeURIComponent(key) + '=' + encodeURIComponent(data[key])).join('&');
            xhr.send(params);
        }
        
        // Show loading spinner
        function showLoading(element) {
            element.disabled = true;
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        }
        
        // Hide loading spinner
        function hideLoading(element, originalText) {
            element.disabled = false;
            element.innerHTML = originalText;
        }
        
        // Show confirmation dialog
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
    </script>
