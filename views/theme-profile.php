<?php
$login_url = get_permalink(get_option('ugc-login-pageid'));
$profile_url = get_permalink(get_option('ugc-profile-pageid'));

if($_GET['action'] == 'logout') {
	wp_logout();
	echo "<script>location.href='".$login_url."';</script>";
}

$post = get_post( get_the_ID() ); 
$uid = (is_user_logged_in()) ? get_current_user_id() : 0;


if($uid == 0) {
	echo "<script>location.href='".$login_url."';</script>";
}

$action = (isset($_GET['action'])) ? $_GET['action'] : '';
$post_type = (isset($_GET['type'])) ? $_GET['type'] : 'all';



$user = get_user_by('id',$uid);

$default_avatar = get_avatar_url($uid);
$default_avatar = str_replace('//www','http://www',$default_avatar);

$fname = get_user_meta($uid,'first_name');
$lname = get_user_meta($uid,'last_name');
$avatar = get_user_meta($uid,'ugc-user-avatar');
$avatar = (isset($avatar[0])) ? $avatar[0] : $default_avatar;

$total_video = count_post('video',$uid);
$total_youtube = count_post('youtube',$uid);
$total_photos = count_post('photo',$uid);

$wp_post_type = array('ugc-post-video','ugc-post-photo','ugc-post-youtube');

if($post_type == 'videos') {
	$wp_post_type = array('ugc-post-video');
} else if($post_type == 'photos') {
	$wp_post_type = array('ugc-post-photo');
} else if($post_type == 'youtube') {
	$wp_post_type = array('ugc-post-youtube');
} 

get_header(); 
show_unconfirm_notif();
?>


<?php if($action == ''): ?>


<div class="wrap">
	<div class="ugc-wrap">
		<div class="ugc-breadcrumb-area">
			<?php 
			breadcrumbs(array(
							array('type'=>'page','caption'=>'Profile')
						));
			?>
		</div>
		<div class="ugc-own-panel">
			<div class="ugc-bio">
				<div class="ugc-avatar">
					<img src="<?php echo $avatar; ?>">
				</div>
				<div class="ugc-displayname">
					<?php echo $user->data->display_name; ?>
					<br>
					<div class="bio"><?php echo $user->description; ?></div>
					<a href="?action=edit-profile"><button><i class="fa fa-cog"></i> Edit Profile</button></a>
					<a href="?action=logout"><button class="red"><i class="fa fa-sign-out"></i> Sign Out</button></a>
				</div>
			</div>
			<div class="ugc-stats">
				<div class="ugc-info">
					<h1><?php echo ($total_video + $total_youtube + $total_photos); ?></h1>
					Posts
				</div>
				<div class="ugc-info">
					<h1><?php echo $total_video; ?></h1>
					Videos
				</div>
				<div class="ugc-info">
					<h1><?php echo $total_youtube; ?></h1>
					Youtube
				</div>
				<div class="ugc-info">
					<h1><?php echo $total_photos; ?></h1>
					Photos
				</div>

			</div>
		</div>

		

	</div>
	<div class="ugc-tab">
		<a href="?type=all"><button <?php if($post_type == 'all') echo 'class="active"'; ?>>All Posts</button></a>
		<a href="?type=videos"><button <?php if($post_type == 'videos') echo 'class="active"'; ?>>Videos</button></a>
		<a href="?type=youtube"><button <?php if($post_type == 'youtube') echo 'class="active"'; ?>>Youtube</button></a>
		<a href="?type=photos"><button <?php if($post_type == 'photos') echo 'class="active"'; ?>>Photos</button></a>
	</div>
	<?php 
	$max = get_option('ugc-display-limit-post');
	if(empty($max) or !isset($max)) {
		$max = 30;
	}

	render_submissions(array(
							'user_id' => $uid,
							'page' => (isset($_GET['nav']) ? $_GET['nav'] : 1),
							'max' => $max,
							'upload_button' => true,
							'enable_delete' => true,
							'post_type' => $wp_post_type,
							'page_type' => $post_type
						));
	?>
</div>

<?php elseif($action == 'edit-profile'): ?>

<div class="wrap">
	<div class="ugc-wrap">
		<div class="ugc-register-box">
			<h1 style="padding-top:1em">Edit Profile</h1>
			<br>
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
				<form action="<?php echo site_url(); ?>/wp-admin/admin-ajax.php?action=ugc_edit_profile" method="POST" class="ajax-form" id="profile-form" enctype="multipart/form-data">
					<input type="hidden" name="action" value="ugc_edit_profile">
					<div class="ugc-field center">
						<div class="ugc-avatar">
							<img src="<?php echo $avatar; ?>">
						</div>
						<div class="ugc-input">
							<input type="file" name="ugc-avatar">
						</div>
					</div>
					<div class="ugc-field">
						<div class="ugc-input">
							<input type="text" name="ugc-fname" placeholder="First Name" class="half" value="<?php echo (isset($fname[0]) ? $fname[0] : ''); ?>">
							<input type="text" name="ugc-lname" placeholder="Last Name" class="half" value="<?php echo (isset($lname[0]) ? $lname[0] : ''); ?>">
						</div>
					</div>
					<div class="ugc-field">
						<div class="ugc-input">
							<textarea name="ugc-bio" placeholder="Your Bio" autocomplete="off"><?php echo $user->description; ?></textarea>
						</div>
						<span class="alert"></span>
					</div>


					<h3 style="padding-top: 0">Update Password</h3>					

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

					<hr>
					
					<div class="ugc-field">
						<button type="submit" class="ugc-submit" data-form="#profile-form">Save Changes</button>
						<button type="button" class="ugc-loader display-none"><i class="fa fa-spinner fa-spin"></i></button>
					</div>

					<div class="ugc-field">
						<a href="<?php echo $profile_url; ?>">
							<button type="button">Back to Profile</button>
						</a>
					</div>
					
				</form>
			</div>

		</div>
	</div>
</div>

<?php endif; ?>
<?php get_footer();