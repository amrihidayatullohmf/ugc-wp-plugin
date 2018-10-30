<?php
class Ugc_AjaxHandler {

	private $WPDB;
	private $MAIL;

	function __construct($wpdb,$mailer) {
		$this->WPDB = $wpdb;
		$this->MAIL = $mailer;
	}


	private function mailchimp_push($uid) {
		$user = get_user_by('id',$uid);

    	if(isset($user->data->ID)) {
			$apikey = get_option('ugc-mailchimp-key');
	        $auth = base64_encode( 'user:'.$apikey );

	       	$optin = get_user_meta($uid,'ugc-opt-in-promo');
	        $fname = get_user_meta($uid,'first_name');
	        $lname = get_user_meta($uid,'last_name');

	        $data = array(
	                    'apikey'        => $apikey,
	                    'email_address' => $user->data->user_email,
	                    'status'        => (isset($optin[0]) and $optin[0] == 1) ? 'subscribed' : 'unsubscribed',
	                    'merge_fields'  => array(
	                        'FNAME' => (isset($fname[0]) ? $fname[0] : ''),
	                        'LNAME' => (isset($lname[0]) ? $lname[0] : ''),
	                    )
	                );


	        $json_data = json_encode($data);

	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apikey);
	        curl_setopt($ch, CURLOPT_URL, get_option('ugc-mailchimp-endpoint').'lists/'.get_option('ugc-mailchimp-listid').'/members/');
	        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);                                                                                                         

	        $result = curl_exec($ch);

	        return $result;
    	}

    	return FALSE;
	}

	public function mailchimp_get_list() {
		
		$apikey = get_option('ugc-mailchimp-key');
	    $auth = base64_encode( 'user:'.$apikey );

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apikey);
	    curl_setopt($ch, CURLOPT_URL, get_option('ugc-mailchimp-endpoint').'lists');
	    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    
	    $result = curl_exec($ch);

	    var_dump($result);

	    wp_die();

	    //
	}

	public function validate_recaptcha() {
    	$recaptcha = $_POST['g-recaptcha-response'];
		$captcha_valid = FALSE;

		if(!empty($recaptcha)) {
			$secret = get_option('ugc-recaptcha-secret');
	        //$responseData = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$recaptcha);
	        
			$ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$recaptcha);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    $responseData = curl_exec($ch);

	        $responseData = json_decode($responseData);


	        if(isset($responseData->success) and $responseData->success) {
	        	$captcha_valid = TRUE;
	        }
		}

		return $captcha_valid;
    }

    private function send_email_activation($uid) {
    	$user = get_user_by('id',$uid);

    	if(isset($user->data->ID)) {
    		$name = ucwords($user->data->display_name);
    		$activation_key = sha1(md5($uid).date('U'));
    		$register_url = get_permalink(get_option('ugc-register-pageid'));
    		$link = $register_url.'?activation_key='.$activation_key;

    		update_user_meta($uid,'ugc-activation-ticket',$activation_key);
    		
    		$this->MAIL->set_subject(get_option('ugc-activation-subject'));
    		$this->MAIL->set_to($user->data->user_email);
    		$this->MAIL->set_template(UGC_PLUGIN_DIR.'views/email-activation.html');
    		$this->MAIL->set_content_pair(array(
    										'NAME' => $name,
    										'LINK' => $link
    									  ));
    		return $this->MAIL->send();
    	}

    	return FALSE;
    }

    private function register_user($fname,$lname,$password,$repassword,$email,$optin = 0) {
    	$return = array('code'=>500,'msg'=>'Complete registration form correctly !');

    	if(!empty($fname) and !empty($password) and !empty($repassword) and !empty($email)) {
	        if($password == $repassword) {
		       	$this->WPDB->get_results("SELECT * FROM ".$this->WPDB->prefix."users WHERE user_email = '".$email."'");
				if($this->WPDB->num_rows == 0) {
					$fname = esc_sql($fname);
					$lname = esc_sql($lname);
					$password = esc_sql($password);
					$email = esc_sql($email);
					$username = esc_sql($username);
					$bio = "";

					$activation_key = sha1(md5($email.$password.$fname.$lname).date('U'));

					$new_user_id = wp_insert_user(array(
										'user_login'		=> $email,
										'user_pass'	 		=> $password,
										'user_email'		=> $email,
										'first_name'		=> $fname,
										'last_name'			=> $lname,
										'user_registered'	=> date('Y-m-d H:i:s'),
										'role'				=> 'subscriber',
										'show_admin_bar_front' => false
									));

					if($new_user_id) {
						add_user_meta($new_user_id, 'ugc-activation-ticket', '');
						add_user_meta($new_user_id, 'ugc-forgot-ticket', '');
						add_user_meta($new_user_id, 'ugc-account-activated', 0);
						add_user_meta($new_user_id, 'ugc-privilege', serialize(array('video'=>1,'youtube'=>1,'photo'=>1,'comment'=>1,'like'=>1,'view'=>1)));
						add_user_meta($new_user_id, 'ugc-opt-in-promo', $optin);

						$this->send_email_activation($new_user_id);

						$token = sha1($activation_key);
						$_SESSION['succes_token'] = $token;
						$_SESSION['succes_email'] = $email;

						$return = array('code'=>202,'msg'=>'Created','token'=>$token,'uid'=>$new_user_id);
					} else {
						$return = array('code'=>500,'msg'=>'Register fail, try again later');
					}
				} else {
					$return = array('code'=>500,'msg'=>'E-Mail you\'ve entered has alredy been used');
				}
			} else {
				$return = array('code'=>500,'msg'=>'Please retype your password correctly');
			}
	    }

	    return $return;
    }

    public function resend_activation() {
    	$return = array('code'=>500,'msg'=>'Please enter your email !');
    
    	if($this->validate_recaptcha()) {
    		$token = sanitize_text_field( $_POST['ugc-token'] );

    		if(isset($_SESSION['succes_token']) and isset($_SESSION['succes_email']) and !empty($token) and $_SESSION['succes_token'] == $token) {
		        $email = sanitize_email( $_POST['ugc-email'] );

		        if(!empty($email) and $email == $_SESSION['succes_email']) {
		        	$user = get_user_by('email',$email);
		        	if(isset($user->data->ID)) {
		        		$this->send_email_activation($user->data->ID);
		        		$return = array('code'=>200,'msg'=>'Activation email has been re-sent');
		        	} else {
		        		$return = array('code'=>500,'msg'=>'Email you have entered is not registered');
		        	}
		        }
	    	} else {
	    		$return = array('code'=>500,'msg'=>'Token is invalid or expired');
	    	}	
    	} else {
    		$return = array('code'=>500,'msg'=>'Recaptcha is invalid');
    	}

    	echo json_encode($return);
		wp_die();
    }

	public function save_register() {
		$return = array('code'=>500,'msg'=>'Recaptcha is invalid');

		if($this->validate_recaptcha()) {

			$fname   	=   sanitize_text_field( $_POST['ugc-fname'] );
			$lname   	=   sanitize_text_field( $_POST['ugc-lname'] );
	        $password   =   esc_attr( $_POST['ugc-password'] );
	        $repassword =   esc_attr( $_POST['ugc-repassword'] );
	        $email      =   sanitize_email( $_POST['ugc-email'] );
	        $optin      =   (isset($_POST['ugc-optin'])) ? 1 : 0;

	        $return = $this->register_user($fname,$lname,$password,$repassword,$email,$optin);

    	}

		echo json_encode($return);
		wp_die();
	}

	public function saveregister_socmed() {
		$return = array('code'=>500,'msg'=>'Recaptcha is invalid');

		if($this->validate_recaptcha()) {

			$callback_info = $_SESSION['callback_info'];

			if($callback_info != FALSE) {

				$fname   	=   sanitize_text_field( $_POST['ugc-fname'] );
				$lname   	=   sanitize_text_field( $_POST['ugc-lname'] );
				$password   =   "";
				$repassword =   "";

		        if($callback_info['callback_type'] == 'twitter') {
		        	$password   =   esc_attr( $callback_info['access_token']['oauth_token_secret'] );
		        	$repassword =   esc_attr( $callback_info['access_token']['oauth_token_secret'] );
		        } else {
		        	$password   =   sha1($callback_info['access_token']['token_id']);
		        	$repassword =   sha1($callback_info['access_token']['token_id']);
		        }

		        $email      =   sanitize_email( $_POST['ugc-email'] );
		        $optin      =   (isset($_POST['ugc-optin'])) ? 1 : 0;

		        $return = $this->register_user($fname,$lname,$password,$repassword,$email,$optin);


		        if($return['code'] == 202) {
		        	if($callback_info['callback_type'] == 'twitter') {
		        		add_user_meta($return['uid'],'ugc-twitter-accesstoken',$callback_info['access_token']['oauth_token_secret']);
		        		add_user_meta($return['uid'],'ugc-twitter-uid',$callback_info['user_info']['uid']);
		        		add_user_meta($return['uid'],'ugc-twitter-screenname',$callback_info['user_info']['screenname']);
		        		add_user_meta($return['uid'],'ugc-user-avatar',$callback_info['user_info']['avatar']);
		        	} else {
		        		add_user_meta($return['uid'],'ugc-facebook-uid',$callback_info['user_info']['uid']);
		        		add_user_meta($return['uid'],'ugc-facebook-accesstoken',$callback_info['access_token']['token_secret']);
		        		add_user_meta($return['uid'],'ugc-user-avatar',$callback_info['user_info']['avatar']);
		        	}
		        	unset($_SESSION['callback_info']);
		        }
	    	} else {
	    		$return = array('code'=>500,'msg'=>'Session is expired !');
	    	}
    	}

		echo json_encode($return);
		wp_die();
	}

	public function check_email() {
		$return = array('code'=>200,'msg'=>'Success');

		$email = sanitize_email($_POST['email']);

		$this->WPDB->get_results("SELECT * FROM ".$this->WPDB->prefix."users WHERE user_email = '".$email."'");
		if($this->WPDB->num_rows > 0) {
			$return = array('code'=>500,'msg'=>'E-Mail you\'ve entered has alredy been used');
		}

		echo json_encode($return);
		wp_die();	
	}

	public function login() {
		$return = array('code'=>500,'msg'=>'Complete registration form correctly !');		

		$password   =   esc_attr( $_POST['ugc-password'] );
	    $email      =   sanitize_email( $_POST['ugc-email'] );
	    $remember   =   (isset($_POST['ugc-remember'])) ? TRUE : FALSE;

	    if(!empty($password) and !empty($email)) {
	    	$creds = array(
		        'user_login'    => $email,
		        'user_password' => $password,
		        'remember'      => $remember
		    );
		 
		    $user = wp_signon( $creds, is_ssl() );
		 
		    if ( is_wp_error( $user ) ) {
		        $return = array('code'=>500,'msg'=>$user->get_error_message());
		    } else {
		    	$activate = get_user_meta($signon->data->ID,'ugc-account-activated');

				if(isset($activate[0]) and $activate[0] == 0) {
					wp_logout();
					$return = array('code'=>500,'msg'=>'You havent activated your Account yet, check E-mail that we sent earlier to activate your account');
				} else {
		    		$return = array('code'=>200,'msg'=>'Success');
		    	}
		    }
	    }

		echo json_encode($return);
		wp_die();	
	}

	public function acc_activation() {
		$return = array('code'=>500,'msg'=>'Token is invalid or expired');
		$activation_key = sanitize_text_field( $_POST['ugc_activation_key'] );
		if(!empty($activation_key)) {
			$user = $this->WPDB->get_results("SELECT * FROM ".$this->WPDB->prefix."usermeta WHERE meta_key = 'ugc-activation-ticket' AND meta_value = '".$activation_key."'");
			if(isset($user[0]->user_id)) {
				$uid = $user[0]->user_id;

				update_user_meta($uid,'ugc-activation-ticket','');
				update_user_meta($uid,'ugc-account-activated',1);
				$this->mailchimp_push($uid);

				$return = array('code'=>200,'msg'=>'Success');
			}
		}

		echo json_encode($return);
		wp_die();			
	}

	public function send_reset_password() {
		$return = array('code'=>500,'msg'=>'Please enter your email !');
    
    	if($this->validate_recaptcha()) {
    		$email = sanitize_email( $_POST['ugc-email'] );

		    if(!empty($email)) {
		    	$user = get_user_by('email',$email);
		    	if(isset($user->data->ID)) {
		    		$name = ucwords($user->data->display_name);
		    		$uid = $user->data->ID;
		    		$activation_key = sha1(md5($uid).date('U'));
		    		$login_url = get_permalink(get_option('ugc-login-pageid'));
		    		$link = $login_url.'?action=recovery&atoken='.$activation_key;

		    		update_user_meta($uid,'ugc-forgot-ticket',$activation_key);
		    		
		    		$this->MAIL->set_subject(get_option('ugc-reset-subject'));
		    		$this->MAIL->set_to($email);
		    		$this->MAIL->set_template(UGC_PLUGIN_DIR.'views/email-reset.html');
		    		$this->MAIL->set_content_pair(array(
		    										'NAME' => $name,
		    										'LINK' => $link
		    									  ));
		    		$this->MAIL->send();
		    		$return = array('code'=>200,'msg'=>'Activation email has been re-sent');
		    	} else {
		    		$return = array('code'=>500,'msg'=>'Email you have entered is not registered');
		    	}
		    }
    	} else {
    		$return = array('code'=>500,'msg'=>'Recaptcha is invalid');
    	}

    	echo json_encode($return);
		wp_die();
	}

	public function save_password() {
		$return = array('code'=>500,'msg'=>'Please complete form correctly');
    
    	if($this->validate_recaptcha()) {
    		$token = sanitize_text_field( $_POST['ugc-token'] );
    		$password   =   esc_attr( $_POST['ugc-password'] );
	        $repassword =   esc_attr( $_POST['ugc-repassword'] );

	        if(!empty($token) and !empty($password) and !empty($repassword)) {
	        	if($password == $repassword) {
					$user = $this->WPDB->get_results("SELECT * FROM ".$this->WPDB->prefix."usermeta WHERE meta_key = 'ugc-activation-ticket' AND meta_value = '".$activation_key."'");
					if(isset($user[0]->user_id)) {
						$uid = $user[0]->user_id;

						wp_set_password( $password, $uid );
						update_user_meta($uid,'ugc-forgot-ticket','');

						$return = array('code'=>200,'msg'=>'Password has been saved');

					} else {
						$return = array('code'=>500,'msg'=>'Token is invalid or expired');
					}
	        	} else {
	        		$return = array('code'=>500,'msg'=>'Please retype password correctly');
	        	}
	        }
    	} else {
    		$return = array('code'=>500,'msg'=>'Recaptcha is invalid');
    	}

    	echo json_encode($return);
		wp_die();		
	}

	public function save_profile() {
		$uid = (is_user_logged_in()) ? get_current_user_id() : 0;

		//echo $uid;

		if($uid != 0) {
	    	$fname 		= sanitize_text_field( $_POST['ugc-fname'] );
	    	$lname 		= sanitize_text_field( $_POST['ugc-lname'] );
	    	$bio 		= sanitize_text_field( $_POST['ugc-bio'] );
	    	$password   = esc_attr( $_POST['ugc-password'] );
		    $repassword = esc_attr( $_POST['ugc-repassword'] );

		    if(!empty($fname) and !empty($lname)) {
		    	$error_msg = array();
		    	if(isset($_FILES['ugc-avatar']) and !empty($_FILES['ugc-avatar']['name'])) {
		    		$upload = ugc_upload_file('ugc-avatar');
		    		if($upload['code'] == 200) {
		    			update_user_meta($uid,'ugc-user-avatar',$upload['url']);
		    		} else {
		    			$error_msg[] = "<li>".$upload['msg']."</li>";
		    		}
		    	}

		    	if(!empty($fname) or !empty($lname) or !empty($bio)) {
		    		$user_id = wp_update_user(array(
		    									'ID' => $uid,
		    									'display_name' => $fname." ".$lname,
		    									'first_name' => $fname,
		    									'last_name' => $lname,
		    									'description' => $bio
		    								 ));

		    		if ( is_wp_error( $user_id ) ) {
		    			$error_msg[] = "<li>Fail to updating user profile</li>";
		    		}
		    	}
				
				if(!empty($password)) {
					if($password == $repassword) {
						wp_set_password( $password, $uid );
					} else {
						$error_msg[] = "<li>Please retype password correctly</li>";
					}
				}    	

				if(count($error_msg) > 0) {
					$error_msg = "<ul>".implode("", $error_msg)."</ul>";
					$return = array('code'=>500,'msg'=>'Some changes might be saved, but with following error<br>'.$error_msg);	
				} else {
					$return = array('code'=>200,'msg'=>'Changes has been saved');	
				}

		    } else {
		    	$return = array('code'=>500,'msg'=>'First name or last name can not be empty');
		    }
		} else {
			$return = array('code'=>500,'msg'=>'Unauthorized');	
		}
    	

    	echo json_encode($return);
		wp_die();	
	}

	public function get_youtube_data() {
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$api_key = get_option('ugc-youtube-key');
		$video_url = sanitize_text_field($_POST['youtube_url']);
		$video_id = linkifyYouTubeURLs($video_url);
		$api_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails%2Cstatistics&id=' . $video_id . '&key=' . $api_key;
		$data = file_get_contents($api_url);
		/*
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		*/
		echo $data;
		wp_die();
	}

	public function upload_image_handler() {
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$upload = ugc_upload_file('meta_image');

		echo json_encode($upload);
		wp_die();
	}

	public function save_post_handler() {
		$return = array('code'=>500,'msg'=>'Complete correctly form bellow !');
		$uid = (is_user_logged_in()) ? get_current_user_id() : 0;

		if($uid == 0) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$meta_id = (isset($_POST['meta_id']) and !empty($_POST['meta_id'])) ? sanitize_text_field($_POST['meta_id']) : 0;
		$type = (isset($_POST['type'])) ? sanitize_text_field($_POST['type']) : '';
		$url = (isset($_POST['url'])) ? sanitize_text_field($_POST['url']) : '';
		$meta_image = (isset($_POST['meta_image'])) ? sanitize_text_field($_POST['meta_image']) : '';
		$meta_title = (isset($_POST['meta_title'])) ? sanitize_text_field($_POST['meta_title']) : '';
		$meta_description = (isset($_POST['meta_description'])) ? sanitize_text_field($_POST['meta_description']) : '';
		$youtube_id = (isset($_POST['youtube_id'])) ? sanitize_text_field($_POST['youtube_id']) : '';
		

		if(!empty($type) and !empty($meta_image) 
						 and !empty($meta_title) ) {

			$metas = array(
						'ugc_meta_image' => $meta_image,
						'ugc_view_count' => 0,
						'ugc_like_count' => 0,
						'ugc_comment_count' => 0
					 );

			if($type == 'youtube') {
				$metas = array_merge($metas,array(
												'ugc_youtube_id' => $youtube_id,
												'ugc_youtube_url' => $url
						 			 		));		
			} else if($type == 'video') {
				$upload = ugc_upload_file('videopath');
				if($upload['code'] == 200) {
					$metas = array_merge($metas,array(
													'ugc_video_path' => $upload['url']
							 			 		));		
				} else {
					echo json_encode(array('code'=>500,'msg'=>$upload['msg']));
					wp_die();
				}
			}

			$datas = array(
						'ID' => $meta_id,
						'post_author' => $uid,
						'post_date' => date('Y-m-d H:i:s'),
						'post_content' => $meta_description,
						'post_content_filtered' => strip_tags($meta_description),
						'post_title' => $meta_title,
						'comment_status' => 'open',
						'ping_status' => 'open',
						'post_type' => 'ugc-post-'.$type,
						'meta_input' => $metas,
						//'post_status' => 'publish'
					 );

			$set = wp_insert_post($datas);

			if ( is_wp_error( $set ) ) {
				$return = array('code'=>500,'msg'=>'Failed to save your Submission, try again later !');
			} else {
				set_stat_post($set);

				$return = array('code'=>200,'msg'=>'Your changes have been saved, Thank you !');
			}
			
		}

		echo json_encode($return);
		wp_die();	
	}

	public function remove_post() {
		global $wpdb;

		
		$return = array('code'=>500,'msg'=>'Complete correctly form bellow !');
		$uid = (is_user_logged_in()) ? get_current_user_id() : 0;

		if($uid == 0) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$post_id = $_POST['post_id'];
		$is_bulk = (isset($_POST['is_bulk']) and $_POST['is_bulk'] == 1) ? TRUE : FALSE;

		if(!$is_bulk) {
			$get_post = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."posts WHERE ID = ".$post_id);
			$get_post = end($get_post);

			if($get_post->post_author == $uid or is_admin()) {
				$update = wp_update_post(array('ID'=>$post_id,'post_status'=>'removed'));
				if(is_wp_error($update)) {
					$return = array('code'=>500,'msg'=>'Failed to remove post !');
				} else {
					$return = array('code'=>200,'msg'=>'Success');
				}
			} else {
				$return = array('code'=>500,'msg'=>'Youre not allowed to remove this post');
			} 
		} else {
			$ids = explode(",", $post_id);
			$number_of_success = 0;
			foreach ($ids as $key => $id) {
				$get_post = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."posts WHERE ID = ".$id);
				$get_post = end($get_post);

				if($get_post->post_author == $uid or is_admin()) {
					$update = wp_update_post(array('ID'=>$post_id,'post_status'=>'removed'));
					if(!is_wp_error($update)) {
						$number_of_success++;
					} 
				}
			}

			if($number_of_success == count($ids)) {
				$return = array('code'=>200,'msg'=>'Success');
			} else {
				$return = array('code'=>500,'msg'=>'Failed to remove some posts. But some other might have been removed');
			}
		}

		echo json_encode($return);
		wp_die();	
	}

	public function set_view_count() {
		$post_id = sanitize_text_field($_POST['post_id']);
		$agent =  (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$ipaddr = get_client_ip();
		$uid = (is_user_logged_in()) ? get_current_user_id() : 0;
		$return = array('code'=>500);

		if(!empty($post_id) and !empty($agent) and !empty($ipaddr)) {
			
			$set = update_stat_post($post_id,'view',array(
														'post_id' => $post_id,
														'user_id' => $uid,
														'action_type' => 'view',
														'action_param' => serialize(array('agent'=>$agent,'ip'=>$ipaddr,'cookie'=>$cookie_name)),
														'status' => 1,
														'created_date' => date('Y-m-d H:i:s')
												    ));

			if($set != FALSE) {
				$return = array('code'=>200,'count'=>$set);
			}
		}

		echo json_encode($return);
		wp_die();
	}

	public function set_like_count() {
		$post_id = sanitize_text_field($_POST['post_id']);
		$agent =  (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$ipaddr = get_client_ip();
		$uid = (is_user_logged_in()) ? get_current_user_id() : 0;
		$return = array('code'=>500);

		if(!empty($post_id) and !empty($agent) and !empty($ipaddr) and $uid != 0 and !has_like($post_id)) {
			
			$set = update_stat_post($post_id,'like',array(
														'post_id' => $post_id,
														'user_id' => $uid,
														'action_type' => 'like',
														'action_param' => serialize(array('agent'=>$agent,'ip'=>$ipaddr,'cookie'=>$cookie_name)),
														'status' => 1,
														'created_date' => date('Y-m-d H:i:s')
												    ));

			if($set != FALSE) {
				$return = array('code'=>200,'count'=>$set);
			}
		}

		echo json_encode($return);
		wp_die();
	}

	public function save_comment() {
		global $wpdb;
		
		$return = array('code'=>500,'msg'=>'Complete correctly form bellow !');
		$uid = (is_user_logged_in()) ? get_current_user_id() : 0;

		if($uid == 0) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		//if($this->validate_recaptcha()) {
			$post_id = sanitize_text_field($_POST['post_id']);
			$comment = sanitize_text_field($_POST['ugc-comment']);
			$get_post = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."posts WHERE ID = ".$post_id);
			$get_post = end($get_post);
			$user = get_user_by('id',$uid);
			$agent =  (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
			$ipaddr = get_client_ip();

			if($get_post->post_status == 'publish' and $get_post->ping_status == 'open' 
												   and $get_post->comment_status == 'open' 
												   and !empty($post_id)
												   and !empty($comment)) {
				$insert = $wpdb->insert($wpdb->prefix."comments",array(
																	'comment_post_ID' => $post_id,
																	'user_id' => $uid,
																	'comment_content' => $comment,
																	'comment_author' => $user->data->display_name,
																	'comment_author_email' => $user->data->user_email,
																	'comment_author_url' => $user->data->user_url,
																	'comment_author_IP' => $ipaddr,
																	'comment_date' => date('Y-m-d H:i:s'),
																	'comment_date_gmt' => date('Y-m-d H:i:s'),
																	'comment_karma' => 0,
																	'comment_approved' => 0,
																	'comment_agent' => $agent,
																	'comment_parent' => 0
																 ));
				

				if(is_wp_error($insert)) {
					$return = array('code'=>500,'msg'=>'Failed to save comment !');
				} else {
					$set = update_stat_post($post_id,'comment',array(
															'post_id' => $post_id,
															'user_id' => $uid,
															'action_type' => 'comment',
															'action_param' => serialize(array('agent'=>$agent,'ip'=>$ipaddr,'comment_id'=>$insert)),
															'status' => 1,
															'created_date' => date('Y-m-d H:i:s')
													    ));
					$return = array('code'=>200,'msg'=>'Your comment has been saved. It will be published after Admin approval');
				}
			} else {
				$return = array('code'=>500,'msg'=>'Comment is disabled for this post');
			}
		//} else {
		//	$return = array('code'=>500,'msg'=>'Recaptcha is invalid');
		//}

		echo json_encode($return);
		wp_die();			
	}

	public function get_playlist() {
		global $wpdb;
		$return = array('code'=>500);	

		$post_id = sanitize_text_field($_POST['post_id']);	
		$youtube_embed = sanitize_text_field($_POST['youtube_embed']);	
		
		if($youtube_embed == 'no') {
			$obj = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."posts WHERE ID = '".$post_id."'");
			$detail_url = get_permalink(get_option('ugc-detail-pageid'));

			if(isset($obj->ID)) {
				$object = "";
				$metas = get_post_meta($obj->ID);
				if($obj->post_type == 'ugc-post-youtube') {
					$yid = isset($metas['ugc_youtube_id'][0]) ? $metas['ugc_youtube_id'][0] : 0;
					$object = '<iframe src="https://www.youtube.com/embed/'.$yid.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';				
				} else if($obj->post_type == 'ugc-post-video') {
					$vid = isset($metas['ugc_video_path'][0]) ? $metas['ugc_video_path'][0] : 0;
					$object = '<video controls>
								  <source src="'.$vid.'" type="video/mp4">
								Your browser does not support the video tag.
								</video>';
				} else if($obj->post_type == 'ugc-post-photo') {
					$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
					$object = '<div class="ugc-playlist-main-image maintainratio">
									<img src="'.$image.'">
									<div class="text-area">
										<div class="title">'.$obj->post_title.'</div>
										<div class="desc">'.$obj->post_content.'</div>
										<div class="button"><a href="'.$detail_url.'?id='.$obj->ID.'">View Detail</a></div>
									</div>
								</div>';
				}
				$return = array('code'=>200,'obj'=>$object);
			}
		} else {
			$object = '<iframe src="https://www.youtube.com/embed/'.$post_id.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';	
			$return = array('code'=>200,'obj'=>$object);
		}

		echo json_encode($return);
		wp_die();	
	}

	function approve_post() {
		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

		$return = array('code'=>500,'msg'=>'Nothing is selected !');

		$ids = $_POST['ids'];
		$type = $_POST['type'];
		$email = $_POST['email'];
		$action = "approve";

		if($type == 'verify') {
			$ids = explode(",", $ids);
			$rows = array();
       		foreach ($ids as $key => $value) {
       			$post = get_post( $value ); 
       			$get = get_user_by('id',$post->post_author);
       			$rows[] = array($value,$get->data->user_email);
       		}
       		$return = array('code'=>200,'type'=>'verify','datas'=>$rows);
		} else if($type == 'sending') {
			$post = get_post( $ids ); 
			$user = get_user_by('id',$post->post_author);
			$metas = get_post_meta($post->ID);
			$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');

			$datas = array(
						'*|NAME|*' => $user->data->display_name,
						'*|IMAGE|*' => $image,
						'*|TITLE|*' => $post->post_title,
						'*|DESC|*' => $post->post_content
					 );

			///$email = "amri.hidayatulloh@gmail.com";


			$action = ($action == "approve") ? 'publish' : 'draft';
			$subject = ($action == 1) ? 'Content Approval' : 'Content Rejection';
			$template = ($action == 1) ? 'email-approval' : 'email-rejection';
			$template =  UGC_PLUGIN_DIR.'views/'.$template.'.html';

			//$this->query->update_state($ids,$action);
			$update = wp_update_post(array('ID'=>$ids,'post_status'=>'publish'));
			
			if(!is_wp_error($update)) {
				$this->MAIL->set_subject($subject);
				$this->MAIL->set_to($user->data->user_email);
				$this->MAIL->set_template($template);
				$this->MAIL->set_content_pair($datas);
				$this->MAIL->send();

				$return = array('code'=>200);
			} else {
				$return = array('code'=>500);
			}
		}

	    echo json_encode($return);
	    wp_die();
	}  

	public function remove_like(){
		global $wpdb;

		if(!is_user_logged_in()) {
			echo json_encode(array('code'=>500,'msg'=>'Unauthorized!'));
			wp_die();
		}

        $ids = $_POST['ids'];
        $parent_id = $_POST['parent_id'];
        $return = array('code'=>500,'msg'=>'Nothing is selected !');

        if(!empty($ids)) {
       		$ids = explode(",", $ids);
       		foreach ($ids as $key => $value) {
       			$wpdb->update($wpdb->prefix."ugc_user_action",array('status'=>0),array('id'=>$value));
       		}
       		$total_left = get_like_count($parent_id);
       		$wpdb->update($wpdb->prefix."ugc_post_stat",array('like_count'=>$total_left),array('post_id'=>$parent_id));
       		$return = array('code'=>200,'msg'=>'Selected voter has been revoked, page will be reloaded !');
        }

	    echo json_encode($return);
	    wp_die();
	}
}