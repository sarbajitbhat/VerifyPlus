/**
 * VerifyPlus Admin JavaScript
 * 
 * Handles admin panel interactions and AJAX requests
 * 
 * @since 1.0.0
 */

// Performance: Removed debug logging for production

(function($) {
    'use strict';

    // Admin functionality
    var VerifyPlusAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.initColorPickers();
            this.initFileUpload();
            this.initFormValidation();
            this.initAccessibility();
            this.showAdminNotices();
            this.initColumnResizing();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Removed test buttons
            
            // Form submissions
            $(document).on('submit', '.verifyplus-form', this.handleFormSubmit);
            
            // Delete actions
            $(document).on('click', '.delete-action', this.handleDeleteAction);
            $(document).on('click', '.delete-all-action', this.handleDeleteAllAction);
            
            // Export actions
            $(document).on('click', '.export-action', this.handleExportAction);
            
            // Import actions
            $(document).on('change', '#csv-file', this.handleFileSelect);
            
            // Purchase location management
            $(document).on('click', '.add-location-btn', this.handleAddLocation);
            $(document).on('click', '.delete-location-btn', this.handleDeleteLocation);
            
            // Removed test actions
            
            // Real-time validation
            $(document).on('blur', '.form-control', this.handleFieldBlur);
            $(document).on('input', '.form-control', this.handleFieldInput);
            
            // Keyboard navigation
            $(document).on('keydown', '.form-control', this.handleKeyboardNavigation);
        },

        /**
         * Initialize tab functionality
         */
        initTabs: function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                var target = $(this).attr('href');
                var tabType = $(this).data('tab');
                
                // Update active tab
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Show target content
                $('.tab-content').removeClass('active');
                $(target).addClass('active');
                
                // Update URL hash
                if (history.pushState) {
                    history.pushState(null, null, target);
                }
            });
            
            // Handle initial tab based on URL hash
            var hash = window.location.hash;
            if (hash && $(hash).length) {
                $('.nav-tab[href="' + hash + '"]').click();
            } else {
                $('.nav-tab:first').click();
            }
        },

        /**
         * Initialize color pickers
         */
        initColorPickers: function() {
            if (typeof $.fn.wpColorPicker !== 'undefined') {
                $('.color-picker').wpColorPicker({
                    change: function(event, ui) {
                        // Update preview
                        var $picker = $(this);
                        var $preview = $picker.closest('.color-picker-group').find('.color-preview');
                        if ($preview.length) {
                            $preview.css('background-color', ui.color.toString());
                        }
                    }
                });
            } else {
                console.warn('wpColorPicker not available, using fallback color inputs');
                // Fallback to regular color inputs
                $('.color-picker').attr('type', 'color');
            }
        },

        /**
         * Initialize file upload functionality
         */
        initFileUpload: function() {
            var $uploadArea = $('.file-upload-area');
            var $fileInput = $('#csv-file');
            
            if ($uploadArea.length && $fileInput.length) {
                // Drag and drop functionality
                $uploadArea.on('dragover', function(e) {
                    e.preventDefault();
                    $(this).addClass('dragover');
                });
                
                $uploadArea.on('dragleave', function(e) {
                    e.preventDefault();
                    $(this).removeClass('dragover');
                });
                
                $uploadArea.on('drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('dragover');
                    
                    var files = e.originalEvent.dataTransfer.files;
                    if (files.length > 0) {
                        $fileInput[0].files = files;
                        VerifyPlusAdmin.handleFileSelect();
                    }
                });
                
                // Click to upload
                $uploadArea.on('click', function() {
                    $fileInput.click();
                });
            }
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            // Add validation attributes
            $('input[type="email"]').attr('pattern', '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$');
            $('input[type="url"]').attr('pattern', 'https?://.+');
            
            // Custom validation for required fields
            $('input[required], select[required], textarea[required]').on('blur', function() {
                var $field = $(this);
                var $group = $field.closest('.form-group');
                
                if (!$field.val().trim()) {
                    $group.addClass('error');
                    if (!$group.find('.error-message').length) {
                        $group.append('<div class="error-message">This field is required.</div>');
                    }
                } else {
                    $group.removeClass('error');
                    $group.find('.error-message').remove();
                }
            });
        },

        /**
         * Initialize accessibility features
         */
        initAccessibility: function() {
            // Add ARIA labels
            $('input, select, textarea').each(function() {
                var $field = $(this);
                var $label = $field.closest('.form-group').find('label');
                
                if ($label.length && !$field.attr('aria-labelledby')) {
                    $field.attr('aria-labelledby', $label.attr('for'));
                }
            });
            
            // Add focus management
            $('.nav-tab').on('click', function() {
                setTimeout(function() {
                    $('.tab-content.active').find('input:first, select:first, textarea:first').focus();
                }, 100);
            });
        },

        /**
         * Show admin notices
         */
        showAdminNotices: function() {
            // Check for success/error messages in URL parameters
            var urlParams = new URLSearchParams(window.location.search);
            var message = urlParams.get('message');
            var type = urlParams.get('type') || 'success';
            
            if (message) {
                this.showNotice(message, type);
                
                // Clean URL
                var newUrl = window.location.pathname + window.location.hash;
                history.replaceState({}, document.title, newUrl);
            }
        },

        /**
         * Initialize column resizing for tables
         */
        initColumnResizing: function() {
            var self = this;
            var isResizing = false;
            var startX = 0;
            var startWidth = 0;
            var $resizingColumn = null;
            var $resizeLine = null;

            // Add resize handles to table headers
            $('.verifyplus-table th:not(:last-child)').each(function() {
                var $th = $(this);
                if (!$th.find('.column-resizer').length) {
                    $th.append('<div class="column-resizer"></div>');
                }
            });

            // Create resize indicator line
            if (!$('.column-resize-line').length) {
                $('body').append('<div class="column-resize-line"></div>');
                $resizeLine = $('.column-resize-line');
            } else {
                $resizeLine = $('.column-resize-line');
            }

            // Mouse down on resizer
            $(document).on('mousedown', '.column-resizer', function(e) {
                e.preventDefault();
                isResizing = true;
                startX = e.pageX;
                $resizingColumn = $(this).parent();
                startWidth = $resizingColumn.outerWidth();
                
                $resizingColumn.addClass('resizing');
                $('body').addClass('col-resize');
                
                // Show resize line
                $resizeLine.css({
                    left: e.pageX + 'px',
                    display: 'block'
                });
            });

            // Mouse move
            $(document).on('mousemove', function(e) {
                if (!isResizing) return;
                
                var diff = e.pageX - startX;
                var newWidth = Math.max(50, startWidth + diff); // Minimum width of 50px
                
                // Update resize line position
                $resizeLine.css('left', e.pageX + 'px');
                
                // Preview the resize
                $resizingColumn.css('width', newWidth + 'px');
            });

            // Mouse up
            $(document).on('mouseup', function(e) {
                if (!isResizing) return;
                
                isResizing = false;
                $('body').removeClass('col-resize');
                $resizeLine.hide();
                
                if ($resizingColumn) {
                    $resizingColumn.removeClass('resizing');
                    
                    // Apply the final width
                    var finalWidth = $resizingColumn.outerWidth();
                    $resizingColumn.css('width', finalWidth + 'px');
                    
                    // Store the width in localStorage for persistence
                    var columnIndex = $resizingColumn.index();
                    var tableClass = $resizingColumn.closest('table').attr('class');
                    var storageKey = 'verifyplus_column_width_' + tableClass + '_' + columnIndex;
                    localStorage.setItem(storageKey, finalWidth);
                    
                    $resizingColumn = null;
                }
            });

            // Restore saved column widths
            $('.verifyplus-table').each(function() {
                var $table = $(this);
                var tableClass = $table.attr('class');
                
                $table.find('th').each(function(index) {
                    var $th = $(this);
                    var storageKey = 'verifyplus_column_width_' + tableClass + '_' + index;
                    var savedWidth = localStorage.getItem(storageKey);
                    
                    if (savedWidth && savedWidth !== 'null') {
                        $th.css('width', savedWidth + 'px');
                    }
                });
            });

            // Double-click to auto-fit column
            $(document).on('dblclick', '.column-resizer', function(e) {
                e.preventDefault();
                var $column = $(this).parent();
                var columnIndex = $column.index();
                var $table = $column.closest('table');
                
                // Find the widest content in this column
                var maxWidth = 0;
                var $cells = $table.find('tr').find('td:nth-child(' + (columnIndex + 1) + '), th:nth-child(' + (columnIndex + 1) + ')');
                
                $cells.each(function() {
                    var $cell = $(this);
                    var tempDiv = $('<div>').html($cell.html()).css({
                        'position': 'absolute',
                        'visibility': 'hidden',
                        'height': 'auto',
                        'width': 'auto',
                        'white-space': 'nowrap',
                        'font-family': $cell.css('font-family'),
                        'font-size': $cell.css('font-size'),
                        'font-weight': $cell.css('font-weight')
                    });
                    
                    $('body').append(tempDiv);
                    var width = tempDiv.outerWidth() + 30; // Add padding
                    maxWidth = Math.max(maxWidth, width);
                    tempDiv.remove();
                });
                
                // Apply the auto-fit width
                var finalWidth = Math.max(80, Math.min(300, maxWidth)); // Between 80px and 300px
                $column.css('width', finalWidth + 'px');
                
                // Save to localStorage
                var tableClass = $table.attr('class');
                var storageKey = 'verifyplus_column_width_' + tableClass + '_' + columnIndex;
                localStorage.setItem(storageKey, finalWidth);
            });

            // Column resizing initialized
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e) {
            console.log('Form submission triggered');
            var $form = $(this);
            var formType = $form.data('form-type');
            console.log('Form type:', formType);
            
            if (!formType) {
                return; // Let normal form submission proceed
            }
            
            e.preventDefault();
            
            // Clear previous validation
            $form.find('.form-group').removeClass('error');
            $form.find('.error-message').remove();
            
            // Validate form
            if (!VerifyPlusAdmin.validateForm($form)) {
                return false;
            }
            
            // Show loading state
            var $submitButton = $form.find('button[type="submit"]');
            var originalText = $submitButton.text();
            $submitButton.addClass('loading').text('Saving...').prop('disabled', true);
            
            // Collect form data
            var formData = new FormData(this);
            // Match PHP handlers: verifyplus_save_form_settings|form_styles|popup_settings
            formData.append('action', 'verifyplus_save_' + formType);
            formData.append('nonce', verifyPlusAdmin.nonce);
            
            // Submit via AJAX
            $.ajax({
                url: verifyPlusAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    VerifyPlusAdmin.handleFormResponse(response, $form, $submitButton, originalText);
                },
                error: function() {
                    VerifyPlusAdmin.handleFormError($form, $submitButton, originalText);
                }
            });
        },

        /**
         * Handle form response
         */
        handleFormResponse: function(response, $form, $submitButton, originalText) {
            // Reset button state
            $submitButton.removeClass('loading').text(originalText).prop('disabled', false);
            
            if (response.success) {
                VerifyPlusAdmin.showNotice(response.data, 'success');
                
                // Refresh page data if needed
                if (response.refresh) {
                    location.reload();
                }
            } else {
                VerifyPlusAdmin.showNotice(response.data, 'error');
            }
        },

        /**
         * Handle form error
         */
        handleFormError: function($form, $submitButton, originalText) {
            // Reset button state
            $submitButton.removeClass('loading').text(originalText).prop('disabled', false);
            
            VerifyPlusAdmin.showNotice('An error occurred. Please try again.', 'error');
        },

        /**
         * Validate form
         */
        validateForm: function($form) {
            var isValid = true;
            
            // Validate required fields
            $form.find('input[required], select[required], textarea[required]').each(function() {
                var $field = $(this);
                var $group = $field.closest('.form-group');
                var value = $field.val().trim();
                
                if (!value) {
                    $group.addClass('error');
                    if (!$group.find('.error-message').length) {
                        $group.append('<div class="error-message">This field is required.</div>');
                    }
                    isValid = false;
                }
            });
            
            // Validate email format
            $form.find('input[type="email"]').each(function() {
                var $field = $(this);
                var $group = $field.closest('.form-group');
                var value = $field.val().trim();
                
                if (value && !VerifyPlusAdmin.isValidEmail(value)) {
                    $group.addClass('error');
                    if (!$group.find('.error-message').length) {
                        $group.append('<div class="error-message">Please enter a valid email address.</div>');
                    }
                    isValid = false;
                }
            });
            
            return isValid;
        },

        /**
         * Validate email format
         */
        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Handle delete action
         */
        handleDeleteAction: function(e) {
            console.log('Delete action clicked');
            e.preventDefault();
            
            if (!confirm(verifyPlusAdmin.strings.confirmDelete)) {
                return false;
            }
            
            var $button = $(this);
            var itemId = $button.data('id');
            var itemType = $button.data('type');
            
            // Show loading state
            $button.addClass('loading').prop('disabled', true);
            
            // Send delete request
            $.ajax({
                url: verifyPlusAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'verifyplus_delete_' + itemType,
                    id: itemId,
                    nonce: verifyPlusAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        VerifyPlusAdmin.showNotice(response.data, 'success');
                        $button.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        VerifyPlusAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    VerifyPlusAdmin.showNotice('An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Handle delete all action
         */
        handleDeleteAllAction: function(e) {
            e.preventDefault();
            
            if (!confirm(verifyPlusAdmin.strings.confirmDeleteAll)) {
                return false;
            }
            
            var $button = $(this);
            var itemType = $button.data('type');
            
            // Show loading state
            $button.addClass('loading').prop('disabled', true);
            
            // Send delete all request
            $.ajax({
                url: verifyPlusAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'verifyplus_delete_all_' + itemType,
                    nonce: verifyPlusAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        VerifyPlusAdmin.showNotice(response.data, 'success');
                        location.reload();
                    } else {
                        VerifyPlusAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    VerifyPlusAdmin.showNotice('An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Handle export action
         */
        handleExportAction: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var exportType = $button.data('type');
            
            // Show loading state
            $button.addClass('loading').prop('disabled', true);
            
            // Create form for download
            var $form = $('<form>', {
                method: 'POST',
                action: verifyPlusAdmin.ajaxurl,
                target: '_blank'
            });
            
            $form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: 'verifyplus_export_' + exportType
            }));
            
            $form.append($('<input>', {
                type: 'hidden',
                name: 'nonce',
                value: verifyPlusAdmin.nonce
            }));
            
            $('body').append($form);
            $form.submit();
            $form.remove();
            
            // Reset button state
            setTimeout(function() {
                $button.removeClass('loading').prop('disabled', false);
            }, 1000);
        },

        /**
         * Handle file selection
         */
        handleFileSelect: function() {
            var $input = $(this);
            var $uploadArea = $('.file-upload-area');
            var file = $input[0].files[0];
            
            if (file) {
                // Update upload area text
                $uploadArea.find('.file-upload-text').text(file.name);
                $uploadArea.find('.file-upload-hint').text('File selected: ' + file.name);
                
                // Validate file type
                if (!file.name.toLowerCase().endsWith('.csv')) {
                    VerifyPlusAdmin.showNotice('Please select a CSV file.', 'error');
                    $input.val('');
                    $uploadArea.find('.file-upload-text').text('Drop CSV file here or click to browse');
                    $uploadArea.find('.file-upload-hint').text('Only CSV files are allowed');
                }
            }
        },

        /**
         * Handle add location
         */
        handleAddLocation: function(e) {
            e.preventDefault();
            
            var $input = $('#new-location');
            var location = $input.val().trim();
            
            if (!location) {
                VerifyPlusAdmin.showNotice('Please enter a location name.', 'error');
                return;
            }
            
            // Send add request
            $.ajax({
                url: verifyPlusAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'verifyplus_add_purchase_location',
                    location: location,
                    nonce: verifyPlusAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        VerifyPlusAdmin.showNotice(response.data, 'success');
                        $input.val('');
                        location.reload();
                    } else {
                        VerifyPlusAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    VerifyPlusAdmin.showNotice('An error occurred. Please try again.', 'error');
                }
            });
        },

        /**
         * Handle delete location
         */
        handleDeleteLocation: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this location?')) {
                return false;
            }
            
            var $button = $(this);
            var location = $button.data('location');
            
            // Send delete request
            $.ajax({
                url: verifyPlusAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'verifyplus_delete_purchase_location',
                    location: location,
                    nonce: verifyPlusAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        VerifyPlusAdmin.showNotice(response.data, 'success');
                        $button.closest('li').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        VerifyPlusAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    VerifyPlusAdmin.showNotice('An error occurred. Please try again.', 'error');
                }
            });
        },

        /**
         * Handle test action
         */
        handleTestAction: function(e) {
            e.preventDefault();
            
            console.log('Test action triggered');
            var $button = $(this);
            var testType = $button.data('type');
            console.log('Test type:', testType);
            
            // Show loading state
            $button.addClass('loading').prop('disabled', true);
            
            // Send test request
            console.log('Sending AJAX request for:', 'verifyplus_test_' + testType);
            console.log('AJAX URL:', verifyPlusAdmin.ajaxurl);
            console.log('Nonce:', verifyPlusAdmin.nonce);
            
            $.ajax({
                url: verifyPlusAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'verifyplus_test_' + testType,
                    nonce: verifyPlusAdmin.nonce
                },
                success: function(response) {
                    console.log('AJAX success response:', response);
                    if (response.success) {
                        VerifyPlusAdmin.showTestResults(response.data, testType);
                    } else {
                        VerifyPlusAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX error:', {xhr: xhr, status: status, error: error});
                    VerifyPlusAdmin.showNotice('An error occurred. Please try again.', 'error');
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        /**
         * Show test results
         */
        showTestResults: function(data, testType) {
            var message = '';
            
            if (testType === 'database') {
                message = 'Database Test Results:\n';
                message += 'Table exists: ' + data.table_exists + '\n';
                message += 'Total codes: ' + data.total_codes + '\n';
                message += 'Test code: ' + data.test_code + '\n';
                message += 'Code found: ' + data.code_found + '\n';
                message += 'Code status: ' + data.code_status;
            } else if (testType === 'authentication') {
                message = 'Authentication Test Results:\n';
                message += 'Success: ' + data.success + '\n';
                message += 'Message: ' + data.message + '\n';
                message += 'Log message: ' + data.log_message;
            }
            
            alert(message);
        },

        /**
         * Handle field blur
         */
        handleFieldBlur: function() {
            var $field = $(this);
            var $group = $field.closest('.form-group');
            
            // Remove previous validation
            $group.removeClass('error success');
            $group.find('.error-message').remove();
            
            // Validate field
            if ($field.is('[required]') && !$field.val().trim()) {
                $group.addClass('error');
                $group.append('<div class="error-message">This field is required.</div>');
            } else if ($field.attr('type') === 'email' && $field.val().trim() && !VerifyPlusAdmin.isValidEmail($field.val().trim())) {
                $group.addClass('error');
                $group.append('<div class="error-message">Please enter a valid email address.</div>');
            } else if ($field.val().trim()) {
                $group.addClass('success');
            }
        },

        /**
         * Handle field input
         */
        handleFieldInput: function() {
            var $field = $(this);
            var $group = $field.closest('.form-group');
            
            // Remove error state on input
            if ($group.hasClass('error')) {
                $group.removeClass('error');
                $group.find('.error-message').remove();
            }
        },

        /**
         * Handle keyboard navigation
         */
        handleKeyboardNavigation: function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                $(this).closest('form').submit();
            }
        },

        /**
         * Show notice
         */
        showNotice: function(message, type) {
            // Remove existing notices
            $('.verifyplus-notice').remove();
            
            // Create notice
            var $notice = $('<div>', {
                class: 'verifyplus-notice ' + type,
                text: message
            });
            
            // Add close button
            var $closeBtn = $('<button>', {
                type: 'button',
                class: 'notice-close',
                html: '&times;',
                click: function() {
                    $notice.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
            
            $notice.append($closeBtn);
            
            // Insert notice
            $('.verifyplus-admin-page').prepend($notice);
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('VerifyPlus Admin JS loaded');
        console.log('verifyPlusAdmin object:', typeof verifyPlusAdmin !== 'undefined' ? verifyPlusAdmin : 'undefined');
        console.log('verifyPlusAdmin.ajaxurl:', typeof verifyPlusAdmin !== 'undefined' ? verifyPlusAdmin.ajaxurl : 'undefined');
        console.log('verifyPlusAdmin.nonce:', typeof verifyPlusAdmin !== 'undefined' ? verifyPlusAdmin.nonce : 'undefined');
        
        // Force initialization
        if (typeof VerifyPlusAdmin !== 'undefined') {
            VerifyPlusAdmin.init();
        } else {
            console.error('VerifyPlusAdmin is not defined!');
        }
        
        // Enhanced Color Picker Functionality
        $('.color-picker').each(function() {
            var $input = $(this);
            var color = $input.val();
            
            // Set CSS custom property for color preview
            $input.css('--current-color', color);
            
            // Update color preview on input change
            $input.on('input change', function() {
                var newColor = $(this).val();
                $(this).css('--current-color', newColor);
            });
        });
        
        // Enhanced Unit Input Validation
        $('input[placeholder*="px"], input[placeholder*="rem"], input[placeholder*="em"]').on('blur', function() {
            var value = $(this).val();
            if (value && !value.match(/^[\d.]+(px|rem|em|%|vh|vw)$/)) {
                // Add px if no unit is specified
                if (value.match(/^[\d.]+$/)) {
                    $(this).val(value + 'px');
                }
            }
        });
        
        // Enhanced Tab Navigation
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            var target = $(this).attr('href');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show target content
            $('.tab-content').removeClass('active');
            $(target).addClass('active');
        });
        
        // Removed duplicate forced handlers to avoid double submissions/notices
    });

})(jQuery);
