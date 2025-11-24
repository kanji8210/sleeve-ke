<?php
/**
 * Custom login form and page
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/public
 */

class Sleeve_KE_Login {

    /**
     * Initialize the class
     */
    public function __construct() {
        // Register shortcode
        add_shortcode( 'sleeve_ke_login', array( $this, 'login_shortcode' ) );
        
        // Customize WordPress login page
        add_action( 'login_enqueue_scripts', array( $this, 'customize_login_page' ) );
        add_filter( 'login_headerurl', array( $this, 'login_logo_url' ) );
        add_filter( 'login_headertext', array( $this, 'login_logo_title' ) );
        add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );
        
        // Handle custom login form submission
        add_action( 'init', array( $this, 'handle_custom_login' ) );
    }

    /**
     * Login form shortcode
     */
    public function login_shortcode( $atts ) {
        // If user is already logged in, redirect to dashboard
        if ( is_user_logged_in() ) {
            $dashboard_page = get_option( 'sleeve_ke_dashboard_page' );
            $dashboard_url = $dashboard_page ? get_permalink( $dashboard_page ) : home_url( '/dashboard' );
            wp_redirect( $dashboard_url );
            exit;
        }

        $atts = shortcode_atts( array(
            'redirect' => ''
        ), $atts );

        ob_start();
        $this->render_login_form( $atts );
        return ob_get_clean();
    }

    /**
     * Render custom login form
     */
    private function render_login_form( $atts ) {
        $employer_page = get_option( 'sleeve_ke_employer_registration_page' );
        $candidate_page = get_option( 'sleeve_ke_candidate_registration_page' );
        
        $employer_url = $employer_page ? get_permalink( $employer_page ) : home_url( '/employer-registration' );
        $candidate_url = $candidate_page ? get_permalink( $candidate_page ) : home_url( '/candidate-registration' );
        
        $redirect_to = ! empty( $atts['redirect'] ) ? $atts['redirect'] : ( isset( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : '' );
        
        $error_message = '';
        if ( isset( $_GET['login'] ) && $_GET['login'] === 'failed' ) {
            $error_message = __( 'Invalid username or password. Please try again.', 'sleeve-ke' );
        } elseif ( isset( $_GET['login'] ) && $_GET['login'] === 'empty' ) {
            $error_message = __( 'Please enter your username and password.', 'sleeve-ke' );
        }
        
        ?>
        <div class="sleeve-ke-login-container">
            <div class="sleeve-ke-login-box">
                <div class="login-header">
                    <?php 
                    $logo_url = get_option( 'sleeve_ke_login_logo' );
                    if ( $logo_url ) : ?>
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="login-logo" />
                    <?php else : ?>
                        <h1><?php bloginfo( 'name' ); ?></h1>
                    <?php endif; ?>
                    <p class="login-subtitle"><?php _e( 'Welcome back! Please login to your account.', 'sleeve-ke' ); ?></p>
                </div>

                <?php if ( $error_message ) : ?>
                    <div class="login-error">
                        <span class="dashicons dashicons-warning"></span>
                        <?php echo esc_html( $error_message ); ?>
                    </div>
                <?php endif; ?>

                <form name="loginform" id="sleeve-ke-loginform" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
                    <div class="form-group">
                        <label for="user_login">
                            <span class="dashicons dashicons-admin-users"></span>
                            <?php _e( 'Username or Email', 'sleeve-ke' ); ?>
                        </label>
                        <input type="text" 
                               name="log" 
                               id="user_login" 
                               class="form-control" 
                               value="<?php echo esc_attr( isset( $_POST['log'] ) ? $_POST['log'] : '' ); ?>" 
                               size="20" 
                               required />
                    </div>

                    <div class="form-group">
                        <label for="user_pass">
                            <span class="dashicons dashicons-lock"></span>
                            <?php _e( 'Password', 'sleeve-ke' ); ?>
                        </label>
                        <input type="password" 
                               name="pwd" 
                               id="user_pass" 
                               class="form-control" 
                               value="" 
                               size="20" 
                               required />
                    </div>

                    <div class="form-group remember-me">
                        <label>
                            <input name="rememberme" 
                                   type="checkbox" 
                                   id="rememberme" 
                                   value="forever" />
                            <span><?php _e( 'Remember Me', 'sleeve-ke' ); ?></span>
                        </label>
                    </div>

                    <?php if ( $redirect_to ) : ?>
                        <input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>" />
                    <?php endif; ?>

                    <div class="form-group">
                        <button type="submit" name="wp-submit" id="wp-submit" class="btn btn-primary btn-block">
                            <span class="dashicons dashicons-unlock"></span>
                            <?php _e( 'Log In', 'sleeve-ke' ); ?>
                        </button>
                    </div>

                    <div class="login-links">
                        <a href="<?php echo wp_lostpassword_url(); ?>" class="forgot-password">
                            <?php _e( 'Forgot Password?', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </form>

                <div class="login-divider">
                    <span><?php _e( 'OR', 'sleeve-ke' ); ?></span>
                </div>

                <div class="register-section">
                    <p class="register-title"><?php _e( "Don't have an account? Register now:", 'sleeve-ke' ); ?></p>
                    
                    <div class="register-buttons">
                        <a href="<?php echo esc_url( $employer_url ); ?>" class="btn btn-register btn-employer">
                            <span class="btn-icon">🏢</span>
                            <div class="btn-content">
                                <span class="btn-label"><?php _e( 'Register as Employer', 'sleeve-ke' ); ?></span>
                                <span class="btn-desc"><?php _e( 'Post jobs & hire talent', 'sleeve-ke' ); ?></span>
                            </div>
                        </a>

                        <a href="<?php echo esc_url( $candidate_url ); ?>" class="btn btn-register btn-candidate">
                            <span class="btn-icon">👤</span>
                            <div class="btn-content">
                                <span class="btn-label"><?php _e( 'Register as Candidate', 'sleeve-ke' ); ?></span>
                                <span class="btn-desc"><?php _e( 'Find your dream job', 'sleeve-ke' ); ?></span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .sleeve-ke-login-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }
        .sleeve-ke-login-box {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px;
            border: 1px solid #e0e0e0;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo {
            max-width: 250px;
            max-height: 80px;
            margin-bottom: 15px;
        }
        .login-header h1 {
            margin: 0 0 10px 0;
            color: #2271b1;
            font-size: 28px;
        }
        .login-subtitle {
            color: #666;
            font-size: 15px;
            margin: 0;
        }
        .login-error {
            background: #fcf0f1;
            border-left: 4px solid #d63638;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #d63638;
        }
        .login-error .dashicons {
            font-size: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3338;
            font-size: 14px;
        }
        .form-group label .dashicons {
            font-size: 18px;
            color: #2271b1;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #2271b1;
            box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
        }
        .remember-me {
            margin-bottom: 25px;
        }
        .remember-me label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
            cursor: pointer;
        }
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-primary {
            background: #2271b1;
            color: #fff;
            width: 100%;
        }
        .btn-primary:hover {
            background: #135e96;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(34, 113, 177, 0.3);
        }
        .btn-primary .dashicons {
            font-size: 20px;
        }
        .btn-block {
            width: 100%;
        }
        .login-links {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password {
            color: #2271b1;
            text-decoration: none;
            font-size: 14px;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        .login-divider {
            margin: 30px 0;
            position: relative;
            text-align: center;
        }
        .login-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }
        .login-divider span {
            background: #fff;
            padding: 0 15px;
            position: relative;
            color: #999;
            font-size: 13px;
            font-weight: 600;
        }
        .register-section {
            margin-top: 30px;
        }
        .register-title {
            text-align: center;
            color: #666;
            font-size: 15px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .register-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .btn-register {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 15px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            min-height: 120px;
            justify-content: center;
            text-decoration: none;
            color: #2c3338;
        }
        .btn-register:hover {
            background: #fff;
            border-color: #2271b1;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        .btn-register .btn-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .btn-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .btn-label {
            font-size: 14px;
            font-weight: 700;
            color: #2c3338;
            margin-bottom: 5px;
        }
        .btn-desc {
            font-size: 11px;
            color: #666;
            font-weight: 400;
        }
        @media (max-width: 600px) {
            .register-buttons {
                grid-template-columns: 1fr;
            }
            .sleeve-ke-login-box {
                padding: 30px 20px;
            }
        }
        </style>
        <?php
    }

    /**
     * Customize WordPress login page
     */
    public function customize_login_page() {
        $logo_url = get_option( 'sleeve_ke_login_logo' );
        
        ?>
        <style type="text/css">
            body.login {
                background: #f0f0f1;
            }
            #login h1 a {
                <?php if ( $logo_url ) : ?>
                background-image: url(<?php echo esc_url( $logo_url ); ?>);
                background-size: contain;
                width: 320px;
                height: 80px;
                <?php else : ?>
                background-image: none;
                text-indent: 0;
                width: auto;
                height: auto;
                font-size: 24px;
                font-weight: 600;
                color: #2271b1;
                <?php endif; ?>
            }
            <?php if ( ! $logo_url ) : ?>
            #login h1 a::after {
                content: '<?php bloginfo( 'name' ); ?>';
            }
            <?php endif; ?>
            .login form {
                border: 1px solid #e0e0e0;
                box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                border-radius: 12px;
            }
            .login form .input {
                border-radius: 6px;
                border: 2px solid #ddd;
            }
            .login form .input:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
            }
            .wp-core-ui .button-primary {
                background: #2271b1;
                border-color: #2271b1;
                box-shadow: none;
                text-shadow: none;
                border-radius: 6px;
                padding: 8px 24px;
                height: auto;
                font-size: 15px;
            }
            .wp-core-ui .button-primary:hover {
                background: #135e96;
                border-color: #135e96;
                transform: translateY(-2px);
            }
            .login #backtoblog a,
            .login #nav a {
                color: #2271b1;
            }
            .login #backtoblog a:hover,
            .login #nav a:hover {
                color: #135e96;
            }
        </style>
        <?php
    }

    /**
     * Change login logo URL
     */
    public function login_logo_url() {
        return home_url();
    }

    /**
     * Change login logo title
     */
    public function login_logo_title() {
        return get_bloginfo( 'name' );
    }

    /**
     * Handle login redirect based on user role
     */
    public function login_redirect( $redirect_to, $request, $user ) {
        if ( ! isset( $user->roles ) || ! is_array( $user->roles ) ) {
            return $redirect_to;
        }

        // Check for employer
        if ( in_array( 'employer', $user->roles ) ) {
            $employer_redirect = get_option( 'sleeve_ke_login_redirect_employer' );
            if ( $employer_redirect ) {
                return get_permalink( $employer_redirect );
            }
        }

        // Check for candidate
        if ( in_array( 'candidate', $user->roles ) ) {
            $candidate_redirect = get_option( 'sleeve_ke_login_redirect_candidate' );
            if ( $candidate_redirect ) {
                return get_permalink( $candidate_redirect );
            }
        }

        // Default: redirect to dashboard page
        $dashboard_page = get_option( 'sleeve_ke_dashboard_page' );
        if ( $dashboard_page ) {
            return get_permalink( $dashboard_page );
        }

        return $redirect_to;
    }

    /**
     * Handle custom login form submission
     */
    public function handle_custom_login() {
        // Not implemented yet - WordPress handles login via wp-login.php
    }
}
