<?php
get_header(); 
$post = get_post( get_the_ID() ); 
$login = is_user_logged_in();
$action = (isset($_GET['action'])) ? $_GET['action'] : NULL;
$token = (isset($_GET['token'])) ? $_GET['token'] : NULL;
$error = (isset($_GET['err'])) ? TRUE : NULL;
$type = (isset($_GET['type'])) ? $_GET['type'] : NULL;



$register_url = get_permalink(get_option('ugc-register-pageid'));
$login_url = get_permalink(get_option('ugc-login-pageid'));


if($token != NULL and $action == 'recovery') {
	$users = get_users(array(
	    'meta_key' => 'ugc-forgot-ticket',
	    'meta_value' => $token
	));


	if(!isset($users) or count($users) == 0) {
		echo "<script>location.href='".$login_url."';</script>";
	}
}
if(!$login):
?>

<div class="wrap">
	<div class="ugc-wrap">

		<?php if($action == NULL): ?>
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
				<?php 
				if(isset($error)) {
					$msg = 'Unknown error occured';
					if(isset($type) and $type == 'unconfirm') {
						$msg = "You havent activated your Account yet, check E-mail that we sent earlier to activate your account";
					}
				?>
				<div class="ugc-error-message" style="display: flex;">
					<i class="fa fa-exclamation-circle"></i>
					<div class="text">
						<div class="heading">Ops!</div>
						<div class="message"><?php echo $msg; ?></div>
					</div>
				</div>
				<?php } ?>
				<form action="" method="POST" class="ajax-form-login">
					<input type="hidden" name="action" value="ugc_login">
					

					<div class="ugc-field">
						<div class="ugc-input">
							<input type="text" name="ugc-email" placeholder="E-Mail" autocomplete="off" >
							
						</div>
					</div>
					
					
					<div class="ugc-field">
						<div class="ugc-input">
							<input type="password" name="ugc-password" placeholder="Password" autocomplete="off">
						</div>
					</div>

					<div class="ugc-field">
						<div class="ugc-input left-middle">
							<input type="checkbox" name="ugc-remember" value="1"> <span>Remember Me</span>
						</div>
					</div>

					
					
					<div class="ugc-field">
						<button type="submit" class="ugc-submit">Sign In</button>
						<button type="button" class="ugc-loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
					</div>
					
					<div class="ugc-field">
						
						<div class="ugc-label center">
							Haven't made an account ? <a href="<?php echo $register_url; ?>">Register here</a><br>or forgot your password ? <a href="?action=forgot">Click here</a>
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
						<button type="button" class="socmed twitter"><i class="fa fa-twitter"></i>&nbsp;&nbsp;Sign In via Twitter</button>
					</a>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		
		<?php elseif($action == 'forgot'): ?>

		<div class="ugc-register-box">
			<h1>Forgot Password ?</h1>
			<p class="ugc-small">Dont worry, please enter your E-mail address so we can verify your account and send you a recovery mail</p>
			
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
					<input type="hidden" name="action" value="ugc_reset_password">

					<div class="ugc-field">
						<div class="ugc-input">
							<input type="text" name="ugc-email" placeholder="E-Mail" autocomplete="off">
							
						</div>
						<span class="alert"></span>
					</div>
					
					

					<div class="ugc-field">
						<div class="ugc-recaptcha" id="recaptcha-register"></div>
					</div>
					
					
					<div class="ugc-field">
						<button type="submit" class="ugc-submit">Send Email Recovery</button>
						<button type="button" class="ugc-loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
					</div>
					
					
					
				</form>
			</div>

		</div>

		<?php elseif($action == 'recovery' and !empty($token)): ?>

		<div class="ugc-register-box display-none" id="successactivation">
			<button class="ugc-success-circle"><i class="fa fa-check"></i></button>
			<h1 style="padding-top:1em">You're All Set</h1>
			<p>Your password has been updated, please login to continue</p>
		
			<div class="ugc-form-container">
				<div class="ugc-field">
					<a href="<?php echo $login_url; ?>">
						<button type="button" class="ugc-submit">Sign In</button>
					</a>
				</div>
					
			</div>
		</div>

		<div class="ugc-register-box" id="activationsection">
			<h1 style="padding-top:1em">Set Your New Password</h1>
			<p>Enter your new password</p>
		
			<div class="ugc-form-container">
				<div class="ugc-error-message" id="ugc-error-message">
					<i class="fa fa-exclamation-circle"></i>
					<div class="text">
						<div class="heading">Ops!</div>
						<div class="message">Something went funny !</div>
					</div>
				</div>
				<form action="" method="POST" class="ajax-form-recovery">
					<input type="hidden" name="action" value="ugc_save_password">
					<input type="hidden" name="ugc-token" value="<?php echo $token; ?>">
					
					<div class="ugc-field">
						<div class="ugc-input">
							<input type="password" name="ugc-password" placeholder="New Password" autocomplete="off" id="checkpassword">
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
						<div class="ugc-recaptcha" id="recaptcha-register"></div>
					</div>
					
					
					<div class="ugc-field">
						<button type="submit" class="ugc-submit">Sign Up</button>
						<button type="button" class="ugc-loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
					</div>
					
					
					
				</form>
					
			</div>
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
