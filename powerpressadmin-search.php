<?php
// powerpressadmin-search.php

function powerpress_admin_search()
{
	$General = powerpress_get_settings('powerpress_general');
	if( empty($General['seo_feed_title']) )
		$General['seo_feed_title'] = '';
		
	$TagSettings = powerpress_default_settings($General, 'search');
	
?>
<script language="javascript"><!--
//-->
</script>
<input type="hidden" name="action" value="powerpress-save-search" />
<h2><?php echo __('Podcasting SEO', 'powerpress'); ?></h2>

<p><?PHP echo __('Enable features to help with podcasting search engine optimization (SEO). The following options can assist your web and podcasting SEO strategies.', 'powerpress'); ?></p>


<?php

?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __('Episode Titles', 'powerpress'); ?></th> 
<td>
	<p>
		
		<label for="seo_feed_title">
		<input name="PowerPressSearchToggle[seo_feed_title]" type="hidden" value="0" />
		<input id="seo_feed_title" name="PowerPressSearchToggle[seo_feed_title]" type="checkbox" value="1" <?php if( !empty($General['seo_feed_title']) ) echo 'checked '; ?> /> 
		<?php echo __('Specify custom episode titles for podcast feeds.', 'powerpress'); ?></label>
	</p>
	<div style="margin-left: 40px;">
		<p><label style="display: block;"><input type="radio" name="General[seo_feed_title]" value="1" <?php if( $General['seo_feed_title'] == 1 ) echo 'checked'; ?> />
			<?php echo __('Feed episode title replaces post title (default)', 'powerpress'); ?></label></p>
		<p><label style="display: block;"><input type="radio" name="General[seo_feed_title]" value="2" <?php if( $General['seo_feed_title'] == 2 ) echo 'checked'; ?> /> 
			<?php echo __('Feed episode title prefixes post title', 'powerpress'); ?></label></p>
		<p><label style="display: block;"><input type="radio" name="General[seo_feed_title]" value="3" <?php if( $General['seo_feed_title'] == 3 ) echo 'checked'; ?> /> 
			<?php echo __('Feed episode title appended to post title', 'powerpress'); ?></label></p>
	</div>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php echo __('AudioObjects', 'powerpress'); ?></th> 
<td>
	<p>
		<input name="General[seo_audio_objects]" type="hidden" value="0" />
		<input name="General[seo_audio_objects]" type="checkbox" value="1" <?php if( !empty($General['seo_audio_objects']) ) echo 'checked '; ?> /> 
		<?php echo __('Schema.org audio objects in microdata format.', 'powerpress'); ?>
	</p>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php echo __('VideoObjects', 'powerpress'); ?></th> 
<td>
	<p>
		<input name="General[seo_video_objects]" type="hidden" value="0" />
		<input name="General[seo_video_objects]" type="checkbox" value="1" <?php if( !empty($General['seo_video_objects']) ) echo 'checked '; ?> /> 
		<?php echo __('Schema.org video objects in microdata format.', 'powerpress'); ?>
	</p>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php echo __('iTunes SEO Guidance', 'powerpress'); ?></th> 
<td>
	<p>
		<input name="General[seo_itunes]" type="hidden" value="0" />
		<input name="General[seo_itunes]" type="checkbox" value="1" <?php if( !empty($General['seo_itunes']) ) echo 'checked '; ?> /> 
		<?php echo __('Enable and highlight features that help with iTunes SEO.', 'powerpress'); ?>
	</p>
	<p>
	<ul>
			<li>
		<ul>
			<li>
				<?php echo __('Highlight fields for iTunes SEO', 'powerpress'); ?>
			</li>
			<li>
				<?php echo __('Enables iTunes Subtitle field', 'powerpress'); ?>
			</li>
			<li>
				<?php echo __('Enables Enhanced iTunes Summary feature', 'powerpress'); ?>
			</li>
		</ul>
			</li>
		</ul>
	</p>
</td>
</tr>
</table>

<?php

?>

<?php
} // End powerpress_admin_search()


?>