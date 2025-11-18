<?php
/**
 * Employers management functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Employers management class.
 *
 * Handles all functionality related to employer management
 * including registration, company profiles, job postings management,
 * and subscription/payment tracking.
 */
class Sleeve_KE_Employers {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Constructor can be used for initialization if needed
    }

    /**
     * Display the employers management page.
     */
    public function display_page() {
        // Handle form submissions
        if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['sleeve_nonce'], 'sleeve_employers' ) ) {
            $this->handle_employer_actions();
        }
        
        // Check if we're adding/editing/viewing an employer
        $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
        $employer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        
        switch ( $action ) {
            case 'add':
                $this->display_add_employer_form();
                break;
            case 'edit':
                $this->display_edit_employer_form( $employer_id );
                break;
            case 'view':
                $this->display_employer_view( $employer_id );
                break;
            default:
                $this->display_employers_list();
                break;
        }
    }

    /**
     * Display the employers list page.
     */
    private function display_employers_list() {
        // Get employers data
        $employers = $this->get_employers_data();
        $statuses = $this->get_status_options();
        $current_user = wp_get_current_user();
        
        // Display success messages
        if (isset($_GET['employer_created'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 __('Employer created successfully!', 'sleeve-ke') . 
                 '</p></div>';
        }
        
        if (isset($_GET['employer_deleted'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 __('Employer deleted successfully!', 'sleeve-ke') . 
                 '</p></div>';
        }
        
        if (isset($_GET['bulk_action_done'])) {
            $message = get_transient('sleeve_ke_bulk_action_message');
            if ($message) {
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                     esc_html($message) . 
                     '</p></div>';
                delete_transient('sleeve_ke_bulk_action_message');
            }
        }
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e( 'Employers', 'sleeve-ke' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers&action=add' ) ); ?>" class="page-title-action">
                    <?php esc_html_e( 'Add New Employer', 'sleeve-ke' ); ?>
                </a>
            </h1>
            
            <!-- Filter and Search Section -->
            <div class="sleeve-ke-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="sleeve-ke-employers" />
                    
                    <div class="filter-row">
                        <input type="text" name="search" placeholder="<?php esc_attr_e( 'Search by company name, email, or industry...', 'sleeve-ke' ); ?>" 
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
                        
                        <select name="company_size">
                            <option value=""><?php esc_html_e( 'All Company Sizes', 'sleeve-ke' ); ?></option>
                            <?php
                            $company_sizes = $this->get_company_sizes();
                            foreach ( $company_sizes as $size_key => $size_label ) :
                            ?>
                                <option value="<?php echo esc_attr( $size_key ); ?>" 
                                        <?php selected( isset( $_GET['company_size'] ) ? $_GET['company_size'] : '', $size_key ); ?>>
                                    <?php echo esc_html( $size_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="industry">
                            <option value=""><?php esc_html_e( 'All Industries', 'sleeve-ke' ); ?></option>
                            <?php
                            $industries = $this->get_industries();
                            foreach ( $industries as $industry_key => $industry_label ) :
                            ?>
                                <option value="<?php echo esc_attr( $industry_key ); ?>" 
                                        <?php selected( isset( $_GET['industry'] ) ? $_GET['industry'] : '', $industry_key ); ?>>
                                    <?php echo esc_html( $industry_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php submit_button( __( 'Filter', 'sleeve-ke' ), 'secondary', 'filter', false ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers' ) ); ?>" class="button">
                            <?php esc_html_e( 'Clear', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Employers Table -->
            <form method="post" action="">
                <?php wp_nonce_field( 'sleeve_employers', 'sleeve_nonce' ); ?>
                
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="bulk_action">
                            <option value=""><?php esc_html_e( 'Bulk Actions', 'sleeve-ke' ); ?></option>
                            <option value="approve"><?php esc_html_e( 'Approve', 'sleeve-ke' ); ?></option>
                            <option value="pending"><?php esc_html_e( 'Set Pending', 'sleeve-ke' ); ?></option>
                            <option value="suspend"><?php esc_html_e( 'Suspend', 'sleeve-ke' ); ?></option>
                            <option value="deactivate"><?php esc_html_e( 'Deactivate', 'sleeve-ke' ); ?></option>
                        </select>
                        <?php submit_button( __( 'Apply', 'sleeve-ke' ), 'action', 'apply_bulk_action', false ); ?>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped sleeve-ke-employers-table">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all" />
                            </td>
                            <th class="manage-column"><?php esc_html_e( 'Company', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Contact Person', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Email', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Industry', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Size', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Location', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Active Jobs', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Status', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Subscription', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Joined', 'sleeve-ke' ); ?></th>
                            <th class="manage-column"><?php esc_html_e( 'Actions', 'sleeve-ke' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty( $employers ) ) : ?>
                            <tr>
                                <td colspan="12" class="no-items">
                                    <?php esc_html_e( 'No employers found.', 'sleeve-ke' ); ?>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers&action=add' ) ); ?>">
                                        <?php esc_html_e( 'Add first employer', 'sleeve-ke' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ( $employers as $employer ) : ?>
                                <tr>
                                    <th class="check-column">
                                        <input type="checkbox" name="employer_ids[]" value="<?php echo esc_attr( $employer['id'] ); ?>" />
                                    </th>
                                    <td>
                                        <strong>
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers&action=view&id=' . $employer['id'] ) ); ?>">
                                                <?php echo esc_html( $employer['company_name'] ); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers&action=view&id=' . $employer['id'] ) ); ?>"><?php esc_html_e( 'View', 'sleeve-ke' ); ?></a> | </span>
                                            <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers&action=edit&id=' . $employer['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'sleeve-ke' ); ?></a> | </span>
                                            <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&employer_id=' . $employer['id'] ) ); ?>"><?php esc_html_e( 'Jobs', 'sleeve-ke' ); ?></a> | </span>
                                            <span><a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers&action=delete&id=' . $employer['id'] ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'sleeve-ke' ); ?>')" class="delete"><?php esc_html_e( 'Delete', 'sleeve-ke' ); ?></a></span>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html( $employer['contact_person'] ); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo esc_attr( $employer['email'] ); ?>">
                                            <?php echo esc_html( $employer['email'] ); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php 
                                        $industries = $this->get_industries();
                                        echo esc_html( isset( $industries[ $employer['industry'] ] ) ? $industries[ $employer['industry'] ] : $employer['industry'] );
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $company_sizes = $this->get_company_sizes();
                                        echo esc_html( isset( $company_sizes[ $employer['company_size'] ] ) ? $company_sizes[ $employer['company_size'] ] : $employer['company_size'] );
                                        ?>
                                    </td>
                                    <td><?php echo esc_html( $employer['location'] ); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&employer_id=' . $employer['id'] ) ); ?>">
                                            <?php echo esc_html( $employer['active_jobs_count'] ); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr( $employer['status'] ); ?>">
                                            <?php echo esc_html( $statuses[ $employer['status'] ] ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="subscription-badge subscription-<?php echo esc_attr( $employer['subscription_plan'] ); ?>">
                                            <?php echo esc_html( ucfirst( $employer['subscription_plan'] ) ); ?>
                                        </span>
                                        <?php if ( ! empty( $employer['subscription_expires'] ) ) : ?>
                                            <div class="subscription-expires">
                                                <?php esc_html_e( 'Expires:', 'sleeve-ke' ); ?> <?php echo esc_html( $employer['subscription_expires'] ); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html( $employer['registered_date'] ); ?></td>
                                    <td>
                                        <select class="status-select" data-employer-id="<?php echo esc_attr( $employer['id'] ); ?>">
                                            <?php foreach ( $statuses as $status_key => $status_label ) : ?>
                                                <option value="<?php echo esc_attr( $status_key ); ?>" 
                                                        <?php selected( $employer['status'], $status_key ); ?>>
                                                    <?php echo esc_html( $status_label ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
            
            <!-- Statistics Section -->
            <div class="sleeve-ke-employers-stats">
                <h3><?php esc_html_e( 'Employer Statistics', 'sleeve-ke' ); ?></h3>
                <div class="stats-grid">
                    <?php
                    $stats = $this->get_employer_stats();
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
                var employerId = $(this).data('employer-id');
                var newStatus = $(this).val();
                
                $.post(ajaxurl, {
                    action: 'update_employer_status',
                    employer_id: employerId,
                    status: newStatus,
                    nonce: '<?php echo wp_create_nonce( 'update_employer_status' ); ?>'
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
                $('input[name="employer_ids[]"]').prop('checked', this.checked);
            });
        });
        </script>
        <?php
    }

    /**
     * Get status options for employers
     */
    public function get_status_options() {
        return array(
            'pending' => __( 'Pending Review', 'sleeve-ke' ),
            'approved' => __( 'Approved', 'sleeve-ke' ),
            'active' => __( 'Active', 'sleeve-ke' ),
            'suspended' => __( 'Suspended', 'sleeve-ke' ),
            'inactive' => __( 'Inactive', 'sleeve-ke' )
        );
    }

    /**
     * Get company sizes
     */
    public function get_company_sizes() {
        return array(
            'startup' => __( 'Startup (1-10 employees)', 'sleeve-ke' ),
            'small' => __( 'Small (11-50 employees)', 'sleeve-ke' ),
            'medium' => __( 'Medium (51-200 employees)', 'sleeve-ke' ),
            'large' => __( 'Large (201-1000 employees)', 'sleeve-ke' ),
            'enterprise' => __( 'Enterprise (1000+ employees)', 'sleeve-ke' )
        );
    }

    /**
     * Get industries
     */
    public function get_industries() {
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
     * Get employers data from database
     */
    public function get_employers_data() {
        global $wpdb;
        
        // Apply filters if any
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $size_filter = isset( $_GET['company_size'] ) ? sanitize_text_field( $_GET['company_size'] ) : '';
        $industry_filter = isset( $_GET['industry'] ) ? sanitize_text_field( $_GET['industry'] ) : '';
        
        // Get users with employer role
        $user_query = new WP_User_Query(array(
            'role' => 'employer',
            'orderby' => 'registered',
            'order' => 'DESC',
            'number' => -1
        ));
        
        $employers = array();
        $employers_table = $wpdb->prefix . 'sleeve_employers';
        
        foreach ($user_query->get_results() as $user) {
            // Get employer profile from custom table
            $profile = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$employers_table} WHERE user_id = %d",
                $user->ID
            ), ARRAY_A);
            
            // Count active jobs for this employer
            $active_jobs = count(get_posts(array(
                'post_type' => 'job',
                'post_status' => 'publish',
                'author' => $user->ID,
                'posts_per_page' => -1
            )));
            
            // Count total jobs posted
            $total_jobs = count(get_posts(array(
                'post_type' => 'job',
                'post_status' => 'any',
                'author' => $user->ID,
                'posts_per_page' => -1
            )));
            
            // Get last login from user meta
            $last_login = get_user_meta($user->ID, 'last_login', true);
            
            // Build employer data array
            $employer_data = array(
                'id' => $profile ? $profile['id'] : 0,
                'user_id' => $user->ID,
                'company_name' => $profile ? $profile['company_name'] : $user->display_name,
                'contact_person' => $user->display_name,
                'email' => $user->user_email,
                'phone' => $profile ? $profile['phone'] : '',
                'industry' => $profile ? $profile['industry'] : 'other',
                'company_size' => $profile ? $profile['company_size'] : 'startup',
                'location' => $profile ? $profile['location'] : '',
                'website' => $profile ? $profile['website'] : '',
                'description' => $profile ? $profile['company_description'] : '',
                'founded_year' => '',
                'employees_count' => '',
                'active_jobs_count' => $active_jobs,
                'total_jobs_posted' => $total_jobs,
                'subscription_plan' => get_user_meta($user->ID, 'subscription_plan', true) ?: 'free',
                'subscription_expires' => get_user_meta($user->ID, 'subscription_expires', true),
                'status' => get_user_meta($user->ID, 'employer_status', true) ?: 'pending',
                'registered_date' => date('Y-m-d', strtotime($user->user_registered)),
                'last_login' => $last_login ? date('Y-m-d', strtotime($last_login)) : 'Never'
            );
            
            $employers[] = $employer_data;
        }

        
        // Apply filters
        $filtered_employers = $employers;
        
        if ( ! empty( $search ) ) {

            $filtered_employers = array_filter( $filtered_employers, function( $employer ) use ( $search ) {
                return stripos( $employer['company_name'], $search ) !== false || 
                       stripos( $employer['email'], $search ) !== false ||
                       stripos( $employer['industry'], $search ) !== false ||
                       stripos( $employer['contact_person'], $search ) !== false;
            });
        }

        if ( ! empty( $status_filter ) ) {
            $filtered_employers = array_filter( $filtered_employers, function( $employer ) use ( $status_filter ) {
                return $employer['status'] === $status_filter;
            });
        }

        if ( ! empty( $size_filter ) ) {
            $filtered_employers = array_filter( $filtered_employers, function( $employer ) use ( $size_filter ) {
                return $employer['company_size'] === $size_filter;
            });
        }

        if ( ! empty( $industry_filter ) ) {
            $filtered_employers = array_filter( $filtered_employers, function( $employer ) use ( $industry_filter ) {
                return $employer['industry'] === $industry_filter;
            });
        }

        return $filtered_employers;
    }

    /**
     * Get employer statistics
     */
    public function get_employer_stats() {
        $employers = $this->get_employers_data();
        $statuses = $this->get_status_options();
        
        $stats = array();
        $stats[] = array( 'count' => count( $employers ), 'label' => __( 'Total Employers', 'sleeve-ke' ) );
        
        foreach ( $statuses as $status_key => $status_label ) {
            $count = count( array_filter( $employers, function( $employer ) use ( $status_key ) {
                return $employer['status'] === $status_key;
            }));
            if ( $count > 0 ) {
                $stats[] = array( 'count' => $count, 'label' => $status_label );
            }
        }
        
        // Add subscription stats
        $premium_count = count( array_filter( $employers, function( $employer ) {
            return in_array( $employer['subscription_plan'], array( 'premium', 'enterprise' ) );
        }));
        
        if ( $premium_count > 0 ) {
            $stats[] = array( 'count' => $premium_count, 'label' => __( 'Premium Members', 'sleeve-ke' ) );
        }
        
        return $stats;
    }

    /**
     * Display add employer form.
     */
    private function display_add_employer_form() {
        $industries = $this->get_industries();
        $company_sizes = $this->get_company_sizes();
        $statuses = $this->get_status_options();
        
        // Get form values from transient (if validation failed)
        $form_values = get_transient('sleeve_ke_employer_form_values');
        $form_errors = get_transient('sleeve_ke_employer_form_errors');
        
        // Check for success message
        $success_data = get_transient('sleeve_ke_employer_created_success');
        
        delete_transient('sleeve_ke_employer_form_values');
        delete_transient('sleeve_ke_employer_form_errors');
        delete_transient('sleeve_ke_employer_created_success');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Add New Employer', 'sleeve-ke' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers' ) ); ?>" class="button">
                <?php esc_html_e( '← Back to Employers', 'sleeve-ke' ); ?>
            </a>
            
            <?php if ($success_data): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php esc_html_e('Success!', 'sleeve-ke'); ?></strong> 
                    <?php printf(__('Employer "%s" has been created successfully!', 'sleeve-ke'), esc_html($success_data['company_name'])); ?>
                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('employer-profile')->ID) . '?user_id=' . $success_data['user_id']); ?>" class="button button-primary">
                        <?php esc_html_e('View Profile', 'sleeve-ke'); ?>
                    </a>
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if ($form_errors): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong><?php esc_html_e('Please correct the following errors:', 'sleeve-ke'); ?></strong></p>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <?php foreach ($form_errors as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" class="sleeve-ke-employer-form" enctype="multipart/form-data">
                <?php wp_nonce_field('sleeve_ke_add_employer', 'sleeve_employer_nonce'); ?>
                <input type="hidden" name="action" value="add_employer" />
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="username"><?php esc_html_e('Username', 'sleeve-ke'); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="text" id="username" name="username" class="regular-text" required
                                   value="<?php echo esc_attr($form_values['username'] ?? ''); ?>" />
                            <p class="description"><?php esc_html_e('Login username for the employer account.', 'sleeve-ke'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email"><?php esc_html_e('Email Address', 'sleeve-ke'); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="email" id="email" name="email" class="regular-text" required
                                   value="<?php echo esc_attr($form_values['email'] ?? ''); ?>" />
                            <p class="description"><?php esc_html_e('Contact email for the employer.', 'sleeve-ke'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="password"><?php esc_html_e('Password', 'sleeve-ke'); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="password" id="password" name="password" class="regular-text" required />
                            <p class="description"><?php esc_html_e('Initial password for the account.', 'sleeve-ke'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="company_name"><?php esc_html_e('Company Name', 'sleeve-ke'); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="text" id="company_name" name="company_name" class="regular-text" required
                                   value="<?php echo esc_attr($form_values['company_name'] ?? ''); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="phone"><?php esc_html_e('Phone Number', 'sleeve-ke'); ?></label></th>
                        <td>
                            <input type="tel" id="phone" name="phone" class="regular-text"
                                   value="<?php echo esc_attr($form_values['phone'] ?? ''); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="website"><?php esc_html_e('Website', 'sleeve-ke'); ?></label></th>
                        <td>
                            <input type="url" id="website" name="website" class="regular-text"
                                   value="<?php echo esc_attr($form_values['website'] ?? ''); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="location"><?php esc_html_e('Location', 'sleeve-ke'); ?></label></th>
                        <td>
                            <input type="text" id="location" name="location" class="regular-text"
                                   value="<?php echo esc_attr($form_values['location'] ?? ''); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="industry"><?php esc_html_e('Industry', 'sleeve-ke'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select id="industry" name="industry" required>
                                <option value=""><?php esc_html_e('Select Industry', 'sleeve-ke'); ?></option>
                                <?php foreach ($industries as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" 
                                            <?php selected($form_values['industry'] ?? '', $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="company_size"><?php esc_html_e('Company Size', 'sleeve-ke'); ?> <span class="required">*</span></label></th>
                        <td>
                            <select id="company_size" name="company_size" required>
                                <option value=""><?php esc_html_e('Select Size', 'sleeve-ke'); ?></option>
                                <?php foreach ($company_sizes as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" 
                                            <?php selected($form_values['company_size'] ?? '', $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="company_description"><?php esc_html_e('Company Description', 'sleeve-ke'); ?></label></th>
                        <td>
                            <textarea id="company_description" name="company_description" rows="5" class="large-text"><?php echo esc_textarea($form_values['company_description'] ?? ''); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="company_logo"><?php esc_html_e('Company Logo', 'sleeve-ke'); ?></label></th>
                        <td>
                            <input type="file" id="company_logo" name="company_logo" accept="image/*" />
                            <p class="description"><?php esc_html_e('Upload company logo (JPG, PNG, max 2MB).', 'sleeve-ke'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="status"><?php esc_html_e('Status', 'sleeve-ke'); ?></label></th>
                        <td>
                            <select id="status" name="status">
                                <?php foreach ($statuses as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" 
                                            <?php selected($form_values['status'] ?? 'approved', $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="subscription_plan"><?php esc_html_e('Subscription Plan', 'sleeve-ke'); ?></label></th>
                        <td>
                            <select id="subscription_plan" name="subscription_plan">
                                <option value="free" <?php selected($form_values['subscription_plan'] ?? 'free', 'free'); ?>><?php esc_html_e('Free', 'sleeve-ke'); ?></option>
                                <option value="basic" <?php selected($form_values['subscription_plan'] ?? '', 'basic'); ?>><?php esc_html_e('Basic', 'sleeve-ke'); ?></option>
                                <option value="premium" <?php selected($form_values['subscription_plan'] ?? '', 'premium'); ?>><?php esc_html_e('Premium', 'sleeve-ke'); ?></option>
                                <option value="enterprise" <?php selected($form_values['subscription_plan'] ?? '', 'enterprise'); ?>><?php esc_html_e('Enterprise', 'sleeve-ke'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="send_welcome_email"><?php esc_html_e('Send Welcome Email', 'sleeve-ke'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" id="send_welcome_email" name="send_welcome_email" value="1" checked />
                                <?php esc_html_e('Send account credentials to the employer via email', 'sleeve-ke'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Add Employer', 'sleeve-ke')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Display edit employer form.
     */
    private function display_edit_employer_form( $employer_id ) {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Edit Employer', 'sleeve-ke' ) . '</h1>';
        echo '<p>' . esc_html__( 'Form for editing employer with ID: ', 'sleeve-ke' ) . esc_html( $employer_id ) . '</p>';
        echo '</div>';
    }

    /**
     * Display employer view page.
     */
    public function display_employer_view( $employer_id ) {
        $employers = $this->get_employers_data();
        $employer = null;
        
        foreach ( $employers as $emp ) {
            if ( $emp['id'] == $employer_id ) {
                $employer = $emp;
                break;
            }
        }
        
        if ( ! $employer ) {
            echo '<div class="wrap"><h1>' . __( 'Employer Not Found', 'sleeve-ke' ) . '</h1></div>';
            return;
        }

        $statuses = $this->get_status_options();
        $industries = $this->get_industries();
        $company_sizes = $this->get_company_sizes();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Employer Details', 'sleeve-ke' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers' ) ); ?>" class="button">
                <?php esc_html_e( '← Back to Employers', 'sleeve-ke' ); ?>
            </a>
            
            <div class="sleeve-ke-employer-details">
                <div class="employer-header">
                    <h2><?php echo esc_html( $employer['company_name'] ); ?></h2>
                    <div class="employer-meta">
                        <span class="status-badge status-<?php echo esc_attr( $employer['status'] ); ?>">
                            <?php echo esc_html( $statuses[ $employer['status'] ] ); ?>
                        </span>
                        <span class="subscription-badge subscription-<?php echo esc_attr( $employer['subscription_plan'] ); ?>">
                            <?php echo esc_html( ucfirst( $employer['subscription_plan'] ) ); ?>
                        </span>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-employers&action=edit&id=' . $employer['id'] ) ); ?>" class="button button-primary">
                            <?php esc_html_e( 'Edit Employer', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </div>
                
                <div class="employer-details-grid">
                    <div class="employer-details-main">
                        <h3><?php esc_html_e( 'Company Information', 'sleeve-ke' ); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Company Name', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $employer['company_name'] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Contact Person', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $employer['contact_person'] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Email', 'sleeve-ke' ); ?></th>
                                <td>
                                    <a href="mailto:<?php echo esc_attr( $employer['email'] ); ?>">
                                        <?php echo esc_html( $employer['email'] ); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Phone', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $employer['phone'] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Website', 'sleeve-ke' ); ?></th>
                                <td>
                                    <a href="<?php echo esc_url( $employer['website'] ); ?>" target="_blank">
                                        <?php echo esc_html( $employer['website'] ); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Industry', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $industries[ $employer['industry'] ] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Company Size', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $company_sizes[ $employer['company_size'] ] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Location', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $employer['location'] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Founded', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $employer['founded_year'] ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Employees', 'sleeve-ke' ); ?></th>
                                <td><?php echo esc_html( $employer['employees_count'] ); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="employer-details-content">
                        <div class="employer-section">
                            <h3><?php esc_html_e( 'Company Description', 'sleeve-ke' ); ?></h3>
                            <div class="employer-content">
                                <?php echo wp_kses_post( wpautop( $employer['description'] ) ); ?>
                            </div>
                        </div>
                        
                        <div class="employer-section">
                            <h3><?php esc_html_e( 'Job Posting Activity', 'sleeve-ke' ); ?></h3>
                            <div class="job-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html( $employer['active_jobs_count'] ); ?></div>
                                    <div class="stat-label"><?php esc_html_e( 'Active Jobs', 'sleeve-ke' ); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo esc_html( $employer['total_jobs_posted'] ); ?></div>
                                    <div class="stat-label"><?php esc_html_e( 'Total Jobs Posted', 'sleeve-ke' ); ?></div>
                                </div>
                            </div>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-jobs&employer_id=' . $employer['id'] ) ); ?>" class="button">
                                <?php esc_html_e( 'View All Jobs', 'sleeve-ke' ); ?>
                            </a>
                        </div>
                        
                        <div class="employer-section">
                            <h3><?php esc_html_e( 'Subscription Details', 'sleeve-ke' ); ?></h3>
                            <div class="subscription-info">
                                <p><strong><?php esc_html_e( 'Plan:', 'sleeve-ke' ); ?></strong> <?php echo esc_html( ucfirst( $employer['subscription_plan'] ) ); ?></p>
                                <?php if ( ! empty( $employer['subscription_expires'] ) ) : ?>
                                    <p><strong><?php esc_html_e( 'Expires:', 'sleeve-ke' ); ?></strong> <?php echo esc_html( date( 'F j, Y', strtotime( $employer['subscription_expires'] ) ) ); ?></p>
                                <?php endif; ?>
                                <p><strong><?php esc_html_e( 'Registered:', 'sleeve-ke' ); ?></strong> <?php echo esc_html( date( 'F j, Y', strtotime( $employer['registered_date'] ) ) ); ?></p>
                                <p><strong><?php esc_html_e( 'Last Login:', 'sleeve-ke' ); ?></strong> <?php echo esc_html( date( 'F j, Y', strtotime( $employer['last_login'] ) ) ); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle employer actions
     */
    public function handle_employer_actions() {
        // Handle add employer
        if (isset($_POST['action']) && $_POST['action'] === 'add_employer' && 
            wp_verify_nonce($_POST['sleeve_employer_nonce'], 'sleeve_ke_add_employer')) {
            $this->create_employer();
            return;
        }
        
        // Handle edit employer
        if (isset($_POST['action']) && $_POST['action'] === 'edit_employer' && 
            wp_verify_nonce($_POST['sleeve_employer_nonce'], 'sleeve_ke_edit_employer')) {
            $this->update_employer();
            return;
        }
        
        // Handle delete employer
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $this->delete_employer(intval($_GET['id']));
            return;
        }
        
        // Handle bulk actions
        if ( isset( $_POST['apply_bulk_action'] ) && isset( $_POST['bulk_action'] ) && isset( $_POST['employer_ids'] ) ) {
            $this->handle_bulk_actions();
        }
    }
    
    /**
     * Create new employer
     */
    private function create_employer() {
        global $wpdb;
        
        error_log('=== ADMIN EMPLOYER CREATION START ===');
        error_log('POST Data: ' . print_r($_POST, true));
        error_log('FILES Data: ' . print_r($_FILES, true));
        
        // Validate required fields
        $errors = array();
        $values = array();
        
        $required_fields = array('username', 'email', 'password', 'company_name', 'industry', 'company_size');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $error_msg = sprintf(__('%s is required.', 'sleeve-ke'), ucfirst(str_replace('_', ' ', $field)));
                $errors[] = $error_msg;
                error_log('VALIDATION ERROR: ' . $error_msg);
            } else {
                $values[$field] = sanitize_text_field($_POST[$field]);
            }
        }
        
        // Validate email
        if (!empty($_POST['email']) && !is_email($_POST['email'])) {
            $errors[] = __('Invalid email address.', 'sleeve-ke');
            error_log('VALIDATION ERROR: Invalid email address');
        }
        
        // Check if username already exists
        if (!empty($_POST['username']) && username_exists($_POST['username'])) {
            $errors[] = __('Username already exists.', 'sleeve-ke');
            error_log('VALIDATION ERROR: Username already exists - ' . $_POST['username']);
        }
        
        // Check if email already exists
        if (!empty($_POST['email']) && email_exists($_POST['email'])) {
            $errors[] = __('Email already exists.', 'sleeve-ke');
            error_log('VALIDATION ERROR: Email already exists - ' . $_POST['email']);
        }
        
        // If there are errors, store them and redirect back
        if (!empty($errors)) {
            error_log('VALIDATION FAILED: ' . count($errors) . ' errors found');
            error_log('Errors: ' . print_r($errors, true));
            set_transient('sleeve_ke_employer_form_errors', $errors, 60);
            set_transient('sleeve_ke_employer_form_values', $_POST, 60);
            wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-employers&action=add'));
            exit;
        }
        
        error_log('VALIDATION PASSED - Creating WordPress user');
        
        // Create WordPress user
        $user_id = wp_create_user(
            $values['username'],
            $_POST['password'],
            $values['email']
        );
        
        if (is_wp_error($user_id)) {
            error_log('USER CREATION FAILED: ' . $user_id->get_error_message());
            set_transient('sleeve_ke_employer_form_errors', array($user_id->get_error_message()), 60);
            set_transient('sleeve_ke_employer_form_values', $_POST, 60);
            wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-employers&action=add'));
            exit;
        }
        
        error_log('USER CREATED SUCCESSFULLY - User ID: ' . $user_id);
        
        // Set user role to employer
        $user = new WP_User($user_id);
        $user->set_role('employer');
        error_log('USER ROLE SET: employer');
        
        // Update user meta
        update_user_meta($user_id, 'employer_status', sanitize_text_field($_POST['status'] ?? 'approved'));
        update_user_meta($user_id, 'subscription_plan', sanitize_text_field($_POST['subscription_plan'] ?? 'free'));
        error_log('USER META UPDATED');
        
        // Handle logo upload
        $logo_url = '';
        if (!empty($_FILES['company_logo']['name'])) {
            error_log('LOGO UPLOAD: Processing file - ' . $_FILES['company_logo']['name']);
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($_FILES['company_logo'], $upload_overrides);
            
            if ($movefile && !isset($movefile['error'])) {
                $logo_url = $movefile['url'];
                error_log('LOGO UPLOADED SUCCESSFULLY: ' . $logo_url);
            } else {
                error_log('LOGO UPLOAD FAILED: ' . print_r($movefile, true));
            }
        } else {
            error_log('NO LOGO FILE PROVIDED');
        }
        
        // Insert into employers table
        error_log('INSERTING INTO EMPLOYERS TABLE');
        $employers_table = $wpdb->prefix . 'sleeve_employers';
        $insert_result = $wpdb->insert(
            $employers_table,
            array(
                'user_id' => $user_id,
                'company_name' => sanitize_text_field($_POST['company_name']),
                'company_description' => wp_kses_post($_POST['company_description'] ?? ''),
                'company_logo' => $logo_url,
                'phone' => sanitize_text_field($_POST['phone'] ?? ''),
                'website' => esc_url_raw($_POST['website'] ?? ''),
                'location' => sanitize_text_field($_POST['location'] ?? ''),
                'industry' => sanitize_text_field($_POST['industry']),
                'company_size' => sanitize_text_field($_POST['company_size']),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($insert_result === false) {
            error_log('DATABASE INSERT FAILED: ' . $wpdb->last_error);
            error_log('Query: ' . $wpdb->last_query);
            set_transient('sleeve_ke_employer_form_errors', array('Database error: ' . $wpdb->last_error), 60);
            wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-employers&action=add'));
            exit;
        }
        
        error_log('DATABASE INSERT SUCCESSFUL - Employer ID: ' . $wpdb->insert_id);
        
        // Send welcome email if requested
        if (!empty($_POST['send_welcome_email'])) {
            error_log('SENDING WELCOME EMAIL');
            wp_new_user_notification($user_id, null, 'both');
        }
        
        // Set success transient for display
        set_transient('sleeve_ke_employer_created_success', array(
            'user_id' => $user_id,
            'company_name' => sanitize_text_field($_POST['company_name'])
        ), 60);
        
        // Get the employer profile page URL
        $profile_page = get_page_by_path('employer-profile');
        if ($profile_page) {
            $profile_url = get_permalink($profile_page->ID) . '?user_id=' . $user_id;
            error_log('REDIRECTING TO PROFILE: ' . $profile_url);
            // Redirect to employer profile
            wp_safe_redirect($profile_url);
        } else {
            error_log('PROFILE PAGE NOT FOUND - Redirecting to add form with success');
            // Redirect back to add form to show success message
            wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-employers&action=add&created=1'));
        }
        
        error_log('=== ADMIN EMPLOYER CREATION END - SUCCESS ===');
        exit;
    }
    
    /**
     * Update existing employer
     */
    private function update_employer() {
        // Implementation similar to create_employer but for updates
        // Will be implemented when edit form is ready
    }
    
    /**
     * Delete employer
     */
    private function delete_employer($employer_id) {
        global $wpdb;
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'sleeve-ke'));
        }
        
        // Get employer data
        $employers_table = $wpdb->prefix . 'sleeve_employers';
        $employer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$employers_table} WHERE id = %d",
            $employer_id
        ));
        
        if (!$employer) {
            wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-employers&error=not_found'));
            exit;
        }
        
        // Delete from employers table
        $wpdb->delete($employers_table, array('id' => $employer_id), array('%d'));
        
        // Optionally delete the user account
        // require_once(ABSPATH.'wp-admin/includes/user.php');
        // wp_delete_user($employer->user_id);
        
        wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-employers&employer_deleted=1'));
        exit;
    }

    /**
     * Handle bulk actions
     */
    private function handle_bulk_actions() {
        global $wpdb;
        
        $action = sanitize_text_field( $_POST['bulk_action'] );
        $employer_ids = array_map( 'intval', $_POST['employer_ids'] );
        
        if (empty($employer_ids)) {
            wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-employers'));
            exit;
        }
        
        $employers_table = $wpdb->prefix . 'sleeve_employers';
        $count = 0;
        
        foreach ($employer_ids as $employer_id) {
            $employer = $wpdb->get_row($wpdb->prepare(
                "SELECT user_id FROM {$employers_table} WHERE id = %d",
                $employer_id
            ));
            
            if (!$employer) continue;
            
            switch ( $action ) {
                case 'approve':
                case 'pending':
                case 'suspend':
                case 'deactivate':
                    // Map bulk action to status
                    $status_map = array(
                        'approve' => 'approved',
                        'pending' => 'pending',
                        'suspend' => 'suspended',
                        'deactivate' => 'inactive'
                    );
                    update_user_meta($employer->user_id, 'employer_status', $status_map[$action]);
                    $count++;
                    break;
            }
        }

        $message = '';
        switch ( $action ) {
            case 'approve':
                $message = sprintf(__( '%d employers approved successfully.', 'sleeve-ke' ), $count);
                break;
            case 'pending':
                $message = sprintf(__( '%d employers set to pending review.', 'sleeve-ke' ), $count);
                break;
            case 'suspend':
                $message = sprintf(__( '%d employers suspended.', 'sleeve-ke' ), $count);
                break;
            case 'deactivate':
                $message = sprintf(__( '%d employers deactivated.', 'sleeve-ke' ), $count);
                break;
        }

        set_transient('sleeve_ke_bulk_action_message', $message, 60);
        wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-employers&bulk_action_done=1'));
        exit;
    }

    /**
     * Handle AJAX request to update employer status
     */
    public function ajax_update_employer_status() {
        global $wpdb;
        
        // Check nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'update_employer_status' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'sleeve-ke' ) ) );
        }
        
        $employer_id = intval( $_POST['employer_id'] );
        $status = sanitize_text_field( $_POST['status'] );
        
        // Validate status
        $valid_statuses = array_keys( $this->get_status_options() );
        if ( ! in_array( $status, $valid_statuses ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid status', 'sleeve-ke' ) ) );
        }
        
        // Get employer user_id
        $employers_table = $wpdb->prefix . 'sleeve_employers';
        $employer = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id FROM {$employers_table} WHERE id = %d",
            $employer_id
        ));
        
        if (!$employer) {
            wp_send_json_error( array( 'message' => __( 'Employer not found', 'sleeve-ke' ) ) );
        }
        
        // Update status in user meta
        update_user_meta($employer->user_id, 'employer_status', $status);
        
        wp_send_json_success( array( 
            'message' => __( 'Employer status updated successfully', 'sleeve-ke' ),
            'employer_id' => $employer_id,
            'new_status' => $status
        ) );
    }
}