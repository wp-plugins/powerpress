<?php
	// powerpressadmin-ping-sites.php
	
	function powerpressadmin_ping_sites_process()
	{
		$PingSites = $_POST['PingSite'];
		if( $PingSites && count($PingSites) > 0 )
		{
			$ping_site_data = get_option('ping_sites');
			
			while( list($null,$url) = each($PingSites) )
				$ping_site_data = trim($ping_site_data)."\r\n$url";
				
			update_option('ping_sites', $ping_site_data);
			powerpress_page_message_add_notice(  __('Update services added successfully.') );
		}
		else
		{
			powerpress_page_message_add_notice(  __('No update services selected to add.') );
		}
	}
	
	function powerpress_admin_ping_sites()
	{
		$ping_sites = get_option('ping_sites');
		$BlogSites = array('http://rpc.pingomatic.com/'=>'Ping-o-Matic!',
			'http://blogsearch.google.com/ping/RPC2'=>'Google Blog Search',
			'http://rssrpc.weblogs.com/RPC2'=>'WebLogs',
			'http://rpc.technorati.com/rpc/ping'=>'Technorati');
			
		$PodcastSites = array('http://audiorpc.weblogs.com/RPC2'=>'WebLogs Audio',
			'http://www.allpodcasts.com/Ping.aspx'=>'AllPodcasts.com',
			'http://www.podnova.com/xmlrpc.srf'=>'PodNova.com',
			'http://ping.podcast.com/ping.php'=>'Podcasts.com',
			'http://ping.syndic8.com/xmlrpc.php'=>'Syndic8.com');
?>


<input type="hidden" name="action" value="powerpress-ping-sites" />
<h2><?php echo __("Add Update services / Ping Sites"); ?></h2>

<p style="margin-bottom: 0;">Notify the following Update Services / Ping Sites when you create a new blog post / podcast episode.</p>

<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __("Update Blog Searvices"); ?></th> 
<td>
	<p>Select the blog service you would like to notify.</p>
<?php
	while( list($url,$name) = each($BlogSites) )
	{
		if( stristr($ping_sites, $url) )
		{
?>
	<p><input name="Ignore[]" type="checkbox" checked disabled value="1" /> <?php echo $name; ?></p>
<?php
		}
		else
		{
?>
	<p><input name="PingSite[]" type="checkbox" value="<?php echo $url; ?>" /> <?php echo $name; ?></p>
<?php
		}
	}
?>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php echo __("Update Podcast Searvices"); ?></th> 
<td>
	<p>Select the podcasting service you would like to notify.</p>
<?php
	while( list($url,$name) = each($PodcastSites) )
	{
		if( stristr($ping_sites, $url) )
		{
?>
	<p><input name="Ignore[]" type="checkbox" checked disabled value="1" /> <?php echo $name; ?></p>
<?php
		}
		else
		{
?>
	<p><input name="PingSite[]" type="checkbox" value="<?php echo $url; ?>" /> <?php echo $name; ?></p>
<?php
		}
	}
?>
</td>
</tr>

</table>
<p>
	You can manually add ping services by going to the to the "Update Services" section found in the <strong>WordPress Settings</strong> &gt; <strong>Writing</strong> page.
</p>
<p class="submit">
	<input type="submit" name="Submit" id="powerpress_save_button" class="button-primary" value="Add Selected Update Services" />
</p>

	<!-- start footer -->
<?php
	}

?>