<?php

function powerpress_admin_podpress_stats()
{
	global $wpdb;
	
	$query = "SELECT COUNT(`media`) AS media_count FROM {$wpdb->prefix}podpress_statcounts ";
	$EpisodeTotal = $wpdb->get_results($query, ARRAY_A);
	//var_dump($EpisodeTotal);
	$total = $EpisodeTotal[0]['media_count'];
	$limit = 20;
	$start = (!empty($_GET['start'])? $_GET['start']:0);
	while( $start >= $total && $start > 0 )
		$start -= $limit;
	if( $start < 0 )
		$start = 0;
		
	?>
	<h2><?php echo __("Archive of PodPress Stats"); ?></h2>
	<p><?php echo sprintf(__('Displaying %d - %d of %d total'), $start+1, ($start+$limit<$total?$start+$limit:$total), $total); ?></p>
	<table class="widefat">
	<thead><tr>
		<th><?php echo __("File"); ?></th><th colspan="2"><?php echo __("Feed");?></th><th colspan="2"><?php echo __("Web");?></th>
		<th colspan="2"><?php echo __("Play");?></th><th><?php echo __("Total");?></th>
	</tr></thead>
	<tfoot><tr>
		<th><?php echo __("File"); ?></th><th colspan="2"><?php echo __("Feed");?></th><th colspan="2"><?php echo __("Web");?></th>
		<th colspan="2"><?php echo __("Play");?></th><th><?php echo __("Total");?></th>
	</tr></tfoot>
	<tbody>
	<?php
	
	
	$EpisodeCounts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}podpress_statcounts LIMIT $start, $limit", ARRAY_A);
	
	foreach ($EpisodeCounts as $Episode){
		$t = $Episode['total'];
	?>
	
		<tr>
			<td><?php echo $Episode['media']; ?></td>
			<td><?php echo $Episode['feed']; ?></td><td><?php echo " (".round(($Episode['feed']/$t)*100,1)."%)"; ?></td>
			<td><?php echo $Episode['web']; ?></td><td><?php echo " (".round(($Episode['web']/$t)*100,1)."%)"; ?></td>
			<td><?php echo $Episode['play']; ?></td><td><?php echo " (".round(($Episode['play']/$t)*100,1)."%)"; ?></td>
			<td><?php echo $Episode['total']; ?></td>
		</tr>
	<?php } ?>
	</tbody>
	</table>
	<div style="width: 100px; float: left;">
		<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_podpress-stats.php&amp;start=0"); ?>">first</a> |
		<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_podpress-stats.php&amp;start=") . ($start-$limit); ?>">prev</a>
	</div>
	<div style="width: 100px; float: right; text-align: right;">
		<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_podpress-stats.php&amp;start=") . ($start+$limit); ?>">next</a> |
		<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_podpress-stats.php&amp;start=") . ($total%$limit==0? $total - $limit : floor($total/$limit)*$limit ); ?>">last</a>
	</div>
	<div class="clear"></div>
<?php 
}
?>