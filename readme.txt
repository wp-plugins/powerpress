=== Podcasting - Blubrry PowerPress Podcast plugin ===
Contributors: Angelo Mandato, Blubrry.com
Tags: podcasting, podcast, podcaster, itunes, enclosure, zune, iphone, youtube, viddler, blip.tv, ustream, podcasting, audio, video, rss2, feed, player, media, rss, mp3, music, embed, feedburner, statistics, stats, flv, flash, id3, episodes, blubrry
Requires at least: 2.6.0
Tested up to: 2.8.4
Stable tag: 0.9.10

Add podcasting support to your blog.

== Description ==
Blubrry PowerPress podcasting plugin adds all of the essential features for podcasting to WordPress. Developed by podcasters for podcasters, PowerPress goes above and beyond with full iTunes support, Update iTunes listing feature, web audio/video media players and more!

For users who feel the plugin is too complicated, we've included a Simple Mode. Simple Mode only includes the essential features to your blog to support podcasting.

The Blubrry PowerPress interface allows you to easily add/modify/remove podcast episodes from blog posts and includes a simple on-line media player, iTunes compatibile RSS feed tags, ability to upload cover art images, ping iTunes, detect media size, detect time duration (mp3's only) and add 3rd party media statistics.

Features:

* Simple Mode (displays only the essential features in one settings page)
* Easily add/modify/remove podcast episodes from blog posts and pages
* Integrated audio/video media player
* Embed player within blog posts with [powerpress] shortcode
* Supports embeds from sites such as YouTube, Viddler and Blip.tv
* Podcast Only Feed
* Custom Podcast Feeds (no limit)
* Category Podcast Feeds (Category Casting)
* Integrated Media Hosting via Blubrry Services
* Integrated Media Statistics via Blubrry Services
* Mp3 ID3 Tagging via Blubrry Services
* iTunes RSS tags
* Enhanced iTunes summaries from blog posts option
* iTunes album/cover art
* Upload new iTunes/RSS cover art
* Ping iTunes (with logging)
* Media size detection
* Duration detection (mp3 only)
* 3rd party statistics integration
* Import PodPress settings and media episodes
* Import Blogger/Movable Type media episodes
* Tag/Keyword Podcast Feeds (Tag Casting)
* Hosted Feed Support (FeedBurner.com)
* User Role Management (Control which users on blog can Podcast)

File Types detected: mp3, m4a, ogg, wma, ra, mp4a, m4v, mp4v,  mpg, asf, avi, wmv, flv, swf, mov, divx, 3gp, midi, wav, aa, pdf, torrent, m4b, m4r 
 
For the latest information visit the website.

http://www.blubrry.com/powerpress/

Documentation available on the blubrry help site.

http://help.blubrry.com/blubrry-powerpress/

== Frequently Asked Questions ==

 = Why doesn't Blubrry PowerPress support multiple enclosures in one feed item/post? =
Blubrry PowerPress does not allow you to include multiple media files for one feed item (blog post). This is because each podcatcher handles multiple enclosures in feeds differently. iTunes will download the first enclosure that it sees in the feed ignoring the rest. Other podcatchers and podcasting directories either pick up the first enclosure or the last in each post item. This inconsistency combined with the fact that [Dave Winer does not recommend multiple enclosures](http://www.reallysimplesyndication.com/2004/12/21) and the [FeedValidator.org recommendation against it](http://www.feedvalidator.org/docs/warning/DuplicateEnclosure.html) is why the Blubrry PowerPress does not support them.

As a alternative, PowerPress allows you to create additional Custom Podcast Feeds to associate any magnitude of media format and/or length in a blog post to specific custom feeds. For example, you can create one blog post associated to separate video and audio podcast feeds saving you time from entering your show notes twice. 

 = Why doesn't Blubrry PowerPress include media statistics built-in? =
 Blubrry PowerPress does not include media statistics built-in. This is not because Blubrry has its own statistics service, although that's a good reason by itself. Maintaining and calculating statistics is a resource and server intensive task that would add bloat to an otherwise efficient WordPress podcasting plugin. We recommend using your media hosting's web statistics to give you basic download numbers and, if you are seeking more grandular measurements such as client and geographical information for each episode, you're more than welcome to use the [Blubrry Statistics service](http://www.blubrry.com/podcast_statistics/) as well.
 
 As of Blubrry PowerPress version 0.8, you may now access your Blubrry Statistics from within your WordPress dashboard.
 
 = How do you insert the player within a blog post? =
You can insert the media player within yoru blog posts by using the WordPress shortcode feature. The shortcode for PowerPress is [powerpress] (all lowercase)

 You may use the shortcode to add a player to other media files (non episode files) by specifying the media url in the shortcode: [powerpress url="http://example.com/path/to/media.mp3"]
	
 For advanced users with multiple podcast feeds, you may insert the player for a specific feed by specifying the feed slug in the shortcode: [powerpress feed="podcast"]
 
 If you want to specify a cover image, add an image attribute which points to the specific image url: [powerpress image="http://example.com/path/to/cover_image.jpg"] *Experimental*
	
== Installation ==
1. Copy the entire directory from the downloaded zip file into the /wp-content/plugins/ folder.
2. Activate the "Blubrry PowerPress" plugin in the Plugin Management page.
3. Configure your Blubrry PowerPress by going to the **Settings** > **Blubrry PowerPress** page.
		
== Screenshots ==
1. Add podcast episode, found within the edit post screen
2. Cross section of Blubrry PowerPress Feed settings page (Advanced Mode)
3. Cross section of Blubrry PowerPress Basic settings page (Simple Mode)
4. Cross section of blog post with built-in flash player and download links

== Changelog ==

= 0.9.11 =
* Released on 9/??/2009
* Fixed minor bugs with mp3 media detection: proper user agent set, logic added to deal with LibSyn 406 error, and media detection script can now be used for detecting file size for other media types.
* Improved iTunes subtitle so value is contained within one line.
* Fixed bug with mv4 video displaying incorrectly in PowerPress player.


= 0.9.10 =
* Released on 9/21/2009
* Fixed code that detects media information when encountering redirects. Bug only affected users who did not have cURL configured in PHP.
* Updated code for obtaining the uploads directory.
* Added new Diagnostics page, diagnostics test checks if blog can obtain media information from media URLs, if you can ping iTunes, if you can upload podcast artwork and system information. Option also included to email the results to a specific address.
* New Diagnostics page accessible via PowerPress > Tools > Diagnostics.
* When 'PodPress Episodes' setting enabled, we now process downloads with podpress_trac in the URL. This fixes issue for some users who have old podpress media links in their blog posts after PodPress is disabled.
* When 'PodPress Episodes' setting enabled, we now insert a player for the PodPress [display_podcast] shortcode.
* To eliminate confusion, the Mp3 Tags settings page does not appear unless you've configured Blubrry Services with hosting.
* New define added `POWERPRESS_DO_ENCLOSE_FIX`, when defined true in the wp-config.php, PowerPress will add the Media URL to your podcast episodes as a comment in your post content to prevent WordPress do_enclose() function from deleting them later. Learn more about bug here: [http://core.trac.wordpress.org/ticket/10511](http://core.trac.wordpress.org/ticket/10511)
* Changed language from "Pinging iTunes" to "Update iTunes Listing" to clear up confusion what the feature does.
* Shortocde [powerpress] now includes download and play in new window links.
* Simplified logic for pulling PodPress stored episode data.
* Updated some grammar and setting labels so they are easier to understand.
* Fixed bug with the "Use blog post author's name for individual episodes setting".
* Added Sample Rate and Channel Mode warnings, a warning is now printed if sample rate is not 22Khz or 44Khz and Channel mode is not stereo or joint stereo.


= 0.9.9 =
* Released on 9/12/2009
* No longer checking content type returned from servers, check effected video podcasters who have no control of their servers.
* Added Media URL redirect warning when redirects exceed 5, previously this was an error.
* Better detection used when server does not support HTTP URL connections, we now display a message when `allow_url_fopen` setting is disabled and cURL library not available.
* Blubrry Services Integration, added message when authentication failed and display error when web server does not support Blubrry services.
* Added new background color option for podcast episode box, configurable in the Custom Podcast Feed > Edit Feed screen.
* Made Category Feed Settings, Custom Feed Settings, Movabyle Type and PodPress import screens WordPress 2.6 compatible.
* Added wmode: 'transparent' setting to Flash player so pop-up HTML divs can display over top of the flash player.
* Added extra check to prevent page errors when displaying old PodPress data in blog pages.
* Added note in PodPress Import screen: If you are unsure about importing your PodPress data, try the option under Basic Settings titled 'PodPress Episodes' and set to 'Include in posts and feeds'.
* Added logic that detects if `safe_mode` / `open_basedir` are on, to handle redirects directly rather than in the cURL library.


= 0.9.8 =
* Released on 9/02/2009
* When we release 1.0 of PowerPress, new features will be added separately so bugs introduced by new features do not effect the existing plugin.
* Fixed bug introduced in 0.9.6, caused by the array_unshift() function. Its behavior was not consistent and has been removed. Bug caused a chain reaction of bugs, its fix resolves the following: Play in new Window error: Unable to retrieve media information, Episodes randomly not appearing in post pages, Previous PodPress created episodes no longer appearing.
* Fixed bug with publishing a new post with media file warning messages for curl_setopt() function
* Fixed bug for PodPress data that is wrongly serialized in database. WP 2.8+ get_meta_data() function was returning false rather than the damaged serialized string.
* PodPress Import improved, some cases PodPress data in the database is double serialized, new code resolves this complexity with help with bug fix above.
* Select Media screen, better message printed to user when they are not a media hosting customer.
* Fixed bug with RSS language tag for Custom Podcast Category feeds.
* Cleaned up code for jQuery/Thickbox screens.
* Added extra security checks and additional Nonces checks to ajax methods added by PowerPress.
* Added option for Blubrry Services users to be able to delete uploaded media


= 0.9.7 =
* Released on 8/31/2009
* Fixed array_unshift() for users in simple mode, bug created in 0.9.6 release.
* Fixed link to change from simple/advanced modes, bug created in 0.9.6 release.
* Fixed bug with changing Blubrry Services account email address.


= 0.9.6 =
* Released on 8/31/2009
* Added Auto Add Media option, auto adds first/last media link found in post content. (default is off)
* Added option in Appearance settings to disable player per custom podcast feed basis.
* Added option to Episode Entry Box Custom Mode to select iTunes Explicit setting on a per episode basis.
* Added note when in simple mode if there are advanced mode settings that take precedence.
* Fixed bug with language setting not getting applied to the default PowerPress custom podcast feed in some cases.
* Fixed a bug in the getid3 library where it would occasionally not detect the media duration.
* Detecting mp3 duration change, increased the amount of the file downloaded from 25k to 40k for users who have a lot of tags in their mp3 files.
* Consolidated duplicate code for detecting media information.
* Display error message when PowerPress cannot detect media file content type.
* Display error message when PowerPress cannot detect media file size and duration information.
* Display error message when PowerPress encounters an error with Blubrry Services.
* Stream lined code for loading PowerPress general and feed settings.
* Added more aggressive code to prevent WordPress from deleting enclosures when scheduling blog posts.
* Improved the look of the AJAX windows for selecting media/configuring services.
* Fixed bug where 'more' link for statistics would fail if user was not a blog admin.
* Added code to admin_head() function so css/js only included on appropriate pages (Thanks @Frumph!).
* Made Default Podcast Episode first media player listed when displaying multiple players on page.
* Blubrry Media Hosting list of uploaded media screen enhanced with new in-page media upload option and monthly quota information.
* Fixed bug with Podcast Category feed links to FeedValidator (thanks Darcy Fiander).
* Fixed bug with Podcast Category feed title not matching the feed image title FeedValidator (thanks Darcy Fiander).
* Fixed Windows Media embed issue with Firefox 3.x+ and re-added note to use the Firefox Windows Media addon.


= 0.9.5 =
* Released on 8/15/2009
* Redirect logic enhanced to track in page plays for users using the Blubrry/RawVoice/TechPodcasts statistics redirect system.
* Fixed bug where `more` link would occasionally disappear in the Blubrry PowerPress Statistics Dashboard widget.

= 0.9.4 =
* Released on 8/10/2009
* Fixed bug with setting width of video player.
* Added logic to always use the FlowPlayerClassic.swf player for flv video files.

= 0.9.3 =
* Released on 7/31/2009
* Added option for wp-config.php `define('POWERPRESS_ENABLE_HTTPS_MEDIA', 'warning');` to allow https:// links but still display a warning message.
* Finalized code for new powerpress_player filter. Filter will be used in the up coming 1.0 release of Blubrry PowerPress.
* Fixed bug with setting specific language in a custom podcast feed.

= 0.9.2 =
* Released on 07/23/2009
* Fixed logic error for new window player, now only displays embed or player, not both.
* Fixed content-type detection bug introduced in version 0.9.1.
* Added code to auto-correct the content-type in the feeds if it is invalid.
* Updated readme.txt to use the recommended Changelog format.
 
= 0.9.1 =
* Released on 7/22/2009
* Added new define for wp-config.php `define('POWERPRESS_ENABLE_HTTPS_MEDIA', true);` for allowing https:// media links.
* iTunes ping logic updated to support scheduled posts as well as category and custom podcast feeds.
* iTunes URL setting added to category and custom podcast feeds settings page.
* Latest iTunes ping status now displayed below iTunes URL setting. `in_the_loop()` check added to 0.9.0 removed which broke some themes.
* Screenshots updated.

 = 0.9.0 =
* Released on 7/20/2009
* Added `powerpress_get_enclosure_data()` and `powerpress_get_enclosure()` courtesy functions for theme developers to obtain enclosure data
* changed the icon used for selecting blubrry hosted media
* added m4b and m4r to list of detected audio formats
* relabled Redirect Feed URL to FeedBurner Feed URL to clear up confusion.
* Completely revamped the on page and new window players, removed the "play in page" options as they were a source of confusion for some users
* Added code to support poorly written themes that capture post content incorrectly, 
* New powerpress shortcode added, enter [powerpress] within your post and the player will be inserted at that location, player.
* New embed option added to episode entry box, displayed just above the built-in player.
* New option to remove the player on a per episode basis.
* New optional episode entry fields for iTunes Keywords, iTunes Subtitle and iTunes Summary. 
* Added courtesy functions `the_powerpress_content()` and `get_the_powerpress_content()` for theme developers to use for special themes. Example usage: `<?php if( function_exists('the_powerpress_content') ) the_powerpress_content(); ?>`
* Fixed bug with `require_once()` calls on some servers displaying a fatal error.
* Fixed scheduled posts bug with WordPress 2.8+.

0.8.3 released on 6/27/2009
Fixed bug with feed redirect URL setting for custom feeds, added option to disable the dashboard statistics widget, fixed 2 bugs with Blubrry hosting integration: media URL field no longer read-only after publishing and ID3 write tags bug fixed.

0.8.2 released on 5/31/2009
Fixed conflict with Twitter Updater bug when user is using PHP4 (Not a bug for PHP5 users) and fixed error on line 310 in the edit Feed settings page. Option added under tools to clear the Update Plugins Cache in WordPress. Added new user capabilities for viewing podcast statistics, for blog users who may have dashboard access but shouldn't have access to the podcast statistics.

0.8.1 released on 5/28/2009
Fixed bug with line 930 printing warning in podcast feeds.

0.8.0 released on 5/28/2009
Fixed bug with merging custom feed settings with regular feed settings, fixed bug where exerpt not used as itunes summary, added new error reporting when editing posts/pages and integrated Blubrry Services (optional), Added multi-language support to the custom podcast feeds. Added better listing of custom feeds on the main feed settings page. Add new feature under Tools section for adding Update Services / Ping sites relevant to podcasting. Added new select mode screen for new installations. Added podcast category feeds. Added statistics view to WordPress Dashboard. Write mp3 ID3 edit screen added for Blubrry Hosting users.

0.7.3 released on 4/21/2009
Fixed POWERPRESS _CONTENT _ACTION _PRIORITY define typo, incremented version check (PowerPress now requires WordPress 2.6.x), fixed bug where player.js was not included in the header if simple mode was used, made the enhanced itunes summary set on by default for new installations, detecting additional file types, add new import episodes previously created in Blogger/Movable Type, fix bug where channel link matches image link, no longer including empty duration values in feeds, fixed bug with curl-setopt function printing a PHP warning in some situations, added warning when user enters an unknown media redirect url.

0.7.2 released on 3/26/2009
Re-ordered change log so latest release is listed first rather than last. Fixed powerpress_bloginfo_rss() warning message that occurred for some users, Updated the itunes_url setting to handle the latest itunes URL itunes.apple.com, No longer including revision posts in the PodPress import episode utility, Fixed bug with iTunes author setting not getting applied to feeds, display player in excerpts checkbox bug fixed, no longer including player code in HTML headers if not necessary, fixed duration bug with 1:00:60 which now reports 1:01:00, feed settings are now used for custom feeds that do not have settings of their own, added additional fix for FeedSmith when permalinks is not enabled, fixed bug where feed settings were not being applied to podcast feed in simple mode under some situations, fixed bug with enhance podcast only and main site feed only setting, fixed bug with pinging iTunes with CURL missing SSL certificates, and fixed itunes ping for latest itunes.apple.com one click subscribe links.

0.7.1 released on 03/11/2009
Bugs: Improved flash player loading on pages with multiple podcast episodes, Detecting file size when specifying duration bug fixed. Fixed bug where duration would sometimes save minutes over 60 rather than rolling over one hour.
Features: Added new Simple and Advanced Modes. Advance mode adds 5 new pages (Basic, Appearance, Feed Settings, Custom Feeds and Tools). New Edit Post settings section added which allows user to toggle between a simple and normal edit episode boxes. The simple mode displays only the Media URL field. Users can now set the width of the audio player as well as the width and height of the video player. New language added to the Feed Settings page to better explain the apply settings to setting which is now renamed "Enhance Feeds" setting. Added new Enhance iTunes Summary from Blog Posts option, which intelligently takes web links and links to images in your regular blog post into a readable and clickable format for the iTunes summary. Custom feeds including the default podcast feed include additional options such as setting the number of most recent posts in the feed, customizing the feed title, description and landing web link, as well as full support for redirecting feed to a hosted feed service such as FeedBurner.com. The new Custom Feeds section allows user to create an endless number of podcast specific feeds either for separating long and sort formats and/or media types such as ogg, wma and mp3. New Tools section allows users to re-import PodPress settings (typically imported automatically upon first install), Import PodPress episodes with a detailed table of media files with options to specify which media files go to which feeds. Tools page also includes an option to add Edit Podcast Capability for User Role Management. Added Nonces support to all edit and delete transactions to admin pages. Added simple mode for podcast episode boxes in the edit post screen.

0.7.0 released on 03/10/2009
Beta release only intended for a handfull of beta testers. For full list of changes, please refer to version 0.7.1

0.6.5 released on 02/20/2009
Fixed warning from being printed when v0.6.3/v0.6.4 used with PHP4. Bug only affected users using PHP4.

0.6.4 released on 02/16/2009
Fixed bug where post_password_required() function does not exist, bug only affects users using Wordpress blogs older than version 2.7.

0.6.3 released on 02/16/2009
Added new options to load all javascript for players in the wp_footer() function. Options are available as defines to add to the wp-config.php and are documented near the top of the powerpress.php. Added option in settings to display player in excerpts. Added code to repair corrupted Podpress data for displaying previously created podpress episodes. Note: Podpress data corruption was originally caused by previous versions of Podpress. Added code to prevent Wordpress from auto adding enclosures created from links to media found in the blog post.

0.6.2 released on 01/26/2009
Added option to reset rewrite rules when settings saved to fix problem with podcast feed returning 404, logic added to prevent FeedSmith plugin from redirecting podcast feed, and added support for the Kimili Flash Embed plugin

0.6.1 released on 01/20/2009
Player now handles Windows Media (wmv) in Firefox, offering link to preferred Firefox plugin, now using the wp_specialchars() function for adding entities to feed values, fix problem with themes using excerpts not displaying the player correctly (Thanks @wayofthegeek for your help), and a number of other syntactical changes.

0.6.0 released on 12/17/2008
Fixed bug with podcast feed in Wordpress 2.7, added defaults for file size and duration, and added iTunes New Feed URL option.

0.5.2 released on 12/14/2008
Fixed bug with the feed channel itunes:summary being limited to 255 characters, the limit is now set to 4,000.

0.5.1 released on 12/10/2008
Added podcast to pages option (Thanks @Frumph), added code to make sure the itunes:subtitle, keywords and summary feed tags never exceed their size limits.

0.5.0 released on 11/26/2008
Added options to report media duration and file size next to download links. Removed optional defines POWERPRESS _ PLUGIN _ PATH and POWERPRESS _ ITEM _ SUMMARY, defines no longer necessary. Added itunes:author and itunes:subtitle to header portion of podcast feeds and itunes:summary to post items. No longer removing quotes or extra spaces from itunes summary. Player auto-play bug fixed when quicktime files mixed with mp3s. Added new option to ping iTunes in a new browser window. Verify flash player check added, Wordpress auto plugin update will corrupt the flash player. This bug is fixed in Wordpress 2.7 beta 2 and newer. Media URL now displays a warning if the value contains characters which may cause problems.

0.4.2 released on 11/02/2008
Fixed quicktime in-page player bug, fixed bug which caused itunes keywords and subtitle to be blank and incremented version number.

0.4.1 released on 10/24/2008
Fixed auto-play bug found in last update, only affected quicktime files with the play on page option.

0.4.0 released on 10/21/2008
Added two new play options adding 'play on page' links with and without play in new window links and now use a customizable play image for quicktime formatted media. Image may be customized by adding a define('POWERPRESS _ PLAY _ IMAGE', 'URL to image') to wp config file.

0.3.2 released on 10/05/2008
Added alternative logic for those who host their blogs on servers with allow _ url _ fopen turned off.

0.3.1 released on 10/02/2008
Fixed bug and added enhancements: iTunes subtitle, keywords and summary values now properly escape html special characters such as &nbsp; added define for adding itunes:new-feed-url tag, added define to display player for legacy Podpress episodes only.

0.3.0 released on 09/24/2008
New features: Added important feeds list in feed settings, logic to prevent stats redirect duplication and added podcast only feed.

0.2.1 released on 09/17/2008
Fixed bugs: itunes:subtitle bug, itunes:summary is now enabled by default, add ending trailing slash to media url if missing, and copy blubrry keyword from podpress fix.

0.2 released on 08/11/2008
Initial release of Blubrry PowerPress


== Contributors ==
Angelo Mandato, CIO [RawVoice](http://www.rawvoice.com) - Plugin founder, architect and lead developer

Pat McSweeny, Developer for [RawVoice](http://www.rawvoice.com) - Developed initial version (v0.1.0) of plugin

Jerry Stephens, Way of the Geek [wayofthegeek.org](http://wayofthegeek.org) - Contributed initial code fix for excerpt bug resolved in v0.6.1

Darcy Fiander, Rooty Radio (http://rootyradio.com) - Fixed bug with category links to FeedValidator.org and category title warning when validating category feeds.


== Feedback == 
 http://www.blubrry.com/powerpress/

== Support == 
 http://help.blubrry.com/blubrry-powerpress/

== Twitter == 
 http://twitter.com/blubrry
