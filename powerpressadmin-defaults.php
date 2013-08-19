<?php


function powerpressadmin_default_steps($FeedSettings, $General, $Step = 0)
{

	// TODO: Work on step 2 links!
?>
<div id="powerpress_steps">
	<div class="powerpress-step active-step" id="powerpreess_step_1">
	<h3><?php echo __('Step 1', 'powerpress'); ?></h3>
	<p>
	<?php echo __('Fill out the settings on this page', 'powerpress'); ?>
	</p>
	<?php powerpressadmin_complete_check($Step >= 1); ?>
	</div>
	<div class="powerpress-step<?php echo ($Step >= 1? ' active-step':''); ?>">
	<h3><?php echo __('Step 2', 'powerpress'); ?></h3>
	<p>
	<a href=""><?php echo __('Create a blog post with an episode', 'powerpress'); ?></a>
	</p>
	<p><a href=""><?php echo __('Need Help?', 'powerpress'); ?></a>
	</p>
	<?php powerpressadmin_complete_check($Step >= 2); ?>
	</div>
	<div class="powerpress-step<?php echo ($Step >= 2? ' active-step':''); ?>">
	<h3><?php echo __('Step 3', 'powerpress'); ?></h3>
	<p>
	<a href="create.blubrry.com/resources/blubrry-podcast-directory/feed-requirements/submit-podcast-to-itunes/?podcast-feed=<?php echo urlencode(get_feed_link('podcast')); ?>" target="_blank"><?php echo __('Submit your feed to iTunes and other podcast directories', 'powerpress'); ?></a>
	</p>
	<?php powerpressadmin_complete_check($Step == 3); ?>
	</div>
	<div class="clear"></div>
</div>
<?php
	
}

function powerpressadmin_complete_check($checked=false)
{
?>
<div class="powerpress-step-complete<?php echo ($checked?' powerpress-step-completed':''); ?>">
	<p>complete
	<span class="powerpress-step-complete-box">&nbsp;</span>
	</p>
</div>
<?php
}

function powerpress_admin_defaults()
{
	// TODO: Add all that stuff brian designed at the top1!
	$FeedAttribs = array('type'=>'general', 'channel'=>'', 'category_id'=>0, 'term_taxonomy_id'=>0, 'term_id'=>0, 'taxonomy_type'=>'', 'post_type'=>'');
	
	$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'basic');
	
	$FeedSettings = powerpress_get_settings('powerpress_feed');
	$FeedSettings = powerpress_default_settings($FeedSettings, 'editfeed');
	
	$Step = 0;
	if( !empty($FeedSettings['itunes_cat_1']) && !empty($FeedSettings['email']) && !empty($FeedSettings['itunes_image']) )
		$Step = 1;
	
	$episode_total = 0;
	if( $Step == 1 )
	{
		$episode_total = powerpress_admin_episodes_per_feed('podcast');
		if( $episode_total > 0 )
			$Step = 2;
	}
	$Step = 1;
	if( $Step == 2 && !empty($FeedSettings['itunes_url']) )
		$Step = 3;
	
?>
<script type="text/javascript"><!--


jQuery(document).ready(function($) {
	
	
	
	jQuery('#powerpress_advanced_mode_button').click( function(event) {
		event.preventDefault();
		jQuery('#powerpress_advanced_mode').val('1');
		jQuery(this).closest("form").submit();
	} );


} );
//-->
</script>
<input type="hidden" name="action" value="powerpress-save-settings" />
<input type="hidden" id="powerpress_advanced_mode" name="General[advanced_mode_2]" value="0" />

<div id="powerpress_admin_header">
<h2><?php echo __('Blubrry PowerPress Settings', 'powerpress'); ?></h2> 
<span class="powerpress-mode"><?php echo __('Default Mode', 'powerpress'); ?>
	&nbsp; <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_basic.php&mode=advanced"); ?>" id="powerpress_advanced_mode_button" class="button-primary"><?php echo __('Switch to Advanced Mode', 'powerpress'); ?></a>
</span>
</div>

<?php

	powerpressadmin_default_steps($FeedSettings, $General, $Step);
?>

<table class="form-table">
<tr valign="top">
<th scope="row">
<?php echo __('Program Title', 'powerpress'); ?>
</th>
<td>
<input type="text" name="Feed[title]"style="width: 60%;"  value="<?php echo $FeedSettings['title']; ?>" maxlength="250" />
(<?php echo __('leave blank to use blog title', 'powerpress'); ?>)
<p><?php echo __('Blog title:', 'powerpress') .' '. get_bloginfo_rss('name'); ?></p>
</td>
</tr>
</table>
<?php
	if( $Step > 1 ) // Only display if we have episdoes in the feed!
		powerpressadmin_edit_itunes_general($FeedSettings, $General, $FeedAttribs);
	// iTunes settings (in simple mode of course)
	powerpressadmin_edit_itunes_feed($FeedSettings, $General, $FeedAttribs);
	
	powerpressadmin_edit_artwork($FeedSettings, $General);
	powerpressadmin_appearance($General);
	
	/*
?>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo htmlspecialchars(__('Display Media & Links', 'powerpress')); ?></th> 
<td>
	<ul>
		<li><label><input type="radio" name="General[display_player]" value="1" <?php if( $General['display_player'] == 1 ) echo 'checked'; ?> /> <?php echo __('Below page content', 'powerpress'); ?></label> (<?php echo __('default', 'powerpress'); ?>)</li>
		<li>
			<ul>
				<li><?php echo __('Player and media links will appear <u>below</u> your post and page content.', 'powerpress'); ?></li>
			</ul>
		</li>
		
		<li><label><input type="radio" name="General[display_player]" value="2" <?php if( $General['display_player'] == 2 ) echo 'checked'; ?> /> <?php echo __('Above page content', 'powerpress'); ?></label></li>
		<li>
			<ul>
				<li><?php echo __('Player and media links will appear <u>above</u> your post and page content.', 'powerpress'); ?></li>
			</ul>
		</li>
		<li><label><input type="radio" name="General[display_player]" value="0" <?php if( $General['display_player'] == 0 ) echo 'checked'; ?> /> <?php echo __('Disable', 'powerpress'); ?></label></li>
		<li>
			<ul>
				<li><?php echo __('Player and media links will <u>NOT</u> appear in your post and page content. Media player and links can be added manually by using the <i>shortcode</i> below.', 'powerpress'); ?></li>
			</ul>
		</li>
	</ul>
	<p><input name="General[display_player_excerpt]" type="checkbox" value="1" <?php if( !empty($General['display_player_excerpt']) ) echo 'checked '; ?>/> <?php echo __('Display media / links in:', 'powerpress'); ?> <a href="http://codex.wordpress.org/Template_Tags/the_excerpt" title="<?php echo __('WordPress Excerpts', 'powerpress'); ?>" target="_blank"><?php echo __('WordPress Excerpts', 'powerpress'); ?></a>  (<?php echo __('e.g. search results', 'powerpress'); ?>)</p>
</td>
</tr>
</table>
<?php
	*/
	powerpressadmin_advanced_options($General);
}


?>