<?php

function powerpress_page_message_add_error($msg)
{
	global $g_powerpress_page_message;
	$g_powerpress_page_message .= '<div class="error powerpress-error">'. $msg . '</div>';
}

function powerpress_page_message_add_notice($msg)
{
	global $g_powerpress_page_message;
	$g_powerpress_page_message .= '<div class="updated fade powerpress-notice">'. $msg . '</div>';
}


function powerpress_page_message_print()
{
	global $g_powerpress_page_message;
	if( $g_powerpress_page_message )
		echo $g_powerpress_page_message;
	$g_powerpress_page_message = '';
}

function powerpress_admin_init()
{
	global $wp_rewrite;
	
	if( !current_user_can('manage_options') )
	{
		powerpress_page_message_add_error( __('You do not have sufficient permission to manage options.') );
		return;
	}
	
	if( isset($_POST['CheckSWF']) ) // Leave until we no longer support Wordpress 2.6.x
	{
		$md5 = md5_file( dirname(__FILE__).'/FlowPlayerClassic.swf' );
		if( $md5 == '051ed574774436e228e5dafd97d0f5f0' )
			powerpress_page_message_add_notice( __('Flash player verified successfully.') );
		else
			powerpress_page_message_add_error( __('FlowPlayerClassic.swf is corrupt, please re-upload.') );
	}

	// Check for other podcasting plugin
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) )
		powerpress_page_message_add_error( __('Another podcasting plugin has been detected, PowerPress is currently disabled.') );
	
	global $wp_version;
	$VersionDiff = version_compare($wp_version, 2.5);
	if( $VersionDiff < 0 )
		powerpress_page_message_add_error( __('Blubrry PowerPress requires Wordpress version 2.5 or greater.') );
	
	// Save settings here
	if( isset($_POST[ 'Feed' ]) || isset($_POST[ 'General' ])  )
	{
		check_admin_referer('powerpress-edit');
		$UploadArray = wp_upload_dir();
		$upload_path =  rtrim( substr($UploadArray['path'], 0, 0 - strlen($UploadArray['subdir']) ), '\\/').'/powerpress/';
		$urlImages = rtrim( substr($UploadArray['url'], 0, 0 - strlen($UploadArray['subdir']) ), '/').'/powerpress/';
	
		// Save the posted value in the database
		$Feed = $_POST['Feed'];
    $General = $_POST['General'];
		$FeedSlug = (isset($_POST['feed_slug'])?$_POST['feed_slug']:false);
		
		// New iTunes image
		if( @$_POST['itunes_image_checkbox'] == 1 )
		{
			$filename = str_replace(" ", "_", basename($_FILES['itunes_image_file']['name']) );
			$temp = $_FILES['itunes_image_file']['tmp_name'];
			
			if( file_exists($upload_path . $filename ) )
			{
				$filenameParts = pathinfo($filename);
				do {
					$filename_no_ext = substr($filenameParts['basename'], 0, (strlen($filenameParts['extension'])+1) * -1 );
					$filename = sprintf('%s-%03d.%s', $filename_no_ext, rand(0, 999), $filenameParts['extension'] );
				} while( file_exists($upload_path . $filename ) );
			}
			
			// Check the image...
			$ImageData = getimagesize($temp);
			if( $ImageData && ( $ImageData[2] == IMAGETYPE_JPEG || $ImageData[2] == IMAGETYPE_PNG ) && $ImageData[0] == $ImageData[1] ) // Just check that it is an image, the correct image type and that the image is square
			{
				move_uploaded_file($temp, $upload_path . $filename);
				$Feed['itunes_image'] = $urlImages . $filename;
			}
			else
			{
				powerpress_page_message_add_error( 'Invalid iTunes image ' . htmlspecialchars($_FILES['itunes_image_file']['name']) );
			}
		}
		
		// New RSS2 image
		if( @$_POST['rss2_image_checkbox'] == 1 )
		{
			$filename = str_replace(" ", "_", basename($_FILES['rss2_image_file']['name']) );
			$temp = $_FILES['rss2_image_file']['tmp_name'];
			
			if( file_exists($upload_path . $filename ) )
			{
				$filenameParts = pathinfo($filename);
				do {
					$filename_no_ext = substr($filenameParts['basename'], 0, (strlen($filenameParts['extension'])+1) * -1 );
					$filename = sprintf('%s-%03d.%s', $filename_no_ext, rand(0, 999), $filenameParts['extension'] );
				} while( file_exists($upload_path . $filename ) );
			}
			
			if( getimagesize($temp) )  // Just check that it is an image, we may add more to this later
			{
				move_uploaded_file($temp, $upload_path . $filename);
				$Feed['rss2_image'] = $urlImages . $filename;
			}
			else
			{
				powerpress_page_message_add_error( 'Invalid RSS image: ' . htmlspecialchars($_FILES['rss2_image_file']['name']) );
			}
		}
		
		// Check to see if we need to update the feed title
		if( $FeedSlug )
		{
			$GeneralSettingsTemp = powerpress_get_settings('powerpress_general', false);
			if( $GeneralSettingsTemp['custom_feeds'][$FeedSlug] != $Feed['title'] )
			{
				if( !$General )
					$General = array();
				$General['custom_feeds'] = $GeneralSettingsTemp['custom_feeds'];
				$General['custom_feeds'][$FeedSlug] = $Feed['title'];
			}
		}
		
		// Update the settings in the database:
		if( $General )
		{
			if( $_POST['action'] == 'powerpress-save-appearance' )
			{
				if( !isset($General['display_player_excerpt']) ) // If we are modifying appearance settings but this option was not checked...
					$General['display_player_excerpt'] = 0; // Set it to zero.
			}
			
			// Wordpress adds slashes to everything, but since we're storing everything serialized, lets remove them...
			$General = powerpress_stripslashes($General);
			powerpress_save_settings($General);
		}
		
		if( $Feed )
		{
			if( !isset($Feed['enhance_itunes_summary']) )
				$Feed['enhance_itunes_summary'] = false;
			$Feed = powerpress_stripslashes($Feed);
			powerpress_save_settings($Feed, 'powerpress_feed'.($FeedSlug?'_'.$FeedSlug:'') );
		}
		
		// Anytime settings are saved lets flush the rewrite rules
		$wp_rewrite->flush_rules();
		
		// Settings saved successfully
		switch( $_POST['action'] )
		{
			case 'powerpress-save-appearance': {
				powerpress_page_message_add_notice( __('Blubrry PowerPress Appearance settings saved.') );
			}; break;
			case 'powerpress-save-customfeed': {
				powerpress_page_message_add_notice( __('Blubrry PowerPress Custom Feed settings saved.') );
			}; break;
			case 'powerpress-save-feedsettings': {
				powerpress_page_message_add_notice( __('Blubrry PowerPress Feed settings saved.') );
			}; break;
			case 'powerpress-save-basic':
			default: {
				powerpress_page_message_add_notice( __('Blubrry PowerPress settings saved.') );
			}; break;
		}
		
		if( @$_POST['TestiTunesPing'] == 1 )
		{
			$PingResults = powerpress_ping_itunes($General['itunes_url']);
			if( @$PingResults['success'] )
			{
				powerpress_page_message_add_notice( 'iTunes Ping Successful. Podcast Feed URL:'. $PingResults['feed_url'] );
			}
			else
			{
				powerpress_page_message_add_error( htmlspecialchars($PingResults['content']) );
			}
		}
	}
	
	// Handle POST actions...
	if( isset($_POST['action'] ) )
	{
		switch($_POST['action'])
		{
			case 'powerpress-addfeed': {
				check_admin_referer('powerpress-add-feed');
				
				$Settings = get_option('powerpress_general');
				$key = sanitize_title($_POST['feed_slug']);
				$value = $_POST['feed_name'];
				$value = powerpress_stripslashes($value);
				
				/*
				if( isset($Settings['custom_feeds'][ $key ]) && @$_POST['overwrite'] != 1 )
				{
					powerpress_page_message_add_error( sprintf(__('Feed slug "%s" already exists.'), $key) );
				} else */
				if( $key == '' )
				{
					powerpress_page_message_add_error( sprintf(__('Feed slug "%s" is not valid.'), $_POST['feed_slug']) );
				}
				else if( in_array($key, $wp_rewrite->feeds)  && !isset($Settings['custom_feeds'][ $key ]) ) // If it is a system feed or feed created by something else
				{
					powerpress_page_message_add_error( sprintf(__('Feed slug "%s" is not available.'), $key) );
				}
				else
				{
					$Settings['custom_feeds'][ $key ] = $value;
					powerpress_save_settings($Settings);
					
					add_feed($key, 'powerpress_do_podcast_feed'); // Before we flush the rewrite rules we need to add the new custom feed...
					$wp_rewrite->flush_rules();
					
					powerpress_page_message_add_notice( sprintf(__('Podcast Feed "%s" added, please configure your new feed now.'), $value) );
					$_GET['action'] = 'powerpress-editfeed';
					$_GET['feed_slug'] = $key;
				}
			}; break;
			case 'powerpress-importpodpress': {
				check_admin_referer('powerpress-import-podpress');
				
				require_once( dirname(__FILE__) . '/powerpressadmin-podpress.php');
				powerpressadmin_podpress_do_import();
				
				$_GET['action'] = 'powerpress-podpress-epiosdes';
			}; break;
			case 'deletepodpressdata': {
				check_admin_referer('powerpress-delete-podpress-data');
				
				require_once( dirname(__FILE__) . '/powerpressadmin-podpress.php');
				powerpressadmin_podpress_delete_data();
				
			}; break;
		}
	}
	
	// Handle GET actions...
	if( isset($_GET['action'] ) )
	{
		switch( $_GET['action'] )
		{
			case 'powerpress-delete-feed': {
				$delete_slug = $_GET['feed_slug'];
				check_admin_referer('powerpress-delete-feed-'.$delete_slug);
				
				$Episodes = powerpress_admin_episodes_per_feed($delete_slug);
				
				if( $delete_slug == 'podcast' )
				{
					powerpress_page_message_add_error( __('Cannot delete default podcast feed.') );
				}
				else if( $Episodes > 0 )
				{
					powerpress_page_message_add_error( sprintf(__('Cannot delete feed. Feed contains %d episode(s).'), $Episodes) );
				}
				else
				{
					$Settings = get_option('powerpress_general');
					unset($Settings['custom_feeds'][ $delete_slug ]);
					powerpress_save_settings($Settings); // Delete the feed from the general settings
					delete_option('powerpress_feed_'.$delete_slug); // Delete the actual feed settings
					
					// Now we need to update the rewrite cso the cached rules are up to date
					if ( in_array($delete_slug, $wp_rewrite->feeds))
					{
						$index = array_search($delete_slug, $wp_rewrite->feeds);
						if( $index !== false )
							unset($wp_rewrite->feeds[$index]); // Remove the old feed
					}
				
					// Remove feed function hook
					$hook = 'do_feed_' . $delete_slug;
					remove_action($hook, $hook, 10, 1); // This may not be necessary
					$wp_rewrite->flush_rules(); // This is definitely necessary
					
					powerpress_page_message_add_notice( 'Feed deleted successfully.' );
				}
			}; break;
			case 'powerpress-podpress-settings': {
				check_admin_referer('powerpress-podpress-settings');
				
				// Import settings here..
				if( powerpress_admin_import_podpress_settings() )
					powerpress_page_message_add_notice( __('Podpress settings imported successfully.') );
				else
					powerpress_page_message_add_error( __('No Podpress settings found.') );
				
			}; break;
			case 'powerpress-add-caps': {
				check_admin_referer('powerpress-add-caps');
				
				$users = array('administrator','editor', 'author'); // , 'contributor', 'subscriber');
				while( list($null,$user) = each($users) )
				{
					$role = get_role($user);
					if( !$role->has_cap('edit_podcast') )
						$role->add_cap('edit_podcast');
				}
				$General = array('use_caps'=>true);
				powerpress_save_settings($General);
				powerpress_page_message_add_notice( __('Edit Podcast Capability added successfully.') );
				
			}; break;
			case 'powerpress-remove-caps': {
				check_admin_referer('powerpress-remove-caps');
				
				$users = array('administrator','editor', 'author', 'contributor', 'subscriber');
				while( list($null,$user) = each($users) )
				{
					$role = get_role($user);
					if( $role->has_cap('edit_podcast') )
						$role->remove_cap('edit_podcast');
				}
				$General = array('use_caps'=>false);
				powerpress_save_settings($General);
				powerpress_page_message_add_notice( __('Edit Podcast Capability removed successfully.') );
				
			}; break;
		}
	}
}

add_action('init', 'powerpress_admin_init');

function powerpress_save_settings($SettingsNew=false, $field = 'powerpress_general' )
{
	// Save general settings
	if( $SettingsNew )
	{
		$Settings = get_option($field);
		if( !is_array($Settings) )
			$Settings = array();
		while( list($key,$value) = each($SettingsNew) )
			$Settings[$key] = $value;
		update_option($field,  $Settings);
	}
}

function powerpress_get_settings($field, $for_editing=true)
{
	$Settings = get_option($field);
	if( $for_editing )
		$Settings = powerpress_htmlspecialchars($Settings);
	return $Settings;
}

function powerpress_htmlspecialchars($data)
{
	if( !$data )
		return $data;
	if( is_array($value) )
	{
		while( list($key,$value) = each($data) )
		{
			if( is_array($value) )
				$data[$key] = powerpress_htmlspecialchars($value);
			else
				$data[$key] = htmlspecialchars($value);
		}
		reset($data);
	}
	return $data;
}

function powerpress_stripslashes($data)
{
	if( !$data )
		return $data;
	
	if( !is_array($data) )
		return stripslashes($data);
	
	while( list($key,$value) = each($data) )
	{
		if( is_array($value) )
			$data[$key] = powerpress_stripslashes($value);
		else
			$data[$key] = stripslashes($value);
	}
	reset($data);
	return $data;
}

function powerpress_admin_menu()
{
	$Powerpress = get_option('powerpress_general');
	
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) )
	{
		// CRAP
	}
	else if( function_exists('add_meta_box') && (!@$Powerpress['use_caps'] || current_user_can('edit_podcast') ) )
	{ // Otherwise we're using a version of wordpress that is not supported.
		
		require_once( dirname(__FILE__).'/powerpressadmin-metabox.php');
		add_meta_box('powerpress-podcast', 'Podcast Episode', 'powerpress_meta_box', 'page', 'normal');
		
		if( isset($Powerpress['custom_feeds']) )
		{
			if( !isset($Powerpress['custom_feeds']['podcast'] ) )
			{
				add_meta_box('powerpress-podcast', 'Podcast Episode (default)', 'powerpress_meta_box', 'post', 'normal');
				
			}
			else
			{
				//add_meta_box('powerpress-podcast', 'Podcast Episode for: '.$Powerpress['custom_feeds']['podcast'].' (default)', 'powerpress_meta_box', 'post', 'normal');
				add_meta_box('powerpress-podcast', 'Podcast Episode (default)', 'powerpress_meta_box', 'post', 'normal');
			}
			
			while( list($feed_slug, $feed_title) = each($Powerpress['custom_feeds']) )
			{
				if( $feed_slug == 'podcast' )
					continue;
				add_meta_box('powerpress-'.$feed_slug, 'Podcast Episode for Custom Feed: '.$feed_title, 'powerpress_meta_box', 'post', 'normal');
			}
		}
		else
		{
			add_meta_box('powerpress-podcast', 'Podcast Episode', 'powerpress_meta_box', 'post', 'normal');
		}
	}
	
	if( current_user_can('manage_options') )
	{
		if( $Powerpress['advanced_mode'] )
		{
			add_menu_page(__('PowerPress'), __('PowerPress'), 1, 'powerpress/powerpressadmin_basic.php', 'powerpress_admin_page_basic', powerpress_get_root_url() . 'powerpress_ico.png');
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Basic Settings'), __('Basic Settings'), 1, 'powerpress/powerpressadmin_basic.php', 'powerpress_admin_page_basic' );
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Appearance Settings'), __('Appearance'), 1, 'powerpress/powerpressadmin_appearance.php', 'powerpress_admin_page_appearance' );
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Feed Settings'), __('Feed Settings'), 1, 'powerpress/powerpressadmin_feedsettings.php', 'powerpress_admin_page_feedsettings');
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Custom Feeds'), __('Custom Feeds'), 1, 'powerpress/powerpressadmin_customfeeds.php', 'powerpress_admin_page_customfeeds');
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Tools'), __('Tools'), 1, 'powerpress/powerpressadmin_tools.php', 'powerpress_admin_page_tools');
		}
		else
		{
			add_options_page('Blubrry PowerPress Settings', 'Blubrry PowerPress', 1, 'powerpress/powerpressadmin_basic.php', 'powerpress_admin_page');
		}
	}
}


add_action('admin_menu', 'powerpress_admin_menu');



// Save episode information
function powerpress_edit_post($post_ID, $post)
{
	if ( !current_user_can('edit_post', $post_ID) )
		return $postID;
		
	$Episodes = $_POST['Powerpress'];
	
	if( $Episodes )
	{
		while( list($feed_slug,$Powerpress) = each($Episodes) )
		{
			$field = 'enclosure';
			if( $feed_slug != 'podcast' )
				$field = '_'.$feed_slug.':enclosure';
			
			if( $Powerpress['remove_podcast'] == 1 )
			{
				delete_post_meta( $post_ID, $field);
				
				if( $feed_slug == 'podcast' ) // Clean up the old data
					delete_post_meta( $post_ID, 'itunes:duration');
			}
			else if( @$Powerpress['change_podcast'] == 1 || @$Powerpress['new_podcast'] == 1 )
			{
				// No URL specified, then it's not really a podcast to save
				if( $Powerpress['url'] == '' )
					continue; // go to the next media file
				
				// Initialize the important variables:
				$MediaURL = $Powerpress['url'];
				if( strpos($MediaURL, 'http://') !== 0 ) // If the url entered does not start with a http://
				{
					$Settings = get_option('powerpress_general');
					$MediaURL = rtrim(@$Settings['default_url'], '/') .'/'. $MediaURL;
				}
				
				$FileSize = '';
				$ContentType = '';
				$Duration = false;

				// Get the content type based on the file extension, first we have to remove query string if it exists
				$UrlParts = parse_url($Powerpress['url']);
				if( $UrlParts['path'] )
				{
					// using functions that already exist in Wordpress when possible:
					$FileType = wp_check_filetype($UrlParts['path']);
					if( $FileType )
						$ContentType = $FileType['type'];
					
					/*
					$FileParts = pathinfo($UrlParts['path']);
					if( $FileParts )
					{
						
						$ContentType = powerpress_mimetypes($FileParts['extension']);
					}
					*/
				}

				//Set the duration specified by the user
				if( $Powerpress['set_duration'] == 1 ) // specify duration
				{
					$Duration = sprintf('%02d:%02d:%02d', $Powerpress['duration_hh'], $Powerpress['duration_mm'], $Powerpress['duration_ss'] );
				}
				
				//Set the file size specified by the user
				if( $Powerpress['set_size'] == 1 ) // specify file size
				{
					$FileSize = $Powerpress['size'];
				}
				
				if( $Powerpress['set_size'] == 0 || $Powerpress['set_duration'] == 0 )
				{
					// Lets use the mp3info class:
					require_once('mp3info.class.php');
					
					$Mp3Info = new Mp3Info();
					if( $Powerpress['set_duration'] == 0 && $ContentType == 'audio/mpeg' )
					{
						$Mp3Data = $Mp3Info->GetMp3Info($MediaURL);
						if( $Mp3Data )
						{
							if( @$Powerpress['set_size'] == 0 )
								$FileSize = $Mp3Info->GetContentLength();
							$Duration = $Mp3Data['playtime_string'];
							if( substr_count($Duration, ':' ) == 0 )
							{
								if( $Duration < 60 )
									$Duration = '00:00:'.$Duration;
							}
							else if( substr_count($Duration, ':' ) == 1 )
							{
								$Duration = '00:'.$Duration;
							}
							$Duration = powerpress_readable_duration($Duration, true); // Fix so it looks better when viewed for editing
						}
					}
					
					// Just get the file size
					if( $Powerpress['set_size'] == 0 && $FileSize == '' )
					{
						$headers = wp_get_http_headers($MediaURL);
						if( $headers && $headers['content-length'] )
						{
							$FileSize = (int) $headers['content-length'];
						}
					}
				}
				
				$EnclosureData = $MediaURL . "\n" . $FileSize . "\n". $ContentType;	
				$ToSerialize = array();
				// iTunes duration
				if( $Duration )
					$ToSerialize['duration'] = $Duration; // regular expression '/^(\d{1,2}\:)?\d{1,2}\:\d\d$/i' (examples: 1:23, 12:34, 1:23:45, 12:34:56)
				// iTunes Subtitle (FUTURE USE)
				if( isset($Powerpress['subtitle']) && trim($Powerpress['subtitle']) != '' ) 
					$ToSerialize['subtitle'] = $Powerpress['subtitle'];
				// iTunes Summary (FUTURE USE)
				if( isset($Powerpress['summary']) && trim($Powerpress['summary']) != '' ) 
					$ToSerialize['summary'] = $Powerpress['summary'];
				// iTunes keywords (FUTURE USE)
				if( isset($Powerpress['keywords']) && trim($Powerpress['keywords']) != '' ) 
					$ToSerialize['keywords'] = $Powerpress['keywords'];
				// iTunes Author (FUTURE USE)
				if( isset($Powerpress['author']) && trim($Powerpress['author']) != '' ) 
					$ToSerialize['author'] = $Powerpress['author'];
				// iTunes Explicit (FUTURE USE)
				if( isset($Powerpress['explicit']) && trim($Powerpress['explicit']) != '' ) 
					$ToSerialize['explicit'] = $Powerpress['explicit'];
				// iTunes Block (FUTURE USE)
				if( isset($Powerpress['block']) && (trim($Powerpress['block']) == 'yes' || trim($Powerpress['block']) == 'no') ) 
					$ToSerialize['block'] = ($Powerpress['block']=='yes'?'yes':'');
				// Player Embed (FUTURE USE)
				if( isset($Powerpress['player_embed']) && trim($Powerpress['player_embed']) != '' )
					$ToSerialize['player_embed'] = $Powerpress['player_embed'];
				if( $Powerpress['set_duration'] == -1 )
					unset($ToSerialize['duration']);
				if( count($ToSerialize) > 0 ) // Lets add the serialized data
					$EnclosureData .= "\n".serialize( $ToSerialize );
				
				if( @$Powerpress['new_podcast'] )
				{
					add_post_meta($post_ID, $field, $EnclosureData, true);
				}
				else
				{
					update_post_meta($post_ID, $field, $EnclosureData);
					if( $feed_slug == 'podcast' )
						delete_post_meta( $post_ID, 'itunes:duration'); // Simple cleanup, we're storing the duration in the enclosure as serialized value
				}
			}
			
			// If we're moving from draft to published, maybe we should ping iTunes?
			if($_POST['prev_status'] == 'draft' && $_POST['publish'] == 'Publish' )
			{
				// Next double check we're looking at a podcast episode...
				$Enclosure = get_post_meta($post_ID, $field, true);
				if( $Enclosure )
				{
					$Settings = get_option('powerpress_feed_'.$feed_slug);
					if( $Settings['ping_itunes'] && $Settings['itunes_url'] )
					{
						$PingResults = powerpress_ping_itunes($Settings['itunes_url']);
						//mail( 'email@host.com', 'Ping iTunes Results', implode("\n", $PingResults) ); // Let me know how the ping went.
					}
				}
			}
		} // Loop through posted episodes...
	}
	
	// If we're moving from draft to published, maybe we should ping iTunes?
	if($_POST['prev_status'] == 'draft' && $_POST['publish'] == 'Publish' )
	{
		// Next double check we're looking at a podcast episode...
		$Enclosure = get_post_meta($post_ID, 'enclosure', true);
		if( $Enclosure )
		{
			$Settings = get_option('powerpress_general');
			if( $Settings['ping_itunes'] && $Settings['itunes_url'] )
			{
				$PingResults = powerpress_ping_itunes($Settings['itunes_url']);
				//mail( 'email@host.com', 'Ping iTunes Results', implode("\n", $PingResults) ); // Let me know how the ping went.
			}
		}
	}
		
	// And we're done!
}

add_action('edit_post', 'powerpress_edit_post', 10, 2);

// Admin page, html meta header
function powerpress_admin_head()
{
	if( strstr($_GET['page'], 'powerpress' ) )
	{
?>
<script type="text/javascript">
function powerpress_show_field(id, show) {
	if( document.getElementById(id).nodeName == "SPAN" )
	 document.getElementById(id).style.display = (show?"inline":"none");
 else
	 document.getElementById(id).style.display = (show?"block":"none");
}
function powerpress_new_feed_url_prompt() {
	var Msg = 'WARNING: Changes made here are permanent. If the New Feed URL entered is incorrect, you will lose subscribers and will no longer be able to update your listing in the iTunes Store.\n\nDO NOT MODIFY THIS SETTING UNLESS YOU ABSOLUTELY KNOW WHAT YOU ARE DOING.\n\nAre you sure you want to continue?';
	if( confirm(Msg) ) {
		powerpress_show_field('new_feed_url_step_1', false);
		powerpress_show_field('new_feed_url_step_2', true);
	}
	return false;
}
function powerpress_changemode(Mode)
{
	if( Mode )
	{
		if( !confirm("Are you sure you want to switch to Advanced Mode?\n\nAdvanced Mode provides:\n   * Advanced Settings\n   * Presentation Settings\n   * Extensive Feed Settings \n   * Custom Feeds \n   * Useful Tools") )
			return false;
	}
	else
	{
		if( !confirm("Are you sure you want to switch to Simple Mode?\n\nSimple Mode provides:\n   * Only the bare essential settings\n   * All settings on one page") )
			return false;
	}
	document.getElementById('powerpress_advanced_mode').value = Mode;
	document.forms[0].submit();
	return false;
}
</script>
<style type="text/css">
.powerpress-notice {
	margin-top: 10px;
	margin-bottom: 10px;
	line-height: 29px;
	font-size: 12px;
	border-width: 1px;
	border-style: solid;
	font-weight: bold;
}
.powerpress-error {
	margin-top: 10px;
	margin-bottom: 10px;
	line-height: 29px;
	font-size: 12px;
	border-width: 1px;
	border-style: solid;
	font-weight: bold;
}
#powerpress_settings {
	background-image:url(http://images.blubrry.com/powerpress/blubrry_logo.png);
	background-repeat: no-repeat;
	background-position: bottom right;
}

#powerpress_settings ul li ul {
	list-style: disc;
}
#powerpress_settings ul li ul li {
	margin-left: 50px;
	font-size: 90%;
}
#powerpress_settings label {
	font-size: 120%;
}
.bpp_input_sm {
	width: 120px;
}
.bpp_input_med {
	width: 250px;
}
</style>
<?php
	}
	else
	{
?>
<style type="text/css">
.powerpress_podcast_box {
	
}
.powerpress_podcast_box label {
	width: 120px;
	font-weight: bold;
	font-size: 110%;
	display: inline;
	position: absolute;
	top: 0;
	left: 0;
}
.powerpress_podcast_box .powerpress_row {
	margin-top: 10px;
	margin-bottom: 10px;
	position: relative;
}
.powerpress_podcast_box .powerpress_row_content {
	margin-left: 120px;
}
.powerpress_podcast_box  .error {
	margin-top: 10px;
	margin-bottom: 10px;
	padding: 5px;
	font-size: 12px;
	text-align: center;
	border-width: 1px;
	border-style: solid;
	font-weight: bold;
}
</style>
<script language="javascript">
function powerpress_check_url(url)
{
	var DestDiv = 'powerpress_warning';
	if( powerpress_check_url.arguments.length > 1 )
		DestDiv = powerpress_check_url.arguments[1];
	
	var validChars = ':0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/-_.';

	for( var x = 0; x < url.length; x++ )
	{
		if( validChars.indexOf( url.charAt(x) ) == -1 )
		{
			document.getElementById(DestDiv).innerHTML = 'Media URL contains characters that may cause problems for some clients. For maximum compatibility, only use letters, numbers, dash - and underscore _ characters only.';
			document.getElementById(DestDiv).style.display = 'block';
			return;
		}
	
		if( x == 5 )
			validChars = validChars.substring(1); // remove the colon, should no longer appear in URLs
	}

	document.getElementById(DestDiv).style.display = 'none';
}

</script>
<?php
	}
}

add_action('admin_head', 'powerpress_admin_head');

// Admin page, header
function powerpress_admin_page_header($page=false, $nonce_field = 'powerpress-edit')
{
	if( !$page )
		$page = 'powerpress/powerpressadmin_basic.php';
?>
<div class="wrap" id="powerpress_settings">
<form enctype="multipart/form-data" method="post" action="<?php echo admin_url('admin.php?page='.$page) ?>">
<?php
	if( $nonce_field )
		wp_nonce_field($nonce_field);
	
	powerpress_page_message_print();
}

// Admin page, footer
function powerpress_admin_page_footer($SaveButton=true)
{
	if( $SaveButton ) { ?>
<p class="submit">
<input type="submit" name="Submit" id="powerpress_save_button" class="button-primary" value="<?php _e('Save Changes' ) ?>" />
</p>
<?php } ?>
<p style="font-size: 85%; text-align: center; padding-bottom: 25px;">
	<a href="http://www.blubrry.com/powerpress/" title="Blubrry PowerPress" target="_blank">Blubrry PowerPress</a> <?php echo POWERPRESS_VERSION; ?>
	&#8212; <a href="http://help.blubrry.com/blubrry-powerpress/" target="_blank" title="Blubrry PowerPress Documentation">Documentation</a> | <a href="http://twitter.com/blubrry" target="_blank" title="Follow Blubrry on Twitter">Follow Blubrry on Twitter</a>
</p>
</form>
</div>
<?php 
}

// Admin page, advanced mode: basic settings
function powerpress_admin_page_basic()
{
	powerpress_admin_page_header();
	require_once( dirname(__FILE__).'/powerpressadmin-basic.php');
	powerpress_admin_basic();
	powerpress_admin_page_footer(true, true);
}

// Admin page, advanced mode: appearance settings
function powerpress_admin_page_appearance()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_appearance.php');
	require_once( dirname(__FILE__).'/powerpressadmin-appearance.php');
	powerpressadmin_appearance();
	powerpress_admin_page_footer();
}

// Admin page, advanced mode: feed settings
function powerpress_admin_page_feedsettings()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_feedsettings.php');
	require_once( dirname(__FILE__).'/powerpressadmin-editfeed.php');
	powerpress_admin_editfeed();
	powerpress_admin_page_footer();
}

// Admin page, advanced mode: custom feeds
function powerpress_admin_page_customfeeds()
{
	switch( @$_GET['action'] )
	{
		case 'powerpress-editfeed' : {
			powerpress_admin_page_header('powerpress/powerpressadmin_customfeeds.php');
			require_once( dirname(__FILE__).'/powerpressadmin-editfeed.php');
			powerpress_admin_editfeed($_GET['feed_slug']);
			powerpress_admin_page_footer();
		}; break;
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_customfeeds.php', 'powerpress-add-feed');
			require_once( dirname(__FILE__).'/powerpressadmin-customfeeds.php');
			powerpress_admin_customfeeds();
			powerpress_admin_page_footer(false);
		};
	}
}

// Admin page, advanced mode: tools
function powerpress_admin_page_tools()
{
	switch( @$_GET['action'] )
	{
		case 'powerpress-podpress-epiosdes' : {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', 'powerpress-import-podpress');
			require_once( dirname(__FILE__).'/powerpressadmin-podpress.php');
			powerpress_admin_podpress();
			powerpress_admin_page_footer(false);
		}; break;
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php');
			require_once( dirname(__FILE__).'/powerpressadmin-tools.php');
			powerpress_admin_tools();
			powerpress_admin_page_footer(false);
		};
	}
}

function powerpress_podpress_episodes_exist()
{
	global $wpdb;
	$query = "SELECT post_id ";
	$query .= "FROM {$wpdb->postmeta} ";
	$query .= "WHERE meta_key = 'podPressMedia' ";
	$query .= "LIMIT 0, 1";
	$results = $wpdb->get_results($query, ARRAY_A);
	if( count($results) )
		return true;
	return false;
}

// Admin page, simple mode
function powerpress_admin_page()
{
	powerpress_admin_page_header();

	require_once( dirname(__FILE__).'/powerpressadmin-basic.php');
	powerpress_admin_basic();

	require_once( dirname(__FILE__).'/powerpressadmin-editfeed.php');
	powerpress_admin_editfeed();

	powerpress_admin_page_footer(true, true);
}
	
function powerpress_shutdown()
{
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_encloseme' ");
}

add_action('shutdown','powerpress_shutdown'); // disable the auto enclosure process
	
/*
// Helper functions:
*/
function powerpress_ping_itunes($iTunes_url)
{
	if( strpos($iTunes_url, 'phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=' ) === false )
		return array('error'=>true, 'content'=>'iTunes URL required to ping iTunes.');
	
	// convert: https://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=
	// to: https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=
	$ping_url = str_replace(
		array(	'https://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
							'http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
							'https://www.itunes.com/podcast?id=',
							'http://www.itunes.com/podcast?id='),
		'https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=', $iTunes_url);
	
	$tempdata = wp_remote_fopen($ping_url);
	
	if( $tempdata == false )
		return array('error'=>true, 'content'=>'Unable to connect to iTunes ping server.');
	
	if( stristr($tempdata, 'No Podcast Found') )
		return array('error'=>true, 'content'=>'No Podcast Found from iTunes ping request');
		
	// Parse the data into something readable
	$results = trim( str_replace('Podcast Ping Received', '', strip_tags($tempdata) ) );
	list($null, $FeedURL, $null, $null, $null, $PodcastID) = split("\n", $results );
	
	return array('success'=>true, 'content'=>$tempdata, 'feed_url'=>trim($FeedURL), 'podcast_id'=>trim($PodcastID) );
}

// Import podpress settings
function powerpress_admin_import_podpress_settings()
{
	// First pull in the Podpress settings
	$PodpressData = get_option('podPress_config');
	if( !$PodpressData )
		return false;
	
	$General = get_option('powerpress_general');
	if( !$General)
		$General = array();
	$General['process_podpress'] = 1;
	$General['display_player'] = 1;
	$General['player_function'] = 1;
	$General['podcast_link'] = 1;
	$General['ping_itunes'] = 1;
	// Lets try to copy settings from podpress
	$General['default_url'] = $PodpressData['mediaWebPath'];
	if( substr($General['default_url'], 0, -1) != '/' )
		$General['default_url'] .= '/'; // Add the trailing slash, donno it's not there...
	
	// Insert the blubrry redirect
	if( isset($PodpressData['statBluBrryProgramKeyword']) && strlen($PodpressData['statBluBrryProgramKeyword']) > 2 )
	{
		$General['redirect1'] = 'http://media.blubrry.com/'.$PodpressData['statBluBrryProgramKeyword'].'/';
	}
	
	// Insert the Podtrac redirect
	if( $PodpressData['enable3rdPartyStats'] == 'PodTrac' )
	{
		if( $General['redirect1'] )
			$General['redirect2'] = 'http://www.podtrac.com/pts/redirect.mp3/';
		else
			$General['redirect1'] = 'http://www.podtrac.com/pts/redirect.mp3/';
	}
	
	if( $PodpressData['contentDownload'] == 'enabled' )
		$General['podcast_link'] = 1;
	else
		$General['podcast_link'] = 0;
	
	if( $PodpressData['contentPlayer'] == 'both' )
		$General['player_function'] = 1;
	else if( $PodpressData['contentPlayer'] == 'inline' )
		$General['player_function'] = 2;
	else if( $PodpressData['contentPlayer'] == 'popup' )
		$General['player_function'] = 3;
	else
		$General['player_function'] = 0;
		
	if( $PodpressData['contentPlayer'] == 'start' )
		$General['display_player'] = 2;
	else
		$General['display_player'] = 1;
	
	if( $PodpressData['iTunes']['FeedID'] )
		$General['itunes_url'] = 'http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id='. $PodpressData['iTunes']['FeedID'];
	
	// save these imported general settings
	powerpress_save_settings($General, 'powerpress_general');

	$FeedSettings = get_option('powerpress_feed');
	
	if( !$FeedSettings ) // If no feed settings, lets set defaults or copy from podpress.
		$FeedSettings = array();
		
	$FeedSettings['apply_to'] = 1; // Default, apply to all the rss2 feeds
	
	$FeedSettings['itunes_image'] = $PodpressData['iTunes']['image'];
	if( strstr($FeedSettings['itunes_image'], 'powered_by_podpress') )
		$FeedSettings['itunes_image'] = ''; // We're not using podpress anymore
	
	$FeedSettings['itunes_summary'] = $PodpressData['iTunes']['summary'];
	$FeedSettings['itunes_talent_name'] = $PodpressData['iTunes']['author'];
	$FeedSettings['itunes_subtitle'] = $PodpressData['iTunes']['subtitle'];
	$FeedSettings['itunes_keywords'] = $PodpressData['iTunes']['keywords'];
	$FeedSettings['copyright'] = $PodpressData['rss_copyright'];
	// Categories are tricky...
	$iTunesCategories = powerpress_itunes_categories(true);
	for( $x = 0; $x < 3; $x++ )
	{
		$CatDesc = str_replace(':', ' > ', $PodpressData['iTunes']['category'][$x]);
		$CatKey = array_search($CatDesc, $iTunesCategories);
		if( $CatKey )
			$FeedSettings['itunes_cat_'.($x+1)] = $CatKey;
	}
	
	if( $PodpressData['iTunes']['explicit'] == 'No' )
		$FeedSettings['itunes_explicit'] = 0;
	else if( $PodpressData['iTunes']['explicit'] == 'Yes' )
		$FeedSettings['itunes_explicit'] = 1;
	else if( $PodpressData['iTunes']['explicit'] == 'Clean' )
		$FeedSettings['itunes_explicit'] = 2;

	// Lastly, lets try to get the RSS image from the database
	$RSSImage = get_option('rss_image');
	if( $RSSImage )
		$FeedSettings['rss2_image'] = $RSSImage;
	if( strstr($FeedSettings['rss2_image'], 'powered_by_podpress') )
		$FeedSettings['rss2_image'] = ''; // We're not using podpress anymore
	$AdminEmail = get_option('admin_email');
	if( $AdminEmail )
		$FeedSettings['email'] = $AdminEmail;
		
	// save these imported feed settings
	powerpress_save_settings($FeedSettings, 'powerpress_feed');
	return true;
}

function powerpress_admin_episodes_per_feed($feed_slug)
{
	$field = 'enclosure';
	if( $feed_slug != 'podcast' )
		$field = '_'. $feed_slug .':enclosure';
	global $wpdb;
	if ( $results = $wpdb->get_results("SELECT COUNT(post_id) AS episodes_total FROM $wpdb->postmeta WHERE meta_key = '$field'", ARRAY_A) ) {
		if( count($results) )
		{
			list($key,$row) = each($results);
			if( $row['episodes_total'] )
				return $row['episodes_total'];
		}
	}
	return 0;
}
?>