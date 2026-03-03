<?php
/**
 * ConSlot Footer Template
 * DCC Consultation Booking Portal
 */

// Check if this is included from the main config
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-brand">
                        <i class="fas fa-calendar-check"></i>
                        <span><?php echo APP_NAME; ?></span>
                    </div>
                    <p class="footer-description">
                        Your trusted DCC consultation booking portal. 
                        Smart scheduling for academic success.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="social-link" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo APP_URL; ?>/index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li><a href="<?php echo APP_URL; ?>/about.php"><i class="fas fa-info-circle"></i> About</a></li>
                        <li><a href="<?php echo APP_URL; ?>/features.php"><i class="fas fa-star"></i> Features</a></li>
                        <li><a href="<?php echo APP_URL; ?>/contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo APP_URL; ?>/help.php"><i class="fas fa-question-circle"></i> Help Center</a></li>
                        <li><a href="<?php echo APP_URL; ?>/faq.php"><i class="fas fa-comments"></i> FAQ</a></li>
                        <li><a href="<?php echo APP_URL; ?>/privacy.php"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
                        <li><a href="<?php echo APP_URL; ?>/terms.php"><i class="fas fa-file-contract"></i> Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <div class="footer-contact">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>support@conslot.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+1 (555) 123-4567</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>DCC Campus, Building A</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="footer-bottom-links">
                    <a href="<?php echo APP_URL; ?>/privacy.php">Privacy</a>
                    <a href="<?php echo APP_URL; ?>/terms.php">Terms</a>
                    <a href="<?php echo APP_URL; ?>/sitemap.php">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- JavaScript -->
    <script src="<?php echo APP_URL; ?>/js/script.js"></script>
    
    <?php if (DEBUG_MODE): ?>
    <!-- Debug Information (only shown in debug mode) -->
    <div class="debug-info">
        <details>
            <summary>Debug Information</summary>
            <div class="debug-content">
                <p><strong>Page Load Time:</strong> <?php echo round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4); ?>s</p>
                <p><strong>Memory Usage:</strong> <?php echo round(memory_get_usage() / 1024 / 1024, 2); ?>MB</p>
                <p><strong>User Agent:</strong> <?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'); ?></p>
                <p><strong>Remote IP:</strong> <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?></p>
                <?php if (isLoggedIn()): ?>
                    <p><strong>Logged User:</strong> <?php echo htmlspecialchars($current_user['name'] ?? 'Unknown'); ?> (<?php echo $current_user['role'] ?? 'Unknown'; ?>)</p>
                <?php endif; ?>
            </div>
        </details>
    </div>
    <?php endif; ?>

    <script>
        // Back to top functionality
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/hide back to top button based on scroll position
        window.addEventListener('scroll', function() {
            const backToTopBtn = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });

        // Form validation helpers
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
            let isValid = true;

            inputs.forEach(function(input) {
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                }
            });

            return isValid;
        }

        // Loading state for forms
        function setFormLoading(formId, loading = true) {
            const form = document.getElementById(formId);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            if (loading) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // Copy to clipboard helper
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                button.classList.add('success');
                
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.classList.remove('success');
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
            });
        }

        // Print function
        function printPage() {
            window.print();
        }

        // Export to CSV helper
        function exportToCSV(data, filename) {
            const csv = convertToCSV(data);
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('hidden', '');
            a.setAttribute('href', url);
            a.setAttribute('download', filename);
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function convertToCSV(data) {
            if (!data || data.length === 0) return '';
            
            const headers = Object.keys(data[0]);
            const csvHeaders = headers.join(',');
            
            const csvRows = data.map(row => {
                return headers.map(header => {
                    const value = row[header] || '';
                    return `"${value.toString().replace(/"/g, '""')}"`;
                }).join(',');
            });
            
            return [csvHeaders, ...csvRows].join('\n');
        }

        // Initialize tooltips if needed
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipElements = document.querySelectorAll('[data-tooltip]');
            tooltipElements.forEach(function(element) {
                element.addEventListener('mouseenter', function() {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = this.getAttribute('data-tooltip');
                    document.body.appendChild(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                    
                    this.tooltip = tooltip;
                });
                
                element.addEventListener('mouseleave', function() {
                    if (this.tooltip) {
                        this.tooltip.remove();
                        delete this.tooltip;
                    }
                });
            });
        });
    </script>
</body>
</html>
