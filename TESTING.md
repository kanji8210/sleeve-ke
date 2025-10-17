# Testing Guide for Sleeve KE Plugin

## Manual Testing in WordPress

### Prerequisites
- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### Installation and Activation Testing

1. **Upload the plugin**:
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   git clone https://github.com/kanji8210/sleeve-ke.git
   ```

2. **Activate the plugin**:
   - Log in to WordPress admin panel
   - Navigate to Plugins → Installed Plugins
   - Find "Sleeve KE" and click "Activate"

3. **Verify Role Creation**:
   After activation, verify that the custom roles were created:
   - Navigate to Users → Add New
   - Check the "Role" dropdown - you should see:
     - Employer
     - Candidate
     - Sleve Admin

4. **Verify Database Tables**:
   Connect to your MySQL database and run:
   ```sql
   SHOW TABLES LIKE 'wp_sleeve_%';
   ```
   You should see 5 tables:
   - `wp_sleeve_jobs`
   - `wp_sleeve_applications`
   - `wp_sleeve_candidates`
   - `wp_sleeve_employers`
   - `wp_sleeve_payments`

5. **Check Table Structure**:
   ```sql
   DESCRIBE wp_sleeve_jobs;
   DESCRIBE wp_sleeve_applications;
   DESCRIBE wp_sleeve_candidates;
   DESCRIBE wp_sleeve_employers;
   DESCRIBE wp_sleeve_payments;
   ```

### Testing User Roles

#### Test Employer Role:
1. Create a new user with the "Employer" role
2. Log in as that user
3. Verify capabilities:
   - Can read content
   - Can upload files
   - Has custom capabilities for job management

#### Test Candidate Role:
1. Create a new user with the "Candidate" role
2. Log in as that user
3. Verify capabilities:
   - Can read content
   - Can upload files
   - Has custom capabilities for job applications

#### Test Sleve Admin Role:
1. Create a new user with the "Sleve Admin" role
2. Log in as that user
3. Verify elevated capabilities for managing the entire job board

### Testing Deactivation

1. **Deactivate the plugin**:
   - Navigate to Plugins → Installed Plugins
   - Click "Deactivate" on Sleeve KE

2. **Verify data persistence**:
   - Roles should still exist
   - Database tables should still exist
   - This is expected behavior - data is preserved on deactivation

### Testing Uninstallation

**WARNING**: This will delete ALL plugin data permanently!

1. **Uninstall the plugin**:
   - First deactivate the plugin if it's active
   - Click "Delete" on the Sleeve KE plugin
   - Confirm the deletion

2. **Verify cleanup**:
   - Check Users → Add New - custom roles should be removed
   - Check database - custom tables should be dropped
   - Plugin options should be deleted

### Automated Testing

A basic test script is provided in `/tmp/test-initialization.php` that simulates the WordPress environment and tests:
- Role creation
- Database table creation
- Activation process
- Role removal
- Table removal

Run it with:
```bash
php /tmp/test-initialization.php
```

## Expected Behavior Summary

### On Activation:
✓ 3 custom user roles created (employer, candidate, sleve_admin)
✓ 5 database tables created
✓ Plugin version and activation date saved
✓ Rewrite rules flushed

### On Deactivation:
✓ Rewrite rules flushed
✓ Data preserved (roles and tables remain)

### On Uninstall:
✓ All 3 custom roles removed
✓ All 5 database tables dropped
✓ All plugin options deleted
✓ All transients cleared

## Security Considerations

- Direct file access is prevented with `WPINC` check
- Database tables use proper WordPress prefixes
- SQL queries use proper escaping via WordPress functions
- Unique constraints prevent duplicate applications
- Foreign key relationships maintain data integrity

## Troubleshooting

### Roles not appearing:
- Ensure you're logged in as an Administrator
- Clear WordPress cache
- Deactivate and reactivate the plugin

### Tables not created:
- Check database user has CREATE TABLE permissions
- Check for MySQL errors in WordPress debug log
- Enable `WP_DEBUG` in wp-config.php

### Uninstall not removing data:
- Ensure uninstall.php has proper file permissions
- Check WordPress uninstall process completed without errors
