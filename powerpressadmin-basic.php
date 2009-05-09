<?php

function powerpress_admin_basic()
{
	$General = powerpress_get_settings('powerpress_general');
	$AdvancedMode = $General['advanced_mode'];
	$General = powerpress_default_settings($General, 'basic');
?>
<script type="text/javascript">
function CheckRedirect(obj)
{
	if( obj.value )
	{
		if( obj.value.indexOf('rawvoice') == -1 && obj.value.indexOf('techpodcasts') == -1 && 
			obj.value.indexOf('blubrry') == -1 && obj.value.indexOf('podtrac') == -1 )
		{
			if( !confirm('The redirect entered is not recongized as a supported statistics redirect service.\n\nAre you sure you wish to continue with this redirect url?') )
			{
				obj.value = '';
				return false;
			}
		}
	}
	return true;
}
</script>
<input type="hidden" name="action" value="powerpress-save-basic" />
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
<th scope="row"><?php _e("iTunes URL"); ?></th> 
<td>
<input type="text" style="width: 80%;" name="General[itunes_url]" value="<?php echo $General['itunes_url']; ?>" maxlength="250" />
<p>Click the following link to <a href="https://phobos.apple.com/WebObjects/MZFinance.woa/wa/publishPodcast" target="_blank" title="Publish a Podcast on iTunes">Publish a Podcast on iTunes</a>.
Once your podcast is listed on iTunes, enter your one-click subscription URL above.
</p>
<p>e.g. http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=000000000</p>
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
								'https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
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


<h2><?php _e("Edit Post Settings"); ?></h2>
<p style="margin-bottom: 0;">Configure how the Podcast Episode entry boxes function when editing blog posts.</p>
<table class="form-table">
<tr valign="top">
<th scope="row">

<?php _e("Podcast Entry Box"); ?></th> 
<td>

	<ul>
		<li><label><input type="radio" name="General[episode_box_mode]" value="0" <?php if( $General['episode_box_mode'] == 0 ) echo 'checked'; ?> onclick="javascript:document.getElementById('episode_entry_settings').style.display='block';" /> Normal</label> (default)</li>
		<li>
			<ul>
				<li>Episode entry box includes Media URL, File Size and Duration fields.</li>
			</ul>
		</li>
		<li><label><input type="radio" name="General[episode_box_mode]" value="1" <?php if( $General['episode_box_mode'] == 1 ) echo 'checked'; ?> onclick="javascript:document.getElementById('episode_entry_settings').style.display='none';" /> Simple</label></li>
		<li>
			<ul>
				<li>Episode entry box includes Media URL field only. File Size and Duration will be auto detected.</li>
			</ul>
		</li>
	</ul>

</td>
</tr>
</table>
<div id="episode_entry_settings" style="<?php if( $General['episode_box_mode'] == 1 ) echo 'display:none;'; ?>">
<?php if( $AdvancedMode ) { ?>
<table class="form-table">
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
</table>
<br />
<?php
	}
?>
</div>

<?php
	
	$ModeDesc = 'None';
	if( $General['blubrry_auth'] )
		$ModeDesc = 'Media Statistics Only';
	if( $General['blubrry_hosting'] )
		$ModeDesc = 'Media Statistics and Hosting';
		
?>
<h2><?php _e("Blubrry Services Integration"); ?></h2>
<p>
	Adds <a href="http://www.blubrry.com/podcast_statistics/" title="Blubrry Media Statistics" target="_blank">Blubrry Media Statistics</a> to your blog's <a href="<?php echo admin_url(); ?>" title="WordPress Dashboard">dashboard</a> plus 
	features for <a href="https://secure.blubrry.com/podcast-publishing-premium-with-hosting/" title="Blubrry Media Hosting" target="_blank">Blubrry Media Hosting</a> users to quickly select and publish uploaded media.
</p>
<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php _e("Blubrry Services"); ?> 
	</th>
	<td>
		<p style="margin-top: 5px;"><span id="service_mode"><?php echo $ModeDesc; ?></span> (<strong><a href="<?php echo admin_url(); ?>?action=powerpress-jquery-account&KeepThis=true&TB_iframe=true&width=500&height=400&modal=true" target="_blank" class="thickbox" style="color: #3D517E;" title="Blubrry Services Integration">Click here to configure Blubrry Services</a></strong>)</p>
	</td>
	</tr>
</table>

<h2><?php _e("Media Statistics"); ?></h2>
<p>
Enter your Redirect URL issued by your media statistics service provider below.
</p>

<div style="position: relative;">
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php _e("Redirect URL 1"); ?> 
	</th>
	<td>
	<input type="text" style="width: 60%;" name="General[redirect1]" value="<?php echo $General['redirect1']; ?>" onChange="return CheckRedirect(this);" maxlength="250" /> 
	<!-- <div style="margin-left: 2px;margin-top: 0;">Enter your issued Blubrry Statistics Redirect URL above.</div> -->
	</td>
	</tr>
	</table>
	<?php if( $General['redirect2'] == '' && $General['redirect3'] == '' ) { ?>
	<div style="position: absolute;bottom: 0px;right: 10px;font-size: 85%;" id="powerpress_redirect2_showlink">
		<a href="javascript:void();" onclick="javascript:document.getElementById('powerpress_redirect2_table').style.display='block';document.getElementById('powerpress_redirect2_showlink').style.display='none';return false;">Add Another Redirect</a>
	</div>
<?php } ?>
</div>
	
<div id="powerpress_redirect2_table" style="position: relative;<?php if( $General['redirect2'] == '' && $General['redirect3'] == '' ) echo 'display:none;'; ?>">
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php _e("Redirect URL 2"); ?> 
	</th>
	<td>
	<input type="text"  style="width: 60%;" name="General[redirect2]" value="<?php echo $General['redirect2']; ?>" onblur="return CheckRedirect(this);" maxlength="250" />
	</td>
	</tr>
	</table>
	<?php if( $General['redirect3'] == '' ) { ?>
	<div style="position: absolute;bottom: 0px;right: 10px;font-size: 85%;" id="powerpress_redirect3_showlink">
		<a href="javascript:void();" onclick="javascript:document.getElementById('powerpress_redirect3_table').style.display='block';document.getElementById('powerpress_redirect3_showlink').style.display='none';return false;">Add Another Redirect</a>
	</div>
	<?php } ?>
</div>

<div id="powerpress_redirect3_table" style="<?php if( $General['redirect3'] == '' ) echo 'display:none;'; ?>">
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php _e("Redirect URL 3"); ?> 
	</th>
	<td>
	<input type="text" style="width: 60%;" name="General[redirect3]" value="<?php echo $General['redirect3']; ?>" onblur="return CheckRedirect(this);" maxlength="250" />
	</td>
	</tr>
	</table>
</div>
<style type="text/css">
#TB_window {
	border: solid 1px #3D517E;
}
</style>
<input type="hidden" id="hide_free_stats" name="General[hide_free_stats]" value="<?php echo $General['hide_free_stats']; ?>" />

<div id="blubrry_stats_box" style="<?php if( $General['hide_free_stats'] == 1 ) echo 'display:none;'; ?>">
	<div style="font-family: Arial, Helvetica, sans-serif; border: solid 1px #3D517E; background-color:#D2E9FF;padding:10px; margin-left:10px;margin-right:10px;margin-top:10px;">
		<div style="color: #3D517E; font-weight: bold; font-size: 18px;">Free Access to the Best Media Statistics!</div>
		<div style="font-size: 14px;margin-top: 10px; margin-bottom: 10px;">
			Get <span style="color: #990000; font-weight: bold;">Free</span> Media Statistics by taking a few minutes and adding your podcast to Blubrry.com. What's the catch? Nothing!
			For many, our free service is all you will need. But if you're looking to further your abilities with media download information, we hope you consider upgrading to our paid Premium Statistics service. 
		</div>
		<div style="text-align: center; font-size: 16px; font-weight: bold;"><a href="http://www.blubrry.com/addpodcast.php?feed=<?php echo urlencode(get_feed_link('podcast')); ?>" target="_blank" style="color: #3D517E;">Sign Up For Free Media Statistics Now</a></div>
	</div>
	<div style="font-size: 10px;margin-left: 10px;">
		<a href="javascript:void();" onclick="javascript:document.getElementById('blubrry_stats_box').style.display='none';document.getElementById('hide_free_stats').value=1;document.getElementById('show_free_stats').style.display='block';return false;">hide</a>
	</div>
</div>

<div id="show_free_stats" style="<?php if( $General['hide_free_stats'] != 1 ) echo 'display:none;'; ?>">
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	&nbsp;
	</th>
	<td>
	<p style="margin: 0;"><a href="javascript:void();" onclick="javascript:document.getElementById('blubrry_stats_box').style.display='block';document.getElementById('hide_free_stats').value=0;document.getElementById('show_free_stats').style.display='none';return false;">Learn About Free Blubrry Statistics</a></p>
	</td>
	</tr>
	</table>
</div>
	

<!--
<p>
	The services above must support redirects that do
	not include nested 'http://' within the URL. Statistics services such as
	<a href="http://www.podtrac.com" target="_blank" title="PodTrac">PodTrac.com</a>,
	<a href="http://www.blubrry.com/podcast_statistics/" target="_blank" title="Blubrry Statistics">Blubrry.com</a>,
	<a href="http://www.techpodcasts.com/podcast_statistics/" target="_blank" title="TechPodcasts Statistics">TechPodcasts.com</a>,
	<a href="http://www.rawvoice.com/products/statistics/" target="_blank" title="RawVoice Statistics">RawVoice.com</a>
	are supported.
</p>
-->


<?php
}
?>