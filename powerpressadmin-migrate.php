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
			$Update = true;
			$AddedCount++;
		}
	}
	
	if( $Update )
	{
		// Make API CALL to add files to queue here!
		
		
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

function powerpress_admin_migrate_step2()
{
?>
<input type="hidden" name="action" value="powerpress-migrate-step2" />
<h2><?php echo __('Migrate to Blubrry - View migration queue', 'powerpress'); ?></h2>

<?php
}

function powerpress_admin_migrate_step3()
{
?>
<input type="hidden" name="action" value="powerpress-migrate-step3" />
<h2><?php echo __('Migrate to Blubrry - Update your epsidoes', 'powerpress'); ?></h2>


<?php
}


function powerpress_admin_migrate()
{
	$files = powerpress_admin_migrate_get_files();
	
	if( !empty($_REQUEST['migrate_step']) )
	{
		switch( $_REQUEST['migrate_step'] )
		{
			case 1: powerpress_admin_migrate_step1(); break;
			case 2: powerpress_admin_migrate_step2(); break;
			case 3: powerpress_admin_migrate_step3(); break;
		}
		return;
	}
	
	$Step = 0;
	$QueuedCount = 0;
	$MigratedCount = 0;
	$FailedCount = 0;
	$QueuedResults = get_option('powerpress_migrate_queued');
	if( is_array($QueuedResults) )
	{
		$QueuedCount = count($QueuedResults);
		$Step = 1;
		
		
	}
	
?>

<h2><?php echo __('Migrate Media to your Blubrry Podcast Media Hosting Account', 'powerpress'); ?></h2>

<p><?php echo __('Migrate all of your media to Blubrry with only a few clicks.', 'powerpress'); ?></p>

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
	<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=2"); ?>"><?php echo __('Wait for media to migrate', 'powerpress'); ?></a>
	</p>
	<br />
	<?php if( $Step >= 1 ) { ?>
	<p class="normal"><?php echo sprintf( __('%d files migrated', 'powerpress'), $MigratedCount); ?></p><?php } ?>
	<?php if( $FailedCount ) { ?><p class="normal"><?php echo sprintf( __('%d files failed', 'powerpress'), $FailedCount); ?></p><?php } ?>
	<p  class="normal"><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=2"); ?>">Refresh Migration Results</a></p>
	<?php  ?>
	</div>
	<div class="powerpress-step<?php echo ($Step >= 2? ' active-step':''); ?>">
	<h3><?php echo __('Step 3', 'powerpress'); ?></h3>
	<p>
	<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=3"); ?>"><?php echo __('Update your Episodes', 'powerpress'); ?></a>
	</p>
	<br />
	<p class="normal">0 episodes updated</p>
	<p class="normal"><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_migrate.php&amp;action=powerpress-migrate-media&amp;migrate_step=3"); ?>">Update Episodes Now</a></p>
	<?php  ?>
	</div>
	<div class="clear"></div>
</div>
<br /><br />
<p>
 Migration can take a while, depending on the time of the day as well as the performnace of the service where the media is currently hosted. Please be patient.
</p>
<br /><br />
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

