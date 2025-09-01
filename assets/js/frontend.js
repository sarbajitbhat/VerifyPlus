/**
 * Code Authentication Frontend JavaScript
 * 
 * Handles frontend form submissions and AJAX requests
 * 
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Frontend functionality
    var VerifyPlusFrontend = {
        
        /**
         * Initialize frontend functionality
         */
        init: function() {
            this.bindEvents();
            this.initFormValidation();
            this.initAccessibility();
            this.initPopup();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Form submission
            $(document).on('submit', '#verifyplus-form', this.handleFormSubmit);
            
            // Real-time validation
            $(document).on('blur', '.form-control', this.handleFieldBlur);
            $(document).on('input', '.form-control', this.handleFieldInput);
            
            // Message close
            $(document).on('click', '.verifyplus-message .close', this.handleMessageClose);
            
            // Keyboard navigation
            $(document).on('keydown', '.form-control', this.handleKeyboardNavigation);
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitButton = $form.find('button[type="submit"]');
            var originalText = $submitButton.text();
            
            // Clear previous validation states
            $form.find('.form-group').removeClass('error success');
            $form.find('.error-message').remove();
            
            // Validate form
            if (!VerifyPlusFrontend.validateForm($form)) {
                return false;
            }
            
            // Show loading state with enhanced styling
            $form.addClass('loading');
            $submitButton.addClass('loading').prop('disabled', true);
            
            // Collect form data
            var formData = new FormData(this);
            formData.append('action', 'verifyplus_authenticate');
            
            // Form data prepared for submission
            
            // Submit via AJAX
            $.ajax({
                url: verifyPlusAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('AJAX success response:', response);
                    VerifyPlusFrontend.handleResponse(response, $form, $submitButton, originalText);
                },
                error: function(xhr, status, error) {
                    console.log('AJAX error details:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusText: xhr.statusText
                    });
                    VerifyPlusFrontend.handleError($form, $submitButton, originalText);
                }
            });
        },

        /**
         * Handle AJAX response
         */
        handleResponse: function(response, $form, $submitButton, originalText) {
            // Reset form state
            $form.removeClass('loading');
            $submitButton.removeClass('loading').text(originalText).prop('disabled', false);
            
            // Apply visual feedback to form fields
            if (response.success) {
                $form.find('.form-group').addClass('success');
            } else {
                $form.find('.form-group').addClass('error');
            }
            
            // Show message
            console.log('Response received:', response);
            console.log('Popup settings:', typeof verifyPlusPopupSettings !== 'undefined' ? verifyPlusPopupSettings : 'undefined');
            
            // Always try to show popup first, fallback to inline message
            if (typeof verifyPlusPopupSettings !== 'undefined' && verifyPlusPopupSettings.enable_popup === false) {
                console.log('Popup disabled, showing inline message...');
                VerifyPlusFrontend.showInlineMessage(response);
            } else {
                console.log('Showing popup...');
                VerifyPlusFrontend.showPopup(response);
            }
            
            // Clear validation states after delay
            setTimeout(function() {
                $form.find('.form-group').removeClass('success error');
            }, 3000);
        },

        /**
         * Handle AJAX error
         */
        handleError: function($form, $submitButton, originalText) {
            // Reset form state
            $form.removeClass('loading');
            $submitButton.removeClass('loading').text(originalText).prop('disabled', false);
            
            // Apply error state
            $form.find('.form-group').addClass('error');
            
            // Show error message
            var errorResponse = {
                success: false,
                message: verifyPlusAjax.strings.error
            };
            
            console.log('Error response:', errorResponse);
            console.log('Popup settings for error:', typeof verifyPlusPopupSettings !== 'undefined' ? verifyPlusPopupSettings : 'undefined');
            
            // Always try to show popup first, fallback to inline message
            if (typeof verifyPlusPopupSettings !== 'undefined' && verifyPlusPopupSettings.enable_popup === false) {
                console.log('Popup disabled, showing error inline message...');
                VerifyPlusFrontend.showInlineMessage(errorResponse);
            } else {
                console.log('Showing error popup...');
                VerifyPlusFrontend.showPopup(errorResponse);
            }
            
            // Clear error state after delay
            setTimeout(function() {
                $form.find('.form-group').removeClass('error');
            }, 3000);
        },

        /**
         * Validate form
         */
        validateForm: function($form) {
            var isValid = true;
            
            // Clear previous validation
            $form.find('.form-group').removeClass('error');
            $form.find('.error-message').remove();
            
            // Validate required fields
            $form.find('input[required], select[required]').each(function() {
                var $field = $(this);
                var $group = $field.closest('.form-group');
                var value = $field.val().trim();
                
                if (!value) {
                    $group.addClass('error');
                    $group.append('<div class="error-message">' + verifyPlusAjax.strings.required + '</div>');
                    isValid = false;
                }
            });
            
            // Validate email format
            $form.find('input[type="email"]').each(function() {
                var $field = $(this);
                var $group = $field.closest('.form-group');
                var value = $field.val().trim();
                
                if (value && !VerifyPlusFrontend.isValidEmail(value)) {
                    $group.addClass('error');
                    $group.append('<div class="error-message">' + verifyPlusAjax.strings.invalidEmail + '</div>');
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
                $group.append('<div class="error-message">' + verifyPlusAjax.strings.required + '</div>');
            } else if ($field.attr('type') === 'email' && $field.val().trim() && !VerifyPlusFrontend.isValidEmail($field.val().trim())) {
                $group.addClass('error');
                $group.append('<div class="error-message">' + verifyPlusAjax.strings.invalidEmail + '</div>');
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
         * Handle message close
         */
        handleMessageClose: function() {
            $(this).closest('.verifyplus-message').fadeOut();
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
         * Initialize form validation
         */
        initFormValidation: function() {
            // Add validation attributes
            $('#verifyplus-form input[type="email"]').attr('pattern', '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$');
            $('#verifyplus-form input[type="tel"]').attr('pattern', '[0-9+\-\s\(\)]+');
        },

        /**
         * Initialize accessibility features
         */
        initAccessibility: function() {
            // Add ARIA labels
            $('#verifyplus-form input, #verifyplus-form select').each(function() {
                var $field = $(this);
                var $label = $field.closest('.form-group').find('label');
                
                if ($label.length) {
                    $field.attr('aria-labelledby', $label.attr('for'));
                }
            });
            
            // Add focus management
            $('#verifyplus-form').on('submit', function() {
                $(this).find('button[type="submit"]').focus();
            });
        },

        /**
         * Initialize popup functionality
         */
        initPopup: function() {
            var self = this;
            
            console.log('Initializing popup functionality...');
            console.log('Popup element exists:', $('#verifyplus-popup').length > 0);
            console.log('Popup settings:', typeof verifyPlusPopupSettings !== 'undefined' ? verifyPlusPopupSettings : 'undefined');

            // Create popup markup if missing
            if (!$('#verifyplus-popup').length) {
                var popupHtml = "\n" +
                    '<div id="verifyplus-popup" class="verifyplus-popup" style="display:none;">' +
                        '<div class="popup-overlay"></div>' +
                        '<div class="popup-content">' +
                            '<button type="button" class="popup-close">&times;</button>' +
                            '<div class="popup-header"><h3 class="popup-title"></h3></div>' +
                            '<div class="popup-body"><div class="popup-message"></div></div>' +
                        '</div>' +
                    '</div>';
                $('body').append(popupHtml);
                console.log('Popup container created dynamically');
            }
            
            // Close popup on overlay click
            $(document).on('click', '.popup-overlay', function() {
                console.log('Overlay clicked, hiding popup...');
                self.hidePopup();
            });
            
            // Close popup on close button click
            $(document).on('click', '.popup-close', function() {
                console.log('Close button clicked, hiding popup...');
                self.hidePopup();
            });
            
            // Close popup on Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#verifyplus-popup').is(':visible')) {
                    console.log('Escape key pressed, hiding popup...');
                    self.hidePopup();
                }
            });
            
            // Prevent popup content clicks from closing popup
            $(document).on('click', '.popup-content', function(e) {
                e.stopPropagation();
            });
        },

        /**
         * Show popup with message
         */
        showPopup: function(response) {
            console.log('showPopup called with response:', response);
            
            var $popup = $('#verifyplus-popup');
            var $content = $popup.find('.popup-content');
            var $overlay = $popup.find('.popup-overlay');
            var $title = $popup.find('.popup-title');
            var $message = $popup.find('.popup-message');
            
            console.log('Popup elements found:', {
                popup: $popup.length,
                content: $content.length,
                overlay: $overlay.length,
                title: $title.length,
                message: $message.length
            });
            
            // Set popup content
            if (response.success) {
                $title.text('Success');
                $message.html(response.message || 'Authentication successful!');
                $content.addClass('success').removeClass('error');
            } else {
                $title.text('Error');
                $message.html(response.message || 'Authentication failed. Please try again.');
                $content.addClass('error').removeClass('success');
            }
            
            // Apply popup settings
            if (typeof verifyPlusPopupSettings !== 'undefined') {
                var settings = verifyPlusPopupSettings;
                
                // Set animation
                if (settings.popup_animation) {
                    $content.attr('data-animation', settings.popup_animation);
                }
                
                // Apply position settings
                this.applyPopupPosition($content, settings);
                
                // Apply custom styles
                var customStyles = '';
                if (settings.popup_width) customStyles += 'width: ' + settings.popup_width + '; ';
                if (settings.popup_height) customStyles += 'height: ' + settings.popup_height + '; ';
                if (settings.popup_bg_color) customStyles += 'background-color: ' + settings.popup_bg_color + '; ';
                if (settings.popup_border_color) customStyles += 'border: 1px solid ' + settings.popup_border_color + '; ';
                if (settings.popup_border_radius) customStyles += 'border-radius: ' + settings.popup_border_radius + '; ';
                if (settings.popup_box_shadow) customStyles += 'box-shadow: ' + settings.popup_box_shadow + '; ';
                
                if (customStyles) {
                    $content.attr('style', customStyles);
                }
                
                // Apply overlay settings
                this.applyOverlaySettings($overlay, settings);
                
                // Apply typography styles
                if (settings.popup_title_color) $title.css('color', settings.popup_title_color);
                if (settings.popup_title_font_size) $title.css('font-size', settings.popup_title_font_size);
                if (settings.popup_content_color) $message.css('color', settings.popup_content_color);
                if (settings.popup_content_font_size) $message.css('font-size', settings.popup_content_font_size);
                if (settings.popup_close_button_color) $popup.find('.popup-close').css('color', settings.popup_close_button_color);
            }
            
            // Show popup
            $popup.fadeIn(300);
            
            // Focus management
            $popup.find('.popup-close').focus();
            
            // Auto close if enabled
            if (typeof verifyPlusPopupSettings !== 'undefined' && verifyPlusPopupSettings.popup_auto_close) {
                var delay = verifyPlusPopupSettings.popup_auto_close_delay || 5;
                setTimeout(function() {
                    VerifyPlusFrontend.hidePopup();
                }, delay * 1000);
            }
        },

        /**
         * Apply popup position settings
         */
        applyPopupPosition: function($content, settings) {
            if (!settings.popup_position) return;
            
            var position = settings.popup_position;
            var positionStyles = '';
            
            switch (position) {
                case 'center':
                    positionStyles = 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
                    break;
                case 'top':
                    positionStyles = 'top: 20px; left: 50%; transform: translateX(-50%);';
                    break;
                case 'bottom':
                    positionStyles = 'bottom: 20px; left: 50%; transform: translateX(-50%);';
                    break;
                case 'left':
                    positionStyles = 'top: 50%; left: 20px; transform: translateY(-50%);';
                    break;
                case 'right':
                    positionStyles = 'top: 50%; right: 20px; transform: translateY(-50%);';
                    break;
                case 'top-left':
                    positionStyles = 'top: 20px; left: 20px;';
                    break;
                case 'top-right':
                    positionStyles = 'top: 20px; right: 20px;';
                    break;
                case 'bottom-left':
                    positionStyles = 'bottom: 20px; left: 20px;';
                    break;
                case 'bottom-right':
                    positionStyles = 'bottom: 20px; right: 20px;';
                    break;
                case 'custom':
                    if (settings.popup_position_custom_top) positionStyles += 'top: ' + settings.popup_position_custom_top + '; ';
                    if (settings.popup_position_custom_left) positionStyles += 'left: ' + settings.popup_position_custom_left + '; ';
                    if (settings.popup_position_custom_transform) positionStyles += 'transform: ' + settings.popup_position_custom_transform + '; ';
                    break;
            }
            
            if (positionStyles) {
                $content.css('position', 'absolute');
                $content.attr('style', $content.attr('style') + '; ' + positionStyles);
            }
        },

        /**
         * Apply overlay settings
         */
        applyOverlaySettings: function($overlay, settings) {
            if (!settings.popup_enable_overlay) {
                $overlay.hide();
                return;
            }
            
            $overlay.show();
            
            var overlayColor = settings.popup_overlay_color || '#000000';
            var overlayOpacity = settings.popup_overlay_opacity || 0.5;
            
            // Convert hex to rgba
            var hex = overlayColor.replace('#', '');
            var r = parseInt(hex.substr(0, 2), 16);
            var g = parseInt(hex.substr(2, 2), 16);
            var b = parseInt(hex.substr(4, 2), 16);
            
            $overlay.css('background-color', 'rgba(' + r + ', ' + g + ', ' + b + ', ' + overlayOpacity + ')');
        },

        /**
         * Hide popup
         */
        hidePopup: function() {
            $('#verifyplus-popup').fadeOut(300);
        },

        /**
         * Show inline message
         */
        showInlineMessage: function(response) {
            var $message = $('#verifyplus-message');
            var $content = $message.find('.message-content');
            
            // Set message content
            $content.html(response.message);
            
            // Set message type
            $message.removeClass('success error').addClass(response.success ? 'success' : 'error');
            
            // Show message
            $message.fadeIn(300);
            
            // Auto hide after 5 seconds
            setTimeout(function() {
                $message.fadeOut(300);
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('VerifyPlus Frontend JS loaded');
        console.log('verifyPlusAjax object:', typeof verifyPlusAjax !== 'undefined' ? verifyPlusAjax : 'undefined');
        console.log('verifyPlusPopupSettings object:', typeof verifyPlusPopupSettings !== 'undefined' ? verifyPlusPopupSettings : 'undefined');
        VerifyPlusFrontend.init();
    });

})(jQuery);
