<?php
// powerpressadmin-presentation.php

function powerpressadmin_appearance()
{
	$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'appearance');
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
<th scope="row" style="background-image: url(/wp-includes/images/smilies/icon_exclaim.gif); background-position: 10px 10px; background-repeat: no-repeat; ">

<div style="margin-left: 24px;"><?php _e("Hybrid Themes"); ?></div></th>
<td>
<p style="margin-top: 5px;">
	The Hybrid Theme system will fail to display the player and links when the Hybrid Theme Setting "Use the excerpt on single posts for your meta description?" is checked. To fix,
	either uncheck the option or use the <a href="http://wordpress.org/extend/plugins/hybrid-bugfix/" target="_blank">Hybrid Theme Bugfix plugin</a>.
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
<p>
	Looking for a better Audio Player? Check out the <a href="http://wpaudioplayer.com" target="_blank" title="WP Audio Player 2.0">WP Audio Player 2.0</a>. 
	The WP Audio Player 2.0 options include
	theme colors, initial volume, player width and more.
</p>
<br />
<?php  
} // End powerpress_admin_appearance()

?>