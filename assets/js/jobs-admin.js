(function($) {
    'use strict';

    class SleeveKEJobsAdmin {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.storeOriginalValues();
        }

        bindEvents() {
            // Handle status change
            $(document).on('change', '.status-select', this.handleStatusChange.bind(this));
            
            // Handle select all checkbox
            $(document).on('change', '#cb-select-all', this.handleSelectAll.bind(this));
            
            // Handle form validation
            $(document).on('submit', '.sleeve-ke-job-form', this.handleFormSubmit.bind(this));
        }

        storeOriginalValues() {
            $('.status-select').each(function() {
                $(this).data('original-value', $(this).val());
            });
        }

        handleStatusChange(e) {
            const $select = $(e.target);
            const jobId = $select.data('job-id');
            const newStatus = $select.val();
            
            // Show loading state
            $select.prop('disabled', true).addClass('updating');
            
            $.post(sleeve_ke_ajax.ajax_url, {
                action: 'update_job_status',
                job_id: jobId,
                status: newStatus,
                nonce: sleeve_ke_ajax.nonce
            }, (response) => {
                if (response.success) {
                    // Update the status badge
                    const $row = $select.closest('tr');
                    const $badge = $row.find('.status-badge');
                    $badge.removeClass().addClass('status-badge status-' + newStatus).text(response.data.new_status_label);
                    
                    // Show success message
                    this.showNotice(sleeve_ke_ajax.i18n.status_updated, 'success');
                } else {
                    // Revert select to original value
                    $select.val($select.data('original-value'));
                    this.showNotice(response.data.message || sleeve_ke_ajax.i18n.error_updating, 'error');
                }
            }).fail(() => {
                $select.val($select.data('original-value'));
                this.showNotice(sleeve_ke_ajax.i18n.network_error, 'error');
            }).always(() => {
                $select.prop('disabled', false).removeClass('updating');
            });
        }

        handleSelectAll(e) {
            $('input[name="job_ids[]"]').prop('checked', e.target.checked);
        }

        handleFormSubmit(e) {
            const $form = $(e.target);
            let isValid = true;
            const errors = [];

            // Validate required fields
            $form.find('[required]').each(function() {
                const $field = $(this);
                if (!$field.val().trim()) {
                    isValid = false;
                    $field.addClass('field-error');
                    errors.push($field.attr('name'));
                } else {
                    $field.removeClass('field-error');
                }
            });

            // Validate salary range
            const salaryMin = $form.find('input[name="salary_min"]').val();
            const salaryMax = $form.find('input[name="salary_max"]').val();
            
            if (salaryMin && salaryMax && parseInt(salaryMin) > parseInt(salaryMax)) {
                isValid = false;
                this.showNotice('Minimum salary cannot be greater than maximum salary.', 'error');
            }

            if (!isValid) {
                e.preventDefault();
                this.showNotice('Please fill in all required fields correctly.', 'error');
                return false;
            }

            return true;
        }

        showNotice(message, type) {
            // Remove existing notices
            $('.sleeve-ke-notice').remove();
            
            const notice = $('<div class="notice notice-' + type + ' is-dismissible sleeve-ke-notice"><p>' + message + '</p></div>');
            $('.wrap h1').after(notice);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notice.fadeOut(() => {
                    notice.remove();
                });
            }, 5000);
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new SleeveKEJobsAdmin();
    });

})(jQuery);