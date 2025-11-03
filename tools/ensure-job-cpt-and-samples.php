<?php
/**
 * Safe helper to ensure 'job' post type is registered and (optionally) create sample jobs.
 * Usage:
 *  - Place in plugin's tools/ folder (done).
 *  - Visit in browser while logged in as admin: /wp-content/plugins/sleeve-ke/tools/ensure-job-cpt-and-samples.php
 *  - To create samples, append ?run=1 and be logged in as a user with manage_options capability.
 *
 * This script will NOT create posts unless you are logged-in admin and pass run=1.
 */

// Bootstrap WordPress
$base = dirname( dirname( dirname( __FILE__ ) ) ); // plugin root -> .../plugins/sleeve-ke
if ( file_exists( $base . '/../../wp-load.php' ) ) {
    require_once $base . '/../../wp-load.php';
} elseif ( file_exists( $base . '/../../../wp-load.php' ) ) {
    require_once $base . '/../../../wp-load.php';
} else {
    echo "Could not locate wp-load.php. Please place this file in the plugin folder and ensure WordPress root is reachable.";
    exit;
}

// Helper functions
function sleeve_ke_print($msg) {
    echo '<div style="font-family:Arial,Helvetica,sans-serif;margin:8px;padding:8px;background:#fff;border-left:4px solid #0073aa;">' . $msg . '</div>';
}

sleeve_ke_print('<h2>Sleeve KE — CPT & Sample Jobs Helper</h2>');

// Check post type
$post_types = get_post_types( array(), 'names' );
if ( in_array( 'job', $post_types, true ) ) {
    sleeve_ke_print('✅ Job post type is registered.');
} else {
    sleeve_ke_print('⚠️ Job post type is NOT registered. Attempting to register temporarily now...');
    // Register a minimal job CPT so we can create posts
    register_post_type( 'job', array(
        'labels' => array( 'name' => 'Jobs', 'singular_name' => 'Job' ),
        'public' => true,
        'has_archive' => true,
        'supports' => array( 'title', 'editor' )
    ) );
    // Refresh post types list
    $post_types = get_post_types( array(), 'names' );
    if ( in_array( 'job', $post_types, true ) ) {
        sleeve_ke_print('✅ Job post type registered temporarily.');
    } else {
        sleeve_ke_print('❌ Failed to register job post type.');
    }
}

// Counts
$counts = wp_count_posts( 'job' );
$published = is_object( $counts ) && isset( $counts->publish ) ? intval( $counts->publish ) : 0;
$all = get_posts( array( 'post_type' => 'job', 'post_status' => array( 'publish', 'private', 'draft' ), 'numberposts' => -1 ) );
$sleeve_msg = sprintf('Published jobs: %d — Total (any status): %d', $published, count( $all ) );
sleeve_ke_print( $sleeve_msg );

// Provide instructions and ability to create samples
$can_run = is_user_logged_in() && current_user_can( 'manage_options' );
if ( ! $can_run ) {
    sleeve_ke_print('To create sample jobs from this script you must be logged in as an administrator.');
    sleeve_ke_print('If you are the admin, log in to WordPress and revisit this page.');
    sleeve_ke_print('You can also create sample jobs via WP admin or WP-CLI.');
    exit;
}

if ( isset( $_GET['run'] ) && $_GET['run'] === '1' ) {
    sleeve_ke_print('<strong>Creating 10 sample jobs...</strong>');
    $titles = array(
        'Senior PHP Developer', 'Frontend Engineer', 'Python Data Scientist', 'Mobile App Developer', 'DevOps Engineer',
        'UI/UX Designer', 'Junior Web Developer', 'Digital Marketing Manager', 'Project Manager', 'Database Administrator'
    );
    $created = 0;
    foreach ( $titles as $i => $t ) {
        $post_id = wp_insert_post( array(
            'post_title'   => $t . ' #' . rand(100,999),
            'post_content' => 'Sample job content for ' . $t,
            'post_status'  => 'publish',
            'post_type'    => 'job',
            'post_author'  => get_current_user_id()
        ) );
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, 'job_type', ( $i % 2 === 0 ) ? 'full-time' : 'part-time' );
            update_post_meta( $post_id, 'job_location', ( $i % 3 === 0 ) ? 'Nairobi' : 'Remote' );
            update_post_meta( $post_id, 'company_name', 'SampleCo ' . ($i+1) );
            update_post_meta( $post_id, 'salary_min', 30000 + ($i*5000) );
            update_post_meta( $post_id, 'salary_max', 50000 + ($i*7000) );
            update_post_meta( $post_id, 'experience_level', ( $i % 3 === 0 ) ? 'entry' : 'mid' );
            update_post_meta( $post_id, 'is_remote', ( $i % 2 === 0 ) ? 'yes' : 'no' );
            $created++;
        }
    }
    sleeve_ke_print('Created ' . $created . ' sample jobs.');
    // Recount
    $all = get_posts( array( 'post_type' => 'job', 'post_status' => array( 'publish', 'private', 'draft' ), 'numberposts' => -1 ) );
    sleeve_ke_print('New total jobs: ' . count( $all ));
    sleeve_ke_print('Now visit the test-simple-jobs.php page again to confirm.');
} else {
    $url = $_SERVER['REQUEST_URI'];
    $run_url = $url . ( strpos( $url, '?' ) === false ? '?run=1' : '&run=1' );
    // Nonce for removal action
    $remove_nonce = wp_create_nonce( 'sleeve_ke_remove_samples' );
    $remove_url = $url . ( strpos( $url, '?' ) === false ? '?remove=1' : '&remove=1' ) . '&_wpnonce=' . $remove_nonce;

    sleeve_ke_print('To create sample jobs click: <a href="' . esc_url( $run_url ) . '">Create sample jobs (admin only)</a>');
    sleeve_ke_print('To remove sample jobs created by this helper click: <a href="' . esc_url( $remove_url ) . '">Remove sample jobs</a>');
}

// Handle removal request (only for admin users with valid nonce)
if ( isset( $_GET['remove'] ) && $_GET['remove'] === '1' ) {
    if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
        sleeve_ke_print( 'Insufficient permissions to remove samples.' );
        exit;
    }

    if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'sleeve_ke_remove_samples' ) ) {
        sleeve_ke_print( 'Invalid request (bad nonce).' );
        exit;
    }

    sleeve_ke_print( '<strong>Removing sample jobs created by this helper...</strong>' );
    // Find posts that look like they were created by this helper
    $candidates = get_posts( array(
        'post_type' => 'job',
        'post_status' => array( 'publish', 'draft', 'private' ),
        'numberposts' => -1,
    ) );

    $removed = 0;
    foreach ( $candidates as $p ) {
        $keep = true;
        // Remove if content matches helper pattern
        if ( strpos( $p->post_content, 'Sample job content for' ) !== false ) {
            $keep = false;
        }
        // Or if meta company_name starts with SampleCo
        $company = get_post_meta( $p->ID, 'company_name', true );
        if ( ! empty( $company ) && stripos( $company, 'SampleCo' ) !== false ) {
            $keep = false;
        }

        if ( ! $keep ) {
            wp_delete_post( $p->ID, true );
            $removed++;
        }
    }

    // Clear the sample-created option if exists
    if ( get_option( 'sleeve_ke_sample_jobs_created' ) ) {
        delete_option( 'sleeve_ke_sample_jobs_created' );
    }

    sleeve_ke_print( 'Removed ' . intval( $removed ) . ' sample jobs.' );
    // Recount
    $all = get_posts( array( 'post_type' => 'job', 'post_status' => array( 'publish', 'private', 'draft' ), 'numberposts' => -1 ) );
    sleeve_ke_print( 'New total jobs: ' . count( $all ) );
    exit;
}

exit;
