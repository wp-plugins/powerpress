<?php

function powerpress_dashboard_head()
{
?>
<style type="text/css">
#blubrry_stats_summary {
	
}
#blubrry_stats_summary label {
	width: 40%;
	max-width: 150px;
	float: left;
}
#blubrry_stats_summary h2 {
	font-size: 14px;
	margin: 0;
	padding: 0;
}
.blubrry_stats_ul {
	padding-left: 20px;
	margin-top: 5px;
	margin-bottom: 10px;
}
.blubrry_stats_ul li {
	list-style-type: none;
	margin: 0px;
	padding: 0px;
}
#blubrry_stats_media {
	display: none;
}
#blubrry_stats_media_show {
	text-align: right;
	font-size: 85%;
}
#blubrry_stats_media h4 {
	margin-bottom: 10px;
}
.blubrry_stats_title {
	margin-left: 10px;
}
.blubrry_stats_updated {
	font-size: 80%;
}
</style>
<?php
}

function powerpress_dashboard_stats_content()
{
	$content = false;
	$StatsCached = get_option('powerpress_stats');
	if( $StatsCached && $StatsCached['updated'] > (time()-(60*60*3)) )
		$content = $StatsCached['content'];
	
	if( !$content )
	{
		$Settings = get_option('powerpress_general');
		$UserPass = $Settings['blubrry_auth'];
		$Keyword = $Settings['blubrry_program_keyword'];
		
		if( !$UserPass )
		{
			$content = '<p>Wait a sec! This feature is only available to Blubrry Podcast Community members. Join our community to get free podcast statistics and access to other valuable services.</p>
<p>Our podcast-hosting integrated PowerPress makes podcast publishing simple. Check out the video on our exciting three-step publishing system!</p>';
		}
		else
		{
			$api_url = sprintf('%s/stats/%s/summary.html?nobody=1', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $Keyword);

			$content = powerpress_remote_fopen($api_url, $UserPass);
			if( $content )
				update_option('powerpress_stats', array('updated'=>time(), 'content'=>$content) );
			else
				$content = 'Error: An error occurred authenticating user.';
		}
	}
?>
<div id="">
<?php
//$content = http_get('http://api.blubrry.local/stats/compiled_weekly2/summary.html?year=2008&month=7', 'amandato@gmail.com', 'testit');

//$decoded = my_json_decode($content['data'], true);
//print_r( $content ); 
	echo $content;
	//echo 'Podcast Statistics go here.';
	
	if( $UserPass )
	{
?>
	<div id="blubrry_stats_media_show">
		<a href="<?php echo admin_url(); ?>?action=powerpress-jquery-stats&KeepThis=true&TB_iframe=true" title="Blubrry Media statistics" class="thickbox">more</a>
	</div>
<?php } ?>
</div>
<?php
}
	 

function powerpress_dashboard_setup()
{
	$Settings = get_option('powerpress_general');
	$Settings['blubrry_stats'] = true;
	if( $Settings && $Settings['blubrry_stats'] == true )
	{
		wp_add_dashboard_widget( 'powerpress_dashboard_stats', __( 'Blubrry Podcast Statistics' ), 'powerpress_dashboard_stats_content' );
	}
}
	 
add_action('admin_head-index.php', 'powerpress_dashboard_head');
add_action('wp_dashboard_setup', 'powerpress_dashboard_setup');

?>