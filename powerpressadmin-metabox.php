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
	$Embed = '';
	$iTunesKeywords = '';
	$iTunesSubtitle = '';
	$iTunesSummary = '';
	$iTunesExplicit = '';
	$NoPlayer = false;
	$GeneralSettings = get_option('powerpress_general');
	if( !isset($GeneralSettings['set_size']) )
		$GeneralSettings['set_size'] = 0;
	if( !isset($GeneralSettings['set_duration']) )
		$GeneralSettings['set_duration'] = 0;
	if( !isset($GeneralSettings['episode_box_embed']) )
		$GeneralSettings['episode_box_embed'] = 0;
	
	if( $object->ID )
	{
		
		if( $FeedSlug == 'podcast' )
			$enclosureArray = get_post_meta($object->ID, 'enclosure', true);
		else
			$enclosureArray = get_post_meta($object->ID, '_'.$FeedSlug.':enclosure', true);
		
		list($EnclosureURL, $EnclosureLength, $EnclosureType, $EnclosureSerialized) =  explode("\n", $enclosureArray, 4);
		if( $EnclosureSerialized )
		{
			$ExtraData = @unserialize($EnclosureSerialized);
			if( $ExtraData && isset($ExtraData['duration']) )
				$iTunesDuration = $ExtraData['duration'];
			if( $ExtraData && isset($ExtraData['embed']) )
				$Embed = $ExtraData['embed'];
			if( $ExtraData && isset($ExtraData['keywords']) )
				$iTunesKeywords = $ExtraData['keywords'];
			if( $ExtraData && isset($ExtraData['subtitle']) )
				$iTunesSubtitle = $ExtraData['subtitle'];
			if( $ExtraData && isset($ExtraData['summary']) )
				$iTunesSummary = $ExtraData['summary'];
			if( $ExtraData && isset($ExtraData['no_player']) )
				$NoPlayer = $ExtraData['no_player'];
			if( $ExtraData && isset($ExtraData['explicit']) )	
				$iTunesExplicit = $ExtraData['explicit'];
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
		<div class="error below-h2" id="powerpress_warning_<?php echo $FeedSlug; ?>" style="display:none;"></div>
		<div class="success below-h2" id="powerpress_success_<?php echo $FeedSlug; ?>" style="display:none;"></div>
		<div class="powerpress_row">
			<label for="Powerpress[<?php echo $FeedSlug; ?>][url]">Media URL</label>
			<div class="powerpress_row_content">
				<input id="powerpress_url_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][url]" value="<?php echo $EnclosureURL; ?>" onchange="powerpress_check_url(this.value,'powerpress_warning_<?php echo $FeedSlug; ?>')" style="width: 70%; font-size: 90%;" />
				<?php if( @$GeneralSettings['blubrry_hosting'] == 1 ) { ?>
				<a href="<?php echo admin_url(); ?>?action=powerpress-jquery-media&podcast-feed=<?php echo $FeedSlug; ?>&KeepThis=true&TB_iframe=true&modal=true" title="Select Media File" class="thickbox"><img src="<?php echo powerpress_get_root_url(); ?>/images/blubrry_folder.png" alt="Browse Media Files" /></a>
				<?php } ?>
				<input type="button" id="powerpress_check_<?php echo $FeedSlug; ?>_button" name="powerpress_check_<?php echo $FeedSlug; ?>_button" value="Verify" onclick="powerpress_get_media_info('<?php echo $FeedSlug; ?>');" alt="Verify Media" />
				<img id="powerpress_check_<?php echo $FeedSlug; ?>" src="<?php echo admin_url(); ?>images/loading.gif" style="vertical-align:text-top; display: none;" alt="Checking Media" />
				
				<input type="hidden" id="powerpress_hosting_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][hosting]" value="0" />
<?php

	if( $GeneralSettings['episode_box_mode'] == 2 && $GeneralSettings['episode_box_no_player'] )
	{
?>
	<div style="margin-left: 2px; padding-bottom: 2px; padding-top: 2px;"><input id="powerpress_no_player_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][no_player]" value="1" type="checkbox" <?php echo ($NoPlayer==1?'checked':''); ?> /> Do not display player for media above</div>
<?php
	}
?>
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
					<input id="powerpress_set_size_0_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_size]" value="0" type="radio" <?php echo ($GeneralSettings['set_size']==0?'checked':''); ?> /> Auto detect file size
				</div>
				<div>
					<input id="powerpress_set_size_1_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_size]" value="1" type="radio" <?php echo ($GeneralSettings['set_size']==1?'checked':''); ?> /> Specify: 
					<input id="powerpress_size_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][size]" value="<?php echo $EnclosureLength; ?>" style="width: 110px; font-size: 90%;" onchange="javascript:jQuery('#powerpress_set_size_1_<?php echo $FeedSlug; ?>').attr('checked', true);"  /> in bytes
				</div>
			</div>
		</div>
		<div class="powerpress_row">
			<label for "size">Duration</label>
			<div class="powerpress_row_content">
				<div style="margin-bottom: 4px;">
					<input id="powerpress_set_duration_0_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_duration]" value="0" type="radio" <?php echo ($GeneralSettings['set_duration']==0?'checked':''); ?> /> Auto detect duration (mp3's only)
				</div>
				<div style="margin-bottom: 4px;">
					<input id="powerpress_set_duration_1_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_duration]" value="1" type="radio" <?php echo ($GeneralSettings['set_duration']==1?'checked':''); ?> /> Specify: 
					<input id="powerpress_duration_hh_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][duration_hh]" maxlength="2" value="<?php echo $DurationHH; ?>" style="width: 24px; font-size: 90%; text-align: right;" onchange="javascript:jQuery('#powerpress_set_duration_1_<?php echo $FeedSlug; ?>').attr('checked', true);" /><strong>:</strong> 
					<input id="powerpress_duration_mm_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][duration_mm]" maxlength="2" value="<?php echo $DurationMM; ?>" style="width: 24px; font-size: 90%; text-align: right;" onchange="javascript:jQuery('#powerpress_set_duration_1_<?php echo $FeedSlug; ?>').attr('checked', true);" /><strong>:</strong> 
					<input id="powerpress_duration_ss_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][duration_ss]" maxlength="10" value="<?php echo $DurationSS; ?>" style="width: 24px; font-size: 90%; text-align: right;" onchange="javascript:jQuery('#powerpress_set_duration_1_<?php echo $FeedSlug; ?>').attr('checked', true);" /> HH:MM:SS
				</div>
				<div>
					<input id="powerpress_set_duration_2_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][set_duration]" value="-1" type="radio" <?php echo ($GeneralSettings['set_duration']==-1?'checked':''); ?> /> Not specified
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

	
	if( $GeneralSettings['episode_box_mode'] == 2 )
	{
		// Embed option, enter your own embed code provided by sites such as YouTube, Viddler and Blip.tv
		if( $GeneralSettings['episode_box_embed'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][embed]">Media Embed</label>
			<div class="powerpress_row_content">
				<textarea id="powerpress_embed_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][embed]" style="width: 90%; height: 80px; font-size: 90%;" onfocus="this.select();"><?php echo htmlspecialchars($Embed); ?></textarea>
			</div>
		</div>
<?php
		}
	
		if( $GeneralSettings['episode_box_keywords'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][keywords]">iTunes Keywords</label>
			<div class="powerpress_row_content">
				<input id="powerpress_keywords_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][keywords]" value="<?php echo htmlspecialchars($iTunesKeywords); ?>" style="width: 90%; font-size: 90%;" size="250" />
			</div>
			<div class="powerpress_row_content">
				<em>Enter up to 12 keywords separated by commas. Leave blank to use your blog post tags.</em>
			</div>
		</div>
<?php
		}
		
		if( $GeneralSettings['episode_box_subtitle'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][subtitle]">iTunes Subtitle</label>
			<div class="powerpress_row_content">
				<input id="powerpress_subtitle_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][subtitle]" value="<?php echo htmlspecialchars($iTunesSubtitle); ?>" style="width: 90%; font-size: 90%;" size="250" />
			</div>
			<div class="powerpress_row_content">
				<em>Your subtitle may not contain HTML and cannot exceed 250 characters in length.
				Leave blank to use the first 250 characters of your blog post.</em>
			</div>
		</div>
<?php
		}
		
		if( $GeneralSettings['episode_box_summary'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][summary]">iTunes Summary</label>
			<div class="powerpress_row_content">
				<textarea id="powerpress_summary_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][summary]" style="width: 90%; height: 80px; font-size: 90%;"><?php echo htmlspecialchars($iTunesSummary); ?></textarea>
			</div>	
			<div class="powerpress_row_content">
				<em>Your summary may not contain HTML and cannot exceed 4,000 characters in length.
				Leave blank to use your blog post.</em>
			</div>
		</div>
<?php
		}
		
		if( $GeneralSettings['episode_box_explicit'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][summary]">iTunes Explicit</label>
			<div class="powerpress_row_content">
				<select id="powerpress_explicit_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][explicit]" style="width: 200px;">
<?php
$explicit_array = array(''=>'Use feed\'s explicit setting', 0=>"no - display nothing", 1=>"yes - explicit content", 2=>"clean - no explicit content");

while( list($value,$desc) = each($explicit_array) )
	echo "\t<option value=\"$value\"". ($iTunesExplicit==$value?' selected':''). ">$desc</option>\n";

?>
					</select>
			</div>	
		</div>
<?php
		}
	}
?>
	</div>
</div>
<?php if( !empty($GeneralSettings['episode_box_background_color'][$FeedSlug]) ) { ?>
<script type="text/javascript">
jQuery('#powerpress-<?php echo $FeedSlug; ?>').css( {'background-color' : '<?php echo $GeneralSettings['episode_box_background_color'][$FeedSlug]; ?>' });
</script><?php } ?>
<?php
}

?>