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
        
        if ( ! $can_manage ) {
            return;
        }

        // Main menu
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
        // Handle form submissions
        if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['sleeve_nonce'], 'sleeve_applications' ) ) {
            $this->handle_application_actions();
        }
        
        // Get applications data (mock data for now)
        $applications = $this->get_applications_data();
        $statuses = array(
            'pending' => __( 'Pending', 'sleeve-ke' ),
            'reviewing' => __( 'Under Review', 'sleeve-ke' ),
            'interview' => __( 'Interview Scheduled', 'sleeve-ke' ),
            'accepted' => __( 'Accepted', 'sleeve-ke' ),
            'rejected' => __( 'Rejected', 'sleeve-ke' )
        );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <!-- Filter and Search Section -->
            <div class="sleeve-ke-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="sleeve-ke-applications" />
                    
                    <div class="filter-row">
                        <input type="text" name="search" placeholder="<?php esc_attr_e( 'Search by candidate name or job title...', 'sleeve-ke' ); ?>" 
                               value="<?php echo esc_attr( isset( $_GET['search'] ) ? $_GET['search'] : '' ); ?>" />
                        
                        <select name="status">
                            <option value=""><?php esc_html_e( 'All Statuses', 'sleeve-ke' ); ?></option>
                            <?php foreach ( $statuses as $status_key => $status_label ) : ?>
                                <option value="<?php echo esc_attr( $status_key ); ?>" 
                                        <?php selected( isset( $_GET['status'] ) ? $_GET['status'] : '', $status_key ); ?>>
                                    <?php echo esc_html( $status_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="job_id">
                            <option value=""><?php esc_html_e( 'All Jobs', 'sleeve-ke' ); ?></option>
                            <?php
                            $jobs = $this->get_jobs_for_filter();
                            foreach ( $jobs as $job ) :
                            ?>
                                <option value="<?php echo esc_attr( $job['id'] ); ?>" 
                                        <?php selected( isset( $_GET['job_id'] ) ? $_GET['job_id'] : '', $job['id'] ); ?>>
                                    <?php echo esc_html( $job['title'] ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php submit_button( __( 'Filter', 'sleeve-ke' ), 'secondary', 'filter', false ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-applications' ) ); ?>" class="button">
                            <?php esc_html_e( 'Clear', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Applications Table -->
            <form method="post" action="">
                <?php wp_nonce_field( 'sleeve_applications', 'sleeve_nonce' ); ?>
                
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="bulk_action">
                            <option value=""><?php esc_html_e( 'Bulk Actions', 'sleeve-ke' ); ?></option>
                            <option value="approve"><?php esc_html_e( 'Mark as Reviewing', 'sleeve-ke' ); ?></option>
                            <option value="interview"><?php esc_html_e( 'Schedule Interview', 'sleeve-ke' ); ?></option>
                            <option value="accept"><?php esc_html_e( 'Accept', 'sleeve-ke' ); ?></option>
                            <option value="reject"><?php esc_html_e( 'Reject', 'sleeve-ke' ); ?></option>
                            <option value="delete"><?php esc_html_e( 'Delete', 'sleeve-ke' ); ?></option>
                        </select>
                        <?php submit_button( __( 'Apply', 'sleeve-ke' ), 'action', 'apply_bulk_action', false ); ?>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped sleeve-ke-applications-table">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all" />
                            </td>
                            <th class="manage-column"><?php esc_html_e( 'Candidate', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Job Title', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Applied Date', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Status', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Score', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Actions', 'sleeve-ke' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty( $applications ) ) : ?>
                            <tr>
                                <td colspan="7" class="no-items">
                                    <?php esc_html_e( 'No applications found.', 'sleeve-ke' ); ?>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ( $applications as $application ) : ?>
                                <tr>
                                    <th class="check-column">
                                        <input type="checkbox" name="application_ids[]" value="<?php echo esc_attr( $application['id'] ); ?>" />
                                    </th>
                                    <td>
                                        <strong><?php echo esc_html( $application['candidate_name'] ); ?></strong><br>
                                        <small><?php echo esc_html( $application['candidate_email'] ); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html( $application['job_title'] ); ?></strong><br>
                                        <small><?php echo esc_html( $application['company_name'] ); ?></small>
                                    </td>
                                    <td><?php echo esc_html( $application['applied_date'] ); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr( $application['status'] ); ?>">
                                            <?php echo esc_html( $statuses[ $application['status'] ] ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ( $application['score'] ) : ?>
                                            <div class="score-bar">
                                                <div class="score-fill" style="width: <?php echo esc_attr( $application['score'] ); ?>%"></div>
                                                <span class="score-text"><?php echo esc_html( $application['score'] ); ?>%</span>
                                            </div>
                                        <?php else : ?>
                                            <span class="no-score"><?php esc_html_e( 'Not scored', 'sleeve-ke' ); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-applications&action=view&id=' . $application['id'] ) ); ?>" 
                                               class="button button-small"><?php esc_html_e( 'View', 'sleeve-ke' ); ?></a>
                                            
                                            <select class="status-select" data-application-id="<?php echo esc_attr( $application['id'] ); ?>">
                                                <?php foreach ( $statuses as $status_key => $status_label ) : ?>
                                                    <option value="<?php echo esc_attr( $status_key ); ?>" 
                                                            <?php selected( $application['status'], $status_key ); ?>>
                                                        <?php echo esc_html( $status_label ); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-applications&action=delete&id=' . $application['id'] ) ); ?>" 
                                               class="button button-small button-link-delete" 
                                               onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this application?', 'sleeve-ke' ); ?>')">
                                                <?php esc_html_e( 'Delete', 'sleeve-ke' ); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
            
            <!-- Statistics Section -->
            <div class="sleeve-ke-applications-stats">
                <h3><?php esc_html_e( 'Application Statistics', 'sleeve-ke' ); ?></h3>
                <div class="stats-grid">
                    <?php
                    $stats = $this->get_application_stats();
                    foreach ( $stats as $stat ) :
                    ?>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo esc_html( $stat['count'] ); ?></div>
                            <div class="stat-label"><?php echo esc_html( $stat['label'] ); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Handle status change
            $('.status-select').on('change', function() {
                var applicationId = $(this).data('application-id');
                var newStatus = $(this).val();
                
                $.post(ajaxurl, {
                    action: 'update_application_status',
                    application_id: applicationId,
                    status: newStatus,
                    nonce: '<?php echo wp_create_nonce( 'update_application_status' ); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating status');
                    }
                });
            });
            
            // Handle select all checkbox
            $('#cb-select-all').on('change', function() {
                $('input[name="application_ids[]"]').prop('checked', this.checked);
            });
        });
        </script>
        <?php
    }

    /**
     * Get applications data (mock data for demonstration)
     */
    private function get_applications_data() {
        // This would normally fetch from the database
        return array(
            array(
                'id' => 1,
                'candidate_name' => 'John Doe',
                'candidate_email' => 'john.doe@email.com',
                'job_title' => 'Senior PHP Developer',
                'company_name' => 'Tech Solutions Ltd',
                'applied_date' => '2025-10-15',
                'status' => 'pending',
                'score' => 85
            ),
            array(
                'id' => 2,
                'candidate_name' => 'Jane Smith',
                'candidate_email' => 'jane.smith@email.com',
                'job_title' => 'Frontend Developer',
                'company_name' => 'Creative Agency',
                'applied_date' => '2025-10-14',
                'status' => 'reviewing',
                'score' => 92
            ),
            array(
                'id' => 3,
                'candidate_name' => 'Mike Johnson',
                'candidate_email' => 'mike.johnson@email.com',
                'job_title' => 'Project Manager',
                'company_name' => 'Business Corp',
                'applied_date' => '2025-10-13',
                'status' => 'interview',
                'score' => 78
            ),
            array(
                'id' => 4,
                'candidate_name' => 'Sarah Wilson',
                'candidate_email' => 'sarah.wilson@email.com',
                'job_title' => 'UX Designer',
                'company_name' => 'Design Studio',
                'applied_date' => '2025-10-12',
                'status' => 'accepted',
                'score' => 95
            ),
            array(
                'id' => 5,
                'candidate_name' => 'David Brown',
                'candidate_email' => 'david.brown@email.com',
                'job_title' => 'Data Analyst',
                'company_name' => 'Analytics Inc',
                'applied_date' => '2025-10-11',
                'status' => 'rejected',
                'score' => 65
            )
        );
    }
    
    /**
     * Get jobs for filter dropdown
     */
    private function get_jobs_for_filter() {
        return array(
            array( 'id' => 1, 'title' => 'Senior PHP Developer' ),
            array( 'id' => 2, 'title' => 'Frontend Developer' ),
            array( 'id' => 3, 'title' => 'Project Manager' ),
            array( 'id' => 4, 'title' => 'UX Designer' ),
            array( 'id' => 5, 'title' => 'Data Analyst' )
        );
    }
    
    /**
     * Get application statistics
     */
    private function get_application_stats() {
        return array(
            array( 'count' => 5, 'label' => __( 'Total Applications', 'sleeve-ke' ) ),
            array( 'count' => 1, 'label' => __( 'Pending Review', 'sleeve-ke' ) ),
            array( 'count' => 1, 'label' => __( 'Under Review', 'sleeve-ke' ) ),
            array( 'count' => 1, 'label' => __( 'Interviews Scheduled', 'sleeve-ke' ) ),
            array( 'count' => 1, 'label' => __( 'Accepted', 'sleeve-ke' ) ),
            array( 'count' => 1, 'label' => __( 'Rejected', 'sleeve-ke' ) )
        );
    }
    
    /**
     * Handle application actions
     */
    private function handle_application_actions() {
        if ( isset( $_POST['apply_bulk_action'] ) && isset( $_POST['bulk_action'] ) && isset( $_POST['application_ids'] ) ) {
            $action = sanitize_text_field( $_POST['bulk_action'] );
            $application_ids = array_map( 'intval', $_POST['application_ids'] );
            
            switch ( $action ) {
                case 'approve':
                    $this->update_applications_status( $application_ids, 'reviewing' );
                    $this->show_admin_notice( __( 'Applications marked as under review.', 'sleeve-ke' ), 'success' );
                    break;
                case 'interview':
                    $this->update_applications_status( $application_ids, 'interview' );
                    $this->show_admin_notice( __( 'Interviews scheduled for selected applications.', 'sleeve-ke' ), 'success' );
                    break;
                case 'accept':
                    $this->update_applications_status( $application_ids, 'accepted' );
                    $this->show_admin_notice( __( 'Applications accepted.', 'sleeve-ke' ), 'success' );
                    break;
                case 'reject':
                    $this->update_applications_status( $application_ids, 'rejected' );
                    $this->show_admin_notice( __( 'Applications rejected.', 'sleeve-ke' ), 'success' );
                    break;
                case 'delete':
                    $this->delete_applications( $application_ids );
                    $this->show_admin_notice( __( 'Applications deleted.', 'sleeve-ke' ), 'success' );
                    break;
            }
        }
    }
    
    /**
     * Update applications status
     */
    private function update_applications_status( $application_ids, $status ) {
        // This would normally update the database
        // For now, just simulate the action
        foreach ( $application_ids as $id ) {
            // Update database: UPDATE sleeve_applications SET status = $status WHERE id = $id
        }
    }
    
    /**
     * Delete applications
     */
    private function delete_applications( $application_ids ) {
        // This would normally delete from the database
        // For now, just simulate the action
        foreach ( $application_ids as $id ) {
            // Delete from database: DELETE FROM sleeve_applications WHERE id = $id
        }
    }
    
    /**
     * Show admin notice
     */
    private function show_admin_notice( $message, $type = 'info' ) {
        add_action( 'admin_notices', function() use ( $message, $type ) {
            echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
        });
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
    
    /**
     * Handle AJAX request to update application status
     */
    public function ajax_update_application_status() {
        // Check nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'update_application_status' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'sleeve-ke' ) ) );
        }
        
        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'sleeve-ke' ) ) );
        }
        
        $application_id = intval( $_POST['application_id'] );
        $status = sanitize_text_field( $_POST['status'] );
        
        // Validate status
        $valid_statuses = array( 'pending', 'reviewing', 'interview', 'accepted', 'rejected' );
        if ( ! in_array( $status, $valid_statuses ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid status', 'sleeve-ke' ) ) );
        }
        
        // Here you would normally update the database
        // For now, we'll just simulate success
        // global $wpdb;
        // $result = $wpdb->update(
        //     $wpdb->prefix . 'sleeve_applications',
        //     array( 'status' => $status ),
        //     array( 'id' => $application_id ),
        //     array( '%s' ),
        //     array( '%d' )
        // );
        
        wp_send_json_success( array( 
            'message' => __( 'Application status updated successfully', 'sleeve-ke' ),
            'application_id' => $application_id,
            'new_status' => $status
        ) );
    }
}
