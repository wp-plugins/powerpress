<?php

function powerpresssubscribe_get_subscribe_page($Settings)
{
	if( !empty($Settings['subscribe_page_link_id']) && is_numeric($Settings['subscribe_page_link_id']) )
		return get_page_link($Settings['subscribe_page_link_id']);
	if( !empty($Settings['subscribe_page_link_href']) )
		return $Settings['subscribe_page_link_href'];
	return '';
}

function powerpresssubscribe_get_itunes_url($Settings)
{
	if( !empty($Settings['itunes_url']) )
		return trim($Settings['itunes_url']);

	if( !empty($Settings['feed_url']) )
		return preg_replace('/(^https?:\/\/)/i', 'itpc://', $Settings['feed_url']);
	
	return '';
}

function powerpresssubscribe_get_settings($ExtraData)
{
	$GeneralSettings = get_option('powerpress_general');
	//if( !empty($GeneralSettings['taxonomy_podcasting']) )
	//	return false; // Feature not available for taxonomy podcasting
	
	$feed_slug = (empty($ExtraData['feed'])?'podcast': $ExtraData['feed']);
	$post_type = (empty($ExtraData['post_type'])?false: $ExtraData['post_type']);
	$category_id = (empty($ExtraData['cat_id'])?false: $ExtraData['cat_id']);
	$taxonomy_term_id = (empty($ExtraData['taxonomy_term_id'])?false: $ExtraData['taxonomy_term_id']);
	
	if( empty($ExtraData['type']) ) // Make sure this value is set
		$ExtraData['type'] = '';
	
	switch( $ExtraData['type'] )
	{
		case 'post_type': {
			$category_id = 0;
			$taxonomy_term_id = 0;
		};
		case 'channel': {
			$category_id = 0;
			$taxonomy_term_id = 0;
			$post_type = 0;
		}; break;
		case 'category': {
			$feed_slug = 'podcast';
			$taxonomy_term_id = 0;
			$post_type = 0;
		}; break;
		case 'ttid': {
			$feed_slug = 'podcast';
			$category_id = 0;
			$post_type = 0;
		}; break;
		case 'general': {
			$feed_slug = 'podcast';
			$category_id = 0;
			$post_type = 0;
			$taxonomy_term_id = 0;
		}; break;
	}	
	
	if( !empty($GeneralSettings['taxonomy_podcasting']) )
	{
		// TODO! Taxonomy Podcasting subscription options
	}
		
	// We need to know if category podcasting is enabled, if it is then we may need to dig deeper for this info....
	if( !empty($GeneralSettings['cat_casting']) && $feed_slug == 'podcast' )
	{
		if( !$category_id && is_category() )
		{
			$category_id = get_query_var('cat');
		}
		if( !$category_id && is_single() )
		{
			$categories = wp_get_post_categories( get_the_ID() );
			if( count($categories) == 1 )
				list($null,$category_id) = each($categories);
		}
		
		if( $category_id ) // We are on a category page, makes it easy...
		{
			$Settings = get_option('powerpress_cat_feed_'.$category_id );
			if( !empty($Settings) )
			{
				$Settings['title'] = $Settings['feed_title'];
				$Settings['feed_url'] = get_category_feed_link( $category_id ); // Get category feed URL
				$Settings['subscribe_page_url'] = powerpresssubscribe_get_subscribe_page($Settings);
				$Settings['itunes_url'] = powerpresssubscribe_get_itunes_url($Settings);
				return $Settings;
			}
		}
		// let fall through to find better settings
	}
	
	// Post Type Podcasting
	if( !empty($GeneralSettings['posttype_podcasting']) )
	{
		if( empty($post_type) && !empty($ExtraData['id']) )
			$post_type = get_post_type( $ExtraData['id'] );
		
		switch( $post_type )
		{
			case 'page':
			case 'post':
			{
				// SWEET, CARRY ON!
			}; break;
			default: {
				// TODO
				// $url = get_post_type_archive_feed_link($post_type, $feed_slug);
				return false; // Not suported for now
			}; break;
		}
	}
	
	// Podcast default and channel feed settings
	$FeedSettings = get_option('powerpress_feed_'. $feed_slug);
	if( empty($FeedSettings) && $feed_slug == 'podcast' )
		$FeedSettings = get_option('powerpress_feed'); // Get the main feed settings
	
	if( !empty($FeedSettings) )
	{
		$FeedSettings['title'] = $FeedSettings['feed_title'];
		$FeedSettings['feed_url'] =  get_feed_link($feed_slug); // Get Podcast RSS Feed
		$FeedSettings['subscribe_page_url'] = powerpresssubscribe_get_subscribe_page($FeedSettings);
		$FeedSettings['itunes_url'] = powerpresssubscribe_get_itunes_url($FeedSettings);
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
			
		}; break;
		case 'general':
		default: {
			echo get_feed_link('podcast');
		}
*/

// 1: Subscribe widget added to the links...
function powerpressplayer_link_subscribe_pre($content, $media_url, $ExtraData = array() )
{
	$SubscribeSettings = powerpresssubscribe_get_settings( $ExtraData );
	if( empty($SubscribeSettings) )
		return $content;
	
	if( !isset($SubscribeSettings['subscribe_links']) )
		$SubscribeSettings['subscribe_links'] = 1; // Default make this the first link option
		
	if( $SubscribeSettings['subscribe_links'] != 1 ) // beginning of links
		return $content;
		
	$feed_url = $SubscribeSettings['feed_url'];
	$itunes_url = trim($SubscribeSettings['itunes_url']);
	if( empty($itunes_url) )
		$itunes_url = preg_replace('/(^https?:\/\/)/i', 'itpc://', $feed_url);
	
	$player_links = '';
	$player_links .= "<a href=\"{$itunes_url}\" class=\"powerpress_link_subscribe powerpress_link_subscribe_itunes\" title=\"". __('Subscribe on iTunes', 'powerpress') ."\" rel=\"nofollow\">". __('iTunes','powerpress') ."</a>".PHP_EOL;
	$player_links .= ' '.POWERPRESS_LINK_SEPARATOR .' ';
	$player_links .= "<a href=\"{$feed_url}\" class=\"powerpress_link_subscribe powerpress_link_subscribe_rss\" title=\"". __('Subscribe via RSS', 'powerpress') ."\" rel=\"nofollow\">". __('RSS','powerpress') ."</a>".PHP_EOL;
	if( !empty($SubscribeSettings['subscribe_page_url']) )
	{
		$label = (empty($SubscribeSettings['subscribe_page_link_text'])?__('More Subscribe Options', 'powerpress'):$SubscribeSettings['subscribe_page_link_text']);
		$player_links .= ' '.POWERPRESS_LINK_SEPARATOR .' ';
		$player_links .= "<a href=\"{$SubscribeSettings['subscribe_page_url']}\" class=\"powerpress_link_subscribe powerpress_link_subscribe_more\" title=\"". htmlspecialchars($label) ."\" rel=\"nofollow\">". htmlspecialchars($label) ."</a>".PHP_EOL;
	}
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

function powerpress_subscribe_shortcode( $attr ) {
	global $content_width;
	
	if ( is_feed() ) {
		return '';
	}

	static $instance = 0;
	$instance++;
	
	extract( shortcode_atts( array(
		'channel'=>'', // Used for PowerPress Podcast Channels
		'slug' => '', // Used for PowerPress (alt for 'channel')
		'feed' => '', // Used for PowerPress (alt for 'channel')
		'post_type' => 'post', // Used for PowerPress 
		'category'=>'', // Used for PowerPress (specify category ID, name or slug)
		'term_taxonomy_id'=>'', // Used for PowerPress (specify term taxonomy ID)
		//'term_id'=>'', // Used for PowerPress (specify term ID, name or slug)
		//'taxonomy'=>'', // Used for PowerPress (specify taxonomy name)
		
		'title'	=> '', // Display custom title of show/program
		'feed_url'=>'', // provide subscribe widget for specific RSS feed
		'itunes_url'=>'' // provide subscribe widget for specific iTunes subscribe URL
	), $attr, 'powerpresssubscribe' ) );
	
	if( empty($slug) && !empty($feed) ) 
		$slug = $feed;
	if( empty($slug) && !empty($channel) ) 
		$slug = $channel;
	if( empty($slug) )
		$slug = 'podcast';

	
	$Settings = powerpresssubscribe_get_settings(  array('feed'=>$slug, 'taxonomy_term_id'=>$term_taxonomy_id, 'cat_id'=>$category, 'post_type'=>$post_type) );
	if( empty($Settings) )
		return '';
		
	return powerpress_do_subscribe_widget($Settings);

	// DO SHORTCODE HERE!!!
	$html = '<div>';
		// iTunes Subscribe Button
		$html .= '<div>';
		$html .= '';
		$html .='<a href="';
		$html .= $Settings['itunes_url'];
		// TODO: Always add ?mt=2 to end of itunes.apple.com URLs, and alwyas make them https URLs
		$html .= '" target="itunes_store" style="display:inline-block;overflow:hidden;background:url(https://linkmaker.itunes.apple.com/htmlResources/assets/en_us//images/web/linkmaker/badge_subscribe-lrg.png) no-repeat;width:135px;height:40px;}"></a>';
		$html .= '</div>';
		
		// RSS Subscribe Link...
		$html .= '<p>';
		$html .= "<a href=\"{$Settings['feed_url']}\" title=\"". __('Subscribe via RSS', 'powerpress') ."\" rel=\"nofollow\">";
		$html .= "<img style=\"border: 0;vertical-align: middle;\" src=\"". powerpress_get_root_url() ."images/RSSIcon24x24.png\" alt=\"". __('Subscribe via RSS', 'powerpress') ."\" />";
		$html .= "</a> ";
		$html .= "<a href=\"{$Settings['feed_url']}\" title=\"". __('Subscribe via RSS', 'powerpress') ."\" rel=\"nofollow\">". __('Subscribe via RSS','powerpress') ."</a>".PHP_EOL;
		$html .= '</p>';
		
		
		// PB Subscribe Link...
		$html .= '<p>';
		$html .= "<a href=\"{$Settings['feed_url']}\" title=\"". __('Subscribe on BeyondPod for Android', 'powerpress') ."\" rel=\"nofollow\">";
		$html .= "<img style=\"border: 0;vertical-align: middle;\" src=\"". powerpress_get_root_url() ."images/BPIcon24x24.png\" alt=\"". __('Subscribe on BeyondPod for Android', 'powerpress') ."\" />";
		$html .= "</a> ";
		$html .= "<a href=\"{$Settings['feed_url']}\" title=\"". __('Subscribe on BeyondPod for Android', 'powerpress') ."\" rel=\"nofollow\">". __('Subscribe on BeyondPod for Android','powerpress') ."</a>".PHP_EOL;
		$html .= '</p>';
		
		// PR Subscribe Link...
		$html .= '<p>';
		$html .= "<a href=\"{$Settings['feed_url']}\" title=\"". __('Subscribe on Podcast Republic for Android', 'powerpress') ."\" rel=\"nofollow\">";
		$html .= "<img style=\"border: 0;vertical-align: middle;\" src=\"". powerpress_get_root_url() ."images/PRIcon24x24.png\" alt=\"". __('Subscribe on Podcast Republic for Android', 'powerpress') ."\" />";
		$html .= "</a> ";
		$html .= "<a href=\"{$Settings['feed_url']}\" title=\"". __('Subscribe on Podcast Republic for Android', 'powerpress') ."\" rel=\"nofollow\">". __('Subscribe on Podcast Republic for Android','powerpress') ."</a>".PHP_EOL;
		$html .= '</p>';
	
	$html .= '</div>';
	return $html;
}

add_shortcode( 'powerpresssubscribe', 'powerpress_subscribe_shortcode' );
add_shortcode( 'powerpress_subscribe', 'powerpress_subscribe_shortcode' );
	
require_once( POWERPRESS_ABSPATH . '/class.powerpress-subscribe-widget.php' );

function powerpress_do_subscribe_widget($settings)
{
	if( empty($settings['feed_url']) )
	{
		//return print_r($settings, true);
	}
	
	if( empty($settings['title']) )
	{
		$settings['title'] = get_bloginfo('name');
	}
	
	if( empty($settings['itunes_url']) )
	{
		$settings['itunes_url'] = powerpresssubscribe_get_itunes_url( $settings );
	}
	
	if( empty($settings['style']) )
	{
		$settings['style'] = 'modern';
	}
	
	if( empty($settings['image_url']) )
	{
		$settings['image_url'] = powerpress_get_root_url() . 'itunes_default.jpg'; // Default PowerPress image used in this case.
	}
	
	$htmlX = '';
	$html = '';
	$html .= '<div class="pp-sub-widget pp-sub-widget-'. $settings['style'] .'">';
		$html .= '<div class="pp-sub-h">'.  __('Subscribe to', 'powerpress') .'</div>';
		$html .= '<h2 class="pp-sub-t">'.  htmlspecialchars( $settings['title'] ) .'</h2>';
			$html .= '<div class="pp-sub-bx">';
				$html .= '<img class="pp-sub-l" src="'. htmlspecialchars( $settings['image_url'] ) .'" alt="'.  htmlspecialchars( $settings['title'] ) .'" />';
				$html .= '<div class="pp-sub-btns">';
				$html .= '<a href="'.  htmlspecialchars( $settings['itunes_url'] ) .'" class="pp-sub-btn pp-sub-itunes"><span class="pp-sub-ic"></span>'.  htmlspecialchars( __('on iTunes', 'powerpress') ) .'</a>';
				$html .= '<a href="'.  htmlspecialchars( $settings['feed_url'] ) .'" class="pp-sub-btn pp-sub-rss"><span class="pp-sub-ic"></span>'.  htmlspecialchars( __('via RSS', 'powerpress') ) .'</a>';
				$htmlX .= '<a href="" class="pp-sub-btn pp-sub-email"><span class="pp-sub-ic"></span>'.  htmlspecialchars( __('via Email', 'powerpress') ) .'</a>';
				$html .= '<a href="'.  htmlspecialchars( $settings['itunes_url'] ) .'" class="pp-sub-btn pp-sub-bp"><span class="pp-sub-ic"></span>'.  htmlspecialchars( __('BeyondPod', 'powerpress') ) .' <span class="pp-sub-btn-x">'.  htmlspecialchars( __('for Android', 'powerpress') ) .'</span></a>';
				$html .= '<a href="'.  htmlspecialchars( $settings['feed_url'] ) .'" class="pp-sub-btn pp-sub-pr"><span class="pp-sub-ic"></span>'.  htmlspecialchars( __('Podcast Republic', 'powerpress') ) .' <span class="pp-sub-btn-x">'.  htmlspecialchars( __('for Android', 'powerpress') ) .'</span></a>';
			$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="pp-sub-m">';
			$html .= '<p class="pp-sub-m-p">'.  htmlspecialchars( __('Podcast Republic', 'powerpress') ) .' <span class="pp-sub-btn-x">'.  htmlspecialchars( __('Or subscribe with your favorite app by using the address below', 'powerpress') ) .'</p>';
			$html .= '<input class="pp-sub-m-i" type="text" name="NULL'. rand(0,9999) .'" value="'.  htmlspecialchars( $settings['feed_url'] ) .'" onclick="this.focus();this.select();" />';
		$html .= '</div>';
	$html .= '</div>';

	return $html;
}

function powerpress_do_subscribe_sidebar_widget($settings)
{
	
	if( empty($settings['feed_url']) )
	{
		return '';
	}
	
	if( empty($settings['itunes_url']) )
	{
		$settings['itunes_url'] = powerpresssubscribe_get_itunes_url( $settings );
	}
	
	if( empty($settings['style']) )
	{
		$settings['style'] = 'modern';
	}
	

	
	
	$htmlX = '';
	$html = '';

	$html .= '<div class="pp-ssb-widget pp-ssb-widget-'. $settings['style'] .'">';
		$html .= '<a href="'.  htmlspecialchars( $settings['itunes_url'] ) .'" class="pp-ssb-btn pp-ssb-itunes"><span class="pp-ssb-ic"></span>'.  htmlspecialchars( __('on iTunes', 'powerpress') ) .'</a>';
		$html .= '<a href="'.  htmlspecialchars( $settings['feed_url'] ) .'" class="pp-ssb-btn pp-ssb-rss"><span class="pp-ssb-ic"></span>'.  htmlspecialchars( __('via RSS', 'powerpress') ) .'</a>';
		$htmlX .= '<a href="" class="pp-ssb-btn pp-ssb-email"><span class="pp-ssb-ic"></span>'.  htmlspecialchars( __('via Email', 'powerpress') ) .'</a>';
		if( !empty($settings['subscribe_page_url']) )
			$html .= '<a href="'.  htmlspecialchars( $settings['subscribe_page_url'] ) .'" class="pp-ssb-btn pp-ssb-more"><span class="pp-ssb-ic"></span>'.  htmlspecialchars( __('More Subscribe Options', 'powerpress') ) .'</a>';
	$html .= '</div>';

	return $html;
}