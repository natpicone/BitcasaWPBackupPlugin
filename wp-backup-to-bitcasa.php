<?php
/*
Plugin Name: WordPress Backup to Bitcasa
Description: Keep your valuable WordPress website, its media and database backed up to Bitcasa in minutes with this sleek, easy to use plugin.
Version: 1.0.0
Author: Bitcasa, Inc.
*/
define('BACKUP_TO_BITCASA_VERSION', '1.0.0');
define('BACKUP_TO_BITCASA_DATABASE_VERSION', '2');
define('EXTENSIONS_DIR', str_replace('/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR . '/plugins/wordpress-backup-to-bitcasa/Classes/Extension/'));
define('CHUNKED_UPLOAD_THREASHOLD', 10485760); //10 MB
define('MINUMUM_PHP_VERSION', '5.2.16');
if (function_exists('spl_autoload_register')) {
    spl_autoload_register('wpb2b_autoload');
} else {
					 
					require_once 'Classes/Extension/Base.php';
					require_once 'Classes/Extension/Manager.php';
					require_once 'Classes/Extension/DefaultOutput.php';
					require_once 'Classes/Processed/Base.php';
					require_once 'Classes/Processed/Files.php';
					require_once 'Classes/Processed/DBTables.php';
					require_once 'Classes/DatabaseBackup.php';
					require_once 'Classes/FileList.php';
					require_once 'Classes/BitcasaFacade.php';
					require_once 'Classes/Config.php';
					require_once 'Classes/BackupController.php';
					require_once 'Classes/Logger.php';
					require_once 'Classes/Factory.php';
					require_once 'Classes/UploadTracker.php';
	
}

function wpb2b_autoload($className)
{
    $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    if (preg_match('/^BACKUP/', $fileName)) {
         $fileName = 'Classes' . str_replace('BACKUP', '', $fileName);
    } elseif (preg_match('/^Bitca/', $fileName)) {
         $fileName = 'Bitca' . DIRECTORY_SEPARATOR . $fileName;
    } else {
        return false;
    }

   $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $fileName;

    if (file_exists($path)) {
        require_once $path;
    }
}

function wpb2b_style()
{
    //Register stylesheet
    wp_register_style('wpb2b-style', plugins_url('wp-backup-to-bitcasa.css', __FILE__) );
    wp_enqueue_style('wpb2b-style');
}

/**
 * A wrapper function that adds an options page to setup Bitcasa Backup
 * @return void
 */
function backup_to_bitcasa_admin_menu()
{
   $imgUrl = rtrim(WP_PLUGIN_URL, '/') . '/wordpress-backup-to-bitcasa/Images/favicon_bitcasa.ico';

    $text = __('Bitcasa', 'wpbtd');
    add_menu_page($text, $text, 'activate_plugins', 'backup-to-bitcasa', 'backup_to_bitcasa_admin_menu_contents', $imgUrl, '80.0564');

    $text = __('Backup Settings', 'wpbtd');
    add_submenu_page('backup-to-bitcasa', $text, $text, 'activate_plugins', 'backup-to-bitcasa', 'backup_to_bitcasa_admin_menu_contents');

    if (version_compare(PHP_VERSION, MINUMUM_PHP_VERSION) >= 0) {
        $text = __('Account Upgrade', 'wpbtd');
        add_submenu_page('backup-to-bitcasa', $text, $text, 'activate_plugins', 'backup-to-bitcasa-monitor', 'backup_to_bitcasa_monitor');

        BACKUP_Extension_Manager::construct()->add_menu_items();

        $text = __('Premium Extensions', 'wpbtd');
        
    }
	
	$text = __('Bitcasa Monitor', 'wpbtd');
    add_submenu_page('backup-to-bitcasa', $text, $text, 'activate_plugins', 'backup-to-store-bitcasa', 'backup_to_admin_menu_contents_bitcasa');
	
}

/**
 * A wrapper function that includes the backup to Bitcasa options page
 * @return void
 */
function backup_to_bitcasa_admin_menu_contents()
{
    
	include_once 'BitcasaClient.php';
	$uri = rtrim(WP_PLUGIN_URL, '/') . '/wordpress-backup-to-bitcasa';

    if(version_compare(PHP_VERSION, MINUMUM_PHP_VERSION) >= 0) {
        include 'Views/wpb2b-options.php';
    } else {
        include 'Views/wpb2b-deprecated.php';
 }
}


function backup_to_admin_menu_contents_bitcasa()
{
		include_once 'BitcasaClient.php';
        include 'Views/wpb2b-bitcasa-options.php';
	}


/**
 * A wrapper function that includes the backup to Bitcasa monitor page
 * @return void
 */
function backup_to_bitcasa_monitor()
{ 

			require_once(ABSPATH . 'wp-admin/admin-header.php');
			include_once 'Bitcasa/BitcasaClient.php';
			include 'Views/wpb2b-monitor.php';
 

}

/**
 * A wrapper function that includes the backup to Bitcasa premium page
 * @return void
 */
function backup_to_bitcasa_premium()
{
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');

    $uri = rtrim(WP_PLUGIN_URL, '/') . '/wordpress-backup-to-bitcasa';
    include 'Views/wpb2b-premium.php';
}

/**
 * A wrapper function for the file tree AJAX request
 * @return void
 */
function backup_to_bitcasa_file_tree()
{
    include 'Views/wpb2b-file-tree.php';
    die();
}

/**
 * A wrapper function for the progress AJAX request
 * @return void
 */
function backup_to_bitcasa_progress()
{
    include 'Views/wpb2b-progress.php';
    die();
}

/**
 * A wrapper function that executes the backup
 * @return void
 */
function execute_drobox_backup()
{
    BACKUP_Factory::get('logger')->delete_log();
    BACKUP_Factory::get('logger')->log(sprintf(__('Backup started on %s.', 'wpbtd'), date("l F j, Y", strtotime(current_time('mysql')))));

    $time = ini_get('max_execution_time');
    BACKUP_Factory::get('logger')->log(sprintf(
        __('Your time limit is %s and your memory limit is %s'),
        $time ? $time . ' ' . __('seconds', 'wpbtd') : __('unlimited', 'wpbtd'),
        ini_get('memory_limit')
    ));

    if (ini_get('safe_mode')) {
        BACKUP_Factory::get('logger')->log(__("Safe mode is enabled on your server so the PHP time and memory limit cannot be set by the backup process. So if your backup fails it's highly probable that these settings are too low.", 'wpbtd'));
    }

    BACKUP_Factory::get('config')->set_option('in_progress', true);

    if (defined('BACKUP_TEST_MODE')) {
        run_bitcasa_backup();
    } else {
        wp_schedule_single_event(time(), 'run_bitcasa_backup_hook');
        wp_schedule_event(time(), 'every_min', 'monitor_bitcasa_backup_hook');
    }
}

/**
 * @return void
 */
function monitor_bitcasa_backup()
{
    $config = BACKUP_Factory::get('config');
    $mtime = filemtime(BACKUP_Factory::get('logger')->get_log_file());

    //5 mins to allow for socket timeouts and long uploads
    if ($config->get_option('in_progress') && ($mtime < time() - 300)) {
        BACKUP_Factory::get('logger')->log(sprintf(__('There has been no backup activity for a long time. Attempting to resume the backup.' , 'wpbtd'), 5));
        $config->set_option('is_running', false);

        wp_schedule_single_event(time(), 'run_bitcasa_backup_hook');
    }
}

/**
 * @return void
 */
function run_bitcasa_backup()
{
    $options = BACKUP_Factory::get('config');
    if (!$options->get_option('is_running')) {
        $options->set_option('is_running', true);
        BACKUP_BackupController::construct()->execute();
    }
}

/**
 * Adds a set of custom intervals to the cron schedule list
 * @param  $schedules
 * @return array
 */
function backup_to_bitcasa_cron_schedules($schedules)
{
    $new_schedules = array(
        'every_min' => array(
            'interval' => 60,
            'display' => 'BACKUP - Monitor'
        ),
        'daily' => array(
            'interval' => 86400,
            'display' => 'BACKUP - Daily'
        ),
        'weekly' => array(
            'interval' => 604800,
            'display' => 'BACKUP - Weekly'
        ),
        'fortnightly' => array(
            'interval' => 1209600,
            'display' => 'BACKUP - Fortnightly'
        ),
        'monthly' => array(
            'interval' => 2419200,
            'display' => 'BACKUP - Once Every 4 weeks'
        ),
        'two_monthly' => array(
            'interval' => 4838400,
            'display' => 'BACKUP - Once Every 8 weeks'
        ),
        'three_monthly' => array(
            'interval' => 7257600,
            'display' => 'BACKUP - Once Every 12 weeks'
        ),
    );

    return array_merge($schedules, $new_schedules);
}

function wpb2b_install()
{
    $wpdb = BACKUP_Factory::db();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $table_name = $wpdb->prefix . 'wpb2b_options';
    dbDelta("CREATE TABLE $table_name (
        name varchar(50) NOT NULL,
        value varchar(255) NOT NULL,
        UNIQUE KEY name (name)
    );");

    $table_name = $wpdb->prefix . 'wpb2b_processed_files';
    dbDelta("CREATE TABLE $table_name (
        file varchar(255) NOT NULL,
        offset int NOT NULL DEFAULT 0,
        uploadid varchar(50),
        UNIQUE KEY file (file)
    );");

    $table_name = $wpdb->prefix . 'wpb2b_processed_dbtables';
    dbDelta("CREATE TABLE $table_name (
        name varchar(255) NOT NULL,
        count int NOT NULL DEFAULT 0,
        UNIQUE KEY name (name)
    );");

    $table_name = $wpdb->prefix . 'wpb2b_excluded_files';
    dbDelta("CREATE TABLE $table_name (
        file varchar(255) NOT NULL,
        isdir tinyint(1) NOT NULL,
        UNIQUE KEY file (file)
    );");

    //Ensure that there where no insert errors
    $errors = array();

    global $EZSQL_ERROR;
    if ($EZSQL_ERROR) {
        foreach ($EZSQL_ERROR as $error) {
            if (preg_match("/^CREATE TABLE {$wpdb->prefix}wpb2b_/", $error['query']))
                $errors[] = $error['error_str'];
        }

        delete_option('wpb2b-init-errors');
        add_option('wpb2b-init-errors', implode($errors, '<br />'), false, 'no');
    }

    //Only set the DB version if there are no errors
    if (empty($errors)) {
        BACKUP_Factory::get('config')->set_option('database_version', BACKUP_TO_BITCASA_DATABASE_VERSION);
    }
}

function wpb2b_init()
{
    try {
        if (BACKUP_Factory::get('config')->get_option('database_version') < BACKUP_TO_BITCASA_DATABASE_VERSION) {
            wpb2b_install();
        }

        if (!get_option('wpb2b-premium-extensions')) {
            add_option('wpb2b-premium-extensions', array(), false, 'no');
        }

    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}
function get_sanitized_home_path()
{
    //Needed for get_home_path() function and may not be loaded
    require_once(ABSPATH . 'wp-admin/includes/file.php');

    //If site address and WordPress address differ but are not in a different directory
    //then get_home_path will return '/' and cause issues.
    $home_path = get_home_path();
    if ($home_path == '/') {
        $home_path = ABSPATH;
    }
    return rtrim(str_replace('/', DIRECTORY_SEPARATOR, $home_path), DIRECTORY_SEPARATOR);
}

//More cron shedules
add_filter('cron_schedules', 'backup_to_bitcasa_cron_schedules');

//Backup hooks
add_action('monitor_bitcasa_backup_hook', 'monitor_bitcasa_backup');
add_action('run_bitcasa_backup_hook', 'run_bitcasa_backup');
add_action('execute_periodic_bitcasa_backup', 'execute_drobox_backup');
add_action('execute_instant_drobox_backup', 'execute_drobox_backup');

//Register database install
register_activation_hook(__FILE__, 'wpb2b_install');

add_action('admin_init', 'wpb2b_init');
add_action('admin_enqueue_scripts', 'wpb2b_style');

//i18n language text domain
load_plugin_textdomain('wpbtd', false, 'wordpress-backup-to-bitcasa/Languages/');

if (is_admin()) {
    //WordPress filters and actions
    add_action('wp_ajax_file_tree', 'backup_to_bitcasa_file_tree');
    add_action('wp_ajax_progress', 'backup_to_bitcasa_progress');

    if (defined('MULTISITE') && MULTISITE) {
        add_action('network_admin_menu', 'backup_to_bitcasa_admin_menu');
    } else {
        add_action('admin_menu', 'backup_to_bitcasa_admin_menu');
    }
}
