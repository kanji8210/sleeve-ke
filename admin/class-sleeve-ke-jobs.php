<?php
/**
 * Jobs management functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

// Include required classes
require_once plugin_dir_path(__FILE__) . 'class-sleeve-ke-job-db-handler.php';
require_once plugin_dir_path(__FILE__) . 'class-sleeve-ke-job-form-handler.php';
require_once plugin_dir_path(__FILE__) . 'class-sleeve-ke-job-view-handler.php';

/**
 * Jobs management class.
 *
 * Handles all functionality related to job postings management
 * including display, creation, editing, and deletion.
 * Supports different user roles: admin, sleve_admin, and employer.
 */
class Sleeve_KE_Jobs {
    
    const JOB_STATUSES = array(
        'draft' => 'Draft',
        'published' => 'Published', 
        'archived' => 'Archived',
        'expired' => 'Expired'
    );
    
    const JOB_TYPES = array(
        'full-time' => 'Full-Time',
        'part-time' => 'Part-Time',
        'contract' => 'Contract',
        'temporary' => 'Temporary',
        'internship' => 'Internship', 
        'freelance' => 'Freelance'
    );

    /**
     * The database handler instance.
     */
    private $db_handler;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        $this->db_handler = new Sleeve_KE_Job_DB_Handler();
        
        // Add AJAX handlers
        add_action('wp_ajax_update_job_status', array($this, 'ajax_update_job_status'));
        
        // Handle form submissions on init
        add_action('init', array($this, 'handle_form_submissions'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'sleeve-ke-jobs') === false) {
            return;
        }

        wp_enqueue_style(
            'sleeve-ke-jobs-css',
            plugin_dir_url(__FILE__) . '../assets/css/jobs-admin.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'sleeve-ke-jobs-js',
            plugin_dir_url(__FILE__) . '../assets/js/jobs-admin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script for AJAX
        wp_localize_script('sleeve-ke-jobs-js', 'sleeve_ke_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('update_job_status'),
            'i18n' => array(
                'status_updated' => __('Status updated successfully!', 'sleeve-ke'),
                'error_updating' => __('Error updating status', 'sleeve-ke'),
                'network_error' => __('Network error. Please try again.', 'sleeve-ke'),
                'confirm_delete' => __('Are you sure?', 'sleeve-ke')
            )
        ));
    }

    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        if (isset($_POST['job_form_nonce']) && wp_verify_nonce($_POST['job_form_nonce'], 'sleeve_job_form')) {
            $this->handle_job_actions();
        }
        
        if (isset($_POST['sleeve_nonce']) && wp_verify_nonce($_POST['sleeve_nonce'], 'sleeve_jobs')) {
            $this->handle_bulk_actions();
        }
    }

    /**
     * Display the jobs management page.
     */
    public function display_page() {
        // Display success/error messages
        $this->display_admin_notices();
        
        // Check if we're adding/editing a job
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        switch ($action) {
            case 'add':
                $this->display_add_job_form();
                break;
            case 'edit':
                $this->display_edit_job_form($job_id);
                break;
            case 'view':
                $this->display_job_view($job_id);
                break;
            default:
                $this->display_jobs_list();
                break;
        }
    }

    /**
     * Display admin notices for success/error messages
     * Note: Employers see these as regular page messages, not admin notices
     */
    private function display_admin_notices() {
        $is_employer = $this->is_employer();
        $notice_class = $is_employer ? 'sleeve-ke-message' : 'notice';
        
        // Display success messages
        if (isset($_GET['success'])) {
            $message = '';
            switch ($_GET['success']) {
                case 'job_created':
                    $message = __('Job created successfully!', 'sleeve-ke');
                    break;
                case 'job_updated':
                    $message = __('Job updated successfully!', 'sleeve-ke');
                    break;
                case 'job_deleted':
                    $message = __('Job deleted successfully!', 'sleeve-ke');
                    break;
                case 'bulk_action_completed':
                    $message = __('Bulk action completed successfully!', 'sleeve-ke');
                    break;
            }
            
            if ($message) {
                if ($is_employer) {
                    echo '<div class="sleeve-ke-message success"><p>✓ ' . esc_html($message) . '</p></div>';
                } else {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                }
            }
        }

        // Display error messages
        if (isset($_GET['error'])) {
            $message = '';
            switch ($_GET['error']) {
                case 'invalid_nonce':
                    $message = __('Security verification failed. Please try again.', 'sleeve-ke');
                    break;
                case 'permission_denied':
                    $message = __('You do not have permission to perform this action.', 'sleeve-ke');
                    break;
                case 'job_not_found':
                    $message = __('Job not found.', 'sleeve-ke');
                    break;
                case 'validation_failed':
                    $message = __('Please fill in all required fields.', 'sleeve-ke');
                    break;
            }
            
            if ($message) {
                if ($is_employer) {
                    echo '<div class="sleeve-ke-message error"><p>✗ ' . esc_html($message) . '</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
                }
            }
        }

        // Display form validation errors from transient
        $form_errors = get_transient('sleeve_ke_job_form_errors');
        if ($form_errors && is_array($form_errors)) {
            if ($is_employer) {
                echo '<div class="sleeve-ke-message error">';
                echo '<p><strong>' . __('Please fix the following errors:', 'sleeve-ke') . '</strong></p>';
                echo '<ul style="list-style-type: disc; margin-left: 20px;">';
                foreach ($form_errors as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            } else {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>' . __('Please fix the following errors:', 'sleeve-ke') . '</strong></p>';
                echo '<ul style="list-style-type: disc; margin-left: 20px;">';
                foreach ($form_errors as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            delete_transient('sleeve_ke_job_form_errors');
        }
    }

    /**
     * Display the jobs list page.
     */
    private function display_jobs_list() {
        // Get jobs data
        $jobs = $this->db_handler->get_jobs_with_filters($_GET);
        $statuses = self::JOB_STATUSES;
        $current_user = wp_get_current_user();
        $can_add_jobs = $this->user_can_add_jobs();
        
        // Determine the correct page slug based on user role
        $current_page = $this->is_employer() ? 'sleeve-ke-employer-jobs' : 'sleeve-ke-jobs';
        
        ?>
        <div class="wrap sleeve-ke-jobs-wrap">
            <h1>
                <?php esc_html_e('Job Postings', 'sleeve-ke'); ?>
                <?php if ($can_add_jobs) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $current_page . '&action=add')); ?>" class="page-title-action">
                        <?php esc_html_e('Add New Job', 'sleeve-ke'); ?>
                    </a>
                <?php endif; ?>
            </h1>
            
            <!-- Filter and Search Section -->
            <div class="sleeve-ke-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="sleeve-ke-jobs" />
                    
                    <div class="filter-row">
                        <input type="text" name="search" placeholder="<?php esc_attr_e('Search by job title or company...', 'sleeve-ke'); ?>" 
                               value="<?php echo esc_attr(isset($_GET['search']) ? $_GET['search'] : ''); ?>" />
                        
                        <select name="status">
                            <option value=""><?php esc_html_e('All Statuses', 'sleeve-ke'); ?></option>
                            <?php foreach ($statuses as $status_key => $status_label) : ?>
                                <option value="<?php echo esc_attr($status_key); ?>" 
                                        <?php selected(isset($_GET['status']) ? $_GET['status'] : '', $status_key); ?>>
                                    <?php echo esc_html($status_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="job_type">
                            <option value=""><?php esc_html_e('All Job Types', 'sleeve-ke'); ?></option>
                            <?php foreach (self::JOB_TYPES as $type_key => $type_label) : ?>
                                <option value="<?php echo esc_attr($type_key); ?>" 
                                        <?php selected(isset($_GET['job_type']) ? $_GET['job_type'] : '', $type_key); ?>>
                                    <?php echo esc_html($type_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php submit_button(__('Filter', 'sleeve-ke'), 'secondary', 'filter', false); ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=' . $current_page)); ?>" class="button">
                            <?php esc_html_e('Clear', 'sleeve-ke'); ?>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Jobs Table -->
            <?php if ($this->user_can_manage_all_jobs()) : ?>
            <form method="post" action="">
                <?php wp_nonce_field('sleeve_jobs', 'sleeve_nonce'); ?>
                
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="bulk_action">
                            <option value=""><?php esc_html_e('Bulk Actions', 'sleeve-ke'); ?></option>
                            <option value="publish"><?php esc_html_e('Publish', 'sleeve-ke'); ?></option>
                            <option value="draft"><?php esc_html_e('Move to Draft', 'sleeve-ke'); ?></option>
                            <option value="archive"><?php esc_html_e('Archive', 'sleeve-ke'); ?></option>
                            <option value="delete"><?php esc_html_e('Delete', 'sleeve-ke'); ?></option>
                        </select>
                        <?php submit_button(__('Apply', 'sleeve-ke'), 'action', 'apply_bulk_action', false); ?>
                    </div>
                </div>
            <?php endif; ?>

                <table class="wp-list-table widefat fixed striped sleeve-ke-jobs-table">
                    <thead>
                        <tr>
                            <?php if ($this->user_can_manage_all_jobs()) : ?>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all" />
                            </td>
                            <?php endif; ?>
                            <th class="manage-column"><?php esc_html_e('Job Title', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Employer', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Location', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Type', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Salary', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Status', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Posted', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Actions', 'sleeve-ke'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)) : ?>
                            <tr>
                                <td colspan="<?php echo $this->user_can_manage_all_jobs() ? '9' : '8'; ?>" class="no-items">
                                    <?php esc_html_e('No jobs found.', 'sleeve-ke'); ?>
                                    <?php if ($can_add_jobs) : ?>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=' . $current_page . '&action=add')); ?>">
                                            <?php esc_html_e('Add your first job', 'sleeve-ke'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($jobs as $job) : ?>
                                <tr>
                                    <?php if ($this->user_can_manage_all_jobs()) : ?>
                                    <th class="check-column">
                                        <input type="checkbox" name="job_ids[]" value="<?php echo esc_attr($job['id']); ?>" />
                                    </th>
                                    <?php endif; ?>
                                    <td>
                                        <strong>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=' . $current_page . '&action=view&id=' . $job['id'])); ?>">
                                                <?php echo esc_html($job['title']); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span><a href="<?php echo esc_url(admin_url('admin.php?page=' . $current_page . '&action=view&id=' . $job['id'])); ?>"><?php esc_html_e('View', 'sleeve-ke'); ?></a> | </span>
                                            <?php if ($this->user_can_edit_job($job)) : ?>
                                                <span><a href="<?php echo esc_url(admin_url('admin.php?page=' . $current_page . '&action=edit&id=' . $job['id'])); ?>"><?php esc_html_e('Edit', 'sleeve-ke'); ?></a> | </span>
                                            <?php endif; ?>
                                            <?php if ($this->user_can_delete_job($job)) : ?>
                                                <span><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=' . $current_page . '&action=delete&id=' . $job['id']), 'delete_job_' . $job['id'])); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'sleeve-ke'); ?>')" class="delete"><?php esc_html_e('Delete', 'sleeve-ke'); ?></a></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        // Get employer name from employer_id
                                        $employer = get_userdata($job['employer_id']);
                                        echo esc_html($employer ? $employer->display_name : __('Unknown', 'sleeve-ke'));
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($job['location'] ?? __('Not specified', 'sleeve-ke')); ?></td>
                                    <td><?php echo esc_html(!empty($job['job_type']) ? (self::JOB_TYPES[$job['job_type']] ?? ucfirst($job['job_type'])) : __('Not specified', 'sleeve-ke')); ?></td>
                                    <td>
                                        <?php if (!empty($job['salary_range'])) : ?>
                                            <?php echo esc_html($job['salary_range']); ?>
                                        <?php else : ?>
                                            <span class="no-salary"><?php esc_html_e('Not specified', 'sleeve-ke'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr($job['status']); ?>">
                                            <?php echo esc_html($statuses[$job['status']] ?? ucfirst($job['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html(date('M j, Y', strtotime($job['created_at']))); ?></td>
                                    <td>
                                        <?php if ($this->user_can_edit_job($job)) : ?>
                                            <select class="status-select" data-job-id="<?php echo esc_attr($job['id']); ?>">
                                                <?php foreach ($statuses as $status_key => $status_label) : ?>
                                                    <option value="<?php echo esc_attr($status_key); ?>" 
                                                            <?php selected($job['status'], $status_key); ?>>
                                                        <?php echo esc_html($status_label); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php if ($this->user_can_manage_all_jobs()) : ?>
            </form>
            <?php endif; ?>
            
            <!-- Statistics Section -->
            <div class="sleeve-ke-jobs-stats">
                <h3><?php esc_html_e('Job Statistics', 'sleeve-ke'); ?></h3>
                <div class="stats-grid">
                    <?php
                    $stats = $this->db_handler->get_job_stats();
                    foreach ($stats as $stat) :
                    ?>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo esc_html($stat['count']); ?></div>
                            <div class="stat-label"><?php echo esc_html($stat['label']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Display the add job form
     */
    private function display_add_job_form() {
        $current_page = $this->is_employer() ? 'sleeve-ke-employer-jobs' : 'sleeve-ke-jobs';
        $form_handler = new Sleeve_KE_Job_Form_Handler();
        $form_handler->display_job_form(null, $current_page);
    }

    /**
     * Display the edit job form
     */
    private function display_edit_job_form($job_id) {
        $job = $this->db_handler->get_job_by_id($job_id);
        if (!$job) {
            wp_die(__('Job not found.', 'sleeve-ke'));
        }
        
        if (!$this->user_can_edit_job($job)) {
            wp_die(__('You do not have permission to edit this job.', 'sleeve-ke'));
        }
        
        $current_page = $this->is_employer() ? 'sleeve-ke-employer-jobs' : 'sleeve-ke-jobs';
        $form_handler = new Sleeve_KE_Job_Form_Handler();
        $form_handler->display_job_form($job, $current_page);
    }

    /**
     * Display job view page
     */
    private function display_job_view($job_id) {
        $job = $this->db_handler->get_job_by_id($job_id);
        if (!$job) {
            wp_die(__('Job not found.', 'sleeve-ke'));
        }
        
        if (!$this->user_can_view_job($job)) {
            wp_die(__('You do not have permission to view this job.', 'sleeve-ke'));
        }
        
        $view_handler = new Sleeve_KE_Job_View_Handler();
        $view_handler->display_job_view($job);
    }

    // ========== BUSINESS LOGIC METHODS ==========

    /**
     * Handle job actions
     */
    public function handle_job_actions() {
        if (!isset($_POST['job_action'])) {
            return;
        }

        $action = sanitize_text_field($_POST['job_action']);
        
        switch ($action) {
            case 'create':
                $this->create_job();
                break;
            case 'update':
                $this->update_job();
                break;
        }
    }

    /**
     * Create new job
     */
    private function create_job() {
        error_log('=== JOB CREATION START ===');
        error_log('Current User ID: ' . get_current_user_id());
        error_log('Current User Roles: ' . print_r(wp_get_current_user()->roles, true));
        error_log('Is Employer: ' . ($this->is_employer() ? 'YES' : 'NO'));
        error_log('Can Add Jobs: ' . ($this->user_can_add_jobs() ? 'YES' : 'NO'));
        error_log('Current Page: ' . (isset($_GET['page']) ? $_GET['page'] : 'N/A'));
        
        // Validate required fields
        error_log('Step 1: Validating form data...');
        $validation_errors = $this->validate_job_form_data();
        
        if (!empty($validation_errors)) {
            error_log('Validation FAILED: ' . print_r($validation_errors, true));
            set_transient('sleeve_ke_job_form_errors', $validation_errors, 45);
            set_transient('sleeve_ke_job_form_data', $_POST, 45);
            
            // DO NOT REDIRECT - Stay on form to show errors
            error_log('Staying on form page to display validation errors');
            return;
        }
        error_log('Validation PASSED');
        
        // Check permissions
        error_log('Step 2: Checking permissions...');
        if (!$this->user_can_add_jobs()) {
            error_log('Permission DENIED - Cannot add jobs');
            set_transient('sleeve_ke_job_form_errors', array('Permission denied. You cannot add jobs.'), 45);
            return;
        }
        error_log('Permission GRANTED');
        
        // Prepare job data
        error_log('Step 3: Preparing job data...');
        $job_data = $this->sanitize_job_data($_POST);
        $job_data['employer_id'] = get_current_user_id();
        // created_at and updated_at are auto-generated by MySQL
        error_log('Job Data: ' . print_r($job_data, true));
        
        // Save to database
        error_log('Step 4: Saving to database...');
        $result = $this->db_handler->insert_job($job_data);
        
        if ($result) {
            error_log('Database insert SUCCESSFUL - Job ID: ' . $result);
            // Clear any stored form data
            delete_transient('sleeve_ke_job_form_data');
            delete_transient('sleeve_ke_job_form_errors');
            
            // ONLY REDIRECT ON SUCCESS
            if ($this->is_employer()) {
                $redirect_url = add_query_arg('success', 'job_created', admin_url('admin.php?page=sleeve-ke-employer-jobs'));
                error_log('Redirecting employer to: ' . $redirect_url);
                wp_redirect($redirect_url);
                exit;
            } else {
                $redirect_url = add_query_arg('success', 'job_created', admin_url('admin.php?page=sleeve-ke-jobs'));
                error_log('Redirecting admin to: ' . $redirect_url);
                wp_redirect($redirect_url);
                exit;
            }
        } else {
            error_log('Database insert FAILED');
            global $wpdb;
            $db_error = $wpdb->last_error ? $wpdb->last_error : 'Unknown database error';
            error_log('Database Error: ' . $db_error);
            
            // Store error and stay on form
            set_transient('sleeve_ke_job_form_errors', array('Failed to save job: ' . $db_error), 45);
            set_transient('sleeve_ke_job_form_data', $_POST, 45);
            error_log('Staying on form page to display database error');
            return;
        }
    }

    /**
     * Update existing job
     */
    private function update_job() {
        $job_id = intval($_POST['job_id']);
        $job = $this->db_handler->get_job_by_id($job_id);
        
        $redirect_page = $this->is_employer() ? 'sleeve-ke-employer-jobs' : 'sleeve-ke-jobs';
        
        if (!$job) {
            wp_redirect(add_query_arg('error', 'job_not_found', admin_url('admin.php?page=' . $redirect_page)));
            exit;
        }
        
        // Check permissions
        if (!$this->user_can_edit_job($job)) {
            wp_redirect(add_query_arg('error', 'permission_denied', admin_url('admin.php?page=' . $redirect_page)));
            exit;
        }
        
        // Validate required fields
        $validation_errors = $this->validate_job_form_data();
        
        if (!empty($validation_errors)) {
            set_transient('sleeve_ke_job_form_errors', $validation_errors, 45);
            set_transient('sleeve_ke_job_form_data', $_POST, 45);
            wp_redirect(add_query_arg('error', 'validation_failed', admin_url('admin.php?page=' . $redirect_page . '&action=edit&id=' . $job_id)));
            exit;
        }
        
        // Prepare updated job data
        $job_data = $this->sanitize_job_data($_POST);
        
        // Update in database
        $result = $this->db_handler->update_job($job_id, $job_data);
        
        if ($result) {
            // Clear any stored form data
            delete_transient('sleeve_ke_job_form_data');
            wp_redirect(add_query_arg('success', 'job_updated', admin_url('admin.php?page=' . $redirect_page)));
        } else {
            wp_redirect(add_query_arg('error', 'update_failed', admin_url('admin.php?page=' . $redirect_page . '&action=edit&id=' . $job_id)));
        }
        exit;
    }

    /**
     * Sanitize job form data
     */
    private function sanitize_job_data($data) {
        // Build salary_range from min/max if provided
        $salary_range = '';
        if (!empty($data['salary_min']) && !empty($data['salary_max'])) {
            $currency = !empty($data['currency']) ? sanitize_text_field($data['currency']) : 'KES';
            $salary_range = number_format(intval($data['salary_min'])) . ' - ' . number_format(intval($data['salary_max'])) . ' ' . $currency;
        } elseif (!empty($data['salary_min'])) {
            $currency = !empty($data['currency']) ? sanitize_text_field($data['currency']) : 'KES';
            $salary_range = number_format(intval($data['salary_min'])) . '+ ' . $currency;
        }
        
        // Only include columns that exist in wp_sleeve_jobs table
        return array(
            'title' => sanitize_text_field($data['job_title']),
            'description' => wp_kses_post($data['job_description']),
            'requirements' => !empty($data['job_requirements']) ? wp_kses_post($data['job_requirements']) : null,
            'salary_range' => !empty($salary_range) ? $salary_range : null,
            'location' => !empty($data['location']) ? sanitize_text_field($data['location']) : null,
            'job_type' => !empty($data['job_type']) ? sanitize_key($data['job_type']) : null,
            'status' => sanitize_key($data['job_status']),
            'expires_at' => !empty($data['expires_at']) ? sanitize_text_field($data['expires_at']) : date('Y-m-d H:i:s', strtotime('+30 days'))
        );
    }

    /**
     * Validate job form data
     */
    private function validate_job_form_data() {
        $errors = array();
        
        // Only validate fields that exist in the database
        $required_fields = array(
            'job_title' => __('Job Title', 'sleeve-ke'),
            'job_description' => __('Job Description', 'sleeve-ke'),
            'job_status' => __('Status', 'sleeve-ke')
        );
        
        foreach ($required_fields as $field => $label) {
            if (empty($_POST[$field])) {
                $errors[] = sprintf(__('%s is required.', 'sleeve-ke'), $label);
            }
        }
        
        // Validate salary data
        $salary_errors = $this->validate_salary_data(
            $_POST['salary_min'] ?? '',
            $_POST['salary_max'] ?? '',
            $_POST['currency'] ?? ''
        );
        $errors = array_merge($errors, $salary_errors);
        
        // Validate expiration date
        if (!empty($_POST['expires_at'])) {
            $expires = strtotime($_POST['expires_at']);
            $today = strtotime(date('Y-m-d'));
            
            if ($expires < $today) {
                $errors[] = __('Expiration date cannot be in the past.', 'sleeve-ke');
            }
        }
        
        return $errors;
    }

    /**
     * Validate salary data
     */
    private function validate_salary_data($min, $max, $currency) {
        $errors = array();
        
        if (!empty($min) && !is_numeric($min)) {
            $errors[] = __('Minimum salary must be a valid number.', 'sleeve-ke');
        }
        
        if (!empty($max) && !is_numeric($max)) {
            $errors[] = __('Maximum salary must be a valid number.', 'sleeve-ke');
        }
        
        if (!empty($min) && !empty($max) && $min > $max) {
            $errors[] = __('Minimum salary cannot exceed maximum salary.', 'sleeve-ke');
        }
        
        $valid_currencies = array('KES', 'USD', 'EUR', 'GBP', 'TZS', 'UGX');
        if (!empty($currency) && !in_array($currency, $valid_currencies)) {
            $errors[] = __('Invalid currency selected.', 'sleeve-ke');
        }
        
        return $errors;
    }

    /**
     * Handle bulk actions
     */
    private function handle_bulk_actions() {
        if (!isset($_POST['bulk_action']) || empty($_POST['bulk_action'])) {
            return;
        }
        
        $redirect_page = $this->is_employer() ? 'sleeve-ke-employer-jobs' : 'sleeve-ke-jobs';
        
        if (!isset($_POST['job_ids']) || empty($_POST['job_ids'])) {
            wp_redirect(add_query_arg('error', 'no_jobs_selected', admin_url('admin.php?page=' . $redirect_page)));
            exit;
        }
        
        $bulk_action = sanitize_text_field($_POST['bulk_action']);
        $job_ids = array_map('intval', $_POST['job_ids']);
        $processed = 0;
        
        foreach ($job_ids as $job_id) {
            $job = $this->db_handler->get_job_by_id($job_id);
            
            if (!$job || !$this->user_can_edit_job($job)) {
                continue;
            }
            
            switch ($bulk_action) {
                case 'publish':
                    if ($this->db_handler->update_job_status($job_id, 'published')) {
                        $processed++;
                    }
                    break;
                case 'draft':
                    if ($this->db_handler->update_job_status($job_id, 'draft')) {
                        $processed++;
                    }
                    break;
                case 'archive':
                    if ($this->db_handler->update_job_status($job_id, 'archived')) {
                        $processed++;
                    }
                    break;
                case 'delete':
                    if ($this->db_handler->delete_job($job_id)) {
                        $processed++;
                    }
                    break;
            }
        }
        
        if ($processed > 0) {
            wp_redirect(add_query_arg('success', 'bulk_action_completed', admin_url('admin.php?page=' . $redirect_page)));
        } else {
            wp_redirect(add_query_arg('error', 'no_actions_processed', admin_url('admin.php?page=' . $redirect_page)));
        }
        exit;
    }

    /**
     * Handle AJAX request to update job status
     */
    public function ajax_update_job_status() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'update_job_status')) {
            wp_send_json_error(array('message' => __('Security verification failed.', 'sleeve-ke')));
        }
        
        $job_id = intval($_POST['job_id']);
        $status = sanitize_text_field($_POST['status']);
        $job = $this->db_handler->get_job_by_id($job_id);
        
        // Check permissions
        if (!$job || !$this->user_can_edit_job($job)) {
            wp_send_json_error(array('message' => __('You do not have permission to edit this job.', 'sleeve-ke')));
        }
        
        // Validate status
        $valid_statuses = array_keys(self::JOB_STATUSES);
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(array('message' => __('Invalid status.', 'sleeve-ke')));
        }
        
        // Update status in database
        $result = $this->db_handler->update_job_status($job_id, $status);
        
        if ($result) {
            $new_status_label = self::JOB_STATUSES[$status];
            wp_send_json_success(array( 
                'message' => __('Job status updated successfully.', 'sleeve-ke'),
                'job_id' => $job_id,
                'new_status' => $status,
                'new_status_label' => $new_status_label
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to update job status.', 'sleeve-ke')));
        }
    }

    /**
     * Get sectors
     */
    public function get_sectors() {
        return array(
            'technology' => __('Technology & IT', 'sleeve-ke'),
            'healthcare' => __('Healthcare & Medical', 'sleeve-ke'),
            'finance' => __('Finance & Banking', 'sleeve-ke'),
            'education' => __('Education & Training', 'sleeve-ke'),
            'manufacturing' => __('Manufacturing & Production', 'sleeve-ke'),
            'retail' => __('Retail & Sales', 'sleeve-ke'),
            'hospitality' => __('Hospitality & Tourism', 'sleeve-ke'),
            'agriculture' => __('Agriculture & Farming', 'sleeve-ke'),
            'construction' => __('Construction & Real Estate', 'sleeve-ke'),
            'telecommunications' => __('Telecommunications & Media', 'sleeve-ke'),
            'legal' => __('Legal & Professional Services', 'sleeve-ke'),
            'marketing' => __('Marketing & Advertising', 'sleeve-ke'),
            'business' => __('Business & Consulting', 'sleeve-ke'),
            'nonprofit' => __('Non-Profit & NGO', 'sleeve-ke'),
            'government' => __('Government & Public Sector', 'sleeve-ke'),
            'transport' => __('Transportation & Logistics', 'sleeve-ke'),
            'energy' => __('Energy & Environment', 'sleeve-ke'),
            'arts' => __('Arts & Creative', 'sleeve-ke'),
            'sports' => __('Sports & Recreation', 'sleeve-ke'),
            'other' => __('Other', 'sleeve-ke')
        );
    }

    /**
     * Check if current user can add jobs
     */
    public function user_can_add_jobs() {
        return current_user_can('manage_options') || 
               current_user_can('manage_jobs') || 
               in_array('employer', wp_get_current_user()->roles) ||
               in_array('sleve_admin', wp_get_current_user()->roles);
    }

    /**
     * Check if current user can manage all jobs
     */
    public function user_can_manage_all_jobs() {
        return current_user_can('manage_options') || 
               in_array('sleve_admin', wp_get_current_user()->roles);
    }

    /**
     * Check if current user can edit specific job
     */
    public function user_can_edit_job($job) {
        // Admins and sleve_admins can edit all jobs
        if ($this->user_can_manage_all_jobs()) {
            return true;
        }
        
        // Employers can only edit their own jobs
        if ($this->is_employer()) {
            return $job['employer_id'] === get_current_user_id();
        }
        
        return false;
    }

    /**
     * Check if current user can delete specific job
     */
    public function user_can_delete_job($job) {
        return $this->user_can_edit_job($job);
    }

    /**
     * Check if current user can view specific job
     */
    public function user_can_view_job($job) {
        // Admins and sleve_admins can view all jobs
        if ($this->user_can_manage_all_jobs()) {
            return true;
        }
        
        // Employers can only view their own jobs
        if ($this->is_employer()) {
            return $job['employer_id'] === get_current_user_id();
        }
        
        return false;
    }

    /**
     * Check if current user is an employer
     */
    private function is_employer() {
        return in_array('employer', wp_get_current_user()->roles);
    }
}