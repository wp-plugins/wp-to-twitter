<?php
/*
Plugin Name: WP to Twitter
Plugin URI: http://www.joedolson.com/articles/wp-to-twitter/
Description: Updates Twitter when you create a new blog post using Cli.gs. With a Cli.gs API key, creates a clig in your Cli.gs account with the name of your post as the title.
Version: 1.1.1
Author: Joseph Dolson
Author URI: http://www.joedolson.com/
*/

/*  Copyright 2008  Joseph C Dolson  (email : wp-to-twitter@joedolson.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
$jd_plugin_url = "http://www.joedolson.com/articles/wp-to-twitter/";
// This function performs the API post to Twitter
function jd_doTwitterAPIPost( $twit, $twitterURI ) {
	$host = 'twitter.com';
	$port = 80;
	$fp = @fsockopen($host, $port, $err_num, $err_msg, 10);

	//check if user login details have been entered on admin page
	$thisLoginDetails = get_option( 'twitterlogin_encrypted' );

	if ( $thisLoginDetails != '' )	{
		if ( !is_resource($fp) ) {
			#echo "$err_msg ($err_num)<br>\n"; // Fail Silently, but you could turn these back on...
			return FALSE;
		} else {
		$response = TRUE;
			if (!fputs( $fp, "POST $twitterURI HTTP/1.1\r\n" )) {
			return FALSE;
			}
			fputs( $fp, "Authorization: Basic ".$thisLoginDetails."\r\n" );
			fputs( $fp, "User-Agent: ".$agent."\n" );
			fputs( $fp, "Host: $host\n" );
			fputs( $fp, "Content-type: application/x-www-form-urlencoded\n" );
			fputs( $fp, "Content-length: ".strlen( $twit )."\n" );
			fputs( $fp, "Connection: close\n\n" );
			fputs( $fp, $twit );
			for ( $i = 1; $i < 10; $i++ ) {
				$reply = fgets( $fp, 256 );
				}
			fclose( $fp );
			return $response;
		}
	} else {
		// no username/password: return an empty string
		return FALSE;
	}
}

// cURL query contributed by Thor Erik (http://thorerik.net)
function getfilefromurl($url) {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_VERBOSE, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	$output = curl_exec( $ch );
	curl_close( $ch );
	return $output;
}

function jd_twit( $post_ID )  {
    $twitterURI = "/statuses/update.xml";
    $thisposttitle = urlencode( stripcslashes( $_POST['post_title'] ) );
    $thispostlink = get_permalink( $post_ID );
	$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
	$cligsapi = get_option( 'cligsapi' );
    $sentence = '';
	$customTweet = stripcslashes( $_POST['jd_twitter'] );
	$oldClig = get_post_meta( $post_ID, 'wp_jd_clig', TRUE );

		if ( $_POST['publish'] == 'Publish' ){
			// publish new post
			if ( get_option( 'newpost-published-update' ) == '1' ) {
				$sentence = get_option( 'newpost-published-text' );
				if ( get_option( 'newpost-published-showlink') == '1' ) {
					// Generate and grab the clig using the Cli.gs API
					// cURL alternative contributed by Thor Erik (http://thorerik.net)

					$shrink = @file_get_contents( "http://cli.gs/api/v1/cligs/create?url=".$thispostlink."&title=".$thisposttitle."&key=".$cligsapi."&appid=WP-to-Twitter" );
					if ( $shrink === FALSE ) {
						$shrink = getfilefromurl( "http://cli.gs/api/v1/cligs/create?url=".$thispostlink."&title=".$thisposttitle."&key=".$cligsapi."&appid=WP-to-Twitter" );
					}	
					if ( $shrink === FALSE ) {
					update_option('wp_cligs_failure','1');		
					$shrink = $thispostlink;
					}
				$sentence = $sentence . " " . $shrink;
					
					if ( $customTweet != "" ) {
					// Get the custom Tweet message if it's been supplied. Truncate it to fit if necessary.
						if ( get_option( 'newpost-published-showlink') == '1' ) {
							if ( ( strlen( $customTweet ) + 21) > 140 ) {
							$customTweet = substr( $customTweet, 0, 119 );
							}
						} else {
							if ( strlen( $customTweet ) > 140 ) {
							$customTweet = substr( $customTweet, 0, 140 );
							}						
						}
						if ( get_option( 'newpost-published-showlink') == '1' ) {						
						$sentence = $customTweet . " " . $shrink;							
						} else {
						$sentence = $customTweet;
						}
					} else {
					    // Check the length of the tweet and truncate parts as necessary.
						$twit_length = strlen( $sentence );
						$title_length = strlen( $thisposttitle );
						$blog_length = strlen( $thisblogtitle );
						if ( ( ( $twit_length + $title_length ) -  7 ) < 140 ) {
						$sentence = str_replace( '#title#', $thisposttitle, $sentence );
						$twit_length = strlen( $sentence );				
						} else {
						$thisposttitle = substr( $thisposttitle, 0, ( 140- ( $twit_length-3 ) ) ) . "...";
						$sentence = str_replace ( '#title#', $thisposttitle, $sentence );
						$twit_length = strlen( $sentence );
						}
						if ( ( ( $twit_length + $blog_length ) -  6 ) < 140 ) {
						$thisblogtitle = substr( $thisblogtitle, 0, ( 140-( $twit_length-3 ) ) ) . "...";				
						$sentence = str_replace ( '#blog#',$thisblogtitle,$sentence );
						}
					}
					// Stores the posts CLIG in a custom field for later use as needed.
					add_post_meta ( $post_ID, 'wp_jd_clig', $shrink );
/* This is for testing. Creates a post meta field containing the Cli.gs API request string
//add_post_meta($post_ID, 'post_cligs_text',"http://cli.gs/api/v1/cligs/create?url=".$thispostlink."&title=".$thisposttitle."&key=".$cligsapi."&appid=WP-to-Twitter"); */			
				}
			}
		} else if ( ( $_POST['originalaction'] == "editpost" ) && ( $_POST['prev_status'] == 'publish' ) ) {
			// if this is an old post and editing updates are enabled
			if ( get_option( 'oldpost-edited-update') == '1' ) {
				$sentence = get_option( 'oldpost-edited-text' );
				if ( get_option( 'oldpost-edited-showlink') == '1') {
					if ( $oldClig != '' ) {
					$old_post_link = $oldClig;
					} else {
					$old_post_link = $thispostlink;
					}
					$thisposttitle = $thisposttitle . ' (' . $old_post_link . ')';
				}
				$sentence = str_replace( '#title#', $thisposttitle, $sentence );
				$sentence = str_replace( '#blog#',$thisblogtitle,$sentence );
				
			}
		}
		
		if ( $sentence != '' ) {
			$sendToTwitter = jd_doTwitterAPIPost( 'source=wptotwitter&status='.$sentence, $twitterURI );
			if ($sendToTwitter === FALSE) {
			add_post_meta( $post_ID,'jd_wp_twitter',$sentence);
			update_option('wp_twitter_failure','1');
			}
		}
  
    return $post_ID;
}

// Add custom Tweet field on Post & Page write/edit forms
function jd_add_twitter_textinput() {
	global $post;
	$post_id = $post;
	if (is_object($post_id)) {
		$post_id = $post_id->ID;
	}
	$jd_twitter = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'jd_twitter', true)));
	?>
	<script type="text/javascript">
	<!-- Begin
	function countChars(field,cntfield) {
	cntfield.value = field.value.length;
	}
	//  End -->
	</script>
	<?php /* Compatibility with version 2.3 and below (needs to be tested.) */ ?>
	<?php if (substr(get_bloginfo('version'), 0, 3) >= '2.5') { ?>
	<div id="wp-to-twitter" class="postbox closed">
	<h3><?php _e('WP to Twitter', 'wp-to-twitter') ?></h3>
	<div class="inside">
	<div id="jd-twitter">
	<?php } else { ?>
	<div class="dbx-b-ox-wrapper">
	<fieldset id="twitdiv" class="dbx-box">
	<div class="dbx-h-andle-wrapper">
	<h3 class="dbx-handle"><?php _e('WP to Twitter', 'wp-to-twitter') ?></h3>
	</div>
	<div class="dbx-c-ontent-wrapper">
	<div class="dbx-content">
	<?php } ?>

	<input value="jd_twit_edit" type="hidden" name="jd_twit_edit" />

	<label for="jd_twitter"><?php _e('Twitter Post', 'wp-to-twitter') ?></label><br /><textarea name="jd_twitter" id="jd_twitter" rows="2" cols="60"
	onKeyDown="countChars(document.post.jd_twitter,document.post.twitlength)"
	onKeyUp="countChars(document.post.jd_twitter,document.post.twitlength)"><?php echo $jd_twitter ?></textarea>
	<p><input readonly type="text" name="twitlength" size="3" maxlength="3" value="<?php echo strlen( $description); ?>" />
	<?php _e(' characters. Twitter posts are a maximum of 140 characters; if your Cli.gs URL is appended to the end of your document, you have 119 characters available.', 'wp-to-twitter') ?> <a target="__blank" href="<?php echo $jd_plugin_url; ?>"><?php _e('Get Support', 'wp-to-twitter') ?></a> &raquo;
</p>
	<?php if (substr(get_bloginfo('version'), 0, 3) >= '2.5') { ?>
	</div></div></div>
	<?php } else { ?>
	</div>
	</fieldset>
	</div>
	<?php } ?>

	<?php
}
// Post the Custom Tweet into the post meta table
function post_jd_twitter( $id ) {
	$jd_twit_edit = $_POST["jd_twit_edit"];
	if (isset($jd_twit_edit) && !empty($jd_twit_edit)) {
		$jd_twitter = $_POST["jd_twitter"];
		delete_post_meta( $id, 'jd_twitter' );
			if (isset($jd_twitter) && !empty($jd_twitter)) {
				add_post_meta( $id, 'jd_twitter', $jd_twitter );
			}
	}
}

// Add the administrative settings to the "Settings" menu.
function jd_addTwitterAdminPages() {
    if ( function_exists( 'add_submenu_page' ) ) {
		 add_options_page( 'WP -> Twitter', 'WP -> Twitter', 8, __FILE__, 'jd_wp_Twitter_manage_page' );
    }
 }
// Include the Manager page
function jd_wp_Twitter_manage_page() {
    include(dirname(__FILE__).'/wp-to-twitter-manager.php' );
}
function plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/wp-to-twitter.php'))
		$links[] = "<a href='options-general.php?page=wp-to-twitter/wp-to-twitter-manager.php'>" . __('Settings', 'wp-to-twitter') . "</a>";
	return $links;
}

//Add Plugin Actions to WordPress

add_filter('plugin_action_links', 'plugin_action', -10, 2);

if ( substr( get_bloginfo( 'version' ), 0, 3 ) >= '2.5' ) {
	add_action( 'edit_form_advanced','jd_add_twitter_textinput' );
	if ( get_option( 'jd_twit_pages')=='1') {
	add_action( 'edit_page_form','jd_add_twitter_textinput' );
	}
} else {
	add_action( 'dbx_post_advanced','jd_add_twitter_textinput' );
	if (get_option( 'jd_twit_pages')=='1') {
	add_action( 'dbx_page_advanced','jd_add_twitter_textinput' );
	}
}
if ( get_option( 'wp_twitter_failure' ) == '1' || get_option( 'wp_cligs_failure' ) == '1' ) {
add_action('admin_notices', create_function( '', "echo '<div class=\"error\"><p>There\'s been an error posting your Twitter status! <a href=\"".get_bloginfo('wpurl')."/wp-admin/options-general.php?page=wp-to-twitter/wp-to-twitter.php\">Visit your WP to Twitter settings page</a> to get more information and to clear this error message.</div>';" ) );
}

if ( get_option( 'jd_twit_pages')=='1') {
	add_action( 'publish_page', 'jd_twit' );
	add_action( 'edit_page','post_jd_twitter' );
	add_action( 'publish_page','post_jd_twitter' );
}
add_action( 'publish_post', 'jd_twit' );
add_action( 'admin_menu', 'jd_addTwitterAdminPages' );
add_action( 'edit_post','post_jd_twitter' );
add_action( 'publish_post','post_jd_twitter' );
?>