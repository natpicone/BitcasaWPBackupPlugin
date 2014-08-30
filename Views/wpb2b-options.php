<?php
error_reporting(0);
try {
    if ($errors = get_option('wpb2b-init-errors')) {
        delete_option('wpb2b-init-errors');
        throw new Exception(__('WordPress Backup to Bitcasa failed to initialize due to these database errors.', 'wpbtd') . '<br /><br />' . $errors);
    }

    $validation_errors = null;

    $bitcasa = BACKUP_Factory::get('bitcasa');
    $config = BACKUP_Factory::get('config');

    $backup = new BACKUP_BackupController();

    $backup->create_dump_dir();

    $disable_backup_now = $config->get_option('in_progress');

    //We have a form submit so update the schedule and options
    if (array_key_exists('wpb2b_save_changes', $_POST)) {
	
	  
	
        check_admin_referer('backup_to_bitcasa_options_save');

        if (preg_match('/[^A-Za-z0-9-_.\/]/', $_POST['bitcasa_location'])) {
            add_settings_error('wpb2b_options', 'invalid_subfolder', __('The sub directory must only contain alphanumeric characters.', 'wpbtd'), 'error');

            $bitcasa_location = $_POST['bitcasa_location'];
            $store_in_subfolder = true;
        } else {
            $config
                ->set_schedule($_POST['day'], $_POST['time'], $_POST['frequency'])
                ->set_option('store_in_subfolder', $_POST['store_in_subfolder'] == "on")
                ->set_option('bitcasa_location', $_POST['bitcasa_location']);

            add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
        }
    } elseif (array_key_exists('unlink', $_POST)) {
 		 
	        global $wpdb;
			$table_name_bitcasa = $wpdb->prefix . "wpb2b_options";
			$sql = "DELETE FROM $table_name_bitcasa WHERE name='bitcasa_access_token'";
			$query = $wpdb->query($sql);
			 
		 
		 //check_admin_referer('backup_to_bitcasa_options_save');
         //$bitcasa->unlink_account()->init();
		
		
    } elseif (array_key_exists('clear_history', $_POST)) {
        
		 
		check_admin_referer('backup_to_bitcasa_options_save');
        $config->clear_history();
    
	
	
	
	}

    //Lets grab the schedule and the options to display to the user
    list($unixtime, $frequency) = $config->get_schedule();
    if (!$frequency) {
        $frequency = 'weekly';
    }

    if (!get_settings_errors('wpb2b_options')) {
        $bitcasa_location = $config->get_option('bitcasa_location');
        $store_in_subfolder = $config->get_option('store_in_subfolder');
    }

    $time = date('H:i', $unixtime);
    $day = date('D', $unixtime);
    ?>
<link rel="stylesheet" type="text/css" href="<?php echo $uri ?>/JQueryFileTree/jqueryFileTree.css"/>
<style>
.new_class_a {background: none repeat scroll 0 0 #2EA2CC;
    border-color: #0074A2;
    box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset, 0 1px 0 rgba(0, 0, 0, 0.15);
    color: #FFFFFF;
    height: auto;
    padding: 10px;
    text-align: center;
    text-decoration: none;
    width: 100px;
	}
</style>

<script src="<?php echo $uri ?>/JQueryFileTree/jqueryFileTree.js" type="text/javascript" language="javascript"></script>
<script src="<?php echo $uri ?>/wp-backup-to-bitcasa.js" type="text/javascript" language="javascript"></script>
<script type="text/javascript" language="javascript">
    jQuery(document).ready(function ($) {
        $('#frequency').change(function() {
            var len = $('#day option').size();
            if ($('#frequency').val() == 'daily') {
                $('#day').append($("<option></option>").attr("value", "").text('<?php _e('Daily', 'wpbtd'); ?>'));
                $('#day option:last').attr('selected', 'selected');
                $('#day').attr('disabled', 'disabled');
            } else if (len == 8) {
                $('#day').removeAttr('disabled');
                $('#day option:last').remove();
            }
        });

        //Display the file tree with a call back to update the clicked on check box and white list
        $('#file_tree').fileTree({
            root: '<?php echo str_replace("\\", "/", get_sanitized_home_path()) . "/"; ?>',
            script: ajaxurl,
            expandSpeed: 500,
            collapseSpeed: 500,
            multiFolder: false
        });

        $('#togglers .button').click(function() {
            switch ($(this).attr('rel')) {
            case "all":
                // clicking an unchecked, expanded directory triggers a collapse which is confusing
                // skip expanded directories when checking everything (they'll auto-check themselves)
                $('#file_tree .checkbox').not('.checked, .partial, .directory.expanded>.checkbox').click();
                break;
            case "none":
                $('#file_tree .checkbox.checked').click();
                break;
            case "invert":
                $('#file_tree .checkbox').not('.partial, .directory.expanded>.checkbox').click();
                break;
            }
        })

        $('#store_in_subfolder').click(function (e) {
            if ($('#store_in_subfolder').is(':checked')) {
                $('.bitcasa_location').show('fast', function() {
                    $('#bitcasa_location').focus();
                });
            } else {
                $('#bitcasa_location').val('');
                $('.bitcasa_location').hide();
            }
        });
    });

    /**
     * Display the bitcasa authorize url, hide the authorize button and then show the continue button.
     * @param url
     */
    function bitcasa_authorize(url) {
        window.open(url);
        document.getElementById('continue').style.visibility = 'visible';
        document.getElementById('authorize').style.visibility = 'hidden';
    }
</script>
    <div class="wrap" id="wpb2b">
     
<h2><?php _e('WordPress Backup to Bitcasa', 'wpbtd'); ?></h2>
<p class="description"><?php printf(__('Version %s', 'wpbtd'), BACKUP_TO_BITCASA_VERSION) ?></p>

    <?php settings_errors(); ?>
<?php
global $wpdb;
$table_name_bitcasa = $wpdb->prefix . "wpb2b_options";
$sql = "SELECT * from $table_name_bitcasa where name='bitcasa_access_token'";
$query = $wpdb->get_results($sql);

$bitcasa_access_token=$query[0]->value;

?>


    <?php if (!empty($bitcasa_access_token)) {
	
	
	
		$client = new BitcasaClient();
//$client->setAccessToken($_GET["access_token"]);
        $client->setAccessTokenFromRequest();

		//$bid = $client->getInfiniteDrive();
		//$item = $bid->add($client, "bitcasa_backup");
		 
    ?>
    <h3><?php _e('Bitcasa Account Details', 'wpbtd'); ?></h3>
    <form id="backup_to_bitcasa_options" name="backup_to_bitcasa_options"
          action="admin.php?page=backup-to-bitcasa" method="post">
		  
		  <?php 
		  $bitcasa_user_info='https://developer.api.bitcasa.com/v1/user/profile?access_token='.$bitcasa_access_token.'';
		  
		 $json = file_get_contents($bitcasa_user_info);
		 $obj = json_decode($json);
		 
		  
		  ?>
		  
    <p class="bump">
        <?php echo $obj->result->display_name; ?> 
		&nbsp; <?php echo $obj->result->storage->display; ?>
		    </p>
    <input type="submit" id="unlink" name="unlink" class="bump button-secondary" value="<?php _e('Unlink Account', 'wpbtd'); ?>">

    <h3><?php _e('Next Scheduled', 'wpbtd'); ?></h3>
        <?php
        $schedule = $config->get_schedule();
        if ($schedule) {
            ?>
            <p style="margin-left: 10px;"><?php printf(__('Next backup scheduled for %s at %s', 'wpbtd'), date('Y-m-d', $schedule[ 0 ]), date('H:i:s', $schedule[ 0 ])) ?></p>
            <?php } else { ?>
            <p style="margin-left: 10px;"><?php _e('No backups are scheduled yet. Please select a day, time and frequency below. ', 'wpbtd') ?></p>
            <?php } ?>
        <h3><?php _e('History', 'wpbtd'); ?></h3>
        <?php
        $backup_history = array_reverse($config->get_history());
        if ($backup_history) {
            echo '<ol class="history_box">';
            foreach ($backup_history as $backup_time) {

                if (is_array($backup_time))
                    continue;

                $blog_time = strtotime(date('Y-m-d H', strtotime(current_time('mysql'))) . ':00:00');
                $blog_time += $backup_time - strtotime(date('Y-m-d H') . ':00:00');

                $backup_date = date('l F j, Y', $blog_time);
                $backup_time_str = date('H:i:s', $blog_time);

                echo '<li>' . sprintf(__('Backup completed on %s at %s.', 'wpbtd'), $backup_date, $backup_time_str) . '</li>';
            }
            echo '</ol>';
            echo '<input type="submit" id="clear_history" name="clear_history"" class="bump button-secondary" value="' . __('Clear history', 'wpbtd') . '">';
        } else {
            echo '<p style="margin-left: 10px;">' . __('No history', 'wpbtd') . '</p>';
        }
        ?>
    <h3><?php _e('Settings', 'wpbtd'); ?></h3>
    <table class="form-table">
        <tbody>
         

        <tr valign="top">
            <th scope="row"><label for="time"><?php _e('Day and Time', 'wpbtd'); ?></label></th>
            <td>
                <select id="day" name="day" <?php echo ($frequency == 'daily') ? 'disabled="disabled"' : '' ?>>
                    <option value="Mon" <?php echo $day == 'Mon' ? ' selected="selected"'
                            : "" ?>><?php _e('Monday', 'wpbtd'); ?></option>
                    <option value="Tue" <?php echo $day == 'Tue' ? ' selected="selected"'
                            : "" ?>><?php _e('Tuesday', 'wpbtd'); ?></option>
                    <option value="Wed" <?php echo $day == 'Wed' ? ' selected="selected"'
                            : "" ?>><?php _e('Wednesday', 'wpbtd'); ?></option>
                    <option value="Thu" <?php echo $day == 'Thu' ? ' selected="selected"'
                            : "" ?>><?php _e('Thursday', 'wpbtd'); ?></option>
                    <option value="Fri" <?php echo $day == 'Fri' ? ' selected="selected"'
                            : "" ?>><?php _e('Friday', 'wpbtd'); ?></option>
                    <option value="Sat" <?php echo $day == 'Sat' ? ' selected="selected"'
                            : "" ?>><?php _e('Saturday', 'wpbtd'); ?></option>
                    <option value="Sun" <?php echo $day == 'Sun' ? ' selected="selected"'
                            : "" ?>><?php _e('Sunday', 'wpbtd'); ?></option>
                    <?php if ($frequency == 'daily') { ?>
                    <option value="" selected="selected"><?php _e('Daily', 'wpbtd'); ?></option>
                    <?php } ?>
                </select> <?php _e('at', 'wpbtd'); ?>
                <select id="time" name="time">
                    <option value="00:00" <?php echo $time == '00:00' ? ' selected="selected"' : "" ?>>00:00
                    </option>
                    <option value="01:00" <?php echo $time == '01:00' ? ' selected="selected"' : "" ?>>01:00
                    </option>
                    <option value="02:00" <?php echo $time == '02:00' ? ' selected="selected"' : "" ?>>02:00
                    </option>
                    <option value="03:00" <?php echo $time == '03:00' ? ' selected="selected"' : "" ?>>03:00
                    </option>
                    <option value="04:00" <?php echo $time == '04:00' ? ' selected="selected"' : "" ?>>04:00
                    </option>
                    <option value="05:00" <?php echo $time == '05:00' ? ' selected="selected"' : "" ?>>05:00
                    </option>
                    <option value="06:00" <?php echo $time == '06:00' ? ' selected="selected"' : "" ?>>06:00
                    </option>
                    <option value="07:00" <?php echo $time == '07:00' ? ' selected="selected"' : "" ?>>07:00
                    </option>
                    <option value="08:00" <?php echo $time == '08:00' ? ' selected="selected"' : "" ?>>08:00
                    </option>
                    <option value="09:00" <?php echo $time == '09:00' ? ' selected="selected"' : "" ?>>09:00
                    </option>
                    <option value="10:00" <?php echo $time == '10:00' ? ' selected="selected"' : "" ?>>10:00
                    </option>
                    <option value="11:00" <?php echo $time == '11:00' ? ' selected="selected"' : "" ?>>11:00
                    </option>
                    <option value="12:00" <?php echo $time == '12:00' ? ' selected="selected"' : "" ?>>12:00
                    </option>
                    <option value="13:00" <?php echo $time == '13:00' ? ' selected="selected"' : "" ?>>13:00
                    </option>
                    <option value="14:00" <?php echo $time == '14:00' ? ' selected="selected"' : "" ?>>14:00
                    </option>
                    <option value="15:00" <?php echo $time == '15:00' ? ' selected="selected"' : "" ?>>15:00
                    </option>
                    <option value="16:00" <?php echo $time == '16:00' ? ' selected="selected"' : "" ?>>16:00
                    </option>
                    <option value="17:00" <?php echo $time == '17:00' ? ' selected="selected"' : "" ?>>17:00
                    </option>
                    <option value="18:00" <?php echo $time == '18:00' ? ' selected="selected"' : "" ?>>18:00
                    </option>
                    <option value="19:00" <?php echo $time == '19:00' ? ' selected="selected"' : "" ?>>19:00
                    </option>
                    <option value="20:00" <?php echo $time == '20:00' ? ' selected="selected"' : "" ?>>20:00
                    </option>
                    <option value="21:00" <?php echo $time == '21:00' ? ' selected="selected"' : "" ?>>21:00
                    </option>
                    <option value="22:00" <?php echo $time == '22:00' ? ' selected="selected"' : "" ?>>22:00
                    </option>
                    <option value="23:00" <?php echo $time == '23:00' ? ' selected="selected"' : "" ?>>23:00
                    </option>
                </select>
                <span class="description"><?php _e('The day and time the backup to bitcasa is to be performed.', 'wpbtd'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="frequency"><?php _e('Frequency', 'wpbtd'); ?></label></th>
            <td>
                <select id="frequency" name="frequency">
                    <option value="daily" <?php echo $frequency == 'daily' ? ' selected="selected"' : "" ?>>
                        <?php _e('Daily', 'wpbtd') ?>
                    </option>
                    <option value="weekly" <?php echo $frequency == 'weekly' ? ' selected="selected"' : "" ?>>
                        <?php _e('Weekly', 'wpbtd') ?>
                    </option>
                    <option value="fortnightly" <?php echo $frequency == 'fortnightly' ? ' selected="selected"'
                            : "" ?>>
                        <?php _e('Fortnightly', 'wpbtd') ?>
                    </option>
                    <option value="monthly" <?php echo $frequency == 'monthly' ? ' selected="selected"' : "" ?>>
                        <?php _e('Every 4 weeks', 'wpbtd') ?>
                    </option>
                    <option value="two_monthly" <?php echo $frequency == 'two_monthly' ? ' selected="selected"'
                            : "" ?>>
                        <?php _e('Every 8 weeks', 'wpbtd') ?>
                    </option>
                    <option value="three_monthly" <?php echo $frequency == 'three_monthly' ? ' selected="selected"'
                            : "" ?>>
                        <?php _e('Every 12 weeks', 'wpbtd') ?>
                    </option>
                </select>
                <span class="description"><?php _e('How often the backup to Bitcasa is to be performed.', 'wpbtd'); ?></span>
            </td>
        </tr>
        </tbody>
    </table>
    <!--[if !IE | gt IE 7]><!-->
    <h3><?php _e('Excluded Files and Directories', 'wpbtd'); ?></h3>
    <p style='margin-left: 10px;'>
        <span class="description">
            <?php _e('Select the files and directories that you wish to exclude from your backup. You can expand directories with contents by clicking its name.', 'wpbtd') ?><br />
            <strong><?php _e('Please Note:', 'wpbtd'); ?></strong>&nbsp;<?php _e('Your SQL dump file will always be backed up regardless of what is selected below.', 'wpbtd'); ?>
        </span>
    </p>
    <div id="file_tree">


					<div id="circleG" class="start">
						<div id="circleG_1" class="circleG"></div>
						<div id="circleG_2" class="circleG"></div>
						<div id="circleG_3" class="circleG"></div>
					</div>
					



		<div class="loading start"><?php _e('Loading...') ?></div>
		
		
		
    </div>
    <div id="togglers"><?php _e("Exclude:", 'wpbtd'); ?>
        <span class="button" rel="all" href="#"><?php _e("All", 'wpbtd'); ?></span>
        <span class="button" rel="none" href="#"><?php _e("None", 'wpbtd'); ?></span>
        <span class="button" rel="invert" href="#"><?php _e("Inverse", 'wpbtd'); ?></span>
    </div>
    <!--<![endif]-->
    <p class="submit">
        <input type="submit" id="wpb2b_save_changes" name="wpb2b_save_changes" class="button-primary" value="<?php _e('Save Changes', 'wpbtd'); ?>">
    </p>
        <?php wp_nonce_field('backup_to_bitcasa_options_save'); ?>
    </form>
        <?php

    } else {

        ?>
    <h3><?php _e('Thank you for installing WordPress Backup to Bitcasa!', 'wpbtd'); ?></h3>
    <p><?php _e('In order to use this plugin you will need to authorized it with your Bitcasa account.', 'wpbtd'); ?></p>
    <p><?php _e('Please click the authorize button below and follow the instructions inside the pop up window.', 'wpbtd'); ?></p>
        <?php if (array_key_exists('continue', $_POST) && !$bitcasa->is_authorized()): ?>
            <?php $bitcasa->unlink_account()->init(); ?>
            <p style="color: red"><?php _e('There was an error authorizing the plugin with your Bitcasa account. Please try again.', 'wpbtd'); ?></p>
        <?php endif; ?>
   
  
	 
	 <div class="new_class_a">
<a style="color:#FFFFFF;text-decoration:none;" href="https://developer.api.bitcasa.com/v1/oauth2/authenticate?client_id=<?php echo OAUTH_CLIENTID; ?>&redirect=http://<?php echo $_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF']; ?>?page=backup-to-bitcasa-monitor/" target="_blank" > Authorize</a>
	 </div>
 
        <?php

    }
} catch (Exception $e) {
    echo '<h3>Error</h3>';
    echo '<p>' . __('There was a fatal error loading WordPress Backup to Bitcasa. Please fix the problems listed and reload the page.', 'wpbtd') . '</h3>';
    echo '<p>' . __('If the problem persists please re-install WordPress Backup to Bitcasa.', 'wpbtd') . '</h3>';
    echo '<p><strong>' . __('Error message:') . '</strong> ' . $e->getMessage() . '</p>';

    if ($bitcasa)
        $bitcasa->unlink_account();
}
?>
</div>

 
 



