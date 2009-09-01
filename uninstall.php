<?php
if ( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit();
} else {
delete_option( 'newpost-published-update' );
delete_option( 'newpost-published-text' );
delete_option( 'newpost-published-showlink' );

delete_option( 'oldpost-edited-update' );
delete_option( 'oldpost-edited-text' );
delete_option( 'oldpost-edited-showlink' );

delete_option( 'jd_twit_pages' );
delete_option( 'jd_twit_edited_pages' );

delete_option( 'jd_twit_remote' );

delete_option( 'jd_post_excerpt' );

// Use Google Analytics with Twitter
delete_option( 'twitter-analytics-campaign' );
delete_option( 'use-twitter-analytics' );

// Use custom external URLs to point elsewhere. 
delete_option( 'jd_twit_custom_url' );

// Cligs API
delete_option( 'cligsapi' );

// Error checking
delete_option( 'jd-functions-checked' );
delete_option( 'wp_twitter_failure' );
delete_option( 'wp_cligs_failure' );
delete_option( 'wp_url_failure' );
delete_option( 'wp_bitly_failure' );

// Blogroll options
delete_option( 'jd-use-link-title' );
delete_option( 'jd-use-link-description' );
delete_option( 'newlink-published-text' );
delete_option( 'jd_twit_blogroll' );

// Default publishing options.
delete_option( 'jd_tweet_default' );
// Note that default options are set.
delete_option( 'twitterInitialised' );
delete_option( 'wp_twitter_failure' );
delete_option( 'wp_cligs_failure' );
delete_option( 'twitterlogin' );
delete_option( 'twitterpw' );
delete_option( 'twitterlogin_encrypted' );
delete_option( 'cligsapi' );
delete_option( 'jd_twit_quickpress' );
delete_option( 'jd-use-cligs' );
delete_option( 'jd-use-none' );

// Special Options
delete_option( 'jd_twit_prepend' );
delete_option( 'jd_twit_remote' );
delete_option( 'twitter-analytics-campaign' );
delete_option( 'use-twitter-analytics' );
delete_option( 'jd_twit_custom_url' );
delete_option( 'jd_shortener' );
delete_option( 'jd_twit_append' );
delete_option( 'jd_individual_twitter_users' );
delete_option( 'use_tags_as_hashtags' );

// Bitly Settings
delete_option( 'bitlylogin' );
delete_option( 'jd-use-bitly' );
delete_option( 'bitlyapi' );
}
?>