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
function ToggleID3Tags(Obj)
{
	document.getElementById('edit_id3_tags').style.display=(Obj.checked?'block':'none');
}
//-->
</script>
<input type="hidden" name="action" value="powerpress-save-search" />
<h2><?php echo __('Podcasting Search and SEO', 'powerpress'); ?></h2>

<p><?PHP echo __('Blubrry Hosting users can configure how to have the service write their MP3 ID3 Tags before publishing episodes.', 'powerpress'); ?></p>

<?php

?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __('Custom Feed Title', 'powerpress'); ?></th> 
<td>
	<p>
		<input name="PowerPressSearchToggle[seo_feed_title]" type="hidden" value="0" />
		<input name="PowerPressSearchToggle[seo_feed_title]" type="checkbox" value="1" <?php if( !empty($General['seo_feed_title']) ) echo 'checked '; ?> /> 
		<?php echo __('Unique title for syndication only.', 'powerpress'); ?>
	</p>
	<div style="margin-left: 40px;">
		<p><label style="display: block;"><input type="radio" name="General[seo_feed_title]" value="1" <?php if( $General['seo_feed_title'] == 1 ) echo 'checked'; ?> />
			<?php echo __('Feed title replaces post title', 'powerpress'); ?></label></p>
		<p><label style="display: block;"><input type="radio" name="General[seo_feed_title]" value="2" <?php if( $General['seo_feed_title'] == 2 ) echo 'checked'; ?> /> 
			<?php echo __('Feed title prefixes post title', 'powerpress'); ?></label></p>
		<p><label style="display: block;"><input type="radio" name="General[seo_feed_title]" value="3" <?php if( $General['seo_feed_title'] == 3 ) echo 'checked'; ?> /> 
			<?php echo __('Feed title appended to post title', 'powerpress'); ?></label></p>
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
<th scope="row"><?php echo __('iTunes Search Guidance', 'powerpress'); ?></th> 
<td>
	<p>
		<input name="General[seo_itunes_search]" type="hidden" value="0" />
		<input name="General[seo_itunes_search]" type="checkbox" value="1" <?php if( !empty($General['seo_itunes_search']) ) echo 'checked '; ?> /> 
		<?php echo __('Enable and highlight features that help with iTunes searching.', 'powerpress'); ?>
	</p>
	<p>
	<ul>
			<li>
		<ul>
			<li>
				Highlights fields to help with iTunes podcast search
			</li>
			<li>
				Enables iTunes subtitle field
			</li>
			<li>
				Enhanced iTunes summary automatically
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
} // End powerpress_admin_appearance()


function powerpressadmin_tag_option($tag, $value, $label, $default_desc )
{
	$file = false;
	$other = false;
	$track = false;
	switch( $tag )
	{
		case 'tag_title': {
			$other = false;
		}; break;
		case 'tag_track': {
			$track = true;
		}; break;
		case 'tag_coverart': {
			$other = false;
			$file = true;
		}; break;
		default: {
			$other = true;
		}
	}
?>
<tr valign="top">
<th scope="row">
<?php echo $label; ?>
</th>
<td>
<?php
	if( !$file )
	{
?>
<input type="radio" name="General[<?php echo $tag; ?>]" value="0" <?php if( $value == '' ) echo 'checked'; ?> />
<?php
		echo $default_desc;
	}
	
	if( $file )
	{
		$FeedSettings = get_option('powerpress_feed');
		$SupportUploads = false;
		$UploadArray = wp_upload_dir();
		if( false === $UploadArray['error'] )
		{
			$upload_path =  $UploadArray['basedir'].'/powerpress/';
			
			if( !file_exists($upload_path) )
				$SupportUploads = @wp_mkdir_p( rtrim($upload_path, '/') );
			else
				$SupportUploads = true;
		}
?>
<input type="radio" name="General[<?php echo $tag; ?>]" value="0" <?php if( $value == '' ) echo 'checked'; ?> />
<?php echo __('Do not add a coverart image.', 'powerpress'); ?><br />
<input type="radio" id="<?php echo $tag; ?>_specify" name="General[<?php echo $tag; ?>]" value="1" <?php if( $value != '' ) echo 'checked'; ?> />

<input type="text" id="coverart_image" name="TagValues[<?php echo $tag; ?>]" style="width: 50%;" value="<?php echo $value; ?>" maxlength="250" />
<a href="#" onclick="javascript: window.open( document.getElementById('coverart_image').value ); return false;"><?php echo __('preview', 'powerpress'); ?></a>

<p><?php echo __('Place the URL to the Coverart image above. e.g. http://mysite.com/images/coverart.jpg', 'powerpress'); ?></P>
<P><?php echo __('Coverart images may be saved as either .gif, .jpg or .png images of any size, though 300 x 300 or 600 x 600 in either png or jpg format is recommended.', 'powerpress'); ?>
</p>
<p>
<?php if( $FeedSettings['itunes_image'] ) { ?>
<a href="#" title="" onclick="document.getElementById('coverart_image').value='<?php echo $FeedSettings['itunes_image']; ?>';document.getElementById('tag_coverart_specify').checked=true;return false;"><?php echo __('Click here to use your current iTunes image.', 'powerpress'); ?></a>

<?php } ?>
</p>
<?php if( $SupportUploads ) { ?>
<p><input name="coverart_image_checkbox" type="checkbox" onchange="powerpress_show_field('coverart_image_upload', this.checked)" value="1" /> <?php echo __('Upload new image', 'powerpress'); ?> </p>
<div style="display:none" id="coverart_image_upload">
	<label for="coverart_image_file"><?php echo __('Choose file', 'powerpress'); ?>:</label> <input type="file" name="coverart_image_file" />
</div>
<?php } ?>

<?php
	}
	
	if( $track )
	{
		$PowerPressTrackNumber = get_option('powerpress_track_number');
?><br />
<input type="radio" name="General[<?php echo $tag; ?>]" value="1" <?php if( !empty($value) ) echo 'checked'; ?> /> <?php echo __('Specify', 'powerpress'); ?>: 
<input type="text" name="PowerPressTrackNumber" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo ( !empty($PowerPressTrackNumber) ?$PowerPressTrackNumber:'1'); ?>" maxlength="5" />
<?php
		echo __('(value entered increments every episode)', 'powerpress');
	}
	
	if( $other )
	{
?><br />
<input type="radio" name="General[<?php echo $tag; ?>]" value="1" <?php if( $value != '' ) echo 'checked'; ?> /> <?php echo __('Specify', 'powerpress'); ?>: 
<input type="text" name="TagValues[<?php echo $tag; ?>]" style="width: 300px" value="<?php echo htmlspecialchars($value); ?>" maxlength="250" />
<?php
	}
	
?>
</td>
</tr>
<?php
}

?>