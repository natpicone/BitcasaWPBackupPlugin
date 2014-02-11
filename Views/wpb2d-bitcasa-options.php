 
	
	<?php
				$config = WPB2D_Factory::get('config');
				$backup = new WPB2D_BackupController();
				 
				global $wpdb;
				$table_name_bitcasa = $wpdb->prefix . "wpb2d_options";
				$sql = "SELECT * from $table_name_bitcasa where name='bitcasa_access_token'";
				$query = $wpdb->get_results($sql);
				$alldatabase_table="SHOW TABLES FROM $wpdb->dbname";
				$query_databasetable_name = $wpdb->get_results($alldatabase_table);
				$all_file_name = $wpdb->prefix . "wpb2d_excluded_files";
				$sql_all_filename = "SELECT * from $all_file_name ";
				$query_all_file = $wpdb->get_results($sql_all_filename);
				$bitcasa_access_token=$query[0]->value;
 if (!empty($bitcasa_access_token)) {
 		
		
		 $client = new BitcasaClient();
         $client->setAccessTokenFromRequest();
		 //$bid = $client->getInfiniteDrive();
		 //$item = $bid->add($client, "bitcasa_backup");
		  
	    //$bid = BitcasaInfiniteDrive::getInfiniteDrive($client);
		//$result = $bid->upload($client, "/home/demo/public_html/bitcasa_jonchang", "testfile.txt");
					
					//$fold_path='/4hAtyBw4TdaOCS8qpLJc1A/-hizHwvbQKSLpzjLcoANSA';
		 
			 
		  
	} else {
	
			echo "You Are Not Authrised.";
	
	}




      if (array_key_exists('stop_backup', $_POST)) {
    	
		check_admin_referer('backup_to_dropbox_monitor_stop');
		$backup->stop();
		add_settings_error('wpb2d_monitor', 'backup_stopped', __('Backup stopped.', 'wpbtd'), 'updated');

} elseif (array_key_exists('start_backup', $_POST)) {


	 
	
    check_admin_referer('backup_to_dropbox_monitor_stop');
    $backup->backup_now();
    $started = true;
    $date_new = date('m-d-Y', time());
    $bid = $client->getInfiniteDrive();
    $item = $bid->add($client, "bitcasa_backup");
    $newfolder =  $item->add($client, "App");
    $mainfolder =  $newfolder->add($client,$date_new);
    //$result = $item->upload($client, "/home/demo/public_html/bitcasa_jonchang/", "readme.html");
    $bacup_path = $mainfolder->getPath();
   
  if(!empty($bacup_path)) {
  
				global $wpdb;
				
				$table_name_bitcasa = $wpdb->prefix . "wpb2d_options";
				$wpdb->query("INSERT INTO $table_name_bitcasa (name, value) VALUES('".$date_new."','".$bacup_path."')");
				$sql_last_bitcasa = "SELECT * from $table_name_bitcasa where name='last_bitcasa_time'";
				$query_last_bitcasa = $wpdb->get_results($sql_last_bitcasa);
				if(empty($query_last_bitcasa)) {
				$wpdb->query("INSERT INTO $table_name_bitcasa (name, value) VALUES('last_bitcasa_time','".$date_new."')");
				} else {
				 
				
				$wpdb->query("UPDATE $table_name_bitcasa SET value = $date_new WHERE name = 'last_bitcasa_time'");
				}
  
  						$table_name_bitcasa_exe = $wpdb->prefix . "wpb2d_excluded_files";
						$sql_exe = "SELECT * from $table_name_bitcasa_exe";
						$query_exe = $wpdb->get_results($sql_exe);
					 
					 
					 
					 
					 
					 	   ////////////////////For The Database Upload //////////////////////////
								
								
								$path    = '/home/demo/public_html/bitcasa_jonchang/wp-content/backups';
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
					 
					 
					  
					 
					 
						foreach($query_exe as $result_bitcasa) {
					
								if($result_bitcasa->isdir=='0') {
								
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
								
								
								} else {
								
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
								//echo "<pre>";
								//print_r($response_new['result']['items']['0']['path']);
								$newpa=$response_new['result']['items']['0']['path'];
								//exit;
								//die();
							 
								curl_close($ch); 
												
						      if(!empty($response_new)) {
												
							 
								
								foreach(glob('.'.$result_bitcasa->file.'/*.*') as $filename_up){
  										 
								$request_url = 'https://developer.api.bitcasa.com/v1/files'.$newpa.'/?access_token='.$bitcasa_access_token.'';
								$post_params['name'] = urlencode('Test User');
								$post_params['file'] = '@'.$result_bitcasa->file."/".$filename_up;
 							    $post_params['submit'] = urlencode('submit');
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $request_url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_POST, true);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
								$result = curl_exec($ch);
								//$response_new = json_decode($result, true);
								curl_close($ch); 
										 
									
								}
												
								
								
												
												
							}								
								
								
								}
								
								
								
					
					 
					
					
					
		
		}
						
					 


               
  
   		  
    }
 
 	
    //add_settings_error('wpb2d_monitor', 'backup_started', __('Backup started.', 'wpbtd'), 'updated');
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

<div class="wrap" id="wpb2d">
  <div class="icon32"><img width="36px" height="36px"
                                 src="<?php echo $uri ?>/Images/WordPressBackupToDropbox_64.png"
                                 alt="WordPress Backup to Dropbox Logo"></div>
  <h2>
    <?php _e('WordPress Backup to Bitcasa', 'wpbtd'); ?>
  </h2>
  <p class="description"><?php printf(__('Version %s', 'wpbtd'), BACKUP_TO_DROPBOX_VERSION) ?></p>
  <?php settings_errors(); ?>
  <h3>
    <?php _e('Backup Monitor', 'wpbtd'); ?>
  </h3>
  <div id="progress">
 	 <?php if ($config->get_option('in_progress') || isset($started)): ?>
			
			   <ul>
				  
				   <?php 
				   foreach($query_databasetable_name as $all_tablename) {
				   
				   		//print_r($all_tablename->Tables_in_bitcasa_jonchang);
				   
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
   
    <div class="loading">
      <?php _e('Loading...') ?>
    </div>
  </div>
  <form id="backup_to_dropbox_options" name="backup_to_dropbox_options" action="admin.php?page=backup-to-dropbox-bitcasa" method="post">
    <?php if ($config->get_option('in_progress') || isset($started)): ?>
    <input type="submit" id="stop_backup" name="stop_backup" class="button-primary" value="<?php _e('Stop Backup', 'wpbtd'); ?>">
    <?php else: ?>
    <input type="submit" id="start_backup" name="start_backup" class="button-primary" value="<?php _e('Start Backup', 'wpbtd'); ?>">
    <?php endif; ?>
    <?php wp_nonce_field('backup_to_dropbox_monitor_stop'); ?>
  </form>
</div>
