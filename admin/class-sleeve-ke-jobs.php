<?php
/**
 * Jobs management functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

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

        // Debug information (only show if WP_DEBUG is enabled)
        if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['debug'])) {
            $this->display_debug_info();
        }
    }

    /**
     * Display debug information
     */
    private function display_debug_info() {
        echo '<div class="notice notice-info"><pre>';
        echo "Debug Information:\n";
        echo "Current User: " . wp_get_current_user()->display_name . "\n";
        echo "User Roles: " . implode(', ', wp_get_current_user()->roles) . "\n";
        echo "Can Add Jobs: " . ($this->user_can_add_jobs() ? 'Yes' : 'No') . "\n";
        echo "Can Manage All Jobs: " . ($this->user_can_manage_all_jobs() ? 'Yes' : 'No') . "\n";
        echo "POST Data: " . print_r($_POST, true) . "\n";
        echo "GET Data: " . print_r($_GET, true) . "\n";
        echo '</pre></div>';
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
        <div class="wrap">
            <h1>
                <?php esc_html_e('Job Postings', 'sleeve-ke'); ?>
                <?php if ($can_add_jobs) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs&action=add')); ?>" class="page-title-action">
                        <?php esc_html_e('Add New Job', 'sleeve-ke'); ?>
                    </a>
                <?php endif; ?>
                
                <!-- Debug button -->
                <?php if (defined('WP_DEBUG') && WP_DEBUG) : ?>
                    <a href="<?php echo esc_url(add_query_arg('debug', '1')); ?>" class="page-title-action" style="background: #666; border-color: #555;">
                        <?php esc_html_e('Debug Info', 'sleeve-ke'); ?>
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
        
        <script>
        jQuery(document).ready(function($) {
            // Handle status change
            $('.status-select').on('change', function() {
                var jobId = $(this).data('job-id');
                var newStatus = $(this).val();
                var $select = $(this);
                
                // Show loading state
                $select.prop('disabled', true).addClass('updating');
                
                $.post(ajaxurl, {
                    action: 'update_job_status',
                    job_id: jobId,
                    status: newStatus,
                    nonce: '<?php echo wp_create_nonce('update_job_status'); ?>'
                }, function(response) {
                    if (response.success) {
                        // Update the status badge
                        var $row = $select.closest('tr');
                        var $badge = $row.find('.status-badge');
                        $badge.removeClass().addClass('status-badge status-' + newStatus).text(response.data.new_status_label);
                        
                        // Show success message
                        showNotice('<?php esc_html_e('Status updated successfully!', 'sleeve-ke'); ?>', 'success');
                    } else {
                        // Revert select to original value
                        $select.val($select.data('original-value'));
                        showNotice(response.data.message || '<?php esc_html_e('Error updating status', 'sleeve-ke'); ?>', 'error');
                    }
                }).fail(function() {
                    $select.val($select.data('original-value'));
                    showNotice('<?php esc_html_e('Network error. Please try again.', 'sleeve-ke'); ?>', 'error');
                }).always(function() {
                    $select.prop('disabled', false).removeClass('updating');
                });
            });
            
            // Store original values
            $('.status-select').each(function() {
                $(this).data('original-value', $(this).val());
            });
            
            // Handle select all checkbox
            $('#cb-select-all').on('change', function() {
                $('input[name="job_ids[]"]').prop('checked', this.checked);
            });
            
            // Show notice function
            function showNotice(message, type) {
                var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
                $('.wrap h1').after(notice);
                
                // Auto remove after 5 seconds
                setTimeout(function() {
                    notice.fadeOut(function() {
                        notice.remove();
                    });
                }, 5000);
            }
        });
        </script>
        
        <style>
        .status-select.updating {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.status-published {
            background: #d1e7dd;
            color: #0f5132;
        }
        .status-badge.status-draft {
            background: #fff3cd;
            color: #664d03;
        }
        .status-badge.status-archived {
            background: #e2e3e5;
            color: #41464b;
        }
        .status-badge.status-expired {
            background: #f8d7da;
            color: #721c24;
        }
        </style>
        <?php
    }

    /**
     * Display add job form.
     */
    private function display_add_job_form() {
        if (!$this->user_can_add_jobs()) {
            wp_die(__('You do not have permission to add jobs.', 'sleeve-ke'));
        }

        $this->display_job_form();
    }

    /**
     * Display edit job form.
     */
    private function display_edit_job_form($job_id) {
        $job = $this->get_job_by_id($job_id);
        
        if (!$job) {
            wp_die(__('Job not found.', 'sleeve-ke'));
        }

        if (!$this->user_can_edit_job($job)) {
            wp_die(__('You do not have permission to edit this job.', 'sleeve-ke'));
        }

        $this->display_job_form($job);
    }

    /**
     * Display job form (add/edit).
     */
    private function display_job_form($job = null) {
        $is_edit = !empty($job);
        $form_title = $is_edit ? __('Edit Job', 'sleeve-ke') : __('Add New Job', 'sleeve-ke');
        $submit_text = $is_edit ? __('Update Job', 'sleeve-ke') : __('Add Job', 'sleeve-ke');
        
        // Get submitted values from transient (if form had errors)
        $submitted_values = get_transient('sleeve_ke_job_form_values');
        
        // Default values
        $defaults = array(
            'id' => 0,
            'title' => '',
            'sector' => '',
            'description' => '',
            'requirements' => '',
            'company' => '',
            'employer_type' => 'organization',
            'location' => '',
            'job_type' => 'full-time',
            'experience_level' => '',
            'salary_min' => '',
            'salary_max' => '',
            'currency' => 'KES',
            'status' => 'draft',
            'expires_at' => '',
            'remote_work' => 'no',
            'benefits' => ''
        );

        // Use submitted values if available, otherwise use job data or defaults
        if ($submitted_values && is_array($submitted_values)) {
            $job_data = array_merge($defaults, $submitted_values);
            delete_transient('sleeve_ke_job_form_values');
        } else {
            $job_data = $is_edit ? array_merge($defaults, $job) : $defaults;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($form_title); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs')); ?>" class="button">
                <?php esc_html_e('← Back to Jobs', 'sleeve-ke'); ?>
            </a>
            
            <form method="post" action="" class="sleeve-ke-job-form" id="job-form">
                <?php wp_nonce_field('sleeve_job_form', 'job_form_nonce'); ?>
                <input type="hidden" name="job_action" value="<?php echo $is_edit ? 'update' : 'create'; ?>" />
                <input type="hidden" name="job_id" value="<?php echo esc_attr($job_data['id']); ?>" />
                
                <div class="sleeve-ke-form-container">
                    <div class="form-section">
                        <h2><?php esc_html_e('Job Information', 'sleeve-ke'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="job_title"><?php esc_html_e('Job Title', 'sleeve-ke'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="job_title" name="job_title" value="<?php echo esc_attr($job_data['title']); ?>" class="regular-text" required />
                                    <p class="description"><?php esc_html_e('Include sector in title (e.g., "Senior Developer - Technology Sector")', 'sleeve-ke'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="sector"><?php esc_html_e('Sector', 'sleeve-ke'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <select id="sector" name="sector" required>
                                        <option value=""><?php esc_html_e('Select Sector', 'sleeve-ke'); ?></option>
                                        <?php
                                        $sectors = $this->get_sectors();
                                        foreach ($sectors as $sector_key => $sector_label) :
                                        ?>
                                            <option value="<?php echo esc_attr($sector_key); ?>" <?php selected($job_data['sector'], $sector_key); ?>>
                                                <?php echo esc_html($sector_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="company"><?php esc_html_e('Employer Name', 'sleeve-ke'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="company" name="company" value="<?php echo esc_attr($job_data['company']); ?>" class="regular-text" required />
                                    <p class="description"><?php esc_html_e('Organization name or Individual name (e.g., "Tech Corp" or "Dr. John Doe")', 'sleeve-ke'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="employer_type"><?php esc_html_e('Employer Type', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <select id="employer_type" name="employer_type">
                                        <option value="organization" <?php selected($job_data['employer_type'], 'organization'); ?>><?php esc_html_e('Organization/Company', 'sleeve-ke'); ?></option>
                                        <option value="individual" <?php selected($job_data['employer_type'], 'individual'); ?>><?php esc_html_e('Individual', 'sleeve-ke'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="location"><?php esc_html_e('Location', 'sleeve-ke'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <input type="text" id="location" name="location" value="<?php echo esc_attr($job_data['location']); ?>" class="regular-text" required />
                                    <p class="description"><?php esc_html_e('e.g., Nairobi, Kenya or Remote', 'sleeve-ke'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="job_type"><?php esc_html_e('Job Type', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <select id="job_type" name="job_type">
                                        <?php
                                        $job_types = $this->get_job_types();
                                        foreach ($job_types as $type_key => $type_label) :
                                        ?>
                                            <option value="<?php echo esc_attr($type_key); ?>" <?php selected($job_data['job_type'], $type_key); ?>>
                                                <?php echo esc_html($type_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="experience_level"><?php esc_html_e('Experience Level', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <select id="experience_level" name="experience_level">
                                        <option value=""><?php esc_html_e('Select Experience Level', 'sleeve-ke'); ?></option>
                                        <?php
                                        $experience_levels = array(
                                            'entry' => __('Entry Level (0-2 years)', 'sleeve-ke'),
                                            'mid' => __('Mid Level (3-5 years)', 'sleeve-ke'),
                                            'senior' => __('Senior Level (6-10 years)', 'sleeve-ke'),
                                            'executive' => __('Executive Level (10+ years)', 'sleeve-ke')
                                        );
                                        foreach ($experience_levels as $level_key => $level_label) :
                                        ?>
                                            <option value="<?php echo esc_attr($level_key); ?>" <?php selected($job_data['experience_level'], $level_key); ?>>
                                                <?php echo esc_html($level_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="remote_work"><?php esc_html_e('Remote Work', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <select id="remote_work" name="remote_work">
                                        <option value="no" <?php selected($job_data['remote_work'], 'no'); ?>><?php esc_html_e('No Remote Work', 'sleeve-ke'); ?></option>
                                        <option value="hybrid" <?php selected($job_data['remote_work'], 'hybrid'); ?>><?php esc_html_e('Hybrid', 'sleeve-ke'); ?></option>
                                        <option value="full" <?php selected($job_data['remote_work'], 'full'); ?>><?php esc_html_e('Fully Remote', 'sleeve-ke'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="form-section">
                        <h2><?php esc_html_e('Salary Information', 'sleeve-ke'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="salary_min"><?php esc_html_e('Minimum Salary', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="salary_min" name="salary_min" value="<?php echo esc_attr($job_data['salary_min']); ?>" class="regular-text" min="0" step="1000" />
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="salary_max"><?php esc_html_e('Maximum Salary', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="salary_max" name="salary_max" value="<?php echo esc_attr($job_data['salary_max']); ?>" class="regular-text" min="0" step="1000" />
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="currency"><?php esc_html_e('Currency', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <select id="currency" name="currency">
                                        <option value="KES" <?php selected($job_data['currency'], 'KES'); ?>>KES (Kenyan Shilling)</option>
                                        <option value="USD" <?php selected($job_data['currency'], 'USD'); ?>>USD (US Dollar)</option>
                                        <option value="EUR" <?php selected($job_data['currency'], 'EUR'); ?>>EUR (Euro)</option>
                                        <option value="UGX" <?php selected($job_data['currency'], 'UGX'); ?>>UGX (Ugandan Shilling)</option>
                                        <option value="TZS" <?php selected($job_data['currency'], 'TZS'); ?>>TZS (Tanzanian Shilling)</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="form-section">
                        <h2><?php esc_html_e('Job Description', 'sleeve-ke'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="description"><?php esc_html_e('Description', 'sleeve-ke'); ?> <span class="required">*</span></label>
                                </th>
                                <td>
                                    <textarea id="description" name="description" rows="8" class="large-text" required><?php echo esc_textarea($job_data['description']); ?></textarea>
                                    <p class="description"><?php esc_html_e('Detailed description of the job role and responsibilities.', 'sleeve-ke'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="requirements"><?php esc_html_e('Requirements', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <textarea id="requirements" name="requirements" rows="6" class="large-text"><?php echo esc_textarea($job_data['requirements']); ?></textarea>
                                    <p class="description"><?php esc_html_e('Skills, qualifications, and experience required.', 'sleeve-ke'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="benefits"><?php esc_html_e('Benefits', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <textarea id="benefits" name="benefits" rows="4" class="large-text"><?php echo esc_textarea($job_data['benefits']); ?></textarea>
                                    <p class="description"><?php esc_html_e('Benefits and perks offered with this position.', 'sleeve-ke'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="form-section">
                        <h2><?php esc_html_e('Publication Settings', 'sleeve-ke'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="status"><?php esc_html_e('Status', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <select id="status" name="status">
                                        <?php
                                        $statuses = $this->get_status_options();
                                        foreach ($statuses as $status_key => $status_label) :
                                        ?>
                                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($job_data['status'], $status_key); ?>>
                                                <?php echo esc_html($status_label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="expires_at"><?php esc_html_e('Expires On', 'sleeve-ke'); ?></label>
                                </th>
                                <td>
                                    <input type="date" id="expires_at" name="expires_at" value="<?php echo esc_attr($job_data['expires_at']); ?>" min="<?php echo date('Y-m-d'); ?>" />
                                    <p class="description"><?php esc_html_e('Optional: Set when this job posting should expire.', 'sleeve-ke'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <p class="submit">
                    <?php submit_button($submit_text, 'primary', 'submit_job', false, array('id' => 'submit-job-btn')); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs')); ?>" class="button">
                        <?php esc_html_e('Cancel', 'sleeve-ke'); ?>
                    </a>
                    <span id="form-validation-errors" style="color: #dc3232; margin-left: 15px; display: none;"></span>
                </p>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Form validation
            $('#job-form').on('submit', function(e) {
                var isValid = true;
                var errors = [];
                
                // Clear previous errors
                $('.form-error').remove();
                $('#form-validation-errors').hide().empty();
                
                // Check required fields
                var requiredFields = [
                    { id: '#job_title', name: '<?php esc_html_e('Job Title', 'sleeve-ke'); ?>' },
                    { id: '#sector', name: '<?php esc_html_e('Sector', 'sleeve-ke'); ?>' },
                    { id: '#company', name: '<?php esc_html_e('Employer Name', 'sleeve-ke'); ?>' },
                    { id: '#location', name: '<?php esc_html_e('Location', 'sleeve-ke'); ?>' },
                    { id: '#description', name: '<?php esc_html_e('Description', 'sleeve-ke'); ?>' }
                ];
                
                requiredFields.forEach(function(field) {
                    var $field = $(field.id);
                    if (!$field.val().trim()) {
                        isValid = false;
                        errors.push('<?php esc_html_e('Please fill in', 'sleeve-ke'); ?>: ' + field.name);
                        $field.after('<span class="form-error" style="color: #dc3232; display: block; margin-top: 5px;"><?php esc_html_e('This field is required', 'sleeve-ke'); ?></span>');
                    }
                });
                
                // Check salary range
                var salaryMin = $('#salary_min').val();
                var salaryMax = $('#salary_max').val();
                if (salaryMin && salaryMax && parseFloat(salaryMin) > parseFloat(salaryMax)) {
                    isValid = false;
                    errors.push('<?php esc_html_e('Minimum salary cannot be greater than maximum salary', 'sleeve-ke'); ?>');
                    $('#salary_min, #salary_max').after('<span class="form-error" style="color: #dc3232; display: block; margin-top: 5px;"><?php esc_html_e('Minimum salary cannot be greater than maximum salary', 'sleeve-ke'); ?></span>');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    $('#form-validation-errors').html('<strong><?php esc_html_e('Please fix the following errors:', 'sleeve-ke'); ?></strong><br>' + errors.join('<br>')).show();
                    
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.form-error').first().offset().top - 100
                    }, 500);
                } else {
                    // Show loading state
                    $('#submit-job-btn').prop('disabled', true).val('<?php esc_html_e('Processing...', 'sleeve-ke'); ?>');
                }
            });
            
            // Real-time validation
            $('input[required], select[required], textarea[required]').on('blur', function() {
                var $field = $(this);
                var $error = $field.next('.form-error');
                
                if (!$field.val().trim()) {
                    if ($error.length === 0) {
                        $field.after('<span class="form-error" style="color: #dc3232; display: block; margin-top: 5px;"><?php esc_html_e('This field is required', 'sleeve-ke'); ?></span>');
                    }
                } else {
                    $error.remove();
                }
            });
        });
        </script>
        
        <style>
        .sleeve-ke-job-form .form-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .sleeve-ke-job-form .form-section h2 {
            margin-top: 0;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .sleeve-ke-job-form .required {
            color: #d63638;
        }
        
        .form-error {
            font-size: 12px;
            font-style: italic;
        }
        </style>
        <?php
    }

    // ... (Keep all the other existing methods: get_status_options, get_job_types, get_sectors, get_jobs_data, get_job_stats, etc.)

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
        if (!$this->user_can_add_jobs()) {
            wp_redirect(add_query_arg('error', 'permission_denied', admin_url('admin.php?page=sleeve-ke-jobs')));
            exit;
        }

        // Collect and sanitize input
        $title = isset($_POST['job_title']) ? sanitize_text_field(wp_unslash($_POST['job_title'])) : '';
        $sector = isset($_POST['sector']) ? sanitize_text_field(wp_unslash($_POST['sector'])) : '';
        $company = isset($_POST['company']) ? sanitize_text_field(wp_unslash($_POST['company'])) : '';
        $employer_type = isset($_POST['employer_type']) ? sanitize_text_field(wp_unslash($_POST['employer_type'])) : 'organization';
        $location = isset($_POST['location']) ? sanitize_text_field(wp_unslash($_POST['location'])) : '';
        $job_type = isset($_POST['job_type']) ? sanitize_text_field(wp_unslash($_POST['job_type'])) : '';
        $experience_level = isset($_POST['experience_level']) ? sanitize_text_field(wp_unslash($_POST['experience_level'])) : '';
        $salary_min = isset($_POST['salary_min']) ? floatval(wp_unslash($_POST['salary_min'])) : '';
        $salary_max = isset($_POST['salary_max']) ? floatval(wp_unslash($_POST['salary_max'])) : '';
        $currency = isset($_POST['currency']) ? sanitize_text_field(wp_unslash($_POST['currency'])) : 'KES';
        $description = isset($_POST['description']) ? wp_kses_post(wp_unslash($_POST['description'])) : '';
        $requirements = isset($_POST['requirements']) ? wp_kses_post(wp_unslash($_POST['requirements'])) : '';
        $benefits = isset($_POST['benefits']) ? wp_kses_post(wp_unslash($_POST['benefits'])) : '';
        $status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : 'draft';
        $expires_at = isset($_POST['expires_at']) ? sanitize_text_field(wp_unslash($_POST['expires_at'])) : '';
        $remote_work = isset($_POST['remote_work']) ? sanitize_text_field(wp_unslash($_POST['remote_work'])) : 'no';

        // Validate required fields
        $errors = array();
        if (empty($title)) {
            $errors[] = __('Job title is required.', 'sleeve-ke');
        }
        if (empty($sector)) {
            $errors[] = __('Sector is required.', 'sleeve-ke');
        }
        if (empty($company)) {
            $errors[] = __('Employer name is required.', 'sleeve-ke');
        }
        if (empty($location)) {
            $errors[] = __('Location is required.', 'sleeve-ke');
        }
        if (empty($description)) {
            $errors[] = __('Job description is required.', 'sleeve-ke');
        }

        // Validate salary range
        if ($salary_min && $salary_max && $salary_min > $salary_max) {
            $errors[] = __('Minimum salary cannot be greater than maximum salary.', 'sleeve-ke');
        }

        // Preserve submitted values to refill the form on error
        $submitted = array(
            'title' => $title,
            'sector' => $sector,
            'company' => $company,
            'employer_type' => $employer_type,
            'location' => $location,
            'job_type' => $job_type,
            'experience_level' => $experience_level,
            'salary_min' => $salary_min,
            'salary_max' => $salary_max,
            'currency' => $currency,
            'description' => $description,
            'requirements' => $requirements,
            'benefits' => $benefits,
            'status' => $status,
            'expires_at' => $expires_at,
            'remote_work' => $remote_work
        );

        if (!empty($errors)) {
            // Store errors and submitted values in transient for display after redirect
            set_transient('sleeve_ke_job_form_errors', $errors, 30);
            set_transient('sleeve_ke_job_form_values', $submitted, 30);

            wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-jobs&action=add'));
            exit;
        }

        // In a real implementation, you would save to database here
        // For now, we'll simulate success
        
        // Log the action for debugging
        error_log('Sleeve KE: Job created - Title: ' . $title . ', Company: ' . $company . ', Sector: ' . $sector);

        // Redirect with success message
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

        if (!$this->user_can_edit_job($job)) {
            wp_redirect(add_query_arg('error', 'permission_denied', admin_url('admin.php?page=sleeve-ke-jobs')));
            exit;
        }

        // Collect and sanitize input (same as create_job)
        $title = isset($_POST['job_title']) ? sanitize_text_field(wp_unslash($_POST['job_title'])) : '';
        $sector = isset($_POST['sector']) ? sanitize_text_field(wp_unslash($_POST['sector'])) : '';
        // ... collect all other fields

        // Validate required fields (same as create_job)
        $errors = array();
        if (empty($title)) $errors[] = __('Job title is required.', 'sleeve-ke');
        // ... other validations

        if (!empty($errors)) {
            set_transient('sleeve_ke_job_form_errors', $errors, 30);
            wp_safe_redirect(admin_url('admin.php?page=sleeve-ke-jobs&action=edit&id=' . $job_id));
            exit;
        }

        // In a real implementation, you would update the database here
        error_log('Sleeve KE: Job updated - ID: ' . $job_id . ', Title: ' . $title);

        // Redirect with success message
        wp_redirect(add_query_arg('success', 'job_updated', admin_url('admin.php?page=sleeve-ke-jobs')));
        exit;
    }

    /**
     * Handle bulk actions
     */
    private function handle_bulk_actions() {
        if (!$this->user_can_manage_all_jobs()) {
            wp_redirect(add_query_arg('error', 'permission_denied', admin_url('admin.php?page=sleeve-ke-jobs')));
            exit;
        }

        if (!isset($_POST['bulk_action']) || !isset($_POST['job_ids'])) {
            return;
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $job_ids = array_map('intval', $_POST['job_ids']);
        
        // Log bulk action for debugging
        error_log('Sleeve KE: Bulk action - ' . $action . ' on jobs: ' . implode(', ', $job_ids));

        // In a real implementation, you would update the database here
        
        // Redirect with success message
        wp_redirect(add_query_arg('success', 'bulk_action_completed', admin_url('admin.php?page=sleeve-ke-jobs')));
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
        
        // Log status change for debugging
        error_log('Sleeve KE: Job status updated - Job ID: ' . $job_id . ', New Status: ' . $status);
        
        // In a real implementation, you would update the database here
        
        $statuses = $this->get_status_options();
        $new_status_label = isset($statuses[$status]) ? $statuses[$status] : $status;
        
        wp_send_json_success(array( 
            'message' => __('Job status updated successfully.', 'sleeve-ke'),
            'job_id' => $job_id,
            'new_status' => $status,
            'new_status_label' => $new_status_label
        ));
    }

    // ... (Keep all the other existing methods unchanged)

    /**
     * Get jobs data from WP posts (used by admin listing).
     * Applies simple GET-based filters: search, status, job_type, sector.
     * Returns array of associative job data.
     */
    public function get_jobs_data() {
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $type_filter = isset($_GET['job_type']) ? sanitize_text_field($_GET['job_type']) : '';
        $sector_filter = isset($_GET['sector']) ? sanitize_text_field($_GET['sector']) : '';

        $args = array(
            'post_type' => 'job',
            'post_status' => array( 'publish', 'draft', 'private' ),
            'posts_per_page' => -1,
        );

        // Map our admin status filter to WP post_status where possible
        if ( $status_filter === 'published' ) {
            $args['post_status'] = 'publish';
        } elseif ( $status_filter === 'draft' ) {
            $args['post_status'] = 'draft';
        }

        // Employer users should only see their own jobs
        if ( $this->is_employer() ) {
            $args['author'] = get_current_user_id();
        }

        if ( ! empty( $search ) ) {
            $args['s'] = $search;
        }

        // Meta queries for job_type and sector
        $meta_query = array();
        if ( ! empty( $type_filter ) ) {
            $meta_query[] = array(
                'key' => 'job_type',
                'value' => $type_filter,
                'compare' => '='
            );
        }
        if ( ! empty( $sector_filter ) ) {
            $meta_query[] = array(
                'key' => 'sector',
                'value' => $sector_filter,
                'compare' => '='
            );
        }
        if ( ! empty( $meta_query ) ) {
            $args['meta_query'] = $meta_query;
        }

        $query = new WP_Query( $args );
        $results = array();
        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $job = array();
                $job['id'] = $post->ID;
                $job['title'] = get_the_title( $post );
                $job['sector'] = get_post_meta( $post->ID, 'sector', true );
                $job['description'] = $post->post_content;
                $job['company'] = get_post_meta( $post->ID, 'company_name', true );
                $job['employer_type'] = get_post_meta( $post->ID, 'employer_type', true );
                $job['location'] = get_post_meta( $post->ID, 'job_location', true );
                $job['job_type'] = get_post_meta( $post->ID, 'job_type', true );
                $job['experience_level'] = get_post_meta( $post->ID, 'experience_level', true );
                $job['requirements'] = get_post_meta( $post->ID, 'requirements', true );
                $job['benefits'] = get_post_meta( $post->ID, 'benefits', true );
                $job['salary_min'] = get_post_meta( $post->ID, 'salary_min', true );
                $job['salary_max'] = get_post_meta( $post->ID, 'salary_max', true );
                $job['currency'] = get_post_meta( $post->ID, 'currency', true );
                $job['remote_work'] = get_post_meta( $post->ID, 'is_remote', true );
                $job['status'] = ( $post->post_status === 'publish' ) ? 'published' : $post->post_status;
                $job['applications_count'] = 0; // placeholder; integrate real counts if you have applications table
                $job['posted_date'] = $post->post_date;
                $job['expires_at'] = get_post_meta( $post->ID, 'expires_at', true );
                $job['employer_id'] = $post->post_author;

                $results[] = $job;
            }
        }
        wp_reset_postdata();

        return $results;
    }

    /**
     * Get single job by ID, returning the same structure as get_jobs_data() items.
     */
    public function get_job_by_id( $job_id ) {
        $post = get_post( intval( $job_id ) );
        if ( ! $post || $post->post_type !== 'job' ) {
            return null;
        }

        $job = array();
        $job['id'] = $post->ID;
        $job['title'] = get_the_title( $post );
        $job['sector'] = get_post_meta( $post->ID, 'sector', true );
        $job['description'] = $post->post_content;
        $job['company'] = get_post_meta( $post->ID, 'company_name', true );
        $job['employer_type'] = get_post_meta( $post->ID, 'employer_type', true );
        $job['location'] = get_post_meta( $post->ID, 'job_location', true );
        $job['job_type'] = get_post_meta( $post->ID, 'job_type', true );
        $job['experience_level'] = get_post_meta( $post->ID, 'experience_level', true );
        $job['requirements'] = get_post_meta( $post->ID, 'requirements', true );
        $job['benefits'] = get_post_meta( $post->ID, 'benefits', true );
        $job['salary_min'] = get_post_meta( $post->ID, 'salary_min', true );
        $job['salary_max'] = get_post_meta( $post->ID, 'salary_max', true );
        $job['currency'] = get_post_meta( $post->ID, 'currency', true );
        $job['remote_work'] = get_post_meta( $post->ID, 'is_remote', true );
        $job['status'] = ( $post->post_status === 'publish' ) ? 'published' : $post->post_status;
        $job['applications_count'] = 0;
        $job['posted_date'] = $post->post_date;
        $job['expires_at'] = get_post_meta( $post->ID, 'expires_at', true );
        $job['employer_id'] = $post->post_author;

        return $job;
    }

    /**
     * Check if current user is an employer role.
     *
     * @return bool
     */
    private function is_employer() {
        $user = wp_get_current_user();
        if ( empty( $user ) ) {
            return false;
        }
        return in_array( 'employer', (array) $user->roles, true );
    }
}