# Debugging Jobs Display Issue

## Problem
Jobs are not displaying despite being present in the jobs list.

## Debugging Steps Added

### 1. Enhanced Logging
Added detailed logging to `get_jobs_query()` method:
- Query arguments
- Number of posts found
- SQL query executed
- Available post types
- Job post type existence check
- Total jobs in database (any status)

### 2. Post Type Registration
Added `register_job_post_type()` method to ensure 'job' post type exists with proper configuration.

### 3. Sample Data Creation
Added `create_sample_jobs()` method to create test jobs automatically in debug mode.

### 4. Function Call Logging
Added logging to `display_jobs_listing()` to confirm method execution.

## Testing Instructions

1. **Enable WordPress Debug Logging**
   Add to `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. **Access Test Page**
   Create a WordPress page with shortcode: `[sleeve_ke_jobs]`

3. **Check Debug Logs**
   View `wp-content/debug.log` for:
   - "Sleeve KE: display_jobs_listing called"
   - "Sleeve KE Jobs Query Args"
   - "Found X posts"
   - "Job post type exists/does NOT exist"

4. **Use Debug Script**
   Access `/test-jobs-debug.php` to check:
   - Available post types
   - Existing jobs
   - Manual job creation
   - Shortcode output

## Common Issues to Check

1. **Post Type Not Registered**
   - Verify 'job' appears in available post types
   - Check post type registration timing

2. **No Job Posts**
   - Check WordPress admin for Jobs menu
   - Manually create a job post
   - Verify post status is 'publish'

3. **Query Issues**
   - Check meta_query parameters
   - Verify field names match database
   - Check for conflicting plugins

4. **Shortcode Registration**
   - Verify shortcode is registered properly
   - Check for naming conflicts
   - Test with minimal parameters

## Next Steps

Based on debug output, we can determine:
- Is the shortcode function being called?
- Is the job post type properly registered?
- Are there any jobs in the database?
- What does the SQL query look like?
- Are there any WordPress errors?