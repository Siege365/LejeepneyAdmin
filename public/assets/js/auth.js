/**
 * Auth JavaScript - Login/Register/Forgot Password
 * Lejeepney Admin Panel - Enhanced UX
 */

document.addEventListener('DOMContentLoaded', function() {
    initFormValidation();
    initPasswordToggle();
    initFormEnhancements();
    initButtonEffects();
    initInputAnimations();
    initParallaxEffect();
});

/**
 * Form Validation with visual feedback
 */
function initFormValidation() {
    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        setupFormValidation(loginForm);
    }
    
    // Signup Form
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        setupFormValidation(signupForm);
    }
    
    // Forgot Password Form
    const forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        setupFormValidation(forgotForm);
    }
}

/**
 * Setup form validation
 */
function setupFormValidation(form) {
    const inputs = form.querySelectorAll('input[required], input[type="email"], input[type="password"]');
    
    // Real-time validation
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInput(this);
        });
        
        input.addEventListener('input', function() {
            if (this.closest('.input-wrapper')?.classList.contains('error')) {
                validateInput(this);
            }
        });
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateInput(input)) {
                isValid = false;
            }
        });
        
        // Password confirmation check
        const password = form.querySelector('input[name="password"]');
        const passwordConfirm = form.querySelector('input[name="password_confirmation"]');
        
        if (password && passwordConfirm) {
            if (password.value !== passwordConfirm.value) {
                showInputError(passwordConfirm, 'Passwords do not match');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Shake animation for the card
            const card = form.closest('.auth-card');
            if (card) {
                card.classList.add('shake');
                setTimeout(() => card.classList.remove('shake'), 500);
            }
            
            // Focus first invalid input
            const firstInvalid = form.querySelector('.input-wrapper.error input');
            if (firstInvalid) {
                firstInvalid.focus();
            }
            
            showNotification('Please fix the errors before continuing', 'error');
        } else {
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Please wait...';
            }
        }
    });
}

/**
 * Validate single input with visual feedback
 */
function validateInput(input) {
    const value = input.value.trim();
    const type = input.type;
    const name = input.name;
    
    // Required check
    if (input.required && !value) {
        showInputError(input, 'This field is required');
        return false;
    }
    
    // Email validation
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showInputError(input, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Password validation (only for password field, not confirm)
    if (type === 'password' && value && name === 'password') {
        if (value.length < 8) {
            showInputError(input, 'Password must be at least 8 characters');
            return false;
        }
    }
    
    // Name validation
    if (name === 'name' && value) {
        if (value.length < 2) {
            showInputError(input, 'Name must be at least 2 characters');
            return false;
        }
    }
    
    // Show success state for valid input
    if (value) {
        showInputSuccess(input);
    }
    
    return true;
}

/**
 * Show input error state
 */
function showInputError(input, message) {
    const wrapper = input.closest('.input-wrapper');
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup?.querySelector('.error-message');
    
    if (wrapper) {
        wrapper.classList.remove('success');
        wrapper.classList.add('error');
    }
    
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
        errorElement.style.animation = 'slideDown 0.2s ease forwards';
    }
}

/**
 * Show input success state
 */
function showInputSuccess(input) {
    const wrapper = input.closest('.input-wrapper');
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup?.querySelector('.error-message');
    
    if (wrapper) {
        wrapper.classList.remove('error');
        wrapper.classList.add('success');
    }
    
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
}

/**
 * Clear input state
 */
function clearInputState(input) {
    const wrapper = input.closest('.input-wrapper');
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup?.querySelector('.error-message');
    
    if (wrapper) {
        wrapper.classList.remove('error', 'success');
    }
    
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }
}

/**
 * Password Toggle with animation
 */
function initPasswordToggle() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const wrapper = this.closest('.input-wrapper');
            const input = wrapper?.querySelector('input');
            const icon = this.querySelector('i');
            
            if (!input || !icon) return;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('title', 'Hide password');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('title', 'Show password');
            }
            
            // Keep focus on input
            input.focus();
            
            // Button pulse effect
            this.style.transform = 'translateY(-50%) scale(1.15)';
            setTimeout(() => {
                this.style.transform = 'translateY(-50%) scale(1)';
            }, 150);
        });
    });
}

/**
 * Form Enhancements
 */
function initFormEnhancements() {
    // Auto-focus first input with delay for animation
    const firstInput = document.querySelector('.auth-form input:not([type="hidden"])');
    if (firstInput) {
        setTimeout(() => {
            firstInput.focus();
        }, 600);
    }
    
    // Remember email functionality
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        const emailInput = loginForm.querySelector('input[name="email"], input[type="email"]');
        const rememberCheck = loginForm.querySelector('input[name="remember"]');
        
        // Load saved email
        const savedEmail = localStorage.getItem('lejeepney_remembered_email');
        if (savedEmail && emailInput) {
            emailInput.value = savedEmail;
            if (rememberCheck) rememberCheck.checked = true;
            
            // Move to password field
            const passwordInput = loginForm.querySelector('input[type="password"]');
            if (passwordInput) {
                setTimeout(() => passwordInput.focus(), 600);
            }
        }
        
        // Save email on submit
        loginForm.addEventListener('submit', function() {
            if (rememberCheck?.checked && emailInput) {
                localStorage.setItem('lejeepney_remembered_email', emailInput.value);
            } else {
                localStorage.removeItem('lejeepney_remembered_email');
            }
        });
    }
    
    // Caps Lock warning
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('keyup', function(e) {
            const capsWarning = this.closest('.form-group')?.querySelector('.caps-warning');
            
            if (e.getModifierState && e.getModifierState('CapsLock')) {
                if (!capsWarning) {
                    const warning = document.createElement('div');
                    warning.className = 'caps-warning';
                    warning.innerHTML = '<i class="fa-solid fa-exclamation-triangle"></i> Caps Lock is on';
                    warning.style.cssText = `
                        color: #F59E0B;
                        font-size: 0.75rem;
                        margin-top: 0.5rem;
                        display: flex;
                        align-items: center;
                        gap: 0.375rem;
                        animation: slideDown 0.2s ease;
                    `;
                    this.closest('.form-group')?.appendChild(warning);
                }
            } else if (capsWarning) {
                capsWarning.remove();
            }
        });
    });
}

/**
 * Button Ripple Effect
 */
function initButtonEffects() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Don't add ripple if disabled
            if (this.disabled) return;
            
            // Create ripple
            const ripple = document.createElement('span');
            ripple.className = 'ripple-effect';
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height) * 2;
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.4);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out forwards;
                pointer-events: none;
            `;
            
            // Ensure button has relative positioning
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Add ripple animation if not exists
    if (!document.getElementById('rippleStyles')) {
        const style = document.createElement('style');
        style.id = 'rippleStyles';
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(1);
                    opacity: 0;
                }
            }
            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-5px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .shake {
                animation: shake 0.5s ease-in-out !important;
            }
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Input Focus Animations
 */
function initInputAnimations() {
    const inputs = document.querySelectorAll('.form-group input');
    
    inputs.forEach(input => {
        const wrapper = input.closest('.input-wrapper');
        
        if (wrapper) {
            input.addEventListener('focus', function() {
                wrapper.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                wrapper.classList.remove('focused');
            });
        }
    });
}

/**
 * Subtle Parallax Effect on Background
 */
function initParallaxEffect() {
    const floatingElements = document.querySelectorAll('.floating-element');
    
    if (floatingElements.length === 0) return;
    
    // Mouse movement parallax
    let mouseX = 0;
    let mouseY = 0;
    let targetX = 0;
    let targetY = 0;
    
    document.addEventListener('mousemove', function(e) {
        mouseX = (e.clientX / window.innerWidth - 0.5) * 20;
        mouseY = (e.clientY / window.innerHeight - 0.5) * 20;
    });
    
    function animate() {
        targetX += (mouseX - targetX) * 0.05;
        targetY += (mouseY - targetY) * 0.05;
        
        floatingElements.forEach((el, index) => {
            const speed = 1 + (index * 0.3);
            const x = targetX * speed;
            const y = targetY * speed;
            el.style.transform = `translate(${x}px, ${y}px)`;
        });
        
        requestAnimationFrame(animate);
    }
    
    animate();
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification-toast').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    
    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const colorMap = {
        success: '#10B981',
        error: '#DC3545',
        warning: '#F59E0B',
        info: '#0C4E94'
    };
    
    notification.innerHTML = `
        <i class="fa-solid ${iconMap[type] || iconMap.info}"></i>
        <span>${message}</span>
    `;
    notification.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 1rem 1.5rem;
        background: ${colorMap[type] || colorMap.info};
        color: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 9999;
        transform: translateX(150%);
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        font-weight: 500;
        font-size: 0.9375rem;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    requestAnimationFrame(() => {
        notification.style.transform = 'translateX(0)';
    });
    
    // Remove after delay
    setTimeout(() => {
        notification.style.transform = 'translateX(150%)';
        setTimeout(() => notification.remove(), 400);
    }, 4000);
}

// Expose function globally
window.showNotification = showNotification;
