<?php

function powerpress_admin_podpress_stats()
{
	?>
	<h2><?php echo __("PodPress Stats"); ?></h2>
	<table class="widefat">
	<thead><tr>
		<th><?php echo __("File"); ?></th><th colspan="2"><?php echo __("Feed");?></th><th colspan="2"><?php echo __("Web");?></th>
		<th colspan="2"><?php echo __("Play");?></th><th><?php echo __("Total");?></td>
	</tr></thead>
	<tfoot><tr>
		<th><?php echo __("File"); ?></th><th colspan="2"><?php echo __("Feed");?></th><th colspan="2"><?php echo __("Web");?></th>
		<th colspan="2"><?php echo __("Play");?></th><th><?php echo __("Total");?></td>
	</tr></tfoot>
	<tbody>
	<?php
	global $wpdb;
	$EpisodeCounts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}podpress_statcounts ", ARRAY_A);
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
<?php 
}
?>