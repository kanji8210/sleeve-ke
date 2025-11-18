<?php
/**
 * Check jobs in database
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../../wp-load.php');

global $wpdb;

echo "=== CHECKING JOBS IN DATABASE ===\n\n";

// Check custom table
$table_name = $wpdb->prefix . 'sleeve_jobs';
$jobs_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
echo "Jobs in wp_sleeve_jobs table: " . $jobs_count . "\n";

if ($jobs_count > 0) {
    echo "\nRecent jobs:\n";
    $recent_jobs = $wpdb->get_results("SELECT id, employer_id, title, status, created_at FROM $table_name ORDER BY created_at DESC LIMIT 5", ARRAY_A);
    foreach ($recent_jobs as $job) {
        echo "  - ID: {$job['id']}, Title: {$job['title']}, Status: {$job['status']}, Created: {$job['created_at']}\n";
    }
}

echo "\n";

// Check WordPress posts
$posts_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='job'");
echo "Jobs as WordPress posts (any status): " . $posts_count . "\n";

$published_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='job' AND post_status='publish'");
echo "Published job posts: " . $published_count . "\n";

// Check if job post type is registered
echo "\nPost type 'job' registered: " . (post_type_exists('job') ? 'YES' : 'NO') . "\n";

echo "\n=== END ===\n";
