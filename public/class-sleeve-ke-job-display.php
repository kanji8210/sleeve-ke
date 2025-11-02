<?php

/**
 * The job display functionality of the plugin.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/public
 */

/**
 * Job display and filtering class.
 *
 * Defines the plugin name, version, and handles job listing display with filters and search.
 */
class Sleeve_KE_Job_Display {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Register job post type
        add_action( 'init', array( $this, 'register_job_post_type' ) );
        
        // Register shortcode
        add_shortcode( 'sleeve_ke_jobs', array( $this, 'jobs_shortcode' ) );
        
        // Enqueue frontend styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        
        // Handle AJAX requests
        add_action( 'wp_ajax_sleeve_ke_filter_jobs', array( $this, 'ajax_filter_jobs' ) );
        add_action( 'wp_ajax_nopriv_sleeve_ke_filter_jobs', array( $this, 'ajax_filter_jobs' ) );
        
        // Handle job save/unsave AJAX requests
        add_action( 'wp_ajax_sleeve_ke_save_job', array( $this, 'ajax_save_job' ) );
        add_action( 'wp_ajax_sleeve_ke_unsave_job', array( $this, 'ajax_unsave_job' ) );
    }

    /**
     * Register job post type
     */
    public function register_job_post_type() {
        $labels = array(
            'name'                  => _x( 'Jobs', 'Post type general name', 'sleeve-ke' ),
            'singular_name'         => _x( 'Job', 'Post type singular name', 'sleeve-ke' ),
            'menu_name'             => _x( 'Jobs', 'Admin Menu text', 'sleeve-ke' ),
            'name_admin_bar'        => _x( 'Job', 'Add New on Toolbar', 'sleeve-ke' ),
            'add_new'               => __( 'Add New', 'sleeve-ke' ),
            'add_new_item'          => __( 'Add New Job', 'sleeve-ke' ),
            'new_item'              => __( 'New Job', 'sleeve-ke' ),
            'edit_item'             => __( 'Edit Job', 'sleeve-ke' ),
            'view_item'             => __( 'View Job', 'sleeve-ke' ),
            'all_items'             => __( 'All Jobs', 'sleeve-ke' ),
            'search_items'          => __( 'Search Jobs', 'sleeve-ke' ),
            'parent_item_colon'     => __( 'Parent Jobs:', 'sleeve-ke' ),
            'not_found'             => __( 'No jobs found.', 'sleeve-ke' ),
            'not_found_in_trash'    => __( 'No jobs found in Trash.', 'sleeve-ke' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // Managed by admin class
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'job' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'job', $args );
        
        // Create sample jobs if none exist (only in development)
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            add_action( 'wp_loaded', array( $this, 'create_sample_jobs' ) );
        }
    }

    /**
     * Create sample jobs for testing (only runs once)
     */
    public function create_sample_jobs() {
        // Check if sample jobs already exist
        if ( get_option( 'sleeve_ke_sample_jobs_created', false ) ) {
            return;
        }

        $job_types = array( 'full-time', 'part-time', 'contract', 'freelance' );
        $experience_levels = array( 'entry', 'mid', 'senior', 'executive' );
        $remote_options = array( 'yes', 'no', 'hybrid' );
        $locations = array( 
            'Nairobi, Kenya', 'Mombasa, Kenya', 'Kisumu, Kenya', 'Nakuru, Kenya', 
            'Eldoret, Kenya', 'Thika, Kenya', 'Machakos, Kenya', 'Nyeri, Kenya' 
        );
        $companies = array(
            'TechCorp Kenya', 'Digital Solutions Ltd', 'Creative Agency', 'InnovateHub',
            'StartupVentures', 'GlobalTech Kenya', 'LocalBusiness Co', 'Enterprise Systems',
            'WebDevelopers Inc', 'DataAnalytics Pro', 'CloudSolutions Ltd', 'MobileTech Kenya',
            'EcommercePlatform', 'FinTech Innovations', 'HealthTech Solutions', 'EduTech Kenya'
        );

        $sample_jobs = array(
            // Tech Jobs
            array( 'title' => 'Senior PHP Developer', 'content' => 'We are looking for an experienced PHP developer to join our dynamic team...', 'type' => 'full-time', 'level' => 'senior', 'remote' => 'no', 'salary_min' => 80000, 'salary_max' => 120000 ),
            array( 'title' => 'Frontend Developer (React)', 'content' => 'Join our frontend team and help build amazing user interfaces with React...', 'type' => 'full-time', 'level' => 'mid', 'remote' => 'yes', 'salary_min' => 60000, 'salary_max' => 90000 ),
            array( 'title' => 'Full Stack JavaScript Developer', 'content' => 'Looking for a full stack developer proficient in Node.js and React...', 'type' => 'full-time', 'level' => 'mid', 'remote' => 'hybrid', 'salary_min' => 70000, 'salary_max' => 100000 ),
            array( 'title' => 'Python Data Scientist', 'content' => 'Join our data team to analyze complex datasets and build ML models...', 'type' => 'full-time', 'level' => 'senior', 'remote' => 'yes', 'salary_min' => 90000, 'salary_max' => 130000 ),
            array( 'title' => 'Mobile App Developer (Flutter)', 'content' => 'Develop cross-platform mobile applications using Flutter framework...', 'type' => 'contract', 'level' => 'mid', 'remote' => 'yes', 'salary_min' => 50000, 'salary_max' => 80000 ),
            array( 'title' => 'DevOps Engineer', 'content' => 'Manage our cloud infrastructure and deployment pipelines...', 'type' => 'full-time', 'level' => 'senior', 'remote' => 'hybrid', 'salary_min' => 85000, 'salary_max' => 125000 ),
            array( 'title' => 'UI/UX Designer', 'content' => 'Create intuitive and beautiful user interfaces for web and mobile apps...', 'type' => 'part-time', 'level' => 'mid', 'remote' => 'yes', 'salary_min' => 40000, 'salary_max' => 70000 ),
            array( 'title' => 'Junior Web Developer', 'content' => 'Great opportunity for a junior developer to grow their skills...', 'type' => 'full-time', 'level' => 'entry', 'remote' => 'no', 'salary_min' => 35000, 'salary_max' => 55000 ),
            
            // Marketing Jobs
            array( 'title' => 'Digital Marketing Manager', 'content' => 'Lead our digital marketing efforts across all channels...', 'type' => 'full-time', 'level' => 'senior', 'remote' => 'hybrid', 'salary_min' => 60000, 'salary_max' => 90000 ),
            array( 'title' => 'Content Marketing Specialist', 'content' => 'Create engaging content for our blog, social media, and email campaigns...', 'type' => 'part-time', 'level' => 'mid', 'remote' => 'yes', 'salary_min' => 35000, 'salary_max' => 55000 ),
            array( 'title' => 'SEO Specialist', 'content' => 'Optimize our website and content for search engines...', 'type' => 'freelance', 'level' => 'mid', 'remote' => 'yes', 'salary_min' => 30000, 'salary_max' => 50000 ),
            array( 'title' => 'Social Media Manager', 'content' => 'Manage our social media presence across multiple platforms...', 'type' => 'part-time', 'level' => 'entry', 'remote' => 'yes', 'salary_min' => 25000, 'salary_max' => 40000 ),
            
            // Business Jobs
            array( 'title' => 'Project Manager', 'content' => 'Lead cross-functional teams to deliver projects on time and budget...', 'type' => 'full-time', 'level' => 'senior', 'remote' => 'hybrid', 'salary_min' => 70000, 'salary_max' => 100000 ),
            array( 'title' => 'Business Analyst', 'content' => 'Analyze business processes and recommend improvements...', 'type' => 'full-time', 'level' => 'mid', 'remote' => 'no', 'salary_min' => 55000, 'salary_max' => 80000 ),
            array( 'title' => 'Sales Representative', 'content' => 'Generate leads and close deals with potential clients...', 'type' => 'full-time', 'level' => 'entry', 'remote' => 'no', 'salary_min' => 40000, 'salary_max' => 60000 ),
            array( 'title' => 'Customer Success Manager', 'content' => 'Ensure our customers achieve their goals using our products...', 'type' => 'full-time', 'level' => 'mid', 'remote' => 'hybrid', 'salary_min' => 50000, 'salary_max' => 75000 ),
            
            // Design Jobs
            array( 'title' => 'Graphic Designer', 'content' => 'Create visual content for print and digital media...', 'type' => 'part-time', 'level' => 'entry', 'remote' => 'yes', 'salary_min' => 30000, 'salary_max' => 50000 ),
            array( 'title' => 'Brand Designer', 'content' => 'Develop and maintain brand identity across all touchpoints...', 'type' => 'contract', 'level' => 'senior', 'remote' => 'yes', 'salary_min' => 60000, 'salary_max' => 90000 ),
            array( 'title' => 'Video Editor', 'content' => 'Edit promotional videos and marketing content...', 'type' => 'freelance', 'level' => 'mid', 'remote' => 'yes', 'salary_min' => 35000, 'salary_max' => 55000 ),
            
            // Finance Jobs
            array( 'title' => 'Financial Analyst', 'content' => 'Analyze financial data and create reports for management...', 'type' => 'full-time', 'level' => 'mid', 'remote' => 'no', 'salary_min' => 55000, 'salary_max' => 80000 ),
            array( 'title' => 'Accounting Assistant', 'content' => 'Support the accounting team with daily financial operations...', 'type' => 'part-time', 'level' => 'entry', 'remote' => 'no', 'salary_min' => 25000, 'salary_max' => 40000 ),
            
            // Operations Jobs
            array( 'title' => 'Operations Manager', 'content' => 'Oversee daily operations and improve efficiency...', 'type' => 'full-time', 'level' => 'executive', 'remote' => 'hybrid', 'salary_min' => 80000, 'salary_max' => 120000 ),
            array( 'title' => 'HR Specialist', 'content' => 'Handle recruitment, employee relations, and HR policies...', 'type' => 'full-time', 'level' => 'mid', 'remote' => 'no', 'salary_min' => 45000, 'salary_max' => 65000 ),
            
            // Additional Jobs to reach 25+
            array( 'title' => 'Database Administrator', 'content' => 'Manage and optimize database systems...', 'type' => 'full-time', 'level' => 'senior', 'remote' => 'hybrid', 'salary_min' => 75000, 'salary_max' => 105000 ),
            array( 'title' => 'Quality Assurance Tester', 'content' => 'Test software applications for bugs and usability issues...', 'type' => 'contract', 'level' => 'mid', 'remote' => 'yes', 'salary_min' => 40000, 'salary_max' => 60000 ),
            array( 'title' => 'Technical Writer', 'content' => 'Create technical documentation and user guides...', 'type' => 'freelance', 'level' => 'mid', 'remote' => 'yes', 'salary_min' => 35000, 'salary_max' => 55000 )
        );

        $created_count = 0;
        foreach ( $sample_jobs as $index => $job_data ) {
            $post_id = wp_insert_post( array(
                'post_title'   => $job_data['title'],
                'post_content' => $job_data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'job',
                'post_author'  => 1,
                'post_date'    => date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 30 ) . ' days' ) )
            ) );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                $location_index = $index % count( $locations );
                $company_index = $index % count( $companies );
                
                update_post_meta( $post_id, 'job_type', $job_data['type'] );
                update_post_meta( $post_id, 'job_location', $locations[$location_index] );
                update_post_meta( $post_id, 'company_name', $companies[$company_index] );
                update_post_meta( $post_id, 'salary_min', $job_data['salary_min'] );
                update_post_meta( $post_id, 'salary_max', $job_data['salary_max'] );
                update_post_meta( $post_id, 'experience_level', $job_data['level'] );
                update_post_meta( $post_id, 'is_remote', $job_data['remote'] );
                update_post_meta( $post_id, 'featured', rand( 0, 1 ) ? '1' : '0' );
                
                $created_count++;
            }
        }

        error_log( 'Sleeve KE: Created ' . $created_count . ' sample jobs' );
        
        // Mark sample jobs as created
        update_option( 'sleeve_ke_sample_jobs_created', true );
    }

    /**
     * Enqueue assets for job display
     */
    public function enqueue_assets() {
        // Only enqueue on pages with our shortcode
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'sleeve_ke_jobs' ) ) {
            
            // Enqueue CSS
            wp_enqueue_style( 
                'sleeve-ke-job-display', 
                SLEEVE_KE_PLUGIN_URL . 'assets/css/sleeve-ke-job-display.css', 
                array(), 
                SLEEVE_KE_VERSION 
            );
            
            // Enqueue JavaScript
            wp_enqueue_script( 
                'sleeve-ke-job-display', 
                SLEEVE_KE_PLUGIN_URL . 'assets/js/sleeve-ke-job-display.js', 
                array( 'jquery' ), 
                SLEEVE_KE_VERSION, 
                true 
            );
            
            // Localize script for AJAX and include user and debug flags
            wp_localize_script( 'sleeve-ke-job-display', 'sleeve_ke_jobs_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'sleeve_ke_jobs_nonce' ),
                'user_id'  => get_current_user_id(),
                'debug'    => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? true : false
            ) );
        }
    }

    /**
     * Jobs display shortcode
     */
    public function jobs_shortcode( $atts ) {
        // Debug: Log shortcode call
        error_log( 'Sleeve KE Jobs Shortcode Called with attributes: ' . print_r( $atts, true ) );
        
        $atts = shortcode_atts( array(
            'columns' => '3',
            'posts_per_page' => '21',
            'show_filters' => 'true',
            'show_search' => 'true',
            'show_pagination' => 'true',
            'job_type' => '',
            'location' => '',
            'category' => '',
            'featured_only' => 'false',
            'layout' => 'grid', // grid or list
            'show_company_logo' => 'true',
            'show_salary' => 'true',
            'show_date' => 'true',
            'orderby' => 'date',
            'order' => 'DESC'
        ), $atts );

        // Sanitize attributes
        $atts['columns'] = absint( $atts['columns'] );
        $atts['posts_per_page'] = absint( $atts['posts_per_page'] );
        $atts['columns'] = max( 1, min( 4, $atts['columns'] ) ); // Limit to 1-4 columns
        $atts['posts_per_page'] = max( 1, min( 50, $atts['posts_per_page'] ) ); // Limit to 1-50 posts

        // Add debug info to output when in debug mode
        $debug_output = '';
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $debug_output = '<!-- Sleeve KE Jobs Shortcode Executed -->';
        }

        ob_start();
        echo $debug_output;
        $this->display_jobs_listing( $atts );
        return ob_get_clean();
    }

    /**
     * Display jobs listing with filters
     */
    private function display_jobs_listing( $atts ) {
        // Debug: Log that function is called
        error_log( 'Sleeve KE: display_jobs_listing called with atts: ' . print_r( $atts, true ) );
        
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        
        // Get jobs
        $jobs_query = $this->get_jobs_query( $atts, $paged );
        
        ?>
        <div class="sleeve-ke-jobs-container" 
             data-columns="<?php echo esc_attr( $atts['columns'] ); ?>"
             data-posts-per-page="<?php echo esc_attr( $atts['posts_per_page'] ); ?>"
             data-layout="<?php echo esc_attr( $atts['layout'] ); ?>">
            
            <?php if ( $atts['show_search'] === 'true' || $atts['show_filters'] === 'true' ) : ?>
            <div class="jobs-filters-section">
                <?php if ( $atts['show_search'] === 'true' ) : ?>
                    <?php $this->display_search_form(); ?>
                <?php endif; ?>
                
                <?php if ( $atts['show_filters'] === 'true' ) : ?>
                    <?php $this->display_filters(); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="jobs-results-header">
                <div class="results-info">
                    <span class="results-count">
                        <?php printf( 
                            _n( '%d job found', '%d jobs found', $jobs_query->found_posts, 'sleeve-ke' ), 
                            $jobs_query->found_posts 
                        ); ?>
                    </span>
                </div>
                
                <div class="layout-controls">
                    <button class="layout-btn grid-btn <?php echo $atts['layout'] === 'grid' ? 'active' : ''; ?>" 
                            data-layout="grid" title="<?php _e( 'Grid View', 'sleeve-ke' ); ?>">
                        <span class="dashicons dashicons-grid-view"></span>
                    </button>
                    <button class="layout-btn list-btn <?php echo $atts['layout'] === 'list' ? 'active' : ''; ?>" 
                            data-layout="list" title="<?php _e( 'List View', 'sleeve-ke' ); ?>">
                        <span class="dashicons dashicons-list-view"></span>
                    </button>
                </div>
            </div>

            <div class="jobs-loading" style="display: none;">
                <div class="loading-spinner"></div>
                <p><?php _e( 'Loading jobs...', 'sleeve-ke' ); ?></p>
            </div>

            <div class="jobs-grid layout-<?php echo esc_attr( $atts['layout'] ); ?> columns-<?php echo esc_attr( $atts['columns'] ); ?>">
                <?php if ( $jobs_query->have_posts() ) : ?>
                    <?php while ( $jobs_query->have_posts() ) : $jobs_query->the_post(); ?>
                        <?php $this->display_job_card( $atts ); ?>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <div class="no-jobs-found">
                        <div class="no-jobs-icon">
                            <span class="dashicons dashicons-search"></span>
                        </div>
                        <h3><?php _e( 'No jobs found', 'sleeve-ke' ); ?></h3>
                        <p><?php _e( 'Try adjusting your search criteria.', 'sleeve-ke' ); ?></p>
                        <button class="btn btn-secondary clear-filters"><?php _e( 'Clear Filters', 'sleeve-ke' ); ?></button>
                        
                        <!-- Debug Information -->
                        <?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
                            <div style="margin-top: 20px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                                <h4>Debug Information:</h4>
                                <p><strong>Query found posts:</strong> <?php echo $jobs_query->found_posts; ?></p>
                                <p><strong>Post type exists:</strong> <?php echo post_type_exists( 'job' ) ? 'Yes' : 'No'; ?></p>
                                <p><strong>Total jobs (any status):</strong> 
                                    <?php 
                                    $all_jobs = get_posts( array(
                                        'post_type' => 'job',
                                        'post_status' => array( 'publish', 'private', 'draft' ),
                                        'numberposts' => -1
                                    ) );
                                    echo count( $all_jobs );
                                    ?>
                                </p>
                                <p><strong>Query args:</strong></p>
                                <pre style="font-size: 11px; background: #fff; padding: 5px; overflow: auto;">
                                    <?php 
                                    $debug_args = array(
                                        'post_type' => 'job',
                                        'post_status' => 'publish',
                                        'posts_per_page' => $atts['posts_per_page'],
                                        'orderby' => $atts['orderby'],
                                        'order' => $atts['order']
                                    );
                                    print_r( $debug_args ); 
                                    ?>
                                </pre>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( $atts['show_pagination'] === 'true' && $jobs_query->max_num_pages > 1 ) : ?>
                <div class="jobs-pagination">
                    <?php
                    echo paginate_links( array(
                        'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                        'format'    => '?paged=%#%',
                        'current'   => max( 1, $paged ),
                        'total'     => $jobs_query->max_num_pages,
                        'prev_text' => '&laquo; ' . __( 'Previous', 'sleeve-ke' ),
                        'next_text' => __( 'Next', 'sleeve-ke' ) . ' &raquo;',
                    ) );
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Display search form
     */
    private function display_search_form() {
        ?>
        <div class="jobs-search">
            <form class="search-form" id="jobs-search-form">
                <div class="search-fields">
                    <div class="search-field">
                        <label for="job-keyword"><?php _e( 'Keyword', 'sleeve-ke' ); ?></label>
                        <input type="text" 
                               id="job-keyword" 
                               name="keyword" 
                               placeholder="<?php _e( 'Titre du poste, entreprise...', 'sleeve-ke' ); ?>"
                               value="<?php echo esc_attr( isset( $_GET['keyword'] ) ? $_GET['keyword'] : '' ); ?>">
                    </div>
                    
                    <div class="search-field">
                        <label for="job-location"><?php _e( 'Location', 'sleeve-ke' ); ?></label>
                        <input type="text" 
                               id="job-location" 
                               name="location" 
                               placeholder="<?php _e( 'City, region...', 'sleeve-ke' ); ?>"
                               value="<?php echo esc_attr( isset( $_GET['location'] ) ? $_GET['location'] : '' ); ?>">
                    </div>
                    
                    <div class="search-actions">
                        <button type="submit" class="btn btn-primary search-btn">
                            <span class="dashicons dashicons-search"></span>
                            <?php _e( 'Search', 'sleeve-ke' ); ?>
                        </button>
                        <button type="button" class="btn btn-secondary clear-btn">
                            <?php _e( 'Clear', 'sleeve-ke' ); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Display filters
     */
    private function display_filters() {
        ?>
        <div class="jobs-filters">
            <div class="filters-toggle">
                <button class="toggle-filters-btn">
                    <span class="dashicons dashicons-filter"></span>
                    <?php _e( 'Filtres', 'sleeve-ke' ); ?>
                </button>
            </div>
            
            <div class="filters-content">
                <div class="filter-group">
                    <label for="filter-job-type"><?php _e( 'Job Type', 'sleeve-ke' ); ?></label>
                    <select id="filter-job-type" name="job_type">
                        <option value=""><?php _e( 'All Types', 'sleeve-ke' ); ?></option>
                        <option value="full-time"><?php _e( 'Full-time', 'sleeve-ke' ); ?></option>
                        <option value="part-time"><?php _e( 'Part-time', 'sleeve-ke' ); ?></option>
                        <option value="contract"><?php _e( 'Contract', 'sleeve-ke' ); ?></option>
                        <option value="freelance"><?php _e( 'Freelance', 'sleeve-ke' ); ?></option>
                        <option value="internship"><?php _e( 'Stage', 'sleeve-ke' ); ?></option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter-experience"><?php _e( 'Experience', 'sleeve-ke' ); ?></label>
                    <select id="filter-experience" name="experience_level">
                        <option value=""><?php _e( 'All Levels', 'sleeve-ke' ); ?></option>
                        <option value="entry"><?php _e( 'Entry Level', 'sleeve-ke' ); ?></option>
                        <option value="mid"><?php _e( 'Mid Level', 'sleeve-ke' ); ?></option>
                        <option value="senior"><?php _e( 'Senior', 'sleeve-ke' ); ?></option>
                        <option value="executive"><?php _e( 'Executive', 'sleeve-ke' ); ?></option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter-salary"><?php _e( 'Minimum Salary', 'sleeve-ke' ); ?></label>
                    <select id="filter-salary" name="min_salary">
                        <option value=""><?php _e( 'Any', 'sleeve-ke' ); ?></option>
                        <option value="30000">30 000€+</option>
                        <option value="40000">40 000€+</option>
                        <option value="50000">50 000€+</option>
                        <option value="60000">60 000€+</option>
                        <option value="80000">80 000€+</option>
                        <option value="100000">100 000€+</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter-remote"><?php _e( 'Remote Work', 'sleeve-ke' ); ?></label>
                    <select id="filter-remote" name="is_remote">
                        <option value=""><?php _e( 'Any', 'sleeve-ke' ); ?></option>
                        <option value="yes"><?php _e( 'Remote Possible', 'sleeve-ke' ); ?></option>
                        <option value="hybrid"><?php _e( 'Hybrid', 'sleeve-ke' ); ?></option>
                        <option value="no"><?php _e( 'On-site Only', 'sleeve-ke' ); ?></option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter-date"><?php _e( 'Posted Since', 'sleeve-ke' ); ?></label>
                    <select id="filter-date" name="date_posted">
                        <option value=""><?php _e( 'All dates', 'sleeve-ke' ); ?></option>
                        <option value="1"><?php _e( 'Today', 'sleeve-ke' ); ?></option>
                        <option value="7"><?php _e( 'Last 7 days', 'sleeve-ke' ); ?></option>
                        <option value="30"><?php _e( 'Last 30 days', 'sleeve-ke' ); ?></option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="button" class="btn btn-primary apply-filters">
                        <?php _e( 'Apply Filters', 'sleeve-ke' ); ?>
                    </button>
                    <button type="button" class="btn btn-secondary reset-filters">
                        <?php _e( 'Reset', 'sleeve-ke' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Display individual job card
     */
    private function display_job_card( $atts ) {
        global $post;
        
        // Get job meta data
        $job_meta = get_post_meta( $post->ID );
        $company_name = get_post_meta( $post->ID, 'company_name', true );
        $location = get_post_meta( $post->ID, 'job_location', true );
        $job_type = get_post_meta( $post->ID, 'job_type', true );
        $salary = get_post_meta( $post->ID, 'salary_range', true );
        $featured = get_post_meta( $post->ID, 'featured', true );
        $company_logo = get_post_meta( $post->ID, 'company_logo', true );
        $remote_work = get_post_meta( $post->ID, 'remote_work', true );
        $experience_level = get_post_meta( $post->ID, 'experience_level', true );
        
        $job_classes = array( 'job-card' );
        if ( $featured ) {
            $job_classes[] = 'featured';
        }
        if ( $remote_work === 'yes' ) {
            $job_classes[] = 'remote';
        }
        
        ?>
        <div class="<?php echo implode( ' ', $job_classes ); ?>" data-job-id="<?php echo $post->ID; ?>">
            <?php if ( $featured ) : ?>
                <div class="featured-badge">
                    <span class="dashicons dashicons-star-filled"></span>
                    <?php _e( 'En vedette', 'sleeve-ke' ); ?>
                </div>
            <?php endif; ?>

            <div class="job-header">
                <?php if ( $atts['show_company_logo'] === 'true' && $company_logo ) : ?>
                    <div class="company-logo">
                        <img src="<?php echo esc_url( $company_logo ); ?>" 
                             alt="<?php echo esc_attr( $company_name ); ?>"
                             loading="lazy">
                    </div>
                <?php endif; ?>
                
                <div class="job-info">
                    <h3 class="job-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    
                    <div class="company-name">
                        <?php echo esc_html( $company_name ); ?>
                    </div>
                    
                    <div class="job-meta">
                        <?php if ( $location ) : ?>
                            <span class="location">
                                <span class="dashicons dashicons-location"></span>
                                <?php echo esc_html( $location ); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ( $job_type ) : ?>
                            <span class="job-type type-<?php echo esc_attr( $job_type ); ?>">
                                <?php echo $this->get_job_type_label( $job_type ); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ( $remote_work === 'yes' ) : ?>
                            <span class="remote-badge">
                                <span class="dashicons dashicons-laptop"></span>
                                <?php _e( 'Remote', 'sleeve-ke' ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="job-content">
                <div class="job-excerpt">
                    <?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
                </div>
                
                <?php if ( $experience_level ) : ?>
                    <div class="experience-level">
                        <strong><?php _e( 'Experience:', 'sleeve-ke' ); ?></strong>
                        <?php echo $this->get_experience_level_label( $experience_level ); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="job-footer">
                <div class="job-details">
                    <?php if ( $atts['show_salary'] === 'true' && $salary ) : ?>
                        <span class="salary">
                            <span class="dashicons dashicons-money-alt"></span>
                            <?php echo esc_html( $salary ); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ( $atts['show_date'] === 'true' ) : ?>
                        <span class="post-date">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago'; ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="job-actions">
                    <a href="<?php the_permalink(); ?>" class="btn btn-primary view-job">
                        <?php _e( 'View Job', 'sleeve-ke' ); ?>
                    </a>
                    
                    <button class="btn btn-secondary save-job" data-job-id="<?php echo $post->ID; ?>">
                        <span class="dashicons dashicons-heart"></span>
                        <?php _e( 'Save', 'sleeve-ke' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get jobs query
     */
    private function get_jobs_query( $atts, $paged = 1 ) {
        $args = array(
            'post_type' => 'job',
            'post_status' => 'publish',
            'posts_per_page' => $atts['posts_per_page'],
            'paged' => $paged,
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'meta_query' => array(),
            'tax_query' => array()
        );

        // Debug: Log query args
        error_log( 'Sleeve KE Jobs Query Args: ' . print_r( $args, true ) );

        // Filter by job type
        if ( ! empty( $atts['job_type'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'job_type',
                'value' => $atts['job_type'],
                'compare' => '='
            );
        }

        // Filter by location
        if ( ! empty( $atts['location'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'job_location',
                'value' => $atts['location'],
                'compare' => 'LIKE'
            );
        }

        // Featured only
        if ( $atts['featured_only'] === 'true' ) {
            $args['meta_query'][] = array(
                'key' => 'featured',
                'value' => '1',
                'compare' => '='
            );
        }

        // Handle search parameters from GET/POST
        if ( isset( $_GET['keyword'] ) && ! empty( $_GET['keyword'] ) ) {
            $args['s'] = sanitize_text_field( $_GET['keyword'] );
        }

        if ( isset( $_GET['location'] ) && ! empty( $_GET['location'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'job_location',
                'value' => sanitize_text_field( $_GET['location'] ),
                'compare' => 'LIKE'
            );
        }

        $query = new WP_Query( $args );
        
        // Debug: Log query results
        error_log( 'Sleeve KE Jobs Query Results: Found ' . $query->found_posts . ' posts' );
        if ( $query->found_posts == 0 ) {
            error_log( 'Sleeve KE Jobs Query SQL: ' . $query->request );
            
            // Debug: Check what post types exist
            $post_types = get_post_types( array( 'public' => true ), 'names' );
            error_log( 'Available post types: ' . print_r( $post_types, true ) );
            
            // Debug: Check if job post type is registered
            if ( post_type_exists( 'job' ) ) {
                error_log( 'Job post type exists' );
            } else {
                error_log( 'Job post type does NOT exist' );
            }
            
            // Debug: Check for any posts of type job regardless of status
            $all_jobs = get_posts( array(
                'post_type' => 'job',
                'post_status' => array( 'publish', 'private', 'draft' ),
                'numberposts' => -1
            ) );
            error_log( 'Total jobs in database (any status): ' . count( $all_jobs ) );
        }

        return $query;
    }

    /**
     * AJAX handler for filtering jobs
     */
    public function ajax_filter_jobs() {
        // Log incoming request for debugging
        $remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        error_log( 'Sleeve KE: AJAX filter request from IP: ' . $remote_ip . ' - POST: ' . print_r( $_POST, true ) . ' - User ID: ' . get_current_user_id() );

        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sleeve_ke_jobs_nonce' ) ) {
            error_log( 'Sleeve KE: AJAX filter - Invalid or missing nonce: ' . ( isset( $_POST['nonce'] ) ? $_POST['nonce'] : '(none)' ) );
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'sleeve-ke' ) ) );
        }

        // Pre-query diagnostics: how many published jobs exist before applying filters
        $published_count = 0;
        if ( post_type_exists( 'job' ) ) {
            $counts = wp_count_posts( 'job' );
            if ( is_object( $counts ) && isset( $counts->publish ) ) {
                $published_count = intval( $counts->publish );
            }
        }

        // Also count jobs regardless of status
        $all_jobs = get_posts( array(
            'post_type' => 'job',
            'post_status' => array( 'publish', 'private', 'draft' ),
            'numberposts' => -1
        ) );
        $all_jobs_count = count( $all_jobs );
        error_log( 'Sleeve KE: Pre-filter job counts - published: ' . $published_count . ', total(any status): ' . $all_jobs_count );

        $filters = array();
        $allowed_filters = array( 'keyword', 'location', 'job_type', 'experience_level', 'min_salary', 'remote_work', 'date_posted' );
        
        foreach ( $allowed_filters as $filter ) {
            if ( isset( $_POST[$filter] ) && ! empty( $_POST[$filter] ) ) {
                $filters[$filter] = sanitize_text_field( $_POST[$filter] );
            }
        }

        $atts = array(
            'columns' => absint( $_POST['columns'] ),
            'posts_per_page' => absint( $_POST['posts_per_page'] ),
            'layout' => sanitize_text_field( $_POST['layout'] ),
            'show_company_logo' => sanitize_text_field( $_POST['show_company_logo'] ),
            'show_salary' => sanitize_text_field( $_POST['show_salary'] ),
            'show_date' => sanitize_text_field( $_POST['show_date'] )
        );

        $paged = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;

        // Apply filters to query args
        $args = $this->build_filtered_query( $filters, $atts, $paged );
        
        // Debug: Log AJAX filter query
        error_log( 'Sleeve KE AJAX Filter Query: ' . print_r( $args, true ) );
        error_log( 'Sleeve KE AJAX Filters Applied: ' . print_r( $filters, true ) );
        
        $jobs_query = new WP_Query( $args );
        
        error_log( 'Sleeve KE AJAX Query Results: Found ' . $jobs_query->found_posts . ' posts' );

        ob_start();
        if ( $jobs_query->have_posts() ) {
            while ( $jobs_query->have_posts() ) {
                $jobs_query->the_post();
                $this->display_job_card( $atts );
            }
            wp_reset_postdata();
        } else {
            echo '<div class="no-jobs-found">
                    <div class="no-jobs-icon">
                        <span class="dashicons dashicons-search"></span>
                    </div>
                    <h3>' . __( 'No jobs found', 'sleeve-ke' ) . '</h3>
                    <p>' . __( 'Try adjusting your search criteria.', 'sleeve-ke' ) . '</p>
                  </div>';
        }
        $html = ob_get_clean();

        wp_send_json_success( array(
            'html' => $html,
            'found_posts' => $jobs_query->found_posts,
            'max_pages' => $jobs_query->max_num_pages,
            'pre_total_published' => $published_count,
            'pre_total_all_status' => $all_jobs_count,
            'query_args' => $args
        ) );
    }

    /**
     * Build filtered query arguments
     */
    private function build_filtered_query( $filters, $atts, $paged ) {
        $args = array(
            'post_type' => 'job',
            'post_status' => 'publish',
            'posts_per_page' => $atts['posts_per_page'],
            'paged' => $paged,
            'meta_query' => array(),
        );

        // Keyword search
        if ( ! empty( $filters['keyword'] ) ) {
            $args['s'] = $filters['keyword'];
        }

        // Location filter
        if ( ! empty( $filters['location'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'job_location',
                'value' => $filters['location'],
                'compare' => 'LIKE'
            );
        }

        // Job type filter
        if ( ! empty( $filters['job_type'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'job_type',
                'value' => $filters['job_type'],
                'compare' => '='
            );
        }

        // Experience level filter
        if ( ! empty( $filters['experience_level'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'experience_level',
                'value' => $filters['experience_level'],
                'compare' => '='
            );
        }

        // Minimum salary filter
        if ( ! empty( $filters['min_salary'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'salary_min',
                'value' => intval( $filters['min_salary'] ),
                'compare' => '>='
            );
        }

        // Remote work filter
        if ( ! empty( $filters['remote_work'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'is_remote',
                'value' => $filters['remote_work'],
                'compare' => '='
            );
        }

        // Date posted filter
        if ( ! empty( $filters['date_posted'] ) ) {
            $days = intval( $filters['date_posted'] );
            $args['date_query'] = array(
                array(
                    'after' => $days . ' days ago'
                )
            );
        }

        return $args;
    }

    /**
     * Get job type label
     */
    private function get_job_type_label( $job_type ) {
        $labels = array(
            'full-time' => __( 'Temps plein', 'sleeve-ke' ),
            'part-time' => __( 'Temps partiel', 'sleeve-ke' ),
            'contract' => __( 'Contrat', 'sleeve-ke' ),
            'freelance' => __( 'Freelance', 'sleeve-ke' ),
            'internship' => __( 'Stage', 'sleeve-ke' )
        );

        return isset( $labels[$job_type] ) ? $labels[$job_type] : $job_type;
    }

    /**
     * Get experience level label
     */
    private function get_experience_level_label( $level ) {
        $labels = array(
            'entry' => __( 'Entry Level', 'sleeve-ke' ),
            'mid' => __( 'Mid Level', 'sleeve-ke' ),
            'senior' => __( 'Senior', 'sleeve-ke' ),
            'executive' => __( 'Executive', 'sleeve-ke' )
        );

        return isset( $labels[$level] ) ? $labels[$level] : $level;
    }

    /**
     * AJAX handler for saving a job
     */
    public function ajax_save_job() {
        // Debug: Log the save job request
        error_log( 'Sleeve KE: Save job AJAX request - POST data: ' . print_r( $_POST, true ) );
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'sleeve_ke_jobs_nonce' ) ) {
            error_log( 'Sleeve KE: Save job - Invalid nonce' );
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'sleeve-ke' ) ) );
        }

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            error_log( 'Sleeve KE: Save job - User not logged in' );
            wp_send_json_error( array( 'message' => __( 'You must be logged in to save jobs', 'sleeve-ke' ) ) );
        }

        $job_id = intval( $_POST['job_id'] );
        $user_id = get_current_user_id();

        if ( ! $job_id ) {
            error_log( 'Sleeve KE: Save job - Invalid job ID: ' . $job_id );
            wp_send_json_error( array( 'message' => __( 'Invalid job ID', 'sleeve-ke' ) ) );
        }

        // Check if job exists
        if ( ! get_post( $job_id ) || get_post_type( $job_id ) !== 'job' ) {
            error_log( 'Sleeve KE: Save job - Job not found: ' . $job_id );
            wp_send_json_error( array( 'message' => __( 'Job not found', 'sleeve-ke' ) ) );
        }

        // Save the job to user meta (or custom table if you prefer)
        $saved_jobs = get_user_meta( $user_id, 'saved_jobs', true );
        if ( ! is_array( $saved_jobs ) ) {
            $saved_jobs = array();
        }

        if ( ! in_array( $job_id, $saved_jobs ) ) {
            $saved_jobs[] = $job_id;
            update_user_meta( $user_id, 'saved_jobs', $saved_jobs );
            
            error_log( 'Sleeve KE: Job saved successfully - Job ID: ' . $job_id . ', User ID: ' . $user_id );
            wp_send_json_success( array( 'message' => __( 'Job saved successfully', 'sleeve-ke' ) ) );
        } else {
            error_log( 'Sleeve KE: Job already saved - Job ID: ' . $job_id . ', User ID: ' . $user_id );
            wp_send_json_success( array( 'message' => __( 'Job already saved', 'sleeve-ke' ) ) );
        }
    }

    /**
     * AJAX handler for unsaving a job
     */
    public function ajax_unsave_job() {
        // Debug: Log the unsave job request
        error_log( 'Sleeve KE: Unsave job AJAX request - POST data: ' . print_r( $_POST, true ) );
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'sleeve_ke_jobs_nonce' ) ) {
            error_log( 'Sleeve KE: Unsave job - Invalid nonce' );
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'sleeve-ke' ) ) );
        }

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            error_log( 'Sleeve KE: Unsave job - User not logged in' );
            wp_send_json_error( array( 'message' => __( 'You must be logged in to manage saved jobs', 'sleeve-ke' ) ) );
        }

        $job_id = intval( $_POST['job_id'] );
        $user_id = get_current_user_id();

        if ( ! $job_id ) {
            error_log( 'Sleeve KE: Unsave job - Invalid job ID: ' . $job_id );
            wp_send_json_error( array( 'message' => __( 'Invalid job ID', 'sleeve-ke' ) ) );
        }

        // Remove the job from user meta
        $saved_jobs = get_user_meta( $user_id, 'saved_jobs', true );
        if ( is_array( $saved_jobs ) ) {
            $saved_jobs = array_diff( $saved_jobs, array( $job_id ) );
            update_user_meta( $user_id, 'saved_jobs', $saved_jobs );
            
            error_log( 'Sleeve KE: Job unsaved successfully - Job ID: ' . $job_id . ', User ID: ' . $user_id );
            wp_send_json_success( array( 'message' => __( 'Job removed from saved jobs', 'sleeve-ke' ) ) );
        } else {
            error_log( 'Sleeve KE: No saved jobs found for user: ' . $user_id );
            wp_send_json_success( array( 'message' => __( 'Job was not in saved jobs', 'sleeve-ke' ) ) );
        }
    }
}