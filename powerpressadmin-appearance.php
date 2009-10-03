<?php
// powerpressadmin-appearance.php

function powerpressadmin_appearance()
{
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
	
?>
<input type="hidden" name="action" value="powerpress-save-appearance" />
<h2><?php echo __("Appearance Settings"); ?></h2>

<p style="margin-bottom: 0;">Configure how your media will be found on your blog.</p>


<table class="form-table">
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
<td><select name="General[player_function]" class="bpp_input_med">
<?php
$playeroptions = array(1=>'On Page & New Window', 2=>'On Page Only', 3=>'New Window Only', /* 4=>'On Page Link', 5=>'On Page Link & New Window', */ 0=>'Disable');

while( list($value,$desc) = each($playeroptions) )
	echo "\t<option value=\"$value\"". ($General['player_function']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

?>
</select>
<?php
		global $wp_version;
		if( version_compare($wp_version, '2.7') < 0 ) // Wordpress before version 2.7
		{
?>
<p><input name="CheckSWF" type="checkbox" value="1" /> Verify flash player</p>
<?php
		}
?>
</td>
</tr>
<?php
	if( count($Players) > 1 )
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
?>

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


<h2><?php echo __("Play in New Window Settings"); ?></h2>

<p style="margin-bottom: 0;">Configure the width and height of the new window.</p>


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


<h2><?php echo __("Default Player Settings"); ?></h2>

<p style="margin-bottom: 0;">Configure the width and height of the default media player.</p>


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
<?php echo __("Player Width Audio"); ?>
</th>
<td>
<input type="text" name="General[player_width_audio]" style="width: 50px;" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" value="<?php echo $General['player_width_audio']; ?>" maxlength="4" />
Width of Audio mp3 player (leave blank for 320 default)
</td>
</tr>

</table>

<?php if( !@$General['player_options'] ) { ?>
<h2><?php echo __("Enable Player Options"); ?></h2>
<p>
	<input name="General[player_options]" type="checkbox" value="1" /> 
	<?php echo __('Check this option if you would like to further customize your web player used in your blog pages.'); ?>
</p>
<?php } else { ?>
<h2><?php echo __("Disable Player Options"); ?></h2>
<p>
	<input name="General[player_options]" type="checkbox" value="0" /> 
	<?php echo __('Check this option if you would like to use the default player packaged with Blubrry PowerPress.'); ?>
</p>
<?php } ?>

<?php  
} // End powerpress_admin_appearance()

?>