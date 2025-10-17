<?php
/**
 * Example frontend job application form that triggers email notifications
 * 
 * This file demonstrates how to integrate the notification system
 * with frontend forms and user interactions.
 * 
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/examples
 */

// This would typically be integrated into a theme template or shortcode

/**
 * Handle job application form submission
 */
function sleeve_ke_handle_job_application() {
    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['sleeve_application_nonce'], 'submit_job_application' ) ) {
        wp_die( __( 'Security check failed', 'sleeve-ke' ) );
    }
    
    // Sanitize form data
    $job_id = intval( $_POST['job_id'] );
    $candidate_name = sanitize_text_field( $_POST['candidate_name'] );
    $candidate_email = sanitize_email( $_POST['candidate_email'] );
    $cover_letter = sanitize_textarea_field( $_POST['cover_letter'] );
    
    // Validate required fields
    if ( empty( $job_id ) || empty( $candidate_name ) || empty( $candidate_email ) ) {
        wp_die( __( 'Please fill in all required fields', 'sleeve-ke' ) );
    }
    
    // Here you would normally save to database
    // For demonstration, we'll simulate with mock data
    
    // Mock application ID (in real implementation, this would be the database insert ID)
    $application_id = rand( 1000, 9999 );
    
    // Mock job and employer data (in real implementation, fetch from database)
    $job_data = array(
        'title' => 'Senior Software Developer',
        'company_name' => 'TechCorp Solutions Ltd',
        'employer_email' => 'hr@techcorp.co.ke'
    );
    
    // Send application received notifications
    $notification_variables = array(
        'candidate_name' => $candidate_name,
        'job_title' => $job_data['title'],
        'company_name' => $job_data['company_name'],
        'application_date' => date( 'F j, Y' ),
        'employer_name' => $job_data['company_name']
    );
    
    // Send confirmation email to candidate
    $candidate_notification_sent = sleeve_ke_send_notification( 
        'application_received_candidate', 
        $candidate_email, 
        $notification_variables 
    );
    
    // Send notification to employer
    $employer_notification_sent = sleeve_ke_send_notification( 
        'application_received_employer', 
        $job_data['employer_email'], 
        $notification_variables 
    );
    
    // Send admin notification if enabled
    if ( get_option( 'sleeve_ke_admin_new_application', 1 ) ) {
        sleeve_ke_send_notification( 
            'admin_notification', 
            get_option( 'admin_email' ), 
            array_merge( $notification_variables, array( 
                'message' => 'New job application submitted for ' . $job_data['title'] 
            ) ) 
        );
    }
    
    // Trigger the hook for other integrations
    sleeve_ke_trigger_new_application( $application_id );
    
    // Redirect with success message
    $redirect_url = add_query_arg( 
        array( 
            'application_sent' => 1,
            'job_id' => $job_id 
        ), 
        wp_get_referer() 
    );
    
    wp_redirect( $redirect_url );
    exit;
}

// Hook the form handler
add_action( 'wp', function() {
    if ( isset( $_POST['action'] ) && $_POST['action'] === 'submit_job_application' ) {
        sleeve_ke_handle_job_application();
    }
});

/**
 * Display job application form with notification integration
 */
function sleeve_ke_job_application_form( $job_id = 1 ) {
    ob_start();
    ?>
    <div class="sleeve-ke-application-form">
        <?php if ( isset( $_GET['application_sent'] ) && $_GET['application_sent'] == 1 ) : ?>
            <div class="success-message">
                <h3><?php esc_html_e( 'Application Submitted Successfully!', 'sleeve-ke' ); ?></h3>
                <p><?php esc_html_e( 'Thank you for your application. You will receive a confirmation email shortly, and the employer has been notified.', 'sleeve-ke' ); ?></p>
            </div>
        <?php else : ?>
            <h3><?php esc_html_e( 'Apply for this Position', 'sleeve-ke' ); ?></h3>
            
            <form method="post" action="" class="job-application-form">
                <?php wp_nonce_field( 'submit_job_application', 'sleeve_application_nonce' ); ?>
                <input type="hidden" name="action" value="submit_job_application" />
                <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
                
                <div class="form-group">
                    <label for="candidate_name"><?php esc_html_e( 'Full Name', 'sleeve-ke' ); ?> *</label>
                    <input type="text" id="candidate_name" name="candidate_name" required 
                           class="form-control" placeholder="<?php esc_attr_e( 'Enter your full name', 'sleeve-ke' ); ?>" />
                </div>
                
                <div class="form-group">
                    <label for="candidate_email"><?php esc_html_e( 'Email Address', 'sleeve-ke' ); ?> *</label>
                    <input type="email" id="candidate_email" name="candidate_email" required 
                           class="form-control" placeholder="<?php esc_attr_e( 'Enter your email address', 'sleeve-ke' ); ?>" />
                </div>
                
                <div class="form-group">
                    <label for="cover_letter"><?php esc_html_e( 'Cover Letter', 'sleeve-ke' ); ?></label>
                    <textarea id="cover_letter" name="cover_letter" rows="6" 
                              class="form-control" placeholder="<?php esc_attr_e( 'Tell us why you are interested in this position...', 'sleeve-ke' ); ?>"></textarea>
                </div>
                
                <div class="notification-info">
                    <p><small>
                        <strong><?php esc_html_e( 'Email Notifications:', 'sleeve-ke' ); ?></strong>
                        <?php esc_html_e( 'You will receive email confirmations and updates about your application status. The employer will also be notified of your application.', 'sleeve-ke' ); ?>
                    </small></p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php esc_html_e( 'Submit Application', 'sleeve-ke' ); ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <style>
    .sleeve-ke-application-form {
        max-width: 600px;
        margin: 20px 0;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: #f9f9f9;
    }
    
    .success-message {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        color: #155724;
    }
    
    .success-message h3 {
        margin-top: 0;
        color: #155724;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .form-control:focus {
        border-color: #0073aa;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 115, 170, 0.3);
    }
    
    .notification-info {
        background: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 4px;
        padding: 10px;
        margin: 15px 0;
    }
    
    .notification-info p {
        margin: 0;
        color: #0066cc;
    }
    
    .btn {
        display: inline-block;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .btn-primary {
        background-color: #0073aa;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #005177;
    }
    
    .form-actions {
        text-align: center;
        margin-top: 20px;
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Shortcode to display the job application form
 * Usage: [sleeve_ke_job_application job_id="123"]
 */
function sleeve_ke_job_application_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'job_id' => 1
    ), $atts );
    
    return sleeve_ke_job_application_form( intval( $atts['job_id'] ) );
}
add_shortcode( 'sleeve_ke_job_application', 'sleeve_ke_job_application_shortcode' );

/**
 * Example of triggered notifications for different events
 */

// Example: New employer registration
function sleeve_ke_example_employer_registration( $employer_id ) {
    // Mock employer data
    $employer_data = array(
        'name' => 'John Doe',
        'company' => 'New Tech Company',
        'email' => 'john@newtechcompany.com'
    );
    
    // Send welcome email to employer
    sleeve_ke_send_notification( 
        'employer_registered', 
        $employer_data['email'], 
        array(
            'employer_name' => $employer_data['name'],
            'company_name' => $employer_data['company'],
            'login_url' => wp_login_url()
        )
    );
    
    // Notify admin of new employer registration
    if ( get_option( 'sleeve_ke_admin_new_employer', 1 ) ) {
        sleeve_ke_send_notification( 
            'admin_notification', 
            get_option( 'admin_email' ), 
            array(
                'message' => 'New employer registered: ' . $employer_data['company'],
                'employer_name' => $employer_data['name'],
                'company_name' => $employer_data['company']
            )
        );
    }
}

// Example: New candidate registration
function sleeve_ke_example_candidate_registration( $candidate_id ) {
    // Mock candidate data
    $candidate_data = array(
        'name' => 'Jane Smith',
        'email' => 'jane@email.com'
    );
    
    // Send welcome email to candidate
    sleeve_ke_send_notification( 
        'candidate_registered', 
        $candidate_data['email'], 
        array(
            'candidate_name' => $candidate_data['name'],
            'login_url' => wp_login_url(),
            'jobs_url' => home_url( '/jobs' )
        )
    );
    
    // Notify admin of new candidate registration
    if ( get_option( 'sleeve_ke_admin_new_candidate', 1 ) ) {
        sleeve_ke_send_notification( 
            'admin_notification', 
            get_option( 'admin_email' ), 
            array(
                'message' => 'New candidate registered: ' . $candidate_data['name'],
                'candidate_name' => $candidate_data['name']
            )
        );
    }
}

// Example: Job posted notification
function sleeve_ke_example_job_posted( $job_id ) {
    // Mock job data
    $job_data = array(
        'title' => 'Software Engineer',
        'company' => 'Tech Solutions Inc',
        'employer_name' => 'HR Manager',
        'employer_email' => 'hr@techsolutions.com'
    );
    
    // Notify admin of new job posting
    if ( get_option( 'sleeve_ke_admin_job_posted', 1 ) ) {
        sleeve_ke_send_notification( 
            'job_posted_admin', 
            get_option( 'admin_email' ), 
            array(
                'job_title' => $job_data['title'],
                'company_name' => $job_data['company'],
                'employer_name' => $job_data['employer_name'],
                'post_date' => date( 'F j, Y' )
            )
        );
    }
    
    // Send confirmation to employer
    sleeve_ke_send_notification( 
        'job_posted_employer', 
        $job_data['employer_email'], 
        array(
            'job_title' => $job_data['title'],
            'employer_name' => $job_data['employer_name'],
            'post_date' => date( 'F j, Y' )
        )
    );
}

/**
 * Example usage in WordPress
 * 
 * To trigger these notifications programmatically:
 * 
 * // New application
 * sleeve_ke_trigger_new_application( 123 );
 * 
 * // New employer
 * sleeve_ke_trigger_new_employer( 456 );
 * 
 * // New candidate
 * sleeve_ke_trigger_new_candidate( 789 );
 * 
 * // Job posted
 * sleeve_ke_trigger_job_posted( 321 );
 * 
 * // Or send notifications directly
 * sleeve_ke_send_notification( 'application_received_candidate', 'candidate@email.com', array(
 *     'candidate_name' => 'John Doe',
 *     'job_title' => 'Software Developer',
 *     'company_name' => 'Tech Corp'
 * ) );
 */