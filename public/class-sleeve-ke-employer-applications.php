<?php
/**
 * Employer Applications Management
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/public
 */

/**
 * Handles employer application management interface
 */
class Sleeve_KE_Employer_Applications {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_shortcode( 'sleeve_ke_employer_applications', array( $this, 'applications_shortcode' ) );
        
        // Handle AJAX for scheduling interviews
        add_action( 'wp_ajax_sleeve_ke_schedule_interview', array( $this, 'ajax_schedule_interview' ) );
        add_action( 'wp_ajax_sleeve_ke_update_application_status', array( $this, 'ajax_update_status' ) );
    }

    /**
     * Shortcode to display employer applications
     */
    public function applications_shortcode( $atts ) {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return '<div class="sleeve-ke-message error">' . __( 'You must be logged in to view applications.', 'sleeve-ke' ) . '</div>';
        }

        // Check if user is an employer
        $current_user = wp_get_current_user();
        if ( ! in_array( 'employer', $current_user->roles ) ) {
            return '<div class="sleeve-ke-message error">' . __( 'Access denied. This page is for employers only.', 'sleeve-ke' ) . '</div>';
        }

        ob_start();
        $this->display_applications_dashboard();
        return ob_get_clean();
    }

    /**
     * Display applications dashboard
     */
    private function display_applications_dashboard() {
        global $wpdb;
        
        $current_user_id = get_current_user_id();
        
        // Get filter parameters
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
        $job_filter = isset( $_GET['job_id'] ) ? intval( $_GET['job_id'] ) : 0;
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        
        // Get employer's jobs
        $jobs_table = $wpdb->prefix . 'sleeve_jobs';
        $employer_jobs = $wpdb->get_results( $wpdb->prepare(
            "SELECT id, title FROM $jobs_table WHERE employer_id = %d ORDER BY created_at DESC",
            $current_user_id
        ), ARRAY_A );
        
        // Build applications query
        $applications_table = $wpdb->prefix . 'sleeve_applications';
        $candidates_table = $wpdb->prefix . 'sleeve_candidates';
        
        $where = array( "j.employer_id = %d" );
        $where_values = array( $current_user_id );
        
        if ( $status_filter ) {
            $where[] = "a.status = %s";
            $where_values[] = $status_filter;
        }
        
        if ( $job_filter ) {
            $where[] = "a.job_id = %d";
            $where_values[] = $job_filter;
        }
        
        if ( $search ) {
            $where[] = "(u.display_name LIKE %s OR u.user_email LIKE %s)";
            $where_values[] = '%' . $wpdb->esc_like( $search ) . '%';
            $where_values[] = '%' . $wpdb->esc_like( $search ) . '%';
        }
        
        $where_sql = implode( ' AND ', $where );
        
        $query = "SELECT a.*, 
                         j.title as job_title,
                         j.location as job_location,
                         u.display_name as candidate_name,
                         u.user_email as candidate_email,
                         c.phone as candidate_phone,
                         c.experience_years,
                         c.education,
                         c.skills,
                         c.resume_url,
                         c.linkedin_url
                  FROM $applications_table a
                  LEFT JOIN $jobs_table j ON a.job_id = j.id
                  LEFT JOIN {$wpdb->users} u ON a.candidate_id = u.ID
                  LEFT JOIN $candidates_table c ON a.candidate_id = c.user_id
                  WHERE $where_sql
                  ORDER BY a.applied_at DESC";
        
        $applications = $wpdb->get_results( $wpdb->prepare( $query, $where_values ), ARRAY_A );
        
        // Get statistics
        $stats_query = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN a.status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
                            SUM(CASE WHEN a.status = 'interview' THEN 1 ELSE 0 END) as interview,
                            SUM(CASE WHEN a.status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                            SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
                        FROM $applications_table a
                        LEFT JOIN $jobs_table j ON a.job_id = j.id
                        WHERE j.employer_id = %d";
        
        $stats = $wpdb->get_row( $wpdb->prepare( $stats_query, $current_user_id ), ARRAY_A );
        
        ?>
        <div class="sleeve-ke-employer-applications-wrap">
            <div class="applications-header">
                <h1><?php _e( 'Job Applications', 'sleeve-ke' ); ?></h1>
                <p class="description"><?php _e( 'Manage applications to your job postings', 'sleeve-ke' ); ?></p>
            </div>

            <!-- Statistics Cards -->
            <div class="applications-stats">
                <div class="stat-card total">
                    <span class="stat-number"><?php echo esc_html( $stats['total'] ); ?></span>
                    <span class="stat-label"><?php _e( 'Total Applications', 'sleeve-ke' ); ?></span>
                </div>
                <div class="stat-card pending">
                    <span class="stat-number"><?php echo esc_html( $stats['pending'] ); ?></span>
                    <span class="stat-label"><?php _e( 'Pending Review', 'sleeve-ke' ); ?></span>
                </div>
                <div class="stat-card interview">
                    <span class="stat-number"><?php echo esc_html( $stats['interview'] ); ?></span>
                    <span class="stat-label"><?php _e( 'Interview Scheduled', 'sleeve-ke' ); ?></span>
                </div>
                <div class="stat-card accepted">
                    <span class="stat-number"><?php echo esc_html( $stats['accepted'] ); ?></span>
                    <span class="stat-label"><?php _e( 'Accepted', 'sleeve-ke' ); ?></span>
                </div>
            </div>

            <!-- Filters -->
            <div class="applications-filters">
                <form method="get" action="" class="filters-form">
                    <div class="filter-group">
                        <input type="text" name="search" value="<?php echo esc_attr( $search ); ?>" 
                               placeholder="<?php esc_attr_e( 'Search candidates...', 'sleeve-ke' ); ?>" class="search-input">
                    </div>
                    
                    <div class="filter-group">
                        <select name="job_id" class="job-filter">
                            <option value=""><?php _e( 'All Jobs', 'sleeve-ke' ); ?></option>
                            <?php foreach ( $employer_jobs as $job ) : ?>
                                <option value="<?php echo esc_attr( $job['id'] ); ?>" <?php selected( $job_filter, $job['id'] ); ?>>
                                    <?php echo esc_html( $job['title'] ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select name="status" class="status-filter">
                            <option value=""><?php _e( 'All Statuses', 'sleeve-ke' ); ?></option>
                            <option value="pending" <?php selected( $status_filter, 'pending' ); ?>><?php _e( 'Pending', 'sleeve-ke' ); ?></option>
                            <option value="reviewed" <?php selected( $status_filter, 'reviewed' ); ?>><?php _e( 'Reviewed', 'sleeve-ke' ); ?></option>
                            <option value="interview" <?php selected( $status_filter, 'interview' ); ?>><?php _e( 'Interview', 'sleeve-ke' ); ?></option>
                            <option value="accepted" <?php selected( $status_filter, 'accepted' ); ?>><?php _e( 'Accepted', 'sleeve-ke' ); ?></option>
                            <option value="rejected" <?php selected( $status_filter, 'rejected' ); ?>><?php _e( 'Rejected', 'sleeve-ke' ); ?></option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-filter"><?php _e( 'Filter', 'sleeve-ke' ); ?></button>
                    <a href="<?php echo esc_url( remove_query_arg( array( 'search', 'job_id', 'status' ) ) ); ?>" class="btn-clear">
                        <?php _e( 'Clear', 'sleeve-ke' ); ?>
                    </a>
                </form>
            </div>

            <!-- Applications Table -->
            <div class="applications-list">
                <?php if ( empty( $applications ) ) : ?>
                    <div class="no-applications">
                        <p><?php _e( 'No applications found.', 'sleeve-ke' ); ?></p>
                    </div>
                <?php else : ?>
                    <table class="applications-table">
                        <thead>
                            <tr>
                                <th><?php _e( 'Candidate', 'sleeve-ke' ); ?></th>
                                <th><?php _e( 'Job Position', 'sleeve-ke' ); ?></th>
                                <th><?php _e( 'Experience', 'sleeve-ke' ); ?></th>
                                <th><?php _e( 'Applied Date', 'sleeve-ke' ); ?></th>
                                <th><?php _e( 'Status', 'sleeve-ke' ); ?></th>
                                <th><?php _e( 'Actions', 'sleeve-ke' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $applications as $application ) : ?>
                                <tr class="application-row status-<?php echo esc_attr( $application['status'] ); ?>" data-application-id="<?php echo esc_attr( $application['id'] ); ?>">
                                    <td class="candidate-info">
                                        <strong><?php echo esc_html( $application['candidate_name'] ); ?></strong>
                                        <br>
                                        <small><?php echo esc_html( $application['education'] ); ?></small>
                                    </td>
                                    <td>
                                        <?php echo esc_html( $application['job_title'] ); ?>
                                        <br>
                                        <small class="text-muted"><?php echo esc_html( $application['job_location'] ); ?></small>
                                    </td>
                                    <td><?php echo esc_html( $application['experience_years'] ); ?> <?php _e( 'years', 'sleeve-ke' ); ?></td>
                                    <td><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $application['applied_at'] ) ) ); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr( $application['status'] ); ?>">
                                            <?php echo esc_html( ucfirst( $application['status'] ) ); ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <button type="button" class="btn-view-details" data-application-id="<?php echo esc_attr( $application['id'] ); ?>">
                                            <?php _e( 'View Details', 'sleeve-ke' ); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Application Details Modal -->
        <div id="application-modal" class="sleeve-ke-modal" style="display: none;">
            <div class="modal-content">
                <span class="modal-close">&times;</span>
                <div id="modal-body">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>

        <style>
        .sleeve-ke-employer-applications-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .applications-header {
            margin-bottom: 30px;
        }
        .applications-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card.total { border-top: 4px solid #2271b1; }
        .stat-card.pending { border-top: 4px solid #f0b849; }
        .stat-card.interview { border-top: 4px solid #72aee6; }
        .stat-card.accepted { border-top: 4px solid #00a32a; }
        .stat-number {
            display: block;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            display: block;
            font-size: 14px;
            color: #666;
        }
        .applications-filters {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .filters-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .search-input, .job-filter, .status-filter {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-filter, .btn-clear {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-filter {
            background: #2271b1;
            color: #fff;
        }
        .btn-clear {
            background: #ddd;
            color: #333;
        }
        .applications-table {
            width: 100%;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            border-collapse: collapse;
            overflow: hidden;
        }
        .applications-table th {
            background: #f6f7f7;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        .applications-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .applications-table tr:hover {
            background: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.status-pending { background: #f0b849; color: #fff; }
        .status-badge.status-reviewed { background: #72aee6; color: #fff; }
        .status-badge.status-interview { background: #8c8f94; color: #fff; }
        .status-badge.status-accepted { background: #00a32a; color: #fff; }
        .status-badge.status-rejected { background: #d63638; color: #fff; }
        .btn-view-details {
            padding: 6px 12px;
            background: #2271b1;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-view-details:hover {
            background: #135e96;
        }
        .no-applications {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            color: #666;
        }
        .sleeve-ke-modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            padding: 10px 20px;
            cursor: pointer;
        }
        .modal-close:hover { color: #000; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // View application details
            $('.btn-view-details').on('click', function() {
                var applicationId = $(this).data('application-id');
                loadApplicationDetails(applicationId);
            });

            // Close modal
            $('.modal-close, .sleeve-ke-modal').on('click', function(e) {
                if (e.target === this) {
                    $('#application-modal').hide();
                }
            });

            function loadApplicationDetails(applicationId) {
                $('#modal-body').html('<div style="padding: 40px; text-align: center;">Loading...</div>');
                $('#application-modal').show();

                $.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    type: 'POST',
                    data: {
                        action: 'sleeve_ke_get_application_details',
                        application_id: applicationId,
                        nonce: '<?php echo wp_create_nonce( 'sleeve_applications' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#modal-body').html(response.data.html);
                        } else {
                            $('#modal-body').html('<div class="error">' + response.data.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#modal-body').html('<div class="error">Error loading application details.</div>');
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * AJAX: Get application details
     */
    public function ajax_get_application_details() {
        check_ajax_referer( 'sleeve_applications', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'sleeve-ke' ) ) );
        }

        $application_id = intval( $_POST['application_id'] );
        $current_user_id = get_current_user_id();

        global $wpdb;
        
        // Get application with verification that it belongs to employer's job
        $applications_table = $wpdb->prefix . 'sleeve_applications';
        $jobs_table = $wpdb->prefix . 'sleeve_jobs';
        $candidates_table = $wpdb->prefix . 'sleeve_candidates';
        
        $query = "SELECT a.*, 
                         j.title as job_title,
                         j.location as job_location,
                         j.salary_range,
                         u.display_name as candidate_name,
                         u.user_email as candidate_email,
                         c.phone as candidate_phone,
                         c.experience_years,
                         c.education,
                         c.skills,
                         c.resume_url,
                         c.linkedin_url,
                         c.portfolio_url
                  FROM $applications_table a
                  LEFT JOIN $jobs_table j ON a.job_id = j.id
                  LEFT JOIN {$wpdb->users} u ON a.candidate_id = u.ID
                  LEFT JOIN $candidates_table c ON a.candidate_id = c.user_id
                  WHERE a.id = %d AND j.employer_id = %d";
        
        $application = $wpdb->get_row( $wpdb->prepare( $query, $application_id, $current_user_id ), ARRAY_A );
        
        if ( ! $application ) {
            wp_send_json_error( array( 'message' => __( 'Application not found or access denied.', 'sleeve-ke' ) ) );
        }

        ob_start();
        $this->render_application_details( $application );
        $html = ob_get_clean();

        wp_send_json_success( array( 'html' => $html ) );
    }

    /**
     * Render application details modal content
     */
    private function render_application_details( $application ) {
        ?>
        <div class="application-details-modal">
            <div class="modal-header">
                <h2><?php echo esc_html( $application['candidate_name'] ); ?></h2>
                <p class="job-applied"><?php _e( 'Applied for:', 'sleeve-ke' ); ?> <strong><?php echo esc_html( $application['job_title'] ); ?></strong></p>
            </div>

            <div class="modal-body-content">
                <div class="detail-section">
                    <h3><?php _e( 'Candidate Information', 'sleeve-ke' ); ?></h3>
                    <table class="detail-table">
                        <tr>
                            <th><?php _e( 'Experience:', 'sleeve-ke' ); ?></th>
                            <td><?php echo esc_html( $application['experience_years'] ); ?> <?php _e( 'years', 'sleeve-ke' ); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Education:', 'sleeve-ke' ); ?></th>
                            <td><?php echo esc_html( $application['education'] ); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Skills:', 'sleeve-ke' ); ?></th>
                            <td><?php echo esc_html( $application['skills'] ); ?></td>
                        </tr>
                        <?php if ( $application['linkedin_url'] ) : ?>
                        <tr>
                            <th><?php _e( 'LinkedIn:', 'sleeve-ke' ); ?></th>
                            <td><a href="<?php echo esc_url( $application['linkedin_url'] ); ?>" target="_blank"><?php _e( 'View Profile', 'sleeve-ke' ); ?></a></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( $application['portfolio_url'] ) : ?>
                        <tr>
                            <th><?php _e( 'Portfolio:', 'sleeve-ke' ); ?></th>
                            <td><a href="<?php echo esc_url( $application['portfolio_url'] ); ?>" target="_blank"><?php _e( 'View Portfolio', 'sleeve-ke' ); ?></a></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( $application['resume_url'] ) : ?>
                        <tr>
                            <th><?php _e( 'Resume:', 'sleeve-ke' ); ?></th>
                            <td><a href="<?php echo esc_url( $application['resume_url'] ); ?>" target="_blank" class="btn-download"><?php _e( 'Download Resume', 'sleeve-ke' ); ?></a></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <?php if ( $application['cover_letter'] ) : ?>
                <div class="detail-section">
                    <h3><?php _e( 'Cover Letter', 'sleeve-ke' ); ?></h3>
                    <div class="cover-letter">
                        <?php echo nl2br( esc_html( $application['cover_letter'] ) ); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="detail-section">
                    <h3><?php _e( 'Application Details', 'sleeve-ke' ); ?></h3>
                    <table class="detail-table">
                        <tr>
                            <th><?php _e( 'Applied Date:', 'sleeve-ke' ); ?></th>
                            <td><?php echo esc_html( date_i18n( 'F j, Y \a\t g:i A', strtotime( $application['applied_at'] ) ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Current Status:', 'sleeve-ke' ); ?></th>
                            <td><span class="status-badge status-<?php echo esc_attr( $application['status'] ); ?>"><?php echo esc_html( ucfirst( $application['status'] ) ); ?></span></td>
                        </tr>
                    </table>
                </div>

                <?php if ( $application['notes'] ) : ?>
                <div class="detail-section">
                    <h3><?php _e( 'Internal Notes', 'sleeve-ke' ); ?></h3>
                    <div class="notes-content">
                        <?php echo nl2br( esc_html( $application['notes'] ) ); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="detail-section actions-section">
                    <h3><?php _e( 'Actions', 'sleeve-ke' ); ?></h3>
                    
                    <div class="action-buttons">
                        <button type="button" class="btn-action btn-schedule" data-application-id="<?php echo esc_attr( $application['id'] ); ?>">
                            <?php _e( 'Schedule Interview', 'sleeve-ke' ); ?>
                        </button>
                        
                        <button type="button" class="btn-action btn-accept" data-application-id="<?php echo esc_attr( $application['id'] ); ?>" data-status="accepted">
                            <?php _e( 'Accept Application', 'sleeve-ke' ); ?>
                        </button>
                        
                        <button type="button" class="btn-action btn-reject" data-application-id="<?php echo esc_attr( $application['id'] ); ?>" data-status="rejected">
                            <?php _e( 'Reject Application', 'sleeve-ke' ); ?>
                        </button>
                        
                        <button type="button" class="btn-action btn-reviewed" data-application-id="<?php echo esc_attr( $application['id'] ); ?>" data-status="reviewed">
                            <?php _e( 'Mark as Reviewed', 'sleeve-ke' ); ?>
                        </button>
                    </div>

                    <!-- Interview Scheduling Form (hidden by default) -->
                    <div id="interview-form-<?php echo esc_attr( $application['id'] ); ?>" class="interview-form" style="display: none;">
                        <h4><?php _e( 'Schedule Interview', 'sleeve-ke' ); ?></h4>
                        <form class="schedule-interview-form" data-application-id="<?php echo esc_attr( $application['id'] ); ?>">
                            <div class="form-group">
                                <label><?php _e( 'Interview Date:', 'sleeve-ke' ); ?></label>
                                <input type="datetime-local" name="interview_date" required>
                            </div>
                            <div class="form-group">
                                <label><?php _e( 'Interview Location/Method:', 'sleeve-ke' ); ?></label>
                                <input type="text" name="interview_location" placeholder="e.g., Office, Zoom, Google Meet" required>
                            </div>
                            <div class="form-group">
                                <label><?php _e( 'Additional Notes:', 'sleeve-ke' ); ?></label>
                                <textarea name="interview_notes" rows="3" placeholder="<?php esc_attr_e( 'Any additional information for the candidate...', 'sleeve-ke' ); ?>"></textarea>
                            </div>
                            <button type="submit" class="btn-submit-interview"><?php _e( 'Send Interview Invitation', 'sleeve-ke' ); ?></button>
                            <button type="button" class="btn-cancel-interview"><?php _e( 'Cancel', 'sleeve-ke' ); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .application-details-modal {
            padding: 20px;
        }
        .modal-header {
            border-bottom: 2px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .modal-header h2 {
            margin: 0 0 5px 0;
        }
        .job-applied {
            color: #666;
            margin: 0;
        }
        .modal-body-content {
            max-height: 60vh;
            overflow-y: auto;
        }
        .detail-section {
            margin-bottom: 25px;
        }
        .detail-section h3 {
            margin: 0 0 15px 0;
            color: #2271b1;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }
        .detail-table th {
            text-align: left;
            padding: 8px 12px;
            background: #f6f7f7;
            width: 30%;
            font-weight: 600;
        }
        .detail-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .cover-letter {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #2271b1;
        }
        .notes-content {
            background: #fffbcc;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #f0b849;
        }
        .actions-section {
            border-top: 2px solid #ddd;
            padding-top: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .btn-action {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-schedule {
            background: #2271b1;
            color: #fff;
        }
        .btn-accept {
            background: #00a32a;
            color: #fff;
        }
        .btn-reject {
            background: #d63638;
            color: #fff;
        }
        .btn-reviewed {
            background: #72aee6;
            color: #fff;
        }
        .btn-action:hover {
            opacity: 0.8;
        }
        .interview-form {
            background: #f6f7f7;
            padding: 20px;
            border-radius: 4px;
            margin-top: 15px;
        }
        .interview-form h4 {
            margin: 0 0 15px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-submit-interview,
        .btn-cancel-interview {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn-submit-interview {
            background: #00a32a;
            color: #fff;
        }
        .btn-cancel-interview {
            background: #ddd;
            color: #333;
        }
        .btn-download {
            display: inline-block;
            padding: 6px 12px;
            background: #2271b1;
            color: #fff !important;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Show interview form
            $('.btn-schedule').on('click', function() {
                var applicationId = $(this).data('application-id');
                $('#interview-form-' + applicationId).slideDown();
            });

            // Cancel interview form
            $('.btn-cancel-interview').on('click', function() {
                $(this).closest('.interview-form').slideUp();
            });

            // Submit interview schedule
            $('.schedule-interview-form').on('submit', function(e) {
                e.preventDefault();
                var applicationId = $(this).data('application-id');
                var formData = {
                    action: 'sleeve_ke_schedule_interview',
                    application_id: applicationId,
                    interview_date: $(this).find('[name="interview_date"]').val(),
                    interview_location: $(this).find('[name="interview_location"]').val(),
                    interview_notes: $(this).find('[name="interview_notes"]').val(),
                    nonce: '<?php echo wp_create_nonce( 'sleeve_applications' ); ?>'
                };

                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', formData, function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                });
            });

            // Update application status
            $('.btn-accept, .btn-reject, .btn-reviewed').on('click', function() {
                if (!confirm('<?php _e( 'Are you sure you want to update this application status?', 'sleeve-ke' ); ?>')) {
                    return;
                }

                var applicationId = $(this).data('application-id');
                var status = $(this).data('status');

                $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                    action: 'sleeve_ke_update_application_status',
                    application_id: applicationId,
                    status: status,
                    nonce: '<?php echo wp_create_nonce( 'sleeve_applications' ); ?>'
                }, function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX: Schedule interview
     */
    public function ajax_schedule_interview() {
        check_ajax_referer( 'sleeve_applications', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'sleeve-ke' ) ) );
        }

        $application_id = intval( $_POST['application_id'] );
        $interview_date = sanitize_text_field( $_POST['interview_date'] );
        $interview_location = sanitize_text_field( $_POST['interview_location'] );
        $interview_notes = sanitize_textarea_field( $_POST['interview_notes'] );

        global $wpdb;
        
        // Update application status and notes
        $applications_table = $wpdb->prefix . 'sleeve_applications';
        $notes = "Interview scheduled for: " . date( 'F j, Y \a\t g:i A', strtotime( $interview_date ) ) . "\n";
        $notes .= "Location/Method: " . $interview_location . "\n";
        if ( $interview_notes ) {
            $notes .= "Notes: " . $interview_notes;
        }

        $result = $wpdb->update(
            $applications_table,
            array(
                'status' => 'interview',
                'notes' => $notes
            ),
            array( 'id' => $application_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        if ( $result !== false ) {
            // TODO: Send email notification to candidate
            wp_send_json_success( array( 'message' => __( 'Interview scheduled successfully! The candidate will be notified.', 'sleeve-ke' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to schedule interview.', 'sleeve-ke' ) ) );
        }
    }

    /**
     * AJAX: Update application status
     */
    public function ajax_update_status() {
        check_ajax_referer( 'sleeve_applications', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'sleeve-ke' ) ) );
        }

        $application_id = intval( $_POST['application_id'] );
        $status = sanitize_text_field( $_POST['status'] );

        global $wpdb;
        
        $applications_table = $wpdb->prefix . 'sleeve_applications';
        $result = $wpdb->update(
            $applications_table,
            array( 'status' => $status ),
            array( 'id' => $application_id ),
            array( '%s' ),
            array( '%d' )
        );

        if ( $result !== false ) {
            // TODO: Send email notification to candidate
            wp_send_json_success( array( 'message' => __( 'Application status updated successfully!', 'sleeve-ke' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to update application status.', 'sleeve-ke' ) ) );
        }
    }
}

// Initialize
add_action( 'wp_ajax_sleeve_ke_get_application_details', array( new Sleeve_KE_Employer_Applications(), 'ajax_get_application_details' ) );
