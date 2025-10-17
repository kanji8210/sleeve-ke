<?php
/**
 * Applications management functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Applications management class.
 *
 * Handles all functionality related to job applications management
 * including display, filtering, status updates, and bulk actions.
 */
class Sleeve_KE_Applications {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Constructor can be used for initialization if needed
    }

    /**
     * Display the applications management page.
     */
    public function display_page() {
        // Handle form submissions
        if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['sleeve_nonce'], 'sleeve_applications' ) ) {
            $this->handle_application_actions();
        }
        
        // Get applications data (mock data for now)
        $applications = $this->get_applications_data();
        $statuses = $this->get_status_options();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Job Applications', 'sleeve-ke' ); ?></h1>
            
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
     * Get status options for applications
     */
    public function get_status_options() {
        return array(
            'pending' => __( 'Pending', 'sleeve-ke' ),
            'reviewing' => __( 'Under Review', 'sleeve-ke' ),
            'interview' => __( 'Interview Scheduled', 'sleeve-ke' ),
            'accepted' => __( 'Accepted', 'sleeve-ke' ),
            'rejected' => __( 'Rejected', 'sleeve-ke' )
        );
    }

    /**
     * Get applications data (mock data for demonstration)
     */
    public function get_applications_data() {
        // Apply filters if any
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $job_filter = isset( $_GET['job_id'] ) ? intval( $_GET['job_id'] ) : 0;

        // Mock data - in real implementation, this would fetch from database
        $all_applications = array(
            array(
                'id' => 1,
                'candidate_name' => 'John Doe',
                'candidate_email' => 'john.doe@email.com',
                'job_title' => 'Senior PHP Developer',
                'job_id' => 1,
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
                'job_id' => 2,
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
                'job_id' => 3,
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
                'job_id' => 4,
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
                'job_id' => 5,
                'company_name' => 'Analytics Inc',
                'applied_date' => '2025-10-11',
                'status' => 'rejected',
                'score' => 65
            )
        );

        // Apply filters
        $filtered_applications = $all_applications;

        if ( ! empty( $search ) ) {
            $filtered_applications = array_filter( $filtered_applications, function( $app ) use ( $search ) {
                return stripos( $app['candidate_name'], $search ) !== false || 
                       stripos( $app['job_title'], $search ) !== false;
            });
        }

        if ( ! empty( $status_filter ) ) {
            $filtered_applications = array_filter( $filtered_applications, function( $app ) use ( $status_filter ) {
                return $app['status'] === $status_filter;
            });
        }

        if ( ! empty( $job_filter ) ) {
            $filtered_applications = array_filter( $filtered_applications, function( $app ) use ( $job_filter ) {
                return $app['job_id'] === $job_filter;
            });
        }

        return $filtered_applications;
    }
    
    /**
     * Get jobs for filter dropdown
     */
    public function get_jobs_for_filter() {
        // In real implementation, this would fetch from database
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
    public function get_application_stats() {
        // In real implementation, this would calculate from database
        $applications = $this->get_applications_data();
        $statuses = $this->get_status_options();
        
        $stats = array();
        $stats[] = array( 'count' => count( $applications ), 'label' => __( 'Total Applications', 'sleeve-ke' ) );
        
        foreach ( $statuses as $status_key => $status_label ) {
            $count = count( array_filter( $applications, function( $app ) use ( $status_key ) {
                return $app['status'] === $status_key;
            }));
            $stats[] = array( 'count' => $count, 'label' => $status_label );
        }
        
        return $stats;
    }
    
    /**
     * Handle application actions
     */
    public function handle_application_actions() {
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
    public function update_applications_status( $application_ids, $status ) {
        // This would normally update the database
        // For now, just simulate the action
        foreach ( $application_ids as $id ) {
            // Update database: UPDATE sleeve_applications SET status = $status WHERE id = $id
        }
    }
    
    /**
     * Delete applications
     */
    public function delete_applications( $application_ids ) {
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
        $valid_statuses = array_keys( $this->get_status_options() );
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

    /**
     * Get a single application by ID
     */
    public function get_application_by_id( $application_id ) {
        $applications = $this->get_applications_data();
        foreach ( $applications as $application ) {
            if ( $application['id'] == $application_id ) {
                return $application;
            }
        }
        return null;
    }

    /**
     * Display individual application view
     */
    public function display_application_view( $application_id ) {
        $application = $this->get_application_by_id( $application_id );
        
        if ( ! $application ) {
            echo '<div class="wrap"><h1>' . __( 'Application Not Found', 'sleeve-ke' ) . '</h1></div>';
            return;
        }

        $statuses = $this->get_status_options();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Application Details', 'sleeve-ke' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-applications' ) ); ?>" class="button">
                <?php esc_html_e( '← Back to Applications', 'sleeve-ke' ); ?>
            </a>
            
            <div class="sleeve-ke-application-details">
                <div class="application-header">
                    <h2><?php echo esc_html( $application['candidate_name'] ); ?></h2>
                    <span class="status-badge status-<?php echo esc_attr( $application['status'] ); ?>">
                        <?php echo esc_html( $statuses[ $application['status'] ] ); ?>
                    </span>
                </div>
                
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Candidate Email', 'sleeve-ke' ); ?></th>
                        <td><?php echo esc_html( $application['candidate_email'] ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Job Title', 'sleeve-ke' ); ?></th>
                        <td><?php echo esc_html( $application['job_title'] ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Company', 'sleeve-ke' ); ?></th>
                        <td><?php echo esc_html( $application['company_name'] ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Applied Date', 'sleeve-ke' ); ?></th>
                        <td><?php echo esc_html( $application['applied_date'] ); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Score', 'sleeve-ke' ); ?></th>
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
                    </tr>
                </table>
                
                <div class="application-actions">
                    <h3><?php esc_html_e( 'Update Status', 'sleeve-ke' ); ?></h3>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'update_single_application', 'single_app_nonce' ); ?>
                        <input type="hidden" name="application_id" value="<?php echo esc_attr( $application['id'] ); ?>" />
                        
                        <select name="new_status">
                            <?php foreach ( $statuses as $status_key => $status_label ) : ?>
                                <option value="<?php echo esc_attr( $status_key ); ?>" 
                                        <?php selected( $application['status'], $status_key ); ?>>
                                    <?php echo esc_html( $status_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php submit_button( __( 'Update Status', 'sleeve-ke' ), 'primary', 'update_status' ); ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}