<?php
/**
 * Plugin Activation Handler
 * 
 * Handles database table creation and default options setup
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VerifyPlus Activator Class
 * 
 * @since 1.0.0
 */
class VerifyPlus_Activator {
    
    /**
     * Plugin activation hook
     * 
     * @since 1.0.0
     */
    public static function activate() {
        // Create database tables
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     * 
     * @since 1.0.0
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Authentication codes table
        $table_codes = $wpdb->prefix . 'auth_codes';
        $sql_codes = "CREATE TABLE $table_codes (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            auth_code varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY auth_code (auth_code)
        ) $charset_collate;";
        
        // Authentication logs table
        $table_logs = $wpdb->prefix . 'auth_logs';
        $sql_logs = "CREATE TABLE $table_logs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            auth_code varchar(255) NOT NULL,
            name varchar(255) DEFAULT '',
            email varchar(255) DEFAULT '',
            phone varchar(50) DEFAULT '',
            purchase_location varchar(255) DEFAULT '',
            status varchar(20) NOT NULL DEFAULT 'success',
            ip_address varchar(45) DEFAULT '',
            user_agent text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            KEY auth_code (auth_code),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Include WordPress upgrade functions
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create tables
        dbDelta($sql_codes);
        dbDelta($sql_logs);
    }
    
    /**
     * Set default options
     * 
     * @since 1.0.0
     */
    private static function set_default_options() {
        // Set default form settings
        $form_settings = array(
            // Auth code is always required and shown
            'show_auth_code' => true,
            'required_auth_code' => true,
            'auth_code_label' => __('Authentication Code', 'verifyplus'),
            
            // Other fields
            'show_name' => true,
            'show_email' => true,
            'show_phone' => true,
            'show_purchase_location' => true,
            'required_name' => false,
            'required_email' => false,
            'required_phone' => false,
            'required_purchase_location' => false,
            
            // Custom labels
            'name_label' => __('Your Name', 'verifyplus'),
            'email_label' => __('Email Address', 'verifyplus'),
            'phone_label' => __('Phone Number', 'verifyplus'),
            'purchase_location_label' => __('Purchase Location', 'verifyplus')
        );
        
        if (!get_option('verifyplus_form_settings')) {
            add_option('verifyplus_form_settings', $form_settings);
        }
        
        // Set default form styles
        $form_styles = array(
            'container_bg_color' => '#ffffff',
            'container_border_color' => '#e1e5e9',
            'container_border_radius' => '8px',
            'container_padding' => '30px',
            'container_box_shadow' => '0 4px 6px rgba(0, 0, 0, 0.1)',
            'title_color' => '#333333',
            'title_font_size' => '1.8em',
            'title_font_weight' => '600',
            'label_color' => '#333333',
            'label_font_size' => '0.95em',
            'label_font_weight' => '500',
            'input_bg_color' => '#ffffff',
            'input_border_color' => '#e1e5e9',
            'input_border_radius' => '6px',
            'input_padding' => '12px 15px',
            'input_font_size' => '16px',
            'input_focus_border_color' => '#007cba',
            'button_bg_color' => '#007cba',
            'button_text_color' => '#ffffff',
            'button_border_radius' => '6px',
            'button_padding' => '12px 24px',
            'button_font_size' => '16px',
            'button_hover_bg_color' => '#005a87',
            'form_width' => '100%',
            'form_max_width' => '600px'
        );
        
        if (!get_option('verifyplus_form_styles')) {
            add_option('verifyplus_form_styles', $form_styles);
        }
        
        // Set default popup settings
        $popup_settings = array(
            'enable_popup' => false,
            'popup_width' => '400px',
            'popup_height' => 'auto',
            'popup_bg_color' => '#ffffff',
            'popup_border_color' => '#cccccc',
            'popup_border_radius' => '8px',
            'popup_box_shadow' => '0 4px 20px rgba(0, 0, 0, 0.3)',
            'popup_title_color' => '#333333',
            'popup_title_font_size' => '18px',
            'popup_content_color' => '#666666',
            'popup_content_font_size' => '14px',
            'popup_close_button_color' => '#999999',
            'popup_overlay_color' => '#000000',
            'popup_overlay_opacity' => '0.5',
            'popup_enable_overlay' => true,
            'popup_position' => 'center',
            'popup_position_custom_top' => '50%',
            'popup_position_custom_left' => '50%',
            'popup_position_custom_transform' => 'translate(-50%, -50%)',
            'popup_animation' => 'fade',
            'popup_auto_close' => false,
            'popup_auto_close_delay' => 5
        );
        
        if (!get_option('verifyplus_popup_settings')) {
            add_option('verifyplus_popup_settings', $popup_settings);
        }
        
        // Default purchase locations
        $purchase_locations = array(
            'Online Store',
            'Physical Store',
            'Partner Retailer',
            'Direct Purchase'
        );
        
        if (!get_option('verifyplus_purchase_locations')) {
            add_option('verifyplus_purchase_locations', $purchase_locations);
        }
        
        // Default messages
        if (!get_option('verifyplus_success_message')) {
            add_option('verifyplus_success_message', '<h3>Authentication Successful!</h3><p>Your code has been successfully authenticated. Thank you for using our service.</p>');
        }
        
        if (!get_option('verifyplus_error_message')) {
            add_option('verifyplus_error_message', '<h3>Authentication Failed</h3><p>The authentication code you entered is invalid or has already been used. Please check your code and try again.</p>');
        }
        
        // Plugin settings
        $plugin_settings = array(
            'enable_logging' => true,
            'log_ip_address' => true,
            'log_user_agent' => true,
            'max_attempts_per_hour' => 10,
            'enable_rate_limiting' => true
        );
        
        if (!get_option('verifyplus_settings')) {
            add_option('verifyplus_settings', $plugin_settings);
        }
    }
}
