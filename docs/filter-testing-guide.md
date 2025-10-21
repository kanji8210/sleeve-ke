# Jobs Display Test Configurations

## Default Display (21 jobs)
```
[sleeve_ke_jobs]
```

## Custom Display Options
```
[sleeve_ke_jobs columns="4" posts_per_page="12" layout="grid"]
```

## List View with More Jobs
```
[sleeve_ke_jobs layout="list" posts_per_page="15" columns="1"]
```

## Filtered Display
```
[sleeve_ke_jobs job_type="full-time" show_filters="true"]
```

## No Filters/Search (Clean Display)
```
[sleeve_ke_jobs show_filters="false" show_search="false" posts_per_page="21"]
```

## Testing Filter Functionality

### Job Types to Test:
- full-time
- part-time  
- contract
- freelance

### Experience Levels to Test:
- entry
- mid
- senior
- executive

### Remote Work Options:
- yes (Remote)
- no (On-site)
- hybrid (Hybrid)

### Locations to Test:
- Nairobi, Kenya
- Mombasa, Kenya
- Kisumu, Kenya
- Nakuru, Kenya

## JavaScript Filter Testing

Open browser console and test AJAX filtering:
```javascript
// Test filter by job type
jQuery.post(ajaxurl, {
    action: 'sleeve_ke_filter_jobs',
    nonce: sleeve_ke_jobs_ajax.nonce,
    job_type: 'full-time',
    posts_per_page: 21,
    columns: 3,
    layout: 'grid'
});
```

## Debug Checklist

1. **Check WordPress Debug Log** (`wp-content/debug.log`)
   - Look for "Sleeve KE Jobs" entries
   - Check query results and SQL
   - Verify filter applications

2. **Browser Console**
   - Check for JavaScript errors
   - Monitor AJAX requests/responses
   - Verify filter interactions

3. **Database Verification**
   - Check if jobs exist: Admin > Posts > Jobs
   - Verify meta fields are populated
   - Check post status is 'publish'

4. **Filter Testing Steps**
   - Load page with jobs
   - Apply each filter individually
   - Combine multiple filters
   - Test search functionality
   - Test pagination

## Expected Results

With the new sample data creation:
- **Total Jobs**: 25+ diverse jobs
- **Default Display**: First 21 jobs
- **Pagination**: Should show pagination if more than 21 jobs
- **Filters**: Each filter should reduce the displayed jobs appropriately
- **Search**: Should search in job titles and content
- **Layout Toggle**: Grid/List views should work smoothly