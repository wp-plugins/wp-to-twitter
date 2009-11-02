<?php 
// This file contains secondary functions supporting WP to Twitter
// These functions don't perform any WP to Twitter actions, but are sometimes called for when 
// support for primary functions is lacking.

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
if (!function_exists('mb_strlen')) {
	function mb_strlen($data) {
		return strlen($data);
	}
}

if (!function_exists('mb_substr')) {
	function mb_substr($data,$start,$length = null, $encoding = null) {
		return substr($data,$start,$length);
	}
}

// str_ireplace substitution for PHP4
if ( !function_exists( 'str_ireplace' ) ) {
	function str_ireplace( $needle, $str, $haystack ) {
		$needle = preg_quote( $needle, '/' );
		return preg_replace( "/$needle/i", $str, $haystack );
	}
}
// str_split substitution for PHP4
if( !function_exists( 'str_split' ) ) {
    function str_split( $string,$string_length=1 ) {
        if( strlen( $string )>$string_length || !$string_length ) {
            do {
                $c = strlen($string);
                $parts[] = substr($string,0,$string_length);
                $string = substr($string,$string_length);
            } while($string !== false);
        } else {
            $parts = array($string);
        }
        return $parts;
    }
}
// mb_substr_replace substition for PHP4
if ( !function_exists( 'mb_substr_replace' ) ) {
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
function print_settings() {
global $version;
echo "<div class=\"settings\">";
echo "<strong>Raw Settings Output: Version $version</strong>";
echo "<pre>";
echo get_option( 'newpost-published-update' ) . " : " . get_option( 'newpost-published-text' ) . " : " . get_option( 'newpost-published-showlink' );
echo "<br />";
echo get_option( 'oldpost-edited-update' ) . " : " . get_option( 'oldpost-edited-text' ) . " : " . get_option( 'oldpost-edited-showlink' );
echo "<br />";
echo get_option( 'jd_twit_pages' ) . " : " . get_option( 'newpage-published-text' );
echo "<br />";
echo get_option( 'jd_twit_edited_pages' ) . " : " . get_option( 'oldpage-edited-text' );
echo "<br />[";
echo get_option( 'use_tags_as_hashtags' ) . " | " . get_option( 'jd_max_tags' ) . " | " . get_option( 'jd_max_characters' ) . "]<br />";
echo get_option( 'jd_twit_blogroll' ) . " : " . get_option( 'newlink-published-text' ) . " : " . get_option( 'jd-use-link-title' ) . " : " . get_option( 'jd-use-link-description' );
echo "<br />";
echo get_option( 'jd_post_excerpt' );
echo "<br />[" . get_option( 'jd_twit_prepend' ) . " | " . get_option( 'jd_twit_append' ) . "]<br />";
echo get_option( 'jd_twit_custom_url' );
echo "<br />[" . get_option( 'jd_tweet_default' ) . " | " . get_option( 'jd_twit_remote' ) . " | " . get_option( 'jd_twit_quickpress' ) . "]<br />[";
echo get_option( 'use-twitter-analytics' ) . " : " . get_option( 'twitter-analytics-campaign' ) . "]<br />Individuals:";
echo get_option( 'jd_individual_twitter_users' );
echo "<br />[" . get_option( 'jd-use-cligs' ) . " | " . get_option( 'jd-use-bitly' ) . " | " . get_option( 'jd-use-none' ) . "]<br />";
echo get_option( 'twitterlogin' );
echo "<br />";
if ( get_option('twitterpw') != "") {
_e( "Twitter Password Saved",'wp-to-twitter' );
} else {
_e( "Twitter Password Not Saved",'wp-to-twitter' );
}
if ( get_option( 'bitlyapi' ) != "") {
$bitlyapi = __( "Bit.ly API Saved",'wp-to-twitter' );
} else {
$bitlyapi = __( "Bit.ly API Not Saved",'wp-to-twitter' );
}
echo "<br />[" . get_option( 'cligsapi' ) . " | " . get_option( 'bitlylogin' ) . " | $bitlyapi ]<br />";
echo "[" . get_option( 'jd-functions-checked' ) . " | " . get_option( 'wp_twitter_failure' ) . " | " . get_option( 'wp_cligs_failure' ) . " | " . get_option( 'wp_url_failure' ) . " | " . get_option( 'wp_bitly_failure' ) . " | " . get_option( 'twitterInitialised' ) ." | " . get_option( 'jd_shortener' ) . "]</pre>";

echo "<p>";
_e( "[<a href='options-general.php?page=wp-to-twitter/wp-to-twitter.php'>Hide</a>] If you're experiencing trouble, please copy these settings into any request for support.",'wp-to-twitter'); 
echo "</p></div>";
}
?>