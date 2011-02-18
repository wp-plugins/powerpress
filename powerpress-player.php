<?php
/*
PowerPress player options
*/


function powerpressplayer_get_next_id()
{
	if( !isset($GLOBALS['g_powerpress_player_id']) ) // Use the global unique player id variable for the surrounding div
		$GLOBALS['g_powerpress_player_id'] = rand(0, 10000);
	else
		$GLOBALS['g_powerpress_player_id']++; // increment the player id for the next div so it is unique
	return $GLOBALS['g_powerpress_player_id'];
}

function powerpressplayer_get_extension($media_url, $EpisodeData = array() )
{
	$extension = 'unknown';
	$parts = pathinfo($media_url);
	if( !empty($parts['extension']) )
		$extension = strtolower($parts['extension']);
	
	// Hack to use the audio/mp3 content type to set extension to mp3, some folks use tinyurl.com to mp3 files which remove the file extension...
	if( isset($EpisodeData['type']) && $EpisodeData['type'] == 'audio/mpeg' && $extension != 'mp3' )
		$extension = 'mp3';
	
	// Hack to make sure we play ogg as audio:
	if( $extension == 'ogg' && defined('POWERPRESS_OGG_AUDIO') && POWERPRESS_OGG_AUDIO )
		$extension = 'oga';
		
	return $extension;
}

/*
Initialize powerpress player handling
*/
function powerpressplayer_init($GeneralSettings)
{
	if( isset($_GET['powerpress_pinw']) )
		powerpress_do_pinw($_GET['powerpress_pinw'], !empty($GeneralSettings['process_podpress']) );
		
	if( isset($_GET['powerpress_embed']) )
		powerpress_do_embed($_GET['powerpress_player'], $_GET['powerpress_embed'], !empty($GeneralSettings['process_podpress']) );
		
	// If we are to process podpress data..
	if( !empty($GeneralSettings['process_podpress']) )
	{
		add_shortcode('display_podcast', 'powerpress_shortcode_handler');
	}
	
	if( defined('POWERPRESS_ENQUEUE_SCRIPTS') )
	{
		// include what's needed for each plaer
		wp_enqueue_script( 'powerpress-player', powerpress_get_root_url() .'player.js');
	}
	
	if( !empty($GeneralSettings['display_player_disable_mobile']) && powerpress_is_mobile_client() )
	{
		// Remove all known filters for player embeds...
		remove_filter('powerpress_player', 'powerpressplayer_player_audio', 10, 3);
		remove_filter('powerpress_player', 'powerpressplayer_player_video', 10, 3);
		remove_filter('powerpress_player', 'powerpressplayer_player_other', 10, 3);
	}
}


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
	$width = '';
	$height = '';
	
	extract( shortcode_atts( array(
			'url' => '',
			'feed' => '',
			'image' => '',
			'width' => '',
			'height' => ''
		), $attributes ) );
	
	if( !$url && $content )
	{
		$content_url = trim($content);
		if( @parse_url($content_url) )
			$url = $content_url;
	}
	
	if( $url )
	{
		$url = powerpress_add_redirect_url($url);
		$content_type = '';
		// Handle the URL differently...
		$return = apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($url, 'p'), array('image'=>$image, 'type'=>$content_type,'width'=>$width, 'height'=>$height) );
	}
	else if( $feed )
	{
		$EpisodeData = powerpress_get_enclosure_data($post->ID, $feed);
		if( !empty($EpisodeData['embed']) )
			$return = $EpisodeData['embed'];
		
		// Shortcode over-ride settings:
		if( !empty($image) )
			$EpisodeData['image'] = $image;
		if( !empty($width) )
			$EpisodeData['width'] = $width;
		if( !empty($height) )
			$EpisodeData['height'] = $height;
		
		if( !isset($EpisodeData['no_player']) )
		{
			if( isset($GeneralSettings['premium_caps']) && $GeneralSettings['premium_caps'] && !powerpress_premium_content_authorized($feed) )
			{
				$return .= powerpress_premium_content_message($post->ID, $feed, $EpisodeData);
				continue;
			}
			
			if( !isset($EpisodeData['no_player']) )
				$return = apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($EpisodeData['url'], 'p'), array('feed'=>$feed, 'image'=>$image, 'type'=>$EpisodeData['type'],'width'=>$width, 'height'=>$height) );
			if( empty($EpisodeData['no_links']) )
				$return .= apply_filters('powerpress_player_links', '',  powerpress_add_flag_to_redirect_url($EpisodeData['url'], 'p'), $EpisodeData );
		}
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
			
			$EpisodeData = powerpress_get_enclosure_data($post->ID, $feed_slug);
			if( !$EpisodeData && !empty($GeneralSettings['process_podpress']) && $feed_slug == 'podcast' )
				$EpisodeData = powerpress_get_enclosure_data_podpress($post->ID);
				
			if( !$EpisodeData )
				continue;
				
			if( !empty($EpisodeData['embed']) )
				$return .= $EpisodeData['embed'];
			
			// Shortcode over-ride settings:
			if( !empty($image) )
				$EpisodeData['image'] = $image;
			if( !empty($width) )
				$EpisodeData['width'] = $width;
			if( !empty($height) )
				$EpisodeData['height'] = $height;
				
			if( isset($GeneralSettings['premium_caps']) && $GeneralSettings['premium_caps'] && !powerpress_premium_content_authorized($GeneralSettings) )
			{
				$return .= powerpress_premium_content_message($post->ID, $feed_slug, $EpisodeData);
				continue;
			}
				
			if( !isset($EpisodeData['no_player']) )
			{
				$return .= apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($EpisodeData['url'], 'p'), $EpisodeData );
			}
			if( !isset($EpisodeData['no_links']) )
			{
				$return .= apply_filters('powerpress_player_links', '',  powerpress_add_flag_to_redirect_url($EpisodeData['url'], 'p'), $EpisodeData );
			}
		}
	}
	
	return $return;
}

add_shortcode('powerpress', 'powerpress_shortcode_handler');
if( !defined('PODCASTING_VERSION') )
{
	add_shortcode('podcast', 'powerpress_shortcode_handler');
}




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
	$player_width = 400;
	$player_height = 225;
	if( isset($Settings['player_width']) && $Settings['player_width'] )
		$player_width = $Settings['player_width'];
	if( isset($Settings['player_height']) && $Settings['player_height'] )
		$player_height = $Settings['player_height'];
	
	// Used with some types
	//$content_type = powerpress_get_contenttype($media_url);
	
	$parts = pathinfo($media_url);
	// Hack to use the audio/mp3 content type to set extension to mp3, some folks use tinyurl.com to mp3 files which remove the file extension...
	// This hack only covers mp3s.
	if( isset($EpisodeData['type']) && $EpisodeData['type'] == 'audio/mpeg' && $parts['extension'] != 'mp3' )
		$parts['extension'] = 'mp3';
	
	$ogg_video = false; // Extra securty make sure it is definitely set to video
	switch( strtolower($parts['extension']) )
	{
		// PDFs:
		case 'pdf': {
			return $content; // We don't add a player for PDFs!
		}; break;
		
		// Flash Player:
		case 'flv': // Handled by Flow Player Classic
		case 'mp3': {  // Mp3's are handled already by one of 5 players
			return $content;
		};
		// Ogg audio and video
		case 'ogg': 
		case 'ogv': {
			$ogg_video = true;
		};
		case 'oga':
		 {
			if( !empty($ogg_video) ) // Ogg Video
			{
				//if( $cover_image == '' )
				//	$cover_image = powerpress_get_root_url() . 'play_video_default.jpg';
				$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">'.PHP_EOL;
				$content .= '<video src="'. $media_url .'" width="'. $player_width .'" height="'. $player_height .'" controls="controls" preload="none"';
				if( $cover_image )
					$content .= ' poster="'. $cover_image .'"';
				$content .= '>'.PHP_EOL;
				$content .= __('Your browser does not support the &lt;video&gt; element.', 'powerpress');
				$content .= '</video>'.PHP_EOL;
				$content .= '</div>'.PHP_EOL;
			}
			else // Ogg Audio
			{
				// Ogg Audio
				$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">'.PHP_EOL;
				$content .= '<audio src="'. $media_url .'" controls="controls" preload="none">'.PHP_EOL;
				$content .= __('Your browser does not support the &lt;audio&gt; element.', 'powerpress');
				$content .= '</audio>'.PHP_EOL;
				$content .= '</div>'.PHP_EOL;
			}
			// OGG PLAYER HERE
		
		}; break;
		// H.264:
		case 'm4v': 
		case 'mp4': {
			if( isset($Settings['video_player']) && $Settings['video_player'] != 'flow-player-classic' ) // If we are using some other player than the default flash player
				return $content;
		};
		// WebM
		case 'webm': {
			
			$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">'.PHP_EOL;
			$content .= '<video src="'. $media_url .'" width="'. $player_width .'" height="'. $player_height .'" controls="controls" preload="none"';
			if( $cover_image )
				$content .= ' poster="'. $cover_image .'"';
			$content .= '>'.PHP_EOL;
			$content .= powerpressplayer_build_playimage($media_url, $ExtraData);
			//$content .= __('Your browser does not support the &lt;video&gt; element.', 'powerpress');
			$content .= '</video>'.PHP_EOL;
			$content .= '</div>'.PHP_EOL;
		};
		
		// Quicktime:
		case 'm4a':
		case 'avi':
		case 'mpg':
		case 'mpeg':
		
		case 'm4b':
		case 'm4r':
		case 'qt':
		case 'mov': {
			
			$GeneralSettings = get_option('powerpress_general');
			if( !isset($GeneralSettings['player_scale']) )
				$GeneralSettings['player_scale'] = 'tofit';
				
			// If there is no cover image specified, lets use the default...
			if( $cover_image == '' )
				$cover_image = powerpress_get_root_url() . 'play_video_default.jpg';
			
			if( $autoplay )
			{
				$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'"></div>'.PHP_EOL;
				$content .= '<script type="text/javascript">'.PHP_EOL;
				$content .= "powerpress_embed_quicktime('powerpress_player_{$g_powerpress_player_id}', '{$media_url}', {$player_width}, {$player_height}, '{$GeneralSettings['player_scale']}');\n";
				$content .= "</script>\n";
			}
			else
			{
				$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">'.PHP_EOL;
				$content .= '<a href="'. $media_url .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" onclick="';
				$content .= "return powerpress_embed_quicktime('powerpress_player_{$g_powerpress_player_id}', '{$media_url}', {$player_width}, {$player_height}, '{$GeneralSettings['player_scale']}' );";
				$content .= '">';
				$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" alt="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" />';
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
				
				$content .= '<a href="'. $media_url .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" onclick="';
				$content .= "return powerpress_embed_winplayer('powerpress_player_{$g_powerpress_player_id}', '{$media_url}', {$player_width}, {$player_height} );";
				$content .= '">';
				$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" alt="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" />';
				$content .= '</a>';
			}
			
			if( $firefox )
			{
				$content .= '<p style="font-size: 85%;margin-top:0;">'. __('Best viewed with', 'powerpress');
				$content .= ' <a href="http://support.mozilla.com/en-US/kb/Using+the+Windows+Media+Player+plugin+with+Firefox#Installing_the_plugin" target="_blank">';
				$content .= __('Windows Media Player plugin for Firefox', 'powerpress') .'</a></p>';
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
				$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" alt="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" />';
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
			$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" alt="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" />';
			$content .= '</a>';
			$content .= "</div>\n";
			
		}; break;
	}
	
	return $content;
}

//add_filter('powerpress_player', 'powerpress_player_filter', 10, 3);

function powerpressplayer_filter($content, $media_url, $ExtraData = array())
{
	$Settings = get_option('powerpress_general');
	if( !empty($Settings['display_player_disable_mobile']) && powerpress_is_mobile_client() )
		return $content; // lets not add a player for this situation
		
	// Next check that we're working with an mp3
	$parts = pathinfo($media_url);
	
	$player_content = '';
	switch( strtolower($parts['extension']) )
	{
		case 'mp3': {
			// Check if we are using a custom flash player...

		};break;
		//case 'webm':
		case 'mp4':
		case 'm4v': {
			
			// Check if we are using a custom flash player...
			if( !isset($Settings['video_player']) || $Settings['video_player'] == 'flow-player-classic' ) // Either the default player is selected or the user never selected a player
				return $content;
			$player_content = powerpressvideoplayer_build( $media_url, $Settings, $ExtraData );
		};break;
		default: {
			return $content;
		}
	}
	
	return $content . $player_content;
}


// Hook into the powerprss_player filter
//add_filter('powerpress_player', 'powerpressplayer_filter', 10, 3);

//if( is_admin() )
//	require_once( POWERPRESS_ABSPATH .'/powerpressadmin-player.php');


function powerpressvideoplayer_build($media_url, $Settings, $ExtraData = array())
{
	
	global $g_powerpress_player_id; // Use the global unique player id variable for the surrounding div
	if( !isset($g_powerpress_player_id) )
		$g_powerpress_player_id = rand(0, 10000);
	$g_powerpress_player_id++; // increment the player id for the next div so it is unique
	$content = '';
	$autoplay = false;
	if( isset($ExtraData['autoplay']) && $ExtraData['autoplay'] )
		$autoplay = true; // TODO: We need to handle this
	$feed_slug = '';
	if( !empty($ExtraData['feed']) )
		$feed_slug = $ExtraData['feed'];
	
	switch( $Settings['video_player'] )
	{
		case 'flare-player': {

			//$content .= powerpress_generate_embed($player, $ExtraData);
			if( $autostart )
			{
				$content .= powerpress_generate_embed( 'flare-player', get_the_ID(), $feed_slug, false, false, false, true);
			}
			else
			{
				$content .= powerpress_generate_embed( 'flare-player', get_the_ID(), $feed_slug, $Settings['player_width'], $Settings['player_height']);
			}
		}; break;
		default : {
			//$content .= print_r($Settings['video_player'], true);
		}
	}
	
	return $content;
}
/*
// Everything in $ExtraData except post_id
*/
function powerpress_generate_embed($player, $post_id, $feed_slug, $width=false, $height=false, $media_url = false, $autoplay = false)
{
	if( $width == false )
		$width = 480;
	if( $height == false )
		$height = 390;
	$embed = '';
	$url = get_bloginfo('url') .'/?powerpress_embed=' . $post_id .'-'. $feed_slug;
	if( $autoplay )
		$url .= '&amp;autoplay=true';
	$url .= '&amp;powerpress_player='.$player;
	$embed .= '<iframe';
	$embed .= ' class="powerpress-player-embed"';
	$embed .= ' width="'. $width .'"';
	$embed .= ' height="'. $height .'"';
	$embed .= ' src="'. $url .'"';
	$embed .= ' frameborder="0"';
	$embed .= '></iframe>';
	return $embed;
}

function powerpressplayer_build_embed($media_url, $ExtraData = array() )
{
	if( empty($ExtraData['id']) )
	{
		if( get_the_ID() )
		 $ExtraData['id'] = get_the_ID();
	}
	if( empty($ExtraData['feed']) )
		return '';
	
	$post_id = $ExtraData['id'];
	$feed_slug = $ExtraData['feed'];
	$extension = powerpressplayer_get_extension($media_url, $ExtraData);
	$width = 400;
	$height = 225;
	$player = false;
	
	
	
	switch( $extension )
	{
		case 'mp3': {
			$Settings = get_option('powerpress_general');
			if( !empty($Settings['player']) && $Settings['player'] == 'audio-player' )
			{
				$player = $Settings['player'];
				$height = 25;
				$width = 290;
				// TODO: use the width set for the players settings...
			
			}
			else if( !empty($Settings['player']) && $Settings['player'] == 'mp3-player-maxi' )
			{
				$player = $Settings['player'];
				$height = 20;
				$width = 200;
				// TODO: use the width set for the players settings...
			}
		
		}; break;
		case 'mp4':
		case 'm4v': {
			$Settings = get_option('powerpress_general');
			if( !empty($Settings['video_player']) && $Settings['video_player'] == 'flare-player' )
			{
				$player = $Settings['video_player'];
			}
			
		}; break;
	}
	
	//die($player);
	
	if( !$player )
		return '';
		
	
	
	$embed = '';
	$url = get_bloginfo('url') .'/?powerpress_embed=' . $post_id .'-'. $feed_slug;
	if( $autoplay )
		$url .= '&amp;autoplay=true';
	$url .= '&amp;powerpress_player='.$player;
	$embed .= '<iframe';
	$embed .= ' class="powerpress-player-embed"';
	$embed .= ' width="'. $width .'"';
	$embed .= ' height="'. $height .'"';
	$embed .= ' src="'. $url .'"';
	$embed .= ' frameborder="0"';
	$embed .= '></iframe>';
	return $embed;
}


function powerpressplayer_flare_head()
{
	$content = '';
	$content .= '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js" type="text/javascript"></script>' . PHP_EOL;
	$content .= '<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/jquery-ui.min.js" type="text/javascript"></script>' . PHP_EOL;
	$content .= '<link rel="stylesheet" href="'. powerpress_get_root_url() .'3rdparty/flare_player/flarevideo.css" type="text/css">' . PHP_EOL;
	$content .= '<link rel="stylesheet" href="'. powerpress_get_root_url() .'3rdparty/flare_player/flarevideo.default.css" type="text/css">' . PHP_EOL;
	$content .= '<script src="'. powerpress_get_root_url() .'3rdparty/flare_player/jquery.flash.js" type="text/javascript"></script>' . PHP_EOL;
	$content .= '<script src="'. powerpress_get_root_url() .'3rdparty/flare_player/flarevideo.js" type="text/javascript"></script> ' . PHP_EOL;
	
	$content .= '<style type="text/css" media="screen">' . PHP_EOL;
	$content .= '	body { font-size: 13px; font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; }' . PHP_EOL;
  $content .= '</style>' . PHP_EOL;
	return $content;
}



function powerpressplayer_flare_embed($media_url, $PlayerSettings = array())
{
	//return '<p>Flare player coming soon!</p>';
	$content = '';
	$content .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'. PHP_EOL;
	$content .= '<html xmlns="http://www.w3.org/1999/xhtml">'. PHP_EOL;
	$content .= '<head>'. PHP_EOL;
	$content .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'. PHP_EOL;
	$content .= '<title>'. __('Blubrry PowerPress Player', 'powerpress') .'</title>'. PHP_EOL;
	$content .= powerpressplayer_flare_head();
	$content .= '</head>'. PHP_EOL;
	$content .= '<body>'. PHP_EOL;
	$content .= powerpressplayer_build_flareplayer($media_url, $PlayerSettings + array('fullscreen'=>true) );
	$content .= '</body>'. PHP_EOL;
	$content .= '</html>'. PHP_EOL;
	return $content;
}


function powerpress_player_head()
{


}

/*
Audio Players - Flash/HTML5 compliant mp3 audio

@since 2.0
@content - 
@param string $content Content of post or page to add player to
@param string $media_url Media URL to add player for
@param array $EpisodeData Array of key/value settings that optionally can contribute to player being added
@return string $content The content, possibly modified wih player added
*/
function powerpressplayer_player_audio($content, $media_url, $EpisodeData = array() )
{
	
	// First lets check the extension before we waste time/CPU figuring out what we're going to do
	$extension = powerpressplayer_get_extension($media_url);
	
	switch( $extension )
	{
		// MP3
		case 'mp3':
		{
			$Settings = get_option('powerpress_general');
			switch( $Settings['player'] )
			{
				case 'default':
				case 'flow-player-classic': {
					$content .= powerpressplayer_build_flowplayerclassic($media_url, $EpisodeData);
				}; break;
				case 'audio-player': {
					$content .= powerpressplayer_build_1pxoutplayer($media_url, $EpisodeData);
				}; break;
				case 'flashmp3-maxi': {
					$content .= powerpressplayer_build_flashmp3maxi($media_url, $EpisodeData);
				}; break;
				case 'audioplay': {
					$content .= powerpressplayer_build_audioplay($media_url, $EpisodeData);
				}; break;
				case 'simple_flash': {
					$content .= powerpressplayer_build_simpleflash($media_url, $EpisodeData);
				}; break;
			}
		
		}; break;
	}

	return $content;
}

/*
Video Players - HTML5/Flash compliant video formats
*/
function powerpressplayer_player_video($content, $media_url, $EpisodeData = array() )
{
	$extension = powerpressplayer_get_extension($media_url);
	
	switch( $extension )
	{
		// OGG (audio or video)
		case 'ogg': {
			// Ogg special case, we treat as video unless specified otherwise
			if( defined('POWERPRESS_OGG_AUDIO') && POWERPRESS_OGG_AUDIO == true )
				return $content;
		}
		// OGG Video / WebM
		case 'webm': 
		case 'ogv': { // Use native player when possible
			// TODO: Move logic from player_filter to here
			$GeneralSettings = get_option('powerpress_general');
			$player_width = 400;
			$player_height = 225;
			if( !empty($GeneralSettings['player_width']) )
				$player_width = $GeneralSettings['player_width'];
			if( !empty($GeneralSettings['player_height']) )
				$player_height = $GeneralSettings['player_height'];
			if( !empty($EpisodeData['width']) )
				$player_width = $EpisodeData['width'];
			if( !empty($EpisodeData['height']) )
				$player_height = $EpisodeData['height'];
			$cover_image = powerpress_get_root_url() . 'play_video_hd_default.jpg';
			if( !empty($EpisodeData['image']) )
				$cover_image = $EpisodeData['image'];
		
			$content .= '<div class="powerpress_player" id="powerpress_player_'. powerpressplayer_get_next_id() .'">'.PHP_EOL;
			$content .= '<video src="'. $media_url .'" width="'. $player_width .'" height="'. $player_height .'" controls="controls" preload="none"';
			if( $cover_image )
				$content .= ' poster="'. $cover_image .'"';
			$content .= '>'.PHP_EOL;
			$content .= powerpressplayer_build_playimage($media_url, $ExtraData);
			//$content .= __('Your browser does not support the &lt;video&gt; element.', 'powerpress');
			$content .= '</video>'.PHP_EOL;
			$content .= '</div>'.PHP_EOL;
		}; break;
		// H.264
		case 'm4v':
		case 'mp4':
		// Okay, lets see if we we have a player setup to handle this
		{
			
			$Settings = get_option('powerpress_general');
			//die($Settings['video_player']);
			switch( $Settings['video_player'] )
			{
				case 'default':
				case 'flow-player-classic': {
				
				}; break;
				case 'flare-player': {
					// Flare video always uses the embed
					$content .= powerpressplayer_build_embed($media_url, $EpisodeData );
				}; break;
			}
		}; break;
	}
	
	return $content;
}

function powerpressplayer_player_other($content, $media_url, $EpisodeData = array() )
{
	$extension = powerpressplayer_get_extension($media_url);
	switch( $extension )
	{
		case 'flv': {
		
			$content .= powerpressplayer_build_flowplayerclassic($media_ur, $EpisodeData);
		
		}; break;
	}
	
	return $content;
}

add_filter('powerpress_player', 'powerpressplayer_player_audio', 10, 3); // Audio players (mp3)
add_filter('powerpress_player', 'powerpressplayer_player_video', 10, 3); // Video players (mp4/m4v, webm, ogv)
add_filter('powerpress_player', 'powerpressplayer_player_other', 10, 3); // Audio/Video flv, wmv, wma, oga, m4a and other non-standard media files

/*
Filters for media links, appear below the selected player
*/
function powerpressplayer_link_download($content, $media_url, $ExtraData = array() )
{
	$GeneralSettings = get_option('powerpress_general');
	
	$player_links = '';
	if( $GeneralSettings['podcast_link'] == 1 )
	{
		$player_links .= "<a href=\"{$media_url}\" class=\"powerpress_link_d\" title=\"". POWERPRESS_DOWNLOAD_TEXT ."\">". POWERPRESS_DOWNLOAD_TEXT ."</a>".PHP_EOL;
	}
	else if( $GeneralSettings['podcast_link'] == 2 )
	{
		$player_links .= "<a href=\"{$media_url}\" class=\"powerpress_link_d\" title=\"". POWERPRESS_DOWNLOAD_TEXT ."\">". POWERPRESS_DOWNLOAD_TEXT ."</a> (".powerpress_byte_size($ExtraData['size']).") ".PHP_EOL;
	}
	else if( $GeneralSettings['podcast_link'] == 3 )
	{
		if( $ExtraData['duration'] && ltrim($ExtraData['duration'], '0:') != '' )
			$player_links .= "<a href=\"{$media_url}\" class=\"powerpress_link_d\" title=\"". POWERPRESS_DOWNLOAD_TEXT ."\">". POWERPRESS_DOWNLOAD_TEXT ."</a> (". htmlspecialchars(POWERPRESS_DURATION_TEXT) .": " . powerpress_readable_duration($ExtraData['duration']) ." &#8212; ".powerpress_byte_size($ExtraData['size']).")".PHP_EOL;
		else
			$player_links .= "<a href=\"{$media_url}\" class=\"powerpress_link_d\" title=\"". POWERPRESS_DOWNLOAD_TEXT ."\">". POWERPRESS_DOWNLOAD_TEXT ."</a> (".powerpress_byte_size($ExtraData['size']).")".PHP_EOL;
	}
	
	if( $player_links && !empty($content) )
		$content .= ' '.POWERPRESS_LINK_SEPARATOR .' ';
	
	return $content . $player_links;
}

function powerpressplayer_link_pinw($content, $media_url, $ExtraData = array() )
{
	$GeneralSettings = get_option('powerpress_general');
	$is_pdf = (strtolower( substr($media_url, -3) ) == 'pdf' );
	
	$player_links = '';
	switch( $GeneralSettings['player_function'] )
	{
		case 1: // Play on page and new window
		case 3: // Play in new window only
		case 5: { // Play in page and new window
			if( $is_pdf )
				$player_links .= "<a href=\"{$media_url}\" class=\"powerpress_link_pinw\" target=\"_blank\" title=\"". __('Open in New Window', 'powerpress') ."\">". __('Open in New Window', 'powerpress') ."</a>".PHP_EOL;
			else if( !empty($ExtraData['id']) && !empty($ExtraData['feed']) )
				$player_links .= "<a href=\"{$media_url}\" class=\"powerpress_link_pinw\" target=\"_blank\" title=\"". POWERPRESS_PLAY_IN_NEW_WINDOW_TEXT ."\" onclick=\"return powerpress_pinw('{$ExtraData['id']}-{$ExtraData['feed']}');\">". POWERPRESS_PLAY_IN_NEW_WINDOW_TEXT ."</a>".PHP_EOL;
			else
				$player_links .= "<a href=\"{$media_url}\" class=\"powerpress_link_pinw\" target=\"_blank\" title=\"". POWERPRESS_PLAY_IN_NEW_WINDOW_TEXT ."\">". POWERPRESS_PLAY_IN_NEW_WINDOW_TEXT ."</a>".PHP_EOL;
		}; break;
	}//end switch	
	
	if( $player_links && !empty($content) )
		$content .= ' '.POWERPRESS_LINK_SEPARATOR .' ';
	
	return $content . $player_links;
}

function powerpressplayer_link_embed($content, $media_url, $ExtraData = array() )
{
	$player_links = '';
	// TODO: add code to provide embed to media episode
	
	if( $player_links && !empty($content) )
		$content .= ' '.POWERPRESS_LINK_SEPARATOR .' ';
	return $content;
}

function powerpressplayer_link_title($content, $media_url, $ExtraData = array() )
{
	if( $content )
	{
		$extension = 'unknown';
		$parts = pathinfo($media_url);
		if( $parts && isset($parts['extension']) )
			$extension  = strtolower($parts['extension']);
		
		$prefix = '';
		if( $extension == 'pdf' )
			$prefix .= __('E-Book PDF', 'powerpress') . ( $ExtraData['feed']=='pdf'||$ExtraData['feed']=='podcast'?'':" ({$ExtraData['feed']})") .POWERPRESS_TEXT_SEPARATOR;
		else if( $ExtraData['feed'] != 'podcast' )
			$prefix .= htmlspecialchars(POWERPRESS_LINKS_TEXT) .' ('. htmlspecialchars($ExtraData['feed']) .')'. POWERPRESS_TEXT_SEPARATOR;
		else
			$prefix .= htmlspecialchars(POWERPRESS_LINKS_TEXT) . POWERPRESS_TEXT_SEPARATOR;
		if( !empty($prefix) )
			$prefix .= ' ';
		
		return '<p class="powerpress_links powerpress_links_'. $extension .'">'. $prefix . $content . '</p>'.PHP_EOL;
	}
	return '';
}

add_filter('powerpress_player_links', 'powerpressplayer_link_pinw', 30, 3);
add_filter('powerpress_player_links', 'powerpressplayer_link_download', 50, 3);
add_filter('powerpress_player_links', 'powerpressplayer_link_embed', 50, 3);
add_filter('powerpress_player_links', 'powerpressplayer_link_title', 1000, 3);

/*
Do Play in new Window
*/
function powerpress_do_pinw($pinw, $process_podpress)
{
	list($post_id, $feed_slug) = explode('-', $pinw, 2);
	$EpisodeData = powerpress_get_enclosure_data($post_id, $feed_slug);
	
	if( $EpisodeData == false && $process_podpress && $feed_slug == 'podcast' )
	{
		$EpisodeData = powerpress_get_enclosure_data_podpress($post_id);
	}
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo __('Blubrry PowerPress Player', 'powerpress'); ?></title>
<?php 
	wp_head();
	// powerpress_player_head();
?>
<style type="text/css">
body { font-size: 13px; font-family: Arial, Helvetica, sans-serif; }
</style>
</head>
<body>
<div style="margin: 5px;">
<?php
	$GeneralSettings = get_option('powerpress_general');
	if( !$EpisodeData )
	{
		echo '<p>'.  __('Unable to retrieve media information.', 'powerpress') .'</p>';
	}
	else if( !empty($GeneralSettings['premium_caps']) && !powerpress_premium_content_authorized($feed_slug) )
	{
		echo powerpress_premium_content_message($post_id, $feed_slug, $EpisodeData);
	}
	else if( !empty($EpisodeData['embed']) )
	{
		echo $EpisodeData['embed'];
	}
	else //  if( !isset($EpisodeData['no_player']) ) // Even if there is no player set, if the play in new window option is enabled then it should play here...
	{
		echo apply_filters('powerpress_player', '', powerpress_add_flag_to_redirect_url($EpisodeData['url'], 'p'), array('feed'=>$feed_slug, 'autoplay'=>true, 'type'=>$EpisodeData['type']) );
	}
	
?>
</div>
</body>
</html>
<?php
	exit;
}

/*
Do embed
*/
function powerpress_do_embed($player, $embed, $process_podpress)
{
	list($post_id, $feed_slug) = explode('-', $embed, 2);
	$EpisodeData = powerpress_get_enclosure_data($post_id, $feed_slug);
	
	if( $EpisodeData == false && $process_podpress && $feed_slug == 'podcast' )
	{
		$EpisodeData = powerpress_get_enclosure_data_podpress($post_id);
	}
	
	// Embeds are only available for the following players
	switch( $player )
	{
		case 'flare-player': {
			
			$PlayerSettings = array();
			if( !empty($_GET['autoplay']) )
				$PlayerSettings['autoplay'] = true;
			echo powerpressplayer_flare_embed($EpisodeData['url'], $PlayerSettings);
			exit;
		
		}; break;
		case '1pixelout': {
		
		}; break;
	}
	
	die( __('Embed not available', 'powerpress') );
}

/*
Flow Player Classic
*/
function powerpressplayer_build_flowplayerclassic($media_url, $EpisodeData = array())
{
	// Very important setting, we need to know if the media should auto play or not...
	$autoplay = false; // (default)
	if( !empty($EpisodeData['autoplay']) )
		$autoplay = true;
	$cover_image = '';
	if( $EpisodeData['image'] )
		$cover_image = $EpisodeData['image'];
	
	// Based on $ExtraData, we can determine which type of player to handle here...
	$Settings = get_option('powerpress_general');
	if( !empty($Settings['display_player_disable_mobile']) && powerpress_is_mobile_client() )
		return $content; // lets not add a player for this situation
	
	if( !isset($Settings['player_function']) )
		$Settings['player_function'] = 1;
	
	$player_width = 400;
	$player_height = 225;
	// Global Settings
	if( !empty($Settings['player_width']) )
		$player_width = $Settings['player_width'];
	if( !empty($Settings['player_height']) )
		$player_height = $Settings['player_height'];
	// Episode Settings
	if( !empty($EpisodeData['width']) )
		$player_width = $EpisodeData['width'];
	if( !empty($EpisodeData['height']) )
		$player_height = $EpisodeData['height'];
	
	$extension = powerpressplayer_get_extension($media_url, $EpisodeData);
	if( $extension == 'mp3' )
	{
		// FlowPlayer has differeent sizes for audio than for video
		$player_width = 320;
		if( !empty($Settings['player_width_audio']) )
			$player_width = $Settings['player_width_audio'];
		
		if( !empty($EpisodeData['width']) && !empty($Settings['player_width_audio']) )
			$player_width = $EpisodeData['width'];
			
		//if( $cover_image == '' )
		
		$player_height = 24;
		$cover_image = ''; // Audio should not have a cover image
	}
	
	// Build player...
	$player_id = powerpressplayer_get_next_id();
	$content = '';
	$content .= '<div class="powerpress_player" id="powerpress_player_'. $player_id .'"></div>'.PHP_EOL;
	$content .= '<script type="text/javascript">'.PHP_EOL;
	$content .= "pp_flashembed(\n";
	$content .= "	'powerpress_player_{$player_id}',\n";
	$content .= "	{src: '". powerpress_get_root_url() ."FlowPlayerClassic.swf', width: {$player_width}, height: {$player_height}, wmode: 'transparent' },\n";
	if( $cover_image )
		$content .= "	{config: { autoPlay: ". ($autoplay?'true':'false') .", autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '{$media_url}', splashImageFile: '{$cover_image}', scaleSplash: true, loop: false, autoRewind: true } }\n";
	else
		$content .= "	{config: { autoPlay: ". ($autoplay?'true':'false') .", autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '{$media_url}', loop: false, autoRewind: true } }\n";
	$content .= ");\n";
	$content .= "</script>\n";
	return $content;
}

function powerpressplayer_build_playimage($media_url, $EpisodeData = array(), $include_div = false)
{
	$GeneralSettings = get_option('powerpress_general');
	$content = '';
	$autoplay = false;
	if( !empty($EpisodeData['autoplay']) && $EpisodeData['autoplay'] )
		$autoplay = true;
	$player_width = 400;
	$player_height = 225;
	if( !empty($GeneralSettings['player_width']) )
		$player_width = $GeneralSettings['player_width'];
	if( !empty($GeneralSettings['player_height']) )
		$player_height = $GeneralSettings['player_height'];
	if( !empty($EpisodeData['width']) )
		$player_width = $EpisodeData['width'];
	if( !empty($EpisodeData['height']) )
		$player_height = $EpisodeData['height'];
	$cover_image = powerpress_get_root_url() . 'play_video_hd_default.jpg';
	if( !empty($EpisodeData['image']) )
		$cover_image = $EpisodeData['image'];
	
	if( $include_div )
		$content .= '<div class="powerpress_player" id="powerpress_player_'. powerpressplayer_get_next_id() .'">';
	$content .= '<a href="'. $media_url .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'"'. ($autoplay?'':' target="_blank"') .'>';
	$content .= '<img src="'. $cover_image .'" title="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" alt="'. htmlspecialchars(POWERPRESS_PLAY_TEXT) .'" style="width: '. $player_width .'px; height: '. $player_height .'px;" />';
	$content .= '</a>';
	if( $include_div )
		$content .= "</div>\n";
	return $content;
}

/*
1 pixel out player
*/
function powerpressplayer_build_1pxoutplayer($media_url, $EpisodeData = array())
{
	$content = '';
	$autoplay = false;
	if( isset($EpisodeData['autoplay']) && $EpisodeData['autoplay'] )
		$autoplay = true; // TODO: We need to handle this

	$PlayerSettings = get_option('powerpress_audio-player');
	if( !$PlayerSettings )
	{
		$PlayerSettings = array(
			'width'=>'290',
			'transparentpagebg' => 'yes',
			'lefticon' => '#333333',
			'leftbg' => '#CCCCCC',
			'bg' => '#E5E5E5',
			'voltrack' => '#F2F2F2',
			'volslider' => '#666666',
			'rightbg' => '#B4B4B4',
			'rightbghover' => '#999999',
			'righticon' => '#333333',
			'righticonhover' => '#FFFFFF',
			'loader' => '#009900',
			'track' => '#FFFFFF',
			'tracker' => '#DDDDDD',
			'border' => '#CCCCCC',
			'skip' => '#666666',
			'text' => '#333333',
			'pagebg' => '',
			'noinfo'=>'yes',
			'rtl' => 'no' );
	}

	if( $PlayerSettings['titles'] == '' )
		$PlayerSettings['titles'] = 'Blubrry PowerPress';
	else if( strtoupper($PlayerSettings['titles']) == __('TRACK', 'powerpress') )
		unset( $PlayerSettings['titles'] );

	// Set player width
	if( !isset($PlayerSettings['width']) )	
		$PlayerSettings['width'] = 290;
	if( !empty($EpisodeData['width']) && is_numeric($EpisodeData['width']) )
		$PlayerSettings['width'] = $EpisdoeData['width'];
	
	$transparency = '<param name="wmode" value="transparent" />';
	$PlayerSettings['transparentpagebg'] = 'yes';
	if( !empty($PlayerSettings['pagebg']) )
	{
		$transparency = '<param name="bgcolor" value="'.$PlayerSettings['pagebg'].'" />';
		$PlayerSettings['transparentpagebg'] = 'no';
	}
	
	$flashvars ='';
	while( list($key,$value) = each($PlayerSettings) )
	{
		$flashvars .= '&amp;'. $key .'='. preg_replace('/\#/','',$value);
	}
	
	if( $autoplay )
	{
		$flashvars .= '&amp;autostart=yes';
	}
	
	// TODO: Add 1 px out audio-player player here
	$player_id = powerpressplayer_get_next_id();
	$content .= '<div class="powerpress_player" id="powerpress_player_'. $player_id .'">';
	$content .= '<object type="application/x-shockwave-flash" data="'.powerpress_get_root_url().'audio-player.swf" id="'.$player_id.'" height="24" width="'. $PlayerSettings['width'] .'">'.PHP_EOL;
	$content .= '<param name="movie" value="'.powerpress_get_root_url().'audio-player.swf" />'.PHP_EOL;
	$content .= '<param name="FlashVars" value="playerID='.$player_id.'&amp;soundFile='.urlencode($media_url).$flashvars.'" />'.PHP_EOL;
	$content .= '<param name="quality" value="high" />'.PHP_EOL;
	$content .= '<param name="menu" value="false" />'.PHP_EOL;
	$content .= '<param name="wmode" value="transparent" />'.PHP_EOL;
	$content .= '</object>'.PHP_EOL;
	$content .= '</div>'.PHP_EOL;
	
	return $content;
}

/*
Flash Mp3 player Maxi
*/
function powerpressplayer_build_flashmp3maxi($media_url, $EpisodeData = array())
{
	$autoplay = false;
	if( isset($EpisodeData['autoplay']) && $EpisodeData['autoplay'] )
		$autoplay = true; // TODO: We need to handle this
		
	$PlayerSettings = get_option('powerpress_flashmp3-maxi');
	$keys = array('bgcolor1','bgcolor2','bgcolor','textcolor','buttoncolor','buttonovercolor','showstop','showinfo','showvolume','height','width','showloading','buttonwidth','volume','showslider');
		
		//set PlayerSettings as blank array for initial setup
				//This keeps the foreach loop from returning an error
	if( empty($PlayerSettings) )
	{
		$PlayerSettings = array(
			'bgcolor1'=>'#7c7c7c',
			'bgcolor2'=>'#333333',
			'textcolor' => '#FFFFFF',
			'buttoncolor' => '#FFFFFF',
			'buttonovercolor' => '#FFFF00',
			'showstop' => '0',
			'showinfo' => '0',
			'showvolume' => '1',
			'height' => '20',
			'width' => '200',
			'showloading' => 'autohide',
			'buttonwidth' => '26',
			'volume' => '100',
			'showslider' => '1',
			'slidercolor1'=>'#cccccc',
			'slidercolor2'=>'#888888',
			'sliderheight' => '10',
			'sliderwidth' => '20',
			'loadingcolor' => '#FFFF00', 
			'volumeheight' => '6',
			'volumewidth' => '30',
			'sliderovercolor' => '#eeee00');
	}

	$flashvars = '';
	$flashvars .= "mp3=" . urlencode($media_url);
	if( $autoplay ) 
		$flashvars .= '&amp;autoplay=1';

	//set non-blank options without dependencies as flash variables for preview
	foreach($keys as $key)
	{
		if( !empty($PlayerSettings[$key]) )
		{
			$flashvars .= '&amp;'. $key .'='. preg_replace('/\#/','',$PlayerSettings[''.$key.'']);
		}
	}
	
	//set slider dependencies
	if( !empty($PlayerSettings['showslider']) ) // IF not zero
	{
		if( !empty($PlayerSettings['sliderheight']) ) {
			$flashvars .= '&amp;sliderheight='. $PlayerSettings['sliderheight'];
		}
		if( !empty($PlayerSettings['sliderwidth']) ) {
			$flashvars .= '&amp;sliderwidth='. $PlayerSettings['sliderwidth'];
		}
		if( !empty($PlayerSettings['sliderovercolor']) ){
			$flashvars .= '&amp;sliderovercolor='. preg_replace('/\#/','',$PlayerSettings['sliderovercolor']);
		}
	}
	
	//set volume dependencies
	if($PlayerSettings['showvolume'] != "0")
	{
		if( !empty($PlayerSettings['volumeheight']) ) {
			$flashvars .= '&amp;volumeheight='. $PlayerSettings['volumeheight'];
		}
		if( !empty($PlayerSettings['volumewidth']) ) {
			$flashvars .= '&amp;volumewidth='. $PlayerSettings['volumewidth'];
		}
	}
	
	//set autoload dependencies
	if($PlayerSettings['showloading'] != "never")
	{
		if( !empty($PlayerSettings['loadingcolor']) ) {
			$flashvars .= '&amp;loadingcolor='. preg_replace('/\#/','',$PlayerSettings['loadingcolor']);
		}
	}

	//set default width for object
	if( empty($PlayerSettings['width']) )
		$width = '200';
	else
		$width = $PlayerSettings['width'];
	if( !empty($EpisodeData['width']) && is_numeric($EpisodeData['width']))
		$width = $EpisodeData['width'];
	
	if( empty($PlayerSettings['height']) )
		$height = '20';
	else
		$height = $PlayerSettings['height'];
	if( !empty($EpisodeData['height']) && is_numeric($EpisodeData['height']) ) 
		$height = $EpisodeData['height'];
	
	//set background transparency
	if( !empty($PlayerSettings['bgcolor']) )
		$transparency = '<param name="bgcolor" value="'. $PlayerSettings['bgcolor'] .'" />';
	else
		$transparency = '<param name="wmode" value="transparent" />';
	
	// Add flashmp3-maxi player here
	$player_id = powerpressplayer_get_next_id();
	$content = '';
	$content .= '<div class="powerpress_player" id="powerpress_player_'. $player_id .'">'.PHP_EOL;
	$content .= '<object type="application/x-shockwave-flash" data="'. powerpress_get_root_url().'player_mp3_maxi.swf" id="player_mp3_maxi_'.$player_id.'" width="'. $width.'" height="'. $height .'">'.PHP_EOL;
	$content .=  '<param name="movie" value="'. powerpress_get_root_url().'player_mp3_maxi.swf" />'.PHP_EOL;
	$content .= $transparency.PHP_EOL;
	$content .= '<param name="FlashVars" value="'. $flashvars .'" />'.PHP_EOL;
	$content .= '</object>'.PHP_EOL;
	$content .= '</div>'.PHP_EOL;
	return $content;
}

/*
Audio Play player
*/
function powerpressplayer_build_audioplay($media_url, $EpisodeData = array())
{
	$autoplay = false;
	if( isset($EpisodeData['autoplay']) && $EpisodeData['autoplay'] )
		$autoplay = true;
			
	$PlayerSettings = get_option('powerpress_audioplay');
	if( empty($PlayerSettings) )
	{
		$PlayerSettings = array(
			'bgcolor' => '',
			'buttondir' => 'negative',
			'mode' => 'playpause');
	}

	$width = $height = (strstr($PlayerSettings['buttondir'], 'small')===false?30:15);

	// Set standard variables for player
	$flashvars = 'file='.urlencode($media_url) ;
	$flashvars .= '&amp;repeat=1';
	if( $autoplay )
		$flashvars .= '&amp;auto=yes';

	if( empty($PlayerSettings['bgcolor']) )
	{
		$flashvars .= "&amp;usebgcolor=no";
		$transparency = '<param name="wmode" value="transparent" />';
		$htmlbg = "";
	}
	else
	{
		$flashvars .= "&amp;bgcolor=". preg_replace('/\#/','0x',$PlayerSettings['bgcolor']);
		$transparency = '<param name="bgcolor" value="'. $PlayerSettings['bgcolor']. '" />';
		$htmlbg = 'bgcolor="'. $PlayerSettings['bgcolor'].'"';
	}

	if( empty($PlayerSettings['buttondir']) )
		$flashvars .= "&amp;buttondir=".powerpress_get_root_url()."buttons/negative";
	else
		$flashvars .= "&amp;buttondir=".powerpress_get_root_url().'buttons/'.$PlayerSettings['buttondir'];

	$flashvars .= '&amp;mode='. $PlayerSettings['mode'];
  
	// Add audioplay player here
	$player_id = powerpressplayer_get_next_id();
	$content = '';
	$content .= '<div class="powerpress_player" id="powerpress_player_'. $player_id .'">';
	$content .= '<object type="application/x-shockwave-flash" width="'. $width .'" height="'. $height .'" id="audioplay_'.$player_id.'" data="'. powerpress_get_root_url().'audioplay.swf?'.$flashvars.'">'.PHP_EOL;
	$content .= '<param name="movie" value="'. powerpress_get_root_url().'audioplay.swf?'.$flashvars.'" />'.PHP_EOL;
	$content .= '<param name="quality" value="high" />'.PHP_EOL;
	$content .= $transparency.PHP_EOL;
	$content .= '<param name="FlashVars" value="'.$flashvars.'" />'.PHP_EOL;
	$content .= '<embed src="'. powerpress_get_root_url().'audioplay.swf?'.$flashvars.'" quality="high"  width="30" height="30" type="application/x-shockwave-flash">'.PHP_EOL;
	$content .= "</embed>\n		</object>\n";
	$content .= "</div>\n";
	return $content;
}

/*
Simple Flash player
*/
function powerpressplayer_build_simpleflash($media_url, $EpisodeData = array())
{
	$autoplay = false;
	if( isset($EpisodeData['autoplay']) && $EpisodeData['autoplay'] )
		$autoplay = true; // TODO: We need to handle this
	
	$player_id = powerpressplayer_get_next_id();
	$content = '';
	$content .= '<div class="powerpress_player" id="powerpress_player_'. $player_id .'">';
	$content .= '<object type="application/x-shockwave-flash" data="'. powerpress_get_root_url() .'simple_mp3.swf" id="simple_mp3_'.$player_id.'" width="150" height="50">';
	$content .= '<param name="movie" value="'. powerpress_get_root_url().'simple_mp3.swf" />';
	$content .= '<param name="wmode" value="transparent" />';
	$content .= '<param name="FlashVars" value="'. get_bloginfo('url') .'?url='. urlencode($media_url).'&amp;autostart='. ($autoplay?'true':'false') .'" />';
	$content .= '<param name="quality" value="high" />';
	$content .= '<embed wmode="transparent" src="'. get_bloginfo('url') .'?url='.urlencode($media_url).'&amp;autostart='. ($autoplay?'true':'false') .'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="150" height="50"></embed>';
	$content .= '</object>';
	$content .= "</div>\n";
	return $content;
}

/*
VideoJS
*/
function powerpressplayer_build_videojs($media_url, $EpisodeData = array())
{
	
	
	return $content;
}

/*
Flare Player - Video via HTML5 with Flash fallback 
*/
function powerpressplayer_build_flareplayer($media_url, $EpisodeData = array() )
{
	// Generate next player ID
	$player_id = powerpressplayer_get_next_id();
	$width = 400;
	$height = 225;
	if( !empty($EpisodeData['width']) && !empty($EpisodeData['height']) && is_numeric($EpisodeData['width']) && is_numeric($EpisodeData['height']) ) {
		$width = $EpisodeData['width'];
		$height = $EpisodeData['height'];
	}
	
	$content = '';
	$content .= '<div class="powerpress_player" id="powerpress_player_'. $player_id .'"';
	$content .= '></div>' . PHP_EOL;
	$content .= '<script type="text/javascript" charset="utf-8">' . PHP_EOL;
	$content .= 'jQuery(document).ready( function(){'. PHP_EOL;
	
	$content .= ''. PHP_EOL;
	if( !empty($EpisodeData['fullscreen']) && $EpisodeData['fullscreen'] ) {
		$content .= '	var playerHeight = jQuery(window).height();'. PHP_EOL;
		$content .= '	var playerWidth = jQuery(window).width();'. PHP_EOL;
	} else {
		$content .= '	var playerHeight = '. $height .';'. PHP_EOL;
		$content .= '	var playerWidth = '. $width .';'. PHP_EOL;
	}
	$content .= ''. PHP_EOL;
	$content .= '	jQuery("#powerpress_player_'. $player_id .'").css( {\'overflow\':\'none\',\'width\':playerWidth+\'px\',\'height\':playerHeight+\'px\'});'. PHP_EOL;
	$content .= '	fv'. $player_id .' = jQuery("#powerpress_player_'. $player_id .'").flareVideo({'. PHP_EOL;
	if( !empty($EpisodeData['poster']) )
		$content .= '		poster: "'.  $EpisodeData['poster'] .'",'. PHP_EOL;
  $content .= '		flashSrc: "'. powerpress_get_root_url() .'3rdparty/flare_player/FlareVideo.swf",'. PHP_EOL;
	$content .= '		autobuffer: false,'. PHP_EOL;
	$content .= '		preload: false,'. PHP_EOL;
	$content .= '		autoplay: false,'. PHP_EOL;
	$content .= '		width: playerWidth,'. PHP_EOL;
	$content .= '		height: playerHeight,'. PHP_EOL;
	$content .= '		playload: true'. PHP_EOL;
	$content .= '	});'. PHP_EOL;
	
	$content .= '	fv'. $player_id .'.load([{'. PHP_EOL;
	$content .= '			src: \''. $media_url .'\', type: \'video/mp4\''. PHP_EOL;
	$content .= '	}]);'. PHP_EOL;
	
	if( !empty($EpisodeData['autoplay']) ) // Auto play
		$content .= '  fv'. $g_powerpress_player_id .'.play();'. PHP_EOL;
	$content .= '});'. PHP_EOL; // end jQuery block
	$content .= '</script>' . PHP_EOL;
	return $content;
}


?>