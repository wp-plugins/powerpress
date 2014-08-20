<?php

function powerpresssubscribe_get_settings($feed_slug='podcast')
{
	$GeneralSettings = get_option('powerpress_general');
	if( !empty($GeneralSettings['taxonomy_podcasting']) )
		return false; // Feature not avaialble for taxonomy podcasting
		
	// We need to know if category podcasting is enabled, if it is then we may need to dig deeper for this info....
	if( !empty($GeneralSettings['cat_casting']) && $feed_slug == 'podcast' )
	{
		if( is_category() ) // Special case where we want to track the category separately
		{
			$Settings = get_option('powerpress_cat_feed_'.get_query_var('cat') );
			$Settings['rss_url'] = get_category_feed_link( get_query_var('cat') ); // Get category feed URL
			return $Settings;
		}
		else if( is_single() )
		{
			$categories = wp_get_post_categories( get_the_ID() );
			if( count($categories) == 1 )
			{
				list($null,$cat_id) = each($categories);
				$Settings = get_option('powerpress_cat_feed_'.$cat_id );
				$Settings['rss_url'] = get_category_feed_link($cat_id); // Get category feed URL
				return $Settings;
			}
		}
		
		return false; // When category podcasting enabled, we must only have one category per post
	}
	
	$post_type = get_post_type();
	// $feedslug
	
	// Post Type Podcasting
	if( !empty($GeneralSettings['posttype_podcasting']) )
	{
		return false; // Not suported for now
	}
	
	if( $feed_slug == 'podcast' )
		$FeedSettings = get_option('powerpress_feed'); // Get the main feed settings
	else
		$FeedSettings = get_option('powerpress_feed_'. $feed_slug);
	if( !empty($FeedSettings) )
	{
		$FeedSettings['rss_url'] =  get_feed_link($feed_slug); // Get Podcast RSS Feed
		return $FeedSettings;
	}
	return false;
}

/*
case 'ttid':
		case 'category': {
			echo get_category_feed_link($cat_ID);
		}; break;
		case 'channel': {
			echo get_feed_link($feed_slug);
		}; break;
		case 'post_type': {
			$url = get_post_type_archive_feed_link($post_type, $feed_slug);
		}; break;
		case 'general':
		default: {
			echo get_feed_link('podcast');
		}
*/

// 1: Subscribe widget added to the links...
function powerpressplayer_link_subscribe_pre($content, $media_url, $ExtraData = array() )
{
	
	
	$SubscribeSettings = powerpresssubscribe_get_settings( (empty($ExtraData['feed'])?'podcast': $ExtraData['feed']) );
	if( empty($SubscribeSettings) )
		return $content;
	
	//echo "gooder";
	
	if( !isset($SubscribeSettings['subscribe_links']) )
		$SubscribeSettings['subscribe_links'] = 1; // Default make this the first link option
		
	if( $SubscribeSettings['subscribe_links'] != 1 ) // beginning of links
		return $content;
		
	//var_dump($SubscribeSettings);
		
	$rss_url = $SubscribeSettings['rss_url'];
	$itunes_url = trim($SubscribeSettings['itunes_url']);
	if( empty($itunes_url) )
		$itunes_url = preg_replace('/(^https?:\/\/)/i', 'itpc://', $rss_url);
	
	$player_links = '';
	$player_links .= "<a href=\"{$itunes_url}\" class=\"powerpress_link_subscribe\" title=\"". __('Subscribe on iTunes', 'powerpress') ."\" rel=\"nofollow\">". __('iTunes','powerpress') ."</a>".PHP_EOL;
	$player_links .= ' '.POWERPRESS_LINK_SEPARATOR .' ';
	$player_links .= "<a href=\"{$rss_url}\" class=\"powerpress_link_subscribe\" title=\"". __('Subscribe via RSS', 'powerpress') ."\" rel=\"nofollow\">". __('RSS','powerpress') ."</a>".PHP_EOL;
	$content .= $player_links;
	return $content;
}

function powerpressplayer_link_subscribe_post($content, $media_url, $ExtraData = array() )
{
	if( $content )
	{
		$GeneralSettings = get_option('powerpress_general');
		
		$label = __('Subscribe:', 'powerpress');
		if( !empty($GeneralSettings['subscribe_label']) )
			$label = $GeneralSettings['subscribe_label'];
		// Get label setting from $GeneralSettings
		$prefix = htmlspecialchars($label) . ' ';
		
		$return = '<p class="powerpress_links powerpress_subsribe_links">'. $prefix . $content . '</p>';
		return $return;
	}
	return $content;
}

// 2 Subscribe page shortocde [powerpress_subscribe feedslug="podcast"]

// 3 Subscribe sidebar widget: iTunes, RSS

add_filter('powerpress_player_subscribe_links', 'powerpressplayer_link_subscribe_pre', 1, 3);
add_filter('powerpress_player_subscribe_links', 'powerpressplayer_link_subscribe_post', 1000, 3);
