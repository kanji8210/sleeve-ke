<?php
/**
 * Job Form Handler
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Handles job form display and processing
 */
class Sleeve_KE_Job_Form_Handler {

    /**
     * Display the job form (shared between add and edit)
     */
    public function display_job_form($job = null) {
        $is_edit = !is_null($job);
        $form_title = $is_edit ? __('Edit Job', 'sleeve-ke') : __('Add New Job', 'sleeve-ke');
        $action = $is_edit ? 'update' : 'create';
        $job_id = $is_edit ? $job['id'] : 0;
        
        // Get form data from transient if available
        $form_data = get_transient('sleeve_ke_job_form_data');
        if ($form_data && is_array($form_data)) {
            $job = array_merge($is_edit ? $job : array(), $form_data);
            delete_transient('sleeve_ke_job_form_data');
        }
        ?>
        <div class="wrap sleeve-ke-job-form-wrap">
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
                            
                            <?php if ($is_edit) : ?>
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
        <?php
    }

    // Helper methods for form options
    private function get_status_options() {
        return array(
            'draft' => __('Draft', 'sleeve-ke'),
            'published' => __('Published', 'sleeve-ke'),
            'archived' => __('Archived', 'sleeve-ke'),
            'expired' => __('Expired', 'sleeve-ke')
        );
    }

    private function get_sectors() {
        return array(
            'technology' => __('Technology & IT', 'sleeve-ke'),
            'healthcare' => __('Healthcare & Medical', 'sleeve-ke'),
            // ... other sectors from previous implementation
        );
    }

    private function get_job_types() {
        return array(
            'full-time' => __('Full-Time', 'sleeve-ke'),
            'part-time' => __('Part-Time', 'sleeve-ke'),
            // ... other job types
        );
    }

    private function get_experience_levels() {
        return array(
            'entry' => __('Entry Level (0-2 years)', 'sleeve-ke'),
            'mid' => __('Mid Level (2-5 years)', 'sleeve-ke'),
            // ... other levels
        );
    }

    private function get_remote_work_options() {
        return array(
            'none' => __('On-site Only', 'sleeve-ke'),
            'partial' => __('Partially Remote', 'sleeve-ke'),
            // ... other options
        );
    }

    private function get_currencies() {
        return array(
            'KES' => 'KES (Kenyan Shilling)',
            'USD' => 'USD (US Dollar)',
            // ... other currencies
        );
    }
}