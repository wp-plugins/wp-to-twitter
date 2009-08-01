<?php
	//update_option( 'twitterInitialised', '0' );
	// FUNCTION to see if checkboxes should be checked
	function jd_checkCheckbox( $theFieldname ) {
		if( get_option( $theFieldname ) == '1'){
			echo 'checked="checked"';
		}
	}	
	$wp_twitter_error = FALSE;
	$wp_cligs_error = FALSE;
	$message = "";
	//SETS DEFAULT OPTIONS
	if( get_option( 'twitterInitialised') != '1' ) {
		update_option( 'newpost-published-update', '1' );
		update_option( 'newpost-published-text', 'New post: #title# (#url#)' );
		update_option( 'newpost-published-showlink', '1' );
		update_option( 'jd_twit_quickpress', '1' );
		
		update_option( 'oldpost-edited-update', '1' );
		update_option( 'oldpost-edited-text', 'Post Edited: #title# (#url#)' );
		update_option( 'oldpost-edited-showlink', '1' );

		update_option( 'jd_twit_pages','0' );
		update_option( 'jd_twit_edited_pages','0' );
		
		update_option( 'jd_twit_remote', '0' );
		
		// Use Google Analytics with Twitter
		update_option( 'twitter-analytics-campaign', '' );
		update_option( 'use-twitter-analytics', '0' );
		
		// Use custom external URLs to point elsewhere. 
		update_option( 'jd_twit_custom_url', 'external_link' );
		
		// Cligs API
		update_option( 'cligsapi','' );
		
		// Error checking
		update_option( 'jd_functions_checked','0' );
		update_option( 'wp_twitter_failure','0' );
		update_option( 'wp_cligs_failure','0' );
		
		// Blogroll options
		update_option ('jd-use-link-title','0' );
		update_option( 'jd-use-link-description','1' );
		update_option( 'newlink-published-text', 'New link posted: ' );
		update_option( 'jd_twit_blogroll', '1');
		
		// Default publishing options.
		update_option( 'jd_tweet_default', '0' );
		// Note that default options are set.
		update_option( 'twitterInitialised', '1' );

		$message = __("Set your Twitter login information and Cli.gs API to use this plugin! ");
	}
	if( get_option( 'twitterInitialised') == '1' && get_option( 'twitterpw' ) == "" ) {
		$message .= __("Please add your Twitter password. ");
	}
	
	if ( isset($_POST['submit-type']) && $_POST['submit-type'] == 'clear-error' ) {
		update_option( 'wp_twitter_failure','0' );
		update_option( 'wp_cligs_failure','0' );
		$message =  __("WP to Twitter Errors Cleared");
	}

// Error messages on failures	
if ( get_option( 'wp_twitter_failure' ) == '1' || get_option( 'wp_cligs_failure' ) == '1' ) {
		if ( get_option( 'wp_cligs_failure' ) == '1' ) {
			$wp_to_twitter_failure .= "<p>" . __("Cli.gs request failed! We couldn't shrink that URL, so we attached the normal URL to your Tweet. Check the <a href='http://blog.cli.gs'>Cli.gs blog</a> to see if there are any known issues. ") . "</p>";
		}
		
		if ( get_option( 'wp_twitter_failure' ) == '1' ) {
			$wp_to_twitter_failure .= "<p>" . __("Sorry! I couldn't get in touch with the Twitter servers to post your new blog post. Your tweet has been stored in a custom field attached to the post, so you can Tweet it manually if you wish! ") . "</p>";
		} else if ( get_option( 'wp_twitter_failure' ) == '2') {
			$wp_to_twitter_failure .= "<p>" . __("Sorry! I couldn't get in touch with the Twitter servers to post your <strong>new link</strong>! You'll have to post it manually, I'm afraid. ") . "</p>";
		}
		
	} else {
	$wp_to_twitter_failure = '';
}

	if ( isset($_POST['submit-type']) && $_POST['submit-type'] == 'options' ) {
		//UPDATE OPTIONS
		update_option( 'newpost-published-update', $_POST['newpost-published-update'] );
		update_option( 'newpost-published-text', $_POST['newpost-published-text'] );
		update_option( 'newpost-published-showlink', $_POST['newpost-published-showlink'] );
		update_option( 'jd_tweet_default', $_POST['jd_tweet_default'] );
		update_option( 'oldpost-edited-update', $_POST['oldpost-edited-update'] );
		update_option( 'oldpost-edited-text', $_POST['oldpost-edited-text'] );
		update_option( 'oldpost-edited-showlink', $_POST['oldpost-edited-showlink'] );
		update_option( 'jd_twit_pages',$_POST['jd_twit_pages'] );
		update_option( 'jd_twit_edited_pages',$_POST['jd_twit_edited_pages'] );
		update_option( 'jd_twit_remote',$_POST['jd_twit_remote'] );
		update_option( 'jd_twit_custom_url', $_POST['jd_twit_custom_url'] );
		update_option( 'jd_twit_quickpress', $_POST['jd_twit_quickpress'] );
		
		update_option( 'jd_twit_prepend', $_POST['jd_twit_prepend'] );	
		update_option( 'jd_twit_append', $_POST['jd_twit_append'] );
		
		
		if ( $_POST['jd-use-link-field'] == '2' ) {
		update_option( 'jd-use-link-description', '1' );
		update_option( 'jd-use-link-title', '0' );
		
		} else if ( $_POST['jd-use-link-field'] == '1' ) {
		update_option( 'jd-use-link-title', '1' );	
		update_option( 'jd-use-link-description', '0' );
		}
		update_option( 'newlink-published-text', $_POST['newlink-published-text'] );
		update_option( 'jd_twit_blogroll',$_POST['jd_twit_blogroll'] );
		
		update_option( 'use-twitter-analytics', $_POST['use-twitter-analytics'] );
		update_option( 'twitter-analytics-campaign', $_POST['twitter-analytics-campaign'] );
		
		update_option( 'jd_individual_twitter_users', $_POST['jd_individual_twitter_users'] );
		
		$message = "WP to Twitter Options Updated";

	} else if ( isset($_POST['submit-type']) && $_POST['submit-type'] == 'login' ) {
		//UPDATE LOGIN
		if( ( $_POST['twitterlogin'] != '' ) && ( $_POST['twitterpw'] != '' ) ) {
			update_option( 'twitterlogin', $_POST['twitterlogin'] );
			update_option( 'twitterpw', $_POST['twitterpw'] );
			update_option( 'twitterlogin_encrypted', base64_encode( $_POST['twitterlogin'].':'.$_POST['twitterpw'] ) );
			$message = __("Twitter login and password updated. ");
		} else {
			$message = __("You need to provide your twitter login and password! ");
		}
	} else if ( isset($_POST['submit-type']) && $_POST['submit-type'] == 'cligsapi' ) {
		if ( $_POST['cligsapi'] != '' && isset( $_POST['submit'] ) ) {
			update_option( 'cligsapi',$_POST['cligsapi'] );
			$message = __("Cligs API Key Updated");
		} else if ( isset( $_POST['clear'] ) ) {
			update_option( 'cligsapi','' );
			$message = __("Cli.gs API Key deleted. Cli.gs created by WP to Twitter will no longer be associated with your account. ");
		} else {
			$message = __("Cli.gs API Key not added - <a href='http://cli.gs/user/api/'>get one here</a>! ");
		}
	}

	// Check whether the server has supported for needed functions.
	if (  isset($_POST['submit-type']) && $_POST['submit-type'] == 'check-support' ) {
	update_option('jd-functions-checked', '0');
	}
// If you're attempting to solve the "settings page doesn't display" problem, begin your comment here. 
	if ( get_option('jd-functions-checked') == '0') {
	$cligs_checker = new Snoopy;
	$twit_checker = new Snoopy;
	$testurl = urlencode("http://www.joedolson.com/articles/wp-to-twitter/");
	
	$cligs_checker->fetchtext( "http://cli.gs/api/v2/cligs/create?url=$testurl&appid=WP-to-Twitter&key=&output=&test=1" );
	$twit_checker->fetch( "http://twitter.com/help/test.json" );
	$wp_twitter_error = TRUE;	
	$wp_cligs_error = TRUE;
			if ( strlen(getfilefromurl("http://cli.gs/api/v2/cligs/create?url=$testurl&appid=WP-to-Twitter&key=&output=&test=1")) == 20 || strlen($cligs_checker->results) == 20 ) {
				$wp_cligs_error = FALSE;
				$message .= __("Successfully contacted the Cli.gs API. ");
				//$message .= "Twit: " . $twit_checker->results;			
			} else {
				$message .=__("Failed to contact the Cli.gs API. ");
			}
			if ( $twit_checker->results == "\"ok\"" || getfilefromurl("http://twitter.com/help/test.xml") == "<ok>true</ok>" ) {
				$wp_twitter_error = FALSE;
				$message .= __("Successfully contacted the Twitter API. ");
			} else {
				$message .= __("Failed to contact the Twitter API. ");
			}
		// If everything's OK, there's  no reason to do this again.
		if ($wp_twitter_error == FALSE && $wp_cligs_error == FALSE) {
		$message .= __("Your server appears to support the required PHP functions and classes for WP to Twitter to function.");
		update_option( 'jd-functions-checked','1' );		
		} else { 
			if ( !function_exists( 'fputs' ) ) {
				$wp_function_error = TRUE;
				$message .= __("Your server does not support <code>fputs</code>.");
			} 
			if ( !function_exists( 'curl_init' ) || !function_exists( 'file_get_contents' ) ) {
				$wp_function_error = TRUE;
				$message .= __("Your server does not support <code>file_get_contents</code> or <code>cURL</code> functions.");
			}
			if ( !class_exists( 'Snoopy' ) ) {
				$wp_function_error = TRUE;
				$message .= __("Your server does not support <code>Snoopy</code>.");			
			}
		
		$message .= __("Your server does not appear to support the required PHP functions and classes for WP to Twitter to function. You can try it anyway - these tests aren't perfect - but no guarantees.");
		update_option( 'jd-functions-checked','1' );		
			
		}
	}
// CLOSE BUG FIX COMMENT HERE
?>
<?php if ( $wp_twitter_error == TRUE || $wp_cligs_error == TRUE ) {
echo "<div class='error'><p>";
_e("This plugin may not work in your server environment.");
echo "</p></div>";
}
?>
<?php if ($message) { ?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php } ?>
<div id="dropmessage" class="updated" style="display:none;"></div>

<div class="wrap" id="wp-to-twitter">

<h2><?php _e("WP to Twitter Options"); ?></h2>
<p>
<?php _e("For any update field, you can use the codes <code>#title#</code> for the title of your blog post, <code>#blog#</code> for the title of your blog, or <code>#url#</code> for the post URL! Given the character limit for Twitter, you may not want to include your blog title."); ?>
</p>
		
<?php if ( get_option( 'wp_twitter_failure' ) == '1' || get_option( 'wp_cligs_failure' ) == '1' ) { ?>
		<div class="error">
		<p>
		<?php if ( get_option( 'wp_twitter_failure' ) == '1' ) {
		_e("One or more of your last posts has failed to send it's status update to Twitter. Your Tweet has been saved in the custom meta data for your post, and you can re-Tweet it at your leisure.");
		}
		if ( get_option( 'wp_cligs_failure' ) == '1' ) {
		_e("The query to the Cli.gs API failed, and your URL was not shrunk. The normal post URL was attached to your Tweet.");
		}
		echo $wp_to_twitter_failure;
		?>
		</p>
		</div>
	<form method="post" action="">
			<div>
		<input type="hidden" name="submit-type" value="clear-error" />
		</div>
		<p><input type="submit" name="submit" value="<?php _e("Clear 'WP to Twitter' Error"); ?>" /> <strong><?php _e("Why are you seeing this field?"); ?></strong>
		</p>
	</form>
<?php
}
?>		
		
	<form method="post" action="">
	<div>
		<fieldset>
			<legend><?php _e("Wordpress to Twitter Publishing Options"); ?></legend>
			<p>
				<input type="checkbox" name="jd_twit_pages" id="jd_twit_pages" value="1" <?php jd_checkCheckbox('jd_twit_pages')?> />
				<label for="jd_twit_pages"><strong><?php _e("Update Twitter when new Wordpress Pages are published"); ?></strong></label>
			</p>
			<p>
				<input type="checkbox" name="jd_twit_edited_pages" id="jd_twit_edited_pages" value="1" <?php jd_checkCheckbox('jd_twit_edited_pages')?> />
				<label for="jd_twit_edited_pages"><strong><?php _e("Update Twitter when WordPress Pages are edited"); ?></strong></label>
			</p>
			<p>
				<input type="checkbox" name="newpost-published-update" id="newpost-published-update" value="1" <?php jd_checkCheckbox('newpost-published-update')?> />
				<label for="newpost-published-update"><strong><?php _e("Update Twitter when a new post is published"); ?></strong></label>
			</p>
			<p>
				<label for="newpost-published-text"><?php _e("Text for this Twitter update"); ?></label><br />
				<input type="text" name="newpost-published-text" id="newpost-published-text" size="60" maxlength="120" value="<?php echo(get_option('newpost-published-text')) ?>" />
				&nbsp;&nbsp;
				<input type="checkbox" name="newpost-published-showlink" id="newpost-published-showlink" value="1" <?php jd_checkCheckbox('newpost-published-showlink')?> />
				<label for="newpost-published-showlink"><?php _e("Provide link to blog?"); ?></label>
			</p>

			<p>
				<input type="checkbox" name="oldpost-edited-update" id="oldpost-edited-update" value="1" <?php jd_checkCheckbox('oldpost-edited-update')?> />
				<label for="oldpost-edited-update"><strong><?php _e("Update Twitter when an old post has been edited"); ?></strong></label>
			</p>
			<p>
				<label for="oldpost-edited-text">Text for this Twitter update</label><br />
				<input type="text" name="oldpost-edited-text" id="oldpost-edited-text" size="60" maxlength="120" value="<?php echo(get_option('oldpost-edited-text')) ?>" />
				&nbsp;&nbsp;
				<input type="checkbox" name="oldpost-edited-showlink" id="oldpost-edited-showlink" value="1" <?php jd_checkCheckbox('oldpost-edited-showlink')?> />
				<label for="oldpost-edited-showlink"><?php _e("Provide link to blog?"); ?></label>			
			</p>
			
			<p>
				<input type="checkbox" name="jd_twit_blogroll" id="jd_twit_blogroll" value="1" <?php jd_checkCheckbox('jd_twit_blogroll')?> />
				<label for="oldpost-edited-update"><strong><?php _e("Update Twitter when you post a Blogroll link"); ?></strong></label>
			</p>
			<p>
				<input type="radio" name="jd-use-link-field" id="jd-use-link-title" value="1" <?php jd_checkCheckbox('jd-use-link-title')?> /> <label for="jd-use-link-title"><?php _e("Use <strong>link title</strong> for Twitter updates"); ?></label> <input type="radio" name="jd-use-link-field" id="jd-use-link-description" value="2" <?php jd_checkCheckbox('jd-use-link-description')?> />	<label for="jd-use-link-description"><?php _e("Use <strong>link description</strong> for Twitter updates"); ?></label>			
			</p>			
						
			<p>
				<label for="newlink-published-text"><?php _e("Text for this Twitter update (used if above choice isn't available.)"); ?></label><br />
				<input type="text" name="newlink-published-text" id="newlink-published-text" size="60" maxlength="120" value="<?php echo(get_option('newlink-published-text')) ?>" />
			</p>
	
			<p>
				<input type="checkbox" name="jd_tweet_default" id="jd_tweet_default" value="1" <?php jd_checkCheckbox('jd_tweet_default')?> />
				<label for="jd_tweet_default"><strong><?php _e("Set default Tweet status to 'No.'"); ?></strong></label><br />
				<small><?php _e("Twitter updates can be set on a post by post basis. By default, posts WILL be posted to Twitter. Check this to change the default to NO."); ?></small>
			</p>
			<p>
				<input type="checkbox" name="jd_twit_remote" id="jd_twit_remote" value="1" <?php jd_checkCheckbox('jd_twit_remote')?> />
				<label for="jd_twit_remote"><strong><?php _e("Send Twitter Updates on remote publication (Post by Email or XMLRPC Client)"); ?></strong></label>
			</p>
			<p>
				<input type="checkbox" name="jd_twit_quickpress" id="jd_twit_quickpress" value="1" <?php jd_checkCheckbox('jd_twit_quickpress')?> />
				<label for="jd_twit_quickpress"><strong><?php _e("Update Twitter when a post is published using QuickPress"); ?></strong></label>
			</p>
			
			<p>
				<label for="jd_twit_prepend"><?php _e("Custom text to be prepended on every Tweet"); ?></label><br />
				<input type="text" name="jd_twit_prepend" id="jd_twit_prepend" size="60" maxlength="20" value="<?php echo(get_option('jd_twit_prepend')) ?>" />
			</p>	

			<p>
				<label for="jd_twit_append"><?php _e("Custom text to be appended on every Tweet"); ?></label><br />
				<input type="text" name="jd_twit_append" id="jd_twit_append" size="60" maxlength="20" value="<?php echo(get_option('jd_twit_append')) ?>" />
			</p>			
			
			<p>
				<label for="jd_twit_custom_url"><?php _e("Custom field containing an alternate URL to be shortened and Tweeted."); ?></label><br />
				<input type="text" name="jd_twit_custom_url" id="jd_twit_custom_url" size="60" maxlength="120" value="<?php echo(get_option('jd_twit_custom_url')) ?>" /><br />
				<small><?php _e("You can use a custom field to send Cli.gs and Twitter an alternate URL from the permalink provided by WordPress. The value is the name of the custom field you're using to add an external URL."); ?></small>
			</p>
			<p>
				<label for="twitter-analytics-campaign"><?php _e("Campaign identifier for Google Analytics"); ?></label><br />
				<input type="text" name="twitter-analytics-campaign" id="twitter-analytics-campaign" size="60" maxlength="120" value="<?php echo(get_option('twitter-analytics-campaign')) ?>" /><br />
				<small><?php _e("You can track the response from Twitter using Google Analytics by defining a campaign identifier here."); ?></small>
			</p>
			<p>
				<input type="checkbox" name="use-twitter-analytics" id="use-twitter-analytics" value="1" <?php jd_checkCheckbox('use-twitter-analytics')?> />
				<label for="use-twitter-analytics"><strong><?php _e("Use Google Analytics with WP-to-Twitter"); ?></strong></label>
			</p>
			<p>
				<input type="checkbox" name="jd_individual_twitter_users" id="jd_individual_twitter_users" value="1" <?php jd_checkCheckbox('jd_individual_twitter_users')?> />
				<label for="jd_individual_twitter_users"><strong><?php _e("Authors have individual Twitter accounts"); ?></strong></label><br /><small><?php _e('Each author can set their own Twitter username and password in their user profile. Their posts will be sent to their own Twitter accounts.'); ?></small>
			</p>			
			
		<div>
		<input type="hidden" name="submit-type" value="options" />
		</div>
		<input type="submit" name="submit" value="<?php _e("Save WP->Twitter Options"); ?>" class="button-primary" />
	</fieldset>

	</div>
	</form>

	<h2 class="twitter"><?php _e("Your Twitter account details"); ?></h2>
	
	<form method="post" action="" >
	<div>
		<p>
		<label for="twitterlogin"><?php _e("Your Twitter username:"); ?></label>
		<input type="text" name="twitterlogin" id="twitterlogin" value="<?php echo(get_option('twitterlogin')) ?>" />
		</p>
		<p>
		<label for="twitterpw"><?php _e("Your Twitter password:"); ?></label>
		<input type="password" name="twitterpw" id="twitterpw" value="" />
		</p>
		<input type="hidden" name="submit-type" value="login" />
		<p><input type="submit" name="submit" value="<?php _e("Save Twitter Login Info"); ?>" class="button-primary" /> <?php _e("&raquo; <small>Don't have a Twitter account? <a href='http://www.twitter.com'>Get one for free here</a>"); ?></small></p>
	</div>
	</form>

<h2 class="cligs"><?php _e("Your Cli.gs account details"); ?></h2>

	<form method="post" action="">
	<div>
		<p>
		<label for="cligsapi"><?php _e("Your Cli.gs <abbr title='application programming interface'>API</abbr> Key:"); ?></label>
		<input type="text" name="cligsapi" id="cligsapi" size="40" value="<?php echo(get_option('cligsapi')) ?>" />
		</p>
		<div>
		<input type="hidden" name="submit-type" value="cligsapi" />
		</div>
		<p><input type="submit" name="submit" value="Save Cli.gs API Key" class="button-primary" /> <input type="submit" name="clear" value="Clear Cli.gs API Key" />&raquo; <small><?php _e("Don't have a Cli.gs account or Cligs API key? <a href='http://cli.gs/user/api/'>Get one free here</a>!<br />You'll need an API key in order to associate the Cligs you create with your Cligs account."); ?></small></p>
	</div>
	</form>
		
</div>

	<form method="post" action="">
	<div>
	<input type="hidden" name="submit-type" value="check-support" />
		<p>
		<input type="submit" name="submit" value="Check Support Level" class="button-primary" /> <small>Check whether your server supports the functions required for the Twitter and Cli.gs API calls to work.</small>
		</p>
	</div>
	</form>



<div class="wrap">
	<h3><?php _e("Need help?"); ?></h3>
	<p><?php _e("Visit the <a href='http://www.joedolson.com/articles/wp-to-twitter/'>WP to Twitter plugin page</a>."); ?></p>
		
</div>