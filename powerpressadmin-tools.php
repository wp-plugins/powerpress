<?php
// powerpressadmin-tools.php

	function powerpress_admin_tools()
	{
		$General = get_option('powerpress_general');
?>
<h2><?php echo __("PowerPress Tools"); ?></h2>

<p style="margin-bottom: 0;"><?php echo __('Useful utilities and tools for PowerPress.'); ?></p>


<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __("Import Settings"); ?></th> 
<td>
	<p style="margin-top: 5px;"><strong>
		<a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-podpress-settings", 'powerpress-podpress-settings'); ?>" 
			onclick="return confirm('<?php echo __('Import PodPress settings, are you sure?\n\nExisting PowerPress settings will be overwritten.'); ?>');"><?php echo __('Import PodPress Settings'); ?></a></strong></p>
	<p><?php echo __('Import settings from PodPress into PowerPress.'); ?></p>
	
	<p style="margin-top: 5px;"><strong>
		<a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-podcasting-settings", 'powerpress-podcasting-settings'); ?>" 
			onclick="return confirm('<?php echo __('Import Podcasting plugin settings, are you sure?\n\nExisting PowerPress settings will be overwritten.'); ?>');"><?php echo htmlspecialchars(__('Import plugin "Podcasting" Settings')); ?></a></strong></p>
	<p><?php echo htmlspecialchars(__('Import settings from the plugin "Podcasting" into PowerPress.')); ?></p>
	<p><?php echo htmlspecialchars(__('Note: Episodes created using the plugin "Podcasting" do not require importing.')); ?></p>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php echo __("Import Episodes"); ?></th> 
<td>
	
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-podpress-epiosdes"); ?>"><?php echo __('Import PodPress Episodes'); ?></a></strong> </p>
	<p><?php echo __('Import PodPress created episodes to PowerPress specific podcast feed(s).'); ?></p>
	
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-mt-epiosdes"); ?>"><?php echo __('Import Movable Type/Blogger/Joomla/TypePad Episodes'); ?></a></strong> <?php echo __('(media linked in blog posts)'); ?></p>
	<p><?php echo __('Import Movable Type/Blogger/Joomla/TypePad (and most other blogging systems) podcast episodes to PowerPress specific podcast feed(s).'); ?></p>
	
</td>
</tr>

<!--  ping_sites -->
<tr valign="top">
<th scope="row"><?php echo __("Add Update Services"); ?></th> 
<td>
	
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-ping-sites"); ?>"><?php echo __('Add Update Services / Ping Sites'); ?></a></strong> <?php echo __('(notify podcast directories when you publish new episodes)'); ?></p>
	<p><?php echo __('Add Update Services / Ping Sites geared towards podcasting.</p>'); ?>
	
</td>
</tr>

<!--  find_replace -->
<tr valign="top">
<th scope="row"><?php echo __("Find and Replace Media"); ?></th>
<td>
	
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-find-replace"); ?>"><?php echo __('Find and Replace for Episode URLs'); ?></a></strong></p>
	<p>
		<?php echo __('Find and replace complete or partial segments of media URLs. Useful if you move your media to a new web site or service.'); ?>
	</p>
	
</td>
</tr>

<!-- use_caps -->
<tr valign="top">
<th scope="row"><?php echo __("User Capabilities"); ?></th> 
<td>
<?php
	if( !empty($General['use_caps']) )
	{
?>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-remove-caps", 'powerpress-remove-caps'); ?>"><?php _e('Remove PowerPress Podcasting Capabilities for User Role Management'); ?></a></strong></p>
	<p>
	<?php echo __('Podcasting capability allows administrators, editors and authors access to create and configure podcast episodes. 
	Only administrators will be able to view media statistics from the WordPress Dashboard. Contributors, subscribers and other
	custom users will not have access to create podcast episodes or view statistics from the dashboard. Due to this feature\'s
	complexity, it is not supported by Blubrry.com.'); ?>
	</p>
	
<?php
	}
	else
	{
?>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-add-caps", 'powerpress-add-caps'); ?>">
		<?php _e('Add PowerPress Podcasting Capabilities for User Role Management'); ?></a></strong></p>
	<p>
	<?php echo __('Adding podcasting capability will allow administrators, editors and authors access to create and configure podcast episodes. 
	Only administrators will be able to view media statistics from the WordPress Dashboard. Contributors, subscribers and other
	custom users will not have access to create podcast episodes or view statistics from the dashboard. Due to this feature\'s
	complexity, it is not supported by Blubrry.com.'); ?>
	</p>
<?php
	}
	
	if( @$General['premium_caps'] )
	{
?>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-remove-feed-caps", 'powerpress-remove-feed-caps'); ?>"><?php _e('Remove Password Protection Capabilities for Control of Which Users can Access Your Podcasts'); ?></a></strong>  (Also kown as Premium Content)</p>
	<p>
	<?php
		echo sprintf( __("To use this feature, go to %s and create a new custom podcast channel. In the Edit Podcast Channel page, click the last tab labeled 'Other Settings'. Place a check in the box labled 'Protect Content' and then click 'Save Changes'."),
			'<a href="'. admin_url("admin.php?page=powerpressadmin_customfeeds.php") .'" title="'. __('Podcast Channels') .'">'. __('Podcast Channels') .'</a>' );
	?>
	</p>
	<p>
		<?php echo __('Password protection capabilities for custom podcast channel feeds lets you control who can listen and view your 
		podcast. This feature allows you to password-protect custom podcast channels by adding a new role called "Premium 
		Subscriber." Only users with the "Premium Subscriber" role have access to your password protected custom podcast
		channels. Due to this feature\'s complexity, it is not supported by Blubrry.com.'); ?>
	</p>
<?php
	}
	else
	{
?>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-add-feed-caps", 'powerpress-add-feed-caps'); ?>"><?php _e('Add Password Protection Capabilities for Control of Which Users can Access Your Podcasts'); ?></a></strong> (Also kown as Premium Content)</p>
	<p>
		<?php echo __('Adding password protection capabilities for custom podcast channel feeds lets you control who can listen and view your 
		podcast. This feature allows you to password-protect custom podcast channels by adding a new role called "Premium 
		Subscriber." Only users with the "Premium Subscriber" role have access to your password protected custom podcast
		channels. Due to this feature\'s complexity, it is not supported by Blubrry.com.'); ?>
	</p>
<?php
	}
?>

	<p><strong><?php echo __('What are Roles and Capabilities?'); ?></strong></p>
	<p>
		<?php
		echo sprintf( __("The WordPress %s feature gives the blog owner the ability to control what users can and 
			cannot do in the blog. You will most likely need a roles and capabilities plugin such as %s, %s, or %s
			to take advantage of these features. Due to this feature's complexity, it is not supported by Blubrry.com."),
			'<a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">'. __('Roles and Capabilities') .'</a>',
			'<a href="http://www.im-web-gefunden.de/wordpress-plugins/role-manager/" target="_blank">'. __('Role Manager') .'</a>',
			'<a href="http://alkivia.org/wordpress/capsman/" target="_blank">'. __('Capability Manager') .'</a>',
			'<a href="http://agapetry.net/category/plugins/role-scoper/" target="_blank">'. __('Role Scoper') .'</a>'
			);
		?>
	</p>
	
</td>
</tr>


<tr valign="top">
<th scope="row"><?php echo __("Update Plugins Cache"); ?></th> 
<td>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-clear-update_plugins", 'powerpress-clear-update_plugins'); ?>"><?php _e('Clear Plugins Update Cache'); ?></a></strong></p>
	<p>
	<?php
		echo sprintf( __("The list of plugins on the plugins page will cache the plugin version numbers for up to 24 hours. 
		Click the link above to clear the cache to get the latest versions of plugins listed on your %s
			
			"),
			'<a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">'. __('Roles and Capabilities') .'</a>',
			'<a href="http://www.im-web-gefunden.de/wordpress-plugins/role-manager/" target="_blank">'. __('Role Manager') .'</a>',
			'<a href="http://alkivia.org/wordpress/capsman/" target="_blank">'. __('Capability Manager') .'</a>',
			'<a href="http://agapetry.net/category/plugins/role-scoper/" target="_blank">'. __('Role Scoper') .'</a>'
			);
			
		?>
	 <a href="<?php echo admin_url(). 'plugins.php'; ?>" title="Plugins">plugins</a> page.
	</p>
</td>
</tr>


<tr valign="top">
<th scope="row"><?php echo __("Diagnostics"); ?></th> 
<td>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-diagnostics"); ?>"><?php _e('Diagnose Your PowerPress Installation'); ?></a></strong></p>
	<p>
	<?php echo __('The Diagnostics page checks to see if your server is configured to support all of the available features in Blubrry PowerPress.'); ?>
	</p>
</td>
</tr>

<!--
<tr valign="top">
<th scope="row">Plugin Translation</th> 
<td>
	<p style="margin-top: 5px;">
	Coming soon.
	</p>
</td>
</tr>
-->

</table>
<?php  
	
	}

?>