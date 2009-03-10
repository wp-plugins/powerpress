<?php

function powerpress_admin_basic()
{
	$General = powerpress_get_settings('powerpress_general');
	$AdvancedMode = $General['advanced_mode'];
?>
<h2><?php echo __("Basic Settings"); ?></h2>

<table class="form-table">


<tr valign="top">
<th scope="row">

<?php echo __("Mode"); ?></th> 
<td><input type="hidden" name="General[advanced_mode]" id="powerpress_advanced_mode" value="<?php echo $General['advanced_mode']; ?>" />
<?php
	if( $General['advanced_mode'] == 0 )
	{
?>
	<p style="margin-top: 5px;">Simple Mode (<strong><a href="#" onclick="return powerpress_changemode(1);">Switch to Advanced Mode</a></strong>)</p>
<?php
	}
	else
	{
?>
	<p style="margin-top: 5px;">Advanced Mode (<strong><a href="#" onclick="return powerpress_changemode(0);">Switch to Simple Mode</a></strong>)</p>
<?php
	}
?>
</td>
</tr>


<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row"><?php _e("Default Media URL"); ?></th> 
<td>
<input type="text" style="width: 80%;" name="General[default_url]" value="<?php echo $General['default_url']; ?>" maxlength="250" />
<p>URL above will prefix entered file names that do not start with 'http://'. URL above must end with a trailing slash.
You may leave blank if you always enter the complete URL to your media when creating podcast episodes.
</p>
<p>e.g. http://example.com/mediafolder/</p>
</td>
</tr>
<?php } ?>

<?php
	if( $General['process_podpress'] || powerpress_podpress_episodes_exist() )
	{
?>
<tr valign="top">
<th scope="row">

<?php _e("PodPress Episodes"); ?></th> 
<td>
<select name="General[process_podpress]" class="bpp_input_med">
<?php
$options = array(0=>'Ignore', 1=>'Include in Posts and Feeds');

while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['process_podpress']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>  (includes podcast episodes previously created in PodPress)
</td>
</tr>
<?php } ?>

<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row">

<?php _e("File Size Default"); ?></th> 
<td>
		<select name="General[set_size]" class="bpp_input_med">
<?php
$options = array(0=>'Auto detect file size', 1=>'User specify');

while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['set_size']==$value?' selected':''). ">$desc</option>\n";
	
?>
		</select> (specify default file size option when creating a new episode)
</td>
</tr>
	
	
<tr valign="top">
<th scope="row">

<?php _e("Duration Default"); ?></th> 
<td>
		<select name="General[set_duration]" class="bpp_input_med">
<?php
$options = array(0=>'Auto detect duration (mp3\'s only)', 1=>'User specify', -1=>'Not specified (not recommended)');

while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['set_duration']==$value?' selected':''). ">$desc</option>\n";
	
?>
		</select> (specify default duration option when creating a new episode)
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e("iTunes URL"); ?></th> 
<td>
<input type="text" style="width: 80%;" name="General[itunes_url]" value="<?php echo $General['itunes_url']; ?>" maxlength="250" />
<p>Click the following link to <a href="https://phobos.apple.com/WebObjects/MZFinance.woa/wa/publishPodcast" target="_blank" title="Publish a Podcast on iTunes">Publish a Podcast on iTunes</a>.
Once your podcast is listed on iTunes, enter your one click subscription URL above.
</p>
<p>e.g. http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=000000000</p>
</td>
</tr>

<?php
	$OpenSSLSupport = extension_loaded('openssl');
?>
<tr valign="top">
<th scope="row">

<?php _e("Ping iTunes"); ?></th> 
<td>
<select name="General[ping_itunes]"<?php if( $OpenSSLSupport == false ) echo ' disabled'; ?> class="bpp_input_sm">
<?php
$options = array(0=>'No ', 1=>'Yes ');

if( $OpenSSLSupport == false )
	$value = 0;
	
while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['ping_itunes']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>  (Notify iTunes when you publish a new episode.)
<p><input name="TestiTunesPing" type="checkbox" value="1"<?php if( $OpenSSLSupport == false ) echo ' disabled'; ?> /> Test iTunes Ping (recommended)</p>
<?php if( $General['itunes_url'] ) {

		$ping_url = str_replace(
			array(	'https://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'https://www.itunes.com/podcast?id=',
								'http://www.itunes.com/podcast?id='),
			'https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=', $General['itunes_url']);
?>
<p>You may also ping iTunes by using the following link: <a href="#" onclick="javascript: window.open('<?php echo $ping_url; ?>'); return false;" title="Ping iTunes in New Window">Ping iTunes in New Window</a></p>
<?php } ?>
</td>
</tr>
<?php } // End if $AdvancedMode ?>
</table>
<?php if( $OpenSSLSupport == false && $AdvancedMode ) { ?>
<div class="error powerpress-error">Ping iTunes requires OpenSSL in PHP. Please refer to your php.ini to enable the php_openssl module.</div>
<?php } // End if !$OpenSSLSupport ?>
<br />

<h2><?php _e("Media Statistics"); ?></h2>
<p style="margin-bottom: 0;">Configure 3rd party statistics services to measure your media. (optional)</p>

<table class="form-table">
<tr valign="top">
<th scope="row">
<?php _e("Redirect URL 1"); ?> 
</th>
<td>
<input type="text" style="width: 60%;" name="General[redirect1]" value="<?php echo $General['redirect1']; ?>" maxlength="250" />
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Redirect URL 2"); ?> 
</th>
<td>
<input type="text"  style="width: 60%;" name="General[redirect2]" value="<?php echo $General['redirect2']; ?>" maxlength="250" />
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Redirect URL 3"); ?> 
</th>
<td>
<input type="text" style="width: 60%;" name="General[redirect3]" value="<?php echo $General['redirect3']; ?>" maxlength="250" />
</td>
</tr>
</table>
<p>
	The services above must support redirects that do
	not include nested 'http://' within the URL. Statistics services such as
	<a href="http://www.podtrac.com" target="_blank" title="PodTrac">PodTrac.com</a>,
	<a href="http://www.blubrry.com/podcast_statistics/" target="_blank" title="Blubrry Statistics">Blubrry.com</a>,
	<a href="http://www.techpodcasts.com/podcast_statistics/" target="_blank" title="TechPodcasts Statistics">TechPodcasts.com</a>,
	<a href="http://www.rawvoice.com/products/statistics/" target="_blank" title="RawVoice Statistics">RawVoice.com</a>
	are supported.
</p>
<br />

<?php
}
?>