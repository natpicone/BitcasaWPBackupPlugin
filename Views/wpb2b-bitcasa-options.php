	<style>
	.new_class_a{
	 background: none repeat scroll 0 0 #2EA2CC;
    border-color: #0074A2;
    box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset, 0 1px 0 rgba(0, 0, 0, 0.15);
    color: #FFFFFF;
    height: auto;
    padding: 10px;
    text-align: center;
    text-decoration: none;
    width: 100px; }
	</style>
	<?php
						
				error_reporting(0);
				$config = BACKUP_Factory::get('config');
				$backup = new BACKUP_BackupController();
				///////////////////////////////////////////////Get Acces Token//////////////////////////// 
				global $wpdb;
				$table_name_bitcasa = $wpdb->prefix . "wpb2b_options";
				$sql = "SELECT * from $table_name_bitcasa where name='bitcasa_access_token'";
				$query = $wpdb->get_results($sql);
				///////////////////////////////////////////////And Get Acces Token////////////////////////////
				///////////////////////////////////////////////Get All Database Files////////////////////////////
				$alldatabase_table="SHOW TABLES FROM $wpdb->dbname";
				$query_databasetable_name = $wpdb->get_results($alldatabase_table);
				///////////////////////////////////////////////Code and////////////////////////////
				///////////////////////////////////////////////Get all file and folder path////////////////////////////
				$all_file_name = $wpdb->prefix . "wpb2b_excluded_files";
				$sql_all_filename = "SELECT * from $all_file_name ";
				$query_all_file = $wpdb->get_results($sql_all_filename);
				///////////////////////////////////////////////And code////////////////////////////
				$bitcasa_access_token=$query[0]->value;
				

				///////////////////////////////////////////////Check User are authrise or not //////////////////////////// 		
		
				if (!empty($bitcasa_access_token)) {	
					 $client = new BitcasaClient();
					 $client->setAccessTokenFromRequest();
					  
	} else {
		?>
    <h3><?php _e('Thank you for installing WordPress Backup to Bitcasa! Please Authrise Your Account', 'wpbtd'); ?></h3>
    <p><?php _e('In order to use this plugin you will need to authorized it with your Bitcasa account.', 'wpbtd'); ?></p>
    <p><?php _e('Please click the authorize button below and follow the instructions inside the pop up window.', 'wpbtd'); ?></p>
        <?php if (array_key_exists('continue', $_POST) && !$bitcasa->is_authorized()): ?>
            <?php $bitcasa->unlink_account()->init(); ?>
            <p style="color: red"><?php _e('There was an error authorizing the plugin with your Bitcasa account. Please try again.', 'wpbtd'); ?></p>
        <?php endif; ?>
   
								<!--        Authrise Link..  -->
	 
	 <div class="new_class_a">
<a style="color:#FFFFFF;text-decoration:none;" href="https://developer.api.bitcasa.com/v1/oauth2/authenticate?client_id=<?php echo OAUTH_CLIENTID; ?>&redirect=http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF']; ?>?page=backup-to-bitcasa-monitor/" target="_blank" > Authorize</a>
	 </div>
 
	
	
	
<?php die();	}




      if (array_key_exists('stop_backup', $_POST)) {
    	///////////////////////////////////////////////Stop Backup code////////////////////////////
		check_admin_referer('backup_to_bitcasa_monitor_stop');
		$backup->stop();
		add_settings_error('wpb2b_monitor', 'backup_stopped', __('Backup stopped.', 'wpbtd'), 'updated');
			
		///////////////////////////////////////////////Stop backup code and////////////////////////////	
			
} elseif (array_key_exists('start_backup', $_POST)) {


	 ///////////////////////////////////////////////Start backup code ///////////////////////////
	
    check_admin_referer('backup_to_bitcasa_monitor_stop');
    $backup->backup_now();
    $started = true;
    
	$date_new = date('m-d-Y', time());
    $bid = $client->getInfiniteDrive();
     ///////////////////////////////////////////////Add " bitcasa_backup " folder on bitcasa server////////////////////////////
	$item = $bid->add($client, "bitcasa_backup");
	
	///////////////////////////////////////////////Add " app " folder in bitcasa_backup folder on bitcasa server////////////////////////////
	
	
    $newfolder =  $item->add($client, "App");
    $mainfolder =  $newfolder->add($client,$date_new);
	
	///////////////////////////////////////////////Get File and folder upload path////////////////////////////
	
	$bacup_path = $mainfolder->getPath();
   
  if(!empty($bacup_path)) {
  
				global $wpdb;
				$table_name_bitcasa = $wpdb->prefix . "wpb2b_options";
				$wpdb->query("INSERT INTO $table_name_bitcasa (name, value) VALUES('".$date_new."','".$bacup_path."')");
				$sql_last_bitcasa = "SELECT * from $table_name_bitcasa where name='last_bitcasa_time'";
				$query_last_bitcasa = $wpdb->get_results($sql_last_bitcasa);
				if(empty($query_last_bitcasa)) {
				$wpdb->query("INSERT INTO $table_name_bitcasa (name, value) VALUES('last_bitcasa_time','".$date_new."')");
				} else {
				 
				
				$wpdb->query("UPDATE $table_name_bitcasa SET value = $date_new WHERE name = 'last_bitcasa_time'");
				}
  
  						$table_name_bitcasa_exe = $wpdb->prefix . "wpb2b_excluded_files";
						$sql_exe = "SELECT * from $table_name_bitcasa_exe";
						$query_exe = $wpdb->get_results($sql_exe);
					 
					 
////////////////////////////////////////////////////////////////////////For The File ,Folder and database upload  ////////////////////////////////////////////////////////////////
					 
					 	 
					 foreach($query_exe as $result_bitcasa) {
					
								if($result_bitcasa->isdir=='0') {
						///////////////////File upload/////////////////////////		
								$request_url = 'https://developer.api.bitcasa.com/v1/files'.$bacup_path.'/?access_token='.$bitcasa_access_token.'';
								$post_params['name'] = urlencode('Test User');
								$post_params['file'] = '@'.$result_bitcasa->file;
								$post_params['submit'] = urlencode('submit');
							
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $request_url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_POST, true);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
								$result = curl_exec($ch);
								$response_new = json_decode($result, true);
						 
								curl_close($ch); 
							///////////////////File upload end/////////////////////////			
								
								} else {
						///////////////////Folder Create/////////////////////////				
								$folder_array=explode("/",$result_bitcasa->file);
								$folder_name=end($folder_array);
								$request_url = 'https://developer.api.bitcasa.com/v1/folders'.$bacup_path.'/?access_token='.$bitcasa_access_token.'';
								$post_params = array('folder_name' => $folder_name);
											
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $request_url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_POST, true);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
								$result = curl_exec($ch);
 								$response_new = json_decode($result, true);
								$newpa=$response_new['result']['items']['0']['path'];
							 
								curl_close($ch); 
							///////////////////Folder Create/////////////////////////							
						      if(!empty($response_new)) {
							  
							  ///////////////////Files upload in  Folder ()/////////////////////////		
							  
							  if ($dir = opendir($result_bitcasa->file.'/')) {
							$all_filles = array();
							while (false !== ($file = readdir($dir))) {
								if ($file != "." && $file != "..") {
									 
									
			
								$request_url = 'https://developer.api.bitcasa.com/v1/files'.$newpa.'/?access_token='.$bitcasa_access_token.'';
								$post_params['name'] = urlencode('Test User');
								$post_params['file'] = '@'.$result_bitcasa->file.'/'.$file;
								$post_params['submit'] = urlencode('submit');
							
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $request_url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_POST, true);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
								$result = curl_exec($ch);
								$response_new = json_decode($result, true);
						 
								curl_close($ch); 
			 ///////////////////Files upload in  Folder end/////////////////////////		
									
								}
							}
							closedir($dir);
	
	
	
								
								
								
									}
							  
							  
							  }								
								
								
								}
								
								
								
					
					 
					
					
					
		
		}
						
					   ////////////////////For The Database Upload //////////////////////////
								
								
								$path    = WP_CONTENT_DIR.'/backups';
								$files = scandir($path);
								
								$request_url = 'https://developer.api.bitcasa.com/v1/files'.$bacup_path.'/?access_token='.$bitcasa_access_token.'';
								$post_params['name'] = urlencode('Test User');
								$post_params['file'] = '@'.$path."/".$files['2'];
 								
								$post_params['submit'] = urlencode('submit');
							
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $request_url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_POST, true);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
								$result = curl_exec($ch);
								$response_new = json_decode($result, true);
						 
								curl_close($ch); 
								 
								
								
								///////////////////End Database upload/////////////////////////

////////////////////////////////////////////////////////////////////////For The File ,Folder and database upload code and ////////////////////////////////////////////////////// 

               
  
   		  
    }
 
 	?>
<?php
     }




?>
<script type="text/javascript" language="javascript">
    function reload() {
	
	  
        jQuery('.files').hide();
        jQuery.post(ajaxurl, { action : 'progress' }, function(data) {
            if (data.length) {
                jQuery('#progress').html(data);
                jQuery('.view-files').on('click', function() {
                    $files = jQuery(this).next();

                    $files.toggle();
                    $files.find('li').each(function() {
                        $this = jQuery(this);
                        $this.css(
                            'background',
                            'url(<?php echo $uri ?>/JQueryFileTree/images/' + $this.text().slice(-3).replace(/^\.+/,'') + '.png) left top no-repeat'
                        );
                    });

                });
            }
        });
        <?php if ($config->get_option('in_progress') || isset($started)): ?>
            setTimeout("reload()", 15000);
        <?php endif; ?>
    }
    jQuery(document).ready(function ($) {
        reload();
    });
</script>

<div class="wrap" id="wpb2b">
   
  <h2>
    <?php _e('WordPress Backup to Bitcasa', 'wpbtd'); ?>
  </h2>
  <p class="description"><?php printf(__('Version %s', 'wpbtd'), BACKUP_TO_BITCASA_VERSION) ?></p>
  <?php settings_errors(); ?>
  <h3>
    <?php _e('Backup Monitor', 'wpbtd'); ?>
  </h3>
  <div id="">
 	 <?php if ($config->get_option('in_progress') || isset($started)): ?>
			
			   <ul>
				  
				   <?php 
				   foreach($query_databasetable_name as $all_tablename) {
				   ?>
				     <li>Database table  " <?php echo $all_tablename->Tables_in_bitcasa_jonchang; ?> " has been uploaded..</li>
				   
				   <?php } 
				   ?>
				 <?php 
				   foreach($query_all_file as $all_filename) {
				   		$ar_file_name=explode("/",$all_filename->file);
				   ?>
				     		<li><?php echo end($ar_file_name); ?>  .. Has Been Uploaded..</li>
				   <?php } 
				   ?>
	
    </ul>
  <?php endif; ?>
   
    
  </div>
  <form id="backup_to_bitcasa_options" name="backup_to_bitcasa_options" action="admin.php?page=backup-to-store-bitcasa" method="post">
    <?php if ($config->get_option('in_progress') || isset($started)): ?>
    <input type="submit" id="stop_backup" name="stop_backup" class="button-primary" value="<?php _e('Stop Backup', 'wpbtd'); ?>">
    <?php else: ?>
    <input type="submit" id="start_backup" name="start_backup" class="button-primary" value="<?php _e('Start Backup', 'wpbtd'); ?>">
    <?php endif; ?>
    <?php wp_nonce_field('backup_to_bitcasa_monitor_stop'); ?>
  </form>
</div>
