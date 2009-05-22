<?php
	// powerpressadmin-mode.php
	
	function powerpress_admin_mode()
	{
?>


<input type="hidden" name="action" value="powerpress-save-mode" />
<h2><?php echo __("Welcome to Blubrry PowerPress"); ?></h2>

<p style="margin-bottom: 0;">
	Welcome to Blubrry PowerPress. In order to give each user the best experience, we designed two modes; Simple and Advanced. Please select the mode that is most appropriate for your needs.
</p>

<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __("Select Mode"); ?></th> 
<td>
	
	<p><input name="General[advanced_mode]" type="radio" value="0" /> <strong>Simple Mode</strong></p>
	<p>Simple Mode is intended for podcasters who are just starting out and feel a bit intimidated by all of the possible options and settings. This mode is perfect for someone who is recording in one format (e.g. mp3) and wants to keep things simple.</p>
	<ul><li>Features Include:<ul>
		<li>Only the bare essential settings</li>
		<li>Important feed and iTunes settings</li>
		<li>Player and download links added to bottom of episode posts</li>
	</ul></li></ul>
	
	<p><input name="General[advanced_mode]" type="radio" value="1" /> <strong>Advanced Mode</strong></p>
	<p>Advanced Mode gives you all of the features packaged in Blubrry PowerPress. This mode is perfect for someone who may want to distribute multiple versions of their podcast, customize the web player &amp; download links,
	or import data from a previous podcasting platform.</p>
	<ul><li>Features Include:<ul>
		<li><em>Advanced Settings</em> - Tweak additional basic settings.</li>
		<li><em>Presentation Settings</em> - Customize web player and media download links</li>
		<li><em>Extensive Feed Settings</em> -  Tweak all available feed settings</li>
		<li><em>Manage Custom Feeds</em> -  Manage and add custom podcast feeds</li>
		<li><em>MP3 ID3 Tags</em> - Blubrry Media Hosting  users can configure how their MP3 ID3 tags are written to hosted media</li>
		<li><em>Useful Tools</em> - Import data from previous sites (PodPress, MovableType, Blogger), add Update Sservices to your blog and more</li>
	</ul></li></ul>
	
</td>
</tr>

</table>
<p class="submit">
	<input type="submit" name="Submit" id="powerpress_save_button" class="button-primary" value="Set Mode and Continue" />
</p>

	<!-- start footer -->
<?php
	}

?>