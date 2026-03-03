<?php
/**
 * ConSlot 404 Error Page
 * DCC Consultation Booking Portal
 */

require_once __DIR__ . '/../config/config.php';

$page_title = 'Page Not Found';
$page_description = 'The page you are looking for could not be found';

include __DIR__ . '/../includes/header.php';
?>

<div class="error-page">
    <div class="error-content">
        <div class="error-icon">
            <i class="fas fa-search"></i>
        </div>
        <h1>404 - Page Not Found</h1>
        <p>Sorry, the page you are looking for doesn't exist or has been moved.</p>
        
        <div class="error-actions">
            <a href="<?php echo APP_URL; ?>/index.php" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Go to Homepage
            </a>
            <button class="btn btn-outline" onclick="history.back()">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </button>
        </div>
        
        <div class="error-suggestions">
            <h3>You might be looking for:</h3>
            <ul>
                <?php if (isLoggedIn()): ?>
                    <?php if ($current_user['role'] === 'admin'): ?>
                        <li><a href="<?php echo APP_URL; ?>/admin/dashboard.php">Admin Dashboard</a></li>
                        <li><a href="<?php echo APP_URL; ?>/admin/manage_slots.php">Manage Slots</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo APP_URL; ?>/student/dashboard.php">Student Dashboard</a></li>
                        <li><a href="<?php echo APP_URL; ?>/student/book_slot.php">Book a Consultation</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="<?php echo APP_URL; ?>/auth/login.php">Login</a></li>
                    <li><a href="<?php echo APP_URL; ?>/auth/register.php">Consult Now</a></li>
                <?php endif; ?>
                <li><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
            </ul>
        </div>
    </div>
</div>

<style>
.error-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: var(--spacing-lg);
}

.error-content {
    max-width: 600px;
}

.error-icon {
    font-size: 6rem;
    color: var(--primary-color);
    margin-bottom: var(--spacing-lg);
    opacity: 0.5;
}

.error-content h1 {
    font-size: var(--font-size-3xl);
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
}

.error-content p {
    font-size: var(--font-size-lg);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
}

.error-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
}

.error-suggestions {
    text-align: left;
    background: var(--bg-light);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    margin-top: var(--spacing-lg);
}

.error-suggestions h3 {
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.error-suggestions ul {
    list-style: none;
    padding: 0;
}

.error-suggestions li {
    margin-bottom: var(--spacing-sm);
}

.error-suggestions a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.error-suggestions a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .error-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .error-icon {
        font-size: 4rem;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
