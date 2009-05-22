<?php
// powerpressadmin-tags.php

function powerpress_admin_tags()
{
	
		
	$General = powerpress_get_settings('powerpress_general');
	$TagSettings = powerpress_default_settings($General, 'tags');
?>
<script language="javascript">
function ToggleID3Tags(Obj)
{
	document.getElementById('edit_id3_tags').style.display=(Obj.checked?'block':'none');
}

</script>
<style type="text/css">
items {

}

.item-row {
.	border-bottom-style: solid;
	border-bottom-width: 1px;
	min-height: 36px;
	position: relative;
	width: 100%;
}
</style>
<input type="hidden" name="action" value="powerpress-save-tags" />
<h2><?php echo __("MP3 Tags"); ?></h2>

<p >Blubrry Hosting users can configure how to have the service write their MP3 ID3 Tags before publishing episodes.</p>

<p style="margin-bottomd: 0;">
		ID3 tags contain useful information (title, artist, album, year, etc...) about your podcast as well as an image for display during playback in most media players. 
		Please visit the <a href="http://www.podcastfaq.com/creating-podcast/audio/edit-id3-tags/" title="PodcastFAQ.com" target="_blank">ID3 Tags</a>
		section on <a href="http://www.podcastfaq.com/" title="PodcastFAQ.com" target="_blank">PodcastFAQ.com</a>
		to learn more about MP3 ID3 tags.
</p>
<?php
	if( !@$General['blubrry_hosting'] )
	{
?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __("Write Tags"); ?></th> 
<td>
	<p>
		<input name="NotAvailable" type="checkbox" value="1" onchange="alert('You must configure your Blubrry Services Account in the Blubrry PowerPress > Basic Settings page in order to utilize this feature.'); this.checked=false; return false;" /> 
		Use Blubrry Hosting services to write MP3 ID3 tags to your media.
	</p>
</td>
</tr>
</table>

<?php
	}
	else
	{
?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __("Write Tags"); ?></th> 
<td>
	<p>
		<input name="General[write_tags]" type="checkbox" value="1" <?php if($General['write_tags']) echo 'checked '; ?> onchange="ToggleID3Tags(this);" /> 
		Use Blubrry Hosting services to write MP3 ID3 tags to your media.
	</p>
</td>
</tr>
</table>
<?php } ?>
<table class="form-table" id="edit_id3_tags" style="display:<?php echo ($General['blubrry_hosting']?($General['write_tags']?'block':'none'):'block'); ?>;">

<?php
	
	powerpressadmin_tag_option('tag_title', $General['tag_title'], 'Title Tag', 'Use blog post title' );
	powerpressadmin_tag_option('tag_artist', $General['tag_artist'], 'Artist Tag', 'Use Feed Talent Name' );
	powerpressadmin_tag_option('tag_album', $General['tag_album'], 'Album Tag', 'Use blog title: '.  get_bloginfo('name') .'' );
	powerpressadmin_tag_option('tag_genre', $General['tag_genre'], 'Genre Tag', 'Use genre \'Podcast\'' );
	powerpressadmin_tag_option('tag_year', $General['tag_year'], 'Year Tag', 'Use year of blog post' );
	powerpressadmin_tag_option('tag_comment', $General['tag_comment'], 'Comment Tag', 'Use iTunes subtitle' );
	powerpressadmin_tag_option('tag_track', $General['tag_track'], 'Track Tag', 'Do not specify track number' );
	powerpressadmin_tag_option('tag_composer', $General['tag_composer'], 'Composer Tag', 'Use Feed Talent Name' );
	powerpressadmin_tag_option('tag_copyright', $General['tag_copyright'], 'Copyright Tag', 'Use &copy; Talent Name' );
	powerpressadmin_tag_option('tag_url_file', $General['tag_url_file'], 'URL Tag', 'Use main blog URL: '.  get_bloginfo('url') .'' );
	powerpressadmin_tag_option('tag_coverart', $General['tag_coverart'], 'Coverart Tag', '' );
	
?>

</table>
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
		case 'tag_url_filed': {
			$other = false;
			$file = true;
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
		$UploadArray = wp_upload_dir();
		$upload_path =  rtrim( substr($UploadArray['path'], 0, 0 - strlen($UploadArray['subdir']) ), '\\/').'/powerpress/';
		
		if( !file_exists($upload_path) )
			$SupportUploads = @mkdir($upload_path, 0777);
		else
			$SupportUploads = true;
?>
<input type="radio" name="General[<?php echo $tag; ?>]" value="0" <?php if( $value == '' ) echo 'checked'; ?> />
Do not add a coverart image.<br />
<input type="radio" id="<?php echo $tag; ?>_specify" name="General[<?php echo $tag; ?>]" value="1" <?php if( $value != '' ) echo 'checked'; ?> />

<input type="text" id="coverart_image" name="TagValues[<?php echo $tag; ?>]" style="width: 50%;" value="<?php echo $value; ?>" maxlength="250" />
<a href="#" onclick="javascript: window.open( document.getElementById('coverart_image').value ); return false;">preview</a>

<p>Place the URL to the Coverart image above. e.g. http://mysite.com/images/coverart.jpg<br /><br />Coverart images may be saved as either .gif, .jpg or .png images of any size, 
though 300 x 300 or 600 x 600 in either png or jpg format is recommended.
</p>
<p>
<?php if( $FeedSettings['itunes_image'] ) { ?>
<a href="#" title="" onclick="document.getElementById('coverart_image').value='<?php echo $FeedSettings['itunes_image']; ?>';document.getElementById('tag_coverart_specify').checked=true;return false;">Click here</a>
to use your current iTunes image.
<?php } ?>
</p>
<?php if( $SupportUploads ) { ?>
<p><input name="coverart_image_checkbox" type="checkbox" onchange="powerpress_show_field('coverart_image_upload', this.checked)" value="1" /> Upload new image </p>
<div style="display:none" id="coverart_image_upload">
	<label for="coverart_image_file">Choose file:</label> <input type="file" name="coverart_image_file" />
</div>
<?php } ?>

<?php
	}
	
	if( $track )
	{
?><br />
<input type="radio" name="General[<?php echo $tag; ?>]" value="1" <?php if( $value != '' ) echo 'checked'; ?> /> Specify: 
<input type="text" name="TagValues[<?php echo $tag; ?>]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo ($value?$value:'1'); ?>" maxlength="5" />
<?php
		echo __('(value entered increments every episode)');
	}
	
	if( $other )
	{
?><br />
<input type="radio" name="General[<?php echo $tag; ?>]" value="1" <?php if( $value != '' ) echo 'checked'; ?> /> Specify: 
<input type="text" name="TagValues[<?php echo $tag; ?>]" style="width: 300px" value="<?php echo htmlspecialchars($value); ?>" maxlength="250" />
<?php
	}
	
?>
</td>
</tr>
<?php



}

?>