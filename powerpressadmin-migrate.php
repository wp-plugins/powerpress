<?php


function powerpress_admin_migrate_get_files()
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
				
				$return[$meta_id] = $row;
				$return[$meta_id]['on_blubrry'] = ( strstr($EpisodeData['url'], 'content.blubrry.com') );
				$return[$meta_id]['src_url'] = $EpisodeData['url'];
			}
		}
		return $return;
}


function powerpress_admin_migrate()
{
	$files = powerpress_admin_migrate_get_files();
?>
<input type="hidden" name="action" value="powerpress-save-tags" />
<h2><?php echo __('Migrate Media to your Blubrry Podcast Media Hosting Account', 'powerpress'); ?></h2>

<p><?PHP echo __('Blubrry Hosting users can migrate all of their existing media to blubrry with only a few clicks.', 'powerpress'); ?></p>

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

