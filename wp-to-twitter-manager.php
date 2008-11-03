<?php

$wp_to_twitter_directory = get_bloginfo( 'wpurl' ) . '/' . PLUGINDIR . '/' . dirname( plugin_basename(__FILE__) );

	//update_option('twitterInitialised', '0');
	//SETS DEFAULT OPTIONS
	if(get_option('twitterInitialised') != '1'){
		update_option('newpost-published-update', '1');
		update_option('newpost-published-text', 'Published a new post: #title#');
		update_option('newpost-published-showlink', '1');

		update_option('oldpost-edited-update', '1');
		update_option('oldpost-edited-text', 'Blog post just edited: #title#');
		update_option('oldpost-edited-showlink', '1');

		update_option('twitterInitialised', '1');
		update_option('cligsapi','0');
		update_option('jd_twit_pages','0');
		$message = "Set your Twitter login information and Cli.gs API to use this plugin!";
	}
	

	if($_POST['submit-type'] == 'options'){
		//UPDATE OPTIONS
		update_option('newpost-published-update', $_POST['newpost-published-update']);
		update_option('newpost-published-text', $_POST['newpost-published-text']);
		update_option('newpost-published-showlink', $_POST['newpost-published-showlink']);

		update_option('oldpost-edited-update', $_POST['oldpost-edited-update']);
		update_option('oldpost-edited-text', $_POST['oldpost-edited-text']);
		update_option('oldpost-edited-showlink', $_POST['oldpost-edited-showlink']);
		update_option('jd_twit_pages',$_POST['jd_twit_pages']);
		$message = "WP to Twitter Options Updated";

	}else if ($_POST['submit-type'] == 'login'){
		//UPDATE LOGIN
		if(($_POST['twitterlogin'] != '') AND ($_POST['twitterpw'] != '')){
			update_option('twitterlogin', $_POST['twitterlogin']);
			update_option('twitterlogin_encrypted', base64_encode($_POST['twitterlogin'].':'.$_POST['twitterpw']));
			$message = "Twitter login and password updated.";
		} else {
			$message = "You need to provide your twitter login and password!";
		}
	} else if ($_POST['submit-type'] == 'cligsapi') {
		if($_POST['cligsapi'] != '') {
			update_option('cligsapi',$_POST['cligsapi']);
			$message = "Cligs API Key Updated";
		} else {
			$message = "Cli.gs API Key not added - <a href='http://cli.gs/user/api/'>get one here</a>!";
		}
	}

	// FUNCTION to see if checkboxes should be checked
	function jd_checkCheckbox($theFieldname){
		if( get_option($theFieldname) == '1'){
			echo('checked="true"');
		}
	}
?>
<style type="text/css">
<!-- 
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
.floatright {
float: right;
}
.cligs {
background: #efecdb url(<?php echo $wp_to_twitter_directory; ?>/images/cligs.png)  right 50% no-repeat;
padding: 2px!important;
}
.twitter {
background: url(<?php echo $wp_to_twitter_directory; ?>/images/twitter.png)  right 50% no-repeat;
padding: 2px!important;
}
-->
</style>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div id="dropmessage" class="updated" style="display:none;"></div>

<div class="wrap" id="wp-to-twitter">
<h2>WP to Twitter Options</h2>
<p>
For any update field, you can use the codes <code>#title#</code> for the title of your blog post or <code>#blog#</code> for the title of your blog! Given the character limit for Twitter, you may not want to include your blog title.
</p>
	<form method="post">
	<div>
		<fieldset>
			<legend>Wordpress to Twitter Publishing Options</legend>
			<p>
				<input type="checkbox" name="jd_twit_pages" id="jd_twit_pages" value="1" <?php jd_checkCheckbox('jd_twit_pages')?> />
				<label for="jd_twit_pages">Update Twitter when new Wordpress Pages are published</label>
			</p>		
			<p>
				<input type="checkbox" name="newpost-published-update" id="newpost-published-update" value="1" <?php jd_checkCheckbox('newpost-published-update')?> />
				<label for="newpost-published-update">Update Twitter when the new post is published</label>
			</p>
			<p>
				<label for="newpost-published-text">Text for this Twitter update</label><br />
				<input type="text" name="newpost-published-text" id="newpost-published-text" size="60" maxlength="146" value="<?php echo(get_option('newpost-published-text')) ?>" />
				&nbsp;&nbsp;
				<input type="checkbox" name="newpost-published-showlink" id="newpost-published-showlink" value="1" <?php jd_checkCheckbox('newpost-published-showlink')?> />
				<label for="newpost-published-showlink">Provide link to blog?</label>
			</p>

			<p>
				<input type="checkbox" name="oldpost-edited-update" id="oldpost-edited-update" value="1" <?php jd_checkCheckbox('oldpost-edited-update')?> />
				<label for="oldpost-edited-update">Update Twitter when the an old post has been edited</label>
			</p>
			<p>
				<label for="oldpost-edited-text">Text for this Twitter update</label><br />
				<input type="text" name="oldpost-edited-text" id="oldpost-edited-text" size="60" maxlength="146" value="<?php echo(get_option('oldpost-edited-text')) ?>" />
				&nbsp;&nbsp;
				<input type="checkbox" name="oldpost-edited-showlink" id="oldpost-edited-showlink" value="1" <?php jd_checkCheckbox('oldpost-edited-showlink')?> />
				<label for="oldpost-edited-showlink">Provide link to blog?</label>			
			</p>
		<div>
		<input type="hidden" name="submit-type" value="options" />
		</div>
		<input type="submit" name="submit" value="Save WP->Twitter Options" />
	</fieldset>

	</div>
	</form>

	<h2 class="twitter">Your Twitter account details</h2>
	
	<form method="post" >
	<div>
		<p>
		<label for="twitterlogin">Your Twitter username:</label>
		<input type="text" name="twitterlogin" id="twitterlogin" value="<?php echo(get_option('twitterlogin')) ?>" />
		</p>
		<p>
		<label for="twitterpw">Your Twitter password:</label>
		<input type="password" name="twitterpw" id="twitterpw" value="" />
		</p>
		<input type="hidden" name="submit-type" value="login">
		<p><input type="submit" name="submit" value="Save Twitter Login Info" /> &raquo; <small>Don't have a Twitter account? <a href="http://www.twitter.com">Get one for free here</a></small></p>
	</div>
	</form>

<h2 class="cligs">Your Cli.gs account details</h2>

	<form method="post">
	<div>
		<p>
		<label for="cligsapi">Your Cli.gs API Key:</label>
		<input type="text" name="cligsapi" id="cligsapi" size="40" value="<?php echo(get_option('cligsapi')) ?>" />
		</p>
		<div>
		<input type="hidden" name="submit-type" value="cligsapi">
		</div>
		<p><input type="submit" name="submit" value="Save Cli.gs API Key" /> &raquo; <small>Don't have a Cli.gs account or Cligs API key? <a href="http://cli.gs">Get one free here</a>! You'll need an API key in order to associate the Cligs you create with your Cligs account.</small></p>
	</div>
	<div>
	

	</div>
	</form>
	
</div>


<div class="wrap">
	<h3>Need help?</h3>
	<p>Visit the <a href="http://www.joedolson.com/articles/wp-to-twitter/">WP to Twitter plugin page</a>.</p>
</div>