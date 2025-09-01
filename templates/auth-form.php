<?php
/**
 * Authentication Form Template
 * 
 * Template for the [auth_form] shortcode
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get form settings
$form_settings = get_option('verifyplus_form_settings', array());
$form_styles = get_option('verifyplus_form_styles', array());
$popup_settings = get_option('verifyplus_popup_settings', array());
$purchase_locations = get_option('verifyplus_purchase_locations', array());

// Apply form styles
$container_style = '';
if (!empty($form_styles)) {
    $container_style = sprintf(
        'background-color: %s; border: 1px solid %s; border-radius: %s; padding: %s; box-shadow: %s; width: %s; max-width: %s;',
        $form_styles['container_bg_color'] ?? '#ffffff',
        $form_styles['container_border_color'] ?? '#e1e5e9',
        $form_styles['container_border_radius'] ?? '8px',
        $form_styles['container_padding'] ?? '30px',
        $form_styles['container_box_shadow'] ?? '0 4px 6px rgba(0, 0, 0, 0.1)',
        $form_styles['form_width'] ?? '100%',
        $form_styles['form_max_width'] ?? '600px'
    );
}

$title_style = '';
if (!empty($form_styles)) {
    $title_style = sprintf(
        'color: %s; font-size: %s; font-weight: %s;',
        $form_styles['title_color'] ?? '#333333',
        $form_styles['title_font_size'] ?? '1.8em',
        $form_styles['title_font_weight'] ?? '600'
    );
}

$label_style = '';
if (!empty($form_styles)) {
    $label_style = sprintf(
        'color: %s; font-size: %s; font-weight: %s;',
        $form_styles['label_color'] ?? '#333333',
        $form_styles['label_font_size'] ?? '0.95em',
        $form_styles['label_font_weight'] ?? '500'
    );
}

$input_style = '';
if (!empty($form_styles)) {
    $input_style = sprintf(
        'background-color: %s; border: 1px solid %s; border-radius: %s; padding: %s; font-size: %s;',
        $form_styles['input_bg_color'] ?? '#ffffff',
        $form_styles['input_border_color'] ?? '#e1e5e9',
        $form_styles['input_border_radius'] ?? '6px',
        $form_styles['input_padding'] ?? '12px 15px',
        $form_styles['input_font_size'] ?? '16px'
    );
}

$button_style = '';
if (!empty($form_styles)) {
    $button_style = sprintf(
        'background-color: %s; color: %s; border-radius: %s; padding: %s; font-size: %s; border: none; cursor: pointer;',
        $form_styles['button_bg_color'] ?? '#007cba',
        $form_styles['button_text_color'] ?? '#ffffff',
        $form_styles['button_border_radius'] ?? '6px',
        $form_styles['button_padding'] ?? '12px 24px',
        $form_styles['button_font_size'] ?? '16px'
    );
}
?>

<div class="verifyplus-form-container" style="<?php echo esc_attr($container_style); ?>">
    <?php if (isset($form_settings['show_form_title']) ? $form_settings['show_form_title'] : true): ?>
        <h2 class="verifyplus-title" style="<?php echo esc_attr($title_style); ?>">
            <?php echo esc_html(isset($form_settings['form_title']) ? $form_settings['form_title'] : ($atts['title'] ?? __('Product Authentication', 'verifyplus'))); ?>
        </h2>
    <?php endif; ?>
    
    <form id="verifyplus-form" class="verifyplus-form">
        <?php wp_nonce_field('verifyplus_nonce', 'nonce'); ?>
        
        <!-- Authentication Code Field (Always Required) -->
        <div class="form-group">
            <label for="auth_code" style="<?php echo esc_attr($label_style); ?>">
                <?php echo esc_html(isset($form_settings['auth_code_label']) ? $form_settings['auth_code_label'] : __('Authentication Code', 'verifyplus')); ?> *
            </label>
            <input 
                type="text" 
                id="auth_code" 
                name="auth_code" 
                required 
                style="<?php echo esc_attr($input_style); ?>"
                placeholder="<?php esc_attr_e('Enter your authentication code', 'verifyplus'); ?>"
            >
        </div>
        
        <!-- Name Field -->
        <?php if (!empty($form_settings['show_name'])): ?>
        <div class="form-group">
            <label for="name" style="<?php echo esc_attr($label_style); ?>">
                <?php echo esc_html(isset($form_settings['name_label']) ? $form_settings['name_label'] : __('Your Name', 'verifyplus')); ?>
                <?php if (!empty($form_settings['required_name'])): ?>*<?php endif; ?>
            </label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                <?php echo (!empty($form_settings['required_name'])) ? 'required' : ''; ?>
                style="<?php echo esc_attr($input_style); ?>"
                placeholder="<?php esc_attr_e('Enter your full name', 'verifyplus'); ?>"
            >
        </div>
        <?php endif; ?>
        
        <!-- Email Field -->
        <?php if (!empty($form_settings['show_email'])): ?>
        <div class="form-group">
            <label for="email" style="<?php echo esc_attr($label_style); ?>">
                <?php echo esc_html(isset($form_settings['email_label']) ? $form_settings['email_label'] : __('Email Address', 'verifyplus')); ?>
                <?php if (!empty($form_settings['required_email'])): ?>*<?php endif; ?>
            </label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                <?php echo (!empty($form_settings['required_email'])) ? 'required' : ''; ?>
                style="<?php echo esc_attr($input_style); ?>"
                placeholder="<?php esc_attr_e('Enter your email address', 'verifyplus'); ?>"
            >
        </div>
        <?php endif; ?>
        
        <!-- Phone Field -->
        <?php if (!empty($form_settings['show_phone'])): ?>
        <div class="form-group">
            <label for="phone" style="<?php echo esc_attr($label_style); ?>">
                <?php echo esc_html(isset($form_settings['phone_label']) ? $form_settings['phone_label'] : __('Phone Number', 'verifyplus')); ?>
                <?php if (!empty($form_settings['required_phone'])): ?>*<?php endif; ?>
            </label>
            <input 
                type="tel" 
                id="phone" 
                name="phone" 
                <?php echo (!empty($form_settings['required_phone'])) ? 'required' : ''; ?>
                style="<?php echo esc_attr($input_style); ?>"
                placeholder="<?php esc_attr_e('Enter your phone number', 'verifyplus'); ?>"
            >
        </div>
        <?php endif; ?>
        

        
        <!-- Purchase Location Field -->
        <?php if (!empty($form_settings['show_purchase_location'])): ?>
        <div class="form-group">
            <label for="purchase_location" style="<?php echo esc_attr($label_style); ?>">
                <?php echo esc_html(isset($form_settings['purchase_location_label']) ? $form_settings['purchase_location_label'] : __('Purchase Location', 'verifyplus')); ?>
                <?php if (!empty($form_settings['required_purchase_location'])): ?>*<?php endif; ?>
            </label>
            <select 
                id="purchase_location" 
                name="purchase_location" 
                <?php echo (!empty($form_settings['required_purchase_location'])) ? 'required' : ''; ?>
                style="<?php echo esc_attr($input_style); ?>"
            >
                <option value=""><?php esc_html_e('Select purchase location', 'verifyplus'); ?></option>
                <?php foreach ($purchase_locations as $location): ?>
                <option value="<?php echo esc_attr($location); ?>">
                    <?php echo esc_html($location); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit" class="btn-primary" style="<?php echo esc_attr($button_style); ?>">
                <?php echo esc_html($atts['submit_text']); ?>
            </button>
        </div>
    </form>
</div>

<!-- Loading Indicator -->
<div id="verifyplus-loader" class="verifyplus-loader" style="display: none;">
    <div class="loader-spinner"></div>
    <p><?php _e('Processing...', 'verifyplus'); ?></p>
</div>

<!-- Popup Modal -->
<div id="verifyplus-popup" class="verifyplus-popup" style="display: none;">
    <div class="popup-overlay"></div>
    <div class="popup-content">
        <button type="button" class="popup-close">&times;</button>
        <div class="popup-header">
            <h3 class="popup-title"></h3>
        </div>
        <div class="popup-body">
            <div class="popup-message"></div>
        </div>
    </div>
</div>

<!-- Inline Message Container -->
<div id="verifyplus-message" class="verifyplus-message" style="display: none;">
    <div class="message-content"></div>
    <button type="button" class="message-close">&times;</button>
</div>

<style>
/* Form Styles */
.verifyplus-form-container {
    margin: 20px auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

.verifyplus-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 5px;
}

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: <?php echo esc_attr($form_styles['input_focus_border_color'] ?? '#007cba'); ?>;
    box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
}

.form-group.error input,
.form-group.error select {
    border-color: #dc3232;
    box-shadow: 0 0 0 3px rgba(220, 50, 50, 0.1);
}

.form-group.success input,
.form-group.success select {
    border-color: #46b450;
    box-shadow: 0 0 0 3px rgba(70, 180, 80, 0.1);
}

/* Button Styles */
.btn-primary {
    transition: all 0.3s ease;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary:hover {
    background-color: <?php echo esc_attr($form_styles['button_hover_bg_color'] ?? '#005a87'); ?> !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-primary.loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Loading Styles */
.verifyplus-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    color: white;
}

.loader-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Popup Styles */
.verifyplus-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.popup-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.popup-content {
    position: relative;
    background: white;
    border-radius: 8px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    animation: popupFadeIn 0.3s ease;
}

@keyframes popupFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.popup-close {
    position: absolute;
    top: 15px;
    right: 20px;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
    transition: color 0.3s ease;
}

.popup-close:hover {
    color: #333;
}

.popup-header {
    margin-bottom: 20px;
    text-align: center;
}

.popup-title {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
}

.popup-body {
    text-align: center;
}

.popup-message {
    font-size: 16px;
    line-height: 1.6;
}

/* Inline Message Styles */
.verifyplus-message {
    margin: 20px 0;
    padding: 15px 20px;
    border-radius: 6px;
    position: relative;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.verifyplus-message.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.verifyplus-message.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.message-close {
    position: absolute;
    top: 10px;
    right: 15px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.message-close:hover {
    opacity: 1;
}

/* Responsive Design */
@media (max-width: 768px) {
    .verifyplus-form-container {
        margin: 10px;
        padding: 20px;
    }
    
    .popup-content {
        width: 95%;
        padding: 20px;
    }
    
    .popup-title {
        font-size: 20px;
    }
    
    .popup-message {
        font-size: 14px;
    }
}
</style>
