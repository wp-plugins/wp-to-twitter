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
echo "<div class=\"settings\">";
echo "<strong>Raw Settings Output:</strong>";
echo "<pre>";
echo get_option( 'newpost-published-update' );echo " : ";echo get_option( 'newpost-published-text' );echo " : ";echo get_option( 'newpost-published-showlink' );
echo "<br />";
echo get_option( 'oldpost-edited-update' );echo " : ";echo get_option( 'oldpost-edited-text' );echo " : ";echo get_option( 'oldpost-edited-showlink' );
echo "<br />";
echo get_option( 'jd_twit_pages' );echo " : ";echo get_option( 'newpage-published-text' );
echo "<br />";
echo get_option( 'jd_twit_edited_pages' );echo " : ";echo get_option( 'oldpage-edited-text' );
echo "<br />[";
echo get_option( 'use_tags_as_hashtags' );echo " | ";echo get_option( 'jd_max_tags' );echo " | ";echo get_option( 'jd_max_characters' );echo "]<br />";
echo get_option( 'jd_twit_blogroll' );echo " : ";echo get_option( 'newlink-published-text' );echo " : ";echo get_option( 'jd-use-link-title' );echo " : ";echo get_option( 'jd-use-link-description' );
echo "<br />";
echo get_option( 'jd_post_excerpt' );
echo "<br />[";echo get_option( 'jd_twit_prepend' );echo " | ";echo get_option( 'jd_twit_append' );echo "]<br />";
echo get_option( 'jd_twit_custom_url' );
echo "<br />[";echo get_option( 'jd_tweet_default' );echo " | ";echo get_option( 'jd_twit_remote' );echo " | ";echo get_option( 'jd_twit_quickpress' );echo "]<br />[";
echo get_option( 'use-twitter-analytics' );echo " : ";echo get_option( 'twitter-analytics-campaign' );echo "]<br />Individuals:";
echo get_option( 'jd_individual_twitter_users' );
echo "<br />[";echo get_option( 'jd-use-cligs' );echo " | ";echo get_option( 'jd-use-bitly' );echo " | ";echo get_option( 'jd-use-none' );echo "]<br />";
// Use custom external URLs to point elsewhere. 
echo get_option( 'twitterlogin' );
echo "<br />";
if ( get_option('twitterpw') != "") {
_e( "Twitter Password Saved",'wp-to-twitter' );
} else {
_e( "Twitter Password Not Saved",'wp-to-twitter' );
}
echo "<br />[";echo get_option( 'cligsapi' );echo " | ";echo get_option( 'bitlylogin' );echo " | ";echo get_option( 'bitlyapi' );
echo "]<br />";
echo "[";echo get_option( 'jd-functions-checked' );echo " | ";echo get_option( 'wp_twitter_failure' );echo " | ";echo get_option( 'wp_cligs_failure' );echo " | ";echo get_option( 'wp_url_failure' );echo " | ";echo get_option( 'wp_bitly_failure' );echo " | ";echo get_option( 'twitterInitialised' );echo " | ";echo get_option( 'wp_cligs_failure' );echo " | ";echo get_option( 'jd_shortener' );echo "]</pre>";

echo "<p>";
_e( "[<a href='options-general.php?page=wp-to-twitter/wp-to-twitter.php'>Hide</a>] If you're experiencing trouble, please copy these settings into any request for support.",'wp-to-twitter'); 
echo "</p></div>
}
?>