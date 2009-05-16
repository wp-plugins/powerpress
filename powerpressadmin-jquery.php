<?php
	// jQuery specific functions and code go here..
	
	// Credits:
	/*
	FOLDER ICON provided by Silk icon set 1.3 by Mark James link: http://www.famfamfam.com/lab/icons/silk/
 
	
	
	*/

function powerpress_admin_jquery_init()
{
	
	
	$action = (isset($_GET['action'])?$_GET['action']: (isset($_POST['action'])?$_POST['action']:false) );
	if( !$action )
		return;
		
	switch($action)
	{
		case 'powerpress-jquery-stats': {
		
			$StatsCached = get_option('powerpress_stats');
			
			powerpress_admin_jquery_header('Blubrry Media Statistics');
?>
<h2>Blubrry Media Statistics</h2>
<?php
			echo $StatsCached['content'];
			powerpress_admin_jquery_footer();
			exit;
		}; break;
		case 'powerpress-jquery-media': {
			
			$Settings = get_option('powerpress_general');

			$api_url = sprintf('%s/media/%s/index.json', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $Settings['blubrry_program_keyword'] );
			$json_data = powerpress_remote_fopen($api_url, $Settings['blubrry_auth']);
			$results =  powerpress_json_decode($json_data);
			print_r($files);
				
			$FeedSlug = $_GET['podcast-feed'];
			powerpress_admin_jquery_header('Browsing Media');
?>
		<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding:0;"><a href="#" onclick="self.parent.tb_remove();" title="Cancel"><img src="<?php echo admin_url(); ?>/images/no.png" /></a></p>
		<h2>Browsing Media</h2>
		<p>
			Browsing (unpublished) media files hosted on blubrry.com:
		</p>
		<ul class="media">
<?php
		
		if( isset($results['error']) )
		{
			echo $results['error'];
		}
		else
		{
			while( list($index,$data) = each($results) )
			{
?>	
			
			<li>
				<a href="#" onclick="self.parent.document.getElementById('powerpress_url_<?php echo $FeedSlug; ?>').value='<?php echo $data['name']; ?>'; self.parent.document.getElementById('powerpress_hosting_<?php echo $FeedSlug; ?>').value='1'; self.parent.document.getElementById('powerpress_url_<?php echo $FeedSlug; ?>').readOnly='true'; self.parent.tb_remove(); return false;"><?php echo $data['name']; ?></a>
				<cite><?php echo powerpress_byte_size($data['length']); ?></cite>
			</li>
<?php
			}
		}
?>
	</ul>
	
<?php	
			powerpress_admin_jquery_footer();
			exit;
		}; break;
		case 'powerpress-jquery-account-save': {
			
			check_admin_referer('powerpress-jquery-account-save');
			
			$Password = $_POST['Password'];
			$Settings = $_POST['Settings'];
			$Password = powerpress_stripslashes($Password);
			$General = powerpress_stripslashes($Settings);
			
			
			if( isset($_POST['ChangePassword']) )
				$Settings['blubrry_auth'] = base64_encode( $Settings['blubrry_username'] . ':' . $Password );
			$Settings['blubrry_hosting'] = 0;
			if( $_POST['Services'] == 'stats_hosting')
				$Settings['blubrry_hosting'] = 1;
			
			if( $_POST['Remove'] )
			{
				$Settings['blubrry_username'] = '';
				$Settings['blubrry_auth'] = '';
				$Settings['blubrry_program_keyword'] = '';
				$Settings['blubrry_hosting'] = 0;
			}
			
			powerpress_save_settings($Settings);
			
			// Clear cached statistics
			delete_option('powerpress_stats');
			
			$Successful = true;
			if( $Successful )
			{
				powerpress_admin_jquery_header('Blubrry Services Integration');
			
?>
<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding:0;"><a href="#" onclick="self.parent.tb_remove(); return false;" title="Close"><img src="<?php echo admin_url(); ?>/images/no.png" alt="Close" /></a></p>
<h2>Blubrry Services Integration</h2>
<p style="text-align: center;"><strong>Settings Saved Successfully!</strong></p>
<p style="text-align: center;">
	<a href="#" onclick="self.parent.tb_remove(); return false;" title="Close>Close</a>
</p>
<?php
				powerpress_admin_jquery_footer();
				exit;
			}
			
		} // no break here, let the next case catch it...
		case 'powerpress-jquery-account':
		 {
			$Settings = get_option('powerpress_general');
			
			powerpress_admin_jquery_header('Blubrry Services Integration');
?>
<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding: 0;"><a href="#" onclick="self.parent.tb_remove();" title="Cancel"><img src="<?php echo admin_url(); ?>/images/no.png" /></a></p>
<form action="<?php echo admin_url(); ?>" enctype="multipart/form-data" method="post">
<?php wp_nonce_field('powerpress-jquery-account-save'); ?>
<input type="hidden" name="action" value="powerpress-jquery-account-save" />
<div id="accountinfo">
	<h2>Blubrry Services Integration</h2>
	<p>
		<label>Blubrry User Name</label>
		<input type="text" name="Settings[blubrry_username]" value="<?php echo $Settings['blubrry_username']; ?>" />
	</p>
<?php if( $Settings['blubrry_auth'] != '') { ?>
	<p>
		<input type="checkbox" name="ChangePassword" value="1" onchange="document.getElementById('password_row').style.display=(this.checked?'block':'none');" /> Change password
	</p>
<?php } else { ?>
<input type="hidden" name="ChangePassword" value="1" />
<?php } ?>
	<p id="password_row" <?php if( $Settings['blubrry_auth'] != '') echo 'style="display: none;"'; ?>>
		<label>Blubrry Password</label>
		<input type="password" name="Password" value="" />
	</p>
	<p>
		<label>Blubrry Program Keyword</label>
		<input type="text" name="Settings[blubrry_program_keyword]" value="<?php echo $Settings['blubrry_program_keyword']; ?>" />
	</p>
	<p><strong>Select Blubrry Services</strong></p>
	<p style="margin-left: 20px; margin-bottom: 0px;margin-top: 0px;">
		<input type="radio" name="Services" value="stats" <?php echo ($Settings['blubrry_hosting']==0?'checked':''); ?> />Statistics Integration only
	</p>
	<p style="margin-left: 20px; margin-top: 0px;">
		<input type="radio" name="Services" value="stats_hosting" <?php echo ($Settings['blubrry_hosting']==1?'checked':''); ?> />Statistics and Hosting Integration (Requires Blubrry Hosting Account)
	</p>
	<p>
		<input type="submit" name="Remove" value="Remove" style="float: right;" onclick="return confirm('Remove Blubrry Services Integration, are you sure?');" />
		<input type="submit" name="Save" value="Save" />
		<input type="button" name="Cancel" value="Cancel" onclick="self.parent.tb_remove();" />
	</p>
</div>
</form>
<?php
			powerpress_admin_jquery_footer();
			exit;
		}; break;
	}
	
}

function powerpress_admin_jquery_header($title, $other = false)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php echo $title; ?> &#8212; WordPress</title>
<link rel="stylesheet" href="<?php echo powerpress_get_root_url(); ?>css/jquery.css" type="text/css" media="screen" />
<?php

wp_admin_css( 'css/global' );
wp_admin_css();
wp_admin_css( 'css/colors' );
wp_admin_css( 'css/ie' );

?>
<?php if( $other ) echo $other; ?>
</head>
<body>
<?php
}


function powerpress_admin_jquery_footer()
{
?>
</body>
</html>
<?php
}
	


?>