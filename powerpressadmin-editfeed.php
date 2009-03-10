<?php
// powerpressadmin_editfeed.php
function powerpress_admin_editfeed($feed_slug=false)
{
	$UploadArray = wp_upload_dir();
	$upload_path =  rtrim( substr($UploadArray['path'], 0, 0 - strlen($UploadArray['subdir']) ), '\\/').'/powerpress/';
	
	if( !file_exists($upload_path) )
		$SupportUploads = @mkdir($upload_path, 0777);
	else
		$SupportUploads = true;
		
	$General = powerpress_get_settings('powerpress_general');
	if( $feed_slug )
	{
		$FeedSettings = powerpress_get_settings('powerpress_feed_'.$feed_slug);
		if( !$FeedSettings )
		{
			$FeedSettings = array();
			$FeedSettings['title'] = $General['custom_feeds'][$feed_slug];
		}
	}
	else
	{
		$FeedSettings = powerpress_get_settings('powerpress_feed');
	}
		
	$FeedTitle = __('Feed Settings');
	if( $feed_slug )
	{
		$FeedTitle = sprintf( '%s: %s', $FeedTitle, $General['custom_feeds'][$feed_slug]) ;
		echo sprintf('<input type="hidden" name="feed_slug" value="%s" />', $feed_slug);
	}
	$AdvancedMode = $General['advanced_mode'];
?>
<h2><?php echo $FeedTitle; ?></h2>
<?php if( $feed_slug ) { ?>
<p style="margin-bottom: 0;">
	<?php _e('Configure your custom podcast feed.'); ?>
</p>
<?php } else { ?>
<p style="margin-bottom: 0;">
	<?php _e('Configure your feeds to support podcasting.'); ?>
</p>
<?php } ?>
<table class="form-table">
<?php if( !$feed_slug ) { ?>
<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row">

<?php echo __('Enhance Feeds'); ?></th> 
<td>
	<ul>
		<li><label><input type="radio" name="Feed[apply_to]" value="1" <?php if( $FeedSettings['apply_to'] == 1 ) echo 'checked'; ?> /> Enhance All Feeds</label> (Recommended)</li>
		<li>
			<ul>
				<li>Adds podcasting support to all feeds</li>
				<li>Allows for Category Casting (Visitors may subscribe to your categories as a podcast)</li>
				<li>Allows for Tag/Keyword Casting (Visitors may subscribe to your tags as a podcast)</li>
			</ul>
		</li>
		<li><label><input type="radio" name="Feed[apply_to]" value="2" <?php if( $FeedSettings['apply_to'] == 2 ) echo 'checked'; ?> /> Enhance Main Feed Only</label></li>
		<li>
			<ul>
				<li>Adds podcasting support to your main feed only</li>
			</ul>
		</li>
		<li><label><input type="radio" name="Feed[apply_to]" value="0" <?php if( $FeedSettings['apply_to'] == 0 ) echo 'checked'; ?> /> Do Not Enhance Feeds</label></li>
		<li>
			<ul>
				<li>Feed Settings below will only apply to your podcast only feeds</li>
			</ul>
		</li>
	</ul>
		
<?php /* ?>
<select name="Feed[apply_to]" class="bpp_input_large"  style="width: 60%;">
<?php
$applyoptions = array(1=>'All RSS2 Feeds (category / tag specific podcast feeds)', 2=>'Main RSS2 Feed only', 0=>'Disable (settings below ignored)');

while( list($value,$desc) = each($applyoptions) )
	echo "\t<option value=\"$value\"". ($FeedSettings['apply_to']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>
<p>Select 'All RSS Feeds' to include podcast episodes in all feeds such as category and tag feeds.</p>
<p>Select 'Main RSS2 Feed only' to include podcast episodes only in your primary RSS2 feed.</p>
<p>Select 'Disable' to prevent Blubrry Powerpress from adding podcast episodes to any feeds.</p>
<?php */ ?>
</td>
</tr>
<?php } // End AdvancedMode ?>

<tr valign="top">
<th scope="row">

<?php _e("Important Feeds"); ?></th> 
<td>
<p style="margin-top: 5px;">Main RSS2 Feed: <a href="<?php echo get_bloginfo('rss2_url'); ?>" title="Main RSS 2 Feed" target="_blank"><?php echo get_bloginfo('rss2_url'); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_bloginfo('rss2_url')); ?>" title="Validate Feed" target="_blank">validate</a></p>
<p>Special Podcast only Feed: <a href="<?php echo get_feed_link('podcast'); ?>" title="Podcast Feed" target="_blank"><?php echo get_feed_link('podcast'); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_feed_link('podcast')); ?>" title="Validate Feed" target="_blank">validate</a></p>

</td>
</tr>
<?php } else { // Else if( $feed_slug)  ?>

<tr valign="top">
<th scope="row">
<?php _e("Feed URL"); ?> <br />
</th>
<td>
<p style="margin-top: 0;"><a href="<?php echo get_feed_link($feed_slug); ?>" target="_blank"><?php echo get_feed_link($feed_slug); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_feed_link($feed_slug)); ?>" target="_blank"><?php _e('validate'); ?></a></p>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Feed Title"); ?>
</th>
<td>
<input type="text" name="Feed[title]"style="width: 60%;"  value="<?php echo $FeedSettings['title']; ?>" maxlength="250" /> (leave blank to use blog title)
</td>
</tr>
<tr valign="top">
<th scope="row">
<?php _e("Feed Description"); ?>
</th>
<td>
<input type="text" name="Feed[description]"style="width: 60%;"  value="<?php echo $FeedSettings['description']; ?>" maxlength="1000" />  (leave blank to use blog description)
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Feed Landing Page"); ?> <br />
</th>
<td>
<input type="text" name="Feed[url]"style="width: 60%;"  value="<?php echo $FeedSettings['url']; ?>" maxlength="250" />  (optional)
<p>e.g. <?php echo get_bloginfo('home'); ?>/custom-page/</p>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Show the most recent"); ?>
</th>
<td>
<input type="text" name="Feed[posts_per_rss]"style="width: 50px;"  value="<?php echo $FeedSettings['posts_per_rss']; ?>" maxlength="5" />  episodes (leave blank to use blog default: <?php form_option('posts_per_rss'); ?>)
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Redirect Feed URL"); ?>
</th>
<td>
<input type="text" name="Feed[feed_redirect_url]"style="width: 60%;"  value="<?php echo $FeedSettings['feed_redirect_url']; ?>" maxlength="100" />  (leave blank to use current feed)
<p>Use this option to redirect this feed to a hosted feed service such as <a href="http://www.feedburner.com/" target="_blank">FeedBurner</a>.</p>
<?php 
$link = get_feed_link($feed_slug);
if( strstr($link, '?') )
	$link .= "&redirect=no";
else
	$link .= "?redirect=no";
?>
<p>Bypass Redirect URL: <a href="<?php echo $link; ?>" target="_blank"><?php echo $link; ?></a></p>
</td>
</tr>

<?php } // End $feed_slug ?>

<?php if( $AdvancedMode ) { ?>
	<tr valign="top">
	<th scope="row" >

<?php _e("iTunes New Feed URL"); ?></th> 
	<td>
		<div id="new_feed_url_step_1" style="display: <?php echo ($FeedSettings['itunes_new_feed_url'] || $FeedSettings['itunes_new_feed_url_podcast']  ?'none':'block'); ?>;">
			 <p style="margin-top: 5px;"><a href="#" onclick="return powerpress_new_feed_url_prompt();">Click here</a> if you need to change the Feed URL for iTunes subscribers.</p>
		</div>
		<div id="new_feed_url_step_2" style="display: <?php echo ($FeedSettings['itunes_new_feed_url'] || $FeedSettings['itunes_new_feed_url_podcast']  ?'block':'none'); ?>;">
			<p style="margin-top: 5px;"><strong>WARNING: Changes made here are permanent. If the New Feed URL entered is incorrect, you will lose subscribers and will no longer be able to update your listing in the iTunes Store.</strong></p>
			<p><strong>DO NOT MODIFY THIS SETTING UNLESS YOU ABSOLUTELY KNOW WHAT YOU ARE DOING.</strong></p>
			<p>
				Apple recommends you maintain the &lt;itunes:new-feed-url&gt; tag in your feed for at least two weeks to ensure that most subscribers will receive the new New Feed URL.
			</p>
			<p>
				Example URL: <?php echo get_feed_link( ($feed_slug?$feed_slug:'podcast') ); ?>
			</p>
			<p style="margin-bottom: 0;">
				<label style="width: 25%; float:left; display:block; font-weight: bold;">New Feed URL</label>
				<input type="text" name="Feed[itunes_new_feed_url]"style="width: 55%;"  value="<?php echo $FeedSettings['itunes_new_feed_url']; ?>" maxlength="250" />
			</p>
			<p style="margin-left: 25%;margin-top: 0;font-size: 90%;">(Leave blank for no New Feed URL)</p>
			<p>More information regarding the iTunes New Feed URL is available <a href="http://www.apple.com/itunes/whatson/podcasts/specs.html#changing" target="_blank" title="Apple iTunes Podcasting Specificiations">here</a>.</p>
		</div>
	</td>
	</tr>


<tr valign="top">
<th scope="row">

<?php _e("iTunes Summary"); ?></th>
<td>
<p style="margin-top: 5px;">Your summary may not contain HTML and cannot exceed 4,000 characters in length.</p>

<textarea name="Feed[itunes_summary]" rows="5" style="width:80%;" ><?php echo $FeedSettings['itunes_summary']; ?></textarea>
<div><input type="checkbox" name="Feed[enhance_itunes_summary]" value="1" <?php echo ($FeedSettings['enhance_itunes_summary']?'checked ':''); ?>/> Enhance iTunes Summary from Blog Posts (<a href="http://help.blubrry.com/blubrry-powerpress/settings/enhanced-itunes-summary/" target="_blank">What's this</a>)</div>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Program Subtitle"); ?> <br />
</th>
<td>
<input type="text" name="Feed[itunes_subtitle]"style="width: 60%;"  value="<?php echo $FeedSettings['itunes_subtitle']; ?>" maxlength="250" />
</td>
</tr>
<?php } // End AdvancedMode ?>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Program Keywords"); ?> <br />
</th>
<td>
<input type="text" name="Feed[itunes_keywords]" style="width: 60%;"  value="<?php echo $FeedSettings['itunes_keywords']; ?>" maxlength="250" />
<p>Enter up to 12 keywords separated by commas.</p>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Category"); ?> 
</th>
<td>
<select name="Feed[itunes_cat_1]" style="width: 60%;">
<?php
$linkoptions = array("On page", "Disable");

$Categories = powerpress_itunes_categories(true);

echo '<option value="">Select Category</option>';

while( list($value,$desc) = each($Categories) )
	echo "\t<option value=\"$value\"". ($FeedSettings['itunes_cat_1']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

reset($Categories);
?>
</select>
</td>
</tr>

<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row">
<?php _e("iTunes Category 2"); ?> 
</th>
<td>
<select name="Feed[itunes_cat_2]" style="width: 60%;">
<?php
$linkoptions = array("On page", "Disable");

echo '<option value="">Select Category</option>';

while( list($value,$desc) = each($Categories) )
	echo "\t<option value=\"$value\"". ($FeedSettings['itunes_cat_2']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

reset($Categories);

?>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Category 3"); ?> 
</th>
<td>
<select name="Feed[itunes_cat_3]" style="width: 60%;">
<?php
$linkoptions = array("On page", "Disable");

echo '<option value="">Select Category</option>';

while( list($value,$desc) = each($Categories) )
	echo "\t<option value=\"$value\"". ($FeedSettings['itunes_cat_3']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

reset($Categories);
?>
</select>
</td>
</tr>
<?php } // End AdvancedMode ?>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Explicit"); ?> 
</th>
<td>
<select name="Feed[itunes_explicit]" class="bpp_input_med">
<?php
$explicit = array(0=>"no - display nothing", 1=>"yes - explicit content", 2=>"clean - no explicit content");

while( list($value,$desc) = each($explicit) )
	echo "\t<option value=\"$value\"". ($FeedSettings['itunes_explicit']==$value?' selected':''). ">$desc</option>\n";

?>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Image"); ?> 
</th>
<td>
<input type="text" id="itunes_image" name="Feed[itunes_image]" style="width: 60%;" value="<?php echo $FeedSettings['itunes_image']; ?>" maxlength="250" />
<a href="#" onclick="javascript: window.open( document.getElementById('itunes_image').value ); return false;">preview</a>

<p>Place the URL to the iTunes image above. e.g. http://mysite.com/images/itunes.jpg<br /><br />iTunes prefers square .jpg or .png images that are at 600 x 600 pixels (prevously 300 x 300), which is different than what is specified for the standard RSS image.</p>

<?php if( $SupportUploads ) { ?>
<p><input name="itunes_image_checkbox" type="checkbox" onchange="powerpress_show_field('itunes_image_upload', this.checked)" value="1" /> Upload new image </p>
<div style="display:none" id="itunes_image_upload">
	<label for="itunes_image">Choose file:</label><input type="file" name="itunes_image_file"  />
</div>
<?php } ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("RSS2 Image"); ?> <br />
</th>
<td>
<input type="text" id="rss2_image" name="Feed[rss2_image]" style="width: 60%;" value="<?php echo $FeedSettings['rss2_image']; ?>" maxlength="250" />
<a href="#" onclick="javascript: window.open( document.getElementById('rss2_image').value ); return false;">preview</a>

<p>Place the URL to the RSS image above. e.g. http://mysite.com/images/rss.jpg</p>
<p>RSS image should be at least 88 and at most 144 pixels wide and at least 31 and at most 400 pixels high in either .gif, .jpg and .png format. A square 144 x 144 pixel image is recommended.</p>

<?php if( $SupportUploads ) { ?>
<p><input name="rss2_image_checkbox" type="checkbox" onchange="powerpress_show_field('rss_image_upload', this.checked)" value="1" /> Upload new image</p>
<div style="display:none" id="rss_image_upload">
	<label for="rss2_image">Choose file:</label><input type="file" name="rss2_image_file"  />
</div>
<?php } ?>
</td>
</tr>

<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row">
<?php _e("Talent Name"); ?> <br />
</th>
<td>
<input type="text" name="Feed[itunes_talent_name]"style="width: 60%;"  value="<?php echo $FeedSettings['itunes_talent_name']; ?>" maxlength="250" />
</td>
</tr>
<?php } // End AdvancedMode ?>

<tr valign="top">
<th scope="row">
<?php _e("Email"); ?>
</th>
<td>
<input type="text" name="Feed[email]"  style="width: 60%;" value="<?php echo $FeedSettings['email']; ?>" maxlength="250" />
</td>
</tr>

<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row">
<?php _e("Copyright"); ?>
</th>
<td>
<input type="text" name="Feed[copyright]" style="width: 60%;" value="<?php echo $FeedSettings['copyright']; ?>" maxlength="250" />
</td>
</tr>
<?php } // End AdvancedMode ?>
</table>
<?php
	}
	
?>