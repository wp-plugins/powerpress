/** 
 * jsMediaPlayer 1.1.1 for Blubrry PowerPress
 * 
 * http://www.blubrry.com/powepress/
 *
 * Copyright (c) 2008 Angelo Mandato (angelo [at] mandato {period} com)
 *
 * Released under Aoache 2 license:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * versoin 1.1.3 - 03/23/2009 - Added code to support FlowPlayer v3.
 * versoin 1.1.2 - 03/04/2009 - Added options to set the width for audio, width and height for video.
 * versoin 1.1.1 - 12/22/20008 - Minor change to support Windows Media in Firefox. Includes link to preferred Firefox Windows Media Player plugin.
 * versoin 1.1.0 - 11/25/20008 - Major re-write, object now stored in this include file, auto play is no longer a member variable and is determined by function call.
 * version 1.0.3 - 11/02/2008 - Added option for playing quicktime files in an intermediate fashion with an image to click to play.
 * version 1.0.2 - 07/26/2008 - Fixed pop up player bug caused by v 1.0.1
 * version 1.0.1 - 07/28/2008 - fixed flow player looping playback, flash player no longer loops.
 * version 1.0.0 - 07/26/2008 - initial release
 */

var g_bpPlayer = false;

/**
	Initialize function for javascript based player
	
	@PluginURL - where plugin files are located
	@QuicktimeImage - image displayed in place of quicktime media types
	@OnlyOnePlayer - pass 'true' if only one player should appear in the current page.
*/
function powerpress_player_init(PluginURL, QuicktimeImage)
{
	var FlowPlayerVer = 2;
	if( powerpress_player_init.arguments.length > 3 )
		FlowPlayerVer = powerpress_player_init.arguments[3];
	
	if( g_bpPlayer == false && FlowPlayerVer == 3 )
	{
		g_bpPlayer = new jsMediaPlayer(PluginURL +'flowplayer-3.0.7.swf');
		g_bpPlayer.FlashSrcPlugin( PluginURL + 'flowplayer.audio-3.0.4.swf');
	}
	else
	{
		g_bpPlayer = new jsMediaPlayer(PluginURL +'FlowPlayerClassic.swf');
	}

	g_bpPlayer.PlayImage(QuicktimeImage);
	if( powerpress_player_init.arguments.length > 2 )
		g_bpPlayer.OnePlayerOnly(powerpress_player_init.arguments[2]);
}

/**
	Initialize function for javascript based player
	
	@Width - width of player
	@Height - height of player
	@WidthAudio - width of player (mp3 audio only)
*/
function powerpress_player_size(Width, Height, WidthAudio)
{
	if( g_bpPlayer )
	{
		if( Width >= 100 )
			g_bpPlayer.SetWidth(Width);
		if( Height >= 24 )
			g_bpPlayer.SetHeight(Height);
		if( WidthAudio >= 100 )
			g_bpPlayer.SetWidthAudio(WidthAudio);
	}
}

/**
	play media in page function
	
	@MediaURL - complete url to media file
	@PlayerDiv - Destiniation div id for player
	@AutoPlay - Automatically start playing media file
*/
function powerpress_play_page()
{
	if( !g_bpPlayer )
		return true; // Let the link handle itself
	
	var media_url = powerpress_play_page.arguments[0];
	var player_div = powerpress_play_page.arguments[1];
	var auto_play = false;
	if( powerpress_play_page.arguments.length > 2 && powerpress_play_page.arguments[2] )
		auto_play = true;
	
	return g_bpPlayer.PlayInPage(media_url, player_div, auto_play);
}

/**
	play media in new window function
	
	@MediaURL - complete url to media file
	@AutoPlay - Automatically start playing media file
*/
function powerpress_play_window()
{
	if( !g_bpPlayer )
		return true; // Let the link handle itself
	
	var media_url = powerpress_play_window.arguments[0];
	var auto_play = true; // Always auto play new window plays
	
	return g_bpPlayer.PlayNewWindow(media_url);
}

function jsMediaPlayer(FlashSrc) {
	// Member variables
	this.m_flash_src = FlashSrc;
	this.m_width = 320;
	this.m_height = 240;
	this.m_widthAudio = 320;
	this.m_player_div = false;
	this.m_player_wnd = false;
	this.m_one_player_only = false;
	this.m_media_url = false;
	this.m_play_image = false;
	this.m_FlowPlayerVer = 2;
	this.m_flash_src_plugin = '';
	
	
	this.FlashSrc=function(Src) {
		this.m_flash_src = Src;
	}
	
	this.SetWidth=function(Width) {
		this.m_width = Width;
	}
	
	this.SetHeight=function(Height) {
		this.m_height = Height;
	}
	
	this.SetWidthAudio=function(Width) {
		this.m_widthAudio = Width;
	}
	
	this.OnePlayerOnly=function(Setting) {
		this.m_one_player_only = Setting;
	}
	
	this.FlashSrcPlugin=function(Src) {
		this.m_FlowPlayerVer = (Src==''?2:3);
		this.m_flash_src_plugin = Src;
	}
	
	this.PlayImage=function(URL) {
		this.m_play_image = URL;
	}
	
	this.PlayInPage = function() {
		
		// Check if we should even use javascript based player
		if( this._passthru() )
			return true;
			
		// Make sure we're not already playing this div...
		if( this.m_player_div == this.PlayInPage.arguments[1] )
			return false;
		
		// Close the last opened player
		if( this.m_one_player_only )
			this._closePrevPlayer();
		
		// Set the proeprties:
		this.m_media_url = this.PlayInPage.arguments[0];
		this.m_player_div = this.PlayInPage.arguments[1];
		var auto_play = false;
		if( this.PlayInPage.arguments.length > 2 && this.PlayInPage.arguments[2] )
			auto_play = true;
		
		var ext = this._getExt(this.m_media_url);
		switch( ext )
		{
			case 'm4v':
			case 'm4a':
			case 'avi':
			case 'mpg':
			case 'mpeg':
			case 'mp4':
			case 'qt':
			case 'mov': {
				
				if( this.m_play_image && auto_play == false )
				{
					document.getElementById( this.m_player_div ).innerHTML = '<a href="'+ this.m_media_url +'" onclick="return powerpress_play_page(\''+ this.m_media_url +'\', \''+ this.m_player_div +'\',\'true\');" title="Play on page"><img src="'+ this.m_play_image +'" alt="Play on page" /></a>';
					this.m_player_div = false; // Let this player be used again on the page
					return false;
				}
				
				var contentType = 'video/mpeg'; // Default content type
				if( ext == 'm4v' )
					contentType = 'video/x-m4v';
				else if( ext == 'm4a' )
					contentType = 'audio/x-m4a';
				else if( ext == 'avi' )
					contentType = 'video/avi';
				else if( ext == 'qt' || ext == 'mov' )
					contentType = 'video/quicktime';

				document.getElementById( this.m_player_div ).innerHTML = this._getQuickTime(contentType, auto_play);
			}; break;
			case 'wma':
			case 'wmv':
			case 'asf': {
			
				if( navigator.userAgent.indexOf("Firefox") !=-1 && this.m_play_image && auto_play == false )
				{
					document.getElementById( this.m_player_div ).innerHTML = '<a href="'+ this.m_media_url +'" onclick="return powerpress_play_page(\''+ this.m_media_url +'\', \''+ this.m_player_div +'\',\'true\');" title="Play on page"><img src="'+ this.m_play_image +'" alt="Play on page" /></a>';
					this.m_player_div = false; // Let this player be used again on the page
					return false;
				}
				
				if( navigator.userAgent.indexOf("Firefox") !=-1 ) // Firefox:
					document.getElementById( this.m_player_div ).innerHTML = this._getWinPlayerFirefox(auto_play);
				else
					document.getElementById( this.m_player_div ).innerHTML = this._getWinPlayer(auto_play);
			}; break;
			case 'rm': {
				document.getElementById( this.m_player_div ).innerHTML = this._getRealPlayer(auto_play);
			}
			case 'swf': {
				document.getElementById( this.m_player_div ).innerHTML = this._getFlash(auto_play);
			}
			case 'flv': {
				this._doFlowPlayer(0, auto_play);
			}; break;
			case 'mp3': {
				this._doFlowPlayer(24, auto_play);
				
			}; break;
			default: {
				return true; // We didn't handle this, so lets let the click to the media handle itself.
			};
		}

		// Display the div
		document.getElementById( this.m_player_div ).style.display = 'block';
		return false; // Don't let the href go
	}
	
	this.PlayNewWindow=function() {
		
		// Check if we should even use javascript based player
		if( this._passthru() )
			return true;
			
		if( this.m_one_player_only )
			this._closePrevPlayer();

		// Get the media file and extension
		this.m_media_url = this.PlayNewWindow.arguments[0];
		var ext = this._getExt(this.m_media_url);

		// Calculate the window height
		height = this.m_height;
		if( ext == 'mp3' )
		{
			height = 24;
			// Adjust the height for Opera web browser, only needed for mp3s
			if( navigator.userAgent.indexOf("Opera") != -1 )
				height += 40;
		}
		else
			height += 40; // Add area for menu navigation
		
		this.m_player_wnd = window.open(null,"jsPlayer", 'toolbar=0,status=0,resizable=1,width='+ (this.m_width +40).toString() +',height='+ height.toString() )
		var Html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		Html += '<html xmlns="http://www.w3.org/1999/xhtml">';
		Html += '<head>';
		Html += '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		Html += '<title>Media Player</title>';
		if( ext == 'mp3' || ext == 'flv' || ext == 'mp4' )
		{
			Html += '<script type="text/javascript">\n';
			Html += 'function flashembed(root,userParams,flashvars){function getHTML(){var html="";if(typeof flashvars==\'function\'){flashvars=flashvars();}if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){html=\'<embed type="application/x-shockwave-flash" \';if(params.id){extend(params,{name:params.id});}for(var key in params){if(params[key]!==null){html+=[key]+\'="\'+params[key]+\'"\\n\\t\';}}if(flashvars){html+=\'flashvars=\\\'\'+concatVars(flashvars)+\'\\\'\';}html+=\'/>\';}else{html=\'<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" \';html+=\'width="\'+params.width+\'" height="\'+params.height+\'"\';if(!params.id&&document.all){params.id="_"+(""+Math.random()).substring(5);}if(params.id){html+=\' id="\'+params.id+\'"\';}html+=\'>\';html+=\'\\n\\t<param name="movie" value="\'+params.src+\'" />\';params.id=params.src=params.width=params.height=null;for(var k in params){if(params[k]!==null){html+=\'\\n\\t<param name="\'+k+\'" value="\'+params[k]+\'" />\';}}if(flashvars){html+=\'\\n\\t<param name="flashvars" value=\\\'\'+concatVars(flashvars)+\'\\\' />\';}html+="</object>";if(debug){alert(html);}}return html;}function init(name){var timer=setInterval(function(){var doc=document;var el=doc.getElementById(name);if(el){flashembed(el,userParams,flashvars);clearInterval(timer);}else if(doc&&doc.getElementsByTagName&&doc.getElementById&&doc.body){clearInterval(timer);}},13);return true;}function extend(to,from){if(from){for(key in from){if(from.hasOwnProperty(key)){to[key]=from[key];}}}}var params={src:\'#\',width:\'100%\',height:\'100%\',version:null,onFail:null,expressInstall:null,debug:false,bgcolor:\'#ffffff\',allowfullscreen:true,allowscriptaccess:\'always\',quality:\'high\',type:\'application/x-shockwave-flash\',pluginspage:\'http://www.adobe.com/go/getflashplayer\'};if(typeof userParams==\'string\'){userParams={src:userParams};}extend(params,userParams);var version=flashembed.getVersion();var required=params.version;var express=params.expressInstall;var debug=params.debug;if(typeof root==\'string\'){var el=document.getElementById(root);if(el){root=el;}else{return init(root);}}if(!root){return;}if(!required||flashembed.isSupported(required)){params.onFail=params.version=params.expressInstall=params.debug=null;root.innerHTML=getHTML();return root.firstChild;}else if(params.onFail){var ret=params.onFail.call(params,flashembed.getVersion(),flashvars);if(ret){root.innerHTML=ret;}}else if(required&&express&&flashembed.isSupported([6,65])){extend(params,{src:express});flashvars={MMredirectURL:location.href,MMplayerType:\'PlugIn\',MMdoctitle:document.title};root.innerHTML=getHTML();}else{if(root.innerHTML.replace(/\\s/g,\'\')!==\'\'){}else{root.innerHTML="<h2>Flash version "+required+" or greater is required</h2>"+"<h3>"+(version[0]>0?"Your version is "+version:"You have no flash plugin installed")+"</h3>"+"<p>Download latest version from <a href=\'"+params.pluginspage+"\'>here</a></p>";}}function concatVars(vars){var out="";for(var key in vars){if(vars[key]){out+=[key]+\'=\'+asString(vars[key])+\'&\';}}return out.substring(0,out.length-1);}function asString(obj){switch(typeOf(obj)){case\'string\':return\'"\'+obj.replace(new RegExp(\'(["\\\\\\\\])\',\'g\'),\'\\\\$1\')+\'"\';case\'array\':return\'[\'+map(obj,function(el){return asString(el);}).join(\',\')+\']\';case\'function\':return\'"function()"\';case\'object\':var str=[];for(var prop in obj){if(obj.hasOwnProperty(prop)){str.push(\'"\'+prop+\'":\'+asString(obj[prop]));}}return\'{\'+str.join(\',\')+\'}\';}return String(obj).replace(/\\s/g," ").replace(/\\\'/g,"\\"");}function typeOf(obj){if(obj===null||obj===undefined){return false;}var type=typeof obj;return(type==\'object\'&&obj.push)?\'array\':type;}if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};});}function map(arr,func){var newArr=[];for(var i in arr){if(arr.hasOwnProperty(i)){newArr[i]=func(arr[i]);}}return newArr;}return root;}if(typeof jQuery==\'function\'){(function($){$.fn.extend({flashembed:function(params,flashvars){return this.each(function(){flashembed(this,params,flashvars);});}});})(jQuery);}flashembed=flashembed||{};flashembed.getVersion=function(){var version=[0,0];if(navigator.plugins&&typeof navigator.plugins["Shockwave Flash"]=="object"){var _d=navigator.plugins["Shockwave Flash"].description;if(typeof _d!="undefined"){_d=_d.replace(/^.*\\s+(\\S+\\s+\\S+$)/,"$1");var _m=parseInt(_d.replace(/^(.*)\\..*$/,"$1"),10);var _r=/r/.test(_d)?parseInt(_d.replace(/^.*r(.*)$/,"$1"),10):0;version=[_m,_r];}}else if(window.ActiveXObject){try{var _a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{_a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");version=[6,0];_a.AllowScriptAccess="always";}catch(ee){if(version[0]==6){return;}}try{_a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(eee){}}if(typeof _a=="object"){_d=_a.GetVariable("$version");if(typeof _d!="undefined"){_d=_d.replace(/^\\S+\\s+(.*)$/,"$1").split(",");version=[parseInt(_d[0],10),parseInt(_d[2],10)];}}}return version;};flashembed.isSupported=function(version){var now=flashembed.getVersion();var ret=(now[0]>version[0])||(now[0]==version[0]&&now[1]>=version[1]);return ret;};\n';
			Html += '</script>\n';
		}
		Html += '</head>';
		Html += '<body>';
		
		Html += '<div id="player" style="margin-top: 20px; margin-left: 10px;">';
		if( ext != 'mp3' && ext != 'flv' && ext != 'mp4' )
		{
			switch( ext )
			{
				case 'm4v':
				case 'm4a':
				case 'avi':
				case 'mpg':
				case 'mpeg':
				case 'qt':
				case 'mov': {
					
					var contentType = 'video/mpeg'; // Default content type
					if( ext == 'm4v' )
						contentType = 'video/x-m4v';
					else if( ext == 'm4a' )
						contentType = 'audio/x-m4a';
					else if( ext == 'avi' )
						contentType = 'video/avi';
					else if( ext == 'qt' || ext == 'mov' )
						contentType = 'video/quicktime';
					
					Html += this._getQuickTime(contentType, true);
				}; break;
				case 'wma':
				case 'wmv':
				case 'asf': {
				
					if( navigator.userAgent.indexOf("Firefox") !=-1 ) // Firefox:
					{
						Html += '<style>body { font-family: Arial, Helvetica, Sans-Serif; font-size: 90%;}</style>';
						Html += this._getWinPlayerFirefox(true);
					}
					else
					{
						Html += this._getWinPlayer(true);
					}
				}; break;
				case 'rm': {
					Html += this._getRealPlayer(true);
				}; break;
				case 'swf': {
					Html += this._getFlash(true);
				}; break;
			}
		}
		Html += '</div>';
		if( ext == 'mp3' || ext == 'flv' || ext == 'mp4' )
		{
			Html += '<script type="text/javascript">\n';
			if( ext == 'mp3' )
				Html += this._getFlowPlayer('player', 24, true);
			else
				Html += this._getFlowPlayer('player', this.m_height, true);
			Html += '</script>\n';
		}
		Html += '</body>';
		Html += '</html>';
		this.m_player_wnd.document.write( Html );
		this.m_player_wnd.document.close();
		this.m_player_wnd.focus();
		return false;
	}

	/*
	Private functions:
	*/
	this._doFlowPlayer = function() {
		
		var height = this.m_height;
		var width = this.m_width;
		var auto_play = false;
		if( this._doFlowPlayer.arguments.length > 0 && this._doFlowPlayer.arguments[0] > 0 )
			height = this._doFlowPlayer.arguments[0];
		if( this._doFlowPlayer.arguments.length > 1 )
			auto_play = this._doFlowPlayer.arguments[1];
		if( height == 24 )
			width = this.m_widthAudio;
		if( this.m_FlowPlayerVer == 3 )
		{
			flashembed(
				this.m_player_div,
				{src: this.m_flash_src, width: width, height: height },
				{
					config: {	
						plugins: { 
							controls: {
								borderRadius: '0',
								durationColor: '#000000',
								timeColor: '#ECECEC',
								sliderColor: '#6F6F6F',
								sliderGradient: 'low',
								progressColor: '#6F6F6F',
								progressGradient: 'low',
								bufferColor: '#CCCCCC',
								bufferGradient: 'low',
								buttonColor: '#6F6F6F',
								buttonOverColor: '#7F7F7F',
								backgroundColor: '#9F9F9F',
								backgroundGradient: 'low',
								fullscreen: false,
								opacity:1.0
						 },
							audio: { 
								url: this.m_flash_src_plugin
							} 
						}, 
						clip: { 
							autoPlay: false, 
							autoBuffering: false, 
							url: this.m_media_url
						}
					} 
				}
			);
				// {config: { autoPlay: auto_play?true:false, autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: this.m_media_url, loop: false, autoRewind: true } }
		}
		else
		{
			flashembed(
				this.m_player_div,
				{src: this.m_flash_src, width: width, height: height },
				{config: { autoPlay: auto_play?true:false, autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: this.m_media_url, loop: false, autoRewind: true } }
			);
		}
		
		return false;
	}
	
	this._getFlowPlayer = function(destDiv) {
		
		var height = this.m_height;
		if( this._getFlowPlayer.arguments.length > 1 )
			height = this._getFlowPlayer.arguments[1];
			
		var width = this.m_width;
		if( height == 24 ) // Player height
			width = this.m_widthAudio;
		
		var auto_play = false;
		if( this._getFlowPlayer.arguments.length > 2 )
			auto_play = this._getFlowPlayer.arguments[2];
			
		var Html = '';
		Html += "flashembed(\n";
		Html += "		'"+ destDiv +"', \n";
		Html += "		{src: '"+ this.m_flash_src +"', width: "+ width +", height: "+ height +"}, \n";
		Html += "		{config: { autoPlay: "+ (auto_play?'true':'false') +", duration: 633, autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '"+ this.m_media_url +"', loop: false, autoRewind: true } } \n";
		Html += "	); \n";
		return Html;
	}
	
	this._getFlash = function() {
		var auto_play = false;
		if( this._getFlash.arguments.length > 0 )
			auto_play = this._getFlash.arguments[0];
			
		var Html = '';
		Html += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0"'+ (auto_play?'':' play="false"') +' width="'+ this.m_width +'" height="'+ this.m_height +'" menu="true">\n';
		Html += '	<param name="movie" value="'+ this.m_media_url +'" />\n';
		Html += '	<param name="quality" value="high" />\n';
		Html += '	<param name="menu" value="true" />\n';
		Html += '	<param name="scale" value="noorder" />\n';
		Html += '	<param name="quality" value="high" />\n';
		Html += '	<embed src="'+ this.m_media_url +'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"'+ (auto_play?'':' play="false"') +' width="'+ this.m_width +'" height="'+ this.m_height +'" menu="true"></embed>';
		Html += '</object>\n';
		return Html;
	}
	
	this._getRealPlayer = function() {
		var auto_play = false;
		if( this._getRealPlayer.arguments.length > 0 )
			auto_play = this._getRealPlayer.arguments[0];
			
		var Html = '';
		Html += '<object id="realplayer" classid="clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa" width="'+ this.m_width +'" height="'+ this.m_height +'">\n';
		Html += '	<param name="src" value="'+ this.m_media_url +'" />\n';
		Html += '	<param name="autostart" value="'+ (auto_play?'true':'false') +'" />\n';
		Html += '	<param name="controls" value="imagewindow,controlpanel" />\n';
		Html += '	<embed src="'+ this.m_media_url +'" width="'+ this.m_width +'" height="'+ this.m_height +'" autostart="'+(auto_play?'true':'false')+'" controls="imagewindow,controlpanel" type="audio/x-pn-realaudio-plugin"></embed>';
		Html += '</object>\n';
		return Html;
	}
	
	this._getWinPlayer = function() {
		var auto_play = false;
		if( this._getWinPlayer.arguments.length > 0 )
			auto_play = this._getWinPlayer.arguments[0];
			
		var Html = '';
		Html += '<object id="winplayer" classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'+ this.m_width +'" height="'+ this.m_height +'" standby="Media is loading..." type="application/x-oleobject">\n';
		Html += '	<param name="url" value="'+ this.m_media_url +'" />\n';
		Html += '	<param name="AutoStart" value="'+ (auto_play?'true':'false') +'" />\n';
		Html += '	<param name="AutoSize" value="true" />\n';
		Html += '	<param name="AllowChangeDisplaySize" value="true" />\n';
		Html += '	<param name="standby" value="Media is loading..." />\n';
		Html += '	<param name="AnimationAtStart" value="true" />\n';
		Html += '	<param name="scale" value="aspect" />\n';
		Html += '	<param name="ShowControls" value="true" />\n';
		Html += '	<param name="ShowCaptioning" value="false" />\n';
		Html += '	<param name="ShowDisplay" value="false" />\n';
		Html += '	<param name="ShowStatusBar" value="false" />\n';
		Html += '	<embed type="application/x-mplayer2" src="'+ this.m_media_url +'" width="'+ this.m_width +'" height="'+ this.m_height +'" scale="aspect" AutoStart="'+ (auto_play?'true':'false') +'" ShowDisplay="0" ShowStatusBar="0" AutoSize="1" AnimationAtStart="1" AllowChangeDisplaySize="1" ShowControls="1"></embed>\n';
		Html += '</object>\n';
		return Html;
	}
	
	this._getWinPlayerFirefox = function() {
		var auto_play = false;
		if( this._getWinPlayerFirefox.arguments.length > 0 )
			auto_play = this._getWinPlayerFirefox.arguments[0];

		var Html = '';
		Html += '<object id="winplayer" data="' + this.m_media_url +'" width="'+ this.m_width +'" height="'+ this.m_height +'" type="application/x-ms-wmp">\n';
		Html += '	<param name="url" value="'+ this.m_media_url +'" />\n';
		Html += '	<param name="AutoStart" value="'+ (auto_play?'true':'false') +'" />\n';
		Html += '	<param name="AutoSize" value="true" />\n';
		Html += '	<param name="AllowChangeDisplaySize" value="true" />\n';
		Html += '	<param name="standby" value="Media is loading..." />\n';
		Html += '	<param name="AnimationAtStart" value="true" />\n';
		Html += '	<param name="scale" value="aspect" />\n';
		Html += '	<param name="ShowControls" value="true" />\n';
		Html += '	<param name="ShowCaptioning" value="false" />\n';
		Html += '	<param name="ShowDisplay" value="false" />\n';
		Html += '	<param name="ShowStatusBar" value="false" />\n';
		Html += '</object>\n';
		Html += '<p style="font-size: 85%;margin-top:0;">Best viewed with <a href="http://support.mozilla.com/en-US/kb/Using+the+Windows+Media+Player+plugin+with+Firefox#Installing_the_plugin" target="_blank">';
		Html += 'Windows Media Player plugin for Firefox</a></p>\n';
		return Html;
	}
	
	this._getQuickTime = function() {
		
		var contentType = 'video/mpeg';
		var auto_play = false;
		if( this._getQuickTime.arguments.length > 0 )
			contentType = this._getQuickTime.arguments[0];
		if( this._getQuickTime.arguments.length > 1 )
			auto_play = this._getQuickTime.arguments[1];
			
		var Html = '';
		Html += '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="'+ this.m_width +'" height="'+ this.m_height +'" codebase="http://www.apple.com/qtactivex/qtplugin.cab">\n';
		Html += '	<param name="src" value="'+ this.m_media_url +'" />\n';
		Html += '	<param name="href" value="'+ this.m_media_url +'" />\n';
		Html += '	<param name="scale" value="aspect" />\n';
		Html += '	<param name="controller" value="true" />\n';
		Html += '	<param name="autoplay" value="'+ (auto_play?'true':'false') +'" />\n';
		Html += '	<param name="bgcolor" value="000000" />\n';
		Html += '	<param name="pluginspage" value="http://www.apple.com/quicktime/download/" />\n';
		Html += '	<embed src="'+ this.m_media_url +'" type="'+ contentType +'" width="'+ this.m_width +'" height="'+ this.m_height +'" scale="aspect" cache="true" bgcolor="000000" autoplay="'+ (auto_play?'true':'false') +'" controller="true" pluginspage="http://www.apple.com/quicktime/download/"></embed>';
		Html += '</object>\n';
		return Html;
	}
	
	this._getExt = function(File) {
		// First remove any anchor if any
		var anchor  = File.indexOf('#');
		if( anchor > -1 )
			File = File.substring(0, anchor);
		// Next, get rid of the query string if exists..
		var question  = File.indexOf('?');
		if( question > -1 )
			File = File.substring(0, question);
		// Find the last dot at the end of the file
		var dot = File.lastIndexOf('.');
		if( dot > -1 )
			return File.substring(dot+1).toLowerCase();
		return false; // Unable to find a file extension
	}
	
	this._passthru = function() {
		
		// If we have ourselves an iPhone, let the media passtru when clicked
		if( navigator.userAgent.indexOf("iPhone") != -1 )
			return true; // Let this client download and play the content itself.
		
		// Add additional user agents which cannot handle embed, object or flash code here:
		
		return false;
	}
	
	this._closePrevPlayer = function() {
		if( this.m_player_div )
		{
			document.getElementById( this.m_player_div ).innerHTML = '';
			document.getElementById( this.m_player_div ).style.display = 'none';
			this.m_player_div = false;
		}
		if( this.m_player_wnd )
		{
			this.m_player_wnd.close();
			this.m_player_wnd = false;
		}
	}
}


function powerpress_onload()
{
	if( g_bpLoadDelay )
		setTimeout('powerpress_load_delay()', g_bpLoadDelay);
	else
		powerpress_load_delay();
}

function powerpress_load_delay()
{
	for( var x = 0; x < g_pbPlayerArray.length; x++ )
		powerpress_play_page( g_pbPlayerArray[x][0], g_pbPlayerArray[x][1] );
}

var g_pbPlayerArray = new Array();
function powerpress_queue_player(media, div )
{
	//alert('test');
	var pos = g_pbPlayerArray.length;
	g_pbPlayerArray[pos] = new Array();
	g_pbPlayerArray[pos][0] = media;
	g_pbPlayerArray[pos][1] = div;
}

function powerpress_addLoadEvent(func)
{
  var oldonload = window.onload; 
	if (typeof window.onload != 'function')
	{ 
		window.onload = func; 
	}
	else
	{ 
		window.onload = function()
		{ 
			if (oldonload)
			{
				oldonload(); 
			} 
			func(); 
		} 
	} 
}


/** 
 * flashembed 0.31. Adobe Flash embedding script
 * 
 * http://flowplayer.org/tools/flash-embed.html
 *
 * Copyright (c) 2008 Tero Piirainen (tipiirai@gmail.com)
 *
 * Released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * >> Basically you can do anything you want but leave this header as is <<
 *
 * version 0.01 - 03/11/2008 
 * version 0.31 - Tue Jul 22 2008 06:30:31 GMT+0200 (GMT+02:00)
 */
 
function flashembed(root,userParams,flashvars){function getHTML(){var html="";if(typeof flashvars=='function'){flashvars=flashvars();}if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){html='<embed type="application/x-shockwave-flash" ';if(params.id){extend(params,{name:params.id});}for(var key in params){if(params[key]!==null){html+=[key]+'="'+params[key]+'"\n\t';}}if(flashvars){html+='flashvars=\''+concatVars(flashvars)+'\'';}html+='/>';}else{html='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ';html+='width="'+params.width+'" height="'+params.height+'"';if(!params.id&&document.all){params.id="_"+(""+Math.random()).substring(5);}if(params.id){html+=' id="'+params.id+'"';}html+='>';html+='\n\t<param name="movie" value="'+params.src+'" />';params.id=params.src=params.width=params.height=null;for(var k in params){if(params[k]!==null){html+='\n\t<param name="'+k+'" value="'+params[k]+'" />';}}if(flashvars){html+='\n\t<param name="flashvars" value=\''+concatVars(flashvars)+'\' />';}html+="</object>";if(debug){alert(html);}}return html;}function init(name){var timer=setInterval(function(){var doc=document;var el=doc.getElementById(name);if(el){flashembed(el,userParams,flashvars);clearInterval(timer);}else if(doc&&doc.getElementsByTagName&&doc.getElementById&&doc.body){clearInterval(timer);}},13);return true;}function extend(to,from){if(from){for(key in from){if(from.hasOwnProperty(key)){to[key]=from[key];}}}}var params={src:'#',width:'100%',height:'100%',version:null,onFail:null,expressInstall:null,debug:false,bgcolor:'#ffffff',allowfullscreen:true,allowscriptaccess:'always',quality:'high',type:'application/x-shockwave-flash',pluginspage:'http://www.adobe.com/go/getflashplayer'};if(typeof userParams=='string'){userParams={src:userParams};}extend(params,userParams);var version=flashembed.getVersion();var required=params.version;var express=params.expressInstall;var debug=params.debug;if(typeof root=='string'){var el=document.getElementById(root);if(el){root=el;}else{return init(root);}}if(!root){return;}if(!required||flashembed.isSupported(required)){params.onFail=params.version=params.expressInstall=params.debug=null;root.innerHTML=getHTML();return root.firstChild;}else if(params.onFail){var ret=params.onFail.call(params,flashembed.getVersion(),flashvars);if(ret){root.innerHTML=ret;}}else if(required&&express&&flashembed.isSupported([6,65])){extend(params,{src:express});flashvars={MMredirectURL:location.href,MMplayerType:'PlugIn',MMdoctitle:document.title};root.innerHTML=getHTML();}else{if(root.innerHTML.replace(/\s/g,'')!==''){}else{root.innerHTML="<h2>Flash version "+required+" or greater is required</h2>"+"<h3>"+(version[0]>0?"Your version is "+version:"You have no flash plugin installed")+"</h3>"+"<p>Download latest version from <a href='"+params.pluginspage+"'>here</a></p>";}}function concatVars(vars){var out="";for(var key in vars){if(vars[key]){out+=[key]+'='+asString(vars[key])+'&';}}return out.substring(0,out.length-1);}function asString(obj){switch(typeOf(obj)){case'string':return'"'+obj.replace(new RegExp('(["\\\\])','g'),'\\$1')+'"';case'array':return'['+map(obj,function(el){return asString(el);}).join(',')+']';case'function':return'"function()"';case'object':var str=[];for(var prop in obj){if(obj.hasOwnProperty(prop)){str.push('"'+prop+'":'+asString(obj[prop]));}}return'{'+str.join(',')+'}';}return String(obj).replace(/\s/g," ").replace(/\'/g,"\"");}function typeOf(obj){if(obj===null||obj===undefined){return false;}var type=typeof obj;return(type=='object'&&obj.push)?'array':type;}if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};});}function map(arr,func){var newArr=[];for(var i in arr){if(arr.hasOwnProperty(i)){newArr[i]=func(arr[i]);}}return newArr;}return root;}if(typeof jQuery=='function'){(function($){$.fn.extend({flashembed:function(params,flashvars){return this.each(function(){flashembed(this,params,flashvars);});}});})(jQuery);}flashembed=flashembed||{};flashembed.getVersion=function(){var version=[0,0];if(navigator.plugins&&typeof navigator.plugins["Shockwave Flash"]=="object"){var _d=navigator.plugins["Shockwave Flash"].description;if(typeof _d!="undefined"){_d=_d.replace(/^.*\s+(\S+\s+\S+$)/,"$1");var _m=parseInt(_d.replace(/^(.*)\..*$/,"$1"),10);var _r=/r/.test(_d)?parseInt(_d.replace(/^.*r(.*)$/,"$1"),10):0;version=[_m,_r];}}else if(window.ActiveXObject){try{var _a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{_a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");version=[6,0];_a.AllowScriptAccess="always";}catch(ee){if(version[0]==6){return;}}try{_a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(eee){}}if(typeof _a=="object"){_d=_a.GetVariable("$version");if(typeof _d!="undefined"){_d=_d.replace(/^\S+\s+(.*)$/,"$1").split(",");version=[parseInt(_d[0],10),parseInt(_d[2],10)];}}}return version;};flashembed.isSupported=function(version){var now=flashembed.getVersion();var ret=(now[0]>version[0])||(now[0]==version[0]&&now[1]>=version[1]);return ret;};
