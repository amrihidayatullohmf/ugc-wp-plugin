<?php
$login_url = get_permalink(get_option('ugc-login-pageid'));
$profile_url = get_permalink(get_option('ugc-profile-pageid'));


$post = get_post( get_the_ID() ); 
$uid = (is_user_logged_in()) ? get_current_user_id() : 0;


$post_type = (isset($_GET['type'])) ? $_GET['type'] : 'all';
$sort = (isset($_GET['sort'])) ? $_GET['sort'] : 'date';

$wp_post_type = array('ugc-post-video','ugc-post-photo','ugc-post-youtube','post');

if($post_type == 'videos') {
	$wp_post_type = array('ugc-post-video');
} else if($post_type == 'photos') {
	$wp_post_type = array('ugc-post-photo');
} else if($post_type == 'youtube') {
	$wp_post_type = array('ugc-post-youtube');
} else if($post_type == 'article') {
	$wp_post_type = array('post');
}

get_header(); 
show_unconfirm_notif();
$page_type = $post_type;

if($post_type == 'all') {
	$post_type = 'Posts';
}
?>


<?php if($action == ''): ?>


<div class="wrap">
	<div class="ugc-wrap">
		<div class="ugc-breadcrumb-area">
			<?php 
			breadcrumbs(array(
							array('type'=>'page','caption'=>ucwords($post_type))
						));
			?>
		</div>
		<h1>All <?php echo ucwords($post_type); ?></h1>				

	</div>
	<div class="ugc-tab segment">
		<div>
			<a href="?type=all"><button <?php if($page_type == 'all') echo 'class="active"'; ?>>All Posts</button></a>
			<a href="?type=videos"><button <?php if($page_type == 'videos') echo 'class="active"'; ?>>Videos</button></a>
			<a href="?type=youtube"><button <?php if($page_type == 'youtube') echo 'class="active"'; ?>>Youtube</button></a>
			<a href="?type=photos"><button <?php if($page_type == 'photos') echo 'class="active"'; ?>>Photos</button></a>
			<a href="?type=article"><button <?php if($page_type == 'article') echo 'class="active"'; ?>>Article</button></a>
		</div>
		<div>
			<a href="?type=<?php echo $page_type; ?>&sort=date"><button <?php if($sort == 'date') echo 'class="active"'; ?>>Recently Added</button></a>
			<a href="?type=<?php echo $page_type; ?>&sort=popular"><button <?php if($sort == 'popular') echo 'class="active"'; ?>>Most Popular</button></a>
		</div>
	</div>
	<?php 
	$max = get_option('ugc-display-limit-post');
	if(empty($max) or !isset($max)) {
		$max = 30;
	}


	render_submissions(array(
							'page' => ((isset($_GET['nav'])) ? $_GET['nav'] : 1),
							'max' => $max,
							'upload_button' => false,
							'enable_delete' => false,
							'post_type' => $wp_post_type,
							'page_type' => $page_type,
							'post_status' => array('publish'),
							'orderby' => $sort
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