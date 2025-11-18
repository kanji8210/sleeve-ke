(function($) {
    'use strict';

    class SleeveKEJobsAdmin {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Status change handler
            $(document).on('change', '.status-select', this.handleStatusChange.bind(this));

            // Bulk actions handler
            $(document).on('click', '#apply_bulk_action', this.handleBulkAction.bind(this));

            // Select all checkbox
            $(document).on('change', '#cb-select-all', this.toggleSelectAll.bind(this));

            // Form validation
            $(document).on('submit', '#sleeve-ke-job-form', this.validateJobForm.bind(this));
        }

        handleStatusChange(e) {
            const $select = $(e.target);
            const jobId = $select.data('job-id');
            const newStatus = $select.val();

            if (!jobId || !newStatus) {
                return;
            }

            this.updateJobStatus(jobId, newStatus, $select);
        }

        updateJobStatus(jobId, status, $select) {
            const originalValue = $select.data('original-value');
            
            // Show loading state
            $select.prop('disabled', true).addClass('updating');

            $.ajax({
                url: sleeve_ke_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_job_status',
                    job_id: jobId,
                    status: status,
                    nonce: sleeve_ke_ajax.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showNotice(response.data.message, 'success');
                        
                        // Update the status badge in the same row
                        const $row = $select.closest('tr');
                        const $badge = $row.find('.status-badge');
                        
                        $badge
                            .removeClass('status-draft status-published status-archived status-expired')
                            .addClass('status-' + status)
                            .text(response.data.new_status_label);
                            
                        $select.data('original-value', status);
                    } else {
                        this.showNotice(response.data.message, 'error');
                        $select.val(originalValue);
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotice(sleeve_ke_ajax.i18n.network_error, 'error');
                    $select.val(originalValue);
                },
                complete: () => {
                    $select.prop('disabled', false).removeClass('updating');
                }
            });
        }

        handleBulkAction(e) {
            const $button = $(e.target);
            const $form = $button.closest('form');
            const $select = $form.find('select[name="bulk_action"]');
            const action = $select.val();

            if (!action) {
                alert('Please select a bulk action.');
                e.preventDefault();
                return;
            }

            const $checked = $form.find('input[name="job_ids[]"]:checked');
            if ($checked.length === 0) {
                alert('Please select at least one job.');
                e.preventDefault();
                return;
            }

            if (action === 'delete') {
                if (!confirm(sleeve_ke_ajax.i18n.confirm_delete)) {
                    e.preventDefault();
                    return;
                }
            }
        }

        toggleSelectAll(e) {
            const $selectAll = $(e.target);
            const isChecked = $selectAll.prop('checked');
            $('input[name="job_ids[]"]').prop('checked', isChecked);
        }

        validateJobForm(e) {
            const $form = $(e.target);
            let isValid = true;
            const errors = [];

            // Check required fields
            $form.find('[required]').each(function() {
                const $field = $(this);
                if (!$field.val().trim()) {
                    isValid = false;
                    $field.addClass('field-error');
                    const fieldName = $field.attr('name').replace(/_/g, ' ');
                    errors.push(fieldName + ' is required.');
                } else {
                    $field.removeClass('field-error');
                }
            });

            // Validate salary range
            const $minSalary = $form.find('#salary_min');
            const $maxSalary = $form.find('#salary_max');
            
            if ($minSalary.val() && $maxSalary.val()) {
                const min = parseInt($minSalary.val());
                const max = parseInt($maxSalary.val());
                
                if (min > max) {
                    isValid = false;
                    $minSalary.addClass('field-error');
                    $maxSalary.addClass('field-error');
                    errors.push('Minimum salary cannot be greater than maximum salary.');
                } else {
                    $minSalary.removeClass('field-error');
                    $maxSalary.removeClass('field-error');
                }
            }

            if (!isValid) {
                e.preventDefault();
                this.showFormErrors(errors);
            }
        }

        showFormErrors(errors) {
            let errorHtml = '<div class="notice notice-error is-dismissible">';
            errorHtml += '<p><strong>Please fix the following errors:</strong></p>';
            errorHtml += '<ul style="list-style-type: disc; margin-left: 20px;">';
            
            errors.forEach(error => {
                errorHtml += '<li>' + error + '</li>';
            });
            
            errorHtml += '</ul></div>';
            
            $('.wrap').prepend(errorHtml);
        }

        showNotice(message, type = 'success') {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const noticeHtml = `<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`;
            
            $('.wrap').prepend(noticeHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                $('.notice').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new SleeveKEJobsAdmin();
    });

})(jQuery);