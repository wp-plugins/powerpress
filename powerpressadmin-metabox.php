<?php
// powerpressadmin-metabox.php


function powerpress_meta_box($object, $box)
{
	$FeedSlug = str_replace('powerpress-', '', $box['id']);
	
	$DurationHH = '';
	$DurationMM = '';
	$DurationSS = '';
	$EnclosureURL = '';
	$EnclosureLength = '';
	$GeneralSettings = get_option('powerpress_general');
	if( !isset($GeneralSettings['set_size']) )
		$GeneralSettings['set_size'] = 0;
	if( !isset($GeneralSettings['set_duration']) )
		$GeneralSettings['set_duration'] = 0;
	
	if( $object->ID )
	{
		if( $FeedSlug == 'podcast' )
			$enclosureArray = get_post_meta($object->ID, 'enclosure', true);
		else
			$enclosureArray = get_post_meta($object->ID, '_'.$FeedSlug.':enclosure', true);
		
		list($EnclosureURL, $EnclosureLength, $EnclosureType, $EnclosureSerialized) =  explode("\n", $enclosureArray);
		if( $EnclosureSerialized )
		{
			$ExtraData = @unserialize($EnclosureSerialized);
			if( $ExtraData && isset($ExtraData['duration']) )
				$iTunesDuration = $ExtraData['duration'];
		}
		
		if( $FeedSlug == 'podcast' && !$iTunesDuration )
			$iTunesDuration = get_post_meta($object->ID, 'itunes:duration', true);
			
		if( $iTunesDuration )
		{
			$iTunesDuration = powerpress_readable_duration($iTunesDuration, true);
			list($DurationHH, $DurationMM, $DurationSS) = explode(':', $iTunesDuration);
			if( ltrim($DurationHH, '0') == 0 )
				$DurationHH = '';
			if( $DurationHH == '' && ltrim($DurationMM, '0') == 0 )
				$DurationMM = '';
			if( $DurationHH == '' && $DurationMM == '' && ltrim($DurationSS, '0') == 0 )
				$DurationSS = '';
		}
	}
	
	if( $EnclosureURL )
	{
?>
<div>
	<input type="checkbox" name="Powerpress[<?php echo $FeedSlug; ?>][change_podcast]" id="powerpress_change" value="1"  onchange="javascript:document.getElementById('powerpress_podcast_box_<?php echo $FeedSlug; ?>').style.display=(this.checked?'block':'none');" /> Modify existing podcast episode
</div>
<?php 
	}
	else
	{
		echo '<input type="hidden" name="Powerpress['. $FeedSlug .'][new_podcast]" value="1" />'.PHP_EOL;
	}
?>

<div class="powerpress_podcast_box" id="powerpress_podcast_box_<?php echo $FeedSlug; ?>"<?php if( $EnclosureURL ) echo ' style="display:none;"'; ?>>
<?php
	if( $EnclosureURL )
	{
?>
	<div class="powerpress_row">
		<label>Remove</label>
		<div class="powerpress_row_content">
			<input type="checkbox" name="Powerpress[<?php echo $FeedSlug; ?>][remove_podcast]" id="powerpress_remove" value="1"  onchange="javascript:document.getElementById('powerpress_podcast_edit_<?php echo $FeedSlug; ?>').style.display=(this.checked?'none':'block');" />
			Podcast episode will be removed from this post upon save
		</div>
	</div>
<?php
	}
?>
	<div id="powerpress_podcast_edit_<?php echo $FeedSlug; ?>">
		<div class="error below-h2" id="powerpress_warning_<?php echo $FeedSlug; ?>" style="display:none;">None</div>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][url]">Media URL</label>
			<div class="powerpress_row_content">
				<input id="powerpress_url_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][url]" value="<?php echo $EnclosureURL; ?>" onblur="powerpress_check_url(this.value,'powerpress_warning_<?php echo $FeedSlug; ?>')" style="width: 70%; font-size: 90%;" />
				<a href="<?php echo admin_url(); ?>?action=powerpress-jquery-media&podcast-feed=<?php echo $FeedSlug; ?>&KeepThis=true&TB_iframe=true" title="Select Media File" class="thickbox"><img src="<?php echo powerpress_get_root_url(); ?>/images/blubrry_folder.png" alt="Browse Media Files" /></a>
				<input type="hidden" id="powerpress_hosting_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][hosting]" value="0" />
			</div>
		</div>
<?php
	if( $GeneralSettings['episode_box_mode'] != 1 )
	{
?>
		<div class="powerpress_row">
			<label for "size">File Size</label>
			<div class="powerpress_row_content">
				<div style="margin-bottom: 4px;">
					<input id="powerpress_set_size_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_size]" value="0" type="radio" <?php echo ($GeneralSettings['set_size']==0?'checked':''); ?> /> Auto detect file size
				</div>
				<div>
					<input id="powerpress_set_size_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_size]" value="1" type="radio" <?php echo ($GeneralSettings['set_size']==1?'checked':''); ?> /> Specify: 
					<input id="powerpress_size_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][size]" value="<?php echo $EnclosureLength; ?>" style="width: 110px; font-size: 90%;" /> in bytes
				</div>
			</div>
		</div>
		<div class="powerpress_row">
			<label for "size">Duration</label>
			<div class="powerpress_row_content">
				<div style="margin-bottom: 4px;">
					<input id="powerpress_set_duration_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_duration]" value="0" type="radio" <?php echo ($GeneralSettings['set_duration']==0?'checked':''); ?> /> Auto detect duration (mp3's only)
				</div>
				<div style="margin-bottom: 4px;">
					<input id="powerpress_set_duration_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_duration]" value="1" type="radio" <?php echo ($GeneralSettings['set_duration']==1?'checked':''); ?> /> Specify: 
					<input id="powerpress_duration_hh" name="Powerpress[<?php echo $FeedSlug; ?>][duration_hh]" maxlength="2" value="<?php echo $DurationHH; ?>" style="width: 24px; font-size: 90%; text-align: right;" /><strong>:</strong> 
					<input id="powerpress_duration_mm" name="Powerpress[<?php echo $FeedSlug; ?>][duration_mm]" maxlength="2" value="<?php echo $DurationMM; ?>" style="width: 24px; font-size: 90%; text-align: right;" /><strong>:</strong> 
					<input id="powerpress_duration_ss" name="Powerpress[<?php echo $FeedSlug; ?>][duration_ss]" maxlength="10" value="<?php echo $DurationSS; ?>" style="width: 24px; font-size: 90%; text-align: right;" /> HH:MM:SS
				</div>
				<div>
					<input id="powerpress_set_duration" name="Powerpress[<?php echo $FeedSlug; ?>][set_duration]" value="-1" type="radio" <?php echo ($GeneralSettings['set_duration']==-1?'checked':''); ?> /> Not specified
				</div>
			</div>
		</div>
<?php
	}
	else
	{
?>
<input id="powerpress_set_size_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_size]" value="0" type="hidden" />
<input id="powerpress_set_duration_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_duration]" value="0" type="hidden" />
<?php
	}
?>
	</div>
</div>
<?php
}

?>