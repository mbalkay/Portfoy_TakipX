<?php

/**
 * Advanced Authentication System for Portföy TakipX
 * Handles user registration, login, session management and security
 */
class Portfoy_TakipX_Auth {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Session table name
     */
    private $sessions_table;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        global $wpdb;
        $this->sessions_table = $wpdb->prefix . 'portfoy_takipx_user_sessions';
        
        add_action( 'wp_ajax_portfoy_register_user', array( $this, 'ajax_register_user' ) );
        add_action( 'wp_ajax_nopriv_portfoy_register_user', array( $this, 'ajax_register_user' ) );
        add_action( 'wp_ajax_portfoy_login_user', array( $this, 'ajax_login_user' ) );
        add_action( 'wp_ajax_nopriv_portfoy_login_user', array( $this, 'ajax_login_user' ) );
        add_action( 'wp_ajax_portfoy_logout_user', array( $this, 'ajax_logout_user' ) );
        add_action( 'wp_ajax_portfoy_verify_email', array( $this, 'ajax_verify_email' ) );
        add_action( 'wp_ajax_nopriv_portfoy_verify_email', array( $this, 'ajax_verify_email' ) );
        
        // Session cleanup
        add_action( 'wp_loaded', array( $this, 'cleanup_expired_sessions' ) );
    }

    /**
     * Create user sessions table
     */
    public function create_sessions_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->sessions_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            session_token varchar(64) NOT NULL,
            ip_address varchar(45),
            user_agent text,
            login_time datetime DEFAULT CURRENT_TIMESTAMP,
            last_activity datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            expires_at datetime NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            device_info text,
            location_info text,
            PRIMARY KEY (id),
            UNIQUE KEY session_token (session_token),
            KEY user_id (user_id),
            KEY expires_at (expires_at),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Register new user via AJAX
     */
    public function ajax_register_user() {
        try {
            // Verify nonce
            if ( ! wp_verify_nonce( $_POST['nonce'], 'portfoy_auth_nonce' ) ) {
                throw new Exception( 'Güvenlik kontrolü başarısız.' );
            }

            $username = sanitize_user( $_POST['username'] );
            $email = sanitize_email( $_POST['email'] );
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $first_name = sanitize_text_field( $_POST['first_name'] );
            $last_name = sanitize_text_field( $_POST['last_name'] );

            // Validation
            $this->validate_registration_data( $username, $email, $password, $confirm_password, $first_name, $last_name );

            // Create WordPress user
            $user_id = wp_create_user( $username, $password, $email );
            
            if ( is_wp_error( $user_id ) ) {
                throw new Exception( $user_id->get_error_message() );
            }

            // Update user meta
            wp_update_user( array(
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $first_name . ' ' . $last_name
            ) );

            // Set user meta for email verification
            $verification_token = wp_generate_password( 32, false );
            update_user_meta( $user_id, 'portfoy_email_verification_token', $verification_token );
            update_user_meta( $user_id, 'portfoy_email_verified', 0 );
            update_user_meta( $user_id, 'portfoy_registration_date', current_time( 'mysql' ) );

            // Send verification email
            $this->send_verification_email( $user_id, $email, $verification_token );

            wp_send_json_success( array(
                'message' => 'Kayıt işleminiz başarıyla tamamlandı. Lütfen e-posta adresinizi doğrulayın.',
                'user_id' => $user_id,
                'verification_required' => true
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage()
            ) );
        }
    }

    /**
     * Login user via AJAX
     */
    public function ajax_login_user() {
        try {
            // Verify nonce
            if ( ! wp_verify_nonce( $_POST['nonce'], 'portfoy_auth_nonce' ) ) {
                throw new Exception( 'Güvenlik kontrolü başarısız.' );
            }

            $username = sanitize_user( $_POST['username'] );
            $password = $_POST['password'];
            $remember = isset( $_POST['remember'] ) && $_POST['remember'] === 'true';

            // Attempt to authenticate
            $user = wp_authenticate( $username, $password );
            
            if ( is_wp_error( $user ) ) {
                throw new Exception( 'Kullanıcı adı veya şifre hatalı.' );
            }

            // Check email verification
            $email_verified = get_user_meta( $user->ID, 'portfoy_email_verified', true );
            if ( ! $email_verified ) {
                throw new Exception( 'Lütfen önce e-posta adresinizi doğrulayın.' );
            }

            // Create session
            $session_token = $this->create_user_session( $user->ID, $remember );
            
            // Set login cookie
            wp_set_auth_cookie( $user->ID, $remember );

            // Update last login
            update_user_meta( $user->ID, 'portfoy_last_login', current_time( 'mysql' ) );

            wp_send_json_success( array(
                'message' => 'Giriş başarılı! Portföyünüze yönlendiriliyorsunuz...',
                'user_id' => $user->ID,
                'session_token' => $session_token,
                'redirect_url' => home_url( '/portfoyum/' ),
                'user_data' => array(
                    'display_name' => $user->display_name,
                    'email' => $user->user_email,
                    'avatar_url' => get_avatar_url( $user->ID )
                )
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage()
            ) );
        }
    }

    /**
     * Logout user via AJAX
     */
    public function ajax_logout_user() {
        try {
            $user_id = get_current_user_id();
            
            if ( $user_id ) {
                // Invalidate session
                $this->invalidate_user_sessions( $user_id );
                
                // WordPress logout
                wp_logout();
            }

            wp_send_json_success( array(
                'message' => 'Başarıyla çıkış yaptınız.',
                'redirect_url' => home_url( '/portfoyum/' )
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage()
            ) );
        }
    }

    /**
     * Verify email via AJAX
     */
    public function ajax_verify_email() {
        try {
            $token = sanitize_text_field( $_GET['token'] );
            $user_id = intval( $_GET['user_id'] );

            if ( ! $token || ! $user_id ) {
                throw new Exception( 'Geçersiz doğrulama bağlantısı.' );
            }

            $stored_token = get_user_meta( $user_id, 'portfoy_email_verification_token', true );
            
            if ( $token !== $stored_token ) {
                throw new Exception( 'Doğrulama token\'ı geçersiz.' );
            }

            // Mark email as verified
            update_user_meta( $user_id, 'portfoy_email_verified', 1 );
            delete_user_meta( $user_id, 'portfoy_email_verification_token' );

            wp_send_json_success( array(
                'message' => 'E-posta adresiniz başarıyla doğrulandı. Artık giriş yapabilirsiniz.',
                'verified' => true
            ) );

        } catch ( Exception $e ) {
            wp_send_json_error( array(
                'message' => $e->getMessage()
            ) );
        }
    }

    /**
     * Validate registration data
     */
    private function validate_registration_data( $username, $email, $password, $confirm_password, $first_name, $last_name ) {
        if ( empty( $username ) || empty( $email ) || empty( $password ) || empty( $first_name ) || empty( $last_name ) ) {
            throw new Exception( 'Lütfen tüm alanları doldurun.' );
        }

        if ( ! is_email( $email ) ) {
            throw new Exception( 'Geçerli bir e-posta adresi girin.' );
        }

        if ( username_exists( $username ) ) {
            throw new Exception( 'Bu kullanıcı adı zaten kullanılıyor.' );
        }

        if ( email_exists( $email ) ) {
            throw new Exception( 'Bu e-posta adresi zaten kayıtlı.' );
        }

        if ( strlen( $password ) < 8 ) {
            throw new Exception( 'Şifre en az 8 karakter olmalıdır.' );
        }

        if ( $password !== $confirm_password ) {
            throw new Exception( 'Şifreler eşleşmiyor.' );
        }

        // Password strength check
        if ( ! preg_match( '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password ) ) {
            throw new Exception( 'Şifre en az bir büyük harf, bir küçük harf ve bir rakam içermelidir.' );
        }
    }

    /**
     * Create user session
     */
    private function create_user_session( $user_id, $remember = false ) {
        global $wpdb;

        $session_token = wp_generate_password( 64, false );
        $expires_at = $remember ? 
            date( 'Y-m-d H:i:s', strtotime( '+30 days' ) ) : 
            date( 'Y-m-d H:i:s', strtotime( '+24 hours' ) );

        $wpdb->insert(
            $this->sessions_table,
            array(
                'user_id' => $user_id,
                'session_token' => $session_token,
                'ip_address' => $this->get_client_ip(),
                'user_agent' => substr( $_SERVER['HTTP_USER_AGENT'], 0, 500 ),
                'expires_at' => $expires_at,
                'device_info' => $this->get_device_info(),
                'location_info' => $this->get_location_info()
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        return $session_token;
    }

    /**
     * Invalidate user sessions
     */
    private function invalidate_user_sessions( $user_id, $current_session_only = false ) {
        global $wpdb;

        if ( $current_session_only && isset( $_COOKIE['portfoy_session'] ) ) {
            $wpdb->update(
                $this->sessions_table,
                array( 'is_active' => 0 ),
                array( 
                    'user_id' => $user_id,
                    'session_token' => $_COOKIE['portfoy_session']
                ),
                array( '%d' ),
                array( '%d', '%s' )
            );
        } else {
            $wpdb->update(
                $this->sessions_table,
                array( 'is_active' => 0 ),
                array( 'user_id' => $user_id ),
                array( '%d' ),
                array( '%d' )
            );
        }
    }

    /**
     * Send verification email
     */
    private function send_verification_email( $user_id, $email, $token ) {
        $verification_url = add_query_arg( array(
            'action' => 'portfoy_verify_email',
            'user_id' => $user_id,
            'token' => $token
        ), admin_url( 'admin-ajax.php' ) );

        $subject = 'Portföy TakipX - E-posta Doğrulama';
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2563eb;'>Portföy TakipX'e Hoş Geldiniz!</h2>
                <p>Merhaba,</p>
                <p>Portföy TakipX'e kayıt olduğunuz için teşekkür ederiz. E-posta adresinizi doğrulamak için aşağıdaki butona tıklayın:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$verification_url}' style='background-color: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>E-posta Adresimi Doğrula</a>
                </div>
                <p>Eğer buton çalışmıyorsa, aşağıdaki bağlantıyı kopyalayıp tarayıcınıza yapıştırabilirsiniz:</p>
                <p style='word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 3px;'>{$verification_url}</p>
                <p style='margin-top: 30px; font-size: 12px; color: #666;'>Bu e-posta 24 saat içinde geçerliliğini yitirecektir.</p>
            </div>
        </body>
        </html>";

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        wp_mail( $email, $subject, $message, $headers );
    }

    /**
     * Cleanup expired sessions
     */
    public function cleanup_expired_sessions() {
        global $wpdb;
        
        $wpdb->delete(
            $this->sessions_table,
            array( 'expires_at <' => current_time( 'mysql' ) ),
            array( '%s' )
        );
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
        
        foreach ( $ip_keys as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
                    $ip = trim( $ip );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get device information
     */
    private function get_device_info() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $device_info = array(
            'browser' => $this->get_browser_name( $user_agent ),
            'os' => $this->get_os_name( $user_agent ),
            'is_mobile' => wp_is_mobile()
        );
        
        return json_encode( $device_info );
    }

    /**
     * Get location information (basic)
     */
    private function get_location_info() {
        // Basic implementation - could be enhanced with IP geolocation service
        $location = array(
            'ip' => $this->get_client_ip(),
            'timestamp' => current_time( 'mysql' )
        );
        
        return json_encode( $location );
    }

    /**
     * Get browser name from user agent
     */
    private function get_browser_name( $user_agent ) {
        $browsers = array(
            'Chrome' => '/Chrome/i',
            'Firefox' => '/Firefox/i',
            'Safari' => '/Safari/i',
            'Edge' => '/Edge/i',
            'Opera' => '/Opera/i'
        );

        foreach ( $browsers as $browser => $pattern ) {
            if ( preg_match( $pattern, $user_agent ) ) {
                return $browser;
            }
        }

        return 'Unknown';
    }

    /**
     * Get OS name from user agent
     */
    private function get_os_name( $user_agent ) {
        $os_array = array(
            'Windows' => '/Windows/i',
            'Mac' => '/Mac/i',
            'Linux' => '/Linux/i',
            'iOS' => '/iOS/i',
            'Android' => '/Android/i'
        );

        foreach ( $os_array as $os => $pattern ) {
            if ( preg_match( $pattern, $user_agent ) ) {
                return $os;
            }
        }

        return 'Unknown';
    }

    /**
     * Check if user is authenticated
     */
    public function is_user_authenticated() {
        return is_user_logged_in() && $this->is_email_verified( get_current_user_id() );
    }

    /**
     * Check if email is verified
     */
    public function is_email_verified( $user_id ) {
        return get_user_meta( $user_id, 'portfoy_email_verified', true ) == 1;
    }

    /**
     * Get user portfolio data
     */
    public function get_user_portfolio_access( $user_id ) {
        if ( ! $this->is_email_verified( $user_id ) ) {
            return false;
        }

        return array(
            'user_id' => $user_id,
            'access_level' => 'full',
            'last_login' => get_user_meta( $user_id, 'portfoy_last_login', true ),
            'registration_date' => get_user_meta( $user_id, 'portfoy_registration_date', true )
        );
    }
}