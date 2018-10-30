<?php
/*
  Plugin Name: UGC - User Generated Content
  Plugin URI: 
  Description: This plugin will add the capability of handling User Generated Content on your WP Site. It allow user/member to create their own content (such as video, article or image) and upload it into site. This plugin also handle User registration and login using its own customize form, so user shouldnt use the WP login page.
  Version: 1.0
  Author: Amri Hidayatulloh
  Author URI: https://www.linkedin.com/in/amrimultimedia/
  License: GPLv2+
  Text Domain: biucg
*/

define( 'UGC_VERSION', '1.0.0' );
define( 'UGC_MINIMUM_WP_VERSION', '4.0' );
define( 'UGC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( UGC_PLUGIN_DIR . 'functions.php' );
require_once( UGC_PLUGIN_DIR . 'class.ugc-pagetemplater.php' );
require_once( UGC_PLUGIN_DIR . 'class.ugc-ajaxhandler.php' );
require_once( UGC_PLUGIN_DIR . 'class.ugc-oauthhandler.php' );
require_once( UGC_PLUGIN_DIR . 'class.ugc-mailer.php' );
require_once( UGC_PLUGIN_DIR . 'class.ugc-submissionlist.php' );
require_once( UGC_PLUGIN_DIR . 'class.ugc-likelist.php' );
require_once( UGC_PLUGIN_DIR . 'libraries/Twitteroauth.php' );
require_once( UGC_PLUGIN_DIR . 'libraries/Facebook.php' );


class Ugc {

	private $options = array(
						'ugc-db-version'=>'1.0',
						'ugc-youtube-key'=>'',
						'ugc-recaptcha-key'=>'',
						'ugc-recaptcha-secret'=>'',
						'ugc-mailchimp-key'=>'',
						'ugc-mailchimp-listid'=>'',
						'ugc-mailchimp-endpoint'=>'',
						'ugc-enable-facebook-login'=>1,
						'ugc-enable-twitter-login'=>1,
						'ugc-enable-facebook-share'=>1,
						'ugc-enable-twitter-share'=>1,
						'ugc-enable-pinterset-share'=>1,
						'ugc-enable-upload-photo'=>1,
						'ugc-enable-upload-youtube'=>1,
						'ugc-enable-upload-video'=>1,
						'ugc-enable-user-like'=>1,
						'ugc-enable-user-comment'=>1,
						'ugc-display-limit-post'=>30,
						'ugc-twitter-key'=>'',
						'ugc-twitter-secret'=>'',
						'ugc-twitter-callback'=>'',
						'ugc-facebook-appid'=>'',
						'ugc-facebook-secret'=>'',
						'ugc-facebook-callback'=>'',
						'ugc-register-pageid'=>'',
						'ugc-login-pageid'=>'',
						'ugc-profile-pageid'=>'',
						'ugc-landing-pageid'=>'',
						'ugc-detail-pageid'=>'',
						'ugc-activation-subject'=>'Email Activation',
						'ugc-reset-subject'=>'Email Password Recovery'
					   );

    function __construct() {
    	global $wpdb;

    	if ( !function_exists( 'add_action' ) ) {
			echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
			exit;
		}

		$mailer = new Ugc_Mailer();
		$ajaxhandler = new Ugc_AjaxHandler($wpdb,$mailer);
		$oauthhandler = new Ugc_OauthHandler($wpdb);

		$twitteroauth = new TwitterOAuth();
		$twitteroauth->create(get_option('ugc-twitter-key'), get_option('ugc-twitter-secret'));
		$oauthhandler->set_twitter_object($twitteroauth);

		$facebook = new Facebook(array(
							'appId' => get_option('ugc-facebook-appid'),
							'secret' => get_option('ugc-facebook-secret')
						));
		$oauthhandler->set_facebook_object($facebook);

        register_activation_hook( __FILE__, array( $this, 'wpa_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'wpa_uninstall' ) );

        add_action( 'init', array($this,'myStartSession'), 1);
        add_action( 'admin_menu', array( $this, 'wpa_add_menu' ));
        add_action( 'wp_head',array($this,'ugc_head_init'));
        add_action( 'plugins_loaded', array( 'Ugc_Pagetemplater', 'get_instance' ) );
        add_action( 'ugc_login_stat', 'login_stat' );

        //Non Private Ajax Handler
        add_action( 'wp_ajax_nopriv_ugc_saveregister', array( $ajaxhandler, 'save_register' ) );
        add_action( 'wp_ajax_nopriv_ugc_saveregister_socmed', array( $ajaxhandler, 'saveregister_socmed' ) );
        add_action( 'wp_ajax_nopriv_ugc_check_email', array( $ajaxhandler, 'check_email' ) );
        add_action( 'wp_ajax_nopriv_ugc_login', array( $ajaxhandler, 'login' ) );
        add_action( 'wp_ajax_nopriv_ugc_resend_activation', array( $ajaxhandler, 'resend_activation' ) );
        add_action( 'wp_ajax_nopriv_ugc_activation', array( $ajaxhandler, 'acc_activation' ) );
        add_action( 'wp_ajax_nopriv_ugc_reset_password', array( $ajaxhandler, 'send_reset_password' ) );
        add_action( 'wp_ajax_nopriv_ugc_save_password', array( $ajaxhandler, 'save_password' ) );
        add_action( 'wp_ajax_nopriv_ugc_mailchimplist', array( $ajaxhandler, 'mailchimp_get_list' ) );
        add_action( 'wp_ajax_nopriv_ugc_playlist', array( $ajaxhandler, 'get_playlist' ) );
        add_action( 'wp_ajax_nopriv_ugc_set_view_count', array($ajaxhandler, 'set_view_count') );

        add_action( 'wp_ajax_nopriv_ugc_redirect_twitter', array( $oauthhandler, 'redirect_twitter' ) );
        add_action( 'wp_ajax_nopriv_ugc_callback_twitter', array( $oauthhandler, 'callback_twitter' ) );
        add_action( 'wp_ajax_nopriv_ugc_redirect_facebook', array( $oauthhandler, 'redirect_facebook' ) );
        add_action( 'wp_ajax_nopriv_ugc_callback_facebook', array( $oauthhandler, 'callback_facebook' ) );

        //Private Ajax Handler
        add_action( 'wp_ajax_ugc_edit_profile', array( $ajaxhandler, 'save_profile' ) );
        add_action( 'wp_ajax_ugc_fetchYoutubeData', array( $ajaxhandler, 'get_youtube_data' ) );
        add_action( 'wp_ajax_ugc_uploadImageHandler', array( $ajaxhandler, 'upload_image_handler' ) );
        add_action( 'wp_ajax_ugc_submissionSaveHandler', array( $ajaxhandler, 'save_post_handler' ) );
        add_action( 'wp_ajax_ugc_remove_post', array($ajaxhandler, 'remove_post') );
        add_action( 'wp_ajax_ugc_approve_post', array($ajaxhandler, 'approve_post') );
        add_action( 'wp_ajax_ugc_set_view_count', array($ajaxhandler, 'set_view_count') );
        add_action( 'wp_ajax_ugc_remove_like', array($ajaxhandler, 'remove_like') );
        add_action( 'wp_ajax_ugc_set_like_count', array($ajaxhandler, 'set_like_count') );
        add_action( 'wp_ajax_ugc_save_comment', array($ajaxhandler, 'save_comment') );
        add_action( 'wp_ajax_ugc_playlist', array( $ajaxhandler, 'get_playlist' ) );
        add_action( 'wp_ajax_ugc_export_submission', 'export_submission_csv' );


        //Shortcode
        add_shortcode( 'slides', 'slides_shortcode' );
        add_shortcode( 'slideitem', 'slides_item_shortcode' );
        add_shortcode( 'playlist', 'playlist_shortcode' );
        add_shortcode( 'playlistitem', 'playlistitem_shortcode' );
        add_shortcode( 'popular', 'popular_shortcode' );
        add_shortcode( 'htmlembed', 'htmlembed_shortcode' );


    }

    function wpa_add_menu() {
    	$this->ugc_dashboard_head_init();

        add_menu_page( 'User Generated Content', 'UGC', 'manage_options', 'ugc-dashboard',  array(
                          $this,
                         'wpa_page_file_path'
                        ), plugins_url('images/ugc-icon.png', __FILE__),'2.2.9');

        add_submenu_page( 'ugc-dashboard', 'Submission', ' Submission', 'manage_options', 'ugc-submission', array(
                              $this,
                             'wpa_page_file_path'
                            ));

        add_submenu_page( 'ugc-dashboard', 'Export', 'Export', 'manage_options', 'ugc-export', array(
                              $this,
                             'wpa_page_file_path'
                            ));

        add_submenu_page( 'ugc-dashboard','Settings', '<b style="color:#f9845b">Settings</b>', 'manage_options', 'ugc-settings', array(
                              $this,
                             'wpa_page_file_path'
                            ));

        
    }

    function wpa_page_file_path() {
    	global $wpdb;

    	$screen = get_current_screen();

    	if( !is_admin() ) {
			echo 'Hi there!  you are seems have no right to call me';
			exit;
		}

		if ( strpos( $screen->base, 'ugc-settings' ) !== false ) {
			include( dirname(__FILE__) . '/views/admin-settings.php' );
		} else if ( strpos( $screen->base, 'ugc-dashboard' ) !== false ) {

			include( dirname(__FILE__) . '/views/admin-dashboard.php' );
			
		} else if ( strpos( $screen->base, 'ugc-export' ) !== false ) {
			include( dirname(__FILE__) . '/views/admin-export.php' );
		} else if ( strpos( $screen->base, 'ugc-submission' ) !== false ) {
			$tab = (isset($_GET['tab'])) ? $_GET['tab'] : '';
	    	if($tab == 'like' and isset($_GET['id'])) {
	    		$table = new Ugc_Likelist($wpdb,$_GET['id']);
	    		include( dirname(__FILE__) . '/views/admin-like.php' );
	    	} else if($tab == 'detail' and isset($_GET['id'])) {
	    		include( dirname(__FILE__) . '/views/admin-detail.php' );
	    	} else {
				$table = new Ugc_submissionlist($wpdb);
				include( dirname(__FILE__) . '/views/admin-submission.php' );
			}
		}
	}

	function wpa_install() {
		global $wpdb;

    	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    	$charset_collate = $wpdb->get_charset_collate();

    	$sql = "CREATE TABLE IF NOT EXISTS  ".$wpdb->prefix . 'ugc_user_action'." (
					id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					user_id int(11) NOT NULL,
					post_id int(11) NOT NULL,
					action_type varchar(32) NOT NULL,
					action_param TEXT NOT NULL,
					status int(1) DEFAULT 2,
					created_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					modified_date timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
				) $charset_collate;";

		
		dbDelta( $sql );

		$sql = "CREATE TABLE IF NOT EXISTS  ".$wpdb->prefix . 'ugc_post_stat'." (
					id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					post_id int(11) NOT NULL,
					view_count int(11) NOT NULL,
					like_count int(11) NOT NULL,
					comment_count int(11) NOT NULL,
					share_count int(11) NOT NULL,
					is_highlight int(1) DEFAULT 0,
					modified_date timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
				) $charset_collate;";

		
		dbDelta( $sql );

		foreach ($this->options as $key => $value) {
			add_option($key,$value);
		}

	}

	function wpa_uninstall() {
		foreach ($this->options as $key => $value) {
			delete_option($key);
		}

	}

	function ugc_dashboard_head_init() {
		wp_enqueue_style( 'dashicons' );
    	wp_enqueue_style( 'ugc-dashboard', plugins_url('ugc/css/ugc-dashboard-style.css'));
    	wp_enqueue_style( 'fontawesome-all', plugins_url('ugc/vendor/fontawesome/css/font-awesome.min.css'));
    	wp_enqueue_style( 'swal', plugins_url('ugc/vendor/sweetalert/sweetalert.css'));
    	/*
   		echo '<script type="text/javascript">
    			var ugc_ajaxurl = "'.admin_url('admin-ajax.php').'";
			  </script>';
		*/
		wp_localize_script(
	    	'function',
	    	'ajax_script',
	    	array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

    	wp_enqueue_script( 'jquery-1.11.0', plugins_url('ugc/vendor/jquery-1.11.0.min.js'));
    	wp_enqueue_script( 'jquery.form', plugins_url('ugc/vendor/jquery.form.js'));
    	wp_enqueue_script( 'swal', plugins_url('ugc/vendor/sweetalert/sweetalert.js'));
    	wp_enqueue_script( 'ugc-dashboard', plugins_url('ugc/js/ugc-dashboard.js'));
	}

	function ugc_head_init() {
		$register_url = get_permalink(get_option('ugc-register-pageid'));
		$login_url = get_permalink(get_option('ugc-login-pageid'));
		$profile_url = get_permalink(get_option('ugc-profile-pageid'));

    	wp_enqueue_style( 'dashicons' );
    	wp_enqueue_style( 'ugc-theme-views', plugins_url('ugc/css/ugc-theme-views.css'));
    	wp_enqueue_style( 'fontawesome-all', plugins_url('ugc/vendor/fontawesome/css/font-awesome.min.css'));
    	wp_enqueue_style( 'swal', plugins_url('ugc/vendor/sweetalert/sweetalert.css'));
    	wp_enqueue_style( 'slick', plugins_url('ugc/vendor/slick/slick.min.css'));
    	wp_enqueue_style( 'slick-theme', plugins_url('ugc/vendor/slick/slick-theme.min.css'));

    	echo '<div id="fb-root"></div>';
    	echo '<script type="text/javascript">
    			var ugc_ajaxurl = "'.admin_url('admin-ajax.php').'";
    			var ugc_registerurl = "'.$register_url.'";
    			var ugc_loginurl = "'.$login_url.'";
    			var ugc_profileurl = "'.$profile_url.'";

    			var recaptchaCallback = function () {
				    if($("#recaptcha-register").size() > 0) {
				      recaptcha_register = grecaptcha.render("recaptcha-register", {
				          sitekey: "'.get_option('ugc-recaptcha-key').'"
				      });
				    }
			  	}

			  	window.fbAsyncInit = function() {
				    FB.init({
				      appId      : "'.get_option('ugc-facebook-appid').'",
				      xfbml      : true,
				      version    : "v2.7"
				    });
				  };

				  (function(d, s, id){
				     var js, fjs = d.getElementsByTagName(s)[0];
				     if (d.getElementById(id)) {return;}
				     js = d.createElement(s); js.id = id;
				     js.src = "//connect.facebook.net/en_US/sdk.js";
				     fjs.parentNode.insertBefore(js, fjs);
				   }(document, "script", "facebook-jssdk"));
			  </script>';

		echo "<script src='https://www.google.com/recaptcha/api.js?hl=en&onload=recaptchaCallback&render=explicit' async defer></script>";

    	wp_enqueue_script( 'jquery-1.11.0', plugins_url('ugc/vendor/jquery-1.11.0.min.js'));
    	wp_enqueue_script( 'jquery.form', plugins_url('ugc/vendor/jquery.form.js'));
    	wp_enqueue_script( 'swal', plugins_url('ugc/vendor/sweetalert/sweetalert.js'));
    	wp_enqueue_script( 'slick', plugins_url('ugc/vendor/slick/slick.min.js'));
    	wp_enqueue_script( 'ugc-common-script', plugins_url('ugc/js/ugc-common-script.js'));
    
    	include( dirname(__FILE__) . '/views/theme-part-addpopup.php' );

    }

    function myStartSession() {
	    if(!session_id()) {
	        session_start();
	    }
	}

	
	


}

new Ugc();