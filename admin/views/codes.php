<?php
/**
 * Manage Codes View
 * 
 * Page for managing authentication codes
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] !== '-1') {
    if (wp_verify_nonce($_POST['_wpnonce'], 'bulk-codes')) {
        $action = sanitize_text_field($_POST['action']);
        $codes = isset($_POST['codes']) ? array_map('intval', $_POST['codes']) : array();
        
        if (!empty($codes)) {
            global $wpdb;
            $table_codes = $wpdb->prefix . 'auth_codes';
            
            switch ($action) {
                case 'delete':
                    $wpdb->query("DELETE FROM $table_codes WHERE id IN (" . implode(',', $codes) . ")");
                    add_settings_error('verifyplus', 'bulk_delete_success', sprintf(__('%d codes deleted successfully.', 'verifyplus'), count($codes)), 'success');
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
$table_codes = $wpdb->prefix . 'auth_codes';
$table_logs = $wpdb->prefix . 'auth_logs';

$where_conditions = array();
$where_values = array();

if (!empty($search)) {
    $where_conditions[] = "c.auth_code LIKE %s";
    $where_values[] = '%' . $wpdb->esc_like($search) . '%';
}

$join_clause = "LEFT JOIN (
    SELECT auth_code, created_at 
    FROM $table_logs 
    WHERE status = 'success' 
    GROUP BY auth_code
) l ON c.auth_code = l.auth_code";

if ($status_filter === 'used') {
    $where_conditions[] = "l.auth_code IS NOT NULL";
} elseif ($status_filter === 'unused') {
    $where_conditions[] = "l.auth_code IS NULL";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count
$count_query = "SELECT COUNT(*) FROM $table_codes c $join_clause $where_clause";
if (!empty($where_values)) {
    $count_query = $wpdb->prepare($count_query, $where_values);
}
$total_items = $wpdb->get_var($count_query);

// Get codes
$query = "SELECT c.*, 
          CASE WHEN l.auth_code IS NOT NULL THEN 'used' ELSE 'unused' END as actual_status,
          l.created_at as used_at
          FROM $table_codes c 
          $join_clause 
          $where_clause 
          ORDER BY c.created_at DESC 
          LIMIT %d OFFSET %d";

$query_values = array_merge($where_values, array($per_page, $offset));
$codes = $wpdb->get_results($wpdb->prepare($query, $query_values), ARRAY_A);

// Get statistics with caching
$codes_stats = wp_cache_get('verifyplus_codes_stats');
if (false === $codes_stats) {
    $codes_stats = $wpdb->get_row("
        SELECT 
            COUNT(c.id) as total_codes,
            SUM(CASE WHEN l.auth_code IS NULL THEN 1 ELSE 0 END) as unused_codes,
            SUM(CASE WHEN l.auth_code IS NOT NULL THEN 1 ELSE 0 END) as used_codes
        FROM $table_codes c 
        LEFT JOIN (
            SELECT auth_code FROM $table_logs 
            WHERE status = 'success' 
            GROUP BY auth_code
        ) l ON c.auth_code = l.auth_code
    ", ARRAY_A);
    wp_cache_set('verifyplus_codes_stats', $codes_stats, '', 300); // Cache for 5 minutes
}
$unused_codes = $codes_stats['unused_codes'];
$used_codes = $codes_stats['used_codes'];

$total_pages = ceil($total_items / $per_page);
?>

<div class="wrap verifyplus-admin-page">
    <!-- Header -->
    <div class="verifyplus-header">
        <h1><?php _e('Manage Authentication Codes', 'verifyplus'); ?></h1>
        <p><?php _e('View and manage all authentication codes in your system', 'verifyplus'); ?></p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-number"><?php echo number_format($total_items); ?></span>
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
    </div>

    <!-- Filters and Actions -->
    <div class="verifyplus-content">
        <div class="verifyplus-section">
            <!-- Search and Filters -->
            <form method="get" class="search-form">
                <input type="hidden" name="page" value="verifyplus-codes">
                
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 20px;">
                    <div style="flex: 1;">
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search codes...', 'verifyplus'); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    
                    <div>
                        <select name="status" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value=""><?php _e('All Status', 'verifyplus'); ?></option>
                            <option value="unused" <?php selected($status_filter, 'unused'); ?>><?php _e('Unused', 'verifyplus'); ?></option>
                            <option value="used" <?php selected($status_filter, 'used'); ?>><?php _e('Used', 'verifyplus'); ?></option>
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
                            <a href="<?php echo admin_url('admin.php?page=verifyplus-codes'); ?>" class="verifyplus-button secondary">
                                <span class="dashicons dashicons-dismiss"></span>
                                <?php _e('Clear', 'verifyplus'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="<?php echo admin_url('admin.php?page=verifyplus-upload'); ?>" class="verifyplus-button primary">
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e('Add New Codes', 'verifyplus'); ?>
                </a>
                
                <button type="button" class="verifyplus-button secondary export-action" data-type="codes">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Export Codes', 'verifyplus'); ?>
                </button>
                
                <button type="button" class="verifyplus-button danger delete-all-action" data-type="codes">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Delete All Codes', 'verifyplus'); ?>
                </button>
            </div>
        </div>

        <!-- Codes Table -->
        <div class="verifyplus-section">
            <h2><?php _e('Authentication Codes', 'verifyplus'); ?></h2>
            
            <?php if ($codes): ?>
                <form method="post">
                    <?php wp_nonce_field('bulk-codes'); ?>
                    
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
                        <table class="verifyplus-table verifyplus-codes-table">
                        <thead>
                            <tr>
                                <td class="manage-column column-cb check-column">
                                    <input type="checkbox" id="cb-select-all-1">
                                </td>
                                <th><?php _e('Code', 'verifyplus'); ?></th>
                                <th><?php _e('Status', 'verifyplus'); ?></th>
                                <th><?php _e('Created', 'verifyplus'); ?></th>
                                <th><?php _e('Used At', 'verifyplus'); ?></th>
                                <th style="width: 60px;"><?php _e('Actions', 'verifyplus'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($codes as $code): ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="codes[]" value="<?php echo $code['id']; ?>">
                                    </th>
                                    <td>
                                        <span class="code-badge"><?php echo esc_html($code['auth_code']); ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $code['actual_status']; ?>">
                                            <?php echo ucfirst($code['actual_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="date-badge"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($code['created_at'])); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($code['used_at']) {
                                            echo '<span class="date-badge">' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($code['used_at'])) . '</span>';
                                        } else {
                                            echo '<span class="date-badge" style="background: rgba(0,0,0,0.1); color: #666;">—</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="action-button delete delete-action" data-id="<?php echo $code['id']; ?>" data-type="code" aria-label="<?php esc_attr_e('Delete', 'verifyplus'); ?>" title="<?php esc_attr_e('Delete', 'verifyplus'); ?>">
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
                <div class="no-codes-found" style="text-align: center; padding: 60px 20px; color: #333;">
                    <span class="dashicons dashicons-list-view" style="font-size: 64px; margin-bottom: 20px; display: block; opacity: 0.5;"></span>
                    <h3><?php _e('No codes found', 'verifyplus'); ?></h3>
                    <p><?php _e('No authentication codes match your current filters.', 'verifyplus'); ?></p>
                    
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <a href="<?php echo admin_url('admin.php?page=verifyplus-codes'); ?>" class="verifyplus-button secondary">
                            <?php _e('Clear Filters', 'verifyplus'); ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo admin_url('admin.php?page=verifyplus-upload'); ?>" class="verifyplus-button primary">
                            <span class="dashicons dashicons-plus"></span>
                            <?php _e('Add Your First Codes', 'verifyplus'); ?>
                        </a>
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
        $('input[name="codes[]"]').prop('checked', this.checked);
    });
    
    // Update select all when individual checkboxes change
    $('input[name="codes[]"]').on('change', function() {
        var total = $('input[name="codes[]"]').length;
        var checked = $('input[name="codes[]"]:checked').length;
        $('#cb-select-all-1').prop('checked', checked === total);
    });
});
</script>
