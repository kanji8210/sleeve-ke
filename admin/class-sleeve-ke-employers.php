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
     * Get employers data (mock data for demonstration)
     */
    public function get_employers_data() {
        // Apply filters if any
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $size_filter = isset( $_GET['company_size'] ) ? sanitize_text_field( $_GET['company_size'] ) : '';
        $industry_filter = isset( $_GET['industry'] ) ? sanitize_text_field( $_GET['industry'] ) : '';

        // Mock data - in real implementation, this would fetch from database
        $all_employers = array(
            array(
                'id' => 1,
                'company_name' => 'TechCorp Solutions Ltd',
                'contact_person' => 'James Mwangi',
                'email' => 'hr@techcorp.co.ke',
                'phone' => '+254 700 111 222',
                'industry' => 'technology',
                'company_size' => 'medium',
                'location' => 'Nairobi, Kenya',
                'website' => 'https://techcorp.co.ke',
                'description' => 'Leading technology solutions provider specializing in custom software development, cloud services, and digital transformation for East African businesses.',
                'founded_year' => 2015,
                'employees_count' => 85,
                'active_jobs_count' => 4,
                'total_jobs_posted' => 23,
                'subscription_plan' => 'premium',
                'subscription_expires' => '2025-12-31',
                'status' => 'active',
                'registered_date' => '2025-01-15',
                'last_login' => '2025-10-17'
            ),
            array(
                'id' => 2,
                'company_name' => 'HealthCare Plus',
                'contact_person' => 'Dr. Sarah Kimani',
                'email' => 'recruitment@healthcareplus.co.ke',
                'phone' => '+254 722 333 444',
                'industry' => 'healthcare',
                'company_size' => 'large',
                'location' => 'Mombasa, Kenya',
                'website' => 'https://healthcareplus.co.ke',
                'description' => 'Modern healthcare facility providing comprehensive medical services with state-of-the-art equipment and experienced medical professionals.',
                'founded_year' => 2010,
                'employees_count' => 320,
                'active_jobs_count' => 7,
                'total_jobs_posted' => 45,
                'subscription_plan' => 'enterprise',
                'subscription_expires' => '2026-03-15',
                'status' => 'active',
                'registered_date' => '2024-11-20',
                'last_login' => '2025-10-16'
            ),
            array(
                'id' => 3,
                'company_name' => 'GreenAgri Innovations',
                'contact_person' => 'Peter Oduya',
                'email' => 'jobs@greenagri.co.ke',
                'phone' => '+254 733 555 666',
                'industry' => 'agriculture',
                'company_size' => 'small',
                'location' => 'Eldoret, Kenya',
                'website' => 'https://greenagri.co.ke',
                'description' => 'Innovative agricultural company focused on sustainable farming practices, organic produce, and modern farming technology solutions.',
                'founded_year' => 2018,
                'employees_count' => 35,
                'active_jobs_count' => 2,
                'total_jobs_posted' => 8,
                'subscription_plan' => 'basic',
                'subscription_expires' => '2025-11-30',
                'status' => 'approved',
                'registered_date' => '2025-06-10',
                'last_login' => '2025-10-14'
            ),
            array(
                'id' => 4,
                'company_name' => 'EduTech Academy',
                'contact_person' => 'Mary Wanjugu',
                'email' => 'hr@edutech.ac.ke',
                'phone' => '+254 711 777 888',
                'industry' => 'education',
                'company_size' => 'medium',
                'location' => 'Kisumu, Kenya',
                'website' => 'https://edutech.ac.ke',
                'description' => 'Progressive educational institution offering technology-enhanced learning programs and professional development courses.',
                'founded_year' => 2020,
                'employees_count' => 120,
                'active_jobs_count' => 6,
                'total_jobs_posted' => 18,
                'subscription_plan' => 'premium',
                'subscription_expires' => '2025-12-15',
                'status' => 'active',
                'registered_date' => '2024-08-05',
                'last_login' => '2025-10-17'
            ),
            array(
                'id' => 5,
                'company_name' => 'StartUp Innovators',
                'contact_person' => 'Alex Ngugi',
                'email' => 'team@startupinnovators.co.ke',
                'phone' => '+254 700 999 000',
                'industry' => 'technology',
                'company_size' => 'startup',
                'location' => 'Nakuru, Kenya',
                'website' => 'https://startupinnovators.co.ke',
                'description' => 'Dynamic startup focused on developing innovative mobile applications and digital solutions for emerging markets.',
                'founded_year' => 2023,
                'employees_count' => 8,
                'active_jobs_count' => 1,
                'total_jobs_posted' => 3,
                'subscription_plan' => 'free',
                'subscription_expires' => null,
                'status' => 'pending',
                'registered_date' => '2025-09-20',
                'last_login' => '2025-10-15'
            )
        );

        // Apply filters
        $filtered_employers = $all_employers;

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
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Add New Employer', 'sleeve-ke' ) . '</h1>';
        echo '<p>' . esc_html__( 'Form for adding new employers will be implemented here.', 'sleeve-ke' ) . '</p>';
        echo '</div>';
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
        // Handle form submissions and bulk actions
        if ( isset( $_POST['apply_bulk_action'] ) && isset( $_POST['bulk_action'] ) && isset( $_POST['employer_ids'] ) ) {
            $this->handle_bulk_actions();
        }
    }

    /**
     * Handle bulk actions
     */
    private function handle_bulk_actions() {
        $action = sanitize_text_field( $_POST['bulk_action'] );
        $employer_ids = array_map( 'intval', $_POST['employer_ids'] );
        
        // Here you would normally update the database
        $message = '';
        switch ( $action ) {
            case 'approve':
                $message = __( 'Employers approved successfully.', 'sleeve-ke' );
                break;
            case 'pending':
                $message = __( 'Employers set to pending review.', 'sleeve-ke' );
                break;
            case 'suspend':
                $message = __( 'Employers suspended.', 'sleeve-ke' );
                break;
            case 'deactivate':
                $message = __( 'Employers deactivated.', 'sleeve-ke' );
                break;
        }

        if ( $message ) {
            add_action( 'admin_notices', function() use ( $message ) {
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                     esc_html( $message ) . 
                     '</p></div>';
            });
        }
    }

    /**
     * Handle AJAX request to update employer status
     */
    public function ajax_update_employer_status() {
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
        
        // Here you would normally update the database
        // For now, we'll just simulate success
        
        wp_send_json_success( array( 
            'message' => __( 'Employer status updated successfully', 'sleeve-ke' ),
            'employer_id' => $employer_id,
            'new_status' => $status
        ) );
    }
}