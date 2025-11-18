<?php
/**
 * Create Employer Profile Page
 * 
 * This script creates a WordPress page with the employer profile shortcode.
 * Run once via browser: /wp-content/plugins/sleeve-ke/tools/create-employer-profile-page.php
 */

// Load WordPress
require_once('../../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

// Check if page already exists
$existing_page = get_page_by_path('employer-profile');

if ($existing_page) {
    echo '<h2>Page Already Exists</h2>';
    echo '<p>The Employer Profile page already exists:</p>';
    echo '<ul>';
    echo '<li><strong>Title:</strong> ' . esc_html($existing_page->post_title) . '</li>';
    echo '<li><strong>Slug:</strong> ' . esc_html($existing_page->post_name) . '</li>';
    echo '<li><strong>Status:</strong> ' . esc_html($existing_page->post_status) . '</li>';
    echo '<li><strong>URL:</strong> <a href="' . get_permalink($existing_page->ID) . '">' . get_permalink($existing_page->ID) . '</a></li>';
    echo '<li><strong>Edit:</strong> <a href="' . admin_url('post.php?post=' . $existing_page->ID . '&action=edit') . '">Edit Page</a></li>';
    echo '</ul>';
    
    // Update content if needed
    if (strpos($existing_page->post_content, '[sleeve_ke_employer_profile]') === false) {
        wp_update_post(array(
            'ID' => $existing_page->ID,
            'post_content' => '[sleeve_ke_employer_profile]'
        ));
        echo '<p style="color: green;"><strong>✓</strong> Updated page content with shortcode.</p>';
    } else {
        echo '<p style="color: green;"><strong>✓</strong> Page already contains the shortcode.</p>';
    }
    
    exit;
}

// Create the page
$page_data = array(
    'post_title'    => 'Employer Profile',
    'post_content'  => '[sleeve_ke_employer_profile]',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_author'   => 1,
    'post_name'     => 'employer-profile',
    'comment_status' => 'closed',
    'ping_status'   => 'closed'
);

$page_id = wp_insert_post($page_data);

if (is_wp_error($page_id)) {
    echo '<h2 style="color: red;">Error Creating Page</h2>';
    echo '<p>' . esc_html($page_id->get_error_message()) . '</p>';
    exit;
}

echo '<h2 style="color: green;">✓ Page Created Successfully!</h2>';
echo '<p>The Employer Profile page has been created:</p>';
echo '<ul>';
echo '<li><strong>Page ID:</strong> ' . $page_id . '</li>';
echo '<li><strong>Title:</strong> Employer Profile</li>';
echo '<li><strong>Slug:</strong> employer-profile</li>';
echo '<li><strong>Shortcode:</strong> [sleeve_ke_employer_profile]</li>';
echo '<li><strong>URL:</strong> <a href="' . get_permalink($page_id) . '">' . get_permalink($page_id) . '</a></li>';
echo '<li><strong>Edit:</strong> <a href="' . admin_url('post.php?post=' . $page_id . '&action=edit') . '">Edit Page in Admin</a></li>';
echo '</ul>';

echo '<hr>';
echo '<h3>Next Steps:</h3>';
echo '<ol>';
echo '<li>Test employer registration with logo upload</li>';
echo '<li>Check that redirect goes to this profile page</li>';
echo '<li>Review PHP error log for debug messages</li>';
echo '<li>Verify employer data displays correctly</li>';
echo '</ol>';

echo '<hr>';
echo '<p><a href="' . admin_url('admin.php?page=sleeve-ke-employers') . '">← Back to Employers</a></p>';
