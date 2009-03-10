<?php
// powerpressadmin-tools.php

	function powerpress_admin_tools()
	{
		$General = get_option('powerpress_general');
?>
<h2><?php echo __("Powerpress Tools"); ?></h2>

<p style="margin-bottom: 0;">Useful utilities and tools for Powerpress.</p>


<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __("Import Settings"); ?></th> 
<td>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=podpress_settings", 'powerpress-podpress-settings'); ?>" onclick="return confirm('Import PodPress settings, are you sure?\n\nExisting settings will be overwritten.');">Import PodPress Settings</a></strong></p>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php echo __("Import Episodes"); ?></th> 
<td>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=podpress_epiosdes"); ?>">Import PodPress Episodes</a></strong></p>
	<p>Import PodPress created episodes to Powerpress specific podcast feeds.</p>
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
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=remove_caps", 'powerpress-remove-caps'); ?>"><?php _e('Remove Edit Podcast Capability for User Role Management'); ?></a></strong></p>
	<p>
	Removing Edit Podcast capability will allow anyone who can edit posts the ability to create and edit podcast episodes.
	</p>
	<p>You will most likely need either the <a href="http://www.im-web-gefunden.de/wordpress-plugins/role-manager/" target="_blank">Role Manager</a> or <a href="http://agapetry.net/category/plugins/role-scoper/" target="_blank">Role Scoper</a> plugin to manage the Edit Podcast capability.
	The Edit Podcast capability feature comes with no support. Please do not contact Blubrry.com for help with this feature.
	</p>
<?php
	}
	else
	{
?>
	<p style="margin-top: 5px;"><strong><a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=add_caps", 'powerpress-add-caps'); ?>"><?php _e('Add Edit Podcast Capability for User Role Management'); ?></a></strong></p>
	<p>
	Currently anyone who can edit posts on this blog may also edit podcast episodes.
	</p>
	<p>
	Adding Edit Podcast capability will configure Administrators, Editors and Authors with access to create and edit podcast episodes. Contributors, Subscribers and other custom user roles will not be able to create and edit podcast episodes.
	</p>
<?php
	}
?>
	<p>The WordPress <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">Roles and Capabilities</a> feature is designed to give the blog owner the ability to control and assign what users can and cannot do in the blog.
	By adding the Edit Podcast capability to your blog, you will be able to use the Roles and Capabilities in Wordpress to manage which users can create and edit podcast episodes.
	</p>

</td>
</tr>

</table>
<?php  
	
	}

?>