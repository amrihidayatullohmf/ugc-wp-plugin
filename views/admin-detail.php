<?php
require_once( ABSPATH . 'wp-admin/includes/image.php' );

$save = NULL; 
$msg = ""; 

if($_POST and isset($_POST['meta_id'])) {
	$id = sanitize_text_field($_POST['meta_id']);
	$meta_title = sanitize_text_field($_POST['meta_title']);
	$meta_description = sanitize_text_field($_POST['meta_description']);
	$meta_image = (isset($_FILES['meta_image'])) ? $_FILES['meta_image'] : NULL;
	$url_image = "";

	$data = array(
			'ID' => $id,
			'post_title' => $meta_title,
			'post_content' => $meta_description,
			
			);

	if(isset($meta_image)) {
		$upload = ugc_upload_file('meta_image');
		if($upload['code'] == 200) {
			$url_image = $upload['url'];		
		}
	}

	$update = wp_update_post($data);
	
	if(is_wp_error($update)) {
		$msg = "Failed to save your changes, try again later !";
		$save = FALSE;
	} else {
		update_post_meta($id,'ugc_meta_image',$url_image);
		$msg = "Your changes have been saved, Thank you";
		$save = TRUE;	
	}
}

$post = get_post_detail($_GET['id']);
$metas = get_post_meta($post->ID);
$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
$yid = isset($metas['ugc_youtube_id'][0]) ? $metas['ugc_youtube_id'][0] : 0;
$vid = isset($metas['ugc_video_path'][0]) ? $metas['ugc_video_path'][0] : 0;

$url = $image;

if($post->post_type == 'ugc-post-youtube') {
	$url = "https://youtube.com/watch?v=".$yid;
} else if($post->post_type == 'ugc-post-video') {
	$url = $vid;
}



?>

<div class="wrap">
	<a href="<?php echo site_url(); ?>/wp-admin/admin.php?page=ugc-submission"><button class="common">&larr; Back to Submission List</button></a>
	<br>
	<h1 class="wp-heading-inline">Content Detail</h1>
	<br>

	<?php if(isset($save)) { ?>
	<div class="updated notice notice-warning is-dismissible">
		<p><?php echo $msg; ?></p>
	</div>
	<?php } ?>

	<form action="" method="post" enctype="multipart/form-data">
		<input type="hidden" name="meta_id" value="<?php echo $post->ID; ?>">
		<table class="form-table">
			<tr>
				<th scope="row">URL</th>
				<td>
					<input type="text" name="youtubekey" class="regular-text" value="<?php echo $url; ?>" style="width: 60%" readonly>
				</td>
			</tr>
			<tr>
				<th scope="row">Post Slug</th>
				<td>
					<input type="text" name="slug" class="regular-text" value="<?php echo $post->post_name; ?>" style="width: 60%" readonly>
				</td>
			</tr>
			<?php if($post->post_type == 'ugc-post-youtube' || $post->post_type == 'ugc-post-video') { ?>
			<tr>
				<th scope="row">Preview</th>
				<td>
					<?php if($post->post_type == 'ugc-post-youtube'): ?>
						<iframe src="https://www.youtube.com/embed/<?php echo $yid; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
					<?php elseif($post->post_type == 'ugc-post-video'): ?>
						<video controls>
						  <source src="<?php echo $vid; ?>" type="video/mp4">
						Your browser does not support the video tag.
						</video>
					
					<?php endif; ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">Title</th>
				<td>
					<input type="text" name="meta_title" class="regular-text" value="<?php echo $post->post_title; ?>" style="width: 60%">
				</td>
			</tr>
			<tr>
				<th scope="row">Image Thumbnail</th>
				<td>
					<img src="<?php echo $image; ?>" width="300">
					<br>
					<input type="file" name="meta_image">
				</td>
			</tr>
			<tr>
				<th scope="row">Snippet</th>
				<td>
					<textarea rows="7" style="width: 60%" name="meta_description"><?php echo $post->post_content; ?></textarea>
				</td>
			</tr>
		</table>
		
		<p class="submit">
			<input type="hidden" name="submit_type" value="general">
			<button class="button button-primary" type="submit">Save Changes</button>
			<?php if($post->post_status == 'draft') { ?>
			<button class="button button-primary show-approve" data-id="<?php echo $post->ID; ?>" type="button">Approve</button>
			<?php } ?>
		</p>

	</form>
</div>