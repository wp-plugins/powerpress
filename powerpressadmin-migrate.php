<?php

function powerpress_admin_migrate_get_files($clean=false, $exclude_blubrry=true)
{
		global $wpdb;
		
		// powerpress_page_message_add_error( __('Unable to detect PodPress media URL setting. Please set the "Default Media URL" setting in PowerPress to properly import podcast episodes.', 'powerpress') );
		
		$return = array();
		//$return['feeds_required'] = 0;
		$query = "SELECT p.ID, p.post_title, p.post_date, pm.meta_id, pm.post_id, pm.meta_key, pm.meta_value ";
		$query .= "FROM {$wpdb->posts} AS p ";
		$query .= "INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id ";
		$query .= "WHERE (pm.meta_key = 'enclosure' OR pm.meta_key LIKE '\_%:enclosure') ";
		$query .= "AND p.post_type != 'revision' ";
		$query .= "GROUP BY pm.meta_id ";
		$query .= "ORDER BY p.post_date DESC ";
		
		$results_data = $wpdb->get_results($query, ARRAY_A);
		if( $results_data )
		{
			while( list($null,$row) = each($results_data) )
			{
				$meta_id = $row['meta_id'];
				$EpisodeData = powerpress_get_enclosure_data($row['post_id'], 'podcast', $row['meta_value']);
				
				if( $exclude_blubrry && strstr($EpisodeData['url'], 'content.blubrry.com') )
					continue; // Skip media hosted on blubrry in this case
					
				if( !$clean )
					$return[$meta_id] = $row;
				if( !$exclude_blubrry )
					$return[$meta_id]['on_blubrry'] = ( strstr($EpisodeData['url'], 'content.blubrry.com') );
				$return[$meta_id]['src_url'] = $EpisodeData['url'];
			}
		}
		return $return;
}


function powepress_admin_migrate_add_urls($urls)
{
	$Settings = get_option('powerpress_general');
	if( empty($Settings['blubrry_auth']) )
	{
		powerpress_page_message_add_error( sprintf(__('You must have a blubrry Podcast Hosting account to continue.', 'powerpress')) .' '. '<a href="http://create.blubrry.com/resources/podcast-media-hosting/" target="_blank">'. __('Learn More', 'powerpress') .'</a>' );
		return false;
	}
	
	$PostArgs = array('urls'=>$urls);
	
	$json_data = false;
	$api_url_array = powerpress_get_api_array();
	while( list($index,$api_url) = each($api_url_array) )
	{
		$req_url = sprintf('%s/media/%s/migrate_add.json', rtrim($api_url, '/'), urlencode($Settings['blubrry_program_keyword']) );
		$req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'&'. POWERPRESS_BLUBRRY_API_QSA:'');
		$json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth'], $PostArgs );
		if( $json_data != false )
			break;
	}
	
	if( !$json_data )
	{
		if( !empty($GLOBALS['g_powerpress_remote_errorno']) && $GLOBALS['g_powerpress_remote_errorno'] == 401 )
			$error .=  __('Incorrect sign-in email address or password.', 'powerpress') .' '. __('Verify your account settings then try again.', 'powerpress');
		else if( !empty($GLOBALS['g_powerpress_remote_error']) )
			$error .= '<p>'. $GLOBALS['g_powerpress_remote_error'];
		else
			$error .= __('Authentication failed.', 'powerpress');
		powerpress_page_message_add_error($error);
		return false;
	}
	
	$results = powerpress_json_decode($json_data);
	if( !empty($results['error']) )
	{
		$error = __('Blubrry Migrate Media Error', 'powerpress') .': '. $results['error'];
		powerpress_page_message_add_error($error);
		return false;
	}
	
	return $results;
}


function powerpress_admin_migrate_get_status()
{
	$Settings = get_option('powerpress_general');
	if( empty($Settings['blubrry_auth']) )
	{
		powerpress_page_message_add_error( sprintf(__('You must have a blubrry Podcast Hosting account to continue.', 'powerpress')) );
		return false;
	}
	
	
	$json_data = false;
	$api_url_array = powerpress_get_api_array();
	while( list($index,$api_url) = each($api_url_array) )
	{
		$req_url = sprintf('%s/media/%s/migrate_status.json?status=summary', rtrim($api_url, '/'), $Settings['blubrry_program_keyword'] );
		$req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'&'. POWERPRESS_BLUBRRY_API_QSA:'');
		$json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth']);
		if( $json_data != false )
			break;
	}
	
	if( !$json_data )
	{
		if( !empty($GLOBALS['g_powerpress_remote_errorno']) && $GLOBALS['g_powerpress_remote_errorno'] == 401 )
			$error .=  __('Incorrect sign-in email address or password.', 'powerpress') .' '. __('Verify your account settings then try again.', 'powerpress');
		else if( !empty($GLOBALS['g_powerpress_remote_error']) )
			$error .= '<p>'. $GLOBALS['g_powerpress_remote_error'];
		else
			$error .= __('Authentication failed.', 'powerpress');
		powerpress_page_message_add_error($error);
		return false;
	}
	//mail('cio', 'ok', $json_data);
	$results = powerpress_json_decode($json_data);
	if( !empty($results['error']) )
	{
		$error = __('Blubrry Migrate Media Error', 'powerpress') .': '. $results['error'];
		powerpress_page_message_add_error($error);
		return false;
	}
	
	return $results;
}


function powerpress_admin_migrate_get_migrated_by_status($status='migrated')
{
	$Settings = get_option('powerpress_general');
	if( empty($Settings['blubrry_auth']) )
	{
		powerpress_page_message_add_error( sprintf(__('You must have a blubrry Podcast Hosting account to continue.', 'powerpress')) );
		return false;
	}
	
	
	$json_data = false;
	$api_url_array = powerpress_get_api_array();
	while( list($index,$api_url) = each($api_url_array) )
	{
		$req_url = sprintf('%s/media/%s/migrate_status.json?status=%s&limit=10000', rtrim($api_url, '/'), $Settings['blubrry_program_keyword'], urlencode($status) );
		$req_url .= (defined('POWERPRESS_BLUBRRY_API_QSA')?'&'. POWERPRESS_BLUBRRY_API_QSA:'');
		$json_data = powerpress_remote_fopen($req_url, $Settings['blubrry_auth']);
		if( $json_data != false )
			break;
	}
	
	if( !$json_data )
	{
		if( !empty($GLOBALS['g_powerpress_remote_errorno']) && $GLOBALS['g_powerpress_remote_errorno'] == 401 )
			$error .=  __('Incorrect sign-in email address or password.', 'powerpress') .' '. __('Verify your account settings then try again.', 'powerpress');
		else if( !empty($GLOBALS['g_powerpress_remote_error']) )
			$error .= '<p>'. $GLOBALS['g_powerpress_remote_error'];
		else
			$error .= __('Authentication failed.', 'powerpress');
		powerpress_page_message_add_error($error);
		return false;
	}
	
	$results = powerpress_json_decode($json_data);
	if( !empty($results['error']) )
	{
		$error = __('Blubrry Migrate Media Error', 'powerpress') .': '. $results['error'];
		powerpress_page_message_add_error($error);
		return false;
	}
	
	return $results;
}


// Handle POST/GET page requests here
function powerpress_admin_migrate_request()
{
	if( !empty($_GET['migrate_step']) )
	{
		switch( $_GET['migrate_step'] )
		{
			case 1: {
				$GLOBALS['powerpress_migrate_stats'] = powerpress_admin_extension_counts();
			}; break;
			
		}
	}
	
	if( !empty($_POST['migrate_action']) )
	{
		check_admin_referer('powerpress-migrate-media');
		
		switch($_POST['migrate_action'])
		{
			case 'queue_episodes': {
				
				if( !empty($_POST['Migrate']) )
				{
					powerpress_admin_queue_files($_POST['Migrate']);
						
					// Else error message handled in functoin called above
				}
			}; break;
		
		}
	}
}

function powerpress_admin_extension_counts()
{ // powerpress_admin_migrate_get_files
	$files = powerpress_admin_migrate_get_files(true, false);
	$extensions = array(); // 'blubrry'=>0, 'mp3'=>0, 'm4a'=>0, 'mp4'=>0, 'm4v'=>0, '*'=>0 );
	while( list($meta_id,$row) = each($files) )
	{
		$extension = '*';
			
		$parts = pathinfo($row['src_url']);
		if( preg_match('/(mp3|m4a|mp4|m4v)/i', $parts['extension']) )
			$extension = strtolower($parts['extension']);
			
		if( !empty($row['on_blubrry']) )
			$extension = 'blubrry';
			
		if( empty($extensions[ $extension ]) )
			$extensions[ $extension ] = 0;
		
		$extensions[ $extension ]++;
	}
	return $extensions;
}

function powerpress_admin_queue_files($extensions=array() )
{
	$add_urls = '';
	$extensions_preg_match = '';
	while( list($extension,$null) = each($extensions) )
	{
		if( $extension == '*' )
		{
			$extensions_preg_match = '.*';
			break; // Lets just match everything
		}
		if( !empty($extensions_preg_match) )
			$extensions_preg_match .= '|';
		$extensions_preg_match .= $extension;
	}
	
	if( empty($extensions_preg_match) )
	{
		// No files specified, no error message needed
		return;
	}
	
	$files = powerpress_admin_migrate_get_files(true, true); // Keep the URLs clean, excude blubrry media URLs
	$QueuedFiles = array();
	$Update = false;
	$PastResults = get_option('powerpress_migrate_queued');
	if( is_array($PastResults) )
		$QueuedFiles = $PastResults;
	$AddedCount = 0;;
	$AlreadyAddedCount = 0;
	
	while( list($meta_id,$row) = each($files) )
	{
		$parts = pathinfo($row['src_url']);
		if( preg_match('/('.$extensions_preg_match.')/i', $parts['extension']) )
		{
			if( !empty($QueuedFiles[ $meta_id ]) && $QueuedFiles[ $meta_id ] == $row['src_url'] )
			{
				$AlreadyAddedCount++;
				continue; // Already queued
			}
			
			$QueuedFiles[ $meta_id ] = $row['src_url'];
			if( !empty($add_urls ) )
				$add_urls .= "\n";
			$add_urls .= $row['src_url'];
			$Update = true;
			$AddedCount++;
		}
	}
	
	if( $Update )
	{
		// Make API CALL to add files to queue here!
		$UpdateResults = powepress_admin_migrate_add_urls( $add_urls );
		if( empty($UpdateResults) )
			$Update = false;
	}
	
	if( $Update )
	{
		// IF the API call was successful, lets save the list locally
		update_option('powerpress_migrate_queued', $QueuedFiles);
		powerpress_page_message_add_notice( sprintf(__('%d media files added to migration queue.', 'powerpress'), $AddedCount) );
	}
	
	if( $AlreadyAddedCount  > 0 )
	{
		powerpress_page_message_add_notice( sprintf(__('%d media files were already added to migration queue.', 'powerpress'), $AlreadyAddedCount) );
	}
}


function powerpress_admin_migrate_step1()
{
	// Use check_admin_referer('powerpress-migrate-media');  when handling this post request
?>
<form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media'); ?>">
<?php wp_nonce_field('powerpress-migrate-media'); ?>
<input type="hidden" name="action" value="powerpress-migrate-media" />
<input type="hidden" name="migrate_action" value="queue_episodes" />
<h2><?php echo __('Migrate to Blubrry - Select media to migrate', 'powerpress'); ?></h2>

<ul>
<?php 
	if( count($GLOBALS['powerpress_migrate_stats']) == 0 )
	{
	?>
	<li>
	<?php echo __('No media found to migrate', 'powerpress'); ?>
	</li>
	<?php
	}
	$types = array('mp3', 'm4a', 'mp4', 'm4v', '*', 'blubrry');
	while (list($null, $extension) = each($types) )
	{
		if( empty($GLOBALS['powerpress_migrate_stats'][$extension]) )
			continue;
		$count = $GLOBALS['powerpress_migrate_stats'][$extension];
		$checked = ' checked';
		switch( $extension )
		{
			case 'mp3': $label = __('mp3 audio files', 'powerpress'); break;
			case 'm4a': $label = __('m4a audio files', 'powerpress'); break;
			case 'mp4': $label = __('mp4 video files', 'powerpress'); break;
			case 'm4v': $label = __('m4v video files', 'powerpress'); break;
			case 'blubrry': $label = __('media hosted by Blubrry', 'powerpress'); break;
			default: $label = __('Other media formats', 'powerpress'); $checked = '';
		}
		
	?>
	<li>
	<?php if( $extension == 'blubrry' ) { ?>
	<input type="checkbox" name="NULL[<?php echo $extension; ?>]" value="0" disabled /> <?php echo $label; ?> <?php ?>
	<?php } else { ?>
	<input type="checkbox" name="Migrate[<?php echo $extension; ?>]" value="1" <?php echo $checked; ?> /> <?php echo $label; ?> 
	<?php } ?>
	<?php echo sprintf( __('(%d found)', 'powerpress'), $count); ?>
	</li>
<?php } ?>
</ul>
<?php
	if( count($GLOBALS['powerpress_migrate_stats']) )
		?><p class="submit"><input type="submit" class="button-primary" name="Submit" value="<?php echo __('Request Migration', 'powerpress'); ?>" /></p><?php
?>

</form>
<?php
	//print_r($GLOBALS['powerpress_migrate_stats']);
}

function powerpress_admin_migrate_step3($MigrateStatus)
{
?>
<input type="hidden" name="action" value="powerpress-migrate-step3" />
<h2><?php echo __('Migrate to Blubrry - Update your epsidoes', 'powerpress'); ?></h2>



<p class="submit">
	<input type="submit" name="Submit" id="powerpress_save_button" class="button-primary" value="<?php echo __('Update Episodes', 'powerpress'); ?>" onclick="" />
	&nbsp;
	<input type="checkbox" name="PowerPressVerifyURLs" value="1" checked />
	<strong><?php echo __('Verify modified URLs', 'powerpress'); ?></strong>
		(<?php echo __('Does not change media URL if link is not found or invalid', 'powerpress'); ?>)</p>
</p>

<p style="margin-bottom: 40px; margin-top:0;"><?php echo sprintf( __('We recommend using the %s plugin to backup your database before using this Find and Replace tool.', 'powerpress'), '<a href="http://wordpress.org/extend/plugins/wp-db-backup/" target="_blank">'. __('WP-DB-Backup', 'powerpress') .'</a>' ); ?></p>


<?php
}


function powerpress_admin_migrate()
{
	$files = powerpress_admin_migrate_get_files();
	
	if( !empty($_REQUEST['migrate_step']) && $_REQUEST['migrate_step'] == 1 )
	{
		powerpress_admin_migrate_step1();
		return;
	}
	
	$Step = 0;
	$QueuedCount = 0;
	$MigratedCount = 0;
	$FailedCount = 0;
	$SkippedCount = 0;
	$QueuedResults = get_option('powerpress_migrate_queued');
	if( is_array($QueuedResults) )
	{
		$QueuedCount = count($QueuedResults);
		$Step = 1;
	}
	
	$MigrateStatus = false;
	if( $Step >= 1 )
	{
		$MigrateStatus = get_option('powerpress_migrate_status');
		if( empty($MigrateStatus) || $MigrateStatus['updated_timestamp'] < time()-(60*30) || !empty($_GET['refresh_migrate_status']) ) // Check every 30 minutes
		{
			$NewMigrateStatus = powerpress_admin_migrate_get_status();
			if( is_array($NewMigrateStatus) )
			{
				$NewMigrateStatus['updated_timestamp'] = current_time( 'timestamp' );
				update_option('powerpress_migrate_status', $NewMigrateStatus);
				$MigrateStatus = $NewMigrateStatus;
			}
		}
	}
	
	if( !empty($MigrateStatus['completed']) )
	{
		$Step = 3;
		$MigratedCount = $MigrateStatus['completed'];
	}
	if( !empty($MigrateStatus['failed']) )
	{
		$FailedCount = $MigrateStatus['failed'];
	}
	if( !empty($MigrateStatus['skipped']) )
	{
		$SkippedCount = $MigrateStatus['skipped'];
	}
	
	if( !empty($_REQUEST['migrate_step']) && $_REQUEST['migrate_step'] == 3 && $Step == 3 )
	{
		powerpress_admin_migrate_step3($MigrateStatus);
		return;
	}
	
?>

<h2><?php echo __('Migrate Media to your Blubrry Podcast Media Hosting Account', 'powerpress'); ?></h2>

<p><?php echo __('Migrate all of your media to Blubrry with only a few clicks.', 'powerpress'); ?></p>
<p><a href="http://create.blubrry.com/resources/podcast-media-hosting/" target="_blank"><?php echo __('Don\'t have a blubrry podcast hosting account?', 'powerpress'); ?></a></p>

<?php powerpress_page_message_print(); ?>

<div id="powerpress_steps">
	<div class="powerpress-step active-step" id="powerpreess_step_1a">
	<h3><?php echo __('Step 1', 'powerpress'); ?></h3>
	<p>
	<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=1"); ?>"><?php echo __('Select Media to Migrate', 'powerpress'); ?></a>
	</p>
	<br />
	<p class="normal"><?php echo sprintf( __('%d files queued', 'powerpress'), $QueuedCount); ?></p>
	<p  class="normal"><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=1"); ?>">Add media files</a></p>
	<?php  ?>
	</div>
	<div class="powerpress-step<?php echo ($Step >= 1? ' active-step':''); ?>">
	<h3><?php echo __('Step 2', 'powerpress'); ?></h3>
	<p>
	<?php echo __('Wait for media to migrate', 'powerpress'); ?>
	</p>
	<br />
	<?php if( $Step >= 1 ) { ?>
	<p class="normal"><?php echo sprintf( __('%d files migrated', 'powerpress'), $MigratedCount); ?></p><?php } ?>
	<?php if( $FailedCount ) { ?><p class="normal"><?php echo sprintf( __('%d files failed', 'powerpress'), $FailedCount); ?></p><?php } ?>
	<?php if( $SkippedCount ) { ?><p class="normal"><?php echo sprintf( __('%d files skipped', 'powerpress'), $SkippedCount); ?></p><?php } ?>
	
	<p  class="normal"><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;refresh_migrate_status=1"); ?>">Refresh Migration Results</a></p>
	<?php  ?>
	</div>
	<div class="powerpress-step<?php echo ($Step >= 2? ' active-step':''); ?>">
	<h3><?php echo __('Step 3', 'powerpress'); ?></h3>
	<p>
	<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=3"); ?>"><?php echo __('Update your Episodes', 'powerpress'); ?></a>
	</p>
	<br />
<!--	<p class="normal">0 episodes updated</p> -->
	<p class="normal"><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=3"); ?>">Update Episodes Now</a></p>
	<?php  ?>
	</div>
	<div class="clear"></div>
</div>
<div class="clear"></div>
<br /><br />
<?php

	if( !empty($MigrateStatus['updated_timestamp'])  )
	{
?>
	<p>
	<strong><?php echo __('Migration status last updated', 'powerpress'); ?></strong><br />
	<?php echo date_i18n( get_option( 'date_format' ) .' - '.get_option( 'time_format' ), $MigrateStatus['updated_timestamp'], false ); ?>
	</p>
<?php
	} 
	
	if($SkippedCount > 0 || $FailedCount > 0)
	{
		// TODO: Print a message letting folks know they can go to blubrry.com to see the migration status of the failed URLs.
?>
<p><a href="http://publish.blubrry.com/content/media_migrate2.php" target="_blank"><?php echo __('Please sign into blubrry.com to review media that failed to migrate.', 'powerpress'); ?></a></p>
<?php
	}
?>

<?php if( $Step > 0 ) { ?>
<p>
 <?php echo __('Migration can take a while, please be patient. Please contact support if you do not see results within 48 hours.', 'powepress'); ?>
</p>
<br /><br />
<?php } ?>
<?php
	while( list($index,$row) = each($files) )
	{
		//if( $row['on_blubrry'] )
		//	echo "[X] ". basename($row['src_url']) . '<br />';
		//else
		//	echo "[ ] ". basename($row['src_url']) . '<br />';
		//print_r($row);
		//echo '<br /><br />';
	}
}

