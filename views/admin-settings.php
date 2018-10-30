<?php
$tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'general';
$subtab = (isset($_GET['subtab'])) ? $_GET['subtab'] : 'confirmation';
$wp_pages = get_pages();
$mailchimp_lists = get_mailchimp_list();
$mail_template = array(
					'confirmation' => array(
										'file_path' => plugins_url('ugc/views/email-activation.html'),
										'abs_path' => UGC_PLUGIN_DIR.'/views/email-activation.html',
										'option_name' => 'ugc-activation-subject'
									),
					'forgot' => array(
									'file_path'=> plugins_url('ugc/views/email-reset.html'),
									'abs_path' => UGC_PLUGIN_DIR.'/views/email-reset.html',
									'option_name' => 'ugc-reset-subject'
								)
				 );

if(!isset($mail_template[$subtab])) {
	$subtab = 'confirmation';
}

if($_POST) {
	if($_POST['submit_type'] == 'pages') {

		$registerpage = $_POST['registerpage'];
		$loginpage = $_POST['loginpage'];
		$profilepage = $_POST['profilepage'];
		$landingpage = $_POST['landingpage'];
		$detailpage = $_POST['detailpage'];

		update_option('ugc-register-pageid',$registerpage);
		update_option('ugc-login-pageid',$loginpage);
		update_option('ugc-profile-pageid',$profilepage);
		update_option('ugc-landing-pageid',$landingpage);
		update_option('ugc-detail-pageid',$detailpage);

		$post_result = array(
									'status' => 'success',
									'message' => 'Changes has been successfully saved, thank you !'
							   );

	} else if($_POST['submit_type'] == 'apikey') {

		$youtubekey = $_POST['youtubekey'];
		$recaptchakey = $_POST['recaptchakey'];
		$recaptchasecret = $_POST['recaptchasecret'];
		$facebookappid = $_POST['facebookappid'];
		$facebookappsecret = $_POST['facebookappsecret'];
		$facebookcallback = $_POST['facebookcallback'];
		$twitterkey = $_POST['twitterkey'];
		$twittersecret = $_POST['twittersecret'];
		$twittercallback = $_POST['twittercallback'];
		$mailchimpkey = $_POST['mailchimpkey'];
		$mailchimpurl = $_POST['mailchimpurl'];
		$mailchimplistid = $_POST['mailchimplistid'];

		update_option('ugc-youtube-key',$youtubekey);
		update_option('ugc-recaptcha-key',$recaptchakey);
		update_option('ugc-recaptcha-secret',$recaptchasecret);
		update_option('ugc-facebook-appid',$facebookappid);
		update_option('ugc-facebook-secret',$facebookappsecret);
		update_option('ugc-facebook-callback',$facebookcallback);
		update_option('ugc-twitter-key',$twitterkey);
		update_option('ugc-twitter-secret',$twittersecret);
		update_option('ugc-twitter-callback',$twittercallback);
		update_option('ugc-mailchimp-key',$mailchimpkey);
		update_option('ugc-mailchimp-endpoint',$mailchimpurl);
		update_option('ugc-mailchimp-listid',$mailchimplistid);

		$post_result = array(
									'status' => 'success',
									'message' => 'Changes has been successfully saved, thank you !'
							   );

	} else if($_POST['submit_type'] == 'apikey') {

		$youtubekey = $_POST['youtubekey'];
		$likevisible = isset($_POST['likevisible']) ? 1 : 0;
		$commentvisible = isset($_POST['commentvisible']) ? 1 : 0;
		$sharefacebookvisible = isset($_POST['sharefacebookvisible']) ? 1 : 0;
		$sharetwittervisible = isset($_POST['sharetwittervisible']) ? 1 : 0;
		$videovisible = isset($_POST['videovisible']) ? 1 : 0;
		$youtubevisible = isset($_POST['youtubevisible']) ? 1 : 0;
		$photovisible = isset($_POST['photovisible']) ? 1 : 0;
		$facebookvisible = isset($_POST['facebookvisible']) ? 1 : 0;
		$twittervisible = isset($_POST['twittervisible']) ? 1 : 0;

		update_option('ugc-display-limit-post',$mailchimplistid);
		update_option('ugc-enable-facebook-login',$facebookvisible);
		update_option('ugc-enable-twitter-login',$twittervisible);
		update_option('ugc-enable-user-like',$likevisible);
		update_option('ugc-enable-user-comment',$commentvisible);
		update_option('ugc-enable-facebook-share',$sharefacebookvisible);
		update_option('ugc-enable-twitter-share',$sharetwittervisible);
		update_option('ugc-enable-upload-video',$videovisible);
		update_option('ugc-enable-upload-youtube',$youtubevisible);
		update_option('ugc-enable-upload-photo',$photovisible);

		$post_result = array(
									'status' => 'success',
									'message' => 'Changes has been successfully saved, thank you !'
							   );
	} else if($_POST['submit_type'] == 'email') {
		$subject = $_POST['subject'];
		$sources = $_POST['sources'];
		$sub = $_POST['subtab'];

		if(!empty($subtab) and !empty($subject) and !empty($sources)) {
			if(is_writable($mail_template[$sub]['abs_path'])) {
				$sources = str_replace("\\", "", $sources);
				file_put_contents($mail_template[$sub]['abs_path'],$sources);
				update_option($mail_template[$sub]['option_name'],$subject);
				$post_result = array(
										'status' => 'success',
										'message' => 'Changes has been successfully saved, thank you !'
								   );
			} else {
				$post_result = array(
									'status' => 'error',
									'message' => 'Template file is not writable, changes can not be done'
							   );
			}
		} else {
			$post_result = array(
									'status' => 'error',
									'message' => 'Fail to save changes, Complete form bellow !'
							   );
		}
	}


}
?>

<div class="wrap">



	<h1>Settings</h1>


	<h2 class="nav-tab-wrapper">
		
		<a href="<?php echo admin_url('admin.php?page=ugc-settings&tab=general'); ?>" class="nav-tab <?php if($tab == 'general') echo 'nav-tab-active'; ?>">General</a>
		<a href="<?php echo admin_url('admin.php?page=ugc-settings&tab=pages'); ?>" class="nav-tab <?php if($tab == 'pages') echo 'nav-tab-active'; ?>">Pages</a>
		<a href="<?php echo admin_url('admin.php?page=ugc-settings&tab=apikey'); ?>" class="nav-tab <?php if($tab == 'apikey') echo 'nav-tab-active'; ?>">API Key</a>
		<a href="<?php echo admin_url('admin.php?page=ugc-settings&tab=email'); ?>" class="nav-tab <?php if($tab == 'email') echo 'nav-tab-active'; ?>">Email Template</a>
		<a href="<?php echo admin_url('admin.php?page=ugc-settings&tab=short-code'); ?>" class="nav-tab <?php if($tab == 'short-code') echo 'nav-tab-active'; ?>">Short Codes</a>
	</h2>


	<?php if(count($post_result) > 0 and isset($post_result['message'])) { ?>
	<div class="updated notice notice-warning is-dismissible">
		<p><?php echo $post_result['message']; ?></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text">Dismiss</span>
		</button>
	</div>
	<?php } ?>

	

	<?php if($tab == 'short-code') { ?>

	<table class="form-table">
		<tr>
			<td>
				<p>Use these short codes on your Pages. It will be automatically replaced into Plugin's predefine display</p>
			</td>
		</tr>
		
		<tr>
			<td>
				<h3>Embed Image Slider</h3>
				<div class="shortcode-container">
					[<b>slides</b> <b>post_type</b>="image" <b>display</b>=1 <b>title</b>="Title Text" <b>no_border</b>=1]<br>
					&nbsp;&nbsp;[slideitem src="http://path/to/image.jpg" link="http://url"]<br>
					&nbsp;&nbsp;[slideitem src="http://path/to/image.jpg" link="http://url"]<br>
					&nbsp;&nbsp;...<br>
					[<b>/slides</b>]
				</div>
				<p class="description">
					Short Code will be replaced into image slider, options available
					<br>
					<span class="code">SLIDES.post_type="image"</span> it will determine the type of slider item, use <b>image</b> for image slider<br>
					<span class="code">SLIDES.display=1</span> it will determine how many slides will be displayed on the same time, maximum display is 5. It wont affect mobile view which always display one post at the time<br>
					<span class="code">SLIDES.title="text title"</span> it is slide title/caption. If not empty, slide will have a header title, it will be displayed above the slider it self<br>
					<span class="code">SLIDES.no_border=1</span> it will hide separator border above the slider<br>
					<span class="code">SLIDEITEM.src="URL to image"</span> it is an image URL, you can use external link for the sources<br>
					<span class="code">SLIDEITEM.link="URL link"</span> it is an URL that will be use to direct user when they are clicking the item
				</p>
			</td>
		</tr>
		<tr>
			<td>
				<h3>Embed Post Slider</h3>
				<div class="shortcode-container">
					[<b>slides</b> <b>post_type</b>="article" <b>display</b>=1 <b>title</b>="Title Text" <b>no_border</b>=1]<br>
					&nbsp;&nbsp;[slideitem slug="post-slug-1"]<br>
					&nbsp;&nbsp;[slideitem slug="post-slug-2"]<br>
					&nbsp;&nbsp;...<br>
					[<b>/slides</b>]
				</div>
				<p class="description">
					Short Code will be replaced into post slider, options available
					<br>
					<span class="code">SLIDES.post_type="article"</span> it will determine the type of slider item, use <b>article</b> for post slider<br>
					<span class="code">SLIDES.display=1</span> it will determine how many slides will be displayed on the same time, maximum display is 5. It wont affect mobile view which always display one post at the time<br>
					<span class="code">SLIDES.title="text title"</span> it is slide title/caption. If not empty, slide will have a header title, it will be displayed above the slider it self<br>
					<span class="code">SLIDES.no_border=1</span> it will hide separator border above the slider<br>
					<span class="code">SLIDEITEM.slug="post-slug"</span> it is a post/article slug (what is slug ? <a href="https://www.wpbeginner.com/glossary/post-slug/" target="_blank">Find out here</a>)<br>
				</p>
			</td>
		</tr>
		<tr>
			<td>
				<h3>Submission Playlist</h3>
				<div class="shortcode-container">
					[<b>playlist</b> <b>post_type</b>='video,youtube,photo' <b>order_by</b>='popular' <b>display</b>=10 <b>title</b>="PLAYLIST" <b>playlist_name</b>="Top Videos"]
				</div>
				<p class="description">
					Short Code will be replaced into Submission playlist, options available
					<br>
					<span class="code">PLAYLIST.post_type="video,youtube,photo"</span> it will determine submission content type that will be displayed, you can use <b>video</b>,<b>photo</b>,<b>youtube</b>. Use comma separator to add them together<br>
					<span class="code">PLAYLIST.display=10</span> it will determine how many post will be displayed<br>
					<span class="code">PLAYLIST.title="text title"</span> it is playlist title/caption. If not empty, slide will have a header title, it will be displayed above the slider it self<br>
					<span class="code">PLAYLIST.playlist_name="text name"</span> it is inner playlist name, it will be displayed inside the playlist box (above the list)<br>
					<span class="code">PLAYLIST.order_by="popular"</span> it will determined how content is sorted, available options are <b>date</b> for recently approved, and <b>popular</b> based on like, view and comment<br>
				</p>
			</td>
		</tr>
		<tr>
			<td>
				<h3>Youtube Embed Playlist</h3>
				<div class="shortcode-container">
					[<b>playlist</b> <b>embed</b>='true'<b>title</b>="PLAYLIST" <b>playlist_name</b>="Top Videos" items="NDFTFFA0LtE|xA4aaSzqT9s|..."]
				</div>
				<p class="description">
					Short Code will be replaced into Youtube embed playlist, options available
					<br>
					<span class="code">PLAYLIST.post_type="video,youtube,photo"</span> it will determine submission content type that will be displayed, you can use <b>video</b>,<b>photo</b>,<b>youtube</b>. Use comma separator to add them together<br>
					<span class="code">PLAYLIST.embed=true</span> it will determine if playlist is youtube embedded (if yes, it is required)<br>
					<span class="code">PLAYLIST.title="text title"</span> it is playlist title/caption. If not empty, slide will have a header title, it will be displayed above the slider it self<br>
					<span class="code">PLAYLIST.playlist_name="text name"</span> it is inner playlist name, it will be displayed inside the playlist box (above the list)<br>
					<span class="code">PLAYLIST.items="YOUTUBEID"</span> it is youtube ID that will be embedded on playlist, use comma as seperator<br>
				</p>
			</td>
		</tr>

		<tr>
			<td>
				<h3>Highlight Post Slide Bar</h3>
				<div class="shortcode-container">
					[<b>popular</b> <b>post_type</b>='video,youtube,photo' <b>order_by</b>='popular' <b>display</b>=10 <b>title</b>="POPULAR POST"]
				</div>
				<p class="description">
					Short Code will be replaced into post slider, options available
					<br>
					<span class="code">POPULAR.post_type="video,youtube,photo"</span> it will determine submission content type that will be displayed, you can use <b>video</b>,<b>photo</b>,<b>youtube</b>. Use comma separator to add them together<br>
					<span class="code">POPULAR.order_by="popular"</span> it will determine how content is sorted, available options are <b>date</b> for recently approved, and <b>popular</b> based on like, view and comment<br>
					<span class="code">POPULAR.title="text title"</span> it is playlist title/caption. If not empty, slide will have a header title, it will be displayed above the slider it self<br>
					<span class="code">POPULAR.display=10</span>  it will determine how many post will be displayed<br>
				</p>
			</td>
		</tr>

		<tr>
			<td>
				<h3>Highlight Embed Slide Bar</h3>
				<div class="shortcode-container">
					[<b>popular</b> <b>post_type</b>='video,youtube,photo' <b>order_by</b>='popular' <b>title</b>="POPULAR POST" <b>slugs</b>="post-slug-1|post-slug-2|..."]
				</div>
				<p class="description">
					Short Code will be replaced into post slider, options available
					<br>
					<span class="code">POPULAR.post_type="video,youtube,photo"</span> it will determine submission content type that will be displayed, you can use <b>video</b>,<b>photo</b>,<b>youtube</b>. Use comma separator to add them together<br>
					<span class="code">POPULAR.order_by="popular"</span> it will determine how content is sorted, available options are <b>date</b> for recently approved, and <b>popular</b> based on like, view and comment<br>
					<span class="code">POPULAR.title="text title"</span> it is playlist title/caption. If not empty, slide will have a header title, it will be displayed above the slider it self<br>
					<span class="code">POPULAR.slugs="slug1|slug2"</span>  it is a post/article slug (what is slug ? <a href="https://www.wpbeginner.com/glossary/post-slug/" target="_blank">Find out here</a>), use "<b>|</b> as seperator<br>
				</p>
			</td>
		</tr>

		<tr>
			<td>
				<h3>HTML Embed</h3>
				<div class="shortcode-container">
					[<b>htmlembed</b> <b>title</b>="TITLE"]<br>
					&nbsp;&nbsp;YOUR HTML CONTENT HERE<br>
					[/<b>htmlembed</b>]
				</div>
				<p class="description">
					Short Code will be replaced into post slider, options available
					<br>
					<span class="code">HTMLEMBED.title="text title"</span> it is embed title/caption. If not empty, slide will have a header title, it will be displayed above the embed code it self<br>
				</p>
			</td>
		</tr>

		<tr>
			<td>
				<h3>Commenting/Disabled Short Code</h3>
				<div class="shortcode-container">
					&lt;!--[shortcode][/shortcode]--&gt;
				</div>
				<p class="description">
					To commenting or disabling shortcode, you can use HTML comment tag standard <span class="code">&lt;!-- --&gt;</span>
				</p>
			</td>
		</tr>
	</table>

	<?php } else if($tab == 'general') { ?>

	<form action="" method="post">
		
		<table class="form-table">
			<tr>
				<th scope="row">Pagination Limit per Page</th>
				<td>
					<input type="number" min='1' name="limitperpage" class="regular-text" value="<?php echo get_option('ugc-display-limit-post'); ?>">
					<p class="description">Number of content that will be displayed per page</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row">Like Visibility</th>
				<td>
					<input type="checkbox" id="likevisible" name="likevisible" class="regular-text" value="1" <?php if(get_option('ugc-enable-user-like') == 1) echo 'checked'; ?>>
					<label for="likevisible">Enable Like</label>
				</td>
			</tr>
			<tr>
				<th scope="row">Comment Visibility</th>
				<td>
					<input type="checkbox" id="commentvisible" name="commentvisible" class="regular-text" value="1" <?php if(get_option('ugc-enable-user-comment') == 1) echo 'checked'; ?>>
					<label for="commentvisible">Enable Comment</label>
				</td>
			</tr>
			<tr>
				<th scope="row">Share Visibility</th>
				<td>
					<input type="checkbox" id="sharefacebookvisible" name="sharefacebookvisible" class="regular-text" value="1"  <?php if(get_option('ugc-enable-facebook-share') == 1) echo 'checked'; ?>>
					<label for="sharefacebookvisible">Enable Share Facebook</label>
					<br>
					<input type="checkbox" id="sharetwittervisible" name="sharetwittervisible" class="regular-text" value="1"  <?php if(get_option('ugc-enable-twitter-share') == 1) echo 'checked'; ?>>
					<label for="sharetwittervisible">Enable Share Twitter</label>
					&nbsp;&nbsp;
				</td>
			</tr>

			<tr>
				<th scope="row">Upload Visibility</th>
				<td>
					<input type="checkbox" id="videovisible" name="videovisible" class="regular-text" value="1"  <?php if(get_option('ugc-enable-upload-video') == 1) echo 'checked'; ?>>
					<label for="videovisible">Enable Video</label>
					<br>
					<input type="checkbox" id="youtubevisible" name="youtubevisible" class="regular-text" value="1"  <?php if(get_option('ugc-enable-upload-youtube') == 1) echo 'checked'; ?>>
					<label for="youtubevisible">Enable Youtube</label>
					<br>
					<input type="checkbox" id="photovisible" name="photovisible" class="regular-text" value="1"  <?php if(get_option('ugc-enable-upload-photo') == 1) echo 'checked'; ?>>
					<label for="photovisible">Enable Photo</label>
				</td>
			</tr>
			

			<tr>
				<th scope="row">Social Login Visibility</th>
				<td>
					<input type="checkbox" id="facebookvisible" name="facebookvisible" class="regular-text" value="1"  <?php if(get_option('ugc-enable-facebook-login') == 1) echo 'checked'; ?>>
					<label for="facebookvisible">Enable Facebook Login</label>
					<br>
					<input type="checkbox" id="twittervisible" name="twittervisible" class="regular-text" value="1"  <?php if(get_option('ugc-enable-twitter-login') == 1) echo 'checked'; ?>>
					<label for="twittervisible">Enable Twitter Login</label>
				</td>
			</tr>
			
		</table>
		
		<p class="submit">
			<input type="hidden" name="submit_type" value="general">
			<button class="button button-primary" type="submit">Save Changes</button>
		</p>

	</form>

	<?php } else if($tab == 'apikey') { ?>

	<form action="" method="post">
		
		<table class="form-table">
			<tr>
				<th class="separator" colspan="2">Recaptcha &amp; Youtube</th>
			</tr>
			<tr>
				<th scope="row">Youtube API Key</th>
				<td>
					<input type="text" name="youtubekey" class="regular-text" value="<?php echo get_option('ugc-youtube-key'); ?>">
					<p class="description">For fetching Youtube Data (more info <a href="https://developers.google.com/youtube/v3/docs">https://developers.google.com/youtube/v3/docs</a>)</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Recaptcha API Key</th>
				<td>
					<input type="text" name="recaptchakey" class="regular-text" value="<?php echo get_option('ugc-recaptcha-key'); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">Recaptcha API Secret</th>
				<td>
					<input type="text" name="recaptchasecret" class="regular-text" value="<?php echo get_option('ugc-recaptcha-secret'); ?>">
					<p class="description">For validate google recaptcha (more info <a href="https://www.google.com/recaptcha">https://www.google.com/recaptcha</a>)</p>
				</td>
			</tr>
			<tr>
				<th class="separator" colspan="2">Facebook Oauth</th>
			</tr>
			<tr>
				<th scope="row">Facebook App ID</th>
				<td>
					<input type="text" name="facebookappid" class="regular-text" value="<?php echo get_option('ugc-facebook-appid'); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">Facebook App Secret</th>
				<td>
					<input type="text" name="facebookappsecret" class="regular-text" value="<?php echo get_option('ugc-facebook-secret'); ?>">
					<p class="description">For Facebook Aunthenticator login and sharer (for more info  <a href="https://developer.facebook.com">https://developer.facebook.com</a>)</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Facebook Oauth Callback</th>
				<td>
					<input type="text" name="facebookcallback" class="regular-text" value="<?php echo get_option('ugc-facebook-callback'); ?>">
					<p class="description">Make sure this callback URL is registered on your Facebook APP</p>
				</td>
			</tr>
			<tr>
				<th class="separator" colspan="2">Twitter Oauth</th>
			</tr>
			<tr>
				<th scope="row">Twitter Client Key</th>
				<td>
					<input type="text" name="twitterkey" class="regular-text" value="<?php echo get_option('ugc-twitter-key'); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">Twitter Client Secret</th>
				<td>
					<input type="text" name="twittersecret" class="regular-text" value="<?php echo get_option('ugc-twitter-secret'); ?>">
					<p class="description">For Twitter Aunthenticator login (for more info  <a href="https://apps.twitter.com">https://apps.twitter.com</a>)</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Twitter Oauth Callback</th>
				<td>
					<input type="text" name="twittercallback" class="regular-text" value="<?php echo get_option('ugc-twitter-callback'); ?>">
					<p class="description">Make sure this callback URL is registered on your Twitter APP</p>
				</td>
			</tr>
			<tr>
				<th class="separator" colspan="2">Mailchimp</th>
			</tr>
			<tr>
				<th scope="row">Mailchimp Api Key</th>
				<td>
					<input type="text" name="mailchimpkey" class="regular-text" value="<?php echo get_option('ugc-mailchimp-key'); ?>">
					<p class="description">Refere to this document   <a href="https://mailchimp.com/help/about-api-keys/">https://mailchimp.com/help/about-api-keys/</a> to get your key</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row">Mailchimp API endpoint URL</th>
				<td>
					<input type="text" name="mailchimpurl" class="regular-text" value="<?php echo get_option('ugc-mailchimp-endpoint'); ?>">
					<p class="description">Make sure this endpoint URL is refered to the same data center / server as key is</p>
				</td>
			</tr>
			<tr>
				<th scope="row">Mailchimp List Name</th>
				<td>
					<select name="mailchimplistid">
						<?php if(count($mailchimp_lists) == 0) { ?>
						<option value="">- Not Set -</option>
						<?php 
					    } else {
					    	foreach ($mailchimp_lists as $key => $value) {
					    		$selected = ($value['id'] == get_option('ugc-mailchimp-listid')) ? 'selected' : '';
					    		echo '<option value="'.$value['id'].'" '.$selected.'>'.$value['name'].' ('.$value['count'].' Subscriber)</option>';
					    	}
					    }
					    ?>
					</select>
					<p class="description">The name of subscriber list that will be syncronized. Make sure you have already set key and endpoint URL (then save changes and refresh) and you have already made a list on your Mailchimp Account</p>
				</td>
			</tr>
			
			
		</table>
		
		<p class="submit">
			<input type="hidden" name="submit_type" value="apikey">
			<button class="button button-primary" type="submit">Save Changes</button>
		</p>

	</form>

	<?php } else if($tab == 'pages') { ?>

	<form action="" method="post">
		
		<table class="form-table">
			<tr>
				<th scope="row">Login Page</th>
				<td>
					<select name="loginpage" class="select-long">
						<option>- Select Page -</option>
						<?php foreach ($wp_pages as $key => $value) { ?>
						<option value="<?php echo $value->ID; ?>" <?php if($value->ID == get_option('ugc-login-pageid')) echo 'selected'; ?>><?php echo $value->post_title; ?></option>
						<?php } ?>
					</select>
					
				</td>
			</tr>
			<tr>
				<th scope="row">Register Page</th>
				<td>
					<select name="registerpage" class="select-long">
						<option>- Select Page -</option>
						<?php foreach ($wp_pages as $key => $value) { ?>
						<option value="<?php echo $value->ID; ?>" <?php if($value->ID == get_option('ugc-register-pageid')) echo 'selected'; ?>><?php echo $value->post_title; ?></option>
						<?php } ?>
					</select>
					
				</td>
			</tr>

			<tr>
				<th scope="row">Profile Page</th>
				<td>
					<select name="profilepage" class="select-long">
						<option>- Select Page -</option>
						<?php foreach ($wp_pages as $key => $value) { ?>
						<option value="<?php echo $value->ID; ?>" <?php if($value->ID == get_option('ugc-profile-pageid')) echo 'selected'; ?>><?php echo $value->post_title; ?></option>
						<?php } ?>
					</select>
					
				</td>
			</tr>
			<tr>
				<th scope="row">Detail Page</th>
				<td>
					<select name="detailpage" class="select-long">
						<option>- Select Page -</option>
						<?php foreach ($wp_pages as $key => $value) { ?>
						<option value="<?php echo $value->ID; ?>" <?php if($value->ID == get_option('ugc-detail-pageid')) echo 'selected'; ?>><?php echo $value->post_title; ?></option>
						<?php } ?>
					</select>
					
				</td>
			</tr>

			<tr>
				<th scope="row">Landing Page</th>
				<td>
					<select name="landingpage" class="select-long">
						<option>- Select Page -</option>
						<?php foreach ($wp_pages as $key => $value) { ?>
						<option value="<?php echo $value->ID; ?>" <?php if($value->ID == get_option('ugc-landing-pageid')) echo 'selected'; ?>><?php echo $value->post_title; ?></option>
						<?php } ?>
					</select>
					
				</td>
			</tr>
			
		</table>
		
		<p class="submit">
			<input type="hidden" name="submit_type" value="pages">
			<button class="button button-primary" type="submit">Save Changes</button>
		</p>

	</form>

<?php } else if($tab == 'email') { ?>

		

	<form action="" method="post">
		<table class="form-table">
			
			<tr>
				<td>
					<h3 class="nav-tab-wrapper">
						<a href="<?php echo admin_url('admin.php?page=ugc-settings&tab=email&subtab=confirmation'); ?>" class="nav-tab <?php if($subtab == 'confirmation') echo 'nav-tab-active'; ?>">Activation Mail Template</a>
						<a href="<?php echo admin_url('admin.php?page=ugc-settings&tab=email&subtab=forgot'); ?>" class="nav-tab <?php if($subtab == 'forgot') echo 'nav-tab-active'; ?>">Forgot Password Mail Template</a>
					</h3>

					<div class="mail-editor-box">
						<div class="input-area">
							<input type="text" name="subject" value="<?php echo get_option($mail_template[$subtab]['option_name']); ?>" placeholder="Mail Subject">
							<textarea name="sources" placeholder="Template HTML Code"><?php echo url_get_contents($mail_template[$subtab]['file_path']); ?></textarea>
						</div>
						<div class="iframe-area">
							<iframe src="<?php echo $mail_template[$subtab]['file_path']; ?>"></iframe>
						</div>
					</div>
					
					<input type="hidden" name="subtab" value="<?php echo $subtab; ?>">
				</td>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="submit_type" value="email">
					<button class="button button-primary" type="submit">Save Changes</button>
					
				</td>
			</tr>
		</table>
		<br>
	</form>

	<?php } ?>




</div>