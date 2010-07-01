<?php

function powerpress_admin_basic()
{
	$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'basic');
	
	// Default setings for advanced mode:
	if( @$General['episode_box_mode'] != 2 )
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
			if( !confirm('<?php echo __('The redirect entered is not recongized as a supported statistics redirect service.', 'powerpress'); ?>\n\n<?php echo __('Are you sure you wish to continue with this redirect url?', 'powerpress'); ?>') )
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

function SelectEmbedField(checked)
{
	if( checked )
		jQuery('#embed_replace_player').removeAttr("disabled");
	else
		jQuery('#embed_replace_player').attr("disabled","disabled");
}

jQuery(document).ready(function($) {
	
	jQuery('#episode_box_player_links_options').change(function () {
		
		if( jQuery(this).attr("checked") == true ) {
			jQuery('#episode_box_player_links_options_div').css("display", 'block' );
		}
		else {
			jQuery('#episode_box_player_links_options_div').css("display", 'none' );
			jQuery('.episode_box_no_player_or_links').attr("checked", false );
			jQuery('#episode_box_no_player_and_links').attr("checked", false );
		}
	} );
	
	jQuery('#episode_box_no_player_and_links').change(function () {
		
		if( jQuery(this).attr("checked") == true ) {
			jQuery('.episode_box_no_player_or_links').attr("checked", false );
		}
	} );

	jQuery('.episode_box_no_player_or_links').change(function () {
		if( jQuery(this).attr("checked") == true) {
			jQuery('#episode_box_no_player_and_links').attr("checked", false );
		}
	} );
} );

</script>

<input type="hidden" name="action" value="powerpress-save-settings" />

<h2><?php echo __('Blubrry PowerPress Settings', 'powerpress'); ?></h2>

<div id="powerpress_settings_page" class="powerpress_tabbed_content"> 
  <ul class="powerpress_settings_tabs"> 
		<li><a href="#tab1"><span><?php echo __('Basic Settings', 'powerpress'); ?></span></a></li> 
		<li><a href="#tab2"><span><?php echo htmlspecialchars(__('Services & Statistics', 'powerpress')); ?></span></a></li>
		<li><a href="#tab3"><span><?php echo __('Appearance', 'powerpress'); ?></span></a></li>
		<li><a href="#tab4"><span><?php echo __('Feeds', 'powerpress'); ?></span></a></li>
		<li><a href="#tab5"><span><?php echo __('iTunes', 'powerpress'); ?></span></a></li>
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

<?php
	$ChannelsCheckbox = '';
	if( !empty($General['custom_feeds']) )
		$ChannelsCheckbox = ' onclick="alert(\''.  __('You must delete all of the Podcast Channels to disable this option.', 'powerpress')  .'\');return false;"';
	$CategoryCheckbox = '';
	//if( !empty($General['custom_cat_feeds']) ) // Decided ont to include this warning because it may imply that you have to delete the actual category, which is not true.
	//	$CategoryCheckbox = ' onclick="alert(\'You must remove podcasting from the categories to disable this option.\');return false;"';
?>
<div style="margin-left: 10px;">
	<h3>Advanced Options</h3>
	<div style="margin-left: 50px;">
		<div>
			<input type="checkbox" name="General[advanced_mode]" value="1" <?php echo ($General['advanced_mode']==1?' checked':''); ?> /> 
			<strong><?php echo __('Advanced Mode', 'powerpress'); ?></strong> - 
			<?php echo __('Uncheck to display only the essential settings for podcasting.', 'powerpress'); ?>
		</div>
		<div>
			<input type="checkbox" name="General[player_options]" value="1" <?php echo ($General['player_options']?' checked':''); ?> /> 
			<strong><?php echo __('Audio Player Options', 'powerpress'); ?></strong> - 
			<?php echo __('Select from 5 different web based audio flash players.', 'powerpress'); ?> 
			<span style="font-size: 85%;">(<?php echo __('feature will appear in left menu when enabled', 'powerpress'); ?>)</span>
		</div>
		<div>
			<input type="checkbox" name="General[channels]" value="1" <?php echo ($General['channels']?' checked':''); echo $ChannelsCheckbox; ?> /> 
			<strong><?php echo __('Custom Podcast Channels', 'powerpress'); ?></strong> - 
			<?php echo __('Manage multiple media files and/or formats to one blog post.', 'powerpress'); ?> 
			<span style="font-size: 85%;">(<?php echo __('feature will appear in left menu when enabled', 'powerpress'); ?>)</span>
		</div>
		<div>
			<input type="checkbox" name="General[cat_casting]" value="1" <?php echo ($General['cat_casting']?' checked':'');  echo $CategoryCheckbox;  ?> /> 
			<strong><?php echo __('Category Podcasting', 'powerpress'); ?></strong> - 
			<?php echo __('Manage category podcast feeds.', 'powerpress'); ?> 
			<span style="font-size: 85%;">(<?php echo __('feature will appear in left menu when enabled', 'powerpress'); ?>)</span>
		</div>
	</div>
</div>

<?php
	if( $General['timestamp'] > 0 && $General['timestamp'] < ( time()- (60*60*24*14) ) ) // Lets wait 14 days before we annoy them asking for support
	{
?>
<div style="margin-left: 10px;">
	<h3 style="margin-bottom: 5px;"><?php echo __('Like The Plugin?', 'powerpress'); ?></h3>
	<p style="margin-top: 0;">
		<?php echo __('This plugin is great, don\'t you think? If you like the plugin we\'d be ever so grateful if you\'d give it your support. Here\'s how:', 'powerpress'); ?>
	</p>
	<ul id="powerpress_support">
		<li><?php echo sprintf(__('Rate this plugin 5 stars in the %s.', 'powerpress'), 
			'<a href="http://wordpress.org/extend/plugins/powerpress/" target="_blank">'. __('WordPress Plugins Directory', 'powerpress') .'</a>');
		
		?>
		</li>
		<li><?php echo __('Tell the world about PowerPress by writing about it on your blog', 'powerpress'); ?>, 
		<a href="http://twitter.com/home/?status=<?php echo urlencode( __('I\'m podcasting with Blubrry PowerPress (http://blubrry.com/powerpress/) #powerpress #wordpress', 'powerpress') ); ?>" target="_blank"><?php echo __('Twitter', 'powerpress'); ?></a>, 
		<a href="http://www.facebook.com/share.php?u=<?php echo urlencode('http://www.blubrry.com/powerpress/'); ?>&t=<?php echo urlencode( __('I podcast with Blubrry PowerPress', 'powerpress')); ?>" target="_blank"><?php echo __('Facebook', 'powerpress'); ?></a>,
		<a href="http://digg.com/submit?phase=2&url=<?php echo urlencode('http://www.blubrry.com/powerpress'); ?>&title=<?php echo urlencode( __('Blubrry PowerPress Podcasting Plugin for WordPress', 'powerpress') ); ?>" target="_blank"><?php echo __('Digg', 'powerpress'); ?></a>,
		etc...</li>
		<li><a href="http://www.blubrry.com/contact.php" target="_blank"><?php echo __('Send us feedback', 'powerpress'); ?></a> (<?php echo __('we love getting suggestions for new features!', 'powerpress'); ?>)</li>
	</ul>
</div>
<?php
	}
}

function powerpressadmin_edit_entry_options($General)
{
	if( !isset($General['advanced_mode']) )
		$General['advanced_mode'] = 0;
	if( !isset($General['default_url']) )
		$General['default_url'] = '';
	if( !isset($General['episode_box_mode']) )
		$General['episode_box_mode'] = 0;
	if( !isset($General['episode_box_embed']) )
		$General['episode_box_embed'] = 0;
	if( !isset($General['set_duration']) )
		$General['set_duration'] = 0;
	if( !isset($General['set_size']) )
		$General['set_size'] = 0;
	if( !isset($General['auto_enclose']) )
		$General['auto_enclose'] = 0;
?>
<h3><?php echo __('Episode Entry Options', 'powerpress'); ?></h3>

<table class="form-table">
<?php
	if( $General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row"><?php echo __('Default Media URL', 'powerpress'); ?></th> 
<td>
	<input type="text" style="width: 80%;" name="General[default_url]" value="<?php echo $General['default_url']; ?>" maxlength="250" />
	<p><?php echo __('e.g. http://example.com/mediafolder/', 'powerpress'); ?></p>
	<p><?php echo __('URL above will prefix entered file names that do not start with \'http://\'. URL above must end with a trailing slash. You may leave blank if you always enter the complete URL to your media when creating podcast episodes.', 'powerpress'); ?>
	</p>
</td>
</tr>
<?php
	}
?>
<tr valign="top">
<th scope="row">

<?php echo __('Podcast Entry Box', 'powerpress'); ?></th> 
<td>

	<ul>
		<li><label><input type="radio" name="General[episode_box_mode]" value="1" <?php if( $General['episode_box_mode'] == 1 ) echo 'checked'; ?> onclick="SelectEntryBox(1);" /> <?php echo __('Simple', 'powerpress'); ?></label></li>
		<li>
			<ul>
				<li><?php echo __('Episode entry box includes Media URL field only. File Size and Duration will be auto detected upon saving the post.', 'powerpress'); ?></li>
			</ul>
		</li>
		
		<li><label><input type="radio" name="General[episode_box_mode]" value="0" <?php if( $General['episode_box_mode'] == 0 ) echo 'checked'; ?> onclick="SelectEntryBox(0);" /> <?php echo __('Normal', 'powerpress'); ?></label> (<?php echo __('default', 'powerpress'); ?>)</li>
		<li>
			<ul>
				<li><?php echo __('Episode entry box includes Media URL, File Size and Duration fields.', 'powerpress'); ?></li>
			</ul>
		</li>
		
				<li><label><input type="radio" name="General[episode_box_mode]" value="2" <?php if( $General['episode_box_mode'] == 2 ) echo 'checked'; ?> onclick="SelectEntryBox(2);" /> <?php echo __('Custom', 'powerpress'); ?></label></li>
		<li>
			<ul>
				<li><?php echo __('Episode entry box includes Media URL, File Size and Duration fields, plus:', 'powerpress'); ?>
				<div id="episode_box_mode_adv">
					<p style="margin-top: 15px; margin-bottom: 0;"><input id="episode_box_embed" class="episode_box_option" name="General[episode_box_embed]" type="checkbox" value="1"<?php if( !empty($General['episode_box_embed']) ) echo ' checked'; ?> onclick="SelectEmbedField(this.checked);"  /> <?php echo __('Embed Field', 'powerpress'); ?>
						(<?php echo __('Enter embed code from sites such as YouTube, Viddler and Blip.tv', 'powerpress'); ?>)</p>
							<p style="margin-top: 5px; margin-left: 20px; font-size: 90%;"><input id="embed_replace_player" class="episode_box_option" name="General[embed_replace_player]" type="checkbox" value="1"<?php if( !empty($General['embed_replace_player']) ) echo ' checked'; ?> /> <?php echo __('Replace Player with Embed', 'powerpress'); ?>
								(<?php echo __('Do not display default player if embed present for episode.', 'powerpress'); ?>)</p>
					
					<p style="margin-top: 15px;"><input id="episode_box_player_links_options" class="episode_box_option" name="episode_box_player_links_options" type="checkbox" value="1"<?php if( !empty($General['episode_box_no_player_and_links']) || !empty($General['episode_box_no_player']) || !empty($General['episode_box_no_links']) ) echo ' checked'; ?> /> <?php echo __('Display Player and Links Options', 'powerpress'); ?>
					</p>
					<div id="episode_box_player_links_options_div" style="margin-left: 20px;<?php if( empty($General['episode_box_no_player_and_links']) && empty($General['episode_box_no_player']) && empty($General['episode_box_no_links']) ) echo 'display:none;'; ?>">
						
						<p style="margin-top: 0px; margin-bottom: 5px;"><input id="episode_box_no_player_and_links" class="episode_box_option" name="General[episode_box_no_player_and_links]" type="checkbox" value="1"<?php if( !empty($General['episode_box_no_player_and_links']) ) echo ' checked'; ?> /> <?php echo htmlspecialchars(__('No Player & Links Option', 'powerpress')); ?>
							(<?php echo __('Disable media player and links on a per episode basis', 'powerpress'); ?>)</p>
						
						<p style="margin-top: 0; margin-bottom: 0; margin-left: 20px;"><?php echo __('- or -', 'powerpress'); ?></p>
						
						<p style="margin-top: 5px;  margin-bottom: 10px;"><input id="episode_box_no_player" class="episode_box_option episode_box_no_player_or_links" name="General[episode_box_no_player]" type="checkbox" value="1"<?php if( !empty($General['episode_box_no_player']) ) echo ' checked'; ?> /> <?php echo __('No Player Option', 'powerpress'); ?>
							(<?php echo __('Disable media player on a per episode basis', 'powerpress'); ?>)</p>
						
						<p style="margin-top: 5px;  margin-bottom: 20px;"><input id="episode_box_no_links" class="episode_box_option episode_box_no_player_or_links" name="General[episode_box_no_links]" type="checkbox" value="1"<?php if( !empty($General['episode_box_no_links']) ) echo ' checked'; ?> /> <?php echo __('No Links Option', 'powerpress'); ?>
							(<?php echo __('Disable media links on a per episode basis', 'powerpress'); ?>)</p>
						
					</div>
				
					<p style="margin-top: 15px;"><input id="episode_box_cover_image" class="episode_box_option" name="General[episode_box_cover_image]" type="checkbox" value="1"<?php if( @$General['episode_box_cover_image'] ) echo ' checked'; ?> /> <?php echo __('Video Cover Image', 'powerpress'); ?>
						(<?php echo __('specify URL to image to display in place of QuickTime video', 'powerpress'); ?>)</p>
					
					<p style="margin-top: 15px;"><input id="episode_box_keywords" class="episode_box_option" name="General[episode_box_keywords]" type="checkbox" value="1"<?php if( !empty($General['episode_box_keywords']) ) echo ' checked'; ?> /> <?php echo __('iTunes Keywords Field', 'powerpress'); ?>
						(<?php echo __('Leave unchecked to use your blog post tags', 'powerpress'); ?>)</p>
					<p style="margin-top: 15px;"><input id="episode_box_subtitle" class="episode_box_option" name="General[episode_box_subtitle]" type="checkbox" value="1"<?php if( !empty($General['episode_box_subtitle']) ) echo ' checked'; ?> /> <?php echo __('iTunes Subtitle Field', 'powerpress'); ?>
						(<?php echo __('Leave unchecked to use the first 250 characters of your blog post', 'powerpress'); ?>)</p>
					<p style="margin-top: 15px;"><input id="episode_box_summary" class="episode_box_option" name="General[episode_box_summary]" type="checkbox" value="1"<?php if( !empty($General['episode_box_summary']) ) echo ' checked'; ?> /> <?php echo __('iTunes Summary Field', 'powerpress'); ?>
						(<?php echo __('Leave unchecked to use your blog post', 'powerpress'); ?>)</p>
					<p style="margin-top: 15px;"><input id="episode_box_author" class="episode_box_option" name="General[episode_box_author]" type="checkbox" value="1"<?php if( !empty($General['episode_box_author']) ) echo ' checked'; ?> /> <?php echo __('iTunes Author Field', 'powerpress'); ?>
						(<?php echo __('Leave unchecked to the post author name', 'powerpress'); ?>)</p>
					<p style="margin-top: 15px;"><input id="episode_box_explicit" class="episode_box_option" name="General[episode_box_explicit]" type="checkbox" value="1"<?php if( !empty($General['episode_box_explicit']) ) echo ' checked'; ?> /> <?php echo __('iTunes Explicit Field', 'powerpress'); ?>
						(<?php echo __('Leave unchecked to use your feed\'s explicit setting', 'powerpress'); ?>)</p>	
					
					<em><?php echo __('NOTE: An invalid entry into any of the iTunes fields may cause problems with your iTunes listing. It is highly recommended that you validate your feed using feedvalidator.org everytime you modify any of the iTunes fields listed above.', 'powerpress'); ?></em><br />
					<em><strong><?php echo __('USE THE ITUNES FIELDS ABOVE AT YOUR OWN RISK.', 'powerpress'); ?></strong></em>
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
SelectEmbedField(<?php echo $General['episode_box_embed']; ?>);
</script>

<?php
	if( $General['advanced_mode'] )
	{
?>
<div id="episode_entry_settings" style="<?php if( $General['episode_box_mode'] == 1 ) echo 'display:none;'; ?>">
<table class="form-table">
<tr valign="top">
<th scope="row">

<?php echo __('File Size Default', 'powerpress'); ?></th> 
<td>
		<select name="General[set_size]" class="bpp_input_med">
<?php
$options = array(0=>__('Auto detect file size', 'powerpress'), 1=>__('User specify', 'powerpress') );
	
while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['set_size']==$value?' selected':''). ">$desc</option>\n";
	
?>
		</select> (<?php echo __('specify default file size option when creating a new episode', 'powerpress'); ?>)
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __('Duration Default', 'powerpress'); ?></th> 
<td>
		<select name="General[set_duration]" class="bpp_input_med">
<?php
$options = array(0=>__('Auto detect duration (mp3\'s only)', 'powerpress'), 1=>__('User specify', 'powerpress'), -1=>__('Not specified (not recommended)', 'powerpress') );
	
while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['set_duration']==$value?' selected':''). ">$desc</option>\n";
	
?>
		</select> (<?php echo __('specify default duration option when creating a new episode', 'powerpress'); ?>)
</td>
</tr>
</table>
</div>

<table class="form-table">
<tr valign="top">
<th scope="row">
<?php echo __('Auto Add Media', 'powerpress'); ?></th> 
<td>
		<select name="General[auto_enclose]" class="bpp_input_med">
<?php
$options = array(0=>__('Disabled (default)', 'powerpress'), 1=>__('First media link found in post content', 'powerpress'), 2=>__('Last media link found in post content', 'powerpress') );
	
while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['auto_enclose']==$value?' selected':''). ">$desc</option>\n";
	
?>
		</select>
		<p><?php echo __('When enabled, the first or last media link found in the post content is automatically added as your podcast episode.', 'powerpress'); ?></p>
		<p style="margin-bottom: 0;"><em><?php echo __('NOTE: Use this feature with caution. Links to media files could unintentionally become podcast episodes.', 'powerpress'); ?></em></p>
		<p><em><?php echo __('WARNING: Episodes created with this feature will <u>not</u> include Duration (total play time) information.', 'powerpress'); ?></em></p>
</td>
</tr>
<?php
		global $wp_rewrite;
		if( $wp_rewrite->permalink_structure ) // Only display if permalinks is enabled in WordPress
		{
?>
<tr valign="top">
<th scope="row">
<?php echo __('Podcast Permalinks', 'powerpress'); ?></th> 
<td>
		<select name="General[permalink_feeds_only]" class="bpp_input_med">
<?php
$options = array(0=>__('Default WordPress Behavior', 'powerpress'), 1=>__('Match Feed Name to Page/Category', 'powerpress') );
	
while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['permalink_feeds_only']==$value?' selected':''). ">$desc</option>\n";
	
?>
		</select>
		<p><?php echo sprintf(__('When configured, %s/podcast/ is matched to page/category named \'podcast\'.', 'powerpress'), get_bloginfo('home') ); ?></p>
</td>
</tr>
<?php
		}
?>

</table>
<?php
	}
}

function powerpressadmin_edit_podpress_options($General)
{
	if( !empty($General['process_podpress']) || powerpress_podpress_episodes_exist() )
	{
		if( !isset($General['process_podpress']) )
			$General['process_podpress'] = 0;
		if( !isset($General['podpress_stats']) )	
			$General['podpress_stats'] = 0;
?>

<h3><?php echo __('PodPress Options', 'powerpress'); ?></h3>
<table class="form-table">
<tr valign="top">
<th scope="row">

<?php echo __('PodPress Episodes', 'powerpress'); ?></th> 
<td>
<select name="General[process_podpress]" class="bpp_input_med">
<?php
$options = array(0=>__('Ignore', 'powerpress'), 1=>__('Include in Posts and Feeds', 'powerpress') );

while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($General['process_podpress']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>  (<?php echo __('includes podcast episodes previously created in PodPress', 'powerpress'); ?>)
</td>
</tr>
	<?php if( @$General['podpress_stats'] || powerpress_podpress_stats_exist() ) { ?>
	<tr valign="top">
	<th scope="row">

	<?php echo __('PodPress Stats Archive', 'powerpress'); ?></th> 
	<td>
	<select name="General[podpress_stats]" class="bpp_input_sm">
	<?php
	$options = array(0=>__('Hide', 'powerpress'), 1=>__('Display', 'powerpress') );

	while( list($value,$desc) = each($options) )
		echo "\t<option value=\"$value\"". ($General['podpress_stats']==$value?' selected':''). ">$desc</option>\n";
		
	?>
	</select>  (<?php echo __('display archive of old PodPress statistics', 'powerpress'); ?>)
	</td>
	</tr>
	<?php } ?>
	</table>
<?php
	}
}


function powerpressadmin_edit_itunes_general($General, $FeedSettings = false, $feed_slug='podcast', $cat_ID=false)
{
	// Set default settings (if not set)
	if( $FeedSettings )
	{
		if( !isset($FeedSettings['ping_itunes']) )
			$FeedSettings['ping_itunes'] = 0;
		if( !isset($FeedSettings['itunes_url']) )
			$FeedSettings['itunes_url'] = '';
	}
	if( !isset($General['itunes_url']) )
		$General['itunes_url'] = '';
	if( !isset($General['ping_itunes']) )	
		$General['ping_itunes'] = 0;
		
	
	$OpenSSLSupport = extension_loaded('openssl');
	if( !$OpenSSLSupport && function_exists('curl_version') )
	{
		$curl_info = curl_version();
		$OpenSSLSupport = ($curl_info['features'] & CURL_VERSION_SSL );
	}
		
	if( $OpenSSLSupport == false )
	{
?>
<div class="error powerpress-error"><?php echo __('Ping iTunes requires OpenSSL in PHP. Please refer to your php.ini to enable the php_openssl module.', 'powerpress'); ?></div>
<?php } // End if !$OpenSSLSupport ?>

<h3><?php echo __('iTunes Listing Information', 'powerpress'); ?></h3>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __('iTunes Subscription URL', 'powerpress'); ?></th> 
<td>
<?php
	if( $FeedSettings ) {
?>
<input type="text" style="width: 80%;" name="Feed[itunes_url]" value="<?php echo $FeedSettings['itunes_url']; ?>" maxlength="250" />
<?php } else { ?>
<input type="text" style="width: 80%;" name="General[itunes_url]" value="<?php echo $General['itunes_url']; ?>" maxlength="250" />
<?php } ?>
<p>e.g. http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=000000000</p>

<p><?php echo sprintf( __('Click the following link to %s.', 'powerpress'), '<a href="https://phobos.apple.com/WebObjects/MZFinance.woa/wa/publishPodcast" target="_blank">'. __('Publish a Podcast on iTunes', 'powerpress') .'</a>'); ?>
<?php echo __('iTunes will send an email to your <em>iTunes Email</em> entered below when your podcast is accepted into the iTunes Directory.', 'powerpress'); ?>
</p>
<p>
<?php echo __('Recommended feed to submit to iTunes: ', 'powerpress'); ?>
<?php
	if( $cat_ID )
	{
		echo get_category_feed_link($cat_ID);
	}
	else
	{
		echo get_feed_link($feed_slug);
	}
?>
</p>

</td>
</tr>

<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row">

<?php echo __('Update iTunes Listing', 'powerpress'); ?></th> 
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
$options = array(0=>__('No', 'powerpress'), 1=>__('Yes', 'powerpress') );

$ping_itunes = ($FeedSettings?$FeedSettings['ping_itunes']:$General['ping_itunes']);
if( $OpenSSLSupport == false )
	$value = 0;
	
while( list($value,$desc) = each($options) )
	echo "\t<option value=\"$value\"". ($ping_itunes==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>  <?php echo __('Notify (ping) iTunes when you publish a new episode.', 'powerpress'); ?>
<p><input name="TestiTunesPing" type="checkbox" value="1"<?php if( $OpenSSLSupport == false ) echo ' disabled'; ?> /> <?php echo __('Test Update iTunes Listing (recommended)', 'powerpress'); ?></p>
<?php 
	$itunes_subscribe_url = ($FeedSettings?$FeedSettings['itunes_url']:$General['itunes_url']);
	if( !empty($itunes_subscribe_url) )
	{
		$ping_url = str_replace(
			array(	'https://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'https://www.itunes.com/podcast?id=',
								'http://www.itunes.com/podcast?id='),
			'https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=', $itunes_subscribe_url);
?>
<p><?php echo __('You may also update your iTunes listing by using the following link:', 'powerpress'); ?> <a href="#" onclick="javascript: window.open('<?php echo $ping_url; ?>'); return false;"><?php echo __('Ping iTunes in New Window', 'powerpress'); ?></a></p>

<?php
		if( preg_match('/id=(\d+)/', $itunes_subscribe_url, $matches) )
		{
			$FEEDID = $matches[1];
			$Logging = get_option('powerpress_log');
			
			if( isset($Logging['itunes_ping_'. $FEEDID ]) )
			{
				$PingLog = $Logging['itunes_ping_'. $FEEDID ];
?>
		<h3><?php echo __('Latest Update iTunes Listing Status:', 'powerpress'); ?> <?php if( $PingLog['success'] ) echo '<span style="color: #006505;">'. __('Successful', 'powerpress') .'</span>'; else echo '<span style="color: #f00;">'. __('Error', 'powerpress') .'</span>';  ?></h3>
		<div style="font-size: 85%; margin-left: 20px;">
			<p>
				<?php echo sprintf( __('iTunes notified on %s at %s', 'powerpress'), date(get_option('date_format'), $PingLog['timestamp']), date(get_option('time_format'), $PingLog['timestamp'])); ?>
<?php
					if( $PingLog['post_id'] )
					{
						$post = get_post($PingLog['post_id']);
						if( $post )
							echo ' '. __('for post:', 'powerpress') .' '. htmlspecialchars($post->post_title); 
					}
?>
			</p>
<?php if( $PingLog['success'] ) { ?>
			<p><?php echo __('Feed pulled by iTunes:', 'powerpress'); ?> <?php echo $PingLog['feed_url']; ?>
			</p>
			<?php
				
			?>
<?php } else { ?>
			<p><?php echo __('Error:', 'powerpress'); ?> <?php echo htmlspecialchars($PingLog['content']); ?></p>
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
	if( !empty($General['blubrry_auth']) )
		$ModeDesc = 'Media Statistics Only';
	if( !empty($General['blubrry_hosting']) )
		$ModeDesc = 'Media Statistics and Hosting';
	$StatsInDashboard = true;
	if( !empty($General['disable_dashboard_widget']) )
		$StatsInDashboard = false;
		
?>
<h3><?php echo __('Blubrry Services Integration', 'powerpress'); ?></h3>
<p>
	<?php echo sprintf(
		__('Adds %s to your blog\'s %s plus features for %s users to quickly upload and publish media directly from their blog.', 'powerpress'),
		'<a href="http://www.blubrry.com/podcast_statistics/" target="_blank">'. __('Blubrry Media Statistics', 'powerpress') .'</a>',
		'<a href="'. admin_url() .'">'. __('WordPress Dashboard', 'powerpress') .'</a>',
		'<a href="https://secure.blubrry.com/podcast-publishing-premium-with-hosting/" target="_blank">'. __('Blubrry Media Hosting', 'powerpress') .'</a>' );
	?>
</p>
<p>
	<em><?php echo __('Note: <b>No membership or service is required</b> to use this free open source podcasting plugin.', 'powerpress'); ?></em>
</p>
<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php echo __('Blubrry Services', 'powerpress'); ?>*
	</th>
	<td>
		<p style="margin-top: 5px;"><span id="service_mode"><?php echo $ModeDesc; ?></span> (<strong><a href="<?php echo admin_url(); echo wp_nonce_url( "admin.php?action=powerpress-jquery-account", 'powerpress-jquery-account'); ?>&amp;KeepThis=true&amp;TB_iframe=true&amp;width=500&amp;height=400&amp;modal=true" target="_blank" class="thickbox" style="color: #3D517E;"><?php echo __('Click here to configure Blubrry Services', 'powerpress'); ?></a></strong>)</p>
	</td>
	</tr>
	
	<tr valign="top">
	<th scope="row">
	<?php echo __('Dashboard Integration', 'powerpress'); ?> 
	</th>
	<td>
		<p style="margin-top: 5px;"><input name="StatsInDashboard" type="checkbox" value="1"<?php if( $StatsInDashboard == true ) echo ' checked'; ?> /> 
		<?php echo __('Display Statistics in WordPress Dashboard', 'powerpress'); ?></p>
	</td>
	</tr>
</table>
<p>
*<em>The Blubrry basic statistics service is FREE. Our 
<a href="https://secure.blubrry.com/podcast-statistics-premium/" target="_blank">Premium Statistics Service</a>,
which includes U.S. downloads, trending and exporting, is available for $5 month. Blubrry
<a href="https://secure.blubrry.com/podcast-publishing-premium-with-hosting/" target="_blank">Media Hosting</a>
packages start at $12.</em>
</p>
<?php
}

function powerpressadmin_edit_media_statistics($General)
{
	if( !isset($General['redirect1']) )
		$General['redirect1'] = '';
	if( !isset($General['redirect2']) )
		$General['redirect2'] = '';
	if( !isset($General['redirect3']) )
		$General['redirect3'] = '';
	if( !isset($General['hide_free_stats']) )
		$General['hide_free_stats'] = 0;
	
?>
<h3><?php echo __('Media Statistics', 'powerpress'); ?></h3>
<p>
<?php echo __('Enter your Redirect URL issued by your media statistics service provider below.', 'powerpress'); ?>
</p>

<div style="position: relative;">
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php echo __('Redirect URL 1', 'powerpress'); ?> 
	</th>
	<td>
	<input type="text" style="width: 60%;" name="General[redirect1]" value="<?php echo $General['redirect1']; ?>" onChange="return CheckRedirect(this);" maxlength="250" /> 
	</td>
	</tr>
	</table>
	<?php if( empty($General['redirect2']) && empty($General['redirect3']) ) { ?>
	<div style="position: absolute;bottom: 0px;right: 10px;font-size: 85%;" id="powerpress_redirect2_showlink">
		<a href="javascript:void();" onclick="javascript:document.getElementById('powerpress_redirect2_table').style.display='block';document.getElementById('powerpress_redirect2_showlink').style.display='none';return false;"><?php echo __('Add Another Redirect', 'powerpress'); ?></a>
	</div>
<?php } ?>
</div>
	
<div id="powerpress_redirect2_table" style="position: relative;<?php if( empty($General['redirect2']) && empty($General['redirect3']) ) echo 'display:none;'; ?>">
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php echo __('Redirect URL 2', 'powerpress'); ?> 
	</th>
	<td>
	<input type="text"  style="width: 60%;" name="General[redirect2]" value="<?php echo $General['redirect2']; ?>" onblur="return CheckRedirect(this);" maxlength="250" />
	</td>
	</tr>
	</table>
	<?php if( $General['redirect3'] == '' ) { ?>
	<div style="position: absolute;bottom: 0px;right: 10px;font-size: 85%;" id="powerpress_redirect3_showlink">
		<a href="javascript:void();" onclick="javascript:document.getElementById('powerpress_redirect3_table').style.display='block';document.getElementById('powerpress_redirect3_showlink').style.display='none';return false;"><?php echo __('Add Another Redirect', 'powerpress'); ?></a>
	</div>
	<?php } ?>
</div>

<div id="powerpress_redirect3_table" style="<?php if( empty($General['redirect3']) ) echo 'display:none;'; ?>">
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php echo __('Redirect URL 3', 'powerpress'); ?> 
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
<input type="hidden" id="hide_free_stats" name="General[hide_free_stats]" value="<?php echo (empty($General['hide_free_stats'])?0:1); ?>" />

<div id="blubrry_stats_box" style="<?php if( !empty($General['hide_free_stats']) ) echo 'display:none;'; ?>">
	<div style="font-family: Arial, Helvetica, sans-serif; border: solid 1px #3D517E; background-color:#D2E9FF;padding:10px; margin-left:10px;margin-right:10px;margin-top:10px;">
		<div style="color: #3D517E; font-weight: bold; font-size: 18px;"><?php echo __('Free Access to the Best Media Statistics!', 'powerpress'); ?></div>
		<div style="font-size: 14px;margin-top: 10px; margin-bottom: 10px;">
			<?php echo sprintf( __('Get %s Media Statistics by taking a few minutes and adding your podcast to Blubrry.com. What\'s the catch? Nothing! For many, our free service is all you will need. But if you\'re looking to further your abilities with media download information, we hope you consider upgrading to our paid Premium Statistics service. ', 'powerpress'),
				'<span style="color: #990000; font-weight: bold;">'. __('FREE', 'powerpress') .'</span>' );
			 ?>
		</div>
		<div style="text-align: center; font-size: 16px; font-weight: bold;"><a href="http://www.blubrry.com/addpodcast.php?feed=<?php echo urlencode(get_feed_link('podcast')); ?>" target="_blank" style="color: #3D517E;"><?php echo __('Sign Up For Free Media Statistics Now', 'powerpress'); ?></a></div>
	</div>
	<div style="font-size: 10px;margin-left: 10px;">
		<a href="javascript:void();" onclick="javascript:document.getElementById('blubrry_stats_box').style.display='none';document.getElementById('hide_free_stats').value=1;document.getElementById('show_free_stats').style.display='block';return false;"><?php echo __('hide','powerpress'); ?></a>
	</div>
</div>

<div id="show_free_stats" style="<?php if( $General['hide_free_stats'] != 1 ) echo 'display:none;'; ?>">
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	&nbsp;
	</th>
	<td>
	<p style="margin: 0;"><a href="javascript:void();" onclick="javascript:document.getElementById('blubrry_stats_box').style.display='block';document.getElementById('hide_free_stats').value=0;document.getElementById('show_free_stats').style.display='none';return false;"><?php echo __('Learn About Free Blubrry Statistics', 'powerpress'); ?></a></p>
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
	if( !isset($General['player_function']) )
		$General['player_function'] = 1;
	if( !isset($General['player_aggressive']) )
		$General['player_aggressive'] = 0;
	if( !isset($General['new_window_width']) )
		$General['new_window_width'] = '';
	if( !isset($General['new_window_height']) )
		$General['new_window_height'] = '';
	if( !isset($General['player_width']) )
		$General['player_width'] = '';
	if( !isset($General['player_height']) )
		$General['player_height'] = '';
	if( !isset($General['player_width_audio']) )
		$General['player_width_audio'] = '';	
		
	
	$Players = array('podcast'=>__('Default Podcast (podcast)', 'powerpress') );
	if( isset($General['custom_feeds']) )
	{
		while( list($podcast_slug, $podcast_title) = each($General['custom_feeds']) )
		{
			if( $podcast_slug == 'podcast' )
				continue;
			$Players[$podcast_slug] = sprintf('%s (%s)', $podcast_title, $podcast_slug);
		}
	}

?>

<h3><?php echo __('Appearance Settings', 'powerpress'); ?></h3>

<table class="form-table">

<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row"><?php echo __('Media Presentation', 'powerpress'); ?></th> 
<td><select name="General[display_player]"  class="bpp_input_sm">
<?php
$displayoptions = array(1=>__('Below Post', 'powerpress'), 2=>__('Above Post', 'powerpress'), 0=>__('None', 'powerpress') );

while( list($value,$desc) = each($displayoptions) )
	echo "\t<option value=\"$value\"". ($General['display_player']==$value?' selected':''). ">$desc</option>\n";

?>
</select> (<?php echo __('where media player and download links will be displayed', 'powerpress'); ?>)
<p><input name="General[display_player_excerpt]" type="checkbox" value="1" <?php if( !empty($General['display_player_excerpt']) ) echo 'checked '; ?>/> <?php echo __('Display media / links in:', 'powerpress'); ?> <a href="http://codex.wordpress.org/Template_Tags/the_excerpt" title="<?php echo __('WordPress Excerpts', 'powerpress'); ?>" target="_blank"><?php echo __('WordPress Excerpts', 'powerpress'); ?></a>  (<?php echo __('e.g. search results', 'powerpress'); ?>)</p>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __('PowerPress Shortcode', 'powerpress'); ?></th>
<td>
<p>
<?php echo sprintf(__('The %s shortcode is used to position your media presentation (player and download links) exactly where you want within your Post or Page.', 'powerpress'), '<code>[powerpress]</code>'); ?> 
<?php echo __('Simply insert the code on a new line in your content like this:', 'powerpress'); ?>
</p>
<div style="margin-left: 30px;">
	<code>[powerpress]</code>
</div>
<p>
<?php echo sprintf(__('Please visit the %s page for additional options.', 'powerpress'), '<a href="http://help.blubrry.com/blubrry-powerpress/shortcode/" target="_blank">'. __('PowerPress Shortcode', 'powerpress') .'</a>' ); ?>
</p>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __('Display Media Player', 'powerpress'); ?></th>
<td><select name="General[player_function]" class="bpp_input_med" onchange="javascript: jQuery('#new_window_settings').css('display', (this.value==1||this.value==3?'block':'none') );">
<?php
$playeroptions = array(1=>__('On Page & New Window', 'powerpress'), 2=>__('On Page Only', 'powerpress'), 3=>__('New Window Only', 'powerpress'), /* 4=>'On Page Link', 5=>'On Page Link & New Window', */ 0=>__('Disable', 'powerpress') );
			
while( list($value,$desc) = each($playeroptions) )
	echo "\t<option value=\"$value\"". ($General['player_function']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

?>
</select>
(<?php echo __('select where to display media flash player or embed code', 'powerpress'); ?>)
<p><input type="checkbox" name="General[display_player_disable_mobile]" value="1" <?php if( !empty($General['display_player_disable_mobile']) ) echo 'checked '; ?>/> <?php echo __('Disable Media Player for known mobile devices.', 'powerpress'); ?></p>
</td>
</tr>
</table>




<table class="form-table">

<tr valign="top">
<th scope="row">

<?php echo __('Download Link', 'powerpress'); ?></th> 
<td>
<select name="General[podcast_link]" class="bpp_input_med">
<?php
$linkoptions = array(1=>__('Display', 'powerpress'), 2=>__('Display with file size', 'powerpress'), 3=>__('Display with file size and duration', 'powerpress'), 0=>__('Disable', 'powerpress') );

while( list($value,$desc) = each($linkoptions) )
	echo "\t<option value=\"$value\"". ($General['podcast_link']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>
</td>
</tr>

<?php
	} // end advanced mode
?>


<tr valign="top">
<th scope="row" style="background-image: url(../wp-includes/images/smilies/icon_exclaim.gif); background-position: 10px 10px; background-repeat: no-repeat; ">

<div style="margin-left: 24px;"><?php echo __('Having Theme Issues?', 'powerpress'); ?></div></th>
<td>
	<select name="General[player_aggressive]" class="bpp_input_med">
<?php
$linkoptions = array(0=>__('No, everything is working', 'powerpress'), 1=>__('Yes, please try to fix', 'powerpress') );
	
while( list($value,$desc) = each($linkoptions) )
	echo "\t<option value=\"$value\"". ($General['player_aggressive']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>
<p style="margin-top: 5px; margin-bottom:0;">
	<?php echo __('Use this option if you are having problems with the players not appearing in your pages.', 'powerpress'); ?>
</p>
</td>
</tr>
</table>

<?php
	if( !empty($General['advanced_mode']) )
	{
?>
<div id="new_window_settings" style="display: <?php echo ( $General['player_function']==1 || $General['player_function']==3 ?'block':'none'); ?>">
<h3><?php echo __('Play in New Window Settings', 'powerpress'); ?></h3>
<table class="form-table">

<tr valign="top">
<th scope="row">
<?php echo __('New Window Width', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[new_window_width]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['new_window_width']; ?>" maxlength="4" />
<?php echo __('Width of new window (leave blank for 320 default)', 'powerpress'); ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __('New Window Height', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[new_window_height]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['new_window_height']; ?>" maxlength="4" />
<?php echo __('Height of new window (leave blank for 240 default)', 'powerpress'); ?>
</td>
</tr>
</table>
</div>
<?php
	}
?>

<h3><?php echo __('Video Player Settings', 'powerpress'); ?></h3>

<table class="form-table">
<tr valign="top">
<th scope="row">
<?php echo __('Player Width', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[player_width]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['player_width']; ?>" maxlength="4" />
<?php echo __('Width of player (leave blank for 320 default)', 'powerpress'); ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __('Player Height', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[player_height]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['player_height']; ?>" maxlength="4" />
<?php echo __('Height of player (leave blank for 240 default)', 'powerpress'); ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php echo __('QuickTime Scale', 'powerpress'); ?></th>
<td>
	<select name="General[player_scale]" class="bpp_input_sm" onchange="javascript:jQuery('#player_scale_custom').css('display', (this.value=='tofit'||this.value=='aspect'? 'none':'inline' ))">
<?php
	$scale_options = array('tofit'=>__('ToFit (default)', 'powerpress'), 'aspect'=>__('Aspect', 'powerpress') ); 
	if( !isset($General['player_scale']) )
		$General['player_scale'] = 'tofit'; // Tofit works in almost all cases
	
	if( is_numeric($General['player_scale']) )
		$scale_options[ $General['player_scale'] ]= __('Custom', 'powerpress');
	else
		$scale_options['custom']= __('Custom', 'powerpress');



while( list($value,$desc) = each($scale_options) )
	echo "\t<option value=\"$value\"". ($General['player_scale']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>
<span id="player_scale_custom" style="display: <?php echo (is_numeric($General['player_scale'])?'inline':'none'); ?>">
	<?php echo __('Scale:', 'powerpress'); ?> <input type="text" name="PlayerScaleCustom" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9.]/g, '');" value="<?php echo (is_numeric($General['player_scale'])?$General['player_scale']:''); ?>" maxlength="4" /> <?php echo __('e.g.', 'powerpress'); ?> 1.5
</span>
<p style="margin-top: 5px; margin-bottom: 0;">
	<?php echo __('If you do not see video, adjust the width, height and scale settings above.', 'powerpress'); ?>
</p>
</td>
</tr>

</table>

<h3><?php echo __('Audio Player Settings', 'powerpress'); ?></h3>
<table class="form-table">
<tr valign="top">
<th scope="row">
<?php echo __('Default Player Width', 'powerpress'); ?>
</th>
<td>
<input type="text" name="General[player_width_audio]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['player_width_audio']; ?>" maxlength="4" />
<?php echo __('Width of Audio mp3 player (leave blank for 320 default)', 'powerpress'); ?>
</td>
</tr>
</table>

<?php  
} // End powerpress_admin_appearance()

?>