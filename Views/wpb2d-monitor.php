<?php
$client_id = OAUTH_CLIENTID;
$secret = OAUTH_SECRET;
$client = new BitcasaClient();

if(!empty($_REQUEST['authorization_code'])) {

try {
	if (!$client->authenticate($client_id, $secret)) {
		die("failed to authenticate");
	}
}
catch (Exception $ex) {
	var_dump($ex);
	die($ex->getMessage());
}
//echo $client->getAccessToken();
$a = $client->getAccessToken();
if( !empty($a) ) {
global $wpdb;
$table_name_bitcasa = $wpdb->prefix . "wpb2d_options";
$wpdb->query("INSERT INTO $table_name_bitcasa (name, value) VALUES('bitcasa_access_token','".$a."')");
//$re_dir = site_url( 'wp-admin/admin.php?page=backup-to-dropbox&access_token='.$a );
$re_dir = site_url( 'wp-admin/admin.php?page=backup-to-dropbox' );
print<<<EOM
	 <script>
		window.location.href = "$re_dir";
	 </script>
EOM;
 exit;
 }
 ?>
 
 <?php } else { ?>
 
 		 
 <?php
 
$manager = WPB2D_Factory::get('extension-manager');

if (isset($_REQUEST['error'])) {
    add_settings_error('error', 'wpb2d_premium_error', sprintf(__('There was an error with your payment, please contact %s to resolve.', 'wpbtd'), '<a href="mailto:michael.dewildt@gmail.com">Mikey</a>'), 'error');
}

if (isset($_REQUEST['title'])) {
    add_settings_error('general', 'wpb2d_premium_success', sprintf(__('You have succesfully purchased %s.', 'wpbtd'), "<strong>{$_REQUEST['title']}</strong>"), 'updated');
}

if (isset($_POST['name'])) {
    try {
        $ext = $manager->install($_POST['name']);
        $slug = $manager->get_menu_slug($ext);
        $title = $ext->get_menu();

        add_settings_error('general', 'wpb2d_premium_success', __('Installation successful. Please configure the extension from its menu item.', 'wpbtd'), 'updated');

        ?><script type='text/javascript'>
            jQuery(document).ready(function ($) {
                $('a[href$="backup-to-dropbox-premium"]').parent().before('<li><a href="admin.php?page=<?php echo $slug ?>"><?php echo $title ?></a></li>');
            });
        </script><?php
    } catch (Exception $e) {
        add_settings_error('error', 'wpb2d_premium_error', $e->getMessage(), 'error');
    }
}

try {
    $extensions = $manager->get_extensions();
} catch (Exception $e) {
    add_settings_error('error', 'wpb2d_premium_error', $e->getMessage(), 'error');
}

 
?>
<script type='text/javascript'>
    jQuery(document).ready(function ($) {
        $("#tabs").tabs();
    });
</script>
<div class="wrap premium" id="wpb2d">
    <div class="icon32"><img width="36px" height="36px"
                                 src="<?php echo $uri ?>/Images/WordPressBackupToDropbox_64.png"
                                 alt="WordPress Backup to Bitcasa Logo"></div>
    <h2><?php _e('WordPress Backup to Bitcasa', 'wpbtd'); ?></h2>
    <p class="description"><?php printf(__('Version %s', 'wpbtd'), BACKUP_TO_DROPBOX_VERSION) ?></p>

    <?php settings_errors(); ?>

    <h3><?php _e('Premium Extensions', 'wpbtd'); ?></h3>
    <div>
        <p>
            <?php _e('Welcome to Premium Extensions. Please choose an extension below to enhance WordPress Backup to Bitcasa.', 'wpbtd'); ?>
            <?php _e('Installing a premium extension is easy:', 'wpbtd'); ?>
        </p>
        <ol class="instructions">
            <li><?php _e('Click Buy Now and pay using PayPal.', 'wpbtd'); ?></li>
            <li><?php _e('Click Install Now to download and install the extension.', 'wpbtd'); ?></li>
            <li><?php _e("That's it, options for your extension will be available in the menu on the left.", 'wpbtd'); ?></li>
            <li><?php _e('If you manage many websites, consider the multiple site options.'); ?></li>
        </ol>
    
    </div>

    <p></p>

     
</div>
 <?php } ?>