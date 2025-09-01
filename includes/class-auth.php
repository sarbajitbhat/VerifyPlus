<?php
/**
 * Authentication Handler
 * 
 * Handles authentication form submissions and code validation
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VerifyPlus Handler Class
 * 
 * @since 1.0.0
 */
class VerifyPlus_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Handle form submissions
        add_action('init', array($this, 'process_form_submission'));
        
        // Register AJAX handlers
        add_action('wp_ajax_verifyplus_authenticate', array($this, 'ajax_authenticate'));
        add_action('wp_ajax_nopriv_verifyplus_authenticate', array($this, 'ajax_authenticate'));
    }
    
    /**
     * Process form submission (non-AJAX)
     */
    public function process_form_submission() {
        // Check if this is a POST request and has the required data
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verifyplus_nonce'])) {
            if (wp_verify_nonce($_POST['verifyplus_nonce'], 'verifyplus_nonce')) {
                $this->authenticate_code($_POST);
            }
        }
    }
    
    /**
     * AJAX authentication handler
     */
    public function ajax_authenticate() {
        // Debug: Log the received data (commented out to prevent potential issues)
        // error_log("VerifyPlus Debug - AJAX request received");
        // error_log("VerifyPlus Debug - POST data: " . print_r($_POST, true));
        // error_log("VerifyPlus Debug - Nonce field value: " . (isset($_POST['nonce']) ? $_POST['nonce'] : 'NOT SET'));
        // error_log("VerifyPlus Debug - Action field value: " . (isset($_POST['action']) ? $_POST['action'] : 'NOT SET'));
        
        // Check if nonce exists
        if (!isset($_POST['nonce'])) {
            // error_log("VerifyPlus Debug - Nonce field missing from POST data");
            wp_send_json_error(__('Security check failed - nonce missing', 'verifyplus'));
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'verifyplus_nonce')) {
            // error_log("VerifyPlus Debug - Nonce verification failed");
            // error_log("VerifyPlus Debug - Nonce value: " . $_POST['nonce']);
            // error_log("VerifyPlus Debug - Expected action: verifyplus_nonce");
            wp_send_json_error(__('Security check failed - invalid nonce', 'verifyplus'));
        }
        
        $result = $this->authenticate_code($_POST);
        
        // Debug: Log the result (commented out to prevent potential issues)
        // error_log("VerifyPlus Debug - Authentication result: " . print_r($result, true));
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Authenticate code
     *
     * @param array $data Form data
     * @return array Result array
     */
    public function authenticate_code($data) {
        global $wpdb;
        
        $table_codes = $wpdb->prefix . 'auth_codes';
        $table_logs = $wpdb->prefix . 'auth_logs';
        
        // Debug: Log the incoming data (commented out to prevent potential issues)
        // error_log("VerifyPlus Debug - Incoming data: " . print_r($data, true));
        
        // Sanitize input data
        $auth_code = sanitize_text_field($data['auth_code']);
        $name = isset($data['name']) ? sanitize_text_field($data['name']) : '';
        $email = isset($data['email']) ? sanitize_email($data['email']) : '';
        $phone = isset($data['phone']) ? sanitize_text_field($data['phone']) : '';
        $purchase_location = isset($data['purchase_location']) ? sanitize_text_field($data['purchase_location']) : '';
        
        // Validate required field
        if (empty($auth_code)) {
            return $this->handle_failed_attempt('Empty auth code');
        }
        
        // Debug: Check if there are any codes in the database (commented out to prevent potential issues)
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_codes'");
        // error_log("VerifyPlus Debug - Codes table exists: " . ($table_exists ? 'Yes' : 'No'));
        
        if ($table_exists) {
            $total_codes = $wpdb->get_var("SELECT COUNT(*) FROM $table_codes");
            // error_log("VerifyPlus Debug - Total codes in database: $total_codes");
            
            // Show table structure for debugging (commented out to prevent potential issues)
            // $table_structure = $wpdb->get_results("DESCRIBE $table_codes");
            // error_log("VerifyPlus Debug - Table structure:");
            // foreach ($table_structure as $column) {
            //     error_log("VerifyPlus Debug - Column: {$column->Field}, Type: {$column->Type}, Null: {$column->Null}, Key: {$column->Key}, Default: {$column->Default}");
            // }
            
            // Show some sample codes for debugging (commented out to prevent potential issues)
            // $sample_codes = $wpdb->get_results("SELECT auth_code FROM $table_codes LIMIT 5");
            // foreach ($sample_codes as $sample) {
            //     error_log("VerifyPlus Debug - Sample code: '{$sample->auth_code}'");
            // }
            
            // If no codes exist, create some test codes
            if ($total_codes == 0) {
                // error_log("VerifyPlus Debug - No codes found, creating test codes");
                $this->create_test_codes();
            }
        }
        
        // Check if code exists (case-insensitive and trim whitespace)
        $auth_code_clean = trim($auth_code);
        
        // Try exact match first
        $code_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_codes WHERE auth_code = %s",
            $auth_code_clean
        ));
        
        // If not found, try case-insensitive search
        if (!$code_record) {
            $code_record = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_codes WHERE LOWER(auth_code) = LOWER(%s)",
                $auth_code_clean
            ));
        }
        
        // Debug: Log the search attempt (commented out to prevent potential issues)
        // error_log("VerifyPlus Debug - Searching for code: '$auth_code' (cleaned: '$auth_code_clean')");
        // error_log("VerifyPlus Debug - Found record: " . ($code_record ? 'Yes' : 'No'));
        if ($code_record) {
            // error_log("VerifyPlus Debug - Code ID: " . $code_record->id);
        }
        
        // Check if code exists
        if (!$code_record) {
            // error_log("VerifyPlus Debug - Code not found in database");
            return $this->handle_failed_attempt('Code not found');
        }
        
        // Check if code is already used by looking in logs table
        $log_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_logs WHERE auth_code = %s AND status = 'success'",
            $auth_code_clean
        ));
        
        // error_log("VerifyPlus Debug - Log check result: $log_exists successful authentications found");
        
        if ($log_exists > 0) {
            // error_log("VerifyPlus Debug - Code already used, found in logs");
            return $this->handle_failed_attempt('Code already used');
        }
        
        // error_log("VerifyPlus Debug - Code is valid and unused, proceeding with authentication");
        
        // Code is available and unused - proceed with authentication
        // Log successful authentication
        $log_result = $this->log_attempt($auth_code, $name, $email, $phone, $purchase_location, 'success', 'Authentication successful');
        
        // error_log("VerifyPlus Debug - Final result: " . print_r($log_result, true));
        
        return $log_result;
    }
    
    /**
     * Handle failed authentication attempt (without logging)
     *
     * @param string $message Failure message
     * @return array Result array
     */
    private function handle_failed_attempt($message) {
        $message_content = get_option('verifyplus_error_message', __('Authentication failed. Please check your code.', 'verifyplus'));
        
        $result = array(
            'success' => false,
            'message' => $message_content,
            'log_message' => $message
        );
        
        // error_log("VerifyPlus Debug - Failed attempt result: " . print_r($result, true));
        
        return $result;
    }
    
    /**
     * Log authentication attempt
     *
     * @param string $auth_code Authentication code
     * @param string $name User name
     * @param string $email User email
     * @param string $phone User phone
     * @param string $purchase_location Purchase location
     * @param string $status Success or failed
     * @param string $message Log message
     * @return array Result array
     */
    private function log_attempt($auth_code, $name, $email, $phone, $purchase_location, $status, $message) {
        global $wpdb;
        
        $table_logs = $wpdb->prefix . 'auth_logs';
        
        // Get user IP and user agent
        $ip_address = $this->get_client_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        // Insert log entry
        $log_data = array(
            'auth_code' => $auth_code,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'purchase_location' => $purchase_location,
            'status' => $status,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => current_time('mysql')
        );
        
        $wpdb->insert($table_logs, $log_data);
        
        // Get appropriate message
        if ($status === 'success') {
            $message_content = get_option('verifyplus_success_message', __('Authentication successful!', 'verifyplus'));
        } else {
            $message_content = get_option('verifyplus_error_message', __('Authentication failed. Please check your code.', 'verifyplus'));
        }
        
        return array(
            'success' => ($status === 'success'),
            'message' => $message_content,
            'log_message' => $message
        );
    }
    
    /**
     * Get client IP address
     *
     * @return string IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Create test codes for debugging
     */
    private function create_test_codes() {
        global $wpdb;
        
        $table_codes = $wpdb->prefix . 'auth_codes';
        
        $test_codes = array(
            'TEST001',
            'DEMO123',
            'SAMPLE456',
            'AUTH789',
            'CODE999'
        );
        
        foreach ($test_codes as $code) {
            $wpdb->insert(
                $table_codes,
                array(
                    'auth_code' => $code,
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s')
            );
        }
        
        // error_log("VerifyPlus Debug - Created " . count($test_codes) . " test codes");
        
        // Verify the codes were created (commented out to prevent potential issues)
        // $created_codes = $wpdb->get_results("SELECT auth_code FROM $table_codes WHERE auth_code IN ('TEST001', 'DEMO123', 'SAMPLE456', 'AUTH789', 'CODE999')");
        // error_log("VerifyPlus Debug - Verification of created codes:");
        // foreach ($created_codes as $code) {
        //     error_log("VerifyPlus Debug - Verified: '{$code->auth_code}'");
        // }
    }
    
    /**
     * Validate email format
     *
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public static function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number format
     *
     * @param string $phone Phone to validate
     * @return bool True if valid
     */
    public static function validate_phone($phone) {
        // Basic phone validation - can be customized
        return preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone);
    }
    
    /**
     * Rate limiting check
     *
     * @param string $identifier IP or user identifier
     * @return bool True if within limits
     */
    public static function check_rate_limit($identifier) {
        global $wpdb;
        
        $table_logs = $wpdb->prefix . 'auth_logs';
        $settings = get_option('verifyplus_settings', array());
        
        if (empty($settings['enable_rate_limiting'])) {
            return true;
        }
        
        $max_attempts = intval($settings['max_attempts_per_hour'] ?? 10);
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        $attempts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_logs WHERE ip_address = %s AND created_at > %s",
            $identifier,
            $one_hour_ago
        ));
        
        return intval($attempts) < $max_attempts;
    }
}
