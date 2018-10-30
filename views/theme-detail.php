<?php
$login_url = get_permalink(get_option('ugc-login-pageid'));
$profile_url = get_permalink(get_option('ugc-profile-pageid'));
$landing_url = get_permalink(get_option('ugc-landing-pageid'));

$id = (isset($_GET['id'])) ? $_GET['id'] : '';
$post = get_post( $id ); 
$uid = (is_user_logged_in()) ? get_current_user_id() : 0;

if($post->post_status == 'draft' and $post->post_author != $uid) {
	echo "<script>location.href='".$login_url."';</script>";	
}

$user = get_user_by('id',$uid);
$metas = get_post_meta($post->ID);
$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
$yid = isset($metas['ugc_youtube_id'][0]) ? $metas['ugc_youtube_id'][0] : 0;
$vid = isset($metas['ugc_video_path'][0]) ? $metas['ugc_video_path'][0] : 0;
$view = get_stat_post($id,'view');
$like = get_stat_post($id,'like');

$cookie_name = md5($uid.$id.date('Ymd'));

get_header(); 
show_unconfirm_notif();
?>

<input type="hidden" id="ugc_cookie_post_name" value="<?php echo $cookie_name; ?>">
<input type="hidden" id="ugc_post_id" value="<?php echo $id; ?>">

<div class="wrap">
	<div class="ugc-wrap content-section">
		<div class="ugc-breadcrumb-area">
			<?php 
			$page_cat = "";
			$page_url = "";

			if($post->post_type == 'ugc-post-youtube') {
				$page_cat = "Youtube";
				$page_url = $landing_url."?type=youtube";
			} else if($post->post_type == 'ugc-post-video') {
				$page_cat = "Video";
				$page_url = $landing_url."?type=video";
			} else if($post->post_type == 'ugc-post-photo') {
				$page_cat = "Photo";
				$page_url = $landing_url."?type=photo";
			} else if($post->post_type == 'post') {
				$page_cat = "Article";
				$page_url = $landing_url."?type=article";
			}

			breadcrumbs(array(
							array('type'=>'page','caption'=>$page_cat,'url'=>$page_url),
							array('type'=>'page','caption'=>$post->post_title),
						));
			?>
		</div>
		<div class="ugc-main-segment">
			<div class="attachment-container">
				<?php if($post->post_type == 'ugc-post-youtube'): ?>
					<iframe src="https://www.youtube.com/embed/<?php echo $yid; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
				<?php elseif($post->post_type == 'ugc-post-video'): ?>
					<video controls>
					  <source src="<?php echo $vid; ?>" type="video/mp4">
					Your browser does not support the video tag.
					</video>
				<?php elseif($post->post_type == 'ugc-post-photo'): ?>
					<img src="<?php echo $image; ?>">
				<?php elseif($post->post_type == 'post'): ?>
					<?php echo get_the_post_thumbnail($id); ?>
				<?php endif; ?>
			</div>
			<div class="ugc-head-content">
				<div class="ugc-title">
					<h2><?php echo $post->post_title; ?></h2>
					<p><span id="ugc_view_count"><?php echo $view; ?></span> Views</p>
				</div>
				<?php if(get_option('ugc-enable-user-comment') == 1): ?>
				<div class="ugc-like">
					<div class="ballon" id="ugc_like_count"><?php echo $like; ?> <?php echo ($like == 1) ? 'Like' : 'Likes'; ?></div>
					<button class="<?php echo (!has_like($id)) ? 'trigger-like' : 'on'; ?>"><i class="fa fa-thumbs-up"></i></button>
				</div>
				<?php endif; ?>
			</div>
			<p><?php echo $post->post_content; ?></p>
			
			<div class="ugc-social-media-sharer">
				<button class="socmed small facebook trigger-facebook-share"><i class="fa fa-facebook"></i>&nbsp;&nbsp;Share on Facebook</button>
				<button class="socmed small twitter trigger-twitter-share"><i class="fa fa-twitter"></i>&nbsp;&nbsp;Tweet on Twitter</button>
			</div>

			<?php if($post->post_status == 'publish' and $post->comment_status == 'open' and get_option('ugc-enable-user-comment') == 1): ?>
			<div class="ugc-comment-container ugc-register-box full-left">
				<h3>Leave a Comment</h3>
				<?php if($uid != 0 and isset($user->data->ID)): ?>
					<p>Login as <b><?php echo $user->data->display_name; ?></b></p>
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
							<input type="hidden" name="action" value="ugc_save_comment">
							<input type="hidden" name="post_id" value="<?php echo $id; ?>">
							<div class="ugc-field">
								<div class="ugc-input">
									<textarea  name="ugc-comment" placeholder="Write your comment here" autocomplete="off" ></textarea>
								</div>
							</div>	
							<!--
							<div class="ugc-field">
								<div class="ugc-recaptcha normal" id="recaptcha-register"></div>
							</div>
							-->				
							<div class="ugc-field">
								<button type="submit" class="ugc-submit width-auto ">Submit</button>
								<button type="button" class="ugc-loader width-auto display-none"><i class="fa fa-spinner fa-spin"></i></button>
							</div>
						</form>
					</div>
				<?php  else:  ?>
					<p>Please <a href="">login</a> to leave a comment</p> 
				<?php endif; ?>
				<hr class="red">
				<?php
				$comments = get_comments(array('post_id' => $id,'status'=>'approve','parent'=>0));
				foreach ($comments as $key => $value) { 
					$child = get_comments(array('post_id' => $id,'status'=>'approve','parent'=>$value->comment_ID));
				?>

				<div class="ugc-comment-item">
					<div class="author">
						<?php echo $value->comment_author; ?>
						<br>
						<span><?php echo date('F, d Y', strtotime($value->comment_date)); ?></span>
					</div>
					<div class="comment-text"><?php echo $value->comment_content; ?></div>
					<?php if(count($child) > 0): ?>
					<div class="child-area">
						<?php foreach ($child as $k => $v) { ?>
						<div class="child-item">
							<div class="author">
								<?php echo $v->comment_author; ?>
								<br>
								<span><?php echo date('F, d Y', strtotime($v->comment_date)); ?></span>
							</div>
							<div class="comment-text"><?php echo $v->comment_content; ?></div>
						</div>
						<?php } ?>
					</div>
					<?php endif; ?>
				</div>

				<?php } ?>

			</div>
			<?php endif; ?>
				
		</div>
		<div class="ugc-sidebar">
			<h2>Most Popular</h2>
			<?php get_top_sidebar(); ?>
		</div>
	</div>
</div>

<?php get_footer();