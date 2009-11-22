<?php
/*
Plugin Name: WP to Twitter
Plugin URI: http://www.joedolson.com/articles/wp-to-twitter/
Description: Updates Twitter when you create a new blog post or add to your blogroll using Cli.gs. With a Cli.gs API key, creates a clig in your Cli.gs account with the name of your post as the title.
Version: 1.5.3
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
global $wp_version,$version,$jd_plugin_url;	

$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'wp-to-twitter', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );

define('JDWP_API_POST_STATUS', 'http://twitter.com/statuses/update.json');

$version = "1.5.3";
$jd_plugin_url = "http://www.joedolson.com/articles/wp-to-twitter/";
$jd_donate_url = "http://www.joedolson.com/donate.php";

if ( !defined( 'WP_PLUGIN_DIR' ) ) {
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	  }
require_once( ABSPATH.WPINC.'/class-snoopy.php' );

// Check whether a supported version is in use.
$exit_msg='WP to Twitter requires WordPress 2.5 or a more recent version. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update your WordPress version!</a>';

	if ( version_compare( $wp_version,"2.5","<" )) {
	exit ($exit_msg);
	}
	
// include service functions
require_once( WP_PLUGIN_DIR . '/wp-to-twitter/functions.php' );
	
function wptotwitter_activate() {
		if ( get_option( 'jd-use-link-description' ) == 1 || get_option( 'jd-use-link-description' ) == 0 ) {
		delete_option( 'jd-use-link-description' );
		update_option( 'newlink-published-text', "New Link Posted: #description#" );		
		} 
		if ( get_option( 'jd-use-link-title' ) == 0 || get_option( 'jd-use-link-title' ) == 1 ) {
		delete_option( 'jd-use-link-title' );
		update_option( 'newlink-published-text', "New Link Posted: #title#" );
		}
		if ( get_option( 'newpost-published-showlink' ) == 1 ) {
		delete_option( 'newpost-published-showlink' );	
		$update = get_option( 'newpost-published-text' ) . " #url#";
		update_option( 'newpost-published-text', $update );
		}	
		if (get_option( 'oldpost-edited-showlink' ) == 1 ) {
		delete_option( 'oldpost-edited-showlink' );		
		$update = get_option( 'oldpost-edited-text' ) . " #url#";
		update_option( 'oldpost-edited-text', $update );		
		}
		if ( get_option( 'jd_post_excerpt' ) == "" ) {
		update_option( 'jd_post_excerpt', 25 );
		}
		if ( get_option( 'jd_max_tags' ) == "" ) {
		update_option( 'jd_max_tags', 3 );
		}
		if (get_option( 'jd_max_characters' ) == "" ) {
		update_option( 'jd_max_characters', 5 );
		}
}	
	
// Function checks for an alternate URL to be tweeted. Contribution by Bill Berry.	
function external_or_permalink( $post_ID ) {
       $wtb_extlink_custom_field = get_option('jd_twit_custom_url'); 
       $perma_link = get_permalink( $post_ID );
       $ex_link = get_post_meta($post_ID, $wtb_extlink_custom_field, true);
       return ( $ex_link ) ? $ex_link : $perma_link;
}

// This function performs the primary API post to Twitter
function jd_doTwitterAPIPost( $twit, $authID=FALSE ) {
	global $version, $jd_plugin_url;
	//check if user login details have been entered on admin page
	if ($authID == FALSE || ( get_option( 'jd_individual_twitter_users' ) != '1' ) ) {
	$thisuser = get_option( 'twitterlogin' );
	$thispass = get_option( 'twitterpw' );
	} else {
		if ( ( get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'true' || get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'userTwitter' || get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'userAtTwitter' ) && ( get_usermeta( $authID, 'wp-to-twitter-user-username' ) != "" && get_usermeta( $authID, 'wp-to-twitter-user-password' ) != "" ) ) {	
			$thisuser = get_usermeta( $authID, 'wp-to-twitter-user-username' );
			$thispass = get_usermeta( $authID, 'wp-to-twitter-user-password' );
		} else {
			$thisuser = get_option( 'twitterlogin' );
			$thispass = get_option( 'twitterpw' );		
		}
	}
	if ($thisuser == '' || $thispass == '' || $twit == '' ) {
	return FALSE;
	} else {
	$twit = urldecode($twit);
	$tweet = new Snoopy;
        if (!empty($tweet)) {
	$tweet->agent = 'WP to Twitter $jd_plugin_url';
	$tweet->rawheaders = array(
		'X-Twitter-Client' => 'WP to Twitter'
		, 'X-Twitter-Client-Version' => $version
		, 'X-Twitter-Client-URL' => 'http://www.joedolson.com/scripts/wp-to-twitter.xml'
	);
	$tweet->user = $thisuser;
	$tweet->pass = $thispass;
	$tweet->submit(
		JDWP_API_POST_STATUS
		, array(
			'status' => $twit
			, 'source' => 'wptotwitter'
		)
	);
		if (strpos($tweet->response_code, '200')) {
		return TRUE;
		} else {
			if (jd_old_doTwitterAPIPost( $twit, $authID ) == TRUE) {
			return TRUE;
			} else {
			return FALSE;
			}
		}
        } else {
		if (jd_old_doTwitterAPIPost( $twit ) == TRUE) {
		return TRUE;
		} else {
// If you're attempting to solve the "settings page doesn't display" problem, you're in the wrong file. 
// Please open /wp-to-twitter-manager.php to make your edits.		
		return FALSE;
		}         
        }
	}
}
// This function used to perform the API post to Twitter and now serves as a fallback.
function jd_old_doTwitterAPIPost( $twit, $authID=FALSE, $twitterURI="/statuses/update.xml" ) {
	$host = 'twitter.com';
	$port = 80;
	$fp = @fsockopen($host, $port, $err_num, $err_msg, 10);

	//check if user login details have been entered on admin page
	if ($authID === FALSE || ( get_option( 'jd_individual_twitter_users' ) != '1' ) ) {
	$thisLoginDetails = get_option( 'twitterlogin_encrypted' );
	} else {
		if ( ( get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'true' || get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'userTwitter' || get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'userAtTwitter' ) && get_usermeta( $authID, 'wp-to-twitter-encrypted' )!="" ) {
		$thisLoginDetails = get_usermeta( $authID, 'wp-to-twitter-encrypted' );
		} else {
		$thisLoginDetails = get_option( 'twitterlogin_encrypted' );		
		}
	}

	if ( $thisLoginDetails != '' )	{
		if ( !is_resource($fp) ) {
			#echo "$err_msg ($err_num)<br>\n"; // Fail Silently, but you could turn these back on...
			return FALSE;
		} else {
			if (!fputs( $fp, "POST $twitterURI HTTP/1.1\r\n" )) {
			return FALSE;
			}
			fputs( $fp, "Authorization: Basic ".$thisLoginDetails."\r\n" );
			fputs( $fp, "User-Agent: ".$agent."\n" );
			fputs( $fp, "Host: $host\n" );
			fputs( $fp, "Content-type: application/x-www-form-urlencoded\n" );
			fputs( $fp, "Content-length: ".mb_strlen( $twit )."\n" );
			fputs( $fp, "Connection: close\n\n" );
			fputs( $fp, $twit );
			for ( $i = 1; $i < 10; $i++ ) {
				$reply = fgets( $fp, 256 );
				}
			fclose( $fp );
			return TRUE;
		}
	} else {
		return FALSE; // no username/password: return an empty string
	}
}	

function jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $thisposturl, $thispostcategory, $authID=FALSE ) {

$sentence = trim($sentence);
$thisposttitle = trim($thisposttitle);
$thisblogtitle = trim($thisblogtitle);
$thispostexcerpt = trim($thispostexcerpt);
$thisposturl = trim($thisposturl);
$thispostcategory = trim($thispostcategory);

if ( get_option( 'jd_individual_twitter_users' ) == 1 ) {
	if ( get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'userAtTwitter' ) {
	$at_append = "@" . get_option('twitterlogin');
	} else if ( get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'mainAtTwitter' ) {
	$at_append = "@" . get_usermeta( $authID, 'wp-to-twitter-user-username' );
	} else {
	$at_append = "";
	}
} else {
$at_append = "";
}
	$sentence = $at_append . " " . $sentence;
	if ( get_option( 'jd_twit_prepend' ) != "" ) {
	$sentence = get_option( 'jd_twit_prepend' ) . " " . $sentence;
	}
	if ( get_option( 'jd_twit_append' ) != "" ) {
	$sentence = $sentence . " " . get_option( 'jd_twit_append' );
	}
	if ( mb_substr( $thispostexcerpt, -1 ) == ";" || mb_substr( $thispostexcerpt, -1 ) == "," || mb_substr( $thispostexcerpt, -1 ) == ":" ) {
	$thispostexcerpt = mb_substr_replace( $thispostexcerpt,"",-1, 1 );
	}
	if ( mb_substr( $thispostexcerpt, -1 ) != "." && mb_substr( $thispostexcerpt, -1 ) != "?" && mb_substr( $thispostexcerpt, -1 ) != "!" ) {
	$thispostexcerpt = $thispostexcerpt . "...";
	}
$post_sentence = str_ireplace( "#url#", $thisposturl, $sentence );
$post_sentence = str_ireplace( '#title#', $thisposttitle, $post_sentence );
$post_sentence = str_ireplace ( '#blog#',$thisblogtitle,$post_sentence );
$post_sentence = str_ireplace ( '#post#',$thispostexcerpt,$post_sentence );
$post_sentence = str_ireplace ( '#category#',$thispostcategory,$post_sentence );

$str_length = mb_strlen( urldecode( $post_sentence ) );
$length = get_option( 'jd_post_excerpt' );

$length_array = array();
//$order = get_option( 'jd_truncation_sort_order' );
$length_array['thispostexcerpt'] = mb_strlen($thispostexcerpt);
$length_array['thisblogtitle'] = mb_strlen($thisblogtitle);
$length_array['thisposttitle'] = mb_strlen($thisposttitle);
$length_array['thispostcategory'] = mb_strlen($thispostcategory);

if ($str_length > 140) {
	foreach($length_array AS $key=>$value) {
		if (($str_length > 140) && ($str_length - $value) < 140) {
			$trim = $str_length - 140;
			$old_value = ${$key};
			$new_value = mb_substr($old_value,0,-($trim));
			$post_sentence = str_ireplace($old_value,$new_value,$post_sentence);
			$str_length = mb_strlen( urldecode( $post_sentence ) );
		} else {
		}
	}
}
$sentence = $post_sentence;
return $sentence;
}

function jd_shorten_link( $thispostlink, $thisposttitle, $post_ID ) {
	
		$cligsapi = get_option( 'cligsapi' );
		$bitlyapi = get_option( 'bitlyapi' );
		$bitlylogin = get_option( 'bitlylogin' );
		$snoopy = new Snoopy;

		if ( ( get_option('twitter-analytics-campaign') != '' ) && ( get_option('use-twitter-analytics') == 1 ) ) {
			$this_campaign = get_option('twitter-analytics-campaign');
			if ( strpos( $thispostlink,"%3F" ) === FALSE) {
			$thispostlink .= urlencode("?");
			} else {
			$thispostlink .= urlencode("&");
			}
			$thispostlink .= urlencode("utm_campaign=$this_campaign&utm_medium=twitter&utm_source=twitter");
		}
		
		// Generate and grab the clig using the Cli.gs API
		// cURL alternative contributed by Thor Erik (http://thorerik.net)
		switch ( get_option( 'jd_shortener' ) ) {
			case 0:
			case 1:
			if ( $snoopy->fetchtext( "http://cli.gs/api/v1/cligs/create?t=snoopy&appid=WP-to-Twitter&url=".$thispostlink."&title=".$thisposttitle."&key=".$cligsapi ) ) {
				$shrink = trim($snoopy->results);		
			} else {
				$shrink = @file_get_contents( "http://cli.gs/api/v1/cligs/create?t=fgc&appid=WP-to-Twitter&url=".$thispostlink."&title=".$thisposttitle."&key=".$cligsapi );
			}
			if ( $shrink === FALSE ) {
				$shrink = getfilefromurl( "http://cli.gs/api/v1/cligs/create?t=gffu&appid=WP-to-Twitter&url=".$thispostlink."&title=".$thisposttitle."&key=".$cligsapi);
			}	
			break;
			case 2:
			if ( $snoopy->fetch( "http://api.bit.ly/shorten?version=2.0.1&longUrl=".$thispostlink."&login=".$bitlylogin."&apiKey=".$bitlyapi."&history=1" ) ) {
				$shrink = $snoopy->results;
			} else {
				$shrink = @file_get_contents( "http://api.bit.ly/shorten?version=2.0.1&longUrl=".$thispostlink."&login=".$bitlylogin."&apiKey=".$bitlyapi."&history=1" );
			}
			if ( $shrink === FALSE ) {
				$shrink = getfilefromurl( "http://api.bit.ly/shorten?version=2.0.1&longUrl=".$thispostlink."&login=".$bitlylogin."&apiKey=".$bitlyapi."&history=1" );
			}	
			
			$decoded = json_decode($shrink,TRUE);
			$shrink = $decoded['results'][urldecode($thispostlink)]['shortUrl'];
			//die(print_r($decoded['results'][urldecode($thispostlink)]));
			break;
			case 3:
			$shrink = $thispostlink;
			break;
			case 4:
			$shrink = get_bloginfo('url') . "/?p=" . $post_ID;
			break;
		}
		if ( $shrink === FALSE || ( stristr( $shrink, "http://" ) === FALSE )) {
			update_option( 'wp_url_failure','1' );		
			$shrink = $thispostlink;
		}
	return $shrink;
}

function jd_expand_url( $short_url ) {
	$short_url = urlencode( $short_url );
	$snoopy = new Snoopy;
	$snoopy->fetch( "http://api.longurl.org/v2/expand?format=json&url=" . $short_url );
	$longurl = $snoopy->results;
	$decoded = json_decode( $longurl, TRUE );
	$url = $decoded['long-url'];
	return $url;
}

function jd_twit( $post_ID ) {	
	$jd_tweet_this = get_post_meta( $post_ID, 'jd_tweet_this', TRUE);
	if ( $jd_tweet_this == "yes" ) {
		$get_post_info = get_post( $post_ID );
		$authID = $get_post_info->post_author;

		$category = null;
		$categories = get_the_category( $post_ID );
		if ( $categories > 0 ) {
			$category = $categories[0]->cat_name;	
		}		
		
		$excerpt_length = get_option( 'jd_post_excerpt' );
		if ( trim( $get_post_info->post_excerpt ) == "" ) {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_content), 0, $excerpt_length );
		} else {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_excerpt), 0, $excerpt_length );	
		}
	    $thisposttitle = urlencode( stripcslashes( strip_tags( $_POST['post_title'] ) ) );
		if ($thisposttitle == "") {
			$thisposttitle = urlencode( stripcslashes( strip_tags( $_POST['title'] ) ) );
		}
	    $thispostlink = urlencode( external_or_permalink( $post_ID ) );
		$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
	    $sentence = '';
		$customTweet = stripcslashes( $_POST['jd_twitter'] );
		$oldClig = get_post_meta( $post_ID, 'wp_jd_clig', TRUE );
if (($get_post_info->post_status == 'publish' || $_POST['publish'] == 'Publish') && ($_POST['prev_status'] == 'draft' || $_POST['original_post_status'] == 'draft')) {
				// publish new post
				if ( get_option( 'newpost-published-update' ) == '1' ) {
					if ($customTweet != "") {
					$sentence = $customTweet;
					} else {
					$sentence = stripcslashes( get_option( 'newpost-published-text' ) );
					}
					if ($oldClig != '') {
					$shrink = $oldClig;
					} else {
					$shrink = jd_shorten_link( $thispostlink, $thisposttitle, $post_ID );
					// Stores the posts CLIG in a custom field for later use as needed.
					store_url( $post_ID, $shrink );						
					}
					$sentence = custom_shortcodes( $sentence, $post_ID );
					$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $shrink, $category, $authID );		
				}
			} else if ( (( $_POST['originalaction'] == "editpost" ) && ( ( $_POST['prev_status'] == 'publish' ) || ($_POST['original_post_status'] == 'publish') ) ) && $get_post_info->post_status == 'publish') {
				// if this is an old post and editing updates are enabled
			if ( get_option( 'oldpost-edited-update') == '1' ) {
						if ($customTweet != "") {
						$sentence = $customTweet;
						} else {
						$sentence = stripcslashes( get_option( 'oldpost-edited-text' ) );
						}
						if ( $oldClig != '' ) {
						$old_post_link = $oldClig;
						} else {
						$old_post_link = jd_shorten_link( $thispostlink, $thisposttitle, $post_ID );
						store_url( $post_ID, $old_post_link );
						}
					}
					$sentence = custom_shortcodes( $sentence, $post_ID );					
					$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $old_post_link, $category, $authID );
			}
		if ( $sentence != '' ) {
			if ( get_option( 'use_tags_as_hashtags' ) == '1' ) {
				$sentence = $sentence . " " . generate_hash_tags( $post_ID );
			}		
			$sendToTwitter = jd_doTwitterAPIPost( $sentence, $authID );				
			if ( $sendToTwitter === FALSE ) {
			update_post_meta( $post_ID,'jd_wp_twitter',urldecode( $sentence ) );
			update_option( 'wp_twitter_failure','1' );
			}
		}
	}
	return $post_ID;
}

function jd_twit_page( $post_ID ) {	
	$jd_tweet_this = get_post_meta( $post_ID, 'jd_tweet_this', TRUE);
	if ( $jd_tweet_this == "yes" ) {
		$get_post_info = get_post( $post_ID );
		$authID = $get_post_info->post_author;
		$excerpt_length = get_option( 'jd_post_excerpt' );
		if ( trim( $get_post_info->post_excerpt ) == "" ) {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_content), 0, $excerpt_length );
		} else {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_excerpt), 0, $excerpt_length );	
		}
	    $thisposttitle = urlencode( stripcslashes( strip_tags( $_POST['post_title'] ) ) );
		if ($thisposttitle == "") {
			$thisposttitle = urlencode( stripcslashes( strip_tags( $_POST['title'] ) ) );
		}
	    $thispostlink = urlencode( external_or_permalink( $post_ID ) );
		$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
	    $sentence = '';
		$customTweet = stripcslashes( $_POST['jd_twitter'] );
		$oldClig = get_post_meta( $post_ID, 'wp_jd_clig', TRUE );
if (($get_post_info->post_status == 'publish' || $_POST['publish'] == 'Publish') && ($_POST['prev_status'] == 'draft' || $_POST['original_post_status'] == 'draft')) {
				// publish new post
				if ( get_option( 'jd_twit_pages' ) == '1' ) {
					if ($customTweet != "") {
					$sentence = $customTweet;
					} else {				
					$sentence = stripcslashes( get_option( 'newpage-published-text' ) );
					}
						if ($oldClig != '') {
						$shrink = $oldClig;
						} else {
						$shrink = jd_shorten_link( $thispostlink, $thisposttitle, $post_ID );
						// Stores the posts CLIG in a custom field for later use as needed.
						store_url( $post_ID, $shrink );						
					}
					$sentence = custom_shortcodes( $sentence, $post_ID );					
					$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $shrink, '', $authID );					
				}
			} else if ( (( $_POST['originalaction'] == "editpost" ) && ( ( $_POST['prev_status'] == 'publish' ) || ($_POST['original_post_status'] == 'publish') ) ) && $get_post_info->post_status == 'publish') {
				// if this is an old page and editing updates are enabled
			if ( get_option( 'jd_twit_edited_pages' ) == '1' ) {
					if ($customTweet != "") {
					$sentence = $customTweet;
					} else {
					$sentence = stripcslashes( get_option( 'oldpage-edited-text' ) );
					}
						if ($oldClig != '') {
						$shrink = $oldClig;
						} else {
						$shrink = jd_shorten_link( $thispostlink, $thisposttitle, $post_ID );
						}
					store_url( $post_ID, $shrink );		
					$sentence = custom_shortcodes( $sentence, $post_ID );
					$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $shrink, '', $authID );
			}
		}
		if ( $sentence != '' ) {
			$sendToTwitter = jd_doTwitterAPIPost( $sentence, $authID );				
			if ( $sendToTwitter === FALSE ) {
			update_post_meta( $post_ID,'jd_wp_twitter',urldecode( $sentence ) );
			update_option( 'wp_twitter_failure','1' );
			}
		}
	}
	return $post_ID;
} // page tweet end

// Add Tweets on links in Blogroll
function jd_twit_link( $link_ID )  {
global $version;
	$thislinkprivate = $_POST['link_visible'];
	if ($thislinkprivate != 'N') {
		$thislinkname = urlencode( stripcslashes( $_POST['link_name'] ) );
		$thispostlink = urlencode( $_POST['link_url'] ) ;
		$thislinkdescription = urlencode( stripcslashes( $_POST['link_description'] ) );
		$sentence = stripcslashes( get_option( 'newlink-published-text' ) );

		if ( get_option( 'jd-use-link-description' ) == '1' || get_option ( 'jd-use-link-title' ) == '1' ) {
			if ( get_option( 'jd-use-link-description' ) == '1' && get_option ( 'jd-use-link-title' ) == '0' ) {
			$sentence = $sentence . ' ' . $thislinkdescription;
			} else if ( get_option( 'jd-use-link-description' ) == '0' && get_option ( 'jd-use-link-title' ) == '1' ) {
			$sentence = $sentence . ' ' . $thislinkname;
			}
		} else {
			$sentence = str_ireplace("#title#",$thislinkname,$sentence);
			$sentence = str_ireplace("#description#",$thislinkdescription,$sentence);		 
		}
		if (mb_strlen( $sentence ) > 120) {
			$sentence = mb_substr($sentence,0,116) . '...';
		}
		// Generate and grab the clig using the Cli.gs API
		$shrink = jd_shorten_link( $thispostlink, $thislinkname, $post_ID );
				if ( stripos($sentence,"#url#") === FALSE ) {
				$sentence = $sentence . " " . $shrink;
				} else {
				$sentence = str_ireplace("#url#",$shrink,$sentence);
				}						
			if ( $sentence != '' ) {
				$sendToTwitter = jd_doTwitterAPIPost( $sentence );				
				if ($sendToTwitter === FALSE) {
				update_option('wp_twitter_failure','2');
				}
			}
		return $link_ID;
	} else {
	return '';
	}
}

// HANDLES SCHEDULED POSTS
function jd_twit_future( $post_ID ) {
    $post_ID = $post_ID->ID;
	
	$get_post_info = get_post( $post_ID );
	$jd_tweet_this = get_post_meta( $post_ID, 'jd_tweet_this', TRUE );
	$post_status = $get_post_info->post_status;

	$category = null;
	$categories = get_the_category( $post_ID );
	if ( $categories > 0 ) {
		$category = $categories[0]->cat_name;	
	}	
	
	if ( $jd_tweet_this == "yes" ) {	
		$thispostlink = urlencode( external_or_permalink( $post_ID ) );
		$thisposttitle = urlencode( strip_tags( $get_post_info->post_title ) );	
		$authID = $get_post_info->post_author;		
		$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
		$excerpt_length = get_option( 'jd_post_excerpt' );
		if ( trim( $get_post_info->post_excerpt ) == "" ) {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_content), 0, $excerpt_length );
		} else {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_excerpt), 0, $excerpt_length );	
		}	
		$sentence = '';
		$customTweet = get_post_meta( $post_ID, 'jd_twitter', TRUE ); 
		$sentence = stripcslashes(get_option( 'newpost-published-text' ));
			$shrink = jd_shorten_link( $thispostlink, $thisposttitle, $post_ID );
			// Stores the post's short URL in a custom field for later use as needed.
			store_url($post_ID, $shrink);
				if ( $customTweet != "" ) {
				$sentence = $customTweet;
				}  
			$sentence = custom_shortcodes( $sentence, $post_ID );			
			$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $shrink, $category, $authID );
		
			if ( $sentence != '' ) {
				if ( get_option( 'use_tags_as_hashtags' ) == '1' ) {
		$sentence = $sentence . " " . generate_hash_tags( $post_ID );
				}	
				$sendToTwitter = jd_doTwitterAPIPost( $sentence, $authID );				
				if ($sendToTwitter === FALSE) {
				add_post_meta( $post_ID,'jd_wp_twitter',urldecode($sentence) );
				update_option( 'wp_twitter_failure','1' );
				}
			}
		return $post_ID;
	}	
} // END jd_twit_future

// Tweet from QuickPress (no custom fields, so can't control whether to tweet.)
function jd_twit_quickpress( $post_ID ) {
    $post_ID = $post_ID->ID;
	$get_post_info = get_post( $post_ID );
	$post_status = $get_post_info->post_status;
	$thispostlink = urlencode( external_or_permalink( $post_ID ) );
	$thisposttitle = urlencode( strip_tags( $get_post_info->post_title ) );	
	$authID = $get_post_info->post_author;		
	$excerpt_length = get_option( 'jd_post_excerpt' );
		if ( trim( $get_post_info->post_excerpt ) == "" ) {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_content), 0, $excerpt_length );
		} else {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_excerpt), 0, $excerpt_length );	
		}	
	$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
	$sentence = '';
	$sentence = stripcslashes(get_option( 'newpost-published-text' ));
		$shrink = jd_shorten_link( $thispostlink, $thisposttitle, $post_ID );
		// Stores the posts CLIG in a custom field for later use as needed.
		store_url($post_ID, $shrink);			
		$sentence = custom_shortcodes( $sentence, $post_ID );		
		$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $shrink, '', $authID );
			if ( $sentence != '' ) {
				if ( get_option( 'use_tags_as_hashtags' ) == '1' ) {
					$sentence = $sentence . " " . generate_hash_tags( $post_ID );	
				}
				$sendToTwitter = jd_doTwitterAPIPost( $sentence, $authID );				
				if ($sendToTwitter === FALSE) {
					add_post_meta( $post_ID,'jd_wp_twitter',urldecode($sentence) );
					update_option( 'wp_twitter_failure','1' );
				}
			}
		return $post_ID;	
} // END jd_twit_quickpress

// HANDLES xmlrpc POSTS
function jd_twit_xmlrpc( $post_ID ) {
	$get_post_info = get_post( $post_ID );
	$post_status = $get_post_info->post_status;	
	if ( get_option('oldpost-edited-update') != 1 && get_post_meta ( $post_ID, 'wp_jd_clig', TRUE ) != '' ) {
		return;
	} else {	
	if ( get_option('jd_tweet_default') != '1' && get_option('jd_twit_remote') == '1' ) {
		$authID = $get_post_info->post_author;	
		$thispostlink = urlencode( external_or_permalink( $post_ID ) );
		$thisposttitle = urlencode( strip_tags( $get_post_info->post_title ) );	
		$category = null;
		$categories = get_the_category( $post_ID );
		if ( $categories > 0 ) {
			$category = $categories[0]->cat_name;	
		}		
		$excerpt_length = get_option( 'jd_post_excerpt' );
		if ( trim( $get_post_info->post_excerpt ) == "" ) {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_content), 0, $excerpt_length );
		} else {
		$thispostexcerpt = @mb_substr( strip_tags($get_post_info->post_excerpt), 0, $excerpt_length );	
		}
		$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
		$sentence = '';
		$sentence = stripcslashes(get_option( 'newpost-published-text' ));
			$shrink = jd_shorten_link( $thispostlink, $thisposttitle, $post_ID );
			// Stores the posts CLIG in a custom field for later use as needed.
			store_url($post_ID, $shrink);				
			// Check the length of the tweet and truncate parts as necessary.
			$sentence = custom_shortcodes( $sentence, $post_ID );			
			$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $shrink, $category, $authID );
			
			if ( $sentence != '' ) {
				if ( get_option( 'use_tags_as_hashtags' ) == '1' ) {
					$sentence = $sentence . " " . generate_hash_tags( $post_ID );
				}			
				$sendToTwitter = jd_doTwitterAPIPost( $sentence, $authID );				
				$sendToTwitter = jd_doTwitterAPIPost( $sentence, $authID );				
				if ($sendToTwitter === FALSE) {
				add_post_meta( $post_ID,'jd_wp_twitter',urldecode($sentence));
				update_option('wp_twitter_failure','1');
				}
			}
	}
	return $post_ID;
	}
} // END jd_twit_xmlrpc


add_action('admin_menu','jd_add_twitter_outer_box');

function store_url($post_ID, $url) {
	if ( get_option( 'jd_shortener' ) == 0 || get_option( 'jd_shortener' ) == 1) {
			if ( get_post_meta ( $post_ID, 'wp_jd_clig', TRUE ) != $url ) {
			update_post_meta ( $post_ID, 'wp_jd_clig', $url );
			}	
	} elseif ( get_option( 'jd_shortener' ) == 2 ) {
			if ( get_post_meta ( $post_ID, 'wp_jd_bitly', TRUE ) != $url ) {
			update_post_meta ( $post_ID, 'wp_jd_bitly', $url );
			}	
	}
	$target = jd_expand_url( $url );
	update_post_meta( $post_ID, 'wp_jd_target', $target );
}

function generate_hash_tags( $post_ID ) {

$max_tags = get_option( 'jd_max_tags' );
$max_characters = get_option( 'jd_max_characters' );

	if ($max_characters == 0 || $max_characters == "") {
		$max_characters = 100;
	} else {
		$max_characters = $max_characters + 1;
	}
	if ($max_tags == 0 || $max_tags == "") {
		$max_tags = 100;
	}

		$tags = get_the_tags( $post_ID );
		if ( $tags > 0 ) {
		$i = 1;
			foreach ( $tags as $value ) {
			$tag = $value->name;
			$value = str_ireplace( " ","_",trim( $tag ) );
				$newtag = "#$tag";
					if ( mb_strlen( $newtag ) > 2 && (mb_strlen( $newtag ) <= $max_characters) && ($i <= $max_tags) ) {
					$hashtags .= "$newtag ";
					$i++;
					}
			}
		}
	$hashtags = trim( $hashtags );
	if ( mb_strlen( $hashtags ) <= 1 ) {
	$hashtags = "";
	}		
	return $hashtags;	
}
/*
function generate_hash_tags( $post ) {
global $wp_version;

$max_tags = get_option( 'jd_max_tags' );
$max_characters = get_option( 'jd_max_characters' );

	if ($max_characters == 0 || $max_characters == "") {
		$max_characters = 100;
	} else {
		$max_characters = $max_characters + 1;
	}
	if ($max_tags == 0 || $max_tags == "") {
		$max_tags = 100;
	}
//tax_input[post_tag]=failed&newtag[post_tag]=Add+new+tag
	if ( version_compare( $wp_version,"2.8",">=" ) ) {
		$tags = $post['tax_input']['post_tag'] . "," . $post['newtag']['post_tag'];
		} else {
		$tags = $post['tags_input'];
		}
		//print_r($tags);
	$tags = explode( ",",$tags );
	$tags = array_unique($tags);
	
		$i = 1;
		foreach ( $tags as $value ) {
		$value = str_ireplace( " ","_",trim( $value ) );
			if ( $value != __( "Add_new_tag" , 'wp-to-twitter') && $value != "" ) { 
			$newtag = "#$value";
				if ( mb_strlen( $newtag ) > 2 && (mb_strlen( $newtag ) <= $max_characters) && ($i <= $max_tags) ) {
				//if ( mb_strlen( $newtag ) > 2 ) {
				$hashtags .= "$newtag ";
				$i++;
				}
			}
		}
	$hashtags = trim( $hashtags );
	if ( mb_strlen( $hashtags ) <= 1 ) {
	$hashtags = "";
	}
	return $hashtags;
} */

function jd_add_twitter_old_box() {
?>

<div class="dbx-b-ox-wrapper">
<fieldset id="twitdiv" class="dbx-box">
<div class="dbx-h-andle-wrapper">
<h3 class="dbx-handle"><?php _e('WP to Twitter', 'wp-to-twitter', 'wp-to-twitter') ?></h3>
</div>
<div class="dbx-c-ontent-wrapper">
<div class="dbx-content">
<?php
jd_add_twitter_inner_box();
?>
</div>
</fieldset>
</div>
<?php
}

function jd_add_twitter_inner_box() {

global $post, $jd_plugin_url;
	$post_id = $post;
	if (is_object($post_id)) {
		$post_id = $post_id->ID;
	}
	$jd_twitter = htmlspecialchars( stripcslashes( get_post_meta($post_id, 'jd_twitter', true ) ) );
	$jd_tweet_this = get_post_meta( $post_id, 'jd_tweet_this', true );
		if ( $jd_tweet_this == 'no' || get_option( 'jd_tweet_default' ) == '1' ) {
		$jd_selected = ' checked="checked"';
		}
	$jd_short = get_post_meta( $post_id, 'wp_jd_clig', true );
	$shortener = "Cli.gs";
	if ( $jd_short == "" ) {
		$jd_short = get_post_meta( $post_id, 'wp_jd_bitly', true );
		$shortener = "Bit.ly";
	}
	$jd_expansion = get_post_meta( $post_id, 'wp_jd_target', true );
	?>
<script type="text/javascript">
<!-- Begin
function countChars(field,cntfield) {
cntfield.value = field.value.length;
}
//  End -->
</script>
<p>
<label for="jd_twitter"><?php _e('Twitter Post', 'wp-to-twitter', 'wp-to-twitter') ?></label><br /><textarea style="width:95%;" name="jd_twitter" id="jd_twitter" rows="2" cols="60"
	onKeyDown="countChars(document.post.jd_twitter,document.post.twitlength)"
	onKeyUp="countChars(document.post.jd_twitter,document.post.twitlength)"><?php echo attribute_escape( $jd_twitter ); ?></textarea>
</p>
<p><input readonly type="text" name="twitlength" size="3" maxlength="3" value="<?php echo attribute_escape( mb_strlen( $description) ); ?>" />
<?php _e(' characters.<br />Twitter posts are a maximum of 140 characters; if your Cli.gs URL is appended to the end of your document, you have 119 characters available. You can use <code>#url#</code>, <code>#title#</code>, <code>#post#</code>, <code>#category#</code> or <code>#blog#</code> to insert the shortened URL, post title, the first category selected, or a post excerpt or blog name into the Tweet.', 'wp-to-twitter', 'wp-to-twitter') ?> <a target="__blank" href="<?php echo $jd_donate_url; ?>"><?php _e('Make a Donation', 'wp-to-twitter', 'wp-to-twitter') ?></a> &bull; <a target="__blank" href="<?php echo $jd_plugin_url; ?>"><?php _e('Get Support', 'wp-to-twitter', 'wp-to-twitter') ?></a> &raquo;
</p>
<p>
<input type="checkbox" name="jd_tweet_this" value="no"<?php echo attribute_escape( $jd_selected ); ?> id="jd_tweet_this" /> <label for="jd_tweet_this"><?php _e("Don't Tweet this post.", 'wp-to-twitter'); ?></label>
</p>
<p>
<?php
if ( $jd_short != "" ) {
	_e("The previously-posted $shortener URL for this post is <code>$jd_short</code>, which points to <code>$jd_expansion</code>.", 'wp-to-twitter');
}
?>
</p>
<?php } 
function jd_add_twitter_outer_box() {
	if ( function_exists( 'add_meta_box' )) {
    add_meta_box( 'wptotwitter_div','WP to Twitter', 'jd_add_twitter_inner_box', 'post', 'advanced' );
		if (  get_option( 'jd_twit_pages') == 1 ) {
			add_meta_box( 'wptotwitter_div','WP to Twitter', 'jd_add_twitter_inner_box', 'page', 'advanced' );
		}
   } else {
    add_action('dbx_post_advanced', 'jd_add_twitter_old_box' );
		if ( get_option( 'jd_twit_pages') == 1 ) {
			add_action('dbx_page_advanced', 'jd_add_twitter_old_box' );
		}
  }
}

// Post the Custom Tweet into the post meta table
function post_jd_twitter( $id ) {
	$jd_twitter = $_POST[ 'jd_twitter' ];
		if (isset($jd_twitter) && !empty($jd_twitter)) {
			update_post_meta( $id, 'jd_twitter', $jd_twitter );
		}
	$jd_tweet_this = $_POST[ 'jd_tweet_this' ];
	if ($jd_tweet_this == 'no') {
		update_post_meta( $id, 'jd_tweet_this', 'no');
	} else {
		update_post_meta( $id, 'jd_tweet_this', 'yes');
	}
}


function jd_twitter_profile() {
		global $user_ID;
		get_currentuserinfo();
		if( $_GET['user_id'] ) $user_ID = $_GET['user_id'];
		
			$is_enabled = get_usermeta( $user_ID, 'wp-to-twitter-enable-user' );
			$twitter_username = get_usermeta( $user_ID, 'wp-to-twitter-user-username' );
			$twitter_password = get_usermeta( $user_ID, 'wp-to-twitter-user-password' );
	
		?>
		<h3><?php _e('WP to Twitter User Settings', 'wp-to-twitter'); ?></h3>
		
		<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Use My Twitter Account', 'wp-to-twitter'); ?></th>
			<td><input type="radio" name="wp-to-twitter-enable-user" id="wp-to-twitter-enable-user" value="userTwitter"<?php if ($is_enabled == "userTwitter" || $is_enabled == "true" ) { echo " checked='checked'"; } ?> /> <label for="wp-to-twitter-enable-user"><?php _e('Select this option if you would like your posts to be Tweeted into your own Twitter account with no @ references.', 'wp-to-twitter'); ?></label><br />
<input type="radio" name="wp-to-twitter-enable-user" id="wp-to-twitter-enable-user-2" value="userAtTwitter"<?php if ($is_enabled == "userAtTwitter") { echo " checked='checked'"; } ?> /> <label for="wp-to-twitter-enable-user-2"><?php _e('Tweet my posts into my Twitter account with an @ reference to the site\'s main Twitter account.', 'wp-to-twitter'); ?></label><br />
<input type="radio" name="wp-to-twitter-enable-user" id="wp-to-twitter-enable-user-3" value="mainAtTwitter"<?php if ($is_enabled == "mainAtTwitter") { echo " checked='checked'"; } ?> /> <label for="wp-to-twitter-enable-user-3"><?php _e('Tweet my posts into the main site Twitter account with an @ reference to my username. (Password not required with this option.)', 'wp-to-twitter'); ?></label></td>
		</tr>
		<tr>
			<th scope="row"><label for="wp-to-twitter-user-username"><?php _e('Your Twitter Username', 'wp-to-twitter'); ?></label></th>
			<td><input type="text" name="wp-to-twitter-user-username" id="wp-to-twitter-user-username" value="<?php echo attribute_escape( $twitter_username ); ?>" /> <?php _e('Enter your own Twitter username.', 'wp-to-twitter'); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="wp-to-twitter-user-password"><?php _e('Your Twitter Password', 'wp-to-twitter'); ?></label></th>
			<td><input type="password" name="wp-to-twitter-user-password" id="wp-to-twitter-user-password" value="" /> <?php _e('Enter your own Twitter password.', 'wp-to-twitter'); ?></td>
		</tr>
		</table>
		<?php
}

function custom_shortcodes( $sentence, $post_ID ) {
	$pattern = '/\[\[.*\]\]/';
	$params = array(0=>"[[",1=>"]]");
	preg_match($pattern,$sentence, $matches);
	if ($matches) {
		foreach ($matches as $value) {
			$shortcode = "$value";
			$field = str_replace($params, "", $shortcode);
			$custom = get_post_meta( $post_ID, $field, TRUE );
			$sentence = str_replace( $shortcode, $custom, $sentence );
		}
	return $sentence;
	} else {
	return $sentence;
	}
}
	
function jd_twitter_save_profile(){
	global $user_ID;
	get_currentuserinfo();
	if( $_GET['user_id'] ) { $user_ID = $_GET['user_id']; }
	update_usermeta($user_ID ,'wp-to-twitter-enable-user' , $_POST['wp-to-twitter-enable-user'] );
	update_usermeta($user_ID ,'wp-to-twitter-user-username' , $_POST['wp-to-twitter-user-username'] );
	if ( $_POST['wp-to-twitter-user-password'] != '' ) {
	update_usermeta($user_ID ,'wp-to-twitter-user-password' , $_POST['wp-to-twitter-user-password'] );
	}
	update_usermeta($user_ID ,'wp-to-twitter-encrypted' , base64_encode( $_POST['wp-to-twitter-user-username'].':'.$_POST['wp-to-twitter-user-password'] ) ); 	
}

// Add the administrative settings to the "Settings" menu.
function jd_addTwitterAdminPages() {
    if ( function_exists( 'add_submenu_page' ) ) {
		 $plugin_page = add_options_page( 'WP -> Twitter', 'WP -> Twitter', 8, __FILE__, 'jd_wp_Twitter_manage_page' );
		 add_action( 'admin_head-'. $plugin_page, 'jd_addTwitterAdminStyles' );
    }
 }
function jd_addTwitterAdminStyles() {
 $wp_to_twitter_directory = get_bloginfo( 'wpurl' ) . '/' . PLUGINDIR . '/' . dirname( plugin_basename(__FILE__) );
	echo "
<style type=\"text/css\">
<!--
#wp-to-twitter h2 {
background: #fff url($wp_to_twitter_directory/wp-to-twitter-logo.png) right center no-repeat;
padding: 16px 2px;
margin: 25px 0;
border: 1px solid #ddd;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
border-radius: 3px;
} 
#wp-to-twitter fieldset {
background: #eaf3fa;
padding: 10px;
border: 1px solid #ccc;
-moz-border-radius: 5px;
-webkit-border-radius: 5px;
border-radius: 5px;
margin: 5px 0;
}
#wp-to-twitter form {
clear: right;
max-width: 800px;
}
#wp-to-twitter form .error p {
background: none;
border: none;
}
.floatright {
float: right;
}
.cligs, .bitly {
padding: 2px!important;
margin-top: 1.5em!important;
}
.twitter {
padding: 2px!important;
margin-top: 1.5em!important;
}
legend {
font-weight: 700;
font-size: 1.4em;
padding: 0 6px;
border-left: 2px solid #ccc;
border-right: 2px solid #ccc;
}
.resources {
float: right;
border: 1px solid #aaa;
padding: 10px 10px 0;
margin-left: 10px;
-moz-border-radius: 5px;
-webkit-border-radius: 5px;
border-radius: 5px;
background: #fff;
text-align: center;
}
#wp-to-twitter .resources form {
margin: 0;
}
.settings {
margin: 25px 0;
background: #fff;
padding: 10px;
border: 1px solid #000;
}
-->
</style>";
 }
// Include the Manager page
function jd_wp_Twitter_manage_page() {
	if ( file_exists ( dirname(__FILE__).'/wp-to-twitter-manager.php' )) {
    include( dirname(__FILE__).'/wp-to-twitter-manager.php' );
	} else {
	_e( '<p>Couldn\'t locate the settings page.</p>', 'wp-to-twitter' );
	}
}
function plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/wp-to-twitter.php'))
		$links[] = "<a href='options-general.php?page=wp-to-twitter/wp-to-twitter.php'>" . __('Settings', 'wp-to-twitter', 'wp-to-twitter') . "</a>";
	return $links;
}

//Add Plugin Actions to WordPress

add_filter('plugin_action_links', 'plugin_action', -10, 2);

if ( get_option( 'jd_individual_twitter_users')=='1') {
	add_action( 'show_user_profile', 'jd_twitter_profile' );
	add_action( 'edit_user_profile', 'jd_twitter_profile' );
	add_action( 'profile_update', 'jd_twitter_save_profile');
}

if ( get_option( 'wp_twitter_failure' ) == '1' || get_option( 'wp_cligs_failure' ) == '1' ) {
	add_action('admin_notices', create_function( '', "echo '<div class=\"error\"><p>';_e('There\'s been an error posting your Twitter status! <a href=\"".get_bloginfo('wpurl')."/wp-admin/options-general.php?page=wp-to-twitter/wp-to-twitter.php\">Visit your WP to Twitter settings page</a> to get more information and to clear this error message.','wp-to-twitter'); echo '</p></div>';" ) );
}
if ( get_option( 'jd_twit_pages' )=='1' ) {
	add_action( 'publish_page', 'jd_twit_page' );
}
if ( get_option( 'jd_twit_edited_pages' )=='1' ) {
	add_action( 'publish_page', 'jd_twit_page' );
}
if ( get_option( 'jd_twit_blogroll' ) == '1' ) {
	add_action( 'add_link', 'jd_twit_link' );
}
add_action( 'future_to_publish', 'jd_twit_future' );

if ( version_compare( $wp_version,"2.7","<" )) {
add_action( 'edit_post', 'jd_twit', 12 );

} else {
add_action( 'publish_post', 'jd_twit', 12 );
}

if ( get_option( 'jd_twit_quickpress' ) == '1' ) {
add_action( 'new_to_publish', 'jd_twit_quickpress', 12 ); 
}

if ( get_option( 'jd_twit_remote' ) == '1' ) {
	add_action( 'xmlrpc_publish_post', 'jd_twit_xmlrpc' ); 
	add_action( 'publish_phone', 'jd_twit_xmlrpc' ); // to add later
}
add_action( 'save_post','post_jd_twitter' );
add_action( 'admin_menu', 'jd_addTwitterAdminPages' );

register_activation_hook( __FILE__, 'wptotwitter_activate' );

?>