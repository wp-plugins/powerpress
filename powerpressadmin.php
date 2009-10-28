<?php

if( !function_exists('add_action') )
	die("access denied.");
	
function powerpress_page_message_add_error($msg)
{
	global $g_powerpress_page_message;
	$g_powerpress_page_message .= '<div class="error powerpress-error">'. $msg . '</div>';
}

function powerpress_page_message_add_notice($msg)
{
	global $g_powerpress_page_message;
	// Always pre-pend, since jQuery will re-order with first as last.
	$g_powerpress_page_message = '<div class="updated fade powerpress-notice">'. $msg . '</div>' . $g_powerpress_page_message;
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
	
	add_thickbox(); // we use the thckbox for some settings
	wp_enqueue_script('jquery');
	//wp_enqueue_script('jquery-ui-resizable');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-tabs');
	//wp_enqueue_script('jquery-ui-selectable');
	//wp_enqueue_script('interface');
	
	//wp_enqueue_script('jquery-ui-resizable');
	//wp_enqueue_script('jquery-ui-draggable');
	//wp_enqueue_script('jquery-ui-droppable');
	//wp_enqueue_script('jquery-ui-selectable');
	//wp_enqueue_script('jquery-ui-sortable');
	
	wp_enqueue_script( 'powerpress',  powerpress_get_root_url().'3rdparty/jquery.cookie.js', array('jquery' ) );
	
	
	if( function_exists('powerpress_admin_jquery_init') )
		powerpress_admin_jquery_init();
	
	if( !current_user_can('manage_options') )
	{
		powerpress_page_message_add_error( __('You do not have sufficient permission to manage options.') );
		return;
	}

	// Check for other podcasting plugin
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
		powerpress_page_message_add_error( __('Another podcasting plugin has been detected, PowerPress is currently disabled.') );
	
	global $wp_version;
	$VersionDiff = version_compare($wp_version, 2.6);
	if( $VersionDiff < 0 )
		powerpress_page_message_add_error( __('Blubrry PowerPress requires Wordpress version 2.6 or greater.') );

	
	// Save settings here
	if( isset($_POST[ 'Feed' ]) || isset($_POST[ 'General' ])  )
	{
		check_admin_referer('powerpress-edit');
		
		$upload_path = false;
		$upload_url = false;
		$UploadArray = wp_upload_dir();
		if( false === $UploadArray['error'] )
		{
			$upload_path =  $UploadArray['basedir'].'/powerpress/';
			$upload_url =  $UploadArray['baseurl'].'/powerpress/';
		}
	
		// Save the posted value in the database
		$Feed = $_POST['Feed'];
    $General = $_POST['General'];
		$FeedSlug = (isset($_POST['feed_slug'])?$_POST['feed_slug']:false);
		$Category = (isset($_POST['cat'])?$_POST['cat']:false);
		
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
			$ImageData = @getimagesize($temp);
			if( $ImageData && ( $ImageData[2] == IMAGETYPE_JPEG || $ImageData[2] == IMAGETYPE_PNG ) && $ImageData[0] == $ImageData[1] ) // Just check that it is an image, the correct image type and that the image is square
			{
				move_uploaded_file($temp, $upload_path . $filename);
				$Feed['itunes_image'] = $upload_url . $filename;
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
			
			if( @getimagesize($temp) )  // Just check that it is an image, we may add more to this later
			{
				move_uploaded_file($temp, $upload_path . $filename);
				$Feed['rss2_image'] = $upload_url . $filename;
			}
			else
			{
				powerpress_page_message_add_error( 'Invalid RSS image: ' . htmlspecialchars($_FILES['rss2_image_file']['name']) );
			}
		}
		
		// New mp3 coverart image
		if( @$_POST['coverart_image_checkbox'] == 1 )
		{
			$filename = str_replace(" ", "_", basename($_FILES['coverart_image_file']['name']) );
			$temp = $_FILES['coverart_image_file']['tmp_name'];
			
			if( file_exists($upload_path . $filename ) )
			{
				$filenameParts = pathinfo($filename);
				do {
					$filename_no_ext = substr($filenameParts['basename'], 0, (strlen($filenameParts['extension'])+1) * -1 );
					$filename = sprintf('%s-%03d.%s', $filename_no_ext, rand(0, 999), $filenameParts['extension'] );
				} while( file_exists($upload_path . $filename ) );
			}
			
			if( @getimagesize($temp) )  // Just check that it is an image, we may add more to this later
			{
				move_uploaded_file($temp, $upload_path . $filename);
				$_POST['TagValues']['tag_coverart'] = $upload_url . $filename;
			}
			else
			{
				powerpress_page_message_add_error( 'Invalid Coverat image: ' . htmlspecialchars($_FILES['coverart_image_file']['name']) );
			}
		}
		
		if( isset($_POST['UpdateDisablePlayer']) )
		{
			$player_feed_slug = $_POST['UpdateDisablePlayer'];
			$General['disable_player'] = array();
			$GeneralPrev = get_option('powerpress_general');
			if( isset($GeneralPrev['disable_player']) )
				$General['disable_player'] = $GeneralPrev['disable_player'];
			if( isset($_POST['DisablePlayerFor']) )
				$General['disable_player'][ $player_feed_slug ] = 1;
			else
				unset($General['disable_player'][ $player_feed_slug ]);
		}
		
		
		if( isset($_POST['PlayerScaleCustom']) && isset($General['player_scale']) && $General['player_scale'] !='tofit' && $General['player_scale'] != 'aspect' )
		{
			$General['player_scale'] = $_POST['PlayerScaleCustom'];
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
			
			if( $_POST['action'] == 'powerpress-save-settings' )
			{
				if( !isset($General['display_player_excerpt']) ) // If we are modifying appearance settings but this option was not checked...
					$General['display_player_excerpt'] = 0; // Set it to zero.
				
				$General['disable_dashboard_widget'] = 0;
				if( !isset($_POST['StatsInDashboard'] ) )
					$General['disable_dashboard_widget'] = 1;
					
				if( !isset($General['episode_box_embed'] ) )
					$General['episode_box_embed'] = 0;
				if( !isset($General['episode_box_no_player'] ) )
					$General['episode_box_no_player'] = 0;
				if( !isset($General['episode_box_keywords'] ) )
					$General['episode_box_keywords'] = 0;
				if( !isset($General['episode_box_subtitle'] ) )
					$General['episode_box_subtitle'] = 0;
				if( !isset($General['episode_box_summary'] ) )
					$General['episode_box_summary'] = 0;
				if( !isset($General['episode_box_explicit'] ) )
					$General['episode_box_explicit'] = 0;
				
				// Advanced Features
				if( !isset($General['player_options'] ) )
					$General['player_options'] = 0;
				if( !isset($General['cat_casting'] ) )
					$General['cat_casting'] = 0;
				if( !isset($General['channels'] ) )
					$General['channels'] = 0;
				if( !isset($General['advanced_mode']) )
					$General['advanced_mode'] = 1;
			}
			
			if( $_POST['action'] == 'powerpress-save-tags' )
			{
				if( !isset($General['write_tags']) ) // If we are modifying appearance settings but this option was not checked...
					$General['write_tags'] = 0; // Set it to zero.
					
				$TagValues = $_POST['TagValues'];
				// Set all the tag values...
				while( list($key,$value) = each($General) )
				{
					if( substr($key, 0, 4) == 'tag_' )
					{
						if( $value )
							$General[$key] = $TagValues[$key];
						else
							$General[$key] = '';
					}
				}
				
				if( $TagValues['tag_coverart'] != '' )
				{
					$GeneralSettingsTemp = powerpress_get_settings('powerpress_general', false);
					if( isset($GeneralSettingsTemp['blubrry_hosting']) && $GeneralSettingsTemp['blubrry_hosting'] )
					{
						// Lets try to cache the image onto Blubrry's Server...
						$api_url = sprintf('%s/media/%s/coverart.json?url=%s', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $GeneralSettingsTemp['blubrry_program_keyword'], urlencode($TagValues['tag_coverart']) );
						$json_data = powerpress_remote_fopen($api_url, $GeneralSettingsTemp['blubrry_auth']);
						$results =  powerpress_json_decode($json_data);
							
						if( is_array($results) && !isset($results['error']) )
						{
							// Good!
						}
						else if( isset($results['error']) )
						{
							$error = 'Blubrry Hosting Error (updating coverart): '. $results['error'];
						}
						else
						{
							$error = 'An error occurred updating the coverart with your Blubrry Services Account.';
						}
						
					}
					else
					{
						powerpress_page_message_add_error( 'Coverart Image was not uploaded to your Blubrry Services Account. It will <u>NOT</u> be added to your mp3s.' );
					}
				}
			}
			
			// Wordpress adds slashes to everything, but since we're storing everything serialized, lets remove them...
			$General = powerpress_stripslashes($General);
			powerpress_save_settings($General);
		}
		
		if( $Feed )
		{
			if( !isset($_POST['ProtectContent']) && isset($Feed['premium']) )
				$Feed['premium'] = false;
			if( !isset($Feed['enhance_itunes_summary']) )
				$Feed['enhance_itunes_summary'] = false;
			if( !isset($Feed['itunes_author_post']) )
				$Feed['itunes_author_post'] = false;	
			
			$Feed = powerpress_stripslashes($Feed);
			if( $Category )
				powerpress_save_settings($Feed, 'powerpress_cat_feed_'.$Category);
			else
				powerpress_save_settings($Feed, 'powerpress_feed'.($FeedSlug?'_'.$FeedSlug:'') );
		}
		
		if( isset($_POST['EpisodeBoxBGColor']) )
		{
			$GeneralSettingsTemp = get_option('powerpress_general');
			$SaveEpisdoeBoxBGColor['episode_box_background_color'] = array();
			if( isset($GeneralSettingsTemp['episode_box_background_color']) )
				$SaveEpisdoeBoxBGColor['episode_box_background_color'] = $GeneralSettingsTemp['episode_box_background_color']; //  copy previous settings
			
			list($feed_slug_temp, $background_color) = each($_POST['EpisodeBoxBGColor']);
			$SaveEpisdoeBoxBGColor['episode_box_background_color'][ $feed_slug_temp ] = $background_color;
			powerpress_save_settings($SaveEpisdoeBoxBGColor);
		}
		
		// Anytime settings are saved lets flush the rewrite rules
		$wp_rewrite->flush_rules();
		
		// Settings saved successfully
		switch( $_POST['action'] )
		{
			case 'powerpress-save-settings': {
				powerpress_page_message_add_notice( __('Blubrry PowerPress settings saved successfully.') );
			}; break;
			case 'powerpress-save-appearance': {
				powerpress_page_message_add_notice( __('Blubrry PowerPress Appearance settings saved.') );
			}; break;
			case 'powerpress-save-customfeed': {
				powerpress_page_message_add_notice( __('Blubrry PowerPress Custom Feed settings saved.') );
			}; break;
			
			case 'powerpress-save-feedsettings': {
				powerpress_page_message_add_notice( __('Blubrry PowerPress Feed settings saved.') );
			}; break;
			case 'powerpress-save-categoryfeedsettings': {
				powerpress_page_message_add_notice( __('Blubrry PowerPress Category Feed settings saved.') );
			}; break;
			case 'powerpress-save-tags': {
				$General = get_option('powerpress_general');
				if( !@$General['blubrry_hosting'] )
					powerpress_page_message_add_notice( __('ATTENTION: You must configure your Blubrry Services in the Blubrry PowerPress &gt; Basic Settings page in order to utilize this feature.') );
				else
					powerpress_page_message_add_notice( __('Blubrry PowerPress MP3 Tag settings saved.') );
			}; break;
			case 'powerpress-save-mode': {
				if( $General['advanced_mode'] == 1 )
					powerpress_page_message_add_notice( __('You are now in Advanced Mode.') );
				else
					powerpress_page_message_add_notice( __('You are now in Simple Mode.') );
			}; break;
			case 'powerpress-save-basic':
			default: {
				powerpress_page_message_add_notice( __('Blubrry PowerPress settings saved.') );
			}; break;
		}
		
		if( @$_POST['TestiTunesPing'] == 1 )
		{
			if( $_POST['action'] == 'powerpress-save-settings' )
				$PingResults = powerpress_ping_itunes($General['itunes_url']);
			else
				$PingResults = powerpress_ping_itunes($Feed['itunes_url']);
			
			powerpress_ping_itunes_log($PingResults);
			
			if( @$PingResults['success'] )
			{
				powerpress_page_message_add_notice( 'iTunes Ping Successful. Podcast Feed URL: '. $PingResults['feed_url'] );
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
			case 'powerpress-addcategoryfeed': {
				check_admin_referer('powerpress-add-category-feed');
				$cat_ID = ( isset($_POST['cat'])? $_POST['cat'] : $_GET['cat'] );
				
				$Settings = get_option('powerpress_general');
				/*
				$key = sanitize_title($_POST['feed_slug']);
				$value = $_POST['feed_name'];
				$value = powerpress_stripslashes($value);
				*/
				
				$category = get_category($cat_ID);
				/*
				if( isset($Settings['custom_feeds'][ $key ]) && @$_POST['overwrite'] != 1 )
				{
					powerpress_page_message_add_error( sprintf(__('Feed slug "%s" already exists.'), $key) );
				} else */
				if( $category == false )
				{
					powerpress_page_message_add_error( __('Error obtaining category information.') );
				}
				else
				{
					if( !is_array($Settings['custom_cat_feeds']) )
						$Settings['custom_cat_feeds'] = array();
					
					if( !in_array($cat_ID, @$Settings['custom_cat_feeds']) )
					{
						$Settings['custom_cat_feeds'][] = $cat_ID;
						powerpress_save_settings($Settings);
					}
				
					powerpress_page_message_add_notice( __('Please configure your category podcast feed now.') );
					
					$_GET['action'] = 'powerpress-editcategoryfeed';
					$_GET['cat'] = $cat_ID;
				}
			}; break;
			case 'powerpress-ping-sites': {
				check_admin_referer('powerpress-ping-sites');
				
				require_once( dirname(__FILE__) . '/powerpressadmin-ping-sites.php');
				powerpressadmin_ping_sites_process();
				
				$_GET['action'] = 'powerpress-ping-sites';
			}; break;
			case 'powerpress-importpodpress': {
				check_admin_referer('powerpress-import-podpress');
				
				require_once( dirname(__FILE__) . '/powerpressadmin-podpress.php');
				powerpressadmin_podpress_do_import();
				
				$_GET['action'] = 'powerpress-podpress-epiosdes';
			}; break;
			case 'powerpress-importmt': {
				check_admin_referer('powerpress-import-mt');
				
				require_once( dirname(__FILE__) . '/powerpressadmin-mt.php');
				powerpressadmin_mt_do_import();
				
				$_GET['action'] = 'powerpress-mt-epiosdes';
			}; break;
			case 'deletepodpressdata': {
				check_admin_referer('powerpress-delete-podpress-data');
				
				require_once( dirname(__FILE__) . '/powerpressadmin-podpress.php');
				powerpressadmin_podpress_delete_data();
				
			}; break;
			case 'powerpress-save-mode': {
				
				if( !isset($_POST['General']['advanced_mode']) )
					powerpress_page_message_add_notice( __('You must select a Mode to continue.') );
				
			}; break;
		}
	}
	
	// Handle GET actions...
	if( isset($_GET['action'] ) )
	{
		switch( $_GET['action'] )
		{
			case 'powerpress-enable-categorypodcasting': {
				check_admin_referer('powerpress-enable-categorypodcasting');
				
				$Settings = get_option('powerpress_general');
				$Settings['cat_casting'] = 1;
				powerpress_save_settings($Settings);
				
				wp_redirect('categories.php?message=3');
				exit;
				
			}; break;
			case 'powerpress-addcategoryfeed': {
				check_admin_referer('powerpress-add-category-feed');
				$cat_ID = $_GET['cat'];
				
				$Settings = get_option('powerpress_general');
				$category = get_category($cat_ID);
				if( $category == false )
				{
					powerpress_page_message_add_error( __('Error obtaining category information.') );
				}
				else
				{
					if( !is_array($Settings['custom_cat_feeds']) )
						$Settings['custom_cat_feeds'] = array();
					
					if( !in_array($cat_ID, @$Settings['custom_cat_feeds']) )
					{
						$Settings['custom_cat_feeds'][] = $cat_ID;
						powerpress_save_settings($Settings);
					}
				
					powerpress_page_message_add_notice( __('Please configure your category podcast feed now.') );
					
					$_GET['action'] = 'powerpress-editcategoryfeed';
					$_GET['cat'] = $cat_ID;
				}
			}; break;
			case 'powerpress-delete-feed': {
				$delete_slug = $_GET['feed_slug'];
				$force_deletion = @$_GET['force'];
				check_admin_referer('powerpress-delete-feed-'.$delete_slug);
				
				$Episodes = powerpress_admin_episodes_per_feed($delete_slug);
				
				if( $delete_slug == 'podcast' && $force_deletion == false )
				{
					powerpress_page_message_add_error( __('Cannot delete default podcast feed.') );
				}
				else if( $Episodes > 0 && $force_deletion == false )
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
			case 'powerpress-delete-category-feed': {
				$cat_ID = $_GET['cat'];
				check_admin_referer('powerpress-delete-category-feed-'.$cat_ID);
				
				$Settings = get_option('powerpress_general');
				$key = array_search($cat_ID, $Settings['custom_cat_feeds']);
				if( $key !== false )
				{
					unset( $Settings['custom_cat_feeds'][$key] );
					powerpress_save_settings($Settings); // Delete the feed from the general settings
				}
				delete_option('powerpress_cat_feed_'.$cat_ID); // Delete the actual feed settings
				
				powerpress_page_message_add_notice( 'Removed podcast settings for category feed successfully.' );
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
					if( $user == 'administrator' && !$role->has_cap('view_podcast_stats') )
						$role->add_cap('view_podcast_stats');
				}
				
				$General = array('use_caps'=>true);
				powerpress_save_settings($General);
				powerpress_page_message_add_notice( __('PowerPress Roles and Capabilities added to WordPress Blog.') );
				
			}; break;
			case 'powerpress-remove-caps': {
				check_admin_referer('powerpress-remove-caps');
				
				$users = array('administrator','editor', 'author', 'contributor', 'subscriber');
				while( list($null,$user) = each($users) )
				{
					$role = get_role($user);
					if( $role->has_cap('edit_podcast') )
						$role->remove_cap('edit_podcast');
					if( $role->has_cap('view_podcast_stats') )
						$role->remove_cap('view_podcast_stats');
				}
				$General = array('use_caps'=>false);
				powerpress_save_settings($General);
				powerpress_page_message_add_notice( __('PowerPress Roles and Capabilities removed from WordPress Blog') );
				
			}; break;
			case 'powerpress-add-feed-caps': {
				check_admin_referer('powerpress-add-feed-caps');
				
				$ps_role = get_role('premium_subscriber');
				if(!$ps_role)
				{
					add_role('premium_subscriber', 'Premium Subscriber', $caps);
					$ps_role = get_role('premium_subscriber');
					$ps_role->add_cap('read');
					$ps_role->add_cap('premium_content');
				}
				
				$users = array('administrator','editor', 'author'); // , 'contributor', 'subscriber');
				while( list($null,$user) = each($users) )
				{
					$role = get_role($user);
					if( !$role->has_cap('premium_content') )
						$role->add_cap('premium_content');
				}
				
				$General = array('premium_caps'=>true);
				powerpress_save_settings($General);
				powerpress_page_message_add_notice( __('Podcast Password Protection Capabilities for Custom Channel Feeds added successfully.') );
				
			}; break;
			case 'powerpress-remove-feed-caps': {
				check_admin_referer('powerpress-remove-feed-caps');
				
				$users = array('administrator','editor', 'author', 'contributor', 'subscriber', 'premium_subscriber');
				while( list($null,$user) = each($users) )
				{
					$role = get_role($user);
					if( $role->has_cap('premium_content') )
						$role->remove_cap('premium_content');
				}
				
				remove_role('premium_subscriber');
				
				$General = array('premium_caps'=>false);
				powerpress_save_settings($General);
				powerpress_page_message_add_notice( __('Podcast Password Protection Capabilities for Custom Channel Feeds removed successfully.') );
				
			}; break;
			case 'powerpress-clear-update_plugins': {
				check_admin_referer('powerpress-clear-update_plugins');
				
				delete_option('update_plugins');
				powerpress_page_message_add_notice( __('Plugins Update Cache cleared successfully. You may now to go the <a href="'. admin_url() .'plugins.php" title="Manage Plugins">Manage Plugins</a> page to see the latest plugin versions.') );
				
			}; break;
		}
	}
	
	// Handle edit from category page
	if( isset($_POST['from_categories']) )
	{
		wp_redirect('categories.php?message=3');
		exit;
	}
	
	$GeneralSettings = get_option('powerpress_general');
	if( @$GeneralSettings['player_options'] )
	{
		// Make sure we include the player-options
		require_once( dirname(__FILE__).'/powerpressadmin-player.php');
		powerpress_admin_players_init();
	}
}

add_action('init', 'powerpress_admin_init');

function powerpress_admin_notices()
{
	$errors = get_option('powerpress_errors');
	if( $errors )
	{
		delete_option('powerpress_errors');
		
		while( list($null, $error) = each($errors) )
		{
?>
<div class="updated"><p style="line-height: 125%;"><strong><?php echo $error; ?></strong></p></div>
<?php
		}
	}
}

add_action( 'admin_notices', 'powerpress_admin_notices' );

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
		if( $field == 'powerpress_general' && !isset($Settings['timestamp']) )
			$Settings['timestamp'] = time();
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
	
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
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
		$Powerpress = powerpress_default_settings($Powerpress, 'basic');
		
		add_menu_page(__('PowerPress'), __('PowerPress'), 1, 'powerpress/powerpressadmin_basic.php', 'powerpress_admin_page_basic', powerpress_get_root_url() . 'powerpress_ico.png');
			add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Settings'), __('Settings'), 1, 'powerpress/powerpressadmin_basic.php', 'powerpress_admin_page_basic' );
			if( @$Powerpress['player_options'] )
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Audio Player Options'), __('Audio Player'), 1, 'powerpress/powerpressadmin_player.php', 'powerpress_admin_page_players');
			
			if( $Powerpress['channels'] )
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Custom Podcast Channels'), __('Podcast Channels'), 1, 'powerpress/powerpressadmin_customfeeds.php', 'powerpress_admin_page_customfeeds');
			if( $Powerpress['cat_casting'] )	
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Category Podcasting'), __('Category Podcasting'), 1, 'powerpress/powerpressadmin_categoryfeeds.php', 'powerpress_admin_page_categoryfeeds');
			if( @$Powerpress['podpress_stats'] )
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PodPress Stats'), __('PodPress Stats'), 1, 'powerpress/powerpressadmin_podpress-stats.php', 'powerpress_admin_page_podpress_stats');
			
			
			if( isset($Powerpress['blubrry_hosting']) && $Powerpress['blubrry_hosting'] )
				add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress MP3 Tags'), __('MP3 Tags'), 1, 'powerpress/powerpressadmin_tags.php', 'powerpress_admin_page_tags');
			add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Tools'), __('Tools'), 1, 'powerpress/powerpressadmin_tools.php', 'powerpress_admin_page_tools');
	}
}


add_action('admin_menu', 'powerpress_admin_menu');



// Save episode information
function powerpress_edit_post($post_ID, $post)
{
	if ( !current_user_can('edit_post', $post_ID) )
		return $postID;
		
	$GeneralSettings = get_option('powerpress_general');
	
	if( isset($GeneralSettings['auto_enclose']) && $GeneralSettings['auto_enclose'] )
	{
		powerpress_do_enclose($post->post_content, $post_ID, ($GeneralSettings['auto_enclose']==2) );
	}
		
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
				if( defined('POWERPRESS_ENABLE_HTTPS_MEDIA') )
				{
					if( strpos($MediaURL, 'http://') !== 0 && strpos($MediaURL, 'https://') !== 0 && $Powerpress['hosting'] != 1 ) // If the url entered does not start with a http:// or https://
					{
						$MediaURL = rtrim(@$GeneralSettings['default_url'], '/') .'/'. $MediaURL;
					}
				}
				else
				{
					if( strpos($MediaURL, 'http://') !== 0 && $Powerpress['hosting'] != 1 ) // If the url entered does not start with a http://
					{
						$MediaURL = rtrim(@$GeneralSettings['default_url'], '/') .'/'. $MediaURL;
					}
				}
				
				$FileSize = '';
				$ContentType = '';
				$Duration = false;
				if( $Powerpress['set_duration'] == 0 )
					$Duration = ''; // allow the duration to be detected

				// Get the content type based on the file extension, first we have to remove query string if it exists
				$UrlParts = parse_url($Powerpress['url']);
				if( $UrlParts['path'] )
				{
					// using functions that already exist in WordPress when possible:
					$ContentType = powerpress_get_contenttype($UrlParts['path']);
				}

				if( !$ContentType )
				{
					$error = __('Error') ." [{$Powerpress['url']}]: " .__('Unable to determine content type of media (e.g. audio/mpeg). Verify file extension is correct and try again.');
					powerpress_add_error($error);
					continue;
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
					if( $Powerpress['hosting'] == 1 )
					{
						if( @$Powerpress['set_size'] == 0 || @$Powerpress['set_duration'] == 0 )
						{
							$MediaInfo = powerpress_get_media_info($Powerpress['url']);
							if( !isset($MediaInfo['error']) )
							{
								if( @$Powerpress['set_size'] == 0 )
									$FileSize = $MediaInfo['length'];
								if( @$Powerpress['set_duration'] == 0 )
									$Duration = powerpress_readable_duration($MediaInfo['duration'], true);
							}
							else
							{
								$error = __('Error') ." ({$Powerpress['url']}): {$MediaInfo['error']}";
								powerpress_add_error($error);
								continue;
							}
						}
					}
					else
					{
						$MediaInfo = powerpress_get_media_info_local($MediaURL, $ContentType, 0, $Duration);
						if( isset($MediaInfo['error']) )
						{
							$error = __('Error') ." ({$MediaURL}): {$MediaInfo['error']}";
							powerpress_add_error($error);
							continue;
						}
						else if( empty($MediaInfo['length']) )
							{
							$error = __('Error') ." ({$MediaURL}): ". __('Unable to obtain size of media.');
							powerpress_add_error($error);
							continue;
								}
						else
								{
							// Detect the duration
							if( $Powerpress['set_duration'] == 0 && $MediaInfo['duration'] )
								$Duration = powerpress_readable_duration($MediaInfo['duration'], true); // Fix so it looks better when viewed for editing
						
							// Detect the file size
							if( $Powerpress['set_size'] == 0 && $MediaInfo['length'] > 0 )
								$FileSize = $MediaInfo['length'];
						}
					}
				}
				
				// If we made if this far, we have the content type and file size...
				$EnclosureData = $MediaURL . "\n" . $FileSize . "\n". $ContentType;	
				$ToSerialize = array();
				// iTunes duration
				if( $Powerpress['hosting'] )
					$ToSerialize['hosting'] = 1;
				if( $Duration )
					$ToSerialize['duration'] = $Duration; // regular expression '/^(\d{1,2}\:)?\d{1,2}\:\d\d$/i' (examples: 1:23, 12:34, 1:23:45, 12:34:56)
				// iTunes Subtitle (FUTURE USE)
				if( isset($Powerpress['subtitle']) && trim($Powerpress['subtitle']) != '' ) 
					$ToSerialize['subtitle'] = stripslashes($Powerpress['subtitle']);
				// iTunes Summary (FUTURE USE)
				if( isset($Powerpress['summary']) && trim($Powerpress['summary']) != '' ) 
					$ToSerialize['summary'] = stripslashes($Powerpress['summary']);
				// iTunes keywords (FUTURE USE)
				if( isset($Powerpress['keywords']) && trim($Powerpress['keywords']) != '' ) 
					$ToSerialize['keywords'] = stripslashes($Powerpress['keywords']);
				// iTunes Author (FUTURE USE)
				if( isset($Powerpress['author']) && trim($Powerpress['author']) != '' ) 
					$ToSerialize['author'] = stripslashes($Powerpress['author']);
				// iTunes Explicit (FUTURE USE)
				if( isset($Powerpress['explicit']) && trim($Powerpress['explicit']) != '' ) 
					$ToSerialize['explicit'] = $Powerpress['explicit'];
				// iTunes Block (FUTURE USE)
				if( isset($Powerpress['block']) && (trim($Powerpress['block']) == 'yes' || trim($Powerpress['block']) == 'no') ) 
					$ToSerialize['block'] = ($Powerpress['block']=='yes'?'yes':'');
				// Player Embed
				if( isset($Powerpress['embed']) && trim($Powerpress['embed']) != '' )
					$ToSerialize['embed'] = stripslashes($Powerpress['embed']); // we have to strip slahes if they are present befure we serialize the data
				if( isset($Powerpress['no_player']) && $Powerpress['no_player'] )
					$ToSerialize['no_player'] = 1;
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
		} // Loop through posted episodes...
	}
	
	// Anytime the post is marked published, private or scheduled for the future we need to make sure we're making the media available for hosting
	if( $post->post_status == 'publish' || $post->post_status == 'private' || $post->post_status == 'future' )
	{
		if( isset($GeneralSettings['blubrry_hosting']) && $GeneralSettings['blubrry_hosting'] )
			powerpress_process_hosting($post_ID, $post->post_title); // Call anytime blog post is in the published state
	}
		
	// And we're done!
}

add_action('edit_post', 'powerpress_edit_post', 10, 2);

if( defined('POWERPRESS_DO_ENCLOSE_FIX') )
{
	function powerpress_insert_post_data($data, $postarr)
	{
		// If we added or modified a podcast episode, then we need to re-add/remove the embedded hidden link...
		if( isset($_POST['Powerpress']['podcast']) && $postarr['post_type'] == 'post' )
		{
			// First, remove the previous comment if one exists in the post body.
			$data['post_content'] = preg_replace('/\<!--.*added by PowerPress.*-->/im', '', $data['post_content']);
			
			$Powerpress = $_POST['Powerpress']['podcast'];
			if( @$Powerpress['remove_podcast'] == 1 )
			{
				// Do nothing
			}
			else if( @$Powerpress['change_podcast'] == 1 || @$Powerpress['new_podcast'] == 1 )
			{
				$MediaURL = $Powerpress['url'];
				if( strpos($MediaURL, 'http://') !== 0 && strpos($MediaURL, 'https://') !== 0 && $Powerpress['hosting'] != 1 ) // If the url entered does not start with a http:// or https://
				{
					// Only glitch here is if the media url had an error, and if that's the case then there are other issues the user needs to worry about.
					$GeneralSettings = get_option('powerpress_general');
					if( $GeneralSettings && isset($GeneralSettings['default_url']) )
						$MediaURL = rtrim(@$GeneralSettings['default_url'], '/') .'/'. ltrim($MediaURL, '/');
				}
					
				$data['post_content'] .= "<!-- DO NOT DELETE href=\"$MediaURL\" added by PowerPress to fix WordPress 2.8+ bug -->";
			}
			else
			{
				$EncloseData = powerpress_get_enclosure_data($postarr['ID']);
				if( $EncloseData && $EncloseData['url'] )
					$data['post_content'] .= "<!-- DO NOT DELETE href=\"{$EncloseData['url']}\" added by PowerPress to fix WordPress 2.8+ bug -->";
			}
		}
		
		return $data;
	}
	add_filter('wp_insert_post_data', 'powerpress_insert_post_data',1,2);
}

// Do the iTunes pinging here...
function powerpress_publish_post($post_id)
{
	// Delete scheduled _encloseme requests...
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_encloseme' ");
	
	$GeneralSettings = get_option('powerpress_general');
	if( isset($GeneralSettings['auto_enclose']) && $GeneralSettings['auto_enclose'] )
	{
		$post = &get_post($post_id);
		powerpress_do_enclose($post->post_content, $post_id, ($GeneralSettings['auto_enclose']==2) );
	}
	
	if( isset($GeneralSettings['ping_itunes']) && $GeneralSettings['ping_itunes'] )
	{
		powerpress_do_ping_itunes($post_id);
	}
}

add_action('publish_post', 'powerpress_publish_post');

// Admin page, html meta header
function powerpress_admin_head()
{
	global $parent_file, $hook_suffix;
	$page_name = '';
	if ( isset($parent_file) && !empty($parent_file) )
		$page_name = substr($parent_file, 0, -4);
	else
		$page_name = str_replace(array('.php', '-new', '-add'), '', $hook_suffix);
			
	// Powerpress page
	if( strstr($_GET['page'], 'powerpress' ) !== false )
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

jQuery(document).ready(function($) {

	jQuery("#powerpress_settings_page").tabs({ cookie: { expires: 30 } });
	
});
	
</script>
<link rel="stylesheet" href="<?php echo powerpress_get_root_url(); ?>css/admin.css" type="text/css" media="screen" />
<?php
	}
	else if( $page_name == 'edit' || $page_name == 'edit-pages' ) // || $page_name == '' ) // we don't know the page, we better include our CSS just in case
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
	border-width: 1px;
	border-style: solid;
	font-weight: bold;
	text-align: center;
}
.powerpress_podcast_box  .success {
	margin-top: 10px;
	margin-bottom: 10px;
	padding: 5px;
	border-width: 1px;
	border-style: solid;
	font-weight: bold;
	text-align: center;
	border-width: 1px;
	border-style: solid;
	-moz-border-radius: 3px;
	-khtml-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	border-color: #009900;
	background-color: #CCFFCC;
	font-size: 12px;
	position: relative;
}
.powerpress_podcast_box  .success a.close {
	position: absolute;
	top: 2px;
	right: 2px;
	text-align: right;
	color: #993366;
	text-decoration: none;
}
.powerpress_podcast_box  .updated {
	margin-top: 10px;
	margin-bottom: 10px;
	padding: 5px;
	font-size: 12px;
	border-width: 1px;
	border-style: solid;
	font-weight: bold;
	text-align: center;
}
.powerpress_podcast_box input[type="button"] {
	height: 20px;
	vertical-align: bottom;
	font-size: 90%;
}
</style>
<script language="javascript">

function powerpress_check_url(url)
{
	var DestDiv = 'powerpress_warning';
	if( powerpress_check_url.arguments.length > 1 )
		DestDiv = powerpress_check_url.arguments[1];
	
	jQuery( '#'+DestDiv ).addClass("error");
	jQuery( '#'+DestDiv ).removeClass("updated");
			
	var validChars = ':0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/-_.';

	for( var x = 0; x < url.length; x++ )
	{
		if( validChars.indexOf( url.charAt(x) ) == -1 )
		{
			jQuery( '#'+DestDiv ).text('Media URL contains characters that may cause problems for some clients. For maximum compatibility, only use letters, numbers, dash - and underscore _ characters only.');
			jQuery( '#'+DestDiv ).css('display', 'block');
			return false;
		}
	
		if( x == 5 )
			validChars = validChars.substring(1); // remove the colon, should no longer appear in URLs
	}
	
<?php
	if( !defined('POWERPRESS_ENABLE_HTTPS_MEDIA') )
	{
?>
    if( url.charAt(0) == 'h' && url.charAt(1) == 't' && url.charAt(2) == 't' && url.charAt(3) == 'p' && url.charAt(4) == 's' )
    {
        jQuery( '#'+DestDiv ).html('PowerPress will not accept media URLs starting with https://.<br />Not all podcatching (podcast downloading) applications support secure http.<br />Please enter a different URL beginning with http://.');
				jQuery( '#'+DestDiv ).css('display', 'block');
        return false;
    }
<?php
	} else if( POWERPRESS_ENABLE_HTTPS_MEDIA === 'warning' ) {
?>
    if( url.charAt(0) == 'h' && url.charAt(1) == 't' && url.charAt(2) == 't' && url.charAt(3) == 'p' && url.charAt(4) == 's' )
    {
        jQuery( '#'+DestDiv ).html('Media URL should not start with https://.<br />Not all podcatching (podcast downloading) applications support secure http.<br />By using https://, you may limit the size of your audience.');
        jQuery( '#'+DestDiv ).css('display', 'block');
        return false;
    }
<?php
	}
?>

	jQuery( '#'+DestDiv ).css('display', 'none');
	return true;
}

function powerpress_get_media_info(FeedSlug)
{
	if( jQuery('#powerpress_check_'+FeedSlug).css("display") != "none" )
		return; // Another process is already running

	jQuery( '#powerpress_success_'+FeedSlug ).css('display', 'none');
	jQuery( '#powerpress_warning_'+FeedSlug ).text('');
	jQuery( '#powerpress_warning_'+FeedSlug ).css('display', 'none');
	jQuery( '#powerpress_warning_'+FeedSlug ).addClass("error");
	jQuery( '#powerpress_warning_'+FeedSlug ).removeClass("updated");
	
	var Value = jQuery('#powerpress_url_'+FeedSlug).val();
	var Hosting = jQuery('#powerpress_hosting_'+FeedSlug).val();
	if( Value )
	{
		if( powerpress_check_url(Value, 'powerpress_warning_'+FeedSlug ) )
		{
			jQuery('#powerpress_check_'+FeedSlug).css("display", 'inline');
			jQuery.ajax( {
				type: 'POST',
				url: '<?php echo admin_url(); ?>admin-ajax.php', 
				data: { action: 'powerpress_media_info', media_url : Value, feed_slug : encodeURIComponent(FeedSlug), hosting: Hosting },
				timeout: (30 * 1000),
				success: function(response) {
					
					var Parts = response.split("\n", 5);
					var FeedSlug = Parts[0];
					
					jQuery('#powerpress_check_'+FeedSlug).css("display", 'none');
					
					if( Parts[1] == 'OK' )
					{
						jQuery('#powerpress_set_size_1_'+FeedSlug).attr('checked', true);
						jQuery('#powerpress_size_'+FeedSlug).val( Parts[2] );
						if( Parts[3] )
						{
							jQuery('#powerpress_set_duration_1_'+FeedSlug).attr('checked', true);
							var Duration = Parts[3].split(':');
							jQuery('#powerpress_duration_hh_'+FeedSlug).val( Duration[0] );
							jQuery('#powerpress_duration_mm_'+FeedSlug).val( Duration[1] );
							jQuery('#powerpress_duration_ss_'+FeedSlug).val( Duration[2] );
						}
						else if( jQuery('#powerpress_set_duration_0_'+FeedSlug).attr('checked') )
						{
							jQuery('#powerpress_set_duration_2_'+FeedSlug).attr('checked', true);
							jQuery('#powerpress_duration_hh_'+FeedSlug).val( '' );
							jQuery('#powerpress_duration_mm_'+FeedSlug).val( '' );
							jQuery('#powerpress_duration_ss_'+FeedSlug).val( '' );
						}
						
						if( Parts.length > 4 && Parts[4] != '' )
						{
							jQuery( '#powerpress_warning_'+FeedSlug ).html( Parts[4] );
							jQuery( '#powerpress_warning_'+FeedSlug ).css('display', 'block');
							jQuery( '#powerpress_warning_'+FeedSlug ).addClass("updated");
							jQuery( '#powerpress_warning_'+FeedSlug ).removeClass("error");
						}
						else
						{
							jQuery( '#powerpress_success_'+FeedSlug ).html( 'Media verified successfully. <a href="#" onclick="jQuery( \'#powerpress_success_'+ FeedSlug +'\' ).fadeOut(1000);return false;" title="Close" class="close">X<\/a>' );
							jQuery( '#powerpress_success_'+FeedSlug ).css('display', 'block');
							// setTimeout( function() { jQuery( '#powerpress_success_'+FeedSlug ).fadeOut(1000); }, 10000 );
						}
					}
					else
					{
						var Parts = response.split("\n", 2);
						if( Parts[1] )
							jQuery( '#powerpress_warning_'+FeedSlug ).html( Parts[1] );
						else
							jQuery( '#powerpress_warning_'+FeedSlug ).text( 'Unknown error occurred while checking Media URL.' );
						jQuery( '#powerpress_warning_'+FeedSlug ).css('display', 'block');
					}
				},
				error: function(objAJAXRequest, strError) {
						
					jQuery('#powerpress_check_'+FeedSlug).css("display", 'none');
					if( strError == 'timeout' )
						jQuery( '#powerpress_warning_'+FeedSlug ).text( 'Operation timed out.' );
					else
						jQuery( '#powerpress_warning_'+FeedSlug ).text( 'Unknown error occurred.' );
					jQuery( '#powerpress_warning_'+FeedSlug ).css('display', 'block');
				}
			});
		}
	}
}

</script>
<?php
	}
	else
	{
		// Print this line for debugging when loooking for other pages to include header data for
		//echo "<!-- WP Page Name: $page_name; Hook Suffix: $hook_suffix -->\n";
	}
}

add_action('admin_head', 'powerpress_admin_head');


function powerpress_media_info_ajax()
{
	$feed_slug = $_POST['feed_slug'];
	$media_url = $_POST['media_url'];
	$hosting = $_POST['hosting'];
	$size = 0;
	$duration = '';
	$status = 'OK';
	$GeneralSettings = get_option('powerpress_general');

	if( defined('POWERPRESS_ENABLE_HTTPS_MEDIA') )
	{
		if( strpos($media_url, 'http://') !== 0 && strpos($media_url, 'https://') !== 0 && $hosting != 1 ) // If the url entered does not start with a http:// or https://
		{
			$media_url = rtrim(@$GeneralSettings['default_url'], '/') .'/'. $media_url;
		}
	}
	else
	{
		if( strpos($media_url, 'http://') !== 0 && $hosting != 1 ) // If the url entered does not start with a http://
		{
			$media_url = rtrim(@$GeneralSettings['default_url'], '/') .'/'. $media_url;
		}
	}
	
	// Get media info here...
	if( $hosting )
		$MediaInfo = powerpress_get_media_info($media_url);
	else
		$MediaInfo = powerpress_get_media_info_local($media_url, '', 0, '', true);
	
	if( !isset($MediaInfo['error']) && !empty($MediaInfo['length']) )
	{
		echo "$feed_slug\n";
		echo "OK\n";
		echo "{$MediaInfo['length']}\n";
		echo "{$MediaInfo['duration']}\n";
		if( isset($MediaInfo['warnings']) )
			echo $MediaInfo['warnings'];
		exit;
	}
	
	echo "$feed_slug\n";
	if( $MediaInfo['error'] )
		echo $MediaInfo['error'];
	else
		echo 'Unknown error occurred looking up media information.';
	exit;
}
 
add_action('wp_ajax_powerpress_media_info', 'powerpress_media_info_ajax');


function powerpress_cat_row_actions($actions, $category)
{
	$General = get_option('powerpress_general');
	if( !isset($General['cat_casting']) || $General['cat_casting'] == 0 )
		return $actions;
		
	if( isset($General['custom_cat_feeds']) && is_array($General['custom_cat_feeds']) && in_array($category->cat_ID, $General['custom_cat_feeds']) )
	{
		$edit_link = admin_url('admin.php?page=powerpress/powerpressadmin_categoryfeeds.php&amp;from_categories=1&amp;action=powerpress-editcategoryfeed&amp;cat=') . $category->cat_ID;
		$actions['powerpress'] = '<a href="' . $edit_link . '" title="'. _('Edit Blubrry PowerPress Podcast Settings') .'">' . __('Podcast&nbsp;Settings') . '</a>';
	}
	else
	{
		$edit_link = admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_categoryfeeds.php&amp;from_categories=1&amp;action=powerpress-addcategoryfeed&amp;cat=".$category->cat_ID, 'powerpress-add-category-feed');
		$actions['powerpress'] = '<a href="' . $edit_link . '" title="'. _('Add Blubrry PowerPress Podcasting Settings') .'">' . __('Add&nbsp;Podcasting') . '</a>';
	}
	return $actions;
}

add_filter('cat_row_actions', 'powerpress_cat_row_actions', 1,2);

function powerpress_delete_category($cat_ID)
{
	$Settings = get_option('powerpress_general');
	$key = array_search($cat_ID, $Settings['custom_cat_feeds']);
	if( $key !== false )
	{
		unset( $Settings['custom_cat_feeds'][$key] );
		powerpress_save_settings($Settings); // Delete the feed from the general settings
	}
	delete_option('powerpress_cat_feed_'.$cat_ID); // Delete the actual feed settings
}

add_action('delete_category', 'powerpress_delete_category');


function powerpress_edit_category_form($cat)
{
	if( empty($cat) || !isset( $cat->cat_ID ) )
	{
?>
<div>
<?php
		$General = get_option('powerpress_general');
		if( !isset($General['cat_casting']) || $General['cat_casting'] == 0 )
		{
			$enable_link = admin_url() . wp_nonce_url('categories.php?action=powerpress-enable-categorypodcasting', 'powerpress-enable-categorypodcasting');
?>
	<h2>PowerPress Category Podcasting</h2>
	<p><a href="<?php echo $enable_link; ?>" title="Enable Category Podcasting">Enable Category Podcasting</a> if you would like to add specific podcasting settings to your blog categories.</p>
<?php
		}
		else
		{
?>
	<h2>PowerPress Category Podcasting</h2>
	<p>PowerPress Category Podcasting is enabled. Select <u>Add Podcasting</u> to add podcasting settings. Select <u>Podcast Settings</u> to edit existing podcast settings.</p>
<?php
		}
?>
</div>
<?php
	}
}
add_action('edit_category_form', 'powerpress_edit_category_form');

// Admin page, header
function powerpress_admin_page_header($page=false, $nonce_field = 'powerpress-edit', $simple_mode=false)
{
	if( !$page )
		$page = 'powerpress/powerpressadmin_basic.php';
?>
<div class="wrap" id="powerpress_settings">
<?php
	if( $nonce_field )
	{
?>
<form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'admin.php?page='.$page) ?>">
<?php
		wp_nonce_field($nonce_field);
	}
	
	powerpress_page_message_print();
}

// Admin page, footer
function powerpress_admin_page_footer($SaveButton=true, $form=true)
{
	if( $SaveButton ) { ?>
<p class="submit">
<input type="submit" name="Submit" id="powerpress_save_button" class="button-primary" value="<?php _e('Save Changes' ) ?>" />
</p>
<?php } ?>
<p style="font-size: 85%; text-align: center; padding-bottom: 25px;">
	<a href="http://www.blubrry.com/powerpress/" title="Blubrry PowerPress" target="_blank">Blubrry PowerPress</a> <?php echo POWERPRESS_VERSION; ?>
	&#8212; <a href="http://help.blubrry.com/blubrry-powerpress/" target="_blank" title="Blubrry PowerPress Documentation">Documentation</a> |
	<a href="http://forum.blubrry.com/" target="_blank" title="Blubrry Forum">Forum</a> |
	<a href="http://twitter.com/blubrry" target="_blank" title="Follow Blubrry on Twitter">Follow Blubrry on Twitter</a>
</p>
<?php if( $form ) { ?>
</form><?php } ?>
</div>
<?php 
}

// Admin page, advanced mode: basic settings
function powerpress_admin_page_basic()
{
	$Settings = get_option('powerpress_general');
	
	if( !isset($Settings['advanced_mode']) )
	{
		powerpress_admin_page_header(false,  'powerpress-edit', true);
		require_once( dirname(__FILE__).'/powerpressadmin-mode.php');
		powerpress_admin_mode();
		powerpress_admin_page_footer(false);
		return;
	}
	
	powerpress_admin_page_header();
	require_once( dirname(__FILE__).'/powerpressadmin-basic.php');
	require_once( dirname(__FILE__).'/powerpressadmin-appearance.php');
	require_once( dirname(__FILE__).'/powerpressadmin-editfeed.php');
	powerpress_admin_basic();
	powerpress_admin_page_footer(true);
}

// Admin page, advanced mode: basic settings
function powerpress_admin_page_players()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_player.php');
	require_once( dirname(__FILE__).'/powerpressadmin-player.php');
	powerpress_admin_page_player();
	powerpress_admin_page_footer(true);
}

// Admin page, advanced mode: feed settings
function powerpress_admin_page_podpress_stats()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_podpress-stats.php');
	require_once( dirname(__FILE__).'/powerpressadmin-podpress-stats.php');
	powerpress_admin_podpress_stats();
	powerpress_admin_page_footer(false);
}

// Admin page, advanced mode: feed settings
function powerpress_admin_page_tags()
{
	powerpress_admin_page_header('powerpress/powerpressadmin_tags.php');
	require_once( dirname(__FILE__).'/powerpressadmin-tags.php');
	powerpress_admin_tags();
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
			require_once( dirname(__FILE__).'/powerpressadmin-basic.php');
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

// Category feeds
function powerpress_admin_page_categoryfeeds()
{
	switch( @$_GET['action'] )
	{
		case 'powerpress-editcategoryfeed' : {
			powerpress_admin_page_header('powerpress/powerpressadmin_categoryfeeds.php');
			require_once( dirname(__FILE__).'/powerpressadmin-editfeed.php');
			require_once( dirname(__FILE__).'/powerpressadmin-basic.php');
			powerpress_admin_editfeed(false, $_GET['cat']);
			powerpress_admin_page_footer();
		}; break;
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_categoryfeeds.php', 'powerpress-add-categoryfeed');
			require_once( dirname(__FILE__).'/powerpressadmin-categoryfeeds.php');
			powerpress_admin_categoryfeeds();
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
		case 'powerpress-mt-epiosdes': {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', 'powerpress-import-mt');
			require_once( dirname(__FILE__).'/powerpressadmin-mt.php');
			powerpress_admin_mt();
			powerpress_admin_page_footer(false);
		}; break;
		case 'powerpress-ping-sites': {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', 'powerpress-ping-sites');
			require_once( dirname(__FILE__).'/powerpressadmin-ping-sites.php');
			powerpress_admin_ping_sites();
			powerpress_admin_page_footer(false);
		}; break;
		case 'powerpress-diagnostics': {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', false);
			require_once( dirname(__FILE__).'/powerpressadmin-diagnostics.php');
			powerpressadmin_diagnostics();
			powerpress_admin_page_footer(false, false);
		}; break;
		default: {
			powerpress_admin_page_header('powerpress/powerpressadmin_tools.php', false);
			require_once( dirname(__FILE__).'/powerpressadmin-tools.php');
			powerpress_admin_tools();
			powerpress_admin_page_footer(false, false);
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

function powerpress_podpress_stats_exist()
{
	global $wpdb;
	// First, see if the table exists...
	$query = "SHOW TABLES LIKE '{$wpdb->prefix}podpress_statcounts'";
	$wpdb->hide_errors();
	$results = $wpdb->get_results($query, ARRAY_A);
	$wpdb->show_errors();
	if( count($results) == 0 )
		return false;
	
	// Now see if a record exists...
	$query = "SELECT `media` ";
	$query .= "FROM {$wpdb->prefix}podpress_statcounts ";
	$query .= "LIMIT 1";
	$results = $wpdb->get_results($query, ARRAY_A);
	if( count($results) )
		return true;
	return false;
}

/*
// Helper functions:
*/
function powerpress_do_ping_itunes($post_id)
{
	$Settings = get_option('powerpress_general');
	if( isset($Settings['ping_itunes']) && $Settings['ping_itunes'] )
	{
		$Enclosure = get_post_meta($post_id, 'enclosure', true);
		if( $Enclosure )
		{
			if( $Settings['ping_itunes'] && $Settings['itunes_url'] )
			{
				$PingResults = powerpress_ping_itunes($Settings['itunes_url']);
				powerpress_ping_itunes_log($PingResults, $post_id);
			}
		}
	}
	
	// Category feeds
	if( isset($Settings['custom_cat_feeds']) )
	{
		$post_categories = wp_get_post_categories($post_id);
		while( list($null, $cat_id) = each($post_categories) )
		{
			if( !in_array($cat_id, $Settings['custom_cat_feeds']) )
				continue; // This isn't a podcast category, so skip it...
			
			$FeedSettings = get_option('powerpress_cat_feed_'.$cat_id);
			if( $FeedSettings && @$FeedSettings['ping_itunes'] && $FeedSettings['itunes_url'] )
			{
				$PingResults = powerpress_ping_itunes($FeedSettings['itunes_url']);
				powerpress_ping_itunes_log($PingResults, $post_id);
			}
		}
	}
	
	// Custom Podcast Channels
	if( isset($Settings['custom_feeds']) )
	{
		while( list($feed_slug,$null) = each($Settings['custom_feeds']) )
		{
			if( $feed_slug == 'podcast' )
				continue;
			
			// Next double check we're looking at a podcast episode...
			$Enclosure = get_post_meta($post_id, '_'.$feed_slug.':enclosure', true);
			if( $Enclosure )
			{
				$FeedSettings = get_option('powerpress_feed_'.$feed_slug);
				if( $FeedSettings && @$FeedSettings['ping_itunes'] && $FeedSettings['itunes_url'] )
				{
					$PingResults = powerpress_ping_itunes($FeedSettings['itunes_url']);
					powerpress_ping_itunes_log($PingResults, $post_id);
				}
			}
		}
	}
}

function powerpress_ping_itunes($iTunes_url)
{
	// Pull the iTunes FEEDID from the URL...
	if( !preg_match('/id=(\d+)/', $iTunes_url, $matches) )
		return array('error'=>true, 'content'=>'iTunes URL required to ping iTunes.', 'podcast_id'=>0 );
	
	$FEEDID = $matches[1];
	
	// convert: https://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=
	// to: https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=
	$ping_url = sprintf('https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=%d', $FEEDID );
	
	//$tempdata = wp_remote_fopen($ping_url);
	$tempdata = powerpress_remote_fopen($ping_url);
	
	if( $tempdata == false )
	{
		// Simetimes this happens, lets try again...
		sleep(1); // wait just a second :)
		$tempdata = powerpress_remote_fopen($ping_url);
		if( $tempdata == false )
			return array('error'=>true, 'content'=>'Unable to connect to iTunes ping server.', 'podcast_id'=>trim($PodcastID));
	}
	
	if( stristr($tempdata, 'No Podcast Found') )
		return array('error'=>true, 'content'=>'No Podcast Found from iTunes ping request.', 'podcast_id'=>trim($PodcastID));
		
	// Parse the data into something readable
	$results = trim( str_replace('Podcast Ping Received', '', strip_tags($tempdata) ) );
	list($null, $FeedURL, $null, $null, $null, $PodcastID) = split("\n", $results );
	
	return array('success'=>true, 'content'=>$tempdata, 'feed_url'=>trim($FeedURL), 'podcast_id'=>trim($PodcastID) );
}

function powerpress_ping_itunes_log($Data, $post_id = 0)
{
	if( $Data['podcast_id'] )
	{
		$Log = array();
		$Log['timestamp'] = time();
		$Log['feed_url'] = $Data['feed_url'];
		$Log['success'] = (isset($Data['error'])?0:1);
		$Log['content'] = $Data['content'];
		$Log['post_id'] = $post_id;
		$Save = array();
		$Save['itunes_ping_'.$Data['podcast_id'] ] = $Log;
		powerpress_save_settings($Save, 'powerpress_log');
	}
}

function powerpress_remote_fopen($url, $basic_auth = false, $post_args = array(), $timeout = 10, $custom_request = false )
{
	if( function_exists( 'curl_init' ) ) // Preferred method of connecting
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		if ( !ini_get('safe_mode') && !ini_get('open_basedir') )
		{
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // Follow location redirection
			curl_setopt($curl, CURLOPT_MAXREDIRS, 12); // Location redirection limit
		}
		else
		{
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($curl, CURLOPT_MAXREDIRS, 0 );
		}

		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2 ); // Connect time out
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // The maximum number of seconds to execute.
		curl_setopt($curl, CURLOPT_USERAGENT, 'Blubrry PowerPress/'.POWERPRESS_VERSION);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);
		if( strtolower(substr($url, 0, 5)) == 'https' )
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		}
		// HTTP Authentication
		if( $basic_auth )
		{
			curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$basic_auth) );
		}
		// HTTP Post:
		if( count($post_args) > 0 )
		{
			$post_query = '';
			while( list($name,$value) = each($post_args) )
			{
				if( $post_query != '' )
					$post_query .= '&';
				$post_query .= $name;
				$post_query .= '=';
				$post_query .= urlencode($value);
			}
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_query);
		}
		else if( $custom_request )
		{
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $custom_request);
		}
		
		$content = curl_exec($curl);
		$error = curl_errno($curl);
		curl_close($curl);
		if( $error )
		{
			global $g_powerpress_remote_error;
			return false;
		}
		return $content;
	}
	
	global $wp_version;
	if( version_compare('2.7', $wp_version, '<=') ) // Lets us specify the user agent and set the basic auth string...
	{
		$options = array();
		$options['timeout'] = $timeout;
		$options['user-agent'] = 'Blubrry PowerPress/'.POWERPRESS_VERSION;
		if( $basicauth )
			$options['headers'][] = 'Authorization: Basic '.$basic_auth;
		if( count($post_args) > 0 )
		{
			$options['body'] = $post_args;
			$response = wp_remote_post( $uri, $options );
		}
		else
		{
			$response = wp_remote_get( $uri, $options );
		}
		
		if ( is_wp_error( $response ) )
			return false;

		return $response['body'];
	}
	
	// No sense going any further, we're not allowed to open remote URLs on this server
	if( !ini_get( 'allow_url_fopen' ) )
		return false;

	if( count($post_args) > 0 )
	{
		if( !function_exists('fsockopen') )
			return false; // This was our last attempt, we failed...
			
		$post_query = '';
		while( list($name,$value) = each($post_args) )
		{
			if( $post_query != '' )
				$post_query .= '&';
			$post_query .= $name;
			$post_query .= '=';
			$post_query .= urlencode($value);
		}
		$url_parts = parse_url($url);
		$host = $url_parts['host'];
		$port = ($url_parts['scheme']=='https'?443:80);
		if( isset($url_parts['port']) )
			$port = $url_parts['port'];

		$http_request  = "POST /updated-batch/ HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		if( $basic_auth )
			$http_request .= 'Authorization: Basic '. $basic_auth ."\r\n";
		$http_request .= 'Content-Type: application/x-www-form-urlencoded; charset='.get_option('blog_charset')."\r\n";
		$http_request .= 'Content-Length: ' . strlen($post_query) . "\r\n";
		$http_request .= 'User-Agent: Blubrry PowerPress/'.POWERPRESS_VERSION. "\r\n";
		$http_request .= "\r\n";
		$http_request .= $post_query;

		$response = '';
		$fp = @fsockopen($host, $port, $errno, $errstr, 5);
		if( $fp )
		{
			fwrite($fp, $http_request);
			while ( !feof($fs) )
				$response .= fgets($fs, 1160); // One TCP-IP packet
			fclose($fs);
		}
		
		$response = explode("\r\n\r\n", $response, 2);
		if( count($response) > 1 )
			return $response[1];
		return false;
	}
	
	if( $basic_auth )
	{
		$UserPassDecoded = base64_decode($basic_auth);
		list($User, $Pass) = split(':', $UserPassDecoded, 2);
		$url_prefix = sprintf('http://%s:%s@', str_replace('@', '$', $User), $Pass);
		$url = $url_prefix . substr($url, 7);
	}
	
	// Use the bullt-in remote_fopen...
	return wp_remote_fopen($url);
}

// Process any episodes for the specified post that have been marked for hosting and that do not have full URLs...
function powerpress_process_hosting($post_ID, $post_title)
{
	$errors = array();
	$Settings = get_option('powerpress_general');
	$CustomFeeds = array();
	if( is_array($Settings['custom_feeds']) )
		$CustomFeeds = $Settings['custom_feeds'];
	if( !isset($CustomFeeds['podcast']) )
		$CustomFeeds['podcast'] = 'podcast';
	
	while( list($feed_slug,$null) = each($CustomFeeds) )
	{
		$field = 'enclosure';
		if( $feed_slug != 'podcast' )
			$field = '_'.$feed_slug.':enclosure';
		$EnclosureData = get_post_meta($post_ID, $field, true);
		
		if( $EnclosureData )
		{
			list($EnclosureURL, $EnclosureSize, $EnclosureType, $Serialized) = split("\n", $EnclosureData);
			$EnclosureURL = trim($EnclosureURL);
			$EnclosureType = trim($EnclosureType);
			$EnclosureSize = trim($EnclosureSize);
			$EpisodeData = unserialize($Serialized);
			if( strtolower(substr($EnclosureURL, 0, 7) ) != 'http://' && $EpisodeData && isset($EpisodeData['hosting']) && $EpisodeData['hosting'] )
			{
				
				$error = false;
				// First we need to get media information...
				
				// If we are working with an Mp3, we can write id3 tags and get the info returned...
				if( ($EnclosureType == 'audio/mpg' || $EnclosureType == 'audio/mpeg') && @$Settings['write_tags'] )
				{
					$results = powerpress_write_tags($EnclosureURL, $post_title);
				}
				else
				{
					$results = powerpress_get_media_info($EnclosureURL);
				}
				
				if( is_array($results) && !isset($results['error']) )
				{
					if( isset($results['duration']) && $results['duration'] )
						$EpisodeData['duration'] = $results['duration'];
					if( isset($results['content-type']) && $results['content-type'] )
						$EnclosureType = $results['content-type'];
					if( isset($results['length']) && $results['length'] )
						$EnclosureSize = $results['length'];
				}
				else if( isset($results['error']) )
				{
					$error = 'Blubrry Hosting Error (media info): '. $results['error'];
					powerpress_add_error($error);
				}
				else
				{
					$error = 'Blubrry Hosting Error: An error occurred obtaining media information from <em>'. $EnclosureURL .'</em>. ';
					$error = 'Blubrry Hosting Error (publish): An error occurred publishing media '. $EnclosureURL .'.';
					$error .= '<a href="#" onclick="document.getElementById(\'powerpress_error_'. $rand_id .'\');this.style.display=\'none\';return false;">Display Error</a>';
					$error .= '<div id="powerpress_error_'. $rand_id .'" style="display: none;">'. $json_data .'</div>';
					powerpress_add_error($error);
				}
				
				if( $error == false )
				{
					// Extend the max execution time here
					set_time_limit(60*20); // give it 10 minutes just in case
					$api_url = sprintf('%s/media/%s/%s?format=json&publish=true', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), urlencode($Settings['blubrry_program_keyword']), urlencode($EnclosureURL)  );
					$json_data = powerpress_remote_fopen($api_url, $Settings['blubrry_auth'], array(), 60*30); // give this up to 30 minutes, though 3 seocnds to 20 seconds is all one should need.
					$results =  powerpress_json_decode($json_data);
					
					if( is_array($results) && !isset($results['error']) )
					{
						$EnclosureURL = $results['media_url'];
						unset($EpisodeData['hosting']); // we need to remove the flag since we're now using the correct FULL url
						$EnclosureData = $EnclosureURL . "\n" . $EnclosureSize . "\n". $EnclosureType . "\n" . serialize($EpisodeData);	
						update_post_meta($post_ID, $field, $EnclosureData);
					}
					else if( isset($results['error']) )
					{
						$error = 'Blubrry Hosting Error (publish): '. $results['error'];
						powerpress_add_error($error);
					}
					else
					{
						$rand_id = rand(100,2000);
						$error = 'Blubrry Hosting Error (publish): An error occurred publishing media <em>'. $EnclosureURL .'</em>. ';
						$error .= '<a href="#" onclick="document.getElementById(\'powerpress_error_'. $rand_id .'\');this.style.display=\'none\';return false;">Display Error</a>';
						$error .= '<div id="powerpress_error_'. $rand_id .'" style="display: none;">'. $json_data .'</div>';
						powerpress_add_error($error);
					}
				}
			}
		}
	}
}

function powerpress_json_decode($value)
{
	if( function_exists('json_decode') )
		return json_decode($value, true);
	if( !class_exists('Services_JSON') )
		require_once( dirname(__FILE__).'/3rdparty/JSON.php');
	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
	return $json->decode($value);
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

// Set the default settings basedon the section user is in.
function powerpress_default_settings($Settings, $Section='basic')
{
	// Set the default settings if the setting does not exist...
	switch($Section)
	{
		case 'basic': {
			// Nothing needs to be pre-set in the basic settings area
			
			if( !isset($Settings['player_options'] ) )
			{
				$Settings['player_options'] = 0;
				if( isset($Settings['player']) && $Settings['player'] != '' && $Settings['player'] != 'default' )
					$Settings['player_options'] = 1;
			}
			
			if( !isset($Settings['cat_casting'] ) )
			{
				$Settings['cat_casting'] = 0;
				if( isset($Settings['custom_cat_feeds']) && count($Settings['custom_cat_feeds']) > 0 )
					$Settings['cat_casting'] = 1;
			}
			
			if( !isset($Settings['channels'] ) )
			{
				$Settings['channels'] = 0;
				if( isset($Settings['custom_feeds']) && count($Settings['custom_feeds']) > 0 )
					$Settings['channels'] = 1;
			}
		}; break;
		case 'editfeed': {
			if( !isset($Settings['apply_to']) )
				$Settings['apply_to'] = 1; // Make sure settings are applied to all feeds by default
			//if( !isset($Settings['enhance_itunes_summary']) )
			//	$Settings['enhance_itunes_summary'] = 1;
		}; // Let this fall through to the custom feed settings
		case 'editfeed_custom': {
			if( !isset($Settings['enhance_itunes_summary']) )
				$Settings['enhance_itunes_summary'] = 1;
		}; break;
		case 'appearance': {
			if( !isset($Settings['display_player']) )
				$Settings['display_player'] = 1;
			if( !isset($Settings['player_function']) )
				$Settings['player_function'] = 1;
			if( !isset($Settings['podcast_link']) )
				$Settings['podcast_link'] = 1;
			if( !isset($Settings['display_player_excerpt']) )
					$Settings['display_player_excerpt'] = 0;
			// Play in page obsolete, switching here:
			if( $Settings['player_function'] == 5 )
				$Settings['player_function'] = 1;
			else if( $Settings['player_function'] == 4 )
				$Settings['player_function'] = 2;
			
		}; break;
	}
	
	return $Settings;
}

function powerpress_write_tags($file, $post_title)
{
	// Use the Blubrry API to write ID3 tags. to the media...
	
	$Settings = get_option('powerpress_general');
	
	$PostArgs = array();
	$Fields = array('title','artist','album','genre','year','track','composer','copyright','url');
	while( list($null,$field) = each($Fields) )
	{
		if( @$Settings[ 'tag_'.$field ] != '' )
		{
			$PostArgs[ $field ] = $Settings[ 'tag_'.$field ];
			if( $field == 'track' )
				powerpress_save_settings(array('tag_track'=>$NewNumber), 'powerpress_general');
		}
		else
		{
			switch($field)
			{
				case 'title': {
					$PostArgs['title'] = $post_title;
				}; break;
				case 'album': {
					$PostArgs['album'] = get_bloginfo('name');
				}; break;
				case 'genre': {
					$PostArgs['genre'] = 'Podcast';
				}; break;
				case 'year': {
					$PostArgs['year'] = date('Y');
				}; break;
				case 'artist':
				case 'composer': {
					if( @$Settings['itunes_talent_name'] )
						$PostArgs[ $field ] = $Settings['itunes_talent_name'];
				}; break;
				case 'copyright': {
					if( @$Settings['itunes_talent_name'] )
						$PostArgs['copyright'] = '(c) '.$Settings['itunes_talent_name'];
				}; break;
				case 'url': {
					$PostArgs['url'] = get_bloginfo('url');
				}; break;
			}
		}
	}
							
	// Get meta info via API
	$api_url = sprintf('%s/media/%s/%s?format=json&id3=true', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), urlencode($Settings['blubrry_program_keyword']), urlencode($file) );
	
	$content = powerpress_remote_fopen($api_url, $Settings['blubrry_auth'], $PostArgs );
	if( $content )
	{
		$Results = powerpress_json_decode($content);
		if( $Results && is_array($Results) )
			return $Results;
	}
	
	return array('error'=>'Error occurred writing MP3 ID3 Tags.');
}

function powerpress_get_media_info($file)
{
	$Settings = get_option('powerpress_general');
	
	$api_url = sprintf('%s/media/%s/%s?format=json&info=true', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), urlencode($Settings['blubrry_program_keyword']), urlencode($file) );
	$content = powerpress_remote_fopen($api_url, $Settings['blubrry_auth']);
	if( $content )
	{
		$Results = powerpress_json_decode($content);
		if( $Results && is_array($Results) )
			return $Results;
	}
	
	return array('error'=>'Error occurred obtaining media information.');
}

// Call this function when there is no enclosure currently detected for the post but users set the option to auto-add first media file linked within post option is checked.
function powerpress_do_enclose( $content, $post_ID, $use_last_media_link = false )
{
	$ltrs = '\w';
	$gunk = '/#~:.?+=&%@!\-';
	$punc = '.:?\-';
	$any = $ltrs . $gunk . $punc;

	preg_match_all( "{\b http : [$any] +? (?= [$punc] * [^$any] | $)}x", $content, $post_links_temp );
	
	if( $use_last_media_link )
		$post_links_temp[0] = array_reverse($post_links_temp[0]);
	
	$enclosure = false;
	foreach ( (array) $post_links_temp[0] as $link_test ) {
		$test = parse_url( $link_test );
		// Wordpress also acecpts query strings, which doesn't matter to us what's more important is taht the request ends with a file extension.
		// get the file extension at the end of the request:
		if( preg_match('/\.([a-z0-9]{2,7})$/i', $link_test, $matches) )
		{
			// see if the file extension is one of the supported media types...
			$content_type = powerpress_get_contenttype('test.'.$matches[1], false); // we want to strictly use the content types known for media, so pass false for second argument
			if( $content_type )
			{
				$enclosure = $link_test;
				$MediaInfo = powerpress_get_media_info_local($link_test, $content_type);
				if( !isset($MediaInfo['error']) && !empty($MediaInfo['length']) )
				{
					// Insert enclosure here:
					$EnclosureData = $link_test . "\n" . $MediaInfo['length'] . "\n". $content_type;
					if( !empty($MediaInfo['duration']) )
						$EnclosureData .= "\n".serialize( array('duration'=>$MediaInfo['duration']) );
					add_post_meta($post_ID, 'enclosure', $EnclosureData, true);
					break; // We don't wnat to insert anymore enclosures, this was it!
				}
			}
		}
	}
}

function powerpress_get_media_info_local($media_file, $content_type='', $file_size=0, $duration='', $return_warnings=false)
{
	$error_msg = '';
	$warning_msg = '';
	if( $content_type == '' )
		$content_type = powerpress_get_contenttype($media_file);
	
	if( $content_type == '' )
		return array('error'=>'Unable to detect content type.');
		
	$get_duration_info = ($content_type == 'audio/mpeg' && $duration === '');
	// Lets use the mp3info class:
	require_once(dirname(__FILE__).'/mp3info.class.php');
	$Mp3Info = new Mp3Info();
	$Mp3Data = $Mp3Info->GetMp3Info($media_file, !$get_duration_info);
	if( $Mp3Data )
	{
		if( $Mp3Info->GetRedirectCount() > 5 )
		{
			// Add a warning that the redirect count exceeded 5, which may prevent some podcatchers from downloading the media.
			$warning = sprintf( __('Warning, the Media URL %s contains %d redirects.'), $media_file, $Mp3Info->GetRedirectCount() );
			$warning .=	' [<a href="http://help.blubrry.com/blubrry-powerpress/errors-and-warnings/" title="'. __('Help') .'" target="_blank">'. __('Help') .'</a>]';
			if( $return_warnings )
				$warning_msg .= $warning;
			else
				powerpress_add_error( $warning );
		}
		
		if( $file_size == 0 )
			$file_size = $Mp3Info->GetContentLength();
		
		if( $get_duration_info )
			$duration = powerpress_readable_duration($Mp3Data['playtime_string'], true); // Fix so it looks better when viewed for editing
		
		if( count( $Mp3Info->GetWarnings() ) > 0 )
		{
			$Warnings = $Mp3Info->GetWarnings();
			while( list($null, $warning) = each($Warnings) )
			{
				$warning = sprintf( __('Warning, Media URL %s:'), $media_file) .' '. $warning  .' [<a href="http://help.blubrry.com/blubrry-powerpress/errors-and-warnings/" title="'. __('Help') .'" target="_blank">'. __('Help') .'</a>]';
				if( $return_warnings )
					$warning_msg .= $warning;
				else
					powerpress_add_error( $warning );
			}
		}
	}
	else
	{
		if( $Mp3Info->GetError() )
			return array('error'=>$Mp3Info->GetError() );
		else
			return array('error'=>'Error occurred obtaining media information.');
	}
	
	if( $file_size == 0 )
		return array('error'=>'Error occurred obtaining media file size.' );
	
	if( $return_warnings && $warning_msg != '' )
		return array('content-type'=>$content_type, 'length'=>$file_size, 'duration'=>$duration, 'warnings'=>$warning_msg);
	return array('content-type'=>$content_type, 'length'=>$file_size, 'duration'=>$duration);
		
	// OLD CODE FOLLOWS:
	if( $content_type == 'audio/mpeg' && $duration === '' ) // if duration has a value or is set to false then we don't want to try to obtain it here...
	{
		// Lets use the mp3info class:
		require_once(dirname(__FILE__).'/mp3info.class.php');
		$Mp3Info = new Mp3Info();
		$Mp3Data = $Mp3Info->GetMp3Info($media_file);
		if( $Mp3Data )
		{
			if( $Mp3Info->GetRedirectCount() > 5 )
			{
				// Add a warning that the redirect count exceeded 5, which may prevent some podcatchers from downloading the media.
				powerpress_add_error( sprintf( __('Warning, the Media URL %s contains %d redirects.'), $media_file, $Mp3Info->GetRedirectCount() )
					.' [<a href="http://help.blubrry.com/blubrry-powerpress/errors-and-warnings/" title="'. __('Help') .'" target="_blank">'. __('Help') .'</a>]'
					);
			}
			
			if( $file_size == 0 )
				$file_size = $Mp3Info->GetContentLength();
				
			$duration = powerpress_readable_duration($Mp3Data['playtime_string'], true); // Fix so it looks better when viewed for editing
			
			if( count( $Mp3Info->GetWarnings() ) > 0 )
			{
				$Warnings = $Mp3Info->GetWarnings();
				while( list($null, $warning) = each($Warnings) )
					powerpress_add_error(  sprintf( __('Warning, Media URL %s:'), $media_file) .' '. $warning  .' [<a href="http://help.blubrry.com/blubrry-powerpress/errors-and-warnings/" title="'. __('Help') .'" target="_blank">'. __('Help') .'</a>]' );
			}
		}
		else
		{
			if( $Mp3Info->GetError() )
				return array('error'=>$Mp3Info->GetError() );
			else
				return array('error'=>'Error occurred obtaining media information.');
		}
	}
	
	if( $content_type != '' && $file_size == 0 )
	{
		$response = wp_remote_head( $media_file );
		if ( is_wp_error( $response ) )
		{
			return array('error'=>$response->get_error_message() );
		}
		
		if( isset($response['response']['code']) && $response['response']['code'] < 200 || $response['response']['code'] > 290 )
		{
			return array('error'=>trim('Error, HTTP '.$response['response']['code']) );
		}

		$headers = wp_remote_retrieve_headers( $response );
		
		if( $headers && $headers['content-length'] )
			$file_size = (int) $headers['content-length'];
		else
			return array('error'=>'Unable to obtain file size of media file.' );
	}
	
	if( $file_size == 0 )
		return array('error'=>'Error occurred obtaining media file size.' );
	
	return array('content-type'=>$content_type, 'length'=>$file_size, 'duration'=>$duration);
}

function powerpress_add_error($error)
{
	$Errors = get_option('powerpress_errors');
	if( !is_array($Errors) )
		$Errors = array();
	$Errors[] = $error;
	update_option('powerpress_errors',  $Errors);
}
	


require_once( dirname(__FILE__).'/powerpressadmin-jquery.php');
// Only include the dashboard when appropriate.
require_once(dirname(__FILE__).'/powerpressadmin-dashboard.php');

?>