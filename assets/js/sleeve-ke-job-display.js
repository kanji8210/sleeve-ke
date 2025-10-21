/**
 * Frontend JavaScript for Sleeve KE job display.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/assets/js
 */

(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        initJobDisplay();
    });

    /**
     * Initialize job display functionality
     */
    function initJobDisplay() {
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
    }

    /**
     * Setup search form
     */
    function setupSearchForm() {
        $('#jobs-search-form').on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Clear search
        $('.clear-btn').on('click', function() {
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
        // Save job
        $(document).on('click', '.save-job', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var jobId = $btn.data('job-id');
            
            if ($btn.hasClass('saved')) {
                unsaveJob(jobId, $btn);
            } else {
                saveJob(jobId, $btn);
            }
        });

        // Apply to job (if apply functionality exists)
        $(document).on('click', '.apply-job', function(e) {
            e.preventDefault();
            var jobId = $(this).data('job-id');
            showApplyModal(jobId);
        });
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
        var $container = $('.sleeve-ke-jobs-container');
        var $grid = $('.jobs-grid');
        var $loading = $('.jobs-loading');
        var $resultsHeader = $('.jobs-results-header');
        
        // Show loading
        $loading.show();
        $grid.hide();
        
        // Collect search parameters
        var params = {
            action: 'sleeve_ke_filter_jobs',
            nonce: sleeve_ke_jobs_ajax.nonce,
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

        // Update URL if needed
        if (updateUrl) {
            updateUrlState(params, page);
        }

        // Perform AJAX request
        $.ajax({
            url: sleeve_ke_jobs_ajax.ajax_url,
            type: 'POST',
            data: params,
            success: function(response) {
                if (response.success) {
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
        $btn.addClass('loading');
        
        $.ajax({
            url: sleeve_ke_jobs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sleeve_ke_save_job',
                job_id: jobId,
                nonce: sleeve_ke_jobs_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $btn.addClass('saved')
                        .removeClass('loading')
                        .find('.dashicons')
                        .removeClass('dashicons-heart')
                        .addClass('dashicons-heart-filled');
                    
                    showNotification('Job saved', 'success');
                } else {
                    showNotification(response.data || 'Error saving job', 'error');
                }
            },
            error: function() {
                showNotification('Error saving job', 'error');
            },
            complete: function() {
                $btn.removeClass('loading');
            }
        });
    }

    /**
     * Unsave job
     */
    function unsaveJob(jobId, $btn) {
        $btn.addClass('loading');
        
        $.ajax({
            url: sleeve_ke_jobs_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sleeve_ke_unsave_job',
                job_id: jobId,
                nonce: sleeve_ke_jobs_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $btn.removeClass('saved loading')
                        .find('.dashicons')
                        .removeClass('dashicons-heart-filled')
                        .addClass('dashicons-heart');
                    
                    showNotification('Job removed from favorites', 'success');
                } else {
                    showNotification(response.data || 'Error removing job', 'error');
                }
            },
            error: function() {
                showNotification('Error removing job', 'error');
            },
            complete: function() {
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
        if (sleeve_ke_jobs_ajax.user_id) {
            $.ajax({
                url: sleeve_ke_jobs_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sleeve_ke_get_saved_jobs',
                    nonce: sleeve_ke_jobs_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function(jobId) {
                            $('.save-job[data-job-id="' + jobId + '"]')
                                .addClass('saved')
                                .find('.dashicons')
                                .removeClass('dashicons-heart')
                                .addClass('dashicons-heart-filled');
                        });
                    }
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