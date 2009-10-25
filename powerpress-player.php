<?php
/*
PowerPress player options
*/

// Let PowerPress know that the powerpress_player filter event will be handeled
if( !defined('POWERPRESS_PLAYER') )
	define('POWERPRESS_PLAYER', true);

if( !defined('PHP_EOL') )
	define('PHP_EOL', "\n"); // We need this variable defined for new lines.

function powerpressplayer_get_root_url()
{
	return WP_PLUGIN_URL . '/'. basename( dirname(__FILE__) ) .'/';
}

function powerpressplayer_filter($content, $media_url, $ExtraData = array())
{
	$Settings = get_option('powerpress_general');
	// Check if we are using a custom flash player...
	if( !isset($Settings['player']) || $Settings['player'] == 'default' ) // Either the default player is selected or the user never selected a player
		return $content;
		
	// Next check that we're working with an mp3
	$parts = pathinfo($media_url);
	if( $parts['extension'] != 'mp3' && $EpisdoeData['type'] != 'audio/mpeg' ) // we just need condition one to be false
		return $content; // We're apparently not working with an mp3
		
	$player_content = powerpressplayer_build( $media_url, $Settings, $ExtraData );
	return $content . $player_content;
}

function powerpressplayer_build($media_url, $Settings, $ExtraData = array())
{
	global $g_powerpress_player_id; // Use the global unique player id variable for the surrounding div
	if( !isset($g_powerpress_player_id) )
		$g_powerpress_player_id = rand(0, 10000);
	$g_powerpress_player_id++; // increment the player id for the next div so it is unique
	$content = '';
	$autoplay = false;
	if( isset($ExtraData['autoplay']) && $ExtraData['autoplay'] )
		$autoplay = true; // TODO: We need to handle this
	
	switch( $Settings['player'] )
	{
		case 'audio-player': {
		
			$PlayerSettings = get_option('powerpress_audio-player');

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
																'noinfo'=>'yes',
                                'rtl' => 'no'
                                );
                        endif;
												
												//$PlayerSettings['noinfo'] = 'yes';
												if( $PlayerSettings['titles'] == '' )
													$PlayerSettings['titles'] = 'Blubrry PowerPress';
												
                             $keys = array_keys($PlayerSettings);
                                $flashvars ='';
                            foreach ($keys as $key) {
                                if($PlayerSettings[$key] != "") {                        
                                    $flashvars .= '&amp;'. $key .'='. preg_replace('/\#/','',$PlayerSettings[$key]);
                                }
                                }
																
														if( $autoplay )
														{
															$flashvars .= '&amp;autostart=yes';
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
                        // TODO: Add audio-player player here
			$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">';
			//$content .= '<script language="JavaScript" src="'.powerpressplayer_get_root_url().'audio-player.js"></script>'.PHP_EOL;
                        $content .= '<object type="application/x-shockwave-flash" data="'.powerpressplayer_get_root_url().'audio-player.swf" id="'.$g_powerpress_player_id.'" height="24" width="290">'.PHP_EOL;
                        $content .= '<param name="movie" value="'.powerpressplayer_get_root_url().'/audio-player.swf" />'.PHP_EOL;
                        $content .= '<param name="FlashVars" value="playerID='.$g_powerpress_player_id.'&amp;soundFile='.$media_url.$flashvars.'" />'.PHP_EOL;
                        $content .= '<param name="quality" value="high" />'.PHP_EOL;
                        $content .= '<param name="menu" value="false" />'.PHP_EOL;
                        $content .= '<param name="wmode" value="transparent" />'.PHP_EOL;
                        $content .= '</object>'.PHP_EOL;
			$content .= '</div>'.PHP_EOL;
			
		}; break;
		case 'flashmp3-maxi': {
		
			$PlayerSettings = get_option('powerpress_flashmp3-maxi');
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
                            $flashvars .= "mp3=" . $media_url;
														if( $autoplay ) 
															$flashvars .= '&amp;autoplay=1';

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
                            if($PlayerSettings['showloading'] != "never") {
                                if($PlayerSettings['loadingcolor'] != "") {
                                    $flashvars .= '&amp;loadingcolor='. preg_replace('/\#/','',$PlayerSettings['loadingcolor']);
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

                            
                            
			// TODO: Add flashmp3-maxi player here
$content = '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">'.PHP_EOL;
$content .= '<object type="application/x-shockwave-flash" data="'. powerpressplayer_get_root_url().'player_mp3_maxi.swf" id="'.$g_powerpress_player_id.'" width="'. $width.'" height="'. $height .'">'.PHP_EOL;
$content .=  '<param name="movie" value="'. powerpressplayer_get_root_url().'player_mp3_maxi.swf" />'.PHP_EOL;
$content .= $transparency.PHP_EOL;
$content .= $flashvars;
$content .= '</object>'.PHP_EOL;
$content .= '</div>'.PHP_EOL;

			
		}; break;
		case 'audioplay' : {
			
			$PlayerSettings = get_option('powerpress_audioplay');
                                if($PlayerSettings == "") {
                                    $PlayerSettings = array(
                                    'bgcolor' => '',
                                    'buttondir' => 'negative',
                                    'mode' => 'playpause'
                                    );
                                }
																
																$width = $height = (strstr($PlayerSettings['buttondir'], 'small')===false?30:15);
                                
                                // Set standard variables for player
                                $flashvars = 'file=http://';
                                $flashvars .= '&amp;repeat=1';
																if( $autoplay )
																	$flashvars .= '&amp;auto=yes';
                                
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
                                
                                $flashvars .= '&amp;mode='. $PlayerSettings['mode'];
  
			// TODO: Add audioplay player here
$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">';
$content .= '<object type="application/x-shockwave-flash" width="'. $width .'" height="'. $height .'" id="'.$g_powerpress_player_id.'" data="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'">'.PHP_EOL;
$content .= '<param name="movie" value="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'" />'.PHP_EOL;
$content .= '<param name="quality" value="high" />'.PHP_EOL;
$content .= $transparency.PHP_EOL;
$content .= '<param name="FlashVars" value="'.$flashvars.'" />'.PHP_EOL;
$content .= '<embed src="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'" quality="high"  width="30" height="30" type="application/x-shockwave-flash">'.PHP_EOL;
$content .= "</embed>\n		</object>\n";
$content .= "</div>\n";
			
		}; break;
                case 'simple_flash' : {
$content .= '<div class="powerpress_player" id="powerpress_player_'. $g_powerpress_player_id .'">';
$content .= '<object type="application/x-shockwave-flash" data="'. powerpressplayer_get_root_url() .'simple_mp3.swf" id="'.$g_powerpress_player_id.'" width="150" height="50">';
$content .= '<param name="movie" value="'. powerpressplayer_get_root_url().'simple_mp3.swf" />';
$content .= '<param name="wmode" value="transparent" />';
$content .= '<param name="FlashVars" value="'. get_bloginfo('home') .'?url='. $media_url.'&amp;autostart='. ($autostart?'true':'false') .'" />';
$content .= '<param name="quality" value="high" />';
$content .= '<embed wmode="transparent" src="'. get_bloginfo('home') .'?url='.$media_url.'&amp;autostart='. ($autostart?'true':'false') .'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="150" height="50"></embed>';
$content .= '</object>';
$content .= "</div>\n";
                }; break;
		// Let all other cases fall through...
	}

	return $content;
}

// Hook into the powerprss_player filter
add_filter('powerpress_player', 'powerpressplayer_filter', 10, 3);

//if( is_admin() )
//	require_once(dirname(__FILE__).'/powerpressadmin-player.php');

?>