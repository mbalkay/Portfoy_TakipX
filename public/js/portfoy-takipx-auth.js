/**
 * Next-Generation Authentication JavaScript for Portföy TakipX
 * Modern, interactive authentication with real-time validation
 */

class PortfoyAuth {
    constructor() {
        this.initializeElements();
        this.bindEvents();
        this.initializeValidation();
        this.createParticleEffect();
    }

    initializeElements() {
        this.loginForm = document.getElementById('portfoy-login-form');
        this.registerForm = document.getElementById('portfoy-register-form');
        this.loginContainer = document.getElementById('login-form-container');
        this.registerContainer = document.getElementById('register-form-container');
        this.authTabs = document.querySelectorAll('.auth-tab');
        this.passwordToggles = document.querySelectorAll('.password-toggle');
        this.successMessage = document.getElementById('auth-success-message');
        this.errorMessage = document.getElementById('auth-error-message');
        this.loadingOverlay = document.getElementById('auth-loading-overlay');
    }

    bindEvents() {
        // Tab switching
        this.authTabs.forEach(tab => {
            tab.addEventListener('click', (e) => this.switchTab(e.target.dataset.tab));
        });

        // Form submissions
        if (this.loginForm) {
            this.loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        if (this.registerForm) {
            this.registerForm.addEventListener('submit', (e) => this.handleRegister(e));
        }

        // Password toggles
        this.passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => this.togglePassword(e.target.closest('.password-toggle')));
        });

        // Real-time validation
        this.initializeRealTimeValidation();

        // Error retry button
        const retryBtn = document.querySelector('.error-retry-btn');
        if (retryBtn) {
            retryBtn.addEventListener('click', () => this.hideMessage());
        }

        // Social login buttons
        this.initializeSocialLogin();

        // Keyboard navigation
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));
    }

    switchTab(tabName) {
        // Update tab states
        this.authTabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        // Show/hide form containers with animation
        if (tabName === 'login') {
            this.animateFormSwitch(this.registerContainer, this.loginContainer);
        } else {
            this.animateFormSwitch(this.loginContainer, this.registerContainer);
        }

        // Focus first input
        setTimeout(() => {
            const firstInput = document.querySelector(`#${tabName}-form-container .form-input`);
            if (firstInput) firstInput.focus();
        }, 300);
    }

    animateFormSwitch(hideContainer, showContainer) {
        hideContainer.style.opacity = '0';
        hideContainer.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            hideContainer.style.display = 'none';
            showContainer.style.display = 'block';
            showContainer.style.opacity = '0';
            showContainer.style.transform = 'translateX(20px)';
            
            // Trigger animation
            setTimeout(() => {
                showContainer.style.opacity = '1';
                showContainer.style.transform = 'translateX(0)';
            }, 50);
        }, 150);
    }

    togglePassword(toggle) {
        const targetId = toggle.dataset.target;
        const input = document.getElementById(targetId);
        const showIcon = toggle.querySelector('.show-icon');
        const hideIcon = toggle.querySelector('.hide-icon');

        if (input.type === 'password') {
            input.type = 'text';
            showIcon.style.display = 'none';
            hideIcon.style.display = 'block';
        } else {
            input.type = 'password';
            showIcon.style.display = 'block';
            hideIcon.style.display = 'none';
        }

        // Add animation effect
        toggle.style.transform = 'scale(0.9)';
        setTimeout(() => {
            toggle.style.transform = 'scale(1)';
        }, 100);
    }

    async handleLogin(e) {
        e.preventDefault();
        
        const formData = new FormData(this.loginForm);
        const submitBtn = this.loginForm.querySelector('.auth-submit-btn');
        
        this.setButtonLoading(submitBtn, true);
        
        try {
            const response = await this.makeRequest('portfoy_login_user', {
                username: formData.get('username'),
                password: formData.get('password'),
                remember: formData.get('remember') || 'false',
                nonce: window.portfoyAuthNonce
            });

            if (response.success) {
                this.showSuccessMessage(
                    'Giriş Başarılı!',
                    response.data.message
                );
                
                // Store user data
                localStorage.setItem('portfoy_user', JSON.stringify(response.data.user_data));
                
                // Redirect after delay
                setTimeout(() => {
                    window.location.href = response.data.redirect_url;
                }, 1500);
            } else {
                throw new Error(response.data.message);
            }
        } catch (error) {
            this.showErrorMessage(error.message);
            this.shakeForm(this.loginForm);
        } finally {
            this.setButtonLoading(submitBtn, false);
        }
    }

    async handleRegister(e) {
        e.preventDefault();
        
        if (!this.validateRegistrationForm()) {
            return;
        }
        
        const formData = new FormData(this.registerForm);
        const submitBtn = this.registerForm.querySelector('.auth-submit-btn');
        
        this.setButtonLoading(submitBtn, true);
        
        try {
            const response = await this.makeRequest('portfoy_register_user', {
                username: formData.get('username'),
                email: formData.get('email'),
                password: formData.get('password'),
                confirm_password: formData.get('confirm_password'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                nonce: window.portfoyAuthNonce
            });

            if (response.success) {
                this.showSuccessMessage(
                    'Kayıt Başarılı!',
                    response.data.message,
                    true
                );
                
                // Switch to login form after success
                setTimeout(() => {
                    this.switchTab('login');
                    this.hideMessage();
                }, 3000);
            } else {
                throw new Error(response.data.message);
            }
        } catch (error) {
            this.showErrorMessage(error.message);
            this.shakeForm(this.registerForm);
        } finally {
            this.setButtonLoading(submitBtn, false);
        }
    }

    validateRegistrationForm() {
        const password = document.getElementById('register-password').value;
        const confirmPassword = document.getElementById('register-confirm-password').value;
        
        if (password !== confirmPassword) {
            this.showInputError('register-confirm-password', 'Şifreler eşleşmiyor');
            return false;
        }
        
        if (!this.isStrongPassword(password)) {
            this.showInputError('register-password', 'Şifre yeterince güçlü değil');
            return false;
        }
        
        return true;
    }

    initializeRealTimeValidation() {
        // Username validation
        const usernameInput = document.getElementById('register-username');
        if (usernameInput) {
            usernameInput.addEventListener('input', this.debounce(() => {
                this.validateUsername(usernameInput.value);
            }, 500));
        }

        // Email validation
        const emailInput = document.getElementById('register-email');
        if (emailInput) {
            emailInput.addEventListener('input', this.debounce(() => {
                this.validateEmail(emailInput.value);
            }, 300));
        }

        // Password strength
        const passwordInput = document.getElementById('register-password');
        if (passwordInput) {
            passwordInput.addEventListener('input', () => {
                this.updatePasswordStrength(passwordInput.value);
            });
        }

        // Confirm password
        const confirmPasswordInput = document.getElementById('register-confirm-password');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', () => {
                this.validatePasswordMatch();
            });
        }
    }

    async validateUsername(username) {
        const validationDiv = document.getElementById('username-validation');
        
        if (username.length < 3) {
            this.showValidationMessage(validationDiv, 'Kullanıcı adı en az 3 karakter olmalıdır', 'error');
            return;
        }

        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            this.showValidationMessage(validationDiv, 'Sadece harf, rakam ve alt çizgi kullanılabilir', 'error');
            return;
        }

        // Check availability (mock - would be real API call)
        this.showValidationMessage(validationDiv, '✓ Kullanıcı adı uygun', 'success');
    }

    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const input = document.getElementById('register-email');
        
        if (!emailRegex.test(email)) {
            input.classList.add('error');
            return false;
        }
        
        input.classList.remove('error');
        return true;
    }

    updatePasswordStrength(password) {
        const strengthDiv = document.getElementById('password-strength');
        if (!strengthDiv) return;

        const strength = this.calculatePasswordStrength(password);
        
        let strengthHtml = '<div class="strength-bar"><div class="strength-fill ' + strength.level + '"></div></div>';
        strengthHtml += '<div class="strength-text ' + strength.level + '">' + strength.text + '</div>';
        
        strengthDiv.innerHTML = strengthHtml;
    }

    calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        const levels = [
            { level: 'weak', text: 'Zayıf' },
            { level: 'weak', text: 'Zayıf' },
            { level: 'medium', text: 'Orta' },
            { level: 'medium', text: 'Orta' },
            { level: 'strong', text: 'Güçlü' },
            { level: 'very-strong', text: 'Çok Güçlü' },
            { level: 'very-strong', text: 'Mükemmel' }
        ];
        
        return levels[Math.min(score, 6)];
    }

    isStrongPassword(password) {
        return password.length >= 8 && 
               /[a-z]/.test(password) && 
               /[A-Z]/.test(password) && 
               /[0-9]/.test(password);
    }

    validatePasswordMatch() {
        const password = document.getElementById('register-password').value;
        const confirmPassword = document.getElementById('register-confirm-password').value;
        const validationDiv = document.getElementById('confirm-password-validation');
        
        if (confirmPassword === '') {
            validationDiv.innerHTML = '';
            return;
        }
        
        if (password === confirmPassword) {
            this.showValidationMessage(validationDiv, '✓ Şifreler eşleşiyor', 'success');
        } else {
            this.showValidationMessage(validationDiv, 'Şifreler eşleşmiyor', 'error');
        }
    }

    showValidationMessage(element, message, type) {
        element.className = `input-validation ${type}`;
        element.textContent = message;
    }

    showInputError(inputId, message) {
        const input = document.getElementById(inputId);
        input.classList.add('error');
        
        // Add shake animation
        input.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            input.style.animation = '';
        }, 500);
    }

    setButtonLoading(button, loading) {
        const btnText = button.querySelector('.btn-text');
        const btnLoading = button.querySelector('.btn-loading');
        
        if (loading) {
            btnText.style.display = 'none';
            btnLoading.style.display = 'flex';
            button.disabled = true;
        } else {
            btnText.style.display = 'flex';
            btnLoading.style.display = 'none';
            button.disabled = false;
        }
    }

    showSuccessMessage(title, message, autoHide = false) {
        const titleEl = this.successMessage.querySelector('.success-title');
        const descEl = this.successMessage.querySelector('.success-description');
        
        titleEl.textContent = title;
        descEl.textContent = message;
        
        this.successMessage.style.display = 'block';
        this.loadingOverlay.style.display = 'flex';
        
        if (autoHide) {
            setTimeout(() => this.hideMessage(), 3000);
        }
    }

    showErrorMessage(message) {
        const descEl = this.errorMessage.querySelector('.error-description');
        descEl.textContent = message;
        
        this.errorMessage.style.display = 'block';
        this.loadingOverlay.style.display = 'flex';
        
        // Auto hide after 5 seconds
        setTimeout(() => this.hideMessage(), 5000);
    }

    hideMessage() {
        this.successMessage.style.display = 'none';
        this.errorMessage.style.display = 'none';
        this.loadingOverlay.style.display = 'none';
    }

    shakeForm(form) {
        form.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            form.style.animation = '';
        }, 500);
    }

    async makeRequest(action, data) {
        const response = await fetch(window.portfoyAjaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: action,
                ...data
            })
        });
        
        if (!response.ok) {
            throw new Error('Ağ hatası oluştu');
        }
        
        return await response.json();
    }

    debounce(func, wait) {
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

    initializeSocialLogin() {
        const googleBtn = document.querySelector('.google-btn');
        if (googleBtn) {
            googleBtn.addEventListener('click', () => {
                // Implement Google OAuth
                this.showErrorMessage('Google girişi henüz aktif değil');
            });
        }
    }

    createParticleEffect() {
        // Enhanced particle effect
        const particles = document.querySelector('.auth-particles');
        if (!particles) return;

        // Add floating elements
        for (let i = 0; i < 10; i++) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 4 + 2}px;
                height: ${Math.random() * 4 + 2}px;
                background: rgba(255, 255, 255, ${Math.random() * 0.5 + 0.2});
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                animation: float ${Math.random() * 3 + 2}s ease-in-out infinite alternate;
            `;
            particles.appendChild(particle);
        }

        // Add CSS animation for floating
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0% { transform: translateY(0px) rotate(0deg); opacity: 0.7; }
                100% { transform: translateY(-20px) rotate(180deg); opacity: 0.3; }
            }
        `;
        document.head.appendChild(style);
    }

    handleKeyboard(e) {
        // ESC to close messages
        if (e.key === 'Escape') {
            this.hideMessage();
        }
        
        // Tab navigation enhancement
        if (e.key === 'Tab') {
            // Custom tab handling if needed
        }
        
        // Enter to submit forms
        if (e.key === 'Enter' && e.target.classList.contains('form-input')) {
            const form = e.target.closest('form');
            if (form) {
                form.dispatchEvent(new Event('submit'));
            }
        }
    }

    // Auto-save form data
    initializeAutoSave() {
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                localStorage.setItem(`portfoy_form_${input.name}`, input.value);
            });
            
            // Restore saved data
            const saved = localStorage.getItem(`portfoy_form_${input.name}`);
            if (saved) {
                input.value = saved;
            }
        });
    }

    // Clear saved form data
    clearAutoSave() {
        const keys = Object.keys(localStorage).filter(key => key.startsWith('portfoy_form_'));
        keys.forEach(key => localStorage.removeItem(key));
    }
}

// Initialize authentication system when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if we're on the auth page
    if (document.querySelector('.portfoy-auth-container')) {
        new PortfoyAuth();
    }
});

// Enhanced form animations
document.addEventListener('DOMContentLoaded', () => {
    // Add focus animations to inputs
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', () => {
            input.closest('.input-wrapper').style.transform = 'scale(1.02)';
            input.closest('.input-wrapper').style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', () => {
            input.closest('.input-wrapper').style.transform = 'scale(1)';
        });
    });

    // Add ripple effect to buttons
    document.querySelectorAll('.auth-submit-btn, .social-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            button.style.position = 'relative';
            button.style.overflow = 'hidden';
            button.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // Add ripple animation CSS
    const rippleStyle = document.createElement('style');
    rippleStyle.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(rippleStyle);
});

// Export for external use
window.PortfoyAuth = PortfoyAuth;