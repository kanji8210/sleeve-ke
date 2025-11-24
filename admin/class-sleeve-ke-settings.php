<?php
/**
 * Settings page for Sleeve KE plugin
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

class Sleeve_KE_Settings {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ), 100 );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add settings page to menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'sleeve-ke',
            __( 'Settings', 'sleeve-ke' ),
            __( 'Settings', 'sleeve-ke' ),
            'manage_options',
            'sleeve-ke-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // Register settings
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_employer_registration_page' );
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_candidate_registration_page' );
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_dashboard_page' );
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_jobs_page' );
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_custom_login_enabled' );
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_login_page' );
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_login_logo' );
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_login_redirect_employer' );
        register_setting( 'sleeve_ke_settings', 'sleeve_ke_login_redirect_candidate' );

        // Page URLs Section
        add_settings_section(
            'sleeve_ke_pages_section',
            __( 'Page URLs', 'sleeve-ke' ),
            array( $this, 'pages_section_callback' ),
            'sleeve_ke_settings'
        );

        add_settings_field(
            'sleeve_ke_employer_registration_page',
            __( 'Employer Registration Page', 'sleeve-ke' ),
            array( $this, 'page_select_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_pages_section',
            array( 'option_name' => 'sleeve_ke_employer_registration_page' )
        );

        add_settings_field(
            'sleeve_ke_candidate_registration_page',
            __( 'Candidate Registration Page', 'sleeve-ke' ),
            array( $this, 'page_select_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_pages_section',
            array( 'option_name' => 'sleeve_ke_candidate_registration_page' )
        );

        add_settings_field(
            'sleeve_ke_dashboard_page',
            __( 'Dashboard Page', 'sleeve-ke' ),
            array( $this, 'page_select_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_pages_section',
            array( 'option_name' => 'sleeve_ke_dashboard_page' )
        );

        add_settings_field(
            'sleeve_ke_jobs_page',
            __( 'Jobs Listing Page', 'sleeve-ke' ),
            array( $this, 'page_select_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_pages_section',
            array( 'option_name' => 'sleeve_ke_jobs_page' )
        );

        // Custom Login Section
        add_settings_section(
            'sleeve_ke_login_section',
            __( 'Custom Login Settings', 'sleeve-ke' ),
            array( $this, 'login_section_callback' ),
            'sleeve_ke_settings'
        );

        add_settings_field(
            'sleeve_ke_custom_login_enabled',
            __( 'Enable Custom Login Page', 'sleeve-ke' ),
            array( $this, 'checkbox_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_login_section',
            array( 'option_name' => 'sleeve_ke_custom_login_enabled' )
        );

        add_settings_field(
            'sleeve_ke_login_page',
            __( 'Custom Login Page', 'sleeve-ke' ),
            array( $this, 'page_select_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_login_section',
            array( 'option_name' => 'sleeve_ke_login_page' )
        );

        add_settings_field(
            'sleeve_ke_login_logo',
            __( 'Login Logo URL', 'sleeve-ke' ),
            array( $this, 'text_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_login_section',
            array( 'option_name' => 'sleeve_ke_login_logo' )
        );

        add_settings_field(
            'sleeve_ke_login_redirect_employer',
            __( 'Employer Login Redirect', 'sleeve-ke' ),
            array( $this, 'page_select_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_login_section',
            array( 'option_name' => 'sleeve_ke_login_redirect_employer' )
        );

        add_settings_field(
            'sleeve_ke_login_redirect_candidate',
            __( 'Candidate Login Redirect', 'sleeve-ke' ),
            array( $this, 'page_select_callback' ),
            'sleeve_ke_settings',
            'sleeve_ke_login_section',
            array( 'option_name' => 'sleeve_ke_login_redirect_candidate' )
        );
    }

    /**
     * Pages section callback
     */
    public function pages_section_callback() {
        echo '<p>' . __( 'Select the pages for registration, dashboard, and job listings. These pages should contain the appropriate shortcodes.', 'sleeve-ke' ) . '</p>';
    }

    /**
     * Login section callback
     */
    public function login_section_callback() {
        echo '<p>' . __( 'Customize the login experience with your branding and redirect users based on their role.', 'sleeve-ke' ) . '</p>';
    }

    /**
     * Page select dropdown callback
     */
    public function page_select_callback( $args ) {
        $option_name = $args['option_name'];
        $value = get_option( $option_name, '' );
        
        $pages = get_pages();
        
        echo '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $option_name ) . '" class="regular-text">';
        echo '<option value="">' . __( '— Select Page —', 'sleeve-ke' ) . '</option>';
        
        foreach ( $pages as $page ) {
            $selected = selected( $value, $page->ID, false );
            echo '<option value="' . esc_attr( $page->ID ) . '" ' . $selected . '>' . esc_html( $page->post_title ) . '</option>';
        }
        
        echo '</select>';
        
        if ( $value ) {
            $page_url = get_permalink( $value );
            echo '<p class="description">' . sprintf( __( 'URL: %s', 'sleeve-ke' ), '<a href="' . esc_url( $page_url ) . '" target="_blank">' . esc_html( $page_url ) . '</a>' ) . '</p>';
        }
    }

    /**
     * Checkbox callback
     */
    public function checkbox_callback( $args ) {
        $option_name = $args['option_name'];
        $value = get_option( $option_name, false );
        
        echo '<label>';
        echo '<input type="checkbox" name="' . esc_attr( $option_name ) . '" value="1" ' . checked( 1, $value, false ) . ' />';
        echo ' ' . __( 'Enable custom login page instead of default WordPress login', 'sleeve-ke' );
        echo '</label>';
    }

    /**
     * Text field callback
     */
    public function text_callback( $args ) {
        $option_name = $args['option_name'];
        $value = get_option( $option_name, '' );
        
        echo '<input type="text" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $option_name ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
        
        if ( $option_name === 'sleeve_ke_login_logo' ) {
            echo '<button type="button" class="button sleeve-ke-upload-logo" style="margin-left: 10px;">' . __( 'Upload Logo', 'sleeve-ke' ) . '</button>';
            echo '<p class="description">' . __( 'Upload or enter the URL of your logo for the login page. Recommended size: 320x80px', 'sleeve-ke' ) . '</p>';
            
            if ( $value ) {
                echo '<p><img src="' . esc_url( $value ) . '" style="max-width: 320px; max-height: 80px; margin-top: 10px;" /></p>';
            }
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Handle page creation
        if ( isset( $_POST['sleeve_ke_create_pages'] ) && check_admin_referer( 'sleeve_ke_create_pages' ) ) {
            $this->create_default_pages();
            echo '<div class="notice notice-success"><p>' . __( 'Default pages created successfully!', 'sleeve-ke' ) . '</p></div>';
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <?php settings_errors(); ?>

            <div class="sleeve-ke-settings-container" style="max-width: 1200px;">
                
                <!-- Quick Setup Box -->
                <div class="postbox" style="margin-top: 20px;">
                    <div class="postbox-header">
                        <h2><?php _e( '🚀 Quick Setup', 'sleeve-ke' ); ?></h2>
                    </div>
                    <div class="inside">
                        <p><?php _e( 'Need to create the default pages? Click the button below to automatically create all required pages with the correct shortcodes.', 'sleeve-ke' ); ?></p>
                        
                        <form method="post" action="">
                            <?php wp_nonce_field( 'sleeve_ke_create_pages' ); ?>
                            <button type="submit" name="sleeve_ke_create_pages" class="button button-primary button-large">
                                <span class="dashicons dashicons-admin-page" style="margin-top: 4px;"></span>
                                <?php _e( 'Create Default Pages', 'sleeve-ke' ); ?>
                            </button>
                        </form>
                        
                        <p class="description" style="margin-top: 15px;">
                            <?php _e( 'This will create:', 'sleeve-ke' ); ?>
                            <ul style="margin-left: 20px; list-style: disc;">
                                <li><?php _e( 'Dashboard page with [sleeve_ke_dashboard]', 'sleeve-ke' ); ?></li>
                                <li><?php _e( 'Employer Registration page with [sleeve_ke_employer_registration]', 'sleeve-ke' ); ?></li>
                                <li><?php _e( 'Candidate Registration page with [sleeve_ke_candidate_registration]', 'sleeve-ke' ); ?></li>
                                <li><?php _e( 'Jobs page with [sleeve_ke_jobs]', 'sleeve-ke' ); ?></li>
                                <li><?php _e( 'Login page with [sleeve_ke_login]', 'sleeve-ke' ); ?></li>
                            </ul>
                        </p>
                    </div>
                </div>

                <!-- Settings Form -->
                <form action="options.php" method="post">
                    <?php
                    settings_fields( 'sleeve_ke_settings' );
                    do_settings_sections( 'sleeve_ke_settings' );
                    submit_button( __( 'Save Settings', 'sleeve-ke' ) );
                    ?>
                </form>

                <!-- Info Box -->
                <div class="postbox" style="margin-top: 30px;">
                    <div class="postbox-header">
                        <h2><?php _e( '📘 Shortcode Reference', 'sleeve-ke' ); ?></h2>
                    </div>
                    <div class="inside">
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><?php _e( 'Page Type', 'sleeve-ke' ); ?></th>
                                    <th><?php _e( 'Shortcode', 'sleeve-ke' ); ?></th>
                                    <th><?php _e( 'Description', 'sleeve-ke' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong><?php _e( 'Dashboard', 'sleeve-ke' ); ?></strong></td>
                                    <td><code>[sleeve_ke_dashboard]</code></td>
                                    <td><?php _e( 'Universal dashboard that adapts to user role', 'sleeve-ke' ); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e( 'Employer Registration', 'sleeve-ke' ); ?></strong></td>
                                    <td><code>[sleeve_ke_employer_registration]</code></td>
                                    <td><?php _e( 'Registration form for employers', 'sleeve-ke' ); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e( 'Candidate Registration', 'sleeve-ke' ); ?></strong></td>
                                    <td><code>[sleeve_ke_candidate_registration]</code></td>
                                    <td><?php _e( 'Registration form for candidates', 'sleeve-ke' ); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e( 'Jobs Listing', 'sleeve-ke' ); ?></strong></td>
                                    <td><code>[sleeve_ke_jobs]</code></td>
                                    <td><?php _e( 'Display job listings with filters', 'sleeve-ke' ); ?></td>
                                </tr>
                                <tr>
                                    <td><strong><?php _e( 'Login Page', 'sleeve-ke' ); ?></strong></td>
                                    <td><code>[sleeve_ke_login]</code></td>
                                    <td><?php _e( 'Custom login form', 'sleeve-ke' ); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.sleeve-ke-upload-logo').on('click', function(e) {
                e.preventDefault();
                
                var button = $(this);
                var input = button.prev('input');
                
                var mediaUploader = wp.media({
                    title: '<?php _e( 'Select Logo', 'sleeve-ke' ); ?>',
                    button: {
                        text: '<?php _e( 'Use this logo', 'sleeve-ke' ); ?>'
                    },
                    multiple: false
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    input.val(attachment.url);
                });
                
                mediaUploader.open();
            });
        });
        </script>

        <style>
        .sleeve-ke-settings-container .postbox {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .sleeve-ke-settings-container .postbox-header {
            border-bottom: 1px solid #ccd0d4;
            padding: 12px;
        }
        .sleeve-ke-settings-container .postbox-header h2 {
            margin: 0;
            font-size: 14px;
            line-height: 1.4;
        }
        .sleeve-ke-settings-container .inside {
            padding: 12px;
        }
        </style>
        <?php
    }

    /**
     * Create default pages
     */
    private function create_default_pages() {
        $pages = array(
            array(
                'title' => 'Dashboard',
                'slug' => 'dashboard',
                'content' => '[sleeve_ke_dashboard]',
                'option' => 'sleeve_ke_dashboard_page'
            ),
            array(
                'title' => 'Register as Employer',
                'slug' => 'employer-registration',
                'content' => '[sleeve_ke_employer_registration]',
                'option' => 'sleeve_ke_employer_registration_page'
            ),
            array(
                'title' => 'Register as Candidate',
                'slug' => 'candidate-registration',
                'content' => '[sleeve_ke_candidate_registration]',
                'option' => 'sleeve_ke_candidate_registration_page'
            ),
            array(
                'title' => 'Jobs',
                'slug' => 'jobs',
                'content' => '[sleeve_ke_jobs]',
                'option' => 'sleeve_ke_jobs_page'
            ),
            array(
                'title' => 'Login',
                'slug' => 'login',
                'content' => '[sleeve_ke_login]',
                'option' => 'sleeve_ke_login_page'
            )
        );

        foreach ( $pages as $page_data ) {
            // Check if page already exists
            $existing_page = get_page_by_path( $page_data['slug'] );
            
            if ( ! $existing_page ) {
                $page_id = wp_insert_post( array(
                    'post_title' => $page_data['title'],
                    'post_name' => $page_data['slug'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page'
                ) );

                if ( $page_id && ! is_wp_error( $page_id ) ) {
                    update_option( $page_data['option'], $page_id );
                }
            } else {
                update_option( $page_data['option'], $existing_page->ID );
            }
        }
    }
}
