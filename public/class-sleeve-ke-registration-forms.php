<?php
/**
 * Frontend registration forms functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/public
 */

/**
 * Frontend registration forms class.
 *
 * Handles employer and candidate self-registration forms
 * with shortcode integration and automatic notifications.
 */
class Sleeve_KE_Registration_Forms {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Register shortcodes
        add_shortcode( 'sleeve_ke_employer_registration', array( $this, 'employer_registration_shortcode' ) );
        add_shortcode( 'sleeve_ke_candidate_registration', array( $this, 'candidate_registration_shortcode' ) );
        
        // Handle form submissions
        add_action( 'wp', array( $this, 'handle_form_submissions' ) );
        
        // Enqueue frontend styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
        
        // Handle AJAX requests
        add_action( 'wp_ajax_sleeve_ke_check_email', array( $this, 'ajax_check_email' ) );
        add_action( 'wp_ajax_nopriv_sleeve_ke_check_email', array( $this, 'ajax_check_email' ) );
    }

    /**
     * Enqueue frontend styles and scripts
     */
    public function enqueue_frontend_styles() {
        if ( $this->is_registration_page() ) {
            wp_enqueue_style( 
                'sleeve-ke-registration', 
                SLEEVE_KE_PLUGIN_URL . 'assets/css/sleeve-ke-registration.css', 
                array(), 
                SLEEVE_KE_VERSION 
            );
            
            wp_enqueue_script( 
                'sleeve-ke-registration', 
                SLEEVE_KE_PLUGIN_URL . 'assets/js/sleeve-ke-registration.js', 
                array( 'jquery' ), 
                SLEEVE_KE_VERSION, 
                true 
            );
            
            // Localize script for AJAX
            wp_localize_script( 'sleeve-ke-registration', 'sleeve_ke_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'sleeve_ke_ajax_nonce' )
            ) );
        }
    }

    /**
     * AJAX handler to check email availability
     */
    public function ajax_check_email() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'sleeve_ke_ajax_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        $email = sanitize_email( $_POST['email'] );
        $available = ! email_exists( $email );
        
        wp_send_json_success( array( 'available' => $available ) );
    }

    /**
     * Check if current page contains registration forms
     */
    private function is_registration_page() {
        global $post;
        if ( ! $post ) {
            return false;
        }
        
        return ( 
            has_shortcode( $post->post_content, 'sleeve_ke_employer_registration' ) || 
            has_shortcode( $post->post_content, 'sleeve_ke_candidate_registration' ) 
        );
    }

    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        // Handle employer registration
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'sleeve_ke_register_employer' ) {
            $this->handle_employer_registration();
        }
        
        // Handle candidate registration
        if ( isset( $_POST['action'] ) && $_POST['action'] === 'sleeve_ke_register_candidate' ) {
            $this->handle_candidate_registration();
        }
    }

    /**
     * Employer registration shortcode
     */
    public function employer_registration_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'redirect_url' => '',
            'show_title' => 'true',
            'title' => __( 'Employer Registration', 'sleeve-ke' ),
            'description' => __( 'Register your company to start posting jobs and finding qualified candidates.', 'sleeve-ke' )
        ), $atts );

        ob_start();
        $this->display_employer_registration_form( $atts );
        return ob_get_clean();
    }

    /**
     * Candidate registration shortcode
     */
    public function candidate_registration_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'redirect_url' => '',
            'show_title' => 'true',
            'title' => __( 'Candidate Registration', 'sleeve-ke' ),
            'description' => __( 'Join our platform to discover exciting job opportunities and advance your career.', 'sleeve-ke' )
        ), $atts );

        ob_start();
        $this->display_candidate_registration_form( $atts );
        return ob_get_clean();
    }

    /**
     * Display employer registration form
     */
    private function display_employer_registration_form( $atts ) {
        $success = isset( $_GET['employer_registered'] ) && $_GET['employer_registered'] == 1;
        $error = isset( $_GET['registration_error'] ) ? sanitize_text_field( $_GET['registration_error'] ) : '';
        ?>
        <div class="sleeve-ke-registration-form employer-registration">
            <?php if ( $atts['show_title'] === 'true' ) : ?>
                <div class="registration-header">
                    <h2 class="registration-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                    <p class="registration-description"><?php echo esc_html( $atts['description'] ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( $success ) : ?>
                <div class="registration-success">
                    <div class="success-icon">✅</div>
                    <h3><?php esc_html_e( 'Registration Successful!', 'sleeve-ke' ); ?></h3>
                    <p><?php esc_html_e( 'Thank you for registering as an employer. Your account is pending approval. You will receive a confirmation email shortly with login instructions.', 'sleeve-ke' ); ?></p>
                    <div class="success-actions">
                        <a href="<?php echo wp_login_url(); ?>" class="btn btn-primary">
                            <?php esc_html_e( 'Login to Your Account', 'sleeve-ke' ); ?>
                        </a>
                        <a href="<?php echo home_url(); ?>" class="btn btn-secondary">
                            <?php esc_html_e( 'Back to Home', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </div>
            <?php elseif ( $error ) : ?>
                <div class="registration-error">
                    <div class="error-icon">❌</div>
                    <h3><?php esc_html_e( 'Registration Error', 'sleeve-ke' ); ?></h3>
                    <p><?php echo esc_html( $error ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( ! $success ) : ?>
                <form method="post" action="" class="employer-registration-form" novalidate>
                    <?php wp_nonce_field( 'sleeve_ke_register_employer', 'sleeve_employer_nonce' ); ?>
                    <input type="hidden" name="action" value="sleeve_ke_register_employer" />
                    <input type="hidden" name="redirect_url" value="<?php echo esc_attr( $atts['redirect_url'] ); ?>" />

                    <div class="form-section">
                        <h3 class="section-title"><?php esc_html_e( 'Account Information', 'sleeve-ke' ); ?></h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="emp_username"><?php esc_html_e( 'Username', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="text" id="emp_username" name="username" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Choose a username', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['username'] ) ? $_POST['username'] : '' ); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="emp_email"><?php esc_html_e( 'Email Address', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="email" id="emp_email" name="email" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Your email address', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['email'] ) ? $_POST['email'] : '' ); ?>" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="emp_password"><?php esc_html_e( 'Password', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="password" id="emp_password" name="password" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Create a password', 'sleeve-ke' ); ?>" />
                                <small class="form-text"><?php esc_html_e( 'Minimum 8 characters', 'sleeve-ke' ); ?></small>
                            </div>
                            <div class="form-group">
                                <label for="emp_confirm_password"><?php esc_html_e( 'Confirm Password', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="password" id="emp_confirm_password" name="confirm_password" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Confirm your password', 'sleeve-ke' ); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title"><?php esc_html_e( 'Company Information', 'sleeve-ke' ); ?></h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="company_name"><?php esc_html_e( 'Company Name', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="text" id="company_name" name="company_name" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Your company name', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['company_name'] ) ? $_POST['company_name'] : '' ); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="company_industry"><?php esc_html_e( 'Industry', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <select id="company_industry" name="industry" required class="form-control">
                                    <option value=""><?php esc_html_e( 'Select your industry', 'sleeve-ke' ); ?></option>
                                    <?php
                                    $industries = $this->get_industries();
                                    $selected_industry = isset( $_POST['industry'] ) ? $_POST['industry'] : '';
                                    foreach ( $industries as $key => $label ) :
                                    ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_industry, $key ); ?>>
                                            <?php echo esc_html( $label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="company_size"><?php esc_html_e( 'Company Size', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <select id="company_size" name="company_size" required class="form-control">
                                    <option value=""><?php esc_html_e( 'Select company size', 'sleeve-ke' ); ?></option>
                                    <?php
                                    $company_sizes = $this->get_company_sizes();
                                    $selected_size = isset( $_POST['company_size'] ) ? $_POST['company_size'] : '';
                                    foreach ( $company_sizes as $key => $label ) :
                                    ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_size, $key ); ?>>
                                            <?php echo esc_html( $label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="company_location"><?php esc_html_e( 'Location', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="text" id="company_location" name="location" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'City, Country', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['location'] ) ? $_POST['location'] : '' ); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="company_website"><?php esc_html_e( 'Company Website', 'sleeve-ke' ); ?></label>
                            <input type="url" id="company_website" name="website" 
                                   class="form-control" placeholder="<?php esc_attr_e( 'https://yourcompany.com', 'sleeve-ke' ); ?>"
                                   value="<?php echo esc_attr( isset( $_POST['website'] ) ? $_POST['website'] : '' ); ?>" />
                        </div>

                        <div class="form-group">
                            <label for="company_description"><?php esc_html_e( 'Company Description', 'sleeve-ke' ); ?></label>
                            <textarea id="company_description" name="description" rows="4" 
                                      class="form-control" placeholder="<?php esc_attr_e( 'Tell us about your company...', 'sleeve-ke' ); ?>"><?php echo esc_textarea( isset( $_POST['description'] ) ? $_POST['description'] : '' ); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title"><?php esc_html_e( 'Contact Information', 'sleeve-ke' ); ?></h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact_person"><?php esc_html_e( 'Contact Person', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="text" id="contact_person" name="contact_person" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Full name', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['contact_person'] ) ? $_POST['contact_person'] : '' ); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="contact_phone"><?php esc_html_e( 'Phone Number', 'sleeve-ke' ); ?></label>
                                <input type="tel" id="contact_phone" name="phone" 
                                       class="form-control" placeholder="<?php esc_attr_e( '+254 700 123 456', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['phone'] ) ? $_POST['phone'] : '' ); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms_accepted" value="1" required />
                                <span class="checkmark"></span>
                                <?php esc_html_e( 'I agree to the', 'sleeve-ke' ); ?> 
                                <a href="#" target="_blank"><?php esc_html_e( 'Terms and Conditions', 'sleeve-ke' ); ?></a>
                                <?php esc_html_e( 'and', 'sleeve-ke' ); ?>
                                <a href="#" target="_blank"><?php esc_html_e( 'Privacy Policy', 'sleeve-ke' ); ?></a>
                            </label>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="email_notifications" value="1" checked />
                                <span class="checkmark"></span>
                                <?php esc_html_e( 'I want to receive email notifications about applications and updates', 'sleeve-ke' ); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <span class="btn-text"><?php esc_html_e( 'Register as Employer', 'sleeve-ke' ); ?></span>
                            <span class="btn-loading" style="display: none;"><?php esc_html_e( 'Registering...', 'sleeve-ke' ); ?></span>
                        </button>
                    </div>

                    <div class="login-link">
                        <p><?php esc_html_e( 'Already have an account?', 'sleeve-ke' ); ?> 
                           <a href="<?php echo wp_login_url(); ?>"><?php esc_html_e( 'Login here', 'sleeve-ke' ); ?></a>
                        </p>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Form validation
            $('.employer-registration-form').on('submit', function(e) {
                var isValid = true;
                var password = $('#emp_password').val();
                var confirmPassword = $('#emp_confirm_password').val();
                
                // Check password length
                if (password.length < 8) {
                    alert('<?php esc_js( __( 'Password must be at least 8 characters long', 'sleeve-ke' ) ); ?>');
                    isValid = false;
                }
                
                // Check password match
                if (password !== confirmPassword) {
                    alert('<?php esc_js( __( 'Passwords do not match', 'sleeve-ke' ) ); ?>');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                $(this).find('.btn-text').hide();
                $(this).find('.btn-loading').show();
                $(this).find('button[type="submit"]').prop('disabled', true);
            });
        });
        </script>
        <?php
    }

    /**
     * Display candidate registration form
     */
    private function display_candidate_registration_form( $atts ) {
        $success = isset( $_GET['candidate_registered'] ) && $_GET['candidate_registered'] == 1;
        $error = isset( $_GET['registration_error'] ) ? sanitize_text_field( $_GET['registration_error'] ) : '';
        ?>
        <div class="sleeve-ke-registration-form candidate-registration">
            <?php if ( $atts['show_title'] === 'true' ) : ?>
                <div class="registration-header">
                    <h2 class="registration-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                    <p class="registration-description"><?php echo esc_html( $atts['description'] ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( $success ) : ?>
                <div class="registration-success">
                    <div class="success-icon">✅</div>
                    <h3><?php esc_html_e( 'Registration Successful!', 'sleeve-ke' ); ?></h3>
                    <p><?php esc_html_e( 'Welcome to our platform! Your candidate account has been created successfully. You can now start browsing and applying for jobs.', 'sleeve-ke' ); ?></p>
                    <div class="success-actions">
                        <a href="<?php echo wp_login_url(); ?>" class="btn btn-primary">
                            <?php esc_html_e( 'Login to Your Account', 'sleeve-ke' ); ?>
                        </a>
                        <a href="<?php echo home_url( '/jobs' ); ?>" class="btn btn-secondary">
                            <?php esc_html_e( 'Browse Jobs', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </div>
            <?php elseif ( $error ) : ?>
                <div class="registration-error">
                    <div class="error-icon">❌</div>
                    <h3><?php esc_html_e( 'Registration Error', 'sleeve-ke' ); ?></h3>
                    <p><?php echo esc_html( $error ); ?></p>
                </div>
            <?php endif; ?>

            <?php if ( ! $success ) : ?>
                <form method="post" action="" class="candidate-registration-form" novalidate>
                    <?php wp_nonce_field( 'sleeve_ke_register_candidate', 'sleeve_candidate_nonce' ); ?>
                    <input type="hidden" name="action" value="sleeve_ke_register_candidate" />
                    <input type="hidden" name="redirect_url" value="<?php echo esc_attr( $atts['redirect_url'] ); ?>" />

                    <div class="form-section">
                        <h3 class="section-title"><?php esc_html_e( 'Account Information', 'sleeve-ke' ); ?></h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cand_username"><?php esc_html_e( 'Username', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="text" id="cand_username" name="username" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Choose a username', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['username'] ) ? $_POST['username'] : '' ); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="cand_email"><?php esc_html_e( 'Email Address', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="email" id="cand_email" name="email" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Your email address', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['email'] ) ? $_POST['email'] : '' ); ?>" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="cand_password"><?php esc_html_e( 'Password', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="password" id="cand_password" name="password" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Create a password', 'sleeve-ke' ); ?>" />
                                <small class="form-text"><?php esc_html_e( 'Minimum 8 characters', 'sleeve-ke' ); ?></small>
                            </div>
                            <div class="form-group">
                                <label for="cand_confirm_password"><?php esc_html_e( 'Confirm Password', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="password" id="cand_confirm_password" name="confirm_password" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Confirm your password', 'sleeve-ke' ); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title"><?php esc_html_e( 'Personal Information', 'sleeve-ke' ); ?></h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name"><?php esc_html_e( 'First Name', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="text" id="first_name" name="first_name" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Your first name', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['first_name'] ) ? $_POST['first_name'] : '' ); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="last_name"><?php esc_html_e( 'Last Name', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'Your last name', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['last_name'] ) ? $_POST['last_name'] : '' ); ?>" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone"><?php esc_html_e( 'Phone Number', 'sleeve-ke' ); ?></label>
                                <input type="tel" id="phone" name="phone" 
                                       class="form-control" placeholder="<?php esc_attr_e( '+254 700 123 456', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['phone'] ) ? $_POST['phone'] : '' ); ?>" />
                            </div>
                            <div class="form-group">
                                <label for="location"><?php esc_html_e( 'Location', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <input type="text" id="location" name="location" required 
                                       class="form-control" placeholder="<?php esc_attr_e( 'City, Country', 'sleeve-ke' ); ?>"
                                       value="<?php echo esc_attr( isset( $_POST['location'] ) ? $_POST['location'] : '' ); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title"><?php esc_html_e( 'Professional Information', 'sleeve-ke' ); ?></h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="experience_level"><?php esc_html_e( 'Experience Level', 'sleeve-ke' ); ?> <span class="required">*</span></label>
                                <select id="experience_level" name="experience_level" required class="form-control">
                                    <option value=""><?php esc_html_e( 'Select your experience level', 'sleeve-ke' ); ?></option>
                                    <?php
                                    $experience_levels = $this->get_experience_levels();
                                    $selected_level = isset( $_POST['experience_level'] ) ? $_POST['experience_level'] : '';
                                    foreach ( $experience_levels as $key => $label ) :
                                    ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_level, $key ); ?>>
                                            <?php echo esc_html( $label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="desired_industry"><?php esc_html_e( 'Desired Industry', 'sleeve-ke' ); ?></label>
                                <select id="desired_industry" name="desired_industry" class="form-control">
                                    <option value=""><?php esc_html_e( 'Select preferred industry', 'sleeve-ke' ); ?></option>
                                    <?php
                                    $industries = $this->get_industries();
                                    $selected_industry = isset( $_POST['desired_industry'] ) ? $_POST['desired_industry'] : '';
                                    foreach ( $industries as $key => $label ) :
                                    ?>
                                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_industry, $key ); ?>>
                                            <?php echo esc_html( $label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="skills"><?php esc_html_e( 'Key Skills', 'sleeve-ke' ); ?></label>
                            <input type="text" id="skills" name="skills" 
                                   class="form-control" placeholder="<?php esc_attr_e( 'e.g., PHP, WordPress, Project Management (comma separated)', 'sleeve-ke' ); ?>"
                                   value="<?php echo esc_attr( isset( $_POST['skills'] ) ? $_POST['skills'] : '' ); ?>" />
                            <small class="form-text"><?php esc_html_e( 'Separate skills with commas', 'sleeve-ke' ); ?></small>
                        </div>

                        <div class="form-group">
                            <label for="bio"><?php esc_html_e( 'Professional Summary', 'sleeve-ke' ); ?></label>
                            <textarea id="bio" name="bio" rows="4" 
                                      class="form-control" placeholder="<?php esc_attr_e( 'Tell us about your professional background and career goals...', 'sleeve-ke' ); ?>"><?php echo esc_textarea( isset( $_POST['bio'] ) ? $_POST['bio'] : '' ); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms_accepted" value="1" required />
                                <span class="checkmark"></span>
                                <?php esc_html_e( 'I agree to the', 'sleeve-ke' ); ?> 
                                <a href="#" target="_blank"><?php esc_html_e( 'Terms and Conditions', 'sleeve-ke' ); ?></a>
                                <?php esc_html_e( 'and', 'sleeve-ke' ); ?>
                                <a href="#" target="_blank"><?php esc_html_e( 'Privacy Policy', 'sleeve-ke' ); ?></a>
                            </label>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="job_alerts" value="1" checked />
                                <span class="checkmark"></span>
                                <?php esc_html_e( 'I want to receive job alerts matching my profile', 'sleeve-ke' ); ?>
                            </label>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="email_notifications" value="1" checked />
                                <span class="checkmark"></span>
                                <?php esc_html_e( 'I want to receive email notifications about applications and updates', 'sleeve-ke' ); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <span class="btn-text"><?php esc_html_e( 'Register as Candidate', 'sleeve-ke' ); ?></span>
                            <span class="btn-loading" style="display: none;"><?php esc_html_e( 'Registering...', 'sleeve-ke' ); ?></span>
                        </button>
                    </div>

                    <div class="login-link">
                        <p><?php esc_html_e( 'Already have an account?', 'sleeve-ke' ); ?> 
                           <a href="<?php echo wp_login_url(); ?>"><?php esc_html_e( 'Login here', 'sleeve-ke' ); ?></a>
                        </p>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Form validation
            $('.candidate-registration-form').on('submit', function(e) {
                var isValid = true;
                var password = $('#cand_password').val();
                var confirmPassword = $('#cand_confirm_password').val();
                
                // Check password length
                if (password.length < 8) {
                    alert('<?php esc_js( __( 'Password must be at least 8 characters long', 'sleeve-ke' ) ); ?>');
                    isValid = false;
                }
                
                // Check password match
                if (password !== confirmPassword) {
                    alert('<?php esc_js( __( 'Passwords do not match', 'sleeve-ke' ) ); ?>');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                    return false;
                }
                
                // Show loading state
                $(this).find('.btn-text').hide();
                $(this).find('.btn-loading').show();
                $(this).find('button[type="submit"]').prop('disabled', true);
            });
        });
        </script>
        <?php
    }

    /**
     * Handle employer registration
     */
    private function handle_employer_registration() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['sleeve_employer_nonce'], 'sleeve_ke_register_employer' ) ) {
            $this->redirect_with_error( __( 'Security check failed. Please try again.', 'sleeve-ke' ) );
            return;
        }

        // Sanitize and validate data
        $username = sanitize_user( $_POST['username'] );
        $email = sanitize_email( $_POST['email'] );
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $company_name = sanitize_text_field( $_POST['company_name'] );
        $industry = sanitize_text_field( $_POST['industry'] );
        $company_size = sanitize_text_field( $_POST['company_size'] );
        $location = sanitize_text_field( $_POST['location'] );
        $website = sanitize_url( $_POST['website'] );
        $description = sanitize_textarea_field( $_POST['description'] );
        $contact_person = sanitize_text_field( $_POST['contact_person'] );
        $phone = sanitize_text_field( $_POST['phone'] );
        $terms_accepted = isset( $_POST['terms_accepted'] );
        $email_notifications = isset( $_POST['email_notifications'] );

        // Validation
        $errors = array();

        if ( empty( $username ) ) {
            $errors[] = __( 'Username is required.', 'sleeve-ke' );
        } elseif ( username_exists( $username ) ) {
            $errors[] = __( 'Username already exists.', 'sleeve-ke' );
        }

        if ( empty( $email ) || ! is_email( $email ) ) {
            $errors[] = __( 'Valid email address is required.', 'sleeve-ke' );
        } elseif ( email_exists( $email ) ) {
            $errors[] = __( 'Email address already registered.', 'sleeve-ke' );
        }

        if ( empty( $password ) || strlen( $password ) < 8 ) {
            $errors[] = __( 'Password must be at least 8 characters long.', 'sleeve-ke' );
        }

        if ( $password !== $confirm_password ) {
            $errors[] = __( 'Passwords do not match.', 'sleeve-ke' );
        }

        if ( empty( $company_name ) ) {
            $errors[] = __( 'Company name is required.', 'sleeve-ke' );
        }

        if ( empty( $industry ) ) {
            $errors[] = __( 'Industry selection is required.', 'sleeve-ke' );
        }

        if ( empty( $company_size ) ) {
            $errors[] = __( 'Company size selection is required.', 'sleeve-ke' );
        }

        if ( empty( $location ) ) {
            $errors[] = __( 'Location is required.', 'sleeve-ke' );
        }

        if ( empty( $contact_person ) ) {
            $errors[] = __( 'Contact person name is required.', 'sleeve-ke' );
        }

        if ( ! $terms_accepted ) {
            $errors[] = __( 'You must accept the terms and conditions.', 'sleeve-ke' );
        }

        if ( ! empty( $errors ) ) {
            $this->redirect_with_error( implode( ' ', $errors ) );
            return;
        }

        // Create user account
        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            $this->redirect_with_error( $user_id->get_error_message() );
            return;
        }

        // Set user role
        $user = new WP_User( $user_id );
        $user->set_role( 'employer' );

        // Save employer meta data
        update_user_meta( $user_id, 'company_name', $company_name );
        update_user_meta( $user_id, 'industry', $industry );
        update_user_meta( $user_id, 'company_size', $company_size );
        update_user_meta( $user_id, 'location', $location );
        update_user_meta( $user_id, 'website', $website );
        update_user_meta( $user_id, 'description', $description );
        update_user_meta( $user_id, 'contact_person', $contact_person );
        update_user_meta( $user_id, 'phone', $phone );
        update_user_meta( $user_id, 'email_notifications', $email_notifications );
        update_user_meta( $user_id, 'account_status', 'pending' );
        update_user_meta( $user_id, 'registration_date', current_time( 'mysql' ) );

        // Send notification emails
        $this->send_employer_registration_notifications( $user_id, array(
            'username' => $username,
            'email' => $email,
            'company_name' => $company_name,
            'contact_person' => $contact_person
        ) );

        // Trigger action for other plugins/integrations
        do_action( 'sleeve_ke_employer_registered', $user_id );

        // Redirect to success page
        $redirect_url = ! empty( $_POST['redirect_url'] ) ? $_POST['redirect_url'] : $_SERVER['REQUEST_URI'];
        $redirect_url = add_query_arg( 'employer_registered', 1, $redirect_url );
        wp_redirect( $redirect_url );
        exit;
    }

    /**
     * Handle candidate registration
     */
    private function handle_candidate_registration() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['sleeve_candidate_nonce'], 'sleeve_ke_register_candidate' ) ) {
            $this->redirect_with_error( __( 'Security check failed. Please try again.', 'sleeve-ke' ) );
            return;
        }

        // Sanitize and validate data
        $username = sanitize_user( $_POST['username'] );
        $email = sanitize_email( $_POST['email'] );
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = sanitize_text_field( $_POST['first_name'] );
        $last_name = sanitize_text_field( $_POST['last_name'] );
        $phone = sanitize_text_field( $_POST['phone'] );
        $location = sanitize_text_field( $_POST['location'] );
        $experience_level = sanitize_text_field( $_POST['experience_level'] );
        $desired_industry = sanitize_text_field( $_POST['desired_industry'] );
        $skills = sanitize_text_field( $_POST['skills'] );
        $bio = sanitize_textarea_field( $_POST['bio'] );
        $terms_accepted = isset( $_POST['terms_accepted'] );
        $job_alerts = isset( $_POST['job_alerts'] );
        $email_notifications = isset( $_POST['email_notifications'] );

        // Validation
        $errors = array();

        if ( empty( $username ) ) {
            $errors[] = __( 'Username is required.', 'sleeve-ke' );
        } elseif ( username_exists( $username ) ) {
            $errors[] = __( 'Username already exists.', 'sleeve-ke' );
        }

        if ( empty( $email ) || ! is_email( $email ) ) {
            $errors[] = __( 'Valid email address is required.', 'sleeve-ke' );
        } elseif ( email_exists( $email ) ) {
            $errors[] = __( 'Email address already registered.', 'sleeve-ke' );
        }

        if ( empty( $password ) || strlen( $password ) < 8 ) {
            $errors[] = __( 'Password must be at least 8 characters long.', 'sleeve-ke' );
        }

        if ( $password !== $confirm_password ) {
            $errors[] = __( 'Passwords do not match.', 'sleeve-ke' );
        }

        if ( empty( $first_name ) ) {
            $errors[] = __( 'First name is required.', 'sleeve-ke' );
        }

        if ( empty( $last_name ) ) {
            $errors[] = __( 'Last name is required.', 'sleeve-ke' );
        }

        if ( empty( $location ) ) {
            $errors[] = __( 'Location is required.', 'sleeve-ke' );
        }

        if ( empty( $experience_level ) ) {
            $errors[] = __( 'Experience level is required.', 'sleeve-ke' );
        }

        if ( ! $terms_accepted ) {
            $errors[] = __( 'You must accept the terms and conditions.', 'sleeve-ke' );
        }

        if ( ! empty( $errors ) ) {
            $this->redirect_with_error( implode( ' ', $errors ) );
            return;
        }

        // Create user account
        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            $this->redirect_with_error( $user_id->get_error_message() );
            return;
        }

        // Set user role
        $user = new WP_User( $user_id );
        $user->set_role( 'candidate' );

        // Update user profile
        wp_update_user( array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ) );

        // Save candidate meta data
        update_user_meta( $user_id, 'phone', $phone );
        update_user_meta( $user_id, 'location', $location );
        update_user_meta( $user_id, 'experience_level', $experience_level );
        update_user_meta( $user_id, 'desired_industry', $desired_industry );
        update_user_meta( $user_id, 'skills', $skills );
        update_user_meta( $user_id, 'bio', $bio );
        update_user_meta( $user_id, 'job_alerts', $job_alerts );
        update_user_meta( $user_id, 'email_notifications', $email_notifications );
        update_user_meta( $user_id, 'account_status', 'active' );
        update_user_meta( $user_id, 'registration_date', current_time( 'mysql' ) );

        // Send notification emails
        $this->send_candidate_registration_notifications( $user_id, array(
            'username' => $username,
            'email' => $email,
            'full_name' => $first_name . ' ' . $last_name,
            'first_name' => $first_name
        ) );

        // Trigger action for other plugins/integrations
        do_action( 'sleeve_ke_candidate_registered', $user_id );

        // Redirect to success page
        $redirect_url = ! empty( $_POST['redirect_url'] ) ? $_POST['redirect_url'] : $_SERVER['REQUEST_URI'];
        $redirect_url = add_query_arg( 'candidate_registered', 1, $redirect_url );
        wp_redirect( $redirect_url );
        exit;
    }

    /**
     * Send employer registration notifications
     */
    private function send_employer_registration_notifications( $user_id, $data ) {
        // Send welcome email to employer
        if ( function_exists( 'sleeve_ke_send_notification' ) ) {
            sleeve_ke_send_notification( 
                'employer_registered', 
                $data['email'], 
                array(
                    'employer_name' => $data['contact_person'],
                    'company_name' => $data['company_name'],
                    'login_url' => wp_login_url()
                )
            );

            // Send admin notification
            if ( get_option( 'sleeve_ke_admin_new_employer', 1 ) ) {
                sleeve_ke_send_notification( 
                    'admin_notification', 
                    get_option( 'admin_email' ), 
                    array(
                        'message' => sprintf( 
                            __( 'New employer registered: %s (%s)', 'sleeve-ke' ), 
                            $data['company_name'], 
                            $data['contact_person'] 
                        ),
                        'employer_name' => $data['contact_person'],
                        'company_name' => $data['company_name']
                    )
                );
            }
        }

        // Trigger notification hook
        sleeve_ke_trigger_new_employer( $user_id );
    }

    /**
     * Send candidate registration notifications
     */
    private function send_candidate_registration_notifications( $user_id, $data ) {
        // Send welcome email to candidate
        if ( function_exists( 'sleeve_ke_send_notification' ) ) {
            sleeve_ke_send_notification( 
                'candidate_registered', 
                $data['email'], 
                array(
                    'candidate_name' => $data['full_name'],
                    'login_url' => wp_login_url(),
                    'jobs_url' => home_url( '/jobs' )
                )
            );

            // Send admin notification
            if ( get_option( 'sleeve_ke_admin_new_candidate', 1 ) ) {
                sleeve_ke_send_notification( 
                    'admin_notification', 
                    get_option( 'admin_email' ), 
                    array(
                        'message' => sprintf( 
                            __( 'New candidate registered: %s', 'sleeve-ke' ), 
                            $data['full_name'] 
                        ),
                        'candidate_name' => $data['full_name']
                    )
                );
            }
        }

        // Trigger notification hook
        sleeve_ke_trigger_new_candidate( $user_id );
    }

    /**
     * Redirect with error message
     */
    private function redirect_with_error( $error_message ) {
        $redirect_url = add_query_arg( 'registration_error', urlencode( $error_message ), $_SERVER['REQUEST_URI'] );
        wp_redirect( $redirect_url );
        exit;
    }

    /**
     * Get industries list
     */
    private function get_industries() {
        return array(
            'technology' => __( 'Technology & IT', 'sleeve-ke' ),
            'healthcare' => __( 'Healthcare & Medical', 'sleeve-ke' ),
            'finance' => __( 'Finance & Banking', 'sleeve-ke' ),
            'education' => __( 'Education & Training', 'sleeve-ke' ),
            'manufacturing' => __( 'Manufacturing & Production', 'sleeve-ke' ),
            'retail' => __( 'Retail & E-commerce', 'sleeve-ke' ),
            'hospitality' => __( 'Hospitality & Tourism', 'sleeve-ke' ),
            'agriculture' => __( 'Agriculture & Farming', 'sleeve-ke' ),
            'construction' => __( 'Construction & Real Estate', 'sleeve-ke' ),
            'telecommunications' => __( 'Telecommunications & Media', 'sleeve-ke' ),
            'legal' => __( 'Legal & Professional Services', 'sleeve-ke' ),
            'marketing' => __( 'Marketing & Advertising', 'sleeve-ke' ),
            'consulting' => __( 'Business Consulting', 'sleeve-ke' ),
            'nonprofit' => __( 'Non-Profit & NGO', 'sleeve-ke' ),
            'government' => __( 'Government & Public Sector', 'sleeve-ke' ),
            'transport' => __( 'Transportation & Logistics', 'sleeve-ke' ),
            'energy' => __( 'Energy & Environment', 'sleeve-ke' ),
            'entertainment' => __( 'Entertainment & Media', 'sleeve-ke' ),
            'automotive' => __( 'Automotive', 'sleeve-ke' ),
            'other' => __( 'Other', 'sleeve-ke' )
        );
    }

    /**
     * Get company sizes list
     */
    private function get_company_sizes() {
        return array(
            'startup' => __( 'Startup (1-10 employees)', 'sleeve-ke' ),
            'small' => __( 'Small (11-50 employees)', 'sleeve-ke' ),
            'medium' => __( 'Medium (51-200 employees)', 'sleeve-ke' ),
            'large' => __( 'Large (201-1000 employees)', 'sleeve-ke' ),
            'enterprise' => __( 'Enterprise (1000+ employees)', 'sleeve-ke' )
        );
    }

    /**
     * Get experience levels list
     */
    private function get_experience_levels() {
        return array(
            'entry' => __( 'Entry Level (0-2 years)', 'sleeve-ke' ),
            'junior' => __( 'Junior (2-4 years)', 'sleeve-ke' ),
            'mid' => __( 'Mid Level (4-7 years)', 'sleeve-ke' ),
            'senior' => __( 'Senior (7-10 years)', 'sleeve-ke' ),
            'lead' => __( 'Lead/Principal (10+ years)', 'sleeve-ke' ),
            'executive' => __( 'Executive/C-Level', 'sleeve-ke' )
        );
    }
}