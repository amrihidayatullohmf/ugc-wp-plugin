<?php
class Ugc_OauthHandler {
	public $WPDB;
	public $TWITTER;
	public $FACEBOOK;

	function __construct($wpdb) {
		$this->WPDB = $wpdb;
	}

	public function set_twitter_object($obj) {
		$this->TWITTER = $obj;
	}

	public function get_twitter_login_url() {
		$request_token = $this->TWITTER->getRequestToken(get_option('ugc-twitter-callback'));

		$_SESSION['oauth_token'] = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
		
		if($this->TWITTER->http_code==200){
		    $url = $this->TWITTER->getAuthorizeURL($request_token['oauth_token'],'itscool');
			return $url;
		} else {
		    return '';
		}
	}

	public function redirect_twitter() {
		$url = $this->get_twitter_login_url();
		echo "<script>location.href='".$url."';</script>";
		wp_die();
	}

	public function callback_twitter() {
		$_oauth_token = $_SESSION['oauth_token'];
		$_oauth_token_secret = $_SESSION['oauth_token_secret'];

		$register_url = get_permalink(get_option('ugc-register-pageid'));
		$login_url = get_permalink(get_option('ugc-login-pageid'));
		$profile_url = get_permalink(get_option('ugc-profile-pageid'));

		if(!empty($_GET['oauth_verifier']) && !empty($_oauth_token) && !empty($_oauth_token_secret)) {
			$this->TWITTER->create(get_option('ugc-twitter-key'), get_option('ugc-twitter-secret'),$_oauth_token,$_oauth_token_secret);
			$access_token = $this->TWITTER->getAccessToken($_GET['oauth_verifier']);
			$user_info = $this->TWITTER->get('account/verify_credentials');

			if(isset($access_token['oauth_token_secret'])) {
				$user = $this->WPDB->get_results("SELECT * FROM ".$this->WPDB->prefix."usermeta WHERE meta_key = 'ugc-twitter-accesstoken' AND meta_value = '".$access_token['oauth_token_secret']."'");

				if(isset($user[0]->user_id)) {
					$user = get_user_by('id',$user[0]->user_id);
					if(isset($user->data->ID)) {
						$creds = array(
									'user_login'    => $user->data->user_login,
							        'user_password' => $access_token['oauth_token_secret'],
							        'remember'      => FALSE
								 );

						$signon = wp_signon( $creds, is_ssl() );

		    			if ( is_wp_error( $signon ) ) {
		    				echo "<script>location.href='".$register_url."?error=true';</script>";
		    			} else {
		    				$activate = get_user_meta($signon->data->ID,'ugc-account-activated');

							if(isset($activate[0]) and $activate[0] == 0) {
								wp_logout();
								echo "<script>location.href='".$login_url."?err=true&type=unconfirm';</script>";
							}

		    				echo "<script>location.href='".$profile_url."';</script>";
		    			}
					}
				}

				$token = md5($access_token['oauth_token'].date('U').$_GET['oauth_verifier']);

				$fullname = ($user_info->name) ? $user_info->name : '';
				$fullname = explode(" ", $fullname);
				$fname = '';
				$lname = '';

				if(count($fullname) > 0) {
					$fname = $fullname[0];
					$lname = implode(" ", array_slice($fullname, 1,count($fullname)));
				}

				unset($_SESSION['oauth_token']);
				unset($_SESSION['oauth_token_secret']);

				$_SESSION['callback_info'] = array(
												'ticket' => $token,
												'access_token' => $access_token,
												'callback_type' => 'twitter',
												'user_info' => array(
																	'email' => isset($user_info->screen_name) ? strtolower($user_info->screen_name."@twitter.com") : 'no-mail@twitter.com',
																	'first_name' => $fname,
																	'last_name' => $lname,
																	'screenname' => isset($user_info->screen_name) ? $user_info->screen_name : '',
																	'avatar' => isset($user_info->profile_image_url_https) ? $user_info->profile_image_url_https : '',
																	'uid' => isset($user_info->id) ? $user_info->id : 0
																)
											  );

				echo "<script>location.href='".$register_url."?ref=callback&verify_token=".$token."';</script>";
			}
		}

		wp_die();
	}

	public function set_facebook_object($obj) {
		$this->FACEBOOK = $obj;
	}

	public function redirect_facebook() {
		$loginurl = $this->FACEBOOK->getLoginUrl(array(
						'scope' => 'public_profile,email',
						'redirect_uri' => get_option('ugc-facebook-callback')
					));
		echo "<script>location.href='".$loginurl."';</script>";
		wp_die();
	}

	public function callback_facebook() {
		$register_url = get_permalink(get_option('ugc-register-pageid'));
		$profile_url = get_permalink(get_option('ugc-profile-pageid'));
		$login_url = get_permalink(get_option('ugc-login-pageid'));

		try {
		 	$fbuser = $this->FACEBOOK->api('/me?fields=id,name,email,gender');
    	} catch (FacebookApiException $e) {
			echo "<script>location.href='".$register_url."?error=true';</script>";
			wp_die();
		}

		$access_token = $this->FACEBOOK->getAccessToken();

		if(isset($fbuser['id'])) {
			$user = $this->WPDB->get_results("SELECT * FROM ".$this->WPDB->prefix."usermeta WHERE meta_key = 'ugc-facebook-uid' AND meta_value = '".$fbuser['id']."'");

			if(isset($user[0]->user_id)) {
				$user = get_user_by('id',$user[0]->user_id);
				if(isset($user->data->ID)) {
					$creds = array(
								'user_login'    => $user->data->user_login,
						        'user_password' => sha1($fbuser['id']),
						        'remember'      => FALSE
							 );

					$signon = wp_signon( $creds, is_ssl() );
		 
		    		if ( is_wp_error( $signon ) ) {
		    			echo "<script>location.href='".$register_url."?error=true';</script>";
		    		} else {
		    			$activate = get_user_meta($signon->data->ID,'ugc-account-activated');

						if(isset($activate[0]) and $activate[0] == 0) {
							wp_logout();
							echo "<script>location.href='".$login_url."?err=true&type=unconfirm';</script>";
						}

		    			echo "<script>location.href='".$profile_url."';</script>";
		    		}
				}
			} 

			$token = md5($fbuser['id'].date('U').$access_token);

			$fullname = (isset($fbuser['name'])) ? $fbuser['name'] : '';
			$fullname = explode(" ", $fullname);
			$fname = '';
			$lname = '';

			if(count($fullname) > 0) {
				$fname = $fullname[0];
				$lname = implode(" ", array_slice($fullname, 1,count($fullname)));
			}

			
			$_SESSION['callback_info'] = array(
											'ticket' => $token,
											'access_token' => array('token_id'=>$fbuser['id'],'token_secret'=>$access_token),
											'callback_type' => 'facebook',
											'user_info' => array(
																'email' => isset($fbuser['email']) ? strtolower($fbuser['email']) : 'no-mail@facebook.com',
																'first_name' => $fname,
																'last_name' => $lname,
																'screenname' => isset($fbuser['name']) ? $fbuser['name'] : '',
																'avatar' => isset($fbuser['id']) ? 'http://graph.facebook.com/'.$fbuser['id'].'/picture?type=square' : '',
																'uid' => isset($fbuser['id']) ? $fbuser['id'] : 0
															)
											  );

			echo "<script>location.href='".$register_url."?ref=callback&verify_token=".$token."';</script>";
		} else {
			echo "<script>location.href='".$register_url."?error=true';</script>";
		}

		wp_die();
	}


}