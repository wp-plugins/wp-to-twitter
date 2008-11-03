=== Plugin Name ===
Contributors: joedolson
Donate link: http://www.joedolson.com/donate/
Tags: twitter, microblogging, cligs, redirect, shortener
Requires at least: 2.3
Tested up to: 2.6
Stable tag: 1.0

Posts a Twitter status update when you update your blog, using the Cli.gs URL shortening service.

== Description ==

The WP-to-Twitter plugin posts a Twitter status update from your blog using the Cli.gs URL shortening service to 
provide a link back to your post from Twitter. 

If you have a Cli.gs API key, the shortened URL will also be filed in your Cli.gs account so that you can track
statistics for the shortened URL. 

The plugin can send a default message for updating or editing posts or pages, but also allows you to write a custom
Tweet for your post which says whatever you want. By default, the shortened URL from Cli.gs is appended to the end
of your message, so you should keep that in mind when writing your custom Tweet. 

Any status update you write which is longer than the available space will automatically be truncated by the plugin. This applies to both the default messages and to your custom messages.

This plugin was based on the Twitter Updater plugin by Jonathan Dingman (http://www.firesidemedia.net/dev/), which he adapted from a plugin by Victoria Chan. 

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

== Screenshots ==

1. WP to Twitter custom Tweet box
2. WP to Twitter options page