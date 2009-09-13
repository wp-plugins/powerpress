<?php
// powerpressadmin-tools.php

	function powerpress_admin_tools()
	{
		$General = get_option('powerpress_general');
?>
<h2><?php echo __("PowerPress Tools"); ?></h2>

<p style="margin-bottom: 0;">Useful utilities and tools for PowerPress.</p>


<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __("Import Settings"); ?></th> 
<td>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-podpress-settings", 'powerpress-podpress-settings'); ?>" onclick="return confirm('Import PodPress settings, are you sure?\n\nExisting settings will be overwritten.');">Import PodPress Settings</a></strong></p>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php echo __("Import Episodes"); ?></th> 
<td>
	
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-podpress-epiosdes"); ?>">Import PodPress Episodes</a></strong> </p>
	<p>Import PodPress created episodes to PowerPress specific podcast feed(s).</p>
	
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-mt-epiosdes"); ?>">Import Movable Type/Blogger Episodes</a></strong> (media linked in blog posts)</p>
	<p>Import Movable Type/Blogger podcast episodes to PowerPress specific podcast feed(s).</p>
	
</td>
</tr>

<!--  ping_sites -->
<tr valign="top">
<th scope="row"><?php echo __("Add Update Services"); ?></th> 
<td>
	
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-ping-sites"); ?>">Add Update Services / Ping Sites</a></strong> (notify podcast directories when you publish new episodes)</p>
	<p>Add Update Services / Ping Sites geared towards podcasting.</p>
	
</td>
</tr>


<!-- use_caps -->
<tr valign="top">
<th scope="row"><?php echo __("User Capabilities"); ?></th> 
<td>
<?php
	if( $General['use_caps'] )
	{
?>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-remove-caps", 'powerpress-remove-caps'); ?>"><?php _e('Remove PowerPress Podcasting Capabilities for User Role Management'); ?></a></strong></p>
	<p>
	Removing PowerPress Podcasting Capabilities will allow anyone who can edit posts the ability to create and edit podcast episodes and view statistics from the WordPress Dashboard.
	</p>
	<p>You will most likely need either the <a href="http://www.im-web-gefunden.de/wordpress-plugins/role-manager/" target="_blank">Role Manager</a> or <a href="http://agapetry.net/category/plugins/role-scoper/" target="_blank">Role Scoper</a> plugin to manage the Edit Podcast capability.
	The Edit Podcast capability feature comes with no support. Please do not contact Blubrry.com for help with this feature.
	</p>
<?php
	}
	else
	{
?>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-add-caps", 'powerpress-add-caps'); ?>"><?php _e('Add PowerPress Podcasting Capabilities for User Role Management'); ?></a></strong></p>
	<p>
	Currently anyone who can edit posts on this blog may also edit podcast episodes.
	</p>
	<p>
	Adding Edit Podcast capability will configure Administrators, Editors and Authors with access to create and edit podcast episodes. Only administrators will be able to view statistics from the WordPress Dashboard. Contributors, Subscribers and other custom user roles will not be able to create and edit podcast episodes and view statistics from the dashboard.
	</p>
<?php
	}
?>

	<p>The WordPress <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">Roles and Capabilities</a> feature is designed to give the blog owner the ability to control and assign what users can and cannot do in the blog.
	By adding the Edit Podcast capability to your blog, you will be able to use the Roles and Capabilities in Wordpress to manage which users can create and edit podcast episodes.
	</p>

</td>
</tr>


<tr valign="top">
<th scope="row"><?php echo __("Update Plugins Cache"); ?></th> 
<td>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-clear-update_plugins", 'powerpress-clear-update_plugins'); ?>"><?php _e('Clear Plugins Update Cache'); ?></a></strong></p>
	<p>
	The list of plugins on the plugins page will cache the plugin version numbers for up to 24 hours. Click the link above to clear the cache to get the latest versions of plugins listed on your <a href="<?php echo admin_url(). 'plugins.php'; ?>" title="Plugins">plugins</a> page.
	</p>
</td>
</tr>


<tr valign="top">
<th scope="row"><?php echo __("Diagnostics"); ?></th> 
<td>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-diagnostics"); ?>"><?php _e('Diagnose Your PowerPress Installation'); ?></a></strong></p>
	<p>
	The Diagnostics page checks to see if your server is configured to support all of the available features in Blubrry PowerPress.
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