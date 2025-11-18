<?php
/**
 * Employer profile display functionality.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/public
 */

/**
 * Employer profile display class.
 *
 * Handles the display of employer public profiles
 */
class Sleeve_KE_Employer_Profile {

    /**
     * Initialize the class
     */
    public function __construct() {
        // Register shortcode
        add_shortcode('sleeve_ke_employer_profile', array($this, 'employer_profile_shortcode'));
    }

    /**
     * Employer profile shortcode
     */
    public function employer_profile_shortcode($atts) {
        // Check if viewing specific user (from admin or URL parameter)
        $view_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        
        // If no specific user, must be logged in
        if (!$view_user_id && !is_user_logged_in()) {
            return '<div class="sleeve-ke-notice error">' . 
                   __('You must be logged in to view your profile.', 'sleeve-ke') . 
                   ' <a href="' . wp_login_url(get_permalink()) . '">' . 
                   __('Login here', 'sleeve-ke') . '</a></div>';
        }

        $current_user = wp_get_current_user();
        
        // Determine which user to display
        if ($view_user_id) {
            // Viewing a specific employer (admin created or direct link)
            $display_user_id = $view_user_id;
            $is_own_profile = ($current_user->ID == $view_user_id);
        } else {
            // Viewing own profile
            $display_user_id = $current_user->ID;
            $is_own_profile = true;
            
            // Check if current user is an employer
            if (!in_array('employer', $current_user->roles)) {
                return '<div class="sleeve-ke-notice error">' . 
                       __('This page is only accessible to employers.', 'sleeve-ke') . 
                       '</div>';
            }
        }

        // Get employer data
        global $wpdb;
        $employers_table = $wpdb->prefix . 'sleeve_employers';
        $employer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$employers_table} WHERE user_id = %d",
            $display_user_id
        ));

        // Show success message if just registered
        $success_message = '';
        if (isset($_GET['registered']) && $_GET['registered'] == 1) {
            $success_message = '<div class="sleeve-ke-notice success">
                <h3>🎉 ' . __('Welcome to Sleeve KE!', 'sleeve-ke') . '</h3>
                <p>' . __('Your employer account has been created successfully. Your profile is pending approval.', 'sleeve-ke') . '</p>
                <p>' . __('You can start setting up your company profile and prepare job postings. Once approved, your jobs will be visible to candidates.', 'sleeve-ke') . '</p>
            </div>';
        } elseif (isset($_GET['employer_registered']) && $_GET['employer_registered'] == 1) {
            $success_message = '<div class="sleeve-ke-notice success">
                <h3>🎉 ' . __('Welcome to Sleeve KE!', 'sleeve-ke') . '</h3>
                <p>' . __('Your employer account has been created successfully. Your profile is pending approval.', 'sleeve-ke') . '</p>
                <p>' . __('You can start setting up your company profile and prepare job postings. Once approved, your jobs will be visible to candidates.', 'sleeve-ke') . '</p>
            </div>';
        }

        ob_start();
        ?>
        <div class="sleeve-ke-employer-profile">
            <?php echo $success_message; ?>
            
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if (!empty($employer->company_logo)): ?>
                        <img src="<?php echo esc_url($employer->company_logo); ?>" 
                             alt="<?php echo esc_attr($employer->company_name); ?>" 
                             class="company-logo" />
                    <?php else: ?>
                        <div class="company-logo-placeholder">
                            <?php echo esc_html(substr($employer->company_name, 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h1 class="company-name"><?php echo esc_html($employer->company_name); ?></h1>
                    <p class="profile-meta">
                        <span class="location">📍 <?php echo esc_html($employer->location); ?></span>
                        <?php if ($employer->website): ?>
                            <span class="website">
                                🔗 <a href="<?php echo esc_url($employer->website); ?>" target="_blank">
                                    <?php echo esc_html($employer->website); ?>
                                </a>
                            </span>
                        <?php endif; ?>
                    </p>
                    <div class="profile-status">
                        <?php
                        $status = get_user_meta($current_user->ID, 'employer_status', true);
                        $status_class = $status === 'approved' ? 'approved' : 'pending';
                        $status_text = $status === 'approved' ? __('Approved', 'sleeve-ke') : __('Pending Approval', 'sleeve-ke');
                        ?>
                        <span class="status-badge status-<?php echo esc_attr($status_class); ?>">
                            <?php echo esc_html($status_text); ?>
                        </span>
                        <span class="subscription-badge">
                            <?php 
                            $plan = get_user_meta($current_user->ID, 'subscription_plan', true) ?: 'free';
                            echo esc_html(ucfirst($plan) . ' ' . __('Plan', 'sleeve-ke')); 
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <div class="profile-section">
                    <h2><?php esc_html_e('Company Information', 'sleeve-ke'); ?></h2>
                    <table class="profile-table">
                        <tr>
                            <th><?php esc_html_e('Industry:', 'sleeve-ke'); ?></th>
                            <td><?php echo esc_html($employer->industry); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Company Size:', 'sleeve-ke'); ?></th>
                            <td><?php echo esc_html($employer->company_size); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Phone:', 'sleeve-ke'); ?></th>
                            <td><?php echo esc_html($employer->phone); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Email:', 'sleeve-ke'); ?></th>
                            <td><?php echo esc_html($current_user->user_email); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Member Since:', 'sleeve-ke'); ?></th>
                            <td><?php echo date('F j, Y', strtotime($current_user->user_registered)); ?></td>
                        </tr>
                    </table>
                </div>

                <?php if (!empty($employer->company_description)): ?>
                <div class="profile-section">
                    <h2><?php esc_html_e('About Company', 'sleeve-ke'); ?></h2>
                    <div class="company-description">
                        <?php echo wp_kses_post(wpautop($employer->company_description)); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="profile-section">
                    <h2><?php esc_html_e('Quick Actions', 'sleeve-ke'); ?></h2>
                    <div class="profile-actions">
                        <a href="<?php echo admin_url('admin.php?page=sleeve-ke-employer-jobs&action=add'); ?>" 
                           class="btn btn-primary">
                            ➕ <?php esc_html_e('Post a New Job', 'sleeve-ke'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=sleeve-ke-employer-jobs'); ?>" 
                           class="btn btn-secondary">
                            📋 <?php esc_html_e('Manage My Jobs', 'sleeve-ke'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=sleeve-ke-employer-applications'); ?>" 
                           class="btn btn-secondary">
                            📄 <?php esc_html_e('View Applications', 'sleeve-ke'); ?>
                        </a>
                        <a href="<?php echo admin_url('profile.php'); ?>" 
                           class="btn btn-secondary">
                            ⚙️ <?php esc_html_e('Edit Profile', 'sleeve-ke'); ?>
                        </a>
                    </div>
                </div>

                <?php
                // Get employer's jobs
                $jobs = get_posts(array(
                    'post_type' => 'job',
                    'author' => $current_user->ID,
                    'posts_per_page' => 5,
                    'post_status' => 'any'
                ));
                
                if (!empty($jobs)):
                ?>
                <div class="profile-section">
                    <h2><?php esc_html_e('Recent Jobs', 'sleeve-ke'); ?></h2>
                    <div class="jobs-list">
                        <?php foreach ($jobs as $job): ?>
                            <div class="job-item">
                                <h3>
                                    <a href="<?php echo get_permalink($job->ID); ?>">
                                        <?php echo esc_html($job->post_title); ?>
                                    </a>
                                </h3>
                                <div class="job-meta">
                                    <span class="job-status status-<?php echo esc_attr($job->post_status); ?>">
                                        <?php echo esc_html(ucfirst($job->post_status)); ?>
                                    </span>
                                    <span class="job-date">
                                        <?php echo human_time_diff(strtotime($job->post_date), current_time('timestamp')) . ' ' . __('ago', 'sleeve-ke'); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?php echo admin_url('admin.php?page=sleeve-ke-employer-jobs'); ?>" class="view-all-link">
                        <?php esc_html_e('View All Jobs →', 'sleeve-ke'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <style>
        .sleeve-ke-employer-profile {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .sleeve-ke-notice {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .sleeve-ke-notice.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .sleeve-ke-notice.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .profile-header {
            display: flex;
            gap: 30px;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .profile-avatar .company-logo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .company-logo-placeholder {
            width: 150px;
            height: 150px;
            background: #007cba;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            font-weight: bold;
            border-radius: 8px;
        }
        .company-name {
            margin: 0 0 10px 0;
            font-size: 32px;
        }
        .profile-meta {
            display: flex;
            gap: 20px;
            margin: 10px 0;
            color: #666;
        }
        .profile-status {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .status-badge, .subscription-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-badge.status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-badge.status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .subscription-badge {
            background: #e7f3ff;
            color: #004085;
        }
        .profile-content {
            display: grid;
            gap: 20px;
        }
        .profile-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-section h2 {
            margin-top: 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .profile-table {
            width: 100%;
            margin-top: 20px;
        }
        .profile-table th {
            text-align: left;
            padding: 10px;
            font-weight: 600;
            width: 200px;
        }
        .profile-table td {
            padding: 10px;
        }
        .profile-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007cba;
            color: white;
        }
        .btn-primary:hover {
            background: #005a87;
            color: white;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .jobs-list {
            margin-top: 20px;
        }
        .job-item {
            padding: 15px;
            border-left: 3px solid #007cba;
            background: #f9f9f9;
            margin-bottom: 10px;
        }
        .job-item h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .job-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #666;
        }
        .view-all-link {
            display: inline-block;
            margin-top: 15px;
            color: #007cba;
            font-weight: 600;
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
