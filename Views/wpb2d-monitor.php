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
 
$re_dir = site_url( 'wp-admin/admin.php?page=backup-to-bitcasa' );
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
                $('a[href$="backup-to-bitcasa-premium"]').parent().before('<li><a href="admin.php?page=<?php echo $slug ?>"><?php echo $title ?></a></li>');
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
     
    <h2><?php _e('WordPress Backup to Bitcasa', 'wpbtd'); ?></h2>
    <p class="description"><?php printf(__('Version %s', 'wpbtd'), BACKUP_TO_BITCASA_VERSION) ?></p>

    <?php settings_errors(); ?>

    <h3><?php _e('Bitcasa Premium Extensions', 'wpbtd'); ?></h3>
    <div>
        <p>
            <?php _e('Upgrade your Bitcasa storage and store all your large media files easy sharing and access.', 'wpbtd'); ?>
         
		 
        </p>
		 
        
					<div class="product-box--bundle product-box--bundle--2" style="margin-top: 35px !important;">
            <div class="product-box__title wp-menu-name">Get 1TB of Storage</div>
            <div class="product-box__subtitle">Upgrade your Bitcasa storage and store all your large media files for easy sharing and access.</div>

                             
            
                            <div class="product-box__button">
                    
                    
                        <a href="https://www.bitcasa.com/pricing"><span class="button-primary">Buy Now</span></a>
                    
                </div>
            
                    </div>
    
    </div>

    <p></p>

     
</div>
 <?php } ?>