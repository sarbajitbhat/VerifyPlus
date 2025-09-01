<?php
/**
 * Authentication Logs View
 * 
 * Page for viewing authentication logs
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] !== '-1') {
    if (wp_verify_nonce($_POST['_wpnonce'], 'bulk-logs')) {
        $action = sanitize_text_field($_POST['action']);
        $logs = isset($_POST['logs']) ? array_map('intval', $_POST['logs']) : array();
        
        if (!empty($logs)) {
            global $wpdb;
            $table_logs = $wpdb->prefix . 'auth_logs';
            
            switch ($action) {
                case 'delete':
                    $wpdb->query("DELETE FROM $table_logs WHERE id IN (" . implode(',', $logs) . ")");
                    add_settings_error('verifyplus', 'bulk_delete_success', sprintf(__('%d logs deleted successfully.', 'verifyplus'), count($logs)), 'success');
                    break;
            }
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 50;
$offset = ($paged - 1) * $per_page;

// Build query
global $wpdb;
$table_logs = $wpdb->prefix . 'auth_logs';

$where_conditions = array();
$where_values = array();

if (!empty($search)) {
    $where_conditions[] = "(auth_code LIKE %s OR name LIKE %s OR email LIKE %s OR phone LIKE %s)";
    $search_term = '%' . $wpdb->esc_like($search) . '%';
    $where_values[] = $search_term;
    $where_values[] = $search_term;
    $where_values[] = $search_term;
    $where_values[] = $search_term;
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = %s";
    $where_values[] = $status_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count
$count_query = "SELECT COUNT(*) FROM $table_logs $where_clause";
if (!empty($where_values)) {
    $count_query = $wpdb->prepare($count_query, $where_values);
}
$total_items = $wpdb->get_var($count_query);

// Get logs
$query = "SELECT * FROM $table_logs $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
$query_values = array_merge($where_values, array($per_page, $offset));
$logs = $wpdb->get_results($wpdb->prepare($query, $query_values), ARRAY_A);

// Get statistics with single optimized query
$stats = wp_cache_get('verifyplus_logs_stats');
if (false === $stats) {
    $stats = $wpdb->get_row("
        SELECT 
            COUNT(*) as total_logs,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_auths,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_auths
        FROM $table_logs
    ", ARRAY_A);
    wp_cache_set('verifyplus_logs_stats', $stats, '', 300); // Cache for 5 minutes
}
$total_logs = $stats['total_logs'];
$successful_auths = $stats['successful_auths'];
$failed_auths = $stats['failed_auths'];

$total_pages = ceil($total_items / $per_page);
?>

<div class="wrap verifyplus-admin-page">
    <!-- Header -->
    <div class="verifyplus-header">
        <h1><?php _e('Authentication Logs', 'verifyplus'); ?></h1>
        <p><?php _e('View all authentication attempts and their results', 'verifyplus'); ?></p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
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

    <!-- Filters and Actions -->
    <div class="verifyplus-content">
        <div class="verifyplus-section">
            <!-- Search and Filters -->
            <form method="get" class="search-form">
                <input type="hidden" name="page" value="verifyplus-logs">
                
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search logs...', 'verifyplus'); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    
                    <div>
                        <select name="status" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value=""><?php _e('All Status', 'verifyplus'); ?></option>
                            <option value="success" <?php selected($status_filter, 'success'); ?>><?php _e('Successful', 'verifyplus'); ?></option>
                            <option value="failed" <?php selected($status_filter, 'failed'); ?>><?php _e('Failed', 'verifyplus'); ?></option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="verifyplus-button secondary">
                            <span class="dashicons dashicons-search"></span>
                            <?php _e('Filter', 'verifyplus'); ?>
                        </button>
                    </div>
                    
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <div>
                            <a href="<?php echo admin_url('admin.php?page=verifyplus-logs'); ?>" class="verifyplus-button secondary">
                                <span class="dashicons dashicons-dismiss"></span>
                                <?php _e('Clear', 'verifyplus'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="verifyplus-button secondary export-action" data-type="logs">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Export Logs', 'verifyplus'); ?>
                </button>
                
                <button type="button" class="verifyplus-button danger delete-all-action" data-type="logs">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Delete All Logs', 'verifyplus'); ?>
                </button>
                
                
            </div>
        </div>

        <!-- Logs Table -->
        <div class="verifyplus-section">
            <h2><?php _e('Authentication Logs', 'verifyplus'); ?></h2>
            
            <?php if ($logs): ?>
                <form method="post">
                    <?php wp_nonce_field('bulk-logs'); ?>
                    
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <select name="action">
                                <option value="-1"><?php _e('Bulk Actions', 'verifyplus'); ?></option>
                                <option value="delete"><?php _e('Delete', 'verifyplus'); ?></option>
                            </select>
                            <button type="submit" class="verifyplus-button secondary"><?php _e('Apply', 'verifyplus'); ?></button>
                        </div>
                        
                        <div class="tablenav-pages">
                            <?php if ($total_pages > 1): ?>
                                <span class="displaying-num">
                                    <?php printf(_n('%s item', '%s items', $total_items, 'verifyplus'), number_format_i18n($total_items)); ?>
                                </span>
                                
                                <span class="pagination-links">
                                    <?php
                                    $page_links = paginate_links(array(
                                        'base' => add_query_arg('paged', '%#%'),
                                        'format' => '',
                                        'prev_text' => __('&laquo;'),
                                        'next_text' => __('&raquo;'),
                                        'total' => $total_pages,
                                        'current' => $paged,
                                        'type' => 'array'
                                    ));
                                    
                                    if ($page_links) {
                                        echo join("\n", $page_links);
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="verifyplus-table-container">
                        <table class="verifyplus-table verifyplus-logs-table">
                        <thead>
                            <tr>
                                <td class="manage-column column-cb check-column">
                                    <input type="checkbox" id="cb-select-all-1">
                                </td>
                                <th><?php _e('Code', 'verifyplus'); ?></th>
                                <th><?php _e('Name', 'verifyplus'); ?></th>
                                <th><?php _e('Email', 'verifyplus'); ?></th>
                                <th><?php _e('Phone', 'verifyplus'); ?></th>
                                <th><?php _e('Purchase Location', 'verifyplus'); ?></th>
                                <th><?php _e('Status', 'verifyplus'); ?></th>
                                <th><?php _e('IP Address', 'verifyplus'); ?></th>
                                <th><?php _e('Date', 'verifyplus'); ?></th>
                                <th style="width: 60px;"><?php _e('Actions', 'verifyplus'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="logs[]" value="<?php echo $log['id']; ?>">
                                    </th>
                                    <td>
                                        <span class="code-badge"><?php echo esc_html($log['auth_code']); ?></span>
                                    </td>
                                    <td><?php echo esc_html($log['name'] ?: '—'); ?></td>
                                    <td><?php echo esc_html($log['email'] ?: '—'); ?></td>
                                    <td><?php echo esc_html($log['phone'] ?: '—'); ?></td>
                                    <td><?php echo esc_html($log['purchase_location'] ?: '—'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $log['status']; ?>">
                                            <?php echo ucfirst($log['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="code-badge" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);"><?php echo esc_html($log['ip_address'] ?: '—'); ?></span>
                                    </td>
                                    <td>
                                        <span class="date-badge"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['created_at'])); ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="action-button delete delete-action" data-id="<?php echo $log['id']; ?>" data-type="log" aria-label="<?php esc_attr_e('Delete', 'verifyplus'); ?>" title="<?php esc_attr_e('Delete', 'verifyplus'); ?>">
                                                <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    </div>
                    
                    <div class="tablenav bottom">
                        <div class="alignleft actions bulkactions">
                            <select name="action2">
                                <option value="-1"><?php _e('Bulk Actions', 'verifyplus'); ?></option>
                                <option value="delete"><?php _e('Delete', 'verifyplus'); ?></option>
                            </select>
                            <button type="submit" class="verifyplus-button secondary"><?php _e('Apply', 'verifyplus'); ?></button>
                        </div>
                        
                        <div class="tablenav-pages">
                            <?php if ($total_pages > 1): ?>
                                <span class="displaying-num">
                                    <?php printf(_n('%s item', '%s items', $total_items, 'verifyplus'), number_format_i18n($total_items)); ?>
                                </span>
                                
                                <span class="pagination-links">
                                    <?php
                                    if ($page_links) {
                                        echo join("\n", $page_links);
                                    }
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="no-logs-found" style="text-align: center; padding: 60px 20px; color: #333;">
                    <span class="dashicons dashicons-chart-line" style="font-size: 64px; margin-bottom: 20px; display: block; opacity: 0.5;"></span>
                    <h3><?php _e('No logs found', 'verifyplus'); ?></h3>
                    <p><?php _e('No authentication logs match your current filters.', 'verifyplus'); ?></p>
                    
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <a href="<?php echo admin_url('admin.php?page=verifyplus-logs'); ?>" class="verifyplus-button secondary">
                            <?php _e('Clear Filters', 'verifyplus'); ?>
                        </a>
                    <?php else: ?>
                        <p><?php _e('Authentication logs will appear here once users start using the form.', 'verifyplus'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox
    $('#cb-select-all-1').on('change', function() {
        $('input[name="logs[]"]').prop('checked', this.checked);
    });
    
    // Update select all when individual checkboxes change
    $('input[name="logs[]"]').on('change', function() {
        var total = $('input[name="logs[]"]').length;
        var checked = $('input[name="logs[]"]:checked').length;
        $('#cb-select-all-1').prop('checked', checked === total);
    });
});
</script>
