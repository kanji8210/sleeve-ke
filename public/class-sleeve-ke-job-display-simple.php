<?php
/**
 * Simple job display class for debugging and server-side filtering.
 * Provides a shortcode [sleeve_ke_jobs_simple] which renders jobs server-side
 * and accepts GET parameters for filters so we can verify DB selection without AJAX.
 *
 * @package Sleeve_KE
 */

class Sleeve_KE_Job_Display_Simple {

    public function __construct() {
        add_shortcode( 'sleeve_ke_jobs_simple', array( $this, 'jobs_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        if ( ! is_singular() ) {
            return;
        }
        wp_register_style( 
            'sleeve-ke-job-display-simple', 
            SLEEVE_KE_PLUGIN_URL . 'assets/css/sleeve-ke-job-display.css', 
            array(), 
            SLEEVE_KE_VERSION 
        );
        wp_enqueue_style( 'sleeve-ke-job-display-simple' );
        
        // Enqueue debug script
        wp_register_script(
            'sleeve-ke-debug',
            SLEEVE_KE_PLUGIN_URL . 'assets/js/sleeve-ke-debug.js',
            array('jquery'),
            SLEEVE_KE_VERSION,
            true
        );
        wp_enqueue_script( 'sleeve-ke-debug' );
    }

    public function jobs_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'posts_per_page' => 21,
            'orderby' => 'date',
            'order' => 'DESC'
        ), $atts );

        ob_start();
        $this->display_jobs_listing( $atts );
        return ob_get_clean();
    }

    private function display_jobs_listing( $atts ) {
        echo '<div class="sleeve-ke-debug-container">';
        
        // Display comprehensive debug information
        $this->display_debug_info();
        
        // Process filters from GET parameters
        $filters = $this->get_filters_from_request();
        
        // Build and execute query
        $query_args = $this->build_query_args( $atts, $filters );
        $this->display_query_debug( $query_args );
        
        $jobs_query = new WP_Query( $query_args );
        $this->display_results( $jobs_query );
        
        echo '</div>';
    }

    /**
     * Get filters from GET request
     */
    private function get_filters_from_request() {
        $filters = array();
        $allowed_filters = array( 'keyword', 'location', 'job_type', 'experience_level', 'min_salary', 'is_remote', 'date_posted' );
        
        foreach ( $allowed_filters as $filter_key ) {
            if ( isset( $_GET[ $filter_key ] ) && $_GET[ $filter_key ] !== '' ) {
                $filters[ $filter_key ] = sanitize_text_field( wp_unslash( $_GET[ $filter_key ] ) );
            }
        }
        
        return $filters;
    }

    /**
     * Build query arguments based on filters and attributes
     */
    private function build_query_args( $atts, $filters ) {
        $args = array(
            'post_type' => 'job',
            'post_status' => 'publish',
            'posts_per_page' => absint( $atts['posts_per_page'] ),
            'orderby' => sanitize_text_field( $atts['orderby'] ),
            'order' => sanitize_text_field( $atts['order'] ),
            'meta_query' => array()
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
                'type' => 'NUMERIC',
                'compare' => '>='
            );
        }

        // Remote work filter
        if ( ! empty( $filters['is_remote'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'is_remote',
                'value' => $filters['is_remote'],
                'compare' => '='
            );
        }

        // Date posted filter
        if ( ! empty( $filters['date_posted'] ) ) {
            $days = intval( $filters['date_posted'] );
            $args['date_query'] = array(
                array( 
                    'after' => $days . ' days ago',
                    'inclusive' => true 
                )
            );
        }

        // Handle multiple meta queries
        if ( count( $args['meta_query'] ) > 1 ) {
            $args['meta_query']['relation'] = 'AND';
        }

        return $args;
    }

    /**
     * Display comprehensive debug information
     */
    private function display_debug_info() {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        global $wpdb;
        
        echo '<div class="sleeve-ke-debug-panel" style="background:#f8f9fa;border:2px solid #dc3545;border-radius:5px;padding:15px;margin-bottom:20px;font-family:monospace;font-size:14px;">';
        echo '<h3 style="color:#dc3545;margin-top:0;">🔧 SLEEVE KE DEBUG PANEL</h3>';
        
        // Database table check
        echo '<div style="margin-bottom:15px;">';
        echo '<strong>📊 DATABASE TABLES CHECK:</strong><br>';
        
        $jobs_table = $wpdb->prefix . 'posts';
        $meta_table = $wpdb->prefix . 'postmeta';
        
        $jobs_count = $wpdb->get_var("SELECT COUNT(*) FROM $jobs_table WHERE post_type = 'job' AND post_status = 'publish'");
        $all_jobs_count = $wpdb->get_var("SELECT COUNT(*) FROM $jobs_table WHERE post_type = 'job'");
        
        echo "✅ Jobs table exists: " . esc_html( $jobs_table ) . "<br>";
        echo "✅ Meta table exists: " . esc_html( $meta_table ) . "<br>";
        echo "📈 Published jobs: " . intval( $jobs_count ) . "<br>";
        echo "📊 Total jobs (all status): " . intval( $all_jobs_count ) . "<br>";
        echo '</div>';
        
        // Post type and taxonomy check
        echo '<div style="margin-bottom:15px;">';
        echo '<strong>🎯 POST TYPE & TAXONOMY CHECK:</strong><br>';
        echo "✅ Job post type: " . ( post_type_exists( 'job' ) ? 'Exists' : 'MISSING!' ) . "<br>";
        
        // Check if we have any job posts
        $sample_job = get_posts( array(
            'post_type' => 'job',
            'post_status' => 'publish',
            'numberposts' => 1
        ) );
        
        echo "✅ Sample job post: " . ( ! empty( $sample_job ) ? 'Found' : 'None found' ) . "<br>";
        
        if ( ! empty( $sample_job ) ) {
            $job_id = $sample_job[0]->ID;
            echo "✅ Sample job ID: " . esc_html( $job_id ) . "<br>";
            
            // Check meta fields
            $meta_fields = array( 'company_name', 'job_location', 'job_type', 'experience_level', 'salary_min', 'is_remote' );
            foreach ( $meta_fields as $field ) {
                $value = get_post_meta( $job_id, $field, true );
                echo "🔍 Meta '{$field}': " . ( $value ? esc_html( $value ) : 'Empty/Not set' ) . "<br>";
            }
        }
        echo '</div>';
        
        // AJAX and environment check
        echo '<div style="margin-bottom:15px;">';
        echo '<strong>🌐 ENVIRONMENT & AJAX CHECK:</strong><br>';
        echo "✅ WordPress Version: " . esc_html( get_bloginfo( 'version' ) ) . "<br>";
        echo "✅ PHP Version: " . esc_html( phpversion() ) . "<br>";
        echo "✅ AJAX URL: " . esc_html( admin_url( 'admin-ajax.php' ) ) . "<br>";
        echo "✅ Plugin URL: " . esc_html( SLEEVE_KE_PLUGIN_URL ) . "<br>";
        echo '</div>';
        
        // Current request info
        echo '<div>';
        echo '<strong>📝 CURRENT REQUEST INFO:</strong><br>';
        echo "✅ Current URL: " . esc_html( home_url( $_SERVER['REQUEST_URI'] ) ) . "<br>";
        echo "✅ GET Parameters: " . esc_html( http_build_query( $_GET ) ) . "<br>";
        echo "✅ Filters applied: " . esc_html( implode( ', ', array_keys( $_GET ) ) ) . "<br>";
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Display query debug information
     */
    private function display_query_debug( $query_args ) {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        echo '<div class="sleeve-ke-query-debug" style="background:#e9ecef;border:1px solid #6c757d;border-radius:3px;padding:10px;margin-bottom:15px;font-family:monospace;font-size:13px;">';
        echo '<strong>🔍 QUERY ARGUMENTS:</strong><br>';
        echo '<pre style="white-space:pre-wrap;background:white;padding:10px;border-radius:3px;">' . esc_html( print_r( $query_args, true ) ) . '</pre>';
        echo '</div>';
    }

    /**
     * Display query results
     */
    private function display_results( $query ) {
        echo '<div class="sleeve-ke-simple-list">';
        
        if ( $query->have_posts() ) {
            echo '<div class="sleeve-ke-results-header" style="background:#d4edda;border:1px solid #c3e6cb;border-radius:5px;padding:10px;margin-bottom:15px;">';
            echo '<strong>✅ ' . esc_html( $query->found_posts ) . ' jobs found</strong>';
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                echo '<br><small>Query took: ' . esc_html( $query->query_time ) . ' seconds</small>';
            }
            echo '</div>';
            
            while ( $query->have_posts() ) {
                $query->the_post();
                $this->display_job_card();
            }
            wp_reset_postdata();
        } else {
            echo '<div class="no-jobs-found" style="background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;padding:15px;text-align:center;">';
            echo '<strong>❌ No jobs found with current filters.</strong>';
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                echo '<br><small>Last SQL query: ' . esc_html( $query->request ) . '</small>';
            }
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Display individual job card
     */
    private function display_job_card() {
        $job_id = get_the_ID();
        
        echo '<div class="simple-job-card" style="background:white;border:1px solid #dee2e6;border-radius:5px;padding:15px;margin-bottom:10px;transition:all 0.3s;">';
        echo '<h3 style="margin:0 0 10px 0;"><a href="' . esc_url( get_permalink() ) . '" style="text-decoration:none;color:#007cba;">' . esc_html( get_the_title() ) . '</a></h3>';
        
        $company = get_post_meta( $job_id, 'company_name', true );
        if ( $company ) {
            echo '<div style="color:#6c757d;margin-bottom:5px;"><strong>🏢 Company:</strong> ' . esc_html( $company ) . '</div>';
        }
        
        $location = get_post_meta( $job_id, 'job_location', true );
        if ( $location ) {
            echo '<div style="color:#6c757d;margin-bottom:5px;"><strong>📍 Location:</strong> ' . esc_html( $location ) . '</div>';
        }
        
        $job_type = get_post_meta( $job_id, 'job_type', true );
        if ( $job_type ) {
            echo '<div style="color:#6c757d;margin-bottom:5px;"><strong>💼 Type:</strong> ' . esc_html( $job_type ) . '</div>';
        }
        
        $salary = get_post_meta( $job_id, 'salary_min', true );
        if ( $salary ) {
            echo '<div style="color:#6c757d;margin-bottom:5px;"><strong>💰 Min Salary:</strong> ' . esc_html( $salary ) . '</div>';
        }
        
        // Debug info for each job card
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            echo '<div style="background:#f8f9fa;padding:5px;margin-top:5px;font-size:12px;border-radius:3px;">';
            echo '<small><strong>Debug:</strong> ID: ' . esc_html( $job_id ) . ' | Posted: ' . esc_html( get_the_date() ) . '</small>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}

// Initialize the class
new Sleeve_KE_Job_Display_Simple();