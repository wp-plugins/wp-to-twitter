<?php
/*
Plugin Name: WP to Twitter
Plugin URI: http://www.joedolson.com/articles/wp-to-twitter/
Description: Updates Twitter when you create a new blog post or add to your blogroll using Cli.gs. With a Cli.gs API key, creates a clig in your Cli.gs account with the name of your post as the title.
Version: 1.4.6
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
// Reporting E_NOTICE can be good too (to report uninitialized
// variables or catch variable name misspellings ...)

global $wp_version,$version,$jd_plugin_url;	

$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'wptotwitter', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );


define('JDWP_API_POST_STATUS', 'http://twitter.com/statuses/update.json');

$version = "1.4.6";
$jd_plugin_url = "http://www.joedolson.com/articles/wp-to-twitter/";

if ( !defined( 'WP_PLUGIN_DIR' ) ) {
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	  }
require_once( ABSPATH.WPINC.'/class-snoopy.php' );

if ( !class_exists('SERVICES_JSON') ) {
	require_once( WP_PLUGIN_DIR.'/wp-to-twitter/json.class.php' );
}
if (!function_exists('json_encode')) {
	function json_encode($data) {
		$json = new Services_JSON();
		return( $json->encode($data) );
	}
}
if (!function_exists('json_decode')) {
	function json_decode($data) {
		$json = new Services_JSON( SERVICES_JSON_LOOSE_TYPE );
		return( $json->decode($data) );
	}
}

$exit_msg='WP to Twitter requires WordPress 2.5 or a more recent version. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update your WordPress version!</a>';

	if ( version_compare( $wp_version,"2.5","<" )) {
	exit ($exit_msg);
	}
	
// Function checks for an alternate URL to be tweeted. Contribution by Bill Berry.	
function external_or_permalink( $post_ID ) {
       $wtb_extlink_custom_field = get_option('jd_twit_custom_url'); 
       $perma_link = get_permalink( $post_ID );
       $ex_link = get_post_meta($post_ID, $wtb_extlink_custom_field, true);
       return ( $ex_link ) ? $ex_link : $perma_link;
}

if ( !function_exists( 'str_ireplace' ) ) {
	function str_ireplace( $needle, $str, $haystack ) {
		$needle = preg_quote( $needle, '/' );
		return preg_replace( "/$needle/i", $str, $haystack );
	}
}

if ( function_exists( 'mb_substr_replace' ) === false ) {
    function mb_substr_replace( $string, $replacement, $start, $length = null, $encoding = null ) {
        if ( extension_loaded( 'mbstring' ) === true ) {
            $string_length = (is_null($encoding) === true) ? mb_strlen($string) : mb_strlen($string, $encoding);   
            if ( $start < 0 ) {
                $start = max(0, $string_length + $start);
            } else if ( $start > $string_length ) {
                $start = $string_length;
            }
            if ( $length < 0 ) {
                $length = max( 0, $string_length - $start + $length );
            } else if ( ( is_null( $length ) === true ) || ( $length > $string_length ) ) {
                $length = $string_length;
            }
            if ( ( $start + $length ) > $string_length) {
                $length = $string_length - $start;
            }
            if ( is_null( $encoding ) === true) {
                return mb_substr( $string, 0, $start ) . $replacement . mb_substr( $string, $start + $length, $string_length - $start - $length );
            }
		return mb_substr( $string, 0, $start, $encoding ) . $replacement . mb_substr( $string, $start + $length, $string_length - $start - $length, $encoding );
        }
	return ( is_null( $length ) === true ) ? substr_replace( $string, $replacement, $start ) : substr_replace( $string, $replacement, $start, $length );
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
		// no username/password: return an empty string
		return FALSE;
	}
}	
	
// This function performs the API post to Twitter
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

// cURL query contributed by Thor Erik (http://thorerik.net)
function getfilefromurl($url) {
if ( function_exists( 'curl_init' ) ) {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_VERBOSE, 0 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, $url );
	$output = curl_exec( $ch );
	curl_close( $ch );
	return $output;
	} else {
	return FALSE;
	}
}

function jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID=FALSE ) {

$sentence = trim($sentence);
$thisposttitle = trim($thisposttitle);
$thisblogtitle = trim($thisblogtitle);
$thispostexcerpt = trim($thispostexcerpt);
// Rewrite this function. 

/* Logic: assemble tweet, check length. If under 140, return. 
If over 140, check each element in tweet according to the user-selected priority list (least important first.) After each element is checked, re-assemble and check length. If OK, return; if still too long, truncate next element, until tweet is appropriate length. Much simpler process...
*/

if ( get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'userAtTwitter' ) {
$at_append = "@" . get_option('twitterlogin');
} else if ( get_usermeta( $authID, 'wp-to-twitter-enable-user' ) == 'mainAtTwitter' ) {
$at_append = "@" . get_usermeta( $authID, 'wp-to-twitter-user-username' );
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
	$twit_length = mb_strlen( $sentence );
	$title_length = mb_strlen( $thisposttitle );
	$blog_length = mb_strlen( $thisblogtitle );
	$excerpt_length = mb_strlen( $thispostexcerpt );
	if ( ( ( $twit_length + $title_length ) -  7 ) < 140 ) {
	$sentence = str_ireplace( '#title#', $thisposttitle, $sentence );
	$twit_length = mb_strlen( $sentence );				
	$twit_length = mb_strlen( $sentence );				
	} else {
	$thisposttitle = mb_substr( $thisposttitle, 0, ( 140- ( $twit_length-3 ) ) ) . "...";
	$sentence = str_ireplace ( '#title#', $thisposttitle, $sentence );
	$twit_length = mb_strlen( $sentence );
	}
	if ( ( ( $twit_length + $blog_length ) -  6 ) < 140 ) {
	$sentence = str_ireplace ( '#blog#',$thisblogtitle,$sentence );
	$twit_length = mb_strlen( $sentence );
	} else {
	$thisblogtitle = mb_substr( $thisblogtitle, 0, ( 140-( $twit_length-3 ) ) ) . "...";			
	$sentence = str_ireplace ( '#blog#',$thisblogtitle,$sentence );
	}
	if ( ( ( $twit_length + $excerpt_length ) - 6 ) < 140 ) {
	$sentence = str_ireplace ( '#post#',$thispostexcerpt,$sentence );
	$twit_length = mb_strlen( $sentence );
	} else {
	$thispostexcerpt = mb_substr( $thispostexcerpt, 0, ( 140-( $twit_length-3 ) ) ) . "...";
	$sentence = str_ireplace ( '#post#',$thispostexcerpt,$sentence );
	}
	return $sentence;
}

function jd_shorten_link( $thispostlink, $thisposttitle ) {

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
			$shrink = @file_get_contents( "http://cli.gs/api/v1/cligs/create?t=fgc&appid=WP-to-Twitter&url=".$thispostlink."&title=".$thisposttitle."&key=".$cligsapi);
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
		$excerpt_length = get_option( 'jd_post_excerpt' );
		if ( $excerpt_length == "" ) {
		$excerpt_length = 25;
		}
		if ($get_post_info->post_excerpt == "") {
		$thispostexcerpt = str_split( strip_tags($get_post_info->post_content), $excerpt_length );
		} else {
		$thispostexcerpt = str_split( strip_tags($get_post_info->post_excerpt), $excerpt_length );	
		}
		$thispostexcerpt = $thispostexcerpt[0];
	    $thisposttitle = urlencode( stripcslashes( strip_tags( $_POST['post_title'] ) ) );
		if ($thisposttitle == "") {
			$thisposttitle = urlencode( stripcslashes( strip_tags( $_POST['title'] ) ) );
		}
	    $thispostlink = urlencode( external_or_permalink( $post_ID ) );
		$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
		$cligsapi = get_option( 'cligsapi' );
	    $sentence = '';
		$customTweet = stripcslashes( $_POST['jd_twitter'] );
		$oldClig = get_post_meta( $post_ID, 'wp_jd_clig', TRUE );
if (($get_post_info->post_status == 'publish' || $_POST['publish'] == 'Publish') && ($_POST['prev_status'] == 'draft' || $_POST['original_post_status'] == 'draft')) {
				// publish new post
				if ( get_option( 'newpost-published-update' ) == '1' ) {
					$sentence = stripcslashes( get_option( 'newpost-published-text' ) );
					if ( get_option( 'newpost-published-showlink' ) == '1' ) {
						if ($oldClig != '') {
						$shrink = $oldClig;
						} else {
						$shrink = jd_shorten_link( $thispostlink, $thisposttitle, $cligsapi );
						}
					
					//$sentence = $sentence . " " . $shrink;
						if ( stripos( $sentence, "#url#" ) === FALSE ) {
						$sentence = $sentence . " " . $shrink;
						} else {
						$sentence = str_ireplace( "#url#", $shrink, $sentence );
						}
					
						if ( $customTweet != "" ) {
							if ( get_option( 'newpost-published-showlink' ) == '1' ) {						
								if ( stripos( $customTweet, "#url#" ) === FALSE ) {
								$sentence = $customTweet . " " . $shrink;
								} else {
								$sentence = str_ireplace( "#url#", $shrink, $customTweet );
								}						
							} else {
							$sentence = $customTweet;
							}
						} 
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
						// Stores the posts CLIG in a custom field for later use as needed.
						store_url( $post_ID, $shrink );
	
					} else {
						$sentence = str_ireplace( "#url#", "", $sentence );
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
					}
				}
			} else if ( (( $_POST['originalaction'] == "editpost" ) && ( ( $_POST['prev_status'] == 'publish' ) || ($_POST['original_post_status'] == 'publish') ) ) && $get_post_info->post_status == 'publish') {
				// if this is an old post and editing updates are enabled
			if ( get_option( 'oldpost-edited-update') == '1' || get_option( 'jd_twit_edited_pages' ) == '1' ) {
					$sentence = stripcslashes( get_option( 'oldpost-edited-text' ) );					
					if ( get_option( 'oldpost-edited-showlink') == '1') {
						if ( $oldClig != '' ) {
						$old_post_link = $oldClig;
						} else {
						$old_post_link = jd_shorten_link( $thispostlink, $thisposttitle );
						}
					store_url( $post_ID, $old_post_link );
						
					if ( stripos( $sentence, "#url#" ) === FALSE ) {
						$sentence = $sentence . " " . $old_post_link;
					} else {
						$sentence = str_ireplace( "#url#", $old_post_link, $sentence );
					}	
					
					if ( $customTweet != "" ) {
						if ( get_option( 'oldpost-edited-showlink') == '1' ) {						
								if ( stripos( $customTweet, "#url#" ) === FALSE ) {
								$sentence = $customTweet . " " . $old_post_link;
								} else {
								$sentence = str_ireplace( "#url#", $old_post_link, $customTweet );
								}						
						} else {
						$sentence = $customTweet; 
						}
					}
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
					} else {
						$sentence = str_ireplace( "#url#", "", $sentence );
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
					}
			}
			
		}
	
		if ( $sentence != '' ) {
			if ( get_option( 'use_tags_as_hashtags' ) == '1' ) {
				$sentence = $sentence . " " . generate_hash_tags( $post_ID );
			}		
			$sendToTwitter = jd_doTwitterAPIPost( $sentence, $authID );				
			if ( $sendToTwitter === FALSE ) {
			add_post_meta( $post_ID,'jd_wp_twitter',urldecode( $sentence ) );
			update_option( 'wp_twitter_failure','1' );
			}
		}
	}
	return $post_ID;
}

// Add Tweets on links in Blogroll
function jd_twit_link( $link_ID )  {
global $version;
	$thislinkprivate = $_POST['link_visible'];
	if ($thislinkprivate != 'N') {
		$thislinkname = urlencode( stripcslashes( $_POST['link_name'] ) );
		$thispostlink = urlencode( $_POST['link_url'] ) ;
		$thislinkdescription = urlencode( stripcslashes( $_POST['link_description'] ) );
		$sentence = '';

		# || (get_option( 'jd-use-link-title' ) == '1' && $thislinkname == '')
			if ( (get_option( 'jd-use-link-description' ) == '1' && $thislinkdescription == '') ) {
			$sentence = stripcslashes( get_option( 'newlink-published-text' ) );
			} else {
				if ( get_option( 'jd-use-link-description' ) == '1' && get_option ( 'jd-use-link-title' ) == '0' ) {
				$sentence = $thislinkdescription;
				} else if ( get_option( 'jd-use-link-description' ) == '0' && get_option ( 'jd-use-link-title' ) == '1' ) {
				$sentence = $thislinkname;
				}
			}
			if (mb_strlen( $sentence ) > 120) {
			$sentence = mb_substr($sentence,0,116) . '...';
			}
			// Generate and grab the clig using the Cli.gs API
			// cURL alternative contributed by Thor Erik (http://thorerik.net)
			$shrink = jd_shorten_link( $thispostlink, $thislinkname );
			
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

	
	if ( $jd_tweet_this == "yes" ) {
	
		$thispostlink = urlencode( external_or_permalink( $post_ID ) );
		$thisposttitle = urlencode( strip_tags( $get_post_info->post_title ) );	
		$authID = $get_post_info->post_author;		
		$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
		$excerpt_length = get_option( 'jd_post_excerpt' );
		if ($get_post_info->post_excerpt == "") {
		$thispostexcerpt = str_split( strip_tags($get_post_info->post_content), $excerpt_length );
		} else {
		$thispostexcerpt = str_split( strip_tags($get_post_info->post_excerpt), $excerpt_length );	
		}
		$thispostexcerpt = $thispostexcerpt[0];		
		$sentence = '';
		$customTweet = get_post_meta( $post_ID, 'jd_twitter', TRUE ); 
		$sentence = stripcslashes(get_option( 'newpost-published-text' ));
			if ( get_option( 'newpost-published-showlink' ) == '1' ) {
			$shrink = jd_shorten_link( $thispostlink, $thisposttitle );
						if ( stripos($sentence,"#url#") === FALSE ) {
						$sentence = $sentence . " " . $shrink;
						} else {
						$sentence = str_ireplace("#url#",$shrink,$sentence);
						}
			if ( $customTweet != "" ) {
				// Get the custom Tweet message if it's been supplied. Truncate it to fit if necessary.
					if ( get_option( 'newpost-published-showlink' ) == '1' ) {
						if ( ( mb_strlen( $customTweet ) + 21) > 140 ) {
						$customTweet = mb_substr( $customTweet, 0, 119 );
						}
					} else {
						if ( mb_strlen( $customTweet ) > 140 ) {
						$customTweet = mb_substr( $customTweet, 0, 140 );
						}						
					}
					if ( get_option( 'newpost-published-showlink' ) == '1' ) {						
								if ( stripos( $customTweet, "#url#" ) === FALSE ) {
								$sentence = $customTweet . " " . $shrink;
								} else {
								$sentence = str_ireplace( "#url#", $shrink, $customTweet );
								}							
					} else {
					$sentence = $customTweet;
					}
				} 
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
				// Stores the post's short URL in a custom field for later use as needed.
				store_url($post_ID, $shrink);
				} else {
						$sentence = str_ireplace( "#url#", "", $sentence );
				
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
			}
		
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
		if ($get_post_info->post_excerpt == "") {
		$thispostexcerpt = str_split( strip_tags($get_post_info->post_content), $excerpt_length );
		} else {
		$thispostexcerpt = str_split( strip_tags($get_post_info->post_excerpt), $excerpt_length );	
		}
		$thispostexcerpt = $thispostexcerpt[0];		
		$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
		$sentence = '';
		$customTweet = get_post_meta( $post_ID, 'jd_twitter', TRUE ); 
		$sentence = stripcslashes(get_option( 'newpost-published-text' ));
			if ( get_option( 'newpost-published-showlink' ) == '1' ) {
			$shrink = jd_shorten_link( $thispostlink, $thisposttitle );
						if ( stripos($sentence,"#url#") === FALSE ) {
						$sentence = $sentence . " " . $shrink;
						} else {
						$sentence = str_ireplace("#url#",$shrink,$sentence);
						}
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
				// Stores the posts CLIG in a custom field for later use as needed.
				store_url($post_ID, $shrink);	
			} else {
						$sentence = str_ireplace( "#url#", "", $sentence );			
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
			}

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
		$excerpt_length = get_option( 'jd_post_excerpt' );
		if ($get_post_info->post_excerpt == "") {
		$thispostexcerpt = str_split( strip_tags($get_post_info->post_content), $excerpt_length );
		} else {
		$thispostexcerpt = str_split( strip_tags($get_post_info->post_excerpt), $excerpt_length );	
		}
		$thispostexcerpt = $thispostexcerpt[0];
		$thisblogtitle = urlencode( get_bloginfo( 'name' ) );
		$sentence = '';
		$sentence = stripcslashes(get_option( 'newpost-published-text' ));
			if ( get_option( 'newpost-published-showlink' ) == '1' ) {
			$shrink = jd_shorten_link( $thispostlink, $thisposttitle );
						if ( stripos($sentence,"#url#") === FALSE) {
						$sentence = $sentence . " " . $shrink;
						} else {
						$sentence = str_ireplace("#url#",$shrink,$sentence);
						}
				// Check the length of the tweet and truncate parts as necessary.
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
				// Stores the posts CLIG in a custom field for later use as needed.
				store_url($post_ID, $shrink);		
			} else {
						$sentence = str_ireplace( "#url#", "", $sentence );			
						$sentence = jd_truncate_tweet( $sentence, $thisposttitle, $thisblogtitle, $thispostexcerpt, $authID );
			}
			
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
// NEW IF ADD
add_action('admin_menu','jd_add_twitter_outer_box');

function store_url($post_ID, $url) {
	if ( get_option( 'jd_shortener' ) == 0 || get_option( 'jd_shortener' ) == 1) {
			if ( get_post_meta ( $post_ID, 'wp_jd_clig', TRUE ) != $url ) {
			add_post_meta ( $post_ID, 'wp_jd_clig', $url );
			}	
	} elseif ( get_option( 'jd_shortener' ) == 2 ) {
			if ( get_post_meta ( $post_ID, 'wp_jd_bitly', TRUE ) != $url ) {
			add_post_meta ( $post_ID, 'wp_jd_bitly', $url );
			}	
	}
	$target = jd_expand_url( $url );
	add_post_meta( $post_ID, 'wp_jd_target', $target );
}

function generate_hash_tags( $post_ID ) {
global $wp_version;
	if ( version_compare( $wp_version,"2.8",">=" ) ) {
		$tags = $_POST['tax_input']['post_tag'] . "," . $_POST['newtag']['post_tag'];
		} else {
		$tags = $_POST['tags_input'];
		}
	$tags = explode(",",$tags);
		foreach ( $tags as $value ) {
		$value = str_ireplace( " ","_",trim( $value ) );
			if ( $value != __( "Add_new_tag" , 'wptotwitter') ) { 
			$hashtags .= "#$value ";
			}
		}
	$hashtags = trim( $hashtags );
	if ( mb_strlen( $hashtags ) <= 1 ) {
	$hashtags = "";
	}
	return $hashtags;
}

function jd_add_twitter_old_box() {
?>

<div class="dbx-b-ox-wrapper">
<fieldset id="twitdiv" class="dbx-box">
<div class="dbx-h-andle-wrapper">
<h3 class="dbx-handle"><?php _e('WP to Twitter', 'wp-to-twitter', 'wptotwitter') ?></h3>
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
<label for="jd_twitter"><?php _e('Twitter Post', 'wp-to-twitter', 'wptotwitter') ?></label><br /><textarea style="width:95%;" name="jd_twitter" id="jd_twitter" rows="2" cols="60"
	onKeyDown="countChars(document.post.jd_twitter,document.post.twitlength)"
	onKeyUp="countChars(document.post.jd_twitter,document.post.twitlength)"><?php echo attribute_escape( $jd_twitter ); ?></textarea>
</p>
<p><input readonly type="text" name="twitlength" size="3" maxlength="3" value="<?php echo attribute_escape( mb_strlen( $description) ); ?>" />
<?php _e(' characters.<br />Twitter posts are a maximum of 140 characters; if your Cli.gs URL is appended to the end of your document, you have 119 characters available. You can use <code>#url#</code>, <code>#title#</code>, <code>#post#</code> or <code>#blog#</code> to insert the shortened URL, post title, a post excerpt or blog name into the Tweet.', 'wp-to-twitter', 'wptotwitter') ?> <a target="__blank" href="<?php echo $jd_plugin_url; ?>"><?php _e('Get Support', 'wp-to-twitter', 'wptotwitter') ?></a> &raquo;
</p>
<p>
<input type="checkbox" name="jd_tweet_this" value="no"<?php echo attribute_escape( $jd_selected ); ?> id="jd_tweet_this" /> <label for="jd_tweet_this"><?php _e("Don't Tweet this post.", 'wptotwitter'); ?></label>
</p>
<p>
<?php
if ( $jd_short != "" ) {
	_e("The previously-posted $shortener URL for this post is <code>$jd_short</code>, which points to <code>$jd_expansion</code>.", 'wptotwitter');
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
			delete_post_meta( $id, 'jd_twitter' );
			add_post_meta( $id, 'jd_twitter', $jd_twitter );
		}
	$jd_tweet_this = $_POST[ 'jd_tweet_this' ];
	if ($jd_tweet_this == 'no') {
		delete_post_meta( $id, 'jd_tweet_this' );
		add_post_meta( $id, 'jd_tweet_this', 'no');
	} else {
		delete_post_meta( $id, 'jd_tweet_this' );
		add_post_meta( $id, 'jd_tweet_this', 'yes');
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
		<h3><?php _e('WP to Twitter User Settings', 'wptotwitter'); ?></h3>
		
		<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Use My Twitter Account', 'wptotwitter'); ?></th>
			<td><input type="radio" name="wp-to-twitter-enable-user" id="wp-to-twitter-enable-user" value="userTwitter"<?php if ($is_enabled == "userTwitter" || $is_enabled == "true" ) { echo " checked='checked'"; } ?> /> <label for="wp-to-twitter-enable-user"><?php _e('Select this option if you would like your posts to be Tweeted into your own Twitter account with no @ references.', 'wptotwitter'); ?></label><br />
<input type="radio" name="wp-to-twitter-enable-user" id="wp-to-twitter-enable-user-2" value="userAtTwitter"<?php if ($is_enabled == "userAtTwitter") { echo " checked='checked'"; } ?> /> <label for="wp-to-twitter-enable-user-2"><?php _e('Tweet my posts into my Twitter account with an @ reference to the site\'s main Twitter account.', 'wptotwitter'); ?></label><br />
<input type="radio" name="wp-to-twitter-enable-user" id="wp-to-twitter-enable-user-3" value="mainAtTwitter"<?php if ($is_enabled == "mainAtTwitter") { echo " checked='checked'"; } ?> /> <label for="wp-to-twitter-enable-user-3"><?php _e('Tweet my posts into the main site Twitter account with an @ reference to my username. (Password not required with this option.)', 'wptotwitter'); ?></label></td>
		</tr>
		<tr>
			<th scope="row"><label for="wp-to-twitter-user-username"><?php _e('Your Twitter Username', 'wptotwitter'); ?></label></th>
			<td><input type="text" name="wp-to-twitter-user-username" id="wp-to-twitter-user-username" value="<?php echo attribute_escape( $twitter_username ); ?>" /> <?php _e('Enter your own Twitter username.', 'wptotwitter'); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="wp-to-twitter-user-password"><?php _e('Your Twitter Password', 'wptotwitter'); ?></label></th>
			<td><input type="password" name="wp-to-twitter-user-password" id="wp-to-twitter-user-password" value="" /> <?php _e('Enter your own Twitter password.', 'wptotwitter'); ?></td>
		</tr>
		</table>
		<?php
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
margin: 0;
padding:0;
border: none;
}
#wp-to-twitter form p {
background: #eaf3fa;
padding: 10px 5px;
margin: 4px 0;
border: 1px solid #eee;
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
-->
</style>";
 }
// Include the Manager page
function jd_wp_Twitter_manage_page() {
	if ( file_exists ( dirname(__FILE__).'/wp-to-twitter-manager.php' )) {
    include( dirname(__FILE__).'/wp-to-twitter-manager.php' );
	} else {
	echo "<p>Couldn't locate the settings page.</p>";
	}
}
function plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/wp-to-twitter.php'))
		$links[] = "<a href='options-general.php?page=wp-to-twitter/wp-to-twitter.php'>" . __('Settings', 'wp-to-twitter', 'wptotwitter') . "</a>";
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
	add_action('admin_notices', create_function( '', "echo '<div class=\"error\"><p>';_e('There\'s been an error posting your Twitter status! <a href=\"".get_bloginfo('wpurl')."/wp-admin/options-general.php?page=wp-to-twitter/wp-to-twitter.php\">Visit your WP to Twitter settings page</a> to get more information and to clear this error message.'); echo '</p></div>';" ) );
}
if ( get_option( 'jd_twit_pages' )=='1' ) {
	add_action( 'publish_page', 'jd_twit' );
}
if ( get_option( 'jd_twit_edited_pages' )=='1' ) {
	add_action( 'publish_page', 'jd_twit' );
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
?>