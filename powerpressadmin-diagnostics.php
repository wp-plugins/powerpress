<?php
	// powerpressadmin-ping-sites.php
	
	function powerpressadmin_diagnostics_process()
	{
		global $powerpress_diags;
		$powerpress_diags = array();
		
		// First, see if the user has cURL and/or allow_url_fopen enabled...
		$powerpress_diags['detecting_media'] = array();
		$powerpress_diags['detecting_media']['success'] = true;
		$powerpress_diags['detecting_media']['warning'] = false;
		$powerpress_diags['detecting_media']['allow_url_fopen'] = (ini_get( 'allow_url_fopen' ) != false); // fopen
		$powerpress_diags['detecting_media']['curl'] = function_exists( 'curl_init' ); // cURL
		$powerpress_diags['detecting_media']['message2'] = ''; // if ( !ini_get('safe_mode') && !ini_get('open_basedir') )
		
		// Testing:
		//$powerpress_diags['detecting_media']['allow_url_fopen'] = false;
		//$powerpress_diags['detecting_media']['curl'] = false;
		
		if( $powerpress_diags['detecting_media']['curl'] )
		{
			$powerpress_diags['detecting_media']['message'] = __('Your web server supports the PHP cURL library.');
			if( $powerpress_diags['detecting_media']['allow_url_fopen'] )
				$powerpress_diags['detecting_media']['message'] .= ' '. __('Your web server is also configured with the php.ini setting \'allow_url_fopen\' enabled, but the cURL library takes precedence.');
			
			if( ini_get('safe_mode') && ini_get('open_basedir') )
			{
				$powerpress_diags['detecting_media']['warning'] = true;
				$powerpress_diags['detecting_media']['message2'] = __('Warning: Both php.ini settings \'safe_mode\' and \'open_basedir\' will prevent the cURL library from following redirects in URLs.');
			}
			else if( ini_get('safe_mode') )
			{
				$powerpress_diags['detecting_media']['warning'] = true;
				$powerpress_diags['detecting_media']['message2'] = __('Warning: The php.ini setting \'safe_mode\' will prevent the cURL library from following redirects in URLs.');
			}
			else if( ini_get('open_basedir') )
			{
				$powerpress_diags['detecting_media']['warning'] = true;
				$powerpress_diags['detecting_media']['message2'] = __('Warning: The php.ini setting \'open_basedir\' will prevent the cURL library from following redirects in URLs.');
			}
		}
		else if( $powerpress_diags['detecting_media']['allow_url_fopen'] )
		{
			$powerpress_diags['detecting_media']['message'] = __('Your web server is configured with the php.ini setting \'allow_url_fopen\' enabled.');
		}
		else
		{
			$powerpress_diags['detecting_media']['success'] = false;
			$powerpress_diags['detecting_media']['message'] = __('Your server must either have the php.ini setting \'allow_url_fopen\' enabled or have the PHP cURL library installed in order to detect media information.');
		}
		
		// Second, see if we can ping itunes, OpenSSL is required
		$powerpress_diags['pinging_itunes'] = array();
		$powerpress_diags['pinging_itunes']['success'] = true;
		$powerpress_diags['pinging_itunes']['openssl'] = extension_loaded('openssl');
		$powerpress_diags['pinging_itunes']['curl_ssl'] = false;
		if( function_exists('curl_version') )
		{
			$curl_info = curl_version();
			$powerpress_diags['pinging_itunes']['curl_ssl'] = ($curl_info['features'] & CURL_VERSION_SSL );
		}
		
		// testing:
		//$powerpress_diags['pinging_itunes']['openssl'] = false;
		//$powerpress_diags['pinging_itunes']['curl_ssl'] = false;
		
		if( $powerpress_diags['detecting_media']['success'] == false )
		{
			$powerpress_diags['pinging_itunes']['success'] = false;
			$powerpress_diags['pinging_itunes']['message'] = __('The problem with \'Detecting Media Information\' above needs to be resolved for this test to continue.');
		}
		else if( $powerpress_diags['detecting_media']['curl'] && $powerpress_diags['pinging_itunes']['curl_ssl'] )
		{
			$powerpress_diags['pinging_itunes']['message'] = __('Your web server supports secure HTTPS connections.');
		}
		else if( $powerpress_diags['detecting_media']['curl'] )
		{
			$powerpress_diags['pinging_itunes']['success'] = false;
			$powerpress_diags['pinging_itunes']['message'] = __('Your web server\'s cURL library does not support secure HTTPS connections.');
		}
		else if( $powerpress_diags['pinging_itunes']['openssl'] && $powerpress_diags['detecting_media']['allow_url_fopen'] )
		{
			$powerpress_diags['pinging_itunes']['message'] = __('Your web server supports secure HTTPS connections.');
		}
		else
		{
			$powerpress_diags['pinging_itunes']['success'] = false;
			$powerpress_diags['pinging_itunes']['message'] = __('Pinging iTunes requires the PHP OpenSSL library to be installed.');
		}
		
		// Third, see if the uploads/powerpress folder is writable
		$UploadArray = wp_upload_dir();
		$powerpress_diags['uploading_artwork'] = array();
		$powerpress_diags['uploading_artwork']['success'] = false;
		$powerpress_diags['uploading_artwork']['file_uploads'] = ini_get( 'file_uploads' );
		$powerpress_diags['uploading_artwork']['writable'] = false;
		$powerpress_diags['uploading_artwork']['upload_path'] = '';
		$powerpress_diags['uploading_artwork']['message'] = '';
		
		// Testing:
		//$UploadArray['error'] = 'WordPres broke';
		//$powerpress_diags['uploading_artwork']['file_uploads'] = false;
		//$UploadArray['error'] = true;
		
		if( $powerpress_diags['uploading_artwork']['file_uploads'] == false )
		{
			$powerpress_diags['uploading_artwork']['message'] = __('Your server requires the php.ini setting \'file_uploads\' enabled in order to upload podcast artwork.');
		}
		else if( $UploadArray['error'] === false )
		{
			$powerpress_diags['uploading_artwork']['upload_path'] = $UploadArray['basedir'] . '/powerpress/';
			
			if ( !is_dir($powerpress_diags['uploading_artwork']['upload_path']) && ! wp_mkdir_p( rtrim($powerpress_diags['uploading_artwork']['upload_path'], '/') ) )
			{
				$powerpress_diags['uploading_artwork']['message'] = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), rtrim($powerpress_diags['uploading_artwork']['upload_path'], '/') );
			}
			else
			{
				$powerpress_diags['uploading_artwork']['writable'] = powerpressadmin_diagnostics_is_writable($powerpress_diags['uploading_artwork']['upload_path']);
				if( $powerpress_diags['uploading_artwork']['writable'] == false )
				{
					$powerpress_diags['uploading_artwork']['message'] = sprintf(__('PowerPress is unable to write to the %s directory.'), $powerpress_diags['uploading_artwork']['upload_path']);
				}
				else
				{
					$powerpress_diags['uploading_artwork']['success'] = true;
					$powerpress_diags['uploading_artwork']['message'] = __('You are able to upload and save artwork images for your podcasts.');
				}
			}
		}
		else
		{
			if( strlen($UploadArray['error']) > 2 )
				$powerpress_diags['uploading_artwork']['message'] = $UploadArray['error'];
			else
				$powerpress_diags['uploading_artwork']['message'] = __('An error occurred obtaining the uploads directory from WordPress.');
		}
		
		// Fourth, see if we have enough memory and we're running an appropriate version of PHP
		$powerpress_diags['system_info'] = array();
		$powerpress_diags['system_info']['warning'] = false;
		$powerpress_diags['system_info']['success'] = true;
		$powerpress_diags['system_info']['php_version'] = phpversion();
		$powerpress_diags['system_info']['memory_limit'] = (int) ini_get('memory_limit');
		
		// testing:
		//$powerpress_diags['system_info']['memory_limit'] = -1;
		//$powerpress_diags['system_info']['memory_limit'] = 0;
		//$powerpress_diags['system_info']['memory_limit'] = 16;
		
		if( $powerpress_diags['system_info']['memory_limit'] == 0 )
		{
			if( version_compare($powerpress_diags['system_info']['php_version'], '5.2') > 0 )
				$powerpress_diags['system_info']['memory_limit'] = 128;
			else if( version_compare($powerpress_diags['system_info']['php_version'], '5.2') == 0 )
				$powerpress_diags['system_info']['memory_limit'] = 16;
			else
				$powerpress_diags['system_info']['memory_limit'] = 8;
		}
		$powerpress_diags['system_info']['memory_used'] = 0;
		
		if( version_compare($powerpress_diags['system_info']['php_version'], '5.2') > -1 )
		{
			$powerpress_diags['system_info']['message'] = sprintf( __('Your version of PHP (%s) is OK!'), $powerpress_diags['system_info']['php_version'] );
		}
		else if( version_compare($powerpress_diags['system_info']['php_version'], '5') > -1 )
		{
			$powerpress_diags['system_info']['message'] = sprintf( __('Your version of PHP (%s) is OK, though PHP 5.2 or newer is recommended.'), $powerpress_diags['system_info']['php_version'] );
		}
		else
		{
			$powerpress_diags['system_info']['message'] = sprintf( __('Your version of PHP (%s) will work, but PHP 5.2 or newer is recommended.'), $powerpress_diags['system_info']['php_version'] );
		}
		
		$used = 0;
		$total = $powerpress_diags['system_info']['memory_limit'];
		
		if( $total == -1 )
		{
			$powerpress_diags['system_info']['message2'] = __('Your scripts have no limit to the amount of memory they can use.');
			$used = (function_exists('memory_get_peak_usage')? memory_get_peak_usage() : ( function_exists('memory_get_usage') ? memory_get_usage() : 0 ) );
			if( $used )
				$powerpress_diags['system_info']['memory_used'] = round($used / 1024 / 1024, 2);
		}
		else if( function_exists('memory_get_peak_usage') )
		{
			$used = round(memory_get_peak_usage() / 1024 / 1024, 2);
			$powerpress_diags['system_info']['memory_used'] = $used;
			$percent = ($used/$total)*100;
			$powerpress_diags['system_info']['message2'] = sprintf(__('You are using %d%% (%.01fM of %.01dM) of available memory.'), $percent, $used, $total);
		}
		else if( function_exists('memory_get_usage') )
		{
			$used = round(memory_get_usage() / 1024 / 1024, 2);
			$powerpress_diags['system_info']['memory_used'] = $used;
			$percent = ($used/$total)*100;
			$powerpress_diags['system_info']['message2'] = sprintf(__('You are using %d%% (%.01fM of %dM) of available memory. Versions of PHP 5.2 or newer will give you a more accurate total of memory usage.'), $percent, $used, $total);
		}
		else
		{
			$powerpress_diags['system_info']['message2'] = sprintf(__('Your scripts have a total of %dM.'), $total );
		}
		
		if( $total > 0 && ($used + 4) > $total )
		{
			$powerpress_diags['system_info']['warning'] = true;
			$powerpress_diags['system_info']['message2'] = __('Warning:') .' '. $powerpress_diags['system_info']['message2'];
			$powerpress_diags['system_info']['message2'] .= ' ';
			$powerpress_diags['system_info']['message2'] .= sprintf(__('We recommend that you have at least %dM (4M more that what is currently used) or more memory to accomodate all of your installed plugins.'), ceil($used)+4 );
		}
		
		if( isset($_GET['Email']) && strlen($_GET['Email']) > 4 )
		{
			check_admin_referer('powerpress-diagnostics');
			$email = $_GET['Email'];
			powerpressadmin_diagnostics_email($email);
			powerpress_page_message_add_notice(  sprintf(__('Diagnostic results sent to %s.'), $email) );
		}
	}
	
	function powerpressadmin_diagnostics_email($email)
	{
		global $powerpress_diags, $wpmu_version, $wp_version;
		$SettingsGeneral = get_option('powerpress_general');
		
		// First we need some basic information about the blog...
		$message = __('Blog Title:') .' '. get_bloginfo('name') . "<br />\n";
		$message .= __('Blog URL:') .' '. get_bloginfo('home') . "<br />\n";
		$message .= __('WordPress Version:') .' '. $wp_version . "<br />\n";
		if( !empty($wpmu_version) )
				$message .= __('WordPress MU Version:') .' '. $wpmu_version . "<br />\n";
		$message .= __('System:') .' '. $_SERVER['SERVER_SOFTWARE'] . "<br />\n";
		$message .= __('Safe node:') .' '. ( ini_get('safe_mode')?'true':'false') ."<br />\n";
		$message .= __('Open basedir:') .' '. ini_get('open_basedir') ."<br />\n";
		
		// Crutial PowerPress Settings
		$message .= "<br />\n";
		$message .= '<strong>'. __('Important PowerPress Settings') ."</strong><br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('PowerPress version:') .' '. POWERPRESS_VERSION ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('advanced mode:') .' '. ($SettingsGeneral['advanced_mode']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('episode box mode:') .' '. ($SettingsGeneral['episode_box_mode']==0?__('normal'): ($SettingsGeneral['episode_box_mode']==1?__('simple'):__('advanced')) ) ."<br />\n";
		
		// Detecting Media Information
		$message .= "<br />\n";
		$message .= '<strong>'.__('Detecting Media Information') ."</strong><br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('success:') .' '. ($powerpress_diags['detecting_media']['success']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('warning:') .' '. ($powerpress_diags['detecting_media']['warning']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('allow_url_fopen:') .' '. ($powerpress_diags['detecting_media']['allow_url_fopen']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('curl:') .' '. ($powerpress_diags['detecting_media']['curl']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('message:') .' '. $powerpress_diags['detecting_media']['message'] ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('message 2:') .' '. $powerpress_diags['detecting_media']['message2'] ."<br />\n";
		
		// Pinging iTunes
		$message .= "<br />\n";
		$message .= '<strong>'.__('Pinging iTunes') ."</strong><br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('success:') .' '. ($powerpress_diags['pinging_itunes']['success']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('curl_ssl:') .' '. ($powerpress_diags['pinging_itunes']['curl_ssl']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('openssl:') .' '. ($powerpress_diags['pinging_itunes']['openssl']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('message:') .' '. $powerpress_diags['pinging_itunes']['message'] ."<br />\n";
		
		// Uploading Artwork
		$message .= "<br />\n";
		$message .= '<strong>'.__('Uploading Artwork') ."</strong><br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('success:') .' '. ($powerpress_diags['uploading_artwork']['success']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('file_uploads:') .' '. ($powerpress_diags['uploading_artwork']['file_uploads']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('writable:') .' '. ($powerpress_diags['uploading_artwork']['writable']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('upload_path:') .' '. $powerpress_diags['uploading_artwork']['upload_path'] ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('message:') .' '. $powerpress_diags['uploading_artwork']['message'] ."<br />\n";
		
		// System Information
		$message .= "<br />\n";
		$message .= '<strong>'.__('System Information') ."</strong><br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('success:') .' '. ($powerpress_diags['system_info']['success']?'true':'false') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('warning:') .' '. ($powerpress_diags['system_info']['warning']?'yes':'no') ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('php_version:') .' '. $powerpress_diags['system_info']['php_version'] ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('memory_limit:') .' '. $powerpress_diags['system_info']['memory_limit'] ."M\n";
		$message .= " &nbsp; \t &nbsp; ". __('memory_used:') .' '. sprintf('%.01fM',$powerpress_diags['system_info']['memory_used']) ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('message:') .' '. $powerpress_diags['system_info']['message'] ."<br />\n";
		$message .= " &nbsp; \t &nbsp; ". __('message 2:') .' '. $powerpress_diags['system_info']['message2'] ."<br />\n";

		if( isset($_GET['ap']) && $_GET['ap'] )
		{
			$current_plugins = get_option('active_plugins');
			$message .= "<br />\n";
			$message .= '<strong>'.__('Active Plugins') ."</strong><br />\n";
			while( list($null,$plugin_path) = each($current_plugins) )
			{
				$plugin_data = get_plugin_data( rtrim(WP_PLUGIN_DIR, '/'). '\\/'. rtrim($plugin_path, '\\/'), false, false ); //Do not apply markup/translate as it'll be cached.
				
				$message .= " &nbsp; \t &nbsp; " . __('Title:') .' '. $plugin_data['Title']. "<br />\n";
				$message .= " &nbsp; \t &nbsp; " . __('Relative Path:') .' '. $plugin_path. "<br />\n";
				$message .= " &nbsp; \t &nbsp; " . __('Version:') .' '. $plugin_data['Version']. "<br />\n";
				$message .= " &nbsp; \t &nbsp; " . __('Web Site:') .' '. $plugin_data['PluginURI']. "<br />\n";
				//$message .= " &nbsp; \t &nbsp; " . __('Description:') .' '. $plugin_data['Description']. "<br />\n";
				//$message .= " &nbsp; \t &nbsp; " . __('Author Name:') .' '. $plugin_data['Author']. "<br />\n";
				//$message .= " &nbsp; \t &nbsp; " . __('Author Web Site:') .' '. $plugin_data['AuthorURI']. "<br />\n";
				//print_r($plugin_data);
				$message .= "<br />\n";
			}
		}
		//$message .= " &nbsp; \t &nbsp; ". __('success:') .' '. ($powerpress_diags['system_info']['success']?'true':'false') ."<br />\n";
		
		// Now lets loop through each section of diagnostics
		$user_info = wp_get_current_user();
		$from_email = $user_info->user_email;
		$from_name = $user_info->user_nicename;
		$headers = 'From: "'.$from_name.'" <'.$from_email.'>'."\n"
			.'Reply-To: "'.$from_name.'" <'.$from_email.'>'."\n"
			.'Return-Path: "'.$from_name.'" <'.$from_email.'>'."\n";
		if( isset($_GET['CC']) )
			$headers .= 'CC: "'.$from_name.'" <'.$from_email.'>'."\n";
		$headers .= "Content-Type: text/html\n";
		
		@wp_mail($email, sprintf(__('Blubrry PowerPress diagnostic results for %s'), get_bloginfo('name')), $message, $headers);
	}
	
	function powerpressadmin_diagnostics_is_writable($dir)
	{
		// Make sure we can create a file in the specified directory...
		if( is_dir($dir) )
		{
			return is_writable($dir);
		}
		return false;
	}
	
	function powerpressadmin_diagnostics_status($success=true, $warning=false)
	{
		$img = 'yes.png';
		$color = '#458045';
		$text = __('Success');
		if( $success == false ) // Failed takes precedence over warning
		{
			$img = 'no.png';
			$color = '#CC0000';
			$text = __('Failed');
		}
		else if( $warning )
		{
			$img = '../../../wp-includes/images/smilies/icon_exclaim.gif';
			$color = '#D98500';
			$text = __('Warning');
		}
?>
	<img src="<?php echo admin_url(); ?>/images/<?php echo $img; ?>" style="vertical-align:text-top;" />
	<strong style="color:<?php echo $color; ?>;"><?php echo $text; ?></strong>
<?php
	}
	
	function powerpressadmin_diagnostics()
	{
		global $powerpress_diags;
		$GeneralSettings = get_option('powerpress_general');
		
		if( empty($powerpress_diags) )
		{
			powerpressadmin_diagnostics_process();
			powerpress_page_message_print();
		}
?>

<h2><?php echo __('Blubrry PowerPress Diagnostics'); ?></h2>
<p>
	<?php echo __('The Diagnostics page checks to see if your server is configured to support all of the available features in Blubrry PowerPress.'); ?>
</p>

<h3 style="margin-bottom: 0;"><?php echo __('Detecting Media Information'); ?></h3>
<p style="margin: 0;">
	<?php echo __('The following test checks to see if your web server can make connections with other web servers to obtain file size and media duration information. The test checks to see if either the PHP cURL library is installed or the php.ini setting \'allow_url_fopen\' enabled.'); ?>
</p>
<table class="form-table">
<tr valign="top">
<th scope="row">
	<?php powerpressadmin_diagnostics_status($powerpress_diags['detecting_media']['success'],$powerpress_diags['detecting_media']['warning']); ?>
</th> 
<td>
	<p><?php echo htmlspecialchars($powerpress_diags['detecting_media']['message']); ?></p>
<?php if( $powerpress_diags['detecting_media']['message2'] ) { ?>
	<p><?php echo htmlspecialchars($powerpress_diags['detecting_media']['message2']); ?></p><?php } ?>
<?php if( $powerpress_diags['detecting_media']['success'] ) { ?>
	<p><?php echo __('If you are still having problems detecting media information, check with your web hosting provider if there is a firewall blocking your server.'); ?></p>
<?php } else { ?>
	<p><?php echo __('Contact your web hosting provider with the information above.'); ?></p>
<?php } ?>
</td>
</tr>
</table>

<h3 style="margin-bottom: 0;"><?php echo __('Pinging iTunes'); ?></h3>
<p style="margin: 0;"><?php echo __('The following test checks to see that your web server can make connections with Apple\'s secure ping server.'); ?></p>
<table class="form-table">
<tr valign="top">
<th scope="row">
	<?php powerpressadmin_diagnostics_status($powerpress_diags['pinging_itunes']['success']); ?>
</th> 
<td>
	<p><?php echo htmlspecialchars($powerpress_diags['pinging_itunes']['message']); ?></p>
<?php if( $powerpress_diags['pinging_itunes']['success'] == false ) { ?>
	<p><?php echo __('Contact your web hosting provider with the information above.'); ?></p>
<?php } ?>
</td>
</tr>
</table>

<h3 style="margin-bottom: 0;"><?php echo __("Uploading Artwork"); ?></h3>
<p style="margin: 0;"><?php echo __('The following test checks to see that you can upload and store files on your web server.'); ?></p>
<table class="form-table">
<tr valign="top">
<th scope="row">
	<?php powerpressadmin_diagnostics_status($powerpress_diags['uploading_artwork']['success']); ?>
</th> 
<td>
	<p><?php echo htmlspecialchars($powerpress_diags['uploading_artwork']['message']); ?></p>
</td>
</tr>
</table>

<h3 style="margin-bottom: 0;"><?php echo __('System Information'); ?></h3>
<p style="margin: 0;"><?php echo __('The following test checks your version of PHP and memory usage.'); ?></p>
<table class="form-table">
<tr valign="top">
<th scope="row">
	<?php powerpressadmin_diagnostics_status($powerpress_diags['system_info']['success'], $powerpress_diags['system_info']['warning']); ?>
</th> 
<td>
	<p><?php echo htmlspecialchars($powerpress_diags['system_info']['message']); ?></p>
	<p><?php echo htmlspecialchars($powerpress_diags['system_info']['message2']); ?></p>
<?php if( $powerpress_diags['system_info']['warning'] ) { ?>
	<p><?php echo __('Contact your web hosting provider to inquire how to increase the PHP memory limit on your web server.'); ?></p>
<?php } ?>
</td>
</tr>
</table>

<form enctype="multipart/form-data" method="get" action="<?php echo admin_url('admin.php'); ?>">
<input type="hidden" name="action" value="powerpress-diagnostics" />
<input type="hidden" name="page" value="powerpress/powerpressadmin_<?php echo ($GeneralSettings['advanced_mode']==1?'tools':'basic'); ?>.php" />
<?php
	// Print nonce
	wp_nonce_field('powerpress-diagnostics');
?>

<h3 style="margin-bottom: 0;"><?php echo __('Email Results'); ?></h3>
<p style="margin: 0;"><?php echo __('Send the results above to the specified Email address.'); ?></p>
<table class="form-table">
<tr valign="top">
<th scope="row">
	<?php echo __('Email'); ?>
</th> 
<td>
	<div style="margin-top: 5px;">
		<input type="text" name="Email" value="" style="width: 50%;" />
		<input type="submit" name="Submit" id="powerpress_save_button" class="button-primary" value="Send Results" />
	</div>
	<div>
		<input type="checkbox" name="CC" value="1" style="vertical-align: text-top;" checked /> CC: <?php $user_info = wp_get_current_user(); echo "&quot;{$user_info->user_nicename}&quot; &lt;{$user_info->user_email}&gt;"; ?>
	</div>
	<div>
		<input type="checkbox" name="ap" value="1" style="vertical-align: text-top;" checked /> Include list of active plugins in diagnostics results.
	</div>
</td>
</tr>
</table>
</form>

<p>&nbsp;</p>

	<!-- start footer -->
<?php
	}

?>