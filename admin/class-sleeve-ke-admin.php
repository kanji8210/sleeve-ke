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
     * Initialize the class and set its properties.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
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
        // Only show menu to users with appropriate capabilities
        if ( ! current_user_can( 'manage_applications' ) && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Main menu
        add_menu_page(
            __( 'Sleeve KE', 'sleeve-ke' ),
            __( 'Sleeve KE', 'sleeve-ke' ),
            'manage_applications',
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
            'manage_applications',
            'sleeve-ke',
            array( $this, 'display_dashboard' )
        );

        // Applications submenu
        add_submenu_page(
            'sleeve-ke',
            __( 'Applications', 'sleeve-ke' ),
            __( 'Applications', 'sleeve-ke' ),
            'manage_applications',
            'sleeve-ke-applications',
            array( $this, 'display_applications' )
        );

        // Jobs submenu
        add_submenu_page(
            'sleeve-ke',
            __( 'Jobs', 'sleeve-ke' ),
            __( 'Jobs', 'sleeve-ke' ),
            'manage_applications',
            'sleeve-ke-jobs',
            array( $this, 'display_jobs' )
        );

        // Candidates submenu
        add_submenu_page(
            'sleeve-ke',
            __( 'Candidates', 'sleeve-ke' ),
            __( 'Candidates', 'sleeve-ke' ),
            'manage_applications',
            'sleeve-ke-candidates',
            array( $this, 'display_candidates' )
        );

        // Employers submenu
        add_submenu_page(
            'sleeve-ke',
            __( 'Employers', 'sleeve-ke' ),
            __( 'Employers', 'sleeve-ke' ),
            'manage_applications',
            'sleeve-ke-employers',
            array( $this, 'display_employers' )
        );

        // Countries submenu
        add_submenu_page(
            'sleeve-ke',
            __( 'Countries', 'sleeve-ke' ),
            __( 'Countries', 'sleeve-ke' ),
            'manage_applications',
            'sleeve-ke-countries',
            array( $this, 'display_countries' )
        );

        // Payments submenu
        add_submenu_page(
            'sleeve-ke',
            __( 'Payments', 'sleeve-ke' ),
            __( 'Payments', 'sleeve-ke' ),
            'manage_applications',
            'sleeve-ke-payments',
            array( $this, 'display_payments' )
        );
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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Manage job applications here.', 'sleeve-ke' ); ?></p>
            <!-- Applications list table will be implemented here -->
        </div>
        <?php
    }

    /**
     * Display the jobs management page.
     */
    public function display_jobs() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Manage job postings here.', 'sleeve-ke' ); ?></p>
            <!-- Jobs list table will be implemented here -->
        </div>
        <?php
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
}
