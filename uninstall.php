<?php
/**
 * Functionality to remove Bitcasa backup from your WordPress installation

 */
if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

delete_option('backup-to-dropbox-tokens');
delete_option('backup-to-dropbox-options');
delete_option('backup-to-dropbox-history');
delete_option('backup-to-dropbox-current-action');
delete_option('backup-to-dropbox-actions');
delete_option('backup-to-dropbox-excluded-files');
delete_option('backup-to-dropbox-file-list');
delete_option('backup-to-dropbox-in-progress');
delete_option('backup-to-dropbox-premium-extensions');
delete_option('backup-to-dropbox-processed-files');
delete_option('backup-to-dropbox-log');
delete_option('wpb2d-init-errors');

wp_clear_scheduled_hook('execute_periodic_drobox_backup');
wp_clear_scheduled_hook('execute_instant_drobox_backup');
wp_clear_scheduled_hook('monitor_dropbox_backup_hook');

remove_action('run_dropbox_backup_hook', 'run_dropbox_backup');
remove_action('monitor_dropbox_backup_hook', 'monitor_dropbox_backup');
remove_action('execute_instant_drobox_backup', 'execute_drobox_backup');
remove_action('execute_periodic_drobox_backup', 'execute_drobox_backup');
remove_action('admin_menu', 'backup_to_dropbox_admin_menu');
remove_action('wp_ajax_file_tree', 'backup_to_dropbox_file_tree');
remove_action('wp_ajax_progress', 'backup_to_dropbox_progress');

global $wpdb;

$table_name = $wpdb->prefix . 'wpb2d_options';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

$table_name = $wpdb->prefix . 'wpb2d_processed_files';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

$table_name = $wpdb->prefix . 'wpb2d_excluded_files';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

$table_name = $wpdb->prefix . 'wpb2d_premium_extensions';
$wpdb->query("DROP TABLE IF EXISTS $table_name");
