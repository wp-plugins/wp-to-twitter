=== Plugin Name ===
Contributors: joedolson
Donate link: http://www.joedolson.com/donate.php
Tags: twitter, microblogging, cligs, redirect, shortener, post, links
Requires at least: 2.5
Tested up to: 2.8.2
Stable tag: trunk

Posts a Twitter status update when you update your WordPress blog or post to your blogroll, using the Cligs URL shortening service.

== Description ==

The WP-to-Twitter plugin posts a Twitter status update from your WordPress blog using the Cli.gs URL shortening service to provide a link back to your post from Twitter. 

If you have a Cli.gs API key, the shortened URL will also be filed in your Cli.gs account so that you can track statistics for the shortened URL. 

The plugin can send a default message for updating or editing posts or pages, but also allows you to write a custom Tweet for your post which says whatever you want. By default, the shortened URL from Cli.gs is appended to the end of your message, so you should keep that in mind when writing your custom Tweet. 

Any status update you write which is longer than the available space will automatically be truncated by the plugin. This applies to both the default messages and to your custom messages.

This plugin is based loosely on the Twitter Updater plugin by [Jonathan Dingman](http://www.firesidemedia.net/dev/), which he adapted from a plugin by Victoria Chan. Other contributions by [Thor Erik](http://www.thorerik.net), Bill Berry and [Andrea Baccega](http://www.andreabaccega.com).

== Changelog ==

= 1.4.0 =

* Added support for the Bit.ly URL shortening service.
* Added option to not use URL shortening.
* Added option to add tags to end of status update as hashtag references.
* Fixed a bug where the #url# shortcode failed when editing posts.
* Reduced some redundant code.
* Converted version notes to new Changelog format.

= 1.3.7 = 

* Revised interface to take advantage of features added in versions 2.5 and 2.7. You can now drag and drop the WP to Twitter configuration panel in Post and Page authoring pages.
* Fixed bug where post titles were not Tweeted when using the "Press This" bookmarklet
* Security bug fix.

= 1.3.6 =

*Bug fix release.

= 1.3.5 =

* Bug fix: when "Send link to Twitter" is disabled, Twitter status and shortcodes were not parsed correctly.

= 1.3.4 = 

* Bug fix: html tags in titles are stripped from tweets
* Bug fix: thanks to [Andrea Baccega](http://www.andreabaccega.com), some problems related to WP 2.7.1 should be fixed. 
* Added optional prepend/append text fields.

= 1.3.3 =

* Added support for shortcodes in custom Tweet fields.
* Bug fix when #url# is the first element in a Tweet string.
* Minor interface changes.

= 1.3.2 =

* Added a #url# shortcode so you can decide where your short URL will appear in the tweet.
* Couple small bug fixes.
* Small changes to the settings page.

= 1.3.1 = 

* Modification for multiple authors with independent Twitter accounts -- there are now three options:
 
	1. Tweet to your own account, instead of the blog account. 
	1. Tweet to your account with an @ reference to the main blog account. 
	1. Tweet to the main blog account with an @ reference to your own account.  
	
* Added an option to enable or disable Tweeting of Pages when edited. 
* **Fixed scheduled posting and posting from QuickPress, so both of these options will now be Tweeted.**

= 1.3.0 = 

*Support for multiple authors with independent Twitter & Cligs accounts. 
*Other minor textual revisions, addition of API availability check in the Settings panel. 
*Bugfixes: If editing a post by XMLRPC, you could not disable tweeting your edits. FIXED. 

= 1.2.8 =

*Bug fix to 1.2.7.

= 1.2.7 =

*Uses the Snoopy class to retrieve information from Cligs and to post Twitter updates. Hopefully this will solve a variety of issues.
*Added an option to track traffic from your Tweeted Posts using Google Analytics (Thanks to [Joost](http://yoast.com/twitter-analytics/))

= 1.2.6 =

*Bugfix with XMLRPC publishing -- controls to disable XMLRPC publishing now work correctly.
*Bugfix with error reporting and clearing.
*Added the option to supply an alternate URL along with your post, to be tweeted in place of the WP permalink.

= 1.2.5 =
 
*Support for publishing via XMLRPC 
*Corrected a couple minor bugs 
*Added internationalization support
 
= 1.2.0 =
 
*option to post your new blogroll links to Twitter, using the description field as your status update text.
*option to decide on a post level whether or not that blog post should be posted to Twitter
*option to set a global default 'to Tweet or not to Tweet.'

= 1.1.0 =

*Update to use cURL as an option to fetch information from the Cli.gs API.

== Installation ==

1. Upload the `wp-to-twitter` folder to your `/wp-content/plugins/` directory
2. Activate the plugin using the `Plugins` menu in WordPress
3. Go to Settings > WP->Twitter
4. Adjust the WP->Twitter Options as you prefer them. 
5. Supply your Twitter username and login.
6. **Optional**: Provide your Cli.gs API key ([available free from Cli.gs](http://cli.gs)), if you want to have statistics available for your URL.
7. That's it!  You're all set.

== Frequently Asked Questions ==

= Do I have to have a Twitter.com account to use this plugin? =

Yes, you need an account to use this plugin.

= Do I have to have a Cli.gs account to use this plugin? =

No, the Cli.gs account is entirely optional. Without a Cli.gs API, a "public" Clig will be generated. The redirect will work just fine, but you won't be able to access statistics on your Clig.

= Twitter goes down a lot. What happens if it's not available? =

If Twitter isn't available, you'll get a message telling you that there's been an error with your Twitter status update. The Tweet you were going to send will be saved in your post meta fields, so you can grab it and post it manually if you wish.

= What if Cli.gs isn't available when I make my post? =

If Cli.gs isn't available, your tweet will be sent using it's normal post permalink. You'll also get an error message letting you know that there was a problem contacting Cli.gs.

= What if my server doesn't support the methods you use to contact these other sites? =

Well, there isn't much I can do about that - but the plugin will check and see whether or not the needed methods work. If they don't, you will find a warning message on your settings page. 

= If I mark a blogroll link as private, will it be posted to Twitter? =

No. They're private. 

= I can't see the settings page! =

There’s an unresolved bug which effects some servers which causes the WP-to-Twitter settings page to fail. You can get around this problem by commenting out lines 191 - 256 in wp-to-twitter/wp-to-twitter-manager.php. (Version 1.4.0.) 

= Scheduled posting doesn't work. What's wrong? =

Only posts which you scheduled or edited *after* installing the plugin will be Tweeted. Any future posts written before installing the plugin will be ignored by WP to Twitter.

== Screenshots ==

1. WP to Twitter main settings page.
2. WP to Twitter custom Tweet settings.
3. WP to Twitter user settings.