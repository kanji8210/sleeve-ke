<?php
/**
 * Delete all sample/test jobs
 * 
 * Access: Direct URL or include from admin
 */

// Load WordPress
if (!defined('ABSPATH')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
}

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}

// Process deletion if confirmed
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes' && check_admin_referer('delete_sample_jobs')) {
    $deleted_count = 0;
    
    // Get all jobs
    $jobs = get_posts(array(
        'post_type' => 'job',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($jobs as $job) {
        $should_delete = false;
        
        // Check content for sample patterns
        if (stripos($job->post_content, 'Sample job') !== false ||
            stripos($job->post_content, 'test job') !== false) {
            $should_delete = true;
        }
        
        // Check title for sample patterns
        if (stripos($job->post_title, 'Sample') !== false ||
            stripos($job->post_title, 'Test Job') !== false) {
            $should_delete = true;
        }
        
        // Check company_name meta for SampleCo
        $company_name = get_post_meta($job->ID, 'company_name', true);
        if (!empty($company_name) && stripos($company_name, 'SampleCo') !== false) {
            $should_delete = true;
        }
        
        // Check company_name meta for test patterns
        if (!empty($company_name) && (stripos($company_name, 'Test Company') !== false || 
            stripos($company_name, 'Sample Company') !== false)) {
            $should_delete = true;
        }
        
        // Delete if matches any pattern
        if ($should_delete) {
            wp_delete_post($job->ID, true); // Force delete permanently
            $deleted_count++;
        }
    }
    
    // Redirect with success message
    $redirect_url = add_query_arg(array(
        'page' => 'sleeve-ke',
        'sample_jobs_deleted' => $deleted_count
    ), admin_url('admin.php'));
    
    wp_safe_redirect($redirect_url);
    exit;
}

// Display confirmation page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Sample Jobs - Sleeve KE</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f0f0f1;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1d2327;
            border-bottom: 2px solid #d63638;
            padding-bottom: 10px;
        }
        .warning {
            background: #fcf3cd;
            border-left: 4px solid #dba617;
            padding: 15px;
            margin: 20px 0;
        }
        .job-list {
            background: #f6f7f7;
            padding: 15px;
            margin: 20px 0;
            max-height: 300px;
            overflow-y: auto;
            border-radius: 4px;
        }
        .job-item {
            padding: 8px;
            margin: 5px 0;
            background: white;
            border-left: 3px solid #d63638;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 10px 10px 0;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
        }
        .button-danger {
            background: #d63638;
            color: white;
        }
        .button-danger:hover {
            background: #b32d2e;
            color: white;
        }
        .button-secondary {
            background: #f0f0f1;
            color: #1d2327;
            border: 1px solid #8c8f94;
        }
        .button-secondary:hover {
            background: #e0e0e1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Delete Sample/Test Jobs</h1>
        
        <?php
        // Get all jobs that match sample patterns
        $jobs_to_delete = array();
        $jobs = get_posts(array(
            'post_type' => 'job',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));
        
        foreach ($jobs as $job) {
            $should_delete = false;
            $reason = '';
            
            // Check content
            if (stripos($job->post_content, 'Sample job') !== false ||
                stripos($job->post_content, 'test job') !== false) {
                $should_delete = true;
                $reason = 'Content contains "sample" or "test"';
            }
            
            // Check title
            if (stripos($job->post_title, 'Sample') !== false ||
                stripos($job->post_title, 'Test Job') !== false) {
                $should_delete = true;
                $reason = 'Title contains "sample" or "test"';
            }
            
            // Check company_name
            $company_name = get_post_meta($job->ID, 'company_name', true);
            if (!empty($company_name) && (stripos($company_name, 'SampleCo') !== false ||
                stripos($company_name, 'Test Company') !== false ||
                stripos($company_name, 'Sample Company') !== false)) {
                $should_delete = true;
                $reason = 'Company name contains "sample" or "test"';
            }
            
            if ($should_delete) {
                $jobs_to_delete[] = array(
                    'id' => $job->ID,
                    'title' => $job->post_title,
                    'company' => $company_name,
                    'status' => $job->post_status,
                    'reason' => $reason
                );
            }
        }
        ?>
        
        <?php if (empty($jobs_to_delete)): ?>
            <div class="warning">
                <p><strong>No sample/test jobs found!</strong></p>
                <p>There are no jobs matching the sample/test patterns (containing "Sample", "Test", or "SampleCo").</p>
            </div>
            <a href="<?php echo admin_url('admin.php?page=sleeve-ke'); ?>" class="button button-secondary">← Back to Dashboard</a>
        <?php else: ?>
            <div class="warning">
                <p><strong>Warning!</strong> This action will permanently delete the following <?php echo count($jobs_to_delete); ?> job(s):</p>
            </div>
            
            <div class="job-list">
                <?php foreach ($jobs_to_delete as $job): ?>
                    <div class="job-item">
                        <strong><?php echo esc_html($job['title']); ?></strong> (ID: <?php echo $job['id']; ?>)<br>
                        Company: <?php echo esc_html($job['company'] ?: 'N/A'); ?> | 
                        Status: <?php echo esc_html($job['status']); ?><br>
                        <em>Reason: <?php echo esc_html($job['reason']); ?></em>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <p><strong>Are you sure you want to permanently delete these jobs?</strong></p>
            <p>This action cannot be undone. All associated data will be removed.</p>
            
            <?php $delete_url = wp_nonce_url(add_query_arg('confirm', 'yes'), 'delete_sample_jobs'); ?>
            
            <a href="<?php echo esc_url($delete_url); ?>" class="button button-danger" 
               onclick="return confirm('Are you absolutely sure? This will permanently delete <?php echo count($jobs_to_delete); ?> jobs!');">
                🗑️ Yes, Delete All <?php echo count($jobs_to_delete); ?> Sample Jobs
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=sleeve-ke'); ?>" class="button button-secondary">
                Cancel
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
