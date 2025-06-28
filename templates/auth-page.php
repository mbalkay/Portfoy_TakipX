<?php
/**
 * Modern Authentication Template
 * Next-generation login and registration interface
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="portfoy-auth-container">
    <div class="auth-backdrop">
        <div class="auth-gradient-bg"></div>
        <div class="auth-particles"></div>
    </div>
    
    <div class="auth-content">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand-logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                    <span class="brand-text">Portföy TakipX</span>
                </div>
                <p class="auth-subtitle">Finansal geleceğinizi yönetin</p>
            </div>

            <!-- Login Form -->
            <div class="auth-form-container" id="login-form-container">
                <div class="auth-tabs">
                    <button class="auth-tab active" data-tab="login">Giriş Yap</button>
                    <button class="auth-tab" data-tab="register">Kayıt Ol</button>
                </div>

                <form id="portfoy-login-form" class="auth-form">
                    <div class="form-group">
                        <label for="login-username" class="form-label">
                            <span class="label-text">Kullanıcı Adı veya E-posta</span>
                            <span class="label-required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="login-username" name="username" class="form-input" required>
                            <div class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="login-password" class="form-label">
                            <span class="label-text">Şifre</span>
                            <span class="label-required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" id="login-password" name="password" class="form-input" required>
                            <div class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                                    <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                            <button type="button" class="password-toggle" data-target="login-password">
                                <svg class="show-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <svg class="hide-icon" style="display: none;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.94 17.94C16.2306 19.243 14.1491 19.9649 12 20C5 20 1 12 1 12C2.24389 9.68192 3.96914 7.65663 6.06 6.06M9.9 4.24C10.5883 4.0789 11.2931 3.99836 12 4C19 4 23 12 23 12C22.393 13.1356 21.6691 14.2048 20.84 15.19M14.12 14.12C13.8454 14.4148 13.5141 14.6512 13.1462 14.8151C12.7782 14.9791 12.3809 15.0673 11.9781 15.0744C11.5753 15.0815 11.1749 15.0074 10.8016 14.8565C10.4283 14.7056 10.0887 14.4811 9.80385 14.1962C9.51897 13.9113 9.29439 13.5717 9.14351 13.1984C8.99262 12.8251 8.91853 12.4247 8.92563 12.0219C8.93274 11.6191 9.02091 11.2218 9.18488 10.8538C9.34884 10.4858 9.58525 10.1546 9.88 9.88" stroke="currentColor" stroke-width="2"/>
                                    <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember" value="true">
                            <span class="checkmark"></span>
                            <span class="checkbox-label">Beni hatırla</span>
                        </label>
                        <a href="#" class="forgot-password-link">Şifremi unuttum</a>
                    </div>

                    <button type="submit" class="auth-submit-btn" id="login-submit">
                        <span class="btn-text">Giriş Yap</span>
                        <div class="btn-loading" style="display: none;">
                            <div class="loading-spinner"></div>
                            <span>Giriş yapılıyor...</span>
                        </div>
                    </button>
                </form>
            </div>

            <!-- Registration Form -->
            <div class="auth-form-container" id="register-form-container" style="display: none;">
                <div class="auth-tabs">
                    <button class="auth-tab" data-tab="login">Giriş Yap</button>
                    <button class="auth-tab active" data-tab="register">Kayıt Ol</button>
                </div>

                <form id="portfoy-register-form" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="register-first-name" class="form-label">
                                <span class="label-text">Ad</span>
                                <span class="label-required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="register-first-name" name="first_name" class="form-input" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="register-last-name" class="form-label">
                                <span class="label-text">Soyad</span>
                                <span class="label-required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="register-last-name" name="last_name" class="form-input" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-username" class="form-label">
                            <span class="label-text">Kullanıcı Adı</span>
                            <span class="label-required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="register-username" name="username" class="form-input" required>
                            <div class="input-validation" id="username-validation"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-email" class="form-label">
                            <span class="label-text">E-posta Adresi</span>
                            <span class="label-required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="email" id="register-email" name="email" class="form-input" required>
                            <div class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2"/>
                                    <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-password" class="form-label">
                            <span class="label-text">Şifre</span>
                            <span class="label-required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" id="register-password" name="password" class="form-input" required>
                            <div class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="16" r="1" fill="currentColor"/>
                                    <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                            <button type="button" class="password-toggle" data-target="register-password">
                                <svg class="show-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <svg class="hide-icon" style="display: none;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.94 17.94C16.2306 19.243 14.1491 19.9649 12 20C5 20 1 12 1 12C2.24389 9.68192 3.96914 7.65663 6.06 6.06M9.9 4.24C10.5883 4.0789 11.2931 3.99836 12 4C19 4 23 12 23 12C22.393 13.1356 21.6691 14.2048 20.84 15.19M14.12 14.12C13.8454 14.4148 13.5141 14.6512 13.1462 14.8151C12.7782 14.9791 12.3809 15.0673 11.9781 15.0744C11.5753 15.0815 11.1749 15.0074 10.8016 14.8565C10.4283 14.7056 10.0887 14.4811 9.80385 14.1962C9.51897 13.9113 9.29439 13.5717 9.14351 13.1984C8.99262 12.8251 8.91853 12.4247 8.92563 12.0219C8.93274 11.6191 9.02091 11.2218 9.18488 10.8538C9.34884 10.4858 9.58525 10.1546 9.88 9.88" stroke="currentColor" stroke-width="2"/>
                                    <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                        <div class="password-strength" id="password-strength"></div>
                    </div>

                    <div class="form-group">
                        <label for="register-confirm-password" class="form-label">
                            <span class="label-text">Şifre Tekrarı</span>
                            <span class="label-required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" id="register-confirm-password" name="confirm_password" class="form-input" required>
                            <div class="input-validation" id="confirm-password-validation"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="terms" required>
                            <span class="checkmark"></span>
                            <span class="checkbox-label">
                                <a href="#" class="terms-link">Kullanım şartlarını</a> ve 
                                <a href="#" class="privacy-link">gizlilik politikasını</a> kabul ediyorum
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="auth-submit-btn" id="register-submit">
                        <span class="btn-text">Kayıt Ol</span>
                        <div class="btn-loading" style="display: none;">
                            <div class="loading-spinner"></div>
                            <span>Kayıt oluşturuluyor...</span>
                        </div>
                    </button>
                </form>
            </div>

            <!-- Social Login Options -->
            <div class="social-login-section">
                <div class="divider">
                    <span class="divider-text">veya</span>
                </div>
                <div class="social-login-buttons">
                    <button class="social-btn google-btn" type="button">
                        <svg class="social-icon" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span>Google ile devam et</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Messages -->
        <div class="auth-success-message" id="auth-success-message" style="display: none;">
            <div class="success-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.7088 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4906 2.02168 11.3407C2.16356 9.19077 2.99721 7.14613 4.39828 5.49707C5.79935 3.84802 7.69279 2.69637 9.79619 2.20304C11.8996 1.70971 14.1003 1.89718 16.07 2.74" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="22,4 12,14.01 9,11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="success-title"></h3>
            <p class="success-description"></p>
        </div>

        <!-- Error Messages -->
        <div class="auth-error-message" id="auth-error-message" style="display: none;">
            <div class="error-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
                    <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
            <h3 class="error-title">Bir hata oluştu</h3>
            <p class="error-description"></p>
            <button class="error-retry-btn">Tekrar Dene</button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="auth-loading-overlay" id="auth-loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner-large"></div>
            <p class="loading-text">İşleminiz gerçekleştiriliyor...</p>
        </div>
    </div>
</div>

<script type="text/javascript">
// Add nonce for security
window.portfoyAuthNonce = '<?php echo wp_create_nonce( 'portfoy_auth_nonce' ); ?>';
window.portfoyAjaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
</script>