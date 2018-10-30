<?php
get_header(); 
$post = get_post( get_the_ID() ); 
$login = is_user_logged_in();

$token = (isset($_GET['token'])) ? $_GET['token'] : NULL;
$callback = (isset($_GET['ref']) and $_GET['ref'] == 'callback') ? TRUE : FALSE;
$verify_token = (isset($_GET['verify_token'])) ? $_GET['verify_token'] : FALSE;
$activation_key = (isset($_GET['activation_key'])) ? $_GET['activation_key'] : FALSE;

$user = NULL;
$callback_type = NULL;

$login_url = get_permalink(get_option('ugc-login-pageid'));

if(isset($_SESSION['callback_info'])) {
	$info = $_SESSION['callback_info'];
	if($info['ticket'] == $verify_token) {
		$callback_type = $info['callback_type'];
		$user = $info['user_info'];
	}
}

if($activation_key != FALSE) {
	$users = get_users(array(
	    'meta_key' => 'ugc-activation-ticket',
	    'meta_value' => $activation_key
	));

	if(!isset($users) or count($users) == 0) {
		echo "<script>location.href='".$login_url."';</script>";
	}
}


if(!$login):
?>

<div class="wrap">
	<div class="ugc-wrap">
		<?php if($_SESSION['succes_token'] == $token and $token != NULL and $callback == FALSE and $activation_key == FALSE): ?>
		
		<div class="ugc-register-box">
			<button class="ugc-success-circle"><i class="fa fa-check"></i></button>
			<h1 style="padding-top:1em">Yeay, Almost There</h1>
			<p>We have sent you an activation link to your E-Mail. Please activate your account before sign in</p>
			<p class="ugc-small">Already activate your account ? <a href="<?php echo $login_url; ?>">sign in here</a><br><hr>Haven't received the email yet ? </p>
		
			<div class="ugc-form-container">
				<div class="ugc-error-message" id="ugc-error-message">
					<i class="fa fa-exclamation-circle"></i>
					<div class="text">
						<div class="heading">Ops!</div>
						<div class="message">Something went funny !</div>
					</div>
				</div>
				<div class="ugc-error-message success" id="ugc-success-message">
					<i class="fa fa-check-circle"></i>
					<div class="text">
						<div class="heading">Yeay!</div>
						<div class="message">It went funky !</div>
					</div>
				</div>
				
				<form action="" method="POST" class="ajax-form-resend">
					<input type="hidden" name="action" value="ugc_resend_activation">
					<input type="hidden" name="ugc-token" value="<?php echo $token; ?>">

					<div class="ugc-field">
						<div class="ugc-input">
							<input type="text" name="ugc-email" placeholder="E-Mail" autocomplete="off" value="<?php echo (isset($_SESSION['succes_email']) ? $_SESSION['succes_email'] : ''); ?>">
							
						</div>
						<span class="alert"></span>

					</div>

					<div class="ugc-field">
						<div class="ugc-recaptcha" id="recaptcha-register"></div>
					</div>
					
					<div class="ugc-field">
						<button type="submit" class="ugc-submit">Resend Email</button>
						<button type="button" class="ugc-loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
					</div>
					
				</form>
			</div>

		</div>

		<?php elseif($callback == TRUE and count($user) > 0 and isset($callback_type) and $activation_key == FALSE): ?>

		<div class="ugc-register-box">
			<h1><?php echo $post->post_title; ?></h1>
			<p><?php echo $post->post_content; ?></p>

			<div class="ugc-form-container">
				<div class="ugc-error-message" id="ugc-error-message">
					<i class="fa fa-exclamation-circle"></i>
					<div class="text">
						<div class="heading">Ops!</div>
						<div class="message">Something went funny !</div>
					</div>
				</div>

				<?php if($callback_type == 'twitter'): ?>
				<div class="ugc-socmed-box twitter">
					<div class="avatar-area">
						<img src="<?php echo $user['avatar']; ?>">
					</div>
					<div class="text">
						login with twitter<br>
						<span class="big">@<?php echo $user['screenname']; ?></span>
					</div>
					<i class="fa fa-twitter"></i>
				</div>
				<?php else: ?>

				<div class="ugc-socmed-box facebook">
					<div class="avatar-area">
						<img src="<?php echo $user['avatar']; ?>">
					</div>
					<div class="text">
						login with facebook<br>
						<span class="big"><?php echo $user['screenname']; ?></span>
					</div>
					<i class="fa fa-facebook"></i>
				</div>

				<?php endif; ?>


				<form action="" method="POST" class="ajax-form-register">
					<input type="hidden" name="action" value="ugc_saveregister_socmed">
					<div class="ugc-field">
						<div class="ugc-input">
							<input type="text" name="ugc-fname" placeholder="First Name" class="half" value="<?php echo $user['first_name']; ?>">
							<input type="text" name="ugc-lname" placeholder="Last Name" class="half" value="<?php echo $user['last_name']; ?>">
						</div>
					</div>

					<div class="ugc-field">
						<div class="ugc-input">
							<input type="text" name="ugc-email" placeholder="E-Mail" autocomplete="off" id="checkemail"  value="<?php echo $user['email']; ?>">
							<button type="button" class="loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
						</div>
						<span class="alert"></span>
						<span class="help"><br>Please use an active and valid E-mail Address, we will send you an activation mail to you for taking further action</span>
					</div>

					<div class="ugc-field">
						<div class="ugc-input left-middle">
							<input type="checkbox" name="ugc-optin" value="1"> <span>I agree to receive email notification and promo</span>
							
						</div>
					</div>

					<div class="ugc-field">
						<div class="ugc-recaptcha" id="recaptcha-register"></div>
					</div>
					
					
					<div class="ugc-field">
						<button type="submit" class="ugc-submit">Sign Up</button>
						<button type="button" class="ugc-loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
					</div>
					
					<div class="ugc-field">
						
						<div class="ugc-label">
							Already have an account ? <a href="">Login here</a>
						</div>
					</div>
					
				</form>
			</div>
			
		</div>

		<?php elseif($activation_key != FALSE and !empty($activation_key)): ?>

		<div class="ugc-register-box display-none" id="successactivation">
			<button class="ugc-success-circle"><i class="fa fa-check"></i></button>
			<h1 style="padding-top:1em">You're All Set</h1>
			<p>Your Account has been activated, please login to continue</p>
		
			<div class="ugc-form-container">
				<div class="ugc-field">
					<a href="<?php echo $login_url; ?>">
						<button type="button" class="ugc-submit">Sign In</button>
					</a>
				</div>
					
			</div>
		</div>

		<div class="ugc-register-box" id="activationsection">
			<h1 style="padding-top:1em">Confirm You're Human</h1>
			<p>Since you've made it this far, we want to assume you're a real, live human. But we need to be super sure you aren't a robot.</p>
		
			<div class="ugc-form-container">
				<div class="ugc-error-message" id="ugc-error-message">
					<i class="fa fa-exclamation-circle"></i>
					<div class="text">
						<div class="heading">Ops!</div>
						<div class="message">Something went funny !</div>
					</div>
				</div>
				<div class="ugc-field">
					<button type="button" class="ugc-submit" id="activate-button" data-key="<?php echo $activation_key; ?>">I am not a Robot</button>
				</div>
					
			</div>
		</div>

		<?php else: ?>

		<div class="ugc-register-box">
			<h1><?php echo $post->post_title; ?></h1>
			<p><?php echo $post->post_content; ?></p>

			<div class="ugc-form-container">
				<div class="ugc-error-message" id="ugc-error-message">
					<i class="fa fa-exclamation-circle"></i>
					<div class="text">
						<div class="heading">Ops!</div>
						<div class="message">Something went funny !</div>
					</div>
				</div>
				<form action="" method="POST" class="ajax-form-register">
					<input type="hidden" name="action" value="ugc_saveregister">
					<div class="ugc-field">
						<div class="ugc-input">
							<input type="text" name="ugc-fname" placeholder="First Name" class="half">
							<input type="text" name="ugc-lname" placeholder="Last Name" class="half">
						</div>
					</div>

					<div class="ugc-field">
						<div class="ugc-input">
							<input type="text" name="ugc-email" placeholder="E-Mail" autocomplete="off" id="checkemail">
							<button type="button" class="loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
						</div>
						<span class="alert"></span>
						<span class="help"><br>Please use an active and valid E-mail Address, we will send you an activation mail to you for taking further action</span>
					</div>
					
					<div class="ugc-field">
						<div class="ugc-input">
							<input type="password" name="ugc-password" placeholder="Password" autocomplete="off" id="checkpassword">
							<div class="password-meter" id="passwordmeter">
								<div class="bar">
									<div class="meter"></div>
								</div>
								<div class="caption">Your password is <b></b></div>
							</div>
						</div>
					</div>
					<div class="ugc-field">
						<div class="ugc-input">
							<input type="password" name="ugc-repassword" placeholder="Confirm Password" autocomplete="off" id="checkmatchpassword" data-rel="#checkpassword">
							<button type="button" class="loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
						</div>
						<span class="alert"></span>
					</div>

					<div class="ugc-field">
						<div class="ugc-input left-middle">
							<input type="checkbox" name="ugc-optin" value="1"> <span>I agree to receive email notification and promo</span>
							
						</div>
					</div>

					<div class="ugc-field">
						<div class="ugc-recaptcha" id="recaptcha-register"></div>
					</div>
					
					
					<div class="ugc-field">
						<button type="submit" class="ugc-submit">Sign Up</button>
						<button type="button" class="ugc-loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
					</div>
					
					<div class="ugc-field">
						
						<div class="ugc-label">
							Already have an account ? <a href="<?php echo $login_url; ?>">Sign in here</a>
						</div>
					</div>
					
				</form>
			</div>
			<?php if(get_option('ugc-enable-twitter-login') == 1 or get_option('ugc-enable-facebook-login') == 1): ?>
			<h2 class="ugc-line"><span>OR</span></h2>

			<div class="ugc-form-container">
				<?php if(get_option('ugc-enable-facebook-login') == 1): ?>
				<div class="ugc-field">
					<a href="<?php echo site_url().'/wp-admin/admin-ajax.php?action=ugc_redirect_facebook'; ?>">
						<button type="button" class="socmed facebook"><i class="fa fa-facebook"></i>&nbsp;&nbsp;Sign Up via Facebook</button>
					</a>
				</div>
				<?php endif; ?>
				<?php if(get_option('ugc-enable-twitter-login') == 1): ?>
				<div class="ugc-field">
					<a href="<?php echo site_url().'/wp-admin/admin-ajax.php?action=ugc_redirect_twitter'; ?>">
						<button type="button" class="socmed twitter"><i class="fa fa-twitter"></i>&nbsp;&nbsp;Sign Up via Twitter</button>
					</a>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>

		<?php endif; ?>
	</div>
</div><!-- .wrap -->

<?php else: ?>

<div class="wrap">
	<h1>Hi, Amri Hidayatulloh</h1>
	<p>You have already logged in</p>

</div>

<?php endif; ?>

<?php get_footer();
