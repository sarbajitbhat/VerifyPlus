<?php
/**
 * Messages View
 * 
 * Page for customizing success and error messages
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_messages'])) {
    if (wp_verify_nonce($_POST['_wpnonce'], 'save_messages_nonce')) {
        update_option('verifyplus_success_message', wp_kses_post($_POST['success_message']));
        update_option('verifyplus_error_message', wp_kses_post($_POST['error_message']));
        add_settings_error('verifyplus', 'messages_saved', __('Messages saved successfully.', 'verifyplus'), 'success');
    }
}

// Get current messages
$success_message = get_option('verifyplus_success_message', '<h3>Authentication Successful!</h3><p>Your code has been successfully authenticated. Thank you for using our service.</p>');
$error_message = get_option('verifyplus_error_message', '<h3>Authentication Failed</h3><p>The authentication code you entered is invalid or has already been used. Please check your code and try again.</p>');
?>

<div class="wrap verifyplus-admin-page">
    <!-- Header -->
    <div class="verifyplus-header">
        <h1><?php _e('Customize Messages', 'verifyplus'); ?></h1>
        <p><?php _e('Customize the success and error messages shown to users during authentication', 'verifyplus'); ?></p>
    </div>

    <!-- Settings Errors -->
    <?php settings_errors('verifyplus'); ?>

    <!-- Messages Form -->
    <div class="verifyplus-content">
        <div class="verifyplus-section">
            <h2><?php _e('Authentication Messages', 'verifyplus'); ?></h2>
            
            <form method="post" class="verifyplus-form" data-form-type="messages">
                <?php wp_nonce_field('verifyplus_admin_nonce'); ?>
                
                <div class="form-group">
                    <label for="success_message"><?php _e('Success Message', 'verifyplus'); ?></label>
                    <p class="description">
                        <?php _e('This message is displayed when a user successfully authenticates their code. You can use HTML formatting.', 'verifyplus'); ?>
                    </p>
                    <?php
                    wp_editor(
                        $success_message,
                        'success_message',
                        array(
                            'textarea_name' => 'success_message',
                            'textarea_rows' => 8,
                            'media_buttons' => false,
                            'teeny' => true,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,formatselect,|,undo,redo',
                                'toolbar2' => '',
                                'toolbar3' => '',
                                'height' => 200
                            ),
                            'quicktags' => array(
                                'buttons' => 'strong,em,link,ul,ol,li,close'
                            )
                        )
                    );
                    ?>
                </div>
                
                <div class="form-group">
                    <label for="error_message"><?php _e('Error Message', 'verifyplus'); ?></label>
                    <p class="description">
                        <?php _e('This message is displayed when authentication fails (invalid code or already used). You can use HTML formatting.', 'verifyplus'); ?>
                    </p>
                    <?php
                    wp_editor(
                        $error_message,
                        'error_message',
                        array(
                            'textarea_name' => 'error_message',
                            'textarea_rows' => 8,
                            'media_buttons' => false,
                            'teeny' => true,
                            'tinymce' => array(
                                'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,formatselect,|,undo,redo',
                                'toolbar2' => '',
                                'toolbar3' => '',
                                'height' => 200
                            ),
                            'quicktags' => array(
                                'buttons' => 'strong,em,link,ul,ol,li,close'
                            )
                        )
                    );
                    ?>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="save_messages" class="verifyplus-button primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Save Messages', 'verifyplus'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Message Preview -->
        <div class="verifyplus-section">
            <h2><?php _e('Message Preview', 'verifyplus'); ?></h2>
            
            <div class="form-group">
                <label><?php _e('Success Message Preview', 'verifyplus'); ?></label>
                <div class="message-preview success" style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 10px 0;">
                    <?php echo wp_kses_post($success_message); ?>
                </div>
            </div>
            
            <div class="form-group">
                <label><?php _e('Error Message Preview', 'verifyplus'); ?></label>
                <div class="message-preview error" style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 10px 0;">
                    <?php echo wp_kses_post($error_message); ?>
                </div>
            </div>
        </div>

        <!-- Message Variables -->
        <div class="verifyplus-section">
            <h2><?php _e('Available Variables', 'verifyplus'); ?></h2>
            
            <div class="form-group">
                <p><?php _e('You can use the following variables in your messages:', 'verifyplus'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th><code>{code}</code></th>
                        <td><?php _e('The authentication code that was entered', 'verifyplus'); ?></td>
                    </tr>
                    <tr>
                        <th><code>{name}</code></th>
                        <td><?php _e('The name entered by the user', 'verifyplus'); ?></td>
                    </tr>
                    <tr>
                        <th><code>{email}</code></th>
                        <td><?php _e('The email address entered by the user', 'verifyplus'); ?></td>
                    </tr>
                    <tr>
                        <th><code>{phone}</code></th>
                        <td><?php _e('The phone number entered by the user', 'verifyplus'); ?></td>
                    </tr>
                    <tr>
                        <th><code>{purchase_location}</code></th>
                        <td><?php _e('The purchase location selected by the user', 'verifyplus'); ?></td>
                    </tr>
                    <tr>
                        <th><code>{date}</code></th>
                        <td><?php _e('The current date and time', 'verifyplus'); ?></td>
                    </tr>
                </table>
                
                <p class="description">
                    <?php _e('Note: Variables will be automatically replaced with actual values when the message is displayed.', 'verifyplus'); ?>
                </p>
            </div>
        </div>

        <!-- Message Examples -->
        <div class="verifyplus-section">
            <h2><?php _e('Message Examples', 'verifyplus'); ?></h2>
            
            <div class="form-group">
                <h3><?php _e('Success Message Examples', 'verifyplus'); ?></h3>
                
                <div class="example-messages">
                    <div class="example-message">
                        <h4><?php _e('Simple Success', 'verifyplus'); ?></h4>
                        <div class="message-content">
                            <h3>✅ Authentication Successful!</h3>
                            <p>Thank you for verifying your product. Your authentication code <strong>{code}</strong> has been successfully validated.</p>
                        </div>
                    </div>
                    
                    <div class="example-message">
                        <h4><?php _e('Detailed Success', 'verifyplus'); ?></h4>
                        <div class="message-content">
                            <h3>🎉 Welcome, {name}!</h3>
                            <p>Your product authentication was successful. Here are your details:</p>
                            <ul>
                                <li><strong>Code:</strong> {code}</li>
                                <li><strong>Email:</strong> {email}</li>
                                <li><strong>Purchase Location:</strong> {purchase_location}</li>
                                <li><strong>Authenticated:</strong> {date}</li>
                            </ul>
                            <p>Thank you for choosing our products!</p>
                        </div>
                    </div>
                </div>
                
                <h3><?php _e('Error Message Examples', 'verifyplus'); ?></h3>
                
                <div class="example-messages">
                    <div class="example-message">
                        <h4><?php _e('Simple Error', 'verifyplus'); ?></h4>
                        <div class="message-content">
                            <h3>❌ Authentication Failed</h3>
                            <p>The code <strong>{code}</strong> is invalid or has already been used. Please check your code and try again.</p>
                        </div>
                    </div>
                    
                    <div class="example-message">
                        <h4><?php _e('Helpful Error', 'verifyplus'); ?></h4>
                        <div class="message-content">
                            <h3>⚠️ Authentication Failed</h3>
                            <p>We couldn't authenticate the code <strong>{code}</strong>. This could be because:</p>
                            <ul>
                                <li>The code doesn't exist in our system</li>
                                <li>The code has already been used</li>
                                <li>There was a typo in the code</li>
                            </ul>
                            <p>Please double-check your code and try again. If the problem persists, contact our support team.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="verifyplus-section">
            <h2><?php _e('Quick Actions', 'verifyplus'); ?></h2>
            
            <div class="action-buttons">
                <button type="button" class="verifyplus-button secondary" onclick="loadExample('simple_success')">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e('Load Simple Success', 'verifyplus'); ?>
                </button>
                
                <button type="button" class="verifyplus-button secondary" onclick="loadExample('detailed_success')">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e('Load Detailed Success', 'verifyplus'); ?>
                </button>
                
                <button type="button" class="verifyplus-button secondary" onclick="loadExample('simple_error')">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e('Load Simple Error', 'verifyplus'); ?>
                </button>
                
                <button type="button" class="verifyplus-button secondary" onclick="loadExample('helpful_error')">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php _e('Load Helpful Error', 'verifyplus'); ?>
                </button>
                
                <button type="button" class="verifyplus-button secondary" onclick="resetToDefaults()">
                    <span class="dashicons dashicons-rest-api"></span>
                    <?php _e('Reset to Defaults', 'verifyplus'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.example-messages {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.example-message {
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    background: #f8f9fa;
}

.example-message h4 {
    margin-top: 0;
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 10px;
}

.message-content {
    margin-top: 15px;
}

.message-content h3 {
    margin-top: 0;
    color: #333;
}

.message-content ul {
    margin: 15px 0;
    padding-left: 20px;
}

.message-content li {
    margin-bottom: 5px;
}

.message-preview {
    max-width: 600px;
}

.message-preview h3 {
    margin-top: 0;
    color: inherit;
}

.message-preview ul {
    margin: 15px 0;
    padding-left: 20px;
}

.message-preview li {
    margin-bottom: 5px;
}
</style>

<script>
function loadExample(type) {
    var successEditor = tinymce.get('success_message');
    var errorEditor = tinymce.get('error_message');
    
    var examples = {
        simple_success: {
            success: '<h3>✅ Authentication Successful!</h3><p>Thank you for verifying your product. Your authentication code <strong>{code}</strong> has been successfully validated.</p>',
            error: '<h3>❌ Authentication Failed</h3><p>The code <strong>{code}</strong> is invalid or has already been used. Please check your code and try again.</p>'
        },
        detailed_success: {
            success: '<h3>🎉 Welcome, {name}!</h3><p>Your product authentication was successful. Here are your details:</p><ul><li><strong>Code:</strong> {code}</li><li><strong>Email:</strong> {email}</li><li><strong>Purchase Location:</strong> {purchase_location}</li><li><strong>Authenticated:</strong> {date}</li></ul><p>Thank you for choosing our products!</p>',
            error: '<h3>⚠️ Authentication Failed</h3><p>We couldn\'t authenticate the code <strong>{code}</strong>. This could be because:</p><ul><li>The code doesn\'t exist in our system</li><li>The code has already been used</li><li>There was a typo in the code</li></ul><p>Please double-check your code and try again. If the problem persists, contact our support team.</p>'
        },
        simple_error: {
            success: '<h3>✅ Success!</h3><p>Your code has been authenticated successfully.</p>',
            error: '<h3>❌ Error</h3><p>Invalid code. Please try again.</p>'
        },
        helpful_error: {
            success: '<h3>🎉 Authentication Complete!</h3><p>Your product has been successfully verified. Thank you for your purchase!</p>',
            error: '<h3>⚠️ Verification Failed</h3><p>The authentication code you entered is not valid. Please check the code and try again. If you continue to have issues, please contact our customer support.</p>'
        }
    };
    
    if (examples[type]) {
        if (successEditor) {
            successEditor.setContent(examples[type].success);
        } else {
            document.getElementById('success_message').value = examples[type].success;
        }
        
        if (errorEditor) {
            errorEditor.setContent(examples[type].error);
        } else {
            document.getElementById('error_message').value = examples[type].error;
        }
        
        // Show success message
        alert('Example loaded successfully! Click "Save Messages" to apply the changes.');
    }
}

function resetToDefaults() {
    if (confirm('Are you sure you want to reset the messages to their default values? This action cannot be undone.')) {
        var successEditor = tinymce.get('success_message');
        var errorEditor = tinymce.get('error_message');
        
        var defaultSuccess = '<h3>Authentication Successful!</h3><p>Your code has been successfully authenticated. Thank you for using our service.</p>';
        var defaultError = '<h3>Authentication Failed</h3><p>The authentication code you entered is invalid or has already been used. Please check your code and try again.</p>';
        
        if (successEditor) {
            successEditor.setContent(defaultSuccess);
        } else {
            document.getElementById('success_message').value = defaultSuccess;
        }
        
        if (errorEditor) {
            errorEditor.setContent(defaultError);
        } else {
            document.getElementById('error_message').value = defaultError;
        }
        
        alert('Messages reset to defaults! Click "Save Messages" to apply the changes.');
    }
}

// Auto-save functionality
jQuery(document).ready(function($) {
    var autoSaveTimer;
    
    function autoSave() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Show auto-save indicator
            $('.verifyplus-button.primary').text('Auto-saving...').addClass('loading');
            
            // Simulate auto-save (you can implement actual auto-save here)
            setTimeout(function() {
                $('.verifyplus-button.primary').text('Save Messages').removeClass('loading');
            }, 1000);
        }, 2000); // Auto-save after 2 seconds of inactivity
    }
    
    // Monitor changes in TinyMCE editors
    if (typeof tinymce !== 'undefined') {
        tinymce.on('AddEditor', function(e) {
            e.editor.on('change keyup', autoSave);
        });
    }
    
    // Monitor changes in textareas (fallback)
    $('#success_message, #error_message').on('input', autoSave);
});
</script>
