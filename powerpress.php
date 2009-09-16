<?php
/*
Plugin Name: Blubrry PowerPress
Plugin URI: http://www.blubrry.com/powerpress/
Description: <a href="http://www.blubrry.com/powerpress/" target="_blank">Blubrry PowerPress</a> adds podcasting support to your blog. Features include: media player, 3rd party statistics, iTunes integration, Blubrry Services (Media Statistics and Hosting) integration and a lot more.
Version: 0.9.9
Author: Blubrry
Author URI: http://www.blubrry.com/
Change Log:
	Please see readme.txt for detailed change log.

Contributors:
	Angelo Mandato, CIO RawVoice - Plugin founder, architect and lead developer
	Pat McSweeny, Developer for RawVoice - Developed initial version (v0.1.0) of plugin
	Jerry Stephens, Way of the Geek (http://wayofthegeek.org/) - Contributed initial code fix for excerpt bug resolved in v0.6.1
	
Credits:
	getID3(), License: GPL 2.0+ by James Heinrich <info [at] getid3.org> http://www.getid3.org
		Note: getid3.php analyze() function modified to prevent redundant filesize() function call.
	FlowPlayer, License: GPL 3.0+ http://flowplayer.org/; source: http://flowplayer.org/download.html
	flashembed(), License: MIT by Tero Piirainen (tipiirai [at] gmail.com)
		Note: code found at bottom of player.js
	
Copyright 2008-2009 RawVoice Inc. (http://www.rawvoice.com)

License: GPL (http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt)

	This project uses source that is GPL licensed.
*/


if( !function_exists('add_action') )
	die("access denied.");
	
// WP_PLUGIN_DIR (REMEMBER TO USE THIS DEFINE IF NEEDED)
define('POWERPRESS_VERSION', '0.9.9' );

/////////////////////////////////////////////////////
// The following define options should be placed in your
// wp-config.php file so the setting is not disrupted when
// you upgrade the plugin.
/////////////////////////////////////////////////////

// Set specific play and download labels for your installation of PowerPress
if( !defined('POWERPRESS_LINKS_TEXT') )
	define('POWERPRESS_LINKS_TEXT', __('Podcast') );
if( !defined('POWERPRESS_DURATION_TEXT') )
	define('POWERPRESS_DURATION_TEXT', __('Duration') );
if( !defined('POWERPRESS_PLAY_IN_NEW_WINDOW_TEXT') )
	define('POWERPRESS_PLAY_IN_NEW_WINDOW_TEXT', __('Play in new window') );	
if( !defined('POWERPRESS_DOWNLOAD_TEXT') )
	define('POWERPRESS_DOWNLOAD_TEXT', __('Download') );	
if( !defined('POWERPRESS_PLAY_TEXT') )
	define('POWERPRESS_PLAY_TEXT', __('Play') );	

if( !defined('POWERPRESS_BLUBRRY_API_URL') )
	define('POWERPRESS_BLUBRRY_API_URL', 'http://api.blubrry.com/');
	
// Display custom play image for quicktime media. Applies to on page player only.
//define('POWERPRESS_PLAY_IMAGE', 'http://www.blubrry.com/themes/blubrry/images/player/PlayerBadge150x50NoBorder.jpg');

if( !defined('POWERPRESS_CONTENT_ACTION_PRIORITY') )
	define('POWERPRESS_CONTENT_ACTION_PRIORITY', 10 );

//define('POWERPRESS_ENABLE_HTTPS_MEDIA', true); // Add this define to your wp-config.php if you wnat to allow media URLs that begin with https://

// Define variables, advanced users could define these in their own wp-config.php so lets not try to re-define
if( !defined('POWERPRESS_LINK_SEPARATOR') )
	define('POWERPRESS_LINK_SEPARATOR', '|');
if( !defined('POWERPRESS_PLAY_IMAGE') )
	define('POWERPRESS_PLAY_IMAGE', 'play_video_default.jpg');
if( !defined('PHP_EOL') )
	define('PHP_EOL', "\n"); // We need this variable defined for new lines.
	
$powerpress_feed = NULL; // DO NOT CHANGE

function powerpress_content($content)
{
	global $post, $g_powerpress_excerpt_post_id;
	
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
		return;
	
	if( is_feed() )
		return $content; // We don't want to do anything to the feed
		
	if( function_exists('post_password_required') )
	{
		if( post_password_required($post) )
			return $content;
	}
	
	// Problem: If the_excerpt is used instead of the_content, both the_exerpt and the_content will be called here.
	// Important to note, get_the_excerpt will be called before the_content is called, so we add a simple little hack
	if( current_filter() == 'get_the_excerpt' )
	{
		$g_powerpress_excerpt_post_id = $post->ID;
		return $content; // We don't want to do anything to this content yet...
	}
	else if( current_filter() == 'the_content' && $g_powerpress_excerpt_post_id == $post->ID )
	{
		return $content; // We don't want to do anything to this excerpt content in this call either...
	}
	
	// PowerPress settings:
	$GeneralSettings = get_option('powerpress_general');
	if( !isset($GeneralSettings['custom_feeds']) )
    $GeneralSettings['custom_feeds'] = array('podcast'=>'Default Podcast Feed');
	
	// Re-order so the default podcast episode is the top most...
	$Temp = $GeneralSettings['custom_feeds'];
	$GeneralSettings['custom_feeds'] = array();
	$GeneralSettings['custom_feeds']['podcast'] = 'Default Podcast Feed';
	while( list($feed_slug, $feed_title) = each($Temp) )
	{
		if( $feed_slug == 'podcast' )
			continue;
		$GeneralSettings['custom_feeds'][ $feed_slug ] = $feed_title;
	}
	
	if( !isset($GeneralSettings['display_player']) )
			$GeneralSettings['display_player'] = 1;
	if( !isset($GeneralSettings['player_function']) )
		$GeneralSettings['player_function'] = 1;
	if( !isset($GeneralSettings['podcast_link']) )
		$GeneralSettings['podcast_link'] = 1;
	
	// The blog owner doesn't want anything displayed, so don't bother wasting anymore CPU cycles
	if( $GeneralSettings['display_player'] == 0 )
		return $content;
		
	if( current_filter() == 'the_excerpt' && !$GeneralSettings['display_player_excerpt'] )
		return $content; // We didn't want to modify this since the user didn't enable it for excerpts
		
	// Figure out which players are alerady in the body of the page...
	$ExcludePlayers = array();
	if( isset($GeneralSettings['disable_player']) )
		$ExcludePlayers = $GeneralSettings['disable_player']; // automatically disable the players configured
	
	if( preg_match_all('/(.?)\[(powerpress)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)/s', $content, $matches) )
	{
		if( isset($matches[3]) )
		{
			while( list($key,$row) = each($matches[3]) )
			{
				$attributes = shortcode_parse_atts($row);
				if( isset($attributes['url']) )
				{
					// not a problem...
				}
				else if( isset($attributes['feed']) )
				{
					// we want to exclude this feed from the links aera...
					$ExcludePlayers[ $attributes['feed'] ] = true;
				}
				else
				{
					// we don't want to include any players below...
					$ExcludePlayers = $GeneralSettings['custom_feeds'];
				}
			}
		}
	}
	
	// LOOP HERE TO DISPLAY EACH MEDIA TYPE
	$new_content = '';
	while( list($feed_slug,$feed_title) = each($GeneralSettings['custom_feeds']) )
	{
		// Get the enclosure data
		$EpisodeData = powerpress_get_enclosure_data($post->ID, $feed_slug);
	
		//$enclosureData = get_post_meta($post->ID, 'enclosure', true);
		$duration = false;
		$EnclosureURL = '';
		$EnclosureSize = '';
		$EnclosureType = '';
		
		if( !$EpisodeData )
		{
			if( $GeneralSettings['process_podpress'] && $feed_slug == 'podcast' )
			{
				//$podPressMedia = get_post_meta($post->ID, 'podPressMedia', true);
				$podPressMedia = powerpress_get_post_meta($post->ID, 'podPressMedia');
				
				if( $podPressMedia )
				{
					if( !is_array($podPressMedia) )
					{
						// Sometimes the stored data gets messed up, we can fix it here:
						$podPressMedia = powerpress_repair_serialize($podPressMedia);
						$podPressMedia = @unserialize($podPressMedia);
					}
					
					if( is_array($podPressMedia) && isset($podPressMedia[0]) && isset($podPressMedia[0]['URI']) )
					{
						$EnclosureURL = $podPressMedia[0]['URI'];
						if( isset($podPressMedia[0]['size']) )
							$EnclosureSize = $podPressMedia[0]['size'];
						$EnclosureType = '';
					}
				}
				if( !$EnclosureURL )
					continue;
				
				if( strpos($EnclosureURL, 'http://' ) !== 0 && strpos($EnclosureURL, 'https://' ) !== 0 )
					$EnclosureURL = rtrim($GeneralSettings['default_url'], '/') .'/'. $EnclosureURL;
					
				// Add redirects to Media URL
				$EnclosureURL = powerpress_add_redirect_url($EnclosureURL, $GeneralSettings);
			}
		}
		else
		{
			$EnclosureURL = $EpisodeData['url']; // Already includes redirect
			$EnclosureSize = $EpisodeData['size'];
			$EnclosureType = $EpisodeData['type'];
			$duration = $EpisodeData['duration']; // Always set, though may be set to false
		}
	
		// Just in case, if there's no URL lets escape!
		if( !$EnclosureURL )
			continue;
		
		// If the player is not already inserted in the body of the post using the shortcode...
		//if( preg_match('/\[powerpress(.*)\]/is', $content) == 0 )
		if( !isset($ExcludePlayers[ $feed_slug ]) ) // If the player is not in our exclude list because it's already in the post body somewhere...
		{
			if( $GeneralSettings['player_function'] != 3 && $GeneralSettings['player_function'] != 0 ) // Play in new window only or disabled
			{
				if( $EpisodeData && isset($EpisodeData['embed']) )
					$new_content .=  $EpisodeData['embed'];
				if( !isset($EpisodeData['no_player']) )
					$new_content .= apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($EnclosureURL, 'p'), array('feed'=>$feed_slug, 'type'=>$EpisdoeData['type']) );
			}
		}
		
		// Build links for player
		$player_links = '';
		switch( $GeneralSettings['player_function'] )
		{
			case 1: // Play on page and new window
			case 3: // Play in new window only
			case 5: { // Play in page and new window
				$player_links .= "<a href=\"$EnclosureURL\" class=\"powerpress_link_pinw\" target=\"_blank\" title=\"". POWERPRESS_PLAY_IN_NEW_WINDOW_TEXT ."\" onclick=\"return powerpress_pinw('{$post->ID}-{$feed_slug}');\">". POWERPRESS_PLAY_IN_NEW_WINDOW_TEXT ."</a>".PHP_EOL;
			}; break;
			case 2:
			case 4:{ // Play in/on page only
			}; break;
		}//end switch	
		
		if( $GeneralSettings['podcast_link'] == 1 )
		{
			if( $player_links )
				$player_links .= ' '. POWERPRESS_LINK_SEPARATOR .' ';
			$player_links .= "<a href=\"$EnclosureURL\" class=\"powerpress_link_d\" title=\"". POWERPRESS_DOWNLOAD_TEXT ."\">". POWERPRESS_DOWNLOAD_TEXT ."</a>".PHP_EOL;
		}
		else if( $GeneralSettings['podcast_link'] == 2 )
		{
			if( $player_links )
				$player_links .= ' '. POWERPRESS_LINK_SEPARATOR .' ';
			$player_links .= "<a href=\"$EnclosureURL\" class=\"powerpress_link_d\" title=\"". POWERPRESS_DOWNLOAD_TEXT ."\">". POWERPRESS_DOWNLOAD_TEXT ."</a> (".powerpress_byte_size($EnclosureSize).") ".PHP_EOL;
		}
		else if( $GeneralSettings['podcast_link'] == 3 )
		{
			if( $duration == false )
				$duration = get_post_meta($post->ID, 'itunes:duration', true);
			if( $player_links )
				$player_links .= ' '. POWERPRESS_LINK_SEPARATOR .' ';
			if( $duration && ltrim($duration, '0:') != '' )
				$player_links .= "<a href=\"$EnclosureURL\" class=\"powerpress_link_d\" title=\"". POWERPRESS_DOWNLOAD_TEXT ."\">". POWERPRESS_DOWNLOAD_TEXT ."</a> (". htmlspecialchars(POWERPRESS_DURATION_TEXT) .": " . powerpress_readable_duration($duration) ." &#8212; ".powerpress_byte_size($EnclosureSize).")".PHP_EOL;
			else
				$player_links .= "<a href=\"$EnclosureURL\" class=\"powerpress_link_d\" title=\"". POWERPRESS_DOWNLOAD_TEXT ."\">". POWERPRESS_DOWNLOAD_TEXT ."</a> (".powerpress_byte_size($EnclosureSize).")".PHP_EOL;
		}
		
		if( $player_links && empty($GeneralSettings['disable_player'][$feed_slug]) )
		{
			if( count($GeneralSettings['custom_feeds']) > 1 && $feed_slug != 'podcast' )
			{
				$new_content .= '<p class="powerpress_links">'. htmlspecialchars(POWERPRESS_LINKS_TEXT) .' ('. htmlspecialchars($feed_slug) .'): '. $player_links . '</p>'.PHP_EOL;
			}
			else
			{
				$new_content .= '<p class="powerpress_links">'. htmlspecialchars(POWERPRESS_LINKS_TEXT) .': '. $player_links . '</p>'.PHP_EOL;
			}
		}
	}
	
	if( $new_content == '' )
		return $content;
		
	switch( $GeneralSettings['display_player'] )
	{
		case 1: { // Below posts
			return $content.$new_content;
		}; break;
		case 2: { // Above posts
			return $new_content.$content;
		}; break;
	}
	return $content;
}//end function

add_filter('get_the_excerpt', 'powerpress_content', (POWERPRESS_CONTENT_ACTION_PRIORITY - 1) );
add_filter('the_content', 'powerpress_content', POWERPRESS_CONTENT_ACTION_PRIORITY);
add_filter('the_excerpt', 'powerpress_content', POWERPRESS_CONTENT_ACTION_PRIORITY);

function powerpress_header()
{
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
		return;
	// PowerPress settings:
	$Powerpress = get_option('powerpress_general');
	
	if( !isset($Powerpress['player_function']) || $Powerpress['player_function'] > 0 ) // Don't include the player in the header if it is not needed...
	{
		$PowerpressPluginURL = powerpress_get_root_url();
?>
<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>player.js"></script>
<script type="text/javascript">
<?php
		$new_window_width = 320;
		$new_window_height = 240;

		if( isset($Powerpress['new_window_width']) && $Powerpress['new_window_width'] > 100 )
			$new_window_width = $Powerpress['new_window_width'];
		if( isset($Powerpress['new_window_height']) && $Powerpress['new_window_height'] > 40 )
			$new_window_height = $Powerpress['new_window_height'];
?>
function powerpress_pinw(pinw){window.open('<?php echo get_bloginfo('url'); ?>/?powerpress_pinw='+pinw, 'PowerPressPlayer','toolbar=0,status=0,resizable=1,width=<?php echo ($new_window_width + 40); ?>,height=<?php echo ($new_window_height + 80); ?>');	return false;}
</script>
<?php
	}
}

add_action('wp_head', 'powerpress_header');

function powerpress_rss2_ns()
{
	if( !powerpress_is_podcast_feed() )
		return;
	
	// Okay, lets add the namespace
	echo 'xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"'.PHP_EOL;
}

add_action('rss2_ns', 'powerpress_rss2_ns');

function powerpress_rss2_head()
{
	global $powerpress_feed;
	
	if( !powerpress_is_podcast_feed() )
		return; // Not a feed we manage
	
	$feed_slug = get_query_var( 'feed' );
	$cat_ID = get_query_var('cat');
	
	$Feed = get_option('powerpress_feed'); // Get the main feed settings
	if( is_category() )
	{
		$CustomFeed = get_option('powerpress_cat_feed_'.$cat_ID); // Get the custom podcast feed settings saved in the database
		if( $CustomFeed )
			$Feed = powerpress_merge_empty_feed_settings($CustomFeed, $Feed);
	}
	else if( powerpress_is_custom_podcast_feed() ) // If we're handling a custom podcast feed...
	{
		$CustomFeed = get_option('powerpress_feed_'.$feed_slug); // Get the custom podcast feed settings saved in the database
		$Feed = powerpress_merge_empty_feed_settings($CustomFeed, $Feed);
	}
	
	if( !isset($Feed['url']) || trim($Feed['url']) == '' )
	{
		if( is_category() )
			$Feed['url'] = get_category_link($cat_ID);
		else
			$Feed['url'] = get_bloginfo('url');
	}
	
	$General = get_option('powerpress_general');
	
	// We made it this far, lets write stuff to the feed!
	echo '<!-- podcast_generator="Blubrry PowerPress/'. POWERPRESS_VERSION .'" ';
	if( $General['advanced_mode'] == 0 )
		echo 'mode="simple" ';
	else
		echo 'mode="advanced" ';
	
	if( $General['episode_box_mode'] == 0 )
		echo 'entry="normal" ';
	else if( $General['episode_box_mode'] == 1)
		echo 'entry="simple" ';
	else
		echo 'entry="advanced" ';
	
	echo '-->'.PHP_EOL;
		
	// add the itunes:new-feed-url tag to feed
	if( powerpress_is_custom_podcast_feed() )
	{
		if( trim($Feed['itunes_new_feed_url']) )
			echo "\t<itunes:new-feed-url>". trim($Feed['itunes_new_feed_url']) .'</itunes:new-feed-url>'.PHP_EOL;
	}
	else if( trim($Feed['itunes_new_feed_url']) && ($feed_slug == 'feed' || $feed_slug == 'rss2') ) // If it is the default feed (We don't wnat to apply this to category or tag feeds
	{
		echo "\t<itunes:new-feed-url>". $Feed['itunes_new_feed_url'] .'</itunes:new-feed-url>'.PHP_EOL;
	}
	
	if( $Feed['itunes_summary'] )
		echo "\t".'<itunes:summary>'. powerpress_format_itunes_value( $Feed['itunes_summary'], 'summary' ) .'</itunes:summary>'.PHP_EOL;
	else
		echo "\t".'<itunes:summary>'.  powerpress_format_itunes_value( get_bloginfo('description'), 'summary' ) .'</itunes:summary>'.PHP_EOL;
	
	if( $powerpress_feed['itunes_talent_name'] )
		echo "\t<itunes:author>" . wp_specialchars($powerpress_feed['itunes_talent_name']) . '</itunes:author>'.PHP_EOL;
	
	if( $powerpress_feed['explicit'] )
		echo "\t".'<itunes:explicit>' . $powerpress_feed['explicit'] . '</itunes:explicit>'.PHP_EOL;
		
	if( $powerpress_feed['block'] && $powerpress_feed['block'] == 'yes' )
		echo "\t\t<itunes:block>yes</itunes:block>\n";
		
	if( $Feed['itunes_image'] )
	{
		echo "\t".'<itunes:image href="' . wp_specialchars($Feed['itunes_image'], 'double') . '" />'.PHP_EOL;
	}
	else
	{
		echo "\t".'<itunes:image href="' . powerpress_get_root_url() . 'itunes_default.jpg" />'.PHP_EOL;
	}
	
	if( $Feed['email'] )
	{
		echo "\t".'<itunes:owner>'.PHP_EOL;
		echo "\t\t".'<itunes:name>' . wp_specialchars($powerpress_feed['itunes_talent_name']) . '</itunes:name>'.PHP_EOL;
		echo "\t\t".'<itunes:email>' . wp_specialchars($Feed['email']) . '</itunes:email>'.PHP_EOL;
		echo "\t".'</itunes:owner>'.PHP_EOL;
		echo "\t".'<managingEditor>'. wp_specialchars($Feed['email'] .' ('. $powerpress_feed['itunes_talent_name'] .')') .'</managingEditor>'.PHP_EOL;
	}
	
	if( $Feed['copyright'] )
	{
		// In case the user entered the copyright html version or the copyright UTF-8 or ASCII symbol or just (c)
		$Feed['copyright'] = str_replace(array('&copy;', '(c)', '(C)', chr(194) . chr(169), chr(169) ), '&#xA9;', $Feed['copyright']);
		echo "\t".'<copyright>'. wp_specialchars($Feed['copyright']) . '</copyright>'.PHP_EOL;
	}
	
	if( trim($Feed['itunes_subtitle']) )
		echo "\t".'<itunes:subtitle>' . powerpress_format_itunes_value($Feed['itunes_subtitle'], 'subtitle', true) . '</itunes:subtitle>'.PHP_EOL;
	else
		echo "\t".'<itunes:subtitle>'.  powerpress_format_itunes_value( get_bloginfo('description'), 'subtitle', true) .'</itunes:subtitle>'.PHP_EOL;
	
	if( trim($Feed['itunes_keywords']) )
		echo "\t".'<itunes:keywords>' . powerpress_format_itunes_value($Feed['itunes_keywords'], 'keywords') . '</itunes:keywords>'.PHP_EOL;
		
	if( $Feed['rss2_image'] )
	{
		echo"\t". '<image>' .PHP_EOL;
		if( is_category() )
			echo "\t\t".'<title>' . wp_specialchars( get_bloginfo_rss('name') ) . '</title>'.PHP_EOL;
		else
			echo "\t\t".'<title>' . wp_specialchars( get_bloginfo_rss('name') . get_wp_title_rss() ) . '</title>'.PHP_EOL;
		echo "\t\t".'<url>' . wp_specialchars($Feed['rss2_image']) . '</url>'.PHP_EOL;
		echo "\t\t".'<link>'. $Feed['url'] . '</link>' . PHP_EOL;
		echo "\t".'</image>' . PHP_EOL;
	}
	else // Use the default image
	{
		echo"\t". '<image>' .PHP_EOL;
		if( is_category() )
			echo "\t\t".'<title>' . wp_specialchars( get_bloginfo_rss('name') ) . '</title>'.PHP_EOL;
		else
			echo "\t\t".'<title>' . wp_specialchars( get_bloginfo_rss('name') . get_wp_title_rss() ) . '</title>'.PHP_EOL;
		echo "\t\t".'<url>' . powerpress_get_root_url() . 'rss_default.jpg</url>'.PHP_EOL;
		echo "\t\t".'<link>'. $Feed['url'] . '</link>' . PHP_EOL;
		echo "\t".'</image>' . PHP_EOL;
	}
	
	// Handle iTunes categories
	$Categories = powerpress_itunes_categories();
	$Cat1 = false; $Cat2 = false; $Cat3 = false;
	if( $Feed['itunes_cat_1'] != '' )
			list($Cat1, $SubCat1) = split('-', $Feed['itunes_cat_1']);
	if( $Feed['itunes_cat_2'] != '' )
			list($Cat2, $SubCat2) = split('-', $Feed['itunes_cat_2']);
	if( $Feed['itunes_cat_3'] != '' )
			list($Cat3, $SubCat3) = split('-', $Feed['itunes_cat_3']);
 
	if( $Cat1 )
	{
		$CatDesc = $Categories[$Cat1.'-00'];
		$SubCatDesc = $Categories[$Cat1.'-'.$SubCat1];
		if( $Cat1 != $Cat2 && $SubCat1 == '00' )
		{
			echo "\t".'<itunes:category text="'. wp_specialchars($CatDesc) .'" />'.PHP_EOL;
		}
		else
		{
			echo "\t".'<itunes:category text="'. wp_specialchars($CatDesc) .'">'.PHP_EOL;
			if( $SubCat1 != '00' )
				echo "\t\t".'<itunes:category text="'. wp_specialchars($SubCatDesc) .'" />'.PHP_EOL;
			
			// End this category set
			if( $Cat1 != $Cat2 )
				echo "\t".'</itunes:category>'.PHP_EOL;
		}
	}
 
	if( $Cat2 )
	{
		$CatDesc = $Categories[$Cat2.'-00'];
		$SubCatDesc = $Categories[$Cat2.'-'.$SubCat2];
	 
		// It's a continuation of the last category...
		if( $Cat1 == $Cat2 )
		{
			if( $SubCat2 != '00' )
				echo "\t\t".'<itunes:category text="'. wp_specialchars($SubCatDesc) .'" />'.PHP_EOL;
			
			// End this category set
			if( $Cat2 != $Cat3 )
				echo "\t".'</itunes:category>'.PHP_EOL;
		}
		else // This is not a continuation, lets start a new category set
		{
			if( $Cat2 != $Cat3 && $SubCat2 == '00' )
			{
				echo "\t".'<itunes:category text="'. wp_specialchars($CatDesc) .'" />'.PHP_EOL;
			}
			else // We have nested values
			{
				if( $Cat1 != $Cat2 ) // Start a new category set
					echo "\t".'<itunes:category text="'. wp_specialchars($CatDesc) .'">'.PHP_EOL;
				if( $SubCat2 != '00' )
				echo "\t\t".'<itunes:category text="'. wp_specialchars($SubCatDesc) .'" />'.PHP_EOL;
				if( $Cat2 != $Cat3 ) // End this category set
					echo "\t".'</itunes:category>'.PHP_EOL;
			}
		}
	}
 
	if( $Cat3 )
	{
		$CatDesc = $Categories[$Cat3.'-00'];
		$SubCatDesc = $Categories[$Cat3.'-'.$SubCat3];
	 
		// It's a continuation of the last category...
		if( $Cat2 == $Cat3 )
		{
			if( $SubCat3 != '00' )
				echo "\t\t".'<itunes:category text="'. wp_specialchars($SubCatDesc) .'" />'.PHP_EOL;
			
			// End this category set
			echo "\t".'</itunes:category>'.PHP_EOL;
		}
		else // This is not a continuation, lets start a new category set
		{
			if( $Cat2 != $Cat3 && $SubCat3 == '00' )
			{
				echo "\t".'<itunes:category text="'. wp_specialchars($CatDesc) .'" />'.PHP_EOL;
			}
			else // We have nested values
			{
				if( $Cat2 != $Cat3 ) // Start a new category set
					echo "\t".'<itunes:category text="'. wp_specialchars($CatDesc) .'">'.PHP_EOL;
				if( $SubCat3 != '00' )
					echo "\t\t".'<itunes:category text="'. wp_specialchars($SubCatDesc) .'" />'.PHP_EOL;
				// End this category set
				echo "\t".'</itunes:category>'.PHP_EOL;
			}
		}
	}
	// End Handle iTunes categories
}

add_action('rss2_head', 'powerpress_rss2_head');

function powerpress_rss2_item()
{
	global $post, $powerpress_feed;
	$duration = false;
	
	// are we processing a feed that powerpress should handle
	if( !powerpress_is_podcast_feed() )
		return;
	
	if( function_exists('post_password_required') )
	{
		if( post_password_required($post) )
			return $content;
	}
		
	// Check and see if we're working with a podcast episode
	$custom_enclosure = false;
	if( powerpress_is_custom_podcast_feed() && get_query_var('feed') != 'podcast' && !is_category() )
	{
		$enclosureData = get_post_meta($post->ID, '_'. get_query_var('feed') .':enclosure', true);
		$custom_enclosure = true;
	}
	else
	{
		$enclosureData = get_post_meta($post->ID, 'enclosure', true);
	}
		
	if( !$enclosureData )
	{
		$EnclosureURL = '';
		if( $powerpress_feed['process_podpress'] )
		{
			//$podPressMedia = get_post_meta($post->ID, 'podPressMedia', true);
			$podPressMedia = powerpress_get_post_meta($post->ID, 'podPressMedia');
			
			if( !is_array($podPressMedia) )
			{
				// Sometimes the stored data gets messed up, we can fix it here:
				$podPressMedia = powerpress_repair_serialize($podPressMedia);
				$podPressMedia = @unserialize($podPressMedia);
			}
			
			if( $podPressMedia && isset($podPressMedia[0]) && isset($podPressMedia[0]['URI']) )
			{
				$EnclosureURL = $podPressMedia[0]['URI'];
				if( strpos($EnclosureURL, 'http://' ) !== 0 && strpos($EnclosureURL, 'https://' ) !== 0 )
					$EnclosureURL = $powerpress_feed['default_url'] . $EnclosureURL;
				if( isset($podPressMedia[0]['size']) )
					$EnclosureSize = $podPressMedia[0]['size'];
				if( isset($podPressMedia[0]['duration']) )
					$duration = $podPressMedia[0]['duration'];
				$EnclosureType = false;
				$UrlParts = parse_url($EnclosureURL);
				if( $UrlParts['path'] )
				{
					// using functions that already exist in Wordpress when possible:
					$ContentType = powerpress_get_contenttype($UrlParts['path']);
					if( $ContentType )
						$EnclosureType = $ContentType;
				}
				
				if( $EnclosureType && $EnclosureSize && $EnclosureURL )
					echo "\t\t".'<enclosure url="' . trim(htmlspecialchars($EnclosureURL)) . '" length="'. $EnclosureSize .'" type="'. $EnclosureType .'" />'.PHP_EOL;
				else
					return;
			}
			else
				return;
		}
		else
			return;
	}
	
	// Split the episode information into parts...
	list($EnclosureURL, $EnclosureSize, $EnclosureType, $Serialized) = explode("\n", $enclosureData, 4);
	$EnclosureURL = trim($EnclosureURL);
	$EnclosureType = trim($EnclosureType);
	$EnclosureSize = trim($EnclosureSize);
	
	// Check that the content type is a valid one...
	if( strstr($EnclosureType, '/') == false )
		$EnclosureType = powerpress_get_contenttype($EnclosureURL);
		
	$author = (isset($powerpress_feed['itunes_author_post'])?get_the_author() :$powerpress_feed['itunes_talent_name']);
	$explicit = $powerpress_feed['explicit'];
	$summary = false;
	$subtitle = false;
	$keywords = false;
	$block = $powerpress_feed['block'];
	
	if( $Serialized )
	{
		$EpisodeData = unserialize($Serialized);
		if( isset( $EpisodeData['duration'] ) )
			$duration = $EpisodeData['duration'];
		if( $powerpress_feed['itunes_custom'] )
		{
			if( isset( $EpisodeData['summary'] ) )
				$summary = $EpisodeData['summary'];
			if( isset( $EpisodeData['subtitle'] ) )
				$subtitle = $EpisodeData['subtitle'];
			if( isset( $EpisodeData['keywords'] ) )
				$keywords = $EpisodeData['keywords'];
			if( isset( $EpisodeData['explicit'] ) )
			{
				$explicit_array = array("no", "yes", "clean");
				$explicit = $explicit_array[$EpisodeData['explicit']];
			}
			
			// Code for future use:
			if( isset( $EpisodeData['author'] ) )
				$author = $EpisodeData['author'];
			if( isset( $EpisodeData['block'] ) )
				$block = $EpisodeData['block'];
		}
	}
	
	if( !$duration && !$custom_enclosure )
		$duration = get_post_meta($post->ID, 'itunes:duration', true);
		
	if( $custom_enclosure ) // We need to add the enclosure tag here...
	{
		$ModifiedURL = powerpress_add_redirect_url($EnclosureURL);
		echo "\t". sprintf('<enclosure url="%s" length="%d" type="%s" />%s',
			$ModifiedURL,
			$EnclosureSize,
			$EnclosureType,
			PHP_EOL);
	}
		
	// Get the post tags:
	if( !$keywords )
	{
		// Lets try to use the page tags...
		$tagobject = wp_get_post_tags( $post->ID );
		if( count($tagobject) )
		{
			$tags = array();
			for($c = 0; $c < count($tagobject) && $c < 12; $c++) // iTunes only accepts up to 12 keywords
				$tags[] = $tagobject[$c]->name;
			
			if( count($tags) > 0 )
				$keywords = implode(",", $tags);
		}
	}
	
	if( $keywords )
		echo "\t\t<itunes:keywords>" . powerpress_format_itunes_value($keywords, 'keywords') . '</itunes:keywords>'.PHP_EOL;
	
	// Strip and format the wordpress way, but don't apply any other filters for these itunes tags
	$content_no_html = $post->post_content;
	$content_no_html = strip_shortcodes( $content_no_html ); 
	$content_no_html = str_replace(']]>', ']]&gt;', $content_no_html);
	$content_no_html = strip_tags($content_no_html);
	
	$excerpt_no_html = strip_tags($post->post_excerpt);
	
	if( $subtitle )
		echo "\t\t<itunes:subtitle>". powerpress_format_itunes_value($subtitle, 'subtitle', true) .'</itunes:subtitle>'.PHP_EOL;
	else if( $excerpt_no_html )
		echo "\t\t<itunes:subtitle>". powerpress_format_itunes_value($excerpt_no_html, 'subtitle', true) .'</itunes:subtitle>'.PHP_EOL;
	else	
		echo "\t\t<itunes:subtitle>". powerpress_format_itunes_value($content_no_html, 'subtitle', true) .'</itunes:subtitle>'.PHP_EOL;
	
	
	if( $summary )
		echo "\t\t<itunes:summary>". powerpress_format_itunes_value($summary, 'summary') .'</itunes:summary>'.PHP_EOL;
	else if( $powerpress_feed['enhance_itunes_summary'] )
		echo "\t\t<itunes:summary>". powerpress_itunes_summary($post->post_content) .'</itunes:summary>'.PHP_EOL;
	else if( $excerpt_no_html )
		echo "\t\t<itunes:summary>". powerpress_format_itunes_value($excerpt_no_html, 'summary') .'</itunes:summary>'.PHP_EOL;
	else
		echo "\t\t<itunes:summary>". powerpress_format_itunes_value($content_no_html, 'summary') .'</itunes:summary>'.PHP_EOL;
	
	if( $author )
		echo "\t\t<itunes:author>" . wp_specialchars($author) . '</itunes:author>'.PHP_EOL;
	
	if( $explicit )
		echo "\t\t<itunes:explicit>" . $explicit . '</itunes:explicit>'.PHP_EOL;
	
	if( $duration && preg_match('/^(\d{1,2}:){0,2}\d{1,2}$/i', ltrim($duration, '0:') ) ) // Include duration if it is valid
		echo "\t\t<itunes:duration>" . ltrim($duration, '0:') . '</itunes:duration>'.PHP_EOL;
		
	if( $block && $block == 'yes' )
		echo "\t\t<itunes:block>yes</itunes:block>\n";
}

add_action('rss2_item', 'powerpress_rss2_item');

function powerpress_filter_rss_enclosure($content)
{
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
		return $content; // Another podcasting plugin is enabled...
		
		
	if( powerpress_is_custom_podcast_feed() && get_query_var('feed') != 'podcast' && !is_category() )
		return ''; // We will handle this enclosure in the powerpress_rss2_item() function

	$match_count = preg_match('/\surl="([^"]*)"/', $content, $matches);
	if( count($matches) != 2)
		return $content;
		
	// Original Media URL
	$OrigURL = $matches[1];
	
	if( substr($OrigURL, 0, 5) != 'http:' && substr($OrigURL, 0, 6) != 'https:' )
		return ''; // The URL value is invalid
		
	global $post, $powerpress_rss_enclosure_post_id;
	if( @$powerpress_rss_enclosure_post_id == $post->ID )
		return ''; // we've already included one enclosure, lets not allow anymore
	$powerpress_rss_enclosure_post_id = $post->ID;
	
	// Modified Media URL
	$ModifiedURL = powerpress_add_redirect_url($OrigURL);
	
	// Check that the content type is a valid one...
	$match_count = preg_match('/\stype="([^"]*)"/', $content, $matches);
	if( count($matches) > 1 && strstr($matches[1], '/') == false )
	{
		$ContentType = powerpress_get_contenttype($ModifiedURL);
		$content = str_replace("type=\"{$matches[1]}\"", "type=\"$ContentType\"", $content);
	}
	
	// Replace the original url with the modified one...
	if( $OrigURL != $ModifiedURL )
		return str_replace($OrigURL, $ModifiedURL, $content);
	return $content;
}


add_filter('rss_enclosure', 'powerpress_filter_rss_enclosure');

function powerpress_bloginfo_rss($content, $field = '')
{
	if( powerpress_is_custom_podcast_feed() )
	{
		if( is_category() )
			$Feed = get_option('powerpress_cat_feed_'.get_query_var('cat') );
		else
			$Feed = get_option('powerpress_feed_'.get_query_var('feed') );
		//$Feed = true;
		if( $Feed )
		{
			switch( $field )
			{
				case 'description': {
					if( isset($Feed['description']) && $Feed['description'] != '' )
						return $Feed['description'];
					else if( is_category() )
					{
						$category = get_category( get_query_var('cat') );
						if( $category->description )
							return $category->description;
					}
				}; break;
				case 'url': {
					if( isset($Feed['url']) && $Feed['url'] != '' )
						return trim($Feed['url']);
					else if( is_category() )
						return get_category_link( get_query_var('cat') );
				}; break;
				case 'name':
				default: {
					if( isset($Feed['title']) && $Feed['title'] != '' )
						return $Feed['title'];
				}; break;
			
			}
		}
	}
	
	return $content;
}

add_filter('get_bloginfo_rss', 'powerpress_bloginfo_rss', 10, 2);


function powerpress_wp_title_rss($title)
{
	if( powerpress_is_custom_podcast_feed() )
	{
		if( is_category() )
		{
			$Feed = get_option('powerpress_cat_feed_'.get_query_var('cat') );
			if( $Feed && isset($Feed['title']) && $Feed['title'] != '' )
				return ''; // We alrady did a custom title, lets not add the category to it...
		}
		else
		{
			return ''; // It is not a category, lets not mess with our beautiful title then
		}
	}
	return $title;
}

add_filter('wp_title_rss', 'powerpress_wp_title_rss');

function powerpress_rss_language($value)
{
	if( powerpress_is_custom_podcast_feed() )
	{
		global $powerpress_feed;
		if( $powerpress_feed && isset($powerpress_feed['rss_language']) && $powerpress_feed['rss_language'] != '' )
			$value = $powerpress_feed['rss_language'];
	}
	return $value;
}

add_filter('option_rss_language', 'powerpress_rss_language');


function powerpress_do_podcast_feed($for_comments=false)
{
	global $wp_query;
	$wp_query->get_posts();
	do_feed_rss2($for_comments);
}

function powerpress_template_redirect()
{
	if( is_feed() && powerpress_is_custom_podcast_feed() )
	{
		remove_action('template_redirect', 'ol_feed_redirect'); // Remove this action
		global $powerpress_feed;
		
		if( is_array($powerpress_feed) && trim(@$powerpress_feed['feed_redirect_url']) != '' && !preg_match("/feedburner|feedsqueezer|feedvalidator/i", $_SERVER['HTTP_USER_AGENT'] ) && @$_GET['redirect'] != 'no' )
		{
			if (function_exists('status_header'))
				status_header( 302 );
			header("Location: " . trim($powerpress_feed['feed_redirect_url']));
			header("HTTP/1.1 302 Temporary Redirect");
			exit();
		}
	}
}

add_action('template_redirect', 'powerpress_template_redirect', 0);

function powerpress_init()
{
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
		return false; // Another podcasting plugin is enabled...
	
	// Translation support loaded:
	load_plugin_textdomain('powerpress', false, dirname(plugin_basename(__FILE__)));
		
	if( isset($_GET['powerpress_pinw']) )
		powerpress_do_pinw($_GET['powerpress_pinw']);
		
	add_feed('podcast', 'powerpress_do_podcast_feed');
	
	$GeneralSettings = get_option('powerpress_general');
	
	// If we are to process podpress data..
	if( isset($GeneralSettings['process_podpress']) && $GeneralSettings['process_podpress'] )
	{
		powerpress_podpress_redirect_check();
		add_shortcode('display_podcast', 'powerpress_shortcode_handler');
	}
			
	if( $GeneralSettings && isset($GeneralSettings['custom_feeds']) && is_array($GeneralSettings['custom_feeds']) )
	{
		while( list($feed_slug,$feed_title) = each($GeneralSettings['custom_feeds']) )
		{
			if( $feed_slug != 'podcast' )
				add_feed($feed_slug, 'powerpress_do_podcast_feed');
		}
		
		// FeedSmith support...
		if( isset($_GET['feed']) )
		{
			switch($_GET['feed'])
			{
				case '':
				case 'feed':
				case 'atom':
				case 'rss':
				case 'comments-rss2':
				case 'rss2': break; // Let FeedSmith redirect these feeds if it wants
				default: { // Otherwise lets remove FeedSmith
					if( has_action('init', 'ol_check_url') !== false )
						remove_action('init', 'ol_check_url');
				}
			}
		}
	}
}

add_action('init', 'powerpress_init', 9);

// Load the general feed settings for feeds handled by powerpress
function powerpress_load_general_feed_settings()
{
	global $wp_query;
	global $powerpress_feed;
	
	if( $powerpress_feed !== false ) // If it is not false (either NULL or an array) then we already looked these settings up
	{
		$powerpress_feed = false;
		
		// Get the powerpress settings
		$GeneralSettings = get_option('powerpress_general');
		if( !isset($GeneralSettings['custom_feeds']['podcast']) )
			$GeneralSettings['custom_feeds']['podcast'] = 'Podcast Feed'; // Fixes scenario where the user never configured the custom default podcast feed.
		
		if( $GeneralSettings )
		{
			$FeedSettingsBasic = get_option('powerpress_feed'); // Get overall feed settings
				
			// If we're in advanced mode and we're dealing with a category feed we're extending, lets work with it...
			if( is_category() && is_array($GeneralSettings['custom_cat_feeds']) && in_array( get_query_var('cat'), $GeneralSettings['custom_cat_feeds']) )
			{
				$cat_ID = get_query_var('cat');
				$FeedCustom = get_option('powerpress_cat_feed_'.$cat_ID); // Get custom feed specific settings
				$Feed = powerpress_merge_empty_feed_settings($FeedCustom, $FeedSettingsBasic);
				
				$powerpress_feed = array();
				$powerpress_feed['is_custom'] = true;
				$powerpress_feed['itunes_custom'] = ($GeneralSettings['episode_box_mode'] == 2);
				$powerpress_feed['category'] = $cat_ID;
				$powerpress_feed['process_podpress'] = $GeneralSettings['process_podpress']; // Category feeds could originate from Podpress
				$powerpress_feed['rss_language'] = ''; // default, let WordPress set the language
				$powerpress_feed['default_url'] = rtrim($GeneralSettings['default_url'], '/') .'/';
				$explicit_array = array("no", "yes", "clean");
				$powerpress_feed['explicit'] = $explicit_array[$Feed['itunes_explicit']];
				if( $Feed['itunes_talent_name'] )
					$powerpress_feed['itunes_talent_name'] = $Feed['itunes_talent_name'];
				else
					$powerpress_feed['itunes_talent_name'] = get_bloginfo_rss('name');
				$powerpress_feed['enhance_itunes_summary'] = @$Feed['enhance_itunes_summary'];
				$powerpress_feed['posts_per_rss'] = $Feed['posts_per_rss'];
				if( $Feed['feed_redirect_url'] != '' )
					$powerpress_feed['feed_redirect_url'] = $Feed['feed_redirect_url'];
				if( $Feed['itunes_author_post'] == true )
					$powerpress_feed['itunes_author_post'] = true;
				if( $Feed['rss_language'] != '' )
					$powerpress_feed['rss_language'] = $Feed['rss_language'];
				return;
			}
			
			$feed_slug = get_query_var('feed');
			
			if( isset($GeneralSettings['custom_feeds']) && is_array($GeneralSettings['custom_feeds']) && isset($GeneralSettings['custom_feeds'][ $feed_slug ] ))
			{
				$FeedCustom = get_option('powerpress_feed_'.$feed_slug); // Get custom feed specific settings
				$Feed = powerpress_merge_empty_feed_settings($FeedCustom, $FeedSettingsBasic);
				
				$powerpress_feed = array();
				$powerpress_feed['is_custom'] = true;
				$powerpress_feed['itunes_custom'] = ($GeneralSettings['episode_box_mode'] == 2);
				$powerpress_feed['feed-slug'] = $feed_slug;
				$powerpress_feed['process_podpress'] = ($feed_slug=='podcast'? $GeneralSettings['process_podpress']: false); // We don't touch podpress data for custom feeds
				$powerpress_feed['rss_language'] = ''; // RSS language should be set by WordPress by default
				$powerpress_feed['default_url'] = rtrim($GeneralSettings['default_url'], '/') .'/';
				$explicit = array("no", "yes", "clean");
				$powerpress_feed['explicit'] = $explicit[$Feed['itunes_explicit']];
				if( $Feed['itunes_talent_name'] )
					$powerpress_feed['itunes_talent_name'] = $Feed['itunes_talent_name'];
				else
					$powerpress_feed['itunes_talent_name'] = get_bloginfo_rss('name');
				if( version_compare( '5', phpversion(), '>=' ) )
					$powerpress_feed['enhance_itunes_summary'] = 0;
				else
					$powerpress_feed['enhance_itunes_summary'] = @$Feed['enhance_itunes_summary'];
				$powerpress_feed['posts_per_rss'] = $Feed['posts_per_rss'];
				if( $Feed['feed_redirect_url'] != '' )
					$powerpress_feed['feed_redirect_url'] = $Feed['feed_redirect_url'];
				if( $Feed['rss_language'] != '' )
					$powerpress_feed['rss_language'] = $Feed['rss_language'];
				return;
			}
			
			// We fell this far,we must be in simple mode or the user never saved customized their custom feed settings
			switch( $FeedSettingsBasic['apply_to'] )
			{
				case 0: // enhance only the podcast feed added by PowerPress, with the logic above this code should never be reached but it is added for readability.
				{
					if( $feed_slug != 'podcast' )
						break;
				} // important: no break here!
				case 2: // RSS2 Main feed and podcast feed added by PowerPress only
				{
					if( $feed_slug != 'feed' && $feed_slug != 'rss2' && $feed_slug != 'podcast' )
						break; // We're only adding podcasts to the rss2 feed in this situation
					
					if( $wp_query->is_category ) // don't touch the category feeds...
						break;
					
					if( $wp_query->is_tag ) // don't touch the tag feeds...
						break;
						
					if( $wp_query->is_comment_feed ) // don't touch the comments feeds...
						break;	
				} // important: no break here!
				case 1: // All feeds
				{
					$powerpress_feed = array(); // Only store what's needed for each feed item
					$powerpress_feed['is_custom'] = false; // ($feed_slug == 'podcast'?true:false);
					$powerpress_feed['itunes_custom'] = ($GeneralSettings['episode_box_mode'] == 2);
					$powerpress_feed['feed-slug'] = $feed_slug;
					$powerpress_feed['process_podpress'] = $GeneralSettings['process_podpress']; // We don't touch podpress data for custom feeds
					$powerpress_feed['default_url'] = rtrim($GeneralSettings['default_url'], '/') .'/';
					$explicit = array("no", "yes", "clean");
					$powerpress_feed['explicit'] = $explicit[$FeedSettingsBasic['itunes_explicit']];
					if( $FeedSettingsBasic['itunes_talent_name'] )
						$powerpress_feed['itunes_talent_name'] = $FeedSettingsBasic['itunes_talent_name'];
					else
						$powerpress_feed['itunes_talent_name'] = get_bloginfo_rss('name');
					if( version_compare( '5', phpversion(), '>=' ) )
						$powerpress_feed['enhance_itunes_summary'] = 0;
					else
						$powerpress_feed['enhance_itunes_summary'] = @$FeedSettingsBasic['enhance_itunes_summary'];
					$powerpress_feed['posts_per_rss'] = $FeedSettingsBasic['posts_per_rss'];
					$powerpress_feed['rss_language'] = ''; // Cannot set the language setting in simple mode
				}; break;
				// All other cases we let fall through
			}
		}
	}
}

// Returns true of the feed should be treated as a podcast feed
function powerpress_is_podcast_feed()
{
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
		return false; // Another podcasting plugin is enabled...
	
	global $powerpress_feed;
	if( $powerpress_feed !== false && !is_array($powerpress_feed) )
		powerpress_load_general_feed_settings();
	if( $powerpress_feed === false )
		return false;
	return true;
}

// Returns true if the feed is a custom feed added by PowerPress
function powerpress_is_custom_podcast_feed()
{
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) || isset($GLOBALS['podcast_channel_active']) || defined('PODCASTING_VERSION') )
		return false; // Another podcasting plugin is enabled...
		
	global $powerpress_feed;
	if( $powerpress_feed !== false && !is_array($powerpress_feed) )
		powerpress_load_general_feed_settings();
	if( $powerpress_feed === false )
		return false;
	return $powerpress_feed['is_custom'];
}

function powerpress_posts_join($join)
{	
	if( is_category() )
		return $join;
		
	if( is_feed() && (powerpress_is_custom_podcast_feed() || get_query_var('feed') == 'podcast' ) && !is_category() )
	{
		global $wpdb;
		$join .= " INNER JOIN {$wpdb->postmeta} ";
		$join .= " ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
	}
  return $join;
}

add_filter('posts_join', 'powerpress_posts_join' );

function powerpress_posts_where($where)
{
	if( is_category() )
		return $where;
		
	if( is_feed() && (powerpress_is_custom_podcast_feed() || get_query_var('feed') == 'podcast' ) )
	{
		global $wpdb, $powerpress_feed;
		$where .= " AND (";
		
		if( powerpress_is_custom_podcast_feed() && get_query_var('feed') != 'podcast' && !is_category() )
			$where .= " {$wpdb->postmeta}.meta_key = '_". get_query_var('feed') .":enclosure' ";
		else	
			$where .= " {$wpdb->postmeta}.meta_key = 'enclosure' ";
	
		// Include Podpress data if exists...
		if( $powerpress_feed['process_podpress'] && get_query_var('feed') == 'podcast' )
			$where .= " OR {$wpdb->postmeta}.meta_key = 'podPressMedia' ";
		
		$where .= ") ";
	}
	return $where;
}

add_filter('posts_where', 'powerpress_posts_where' );

// Add the groupby needed for enclosures only
function powerpress_posts_groupby($groupby)
{
	if( is_category() )
		return $groupby;
		
	if( is_feed() && (powerpress_is_custom_podcast_feed() || get_query_var('feed') == 'podcast' ) )
	{
		global $wpdb;
		$groupby = " {$wpdb->posts}.ID ";
	}
	return $groupby;
}
add_filter('posts_groupby', 'powerpress_posts_groupby');

function powerpress_post_limits($limits)
{
	if( is_feed() && powerpress_is_custom_podcast_feed() )
	{
		global $powerpress_feed;
		if( $powerpress_feed['posts_per_rss'] && preg_match('/^(\d)+$/', trim($powerpress_feed['posts_per_rss'])) )
			$limits = "LIMIT 0, {$powerpress_feed['posts_per_rss']}";
	}
	return $limits;
}
add_filter('post_limits', 'powerpress_post_limits');


function powerpress_do_all_pings()
{
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_encloseme' ");
	
	// Now call the WordPress do_all_pings()...
	do_all_pings();
	remove_action('do_pings', 'do_all_pings');
}

remove_action('do_pings', 'do_all_pings');
add_action('do_pings', 'powerpress_do_all_pings', 1, 1);

function powerpress_future_to_publish($post)
{
	// Perform iTunes ping here if configured...
	if( !is_admin() )
	{ // If the future_to_publish is fired by a web visitor, we need to include the administration code so the iTunes ping goes as planned.
		$Settings = get_option('powerpress_general');
		if( isset($Settings['ping_itunes']) && $Settings['ping_itunes'] )
		{
			require_once(dirname(__FILE__).'/powerpressadmin.php');
		}
	}
}

add_action('future_to_publish', 'powerpress_future_to_publish');

function powerpress_player_filter($content, $media_url, $ExtraData = array() )
{
	global $g_powerpress_player_id;
	if( !isset($g_powerpress_player_id) )
		$g_powerpress_player_id = rand(0, 10000);
	else
		$g_powerpress_player_id++;
		
	// Very important setting, we need to know if the media should auto play or not...
	$autoplay = false; // (default)
	if( isset($ExtraData['autoplay']) && $ExtraData['autoplay'] )
		$autoplay = true;
	$cover_image = '';
	if( $ExtraData['image'] )
		$cover_image = $ExtraData['image'];
	
	// Based on $ExtraData, we can determine which type of player to handle here...
	$Settings = get_option('powerpress_general');
	if( !isset($Settings['player_function']) )
		$Settings['player_function'] = 1;
	$player_width = 320;
	$player_height = 240;
	if( isset($Settings['player_width']) && $Settings['player_width'] )
		$player_width = $Settings['player_width'];
	if( isset($Settings['player_height']) && $Settings['player_height'] )
		$player_height = $Settings['player_height'];
	
	// Used with some types
	//$content_type = powerpress_get_contenttype($media_url);
	
	$parts = pathinfo($media_url);
	// Hack to use the audio/mp3 content type to set extension to mp3, some folks use tinyurl.com to mp3 files which remove the file extension...
	// This hack only covers mp3s.
	if( isset($EpisdoeData['type']) && $EpisdoeData['type'] == 'audio/mpeg' && $parts['extension'] != 'mp3' )
		$parts['extension'] = 'mp3';
		
	if( !defined('POWERPRESS_PLAYER') )
		$Settings['player'] = 'default'; // Use the defaul player in case the POWERPRESS_PLAYER is not defined
	
	switch( strtolower($parts['extension']) )
	{
		// Flash Player:
		case 'mp3':
			
			// FlowPlayer has differeent sizes for audio than for video
			if( isset($Settings['player_width_audio']) && $Settings['player_width_audio'] )
				$player_width = $Settings['player_width_audio'];
			if( $cover_image == '' )
				$player_height = 24;
				
			// Check if we are using a custom flash player...
			if( isset($Settings['player']) && $Settings['player'] != 'default' ) // If we are using some other flash player than the default flash player
				return $content;
				
		case 'flv': {
			
			// Okay we are supposed to use FlowPlayerClassic...
			$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'"></div>'.PHP_EOL;
			$content .= '<script type="text/javascript">'.PHP_EOL;
			$content .= "pp_flashembed(\n";
			$content .= "	'powerpress_player_{$g_powerpress_player_id}',\n";
			$content .= "	{src: '". powerpress_get_root_url() ."FlowPlayerClassic.swf', width: {$player_width}, height: {$player_height}, wmode: 'transparent' },\n";
			if( $cover_image ) // 
				$content .= "	{config: { autoPlay: ". ($autoplay?'true':'false') .", autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '{$media_url}', splashImageFile: '{$cover_image}', scaleSplash: true, loop: false, autoRewind: true } }\n";
			else
				$content .= "	{config: { autoPlay: ". ($autoplay?'true':'false') .", autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '{$media_url}', loop: false, autoRewind: true } }\n";
			$content .= ");\n";
			$content .= "</script>\n";
		
		}; break;
			
		// Quicktime:
		case 'm4v':
		case 'm4a':
		case 'avi':
		case 'mpg':
		case 'mpeg':
		case 'mp4':
		case 'm4b':
		case 'm4r':
		case 'qt':
		case 'mov': {
			
			// If there is no cover image specified, lets use the default...
			if( $cover_image == '' )
				$cover_image = powerpress_get_root_url() . 'play_video_default.jpg';
			
			if( $autoplay )
			{
				$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'"></div>'.PHP_EOL;
				$content .= '<script type="text/javascript">'.PHP_EOL;
				$content .= "powerpress_embed_quicktime('powerpress_player_{$g_powerpress_player_id}', '{$media_url}', {$player_width}, {$player_height} );\n";
				$content .= "</script>\n";
			}
			else
			{
				$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">'.PHP_EOL;
				$content .= '<a href="'. $media_url .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" onclick="';
				$content .= "return powerpress_embed_quicktime('powerpress_player_{$g_powerpress_player_id}', '{$media_url}', {$player_width}, {$player_height} );";
				$content .= '">';
				$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" />';
				$content .= '</a>';
				$content .= "</div>\n";
			}
			
		}; break;
		
		// Windows Media:
		case 'wma':
		case 'wmv':
		case 'asf': {
			
			$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">';
			$firefox = (stristr($_SERVER['HTTP_USER_AGENT'], 'firefox') !== false );
			
			if( (!$cover_image && !$firefox ) || $autoplay ) // if we don't have a cover image or we're supposed to auto play the media anyway...
			{
				$content .= '<object id="winplayer" classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'. $player_width .'" height="'. $player_height .'" standby="..." type="application/x-oleobject">';
				$content .= '	<param name="url" value="'. $media_url .'" />';
				$content .= '	<param name="AutoStart" value="'. ($autoplay?'true':'false') .'" />';
				$content .= '	<param name="AutoSize" value="true" />';
				$content .= '	<param name="AllowChangeDisplaySize" value="true" />';
				$content .= '	<param name="standby" value="Media is loading..." />';
				$content .= '	<param name="AnimationAtStart" value="true" />';
				$content .= '	<param name="scale" value="aspect" />';
				$content .= '	<param name="ShowControls" value="true" />';
				$content .= '	<param name="ShowCaptioning" value="false" />';
				$content .= '	<param name="ShowDisplay" value="false" />';
				$content .= '	<param name="ShowStatusBar" value="false" />';
				$content .= '	<embed type="application/x-mplayer2" src="'. $media_url .'" width="'. $player_width .'" height="'. $player_height .'" scale="ASPECT" autostart="'. ($autoplay?'1':'0') .'" ShowDisplay="0" ShowStatusBar="0" autosize="1" AnimationAtStart="1" AllowChangeDisplaySize="1" ShowControls="1"></embed>';
				$content .= '</object>';
			}
			else
			{
				if( $cover_image == '' )
					$cover_image = powerpress_get_root_url() . 'play_video_default.jpg';
				
				$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">'.PHP_EOL;
				$content .= '<a href="'. $media_url .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" onclick="';
				$content .= "return powerpress_embed_winplayer('powerpress_player_{$g_powerpress_player_id}', '{$media_url}', {$player_width}, {$player_height} );";
				$content .= '">';
				$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" border="0" />';
				$content .= '</a>';
				$content .= "</div>\n";
			}
			
			if( $firefox )
			{
				$content .= '<p style="font-size: 85%;margin-top:0;">'. __('Best viewed with');
				$content .= ' <a href="http://support.mozilla.com/en-US/kb/Using+the+Windows+Media+Player+plugin+with+Firefox#Installing_the_plugin" target="_blank">';
				$content .= __('Windows Media Player plugin for Firefox') .'</a></p>';
			}
			
			$content .= "</div>\n";
			
		}; break;
		
		// Flash:
		case 'swf': {
			
			// If there is no cover image specified, lets use the default...
			if( $cover_image == '' )
				$cover_image = powerpress_get_root_url() . 'play_video_default.jpg';
			
			$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">';
			if( !$autoplay )
			{
				$content .= '<a href="'. $media_url .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" onclick="';
				$content .= "return powerpress_embed_swf('powerpress_player_{$g_powerpress_player_id}', '{$media_url}', {$player_width}, {$player_height} );";
				$content .= '">';
				$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" />';
				$content .= '</a>';
			}
			$content .= "</div>\n";
			if( $autoplay )
			{
				$content .= '<script type="text/javascript">'.PHP_EOL;
				$content .= "powerpress_embed_swf('powerpress_player_{$g_powerpress_player_id}', '{$media_url}', {$player_width}, {$player_height} );\n";
				$content .= "</script>\n";
			}
			
		}; break;
			
		// Default, just display the play image. If it is set for auto play, then we don't wnat to open a new window, otherwise we want to open this in a new window..
		default: {
		
			if( $cover_image == '' )
				$cover_image = powerpress_get_root_url() . 'play_video_default.jpg';
			
			$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">';
			$content .= '<a href="'. $media_url .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'"'. ($autoplay?'':' target="_blank"') .'>';
			$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" border="0" />';
			$content .= '</a>';
			$content .= "</div>\n";
			
		}; break;
	}
	
	return $content;
}

add_filter('powerpress_player', 'powerpress_player_filter', 10, 3);

function powerpress_shortcode_handler( $attributes, $content = null )
{
	global $post, $g_powerpress_player_added;
	
	// We can't add flash players to feeds
	if( is_feed() )
		return '';
	
	$return = '';
	$feed = '';
	$url = '';
	$image = '';
	
	extract( shortcode_atts( array(
			'url' => '',
			'feed' => '',
			'image' => ''
		), $attributes ) );
	
	if( $url )
	{
		$url = powerpress_add_redirect_url($url);
		$content_type = '';
		// Handle the URL differently...
		$return = apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($url, 'p'), array('image'=>$image, 'type'=>$content_type) );
	}
	else if( $feed )
	{
		$EpisdoeData = powerpress_get_enclosure_data($post->ID, $feed);
		if( isset($EpisdoeData['embed']) && $EpisdoeData['embed'] )
			$return = $EpisdoeData['embed'];
		if( !isset($EpisdoeData['no_player']) )
			$return = apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($EpisdoeData['url'], 'p'), array('feed'=>$feed, 'image'=>$image, 'type'=>$EpisdoeData['type']) );
	}
	else
	{
		$GeneralSettings = get_option('powerpress_general');
		if( !isset($GeneralSettings['custom_feeds']['podcast']) )
			$GeneralSettings['custom_feeds']['podcast'] = 'Podcast Feed'; // Fixes scenario where the user never configured the custom default podcast feed.
		
		while( list($feed_slug,$feed_title)  = each($GeneralSettings['custom_feeds']) )
		{
			if( isset($GeneralSettings['disable_player']) && isset($GeneralSettings['disable_player'][$feed_slug]) )
				continue;
			
			$EpisdoeData = powerpress_get_enclosure_data($post->ID, $feed_slug);
			if( isset($EpisdoeData['embed']) && $EpisdoeData['embed'] )
				$return .= $EpisdoeData['embed'];
			
			if( !isset($EpisdoeData['no_player']) )
				$return .= apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($EpisdoeData['url'], 'p'), array('feed'=>$feed_slug, 'image'=>$image, 'type'=>$EpisdoeData['type']) );
		}
	}
	
	return $return;
}

add_shortcode('powerpress', 'powerpress_shortcode_handler');


/*
Helper functions:
*/

function powerpress_podpress_redirect_check()
{
	if( preg_match('/podpress_trac\/([^\/]+)\/([^\/]+)\/([^\/]+)\/(.*)$/', $_SERVER['REQUEST_URI'], $matches) )
	{
		$post_id = $matches[2];
		$mediaNum = $matches[3];
		//$filename = $matches[4];
		//$method = $matches[1];
		
		if( is_numeric($post_id) && is_numeric($mediaNum))
		{
			$podPressMedia = powerpress_get_post_meta($post_id, 'podPressMedia');
			if( !is_array($podPressMedia) )
			{
				// Sometimes the stored data gets messed up, we can fix it here:
				$podPressMedia = powerpress_repair_serialize($podPressMedia);
				$podPressMedia = @unserialize($podPressMedia);
			}
			
			if( $podPressMedia && isset($podPressMedia[$mediaNum]) && isset($podPressMedia[$mediaNum]['URI']) )
			{
				$EnclosureURL = $podPressMedia[$mediaNum]['URI'];
				if( strpos($EnclosureURL, 'http://' ) !== 0 && strpos($EnclosureURL, 'https://' ) !== 0 )
				{
					$media_url = '';
					$PodpressSettings = get_option('podPress_config');
					if( $PodpressSettings && isset($PodpressSettings['mediaWebPath']) )
					{
						$media_url = rtrim($PodpressSettings['mediaWebPath'], '/') . '/'; // Make sure the URL has a trailing slash
					}
					else
					{
						$Settings = get_option('powerpress_general');
						if( isset($Settings['default_url']) )
							$media_url = rtrim($Settings['default_url'], '/') . '/'; // Make sure the URL has a trailing slash
					}
					$EnclosureURL = $media_url . $EnclosureURL;
				}
				
				if( strpos($EnclosureURL, 'http://' ) !== 0 && strpos($EnclosureURL, 'https://' ) !== 0 )
				{
					die('Error occurred obtaining the URL for the requested media file.');
					exit;
				}
				
				$EnclosureURL = str_replace(' ', '%20', $EnclosureURL);
				header('Location: '.$EnclosureURL, true, 302);
				header('Content-Length: 0');
				exit;
			}
			// Let the WordPress 404 page load as normal
		}
	}
}

function the_powerpress_content()
{
	echo get_the_powerpress_content();
}

function get_the_powerpress_content()
{
	return powerpress_content('');
}

function powerpress_do_pinw($pinw)
{
	list($post_id, $feed_slug) = split('-', $pinw, 2);
	$EpisdoeData = powerpress_get_enclosure_data($post_id, $feed_slug);
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Blubrry PowerPress Player</title>
<?php wp_head(); ?>
<style type="text/css">
body { font-size: 13px; font-family: Arial, Helvetica, sans-serif; }
</style>
</head>
<body>
<div style="margin: 5px;">
<?php

	if( !$EpisdoeData )
	{
		echo '<p>Unable to retrieve media information.</p>';
	}
	else if( isset($EpisdoeData['embed']) && $EpisdoeData['embed'] )
	{
		echo $EpisdoeData['embed'];
	}
	else //  if( !isset($EpisdoeData['no_player']) ) // Even if there is no player set, if the play in new window option is enabled then it should play here...
	{
		echo apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($EpisdoeData['url'], 'p'), array('feed'=>$feed_slug, 'autoplay'=>true, 'type'=>$EpisdoeData['type']) );
	}
	
?>
</div>
</body>
</html>
<?php
	exit;
}

// Adds content types that are missing from the default wp_check_filetype function
function powerpress_get_contenttype($file, $use_wp_check_filetype = true)
{
	$parts = pathinfo($file);
	switch( strtolower($parts['extension']) )
	{
		// Audio formats
		case 'mp3': // most common
		case 'mpga':
		case 'mp2':
		case 'mp2a':
		case 'm2a':
		case 'm3a':
			return 'audio/mpeg';
		case 'm4a':
			return 'audio/x-m4a';
		case 'm4b': // Audio book format
			return 'audio/m4b';
		case 'm4r': // iPhone ringtone format
			return 'audio/m4r';
		case 'ogg':
			return 'audio/ogg';
		case 'wma':
			return 'audio/x-ms-wma';
		case 'wax':
			return 'audio/x-ms-wax';
		case 'ra':
		case 'ram':
			return 'audio/x-pn-realaudio';
		case 'mp4a':
			return 'audio/mp4';
			
		// Video formats
		case 'm4v':
			return 'video/x-m4v';
		case 'mpeg':
		case 'mpg':
		case 'mpe':
		case 'm1v':
		case 'm2v':
			return 'video/mpeg';
		case 'mp4':
		case 'mp4v':
		case 'mpg4':
			return 'video/mp4';
		case 'asf':
		case 'asx':
			return 'video/x-ms-asf';
		case 'wmx':
			return 'video/x-ms-wmx';
		case 'avi':
			return 'video/x-msvideo';
		case 'wmv':
			return 'video/x-ms-wmv'; // Check this
		case 'flv':
			return 'video/x-flv';
		case 'swf':
			return 'application/x-shockwave-flash';
		case 'mov':
		case 'qt':
			return 'video/quicktime';
		case 'divx':
			return 'video/divx';
		case '3gp':
			return 'video/3gpp';
		
		// rarely used
		case 'mid':
		case 'midi':
			return'audio/midi';
		case 'wav':
			return 'audio/wav';
		case 'aa':
			return 'audio/audible';
		case 'pdf':
			return 'application/pdf';
		case 'torrent':
			return 'application/x-bittorrent';
		default: // Let it fall through
	}
	
	// Last case let wordpress detect it:
	if( $use_wp_check_filetype )
	{
		$FileType = wp_check_filetype($file);
		if( $FileType && isset($FileType['type']) )
			return $FileType['type'];
	}
	return '';
}

function powerpress_itunes_summary($html)
{
	// Do some smart conversion of the post html to readable text without HTML.
	
	// First, convert: <a href="link"...>label</a>
	// to: label (link)
	$html = preg_replace_callback('/(\<a[^\>]*href="([^"]*)"[^\>]*>([^\<]*)<\/a\>)/i', 
				create_function(
					'$matches',
					'return "{$matches[3]} ({$matches[2]})";'
			), 
				$html);
	
	// Second, convert: <img src="link" title="title" />
	// to: if no title (image: link) or (image title: link)
	$html = preg_replace_callback('/(\<img[^\>]*src="([^"]*)"[^\>]*[^\>]*\>)/i', 
				create_function(
					'$matches',
					'return "({$matches[2]})";'
			), 
				$html);
	
	// Third, convert <ul><li> to <li>* 
	// TODO:
	
	// Last, and the hardest, convert <ol><li> to numbers, this will be the hardest
	// TODO:
	
	// For now make them bullet points...
	$html = str_replace('<li>', '<li>* ', $html);
	
	// Now do all the other regular conversions...
	$html = strip_shortcodes( $html ); 
	$html = str_replace(']]>', ']]&gt;', $html);
	$content_no_html = strip_tags($html);

	return powerpress_format_itunes_value($content_no_html, 'summary');
}

function powerpress_itunes_categories($PrefixSubCategories = false)
{
	$temp = array();
	$temp['01-00'] = 'Arts';
		$temp['01-01'] = 'Design';
		$temp['01-02'] = 'Fashion & Beauty';
		$temp['01-03'] = 'Food';
		$temp['01-04'] = 'Literature';
		$temp['01-05'] = 'Performing Arts';
		$temp['01-06'] = 'Visual Arts';

	$temp['02-00'] = 'Business';
		$temp['02-01'] = 'Business News';
		$temp['02-02'] = 'Careers';
		$temp['02-03'] = 'Investing';
		$temp['02-04'] = 'Management & Marketing';
		$temp['02-05'] = 'Shopping';

	$temp['03-00'] = 'Comedy';

	$temp['04-00'] = 'Education';
		$temp['04-01'] = 'Education Technology';
		$temp['04-02'] = 'Higher Education';
		$temp['04-03'] = 'K-12';
		$temp['04-04'] = 'Language Courses';
		$temp['04-05'] = 'Training';
		 
	$temp['05-00'] = 'Games & Hobbies';
		$temp['05-01'] = 'Automotive';
		$temp['05-02'] = 'Aviation';
		$temp['05-03'] = 'Hobbies';
		$temp['05-04'] = 'Other Games';
		$temp['05-05'] = 'Video Games';

	$temp['06-00'] = 'Government & Organizations';
		$temp['06-01'] = 'Local';
		$temp['06-02'] = 'National';
		$temp['06-03'] = 'Non-Profit';
		$temp['06-04'] = 'Regional';

	$temp['07-00'] = 'Health';
		$temp['07-01'] = 'Alternative Health';
		$temp['07-02'] = 'Fitness & Nutrition';
		$temp['07-03'] = 'Self-Help';
		$temp['07-04'] = 'Sexuality';

	$temp['08-00'] = 'Kids & Family';
 
	$temp['09-00'] = 'Music';
 
	$temp['10-00'] = 'News & Politics';
 
	$temp['11-00'] = 'Religion & Spirituality';
		$temp['11-01'] = 'Buddhism';
		$temp['11-02'] = 'Christianity';
		$temp['11-03'] = 'Hinduism';
		$temp['11-04'] = 'Islam';
		$temp['11-05'] = 'Judaism';
		$temp['11-06'] = 'Other';
		$temp['11-07'] = 'Spirituality';
	 
	$temp['12-00'] = 'Science & Medicine';
		$temp['12-01'] = 'Medicine';
		$temp['12-02'] = 'Natural Sciences';
		$temp['12-03'] = 'Social Sciences';
	 
	$temp['13-00'] = 'Society & Culture';
		$temp['13-01'] = 'History';
		$temp['13-02'] = 'Personal Journals';
		$temp['13-03'] = 'Philosophy';
		$temp['13-04'] = 'Places & Travel';

	$temp['14-00'] = 'Sports & Recreation';
		$temp['14-01'] = 'Amateur';
		$temp['14-02'] = 'College & High School';
		$temp['14-03'] = 'Outdoor';
		$temp['14-04'] = 'Professional';
		 
	$temp['15-00'] = 'Technology';
		$temp['15-01'] = 'Gadgets';
		$temp['15-02'] = 'Tech News';
		$temp['15-03'] = 'Podcasting';
		$temp['15-04'] = 'Software How-To';

	$temp['16-00'] = 'TV & Film';

	if( $PrefixSubCategories )
	{
		while( list($key,$val) = each($temp) )
		{
			$parts = split('-', $key);
			$cat = $parts[0];
			$subcat = $parts[1];
		 
			if( $subcat != '00' )
				$temp[$key] = $temp[$cat.'-00'].' > '.$val;
		}
		reset($temp);
	}
 
	return $temp;
}

function powerpress_get_root_url()
{
	$powerpress_dirname = basename( dirname(__FILE__) );
	return WP_PLUGIN_URL . '/'. $powerpress_dirname .'/';
}

function powerpress_format_itunes_value($value, $tag = 255, $remove_new_lines = false)
{
	if( DB_CHARSET != 'utf8' ) // Check if the string is UTF-8
		$value = utf8_encode($value); // If it is not, convert to UTF-8 then decode it...
	
	// Code added to solve issue with KimiliFlashEmbed plugin and also remove the shortcode for the WP Audio Player
	// 99.9% of the time this code will not be necessary
	$value = preg_replace("/\[(kml_(flash|swf)embed|audio\:)\b(.*?)(?:(\/))?(\]|$)/isu", '', $value);
	
	if(version_compare("5", phpversion(), ">"))
		$value = preg_replace( '/&nbsp;/ui' , ' ', $value); // Best we can do for PHP4
	else
		$value = @html_entity_decode($value, ENT_COMPAT, 'UTF-8'); // Remove any additional entities such as &nbsp;
	$value = preg_replace( '/&amp;/ui' , '&', $value); // Best we can do for PHP4. precaution in case it didn't get removed from function above.
	
	if( $remove_new_lines )
		$value = preg_replace( array("/\r\n\r\n/u", "/\n/u", "/\r/u", "/\t/u") , array(' - ',' ', '', '  '), $value);
	
	return wp_specialchars( powerpress_trim_itunes_value($value, $tag) );
}

function powerpress_trim_itunes_value($value, $tag = 'summary')
{
	$length = (function_exists('mb_strlen')?mb_strlen($value):strlen($value) );
	$trim_at = false;
	switch($tag)
	{
		case 'summary': {
			// 4000 character limit
			if( $length > 4000 )
				$trim_at = 4000;
		}; break;
		case 'subtitle':
		case 'keywords':
		case 'author':
		case 'name':
		default: {
			// 255 character limit
			if( $length > 255 )
				$trim_at = 255;
		};
	}
	
	if( $trim_at )
	{
		// Start trimming
		$value = (function_exists('mb_substr')?mb_substr($value, 0, $trim_at):substr($value, 0, $trim_at) );
		$clean_break = false;
		if( preg_match('/(.*[,\n.\?!])[^,\n.\?!]/isu', $value, $matches) ) // pattern modifiers: case (i)nsensitive, entire (s)tring and (u)nicode
		{
			if( isset( $matches[1]) )
			{
				$detected_eof_pos = (function_exists('mb_strlen')?mb_strlen($matches[1]):strlen($matches[1]) );
				// Look back at most 50 characters...
				if( $detected_eof_pos > 3950 || ($detected_eof_pos > 205 && $detected_eof_pos < 255 ) )
				{
					$value = $matches[1];
					$clean_break = true;
				}
				// Otherwise we want to continue with the same value we started with...
			}
		}
		
		if( $clean_break == false && $tag = 'subtitle' ) // Subtitle we want to add a ... at the end
			$value = (function_exists('mb_substr')?mb_substr($value, 0, 252):substr($value, 0, 252) ). '...';
	}
	
	return $value;
}

function powerpress_smart_trim($value, $char_limit = 250, $remove_new_lines = false)
{
	if( strlen($value) > $char_limit )
	{
		$new_value = substr($value, 0, $char_limit);
		// Look back at most 50 characters...
		$eos = strrpos($new_value, '.');
		$eol = strrpos($new_value, "\n");

		// If the end of line is longer than the end of sentence and we're not loosing too much of our string...
		if( $eol > $eos && $eol > (strlen($new_value)-50) )
			$return = substr($new_value, 0, $eol);
		// If the end of sentence is longer than the end of line and we're not loosing too much of our string...
		else if( $eos > $eol && $eos > (strlen($new_value)-50) )
			$return = substr($new_value, 0, $eos);
		else // Otherwise, just add some dots to the end
			$return = substr($new_value, 0, $char_limit).'...';
		//	$return = $new_value;
	}
	else
	{
		$return = $value;
	}

	if( $remove_new_lines )
		$return = str_replace( array("\r\n\r\n", "\n", "\r", "\t"), array(' - ',' ', '', '  '), $return );
	return $return;
}

function powerpress_add_redirect_url($MediaURL, $GeneralSettings = false)
{
	if( preg_match('/^http\:/i', $MediaURL) === false )
		return $MediaURL; // If the user is hosting media not via http (e.g. https or ftp) then we can't handle the redirect
		
	$NewURL = $MediaURL;
	if( !$GeneralSettings ) // Get the general settings if not passed to this function, maintain the settings globally for further use
	{
		global $powerpress_general_settings;
		if( !$powerpress_general_settings )
			$powerpress_general_settings = get_option('powerpress_general');
		$GeneralSettings = $powerpress_general_settings;
	}
	
	for( $x = 3; $x > 0; $x-- )
	{
		$key = sprintf('redirect%d', $x);
		if( $GeneralSettings[ $key ] )
		{
			$RedirectClean = str_replace('http://', '', trim($GeneralSettings[ $key ]) );
			if( !strstr($NewURL, $RedirectClean) )
				$NewURL = 'http://'. $RedirectClean . str_replace('http://', '', $NewURL);
		}
	}

	return $NewURL;
}

function powerpress_add_flag_to_redirect_url($MediaURL, $Flag)
{
	return preg_replace('/(media\.(blubrry|techpodcasts|rawvoice|podcasternews)\.com\/[A-Za-z0-9-_]+\/)/i', '$1'."$Flag/", $MediaURL);
}

/*
Code contributed from upekshapriya on the Blubrry Forums
*/
function powerpress_byte_size($ppbytes) 
{
	$ppsize = $ppbytes / 1024;
	if($ppsize < 1024)
	{
		$ppsize = number_format($ppsize, 1);
		$ppsize .= 'KB';
	} 
	else 
	{
		if($ppsize / 1024 < 1024) 
		{
			$ppsize = number_format($ppsize / 1024, 1);
			$ppsize .= 'MB';
		}
		else if ($ppsize / 1024 / 1024 < 1024)   
		{
		$ppsize = number_format($ppsize / 1024 / 1024, 1);
		$ppsize .= 'GB';
		} 
	}
	return $ppsize;
}

// Merges settings from feed settings page to empty custom feed settings
function powerpress_merge_empty_feed_settings($CustomFeedSettings, $FeedSettings)
{
	// Remove settings from main $FeedSettings that should not be copied to custom feed.
	unset($FeedSettings['itunes_new_feed_url']);
	unset($FeedSettings['apply_to']);
	unset($FeedSettings['feed_redirect_url']);
 
	if( !$CustomFeedSettings )
		return $FeedSettings; // If the $CustomFeedSettings is false
 
	while( list($key,$value) = each($CustomFeedSettings) )
	{
		if( $value !== '' || !isset($FeedSettings[$key]) )
			$FeedSettings[$key] = $value;
	}
	
	return $FeedSettings;
}

function powerpress_readable_duration($duration, $include_hour=false)
{
	$seconds = 0;
	$parts = split(':', $duration);
	if( count($parts) == 3 )
		$seconds = $parts[2] + ($parts[1]*60) + ($parts[0]*60*60);
	else if ( count($parts) == 2 )
		$seconds = $parts[1] + ($parts[0]*60);
	else
		$seconds = $parts[0];
	
	$hours = 0;
	$minutes = 0;
	if( $seconds >= (60*60) )
	{
		$hours = floor( $seconds /(60*60) );
		$seconds -= (60*60*$hours);
	}
	if( $seconds >= (60) )
	{
		$minutes = floor( $seconds /(60) );
		$seconds -= (60*$minutes);
	}
	
	if( $hours || $include_hour ) // X:XX:XX (readable)
		return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
	
	return sprintf('%d:%02d', $minutes, $seconds); // X:XX or 0:XX (readable)
}


// For grabbing data from Podpress data stored serialized, the strings for some values can sometimes get corrupted, so we fix it...
function powerpress_repair_serialize($string)
{
	if( @unserialize($string) )
		return $string; // Nothing to repair...
	$string = preg_replace_callback('/(s:(\d+):"([^"]*)")/', 
			create_function(
					'$matches',
					'if( strlen($matches[3]) == $matches[2] ) return $matches[0]; return sprintf(\'s:%d:"%s"\', strlen($matches[3]), $matches[3]);'
			), 
			$string);
	
	if( substr($string, 0, 2) == 's:' ) // Sometimes the serialized data is double serialized, so we need to re-serialize the outside string
	{
		$string = preg_replace_callback('/(s:(\d+):"(.*)";)$/', 
			create_function(
					'$matches',
					'if( strlen($matches[3]) == $matches[2] ) return $matches[0]; return sprintf(\'s:%d:"%s";\', strlen($matches[3]), $matches[3]);'
			), 
			$string);
	}
	
	return $string;
}

/*
	powerpress_get_post_meta()
	Safe function to retrieve corrupted PodPress data from the database
	@post_id - post id to retrieve post meta for
	@key - key to retrieve post meta for
*/
function powerpress_get_post_meta($post_id, $key)
{
	$pp_meta_cache = wp_cache_get($post_id, 'post_meta');
	if ( !$pp_meta_cache ) {
		update_postmeta_cache($post_id);
		$pp_meta_cache = wp_cache_get($post_id, 'post_meta');
	}
	
	$meta = false;
	if ( isset($pp_meta_cache[$key]) )
		$meta = $pp_meta_cache[$key][0];
	
	if ( is_serialized( $meta ) ) // Logic used up but not including WordPress 2.8, new logic doesn't make sure if unserialized failed or not
	{
		if ( false !== ( $gm = @unserialize( $meta ) ) )
			return $meta;
	}
	
	return $meta;
}

function powerpress_get_enclosure($post_id, $feed_slug = 'podcast')
{
	$Data = powerpress_get_enclosure_data($post_id, $feed_slug);
	if( $Data )
		return $Data['url'];
	return false;
}

function powerpress_get_enclosure_data($post_id, $feed_slug = 'podcast')
{
	if( $feed_slug == 'podcast' || $feed_slug == '' )
		$MetaData = get_post_meta($post_id, 'enclosure', true);
	else
		$MetaData = get_post_meta($post_id, '_'. $feed_slug .':enclosure', true);
	
	if( !$MetaData )
		return false;
	
	$Data = array();
	$Data['duration'] = 0;
	list($Data['url'], $Data['size'], $Data['type'], $Serialized) = explode("\n", $MetaData, 4);
	$Data['url'] = powerpress_add_redirect_url( trim($Data['url']) );
	if( $Serialized )
	{
		$ExtraData = unserialize($Serialized);
		if( isset($ExtraData['duration']) )
			$Data['duration'] = $ExtraData['duration'];
		if( isset($ExtraData['embed']) )
			$Data['embed'] = $ExtraData['embed'];
		if( isset($ExtraData['no_player']) )
			$Data['no_player'] = 1;
	}
	
	// Check that the content type is a valid one...
	if( strstr($Data['type'], '/') == false )
		$Data['type'] = powerpress_get_contenttype($Data['url']);
		
	return $Data;
}
/*
End Helper Functions
*/

// Are we in the admin?
if( is_admin() )
	require_once(dirname(__FILE__).'/powerpressadmin.php');
	
	
if( defined('POWERPRESS_PLAYERS') && POWERPRESS_PLAYERS )
	require_once(dirname(__FILE__).'/player.php');
		
?>