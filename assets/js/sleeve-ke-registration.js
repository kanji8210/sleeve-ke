/**
 * Frontend JavaScript for Sleeve KE registration forms.
 *
 * @package    Sleeve_KE
 * @subpackage Sleeve_KE/assets/js
 */

(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        initRegistrationForms();
    });

    /**
     * Initialize registration forms functionality
     */
    function initRegistrationForms() {
        // Form validation
        setupFormValidation();
        
        // Password strength indicator
        setupPasswordStrength();
        
        // Real-time field validation
        setupRealTimeValidation();
        
        // Form submission handling
        setupFormSubmission();
        
        // File upload handling
        setupFileUpload();
        
        // Character counting
        setupCharacterCounting();
    }

    /**
     * Setup form validation
     */
    function setupFormValidation() {
        $('.sleeve-ke-registration-form form').on('submit', function(e) {
            var $form = $(this);
            var isValid = true;
            var firstErrorField = null;

            // Clear previous errors
            $form.find('.form-group').removeClass('has-error');
            $form.find('.field-error').remove();

            // Validate required fields
            $form.find('[required]').each(function() {
                var $field = $(this);
                var value = $field.val().trim();
                
                if (!value) {
                    showFieldError($field, 'This field is required');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $field;
                }
            });

            // Validate email format
            $form.find('input[type="email"]').each(function() {
                var $field = $(this);
                var email = $field.val().trim();
                
                if (email && !isValidEmail(email)) {
                    showFieldError($field, 'Please enter a valid email address');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $field;
                }
            });

            // Validate password confirmation
            var $password = $form.find('input[name="password"]');
            var $confirmPassword = $form.find('input[name="confirm_password"]');
            
            if ($password.length && $confirmPassword.length) {
                if ($password.val() !== $confirmPassword.val()) {
                    showFieldError($confirmPassword, 'Passwords do not match');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $confirmPassword;
                }
            }

            // Validate password strength
            if ($password.length && $password.val()) {
                var passwordStrength = checkPasswordStrength($password.val());
                if (passwordStrength.score < 3) {
                    showFieldError($password, 'Password must be stronger: ' + passwordStrength.feedback.join(', '));
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $password;
                }
            }

            // Validate phone number
            $form.find('input[name="phone"]').each(function() {
                var $field = $(this);
                var phone = $field.val().trim();
                
                if (phone && !isValidPhone(phone)) {
                    showFieldError($field, 'Please enter a valid phone number');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $field;
                }
            });

            // Validate terms acceptance
            $form.find('input[name="accept_terms"]').each(function() {
                var $field = $(this);
                
                if (!$field.is(':checked')) {
                    showFieldError($field, 'You must accept the terms and conditions');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $field;
                }
            });

            if (!isValid) {
                e.preventDefault();
                if (firstErrorField) {
                    scrollToField(firstErrorField);
                    firstErrorField.focus();
                }
                return false;
            }

            // Show loading state
            showLoadingState($form);
        });
    }

    /**
     * Setup password strength indicator
     */
    function setupPasswordStrength() {
        $('input[name="password"]').each(function() {
            var $field = $(this);
            var $container = $field.closest('.form-group');
            
            // Add strength indicator
            $container.append('<div class="password-strength"><div class="strength-bar"><div class="strength-fill"></div></div><div class="strength-text"></div></div>');
            
            $field.on('input', function() {
                var password = $(this).val();
                var strength = checkPasswordStrength(password);
                updatePasswordStrengthIndicator($container, strength);
            });
        });
    }

    /**
     * Setup real-time field validation
     */
    function setupRealTimeValidation() {
        // Email validation
        $('input[type="email"]').on('blur', function() {
            var $field = $(this);
            var email = $field.val().trim();
            
            clearFieldError($field);
            
            if (email && !isValidEmail(email)) {
                showFieldError($field, 'Invalid email address');
            } else if (email) {
                // Check email availability
                checkEmailAvailability($field, email);
            }
        });

        // Phone validation
        $('input[name="phone"]').on('blur', function() {
            var $field = $(this);
            var phone = $field.val().trim();
            
            clearFieldError($field);
            
            if (phone && !isValidPhone(phone)) {
                showFieldError($field, 'Invalid phone number');
            }
        });

        // Password confirmation
        $('input[name="confirm_password"]').on('input', function() {
            var $field = $(this);
            var $password = $('input[name="password"]');
            
            clearFieldError($field);
            
            if ($field.val() && $password.val() && $field.val() !== $password.val()) {
                showFieldError($field, 'Passwords do not match');
            }
        });
    }

    /**
     * Setup form submission handling
     */
    function setupFormSubmission() {
        $('.sleeve-ke-registration-form form').on('submit', function() {
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            
            // Disable double submission
            $submitBtn.prop('disabled', true);
            
            // Re-enable after 5 seconds (in case of error)
            setTimeout(function() {
                $submitBtn.prop('disabled', false);
                hideLoadingState($form);
            }, 5000);
        });
    }

    /**
     * Setup file upload handling
     */
    function setupFileUpload() {
        $('input[type="file"]').on('change', function() {
            var $field = $(this);
            var file = this.files[0];
            
            clearFieldError($field);
            
            if (file) {
                // Validate file size (max 5MB)
                var maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    showFieldError($field, 'File is too large (max 5MB)');
                    $field.val('');
                    return;
                }
                
                // Validate file type for CV
                if ($field.attr('name') === 'cv_file') {
                    var allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    if (allowedTypes.indexOf(file.type) === -1) {
                        showFieldError($field, 'Unsupported file format (PDF, DOC, DOCX only)');
                        $field.val('');
                        return;
                    }
                }
                
                // Show file name
                var $fileInfo = $field.siblings('.file-info');
                if ($fileInfo.length === 0) {
                    $fileInfo = $('<div class="file-info"></div>');
                    $field.after($fileInfo);
                }
                $fileInfo.html('<i class="dashicons dashicons-paperclip"></i> ' + file.name + ' (' + formatFileSize(file.size) + ')');
            }
        });
    }

    /**
     * Setup character counting
     */
    function setupCharacterCounting() {
        $('textarea[maxlength]').each(function() {
            var $field = $(this);
            var maxLength = parseInt($field.attr('maxlength'));
            var $counter = $('<div class="char-counter"><span class="current">0</span>/' + maxLength + '</div>');
            
            $field.after($counter);
            
            $field.on('input', function() {
                var currentLength = $(this).val().length;
                $counter.find('.current').text(currentLength);
                
                if (currentLength > maxLength * 0.9) {
                    $counter.addClass('warning');
                } else {
                    $counter.removeClass('warning');
                }
            });
        });
    }

    /**
     * Check password strength
     */
    function checkPasswordStrength(password) {
        var score = 0;
        var feedback = [];
        
        // Length check
        if (password.length < 8) {
            feedback.push('At least 8 characters');
        } else {
            score++;
        }
        
        // Lowercase check
        if (!/[a-z]/.test(password)) {
            feedback.push('One lowercase letter');
        } else {
            score++;
        }
        
        // Uppercase check
        if (!/[A-Z]/.test(password)) {
            feedback.push('One uppercase letter');
        } else {
            score++;
        }
        
        // Number check
        if (!/\d/.test(password)) {
            feedback.push('One number');
        } else {
            score++;
        }
        
        // Special character check
        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            feedback.push('One special character');
        } else {
            score++;
        }
        
        return {
            score: score,
            feedback: feedback
        };
    }

    /**
     * Update password strength indicator
     */
    function updatePasswordStrengthIndicator($container, strength) {
        var $indicator = $container.find('.password-strength');
        var $fill = $indicator.find('.strength-fill');
        var $text = $indicator.find('.strength-text');
        
        var percentage = (strength.score / 5) * 100;
        var className = '';
        var text = '';
        
        if (strength.score === 0) {
            className = 'very-weak';
            text = 'Very weak';
        } else if (strength.score <= 2) {
            className = 'weak';
            text = 'Weak';
        } else if (strength.score === 3) {
            className = 'medium';
            text = 'Medium';
        } else if (strength.score === 4) {
            className = 'strong';
            text = 'Strong';
        } else {
            className = 'very-strong';
            text = 'Very strong';
        }
        
        $fill.css('width', percentage + '%').attr('class', 'strength-fill ' + className);
        $text.text(text);
        
        if (strength.feedback.length > 0) {
            $text.append(' - Missing: ' + strength.feedback.join(', '));
        }
    }

    /**
     * Check email availability
     */
    function checkEmailAvailability($field, email) {
        // Add loading indicator
        $field.after('<span class="email-check-loading">Checking...</span>');
        
        $.ajax({
            url: sleeve_ke_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sleeve_ke_check_email',
                email: email,
                nonce: sleeve_ke_ajax.nonce
            },
            success: function(response) {
                $('.email-check-loading').remove();
                
                if (response.success) {
                    if (!response.data.available) {
                        showFieldError($field, 'This email address is already in use');
                    }
                }
            },
            error: function() {
                $('.email-check-loading').remove();
            }
        });
    }

    /**
     * Show field error
     */
    function showFieldError($field, message) {
        var $formGroup = $field.closest('.form-group');
        $formGroup.addClass('has-error');
        
        var $error = $('<div class="field-error">' + message + '</div>');
        $field.after($error);
    }

    /**
     * Clear field error
     */
    function clearFieldError($field) {
        var $formGroup = $field.closest('.form-group');
        $formGroup.removeClass('has-error');
        $field.siblings('.field-error').remove();
    }

    /**
     * Show loading state
     */
    function showLoadingState($form) {
        var $submitBtn = $form.find('button[type="submit"]');
        $submitBtn.addClass('loading').prop('disabled', true);
        $submitBtn.find('.btn-text').hide();
        $submitBtn.find('.btn-loading').show();
    }

    /**
     * Hide loading state
     */
    function hideLoadingState($form) {
        var $submitBtn = $form.find('button[type="submit"]');
        $submitBtn.removeClass('loading').prop('disabled', false);
        $submitBtn.find('.btn-text').show();
        $submitBtn.find('.btn-loading').hide();
    }

    /**
     * Scroll to field
     */
    function scrollToField($field) {
        $('html, body').animate({
            scrollTop: $field.offset().top - 100
        }, 500);
    }

    /**
     * Validate email format
     */
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Validate phone number
     */
    function isValidPhone(phone) {
        // French phone number regex (flexible)
        var phoneRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    /**
     * Format file size
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

})(jQuery);