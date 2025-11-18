<?php
/**
 * Test Employer Creation Debug
 * 
 * This script helps debug employer creation issues by showing all errors.
 * Access: /wp-content/plugins/sleeve-ke/tools/test-employer-creation.php
 */

// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load WordPress
require_once('../../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

echo '<h1>Employer Creation Debug Test</h1>';
echo '<hr>';

// Check if employers table exists
global $wpdb;
$table_name = $wpdb->prefix . 'sleeve_employers';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

echo '<h2>1. Database Check</h2>';
echo '<p><strong>Employers Table:</strong> ' . ($table_exists ? '✓ EXISTS' : '✗ NOT FOUND') . '</p>';

if ($table_exists) {
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo '<p><strong>Total Employers:</strong> ' . $count . '</p>';
    
    // Show table structure
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    echo '<h3>Table Structure:</h3>';
    echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
    echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
    foreach ($columns as $col) {
        echo '<tr>';
        echo '<td>' . esc_html($col->Field) . '</td>';
        echo '<td>' . esc_html($col->Type) . '</td>';
        echo '<td>' . esc_html($col->Null) . '</td>';
        echo '<td>' . esc_html($col->Key) . '</td>';
        echo '<td>' . esc_html($col->Default ?? 'NULL') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

echo '<hr>';
echo '<h2>2. Employer Role Check</h2>';
$employer_role = get_role('employer');
if ($employer_role) {
    echo '<p>✓ Employer role exists</p>';
    echo '<p><strong>Capabilities:</strong></p>';
    echo '<ul>';
    foreach ($employer_role->capabilities as $cap => $granted) {
        if ($granted) {
            echo '<li>' . esc_html($cap) . '</li>';
        }
    }
    echo '</ul>';
} else {
    echo '<p style="color: red;">✗ Employer role NOT found</p>';
}

echo '<hr>';
echo '<h2>3. Upload Directory Check</h2>';
$upload_dir = wp_upload_dir();
echo '<p><strong>Upload Path:</strong> ' . esc_html($upload_dir['path']) . '</p>';
echo '<p><strong>Upload URL:</strong> ' . esc_html($upload_dir['url']) . '</p>';
echo '<p><strong>Writable:</strong> ' . (is_writable($upload_dir['path']) ? '✓ YES' : '✗ NO') . '</p>';

echo '<hr>';
echo '<h2>4. Profile Page Check</h2>';
$profile_page = get_page_by_path('employer-profile');
if ($profile_page) {
    echo '<p>✓ Profile page exists</p>';
    echo '<p><strong>Page ID:</strong> ' . $profile_page->ID . '</p>';
    echo '<p><strong>URL:</strong> <a href="' . get_permalink($profile_page->ID) . '">' . get_permalink($profile_page->ID) . '</a></p>';
    echo '<p><strong>Content:</strong> ' . esc_html(substr($profile_page->post_content, 0, 100)) . '</p>';
} else {
    echo '<p style="color: red;">✗ Profile page NOT found</p>';
}

echo '<hr>';
echo '<h2>5. Test Employer Creation</h2>';
echo '<form method="post" action="" enctype="multipart/form-data">';
wp_nonce_field('test_employer_creation', 'test_nonce');
?>
<table>
    <tr>
        <td>Username:</td>
        <td><input type="text" name="username" value="testemployer<?php echo rand(100, 999); ?>" required /></td>
    </tr>
    <tr>
        <td>Email:</td>
        <td><input type="email" name="email" value="test<?php echo rand(100, 999); ?>@example.com" required /></td>
    </tr>
    <tr>
        <td>Password:</td>
        <td><input type="password" name="password" value="Test@12345" required /></td>
    </tr>
    <tr>
        <td>Company Name:</td>
        <td><input type="text" name="company_name" value="Test Company <?php echo rand(100, 999); ?>" required /></td>
    </tr>
    <tr>
        <td>Industry:</td>
        <td>
            <select name="industry" required>
                <option value="technology">Technology</option>
                <option value="healthcare">Healthcare</option>
                <option value="finance">Finance</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>Company Size:</td>
        <td>
            <select name="company_size" required>
                <option value="1-10">1-10 employees</option>
                <option value="11-50">11-50 employees</option>
                <option value="51-200">51-200 employees</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>Company Logo:</td>
        <td><input type="file" name="company_logo" accept="image/*" /></td>
    </tr>
    <tr>
        <td colspan="2">
            <button type="submit" name="test_create" class="button button-primary">Test Create Employer</button>
        </td>
    </tr>
</table>
</form>

<?php
// Handle form submission
if (isset($_POST['test_create']) && wp_verify_nonce($_POST['test_nonce'], 'test_employer_creation')) {
    echo '<h3 style="color: blue;">CREATING EMPLOYER...</h3>';
    
    try {
        // Create user
        echo '<p>→ Creating WordPress user...</p>';
        $user_id = wp_create_user(
            sanitize_text_field($_POST['username']),
            $_POST['password'],
            sanitize_email($_POST['email'])
        );
        
        if (is_wp_error($user_id)) {
            throw new Exception('User creation failed: ' . $user_id->get_error_message());
        }
        
        echo '<p style="color: green;">✓ User created with ID: ' . $user_id . '</p>';
        
        // Set role
        echo '<p>→ Setting employer role...</p>';
        $user = new WP_User($user_id);
        $user->set_role('employer');
        echo '<p style="color: green;">✓ Role set to employer</p>';
        
        // Handle logo upload
        $logo_url = '';
        if (!empty($_FILES['company_logo']['name'])) {
            echo '<p>→ Uploading logo...</p>';
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($_FILES['company_logo'], $upload_overrides);
            
            if ($movefile && !isset($movefile['error'])) {
                $logo_url = $movefile['url'];
                echo '<p style="color: green;">✓ Logo uploaded: ' . esc_html($logo_url) . '</p>';
            } else {
                echo '<p style="color: orange;">⚠ Logo upload failed: ' . esc_html($movefile['error']) . '</p>';
            }
        }
        
        // Insert into database
        echo '<p>→ Inserting into employers table...</p>';
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'company_name' => sanitize_text_field($_POST['company_name']),
                'company_description' => '',
                'company_logo' => $logo_url,
                'phone' => '',
                'website' => '',
                'location' => '',
                'industry' => sanitize_text_field($_POST['industry']),
                'company_size' => sanitize_text_field($_POST['company_size']),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            throw new Exception('Database insert failed: ' . $wpdb->last_error);
        }
        
        echo '<p style="color: green;">✓ Database insert successful! Employer ID: ' . $wpdb->insert_id . '</p>';
        
        // Show profile link
        if ($profile_page) {
            $profile_url = get_permalink($profile_page->ID) . '?user_id=' . $user_id;
            echo '<p><strong>Profile URL:</strong> <a href="' . esc_url($profile_url) . '" target="_blank">' . esc_html($profile_url) . '</a></p>';
        }
        
        echo '<h3 style="color: green;">✓✓✓ EMPLOYER CREATED SUCCESSFULLY! ✓✓✓</h3>';
        
    } catch (Exception $e) {
        echo '<h3 style="color: red;">✗ ERROR: ' . esc_html($e->getMessage()) . '</h3>';
        echo '<p><strong>Last DB Error:</strong> ' . esc_html($wpdb->last_error) . '</p>';
        echo '<p><strong>Last Query:</strong> ' . esc_html($wpdb->last_query) . '</p>';
    }
}

echo '<hr>';
echo '<p><a href="' . admin_url('admin.php?page=sleeve-ke-employers') . '">← Back to Employers Admin</a></p>';
