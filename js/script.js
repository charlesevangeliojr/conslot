/**
 * ConSlot JavaScript
 * DCC Consultation Booking Portal
 */

// Global variables
let currentTheme = localStorage.getItem('theme') || 'light';
let notifications = [];

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
function initializeApp() {
    // Initialize theme
    initializeTheme();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize auto-refresh
    initializeAutoRefresh();
    
    // Initialize keyboard shortcuts
    initializeKeyboardShortcuts();
    
    // Initialize notifications
    initializeNotifications();
    
    // Log page view
    logPageView();
}

/**
 * Theme Management
 */
function initializeTheme() {
    // Apply saved theme
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }
    
    // Create theme toggle button if it doesn't exist
    if (!document.querySelector('.theme-toggle')) {
        const themeToggle = document.createElement('button');
        themeToggle.className = 'theme-toggle';
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        themeToggle.onclick = toggleTheme;
        themeToggle.title = 'Toggle dark mode';
        document.body.appendChild(themeToggle);
    }
}

function toggleTheme() {
    currentTheme = currentTheme === 'light' ? 'dark' : 'light';
    document.body.classList.toggle('dark-theme');
    localStorage.setItem('theme', currentTheme);
    
    const themeToggle = document.querySelector('.theme-toggle');
    themeToggle.innerHTML = currentTheme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
}

/**
 * Form Validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showNotification('Please fill in all required fields correctly.', 'error');
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    let isValid = true;
    const value = field.value.trim();
    
    // Remove previous error states
    field.classList.remove('error', 'success');
    
    // Check if required and empty
    if (field.hasAttribute('required') && !value) {
        field.classList.add('error');
        isValid = false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            field.classList.add('error');
            isValid = false;
        }
    }
    
    // Password validation
    if (field.type === 'password' && field.hasAttribute('minlength')) {
        if (value.length < parseInt(field.getAttribute('minlength'))) {
            field.classList.add('error');
            isValid = false;
        }
    }
    
    // Custom validation
    if (isValid && value) {
        field.classList.add('success');
    }
    
    return isValid;
}

/**
 * Tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip(this);
        });
    });
}

function showTooltip(element) {
    const text = element.getAttribute('data-tooltip');
    if (!text) return;
    
    // Remove existing tooltip
    hideTooltip(element);
    
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    
    element.tooltip = tooltip;
}

function hideTooltip(element) {
    if (element.tooltip) {
        element.tooltip.remove();
        delete element.tooltip;
    }
}

/**
 * Auto-refresh functionality
 */
function initializeAutoRefresh() {
    // Check for auto-refresh attributes
    const autoRefreshElements = document.querySelectorAll('[data-auto-refresh]');
    
    autoRefreshElements.forEach(element => {
        const interval = parseInt(element.getAttribute('data-auto-refresh')) || 30000;
        
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                refreshElement(element);
            }
        }, interval);
    });
}

function refreshElement(element) {
    const url = element.getAttribute('data-refresh-url');
    if (url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            element.innerHTML = html;
        })
        .catch(error => {
            console.error('Auto-refresh failed:', error);
        });
    }
}

/**
 * Keyboard Shortcuts
 */
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            focusSearchInput();
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            closeAllModals();
        }
        
        // Ctrl/Cmd + / for keyboard shortcuts help
        if ((e.ctrlKey || e.metaKey) && e.key === '/') {
            e.preventDefault();
            showKeyboardShortcuts();
        }
    });
}

function focusSearchInput() {
    const searchInput = document.querySelector('input[type="search"], input[name="search"]');
    if (searchInput) {
        searchInput.focus();
        searchInput.select();
    }
}

function closeAllModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
}

function showKeyboardShortcuts() {
    const shortcuts = [
        { key: 'Ctrl/Cmd + K', description: 'Focus search' },
        { key: 'Escape', description: 'Close modal' },
        { key: 'Ctrl/Cmd + /', description: 'Show shortcuts' },
        { key: 'Ctrl/Cmd + P', description: 'Print page' }
    ];
    
    let html = '<div class="keyboard-shortcuts"><h3>Keyboard Shortcuts</h3><ul>';
    shortcuts.forEach(shortcut => {
        html += `<li><kbd>${shortcut.key}</kbd> - ${shortcut.description}</li>`;
    });
    html += '</ul></div>';
    
    showModal('Keyboard Shortcuts', html);
}

/**
 * Notifications
 */
function initializeNotifications() {
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

function showNotification(message, type = 'info', duration = 5000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    // Add to container
    let container = document.querySelector('.notifications-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notifications-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(notification);
    
    // Auto-remove
    if (duration > 0) {
        setTimeout(() => {
            notification.remove();
        }, duration);
    }
    
    // Browser notification if permitted
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(APP_NAME, {
            body: message,
            icon: '/assets/images/favicon.ico'
        });
    }
}

/**
 * Modal Management
 */
function showModal(title, content, options = {}) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'flex';
    
    modal.innerHTML = `
        <div class="modal-content ${options.size || ''}">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close" onclick="this.closest('.modal').remove()">×</button>
            </div>
            <div class="modal-body">
                ${content}
            </div>
            ${options.footer ? `<div class="modal-footer">${options.footer}</div>` : ''}
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    return modal;
}

function showConfirm(message, onConfirm, onCancel) {
    const content = `
        <p>${message}</p>
        <div class="form-actions">
            <button class="btn btn-primary" onclick="confirmAction(this)">Confirm</button>
            <button class="btn btn-outline" onclick="this.closest('.modal').remove()">Cancel</button>
        </div>
    `;
    
    const modal = showModal('Confirm Action', content);
    
    window.confirmAction = function(button) {
        modal.remove();
        if (onConfirm) onConfirm();
    };
}

/**
 * AJAX Functions
 */
function ajax(url, options = {}) {
    const defaults = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    };
    
    const config = { ...defaults, ...options };
    
    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

function post(url, data) {
    return ajax(url, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

function get(url, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;
    return ajax(fullUrl);
}

/**
 * Utility Functions
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function formatDate(date, format = 'YYYY-MM-DD') {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const seconds = String(d.getSeconds()).padStart(2, '0');
    
    return format
        .replace('YYYY', year)
        .replace('MM', month)
        .replace('DD', day)
        .replace('HH', hours)
        .replace('mm', minutes)
        .replace('ss', seconds);
}

function timeAgo(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    
    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + ' years ago';
    
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + ' months ago';
    
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + ' days ago';
    
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + ' hours ago';
    
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + ' minutes ago';
    
    return 'Just now';
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Copied to clipboard!', 'success', 2000);
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Copied to clipboard!', 'success', 2000);
    }
}

function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .no-print { display: none; }
            </style>
        </head>
        <body>
            ${element.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

/**
 * Analytics and Logging
 */
function logPageView() {
    if (typeof gtag !== 'undefined') {
        gtag('config', 'GA_MEASUREMENT_ID', {
            page_path: window.location.pathname
        });
    }
    
    // Custom logging
    console.log('Page view:', {
        url: window.location.href,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
        referrer: document.referrer
    });
}

function logEvent(action, category = 'User Interaction', label = null, value = null) {
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            event_category: category,
            event_label: label,
            value: value
        });
    }
    
    // Custom logging
    console.log('Event:', {
        action,
        category,
        label,
        value,
        timestamp: new Date().toISOString()
    });
}

/**
 * Performance Monitoring
 */
function measurePerformance() {
    if ('performance' in window) {
        window.addEventListener('load', function() {
            const perfData = performance.getEntriesByType('navigation')[0];
            const loadTime = perfData.loadEventEnd - perfData.loadEventStart;
            
            console.log('Page load time:', loadTime + 'ms');
            
            // Log slow pages
            if (loadTime > 3000) {
                logEvent('slow_page_load', 'Performance', window.location.pathname, Math.round(loadTime));
            }
        });
    }
}

// Initialize performance monitoring
measurePerformance();

/**
 * Service Worker Registration (for PWA support)
 */
function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registration successful');
            })
            .catch(error => {
                console.log('ServiceWorker registration failed:', error);
            });
    }
}

// Register service worker if available
if (window.location.protocol === 'https:' || window.location.hostname === 'localhost') {
    registerServiceWorker();
}

/**
 * Error Handling
 */
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', {
        message: e.message,
        filename: e.filename,
        lineno: e.lineno,
        colno: e.colno,
        stack: e.error?.stack
    });
    
    // Show user-friendly error message
    showNotification('Something went wrong. Please refresh the page.', 'error');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    
    // Show user-friendly error message
    showNotification('An unexpected error occurred. Please try again.', 'error');
});

/**
 * Feature Detection
 */
const features = {
    touch: 'ontouchstart' in window,
    webgl: !!window.WebGLRenderingContext,
    websockets: 'WebSocket' in window,
    localStorage: 'localStorage' in window,
    serviceWorker: 'serviceWorker' in navigator,
    notifications: 'Notification' in window
};

// Log available features
console.log('Available features:', features);

/**
 * Export global functions
 */
window.ConSlot = {
    showNotification,
    showModal,
    showConfirm,
    ajax,
    post,
    get,
    copyToClipboard,
    printElement,
    logEvent,
    debounce,
    throttle,
    formatDate,
    timeAgo,
    features
};
