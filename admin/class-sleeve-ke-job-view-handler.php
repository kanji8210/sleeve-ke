<?php
/**
 * Job view handler class.
 *
 * Handles the display of single job view.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Job view handler class.
 */
class Sleeve_KE_Job_View_Handler {

    /**
     * Display the job view page.
     */
    public function display_job_view($job) {
        $sectors = $this->get_sectors();
        $job_types = $this->get_job_types();
        $experience_levels = $this->get_experience_levels();
        $remote_work_options = $this->get_remote_work_options();
        $status_options = $this->get_status_options();

        ?>
        <div class="wrap sleeve-ke-job-view-wrap">
            <h1><?php echo esc_html($job['title']); ?></h1>

            <div class="job-view-actions">
                <a href="<?php echo admin_url('admin.php?page=sleeve-ke-jobs'); ?>" class="button"><?php _e('Back to Jobs', 'sleeve-ke'); ?></a>
                <?php if (current_user_can('edit_job', $job['id'])) : ?>
                    <a href="<?php echo admin_url('admin.php?page=sleeve-ke-jobs&action=edit&id=' . $job['id']); ?>" class="button button-primary"><?php _e('Edit Job', 'sleeve-ke'); ?></a>
                <?php endif; ?>
            </div>

            <div class="job-view-content">
                <div class="job-main-details">
                    <div class="job-header">
                        <h2><?php _e('Job Overview', 'sleeve-ke'); ?></h2>
                        <span class="status-badge status-<?php echo esc_attr($job['status']); ?>">
                            <?php echo esc_html($status_options[$job['status']]); ?>
                        </span>
                    </div>

                    <div class="job-meta">
                        <div class="meta-item">
                            <strong><?php _e('Company:', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html($job['company']); ?></span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Sector:', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html($sectors[$job['sector']]); ?></span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Location:', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html($job['location']); ?></span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Job Type:', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html($job_types[$job['job_type']]); ?></span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Experience Level:', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html($experience_levels[$job['experience_level']]); ?></span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Remote Work:', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html($remote_work_options[$job['remote_work']]); ?></span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Posted Date:', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html(date('M j, Y', strtotime($job['posted_date']))); ?></span>
                        </div>
                        <div class="meta-item">
                            <strong><?php _e('Expires:', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html(date('M j, Y', strtotime($job['expires_at']))); ?></span>
                        </div>
                    </div>

                    <?php if (!empty($job['salary_min']) || !empty($job['salary_max'])) : ?>
                        <div class="salary-info">
                            <strong><?php _e('Salary:', 'sleeve-ke'); ?></strong>
                            <span>
                                <?php if (!empty($job['salary_min']) && !empty($job['salary_max'])) : ?>
                                    <?php echo esc_html(number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']) . ' ' . $job['currency']); ?>
                                <?php elseif (!empty($job['salary_min'])) : ?>
                                    <?php echo esc_html(number_format($job['salary_min']) . '+ ' . $job['currency']); ?>
                                <?php else : ?>
                                    <?php echo esc_html(number_format($job['salary_max']) . ' ' . $job['currency']); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="job-description">
                    <h3><?php _e('Job Description', 'sleeve-ke'); ?></h3>
                    <div class="description-content">
                        <?php echo wp_kses_post(wpautop($job['description'])); ?>
                    </div>
                </div>

                <div class="job-requirements">
                    <h3><?php _e('Requirements', 'sleeve-ke'); ?></h3>
                    <div class="requirements-content">
                        <?php echo wp_kses_post(wpautop($job['requirements'])); ?>
                    </div>
                </div>

                <?php if (!empty($job['benefits'])) : ?>
                    <div class="job-benefits">
                        <h3><?php _e('Benefits', 'sleeve-ke'); ?></h3>
                        <div class="benefits-content">
                            <?php echo wp_kses_post(wpautop($job['benefits'])); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="job-applications">
                    <h3><?php _e('Applications', 'sleeve-ke'); ?></h3>
                    <div class="applications-count">
                        <p>
                            <?php
                            printf(
                                _n(
                                    'This job has %d application.',
                                    'This job has %d applications.',
                                    $job['applications_count'],
                                    'sleeve-ke'
                                ),
                                $job['applications_count']
                            );
                            ?>
                        </p>
                        <a href="<?php echo admin_url('admin.php?page=sleeve-ke-applications&job_id=' . $job['id']); ?>" class="button">
                            <?php _e('View Applications', 'sleeve-ke'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get sectors.
     */
    private function get_sectors() {
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
     * Get job types.
     */
    private function get_job_types() {
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
     * Get experience levels.
     */
    private function get_experience_levels() {
        return array(
            'entry' => __('Entry Level', 'sleeve-ke'),
            'mid' => __('Mid Level', 'sleeve-ke'),
            'senior' => __('Senior Level', 'sleeve-ke'),
            'executive' => __('Executive', 'sleeve-ke')
        );
    }

    /**
     * Get remote work options.
     */
    private function get_remote_work_options() {
        return array(
            'none' => __('On-Site Only', 'sleeve-ke'),
            'partial' => __('Partially Remote', 'sleeve-ke'),
            'full' => __('Fully Remote', 'sleeve-ke'),
            'hybrid' => __('Hybrid', 'sleeve-ke')
        );
    }

    /**
     * Get status options.
     */
    private function get_status_options() {
        return array(
            'draft' => __('Draft', 'sleeve-ke'),
            'published' => __('Published', 'sleeve-ke'),
            'archived' => __('Archived', 'sleeve-ke'),
            'expired' => __('Expired', 'sleeve-ke')
        );
    }
}