<?php
/**
 * Functionality to remove Bitcasa backup from your WordPress installation

 */
if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

delete_option('backup-to-bitcasa-tokens');
delete_option('backup-to-bitcasa-options');
delete_option('backup-to-bitcasa-history');
delete_option('backup-to-bitcasa-current-action');
delete_option('backup-to-bitcasa-actions');
delete_option('backup-to-bitcasa-excluded-files');
delete_option('backup-to-bitcasa-file-list');
delete_option('backup-to-bitcasa-in-progress');
delete_option('backup-to-bitcasa-premium-extensions');
delete_option('backup-to-bitcasa-processed-files');
delete_option('backup-to-bitcasa-log');
delete_option('wpb2b-init-errors');

wp_clear_scheduled_hook('execute_periodic_bitcasa_backup');
wp_clear_scheduled_hook('execute_instant_drobox_backup');
wp_clear_scheduled_hook('monitor_bitcasa_backup_hook');

remove_action('run_bitcasa_backup_hook', 'run_bitcasa_backup');
remove_action('monitor_bitcasa_backup_hook', 'monitor_bitcasa_backup');
remove_action('execute_instant_drobox_backup', 'execute_drobox_backup');
remove_action('execute_periodic_bitcasa_backup', 'execute_drobox_backup');
remove_action('admin_menu', 'backup_to_bitcasa_admin_menu');
remove_action('wp_ajax_file_tree', 'backup_to_bitcasa_file_tree');
remove_action('wp_ajax_progress', 'backup_to_bitcasa_progress');

global $wpdb;

$table_name = $wpdb->prefix . 'wpb2b_options';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

$table_name = $wpdb->prefix . 'wpb2b_processed_files';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

$table_name = $wpdb->prefix . 'wpb2b_excluded_files';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

$table_name = $wpdb->prefix . 'wpb2b_premium_extensions';
$wpdb->query("DROP TABLE IF EXISTS $table_name");
