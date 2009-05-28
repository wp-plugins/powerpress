<?php
	// jQuery specific functions and code go here..
	
	// Credits:
	/*
	FOLDER ICON provided by Silk icon set 1.3 by Mark James link: http://www.famfamfam.com/lab/icons/silk/
	*/
	
function powerpress_add_blubrry_redirect($program_keyword)
{
	$Settings = powerpress_get_settings('powerpress_general');
	$RedirectURL = 'http://media.blubrry.com/'.$program_keyword;
	$NewSettings = array();
	
	// redirect1
	// redirect2
	// redirect3
	for( $x = 1; $x <= 3; $x++ )
	{
		$field = sprintf('redirect%d', $x);
		if( $Settings[$field] == '' )
		{
			$NewSettings[$field] = $RedirectURL.'/';
			break;
		}
		else if( stristr($Settings[$field], $RedirectURL ) )
		{
			return; // Redirect already implemented
		}
	}
	if( count($NewSettings) > 0 )
		powerpress_save_settings($NewSettings);
}

function powerpress_admin_jquery_init()
{
	$Error = false;
	$Settings = false; // Important, never remove this
	$Programs = false;
	$Step = 1;
	
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
			
			
			if( $Settings['blubrry_auth'] == '' )
			{
				powerpress_admin_jquery_header('Select Media');
?>
<p>Wait a sec! This feature is only available to Blubrry Podcast Community members. Join our community to get free podcast statistics and access to other valuable <a href="http://www.blubrry.com/powerpress_services/" target="_blank">services</a>.</p>
<p>Our <a href="http://www.blubrry.com/powerpress_services/" target="_blank">podcast-hosting integrated</a> PowerPress makes podcast publishing simple. Check out the <a href="http://www.blubrry.com/powerpress_services/" target="_blank">video</a> on our exciting three-step publishing system!</p>
<?php
				powerpress_admin_jquery_footer();
				exit;
			break;
			}

			$api_url = sprintf('%s/media/%s/index.json', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $Settings['blubrry_program_keyword'] );
			$json_data = powerpress_remote_fopen($api_url, $Settings['blubrry_auth']);
			$results =  powerpress_json_decode($json_data);
			// print_r($files);
				
			$FeedSlug = $_GET['podcast-feed'];
			powerpress_admin_jquery_header('Select Media');
?>

<!--		<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding:0;"><a href="#" onclick="self.parent.tb_remove();" title="Cancel"><img src="<?php echo admin_url(); ?>/images/no.png" /></a></p>
		<h2>Select Media</h2> -->
		<p>
			Select from media files uploaded to blubrry.com:
		</p>
<!--		<ul class="media"> -->
	<div id="media-items-container">
		<div id="media-items">
<?php
		
		if( isset($results['error']) )
		{
			echo $results['error'];
		}
		else
		{
			while( list($index,$data) = each($results) )
			{
				// old way:
			/*
?>	
			
			<li>
				<a href="#" onclick="self.parent.document.getElementById('powerpress_url_<?php echo $FeedSlug; ?>').value='<?php echo $data['name']; ?>'; self.parent.document.getElementById('powerpress_hosting_<?php echo $FeedSlug; ?>').value='1'; self.parent.document.getElementById('powerpress_url_<?php echo $FeedSlug; ?>').readOnly='true'; self.parent.tb_remove(); return false;"><?php echo $data['name']; ?></a>
				<cite><?php echo powerpress_byte_size($data['length']); ?></cite>
			</li>
<?php
				*/
				// new way:
?>
<div class="media-item">
	<strong class="media-name"><?php echo $data['name']; ?></strong>
	<cite><?php echo powerpress_byte_size($data['length']); ?></cite>

	<div class="media-item-links">
		<a href="#" onclick="self.parent.document.getElementById('powerpress_url_<?php echo $FeedSlug; ?>').value='<?php echo $data['name']; ?>'; self.parent.document.getElementById('powerpress_hosting_<?php echo $FeedSlug; ?>').value='1'; self.parent.document.getElementById('powerpress_url_<?php echo $FeedSlug; ?>').readOnly='true'; self.parent.tb_remove(); return false;">Select</a>
	</div> 
</div>
<?php				
			}
		}
?>
		</div>
	</div>
<!--	</ul> -->
	
<?php	
			powerpress_admin_jquery_footer();
			exit;
		}; break;
		case 'powerpress-jquery-account-save': {
			
			check_admin_referer('powerpress-jquery-account');
			
			$Password = $_POST['Password'];
			$Settings = $_POST['Settings'];
			$Password = powerpress_stripslashes($Password);
			$General = powerpress_stripslashes($Settings);
			
			$Save = false;
			$Close = false;
			
			/*
			$Settings['blubrry_hosting'] = 0;
			if( $_POST['Services'] == 'stats_hosting')
				$Settings['blubrry_hosting'] = 1;
			*/
			
			
			if( $_POST['Remove'] )
			{
				$Settings['blubrry_username'] = '';
				$Settings['blubrry_auth'] = '';
				$Settings['blubrry_program_keyword'] = '';
				$Settings['blubrry_hosting'] = 0;
				$Close = true;
				$Save = true;
			}
			else
			{
				$Programs = array();
				//if( isset($_POST['ChangePassword']) )
				//{
				//	$Settings['blubrry_program_keyword'] = ''; // Reset the program keyword stored
					
					// Anytime we change the password we need to test it...
				$auth = base64_encode( $Settings['blubrry_username'] . ':' . $Password );
				if( $Settings['blubrry_hosting'] == 0 )
					$api_url = sprintf('%s/stats/index.json', rtrim(POWERPRESS_BLUBRRY_API_URL, '/') );
				else
					$api_url = sprintf('%s/media/index.json', rtrim(POWERPRESS_BLUBRRY_API_URL, '/') );
				$json_data = powerpress_remote_fopen($api_url, $auth);
				$results =  powerpress_json_decode($json_data);
				
				if( isset($results['error']) )
				{
					$Error = $results['error'];
					if( strstr($Error, 'currently not available') )
						$Error = 'Unable to find podcasts for this account.';
				}
				else if( !is_array($results) )
				{
					$Error = $json_data;
				}
				else
				{
					// Get all the programs for this user...
					while( list($null,$row) = each($results) )
						$Programs[ $row['program_keyword'] ] = $row['program_title'];
					
					if( count($Programs) > 0 )
					{
						$Settings['blubrry_auth'] = $auth;
						
						if( $Settings['blubrry_program_keyword'] != '' )
						{
							powerpress_add_blubrry_redirect($Settings['blubrry_program_keyword']);
							$Save = true;
							$Close = true;
						}
						else if( isset($Settings['blubrry_program_keyword']) )
						{
							$Error = 'You must select a program to continue.';
						}
						else if( count($Programs) == 1 )
						{
							list($keyword, $title) = each($Programs);
							$Settings['blubrry_program_keyword'] = $keyword;
							powerpress_add_blubrry_redirect($keyword);
							$Close = true;
							$Save = true;
						}
						else
						{
							$Error = 'Please select your podcast program to continue.';
							$Step = 2;
						}
					}
					else
					{
						$Error = 'No podcasts for this account are listed on blubrry.com.';
					}
				}
			}
			
			if( $Save )
				powerpress_save_settings($Settings);
			
			// Clear cached statistics
			delete_option('powerpress_stats');
			
			if( $Error )
				powerpress_page_message_add_notice( $Error );
				
			if( $Close )
			{
				powerpress_admin_jquery_header('Blubrry Services Integration');
				powerpress_page_message_print();
?>
<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding:0;"><a href="#" onclick="self.parent.tb_remove(); return false;" title="Close"><img src="<?php echo admin_url(); ?>/images/no.png" alt="Close" /></a></p>
<h2>Blubrry Services Integration</h2>
<p style="text-align: center;"><strong>Settings Saved Successfully!</strong></p>
<p style="text-align: center;">
	<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_basic.php"); ?>" target="_top" title="Close">Close</a>
</p>
<?php
				powerpress_admin_jquery_footer();
				exit;
			}
			
			
		} // no break here, let the next case catch it...
		case 'powerpress-jquery-account':
		{
			if( !$Settings )
				$Settings = get_option('powerpress_general');
			
			if( $Programs == false )
				$Programs = array();
			
			// If we have programs to select from, then we're at step 2
			//if( count($Programs) )
			//	$Step = 2;
			
			powerpress_admin_jquery_header('Blubrry Services Integration');
			powerpress_page_message_print();	
?>
<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding: 0;"><a href="#" onclick="self.parent.tb_remove();" title="Cancel"><img src="<?php echo admin_url(); ?>/images/no.png" /></a></p>
<form action="<?php echo admin_url(); ?>" enctype="multipart/form-data" method="post">
<?php wp_nonce_field('powerpress-jquery-account'); ?>
<input type="hidden" name="action" value="powerpress-jquery-account-save" />
<div id="accountinfo">
	<h2>Blubrry Services Integration</h2>
<?php if( $Step == 1 ) { ?>
	<p>
		<label>Blubrry User Name</label>
		<input type="text" name="Settings[blubrry_username]" value="<?php echo $Settings['blubrry_username']; ?>" />
	</p>
	<p id="password_row">
		<label>Blubrry Password</label>
		<input type="password" name="Password" value="" />
	</p>
	<p><strong>Select Blubrry Services</strong></p>
	<p style="margin-left: 20px; margin-bottom: 0px;margin-top: 0px;">
		<input type="radio" name="Settings[blubrry_hosting]" value="0" <?php echo ($Settings['blubrry_hosting']==0?'checked':''); ?> />Statistics Integration only
	</p>
	<p style="margin-left: 20px; margin-top: 0px;">
		<input type="radio" name="Settings[blubrry_hosting]" value="1" <?php echo ($Settings['blubrry_hosting']==1?'checked':''); ?> />Statistics and Hosting Integration (Requires Blubrry Hosting Account)
	</p>
<?php } else { ?>
	<input type="hidden" name="Settings[blubrry_username]" value="<?php echo htmlspecialchars($Settings['blubrry_username']); ?>" />
	<input type="hidden" name="Password" value="<?php echo htmlspecialchars($Password); ?>" />
	<input type="hidden" name="Settings[blubrry_hosting]" value="<?php echo $Settings['blubrry_hosting']; ?>" />
	<p>
		<label>Blubrry Program Keyword</label>
<select name="Settings[blubrry_program_keyword]">
<option value="">Select Program</option>
<?php
while( list($value,$desc) = each($Programs) )
	echo "\t<option value=\"$value\"". ($Settings['blubrry_program_keyword']==$value?' selected':''). ">$desc</option>\n";
?>
</select>
	</p>
<?php } ?>
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
<!-- <link rel="stylesheet" href="wp-admin.css" type="text/css" media="screen" /> -->
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