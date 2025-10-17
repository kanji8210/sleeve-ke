<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for admin area including
 * management of applications, jobs, candidates, employers, and payments.
 */
class Sleeve_KE_Admin {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * The applications manager instance.
     */
    private $applications_manager;

    /**
     * The jobs manager instance.
     */
    private $jobs_manager;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        
        // Load applications manager
        require_once SLEEVE_KE_PLUGIN_DIR . 'admin/class-sleeve-ke-applications.php';
        $this->applications_manager = new Sleeve_KE_Applications();

        // Load jobs manager
        require_once SLEEVE_KE_PLUGIN_DIR . 'admin/class-sleeve-ke-jobs.php';
        $this->jobs_manager = new Sleeve_KE_Jobs();

        // Add AJAX hooks
        add_action( 'wp_ajax_update_application_status', array( $this->applications_manager, 'ajax_update_application_status' ) );
        add_action( 'wp_ajax_update_job_status', array( $this->jobs_manager, 'ajax_update_job_status' ) );
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            SLEEVE_KE_PLUGIN_URL . 'assets/css/sleeve-ke-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            SLEEVE_KE_PLUGIN_URL . 'assets/js/sleeve-ke-admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }

    /**
     * Add admin menu for Sleeve Admin role.
     */
    public function add_admin_menu() {
        // Ensure administrator has the required capabilities
        $admin_role = get_role( 'administrator' );
        if ( $admin_role && ! $admin_role->has_cap( 'manage_applications' ) ) {
            $admin_role->add_cap( 'manage_applications' );
            $admin_role->add_cap( 'manage_jobs' );
            $admin_role->add_cap( 'manage_candidates' );
            $admin_role->add_cap( 'manage_employers' );
            $admin_role->add_cap( 'manage_payments' );
        }
        
        // Debug: Check current user capabilities
        if ( is_admin() && current_user_can( 'manage_options' ) ) {
            // Force show debug info in admin if there are issues
            if ( isset( $_GET['sleeve_debug'] ) ) {
                $current_user = wp_get_current_user();
                echo '<div class="notice notice-info"><p>Current user: ' . $current_user->user_login . '</p>';
                echo '<p>Roles: ' . implode( ', ', $current_user->roles ) . '</p>';
                echo '<p>Can manage_options: ' . ( current_user_can( 'manage_options' ) ? 'Yes' : 'No' ) . '</p>';
                echo '<p>Can manage_applications: ' . ( current_user_can( 'manage_applications' ) ? 'Yes' : 'No' ) . '</p></div>';
            }
        }
        
        // Check if user has appropriate capabilities
        $can_manage = current_user_can( 'manage_options' );
        $is_employer = in_array( 'employer', wp_get_current_user()->roles );
        $is_sleve_admin = in_array( 'sleve_admin', wp_get_current_user()->roles );
        
        if ( ! $can_manage && ! $is_employer && ! $is_sleve_admin ) {
            return;
        }

        // Main menu for administrators and sleve_admins
        if ( $can_manage || $is_sleve_admin ) {
            add_menu_page(
                __( 'Sleeve KE', 'sleeve-ke' ),
                __( 'Sleeve KE', 'sleeve-ke' ),
                'manage_options',
                'sleeve-ke',
                array( $this, 'display_dashboard' ),
                'dashicons-businessman',
                30
            );

            // Dashboard submenu
            add_submenu_page(
                'sleeve-ke',
                __( 'Dashboard', 'sleeve-ke' ),
                __( 'Dashboard', 'sleeve-ke' ),
                'manage_options',
                'sleeve-ke',
                array( $this, 'display_dashboard' )
            );

            // Applications submenu
            add_submenu_page(
                'sleeve-ke',
                __( 'Applications', 'sleeve-ke' ),
                __( 'Applications', 'sleeve-ke' ),
                'manage_options',
                'sleeve-ke-applications',
                array( $this, 'display_applications' )
            );

            // Jobs submenu
            add_submenu_page(
                'sleeve-ke',
                __( 'Jobs', 'sleeve-ke' ),
                __( 'Jobs', 'sleeve-ke' ),
                'manage_options',
                'sleeve-ke-jobs',
                array( $this, 'display_jobs' )
            );

            // Candidates submenu
            add_submenu_page(
                'sleeve-ke',
                __( 'Candidates', 'sleeve-ke' ),
                __( 'Candidates', 'sleeve-ke' ),
                'manage_options',
                'sleeve-ke-candidates',
                array( $this, 'display_candidates' )
            );

            // Employers submenu
            add_submenu_page(
                'sleeve-ke',
                __( 'Employers', 'sleeve-ke' ),
                __( 'Employers', 'sleeve-ke' ),
                'manage_options',
                'sleeve-ke-employers',
                array( $this, 'display_employers' )
            );

            // Countries submenu
            add_submenu_page(
                'sleeve-ke',
                __( 'Countries', 'sleeve-ke' ),
                __( 'Countries', 'sleeve-ke' ),
                'manage_options',
                'sleeve-ke-countries',
                array( $this, 'display_countries' )
            );

            // Payments submenu
            add_submenu_page(
                'sleeve-ke',
                __( 'Payments', 'sleeve-ke' ),
                __( 'Payments', 'sleeve-ke' ),
                'manage_options',
                'sleeve-ke-payments',
                array( $this, 'display_payments' )
            );
        }

        // Employer menu - limited access
        if ( $is_employer ) {
            add_menu_page(
                __( 'Job Management', 'sleeve-ke' ),
                __( 'Jobs', 'sleeve-ke' ),
                'read',
                'sleeve-ke-employer-jobs',
                array( $this, 'display_jobs' ),
                'dashicons-portfolio',
                30
            );

            // Applications for employers
            add_submenu_page(
                'sleeve-ke-employer-jobs',
                __( 'My Job Applications', 'sleeve-ke' ),
                __( 'Applications', 'sleeve-ke' ),
                'read',
                'sleeve-ke-employer-applications',
                array( $this, 'display_applications' )
            );
        }
    }

    /**
     * Display the dashboard page.
     */
    public function display_dashboard() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <div class="sleeve-ke-dashboard">
                <h2><?php esc_html_e( 'Welcome to Sleeve KE', 'sleeve-ke' ); ?></h2>
                <p><?php esc_html_e( 'Manage your job portal from this dashboard.', 'sleeve-ke' ); ?></p>
                <div class="sleeve-ke-stats">
                    <div class="stat-box">
                        <h3><?php esc_html_e( 'Total Applications', 'sleeve-ke' ); ?></h3>
                        <p class="stat-number">0</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php esc_html_e( 'Total Jobs', 'sleeve-ke' ); ?></h3>
                        <p class="stat-number">0</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php esc_html_e( 'Total Candidates', 'sleeve-ke' ); ?></h3>
                        <p class="stat-number">0</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php esc_html_e( 'Total Employers', 'sleeve-ke' ); ?></h3>
                        <p class="stat-number">0</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Display the applications management page.
     */
    public function display_applications() {
        // Check if we're viewing a specific application
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'view' && isset( $_GET['id'] ) ) {
            $application_id = intval( $_GET['id'] );
            $this->applications_manager->display_application_view( $application_id );
        } else {
            // Display the main applications list
            $this->applications_manager->display_page();
        }
    }

    /**
     * Display the jobs management page.
     */
    public function display_jobs() {
        $this->jobs_manager->display_page();
    }

    /**
     * Display the candidates management page.
     */
    public function display_candidates() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Manage candidates here.', 'sleeve-ke' ); ?></p>
            <!-- Candidates list table will be implemented here -->
        </div>
        <?php
    }

    /**
     * Display the employers management page.
     */
    public function display_employers() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Manage employers here.', 'sleeve-ke' ); ?></p>
            <!-- Employers list table will be implemented here -->
        </div>
        <?php
    }

    /**
     * Display the countries management page.
     */
    public function display_countries() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Manage countries and locations here.', 'sleeve-ke' ); ?></p>
            
            <div class="sleeve-ke-countries">
                <h2><?php esc_html_e( 'Countries List', 'sleeve-ke' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Country', 'sleeve-ke' ); ?></th>
                            <th><?php esc_html_e( 'Region', 'sleeve-ke' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'sleeve-ke' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'sleeve-ke' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $countries = array(
                            array( 'name' => 'Kenya', 'region' => 'East Africa', 'status' => 'Active' ),
                            array( 'name' => 'Uganda', 'region' => 'East Africa', 'status' => 'Active' ),
                            array( 'name' => 'Tanzania', 'region' => 'East Africa', 'status' => 'Active' ),
                            array( 'name' => 'Rwanda', 'region' => 'East Africa', 'status' => 'Active' ),
                            array( 'name' => 'Ethiopia', 'region' => 'East Africa', 'status' => 'Active' ),
                            array( 'name' => 'Other', 'region' => 'International', 'status' => 'Active' ),
                        );
                        
                        foreach ( $countries as $country ) :
                        ?>
                        <tr>
                            <td><?php echo esc_html( $country['name'] ); ?></td>
                            <td><?php echo esc_html( $country['region'] ); ?></td>
                            <td><span class="status-active"><?php echo esc_html( $country['status'] ); ?></span></td>
                            <td>
                                <button class="button button-small"><?php esc_html_e( 'Edit', 'sleeve-ke' ); ?></button>
                                <button class="button button-small"><?php esc_html_e( 'Manage Cities', 'sleeve-ke' ); ?></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <br>
                <h3><?php esc_html_e( 'Add New Country', 'sleeve-ke' ); ?></h3>
                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="country_name"><?php esc_html_e( 'Country Name', 'sleeve-ke' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="country_name" name="country_name" class="regular-text" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="country_region"><?php esc_html_e( 'Region', 'sleeve-ke' ); ?></label>
                            </th>
                            <td>
                                <select id="country_region" name="country_region" required>
                                    <option value=""><?php esc_html_e( 'Select Region', 'sleeve-ke' ); ?></option>
                                    <option value="East Africa"><?php esc_html_e( 'East Africa', 'sleeve-ke' ); ?></option>
                                    <option value="West Africa"><?php esc_html_e( 'West Africa', 'sleeve-ke' ); ?></option>
                                    <option value="North Africa"><?php esc_html_e( 'North Africa', 'sleeve-ke' ); ?></option>
                                    <option value="Southern Africa"><?php esc_html_e( 'Southern Africa', 'sleeve-ke' ); ?></option>
                                    <option value="International"><?php esc_html_e( 'International', 'sleeve-ke' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( __( 'Add Country', 'sleeve-ke' ) ); ?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Display the payments management page.
     */
    public function display_payments() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Manage payments here.', 'sleeve-ke' ); ?></p>
            <!-- Payments list table will be implemented here -->
        </div>
        <?php
    }
    
    /**
     * Handle AJAX request to update application status
     */
    public function ajax_update_application_status() {
        $this->applications_manager->ajax_update_application_status();
    }
}
