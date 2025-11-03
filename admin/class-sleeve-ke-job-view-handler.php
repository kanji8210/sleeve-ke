<?php
/**
 * Job View Handler
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/admin
 */

/**
 * Handles job view display
 */
class Sleeve_KE_Job_View_Handler {

    /**
     * Display job view page
     */
    public function display_job_view($job) {
        ?>
        <div class="wrap sleeve-ke-job-view-wrap">
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
                        <!-- Job content from previous implementation -->
                    </div>

                    <div class="job-actions">
                        <!-- Action buttons from previous implementation -->
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // Helper methods
    private function get_status_options() {
        return array(
            'draft' => __('Draft', 'sleeve-ke'),
            'published' => __('Published', 'sleeve-ke'),
            'archived' => __('Archived', 'sleeve-ke'),
            'expired' => __('Expired', 'sleeve-ke')
        );
    }
}