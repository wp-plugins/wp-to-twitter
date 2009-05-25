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

// Use Google Analytics with Twitter
delete_option( 'twitter-analytics-campaign' );
delete_option( 'use-twitter-analytics' );

// Use custom external URLs to point elsewhere. 
delete_option( 'jd_twit_custom_url' );

// Cligs API
delete_option( 'cligsapi' );

// Error checking
delete_option( 'jd_functions_checked' );
delete_option( 'wp_twitter_failure' );
delete_option( 'wp_cligs_failure' );

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
}
?>