/* videoloader.js */

function getParameterByName( name ) {
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}

jQuery(function($){
	fv = $("#video").flareVideo({
	/*	poster: "http://tpn.tv/wp-content/uploads/2011/01/CES-DAY3-300x199.jpg", */
		flashSrc: "FlareVideo.swf",
		autobuffer: false,
		preload: false,
		autoplay: false,
		width: 400,
		height: 200,
		playload: true
	});

	fv.load([
		{
			src: 'http://media.blubrry.com/geeknewscentral/blip.tv/file/get/Techpodcasts-CES2011LiveScosche343.m4v',
			srcX:  'http://flarevideo.com/flarevideo/examples/volcano.mp4',
			srcY: 'http://content.blubrry.com/pluginspodcast/Plugins031_Configure_SMTP.mp3',
			srcX:  'http://localhost/test.m4v',
			type: 'video/mp4'
		}
	]);
	
	// fv.play(); // Auto play
})