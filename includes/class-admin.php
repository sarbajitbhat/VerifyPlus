<?php
/**
 * Admin Panel Handler
 * 
 * Handles all admin panel functionality including menu creation, page rendering, and AJAX handlers
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VerifyPlus Admin Class
 * 
 * @since 1.0.0
 */
class VerifyPlus_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Security: Verify we're in admin context
        if (!is_admin()) {
            return;
        }
        
        // Security: Verify user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register AJAX handlers
        add_action('wp_ajax_verifyplus_save_form_settings', array($this, 'ajax_save_form_settings'));
        add_action('wp_ajax_verifyplus_save_form_styles', array($this, 'ajax_save_form_styles'));
        add_action('wp_ajax_verifyplus_save_popup_settings', array($this, 'ajax_save_popup_settings'));
        add_action('wp_ajax_verifyplus_save_messages', array($this, 'ajax_save_messages'));
        add_action('wp_ajax_verifyplus_add_purchase_location', array($this, 'ajax_add_purchase_location'));
        add_action('wp_ajax_verifyplus_delete_purchase_location', array($this, 'ajax_delete_purchase_location'));
        add_action('wp_ajax_verifyplus_export_codes', array($this, 'ajax_export_codes'));
        add_action('wp_ajax_verifyplus_import_codes', array($this, 'ajax_import_codes'));
        add_action('wp_ajax_verifyplus_download_demo_format', array($this, 'ajax_download_demo_format'));
        add_action('wp_ajax_verifyplus_delete_log', array($this, 'ajax_delete_log'));
        add_action('wp_ajax_verifyplus_delete_all_logs', array($this, 'ajax_delete_all_logs'));
        add_action('wp_ajax_verifyplus_delete_all_codes', array($this, 'ajax_delete_all_codes'));
        add_action('wp_ajax_verifyplus_add_test_log', array($this, 'ajax_add_test_log'));
        add_action('wp_ajax_verifyplus_test_database', array($this, 'ajax_test_database'));
        add_action('wp_ajax_verifyplus_test_authentication', array($this, 'ajax_test_authentication'));
        add_action('wp_ajax_verifyplus_delete_code', array($this, 'ajax_delete_code'));
        add_action('wp_ajax_verifyplus_export_logs', array($this, 'ajax_export_logs'));
        add_action('wp_ajax_verifyplus_test_simple', array($this, 'ajax_test_simple'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('VerifyPlus', 'verifyplus'),
            __('VerifyPlus', 'verifyplus'),
            'manage_options',
            'verifyplus',
            array($this, 'render_dashboard_page'),
            'dashicons-lock',
            30
        );
        
        add_submenu_page(
            'verifyplus',
            __('Dashboard', 'verifyplus'),
            __('Dashboard', 'verifyplus'),
            'manage_options',
            'verifyplus',
            array($this, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'verifyplus',
            __('Upload Codes', 'verifyplus'),
            __('Upload Codes', 'verifyplus'),
            'manage_options',
            'verifyplus-upload',
            array($this, 'render_upload_page')
        );
        
        add_submenu_page(
            'verifyplus',
            __('Manage Codes', 'verifyplus'),
            __('Manage Codes', 'verifyplus'),
            'manage_options',
            'verifyplus-codes',
            array($this, 'render_codes_page')
        );
        
        add_submenu_page(
            'verifyplus',
            __('Authentication Logs', 'verifyplus'),
            __('Authentication Logs', 'verifyplus'),
            'manage_options',
            'verifyplus-logs',
            array($this, 'render_logs_page')
        );
        
        add_submenu_page(
            'verifyplus',
            __('Messages', 'verifyplus'),
            __('Messages', 'verifyplus'),
            'manage_options',
            'verifyplus-messages',
            array($this, 'render_messages_page')
        );
        
        add_submenu_page(
            'verifyplus',
            __('Settings', 'verifyplus'),
            __('Settings', 'verifyplus'),
            'manage_options',
            'verifyplus-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        include VERIFYPLUS_PLUGIN_PATH . 'admin/views/dashboard.php';
    }
    
    /**
     * Render upload page
     */
    public function render_upload_page() {
        include VERIFYPLUS_PLUGIN_PATH . 'admin/views/upload.php';
    }
    
    /**
     * Render codes page
     */
    public function render_codes_page() {
        include VERIFYPLUS_PLUGIN_PATH . 'admin/views/codes.php';
    }
    
    /**
     * Render logs page
     */
    public function render_logs_page() {
        include VERIFYPLUS_PLUGIN_PATH . 'admin/views/logs.php';
    }
    
    /**
     * Render messages page
     */
    public function render_messages_page() {
        include VERIFYPLUS_PLUGIN_PATH . 'admin/views/messages.php';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        include VERIFYPLUS_PLUGIN_PATH . 'admin/views/settings.php';
    }
    
    /**
     * AJAX: Authenticate code
     */
    public function ajax_authenticate() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Create auth handler instance and process
        $auth_handler = new VerifyPlus_Handler();
        $result = $auth_handler->authenticate_code($_POST);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX: Save form settings
     */
    public function ajax_save_form_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        // Get form settings from nested array
        $fs = isset($_POST['form_settings']) && is_array($_POST['form_settings']) ? $_POST['form_settings'] : array();
        
        $form_settings = array(
            // Form title settings
            'show_form_title' => !empty($fs['show_form_title']),
            'form_title' => sanitize_text_field($fs['form_title'] ?? __('Product Authentication', 'verifyplus')),
            
            // Auth code is always required and shown
            'show_auth_code' => true,
            'required_auth_code' => true,
            'auth_code_label' => sanitize_text_field($fs['auth_code_label'] ?? __('Authentication Code', 'verifyplus')),
            
            // Other fields
            'show_name' => !empty($fs['show_name']),
            'show_email' => !empty($fs['show_email']),
            'show_phone' => !empty($fs['show_phone']),
            'show_purchase_location' => !empty($fs['show_purchase_location']),
            'required_name' => !empty($fs['required_name']),
            'required_email' => !empty($fs['required_email']),
            'required_phone' => !empty($fs['required_phone']),
            'required_purchase_location' => !empty($fs['required_purchase_location']),
            
            // Custom labels
            'name_label' => sanitize_text_field($fs['name_label'] ?? __('Your Name', 'verifyplus')),
            'email_label' => sanitize_text_field($fs['email_label'] ?? __('Email Address', 'verifyplus')),
            'phone_label' => sanitize_text_field($fs['phone_label'] ?? __('Phone Number', 'verifyplus')),
            'purchase_location_label' => sanitize_text_field($fs['purchase_location_label'] ?? __('Purchase Location', 'verifyplus'))
        );
        
        update_option('verifyplus_form_settings', $form_settings);
        
        wp_send_json_success(__('Form settings saved successfully', 'verifyplus'));
    }
    
    /**
     * AJAX: Save form styles
     */
    public function ajax_save_form_styles() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        // Get form styles from nested array
        $fs = isset($_POST['form_styles']) && is_array($_POST['form_styles']) ? $_POST['form_styles'] : array();
        
        $form_styles = array(
            'container_bg_color' => sanitize_hex_color($fs['container_background'] ?? '#ffffff'),
            'container_border_color' => sanitize_hex_color($fs['container_border'] ?? '#e1e5e9'),
            'container_border_radius' => sanitize_text_field($fs['container_border_radius'] ?? '8px'),
            'container_padding' => sanitize_text_field($fs['container_padding'] ?? '30px'),
            'container_box_shadow' => sanitize_text_field($fs['container_box_shadow'] ?? '0 4px 6px rgba(0, 0, 0, 0.1)'),
            'title_color' => sanitize_hex_color($fs['title_color'] ?? '#333333'),
            'title_font_size' => sanitize_text_field($fs['title_font_size'] ?? '24px'),
            'title_font_weight' => sanitize_text_field($fs['title_font_weight'] ?? 'bold'),
            'label_color' => sanitize_hex_color($fs['label_color'] ?? '#555555'),
            'label_font_size' => sanitize_text_field($fs['label_font_size'] ?? '14px'),
            'label_font_weight' => sanitize_text_field($fs['label_font_weight'] ?? '600'),
            'input_bg_color' => sanitize_hex_color($fs['input_background'] ?? '#ffffff'),
            'input_border_color' => sanitize_hex_color($fs['input_border'] ?? '#e1e5e9'),
            'input_border_radius' => sanitize_text_field($fs['input_border_radius'] ?? '6px'),
            'input_padding' => sanitize_text_field($fs['input_padding'] ?? '12px 15px'),
            'input_font_size' => sanitize_text_field($fs['input_font_size'] ?? '16px'),
            'input_focus_border_color' => sanitize_hex_color($fs['input_focus_border'] ?? '#007cba'),
            'button_bg_color' => sanitize_hex_color($fs['button_background'] ?? '#007cba'),
            'button_text_color' => sanitize_hex_color($fs['button_text_color'] ?? '#ffffff'),
            'button_border_radius' => sanitize_text_field($fs['button_border_radius'] ?? '6px'),
            'button_padding' => sanitize_text_field($fs['button_padding'] ?? '12px 24px'),
            'button_font_size' => sanitize_text_field($fs['button_font_size'] ?? '16px'),
            'button_hover_bg_color' => sanitize_hex_color($fs['button_hover_background'] ?? '#005a87'),
            'form_width' => sanitize_text_field($fs['form_width'] ?? '100%'),
            'form_max_width' => sanitize_text_field($fs['form_max_width'] ?? '600px')
        );
        
        update_option('verifyplus_form_styles', $form_styles);
        
        wp_send_json_success(__('Form styles saved successfully', 'verifyplus'));
    }
    
    /**
     * AJAX: Save popup settings
     */
    public function ajax_save_popup_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        // Accept nested popup_settings[...] from the form and map to stored keys
        $ps = isset($_POST['popup_settings']) && is_array($_POST['popup_settings']) ? $_POST['popup_settings'] : array();
        $auto_close_seconds = isset($ps['popup_auto_close']) ? intval($ps['popup_auto_close']) : 0;
        $popup_settings = array(
            'enable_popup' => !empty($ps['popup_enable']),
            // Accept any CSS unit for width/height
            'popup_width' => isset($ps['popup_width']) ? sanitize_text_field($ps['popup_width']) : '500px',
            'popup_height' => isset($ps['popup_height']) ? sanitize_text_field($ps['popup_height']) : '300px',
            'popup_bg_color' => isset($ps['popup_background']) ? sanitize_hex_color($ps['popup_background']) : '#ffffff',
            'popup_border_color' => isset($ps['popup_border_color']) ? sanitize_hex_color($ps['popup_border_color']) : '#e1e5e9',
            'popup_border_radius' => isset($ps['popup_border_radius']) ? intval($ps['popup_border_radius']) : 8,
            'popup_border_width' => isset($ps['popup_border_width']) ? intval($ps['popup_border_width']) : 1,
            'popup_padding' => isset($ps['popup_padding']) ? intval($ps['popup_padding']) : 30,
            'popup_font_size' => isset($ps['popup_font_size']) ? intval($ps['popup_font_size']) : 14,
            'popup_box_shadow' => '0 4px 20px rgba(0,0,0,0.3)',
            'popup_title_color' => '#333333',
            'popup_title_font_size' => 24,
            'popup_content_color' => isset($ps['popup_text_color']) ? sanitize_hex_color($ps['popup_text_color']) : '#333333',
            'popup_content_font_size' => 16,
            'popup_close_button_color' => isset($ps['popup_close_color']) ? sanitize_hex_color($ps['popup_close_color']) : '#999999',
            'popup_overlay_color' => isset($ps['popup_overlay_color']) ? sanitize_hex_color($ps['popup_overlay_color']) : '#000000',
            'popup_overlay_opacity' => isset($ps['popup_overlay_opacity']) ? floatval($ps['popup_overlay_opacity']) : 0.5,
            'popup_enable_overlay' => !empty($ps['popup_enable_overlay']),
            'popup_position' => isset($ps['popup_position']) ? sanitize_text_field($ps['popup_position']) : 'center',
            'popup_position_custom_top' => isset($ps['popup_position_custom_top']) ? intval($ps['popup_position_custom_top']) : 50,
            'popup_position_custom_left' => isset($ps['popup_position_custom_left']) ? intval($ps['popup_position_custom_left']) : 50,
            'popup_animation' => isset($ps['popup_animation']) ? sanitize_text_field($ps['popup_animation']) : 'fade',
            'popup_auto_close' => $auto_close_seconds > 0,
            'popup_auto_close_delay' => $auto_close_seconds > 0 ? $auto_close_seconds : 5
        );
        
                update_option('verifyplus_popup_settings', $popup_settings);
        
        // Clear cache when settings change
        wp_cache_delete('verifyplus_popup_settings');

        wp_send_json_success(__('Popup settings saved successfully', 'verifyplus'));
    }
    
    /**
     * AJAX: Save messages
     */
    public function ajax_save_messages() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        update_option('verifyplus_success_message', wp_kses_post($_POST['success_message']));
        update_option('verifyplus_error_message', wp_kses_post($_POST['error_message']));
        
        wp_send_json_success(__('Messages saved successfully', 'verifyplus'));
    }
    
    /**
     * AJAX: Add purchase location
     */
    public function ajax_add_purchase_location() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        $location = sanitize_text_field($_POST['location']);
        
        if (empty($location)) {
            wp_send_json_error(__('Location cannot be empty', 'verifyplus'));
        }
        
        $locations = get_option('verifyplus_purchase_locations', array());
        
        if (in_array($location, $locations)) {
            wp_send_json_error(__('Location already exists', 'verifyplus'));
        }
        
        $locations[] = $location;
        update_option('verifyplus_purchase_locations', $locations);
        
        wp_send_json_success(__('Purchase location added successfully', 'verifyplus'));
    }
    
    /**
     * AJAX: Delete purchase location
     */
    public function ajax_delete_purchase_location() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        $location = sanitize_text_field($_POST['location']);
        $locations = get_option('verifyplus_purchase_locations', array());
        
        $key = array_search($location, $locations);
        if ($key !== false) {
            unset($locations[$key]);
            update_option('verifyplus_purchase_locations', array_values($locations));
            wp_send_json_success(__('Purchase location deleted successfully', 'verifyplus'));
        } else {
            wp_send_json_error(__('Location not found', 'verifyplus'));
        }
    }
    
    /**
     * AJAX: Export codes to CSV
     */
    public function ajax_export_codes() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        global $wpdb;
        $table_codes = $wpdb->prefix . 'auth_codes';
        
        // Get codes with actual usage status
        $table_logs = $wpdb->prefix . 'auth_logs';
        $codes = $wpdb->get_results("
            SELECT c.*, 
                   CASE WHEN l.auth_code IS NOT NULL THEN 'used' ELSE 'unused' END as actual_status,
                   l.created_at as used_at
            FROM $table_codes c 
            LEFT JOIN (
                SELECT auth_code, created_at 
                FROM $table_logs 
                WHERE status = 'success' 
                GROUP BY auth_code
            ) l ON c.auth_code = l.auth_code 
            ORDER BY c.created_at DESC
        ", ARRAY_A);
        
        if (empty($codes)) {
            wp_send_json_error(__('No codes found to export', 'verifyplus'));
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="auth_codes_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, array('Code', 'Status', 'Created At', 'Used At'));
        
        // Add data
        foreach ($codes as $code) {
            fputcsv($output, array(
                $code['auth_code'],
                $code['actual_status'],
                $code['created_at'],
                $code['used_at'] ?: ''
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * AJAX: Import codes from CSV
     */
    public function ajax_import_codes() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(__('Please select a valid CSV file', 'verifyplus'));
        }
        
        $file = $_FILES['csv_file'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($file_extension !== 'csv') {
            wp_send_json_error(__('Please upload a CSV file', 'verifyplus'));
        }
        
        global $wpdb;
        $table_codes = $wpdb->prefix . 'auth_codes';
        
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            wp_send_json_error(__('Could not read the uploaded file', 'verifyplus'));
        }
        
        $imported = 0;
        $skipped = 0;
        $row = 0;
        
        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            
            // Skip header row
            if ($row === 1) {
                continue;
            }
            
            if (count($data) < 1) {
                $skipped++;
                continue;
            }
            
            $code = sanitize_text_field(trim($data[0]));
            
            if (empty($code)) {
                $skipped++;
                continue;
            }
            
            // Check if code already exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_codes WHERE auth_code = %s",
                $code
            ));
            
            if ($exists > 0) {
                $skipped++;
                continue;
            }
            
            // Insert code
            $result = $wpdb->insert(
                $table_codes,
                array(
                    'auth_code' => $code,
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s')
            );
            
            if ($result) {
                $imported++;
            } else {
                $skipped++;
            }
        }
        
        fclose($handle);
        
        wp_send_json_success(sprintf(
            __('Import completed. %d codes imported, %d skipped.', 'verifyplus'),
            $imported,
            $skipped
        ));
    }
    
    /**
     * AJAX: Download demo format
     */
    public function ajax_download_demo_format() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="demo_codes_format.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, array('Code'));
        
        // Add sample data
        fputcsv($output, array('DEMO001'));
        fputcsv($output, array('DEMO002'));
        fputcsv($output, array('DEMO003'));
        fputcsv($output, array('TEST001'));
        fputcsv($output, array('TEST002'));
        
        fclose($output);
        exit;
    }
    
    /**
     * AJAX: Delete log
     */
    public function ajax_delete_log() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        global $wpdb;
        $table_logs = $wpdb->prefix . 'auth_logs';
        
        $log_id = intval($_POST['id']);
        
        $result = $wpdb->delete($table_logs, array('id' => $log_id), array('%d'));
        
        if ($result) {
            // Clear related caches
            wp_cache_delete('verifyplus_logs_stats');
            wp_cache_delete('verifyplus_dashboard_stats');
            wp_cache_delete('verifyplus_recent_logs');
            
            wp_send_json_success(__('Log deleted successfully', 'verifyplus'));
        } else {
            wp_send_json_error(__('Failed to delete log', 'verifyplus'));
        }
    }
    
    /**
     * AJAX: Delete all logs
     */
    public function ajax_delete_all_logs() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        global $wpdb;
        $table_logs = $wpdb->prefix . 'auth_logs';
        
        $result = $wpdb->query("DELETE FROM $table_logs");
        
        if ($result !== false) {
            wp_send_json_success(__('All logs deleted successfully', 'verifyplus'));
        } else {
            wp_send_json_error(__('Failed to delete logs', 'verifyplus'));
        }
    }
    
    /**
     * AJAX: Delete all codes
     */
    public function ajax_delete_all_codes() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        global $wpdb;
        $table_codes = $wpdb->prefix . 'auth_codes';
        
        $result = $wpdb->query("DELETE FROM $table_codes");
        
        if ($result !== false) {
            wp_send_json_success(__('All codes deleted successfully', 'verifyplus'));
        } else {
            wp_send_json_error(__('Failed to delete codes', 'verifyplus'));
        }
    }
    
    /**
     * AJAX: Add test log (for debugging)
     */
    public function ajax_add_test_log() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        global $wpdb;
        $table_logs = $wpdb->prefix . 'auth_logs';
        
        $result = $wpdb->insert(
            $table_logs,
            array(
                'auth_code' => 'TEST001',
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'purchase_location' => 'Online Store',
                'status' => 'success',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Browser',
                'created_at' => current_time('mysql')
            )
        );
        
        if ($result) {
            wp_send_json_success(__('Test log added successfully', 'verifyplus'));
        } else {
            wp_send_json_error(__('Failed to add test log', 'verifyplus'));
        }
    }
    
    /**
     * AJAX: Test database (for debugging)
     */
    public function ajax_test_database() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        global $wpdb;
        $table_codes = $wpdb->prefix . 'auth_codes';
        $table_logs = $wpdb->prefix . 'auth_logs';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_codes'");
        
        // Get total codes
        $total_codes = $wpdb->get_var("SELECT COUNT(*) FROM $table_codes");
        
        // Test code search
        $test_code = $_POST['test_code'] ?? 'TEST001';
        $code_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_codes WHERE auth_code = %s",
            $test_code
        ));
        
        // Get all codes with actual status for reference
        $all_codes = $wpdb->get_results("
            SELECT c.id, c.auth_code, 
                   CASE WHEN l.auth_code IS NOT NULL THEN 'used' ELSE 'unused' END as actual_status
            FROM $table_codes c 
            LEFT JOIN (
                SELECT auth_code 
                FROM $table_logs 
                WHERE status = 'success' 
                GROUP BY auth_code
            ) l ON c.auth_code = l.auth_code 
            ORDER BY c.id LIMIT 10
        ");
        
        $result = array(
            'table_exists' => $table_exists ? 'Yes' : 'No',
            'total_codes' => $total_codes,
            'test_code' => $test_code,
            'code_found' => $code_record ? 'Yes' : 'No',
            'code_status' => $code_record ? 'exists' : 'N/A',
            'all_codes' => $all_codes
        );
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Test authentication with a sample code
     */
    public function ajax_test_authentication() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        // Test with a sample code
        $test_data = array(
            'auth_code' => 'TEST001',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'purchase_location' => 'Online Store'
        );
        
        // Create an instance of the auth handler and test
        $auth_handler = new VerifyPlus_Handler();
        $result = $auth_handler->authenticate_code($test_data);
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Delete individual code
     */
    public function ajax_delete_code() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        $code_id = intval($_POST['id']);
        
        global $wpdb;
        $table = $wpdb->prefix . 'auth_codes';
        
        $result = $wpdb->delete($table, array('id' => $code_id), array('%d'));
        
        if ($result) {
            // Clear related caches
            wp_cache_delete('verifyplus_codes_stats');
            wp_cache_delete('verifyplus_dashboard_stats');
            
            wp_send_json_success(__('Code deleted successfully', 'verifyplus'));
        } else {
            wp_send_json_error(__('Failed to delete code', 'verifyplus'));
        }
    }
    

    
    /**
     * AJAX: Export logs to CSV
     */
    public function ajax_export_logs() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'verifyplus'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'verifyplus'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'auth_logs';
        
        // Get all logs
        $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="authentication_logs_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Create CSV content
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, array('ID', 'Auth Code', 'Name', 'Email', 'Phone', 'Purchase Location', 'IP Address', 'Status', 'Created At'));
        
        // Add data
        foreach ($logs as $log) {
            fputcsv($output, array(
                $log->id,
                $log->auth_code,
                $log->name,
                $log->email,
                $log->phone,
                $log->purchase_location,
                $log->ip_address,
                $log->status,
                $log->created_at
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * AJAX: Simple test handler
     */
    public function ajax_test_simple() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        wp_send_json_success('Simple test successful!');
    }
}
