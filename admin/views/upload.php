<?php
/**
 * Upload Codes View
 * 
 * Page for uploading authentication codes via CSV or manual entry
 * 
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'upload_codes') {
        if (wp_verify_nonce($_POST['_wpnonce'], 'verifyplus_admin_nonce')) {
            $upload_type = sanitize_text_field($_POST['upload_type']);
            
            if ($upload_type === 'csv' && isset($_FILES['csv_file'])) {
                handle_csv_upload();
            } elseif ($upload_type === 'manual') {
                handle_manual_upload();
            }
        }
    }
}

function handle_csv_upload() {
    global $wpdb;
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'verifyplus'));
    }
    
    $file = $_FILES['csv_file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        add_settings_error('verifyplus', 'upload_error', __('File upload failed. Please try again.', 'verifyplus'), 'error');
        return;
    }
    
    // Validate file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_extension !== 'csv') {
        add_settings_error('verifyplus', 'file_type_error', __('Please upload a CSV file.', 'verifyplus'), 'error');
        return;
    }
    
    // Open file
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        add_settings_error('verifyplus', 'file_read_error', __('Could not read the uploaded file.', 'verifyplus'), 'error');
        return;
    }
    
    $table_codes = $wpdb->prefix . 'auth_codes';
    $imported = 0;
    $skipped = 0;
    $errors = array();
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
            $errors[] = sprintf(__('Failed to import code: %s', 'verifyplus'), $code);
        }
    }
    
    fclose($handle);
    
    // Show results
    if ($imported > 0) {
        add_settings_error(
            'verifyplus', 
            'upload_success', 
            sprintf(__('Successfully imported %d codes. %d codes skipped.', 'verifyplus'), $imported, $skipped), 
            'success'
        );
    } else {
        add_settings_error('verifyplus', 'upload_no_imports', __('No codes were imported. Please check your CSV file.', 'verifyplus'), 'warning');
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            add_settings_error('verifyplus', 'upload_error', $error, 'error');
        }
    }
}

function handle_manual_upload() {
    global $wpdb;
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'verifyplus'));
    }
    
    $codes_text = sanitize_textarea_field($_POST['codes_text']);
    $codes = array_filter(array_map('trim', explode("\n", $codes_text)));
    
    if (empty($codes)) {
        add_settings_error('verifyplus', 'no_codes', __('Please enter at least one code.', 'verifyplus'), 'error');
        return;
    }
    
    $table_codes = $wpdb->prefix . 'auth_codes';
    $imported = 0;
    $skipped = 0;
    $errors = array();
    
    foreach ($codes as $code) {
        if (empty($code)) {
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
            $errors[] = sprintf(__('Failed to import code: %s', 'verifyplus'), $code);
        }
    }
    
    // Show results
    if ($imported > 0) {
        add_settings_error(
            'verifyplus', 
            'upload_success', 
            sprintf(__('Successfully imported %d codes. %d codes skipped.', 'verifyplus'), $imported, $skipped), 
            'success'
        );
    } else {
        add_settings_error('verifyplus', 'upload_no_imports', __('No codes were imported. Please check your input.', 'verifyplus'), 'warning');
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            add_settings_error('verifyplus', 'upload_error', $error, 'error');
        }
    }
}
?>

<div class="wrap verifyplus-admin-page">
    <!-- Header -->
    <div class="verifyplus-header">
        <h1><?php _e('Upload Authentication Codes', 'verifyplus'); ?></h1>
        <p><?php _e('Add authentication codes to your system via CSV upload or manual entry', 'verifyplus'); ?></p>
    </div>

    <!-- Navigation Tabs -->
    <nav class="nav-tab-wrapper">
        <a href="#csv-upload" class="nav-tab nav-tab-active" data-tab="csv">
            <span class="dashicons dashicons-upload"></span>
            <?php _e('CSV Upload', 'verifyplus'); ?>
        </a>
        <a href="#manual-upload" class="nav-tab" data-tab="manual">
            <span class="dashicons dashicons-edit"></span>
            <?php _e('Manual Entry', 'verifyplus'); ?>
        </a>
    </nav>

    <!-- Settings Errors -->
    <?php settings_errors('verifyplus'); ?>

    <!-- CSV Upload Section -->
    <div id="csv-upload" class="tab-content active">
        <div class="verifyplus-section">
            <h2><?php _e('Upload CSV File', 'verifyplus'); ?></h2>
            
            <form method="post" enctype="multipart/form-data" class="verifyplus-form" data-form-type="upload">
                <?php wp_nonce_field('verifyplus_admin_nonce'); ?>
                <input type="hidden" name="action" value="upload_codes">
                <input type="hidden" name="upload_type" value="csv">
                
                <div class="form-group">
                    <label><?php _e('CSV File Format', 'verifyplus'); ?></label>
                    <div class="file-upload-area" id="csv-upload-area">
                        <div class="file-upload-icon">📁</div>
                        <div class="file-upload-text"><?php _e('Drop CSV file here or click to browse', 'verifyplus'); ?></div>
                        <div class="file-upload-hint"><?php _e('Only CSV files are allowed', 'verifyplus'); ?></div>
                        <input type="file" name="csv_file" id="csv-file" accept=".csv" style="display: none;" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><?php _e('CSV Format Requirements', 'verifyplus'); ?></label>
                    <ul style="margin-left: 20px;">
                        <li><?php _e('First column should contain the authentication codes', 'verifyplus'); ?></li>
                        <li><?php _e('First row is treated as header and will be skipped', 'verifyplus'); ?></li>
                        <li><?php _e('Each code should be on a separate row', 'verifyplus'); ?></li>
                        <li><?php _e('Empty rows will be ignored', 'verifyplus'); ?></li>
                        <li><?php _e('Duplicate codes will be skipped', 'verifyplus'); ?></li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="verifyplus-button primary">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Upload Codes', 'verifyplus'); ?>
                    </button>
                    
                    <a href="#" class="verifyplus-button secondary" onclick="downloadSampleCSV()">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download Sample CSV', 'verifyplus'); ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Manual Upload Section -->
    <div id="manual-upload" class="tab-content">
        <div class="verifyplus-section">
            <h2><?php _e('Manual Code Entry', 'verifyplus'); ?></h2>
            
            <form method="post" class="verifyplus-form" data-form-type="upload">
                <?php wp_nonce_field('verifyplus_admin_nonce'); ?>
                <input type="hidden" name="action" value="upload_codes">
                <input type="hidden" name="upload_type" value="manual">
                
                <div class="form-group">
                    <label for="codes_text"><?php _e('Enter Codes', 'verifyplus'); ?></label>
                    <textarea 
                        id="codes_text" 
                        name="codes_text" 
                        rows="15" 
                        placeholder="<?php esc_attr_e('Enter one code per line&#10;Example:&#10;CODE001&#10;CODE002&#10;CODE003', 'verifyplus'); ?>"
                        required
                    ></textarea>
                    <p class="description">
                        <?php _e('Enter one authentication code per line. Empty lines will be ignored.', 'verifyplus'); ?>
                    </p>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="verifyplus-button primary">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Codes', 'verifyplus'); ?>
                    </button>
                    
                    <button type="button" class="verifyplus-button secondary" onclick="generateSampleCodes()">
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php _e('Generate Sample Codes', 'verifyplus'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="verifyplus-content">
        <div class="verifyplus-section">
            <h2><?php _e('Quick Actions', 'verifyplus'); ?></h2>
            
            <div class="action-buttons">
                <a href="<?php echo admin_url('admin.php?page=verifyplus-codes'); ?>" class="verifyplus-button secondary">
                    <span class="dashicons dashicons-list-view"></span>
                    <?php _e('View All Codes', 'verifyplus'); ?>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=verifyplus'); ?>" class="verifyplus-button secondary">
                    <span class="dashicons dashicons-dashboard"></span>
                    <?php _e('Dashboard', 'verifyplus'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Tab Styling for Upload Page */
.nav-tab-wrapper {
    background: rgba(255, 255, 255, 0.25) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    border-radius: 16px !important;
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
    margin-bottom: 30px !important;
    overflow: hidden !important;
    display: flex !important;
    flex-wrap: wrap !important;
}

.nav-tab {
    flex: 1 !important;
    min-width: 150px !important;
    padding: 20px 25px !important;
    text-decoration: none !important;
                color: #4a4a4a !important;
    font-weight: 600 !important;
    text-align: center !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border: none !important;
    background: transparent !important;
    position: relative !important;
    overflow: hidden !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    font-size: 14px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.nav-tab::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: -100% !important;
    width: 100% !important;
    height: 100% !important;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent) !important;
    transition: left 0.6s ease !important;
}

.nav-tab:hover::before {
    left: 100% !important;
}

.nav-tab:hover {
    background: rgba(102, 126, 234, 0.1) !important;
    color: #667eea !important;
    transform: translateY(-2px) !important;
}

.nav-tab.nav-tab-active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07) !important;
}

.nav-tab .dashicons {
    font-size: 18px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.nav-tab:hover .dashicons {
    transform: scale(1.1) rotate(5deg) !important;
}

.tab-content {
    display: none !important;
}

.tab-content.active {
    display: block !important;
}

/* Enhanced content styling */
.tab-content .verifyplus-section {
    background: rgba(255, 255, 255, 0.25) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    border-radius: 16px !important;
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1) !important;
    padding: 40px !important;
    margin-bottom: 30px !important;
    position: relative !important;
    overflow: hidden !important;
}

.tab-content .verifyplus-section::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    height: 3px !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
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
        $('#' + targetTab + '-upload').addClass('active');
        
        // Update URL hash
        window.location.hash = targetTab + '-upload';
    });
    
    // Check URL hash on page load
    var hash = window.location.hash.substring(1);
    if (hash) {
        $('.nav-tab[data-tab="' + hash.replace('-upload', '') + '"]').click();
    }
    
    // File upload functionality
    var $uploadArea = $('#csv-upload-area');
    var $fileInput = $('#csv-file');
    
    // Drag and drop
    $uploadArea.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    $uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    $uploadArea.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $fileInput[0].files = files;
            updateFileDisplay(files[0]);
        }
    });
    
    // Click to upload
    $uploadArea.on('click', function() {
        $fileInput.click();
    });
    
    // File selection
    $fileInput.on('change', function() {
        if (this.files.length > 0) {
            updateFileDisplay(this.files[0]);
        }
    });
    
    function updateFileDisplay(file) {
        $uploadArea.find('.file-upload-text').text(file.name);
        $uploadArea.find('.file-upload-hint').text('File selected: ' + file.name);
        
        // Validate file type
        if (!file.name.toLowerCase().endsWith('.csv')) {
            alert('Please select a CSV file.');
            $fileInput.val('');
            $uploadArea.find('.file-upload-text').text('Drop CSV file here or click to browse');
            $uploadArea.find('.file-upload-hint').text('Only CSV files are allowed');
        }
    }
});

function downloadSampleCSV() {
    var csvContent = "Code\nDEMO001\nDEMO002\nDEMO003\nTEST001\nTEST002";
    var blob = new Blob([csvContent], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'sample_codes.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function generateSampleCodes() {
    var codes = [];
    for (var i = 1; i <= 10; i++) {
        codes.push('SAMPLE' + String(i).padStart(3, '0'));
    }
    document.getElementById('codes_text').value = codes.join('\n');
}
</script>
