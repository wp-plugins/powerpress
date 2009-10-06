<?php

function powerpress_admin_basic()
{
	$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'basic');
	
	// Default setings for advanced mode:
	if( $General['episode_box_mode'] != 2 )
	{
	/*
		$General['episode_box_embed'] = 0;
		$General['episode_box_no_player'] = 0;
		$General['episode_box_keywords'] = 0;
		$General['episode_box_subtitle'] = 0;
		$General['episode_box_summary'] = 0;
		*/
	}
	
	$FeedSettings = powerpress_get_settings('powerpress_feed');
	$FeedSettings = powerpress_default_settings($FeedSettings, 'editfeed');
		
		
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
function SelectEntryBox(mode)
{
	if( mode==2 )
		jQuery('.episode_box_option').removeAttr("disabled");
	else
		jQuery('.episode_box_option').attr("disabled","disabled");
}


	

</script>

<input type="hidden" name="action" value="powerpress-save-settings" />

<h2><?php echo __("Blubrry PowerPress Settings"); ?></h2>

<div id="powerpress_settings_page" class="powerpress_tabbed_content"> 
  <ul class="powerpress_settings_tabs"> 
		<li><a href="#tab1"><span>Basic Settings</span></a></li> 
		<li><a href="#tab2"><span>Services &amp; Statistics</span></a></li>
		<li><a href="#tab3"><span>Appearance</span></a></li>
		<li><a href="#tab4"><span>Feeds</span></a></li>
		<li><a href="#tab5"><span>iTunes</span></a></li>
  </ul>
	
  <div id="tab1" class="powerpress_tab">
		<?php
		powerpressadmin_edit_entry_options($General);
		powerpressadmin_edit_podpress_options($General);
		?>
	</div>
	
	<div id="tab2" class="powerpress_tab">
		<?php
		powerpressadmin_edit_blubrry_services($General);
		powerpressadmin_edit_media_statistics($General);
		?>
	</div>
	
	<div id="tab3" class="powerpress_tab">
		<?php
		powerpressadmin_appearance($General);
		?>
	</div>
	
	<div id="tab4" class="powerpress_tab">
		<?php
		powerpressadmin_edit_feed_general($FeedSettings, $General);
		powerpressadmin_edit_feed_settings($FeedSettings, $General);
		?>
	</div>
	
	<div id="tab5" class="powerpress_tab">
		<?php
		powerpressadmin_edit_itunes_general($General);
		powerpressadmin_edit_itunes_feed($FeedSettings, $General);
		?>
	</div>
	
</div>
<div class="clear"></div>
			
<div style="margin-left: 10px;">
	<h3>Advanced Options</h3>
	<div style="margin-left: 50px;">
		<div>
			<input type="checkbox" name="General[advanced_mode]" value="0" <?php echo ($General['advanced_mode']==0?' checked':''); ?>/> <strong>Simple Mode</strong> - 
			Display only the essential settings. Perfect for folks who may feel overwelmed.
		</div>
		<div>
			<input type="checkbox" name="General[player_options]" value="1" <?php echo ($General['player_options']?' checked':''); ?>/> <strong>Audio Player Options</strong> - 
			Select from 5 different web based audio flash players.
		</div>
		<div>
			<input type="checkbox" name="General[channels]" value="1" <?php echo ($General['channels']?' checked':''); ?>/> <strong>Podcast Channels</strong> - 
			Manage multiple media files and/or formats to one blog post.
		</div>
		<div>
			<input type="checkbox" name="General[cat_casting]" value="1" <?php echo ($General['cat_casting']?' checked':''); ?>/> <strong>Category Casting</strong> - 
			Manage category podcast feeds.
		</div>
	</div>
</div>

<?php
}

function powerpressadmin_edit_entry_options($General)
{
	$OpenSSLSupport = extension_loaded('openssl');
?>
<h3><?php echo __("Episode Entry Options"); ?></h3>

<table class="form-table">
<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row"><?php _e("Default Media URL"); ?></th> 
<td>
	<input type="text" style="width: 80%;" name="General[default_url]" value="<?php echo $General['default_url']; ?>" maxlength="250" />
	<p>e.g. http://example.com/mediafolder/</p>
	<p>URL above will prefix entered file names that do not start with 'http://'. URL above must end with a trailing slash.
	You may leave blank if you always enter the complete URL to your media when creating podcast episodes.
	</p>
</td>
</tr>
<?php
	}
?>
<tr valign="top">
<th scope="row">

<?php _e("Podcast Entry Box"); ?></th> 
<td>

	<ul>
		<li><label><input type="radio" name="General[episode_box_mode]" value="1" <?php if( $General['episode_box_mode'] == 1 ) echo 'checked'; ?> onclick="SelectEntryBox(1);" /> Simple</label></li>
		<li>
			<ul>
				<li>Episode entry box includes Media URL field only. File Size and Duration will be auto detected upon saving the post.</li>
			</ul>
		</li>
		
		<li><label><input type="radio" name="General[episode_box_mode]" value="0" <?php if( $General['episode_box_mode'] == 0 ) echo 'checked'; ?> onclick="SelectEntryBox(0);" /> Normal</label> (default)</li>
		<li>
			<ul>
				<li>Episode entry box includes Media URL, File Size and Duration fields.</li>
			</ul>
		</li>
		
				<li><label><input type="radio" name="General[episode_box_mode]" value="2" <?php if( $General['episode_box_mode'] == 2 ) echo 'checked'; ?> onclick="SelectEntryBox(2);" /> Custom</label></li>
		<li>
			<ul>
				<li>Episode entry box includes Media URL, File Size and Duration fields, plus:
				<div id="episode_box_mode_adv">
					<p style="margin-top: 15px;"><input id="episode_box_embed" class="episode_box_option" name="General[episode_box_embed]" type="checkbox" value="1"<?php if( $General['episode_box_embed'] ) echo ' checked'; ?> /> Embed Field
						(Enter embed code from sites such as YouTube, Viddler and Blip.tv)</p>
					<p style="margin-top: 15px;"><input id="episode_box_no_player" class="episode_box_option" name="General[episode_box_no_player]" type="checkbox" value="1"<?php if( $General['episode_box_no_player'] ) echo ' checked'; ?> /> No Player Option
						(Disable player on a per episode basis)</p>
					
					<p style="margin-top: 15px;"><input id="episode_box_keywords" class="episode_box_option" name="General[episode_box_keywords]" type="checkbox" value="1"<?php if( $General['episode_box_keywords'] ) echo ' checked'; ?> /> iTunes Keywords Field
						(Leave unchecked to use your blog post tags)</p>
					<p style="margin-top: 15px;"><input id="episode_box_subtitle" class="episode_box_option" name="General[episode_box_subtitle]" type="checkbox" value="1"<?php if( $General['episode_box_subtitle'] ) echo ' checked'; ?> /> iTunes Subtitle Field
						(Leave unchecked to use the first 250 characters of your blog post)</p>
					<p style="margin-top: 15px;"><input id="episode_box_summary" class="episode_box_option" name="General[episode_box_summary]" type="checkbox" value="1"<?php if( $General['episode_box_summary'] ) echo ' checked'; ?> /> iTunes Summary Field
						(Leave unchecked to use your blog post)</p>
					<p style="margin-top: 15px;"><input id="episode_box_explicit" class="episode_box_option" name="General[episode_box_explicit]" type="checkbox" value="1"<?php if( $General['episode_box_explicit'] ) echo ' checked'; ?> /> iTunes Explicit Field
						(Leave unchecked to use your feed's explicit setting)</p>	
					
					<em>NOTE: An invalid entry into any of the iTunes fields may cause problems with your iTunes listing.
					It is highly recommended that you validate your feed using feedvalidator.org everytime you modify any of the iTunes fields listed above.</em><br />
					<em><strong>USE THE ITUNES FIELDS ABOVE AT YOUR OWN RISK.</strong></em>
				</div>
				</li>
			</ul>
		</li>
	</ul>

</td>
</tr>
</table>
<script language="javascript">
SelectEntryBox(<?php echo $General['episode_box_mode']; ?>);
</script>

<?php
	if( @$General['advanced_mode'] )
	{
?>
<div id="episode_entry_settings" style="<?php if( $General['episode_box_mode'] == 1 ) echo 'display:none;'; ?>">
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
</div>

<table class="form-table">
<tr valign="top">
<th scope="row">
<?php _e("Auto Add Media"); ?></th> 
<td>
		<select name="General[auto_enclose]" class="bpp_input_med">
<?php
$options = array(0=>'Disabled (default)', 1=>'First media link found in post content', 2=>'Last media link found in post content');

while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['auto_enclose']==$value?' selected':''). ">$desc</option>\n";
	
?>
		</select>
		<p>When enabled, the first or last media link found in the post content is automatically added as your podcast episode.</p>
		<p style="margin-bottom: 0;"><em>NOTE: Use this feature with caution. Links to media files could unintentionally become podcast episodes.</em></p>
</td>
</tr>

</table>
<?php
	}
}

function powerpressadmin_edit_podpress_options($General)
{
	if( $General['process_podpress'] || powerpress_podpress_episodes_exist() )
	{
?>

<h3>PodPress Options</h3>
<table class="form-table">
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
	<?php if( @$General['podpress_stats'] || powerpress_podpress_stats_exist() ) { ?>
	<tr valign="top">
	<th scope="row">

	<?php _e("PodPress Stats Archive"); ?></th> 
	<td>
	<select name="General[podpress_stats]" class="bpp_input_sm">
	<?php
	$options = array(0=>'Hide', 1=>'Display');

	while( list($value,$desc) = each($options) )
		echo "\t<option value=\"$value\"". ($General['podpress_stats']==$value?' selected':''). ">$desc</option>\n";
		
	?>
	</select>  (display archive of old PodPress statistics)
	</td>
	</tr>
	<?php } ?>
	</table>
<?php
	}
}


function powerpressadmin_edit_itunes_general($General, $FeedSettings = false)
{
	$OpenSSLSupport = extension_loaded('openssl');
	if( $OpenSSLSupport == false )
	{
?>
<div class="error powerpress-error">Ping iTunes requires OpenSSL in PHP. Please refer to your php.ini to enable the php_openssl module.</div>
<?php } // End if !$OpenSSLSupport ?>

<h3>iTunes Listing Information</h3>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e("iTunes Subscription URL"); ?></th> 
<td>
<?php
	if( $FeedSettings ) {
?>
<input type="text" style="width: 80%;" name="Feed[itunes_url]" value="<?php echo $FeedSettings['itunes_url']; ?>" maxlength="250" />
<?php } else { ?>
<input type="text" style="width: 80%;" name="General[itunes_url]" value="<?php echo $General['itunes_url']; ?>" maxlength="250" />
<?php } ?>
<p>e.g. http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=000000000</p>

<p>Click the following link to <a href="https://phobos.apple.com/WebObjects/MZFinance.woa/wa/publishPodcast" target="_blank" title="Publish a Podcast on iTunes">Publish a Podcast on iTunes</a>.
iTunes will send an email to your <em>iTunes Email</em> entered below when your podcast is accepted into the iTunes Directory.
</p>

</td>
</tr>

<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row">

<?php _e("Update iTunes Listing"); ?></th> 
<td>
<?php
	if( $FeedSettings )
	{
?>
<select name="Feed[ping_itunes]"<?php if( $OpenSSLSupport == false ) echo ' disabled'; ?> class="bpp_input_sm">
<?php } else { ?>
<select name="General[ping_itunes]"<?php if( $OpenSSLSupport == false ) echo ' disabled'; ?> class="bpp_input_sm">
<?php
	}
$options = array(0=>'No ', 1=>'Yes ');

$ping_itunes = ($FeedSettings?$FeedSettings['ping_itunes']:$General['ping_itunes']);
if( $OpenSSLSupport == false )
	$value = 0;
	
while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($ping_itunes==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>  Notify (ping) iTunes when you publish a new episode.
<p><input name="TestiTunesPing" type="checkbox" value="1"<?php if( $OpenSSLSupport == false ) echo ' disabled'; ?> /> Test Update iTunes Listing (recommended)</p>
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
<p>You may also update your iTunes listing by using the following link: <a href="#" onclick="javascript: window.open('<?php echo $ping_url; ?>'); return false;" title="Ping iTunes in New Window">Ping iTunes in New Window</a></p>

<?php
		if( preg_match('/id=(\d+)/', $General['itunes_url'], $matches) )
		{
			$FEEDID = $matches[1];
			$Logging = get_option('powerpress_log');
			
			if( isset($Logging['itunes_ping_'. $FEEDID ]) )
			{
				$PingLog = $Logging['itunes_ping_'. $FEEDID ];
?>
		<h3>Latest Update iTunes Listing Status: <?php if( $PingLog['success'] ) echo '<span style="color: #006505;">Successful</span>'; else echo '<span style="color: #f00;">Error</span>';  ?></h3>
		<div style="font-size: 85%; margin-left: 20px;">
			<p>
				<?php echo sprintf( __('iTunes notified on %s at %s'), date(get_option('date_format'), $PingLog['timestamp']), date(get_option('time_format'), $PingLog['timestamp'])); ?>
<?php
					if( $PingLog['post_id'] )
					{
						$post = get_post($PingLog['post_id']);
						if( $post )
							echo __(' for post: ') . htmlspecialchars($post->post_title); 
					}
?>
			</p>
<?php if( $PingLog['success'] ) { ?>
			<p>Feed pulled by iTunes: <?php echo $PingLog['feed_url']; ?>
			</p>
			<?php
				
			?>
<?php } else { ?>
			<p>Error: <?php echo htmlspecialchars($PingLog['content']); ?></p>
<?php } ?>
		</div>
<?php
			}
		}
?>


<?php } ?>
</td>
</tr>
<?php
	} // end advanced_mode
?>

</table>
<?php
} // end itunes general

function powerpressadmin_edit_blubrry_services($General)
{
	
	$ModeDesc = 'None';
	if( $General['blubrry_auth'] )
		$ModeDesc = 'Media Statistics Only';
	if( $General['blubrry_hosting'] )
		$ModeDesc = 'Media Statistics and Hosting';
	$StatsInDashboard = true;
	if( isset($General['disable_dashboard_widget']) && $General['disable_dashboard_widget'] == 1 )
		$StatsInDashboard = false;
		
?>
<h3><?php _e("Blubrry Services Integration"); ?></h3>
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
		<p style="margin-top: 5px;"><span id="service_mode"><?php echo $ModeDesc; ?></span> (<strong><a href="<?php echo admin_url(); echo wp_nonce_url( "admin.php?action=powerpress-jquery-account", 'powerpress-jquery-account'); ?>&amp;KeepThis=true&amp;TB_iframe=true&amp;width=500&amp;height=400&amp;modal=true" target="_blank" class="thickbox" style="color: #3D517E;" title="Blubrry Services Integration">Click here to configure Blubrry Services</a></strong>)</p>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<?php _e("Dashboard Integration"); ?> 
	</th>
	<td>
		<p style="margin-top: 5px;"><input name="StatsInDashboard" type="checkbox" value="1"<?php if( $StatsInDashboard == true ) echo ' checked'; ?> /> Display Statistics in WordPress Dashboard</p>
	</td>
	</tr>
	
</table>
<?php
}

function powerpressadmin_edit_media_statistics($General)
{
?>
<h3><?php _e("Media Statistics"); ?></h3>
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
<?php
}
	
function powerpressadmin_appearance($General=false)
{
	if( $General === false )
		$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'appearance');
	
	$Players = array('podcast'=>'Default Podcast (podcast)');
	if( isset($General['custom_feeds']) )
	{
		while( list($podcast_slug, $podcast_title) = each($General['custom_feeds']) )
		{
			if( $podcast_slug == 'podcast' )
				continue;
			$Players[$podcast_slug] = sprintf('%s (%s)', $podcast_title, $podcast_slug);
		}
	}

 // <input type="hidden" name="action" value="powerpress-save-appearance" />
?>

<h3><?php echo __("Appearance Settings"); ?></h3>

<table class="form-table">

<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row"><?php echo __("Media Presentation"); ?></th> 
<td><select name="General[display_player]"  class="bpp_input_sm">
<?php
$displayoptions = array(1=>"Below Post", 2=>"Above Post", 0=>"None");

while( list($value,$desc) = each($displayoptions) )
	echo "\t<option value=\"$value\"". ($General['display_player']==$value?' selected':''). ">$desc</option>\n";

?>
</select> (where player and/or links will be displayed)
<p><input name="General[display_player_excerpt]" type="checkbox" value="1" <?php if($General['display_player_excerpt']) echo 'checked '; ?>/> Display player / links in <a href="http://codex.wordpress.org/Template_Tags/the_excerpt" title="Explanation of an excerpt in Wordpress" target="_blank">excerpts</a>  (e.g. search results)</p>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Display Media Player"); ?></th>
<td><select name="General[player_function]" class="bpp_input_med" onchange="javascript: jQuery('#new_window_settings').css('display', (this.value==1||this.value==3?'block':'none') );">
<?php
$playeroptions = array(1=>'On Page & New Window', 2=>'On Page Only', 3=>'New Window Only', /* 4=>'On Page Link', 5=>'On Page Link & New Window', */ 0=>'Disable');

while( list($value,$desc) = each($playeroptions) )
	echo "\t<option value=\"$value\"". ($General['player_function']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

?>
</select>
</td>
</tr>
</table>

<div id="new_window_settings" style="display: <?php echo ( $General['player_function']==1 || $General['player_function']==3 ?'block':'none'); ?>">
<table class="form-table">

<tr valign="top">
<th scope="row">
<?php echo __("New Window Width"); ?>
</th>
<td>
<input type="text" name="General[new_window_width]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['new_window_width']; ?>" maxlength="4" />
Width of new window (leave blank for 320 default)
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __("New Window Height"); ?>
</th>
<td>
<input type="text" name="General[new_window_height]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['new_window_height']; ?>" maxlength="4" />
Height of new window (leave blank for 240 default)
</td>
</tr>
</table>
</div>


<table class="form-table">

<tr valign="top">
<th scope="row">

<?php _e("Download Link"); ?></th> 
<td>
<select name="General[podcast_link]" class="bpp_input_med">
<?php
$linkoptions = array(1=>"Display", 2=>"Display with file size", 3=>"Display with file size and duration", 0=>"Disable");

while( list($value,$desc) = each($linkoptions) )
	echo "\t<option value=\"$value\"". ($General['podcast_link']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>
</td>
</tr>

<?php
		if( false && count($Players) > 1 )
		{
?>
<tr valign="top">
<th scope="row"><?php echo __("Disable Player for"); ?></th> 
<td>
	<input type="hidden" name="UpdateDisablePlayer" value="1" />
	<?php
			while( list($podcast_slug, $podcast_title) = each($Players) )
			{
	?>
	<p><input name="DisablePlayer[<?php echo $podcast_slug; ?>]" type="checkbox" value="1" <?php if( isset($General['disable_player'][$podcast_slug]) ) echo 'checked '; ?>/> <?php echo htmlspecialchars($podcast_title); ?> <?php echo __('feed episodes'); ?></p>
	<?php
			}
	?>
	<p>Check the custom podcast feeds above that you do not want in-page players for.</p>
</td>
</tr>
<?php
		}
	} // end advanced mode
?>


<tr valign="top">
<th scope="row" style="background-image: url(../wp-includes/images/smilies/icon_exclaim.gif); background-position: 10px 10px; background-repeat: no-repeat; ">

<div style="margin-left: 24px;"><?php _e("Having Theme Issues?"); ?></div></th>
<td>
	<select name="General[player_aggressive]" class="bpp_input_med">
<?php
$linkoptions = array(0=>"No, everything is working great", 1=>"Yes, please try to fix");

while( list($value,$desc) = each($linkoptions) )
	echo "\t<option value=\"$value\"". ($General['player_aggressive']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>
<p style="margin-top: 5px;">
	Use this option if you are having problems with the players not appearing in your pages.
</p>
</td>
</tr>
</table>



<h3><?php echo __("Video Player Settings"); ?></h3>

<table class="form-table">
<tr valign="top">
<th scope="row">
<?php echo __("Player Width"); ?>
</th>
<td>
<input type="text" name="General[player_width]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['player_width']; ?>" maxlength="4" />
Width of player (leave blank for 320 default)
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __("Player Height"); ?>
</th>
<td>
<input type="text" name="General[player_height]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['player_height']; ?>" maxlength="4" />
Height of player (leave blank for 240 default)
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("QuickTime Scale"); ?></th>
<td>
	<select name="General[player_scale]" class="bpp_input_sm" onchange="javascript:jQuery('#player_scale_custom').css('display', (this.value=='tofit'||this.value=='aspect'? 'none':'inline' ))">
<?php
	$scale_options = array('aspect'=>"Aspect (default)", 'tofit'=>"ToFit"); 
	if( !isset($General['player_scale']) )
		$General['player_scale'] = 'aspect';
	
	if( is_numeric($General['player_scale']) )
		$scale_options[ $General['player_scale'] ]='Custom';
	else
		$scale_options['custom']='Custom';



while( list($value,$desc) = each($scale_options) )
	echo "\t<option value=\"$value\"". ($General['player_scale']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>
<span id="player_scale_custom" style="display: <?php echo (is_numeric($General['player_scale'])?'inline':'none'); ?>">
	Scale: <input type="text" name="PlayerScaleCustom" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9.]/g, '');" value="<?php echo (is_numeric($General['player_scale'])?$General['player_scale']:''); ?>" maxlength="4" /> e.g. 1.5
</span>
<p style="margin-top: 5px;">
	If you do not see video, adjust the width, height and scale settings above.
</p>
</td>
</tr>

</table>

<h3>Audio Player Settings</h3>
<table class="form-table">
<tr valign="top">
<th scope="row">
<?php echo __("Default Player Width"); ?>
</th>
<td>
<input type="text" name="General[player_width_audio]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['player_width_audio']; ?>" maxlength="4" />
Width of Audio mp3 player (leave blank for 320 default)
</td>
</tr>
</table>

<?php  
} // End powerpress_admin_appearance()

?>