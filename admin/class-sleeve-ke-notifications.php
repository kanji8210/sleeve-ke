<?php
/**
 * Email notifications management functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Email notifications management class.
 *
 * Handles all email notifications for job applications, employer updates,
 * candidate notifications, and administrative alerts.
 */
class Sleeve_KE_Notifications {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Hook into WordPress email system
        add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
        
        // Add custom email headers
        add_action( 'phpmailer_init', array( $this, 'configure_phpmailer' ) );
    }

    /**
     * Set email content type to HTML
     */
    public function set_html_content_type() {
        return 'text/html';
    }

    /**
     * Configure PHPMailer for better email delivery
     */
    public function configure_phpmailer( $phpmailer ) {
        $phpmailer->isHTML( true );
        $phpmailer->CharSet = 'UTF-8';
    }

    /**
     * Display the notifications management page.
     */
    public function display_page() {
        // Handle form submissions
        if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['sleeve_nonce'], 'sleeve_notifications' ) ) {
            $this->handle_notification_actions();
        }
        
        // Check if we're viewing notification logs or settings
        $view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'logs';
        
        switch ( $view ) {
            case 'settings':
                $this->display_notification_settings();
                break;
            case 'templates':
                $this->display_email_templates();
                break;
            case 'test':
                $this->display_test_notifications();
                break;
            default:
                $this->display_notification_logs();
                break;
        }
    }

    /**
     * Display notification logs page
     */
    private function display_notification_logs() {
        $logs = $this->get_notification_logs();
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e( 'Email Notifications', 'sleeve-ke' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=test' ) ); ?>" class="page-title-action">
                    <?php esc_html_e( 'Send Test Email', 'sleeve-ke' ); ?>
                </a>
            </h1>
            
            <!-- Navigation Tabs -->
            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=logs' ) ); ?>" 
                   class="nav-tab nav-tab-active">
                    <?php esc_html_e( 'Email Logs', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=settings' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Settings', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=templates' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Email Templates', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=test' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Test Emails', 'sleeve-ke' ); ?>
                </a>
            </nav>

            <!-- Notification Statistics -->
            <div class="sleeve-ke-notification-stats">
                <h3><?php esc_html_e( 'Email Statistics (Last 30 Days)', 'sleeve-ke' ); ?></h3>
                <div class="stats-grid">
                    <?php
                    $stats = $this->get_notification_stats();
                    foreach ( $stats as $stat ) :
                    ?>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo esc_html( $stat['count'] ); ?></div>
                            <div class="stat-label"><?php echo esc_html( $stat['label'] ); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="sleeve-ke-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="sleeve-ke-notifications" />
                    <input type="hidden" name="view" value="logs" />
                    
                    <div class="filter-row">
                        <input type="text" name="search" placeholder="<?php esc_attr_e( 'Search by recipient, subject...', 'sleeve-ke' ); ?>" 
                               value="<?php echo esc_attr( isset( $_GET['search'] ) ? $_GET['search'] : '' ); ?>" />
                        
                        <select name="type">
                            <option value=""><?php esc_html_e( 'All Types', 'sleeve-ke' ); ?></option>
                            <?php
                            $types = $this->get_notification_types();
                            foreach ( $types as $type_key => $type_label ) :
                            ?>
                                <option value="<?php echo esc_attr( $type_key ); ?>" 
                                        <?php selected( isset( $_GET['type'] ) ? $_GET['type'] : '', $type_key ); ?>>
                                    <?php echo esc_html( $type_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="status">
                            <option value=""><?php esc_html_e( 'All Statuses', 'sleeve-ke' ); ?></option>
                            <option value="sent" <?php selected( isset( $_GET['status'] ) ? $_GET['status'] : '', 'sent' ); ?>>
                                <?php esc_html_e( 'Sent', 'sleeve-ke' ); ?>
                            </option>
                            <option value="failed" <?php selected( isset( $_GET['status'] ) ? $_GET['status'] : '', 'failed' ); ?>>
                                <?php esc_html_e( 'Failed', 'sleeve-ke' ); ?>
                            </option>
                            <option value="pending" <?php selected( isset( $_GET['status'] ) ? $_GET['status'] : '', 'pending' ); ?>>
                                <?php esc_html_e( 'Pending', 'sleeve-ke' ); ?>
                            </option>
                        </select>
                        
                        <?php submit_button( __( 'Filter', 'sleeve-ke' ), 'secondary', 'filter', false ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=logs' ) ); ?>" class="button">
                            <?php esc_html_e( 'Clear', 'sleeve-ke' ); ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Email Logs Table -->
            <table class="wp-list-table widefat fixed striped sleeve-ke-notifications-table">
                <thead>
                    <tr>
                        <th class="manage-column"><?php esc_html_e( 'Date/Time', 'sleeve-ke' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Type', 'sleeve-ke' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Recipient', 'sleeve-ke' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Subject', 'sleeve-ke' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Status', 'sleeve-ke' ); ?></th>
                        <th class="manage-column"><?php esc_html_e( 'Actions', 'sleeve-ke' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $logs ) ) : ?>
                        <tr>
                            <td colspan="6" class="no-items">
                                <?php esc_html_e( 'No email notifications found.', 'sleeve-ke' ); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $logs as $log ) : ?>
                            <tr>
                                <td><?php echo esc_html( $log['sent_date'] ); ?></td>
                                <td>
                                    <span class="notification-type type-<?php echo esc_attr( $log['type'] ); ?>">
                                        <?php echo esc_html( $types[ $log['type'] ] ?? $log['type'] ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( $log['recipient'] ); ?></td>
                                <td>
                                    <strong><?php echo esc_html( $log['subject'] ); ?></strong>
                                    <?php if ( ! empty( $log['preview'] ) ) : ?>
                                        <div class="email-preview"><?php echo esc_html( $log['preview'] ); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr( $log['status'] ); ?>">
                                        <?php echo esc_html( ucfirst( $log['status'] ) ); ?>
                                    </span>
                                    <?php if ( $log['status'] === 'failed' && ! empty( $log['error_message'] ) ) : ?>
                                        <div class="error-message" title="<?php echo esc_attr( $log['error_message'] ); ?>">
                                            ⚠️ <?php echo esc_html( substr( $log['error_message'], 0, 50 ) . '...' ); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="#" class="button-secondary view-email" 
                                       data-email-id="<?php echo esc_attr( $log['id'] ); ?>">
                                        <?php esc_html_e( 'View', 'sleeve-ke' ); ?>
                                    </a>
                                    <?php if ( $log['status'] === 'failed' ) : ?>
                                        <a href="#" class="button-secondary resend-email" 
                                           data-email-id="<?php echo esc_attr( $log['id'] ); ?>">
                                            <?php esc_html_e( 'Resend', 'sleeve-ke' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Email View Modal -->
        <div id="email-view-modal" class="sleeve-ke-modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><?php esc_html_e( 'Email Details', 'sleeve-ke' ); ?></h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="email-content"></div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // View email modal
            $('.view-email').on('click', function(e) {
                e.preventDefault();
                var emailId = $(this).data('email-id');
                
                $.post(ajaxurl, {
                    action: 'view_email_content',
                    email_id: emailId,
                    nonce: '<?php echo wp_create_nonce( 'view_email_content' ); ?>'
                }, function(response) {
                    if (response.success) {
                        $('#email-content').html(response.data.content);
                        $('#email-view-modal').show();
                    }
                });
            });

            // Close modal
            $('.close, #email-view-modal').on('click', function(e) {
                if (e.target === this) {
                    $('#email-view-modal').hide();
                }
            });

            // Resend email
            $('.resend-email').on('click', function(e) {
                e.preventDefault();
                var emailId = $(this).data('email-id');
                
                if (confirm('<?php esc_js( __( 'Are you sure you want to resend this email?', 'sleeve-ke' ) ); ?>')) {
                    $.post(ajaxurl, {
                        action: 'resend_email',
                        email_id: emailId,
                        nonce: '<?php echo wp_create_nonce( 'resend_email' ); ?>'
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error resending email: ' + response.data.message);
                        }
                    });
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Display notification settings page
     */
    private function display_notification_settings() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Email Notification Settings', 'sleeve-ke' ); ?></h1>
            
            <!-- Navigation Tabs -->
            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=logs' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Email Logs', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=settings' ) ); ?>" 
                   class="nav-tab nav-tab-active">
                    <?php esc_html_e( 'Settings', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=templates' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Email Templates', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=test' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Test Emails', 'sleeve-ke' ); ?>
                </a>
            </nav>

            <form method="post" action="">
                <?php wp_nonce_field( 'sleeve_notifications', 'sleeve_nonce' ); ?>
                <input type="hidden" name="action" value="save_settings" />

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable Email Notifications', 'sleeve-ke' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_notifications" value="1" 
                                       <?php checked( get_option( 'sleeve_ke_enable_notifications', 1 ) ); ?> />
                                <?php esc_html_e( 'Enable all email notifications', 'sleeve-ke' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'From Email Address', 'sleeve-ke' ); ?></th>
                        <td>
                            <input type="email" name="from_email" class="regular-text" 
                                   value="<?php echo esc_attr( get_option( 'sleeve_ke_from_email', get_option( 'admin_email' ) ) ); ?>" />
                            <p class="description"><?php esc_html_e( 'Email address that notifications will be sent from.', 'sleeve-ke' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'From Name', 'sleeve-ke' ); ?></th>
                        <td>
                            <input type="text" name="from_name" class="regular-text" 
                                   value="<?php echo esc_attr( get_option( 'sleeve_ke_from_name', get_bloginfo( 'name' ) ) ); ?>" />
                            <p class="description"><?php esc_html_e( 'Name that will appear as the sender.', 'sleeve-ke' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Admin Email Notifications', 'sleeve-ke' ); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="admin_new_application" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_admin_new_application', 1 ) ); ?> />
                                    <?php esc_html_e( 'New job application submitted', 'sleeve-ke' ); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="admin_new_employer" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_admin_new_employer', 1 ) ); ?> />
                                    <?php esc_html_e( 'New employer registration', 'sleeve-ke' ); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="admin_new_candidate" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_admin_new_candidate', 1 ) ); ?> />
                                    <?php esc_html_e( 'New candidate registration', 'sleeve-ke' ); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="admin_job_posted" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_admin_job_posted', 1 ) ); ?> />
                                    <?php esc_html_e( 'New job posted', 'sleeve-ke' ); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Candidate Notifications', 'sleeve-ke' ); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="candidate_application_received" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_candidate_application_received', 1 ) ); ?> />
                                    <?php esc_html_e( 'Application received confirmation', 'sleeve-ke' ); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="candidate_application_status" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_candidate_application_status', 1 ) ); ?> />
                                    <?php esc_html_e( 'Application status updates', 'sleeve-ke' ); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="candidate_job_alerts" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_candidate_job_alerts', 0 ) ); ?> />
                                    <?php esc_html_e( 'New job alerts (matching preferences)', 'sleeve-ke' ); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Employer Notifications', 'sleeve-ke' ); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="employer_new_application" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_employer_new_application', 1 ) ); ?> />
                                    <?php esc_html_e( 'New application on their jobs', 'sleeve-ke' ); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="employer_job_approved" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_employer_job_approved', 1 ) ); ?> />
                                    <?php esc_html_e( 'Job posting approved', 'sleeve-ke' ); ?>
                                </label><br />
                                <label>
                                    <input type="checkbox" name="employer_account_status" value="1" 
                                           <?php checked( get_option( 'sleeve_ke_employer_account_status', 1 ) ); ?> />
                                    <?php esc_html_e( 'Account status changes', 'sleeve-ke' ); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <?php submit_button( __( 'Save Settings', 'sleeve-ke' ) ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Display email templates page
     */
    private function display_email_templates() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Email Templates', 'sleeve-ke' ); ?></h1>
            
            <!-- Navigation Tabs -->
            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=logs' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Email Logs', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=settings' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Settings', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=templates' ) ); ?>" 
                   class="nav-tab nav-tab-active">
                    <?php esc_html_e( 'Email Templates', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=test' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Test Emails', 'sleeve-ke' ); ?>
                </a>
            </nav>

            <div class="email-templates-grid">
                <?php
                $templates = $this->get_email_templates();
                foreach ( $templates as $template_key => $template ) :
                ?>
                    <div class="template-card">
                        <h3><?php echo esc_html( $template['name'] ); ?></h3>
                        <p><?php echo esc_html( $template['description'] ); ?></p>
                        <div class="template-actions">
                            <a href="#" class="button edit-template" data-template="<?php echo esc_attr( $template_key ); ?>">
                                <?php esc_html_e( 'Edit Template', 'sleeve-ke' ); ?>
                            </a>
                            <a href="#" class="button-secondary preview-template" data-template="<?php echo esc_attr( $template_key ); ?>">
                                <?php esc_html_e( 'Preview', 'sleeve-ke' ); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Display test notifications page
     */
    private function display_test_notifications() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Test Email Notifications', 'sleeve-ke' ); ?></h1>
            
            <!-- Navigation Tabs -->
            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=logs' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Email Logs', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=settings' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Settings', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=templates' ) ); ?>" 
                   class="nav-tab">
                    <?php esc_html_e( 'Email Templates', 'sleeve-ke' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=sleeve-ke-notifications&view=test' ) ); ?>" 
                   class="nav-tab nav-tab-active">
                    <?php esc_html_e( 'Test Emails', 'sleeve-ke' ); ?>
                </a>
            </nav>

            <form method="post" action="">
                <?php wp_nonce_field( 'sleeve_notifications', 'sleeve_nonce' ); ?>
                <input type="hidden" name="action" value="send_test_email" />

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Email Template', 'sleeve-ke' ); ?></th>
                        <td>
                            <select name="template_type" class="regular-text" required>
                                <option value=""><?php esc_html_e( 'Select template to test', 'sleeve-ke' ); ?></option>
                                <?php
                                $templates = $this->get_email_templates();
                                foreach ( $templates as $template_key => $template ) :
                                ?>
                                    <option value="<?php echo esc_attr( $template_key ); ?>">
                                        <?php echo esc_html( $template['name'] ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Test Email Address', 'sleeve-ke' ); ?></th>
                        <td>
                            <input type="email" name="test_email" class="regular-text" 
                                   value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>" required />
                            <p class="description"><?php esc_html_e( 'Email address to send the test notification to.', 'sleeve-ke' ); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button( __( 'Send Test Email', 'sleeve-ke' ) ); ?>
            </form>

            <?php if ( isset( $_POST['action'] ) && $_POST['action'] === 'send_test_email' ) : ?>
                <div class="notice notice-success">
                    <p><?php esc_html_e( 'Test email sent successfully!', 'sleeve-ke' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Get notification types
     */
    public function get_notification_types() {
        return array(
            'application_received' => __( 'Application Received', 'sleeve-ke' ),
            'application_status' => __( 'Application Status Update', 'sleeve-ke' ),
            'job_posted' => __( 'Job Posted', 'sleeve-ke' ),
            'job_approved' => __( 'Job Approved', 'sleeve-ke' ),
            'employer_registered' => __( 'Employer Registration', 'sleeve-ke' ),
            'candidate_registered' => __( 'Candidate Registration', 'sleeve-ke' ),
            'account_status' => __( 'Account Status Update', 'sleeve-ke' ),
            'job_alert' => __( 'Job Alert', 'sleeve-ke' ),
            'admin_notification' => __( 'Admin Notification', 'sleeve-ke' ),
            'test_email' => __( 'Test Email', 'sleeve-ke' )
        );
    }

    /**
     * Get email templates
     */
    public function get_email_templates() {
        return array(
            'application_received_candidate' => array(
                'name' => __( 'Application Received - Candidate', 'sleeve-ke' ),
                'description' => __( 'Sent to candidates when they submit a job application', 'sleeve-ke' ),
                'subject' => __( 'Application Submitted Successfully - {job_title}', 'sleeve-ke' ),
                'variables' => array( 'candidate_name', 'job_title', 'company_name', 'application_date' )
            ),
            'application_received_employer' => array(
                'name' => __( 'New Application - Employer', 'sleeve-ke' ),
                'description' => __( 'Sent to employers when they receive a new application', 'sleeve-ke' ),
                'subject' => __( 'New Application for {job_title}', 'sleeve-ke' ),
                'variables' => array( 'employer_name', 'job_title', 'candidate_name', 'application_date' )
            ),
            'application_status_update' => array(
                'name' => __( 'Application Status Update', 'sleeve-ke' ),
                'description' => __( 'Sent to candidates when application status changes', 'sleeve-ke' ),
                'subject' => __( 'Application Update: {job_title} - {status}', 'sleeve-ke' ),
                'variables' => array( 'candidate_name', 'job_title', 'company_name', 'status', 'status_message' )
            ),
            'job_posted_admin' => array(
                'name' => __( 'Job Posted - Admin', 'sleeve-ke' ),
                'description' => __( 'Sent to administrators when a new job is posted', 'sleeve-ke' ),
                'subject' => __( 'New Job Posted: {job_title}', 'sleeve-ke' ),
                'variables' => array( 'job_title', 'company_name', 'employer_name', 'post_date' )
            ),
            'employer_registered' => array(
                'name' => __( 'Employer Registration', 'sleeve-ke' ),
                'description' => __( 'Welcome email sent to new employers', 'sleeve-ke' ),
                'subject' => __( 'Welcome to Sleeve KE - Employer Account Created', 'sleeve-ke' ),
                'variables' => array( 'employer_name', 'company_name', 'login_url' )
            ),
            'candidate_registered' => array(
                'name' => __( 'Candidate Registration', 'sleeve-ke' ),
                'description' => __( 'Welcome email sent to new candidates', 'sleeve-ke' ),
                'subject' => __( 'Welcome to Sleeve KE - Start Your Job Search', 'sleeve-ke' ),
                'variables' => array( 'candidate_name', 'login_url', 'jobs_url' )
            ),
            'job_alert' => array(
                'name' => __( 'Job Alert', 'sleeve-ke' ),
                'description' => __( 'Sent to candidates about matching job opportunities', 'sleeve-ke' ),
                'subject' => __( 'New Job Matches Your Profile - {job_count} Jobs', 'sleeve-ke' ),
                'variables' => array( 'candidate_name', 'job_count', 'jobs_list', 'unsubscribe_url' )
            )
        );
    }

    /**
     * Get notification logs (mock data)
     */
    public function get_notification_logs() {
        // Apply filters
        $search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
        $type_filter = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';
        $status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';

        // Mock data - in real implementation, this would fetch from database
        $all_logs = array(
            array(
                'id' => 1,
                'type' => 'application_received',
                'recipient' => 'john.doe@email.com',
                'subject' => 'Application Submitted Successfully - Software Developer',
                'preview' => 'Thank you for submitting your application for the Software Developer position...',
                'status' => 'sent',
                'sent_date' => '2025-10-18 09:30:00',
                'error_message' => ''
            ),
            array(
                'id' => 2,
                'type' => 'application_received',
                'recipient' => 'hr@techcorp.co.ke',
                'subject' => 'New Application for Software Developer',
                'preview' => 'You have received a new application for the Software Developer position...',
                'status' => 'sent',
                'sent_date' => '2025-10-18 09:30:15',
                'error_message' => ''
            ),
            array(
                'id' => 3,
                'type' => 'job_posted',
                'recipient' => 'admin@sleeve-ke.com',
                'subject' => 'New Job Posted: Marketing Manager',
                'preview' => 'A new job has been posted by HealthCare Plus...',
                'status' => 'sent',
                'sent_date' => '2025-10-18 08:15:00',
                'error_message' => ''
            ),
            array(
                'id' => 4,
                'type' => 'application_status',
                'recipient' => 'mary.wanjiku@email.com',
                'subject' => 'Application Update: Data Analyst - Under Review',
                'preview' => 'Your application status has been updated to Under Review...',
                'status' => 'failed',
                'sent_date' => '2025-10-17 16:45:00',
                'error_message' => 'SMTP connection failed: Could not connect to server'
            ),
            array(
                'id' => 5,
                'type' => 'candidate_registered',
                'recipient' => 'peter.ochieng@email.com',
                'subject' => 'Welcome to Sleeve KE - Start Your Job Search',
                'preview' => 'Welcome to Sleeve KE! We\'re excited to help you find your next opportunity...',
                'status' => 'sent',
                'sent_date' => '2025-10-17 14:20:00',
                'error_message' => ''
            )
        );

        // Apply filters
        $filtered_logs = $all_logs;

        if ( ! empty( $search ) ) {
            $filtered_logs = array_filter( $filtered_logs, function( $log ) use ( $search ) {
                return stripos( $log['recipient'], $search ) !== false || 
                       stripos( $log['subject'], $search ) !== false;
            });
        }

        if ( ! empty( $type_filter ) ) {
            $filtered_logs = array_filter( $filtered_logs, function( $log ) use ( $type_filter ) {
                return $log['type'] === $type_filter;
            });
        }

        if ( ! empty( $status_filter ) ) {
            $filtered_logs = array_filter( $filtered_logs, function( $log ) use ( $status_filter ) {
                return $log['status'] === $status_filter;
            });
        }

        return $filtered_logs;
    }

    /**
     * Get notification statistics
     */
    public function get_notification_stats() {
        $logs = $this->get_notification_logs();
        
        $stats = array();
        $stats[] = array( 'count' => count( $logs ), 'label' => __( 'Total Emails', 'sleeve-ke' ) );
        
        $sent_count = count( array_filter( $logs, function( $log ) {
            return $log['status'] === 'sent';
        }));
        $stats[] = array( 'count' => $sent_count, 'label' => __( 'Sent Successfully', 'sleeve-ke' ) );
        
        $failed_count = count( array_filter( $logs, function( $log ) {
            return $log['status'] === 'failed';
        }));
        if ( $failed_count > 0 ) {
            $stats[] = array( 'count' => $failed_count, 'label' => __( 'Failed', 'sleeve-ke' ) );
        }
        
        // Calculate success rate
        $success_rate = $sent_count > 0 ? round( ( $sent_count / count( $logs ) ) * 100 ) : 0;
        $stats[] = array( 'count' => $success_rate . '%', 'label' => __( 'Success Rate', 'sleeve-ke' ) );
        
        return $stats;
    }

    /**
     * Handle notification actions
     */
    public function handle_notification_actions() {
        $action = sanitize_text_field( $_POST['action'] );
        
        switch ( $action ) {
            case 'save_settings':
                $this->save_notification_settings();
                break;
            case 'send_test_email':
                $this->send_test_email();
                break;
        }
    }

    /**
     * Save notification settings
     */
    private function save_notification_settings() {
        // Update settings
        update_option( 'sleeve_ke_enable_notifications', isset( $_POST['enable_notifications'] ) ? 1 : 0 );
        update_option( 'sleeve_ke_from_email', sanitize_email( $_POST['from_email'] ) );
        update_option( 'sleeve_ke_from_name', sanitize_text_field( $_POST['from_name'] ) );
        
        // Admin notifications
        update_option( 'sleeve_ke_admin_new_application', isset( $_POST['admin_new_application'] ) ? 1 : 0 );
        update_option( 'sleeve_ke_admin_new_employer', isset( $_POST['admin_new_employer'] ) ? 1 : 0 );
        update_option( 'sleeve_ke_admin_new_candidate', isset( $_POST['admin_new_candidate'] ) ? 1 : 0 );
        update_option( 'sleeve_ke_admin_job_posted', isset( $_POST['admin_job_posted'] ) ? 1 : 0 );
        
        // Candidate notifications
        update_option( 'sleeve_ke_candidate_application_received', isset( $_POST['candidate_application_received'] ) ? 1 : 0 );
        update_option( 'sleeve_ke_candidate_application_status', isset( $_POST['candidate_application_status'] ) ? 1 : 0 );
        update_option( 'sleeve_ke_candidate_job_alerts', isset( $_POST['candidate_job_alerts'] ) ? 1 : 0 );
        
        // Employer notifications
        update_option( 'sleeve_ke_employer_new_application', isset( $_POST['employer_new_application'] ) ? 1 : 0 );
        update_option( 'sleeve_ke_employer_job_approved', isset( $_POST['employer_job_approved'] ) ? 1 : 0 );
        update_option( 'sleeve_ke_employer_account_status', isset( $_POST['employer_account_status'] ) ? 1 : 0 );

        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__( 'Notification settings saved successfully.', 'sleeve-ke' ) . 
                 '</p></div>';
        });
    }

    /**
     * Send test email
     */
    private function send_test_email() {
        $template_type = sanitize_text_field( $_POST['template_type'] );
        $test_email = sanitize_email( $_POST['test_email'] );
        
        if ( empty( $template_type ) || empty( $test_email ) ) {
            return;
        }
        
        // Send test email based on template type
        $result = $this->send_notification( $template_type, $test_email, array(
            'candidate_name' => 'John Doe',
            'job_title' => 'Test Position',
            'company_name' => 'Test Company',
            'employer_name' => 'Test Employer',
            'status' => 'approved',
            'application_date' => date( 'F j, Y' )
        ));
        
        if ( $result ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                     esc_html__( 'Test email sent successfully!', 'sleeve-ke' ) . 
                     '</p></div>';
            });
        } else {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . 
                     esc_html__( 'Failed to send test email.', 'sleeve-ke' ) . 
                     '</p></div>';
            });
        }
    }

    /**
     * Send notification email
     */
    public function send_notification( $type, $recipient, $variables = array() ) {
        // Check if notifications are enabled
        if ( ! get_option( 'sleeve_ke_enable_notifications', 1 ) ) {
            return false;
        }

        $templates = $this->get_email_templates();
        if ( ! isset( $templates[ $type ] ) ) {
            return false;
        }

        $template = $templates[ $type ];
        $subject = $this->replace_variables( $template['subject'], $variables );
        $content = $this->get_email_content( $type, $variables );
        
        // Set from email and name
        $from_email = get_option( 'sleeve_ke_from_email', get_option( 'admin_email' ) );
        $from_name = get_option( 'sleeve_ke_from_name', get_bloginfo( 'name' ) );
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        );

        // Send email
        $result = wp_mail( $recipient, $subject, $content, $headers );
        
        // Log the email (in real implementation, save to database)
        $this->log_email( $type, $recipient, $subject, $content, $result );
        
        return $result;
    }

    /**
     * Replace variables in template
     */
    private function replace_variables( $content, $variables ) {
        foreach ( $variables as $key => $value ) {
            $content = str_replace( '{' . $key . '}', $value, $content );
        }
        return $content;
    }

    /**
     * Get email content for specific type
     */
    private function get_email_content( $type, $variables ) {
        // Basic email template structure
        $header = $this->get_email_header();
        $footer = $this->get_email_footer();
        
        $content = '';
        
        switch ( $type ) {
            case 'application_received_candidate':
                $content = $this->get_application_received_candidate_content( $variables );
                break;
            case 'application_received_employer':
                $content = $this->get_application_received_employer_content( $variables );
                break;
            case 'test_email':
                $content = '<h2>Test Email</h2><p>This is a test email from Sleeve KE notifications system.</p>';
                break;
            default:
                $content = '<h2>Notification</h2><p>This is a notification from Sleeve KE.</p>';
                break;
        }
        
        return $header . $content . $footer;
    }

    /**
     * Get email header
     */
    private function get_email_header() {
        $site_name = get_bloginfo( 'name' );
        $site_url = home_url();
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sleeve KE Notification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0073aa; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .footer { background: #333; color: white; padding: 20px; text-align: center; font-size: 12px; }
                .button { display: inline-block; padding: 12px 24px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                .button:hover { background: #005177; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . esc_html( $site_name ) . '</h1>
                    <p>Your Career Partner in Kenya</p>
                </div>
                <div class="content">
        ';
    }

    /**
     * Get email footer
     */
    private function get_email_footer() {
        $site_name = get_bloginfo( 'name' );
        $site_url = home_url();
        
        return '
                </div>
                <div class="footer">
                    <p>&copy; ' . date( 'Y' ) . ' ' . esc_html( $site_name ) . '. All rights reserved.</p>
                    <p><a href="' . esc_url( $site_url ) . '" style="color: #ccc;">Visit our website</a></p>
                </div>
            </div>
        </body>
        </html>
        ';
    }

    /**
     * Get application received candidate content
     */
    private function get_application_received_candidate_content( $variables ) {
        $candidate_name = $variables['candidate_name'] ?? 'Candidate';
        $job_title = $variables['job_title'] ?? 'Position';
        $company_name = $variables['company_name'] ?? 'Company';
        
        return '
            <h2>Application Submitted Successfully!</h2>
            <p>Dear ' . esc_html( $candidate_name ) . ',</p>
            <p>Thank you for submitting your application for the <strong>' . esc_html( $job_title ) . '</strong> position at <strong>' . esc_html( $company_name ) . '</strong>.</p>
            <p>We have received your application and it is currently being reviewed by the employer. You will be notified of any updates regarding your application status.</p>
            <p>In the meantime, feel free to browse other job opportunities on our platform.</p>
            <a href="' . home_url( '/jobs' ) . '" class="button">Browse More Jobs</a>
            <p>Good luck!</p>
            <p>Best regards,<br>The Sleeve KE Team</p>
        ';
    }

    /**
     * Get application received employer content
     */
    private function get_application_received_employer_content( $variables ) {
        $employer_name = $variables['employer_name'] ?? 'Employer';
        $job_title = $variables['job_title'] ?? 'Position';
        $candidate_name = $variables['candidate_name'] ?? 'Candidate';
        
        return '
            <h2>New Application Received!</h2>
            <p>Dear ' . esc_html( $employer_name ) . ',</p>
            <p>You have received a new application for the <strong>' . esc_html( $job_title ) . '</strong> position.</p>
            <p><strong>Candidate:</strong> ' . esc_html( $candidate_name ) . '</p>
            <p>Please review the application and update the status accordingly.</p>
            <a href="' . admin_url( 'admin.php?page=sleeve-ke-applications' ) . '" class="button">Review Application</a>
            <p>Best regards,<br>The Sleeve KE Team</p>
        ';
    }

    /**
     * Log email (mock implementation)
     */
    private function log_email( $type, $recipient, $subject, $content, $success ) {
        // In real implementation, this would save to database
        // For now, we'll just simulate logging
        error_log( sprintf( 
            'Sleeve KE Email Log: Type=%s, Recipient=%s, Subject=%s, Success=%s', 
            $type, 
            $recipient, 
            $subject, 
            $success ? 'Yes' : 'No' 
        ) );
    }

    /**
     * AJAX handler for viewing email content
     */
    public function ajax_view_email_content() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'view_email_content' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'sleeve-ke' ) ) );
        }
        
        $email_id = intval( $_POST['email_id'] );
        
        // In real implementation, fetch from database
        $email_content = '<h3>Email Content</h3><p>This would show the full email content for email ID: ' . $email_id . '</p>';
        
        wp_send_json_success( array( 'content' => $email_content ) );
    }

    /**
     * AJAX handler for resending email
     */
    public function ajax_resend_email() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'resend_email' ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce', 'sleeve-ke' ) ) );
        }
        
        $email_id = intval( $_POST['email_id'] );
        
        // In real implementation, fetch email details and resend
        // For now, simulate success
        wp_send_json_success( array( 'message' => __( 'Email resent successfully', 'sleeve-ke' ) ) );
    }
}