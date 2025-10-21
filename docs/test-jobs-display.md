# Test Page for Jobs Display

## Basic Jobs Display
```
[sleeve_ke_jobs]
```

## Jobs Display with Custom Parameters
```
[sleeve_ke_jobs columns="2" posts_per_page="6" show_filters="true" show_search="true"]
```

## Jobs Display List View
```
[sleeve_ke_jobs layout="list" columns="1" posts_per_page="10"]
```

## Usage Instructions

1. Create a new WordPress page
2. Add any of the shortcodes above to the page content
3. Publish the page and view it on the frontend
4. The jobs should display with search and filter functionality

## Debug Information

Check the WordPress debug log for any error messages:
- `wp-content/debug.log` (if WP_DEBUG_LOG is enabled)
- Browser console for JavaScript errors
- Network tab for AJAX request/response issues

## Troubleshooting

If no jobs appear:
1. Check if the 'job' post type is registered correctly
2. Verify sample jobs were created (check admin > Posts > Jobs)
3. Check the debug logs for SQL queries and errors
4. Ensure the shortcode is properly formatted