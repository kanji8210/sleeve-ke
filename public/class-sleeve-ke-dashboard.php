<?php
/**
 * Universal Dashboard for logged-in users
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/public
 */

/**
 * Handles universal dashboard display based on user role
 */
class Sleeve_KE_Dashboard {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_shortcode( 'sleeve_ke_dashboard', array( $this, 'dashboard_shortcode' ) );
    }

    /**
     * Dashboard shortcode
     */
    public function dashboard_shortcode( $atts ) {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return $this->render_login_prompt();
        }

        $current_user = wp_get_current_user();
        
        // Check user role and display appropriate dashboard
        if ( in_array( 'employer', $current_user->roles ) ) {
            return $this->render_employer_dashboard( $current_user );
        } elseif ( in_array( 'candidate', $current_user->roles ) ) {
            return $this->render_candidate_dashboard( $current_user );
        } elseif ( in_array( 'administrator', $current_user->roles ) || in_array( 'sleve_admin', $current_user->roles ) ) {
            return $this->render_admin_dashboard( $current_user );
        } else {
            return '<div class="sleeve-ke-message error">' . __( 'Access denied. Please register as an employer or candidate.', 'sleeve-ke' ) . '</div>';
        }
    }

    /**
     * Render login prompt for non-logged-in users
     */
    private function render_login_prompt() {
        ob_start();
        ?>
        <div class="sleeve-ke-dashboard-login-prompt">
            <div class="login-prompt-card">
                <div class="prompt-icon">🔐</div>
                <h2><?php _e( 'Welcome to Sleeve KE', 'sleeve-ke' ); ?></h2>
                <p class="main-message"><?php _e( 'Please login or register to view your dashboard', 'sleeve-ke' ); ?></p>
                
                <div class="login-actions">
                    <a href="<?php echo wp_login_url( get_permalink() ); ?>" class="btn btn-primary btn-large">
                        <span class="btn-icon">🔓</span>
                        <span class="btn-label"><?php _e( 'Log In', 'sleeve-ke' ); ?></span>
                    </a>
                </div>
                
                <div class="divider">
                    <span><?php _e( 'OR', 'sleeve-ke' ); ?></span>
                </div>
                
                <div class="register-links">
                    <p class="register-title"><?php _e( "New to Sleeve KE? Create an account:", 'sleeve-ke' ); ?></p>
                    <div class="register-buttons">
                        <a href="<?php echo home_url( '/employer-registration' ); ?>" class="btn btn-secondary btn-register">
                            <span class="btn-icon">🏢</span>
                            <div class="btn-content">
                                <span class="btn-label"><?php _e( 'Register as Employer', 'sleeve-ke' ); ?></span>
                                <span class="btn-desc"><?php _e( 'Post jobs & hire talent', 'sleeve-ke' ); ?></span>
                            </div>
                        </a>
                        <a href="<?php echo home_url( '/candidate-registration' ); ?>" class="btn btn-secondary btn-register">
                            <span class="btn-icon">👤</span>
                            <div class="btn-content">
                                <span class="btn-label"><?php _e( 'Register as Candidate', 'sleeve-ke' ); ?></span>
                                <span class="btn-desc"><?php _e( 'Find your dream job', 'sleeve-ke' ); ?></span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .sleeve-ke-dashboard-login-prompt {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .login-prompt-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .prompt-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .login-prompt-card h2 {
            margin: 0 0 15px 0;
            color: #2271b1;
            font-size: 28px;
        }
        .main-message {
            color: #444;
            font-size: 16px;
            margin-bottom: 30px;
            font-weight: 500;
        }
        .login-actions {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin: 5px;
            border: 2px solid transparent;
        }
        .btn-large {
            padding: 16px 40px;
            font-size: 16px;
            min-width: 200px;
        }
        .btn-icon {
            font-size: 20px;
        }
        .btn-primary {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }
        .btn-primary:hover {
            background: #135e96;
            border-color: #135e96;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(34,113,177,0.3);
        }
        .divider {
            margin: 30px 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }
        .divider span {
            background: #fff;
            padding: 0 15px;
            position: relative;
            color: #999;
            font-size: 13px;
            font-weight: 600;
        }
        .register-links {
            margin-top: 30px;
        }
        .register-title {
            margin: 0 0 20px 0;
            color: #666;
            font-size: 15px;
            font-weight: 600;
        }
        .register-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .btn-register {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 15px;
            background: #f8f9fa;
            color: #2c3338;
            border: 2px solid #e0e0e0;
            min-height: 120px;
            justify-content: center;
        }
        .btn-register:hover {
            background: #fff;
            border-color: #2271b1;
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        .btn-register .btn-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .btn-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .btn-label {
            font-size: 15px;
            font-weight: 700;
            color: #2c3338;
            margin-bottom: 5px;
        }
        .btn-desc {
            font-size: 12px;
            color: #666;
            font-weight: 400;
        }
        @media (max-width: 600px) {
            .register-buttons {
                grid-template-columns: 1fr;
            }
            .login-prompt-card {
                padding: 30px 20px;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Render employer dashboard
     */
    private function render_employer_dashboard( $user ) {
        global $wpdb;
        
        // Get employer data
        $employers_table = $wpdb->prefix . 'sleeve_employers';
        $employer = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $employers_table WHERE user_id = %d",
            $user->ID
        ), ARRAY_A );
        
        // Get employer's jobs count
        $jobs_table = $wpdb->prefix . 'sleeve_jobs';
        $jobs_stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as active_jobs,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_jobs
            FROM $jobs_table WHERE employer_id = %d",
            $user->ID
        ), ARRAY_A );
        
        // Get applications count
        $applications_table = $wpdb->prefix . 'sleeve_applications';
        $apps_stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_applications,
                SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN a.status = 'interview' THEN 1 ELSE 0 END) as interviews
            FROM $applications_table a
            LEFT JOIN $jobs_table j ON a.job_id = j.id
            WHERE j.employer_id = %d",
            $user->ID
        ), ARRAY_A );
        
        // Get recent applications
        $recent_applications = $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, j.title as job_title, u.display_name as candidate_name
            FROM $applications_table a
            LEFT JOIN $jobs_table j ON a.job_id = j.id
            LEFT JOIN {$wpdb->users} u ON a.candidate_id = u.ID
            WHERE j.employer_id = %d
            ORDER BY a.applied_at DESC
            LIMIT 5",
            $user->ID
        ), ARRAY_A );
        
        ob_start();
        ?>
        <div class="sleeve-ke-dashboard employer-dashboard">
            <div class="dashboard-header">
                <h1><?php _e( 'Employer Dashboard', 'sleeve-ke' ); ?></h1>
                <p class="welcome-message">
                    <?php printf( __( 'Welcome back, %s!', 'sleeve-ke' ), '<strong>' . esc_html( $user->display_name ) . '</strong>' ); ?>
                    <?php if ( $employer ) : ?>
                        <span class="company-name">(<?php echo esc_html( $employer['company_name'] ); ?>)</span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Quick Stats -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo esc_html( $jobs_stats['total_jobs'] ); ?></span>
                        <span class="stat-label"><?php _e( 'Total Jobs', 'sleeve-ke' ); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo esc_html( $jobs_stats['active_jobs'] ); ?></span>
                        <span class="stat-label"><?php _e( 'Active Jobs', 'sleeve-ke' ); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo esc_html( $apps_stats['total_applications'] ); ?></span>
                        <span class="stat-label"><?php _e( 'Applications', 'sleeve-ke' ); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo esc_html( $apps_stats['pending'] ); ?></span>
                        <span class="stat-label"><?php _e( 'Pending Review', 'sleeve-ke' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-actions">
                <h2><?php _e( 'Quick Actions', 'sleeve-ke' ); ?></h2>
                <div class="action-buttons">
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-employer-jobs&action=add' ); ?>" class="action-btn primary">
                        <span class="btn-icon">➕</span>
                        <span class="btn-text"><?php _e( 'Post New Job', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-employer-jobs' ); ?>" class="action-btn">
                        <span class="btn-icon">📝</span>
                        <span class="btn-text"><?php _e( 'Manage Jobs', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo home_url( '/employer-applications' ); ?>" class="action-btn">
                        <span class="btn-icon">📬</span>
                        <span class="btn-text"><?php _e( 'View Applications', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo home_url( '/employer-profile' ); ?>" class="action-btn">
                        <span class="btn-icon">⚙️</span>
                        <span class="btn-text"><?php _e( 'Edit Profile', 'sleeve-ke' ); ?></span>
                    </a>
                </div>
            </div>

            <!-- Recent Applications -->
            <?php if ( ! empty( $recent_applications ) ) : ?>
            <div class="dashboard-section recent-applications">
                <div class="section-header">
                    <h2><?php _e( 'Recent Applications', 'sleeve-ke' ); ?></h2>
                    <a href="<?php echo home_url( '/employer-applications' ); ?>" class="view-all"><?php _e( 'View All', 'sleeve-ke' ); ?> →</a>
                </div>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Candidate', 'sleeve-ke' ); ?></th>
                            <th><?php _e( 'Job Position', 'sleeve-ke' ); ?></th>
                            <th><?php _e( 'Date', 'sleeve-ke' ); ?></th>
                            <th><?php _e( 'Status', 'sleeve-ke' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $recent_applications as $app ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $app['candidate_name'] ); ?></strong></td>
                            <td><?php echo esc_html( $app['job_title'] ); ?></td>
                            <td><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $app['applied_at'] ) ) ); ?></td>
                            <td><span class="status-badge status-<?php echo esc_attr( $app['status'] ); ?>"><?php echo esc_html( ucfirst( $app['status'] ) ); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean() . $this->get_dashboard_styles();
    }

    /**
     * Render candidate dashboard
     */
    private function render_candidate_dashboard( $user ) {
        global $wpdb;
        
        // Get candidate data
        $candidates_table = $wpdb->prefix . 'sleeve_candidates';
        $candidate = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $candidates_table WHERE user_id = %d",
            $user->ID
        ), ARRAY_A );
        
        // Get application statistics
        $applications_table = $wpdb->prefix . 'sleeve_applications';
        $apps_stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_applications,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'interview' THEN 1 ELSE 0 END) as interviews,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted
            FROM $applications_table WHERE candidate_id = %d",
            $user->ID
        ), ARRAY_A );
        
        // Get recent applications
        $jobs_table = $wpdb->prefix . 'sleeve_jobs';
        $recent_applications = $wpdb->get_results( $wpdb->prepare(
            "SELECT a.*, j.title as job_title, j.location, e.company_name
            FROM $applications_table a
            LEFT JOIN $jobs_table j ON a.job_id = j.id
            LEFT JOIN {$wpdb->prefix}sleeve_employers e ON j.employer_id = e.user_id
            WHERE a.candidate_id = %d
            ORDER BY a.applied_at DESC
            LIMIT 5",
            $user->ID
        ), ARRAY_A );
        
        // Get saved jobs count
        $saved_jobs = get_user_meta( $user->ID, 'saved_jobs', true );
        $saved_count = is_array( $saved_jobs ) ? count( $saved_jobs ) : 0;
        
        ob_start();
        ?>
        <div class="sleeve-ke-dashboard candidate-dashboard">
            <div class="dashboard-header">
                <h1><?php _e( 'Candidate Dashboard', 'sleeve-ke' ); ?></h1>
                <p class="welcome-message">
                    <?php printf( __( 'Welcome back, %s!', 'sleeve-ke' ), '<strong>' . esc_html( $user->display_name ) . '</strong>' ); ?>
                </p>
            </div>

            <!-- Profile Completion -->
            <?php
            $profile_completion = 0;
            $total_fields = 8;
            if ( ! empty( $user->first_name ) ) $profile_completion++;
            if ( ! empty( $user->last_name ) ) $profile_completion++;
            if ( $candidate ) {
                if ( ! empty( $candidate['phone'] ) ) $profile_completion++;
                if ( ! empty( $candidate['location'] ) ) $profile_completion++;
                if ( ! empty( $candidate['experience_years'] ) ) $profile_completion++;
                if ( ! empty( $candidate['education'] ) ) $profile_completion++;
                if ( ! empty( $candidate['skills'] ) ) $profile_completion++;
                if ( ! empty( $candidate['resume_url'] ) ) $profile_completion++;
            }
            $completion_percent = round( ( $profile_completion / $total_fields ) * 100 );
            ?>
            
            <?php if ( $completion_percent < 100 ) : ?>
            <div class="profile-completion-notice">
                <strong><?php _e( 'Complete Your Profile', 'sleeve-ke' ); ?></strong>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo esc_attr( $completion_percent ); ?>%;"></div>
                </div>
                <p><?php printf( __( 'Your profile is %d%% complete. Complete your profile to get better job matches!', 'sleeve-ke' ), $completion_percent ); ?></p>
                <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-candidates&action=edit&id=' . ( $candidate['id'] ?? 0 ) ); ?>" class="btn-complete-profile">
                    <?php _e( 'Complete Profile', 'sleeve-ke' ); ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Quick Stats -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo esc_html( $apps_stats['total_applications'] ); ?></span>
                        <span class="stat-label"><?php _e( 'Total Applications', 'sleeve-ke' ); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo esc_html( $apps_stats['pending'] ); ?></span>
                        <span class="stat-label"><?php _e( 'Pending', 'sleeve-ke' ); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo esc_html( $apps_stats['interviews'] ); ?></span>
                        <span class="stat-label"><?php _e( 'Interviews', 'sleeve-ke' ); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <span class="stat-number"><?php echo esc_html( $saved_count ); ?></span>
                        <span class="stat-label"><?php _e( 'Saved Jobs', 'sleeve-ke' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-actions">
                <h2><?php _e( 'Quick Actions', 'sleeve-ke' ); ?></h2>
                <div class="action-buttons">
                    <a href="<?php echo home_url( '/jobs' ); ?>" class="action-btn primary">
                        <span class="btn-icon">🔍</span>
                        <span class="btn-text"><?php _e( 'Browse Jobs', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-my-applications' ); ?>" class="action-btn">
                        <span class="btn-icon">📬</span>
                        <span class="btn-text"><?php _e( 'My Applications', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-saved-jobs' ); ?>" class="action-btn">
                        <span class="btn-icon">💾</span>
                        <span class="btn-text"><?php _e( 'Saved Jobs', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-candidates&action=edit&id=' . ( $candidate['id'] ?? 0 ) ); ?>" class="action-btn">
                        <span class="btn-icon">👤</span>
                        <span class="btn-text"><?php _e( 'Edit Profile', 'sleeve-ke' ); ?></span>
                    </a>
                </div>
            </div>

            <!-- Recent Applications -->
            <?php if ( ! empty( $recent_applications ) ) : ?>
            <div class="dashboard-section recent-applications">
                <div class="section-header">
                    <h2><?php _e( 'Recent Applications', 'sleeve-ke' ); ?></h2>
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-my-applications' ); ?>" class="view-all"><?php _e( 'View All', 'sleeve-ke' ); ?> →</a>
                </div>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th><?php _e( 'Job Position', 'sleeve-ke' ); ?></th>
                            <th><?php _e( 'Company', 'sleeve-ke' ); ?></th>
                            <th><?php _e( 'Applied Date', 'sleeve-ke' ); ?></th>
                            <th><?php _e( 'Status', 'sleeve-ke' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $recent_applications as $app ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $app['job_title'] ); ?></strong><br><small><?php echo esc_html( $app['location'] ); ?></small></td>
                            <td><?php echo esc_html( $app['company_name'] ); ?></td>
                            <td><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $app['applied_at'] ) ) ); ?></td>
                            <td><span class="status-badge status-<?php echo esc_attr( $app['status'] ); ?>"><?php echo esc_html( ucfirst( $app['status'] ) ); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else : ?>
            <div class="dashboard-section no-applications">
                <h3><?php _e( 'No Applications Yet', 'sleeve-ke' ); ?></h3>
                <p><?php _e( 'Start applying to jobs to see your applications here.', 'sleeve-ke' ); ?></p>
                <a href="<?php echo home_url( '/jobs' ); ?>" class="btn btn-primary"><?php _e( 'Browse Jobs', 'sleeve-ke' ); ?></a>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean() . $this->get_dashboard_styles();
    }

    /**
     * Render admin dashboard
     */
    private function render_admin_dashboard( $user ) {
        ob_start();
        ?>
        <div class="sleeve-ke-dashboard admin-dashboard">
            <div class="dashboard-header">
                <h1><?php _e( 'Admin Dashboard', 'sleeve-ke' ); ?></h1>
                <p class="welcome-message">
                    <?php printf( __( 'Welcome, %s!', 'sleeve-ke' ), '<strong>' . esc_html( $user->display_name ) . '</strong>' ); ?>
                </p>
            </div>

            <div class="dashboard-actions">
                <h2><?php _e( 'Admin Panel', 'sleeve-ke' ); ?></h2>
                <div class="action-buttons">
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-jobs' ); ?>" class="action-btn primary">
                        <span class="btn-icon">📋</span>
                        <span class="btn-text"><?php _e( 'Manage Jobs', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-candidates' ); ?>" class="action-btn">
                        <span class="btn-icon">👥</span>
                        <span class="btn-text"><?php _e( 'Manage Candidates', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-employers' ); ?>" class="action-btn">
                        <span class="btn-icon">🏢</span>
                        <span class="btn-text"><?php _e( 'Manage Employers', 'sleeve-ke' ); ?></span>
                    </a>
                    <a href="<?php echo admin_url( 'admin.php?page=sleeve-ke-applications' ); ?>" class="action-btn">
                        <span class="btn-icon">📬</span>
                        <span class="btn-text"><?php _e( 'Manage Applications', 'sleeve-ke' ); ?></span>
                    </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean() . $this->get_dashboard_styles();
    }

    /**
     * Get dashboard styles
     */
    private function get_dashboard_styles() {
        return '
        <style>
        .sleeve-ke-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .dashboard-header {
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            margin: 0 0 10px 0;
            color: #2271b1;
        }
        .welcome-message {
            color: #666;
            font-size: 16px;
        }
        .company-name {
            color: #2271b1;
            font-weight: normal;
        }
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .stat-icon {
            font-size: 36px;
        }
        .stat-content {
            flex: 1;
        }
        .stat-number {
            display: block;
            font-size: 28px;
            font-weight: bold;
            color: #2271b1;
        }
        .stat-label {
            display: block;
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
        }
        .dashboard-actions {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .dashboard-actions h2 {
            margin: 0 0 20px 0;
            font-size: 18px;
        }
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .action-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            background: #f0f0f1;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-decoration: none;
            color: #2c3338;
            transition: all 0.3s;
        }
        .action-btn:hover {
            background: #e0e0e1;
            transform: translateY(-2px);
        }
        .action-btn.primary {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }
        .action-btn.primary:hover {
            background: #135e96;
        }
        .btn-icon {
            font-size: 24px;
        }
        .btn-text {
            font-weight: 600;
        }
        .dashboard-section {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .section-header h2 {
            margin: 0;
            font-size: 18px;
        }
        .view-all {
            color: #2271b1;
            text-decoration: none;
            font-weight: 600;
        }
        .view-all:hover {
            text-decoration: underline;
        }
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        .dashboard-table th {
            background: #f6f7f7;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        .dashboard-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .dashboard-table tr:hover {
            background: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.status-pending { background: #f0b849; color: #fff; }
        .status-badge.status-reviewed { background: #72aee6; color: #fff; }
        .status-badge.status-interview { background: #8c8f94; color: #fff; }
        .status-badge.status-accepted { background: #00a32a; color: #fff; }
        .status-badge.status-rejected { background: #d63638; color: #fff; }
        .profile-completion-notice {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .profile-completion-notice strong {
            display: block;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .progress-bar {
            background: rgba(255,255,255,0.3);
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        .progress-fill {
            background: #fff;
            height: 100%;
            transition: width 0.3s;
        }
        .profile-completion-notice p {
            margin: 10px 0;
        }
        .btn-complete-profile {
            display: inline-block;
            padding: 10px 20px;
            background: #fff;
            color: #667eea;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
        }
        .btn-complete-profile:hover {
            background: #f0f0f1;
        }
        .no-applications {
            text-align: center;
            padding: 40px;
        }
        .no-applications h3 {
            color: #666;
            margin-bottom: 10px;
        }
        .no-applications p {
            color: #999;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-primary {
            background: #2271b1;
            color: #fff;
        }
        .btn-primary:hover {
            background: #135e96;
        }
        @media (max-width: 768px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
        </style>';
    }
}

// Initialize
new Sleeve_KE_Dashboard();
