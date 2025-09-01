<?php
/**
 * Settings View
 * 
 * Page for managing form settings, styles, and popup settings
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$form_settings = get_option('verifyplus_form_settings', array());
$form_styles = get_option('verifyplus_form_styles', array());
$popup_settings = get_option('verifyplus_popup_settings', array());
$purchase_locations = get_option('verifyplus_purchase_locations', array());
?>

<div class="wrap verifyplus-admin-page">
    <!-- Header -->
    <div class="verifyplus-header">
        <h1><?php _e('Form Settings', 'verifyplus'); ?></h1>
        <p><?php _e('Configure form fields, styling, and popup settings', 'verifyplus'); ?></p>
        
        
    </div>

    <!-- Settings Errors -->
    <?php settings_errors('verifyplus'); ?>

    <!-- Navigation Tabs -->
    <nav class="nav-tab-wrapper">
        <a href="#form-settings" class="nav-tab nav-tab-active" data-tab="form-settings">
            <span class="dashicons dashicons-admin-generic"></span>
            <?php _e('Form Settings', 'verifyplus'); ?>
        </a>
        <a href="#form-styles" class="nav-tab" data-tab="form-styles">
            <span class="dashicons dashicons-admin-appearance"></span>
            <?php _e('Form Styles', 'verifyplus'); ?>
        </a>
        <a href="#popup-settings" class="nav-tab" data-tab="popup-settings">
            <span class="dashicons dashicons-admin-comments"></span>
            <?php _e('Popup Settings', 'verifyplus'); ?>
        </a>
    </nav>

    <!-- Form Settings Tab -->
    <div id="form-settings" class="tab-content active">
        <div class="verifyplus-section">
            <h2><?php _e('Form Field Settings', 'verifyplus'); ?></h2>
            
            <form method="post" class="verifyplus-form" data-form-type="form_settings">
                <?php wp_nonce_field('verifyplus_admin_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Form Title', 'verifyplus'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="form_settings[show_form_title]" value="1" 
                                    <?php checked(isset($form_settings['show_form_title']) ? $form_settings['show_form_title'] : true); ?>>
                                <?php _e('Show form title', 'verifyplus'); ?>
                            </label>
                            <br><br>
                            <label for="form_title"><?php _e('Custom Title:', 'verifyplus'); ?></label>
                            <input type="text" id="form_title" name="form_settings[form_title]" 
                                value="<?php echo esc_attr(isset($form_settings['form_title']) ? $form_settings['form_title'] : __('Product Authentication', 'verifyplus')); ?>" 
                                placeholder="<?php _e('Enter custom form title', 'verifyplus'); ?>" 
                                style="width: 300px; margin-top: 5px;">
                            <p class="description"><?php _e('Customize the title displayed at the top of the authentication form.', 'verifyplus'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Auth Code Field', 'verifyplus'); ?></th>
                        <td>
                            <p><strong><?php _e('Auth Code field is always required and cannot be disabled.', 'verifyplus'); ?></strong></p>
                            <br>
                            <label for="auth_code_label"><?php _e('Custom Label:', 'verifyplus'); ?></label>
                            <input type="text" id="auth_code_label" name="form_settings[auth_code_label]" 
                                value="<?php echo esc_attr(isset($form_settings['auth_code_label']) ? $form_settings['auth_code_label'] : __('Authentication Code', 'verifyplus')); ?>" 
                                placeholder="<?php _e('Enter custom label', 'verifyplus'); ?>" 
                                style="width: 300px; margin-top: 5px;">
                            <p class="description"><?php _e('You can only customize the label for the Auth Code field.', 'verifyplus'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Name Field', 'verifyplus'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="form_settings[show_name]" value="1" 
                                    <?php checked(isset($form_settings['show_name']) ? $form_settings['show_name'] : true); ?>>
                                <?php _e('Show Name field', 'verifyplus'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="form_settings[required_name]" value="1" 
                                    <?php checked(isset($form_settings['required_name']) ? $form_settings['required_name'] : false); ?>>
                                <?php _e('Make Name field required', 'verifyplus'); ?>
                            </label>
                            <br><br>
                            <label for="name_label"><?php _e('Custom Label:', 'verifyplus'); ?></label>
                            <input type="text" id="name_label" name="form_settings[name_label]" 
                                value="<?php echo esc_attr(isset($form_settings['name_label']) ? $form_settings['name_label'] : __('Your Name', 'verifyplus')); ?>" 
                                placeholder="<?php _e('Enter custom label', 'verifyplus'); ?>" 
                                style="width: 300px; margin-top: 5px;">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Email Field', 'verifyplus'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="form_settings[show_email]" value="1" 
                                    <?php checked(isset($form_settings['show_email']) ? $form_settings['show_email'] : true); ?>>
                                <?php _e('Show Email field', 'verifyplus'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="form_settings[required_email]" value="1" 
                                    <?php checked(isset($form_settings['required_email']) ? $form_settings['required_email'] : false); ?>>
                                <?php _e('Make Email field required', 'verifyplus'); ?>
                            </label>
                            <br><br>
                            <label for="email_label"><?php _e('Custom Label:', 'verifyplus'); ?></label>
                            <input type="text" id="email_label" name="form_settings[email_label]" 
                                value="<?php echo esc_attr(isset($form_settings['email_label']) ? $form_settings['email_label'] : __('Email Address', 'verifyplus')); ?>" 
                                placeholder="<?php _e('Enter custom label', 'verifyplus'); ?>" 
                                style="width: 300px; margin-top: 5px;">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Phone Field', 'verifyplus'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="form_settings[show_phone]" value="1" 
                                    <?php checked(isset($form_settings['show_phone']) ? $form_settings['show_phone'] : true); ?>>
                                <?php _e('Show Phone field', 'verifyplus'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="form_settings[required_phone]" value="1" 
                                    <?php checked(isset($form_settings['required_phone']) ? $form_settings['required_phone'] : false); ?>>
                                <?php _e('Make Phone field required', 'verifyplus'); ?>
                            </label>
                            <br><br>
                            <label for="phone_label"><?php _e('Custom Label:', 'verifyplus'); ?></label>
                            <input type="text" id="phone_label" name="form_settings[phone_label]" 
                                value="<?php echo esc_attr(isset($form_settings['phone_label']) ? $form_settings['phone_label'] : __('Phone Number', 'verifyplus')); ?>" 
                                placeholder="<?php _e('Enter custom label', 'verifyplus'); ?>" 
                                style="width: 300px; margin-top: 5px;">
                        </td>
                    </tr>
                    

                    
                    <tr>
                        <th scope="row"><?php _e('Purchase Location Field', 'verifyplus'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="form_settings[show_purchase_location]" value="1" 
                                    <?php checked(isset($form_settings['show_purchase_location']) ? $form_settings['show_purchase_location'] : true); ?>>
                                <?php _e('Show Purchase Location field', 'verifyplus'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="form_settings[required_purchase_location]" value="1" 
                                    <?php checked(isset($form_settings['required_purchase_location']) ? $form_settings['required_purchase_location'] : false); ?>>
                                <?php _e('Make Purchase Location field required', 'verifyplus'); ?>
                            </label>
                            <br><br>
                            <label for="purchase_location_label"><?php _e('Custom Label:', 'verifyplus'); ?></label>
                            <input type="text" id="purchase_location_label" name="form_settings[purchase_location_label]" 
                                value="<?php echo esc_attr(isset($form_settings['purchase_location_label']) ? $form_settings['purchase_location_label'] : __('Purchase Location', 'verifyplus')); ?>" 
                                placeholder="<?php _e('Enter custom label', 'verifyplus'); ?>" 
                                style="width: 300px; margin-top: 5px;">
                        </td>
                    </tr>
                </table>
                
                <div class="form-group">
                    <button type="submit" class="verifyplus-button primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Save Form Settings', 'verifyplus'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Purchase Locations -->
        <div class="verifyplus-section">
            <h2><?php _e('Purchase Locations', 'verifyplus'); ?></h2>
            
            <div class="form-group">
                <label for="new_location"><?php _e('Add New Location', 'verifyplus'); ?></label>
                <div class="location-input-group">
                    <input type="text" id="new_location" placeholder="<?php _e('Enter location name', 'verifyplus'); ?>" class="regular-text">
                    <button type="button" class="verifyplus-button secondary" id="add_location">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add', 'verifyplus'); ?>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label><?php _e('Current Locations', 'verifyplus'); ?></label>
                <div id="locations_list">
                    <?php if (!empty($purchase_locations)): ?>
                        <?php foreach ($purchase_locations as $location): ?>
                            <div class="location-item">
                                <span class="location-name"><?php echo esc_html($location); ?></span>
                                <button type="button" class="verifyplus-button danger small delete-location" data-location="<?php echo esc_attr($location); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="description"><?php _e('No locations added yet.', 'verifyplus'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Styles Tab -->
    <div id="form-styles" class="tab-content">
        <div class="verifyplus-section">
            <h2><?php _e('Form Styling', 'verifyplus'); ?></h2>
            
            <form method="post" class="verifyplus-form" data-form-type="form_styles">
                <?php wp_nonce_field('verifyplus_admin_nonce'); ?>
                
                <div class="style-sections">
                    <!-- Container Styles -->
                    <div class="style-section">
                        <h3><?php _e('Container Styles', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="container_background"><?php _e('Background Color', 'verifyplus'); ?></label>
                            <input type="text" id="container_background" name="form_styles[container_background]" 
                                value="<?php echo esc_attr(isset($form_styles['container_background']) ? $form_styles['container_background'] : '#ffffff'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="container_border"><?php _e('Border Color', 'verifyplus'); ?></label>
                            <input type="text" id="container_border" name="form_styles[container_border]" 
                                value="<?php echo esc_attr(isset($form_styles['container_border']) ? $form_styles['container_border'] : '#e1e5e9'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="container_border_radius"><?php _e('Border Radius', 'verifyplus'); ?></label>
                            <input type="text" id="container_border_radius" name="form_styles[container_border_radius]" 
                                value="<?php echo esc_attr(isset($form_styles['container_border_radius']) ? $form_styles['container_border_radius'] : '8px'); ?>" 
                                placeholder="<?php _e('e.g., 8px, 0.5rem, 10%', 'verifyplus'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="container_padding"><?php _e('Padding', 'verifyplus'); ?></label>
                            <input type="text" id="container_padding" name="form_styles[container_padding]" 
                                value="<?php echo esc_attr(isset($form_styles['container_padding']) ? $form_styles['container_padding'] : '30px'); ?>" 
                                placeholder="<?php _e('e.g., 30px, 2rem, 5%', 'verifyplus'); ?>">
                        </div>
                    </div>
                    
                    <!-- Title Styles -->
                    <div class="style-section">
                        <h3><?php _e('Title Styles', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="title_color"><?php _e('Title Color', 'verifyplus'); ?></label>
                            <input type="text" id="title_color" name="form_styles[title_color]" 
                                value="<?php echo esc_attr(isset($form_styles['title_color']) ? $form_styles['title_color'] : '#333333'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="title_font_size"><?php _e('Font Size', 'verifyplus'); ?></label>
                            <input type="text" id="title_font_size" name="form_styles[title_font_size]" 
                                value="<?php echo esc_attr(isset($form_styles['title_font_size']) ? $form_styles['title_font_size'] : '24px'); ?>" 
                                placeholder="<?php _e('e.g., 24px, 1.5rem, 2em', 'verifyplus'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="title_font_weight"><?php _e('Font Weight', 'verifyplus'); ?></label>
                            <select id="title_font_weight" name="form_styles[title_font_weight]">
                                <option value="normal" <?php selected(isset($form_styles['title_font_weight']) ? $form_styles['title_font_weight'] : 'bold', 'normal'); ?>><?php _e('Normal', 'verifyplus'); ?></option>
                                <option value="bold" <?php selected(isset($form_styles['title_font_weight']) ? $form_styles['title_font_weight'] : 'bold', 'bold'); ?>><?php _e('Bold', 'verifyplus'); ?></option>
                                <option value="600" <?php selected(isset($form_styles['title_font_weight']) ? $form_styles['title_font_weight'] : 'bold', '600'); ?>><?php _e('Semi Bold', 'verifyplus'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Label Styles -->
                    <div class="style-section">
                        <h3><?php _e('Label Styles', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="label_color"><?php _e('Label Color', 'verifyplus'); ?></label>
                            <input type="text" id="label_color" name="form_styles[label_color]" 
                                value="<?php echo esc_attr(isset($form_styles['label_color']) ? $form_styles['label_color'] : '#555555'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="label_font_size"><?php _e('Font Size', 'verifyplus'); ?></label>
                            <input type="text" id="label_font_size" name="form_styles[label_font_size]" 
                                value="<?php echo esc_attr(isset($form_styles['label_font_size']) ? $form_styles['label_font_size'] : '14px'); ?>" 
                                placeholder="<?php _e('e.g., 14px, 0.875rem, 1em', 'verifyplus'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="label_font_weight"><?php _e('Font Weight', 'verifyplus'); ?></label>
                            <select id="label_font_weight" name="form_styles[label_font_weight]">
                                <option value="normal" <?php selected(isset($form_styles['label_font_weight']) ? $form_styles['label_font_weight'] : '600', 'normal'); ?>><?php _e('Normal', 'verifyplus'); ?></option>
                                <option value="bold" <?php selected(isset($form_styles['label_font_weight']) ? $form_styles['label_font_weight'] : '600', 'bold'); ?>><?php _e('Bold', 'verifyplus'); ?></option>
                                <option value="600" <?php selected(isset($form_styles['label_font_weight']) ? $form_styles['label_font_weight'] : '600', '600'); ?>><?php _e('Semi Bold', 'verifyplus'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Input Styles -->
                    <div class="style-section">
                        <h3><?php _e('Input Styles', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="input_background"><?php _e('Background Color', 'verifyplus'); ?></label>
                            <input type="text" id="input_background" name="form_styles[input_background]" 
                                value="<?php echo esc_attr(isset($form_styles['input_background']) ? $form_styles['input_background'] : '#ffffff'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="input_border"><?php _e('Border Color', 'verifyplus'); ?></label>
                            <input type="text" id="input_border" name="form_styles[input_border]" 
                                value="<?php echo esc_attr(isset($form_styles['input_border']) ? $form_styles['input_border'] : '#cccccc'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="input_border_radius"><?php _e('Border Radius', 'verifyplus'); ?></label>
                            <input type="text" id="input_border_radius" name="form_styles[input_border_radius]" 
                                value="<?php echo esc_attr(isset($form_styles['input_border_radius']) ? $form_styles['input_border_radius'] : '4px'); ?>" 
                                placeholder="<?php _e('e.g., 4px, 0.25rem, 2px', 'verifyplus'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="input_padding"><?php _e('Padding', 'verifyplus'); ?></label>
                            <input type="text" id="input_padding" name="form_styles[input_padding]" 
                                value="<?php echo esc_attr(isset($form_styles['input_padding']) ? $form_styles['input_padding'] : '10px'); ?>" 
                                placeholder="<?php _e('e.g., 10px, 0.625rem, 8px', 'verifyplus'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="input_font_size"><?php _e('Font Size', 'verifyplus'); ?></label>
                            <input type="text" id="input_font_size" name="form_styles[input_font_size]" 
                                value="<?php echo esc_attr(isset($form_styles['input_font_size']) ? $form_styles['input_font_size'] : '14px'); ?>" 
                                placeholder="<?php _e('e.g., 14px, 0.875rem, 1em', 'verifyplus'); ?>">
                        </div>
                    </div>
                    
                    <!-- Button Styles -->
                    <div class="style-section">
                        <h3><?php _e('Button Styles', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="button_background"><?php _e('Background Color', 'verifyplus'); ?></label>
                            <input type="text" id="button_background" name="form_styles[button_background]" 
                                value="<?php echo esc_attr(isset($form_styles['button_background']) ? $form_styles['button_background'] : '#007cba'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="button_color"><?php _e('Text Color', 'verifyplus'); ?></label>
                            <input type="text" id="button_color" name="form_styles[button_color]" 
                                value="<?php echo esc_attr(isset($form_styles['button_color']) ? $form_styles['button_color'] : '#ffffff'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="button_border_radius"><?php _e('Border Radius', 'verifyplus'); ?></label>
                            <input type="text" id="button_border_radius" name="form_styles[button_border_radius]" 
                                value="<?php echo esc_attr(isset($form_styles['button_border_radius']) ? $form_styles['button_border_radius'] : '4px'); ?>" 
                                placeholder="<?php _e('e.g., 4px, 0.25rem, 2px', 'verifyplus'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="button_padding"><?php _e('Padding', 'verifyplus'); ?></label>
                            <input type="text" id="button_padding" name="form_styles[button_padding]" 
                                value="<?php echo esc_attr(isset($form_styles['button_padding']) ? $form_styles['button_padding'] : '12px'); ?>" 
                                placeholder="<?php _e('e.g., 12px, 0.75rem, 10px', 'verifyplus'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="button_font_size"><?php _e('Font Size', 'verifyplus'); ?></label>
                            <input type="text" id="button_font_size" name="form_styles[button_font_size]" 
                                value="<?php echo esc_attr(isset($form_styles['button_font_size']) ? $form_styles['button_font_size'] : '16px'); ?>" 
                                placeholder="<?php _e('e.g., 16px, 1rem, 1.2em', 'verifyplus'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="button_font_weight"><?php _e('Font Weight', 'verifyplus'); ?></label>
                            <select id="button_font_weight" name="form_styles[button_font_weight]">
                                <option value="normal" <?php selected(isset($form_styles['button_font_weight']) ? $form_styles['button_font_weight'] : '600', 'normal'); ?>><?php _e('Normal', 'verifyplus'); ?></option>
                                <option value="bold" <?php selected(isset($form_styles['button_font_weight']) ? $form_styles['button_font_weight'] : '600', 'bold'); ?>><?php _e('Bold', 'verifyplus'); ?></option>
                                <option value="600" <?php selected(isset($form_styles['button_font_weight']) ? $form_styles['button_font_weight'] : '600', '600'); ?>><?php _e('Semi Bold', 'verifyplus'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="verifyplus-button primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Save Form Styles', 'verifyplus'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Settings Tab -->
    <div id="popup-settings" class="tab-content">
        <div class="verifyplus-section">
            <h2><?php _e('Popup Configuration', 'verifyplus'); ?></h2>
            
            <form method="post" class="verifyplus-form" data-form-type="popup_settings">
                <?php wp_nonce_field('verifyplus_admin_nonce'); ?>
                
                <div class="style-sections">
                    <!-- General Popup Settings -->
                    <div class="style-section">
                        <h3><?php _e('General Settings', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="popup_enable"><?php _e('Enable Popup', 'verifyplus'); ?></label>
                            <input type="checkbox" id="popup_enable" name="popup_settings[popup_enable]" value="1" 
                                <?php checked(isset($popup_settings['popup_enable']) ? $popup_settings['popup_enable'] : true); ?>>
                            <span class="description"><?php _e('Show messages in a popup instead of inline', 'verifyplus'); ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_width"><?php _e('Width', 'verifyplus'); ?></label>
                            <input type="text" id="popup_width" name="popup_settings[popup_width]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_width']) ? $popup_settings['popup_width'] : '500px'); ?>" 
                                placeholder="e.g., 500px, 80%, 90vw">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_height"><?php _e('Height', 'verifyplus'); ?></label>
                            <input type="text" id="popup_height" name="popup_settings[popup_height]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_height']) ? $popup_settings['popup_height'] : '300px'); ?>" 
                                placeholder="e.g., 300px, auto, 60vh">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_auto_close"><?php _e('Auto Close (seconds)', 'verifyplus'); ?></label>
                            <input type="number" id="popup_auto_close" name="popup_settings[popup_auto_close]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_auto_close']) ? intval($popup_settings['popup_auto_close']) : '0'); ?>" 
                                min="0" max="30" step="1">
                            <span class="description"><?php _e('0 = no auto close', 'verifyplus'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Popup Position -->
                    <div class="style-section">
                        <h3><?php _e('Position Settings', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="popup_position"><?php _e('Position', 'verifyplus'); ?></label>
                            <select id="popup_position" name="popup_settings[popup_position]">
                                <option value="center" <?php selected(isset($popup_settings['popup_position']) ? $popup_settings['popup_position'] : 'center', 'center'); ?>><?php _e('Center', 'verifyplus'); ?></option>
                                <option value="top" <?php selected(isset($popup_settings['popup_position']) ? $popup_settings['popup_position'] : 'center', 'top'); ?>><?php _e('Top', 'verifyplus'); ?></option>
                                <option value="bottom" <?php selected(isset($popup_settings['popup_position']) ? $popup_settings['popup_position'] : 'center', 'bottom'); ?>><?php _e('Bottom', 'verifyplus'); ?></option>
                                <option value="top-left" <?php selected(isset($popup_settings['popup_position']) ? $popup_settings['popup_position'] : 'center', 'top-left'); ?>><?php _e('Top Left', 'verifyplus'); ?></option>
                                <option value="top-right" <?php selected(isset($popup_settings['popup_position']) ? $popup_settings['popup_position'] : 'center', 'top-right'); ?>><?php _e('Top Right', 'verifyplus'); ?></option>
                                <option value="bottom-left" <?php selected(isset($popup_settings['popup_position']) ? $popup_settings['popup_position'] : 'center', 'bottom-left'); ?>><?php _e('Bottom Left', 'verifyplus'); ?></option>
                                <option value="bottom-right" <?php selected(isset($popup_settings['popup_position']) ? $popup_settings['popup_position'] : 'center', 'bottom-right'); ?>><?php _e('Bottom Right', 'verifyplus'); ?></option>
                                <option value="custom" <?php selected(isset($popup_settings['popup_position']) ? $popup_settings['popup_position'] : 'center', 'custom'); ?>><?php _e('Custom', 'verifyplus'); ?></option>
                            </select>
                        </div>
                        
                        <div id="custom-position-fields" class="form-group" style="display: none;">
                            <div class="form-group">
                                <label for="popup_position_custom_top"><?php _e('Top Position (px)', 'verifyplus'); ?></label>
                                <input type="number" id="popup_position_custom_top" name="popup_settings[popup_position_custom_top]" 
                                    value="<?php echo esc_attr(isset($popup_settings['popup_position_custom_top']) ? intval($popup_settings['popup_position_custom_top']) : '50'); ?>" 
                                    min="0" max="1000" step="1">
                            </div>
                            
                            <div class="form-group">
                                <label for="popup_position_custom_left"><?php _e('Left Position (px)', 'verifyplus'); ?></label>
                                <input type="number" id="popup_position_custom_left" name="popup_settings[popup_position_custom_left]" 
                                    value="<?php echo esc_attr(isset($popup_settings['popup_position_custom_left']) ? intval($popup_settings['popup_position_custom_left']) : '50'); ?>" 
                                    min="0" max="1000" step="1">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Overlay Settings -->
                    <div class="style-section">
                        <h3><?php _e('Overlay Settings', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="popup_enable_overlay"><?php _e('Enable Overlay', 'verifyplus'); ?></label>
                            <input type="checkbox" id="popup_enable_overlay" name="popup_settings[popup_enable_overlay]" value="1" 
                                <?php checked(isset($popup_settings['popup_enable_overlay']) ? $popup_settings['popup_enable_overlay'] : true); ?>>
                            <span class="description"><?php _e('Show background overlay behind popup', 'verifyplus'); ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_overlay_color"><?php _e('Overlay Color', 'verifyplus'); ?></label>
                            <input type="text" id="popup_overlay_color" name="popup_settings[popup_overlay_color]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_overlay_color']) ? $popup_settings['popup_overlay_color'] : '#000000'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_overlay_opacity"><?php _e('Overlay Opacity', 'verifyplus'); ?></label>
                            <input type="range" id="popup_overlay_opacity" name="popup_settings[popup_overlay_opacity]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_overlay_opacity']) ? $popup_settings['popup_overlay_opacity'] : '0.5'); ?>" 
                                min="0" max="1" step="0.1" oninput="updateOpacityValue(this.value)">
                            <span id="opacity-value"><?php echo esc_html(isset($popup_settings['popup_overlay_opacity']) ? $popup_settings['popup_overlay_opacity'] : '0.5'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Popup Colors -->
                    <div class="style-section">
                        <h3><?php _e('Popup Colors', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="popup_background"><?php _e('Background Color', 'verifyplus'); ?></label>
                            <input type="text" id="popup_background" name="popup_settings[popup_background]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_background']) ? $popup_settings['popup_background'] : '#ffffff'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_border_color"><?php _e('Border Color', 'verifyplus'); ?></label>
                            <input type="text" id="popup_border_color" name="popup_settings[popup_border_color]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_border_color']) ? $popup_settings['popup_border_color'] : '#e1e5e9'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_text_color"><?php _e('Text Color', 'verifyplus'); ?></label>
                            <input type="text" id="popup_text_color" name="popup_settings[popup_text_color]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_text_color']) ? $popup_settings['popup_text_color'] : '#333333'); ?>" 
                                class="color-picker">
                        </div>
                    </div>
                    
                    <!-- Popup Styling -->
                    <div class="style-section">
                        <h3><?php _e('Popup Styling', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="popup_border_radius"><?php _e('Border Radius (px)', 'verifyplus'); ?></label>
                            <input type="number" id="popup_border_radius" name="popup_settings[popup_border_radius]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_border_radius']) ? intval($popup_settings['popup_border_radius']) : '8'); ?>" 
                                min="0" max="20" step="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_border_width"><?php _e('Border Width (px)', 'verifyplus'); ?></label>
                            <input type="number" id="popup_border_width" name="popup_settings[popup_border_width]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_border_width']) ? intval($popup_settings['popup_border_width']) : '1'); ?>" 
                                min="0" max="10" step="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_padding"><?php _e('Padding (px)', 'verifyplus'); ?></label>
                            <input type="number" id="popup_padding" name="popup_settings[popup_padding]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_padding']) ? intval($popup_settings['popup_padding']) : '30'); ?>" 
                                min="10" max="50" step="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_font_size"><?php _e('Font Size (px)', 'verifyplus'); ?></label>
                            <input type="number" id="popup_font_size" name="popup_settings[popup_font_size]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_font_size']) ? intval($popup_settings['popup_font_size']) : '14'); ?>" 
                                min="10" max="24" step="1">
                        </div>
                    </div>
                    
                    <!-- Close Button -->
                    <div class="style-section">
                        <h3><?php _e('Close Button', 'verifyplus'); ?></h3>
                        
                        <div class="form-group">
                            <label for="popup_close_color"><?php _e('Close Button Color', 'verifyplus'); ?></label>
                            <input type="text" id="popup_close_color" name="popup_settings[popup_close_color]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_close_color']) ? $popup_settings['popup_close_color'] : '#999999'); ?>" 
                                class="color-picker">
                        </div>
                        
                        <div class="form-group">
                            <label for="popup_close_size"><?php _e('Close Button Size (px)', 'verifyplus'); ?></label>
                            <input type="number" id="popup_close_size" name="popup_settings[popup_close_size]" 
                                value="<?php echo esc_attr(isset($popup_settings['popup_close_size']) ? intval($popup_settings['popup_close_size']) : '20'); ?>" 
                                min="10" max="40" step="1">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="verifyplus-button primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Save Popup Settings', 'verifyplus'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.style-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin: 20px 0;
}

.style-section {
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    background: #f8f9fa;
}

.style-section h3 {
    margin-top: 0;
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 10px;
}

.location-input-group {
    display: flex;
    gap: 12px;
    align-items: stretch;
}

.location-input-group input[type="text"] {
    flex: 1;
    height: 48px;
    padding: 12px 16px;
    border: 2px solid var(--border-color, #e1e5e9);
    border-radius: var(--border-radius-sm, 8px);
    font-size: 14px;
    font-weight: 500;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
}

.location-input-group input[type="text"]:focus {
    outline: none;
    border-color: var(--primary-color, #667eea);
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
    background: rgba(255, 255, 255, 0.95);
}

.location-input-group .verifyplus-button {
    height: 48px;
    padding: 0 24px;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    border-radius: var(--border-radius-sm, 8px);
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    text-decoration: none;
}

.location-input-group .verifyplus-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.location-input-group .verifyplus-button .dashicons {
    font-size: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.location-input-group .verifyplus-button:hover .dashicons {
    transform: scale(1.1);
}

.location-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: var(--border-radius-sm, 8px);
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.location-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    background: rgba(255, 255, 255, 0.3);
}

.location-name {
    font-weight: 600;
    color: var(--text-primary, #2c3e50);
    font-size: 14px;
}

.location-item .verifyplus-button.small {
    height: 36px;
    padding: 0 16px;
    font-size: 12px;
    min-width: auto;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.nav-tab {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.nav-tab .dashicons {
    font-size: 16px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab functionality
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target content
        $('.tab-content').removeClass('active');
        $('#' + targetTab).addClass('active');
        
        // Update URL hash
        window.location.hash = targetTab;
    });
    
    // Check URL hash on page load
    var hash = window.location.hash.substring(1);
    if (hash) {
        $('.nav-tab[data-tab="' + hash + '"]').click();
    }
    
    // Custom position fields toggle
    $('#popup_position').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom-position-fields').show();
        } else {
            $('#custom-position-fields').hide();
        }
    });
    
    // Trigger on page load
    $('#popup_position').trigger('change');
    
    // Initialize color pickers
    if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.color-picker').wpColorPicker();
    } else {
        console.warn('wpColorPicker not available, using fallback color inputs');
        $('.color-picker').attr('type', 'color');
    }
    
    // Location management
    $('#add_location').on('click', function() {
        var location = $('#new_location').val().trim();
        if (location) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'verifyplus_add_purchase_location',
                    location: location,
                    nonce: '<?php echo wp_create_nonce('verifyplus_add_location_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        }
    });
    
    $('.delete-location').on('click', function() {
        var location = $(this).data('location');
        if (confirm('Are you sure you want to delete this location?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'verifyplus_delete_purchase_location',
                    location: location,
                    nonce: '<?php echo wp_create_nonce('verifyplus_delete_location_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        }
    });
});

function updateOpacityValue(value) {
    document.getElementById('opacity-value').textContent = value;
}
</script>
