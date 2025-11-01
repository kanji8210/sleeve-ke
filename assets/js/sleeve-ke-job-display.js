/**
 * Frontend JavaScript for Sleeve KE job display.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/assets/js
 */

(function($) {
    'use strict';

    // Fallback localized AJAX object and debug flag
    var AJAX = (typeof sleeve_ke_jobs_ajax !== 'undefined') ? sleeve_ke_jobs_ajax : { ajax_url: '', nonce: '', user_id: 0, debug: false };
    const DEBUG_MODE = (typeof AJAX !== 'undefined' && AJAX.debug) ? AJAX.debug : (typeof sleeve_ke_debug !== 'undefined' ? sleeve_ke_debug : false);

    // Debug function
    function debugLog(message, data = null) {
        if (DEBUG_MODE || window.location.search.includes('debug=1')) {
            console.log('🔍 Sleeve KE Debug:', message);
            if (data) {
                console.log('📊 Data:', data);
            }
        }
    }

    // Document ready
    $(document).ready(function() {
        debugLog('Document ready - initializing job display');
        debugLog('Available globals:', {
            'sleeve_ke_jobs_ajax': typeof AJAX !== 'undefined' ? AJAX : 'NOT FOUND',
            'jQuery version': $.fn.jquery
        });
        
        initJobDisplay();
    });

    /**
     * Initialize job display functionality
     */
    function initJobDisplay() {
        debugLog('Initializing job display components');
        
        // Check if job container exists
        const $container = $('.sleeve-ke-jobs-container');
        if ($container.length === 0) {
            debugLog('❌ No job container found - shortcode may not be loaded');
            return;
        }
        
        debugLog('✅ Job container found', {
            'container': $container.length,
            'data-attributes': {
                'columns': $container.data('columns'),
                'posts-per-page': $container.data('posts-per-page'),
                'layout': $container.data('layout')
            }
        });
        
        // Search form handling
        setupSearchForm();
        
        // Filters handling
        setupFilters();
        
        // Layout switching
        setupLayoutSwitching();
        
        // Job actions
        setupJobActions();
        
        // Pagination handling
        setupPagination();
        
        // URL state management
        setupUrlState();
        
        debugLog('All components initialized');
    }

    /**
     * Setup search form
     */
    function setupSearchForm() {
        debugLog('Setting up search form');
        
        const $searchForm = $('#jobs-search-form');
        if ($searchForm.length === 0) {
            debugLog('⚠️ Search form not found');
            return;
        }
        
        debugLog('✅ Search form found');
        
        $searchForm.on('submit', function(e) {
            e.preventDefault();
            debugLog('Search form submitted');
            performSearch();
        });

        // Clear search
        $('.clear-btn').on('click', function() {
            debugLog('Clear search button clicked');
            $('#jobs-search-form')[0].reset();
            performSearch();
        });

        // Auto-search on input (debounced)
        var searchTimeout;
        $('#job-keyword, #job-location').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                performSearch();
            }, 500);
        });
    }

    /**
     * Setup filters
     */
    function setupFilters() {
        // Toggle filters visibility
        $('.toggle-filters-btn').on('click', function() {
            $(this).closest('.jobs-filters').find('.filters-content').slideToggle();
            $(this).toggleClass('active');
        });

        // Apply filters
        $('.apply-filters').on('click', function() {
            performSearch();
        });

        // Reset filters
        $('.reset-filters').on('click', function() {
            $('.filters-content select').val('');
            performSearch();
        });

        // Auto-filter on change
        $('.filters-content select').on('change', function() {
            performSearch();
        });

        // Clear all filters
        $('.clear-filters').on('click', function() {
            $('#jobs-search-form')[0].reset();
            $('.filters-content select').val('');
            performSearch();
        });
    }

    /**
     * Setup layout switching
     */
    function setupLayoutSwitching() {
        $('.layout-btn').on('click', function() {
            var layout = $(this).data('layout');
            var $container = $('.sleeve-ke-jobs-container');
            var $grid = $('.jobs-grid');
            
            // Update buttons
            $('.layout-btn').removeClass('active');
            $(this).addClass('active');
            
            // Update grid layout
            $grid.removeClass('layout-grid layout-list')
                 .addClass('layout-' + layout);
            
            // Update container data
            $container.attr('data-layout', layout);
            
            // Save preference in localStorage
            localStorage.setItem('sleeve_ke_job_layout', layout);
            
            // Trigger custom event
            $(document).trigger('sleeve_ke_layout_changed', [layout]);
        });

        // Load saved layout preference
        var savedLayout = localStorage.getItem('sleeve_ke_job_layout');
        if (savedLayout) {
            $('.layout-btn[data-layout="' + savedLayout + '"]').click();
        }
    }

    /**
     * Setup job actions
     */
    function setupJobActions() {
        debugLog('Setting up job actions');
        
        // Save job
        $(document).on('click', '.save-job', function(e) {
            e.preventDefault();
            debugLog('Save job button clicked');
            
            var $btn = $(this);
            var jobId = $btn.data('job-id');
            
            debugLog('Job action details:', {
                'jobId': jobId,
                'buttonHtml': $btn.prop('outerHTML'),
                'isSaved': $btn.hasClass('saved')
            });
            
            if (!jobId) {
                debugLog('❌ No job ID found on button');
                showNotification('Error: Job ID not found', 'error');
                return;
            }
            
            if ($btn.hasClass('saved')) {
                debugLog('Unsaving job:', jobId);
                unsaveJob(jobId, $btn);
            } else {
                debugLog('Saving job:', jobId);
                saveJob(jobId, $btn);
            }
        });

        // Apply to job (if apply functionality exists)
        $(document).on('click', '.apply-job', function(e) {
            e.preventDefault();
            debugLog('Apply to job button clicked');
            
            var jobId = $(this).data('job-id');
            debugLog('Apply to job ID:', jobId);
            
            if (!jobId) {
                debugLog('❌ No job ID found for apply action');
                showNotification('Error: Job ID not found', 'error');
                return;
            }
            
            showApplyModal(jobId);
        });
        
        debugLog('✅ Job actions set up complete');
    }

    /**
     * Setup pagination
     */
    function setupPagination() {
        $(document).on('click', '.jobs-pagination .page-numbers', function(e) {
            e.preventDefault();
            var $link = $(this);
            
            if ($link.hasClass('current') || $link.hasClass('dots')) {
                return;
            }
            
            var href = $link.attr('href');
            var page = getParameterByName('paged', href) || 1;
            
            performSearch(page);
        });
    }

    /**
     * Setup URL state management
     */
    function setupUrlState() {
        // Load initial state from URL
        loadStateFromUrl();
        
        // Handle back/forward button
        window.addEventListener('popstate', function(e) {
            if (e.state) {
                loadStateFromUrl();
                performSearch(1, false);
            }
        });
    }

    /**
     * Perform search with current filters
     */
    function performSearch(page = 1, updateUrl = true) {
        debugLog('Performing search', { page: page, updateUrl: updateUrl });
        
        var $container = $('.sleeve-ke-jobs-container');
        var $grid = $('.jobs-grid');
        var $loading = $('.jobs-loading');
        var $resultsHeader = $('.jobs-results-header');
        
        if ($container.length === 0) {
            debugLog('❌ Job container not found for search');
            return;
        }
        
        // Show loading
        $loading.show();
        $grid.hide();
        
        // Collect search parameters
        var params = {
            action: 'sleeve_ke_filter_jobs',
            nonce: AJAX.nonce,
            paged: page,
            columns: $container.data('columns'),
            posts_per_page: $container.data('posts-per-page'),
            layout: $container.data('layout'),
            show_company_logo: 'true',
            show_salary: 'true',
            show_date: 'true'
        };

        // Add search fields
        params.keyword = $('#job-keyword').val();
        params.location = $('#job-location').val();

        // Add filters
        params.job_type = $('#filter-job-type').val();
        params.experience_level = $('#filter-experience').val();
        params.min_salary = $('#filter-salary').val();
        params.remote_work = $('#filter-remote').val();
        params.date_posted = $('#filter-date').val();
        
        debugLog('Search parameters:', params);

        // Check for required AJAX data
        if (!AJAX.ajax_url) {
            debugLog('❌ AJAX data not available');
            showErrorMessage('AJAX configuration error. Please refresh the page.');
            return;
        }

        // Update URL if needed
        if (updateUrl) {
            updateUrlState(params, page);
        }

        // Perform AJAX request
    debugLog('Sending AJAX request to:', AJAX.ajax_url);
        
        $.ajax({
            url: AJAX.ajax_url,
            type: 'POST',
            data: params,
            beforeSend: function() {
                debugLog('AJAX request started');
            },
            success: function(response) {
                debugLog('AJAX response received:', response);
                
                if (response.success) {
                    debugLog('✅ Search successful', {
                        'found_posts': response.data.found_posts,
                        'max_pages': response.data.max_pages,
                        'html_length': response.data.html ? response.data.html.length : 0
                    });
                    
                    // Update results
                    $grid.html(response.data.html);
                    
                    // Update results count
                    updateResultsCount(response.data.found_posts);
                    
                    // Update pagination
                    updatePagination(page, response.data.max_pages, params);
                    
                    // Scroll to results
                    if (page > 1) {
                        scrollToResults();
                    }
                    
                    // Trigger custom event
                    $(document).trigger('sleeve_ke_jobs_loaded', [response.data]);
                }
            },
            error: function() {
                debugLog('❌ AJAX error during search - unknown error');
                showErrorMessage('An error occurred while loading jobs.');
            },
            complete: function() {
                $loading.hide();
                $grid.show();
            }
        });
    }

    /**
     * Save job
     */
    function saveJob(jobId, $btn) {
        debugLog('Attempting to save job', { jobId: jobId });
        
        $btn.addClass('loading');
        
        const requestData = {
            action: 'sleeve_ke_save_job',
            job_id: jobId,
            nonce: AJAX.nonce
        };
        
        debugLog('Save job request data:', requestData);
        
        $.ajax({
            url: AJAX.ajax_url,
            type: 'POST',
            data: requestData,
            beforeSend: function() {
                debugLog('Sending save job request');
            },
            success: function(response) {
                debugLog('Save job response:', response);
                
                if (response.success) {
                    debugLog('✅ Job saved successfully');
                    $btn.addClass('saved')
                        .removeClass('loading')
                        .find('.dashicons')
                        .removeClass('dashicons-heart')
                        .addClass('dashicons-heart-filled');
                    
                    showNotification('Job saved', 'success');
                } else {
                    debugLog('❌ Failed to save job:', response.data);
                    showNotification(response.data || 'Error saving job', 'error');
                }
            },
            error: function(xhr, status, error) {
                var respText = xhr && xhr.responseText ? xhr.responseText : '(no response)';
                debugLog('❌ AJAX error saving job', { status: xhr.status, statusText: xhr.statusText, error: error, responseText: respText });
                try {
                    var parsed = JSON.parse(respText);
                    debugLog('Parsed error JSON:', parsed);
                } catch (e) {
                    // not JSON
                }
                showNotification('Error saving job', 'error');
            },
            complete: function() {
                debugLog('Save job request completed');
                $btn.removeClass('loading');
            }
        });
    }

    /**
     * Unsave job
     */
    function unsaveJob(jobId, $btn) {
        debugLog('Attempting to unsave job', { jobId: jobId });
        
        $btn.addClass('loading');
        
        const requestData = {
            action: 'sleeve_ke_unsave_job',
            job_id: jobId,
            nonce: AJAX.nonce
        };
        
        debugLog('Unsave job request data:', requestData);
        
        $.ajax({
            url: AJAX.ajax_url,
            type: 'POST',
            data: requestData,
            beforeSend: function() {
                debugLog('Sending unsave job request');
            },
            success: function(response) {
                debugLog('Unsave job response:', response);
                
                if (response.success) {
                    debugLog('✅ Job unsaved successfully');
                    $btn.removeClass('saved loading')
                        .find('.dashicons')
                        .removeClass('dashicons-heart-filled')
                        .addClass('dashicons-heart');
                    
                    showNotification('Job removed from favorites', 'success');
                } else {
                    debugLog('❌ Failed to unsave job:', response.data);
                    showNotification(response.data || 'Error removing job', 'error');
                }
            },
            error: function(xhr, status, error) {
                var respText = xhr && xhr.responseText ? xhr.responseText : '(no response)';
                debugLog('❌ AJAX error unsaving job', { status: xhr.status, statusText: xhr.statusText, error: error, responseText: respText });
                try {
                    var parsed = JSON.parse(respText);
                    debugLog('Parsed error JSON:', parsed);
                } catch (e) {
                    // not JSON
                }
                showNotification('Error removing job', 'error');
            },
            complete: function() {
                debugLog('Unsave job request completed');
                $btn.removeClass('loading');
            }
        });
    }

    /**
     * Update results count
     */
    function updateResultsCount(count) {
        var text = count === 1 ? count + ' job found' : count + ' jobs found';
        $('.results-count').text(text);
    }

    /**
     * Update pagination
     */
    function updatePagination(currentPage, maxPages, params) {
        if (maxPages <= 1) {
            $('.jobs-pagination').hide();
            return;
        }

        var paginationHtml = '';
        var range = 2; // Number of pages to show on each side of current page
        
        // Previous button
        if (currentPage > 1) {
            paginationHtml += '<a href="#" class="page-numbers" data-page="' + (currentPage - 1) + '">&laquo; Précédent</a>';
        }
        
        // First page
        if (currentPage > range + 2) {
            paginationHtml += '<a href="#" class="page-numbers" data-page="1">1</a>';
            if (currentPage > range + 3) {
                paginationHtml += '<span class="page-numbers dots">...</span>';
            }
        }
        
        // Pages around current
        for (var i = Math.max(1, currentPage - range); i <= Math.min(maxPages, currentPage + range); i++) {
            if (i === currentPage) {
                paginationHtml += '<span class="page-numbers current">' + i + '</span>';
            } else {
                paginationHtml += '<a href="#" class="page-numbers" data-page="' + i + '">' + i + '</a>';
            }
        }
        
        // Last page
        if (currentPage < maxPages - range - 1) {
            if (currentPage < maxPages - range - 2) {
                paginationHtml += '<span class="page-numbers dots">...</span>';
            }
            paginationHtml += '<a href="#" class="page-numbers" data-page="' + maxPages + '">' + maxPages + '</a>';
        }
        
        // Next button
        if (currentPage < maxPages) {
            paginationHtml += '<a href="#" class="page-numbers" data-page="' + (currentPage + 1) + '">Suivant &raquo;</a>';
        }
        
        $('.jobs-pagination').html(paginationHtml).show();
        
        // Handle pagination clicks
        $('.jobs-pagination .page-numbers').on('click', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            if (page && !$(this).hasClass('current') && !$(this).hasClass('dots')) {
                performSearch(page);
            }
        });
    }

    /**
     * Update URL state
     */
    function updateUrlState(params, page) {
        var url = new URL(window.location);
        
        // Clear existing parameters
        url.searchParams.delete('keyword');
        url.searchParams.delete('location');
        url.searchParams.delete('job_type');
        url.searchParams.delete('experience_level');
        url.searchParams.delete('min_salary');
        url.searchParams.delete('remote_work');
        url.searchParams.delete('date_posted');
        url.searchParams.delete('paged');
        
        // Add new parameters
        if (params.keyword) url.searchParams.set('keyword', params.keyword);
        if (params.location) url.searchParams.set('location', params.location);
        if (params.job_type) url.searchParams.set('job_type', params.job_type);
        if (params.experience_level) url.searchParams.set('experience_level', params.experience_level);
        if (params.min_salary) url.searchParams.set('min_salary', params.min_salary);
        if (params.remote_work) url.searchParams.set('remote_work', params.remote_work);
        if (params.date_posted) url.searchParams.set('date_posted', params.date_posted);
        if (page > 1) url.searchParams.set('paged', page);
        
        // Update browser history
        window.history.pushState(params, '', url.toString());
    }

    /**
     * Load state from URL
     */
    function loadStateFromUrl() {
        var params = new URLSearchParams(window.location.search);
        
        // Load search fields
        $('#job-keyword').val(params.get('keyword') || '');
        $('#job-location').val(params.get('location') || '');
        
        // Load filters
        $('#filter-job-type').val(params.get('job_type') || '');
        $('#filter-experience').val(params.get('experience_level') || '');
        $('#filter-salary').val(params.get('min_salary') || '');
        $('#filter-remote').val(params.get('remote_work') || '');
        $('#filter-date').val(params.get('date_posted') || '');
    }

    /**
     * Scroll to results
     */
    function scrollToResults() {
        $('html, body').animate({
            scrollTop: $('.jobs-results-header').offset().top - 100
        }, 500);
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        $('.job-notification').remove();
        
        var notification = $('<div class="job-notification notification-' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        // Show notification
        setTimeout(function() {
            notification.addClass('show');
        }, 100);
        
        // Hide notification after 3 seconds
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Show error message
     */
    function showErrorMessage(message) {
        $('.jobs-grid').html(
            '<div class="no-jobs-found error">' +
                '<div class="no-jobs-icon">' +
                    '<span class="dashicons dashicons-warning"></span>' +
                '</div>' +
                '<h3>Error</h3>' +
                '<p>' + message + '</p>' +
                '<button class="btn btn-primary" onclick="location.reload()">Retry</button>' +
            '</div>'
        );
    }

    /**
     * Get parameter from URL
     */
    function getParameterByName(name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
        var results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }

    /**
     * Initialize saved jobs state
     */
    function initSavedJobsState() {
        // Check if user is logged in and load saved jobs
        if (AJAX.user_id) {
            debugLog('Loading saved jobs for user: ' + AJAX.user_id);
            $.ajax({
                url: AJAX.ajax_url,
                type: 'POST',
                data: {
                    action: 'sleeve_ke_get_saved_jobs',
                    nonce: AJAX.nonce
                },
                success: function(response) {
                    debugLog('Saved jobs response:', response);
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function(jobId) {
                            $('.save-job[data-job-id="' + jobId + '"]')
                                .addClass('saved')
                                .find('.dashicons')
                                .removeClass('dashicons-heart')
                                .addClass('dashicons-heart-filled');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    debugLog('Error loading saved jobs:', { xhr: xhr, status: status, error: error });
                }
            });
        }
    }

    // Initialize saved jobs state when document is ready
    $(document).ready(function() {
        initSavedJobsState();
    });

    // Expose functions globally for external use
    window.SleeveKEJobs = {
        performSearch: performSearch,
        saveJob: saveJob,
        unsaveJob: unsaveJob,
        showNotification: showNotification
    };

})(jQuery);

// Add notification styles if not already present
if (!document.querySelector('style[data-sleeve-ke-notifications]')) {
    var style = document.createElement('style');
    style.setAttribute('data-sleeve-ke-notifications', '');
    style.textContent = `
        .job-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            font-weight: 600;
            z-index: 9999;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        .job-notification.show {
            transform: translateX(0);
        }
        .job-notification.notification-success {
            background: #28a745;
        }
        .job-notification.notification-error {
            background: #dc3545;
        }
        .job-notification.notification-info {
            background: #17a2b8;
        }
    `;
    document.head.appendChild(style);
}