<?php
/**
 * Uninstall Script
 * Path: uninstall.php
 * 
 * Fired when the plugin is uninstalled
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Define table name
$table_name = $wpdb->prefix . 'business_wizard_submissions';

// Delete database table
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete all plugin options
delete_option('biz_wizard_enabled');
delete_option('biz_wizard_admin_emails');
delete_option('biz_wizard_user_email_subject');
delete_option('biz_wizard_admin_email_subject');
delete_option('biz_wizard_email_enabled');
delete_option('biz_wizard_companies_house_api');
delete_option('biz_wizard_api_timeout');
delete_option('biz_wizard_debug_mode');

// Delete all custom post types
$post_types = array('biz_package', 'biz_type', 'biz_turnover', 'biz_payment');

foreach ($post_types as $post_type) {
    $posts = get_posts(array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }
}

// Delete transients
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_biz_wizard_%'");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_biz_wizard_%'");

// Delete signature files
$upload_dir = wp_upload_dir();
$signature_dir = $upload_dir['basedir'] . '/wizard-signatures';

if (file_exists($signature_dir)) {
    // Delete all files in directory
    $files = glob($signature_dir . '/*/*/*.png');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    
    // Delete directory structure
    $dirs = glob($signature_dir . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $year_dir) {
        $month_dirs = glob($year_dir . '/*', GLOB_ONLYDIR);
        foreach ($month_dirs as $month_dir) {
            rmdir($month_dir);
        }
        rmdir($year_dir);
    }
    
    // Delete main directory
    rmdir($signature_dir);
}

// Clear any cached data
wp_cache_flush();
