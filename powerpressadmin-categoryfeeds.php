<?php

	
function powerpress_admin_customfeeds_columns($data=array())
{
	$data['name'] = 'Category Name';
	$data['feed-slug'] = 'Slug';
	//$data['episode-count'] = 'Episodes';
	$data['url'] = 'Feed URL';
	return $data;
}

add_filter('manage_powerpressadmin_categoryfeeds_columns', 'powerpress_admin_customfeeds_columns');

function powerpress_admin_categoryfeeds()
{
	$General = powerpress_get_settings('powerpress_general');
	
	
?>
<h2><?php echo __("Category Podcast Feeds"); ?></h2>
<p>
	Category podcast feeds add custom podcast settings to specific blog category feeds.
	Category podcast feeds allow you to organize episodes by topic.
</p>
<p>
	If you are looking to organize episodes by file or format, please use <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_customfeeds.php'); ?>" title="Custom Podcast Feeds">Custom Podcast Feeds</a>.
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
<?php print_column_headers('powerpressadmin_categoryfeeds'); ?>
	</tr>
	</thead>

	<tfoot>
	<tr>
<?php print_column_headers('powerpressadmin_categoryfeeds', false); ?>
	</tr>
	</tfoot>
	<tbody>
<?php
	
	
	$Feeds = array();
	if( isset($General['custom_cat_feeds']) )
		$Feeds = $General['custom_cat_feeds'];
		
	$count = 0;
	while( list($null, $cat_ID) = each($Feeds) )
	{
		$category = get_category_to_edit($cat_ID);
		
		
		$columns = powerpress_admin_customfeeds_columns();
		$hidden = array();
		if( $feed_slug == 'podcast' )
			$feed_title = 'Podcast Feed (default)';
		$feed_title = wp_specialchars($feed_title);
		if( $count % 2 == 0 )
			echo '<tr valign="middle" class="alternate">';
		else
			echo '<tr valign="middle">';
			
		$edit_link = admin_url('admin.php?page=powerpress/powerpressadmin_categoryfeeds.php&amp;action=powerpress-editcategoryfeed&amp;cat=') . $cat_ID;
		
		$feed_title = $category->name;
		$url = get_category_feed_link($cat_ID);
		$short_url = str_replace('http://', '', $url);
		$short_url = str_replace('www.', '', $short_url);
		if (strlen($short_url) > 35)
			$short_url = substr($short_url, 0, 32).'...';

		foreach($columns as $column_name=>$column_display_name) {
			$class = "class=\"column-$column_name\"";
			
			
			
			//$short_url = '';
			
			switch($column_name) {
				case 'feed-slug': {
					
					echo "<td $class>{$category->slug}";
					echo "</td>";
					
				}; break;
				case 'name': {

					echo '<td '.$class.'><strong><a class="row-title" href="'.$edit_link.'" title="' . attribute_escape(sprintf(__('Edit "%s"'), $feed_title)) . '">'.$feed_title.'</a></strong><br />';
					$actions = array();
					$actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
					$actions['remove'] = "<a class='submitdelete' href='". admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_categoryfeeds.php&amp;action=powerpress-delete-category-feed&amp;cat=$cat_ID", 'powerpress-delete-category-feed-' . $cat_ID) . "' onclick=\"if ( confirm('" . js_escape(sprintf( __("You are about to remove podcast settings for category feed '%s'\n  'Cancel' to stop, 'OK' to delete."), $feed_title )) . "') ) { return true;}return false;\">" . __('Remove') . "</a>";
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
							echo '<span class="'.$action .'"><a href="http://www.feedvalidator.org/check.cgi?url='. urlencode( str_replace('&amp;', '&', $url) ) .'" target="_blank">' . __('Validate Feed') . '</a></span>';
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
<h3><?php _e('Add Podcast Settings to existing Category Feed'); ?></h3>
<input type="hidden" name="action" value="powerpress-addcategoryfeed" />
<?php
	//wp_original_referer_field(true, 'previous'); 
	wp_nonce_field('powerpress-add-category-feed');
?>

<div class="form-field form-required">
	<label for="feed_name"><?php _e('Category') ?></label>
	<select name="cat" id="cat_id" style="width: 100%;">
		<option value="">Select Category</option>
<?php
	wp_dropdown_cats();
?>
	</select>
    
</div>

<p class="submit"><input type="submit" class="button" name="submit" value="<?php _e('Add Podcast Settings to Category Feed'); ?>" /></p>

</div>
</div>

</div> <!-- col-left -->

</div> <!-- col-container -->

<h3>Example Usage</h3>
<p>
	Example 1: You have a podcast that covers two topics that sometimes share same posts and sometimes do not. Use your main podcast feed as a combined feed of both topics
	and use category feeds to distribute topic specific episodes.
</p>
<p>
	Example 2: You want to use categories to keep episodes separate from each other. Each category can be used to distribute separate podcasts with the main podcast feed
	combining all categories to provide a network feed.
</p>

<?php
	}
?>