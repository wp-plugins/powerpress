<?php

if( !function_exists('add_action') )
	die("access denied.");
	
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
	$Settings = get_option('powerpress_general');
	
	if( isset($Settings['disable_dashboard_widget']) && $Settings['disable_dashboard_widget'] == 1 )
		return; // Lets not do anythign to the dashboard for PowerPress Statistics
	
	// If using user capabilities...
	if( @$Settings['use_caps'] && !current_user_can('view_podcast_stats') )
		return;
		
	$content = false;
	$UserPass = $Settings['blubrry_auth'];
	$Keyword = $Settings['blubrry_program_keyword'];
	$StatsCached = get_option('powerpress_stats');
	if( $StatsCached && $StatsCached['updated'] > (time()-(60*60*3)) )
		$content = $StatsCached['content'];
	
	if( !$content )
	{
		if( !$UserPass )
		{
			$content = sprintf('<p>'. __('Wait a sec! This feature is only available to Blubrry Podcast Community members. Join our community to get free podcast statistics and access to other valuable %s.') .'</p>',
					'<a href="http://www.blubrry.com/powerpress_services/" target="_blank">'. __('Services') . '</a>' );
			$content .= ' ';
			$content .= sprintf('<p>'. __('Our %s integrated PowerPress makes podcast publishing simple. Check out the %s on our exciting three-step publishing system!') .'</p>',
					'<a href="http://www.blubrry.com/powerpress_services/" target="_blank">'. __('Podcast Hosting') .'</a>',
					'<a href="http://www.blubrry.com/powerpress_services/" target="_blank">'. __('Video') .'</a>' );
		}
		else
		{
			$api_url = sprintf('%s/stats/%s/summary.html?nobody=1', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $Keyword);

			$content = powerpress_remote_fopen($api_url, $UserPass);
			if( $content )
				update_option('powerpress_stats', array('updated'=>time(), 'content'=>$content) );
			else
				$content = __('Error: An error occurred authenticating user.');
		}
	}
?>
<div>
<?php
	echo $content;
	
	if( $UserPass )
	{
?>
	<div id="blubrry_stats_media_show">
		<a href="<?php echo admin_url(); ?>?action=powerpress-jquery-stats&amp;KeepThis=true&amp;TB_iframe=true&amp;modal=true" title="<?php echo __('Blubrry Media statistics'); ?>" class="thickbox"><?php echo __('more'); ?></a>
	</div>
<?php } ?>
</div>
<?php
}
	 

function powerpress_dashboard_setup()
{
	if( !function_exists('wp_add_dashboard_widget') )
		return;
	
	$Settings = get_option('powerpress_general');
	
	if( isset($Settings['disable_dashboard_widget']) && $Settings['disable_dashboard_widget'] == 1 )
		return; // Lets not do anythign to the dashboard for PowerPress Statistics
		
	if( @$Settings['use_caps'] && !current_user_can('view_podcast_stats') )
		return;
		
	if( $Settings )
	{
		wp_add_dashboard_widget( 'powerpress_dashboard_stats', __( 'Blubrry Podcast Statistics' ), 'powerpress_dashboard_stats_content' );
	}
}
	 
add_action('admin_head-index.php', 'powerpress_dashboard_head');
add_action('wp_dashboard_setup', 'powerpress_dashboard_setup');

?>