<?php


function powerpress_admin_defaults()
{
	// TODO: Add all that stuff brian designed at the top1!
	$FeedAttribs = array('type'=>'general', 'channel'=>'', 'category_id'=>0, 'term_taxonomy_id'=>0, 'term_id'=>0, 'taxonomy_type'=>'', 'post_type'=>'');
	
	$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'basic');
	
	$FeedSettings = powerpress_get_settings('powerpress_feed');
	$FeedSettings = powerpress_default_settings($FeedSettings, 'editfeed');
	
	
	
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
	<h4><?php echo __('Default Mode', 'powerpress'); ?>
	&nbsp; <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_basic.php&mode=advanced"); ?>" id="powerpress_advanced_mode_button" class="button-primary"><?php echo __('Switch to Advanced Mode', 'powerpress'); ?></a>
</h4>
</div>

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