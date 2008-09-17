=== Blubrry Powerpress Podcasting plugin ===
Contributors: Angelo Mandato, Blubrry.com
Tags: podcast, podcasting, itunes, enclosure, zune, iphone, audio, video, rss2, feed, player, media, rss
Requires at least: 2.5.0
Tested up to: 2.6.2
Stable tag: 0.2.1

Add podcasting support to your blog.

== Description ==
The Blubrry Powerpress Podcast Plugin has all of the essential features needed to provide podcasting support in a Wordpress blog.

The Blubrry Powerpress interface allows you to easily add/modify/remove podcast episodes from blog posts and includes a simple on-line media player, iTunes compatibile RSS feed tags, ability to upload cover art images, ping iTunes, detect media size, detect time duration (mp3's only) and add 3rd party media statistics.

Features:

* Easily add/modify/remove podcast episodes from blog posts
* Integrated media player
* iTunes RSS tags
* iTunes album/cover art
* upload new iTunes/RSS cover art
* Ping iTunes
* Media size detection
* Duration detection (mp3 only)
* 3rd party statistics integration

For the latest information visit the website.

http://www.blubrry.com/powerpress/

== Frequently Asked Questions ==

 = Why doesn't Blubrry Powerpress support multiple enclosures? =
 Blubrry Powerpress does not support multiple enclosures in one blog post. This is because each podcatcher handles multiple enclosures differently. iTunes will download the first enclosure that it sees in the feed ignoring the rest. Other podcatchers and podcasting directories either pick up the first enclosure or the last in each post item. This inconsistancy and combined with the fact that [Dave Winer does not recommend multiple enclosures](http://www.reallysimplesyndication.com/2004/12/21) is why the Blubrry Powerpress does not support them.

 = Why doesn't Blubrry Powerpress include media statistics? =
 Blubrry Powerpress does not include media statistics. This is not because Blubrry has its own statistics service, although that's a good reason by itself. Maintaining and calculating statistics is a resource and server intensive task that would add bloat to an otherwise lightweight Wordpress podcasting plugin. We recommend you use your media hosting's statistics and you're more than welcome to use the [Blubrry Statistics service](http://www.blubrry.com/podcast_statistics/) as well.

 = Looking for a better Audio Player? =
 Check out the <a href="http://wpaudioplayer.com" target="_blank" title="WP Audio Player 2.0">WP Audio Player 2.0</a>. The WP Audio Player 2.0 options include theme colors, initial volume, player width and more.
	
== Installation ==
1. Copy the entire directory from the downloaded zip file into the /wp-content/plugins/ folder.
2. Activate the "Blubrry Powerpress" plugin in the Plugin Management page.
3. Configure your Blubrry Powerpress by going to the **Settings** > **Blubrry Powerpress** page.
		
== Screenshots ==
1. Add podcast episode, found within the edit post screen
2. Cross section of Blubrry Powerpress settings page.

== Changelog ==

0.2 released on 08/11/2008
Initial release of Blubrry Powerpress

0.2.1 released on 09/17/2008
Fixed bugs: itunes:subtitle bug, itunes:summary is now enabled by default, add ending trailing slash to media url if missing, and copy blubrry keyword from podpress fix.

== Feedback == 
http://www.blubrry.com/powerpress/
