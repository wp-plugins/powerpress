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
	$CoverImage = '';
	$iTunesKeywords = '';
	$iTunesSubtitle = '';
	$iTunesSummary = '';
	$iTunesExplicit = '';
	$NoPlayer = false;
	$NoLinks = false;
	$GeneralSettings = get_option('powerpress_general');
	if( !isset($GeneralSettings['set_size']) )
		$GeneralSettings['set_size'] = 0;
	if( !isset($GeneralSettings['set_duration']) )
		$GeneralSettings['set_duration'] = 0;
	if( !isset($GeneralSettings['episode_box_embed']) )
		$GeneralSettings['episode_box_embed'] = 0;
	$ExtraData = array();
	
	if( $object->ID )
	{
		
		if( $FeedSlug == 'podcast' )
			$enclosureArray = get_post_meta($object->ID, 'enclosure', true);
		else
			$enclosureArray = get_post_meta($object->ID, '_'.$FeedSlug.':enclosure', true);
		
		$EnclosureURL = '';
		$EnclosureLength = '';
		$EnclosureType = '';
		$EnclosureSerialized = false;
		if( $enclosureArray )
			list($EnclosureURL, $EnclosureLength, $EnclosureType, $EnclosureSerialized) =  explode("\n", $enclosureArray, 4);
		$EnclosureURL = trim($EnclosureURL);
		$EnclosureLength = trim($EnclosureLength);
		$EnclosureType = trim($EnclosureType);
		
		if( $EnclosureSerialized )
		{
			$ExtraData = @unserialize($EnclosureSerialized);
			if( $ExtraData )
			{
				if( isset($ExtraData['duration']) )
					$iTunesDuration = $ExtraData['duration'];
				else if( isset($ExtraData['length']) ) // Podcasting plugin support
					$iTunesDuration = $ExtraData['length'];
				if( isset($ExtraData['embed']) )
					$Embed = $ExtraData['embed'];
				if( isset($ExtraData['keywords']) )
					$iTunesKeywords = $ExtraData['keywords'];
				if( isset($ExtraData['subtitle']) )
					$iTunesSubtitle = $ExtraData['subtitle'];
				if( isset($ExtraData['summary']) )
					$iTunesSummary = $ExtraData['summary'];
				if( isset($ExtraData['no_player']) )
					$NoPlayer = $ExtraData['no_player'];
				if( isset($ExtraData['no_links']) )
					$NoLinks = $ExtraData['no_links'];	
				if( isset($ExtraData['explicit']) )	
					$iTunesExplicit = $ExtraData['explicit'];
				if( isset($ExtraData['image']) )	
					$CoverImage = $ExtraData['image'];
			}
		}
		
		$iTunesDuration = false;
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
			<?php echo __('Podcast episode will be removed from this post upon save'); ?>
		</div>
	</div>
<?php
	}
?>
	<div id="powerpress_podcast_edit_<?php echo $FeedSlug; ?>">
		<div class="error below-h2" id="powerpress_warning_<?php echo $FeedSlug; ?>" style="display:none;"></div>
		<div class="success below-h2" id="powerpress_success_<?php echo $FeedSlug; ?>" style="display:none;"></div>
		<div class="powerpress_row">
			<label for="Powerpress[<?php echo $FeedSlug; ?>][url]"><?php echo __('Media URL'); ?></label>
			<div class="powerpress_row_content">
				<input id="powerpress_url_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][url]" value="<?php echo $EnclosureURL; ?>" onchange="powerpress_check_url(this.value,'powerpress_warning_<?php echo $FeedSlug; ?>')" <?php echo (@$ExtraData['hosting']==1?'readOnly':''); ?> style="width: 70%; font-size: 90%;" />
				<?php if( @$GeneralSettings['blubrry_hosting'] == 1 ) { ?>
				<a href="<?php echo admin_url(); ?>?action=powerpress-jquery-media&podcast-feed=<?php echo $FeedSlug; ?>&KeepThis=true&TB_iframe=true&modal=true" title="<?php echo __('Browse Media File'); ?>" class="thickbox"><img src="<?php echo powerpress_get_root_url(); ?>/images/blubrry_folder.png" alt="<?php echo __('Browse Media Files'); ?>" /></a>
				<?php } ?>
				<input type="button" id="powerpress_check_<?php echo $FeedSlug; ?>_button" name="powerpress_check_<?php echo $FeedSlug; ?>_button" value="<?php echo __('Verify'); ?>" onclick="powerpress_get_media_info('<?php echo $FeedSlug; ?>');" alt="<?php echo __('Verify Media'); ?>" />
				<img id="powerpress_check_<?php echo $FeedSlug; ?>" src="<?php echo admin_url(); ?>images/loading.gif" style="vertical-align:text-top; display: none;" alt="<?php echo __('Checking Media'); ?>" />
				
				<input type="hidden" id="powerpress_hosting_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][hosting]" value="<?php echo (@$ExtraData['hosting']==1?'1':'0'); ?>" />
				<div id="powerpress_hosting_note_<?php echo $FeedSlug; ?>" style="margin-left: 2px; padding-bottom: 2px; padding-top: 2px; display: <?php echo (@$ExtraData['hosting']==1?'block':'none'); ?>"><em><?php echo __('Media file hosted by blubrry.com.'); ?>
					(<a href="javascript:void();" title="Remove blubrry hosted media file" onclick="powerpress_remove_hosting('<?php echo $FeedSlug; ?>');return false;"><?php echo __('remove'); ?></a>)
				</em></div>
<?php

	if( $GeneralSettings['episode_box_mode'] == 2 && ( !empty($GeneralSettings['episode_box_no_player']) || !empty($GeneralSettings['episode_box_no_links']) || !empty($GeneralSettings['episode_box_no_player_and_links']) ) )
	{
?>
	<div style="margin-left: 2px; padding-bottom: 2px; padding-top: 2px;">
		<?php
		if( $GeneralSettings['episode_box_no_player_and_links'] )
		{
		?>
		<span><input id="powerpress_no_player_and_links_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][no_player_and_links]" value="1" type="checkbox" <?php echo ($NoPlayer==1&&$NoLinks==1?'checked':''); ?> /> <?php echo __('Do not display player and media links'); ?></span>
		<?php
		}
		if( $GeneralSettings['episode_box_no_player']  )
		{
		?>
		<span style="margin-right: 20px;"><input id="powerpress_no_player_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][no_player]" value="1" type="checkbox" <?php echo ($NoPlayer==1?'checked':''); ?> /> <?php echo __('Do not display player'); ?></span>
		<?php
		}
		if( @$GeneralSettings['episode_box_no_links']  )
		{
		?>
		<span><input id="powerpress_no_links_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][no_links]" value="1" type="checkbox" <?php echo ($NoLinks==1?'checked':''); ?> /> <?php echo __('Do not display media links'); ?></span>
		<?php
		}
		?>
	</div>
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
			<label><?php echo __('File Size'); ?></label>
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
			<label><?php echo __('Duration'); ?></label>
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
			<label for "Powerpress[<?php echo $FeedSlug; ?>][embed]"><?php echo __('Media Embed'); ?></label>
			<div class="powerpress_row_content">
				<textarea id="powerpress_embed_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][embed]" style="width: 90%; height: 80px; font-size: 90%;" onfocus="this.select();"><?php echo htmlspecialchars($Embed); ?></textarea>
			</div>
		</div>
<?php
		}
		
		if( @$GeneralSettings['episode_box_cover_image'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][image]"><?php echo __('Cover Image'); ?></label>
			<div class="powerpress_row_content">
				<input id="powerpress_image_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][image]" value="<?php echo htmlspecialchars($CoverImage); ?>" style="width: 90%; font-size: 90%;" size="250" />
			</div>
			<div class="powerpress_row_content">
				<em><?php echo __('Cover image for Quicktime (m4v, mov, etc..) video only. e.g. http://example.com/path/to/image.jpg'); ?></em>
			</div>
		</div>
<?php
		}
	
		if( $GeneralSettings['episode_box_keywords'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][keywords]"><?php echo __('iTunes Keywords'); ?></label>
			<div class="powerpress_row_content">
				<input id="powerpress_keywords_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][keywords]" value="<?php echo htmlspecialchars($iTunesKeywords); ?>" style="width: 90%; font-size: 90%;" size="250" />
			</div>
			<div class="powerpress_row_content">
				<em><?php echo __('Enter up to 12 keywords separated by commas. Leave blank to use your blog post tags.'); ?></em>
			</div>
		</div>
<?php
		}
		
		if( $GeneralSettings['episode_box_subtitle'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][subtitle]"><?php echo __('iTunes Subtitle'); ?></label>
			<div class="powerpress_row_content">
				<input id="powerpress_subtitle_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][subtitle]" value="<?php echo htmlspecialchars($iTunesSubtitle); ?>" style="width: 90%; font-size: 90%;" size="250" />
			</div>
			<div class="powerpress_row_content">
				<em><?php echo __('Your subtitle may not contain HTML and cannot exceed 250 characters in length. Leave blank to use the first 250 characters of your blog post.'); ?></em>
			</div>
		</div>
<?php
		}
		
		if( $GeneralSettings['episode_box_summary'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][summary]"><?php echo __('iTunes Summary'); ?></label>
			<div class="powerpress_row_content">
				<textarea id="powerpress_summary_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][summary]" style="width: 90%; height: 80px; font-size: 90%;"><?php echo htmlspecialchars($iTunesSummary); ?></textarea>
			</div>	
			<div class="powerpress_row_content">
				<em><?php echo __('Your summary may not contain HTML and cannot exceed 4,000 characters in length. Leave blank to use your blog post.'); ?></em>
			</div>
		</div>
<?php
		}
		
		if( $GeneralSettings['episode_box_explicit'] )
		{
?>
		<div class="powerpress_row">
			<label for "Powerpress[<?php echo $FeedSlug; ?>][summary]"><?php echo __('iTunes Explicit'); ?></label>
			<div class="powerpress_row_content">
				<select id="powerpress_explicit_<?php echo $FeedSlug; ?>" name="Powerpress[<?php echo $FeedSlug; ?>][explicit]" style="width: 200px;">
<?php
$explicit_array = array(''=>__('Use feed\'s explicit setting'), 0=>__('no - display nothing'), 1=>__('yes - explicit content'), 2=>__('clean - no explicit content') );

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