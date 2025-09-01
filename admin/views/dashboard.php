<?php
/**
 * Dashboard View
 * 
 * Main dashboard page showing statistics and overview
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get statistics with caching for better performance
global $wpdb;
$table_codes = $wpdb->prefix . 'auth_codes';
$table_logs = $wpdb->prefix . 'auth_logs';

// Get dashboard stats with single optimized query and caching
$dashboard_stats = wp_cache_get('verifyplus_dashboard_stats');
if (false === $dashboard_stats) {
    $dashboard_stats = $wpdb->get_row("
        SELECT 
            (SELECT COUNT(*) FROM $table_codes) as total_codes,
            (SELECT COUNT(*) FROM $table_logs) as total_logs,
            (SELECT COUNT(*) FROM $table_logs WHERE status = 'success') as successful_auths,
            (SELECT COUNT(*) FROM $table_logs WHERE status = 'failed') as failed_auths,
            (SELECT COUNT(*) FROM $table_codes c 
             LEFT JOIN (SELECT auth_code FROM $table_logs WHERE status = 'success' GROUP BY auth_code) l 
             ON c.auth_code = l.auth_code WHERE l.auth_code IS NULL) as unused_codes
    ", ARRAY_A);
    wp_cache_set('verifyplus_dashboard_stats', $dashboard_stats, '', 300); // Cache for 5 minutes
}

$total_codes = $dashboard_stats['total_codes'];
$total_logs = $dashboard_stats['total_logs'];
$successful_auths = $dashboard_stats['successful_auths'];
$failed_auths = $dashboard_stats['failed_auths'];
$unused_codes = $dashboard_stats['unused_codes'];
$used_codes = $total_codes - $unused_codes;
?>

<div class="wrap verifyplus-admin-page">
    <!-- Header -->
    <div class="verifyplus-header">
        <h1><?php _e('Code Authentication Dashboard', 'verifyplus'); ?></h1>
        <p><?php _e('Manage your authentication codes and monitor authentication attempts', 'verifyplus'); ?></p>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($total_codes); ?></span>
            <span class="stat-label"><?php _e('Total Codes', 'verifyplus'); ?></span>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($unused_codes); ?></span>
            <span class="stat-label"><?php _e('Unused Codes', 'verifyplus'); ?></span>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($used_codes); ?></span>
            <span class="stat-label"><?php _e('Used Codes', 'verifyplus'); ?></span>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($total_logs); ?></span>
            <span class="stat-label"><?php _e('Total Attempts', 'verifyplus'); ?></span>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($successful_auths); ?></span>
            <span class="stat-label"><?php _e('Successful', 'verifyplus'); ?></span>
        </div>
        
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($failed_auths); ?></span>
            <span class="stat-label"><?php _e('Failed', 'verifyplus'); ?></span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="verifyplus-content">
        <div class="verifyplus-section">
            <h2><?php _e('Quick Actions', 'verifyplus'); ?></h2>
            
            <div class="action-buttons">
                <a href="<?php echo admin_url('admin.php?page=verifyplus-upload'); ?>" class="verifyplus-button primary">
                    <span class="dashicons dashicons-upload"></span>
                    <?php _e('Upload Codes', 'verifyplus'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=verifyplus-codes'); ?>" class="verifyplus-button secondary">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php _e('Manage Codes', 'verifyplus'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=verifyplus-logs'); ?>" class="verifyplus-button secondary">
                    <span class="dashicons dashicons-chart-line"></span>
                    <?php _e('View Logs', 'verifyplus'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=verifyplus-settings'); ?>" class="verifyplus-button secondary">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Settings', 'verifyplus'); ?>
                </a>
            </div>
        </div>

        <!-- Shortcode Information -->
        <div class="verifyplus-section">
            <h2><?php _e('How to Use', 'verifyplus'); ?></h2>
            
            <div class="form-group">
                <label for="shortcode-display"><?php _e('Shortcode for Frontend Form:', 'verifyplus'); ?></label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" id="shortcode-display" value="[auth_form]" readonly style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-family: monospace; font-weight: bold; border: none; padding: 12px 16px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <button type="button" class="verifyplus-button secondary" onclick="copyShortcode()">
                        <span class="dashicons dashicons-clipboard"></span>
                        <?php _e('Copy', 'verifyplus'); ?>
                    </button>
                </div>
                <p class="description">
                    <?php _e('Add this shortcode to any page or post to display the authentication form.', 'verifyplus'); ?>
                </p>
            </div>
            
            <div class="form-group">
                <label><?php _e('Shortcode Parameters:', 'verifyplus'); ?></label>
                <ul style="margin-left: 20px;">
                    <li><code>title</code> - <?php _e('Custom title for the form (default: "Product Authentication")', 'verifyplus'); ?></li>
                    <li><code>submit_text</code> - <?php _e('Custom submit button text (default: "Authenticate")', 'verifyplus'); ?></li>
                </ul>
                <p class="description">
                    <?php _e('Example: [auth_form title="Verify Your Product" submit_text="Verify Now"]', 'verifyplus'); ?>
                </p>
            </div>
        </div>



        <!-- System Information -->
        <div class="verifyplus-section">
            <h2><?php _e('System Information', 'verifyplus'); ?></h2>
            
            <div class="verifyplus-table-container">
            <table class="form-table">
                <tr>
                    <th><?php _e('Plugin Version', 'verifyplus'); ?></th>
                    <td><?php echo VERIFYPLUS_VERSION; ?></td>
                </tr>
                <tr>
                    <th><?php _e('WordPress Version', 'verifyplus'); ?></th>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <th><?php _e('PHP Version', 'verifyplus'); ?></th>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <th><?php _e('Database Tables', 'verifyplus'); ?></th>
                    <td>
                        <?php 
                        $codes_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_codes'");
                        $logs_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_logs'");
                        
                        if ($codes_table_exists && $logs_table_exists) {
                            echo '<span style="color: #46b450;">✓ ' . __('All tables exist', 'verifyplus') . '</span>';
                        } else {
                            echo '<span style="color: #dc3232;">✗ ' . __('Some tables are missing', 'verifyplus') . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Form Shortcode', 'verifyplus'); ?></th>
                    <td>
                        <code>[auth_form]</code>
                        <?php if (shortcode_exists('auth_form')): ?>
                            <span style="color: #46b450;">✓ <?php _e('Registered', 'verifyplus'); ?></span>
                        <?php else: ?>
                            <span style="color: #dc3232;">✗ <?php _e('Not registered', 'verifyplus'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            </div>
        </div>
    </div>
</div>

<script>
function copyShortcode() {
    var shortcodeInput = document.getElementById('shortcode-display');
    shortcodeInput.select();
    shortcodeInput.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        // Show success message
        var button = event.target.closest('button');
        var originalText = button.innerHTML;
        button.innerHTML = '<span class="dashicons dashicons-yes"></span> <?php _e('Copied!', 'verifyplus'); ?>';
        button.style.background = 'linear-gradient(135deg, #2ed573 0%, #1e90ff 100%)';
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.style.background = '';
        }, 2000);
    } catch (err) {
        console.error('Failed to copy shortcode');
    }
}
</script>
