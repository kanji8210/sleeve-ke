<?php
/**
 * Single Job Template
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/templates
 */

get_header();

global $wpdb;
$job_id = get_the_ID();

// Get job data from custom table
$jobs_table = $wpdb->prefix . 'sleeve_jobs';
$job = $wpdb->get_row($wpdb->prepare("SELECT * FROM $jobs_table WHERE id = %d", $job_id), ARRAY_A);

if (!$job) {
    echo '<p>' . __('Job not found.', 'sleeve-ke') . '</p>';
    get_footer();
    return;
}

// Get employer info
$employers_table = $wpdb->prefix . 'sleeve_employers';
$employer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $employers_table WHERE user_id = %d", $job['employer_id']), ARRAY_A);

// Check if current user is candidate
$current_user = wp_get_current_user();
$is_candidate = is_user_logged_in() && in_array('candidate', $current_user->roles);
$is_employer = is_user_logged_in() && in_array('employer', $current_user->roles);

// Check if candidate already applied
$has_applied = false;
if ($is_candidate) {
    $applications_table = $wpdb->prefix . 'sleeve_applications';
    $existing_application = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $applications_table WHERE job_id = %d AND candidate_id = %d",
        $job_id,
        $current_user->ID
    ));
    $has_applied = $existing_application > 0;
}

// Get job type label
function sleeve_ke_get_job_type_label($type) {
    $labels = array(
        'full-time' => 'Full-time',
        'part-time' => 'Part-time',
        'contract' => 'Contract',
        'freelance' => 'Freelance',
        'internship' => 'Internship'
    );
    return $labels[$type] ?? $type;
}
?>

<div class="sleeve-ke-single-job-container">
    <div class="job-header-section">
        <div class="job-header-content">
            <div class="back-button">
                <a href="<?php echo esc_url(home_url('/jobs')); ?>" class="btn-back">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                    <?php _e('Back to Jobs', 'sleeve-ke'); ?>
                </a>
            </div>
            
            <div class="job-title-section">
                <h1 class="job-title"><?php echo esc_html($job['title']); ?></h1>
                <div class="company-info">
                    <?php if ($employer && !empty($employer['company_logo'])): ?>
                        <img src="<?php echo esc_url($employer['company_logo']); ?>" alt="<?php echo esc_attr($employer['company_name']); ?>" class="company-logo">
                    <?php endif; ?>
                    <div class="company-details">
                        <h2 class="company-name"><?php echo esc_html($employer['company_name'] ?? 'Company'); ?></h2>
                        <div class="job-meta-inline">
                            <span class="meta-item">
                                <span class="dashicons dashicons-location"></span>
                                <?php echo esc_html($job['location']); ?>
                            </span>
                            <span class="meta-item">
                                <span class="dashicons dashicons-businessman"></span>
                                <?php echo esc_html(sleeve_ke_get_job_type_label($job['job_type'])); ?>
                            </span>
                            <span class="meta-item">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <?php echo human_time_diff(strtotime($job['created_at']), current_time('timestamp')) . ' ago'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="job-content-wrapper">
        <div class="job-main-content">
            <!-- Job Description -->
            <div class="job-section">
                <h3><?php _e('Job Description', 'sleeve-ke'); ?></h3>
                <div class="job-description">
                    <?php echo wp_kses_post(wpautop($job['description'])); ?>
                </div>
            </div>

            <!-- Requirements -->
            <div class="job-section">
                <h3><?php _e('Requirements', 'sleeve-ke'); ?></h3>
                <div class="job-requirements">
                    <?php echo wp_kses_post(wpautop($job['requirements'])); ?>
                </div>
            </div>

            <?php if (!empty($job['benefits'])): ?>
            <!-- Benefits -->
            <div class="job-section">
                <h3><?php _e('Benefits', 'sleeve-ke'); ?></h3>
                <div class="job-benefits">
                    <?php echo wp_kses_post(wpautop($job['benefits'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Application Section -->
            <div class="job-apply-section">
                <?php if (!is_user_logged_in()): ?>
                    <!-- Not logged in -->
                    <div class="apply-prompt login-required">
                        <h3><?php _e('Want to apply for this job?', 'sleeve-ke'); ?></h3>
                        <p><?php _e('Please login or register to apply for this position.', 'sleeve-ke'); ?></p>
                        <div class="action-buttons">
                            <?php
                            $login_page = get_option('sleeve_ke_login_page');
                            $custom_login_enabled = get_option('sleeve_ke_custom_login_enabled', false);
                            $login_url = ($custom_login_enabled && $login_page) ? get_permalink($login_page) : wp_login_url(get_permalink());
                            
                            $candidate_page = get_option('sleeve_ke_candidate_registration_page');
                            $candidate_url = $candidate_page ? get_permalink($candidate_page) : home_url('/candidate-registration');
                            ?>
                            <a href="<?php echo esc_url($login_url); ?>" class="btn btn-primary">
                                <?php _e('Login', 'sleeve-ke'); ?>
                            </a>
                            <a href="<?php echo esc_url($candidate_url); ?>" class="btn btn-secondary">
                                <?php _e('Register as Candidate', 'sleeve-ke'); ?>
                            </a>
                        </div>
                    </div>
                <?php elseif ($is_employer): ?>
                    <!-- Employer viewing -->
                    <div class="apply-prompt employer-view">
                        <h3><?php _e('Employer View', 'sleeve-ke'); ?></h3>
                        <p><?php _e('You are viewing this job as an employer. Only candidates can apply.', 'sleeve-ke'); ?></p>
                    </div>
                <?php elseif ($is_candidate && $has_applied): ?>
                    <!-- Already applied -->
                    <div class="apply-prompt already-applied">
                        <h3><?php _e('Application Submitted', 'sleeve-ke'); ?></h3>
                        <p><?php _e('You have already applied for this position. You can track your application status in your dashboard.', 'sleeve-ke'); ?></p>
                        <?php
                        $dashboard_page = get_option('sleeve_ke_dashboard_page');
                        $dashboard_url = $dashboard_page ? get_permalink($dashboard_page) : home_url('/dashboard');
                        ?>
                        <a href="<?php echo esc_url($dashboard_url); ?>" class="btn btn-primary">
                            <?php _e('View My Applications', 'sleeve-ke'); ?>
                        </a>
                    </div>
                <?php elseif ($is_candidate): ?>
                    <!-- Application form for candidates -->
                    <div class="apply-prompt">
                        <h3><?php _e('Apply for this Position', 'sleeve-ke'); ?></h3>
                        <form id="sleeve-ke-application-form" class="application-form" enctype="multipart/form-data">
                            <?php wp_nonce_field('sleeve_ke_apply_job', 'application_nonce'); ?>
                            <input type="hidden" name="job_id" value="<?php echo esc_attr($job_id); ?>">
                            
                            <div class="form-group">
                                <label for="cover_letter"><?php _e('Cover Letter *', 'sleeve-ke'); ?></label>
                                <textarea id="cover_letter" name="cover_letter" rows="8" required placeholder="<?php esc_attr_e('Tell the employer why you are a great fit for this position...', 'sleeve-ke'); ?>"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="resume_file"><?php _e('Upload Resume/CV *', 'sleeve-ke'); ?></label>
                                <input type="file" id="resume_file" name="resume_file" accept=".pdf,.doc,.docx" required>
                                <p class="description"><?php _e('Accepted formats: PDF, DOC, DOCX (Max 5MB)', 'sleeve-ke'); ?></p>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-apply">
                                    <?php _e('Submit Application', 'sleeve-ke'); ?>
                                </button>
                            </div>

                            <div class="application-message"></div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="job-sidebar">
            <!-- Job Overview Card -->
            <div class="sidebar-card job-overview-card">
                <h4><?php _e('Job Overview', 'sleeve-ke'); ?></h4>
                <ul class="job-details-list">
                    <?php if (!empty($job['salary_range'])): ?>
                    <li>
                        <span class="detail-icon dashicons dashicons-money-alt"></span>
                        <div class="detail-content">
                            <strong><?php _e('Salary', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html($job['salary_range']); ?></span>
                        </div>
                    </li>
                    <?php endif; ?>
                    <li>
                        <span class="detail-icon dashicons dashicons-businessman"></span>
                        <div class="detail-content">
                            <strong><?php _e('Job Type', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html(sleeve_ke_get_job_type_label($job['job_type'])); ?></span>
                        </div>
                    </li>
                    <li>
                        <span class="detail-icon dashicons dashicons-location"></span>
                        <div class="detail-content">
                            <strong><?php _e('Location', 'sleeve-ke'); ?></strong>
                            <span><?php echo esc_html($job['location']); ?></span>
                        </div>
                    </li>
                    <li>
                        <span class="detail-icon dashicons dashicons-calendar-alt"></span>
                        <div class="detail-content">
                            <strong><?php _e('Posted', 'sleeve-ke'); ?></strong>
                            <span><?php echo human_time_diff(strtotime($job['created_at']), current_time('timestamp')) . ' ago'; ?></span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Company Info Card -->
            <?php if ($employer): ?>
            <div class="sidebar-card company-card">
                <h4><?php _e('About Company', 'sleeve-ke'); ?></h4>
                <?php if (!empty($employer['company_logo'])): ?>
                    <img src="<?php echo esc_url($employer['company_logo']); ?>" alt="<?php echo esc_attr($employer['company_name']); ?>" class="company-logo-large">
                <?php endif; ?>
                <h5><?php echo esc_html($employer['company_name']); ?></h5>
                
                <ul class="company-details-list">
                    <?php if (!empty($employer['industry'])): ?>
                    <li>
                        <strong><?php _e('Industry:', 'sleeve-ke'); ?></strong>
                        <span><?php echo esc_html($employer['industry']); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($employer['company_size'])): ?>
                    <li>
                        <strong><?php _e('Company Size:', 'sleeve-ke'); ?></strong>
                        <span><?php echo esc_html($employer['company_size']); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($employer['location'])): ?>
                    <li>
                        <strong><?php _e('Location:', 'sleeve-ke'); ?></strong>
                        <span><?php echo esc_html($employer['location']); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($employer['website'])): ?>
                    <li>
                        <strong><?php _e('Website:', 'sleeve-ke'); ?></strong>
                        <a href="<?php echo esc_url($employer['website']); ?>" target="_blank" rel="noopener"><?php echo esc_html(parse_url($employer['website'], PHP_URL_HOST)); ?></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.sleeve-ke-single-job-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.job-header-section {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 30px;
    margin-bottom: 30px;
}

.back-button {
    margin-bottom: 20px;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #2271b1;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}

.btn-back:hover {
    color: #135e96;
}

.job-title {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 20px 0;
    color: #1e293b;
}

.company-info {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.company-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px;
}

.company-details {
    flex: 1;
}

.company-name {
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 10px 0;
    color: #334155;
}

.job-meta-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    color: #64748b;
    font-size: 14px;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.meta-item .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.job-content-wrapper {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.job-main-content {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 30px;
}

.job-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e2e8f0;
}

.job-section:last-child {
    border-bottom: none;
}

.job-section h3 {
    font-size: 22px;
    font-weight: 600;
    margin: 0 0 15px 0;
    color: #1e293b;
}

.job-description,
.job-requirements,
.job-benefits {
    color: #475569;
    line-height: 1.7;
}

.job-apply-section {
    margin-top: 40px;
    padding-top: 40px;
    border-top: 2px solid #e2e8f0;
}

.apply-prompt {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 30px;
}

.apply-prompt h3 {
    margin-top: 0;
    color: #1e293b;
    font-size: 20px;
}

.apply-prompt.login-required {
    text-align: center;
}

.apply-prompt.already-applied {
    background: #dcfce7;
    border-color: #86efac;
}

.apply-prompt.employer-view {
    background: #fef3c7;
    border-color: #fbbf24;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

.btn-primary {
    background: #2271b1;
    color: #fff;
}

.btn-primary:hover {
    background: #135e96;
    color: #fff;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #fff;
    color: #2271b1;
    border: 2px solid #2271b1;
}

.btn-secondary:hover {
    background: #2271b1;
    color: #fff;
}

.application-form .form-group {
    margin-bottom: 20px;
}

.application-form label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #1e293b;
}

.application-form textarea,
.application-form input[type="file"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-family: inherit;
    font-size: 15px;
}

.application-form textarea {
    resize: vertical;
}

.application-form textarea:focus,
.application-form input:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 3px rgba(34, 113, 177, 0.1);
}

.application-form .description {
    margin: 5px 0 0 0;
    font-size: 13px;
    color: #64748b;
}

.form-actions {
    margin-top: 25px;
}

.btn-apply {
    width: 100%;
}

.application-message {
    margin-top: 15px;
    padding: 12px;
    border-radius: 6px;
    display: none;
}

.application-message.success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
    display: block;
}

.application-message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
    display: block;
}

/* Sidebar */
.job-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 25px;
}

.sidebar-card h4 {
    margin: 0 0 20px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    padding-bottom: 15px;
    border-bottom: 2px solid #e2e8f0;
}

.job-details-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.job-details-list li {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.job-details-list li:last-child {
    border-bottom: none;
}

.detail-icon {
    color: #2271b1;
    font-size: 20px;
    flex-shrink: 0;
}

.detail-content {
    flex: 1;
}

.detail-content strong {
    display: block;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 3px;
}

.detail-content span {
    color: #1e293b;
    font-size: 15px;
}

.company-logo-large {
    width: 100%;
    max-width: 150px;
    height: auto;
    margin: 0 auto 15px;
    display: block;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px;
}

.company-card h5 {
    text-align: center;
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #1e293b;
}

.company-details-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.company-details-list li {
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}

.company-details-list li:last-child {
    border-bottom: none;
}

.company-details-list strong {
    display: block;
    color: #64748b;
    margin-bottom: 3px;
    font-size: 13px;
}

.company-details-list span,
.company-details-list a {
    color: #1e293b;
}

.company-details-list a {
    text-decoration: none;
}

.company-details-list a:hover {
    color: #2271b1;
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 968px) {
    .job-content-wrapper {
        grid-template-columns: 1fr;
    }
    
    .job-sidebar {
        order: -1;
    }
}

@media (max-width: 640px) {
    .job-title {
        font-size: 24px;
    }
    
    .company-info {
        flex-direction: column;
    }
    
    .company-logo {
        width: 60px;
        height: 60px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#sleeve-ke-application-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('.btn-apply');
        var $message = $form.find('.application-message');
        var formData = new FormData(this);
        
        formData.append('action', 'sleeve_ke_submit_application');
        
        $button.prop('disabled', true).text('<?php esc_js(_e('Submitting...', 'sleeve-ke')); ?>');
        $message.removeClass('success error').hide();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').text(response.data.message).show();
                    $form[0].reset();
                    
                    // Reload page after 2 seconds to show "already applied" state
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $message.addClass('error').text(response.data.message).show();
                    $button.prop('disabled', false).text('<?php esc_js(_e('Submit Application', 'sleeve-ke')); ?>');
                }
            },
            error: function() {
                $message.addClass('error').text('<?php esc_js(_e('An error occurred. Please try again.', 'sleeve-ke')); ?>').show();
                $button.prop('disabled', false).text('<?php esc_js(_e('Submit Application', 'sleeve-ke')); ?>');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
