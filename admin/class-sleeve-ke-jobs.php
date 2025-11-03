<?php
/**
 * Jobs management functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

// Include required classes
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

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
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
     */
    private function display_admin_notices() {
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
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
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
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($message) . '</p></div>';
            }
        }

        // Display form validation errors from transient
        $form_errors = get_transient('sleeve_ke_job_form_errors');
        if ($form_errors && is_array($form_errors)) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p><strong>' . __('Please fix the following errors:', 'sleeve-ke') . '</strong></p>';
            echo '<ul style="list-style-type: disc; margin-left: 20px;">';
            foreach ($form_errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
            delete_transient('sleeve_ke_job_form_errors');
        }
    }

    /**
     * Display the jobs list page.
     */
    private function display_jobs_list() {
        // Get jobs data
        $jobs = $this->get_jobs_data();
        $statuses = $this->get_status_options();
        $current_user = wp_get_current_user();
        $can_add_jobs = $this->user_can_add_jobs();
        
        ?>
        <div class="wrap sleeve-ke-jobs-wrap">
            <h1>
                <?php esc_html_e('Job Postings', 'sleeve-ke'); ?>
                <?php if ($can_add_jobs) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs&action=add')); ?>" class="page-title-action">
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
                            <?php
                            $job_types = $this->get_job_types();
                            foreach ($job_types as $type_key => $type_label) :
                            ?>
                                <option value="<?php echo esc_attr($type_key); ?>" 
                                        <?php selected(isset($_GET['job_type']) ? $_GET['job_type'] : '', $type_key); ?>>
                                    <?php echo esc_html($type_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select name="sector">
                            <option value=""><?php esc_html_e('All Sectors', 'sleeve-ke'); ?></option>
                            <?php
                            $sectors = $this->get_sectors();
                            foreach ($sectors as $sector_key => $sector_label) :
                            ?>
                                <option value="<?php echo esc_attr($sector_key); ?>" 
                                        <?php selected(isset($_GET['sector']) ? $_GET['sector'] : '', $sector_key); ?>>
                                    <?php echo esc_html($sector_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php submit_button(__('Filter', 'sleeve-ke'), 'secondary', 'filter', false); ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs')); ?>" class="button">
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
                            <th class="manage-column"><?php esc_html_e('Company', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Sector', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Location', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Type', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Salary', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Status', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Applications', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Posted', 'sleeve-ke'); ?></th>
                            <th class="manage-column"><?php esc_html_e('Actions', 'sleeve-ke'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)) : ?>
                            <tr>
                                <td colspan="<?php echo $this->user_can_manage_all_jobs() ? '11' : '10'; ?>" class="no-items">
                                    <?php esc_html_e('No jobs found.', 'sleeve-ke'); ?>
                                    <?php if ($can_add_jobs) : ?>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs&action=add')); ?>">
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
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs&action=view&id=' . $job['id'])); ?>">
                                                <?php echo esc_html($job['title']); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span><a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs&action=view&id=' . $job['id'])); ?>"><?php esc_html_e('View', 'sleeve-ke'); ?></a> | </span>
                                            <?php if ($this->user_can_edit_job($job)) : ?>
                                                <span><a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs&action=edit&id=' . $job['id'])); ?>"><?php esc_html_e('Edit', 'sleeve-ke'); ?></a> | </span>
                                            <?php endif; ?>
                                            <?php if ($this->user_can_delete_job($job)) : ?>
                                                <span><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=sleeve-ke-jobs&action=delete&id=' . $job['id']), 'delete_job_' . $job['id'])); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'sleeve-ke'); ?>')" class="delete"><?php esc_html_e('Delete', 'sleeve-ke'); ?></a></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo esc_html($job['company']); ?>
                                        <div class="employer-type-indicator">
                                            <?php echo esc_html($job['employer_type'] === 'individual' ? __('(Individual)', 'sleeve-ke') : __('(Org)', 'sleeve-ke')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $sectors = $this->get_sectors();
                                        $sector_display = isset($sectors[$job['sector']]) ? $sectors[$job['sector']] : ucfirst($job['sector']);
                                        echo esc_html($sector_display);
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($job['location']); ?></td>
                                    <td><?php echo esc_html($job['job_type']); ?></td>
                                    <td>
                                        <?php if (!empty($job['salary_min']) && !empty($job['salary_max'])) : ?>
                                            <?php echo esc_html(number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']) . ' ' . $job['currency']); ?>
                                        <?php elseif (!empty($job['salary_min'])) : ?>
                                            <?php echo esc_html(number_format($job['salary_min']) . '+ ' . $job['currency']); ?>
                                        <?php else : ?>
                                            <span class="no-salary"><?php esc_html_e('Not specified', 'sleeve-ke'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr($job['status']); ?>">
                                            <?php echo esc_html($statuses[$job['status']]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-applications&job_id=' . $job['id'])); ?>">
                                            <?php echo esc_html($job['applications_count']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html($job['posted_date']); ?></td>
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
                    $stats = $this->get_job_stats();
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
        $form_handler = new Sleeve_KE_Job_Form_Handler();
        $form_handler->display_job_form();
    }

    /**
     * Display the edit job form
     */
    private function display_edit_job_form($job_id) {
        $job = $this->get_job_by_id($job_id);
        if (!$job) {
            wp_die(__('Job not found.', 'sleeve-ke'));
        }
        
        if (!$this->user_can_edit_job($job)) {
            wp_die(__('You do not have permission to edit this job.', 'sleeve-ke'));
        }
        
        $form_handler = new Sleeve_KE_Job_Form_Handler();
        $form_handler->display_job_form($job);
    }

    /**
     * Display job view page
     */
    private function display_job_view($job_id) {
        $job = $this->get_job_by_id($job_id);
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
        // Validate required fields
        $validation_errors = $this->validate_job_form_data();
        
        if (!empty($validation_errors)) {
            set_transient('sleeve_ke_job_form_errors', $validation_errors, 45);
            set_transient('sleeve_ke_job_form_data', $_POST, 45);
            wp_redirect(add_query_arg('error', 'validation_failed', admin_url('admin.php?page=sleeve-ke-jobs&action=add')));
            exit;
        }
        
        // Check permissions
        if (!$this->user_can_add_jobs()) {
            wp_redirect(add_query_arg('error', 'permission_denied', admin_url('admin.php?page=sleeve-ke-jobs')));
            exit;
        }
        
        // Prepare job data
        $job_data = array(
            'title' => sanitize_text_field($_POST['job_title']),
            'description' => wp_kses_post($_POST['job_description']),
            'requirements' => wp_kses_post($_POST['job_requirements']),
            'benefits' => wp_kses_post($_POST['job_benefits']),
            'company' => sanitize_text_field($_POST['company']),
            'sector' => sanitize_text_field($_POST['sector']),
            'location' => sanitize_text_field($_POST['location']),
            'job_type' => sanitize_text_field($_POST['job_type']),
            'experience_level' => sanitize_text_field($_POST['experience_level']),
            'remote_work' => sanitize_text_field($_POST['remote_work']),
            'salary_min' => !empty($_POST['salary_min']) ? intval($_POST['salary_min']) : null,
            'salary_max' => !empty($_POST['salary_max']) ? intval($_POST['salary_max']) : null,
            'currency' => sanitize_text_field($_POST['currency']),
            'status' => sanitize_text_field($_POST['job_status']),
            'expires_at' => !empty($_POST['expires_at']) ? sanitize_text_field($_POST['expires_at']) : date('Y-m-d', strtotime('+30 days')),
            'posted_date' => date('Y-m-d'),
            'employer_id' => get_current_user_id(),
            'employer_type' => $this->is_employer() ? 'individual' : 'organization',
            'applications_count' => 0
        );
        
        // In a real implementation, you would save to database here
        // For now, we'll simulate success
        
        // Clear any stored form data
        delete_transient('sleeve_ke_job_form_data');
        
        wp_redirect(add_query_arg('success', 'job_created', admin_url('admin.php?page=sleeve-ke-jobs')));
        exit;
    }

    /**
     * Update existing job
     */
    private function update_job() {
        $job_id = intval($_POST['job_id']);
        $job = $this->get_job_by_id($job_id);
        
        if (!$job) {
            wp_redirect(add_query_arg('error', 'job_not_found', admin_url('admin.php?page=sleeve-ke-jobs')));
            exit;
        }
        
        // Check permissions
        if (!$this->user_can_edit_job($job)) {
            wp_redirect(add_query_arg('error', 'permission_denied', admin_url('admin.php?page=sleeve-ke-jobs')));
            exit;
        }
        
        // Validate required fields
        $validation_errors = $this->validate_job_form_data();
        
        if (!empty($validation_errors)) {
            set_transient('sleeve_ke_job_form_errors', $validation_errors, 45);
            set_transient('sleeve_ke_job_form_data', $_POST, 45);
            wp_redirect(add_query_arg('error', 'validation_failed', admin_url('admin.php?page=sleeve-ke-jobs&action=edit&id=' . $job_id)));
            exit;
        }
        
        // Prepare updated job data
        $job_data = array(
            'title' => sanitize_text_field($_POST['job_title']),
            'description' => wp_kses_post($_POST['job_description']),
            'requirements' => wp_kses_post($_POST['job_requirements']),
            'benefits' => wp_kses_post($_POST['job_benefits']),
            'company' => sanitize_text_field($_POST['company']),
            'sector' => sanitize_text_field($_POST['sector']),
            'location' => sanitize_text_field($_POST['location']),
            'job_type' => sanitize_text_field($_POST['job_type']),
            'experience_level' => sanitize_text_field($_POST['experience_level']),
            'remote_work' => sanitize_text_field($_POST['remote_work']),
            'salary_min' => !empty($_POST['salary_min']) ? intval($_POST['salary_min']) : null,
            'salary_max' => !empty($_POST['salary_max']) ? intval($_POST['salary_max']) : null,
            'currency' => sanitize_text_field($_POST['currency']),
            'status' => sanitize_text_field($_POST['job_status']),
            'expires_at' => !empty($_POST['expires_at']) ? sanitize_text_field($_POST['expires_at']) : $job['expires_at']
        );
        
        // In a real implementation, you would update in database here
        // For now, we'll simulate success
        
        // Clear any stored form data
        delete_transient('sleeve_ke_job_form_data');
        
        wp_redirect(add_query_arg('success', 'job_updated', admin_url('admin.php?page=sleeve-ke-jobs')));
        exit;
    }

    /**
     * Validate job form data
     */
    private function validate_job_form_data() {
        $errors = array();
        
        $required_fields = array(
            'job_title' => __('Job Title', 'sleeve-ke'),
            'job_description' => __('Job Description', 'sleeve-ke'),
            'job_requirements' => __('Requirements', 'sleeve-ke'),
            'company' => __('Company', 'sleeve-ke'),
            'sector' => __('Sector', 'sleeve-ke'),
            'location' => __('Location', 'sleeve-ke'),
            'job_type' => __('Job Type', 'sleeve-ke'),
            'experience_level' => __('Experience Level', 'sleeve-ke'),
            'job_status' => __('Status', 'sleeve-ke')
        );
        
        foreach ($required_fields as $field => $label) {
            if (empty($_POST[$field])) {
                $errors[] = sprintf(__('%s is required.', 'sleeve-ke'), $label);
            }
        }
        
        // Validate salary range if provided
        if (!empty($_POST['salary_min']) && !empty($_POST['salary_max'])) {
            $min = intval($_POST['salary_min']);
            $max = intval($_POST['salary_max']);
            
            if ($min > $max) {
                $errors[] = __('Minimum salary cannot be greater than maximum salary.', 'sleeve-ke');
            }
            
            if ($min < 0 || $max < 0) {
                $errors[] = __('Salary values cannot be negative.', 'sleeve-ke');
            }
        }
        
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
     * Handle bulk actions
     */
    private function handle_bulk_actions() {
        if (!isset($_POST['bulk_action']) || empty($_POST['bulk_action'])) {
            return;
        }
        
        if (!isset($_POST['job_ids']) || empty($_POST['job_ids'])) {
            wp_redirect(add_query_arg('error', 'no_jobs_selected', admin_url('admin.php?page=sleeve-ke-jobs')));
            exit;
        }
        
        $bulk_action = sanitize_text_field($_POST['bulk_action']);
        $job_ids = array_map('intval', $_POST['job_ids']);
        $processed = 0;
        
        foreach ($job_ids as $job_id) {
            $job = $this->get_job_by_id($job_id);
            
            if (!$job || !$this->user_can_edit_job($job)) {
                continue;
            }
            
            switch ($bulk_action) {
                case 'publish':
                    // Update job status to published
                    $processed++;
                    break;
                case 'draft':
                    // Update job status to draft
                    $processed++;
                    break;
                case 'archive':
                    // Update job status to archived
                    $processed++;
                    break;
                case 'delete':
                    // Delete job
                    $processed++;
                    break;
            }
        }
        
        if ($processed > 0) {
            wp_redirect(add_query_arg('success', 'bulk_action_completed', admin_url('admin.php?page=sleeve-ke-jobs')));
        } else {
            wp_redirect(add_query_arg('error', 'no_actions_processed', admin_url('admin.php?page=sleeve-ke-jobs')));
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
        $job = $this->get_job_by_id($job_id);
        
        // Check permissions
        if (!$job || !$this->user_can_edit_job($job)) {
            wp_send_json_error(array('message' => __('You do not have permission to edit this job.', 'sleeve-ke')));
        }
        
        // Validate status
        $valid_statuses = array_keys($this->get_status_options());
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(array('message' => __('Invalid status.', 'sleeve-ke')));
        }
        
        $statuses = $this->get_status_options();
        $new_status_label = isset($statuses[$status]) ? $statuses[$status] : $status;
        
        wp_send_json_success(array( 
            'message' => __('Job status updated successfully.', 'sleeve-ke'),
            'job_id' => $job_id,
            'new_status' => $status,
            'new_status_label' => $new_status_label
        ));
    }

    /**
     * Get status options for jobs
     */
    public function get_status_options() {
        return array(
            'draft' => __('Draft', 'sleeve-ke'),
            'published' => __('Published', 'sleeve-ke'),
            'archived' => __('Archived', 'sleeve-ke'),
            'expired' => __('Expired', 'sleeve-ke')
        );
    }

    /**
     * Get job types
     */
    public function get_job_types() {
        return array(
            'full-time' => __('Full-Time', 'sleeve-ke'),
            'part-time' => __('Part-Time', 'sleeve-ke'),
            'contract' => __('Contract', 'sleeve-ke'),
            'temporary' => __('Temporary', 'sleeve-ke'),
            'internship' => __('Internship', 'sleeve-ke'),
            'freelance' => __('Freelance', 'sleeve-ke')
        );
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
     * Get jobs data
     */
    public function get_jobs_data() {
        // Apply filters if any
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $type_filter = isset($_GET['job_type']) ? sanitize_text_field($_GET['job_type']) : '';
        $sector_filter = isset($_GET['sector']) ? sanitize_text_field($_GET['sector']) : '';

        // Mock data - in real implementation, this would fetch from database
        $all_jobs = array(
            array(
                'id' => 1,
                'title' => 'Senior PHP Developer - Technology Sector',
                'sector' => 'technology',
                'description' => 'We are seeking a highly skilled Senior PHP Developer to join our dynamic technology team.',
                'company' => 'Tech Solutions Ltd',
                'employer_type' => 'organization',
                'location' => 'Nairobi, Kenya',
                'job_type' => 'Full-Time',
                'experience_level' => 'senior',
                'requirements' => 'Bachelor\'s degree in Computer Science, 5+ years PHP experience, Laravel framework expertise.',
                'benefits' => 'Health insurance, Flexible working hours, Remote work options',
                'salary_min' => 150000,
                'salary_max' => 300000,
                'currency' => 'KES',
                'remote_work' => 'hybrid',
                'status' => 'published',
                'applications_count' => 24,
                'posted_date' => '2025-10-10',
                'expires_at' => '2025-11-10',
                'employer_id' => 1
            ),
            array(
                'id' => 2,
                'title' => 'Frontend Developer - Creative Technology',
                'sector' => 'technology',
                'description' => 'Join our creative team as a Frontend Developer where you\'ll build beautiful, responsive user interfaces.',
                'company' => 'Creative Agency',
                'employer_type' => 'organization',
                'location' => 'Remote',
                'job_type' => 'Full-Time',
                'experience_level' => 'mid',
                'requirements' => 'Strong JavaScript, HTML5, CSS3 skills, React or Vue.js experience.',
                'benefits' => 'Fully remote work, Creative freedom, Latest equipment',
                'salary_min' => 120000,
                'salary_max' => 250000,
                'currency' => 'KES',
                'remote_work' => 'full',
                'status' => 'published',
                'applications_count' => 18,
                'posted_date' => '2025-10-08',
                'expires_at' => '2025-11-08',
                'employer_id' => 2
            ),
            array(
                'id' => 3,
                'title' => 'Marketing Intern - Digital Marketing',
                'sector' => 'marketing',
                'description' => 'Great opportunity for a Marketing student or recent graduate to gain hands-on experience.',
                'company' => 'StartUp Inc',
                'employer_type' => 'organization',
                'location' => 'Dar es Salaam, Tanzania',
                'job_type' => 'Internship',
                'experience_level' => 'entry',
                'requirements' => 'Currently studying Marketing/Business or recent graduate, Social media knowledge.',
                'benefits' => 'Mentorship program, Certificate of completion, Networking opportunities',
                'salary_min' => 50000,
                'salary_max' => 80000,
                'currency' => 'TZS',
                'remote_work' => 'hybrid',
                'status' => 'draft',
                'applications_count' => 0,
                'posted_date' => '2025-10-15',
                'expires_at' => '2025-11-30',
                'employer_id' => 1
            )
        );

        // Filter by current user if they're an employer
        if ($this->is_employer()) {
            $current_user_id = get_current_user_id();
            $all_jobs = array_filter($all_jobs, function($job) use ($current_user_id) {
                return $job['employer_id'] === $current_user_id;
            });
        }

        // Apply filters
        $filtered_jobs = $all_jobs;

        if (!empty($search)) {
            $filtered_jobs = array_filter($filtered_jobs, function($job) use ($search) {
                return stripos($job['title'], $search) !== false || 
                       stripos($job['company'], $search) !== false;
            });
        }

        if (!empty($status_filter)) {
            $filtered_jobs = array_filter($filtered_jobs, function($job) use ($status_filter) {
                return $job['status'] === $status_filter;
            });
        }

        if (!empty($type_filter)) {
            $filtered_jobs = array_filter($filtered_jobs, function($job) use ($type_filter) {
                return strtolower(str_replace('-', '-', $job['job_type'])) === $type_filter;
            });
        }

        if (!empty($sector_filter)) {
            $filtered_jobs = array_filter($filtered_jobs, function($job) use ($sector_filter) {
                return $job['sector'] === $sector_filter;
            });
        }

        return $filtered_jobs;
    }

    /**
     * Get job statistics
     */
    public function get_job_stats() {
        $jobs = $this->get_jobs_data();
        $statuses = $this->get_status_options();
        
        $stats = array();
        $stats[] = array('count' => count($jobs), 'label' => __('Total Jobs', 'sleeve-ke'));
        
        foreach ($statuses as $status_key => $status_label) {
            $count = count(array_filter($jobs, function($job) use ($status_key) {
                return $job['status'] === $status_key;
            }));
            $stats[] = array('count' => $count, 'label' => $status_label);
        }
        
        return $stats;
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

    /**
     * Get job by ID
     */
    public function get_job_by_id($job_id) {
        $jobs = $this->get_jobs_data();
        foreach ($jobs as $job) {
            if ($job['id'] == $job_id) {
                return $job;
            }
        }
        return null;
    }
}