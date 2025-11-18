<?php
/**
 * Test job shortcode functionality
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../../wp-load.php');

echo "=== TESTING JOB SHORTCODE ===\n\n";

// Test if shortcode is registered
global $shortcode_tags;
if ( isset( $shortcode_tags['sleeve_ke_jobs'] ) ) {
    echo "✓ Shortcode [sleeve_ke_jobs] is registered\n";
} else {
    echo "✗ Shortcode [sleeve_ke_jobs] is NOT registered\n";
}

// Simulate shortcode execution
echo "\n--- Executing shortcode ---\n";
$output = do_shortcode( '[sleeve_ke_jobs]' );

// Count jobs found in output
if ( strpos( $output, 'no-jobs-found' ) !== false ) {
    echo "✗ No jobs displayed\n";
} else {
    preg_match_all( '/class="job-card"/', $output, $matches );
    $job_count = count( $matches[0] );
    echo "✓ Found $job_count job card(s) in output\n";
}

// Check for errors
if ( strpos( $output, 'Debug Information' ) !== false ) {
    echo "\n⚠ Debug info present in output (check WP_DEBUG)\n";
}

// Show a snippet of the output
echo "\n--- Output snippet (first 500 chars) ---\n";
echo substr( strip_tags( $output ), 0, 500 ) . "...\n";

echo "\n=== END ===\n";
