<?php
// PowerPress Player settings page
	
function powerpress_admin_players()
{
	$General = powerpress_get_settings('powerpress_general');
	$select_player = false;
	if( isset($_GET['sp']) )
		$select_player = true;
	else if( !isset($General['player']) )
		$select_player = true;
		
	$Audio = array();
	$Audio['default'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/FlowPlayerClassic.mp3';
	$Audio['audio-player'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/1_Pixel_Out_Flash_Player.mp3';
	$Audio['flashmp3-maxi'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/Flash_Maxi_Player.mp3';
	$Audio['simple_flash'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/Simple_Flash_MP3_Player.mp3';
	$Audio['audioplay'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/AudioPlay.mp3';
		
		
		/*
		<div><
		object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="30" height="30">
		<PARAM NAME=movie VALUE="http://www.strangecube.com/audioplay/online/audioplay.swf?file=http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/AudioPlay.mp3&auto=no&sendstop=yes&repeat=1&buttondir=http://www.strangecube.com/audioplay/online/alpha_buttons/negative&bgcolor=0xffffff&mode=playpause"><PARAM NAME=quality VALUE=high><PARAM NAME=wmode VALUE=transparent><embed src="http://www.strangecube.com/audioplay/online/audioplay.swf?file=http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/AudioPlay.mp3&auto=no&sendstop=yes&repeat=1&buttondir=http://www.strangecube.com/audioplay/online/alpha_buttons/negative&bgcolor=0xffffff&mode=playpause" quality=high wmode=transparent width="30" height="30" align="" TYPE="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object></div><!-- End of generated code -->
		*/
		
		
?>
<link rel="stylesheet" href="<?php echo powerpress_get_root_url(); ?>3rdparty/colorpicker/css/colorpicker.css" type="text/css" />
<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>3rdparty/colorpicker/js/colorpicker.js"></script>
<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>player.js"></script>
<script type="text/javascript">

function rgb2hex(rgb) {
 
 rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
 function hex(x) {
  hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
  return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
 }
 
 if( rgb )
	return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
 return '';
}

function UpdatePlayerPreview(name, value)
{
	if( typeof(generator) != "undefined" ) // Update the Maxi player...
	{
		generator.updateParam(name, value);
		generator.updatePlayer();
	}
	
	if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
		update_audio_player();
}
				
jQuery(document).ready(function($) {
	
	jQuery('.color_preview').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).css({ 'background-color' : '#' + hex });
			jQuery(el).ColorPickerHide();
			var Id = jQuery(el).attr('id');
			Id = Id.replace(/_prev/, '');
			jQuery('#'+ Id  ).val( '#' + hex );
			UpdatePlayerPreview(Id, '#'+hex );
		},
		onBeforeShow: function () {
			jQuery(this).ColorPickerSetColor( rgb2hex( jQuery(this).css("background-color") ) );
		}
	})
	.bind('keyup', function(){
		jQuery(this).ColorPickerSetColor( rgb2hex( jQuery(this).css("background-color") ) );
	});
	
	jQuery('.color_field').bind('change', function () {
		var Id = jQuery(this).attr('id');
		jQuery('#'+ Id + '_prev'  ).css( { 'background-color' : jQuery(this).val() } );
		if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
			update_audio_player();
	});
	
	jQuery('.other_field').bind('change', function () {
		if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
			update_audio_player();
	});

});
	
</script>


<!-- special page styling goes here -->
<style type="text/css">
div.color_control { display: block; float:left; width: 100%; padding:  0; }
div.color_control input { display: inline; float: left; }
div.color_control div.color_picker { display: inline; float: left; margin-top: 3px; }
#player_preview { margin-bottom: 0px; height: 50px; margin-top: 8px;}
input#colorpicker-value-input {
	width: 60px;
	height: 16px;
	padding: 0;
	margin: 0;
	font-size: 12px;
}
</style>
<?php
	
	// mainly 2 pages, first page selects a player, second configures the player, if there are optiosn to configure for that player. If the user is on the second page,
	// a link should be provided to select a different player.
	if( $select_player )
	{
?>
<script language="javascript" type="text/javascript">
function powerpress_activate_player(Player)
{
	jQuery('#player_'+Player).attr('checked', true);
	jQuery("form:first").submit();
	return false;
}
</script>
<input type="hidden" name="action" value="powerpress-select-player" />
<h2><?php echo __('Blubrry PowerPress Player Options', 'powerpress'); ?></h2>
<p style="margin-bottom: 0;"><?php echo __('Select the media player you would like to use.', 'powerpress'); ?></p>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __('Select Player', 'powerpress'); ?></th>  
<td>

	<ul>
		<li><label><input type="radio" name="Player[player]" id="player_default" value="default" <?php if( $General['player'] == 'default' || !isset($General['default']) ) echo 'checked'; ?> />
		<?php echo __('Flow Player Classic (default)', 'powerpress'); ?></label>
			 <strong style="padding-top: 8px; margin-left: 20px;"><a href="#" onclick="return powerpress_activate_player('default');"><?php echo __('Activate and Configure Now', 'powerpress'); ?></a></strong>
		</li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>
<?php
			$media_url = '';
			$content = '';
			$content .= '<div id="flow_player_classic"></div>'.PHP_EOL;
			$content .= '<script type="text/javascript">'.PHP_EOL;
			$content .= "pp_flashembed(\n";
			$content .= "	'flow_player_classic',\n";
			$content .= "	{src: '". powerpress_get_root_url() ."FlowPlayerClassic.swf', width: 320, height: 24 },\n";
			$content .= "	{config: { autoPlay: false, autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '{$Audio['default']}', loop: false, autoRewind: true } }\n";
			$content .= ");\n";
			$content .= "</script>\n";
			echo $content;
?>
			</p>
			<p>
				<?php echo __('Flow Player Classic is an open source flash player that supports both audio (mp3 only) and video (flv only) media files. It includes all the necessary features for playback including a play/pause button, scrollable position bar, ellapsed time, total time, mute button and volume control.', 'powerpress'); ?>
			</p>
			<p>
				<?php echo __('Flow Player Classic was chosen as the default player in Blubrry PowerPress because if its backwards compatibility with older versions of Flash and support for both audio and flash video.', 'powerpress'); ?>
			</p>
		</li>
		
		<li><label><input type="radio" name="Player[player]" id="player_audio_player" value="audio-player" <?php if( $General['player'] == 'audio-player' ) echo 'checked'; ?> /> <?php echo __('1 Pixel Out Audio Player', 'powerpress'); ?></label>
			<strong style="padding-top: 8px; margin-left: 20px;"><a href="#" onclick="return powerpress_activate_player('audio_player');"><?php echo __('Activate and Configure Now', 'powerpress'); ?></a></strong>
		</li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>
<script language="JavaScript" src="<?php echo powerpressplayer_get_root_url();?>audio-player.js"></script>
<object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url();?>audio-player.swf" id="audioplayer1" height="24" width="290">
<param name="movie" value="<?php echo powerpressplayer_get_root_url();?>/audio-player.swf" />
<param name="FlashVars" value="playerID=1&amp;soundFile=<?php echo $Audio['audio-player']; ?>" />
<param name="quality" value="high" />
<param name="menu" value="false" />
<param name="wmode" value="transparent" />
</object>			</p>
			<p>
				<?php echo __('1 Pixel Out Audio Player is a popular customizable audio (mp3 only) flash player. Features include an animated play/pause button, scrollable position bar, ellapsed/remaining time, volume control and color styling options.', 'powerpress'); ?>
			</p>
		</li>
		
		<li><label><input type="radio" name="Player[player]" id="player_flashmp3_maxi" value="flashmp3-maxi" <?php if( $General['player'] == 'flashmp3-maxi' ) echo 'checked'; ?> /> <?php echo __('Mp3 Player Maxi', 'powerpress'); ?></label>
			<strong style="padding-top: 8px; margin-left: 20px;"><a href="#" onclick="return powerpress_activate_player('flashmp3_maxi');"><?php echo __('Activate and Configure Now', 'powerpress'); ?></a></strong>
		</li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>
				<object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url(); ?>player_mp3_maxi.swf" width="200" height="20">
    <param name="movie" value="<?php echo powerpressplayer_get_root_url(); ?>player_mp3_maxi.swf" />
    <param name="bgcolor" value="#ffffff" />
    <param name="FlashVars" value="mp3=<?php echo $Audio['flashmp3-maxi']; ?>&amp;showstop=1&amp;showinfo=1&amp;showvolume=1" />
</object>
			</p>
			<p>
				<?php echo __('Flash Mp3 Maxi Player is a customizable open source audio (mp3 only) flash player. Features include pause/play/stop/file info buttons, scrollable position bar, volume control and color styling options.', 'powerpress'); ?>
			</p>
		</li>
		
		<li><label><input type="radio" name="Player[player]" id="player_simple_flash" value="simple_flash" <?php if( $General['player'] == 'simple_flash' ) echo 'checked'; ?> /> <?php echo __('Simple Flash MP3 Player', 'powerpress'); ?></label>
			<strong style="padding-top: 8px; margin-left: 20px;"><a href="#" onclick="return powerpress_activate_player('simple_flash');"><?php echo __('Activate and Configure Now', 'powerpress'); ?></a></strong>
		</li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>

    <object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url(); ?>simple_mp3.swf" width="150" height="50">
    <param name="movie" value="<?php echo powerpressplayer_get_root_url(); ?>simple_mp3.swf" />
    <param name="wmode" value="transparent" />
    <param name="FlashVars" value="url=<?php echo $Audio['simple_flash']; ?>&amp;autostart=false" />
    <param name="quality" value="high" />
    <embed wmode="transparent" src="<?php echo get_bloginfo('url'); ?>?url=<?php echo $Audio['simple_flash']; ?>&amp;autostart=false" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="150" height="50"></embed>
</object>
			</p>
			<p>
				<?php echo __('Simple Flash MP3 Player is a free and simple audio (mp3 only) flash player. Features include play/pause and stop buttons.', 'powerpress'); ?>
			</p>
		</li>
		
		<li><label><input type="radio" name="Player[player]" id="player_audioplay" value="audioplay" <?php if( $General['player'] == 'audioplay' ) echo 'checked'; ?> /> <?php echo __('AudioPlay', 'powerpress'); ?></label>
			<strong style="padding-top: 8px; margin-left: 20px;"><a href="#" onclick="return powerpress_activate_player('audioplay');"><?php echo __('Activate and Configure Now', 'powerpress'); ?></a></strong>
		</li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>
                           <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="30" height="30">
			<param name="movie" value="<?php echo powerpressplayer_get_root_url(); ?>audioplay.swf?buttondir=<?php echo powerpressplayer_get_root_url(); ?>buttons/negative" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#FFFFFF" />
			
                        <param name="FlashVars" value="buttondir=<?php echo powerpressplayer_get_root_url(); ?>buttons/negative&amp;mode=playstop" />
			    <embed src="<?php echo powerpressplayer_get_root_url(); ?>audioplay.swf?file=<?php echo $Audio['audioplay']; ?>&amp;auto=no&amp;&sendstop=yes&amp;repeat=1&amp;mode=playpause&amp;buttondir=<?php echo powerpressplayer_get_root_url(); ?>buttons/negative" quality=high bgcolor=#FFFFFF width="30" height="30"
				align="" TYPE="application/x-shockwave-flash"
				pluginspage="http://www.macromedia.com/go/getflashplayer">
			    </embed>

		</object>

			</p>
			<p>
				<?php echo __('AudioPlay is one button freeware audio (mp3 only) flash player. Features include a play/stop or play/pause button available in two sizes in either black or white.', 'powerpress'); ?>
			</p>
		</li>
		
		
	</ul>

</td>
</tr>
</table>
<h4 style="margin-bottom: 0;"><?php echo __('Click \'Save Changes\' to activate and configure selected player.', 'powerpress'); ?></h4>
<?php
	}
	else
	{
?>
<h2><?php echo __('Configure Player', 'powerpress'); ?></h2>
<p style="margin-bottom: 20px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_player.php&amp;sp=1"); ?>"><?php echo __('Select a different flash player', 'powerpress'); ?></a></strong></p>
<?php 
		// Start adding logic here to display options based on the player selected...
		switch( $General['player'] )
		{
			case 'audio-player': {
			
            $PlayerSettings = powerpress_get_settings('powerpress_audio-player');
            if($PlayerSettings == ""):
                $PlayerSettings = array(
                    'width'=>'290',
                    'transparentpagebg' => 'yes',
                    'lefticon' => '#333333',
                    'leftbg' => '#CCCCCC',
                    'bg' => '#E5E5E5',
                    'voltrack' => '#F2F2F2',
                    'volslider' => '#666666',
                    'rightbg' => '#B4B4B4',
                    'rightbghover' => '#999999',
                    'righticon' => '#333333',
                    'righticonhover' => '#FFFFFF',
                    'loader' => '#009900',
                    'track' => '#FFFFFF',
                    'tracker' => '#DDDDDD',
                    'border' => '#CCCCCC',
                    'skip' => '#666666',
                    'text' => '#333333',
                    'pagebg' => '',
                    'rtl' => 'no',
										'initialvolume'=>'60',
                    'animation'=>'yes',
										'remaining'=>'no',
                    );
            endif;
						
						if( empty($PlayerSettings['remaining']) )
							$PlayerSettings['remaining'] = 'no'; // New default setting
						if( !isset($PlayerSettings['buffer']) )
							$PlayerSettings['buffer'] = ''; // New default setting	
							
							
                            $keys = array_keys($PlayerSettings);
                    $flashvars ='';
                foreach ($keys as $key) {
                    if($PlayerSettings[$key] != "") {                        
                        $flashvars .= '&amp;'. $key .'='. preg_replace('/\#/','',$PlayerSettings[$key]);
                    }
								}

                if($PlayerSettings['pagebg'] != ""){
                    $transparency = '<param name="bgcolor" value="'.$PlayerSettings['pagebg'].'" />';
                    $PlayerSettings['transparentpagebg'] = "no";
                    $flashvars .= '&amp;transparentpagebg=no';
                    $flashvars .= '&amp;pagebg='.$PlayerSettings['pagebg'];
                }
                else {
                    $PlayerSettings['transparentpagebg'] = "yes";
                    $transparency = '<param name="wmode" value="transparent" />';
                    $flashvars .= '&amp;transparentpagebg=yes';
                }
?>

<script type="text/javascript">

function update_audio_player()
{
	var myParams = new Array("lefticon","leftbg", "bg", "voltrack", "rightbg", "rightbghover", "righticon", "righticonhover", "loader", "track", "tracker", "border", "skip", "text", "pagebg", "rtl", "animation", "titles", "initialvolume");
	var myWidth = document.getElementById('player_width').value;
	var myBackground = '';
	if( myWidth < 10 || myWidth > 900 )
		myWidth = 290;
	
	var out = '<object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url();?>/audio-player.swf" width="'+myWidth+'" height="24">'+"\n";
	out += '    <param name="movie" value="<?php echo powerpressplayer_get_root_url();?>/audio-player.swf" />'+"\n";
	out += '    <param name="FlashVars" value="playerID=1&amp;soundFile=<?php echo $Audio['audio-player']; ?>';
	
	var x = 0;
	for( x = 0; x < myParams.length; x++ )
	{
		if( myParams[ x ] == 'border' )
			var Element = document.getElementById( 'player_border' );
		else
			var Element = document.getElementById( myParams[ x ] );
		
		if( Element )
		{
			if( Element.value != '' )
			{
				out += '&amp;';
				out += myParams[ x ];
				out += '=';
				out += Element.value.replace(/^#/, '');
				if( myParams[ x ] == 'pagebg' )
				{
					myBackground = '<param name="bgcolor" value="'+ Element.value +'" />';
					out += '&amp;transparentpagebg=no';
				}
			}
			else
			{
				if( myParams[ x ] == 'pagebg' )
				{
					out += '&amp;transparentpagebg=yes';
					myBackground = '<param name="wmode" value="transparent" />';
				}
			}
		}
	}
	
	out += '" />'+"\n";
	out += '<param name="quality" value="high" />';
	out += '<param name="menu" value="false" />';
	out += myBackground;
	out += '</object>';
	
	var player = document.getElementById("player_preview");
	player.innerHTML = out;
}

function audio_player_defaults()
{
 	if( confirm('<?php echo __("Set defaults, are you sure?\n\nAll of the current settings will be overwritten!", 'powerpress'); ?>') )
	{
		jQuery('#player_width').val('290');
		UpdatePlayerPreview('player_width',jQuery('#player_width').val() );
		
		jQuery('#transparentpagebg').val( 'yes');
		UpdatePlayerPreview('transparentpagebg',jQuery('#transparentpagebg').val() );
		
		jQuery('#lefticon').val( '#333333');
		UpdatePlayerPreview('lefticon',jQuery('#lefticon').val() );
		jQuery('#lefticon_prev'  ).css( { 'background-color' : '#333333' } );
		
		jQuery('#leftbg').val( '#CCCCCC');
		UpdatePlayerPreview('leftbg',jQuery('#leftbg').val() );
		jQuery('#leftbg_prev'  ).css( { 'background-color' : '#CCCCCC' } );
		
		jQuery('#bg').val( '#E5E5E5');
		UpdatePlayerPreview('bg',jQuery('#bg').val() );
		jQuery('#bg_prev'  ).css( { 'background-color' : '#E5E5E5' } );
		
		jQuery('#voltrack').val( '#F2F2F2');
		UpdatePlayerPreview('voltrack',jQuery('#voltrack').val() );
		jQuery('#voltrack_prev'  ).css( { 'background-color' : '#F2F2F2' } );
		
		jQuery('#volslider').val( '#666666');
		UpdatePlayerPreview('volslider',jQuery('#volslider').val() );
		jQuery('#volslider_prev'  ).css( { 'background-color' : '#666666' } );
		
		jQuery('#rightbg').val( '#B4B4B4');
		UpdatePlayerPreview('rightbg',jQuery('#rightbg').val() );
		jQuery('#rightbg_prev'  ).css( { 'background-color' : '#B4B4B4' } );
		
		jQuery('#rightbghover').val( '#999999');
		UpdatePlayerPreview('rightbghover',jQuery('#rightbghover').val() );
		jQuery('#rightbghover_prev'  ).css( { 'background-color' : '#999999' } );
		
		jQuery('#righticon').val( '#333333');
		UpdatePlayerPreview('righticon',jQuery('#righticon').val() );
		jQuery('#righticon_prev'  ).css( { 'background-color' : '#333333' } );
		
		jQuery('#righticonhover').val( '#FFFFFF');
		UpdatePlayerPreview('righticonhover',jQuery('#righticonhover').val() );
		jQuery('#righticonhover_prev'  ).css( { 'background-color' : '#FFFFFF' } );
		
		jQuery('#loader').val( '#009900');
		UpdatePlayerPreview('loader',jQuery('#loader').val() );
		jQuery('#loader_prev'  ).css( { 'background-color' : '#009900' } );
		
		jQuery('#track').val( '#FFFFFF');
		UpdatePlayerPreview('track',jQuery('#track').val() );
		jQuery('#track_prev'  ).css( { 'background-color' : '#FFFFFF' } );
		
		jQuery('#tracker').val( '#DDDDDD');
		UpdatePlayerPreview('tracker',jQuery('#tracker').val() );
		jQuery('#tracker_prev'  ).css( { 'background-color' : '#DDDDDD' } );
		
		jQuery('#player_border').val( '#CCCCCC');
		UpdatePlayerPreview('player_border',jQuery('#player_border').val() );
		jQuery('#player_border_prev'  ).css( { 'background-color' : '#CCCCCC' } );
		
		jQuery('#skip').val( '#666666');
		UpdatePlayerPreview('skip',jQuery('#skip').val() );
		jQuery('#skip_prev'  ).css( { 'background-color' : '#666666' } );
		
		jQuery('#text').val( '#333333');
		UpdatePlayerPreview('text',jQuery('#text').val() );
		jQuery('#text_prev'  ).css( { 'background-color' : '#333333' } );
		
		jQuery('#pagebg').val( '');
		UpdatePlayerPreview('pagebg',jQuery('#pagebg').val() );
		
		jQuery('#animation').val( 'yes');
		UpdatePlayerPreview('animation',jQuery('#animation').val() );
		
		jQuery('#remaining').val( 'no');
		UpdatePlayerPreview('remaining',jQuery('#remaining').val() );
		
		jQuery('#buffer').val( '');
		UpdatePlayerPreview('buffer',jQuery('#buffer').val() );
		
		jQuery('#rtl' ).val( 'no' );
		UpdatePlayerPreview('rtl',jQuery('#rtl').val() );
		
		jQuery('#initialvolume').val('60');
		UpdatePlayerPreview('initialvolume',jQuery('#initialvolume').val() );
		
		update_audio_player();
	}
}

</script>
	<input type="hidden" name="action" value="powerpress-audio-player" />
	<?php echo __('Configure the 1 pixel out Audio Player', 'powerpress'); ?>
	
	
<table class="form-table">
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Preview of Player', 'powerpress'); ?>
		</th>
		<td>
			<div id="player_preview">
<object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url();?>audio-player.swf" id="audioplayer1" height="24" width="<?php echo $PlayerSettings['width']; ?>">
<param name="movie" value="<?php echo powerpressplayer_get_root_url();?>audio-player.swf" />
<param name="FlashVars" value="playerID=1&amp;soundFile=<?php echo $Audio['audio-player']; ?><?php echo $flashvars;?>" />
<param name="quality" value="high" />
<param name="menu" value="false" />
<?php echo $transparency; ?>
</object>
			</div>
		</td>
	</tr>
</table>

<div id="powerpress_settings_page" class="powerpress_tabbed_content" style="position: relative;">
	<div style="position: absolute; top: 6px; right:0px;">
		<a href="#" onclick="audio_player_defaults();return false;"><?php echo __('Set Defaults', 'powerpress'); ?></a>
	</div>
  <ul class="powerpress_settings_tabs"> 
		<li><a href="#tab_general"><span><?php echo __('Basic Settings', 'powerpress'); ?></span></a></li> 
		<li><a href="#tab_progress"><span><?php echo __('Progress Bar', 'powerpress'); ?></span></a></li> 
		<li><a href="#tab_volume"><span><?php echo __('Volume Button', 'powerpress'); ?></span></a></li>
		<li><a href="#tab_play"><span><?php echo __('Play / Pause Button', 'powerpress'); ?></span></a></li>
  </ul>
	
 <div id="tab_general" class="powerpress_tab">
 <h3><?php echo __('General Settings', 'powerpress'); ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php echo __('Page Background Color', 'powerpress'); ?>
                        
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="pagebg" name="Player[pagebg]" class="color_field" value="<?php echo $PlayerSettings['pagebg']; ?>" maxlength="20" />
				<img id="pagebg_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['pagebg']; ?>;" class="color_preview" />
			</div>
			<small>(<?php echo __('leave blank for transparent', 'powerpress'); ?>)</small>
		</td>
	</tr>	<tr valign="top">
		<th scope="row">
			<?php echo __('Player Background Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bg" name="Player[bg]" class="color_field" value="<?php echo $PlayerSettings['bg']; ?>" maxlength="20" />
				<img id="bg_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bg']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Width (in pixels)', 'powerpress'); ?>
		</th>
		<td>
          <input type="text" style="width: 50px;" id="player_width" name="Player[width]" class="other_field" value="<?php echo $PlayerSettings['width']; ?>" maxlength="20" />
				<?php echo __('width of the player. e.g. 290 (290 pixels) or 100%', 'powerpress'); ?>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Right-to-Left', 'powerpress'); ?>
		</th>
		<td>
			<select style="width: 102px;" id="rtl" name="Player[rtl]" class="other_field"> 
<?php
			$options = array( 'yes'=>__('Yes', 'powerpress'), 'no'=>__('No', 'powerpress') );
			powerpress_print_options( $options, $PlayerSettings['rtl']);
?>
          </select>			<?php echo __('switches the layout to animate from the right to the left', 'powerpress'); ?>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Loading Bar Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="loader" name="Player[loader]" class="color_field" value="<?php echo $PlayerSettings['loader']; ?>" maxlength="20" />
				<img id="loader_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['loader']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Text Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
                <input type="text" style="width: 100px;" id="text" name="Player[text]" class="color_field" value="<?php echo $PlayerSettings['text']; ?>" maxlength="20" />
						<img id="text_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['text']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Text In Player', 'powerpress'); ?> 
		</th>
		<td>
          <div><input type="text" style="width: 60%;" id="titles" name="Player[titles]" class="other_field" value="<?php echo $PlayerSettings['titles']; ?>" maxlength="100" /></div>
				<small><?php echo sprintf(__('Enter \'%s\' to display track name from mp3. Only works if media is hosted on same server as blog.', 'powerpress'), __('TRACK', 'powerpress') ); ?></small>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Play Animation', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
<select style="width: 102px;" id="animation" name="Player[animation]" class="other_field"> 
<?php
			$options = array( 'yes'=>__('Yes', 'powerpress'), 'no'=>__('No', 'powerpress') );
			powerpress_print_options( $options, $PlayerSettings['animation']);
?>
                                </select>			<?php echo __('if no, player is always open', 'powerpress'); ?></div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Display Remaining Time', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
<select style="width: 102px;" id="remaining" name="Player[remaining]" class="other_field">
<?php
			$options = array( 'yes'=>__('Yes', 'powerpress'), 'no'=>__('No', 'powerpress') );
			powerpress_print_options( $options, $PlayerSettings['remaining']);
?>
                                </select>			<?php echo __('if yes, shows remaining track time rather than ellapsed time (default: no)', 'powerpress'); ?></div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Buffering Time', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
<select style="width: 200px;" id="buffer" name="Player[buffer]" class="other_field"> 
<?php
			$options = array('0'=>__('No buffering', 'powerpress'), ''=>__('Default (5 seconds)', 'powerpress'),'10'=>__('10 seconds', 'powerpress'),'15'=>__('15 seconds', 'powerpress'),'20'=>__('20 seconds', 'powerpress'),'30'=>__('30 seconds', 'powerpress'),'60'=>__('60 seconds', 'powerpress'));
			powerpress_print_options( $options, $PlayerSettings['buffer']);
?>
                                </select>		<?php echo __('buffering time in seconds', 'powerpress'); ?></div>
		</td>
	</tr>
	
	
</table>
</div>

 <div id="tab_progress" class="powerpress_tab">
	<h3><?php echo __('Progress Bar', 'powerpress'); ?></h3>
<table class="form-table">
        <tr valign="top">
		<th scope="row">
			<?php echo __('Progress Bar Background', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
										<input type="text" style="width: 100px;" id="track" name="Player[track]" class="color_field" value="<?php echo $PlayerSettings['track']; ?>" maxlength="20" />
										<img id="track_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['track']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Progress Bar Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
                            <input type="text" style="width: 100px;" id="tracker" name="Player[tracker]" class="color_field" value="<?php echo $PlayerSettings['tracker']; ?>" maxlength="20" />
											<img id="tracker_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['tracker']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Progress Bar Border', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
                            <input type="text" style="width: 100px;" id="player_border" name="Player[border]" class="color_field" value="<?php echo $PlayerSettings['border']; ?>" maxlength="20" />
											<img id="player_border_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['border']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>       
	</table>
	</div>
	
	
<div id="tab_volume" class="powerpress_tab">
	<h3><?php echo __('Volume Button Settings', 'powerpress'); ?></h3>
	<table class="form-table">	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Initial Volume', 'powerpress'); ?> 
		</th>
		<td>
			<select style="width: 100px;" id="initialvolume" name="Player[initialvolume]" class="other_field">
<?php
			
			for($x = 0; $x <= 100; $x +=5 )
			{
				echo '<option value="'. $x .'"'. ($PlayerSettings['initialvolume'] == $x?' selected':'') .'>'. $x .'%</option>';
			}
?>
			</select> <?php echo __('initial volume level (default: 60)', 'powerpress'); ?>
		</td>
	</tr>
				
	<tr valign="top">
		<th scope="row">
			<?php echo __('Volumn Background Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="leftbg" name="Player[leftbg]" class="color_field" value="<?php echo $PlayerSettings['leftbg']; ?>" maxlength="20" />
				<img id="leftbg_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['leftbg']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Speaker Icon Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="lefticon" name="Player[lefticon]" class="color_field" value="<?php echo $PlayerSettings['lefticon']; ?>" maxlength="20" />
				<img id="lefticon_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['lefticon']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Volume Icon Background', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="voltrack" name="Player[voltrack]" class="color_field" value="<?php echo $PlayerSettings['voltrack']; ?>" maxlength="20" />
				<img id="voltrack_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['voltrack']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Volume Slider Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="volslider" name="Player[volslider]" class="color_field" value="<?php echo $PlayerSettings['volslider']; ?>" maxlength="20" />
				<img id="volslider_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['volslider']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
</table>
</div>

<div id="tab_play" class="powerpress_tab">
	<h3><?php echo __('Play / Pause Button Settings', 'powerpress'); ?></h3>
	<table class="form-table">	
        <tr valign="top">
		<th scope="row">
			<?php echo __('Play/Pause Background Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="rightbg" name="Player[rightbg]" class="color_field" value="<?php echo $PlayerSettings['rightbg']; ?>" maxlength="20" />
				<img id="rightbg_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['rightbg']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Play/Pause Hover Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="rightbghover" name="Player[rightbghover]" class="color_field" value="<?php echo $PlayerSettings['rightbghover']; ?>" maxlength="20" />
				<img id="rightbghover_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['rightbghover']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Play/Pause Icon Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="righticon" name="Player[righticon]" class="color_field" value="<?php echo $PlayerSettings['righticon']; ?>" maxlength="20" />
				<img id="righticon_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['righticon']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Play/Pause Icon Hover Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="righticonhover" name="Player[righticonhover]" class="color_field" value="<?php echo $PlayerSettings['righticonhover']; ?>" maxlength="20" />
				<img id="righticonhover_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['righticonhover']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>

</table>
</div> <!-- end tab -->
</div> <!-- end tab wrapper -->

<?php
			}; break;
                        case 'simple_flash':{ ?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php echo __('Preview of Player', 'powerpress'); ?>
		</th>
		<td>
			<p>
    <object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url(); ?>simple_mp3.swf" width="150" height="50">
    <param name="movie" value="<?php echo powerpressplayer_get_root_url(); ?>simple_mp3.swf" />
    <param name="wmode" value="transparent" />
    <param name="FlashVars" value="url=<?php echo $Audio['simple_flash']; ?>&amp;autostart=false" />
    <param name="quality" value="high" />
    <embed wmode="transparent" src="?url=<?php echo $Audio['simple_flash']; ?>&amp;autostart=false" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="150" height="50"></embed>
</object>

			</p>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			&nbsp;
		</th>
		<td>
			<p><?php echo __('Simple Flash Player has no additional settings.', 'powerpress'); ?></p>
		</td>
	</tr>
</table>                            
              <?php          }; break;

			case 'flashmp3-maxi': {
                            //get settings for Flash MP3 Maxi player
                            $PlayerSettings = powerpress_get_settings('powerpress_flashmp3-maxi');
                            
                            
                            //set array values for dropdown lists
                            $options = array('0','1');
                            $autoload = array('always'=>'Always','never'=>'Never','autohide'=>'Auto Hide');
                            $volume = array('0'=>'0','25'=>'25','50'=>'50','75'=>'75','100'=>'100','125'=>'125','150'=>'150','175'=>'175','200'=>'200');
                            
                            //set array values for flash variables with no dependencies
                            $keys = array('bgcolor1','bgcolor2','bgcolor','textcolor','buttoncolor','buttonovercolor','showstop','showinfo','showvolume','height','width','showloading','buttonwidth','volume','showslider');
                            
                            //set PlayerSettings as blank array for initial setup
                                //This keeps the foreach loop from returning an error
                            if($PlayerSettings == ""){
                                $PlayerSettings = array(
                                    'bgcolor1'=>'#7c7c7c',
                                    'bgcolor2'=>'#333333',
                                    'textcolor' => '#FFFFFF',
                                    'buttoncolor' => '#FFFFFF',
                                    'buttonovercolor' => '#FFFF00',
                                    'showstop' => '0',
                                    'showinfo' => '0',
                                    'showvolume' => '1',
                                    'height' => '20',
                                    'width' => '200',
                                    'showloading' => 'autohide',
                                    'buttonwidth' => '26',
                                    'volume' => '100',
                                    'showslider' => '1',
																		'slidercolor1'=>'#cccccc',
																		'slidercolor2'=>'#888888',
                                    'sliderheight' => '10',
                                    'sliderwidth' => '20',
                                    'loadingcolor' => '#FFFF00', 
                                    'volumeheight' => '6',
                                    'volumewidth' => '30',
                                    'sliderovercolor' => '#eeee00'
                                    );
                            }

                            $flashvars = '';
                            $flashvars .= "mp3=".$Audio['flashmp3-maxi'];

                            //set non-blank options without dependencies as flash variables for preview
                            foreach($keys as $key) {
                                if($PlayerSettings[$key] != "") {
                                    $flashvars .= '&amp;'. $key .'='. preg_replace('/\#/','',$PlayerSettings[''.$key.'']);
                                }
                            }
                            //set slider dependencies
                            if($PlayerSettings['showslider'] != "0") {
                                if($PlayerSettings['sliderheight'] != "") {
                                    $flashvars .= '&amp;sliderheight='. $PlayerSettings['sliderheight'];
                                }
                                if($PlayerSettings['sliderwidth'] != "") {
                                    $flashvars .= '&amp;sliderwidth='. $PlayerSettings['sliderwidth'];
                                }
                                if($PlayerSettings['sliderovercolor'] != ""){
                                    $flashvars .= '&amp;sliderovercolor='. preg_replace('/\#/','',$PlayerSettings['sliderovercolor']);
                                }
                            }
                            //set volume dependencies
                            if($PlayerSettings['showvolume'] != "0") {
                                if($PlayerSettings['volumeheight'] != "") {
                                    $flashvars .= '&amp;volumeheight='. $PlayerSettings['volumeheight'];
                                }
                                if($PlayerSettings['volumewidth'] != "") {
                                    $flashvars .= '&amp;volumewidth='. $PlayerSettings['volumewidth'];
                                }
                            }
                            //set autoload dependencies
                            if( @$PlayerSettings['showautoload'] != "never") {
                                if($PlayerSettings['loadingcolor'] != "") {
                                    $flashvars .= '&amp;laodingcolor='. preg_replace('/\#/','',$PlayerSettings['loadingcolor']);
                                }
                            }


                            //set default width for object
                            if($PlayerSettings['width'] == ""){
                                $width = "200";
                            }else{
                                $width = $PlayerSettings['width'];
                            }
                            if($PlayerSettings['height'] == ""){
                                $height = "20";
                            }else{
                                $height = $PlayerSettings['height'];
                            }

                            //set background transparency
                            if($PlayerSettings['bgcolor'] != ""){
                                $transparency = '<param name="bgcolor" value="'. $color7 .'" />';
                            }else{
                                $transparency = '<param name="wmode" value="transparent" />';
                            }
                            
                            //set flashvars
                            if($flashvars != ""){
                                $flashvars= '<param name="FlashVars" value="'. $flashvars .'" />'.PHP_EOL;
                            }

?>
<script type="text/javascript">

function audio_player_defaults()
{
	if( confirm('<?php echo __("Set defaults, are you sure?\n\nAll of the current settings will be overwritten!'", 'powerpress'); ?>) )
	{
		jQuery('#bgcolor1').val('#7c7c7c');
		UpdatePlayerPreview('bgcolor1',jQuery('#bgcolor1').val() );
		jQuery('#bgcolor1_prev'  ).css( { 'background-color' : '#7c7c7c' } );
		
		jQuery('#bgcolor2').val('#333333' );
		UpdatePlayerPreview('bgcolor2',jQuery('#bgcolor2').val() );
		jQuery('#bgcolor2_prev'  ).css( { 'background-color' : '#333333' } );
		
		jQuery('#textcolor' ).val( '#FFFFFF' );
		UpdatePlayerPreview('textcolor',jQuery('#textcolor').val() );
		jQuery('#textcolor_prev'  ).css( { 'background-color' : '#FFFFFF' } );
		
		jQuery('#buttoncolor' ).val( '#FFFFFF' );
		UpdatePlayerPreview('buttoncolor',jQuery('#buttoncolor').val() );
		jQuery('#buttoncolor_prev'  ).css( { 'background-color' : '#FFFFFF' } );
		
		jQuery('#buttonovercolor' ).val( '#FFFF00' );
		UpdatePlayerPreview('buttonovercolor',jQuery('#buttonovercolor').val() );
		jQuery('#buttonovercolor_prev'  ).css( { 'background-color' : '#FFFF00' } );
		
		jQuery('#showstop' ).val( '0' );
		UpdatePlayerPreview('showstop',jQuery('#showstop').val() );
		jQuery('#showinfo' ).val( '0' );
		UpdatePlayerPreview('showinfo',jQuery('#showinfo').val() );
		jQuery('#showvolume' ).val( '1' );
		UpdatePlayerPreview('showvolume',jQuery('#showvolume').val() );
		
		jQuery('#player_height' ).val( '20' );
		UpdatePlayerPreview('height',jQuery('#player_height').val() );
		
		jQuery('#player_width' ).val( '200' );
		UpdatePlayerPreview('width',jQuery('#player_width').val() );
		
		jQuery('#showloading' ).val( 'autohide' );
		UpdatePlayerPreview('showloading',jQuery('#showloading').val() );
		
		
		jQuery('#slidercolor1').val('#cccccc' );
		UpdatePlayerPreview('slidercolor1',jQuery('#slidercolor1').val() );
		jQuery('#slidercolor1_prev'  ).css( { 'background-color' : '#cccccc' } );
		
		jQuery('#slidercolor2').val('#888888' );
		UpdatePlayerPreview('slidercolor2',jQuery('#slidercolor2').val() );
		jQuery('#slidercolor2_prev'  ).css( { 'background-color' : '#888888' } );
		
		jQuery('#sliderheight' ).val( '10' );
		UpdatePlayerPreview('sliderheight',jQuery('#sliderheight').val() );
		jQuery('#sliderwidth' ).val( '20' );
		UpdatePlayerPreview('sliderwidth',jQuery('#sliderwidth').val() );
		
		jQuery('#loadingcolor' ).val( '#FFFF00' );
		UpdatePlayerPreview('loadingcolor',jQuery('#loadingcolor').val() );
		jQuery('#loadingcolor_prev'  ).css( { 'background-color' : '#FFFF00' } );
		
		jQuery('#bgcolor').val('');
		UpdatePlayerPreview('bgcolor',jQuery('#bgcolor').val() );
		jQuery('#bgcolor_prev'  ).css( { 'background-color' : '' } );
		
		jQuery('#volumeheight' ).val( '6' );
		UpdatePlayerPreview('volumeheight',jQuery('#volumeheight').val() );
		jQuery('#volumewidth' ).val( '30' );
		UpdatePlayerPreview('volumewidth',jQuery('#volumewidth').val() );
		
		jQuery('#sliderovercolor' ).val( '#eeee00' );
		UpdatePlayerPreview('sliderovercolor',jQuery('#sliderovercolor').val() );
		jQuery('#sliderovercolor_prev'  ).css( { 'background-color' : '#eeee00' } );
		
		jQuery('#volume' ).val( '100' );
		UpdatePlayerPreview('volume',jQuery('#volume').val() );
		
		jQuery('#showslider' ).val( '1' );
		UpdatePlayerPreview('showslider',jQuery('#showslider').val() );
		
		jQuery('#buttonwidth' ).val( '26' );
		UpdatePlayerPreview('buttonwidth',jQuery('#buttonwidth').val() );
		
		//update_audio_player();
		generator.updatePlayer();
	}
}

</script>
	<input type="hidden" name="action" value="powerpress-flashmp3-maxi" />
	Configure the Flash Mp3 Maxi Player
<table class="form-table">
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Preview of Player', 'powerpress'); ?> 
		</th>
		<td>
			<div id="player_preview">
<?php 

$content = '<object type="application/x-shockwave-flash" data="'. powerpressplayer_get_root_url().'player_mp3_maxi.swf" width="'. $width.'" height="'. $height .'">'.PHP_EOL;
$content .=  '<param name="movie" value="'. powerpressplayer_get_root_url().'player_mp3_maxi.swf" />'.PHP_EOL;
$content .= $transparency.PHP_EOL;
$content .= $flashvars;
$content .= '</object>'.PHP_EOL;

// print $content;
?>
                        </div>

<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>3rdparty/maxi_player/generator.js"></script>
<input type="hidden" id="gen_mp3" name="gen_mp3" value="<?php echo $Audio['flashmp3-maxi']; ?>" />


		</td>
	</tr>
</table>

<div id="powerpress_settings_page" class="powerpress_tabbed_content" style="position: relative;">
	<div style="position: absolute; top: 6px; right:0px;">
		<a href="#" onclick="audio_player_defaults();return false;"><?php echo __('Set Defaults', 'powerpress'); ?></a>
	</div>
  <ul class="powerpress_settings_tabs"> 
		<li><a href="#tab_general"><span>Basic Settings</span></a></li> 
		<li><a href="#tab_buttons"><span>Button Settings</span></a></li> 
		<li><a href="#tab_volume"><span>Volume Settings</span></a></li>
		<li><a href="#tab_slider"><span>Slider Settings</span></a></li>
  </ul>
	
 <div id="tab_general" class="powerpress_tab">
		<h3><?php echo __('General Settings'); ?></h3>
		<table class="form-table">
        <tr valign="top">
            <td colspan="2">
            
            <?php echo __('leave blank for default values', 'powerpress'); ?>
            </td>
        </tr>
        <tr valign="top">
		<th scope="row">
			<?php echo __('Player Gradient Color Top', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bgcolor1"  name="Player[bgcolor1]" class="color_field" value="<?php echo $PlayerSettings['bgcolor1']; ?>" maxlength="20" />
				<img id="bgcolor1_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bgcolor1']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Player Gradient Color Bottom', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bgcolor2" name="Player[bgcolor2]" class="color_field" value="<?php echo $PlayerSettings['bgcolor2']; ?>" maxlength="20" />
				<img id="bgcolor2_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bgcolor2']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Background Color', 'powerpress'); ?>
                        
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bgcolor" name="Player[bgcolor]" class="color_field" value="<?php echo $PlayerSettings['bgcolor']; ?>" maxlength="20" />
				<img id="bgcolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bgcolor']; ?>;" class="color_preview" />
			</div>
			<small><?php echo __('leave blank for transparent', 'powerpress'); ?></small>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Text Color', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="textcolor" name="Player[textcolor]" class="color_field" value="<?php echo $PlayerSettings['textcolor']; ?>" maxlength="20" />
				<img id="textcolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['textcolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Player Height (in pixels)', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="player_height" name="Player[height]" value="<?php echo $PlayerSettings['height']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Player Width (in pixels)', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="player_width" name="Player[width]" value="<?php echo $PlayerSettings['width']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
</table>
</div>

 <div id="tab_buttons" class="powerpress_tab">
		<h3><?php echo __('Button Settings', 'powerpress'); ?></h3>
		<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php echo __('Button Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="buttoncolor" name="Player[buttoncolor]" class="color_field" value="<?php echo $PlayerSettings['buttoncolor']; ?>" maxlength="20" />
				<img id="buttoncolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['buttoncolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Button Hover Color', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="buttonovercolor" name="Player[buttonovercolor]" class="color_field" value="<?php echo $PlayerSettings['buttonovercolor']; ?>" maxlength="20" />
				<img id="buttonovercolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['buttonovercolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Button Width (in pixels)', 'powerpress'); ?>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="buttonwidth" name="Player[buttonwidth]" value="<?php echo $PlayerSettings['buttonwidth']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Show Stop Button', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showstop" name="Player[showstop]">
<?php
			$options = array( '1'=>__('Yes', 'powerpress'), '0'=>__('No', 'powerpress') );
			powerpress_print_options( $options, $PlayerSettings['showstop']);
?>
                                </select>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Show Info', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showinfo" name="Player[showinfo]">
<?php
			$options = array( '1'=>__('Yes', 'powerpress'), '0'=>__('No', 'powerpress') );
			powerpress_print_options( $options, $PlayerSettings['showinfo']);
?>
                                </select>
			</div>
		</td>
	</tr>
</table>
</div>

 <div id="tab_volume" class="powerpress_tab">
		<h3><?php echo __('Volume Settings', 'powerpress'); ?></h3>
		<table class="form-table">
        
        <tr valign="top">
		<th scope="row">
			<?php echo __('Show Volume', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showvolume" name="Player[showvolume]">
<?php
			$options = array( '1'=>__('Yes', 'powerpress'), '0'=>__('No', 'powerpress') );
			powerpress_print_options( $options, $PlayerSettings['showvolume']);
?>
                                </select>
			</div>
		</td>
	</tr>	
        <tr valign="top">
		<th scope="row">
			<?php echo __('Volume', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="volume" name="Player[volume]">
<?php
			powerpress_print_options( $volume, $PlayerSettings['volume']);
?>
                                </select>
			</div>
		</td>
	</tr>	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Volume Height (in pixels)', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="volumeheight" name="Player[volumeheight]" value="<?php echo $PlayerSettings['volumeheight']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Volume Width (in pixels)', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="volumewidth" name="Player[volumewidth]" value="<?php echo $PlayerSettings['volumewidth']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>

</table>
</div>

 <div id="tab_slider" class="powerpress_tab">
		<h3><?php echo __('Slider Settings', 'powerpress'); ?></h3>
		<table class="form-table">
		
        <tr valign="top">
		<th scope="row">
			<?php echo __('Show Slider', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showslider" name="Player[showslider]">
<?php
			$options = array( '1'=>__('Yes', 'powerpress'), '0'=>__('No', 'powerpress') );
			powerpress_print_options( $options, $PlayerSettings['showslider']);
?>
                                </select>
			</div>
		</td>
	</tr>	
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Slider Color Top', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="slidercolor1" name="Player[slidercolor1]" class="color_field" value="<?php echo $PlayerSettings['slidercolor1']; ?>" maxlength="20" />
				<img id="slidercolor1_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['slidercolor1']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Slider Color Bottom', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="slidercolor2" name="Player[slidercolor2]" class="color_field" value="<?php echo $PlayerSettings['slidercolor2']; ?>" maxlength="20" />
				<img id="slidercolor2_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['slidercolor2']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Slider Hover Color', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="sliderovercolor" name="Player[sliderovercolor]" class="color_field" value="<?php echo $PlayerSettings['sliderovercolor']; ?>" maxlength="20" />
				<img id="sliderovercolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['sliderovercolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Slider Height (in pixels)', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="sliderheight" name="Player[sliderheight]" value="<?php echo $PlayerSettings['sliderheight']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Slider Width (in pixels)', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="sliderwidth" name="Player[sliderwidth]" value="<?php echo $PlayerSettings['sliderwidth']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Show Loading Buffer', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showloading" name="Player[showloading]">
<?php
			powerpress_print_options( $autoload, $PlayerSettings['showloading']);
?>
                                </select>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Loading Buffer Color', 'powerpress'); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="loadingcolor" name="Player[loadingcolor]" class="color_field" value="<?php echo $PlayerSettings['loadingcolor']; ?>" maxlength="20" />
				<img id="loadingcolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['loadingcolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>

</table>
</div> <!-- end tab -->
</div><!-- end tab container -->

<script type="text/javascript">

	generator.player = '<?php echo powerpressplayer_get_root_url(); ?>player_mp3_maxi.swf';
	generator.addParam("gen_mp3", "mp3", "url", '');
	generator.addParam("player_height", "height", "int", "20");
	generator.addParam("player_width", "width", "int", "200");
	generator.addParam("bgcolor1", "bgcolor1", "color", "#7c7c7c");
	generator.addParam("bgcolor2", "bgcolor2", "color", "#333333");
	generator.addParam("bgcolor", "bgcolor", "color", "");
	generator.addParam("textcolor", "textcolor", "color", "#FFFFFF");
	generator.addParam("loadingcolor", "loadingcolor", "color", "#FFFF00");
	generator.addParam("buttoncolor", "buttoncolor", "color", "#FFFFFF");
	generator.addParam("buttonovercolor", "buttonovercolor", "color", "#FFFF00");
	generator.addParam("showloading", "showloading", "text", "autohide");
	generator.addParam("showinfo", "showinfo", "bool", "0");
	generator.addParam("showstop", "showstop", "int", "0");
	generator.addParam("showvolume", "showvolume", "int", "0");
	generator.addParam("buttonwidth", "buttonwidth", "int", "26");
	generator.addParam("volume", "volume", "int", "100");
	generator.addParam("volumeheight", "volumeheight", "int", "6");
	generator.addParam("volumewidth", "volumewidth", "int", "30");
	generator.addParam("sliderovercolor", "sliderovercolor", "color", "#eeee00");
	generator.addParam("showslider", "showslider", "bool", "1");
	generator.addParam("slidercolor1", "slidercolor1", "color", "#cccccc");
	generator.addParam("slidercolor2", "slidercolor2", "color", "#888888");
	generator.addParam("sliderheight", "sliderheight", "int", "10");
	generator.addParam("sliderwidth", "sliderwidth", "int", "20");
	
	generator.updatePlayer();
</script>

<?php
			}; break;
			
			case 'audioplay': {
				$PlayerSettings = powerpress_get_settings('powerpress_audioplay');
                                if($PlayerSettings == "") {
                                    $PlayerSettings = array(
                                    'bgcolor' => '',
                                    'buttondir' => 'negative',
                                    'mode' => 'playpause'
                                    );
                                }
                                
                                // Set standard variables for player
                                $flashvars = 'file='. $Audio['audioplay'];
                                $flashvars .= '&amp;repeat=1';
                                
                                if($PlayerSettings['bgcolor'] == ""){
                                    $flashvars .= "&amp;usebgcolor=no";
                                    $transparency = '<param name="wmode" value="transparent" />';
                                    $htmlbg = "";
                                }
                                else{
                                    $flashvars .= "&amp;bgcolor=". preg_replace('/\#/','0x',$PlayerSettings['bgcolor']);
                                    $transparency = '<param name="bgcolor" value="'. $PlayerSettings['bgcolor']. '" />';
                                    $htmlbg = 'bgcolor="'. $PlayerSettings['bgcolor'].'"';

                                }
                                
                                if($PlayerSettings['buttondir'] == "") {
                                    $flashvars .= "&amp;buttondir=".powerpressplayer_get_root_url()."buttons/negative";
                                }else{
                                    $flashvars .= "&amp;buttondir=".powerpressplayer_get_root_url().'buttons/'.$PlayerSettings['buttondir'];
                                    
                                }
																
																$width = $height = (strstr($PlayerSettings['buttondir'], 'small')===false?30:15);
                                
                                $flashvars .= '&amp;mode='. $PlayerSettings['mode'];
                                
?>
        	<input type="hidden" name="action" value="powerpress-audioplay" />
	<?php echo __('Configure the AudioPlay Player', 'powerpress'); ?><br clear="all" />

<table class="form-table">
	
	<tr valign="top">
		<th scope="row">
			<?php echo __('Preview of Player', 'powerpress'); ?> 
		</th>
		<td colspan="2">
			<div id="player_preview">
                        
<?php                                                                         
$content = '<object type="application/x-shockwave-flash" width="'. $width .'" height="'. $height .'" data="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'">'.PHP_EOL;
$content .= '<param name="movie" value="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'" />'.PHP_EOL;
$content .= '<param name="quality" value="high" />'.PHP_EOL;
$content .= $transparency.PHP_EOL;
$content .= '<param name="FlashVars" value="'.$flashvars.'" />'.PHP_EOL;
$content .= '<embed src="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'" quality="high"  width="30" height="30" type="application/x-shockwave-flash">'.PHP_EOL;
$content .= "</embed>\n		</object>\n";

print $content;
?>
                </div>
            </td>
        </tr>
</table>
				
		<h2><?php echo __('General Settings', 'powerpress'); ?></h2>
	<table class="form-table">
        <tr valign="top">
		<th scope="row">
			<?php echo __('Background Color', 'powerpress'); ?>
                        
		</th>
		<td valign="top">
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bgcolor" name="Player[bgcolor]" class="color_field" value="<?php echo $PlayerSettings['bgcolor']; ?>" maxlength="20" />
				<img id="bgcolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bgcolor']; ?>;" class="color_preview" />
			</div>
			<small><?php echo __('leave blank for transparent', 'powerpress'); ?></small>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Player Mode', 'powerpress'); ?>
		</th>
		<td valign="top">
			<div class="color_control">
                            <select name="Player[mode]" id="mode">
<?php
			$options = array( 'playpause'=>__('Play/Pause', 'powerpress'), 'playstop'=>__('Play/Stop', 'powerpress') );
			powerpress_print_options( $options, $PlayerSettings['mode']);
?>     
                            </select>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php echo __('Player Button', 'powerpress'); ?>
		</th>
		<td valign="top">
			<div class="color_control">
                        <table cellpadding="0" cellspacing="0">
                                <?php $options = array('classic','classic_small','negative','negative_small');
                                 foreach($options as $option){
                                        if($PlayerSettings['buttondir'] == $option):
                                            $selected = " CHECKED";
                                        else:
                                            $selected = "";
                                        endif;
                                        if(($option == "classic") || ($option == "classic_small")){
                                            $td = '<td style="background: #999;" align="center">';
                                            $warning = "(ideal for dark backgrounds)";
                                            if($option == "classic_small") {
                                                $name = __('Small White', 'powerpress');
                                            }else{
                                                $name = __('Large White', 'powerpress');
                                            }
                                        }
                                        else {
                                            $td = '<td align="center">';
                                            $warning = "";
                                            if($option == "negative_small") {
                                                $name = __('Small Black', 'powerpress');
                                            }else{
                                                $name = __('Large Black', 'powerpress');
                                            }

                                        }
                                        echo '<tr><td><input type="radio" name="Player[buttondir]" value="'. $option .'"'. $selected .' /></td>'.$td.'<img src="'. powerpressplayer_get_root_url().'buttons/'.$option.'/playup.png" /></td><td>'.$name.' Button '.$warning.'</td></tr>';
                                }?>
                                
                            </table>
			</div>
		</td>
	</tr>

</table>
<?php
			}; break;
		
			default: {
			
?>

<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php echo __('Preview of Player', 'powerpress'); ?> 
		</th>
		<td>
			<p>
<?php
			$media_url = '';
			$content = '';
			$content .= '<div id="flow_player_classic"></div>'.PHP_EOL;
			$content .= '<script type="text/javascript">'.PHP_EOL;
			$content .= "pp_flashembed(\n";
			$content .= "	'flow_player_classic',\n";
			$content .= "	{src: '". powerpress_get_root_url() ."FlowPlayerClassic.swf', width: 320, height: 24 },\n";
			$content .= "	{config: { autoPlay: false, autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '{$Audio['default']}', loop: false, autoRewind: true } }\n";
			$content .= ");\n";
			$content .= "</script>\n";
			echo $content;
?>
			</p>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			&nbsp;
		</th>
		<td>
			<p><?php echo __('Flow Player Classic has no additional settings.', 'powerpress'); ?></p>
		</td>
	</tr>
</table>


<?php
			} break;
		}
?>

<?php
	}
}

?>