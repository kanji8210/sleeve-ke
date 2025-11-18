<?php
/**
 * Verify plugin activation - checks if roles and tables were created
 * 
 * Access: wp-admin/admin.php?page=sleeve-ke-verify-activation
 * Or run directly: wp-content/plugins/sleeve-ke/tools/verify-activation.php
 */

// Load WordPress
if (!defined('ABSPATH')) {
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
}

// Security check
if (!current_user_can('activate_plugins')) {
    wp_die('Insufficient permissions');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sleeve KE - Activation Verification</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            max-width: 900px;
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
            border-bottom: 2px solid #2271b1;
            padding-bottom: 10px;
        }
        h2 {
            color: #2271b1;
            margin-top: 30px;
        }
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .status-table th,
        .status-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .status-table th {
            background: #f6f7f7;
            font-weight: 600;
        }
        .status-ok {
            color: #00a32a;
            font-weight: bold;
        }
        .status-missing {
            color: #d63638;
            font-weight: bold;
        }
        .summary {
            background: #f0f6fc;
            border-left: 4px solid #2271b1;
            padding: 15px;
            margin: 20px 0;
        }
        .action-button {
            display: inline-block;
            padding: 10px 20px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .action-button:hover {
            background: #135e96;
        }
        .code {
            background: #f6f7f7;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: Consolas, Monaco, monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Sleeve KE - Activation Verification</h1>
        
        <?php
        global $wpdb;
        
        // Check Roles
        echo '<h2>📋 Custom Roles</h2>';
        echo '<table class="status-table">';
        echo '<thead><tr><th>Role</th><th>Status</th><th>Capabilities</th></tr></thead>';
        echo '<tbody>';
        
        $roles_to_check = array(
            'employer' => 'Employer',
            'candidate' => 'Candidate',
            'sleve_admin' => 'Sleve Admin'
        );
        
        $roles_ok = 0;
        $roles_missing = 0;
        
        foreach ($roles_to_check as $role_slug => $role_name) {
            $role = get_role($role_slug);
            if ($role) {
                $caps = array_keys($role->capabilities);
                $caps_count = count($caps);
                echo '<tr>';
                echo '<td><span class="code">' . esc_html($role_slug) . '</span></td>';
                echo '<td><span class="status-ok">✓ EXISTS</span></td>';
                echo '<td>' . $caps_count . ' capabilities</td>';
                echo '</tr>';
                $roles_ok++;
            } else {
                echo '<tr>';
                echo '<td><span class="code">' . esc_html($role_slug) . '</span></td>';
                echo '<td><span class="status-missing">✗ MISSING</span></td>';
                echo '<td>-</td>';
                echo '</tr>';
                $roles_missing++;
            }
        }
        
        echo '</tbody></table>';
        
        // Check Admin Capabilities
        $admin_role = get_role('administrator');
        $admin_caps_ok = 0;
        $admin_caps_missing = 0;
        $required_admin_caps = array('manage_jobs', 'manage_applications', 'manage_candidates', 'manage_employers', 'manage_payments');
        
        echo '<h2>👑 Administrator Custom Capabilities</h2>';
        echo '<table class="status-table">';
        echo '<thead><tr><th>Capability</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($required_admin_caps as $cap) {
            if ($admin_role && $admin_role->has_cap($cap)) {
                echo '<tr><td><span class="code">' . esc_html($cap) . '</span></td>';
                echo '<td><span class="status-ok">✓ EXISTS</span></td></tr>';
                $admin_caps_ok++;
            } else {
                echo '<tr><td><span class="code">' . esc_html($cap) . '</span></td>';
                echo '<td><span class="status-missing">✗ MISSING</span></td></tr>';
                $admin_caps_missing++;
            }
        }
        
        echo '</tbody></table>';
        
        // Check Database Tables
        echo '<h2>🗄️ Database Tables</h2>';
        echo '<table class="status-table">';
        echo '<thead><tr><th>Table Name</th><th>Status</th><th>Rows</th></tr></thead>';
        echo '<tbody>';
        
        $tables_to_check = array(
            $wpdb->prefix . 'sleeve_jobs' => 'Jobs',
            $wpdb->prefix . 'sleeve_applications' => 'Applications',
            $wpdb->prefix . 'sleeve_candidates' => 'Candidates',
            $wpdb->prefix . 'sleeve_employers' => 'Employers',
            $wpdb->prefix . 'sleeve_payments' => 'Payments'
        );
        
        $tables_ok = 0;
        $tables_missing = 0;
        
        foreach ($tables_to_check as $table_name => $description) {
            $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if ($exists) {
                $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                echo '<tr>';
                echo '<td><span class="code">' . esc_html($table_name) . '</span></td>';
                echo '<td><span class="status-ok">✓ EXISTS</span></td>';
                echo '<td>' . number_format($row_count) . ' rows</td>';
                echo '</tr>';
                $tables_ok++;
            } else {
                echo '<tr>';
                echo '<td><span class="code">' . esc_html($table_name) . '</span></td>';
                echo '<td><span class="status-missing">✗ MISSING</span></td>';
                echo '<td>-</td>';
                echo '</tr>';
                $tables_missing++;
            }
        }
        
        echo '</tbody></table>';
        
        // Summary
        $total_checks = count($roles_to_check) + count($required_admin_caps) + count($tables_to_check);
        $total_ok = $roles_ok + $admin_caps_ok + $tables_ok;
        $total_missing = $roles_missing + $admin_caps_missing + $tables_missing;
        
        echo '<div class="summary">';
        echo '<h2>📊 Summary</h2>';
        echo '<p><strong>Total Checks:</strong> ' . $total_checks . '</p>';
        echo '<p><span class="status-ok">✓ Passed: ' . $total_ok . '</span></p>';
        if ($total_missing > 0) {
            echo '<p><span class="status-missing">✗ Missing: ' . $total_missing . '</span></p>';
            echo '<p><strong>Action Required:</strong> Deactivate and reactivate the plugin to create missing items.</p>';
            echo '<a href="' . admin_url('plugins.php') . '" class="action-button">Go to Plugins</a>';
        } else {
            echo '<p><strong>🎉 All activation checks passed!</strong></p>';
            echo '<p>The plugin is properly activated with all roles and database tables created.</p>';
        }
        echo '</div>';
        
        // Additional Info
        echo '<h2>ℹ️ Additional Information</h2>';
        echo '<table class="status-table">';
        echo '<tr><td><strong>Plugin Version:</strong></td><td>' . (defined('SLEEVE_KE_VERSION') ? SLEEVE_KE_VERSION : 'Unknown') . '</td></tr>';
        echo '<tr><td><strong>DB Version:</strong></td><td>' . get_option('sleeve_ke_db_version', 'Not set') . '</td></tr>';
        echo '<tr><td><strong>WordPress Version:</strong></td><td>' . get_bloginfo('version') . '</td></tr>';
        echo '<tr><td><strong>PHP Version:</strong></td><td>' . PHP_VERSION . '</td></tr>';
        echo '<tr><td><strong>Database Charset:</strong></td><td>' . $wpdb->charset . '</td></tr>';
        echo '</table>';
        
        echo '<p style="margin-top: 30px;"><a href="' . admin_url('admin.php?page=sleeve-ke') . '" class="action-button">← Back to Dashboard</a></p>';
        ?>
    </div>
</body>
</html>
