<?php

	
function powerpress_admin_customfeeds_columns($data=array())
{
	$data['name'] = 'Name';
	$data['feed-slug'] = 'Slug';
	$data['episode-count'] = 'Episodes';
	$data['url'] = 'URL';
	return $data;
}

add_filter('manage_powerpressadmin_customfeeds_columns', 'powerpress_admin_customfeeds_columns');

function powerpress_admin_customfeeds()
{
	$General = powerpress_get_settings('powerpress_general');
	
	
?>
<h2><?php echo __("Custom Podcast Feeds"); ?></h2>
<p>
	Custom podcast feeds allow you to associate multiple media files and/or formats to one blog post. Note that additional custom feeds
	will not appear in the online player or in download links.
</p>
<p>
	If you are looking to organize episodes by topic, please use <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_categoryfeeds.php'); ?>" title="Category Podcast Feeds">Category Podcast Feeds</a>.
</p>

<style type="text/css">

.column-url {
	width: 40%;
}
.column-name {
	width: 30%;
}
.column-feed-slug {
	width: 15%;
}
.column-episode-count {
	width: 15%;
}
</style>
<div id="col-container">

<div id="col-right">
<table class="widefat fixed" cellspacing="0">
	<thead>
	<tr>
<?php print_column_headers('powerpressadmin_customfeeds'); ?>
	</tr>
	</thead>

	<tfoot>
	<tr>
<?php print_column_headers('powerpressadmin_customfeeds', false); ?>
	</tr>
	</tfoot>
	<tbody>
<?php
	
	
	$Feeds = array('podcast'=>'Podcast Feed');
	if( isset($General['custom_feeds']['podcast']) )
		$Feeds = $General['custom_feeds'];
	else if( is_array($General['custom_feeds']) )
		$Feeds += $General['custom_feeds'];
		
	asort($Feeds, SORT_STRING); // Sort feeds 
	
	$count = 0;
	while( list($feed_slug, $feed_title) = each($Feeds	) )
	{
		$episode_total = powerpress_admin_episodes_per_feed($feed_slug);
		$columns = powerpress_admin_customfeeds_columns();
		$hidden = array();
		if( $feed_slug == 'podcast' )
			$feed_title = 'Podcast Feed (default)';
		$feed_title = wp_specialchars($feed_title);
		if( $count % 2 == 0 )
			echo '<tr valign="middle" class="alternate">';
		else
			echo '<tr valign="middle">';

		foreach($columns as $column_name=>$column_display_name) {
			$class = "class=\"column-$column_name\"";
			
			$edit_link = admin_url('admin.php?page=powerpress/powerpressadmin_customfeeds.php&amp;action=powerpress-editfeed&amp;feed_slug=') . $feed_slug;
			
			$url = get_feed_link($feed_slug);
			$short_url = str_replace('http://', '', $url);
			$short_url = str_replace('www.', '', $short_url);
			//if ('/' == substr($short_url, -1))
			//	$short_url = substr($short_url, 0, -1);
			if (strlen($short_url) > 35)
				$short_url = substr($short_url, 0, 32).'...';
			
			//$short_url = '';
			
			switch($column_name) {
				case 'feed-slug': {
					
					echo "<td $class>$feed_slug";
					echo "</td>";
					
				}; break;
				case 'name': {

					echo '<td '.$class.'><strong><a class="row-title" href="'.$edit_link.'" title="' . attribute_escape(sprintf(__('Edit "%s"'), $feed_title)) . '">'.$feed_title.'</a></strong><br />';
					$actions = array();
					$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
					$actions['delete'] = "<a class='submitdelete' href='". admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_customfeeds.php&amp;action=powerpress-delete-feed&amp;feed_slug=$feed_slug", 'powerpress-delete-feed-' . $feed_slug) . "' onclick=\"if ( confirm('" . js_escape(sprintf( __("You are about to delete feed '%s'\n  'Cancel' to stop, 'OK' to delete."), $feed_title )) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
					$action_count = count($actions);
					$i = 0;
					echo '<div class="row-actions">';
					foreach ( $actions as $action => $linkaction ) {
						++$i;
						( $i == $action_count ) ? $sep = '' : $sep = ' | ';
						echo '<span class="'.$action.'">'.$linkaction.$sep .'</span>';
					}
					echo '</div>';
					echo '</td>';
					
				};	break;
					
				case 'url': {
				
					echo "<td $class><a href='$url' title='". attribute_escape(sprintf(__('Visit %s'), $feed_title))."' target=\"_blank\">$short_url</a>";
						echo '<div class="row-actions">';
							echo '<span class="'.$action .'"><a href="http://www.feedvalidator.org/check.cgi?url='. urlencode($url) .'" target="_blank">' . __('Validate Feed') . '</a></span>';
						echo '</div>';
					echo "</td>";
					
				};	break;
					
				case 'episode-count': {
				
					echo "<td $class>$episode_total";
					echo "</td>";
					
				}; break;
				default: {
				
				};	break;
			}
		}
		echo "\n    </tr>\n";
		$count++;
	}
?>
	</tbody>
</table>
</div> <!-- col-right -->

<div id="col-left">
<div class="col-wrap">
<div class="form-wrap">
<h3><?php _e('Add Podcast Feed'); ?></h3>
<div id="ajax-response"></div>
<input type="hidden" name="action" value="powerpress-addfeed" />
<?php
	//wp_original_referer_field(true, 'previous'); 
	//wp_nonce_field('powerpress-add-feed');
?>

<div class="form-field form-required">
	<label for="feed_name"><?php _e('Feed Name') ?></label>
	<input name="feed_name" id="feed_name" type="text" value="" size="40" />
    <p><?php _e('The name is used for use within the administration area only.'); ?></p>
</div>

<div class="form-field">
	<label for="feed_slug"><?php _e('Feed Slug') ?></label>
	<input name="feed_slug" id="feed_slug" type="text" value="" size="40" />
    <p><?php _e('The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.'); ?></p>
</div>

<p class="submit"><input type="submit" class="button" name="submit" value="<?php _e('Add Podcast Feed'); ?>" /></p>

</div>
</div>

</div> <!-- col-left -->

</div> <!-- col-container -->

<h3>Example Usage</h3>
<p>
	Example 1: You want to distribute both an mp3 and an ogg version of your podcast. Use the default podcast feed for your mp3
	media and create a custom feed for your ogg media.
</p>
<p>
	Example 2: You have a video podcast with multiple file formats. Use the default podcast feed for the main media that you
	want to appear on your blog (e.g. m4v). Create additional feeds for the remaining formats (e.g. wmv, mov, mpeg).
</p>
<p>
	Example 3: You create two versions of your podcast, a 20 minute summary and a full 2 hour episode. Use the default feed for
	your 20 minute summary episodes and create a new custom feed for your full length episodes.
</p>

<?php
	}
?>