// Missing JavaScript functions for ConSlot animations and interactions

// Modal screen management functions
function showChoiceScreen() {
    document.getElementById('choiceScreen').style.display = 'block';
    document.getElementById('registerScreen').style.display = 'none';
    document.getElementById('registerDivider').style.display = 'none';
    document.getElementById('registerGoogle').style.display = 'none';
    document.querySelector('.modal-header h3').textContent = 'Welcome to ConSlot!';
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

function closeInstructorLoginModal() {
    const modal = document.getElementById('instructorLoginModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

function closeInstructorRegisterModal() {
    const modal = document.getElementById('instructorRegisterModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

function switchToRegister() {
    closeLoginModal();
    showRegistrationModal();
    showRegisterScreen();
}

// Additional animation functions for better user experience
function addScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe feature cards
    document.querySelectorAll('.feature-card').forEach(card => {
        card.style.opacity = '0';
        observer.observe(card);
    });

    // Observe stat items
    document.querySelectorAll('.stat-item').forEach(item => {
        item.style.opacity = '0';
        observer.observe(item);
    });
}

// Parallax effect for hero section
function addParallaxEffect() {
    const hero = document.querySelector('.hero');
    if (hero) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = hero.querySelector('.hero-content');
            if (parallax) {
                parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });
    }
}

// Smooth reveal animation for elements
function animateOnScroll() {
    const elements = document.querySelectorAll('.hero-text h1, .hero-text p, .hero-actions');
    elements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, index * 200);
    });
}

// Enhanced floating animation for logo
function enhanceLogoAnimation() {
    const logo = document.querySelector('.conslot-logo');
    if (logo) {
        logo.addEventListener('mouseenter', () => {
            logo.style.animation = 'float 1s ease-in-out infinite, pulse 0.5s ease-in-out';
        });
        
        logo.addEventListener('mouseleave', () => {
            logo.style.animation = 'float 3s ease-in-out infinite';
        });
    }
}

// Add pulse animation
const pulseStyle = document.createElement('style');
pulseStyle.textContent = `
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
`;
document.head.appendChild(pulseStyle);

// Initialize animations when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    addScrollAnimations();
    addParallaxEffect();
    animateOnScroll();
    enhanceLogoAnimation();
    
    // Add smooth hover effects to buttons
    document.querySelectorAll('.btn-cta, .btn-secondary, .btn-instructor-register').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add staggered animation to feature cards on hover
    document.querySelectorAll('.feature-card').forEach((card, index) => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
    });
});

// Add loading animation for forms
function showLoadingState(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.disabled = true;
    
    return () => {
        button.innerHTML = originalText;
        button.disabled = false;
    };
}

// Add form shake animation for validation errors
function shakeForm(element) {
    element.style.animation = 'shake 0.5s';
    setTimeout(() => {
        element.style.animation = '';
    }, 500);
}

// Add shake keyframe
const shakeStyle = document.createElement('style');
shakeStyle.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(shakeStyle);

// Enhanced notification system with animations
function showEnhancedNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `enhanced-notification enhanced-notification-${type}`;
    notification.innerHTML = `
        <div class="notification-icon">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        </div>
        <div class="notification-content">
            <span class="notification-message">${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add enhanced styles
    const notificationStyles = document.createElement('style');
    notificationStyles.textContent = `
        .enhanced-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            max-width: 380px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInEnhanced 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            backdrop-filter: blur(10px);
        }
        
        .enhanced-notification-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(32, 201, 151, 0.9));
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .enhanced-notification-error {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.9), rgba(200, 35, 51, 0.9));
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .enhanced-notification-warning {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.9), rgba(224, 168, 0, 0.9));
            border: 1px solid rgba(255, 193, 7, 0.3);
            color: #333;
        }
        
        .notification-icon {
            font-size: 20px;
            opacity: 0.9;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 16px;
            opacity: 0.7;
            transition: opacity 0.2s;
            padding: 4px;
        }
        
        .notification-close:hover {
            opacity: 1;
        }
        
        @keyframes slideInEnhanced {
            from {
                transform: translateX(100%) rotateY(90deg);
                opacity: 0;
            }
            to {
                transform: translateX(0) rotateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutEnhanced {
            from {
                transform: translateX(0) rotateY(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%) rotateY(-90deg);
                opacity: 0;
            }
        }
    `;
    
    if (!document.querySelector('style[data-enhanced-notifications]')) {
        notificationStyles.setAttribute('data-enhanced-notifications', 'true');
        document.head.appendChild(notificationStyles);
    }
    
    document.body.appendChild(notification);
    
    // Auto remove with enhanced animation
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutEnhanced 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            setTimeout(() => notification.remove(), 400);
        }
    }, 5000);
}

// Add smooth page transitions
function addPageTransitions() {
    // Don't set body opacity to 0 initially, it causes the empty screen issue
    document.body.style.transition = 'opacity 0.5s ease-in-out';
    
    window.addEventListener('load', () => {
        document.body.style.opacity = '1';
    });
    
    // Add transition to all links
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Skip modal triggers and anchors with onclick handlers
            if (this.onclick || this.getAttribute('href') === '#') {
                return;
            }
            
            e.preventDefault();
            const href = this.getAttribute('href');
            
            document.body.style.opacity = '0';
            setTimeout(() => {
                window.location.href = href;
            }, 300);
        });
    });
}

// Initialize page transitions - commented out to prevent empty screen issue
// addPageTransitions();

console.log('ConSlot animations and interactions loaded successfully!');
