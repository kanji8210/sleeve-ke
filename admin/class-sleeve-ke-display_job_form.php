<?php
/**
 * Job Forms Handler - Extended class for job creation and editing
 */
class Sleeve_KE_Job_Forms extends Sleeve_KE_Jobs {
    
    /**
     * Display the add job form
     */
    public function display_add_job_form() {
        $this->display_job_form();
    }

    /**
     * Display the edit job form
     */
    public function display_edit_job_form($job_id) {
        $job = $this->get_job_by_id($job_id);
        if (!$job) {
            wp_die(__('Job not found.', 'sleeve-ke'));
        }
        
        // Check if user can edit this job
        if (!$this->user_can_edit_job($job)) {
            wp_die(__('You do not have permission to edit this job.', 'sleeve-ke'));
        }
        
        $this->display_job_form($job);
    }

    /**
     * Display job view page
     */
    public function display_job_view($job_id) {
        $job = $this->get_job_by_id($job_id);
        if (!$job) {
            wp_die(__('Job not found.', 'sleeve-ke'));
        }
        
        // Check if user can view this job
        if (!$this->user_can_view_job($job)) {
            wp_die(__('You do not have permission to view this job.', 'sleeve-ke'));
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('View Job', 'sleeve-ke'); ?></h1>
            
            <div class="sleeve-ke-job-view">
                <!-- Back button -->
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs')); ?>" class="button">
                        &larr; <?php esc_html_e('Back to Jobs', 'sleeve-ke'); ?>
                    </a>
                </p>

                <!-- Job Details Card -->
                <div class="job-details-card">
                    <div class="job-header">
                        <h2><?php echo esc_html($job['title']); ?></h2>
                        <div class="job-meta">
                            <span class="company"><?php echo esc_html($job['company']); ?></span>
                            <span class="location"><?php echo esc_html($job['location']); ?></span>
                            <span class="job-type"><?php echo esc_html($job['job_type']); ?></span>
                            <span class="status-badge status-<?php echo esc_attr($job['status']); ?>">
                                <?php 
                                $statuses = $this->get_status_options();
                                echo esc_html($statuses[$job['status']]); 
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="job-content">
                        <div class="job-section">
                            <h3><?php esc_html_e('Job Description', 'sleeve-ke'); ?></h3>
                            <div class="job-description">
                                <?php echo wp_kses_post(wpautop($job['description'])); ?>
                            </div>
                        </div>

                        <div class="job-section">
                            <h3><?php esc_html_e('Requirements', 'sleeve-ke'); ?></h3>
                            <div class="job-requirements">
                                <?php echo wp_kses_post(wpautop($job['requirements'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($job['benefits'])) : ?>
                        <div class="job-section">
                            <h3><?php esc_html_e('Benefits', 'sleeve-ke'); ?></h3>
                            <div class="job-benefits">
                                <?php echo wp_kses_post(wpautop($job['benefits'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="job-details-grid">
                            <div class="detail-item">
                                <strong><?php esc_html_e('Sector:', 'sleeve-ke'); ?></strong>
                                <span>
                                    <?php 
                                    $sectors = $this->get_sectors();
                                    echo esc_html($sectors[$job['sector']] ?? $job['sector']); 
                                    ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <strong><?php esc_html_e('Experience Level:', 'sleeve-ke'); ?></strong>
                                <span><?php echo esc_html(ucfirst($job['experience_level'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong><?php esc_html_e('Remote Work:', 'sleeve-ke'); ?></strong>
                                <span>
                                    <?php 
                                    $remote_options = $this->get_remote_work_options();
                                    echo esc_html($remote_options[$job['remote_work']] ?? $job['remote_work']); 
                                    ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <strong><?php esc_html_e('Salary:', 'sleeve-ke'); ?></strong>
                                <span>
                                    <?php if (!empty($job['salary_min']) && !empty($job['salary_max'])) : ?>
                                        <?php echo esc_html(number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']) . ' ' . $job['currency']); ?>
                                    <?php elseif (!empty($job['salary_min'])) : ?>
                                        <?php echo esc_html(number_format($job['salary_min']) . '+ ' . $job['currency']); ?>
                                    <?php else : ?>
                                        <span class="no-salary"><?php esc_html_e('Not specified', 'sleeve-ke'); ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <strong><?php esc_html_e('Posted:', 'sleeve-ke'); ?></strong>
                                <span><?php echo esc_html($job['posted_date']); ?></span>
                            </div>
                            <div class="detail-item">
                                <strong><?php esc_html_e('Expires:', 'sleeve-ke'); ?></strong>
                                <span><?php echo esc_html($job['expires_at']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="job-actions">
                        <?php if ($this->user_can_edit_job($job)) : ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs&action=edit&id=' . $job['id'])); ?>" class="button button-primary">
                                <?php esc_html_e('Edit Job', 'sleeve-ke'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-applications&job_id=' . $job['id'])); ?>" class="button">
                            <?php 
                            printf(
                                esc_html__('View Applications (%d)', 'sleeve-ke'),
                                esc_html($job['applications_count'])
                            ); 
                            ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .job-details-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-top: 20px;
        }
        .job-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .job-header h2 {
            margin: 0 0 10px 0;
            color: #23282d;
        }
        .job-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .job-meta span {
            padding: 4px 8px;
            background: #f6f7f7;
            border-radius: 3px;
            font-size: 13px;
        }
        .job-section {
            margin-bottom: 30px;
        }
        .job-section h3 {
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 15px;
            color: #23282d;
        }
        .job-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f6f7f7;
        }
        .job-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        </style>
        <?php
    }

    /**
     * Display the job form (shared between add and edit)
     */
    private function display_job_form($job = null) {
        $is_edit = !is_null($job);
        $form_title = $is_edit ? __('Edit Job', 'sleeve-ke') : __('Add New Job', 'sleeve-ke');
        $action = $is_edit ? 'update' : 'create';
        $job_id = $is_edit ? $job['id'] : 0;
        
        // Get form data from transient if available (for validation repopulation)
        $form_data = get_transient('sleeve_ke_job_form_data');
        if ($form_data && is_array($form_data)) {
            $job = array_merge($is_edit ? $job : array(), $form_data);
            delete_transient('sleeve_ke_job_form_data');
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($form_title); ?></h1>
            
            <!-- Back button -->
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs')); ?>" class="button">
                    &larr; <?php esc_html_e('Back to Jobs', 'sleeve-ke'); ?>
                </a>
            </p>

            <form method="post" action="" class="sleeve-ke-job-form">
                <?php wp_nonce_field('sleeve_job_form', 'job_form_nonce'); ?>
                <input type="hidden" name="job_action" value="<?php echo esc_attr($action); ?>">
                <input type="hidden" name="job_id" value="<?php echo esc_attr($job_id); ?>">
                
                <div class="job-form-container">
                    <!-- Left Column - Main Job Details -->
                    <div class="form-column main-details">
                        <div class="form-section">
                            <h2><?php esc_html_e('Job Details', 'sleeve-ke'); ?></h2>
                            
                            <!-- Job Title -->
                            <div class="form-field">
                                <label for="job_title"><?php esc_html_e('Job Title *', 'sleeve-ke'); ?></label>
                                <input type="text" id="job_title" name="job_title" value="<?php echo esc_attr($job['title'] ?? ''); ?>" required class="regular-text">
                                <p class="description"><?php esc_html_e('Enter the title of the job position', 'sleeve-ke'); ?></p>
                            </div>

                            <!-- Job Description -->
                            <div class="form-field">
                                <label for="job_description"><?php esc_html_e('Job Description *', 'sleeve-ke'); ?></label>
                                <?php
                                $description = $job['description'] ?? '';
                                wp_editor($description, 'job_description', array(
                                    'textarea_name' => 'job_description',
                                    'textarea_rows' => 10,
                                    'media_buttons' => false,
                                    'teeny' => true,
                                    'quicktags' => false
                                ));
                                ?>
                                <p class="description"><?php esc_html_e('Describe the role, responsibilities, and what you\'re looking for in a candidate', 'sleeve-ke'); ?></p>
                            </div>

                            <!-- Requirements -->
                            <div class="form-field">
                                <label for="job_requirements"><?php esc_html_e('Requirements *', 'sleeve-ke'); ?></label>
                                <?php
                                $requirements = $job['requirements'] ?? '';
                                wp_editor($requirements, 'job_requirements', array(
                                    'textarea_name' => 'job_requirements',
                                    'textarea_rows' => 8,
                                    'media_buttons' => false,
                                    'teeny' => true,
                                    'quicktags' => false
                                ));
                                ?>
                                <p class="description"><?php esc_html_e('List the skills, qualifications, and experience required', 'sleeve-ke'); ?></p>
                            </div>

                            <!-- Benefits -->
                            <div class="form-field">
                                <label for="job_benefits"><?php esc_html_e('Benefits & Perks', 'sleeve-ke'); ?></label>
                                <?php
                                $benefits = $job['benefits'] ?? '';
                                wp_editor($benefits, 'job_benefits', array(
                                    'textarea_name' => 'job_benefits',
                                    'textarea_rows' => 6,
                                    'media_buttons' => false,
                                    'teeny' => true,
                                    'quicktags' => false
                                ));
                                ?>
                                <p class="description"><?php esc_html_e('What does your company offer to employees?', 'sleeve-ke'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Job Metadata -->
                    <div class="form-column job-meta">
                        <div class="form-section">
                            <h2><?php esc_html_e('Job Settings', 'sleeve-ke'); ?></h2>
                            
                            <!-- Status -->
                            <div class="form-field">
                                <label for="job_status"><?php esc_html_e('Status *', 'sleeve-ke'); ?></label>
                                <select id="job_status" name="job_status" required>
                                    <?php
                                    $statuses = $this->get_status_options();
                                    $current_status = $job['status'] ?? 'draft';
                                    foreach ($statuses as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_status, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Company -->
                            <div class="form-field">
                                <label for="company"><?php esc_html_e('Company *', 'sleeve-ke'); ?></label>
                                <input type="text" id="company" name="company" value="<?php echo esc_attr($job['company'] ?? ''); ?>" required class="regular-text">
                            </div>

                            <!-- Sector -->
                            <div class="form-field">
                                <label for="sector"><?php esc_html_e('Sector *', 'sleeve-ke'); ?></label>
                                <select id="sector" name="sector" required>
                                    <option value=""><?php esc_html_e('Select Sector', 'sleeve-ke'); ?></option>
                                    <?php
                                    $sectors = $this->get_sectors();
                                    $current_sector = $job['sector'] ?? '';
                                    foreach ($sectors as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_sector, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Location -->
                            <div class="form-field">
                                <label for="location"><?php esc_html_e('Location *', 'sleeve-ke'); ?></label>
                                <input type="text" id="location" name="location" value="<?php echo esc_attr($job['location'] ?? ''); ?>" required class="regular-text">
                                <p class="description"><?php esc_html_e('City, Country (e.g., Nairobi, Kenya)', 'sleeve-ke'); ?></p>
                            </div>

                            <!-- Job Type -->
                            <div class="form-field">
                                <label for="job_type"><?php esc_html_e('Job Type *', 'sleeve-ke'); ?></label>
                                <select id="job_type" name="job_type" required>
                                    <option value=""><?php esc_html_e('Select Job Type', 'sleeve-ke'); ?></option>
                                    <?php
                                    $job_types = $this->get_job_types();
                                    $current_type = $job['job_type'] ?? '';
                                    foreach ($job_types as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_type, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Experience Level -->
                            <div class="form-field">
                                <label for="experience_level"><?php esc_html_e('Experience Level *', 'sleeve-ke'); ?></label>
                                <select id="experience_level" name="experience_level" required>
                                    <option value=""><?php esc_html_e('Select Experience Level', 'sleeve-ke'); ?></option>
                                    <?php
                                    $experience_levels = $this->get_experience_levels();
                                    $current_level = $job['experience_level'] ?? '';
                                    foreach ($experience_levels as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_level, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Remote Work Options -->
                            <div class="form-field">
                                <label for="remote_work"><?php esc_html_e('Remote Work', 'sleeve-ke'); ?></label>
                                <select id="remote_work" name="remote_work">
                                    <?php
                                    $remote_options = $this->get_remote_work_options();
                                    $current_remote = $job['remote_work'] ?? 'none';
                                    foreach ($remote_options as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_remote, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Salary Information -->
                            <div class="form-field salary-field">
                                <label><?php esc_html_e('Salary Information', 'sleeve-ke'); ?></label>
                                <div class="salary-inputs">
                                    <input type="number" name="salary_min" value="<?php echo esc_attr($job['salary_min'] ?? ''); ?>" 
                                           placeholder="<?php esc_attr_e('Min Salary', 'sleeve-ke'); ?>" class="small-text">
                                    <span class="salary-separator">-</span>
                                    <input type="number" name="salary_max" value="<?php echo esc_attr($job['salary_max'] ?? ''); ?>" 
                                           placeholder="<?php esc_attr_e('Max Salary', 'sleeve-ke'); ?>" class="small-text">
                                    <select name="currency" class="currency-select">
                                        <?php
                                        $currencies = $this->get_currencies();
                                        $current_currency = $job['currency'] ?? 'KES';
                                        foreach ($currencies as $value => $label) :
                                        ?>
                                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_currency, $value); ?>>
                                                <?php echo esc_html($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="description"><?php esc_html_e('Leave blank if salary is not specified', 'sleeve-ke'); ?></p>
                            </div>

                            <!-- Expiration Date -->
                            <div class="form-field">
                                <label for="expires_at"><?php esc_html_e('Expiration Date', 'sleeve-ke'); ?></label>
                                <input type="date" id="expires_at" name="expires_at" 
                                       value="<?php echo esc_attr($job['expires_at'] ?? ''); ?>" 
                                       min="<?php echo esc_attr(date('Y-m-d')); ?>" class="regular-text">
                                <p class="description"><?php esc_html_e('Leave blank to auto-expire after 30 days', 'sleeve-ke'); ?></p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <?php submit_button($is_edit ? __('Update Job', 'sleeve-ke') : __('Publish Job', 'sleeve-ke'), 'primary', 'submit_job', false); ?>
                            
                            <?php if ($is_edit) : ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs&action=view&id=' . $job_id)); ?>" class="button">
                                    <?php esc_html_e('Cancel', 'sleeve-ke'); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=sleeve-ke-jobs')); ?>" class="button">
                                    <?php esc_html_e('Cancel', 'sleeve-ke'); ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($is_edit && $this->user_can_delete_job($job)) : ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=sleeve-ke-jobs&action=delete&id=' . $job_id), 'delete_job_' . $job_id)); ?>" 
                                   class="button button-link-delete" 
                                   onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this job? This action cannot be undone.', 'sleeve-ke'); ?>')">
                                    <?php esc_html_e('Delete Job', 'sleeve-ke'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <style>
        .job-form-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        .form-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-section h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .form-field {
            margin-bottom: 20px;
        }
        .form-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .form-field input[type="text"],
        .form-field input[type="number"],
        .form-field input[type="date"],
        .form-field select {
            width: 100%;
            max-width: 100%;
        }
        .form-field .description {
            font-style: italic;
            color: #666;
            margin: 5px 0 0 0;
            font-size: 13px;
        }
        .salary-inputs {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .salary-inputs input {
            flex: 1;
        }
        .salary-separator {
            color: #666;
        }
        .currency-select {
            width: 100px !important;
        }
        .form-actions {
            text-align: right;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .form-actions .button {
            margin-left: 8px;
        }
        .button-link-delete {
            color: #d63638;
            border-color: #d63638;
        }
        .button-link-delete:hover {
            background: #d63638;
            color: white;
        }
        @media (max-width: 1200px) {
            .job-form-container {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }

    /**
     * Get experience levels
     */
    public function get_experience_levels() {
        return array(
            'entry' => __('Entry Level (0-2 years)', 'sleeve-ke'),
            'mid' => __('Mid Level (2-5 years)', 'sleeve-ke'),
            'senior' => __('Senior Level (5+ years)', 'sleeve-ke'),
            'executive' => __('Executive Level', 'sleeve-ke')
        );
    }

    /**
     * Get remote work options
     */
    public function get_remote_work_options() {
        return array(
            'none' => __('On-site Only', 'sleeve-ke'),
            'partial' => __('Partially Remote', 'sleeve-ke'),
            'full' => __('Fully Remote', 'sleeve-ke'),
            'hybrid' => __('Hybrid', 'sleeve-ke')
        );
    }

    /**
     * Get currency options
     */
    public function get_currencies() {
        return array(
            'KES' => 'KES (Kenyan Shilling)',
            'USD' => 'USD (US Dollar)',
            'EUR' => 'EUR (Euro)',
            'GBP' => 'GBP (British Pound)',
            'TZS' => 'TZS (Tanzanian Shilling)',
            'UGX' => 'UGX (Ugandan Shilling)'
        );
    }

    /**
     * Check if user can view a job
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
}