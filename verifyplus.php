<?php
/**
 * Plugin Name: VerifyPlus
 * Plugin URI: https://example.com/verifyplus
 * Plugin File: verifyplus.php
 * Description: A comprehensive WordPress plugin for authenticating codes with advanced admin panel and customizable frontend forms.
 * Version: 1.2.0
 * Author: Sarbajit Bhattacharjee
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: verifyplus
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Security: Disable file access
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    exit;
}

// Security: Add security headers
add_action('init', function() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
});

// Define plugin constants
define('VERIFYPLUS_VERSION', '1.2.0');
define('VERIFYPLUS_PLUGIN_FILE', __FILE__);
define('VERIFYPLUS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('VERIFYPLUS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VERIFYPLUS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main VerifyPlus Plugin Class
 * 
 * @since 1.0.0
 */
class VerifyPlus_Plugin {
    
    /**
     * Plugin instance
     * 
     * @var VerifyPlus_Plugin
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     * 
     * @return VerifyPlus_Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
        
        // Load text domain
        add_action('init', array($this, 'load_textdomain'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        require_once VERIFYPLUS_PLUGIN_PATH . 'includes/class-activator.php';
        VerifyPlus_Activator::activate();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check for plugin updates
        $this->check_for_updates();
        
        // Load required files
        $this->load_files();
        
        // Initialize components
        $this->init_components();
        
        // Register shortcode
        add_shortcode('auth_form', array($this, 'render_auth_form'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Load text domain for internationalization
     */
    public function load_textdomain() {
        load_plugin_textdomain('verifyplus', false, dirname(VERIFYPLUS_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Check for plugin updates
     */
    private function check_for_updates() {
        $current_version = get_option('verifyplus_version', '0.0.0');
        
        if (version_compare($current_version, VERIFYPLUS_VERSION, '<')) {
            $this->update_plugin($current_version);
            update_option('verifyplus_version', VERIFYPLUS_VERSION);
        }
    }
    
    /**
     * Handle plugin updates
     */
    private function update_plugin($old_version) {
        // Add new options for enhanced features
        $form_settings = get_option('verifyplus_form_settings', array());
        if (!isset($form_settings['required_name'])) {
            $form_settings['required_name'] = false;
            $form_settings['required_email'] = false;
            $form_settings['required_phone'] = false;
            $form_settings['required_purchase_location'] = false;
            update_option('verifyplus_form_settings', $form_settings);
        }
        
        // Add form styles if not exists
        if (!get_option('verifyplus_form_styles')) {
            $default_form_styles = array(
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
            add_option('verifyplus_form_styles', $default_form_styles);
        }
        
        // Add popup settings if not exists
        if (!get_option('verifyplus_popup_settings')) {
            $default_popup_settings = array(
                'enable_popup' => true,
                'popup_width' => '500',
                'popup_height' => '300',
                'popup_bg_color' => '#ffffff',
                'popup_border_color' => '#e1e5e9',
                'popup_border_radius' => '8',
                'popup_box_shadow' => '0 4px 20px rgba(0, 0, 0, 0.3)',
                'popup_title_color' => '#333333',
                'popup_title_font_size' => '24',
                'popup_content_color' => '#666666',
                'popup_content_font_size' => '16',
                'popup_close_button_color' => '#999999',
                'popup_overlay_color' => '#000000',
                'popup_overlay_opacity' => '0.5',
                'popup_enable_overlay' => true,
                'popup_position' => 'center',
                'popup_position_custom_top' => '50',
                'popup_position_custom_left' => '50',
                'popup_position_custom_transform' => 'translate(-50%, -50%)',
                'popup_animation' => 'fade',
                'popup_auto_close' => false,
                'popup_auto_close_delay' => 5
            );
            add_option('verifyplus_popup_settings', $default_popup_settings);
        } else {
            // Update existing popup settings to enable popup by default
            $existing_popup_settings = get_option('verifyplus_popup_settings', array());
            if (!isset($existing_popup_settings['enable_popup'])) {
                $existing_popup_settings['enable_popup'] = true;
                update_option('verifyplus_popup_settings', $existing_popup_settings);
            }
        }
    }
    
    /**
     * Load required files
     */
    private function load_files() {
        // Include core classes
        require_once VERIFYPLUS_PLUGIN_PATH . 'includes/class-admin.php';
        require_once VERIFYPLUS_PLUGIN_PATH . 'includes/class-auth.php';
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        // Initialize admin
        if (is_admin()) {
            new VerifyPlus_Admin();
        }
        
        // Initialize authentication handler
        new VerifyPlus_Handler();
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_assets() {
        // Only load assets if shortcode is present on current page
        global $post;
        if (!$post || !has_shortcode($post->post_content, 'auth_form')) {
            return;
        }
        
        wp_enqueue_style(
            'verifyplus-frontend',
            VERIFYPLUS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            VERIFYPLUS_VERSION
        );
        
        wp_enqueue_script(
            'verifyplus-frontend',
            VERIFYPLUS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            VERIFYPLUS_VERSION,
            true
        );
        
        // Create nonce once
        $nonce = wp_create_nonce('verifyplus_nonce');
        
        // Get AJAX URL safely
        $ajax_url = function_exists('admin_url') ? admin_url('admin-ajax.php') : home_url('/wp-admin/admin-ajax.php');
        
        wp_localize_script('verifyplus-frontend', 'verifyPlusAjax', array(
            'ajaxurl' => $ajax_url,
            'nonce' => $nonce,
            'strings' => array(
                'loading' => __('Processing...', 'verifyplus'),
                'error' => __('An error occurred. Please try again.', 'verifyplus')
            )
        ));
        
        // Localize popup settings with caching
        $popup_settings = wp_cache_get('verifyplus_popup_settings');
        if (false === $popup_settings) {
            $popup_settings = get_option('verifyplus_popup_settings', array());
            wp_cache_set('verifyplus_popup_settings', $popup_settings, '', 3600); // Cache for 1 hour
        }
        wp_localize_script('verifyplus-frontend', 'verifyPlusPopupSettings', $popup_settings);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        $plugin_pages = array(
            'toplevel_page_verifyplus',
            'verifyplus_page_verifyplus-upload',
            'verifyplus_page_verifyplus-codes',
            'verifyplus_page_verifyplus-logs',
            'verifyplus_page_verifyplus-messages',
            'verifyplus_page_verifyplus-settings'
        );
        
        if (!in_array($hook, $plugin_pages)) {
            return;
        }
        
        wp_enqueue_style(
            'verifyplus-admin',
            VERIFYPLUS_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            VERIFYPLUS_VERSION
        );
        
        // Add inline styles as fallback
        wp_add_inline_style('verifyplus-admin', '
            .verifyplus-admin-page {
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%) !important;
                min-height: 100vh !important;
                padding: 20px !important;
                margin: 0 !important;
            }
            .verifyplus-header {
                background: rgba(255, 255, 255, 0.25) !important;
                backdrop-filter: blur(20px) !important;
                border: 1px solid rgba(255, 255, 255, 0.18) !important;
                border-radius: 16px !important;
                padding: 40px !important;
                margin-bottom: 30px !important;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
            }
            .verifyplus-header h1 {
                font-size: 2.8em !important;
                font-weight: 800 !important;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                -webkit-background-clip: text !important;
                -webkit-text-fill-color: transparent !important;
                margin: 0 !important;
            }
            .stats-grid {
                display: grid !important;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
                gap: 25px !important;
                margin-bottom: 40px !important;
            }
            .stat-card {
                background: rgba(255, 255, 255, 0.25) !important;
                backdrop-filter: blur(20px) !important;
                border: 1px solid rgba(255, 255, 255, 0.18) !important;
                border-radius: 16px !important;
                padding: 30px !important;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
                text-align: center !important;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            .stat-card:hover {
                transform: translateY(-8px) !important;
                box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15) !important;
            }
            .stat-number {
                font-size: 3em !important;
                font-weight: 800 !important;
                color: #2c3e50 !important;
                margin-bottom: 10px !important;
                display: block !important;
            }
            .stat-label {
                font-size: 14px !important;
                color: #7f8c8d !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                font-weight: 600 !important;
            }
            .verifyplus-content {
                background: rgba(255, 255, 255, 0.25) !important;
                backdrop-filter: blur(20px) !important;
                border: 1px solid rgba(255, 255, 255, 0.18) !important;
                border-radius: 16px !important;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
                padding: 40px !important;
                margin-bottom: 30px !important;
            }
            .verifyplus-button {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 10px !important;
                padding: 14px 28px !important;
                border: none !important;
                border-radius: 8px !important;
                font-size: 14px !important;
                font-weight: 600 !important;
                text-decoration: none !important;
                cursor: pointer !important;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
                min-width: 120px !important;
            }
            .verifyplus-button.primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                color: white !important;
            }
            .verifyplus-button.secondary {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
                color: white !important;
            }
            .verifyplus-button:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
            }
            
            /* Force button colors */
            .verifyplus-button.primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                color: white !important;
            }
            .verifyplus-button.secondary {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
                color: white !important;
            }
            .verifyplus-button.danger {
                background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%) !important;
                color: white !important;
            }
            .verifyplus-button.success {
                background: linear-gradient(135deg, #2ed573 0%, #1e90ff 100%) !important;
                color: white !important;
            }
            .verifyplus-button .dashicons {
                color: white !important;
            }
            
            /* Force checkbox column width */
            .verifyplus-table th:nth-child(1),
            .verifyplus-table td:nth-child(1),
            .verifyplus-codes-table th:nth-child(1),
            .verifyplus-codes-table td:nth-child(1) {
                width: 25px !important;
                min-width: 25px !important;
                max-width: 25px !important;
                padding: 8px 4px !important;
                text-align: center !important;
            }
            
            /* Force white text on violet and red backgrounds */
            .verifyplus-button.primary,
            .verifyplus-button.secondary,
            .verifyplus-button.danger,
            .verifyplus-button.success,
            .verifyplus-button.warning {
                color: white !important;
            }
            
            .verifyplus-button .dashicons {
                color: white !important;
            }
            
            /* Force white text on badges */
            .verifyplus-table .badge,
            .verifyplus-codes-table .badge,
            .verifyplus-table .status-badge,
            .verifyplus-codes-table .status-badge,
            .verifyplus-table .code-badge,
            .verifyplus-codes-table .code-badge {
                color: white !important;
                font-weight: 600 !important;
            }
        ');
        
        wp_enqueue_script(
            'verifyplus-admin',
            VERIFYPLUS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            VERIFYPLUS_VERSION . '.' . time(), // Add cache busting
            true
        );
        
        wp_localize_script('verifyplus-admin', 'verifyPlusAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('verifyplus_admin_nonce'),
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'verifyplus'),
                'confirmDeleteAll' => __('Are you sure you want to delete all items?', 'verifyplus')
            )
        ));
    }
    
    /**
     * Render authentication form shortcode
     */
    public function render_auth_form($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'title' => __('Product Authentication', 'verifyplus'),
            'submit_text' => __('Authenticate', 'verifyplus')
        ), $atts, 'auth_form');
        
        // Get form settings
        $form_settings = get_option('verifyplus_form_settings', array());
        $form_styles = get_option('verifyplus_form_styles', array());
        $popup_settings = get_option('verifyplus_popup_settings', array());
        
        // Start output buffering
        ob_start();
        
        // Include the form template
        include VERIFYPLUS_PLUGIN_PATH . 'templates/auth-form.php';
        
        return ob_get_clean();
    }
}

// Initialize the plugin
VerifyPlus_Plugin::get_instance();
