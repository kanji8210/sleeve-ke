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
        // Enqueue a very small debug stylesheet for the simple view
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        if ( ! is_singular() ) {
            return;
        }
        wp_register_style( 'sleeve-ke-job-display-simple', SLEEVE_KE_PLUGIN_URL . 'assets/css/sleeve-ke-job-display.css', array(), SLEEVE_KE_VERSION );
        wp_enqueue_style( 'sleeve-ke-job-display-simple' );
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
        // Read filters from GET so this works without JS
        $filters = array();
        $allowed = array( 'keyword', 'location', 'job_type', 'experience_level', 'min_salary', 'is_remote', 'date_posted' );
        foreach ( $allowed as $k ) {
            if ( isset( $_GET[ $k ] ) && $_GET[ $k ] !== '' ) {
                $filters[ $k ] = sanitize_text_field( wp_unslash( $_GET[ $k ] ) );
            }
        }

        // Build query args
        $args = array(
            'post_type' => 'job',
            'post_status' => 'publish',
            'posts_per_page' => absint( $atts['posts_per_page'] ),
            'orderby' => sanitize_text_field( $atts['orderby'] ),
            'order' => sanitize_text_field( $atts['order'] ),
            'meta_query' => array()
        );

        if ( ! empty( $filters['keyword'] ) ) {
            $args['s'] = $filters['keyword'];
        }

        if ( ! empty( $filters['location'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'job_location',
                'value' => $filters['location'],
                'compare' => 'LIKE'
            );
        }

        if ( ! empty( $filters['job_type'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'job_type',
                'value' => $filters['job_type'],
                'compare' => '='
            );
        }

        if ( ! empty( $filters['experience_level'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'experience_level',
                'value' => $filters['experience_level'],
                'compare' => '='
            );
        }

        if ( ! empty( $filters['min_salary'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'salary_min',
                'value' => intval( $filters['min_salary'] ),
                'compare' => '>='
            );
        }

        if ( ! empty( $filters['is_remote'] ) ) {
            $args['meta_query'][] = array(
                'key' => 'is_remote',
                'value' => $filters['is_remote'],
                'compare' => '='
            );
        }

        if ( ! empty( $filters['date_posted'] ) ) {
            $days = intval( $filters['date_posted'] );
            $args['date_query'] = array(
                array( 'after' => $days . ' days ago' )
            );
        }

        // Debug output before query
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            echo '<div class="sleeve-ke-simple-debug" style="background:#fff;border-left:4px solid #0073aa;padding:10px;margin-bottom:10px;">';
            echo '<strong>Debug - pre-query args:</strong><pre>' . esc_html( print_r( $args, true ) ) . '</pre>';

            $counts = wp_count_posts( 'job' );
            $published = is_object( $counts ) && isset( $counts->publish ) ? intval( $counts->publish ) : 0;
            echo '<strong>Published jobs:</strong> ' . $published . '<br/>';

            $all = get_posts( array( 'post_type' => 'job', 'post_status' => array( 'publish', 'private', 'draft' ), 'numberposts' => -1 ) );
            echo '<strong>All jobs (any status):</strong> ' . count( $all ) . '<br/>';
            echo '</div>';
        }

        $q = new WP_Query( $args );

        echo '<div class="sleeve-ke-simple-list">';
        if ( $q->have_posts() ) {
            echo '<p><strong>' . esc_html( $q->found_posts ) . ' jobs found</strong></p>';
            while ( $q->have_posts() ) {
                $q->the_post();
                echo '<div class="simple-job-card" style="padding:10px;border:1px solid #eee;margin-bottom:8px;">';
                echo '<h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
                $company = get_post_meta( get_the_ID(), 'company_name', true );
                if ( $company ) {
                    echo '<div><em>' . esc_html( $company ) . '</em></div>';
                }
                $loc = get_post_meta( get_the_ID(), 'job_location', true );
                if ( $loc ) {
                    echo '<div>' . esc_html( $loc ) . '</div>';
                }
                echo '</div>';
            }
            wp_reset_postdata();
        } else {
            echo '<div class="no-jobs-found">No jobs found.</div>';
        }
        echo '</div>';
    }
}
