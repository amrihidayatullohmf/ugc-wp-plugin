<?php

/*
* functions.php
* location : ./
* description : Common functions are stored here
* author : Amri Hidayatulloh
*/

function ugc_forge_mime_type($filename) {
	if(empty($filename)) {
		return '';
	}

	$ext = explode(".", $filename);
	$ext = end($ext);
	$ext = strtolower($ext);

	if($ext == 'jpg' or $ext == "jpeg") {
		return 'image/jpeg';
	} else if($ext == "png") {
		return 'image/png';
	} else if($ext == "gif") {
		return 'image/gif';
	} else if($ext == 'mp4') {
		return 'video/mp4';
	}

	return false;
}

function ugc_upload_file($file_name) {
	$return = array('code'=>500,'msg'=>'Please select an Image !');
	$meta_image = (isset($_FILES[$file_name])) ? $_FILES[$file_name] : NULL;
	$url_image = "";

	if(isset($meta_image)) {
		$save = TRUE;
		$i = 1;
		$wordpress_upload_dir = wp_upload_dir();
		$new_file_path = $wordpress_upload_dir['path'] . '/' . $meta_image['name'];
		$new_file_mime = ugc_forge_mime_type($meta_image['name']);//mime_content_type( $meta_image['tmp_name'] );
			
		if($meta_image['error']) {
			$msg = "Image you tried to upload is corrupted !";
			$save = FALSE;
		}
		if($meta_image['size'] > wp_max_upload_size()) {
			$msg = "Image is too large than expected.";
			$save = FALSE;
		} 
		if(!in_array($new_file_mime, get_allowed_mime_types())) {
			$msg = "File type doesnt allowed";
			$save = FALSE;
		}

		if($save) {
			while( file_exists( $new_file_path ) ) {
				$i++;
				$new_file_path = $wordpress_upload_dir['path'] . '/' .$i."_". str_replace(" ", "_", $meta_image['name']);
			}

			if( move_uploaded_file( $meta_image['tmp_name'], $new_file_path ) ) {
				$upload_id = wp_insert_attachment( array(
														'guid'           => $new_file_path, 
														'post_mime_type' => $new_file_mime,
														'post_title'     => preg_replace( '/\.[^.]+$/', '', $meta_image['name'] ),
														'post_content'   => '',
														'post_status'    => 'inherit'
													), $new_file_path );
								 
				wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $new_file_path ) );
				
				$url_image = $wordpress_upload_dir['url'] . '/' . basename( $new_file_path );
				$return = array('code'=>200,'url'=>$url_image);
			} else {
				$return = array('code'=>500,'msg'=>'Failed to upload your image !');
			}
		} else {
			$return = array('code'=>500,'msg'=>$msg);
		}			 
	}

	return $return;	
}

function show_unconfirm_notif() {
	$uid = (is_user_logged_in()) ? get_current_user_id() : 0;

	if($uid != 0) {
		$activate = get_user_meta($uid,'ugc-account-activated');

		if(isset($activate[0]) and $activate[0] == 0) {
			echo '<div class="ugc-unconfirm-notif">
					<div class="notif-wrap"><i class="fa fa-exclamation-circle"></i>&nbsp;&nbsp;You havent confirm your email Address, please confirm and activate your Account</div>
				  </div>';
		}
	}
}

function count_post($type = 'all',$uid = 0, $state = "") {
	global $wpdb;

	$query = "SELECT ID FROM ".$wpdb->prefix."posts WHERE ";

	if($type == 'all') {
		$query .= " (post_type = 'ugc-post-video' OR post_type = 'ugc-post-youtube' OR post_type = 'ugc-post-photo') ";
	} else {
		$query .= " post_type = 'ugc-post-".strtolower($type)."' "; 
	}

	if($uid != 0) {
		$query .= " AND post_author = ".$uid." ";
	}

	if($state != "") {
		$query .= " AND post_status = '".$state."' ";
	} else {
		$query .= " AND post_status != 'removed' ";
	}

	$wpdb->get_results($query);
	return $wpdb->num_rows;
}

function export_submission_csv() {
	global $wpdb;

	set_time_limit(0);

	$name = sanitize_text_field($_POST['file_name']);
	$state = sanitize_text_field($_POST['state']);
	$type = sanitize_text_field($_POST['type']);

	$name = (empty($name)) ? 'export-submission-'.date('Ymd-His').'.csv' : $name;

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.$name);

	$output = fopen('php://output', 'w');
	$header = array(
						'ID', 
						'User ID', 
						'User Name',
						'User E-Mail',
						'Content Type',
						'Meta Title',
						'Meta Image URL',
						'Meta Description',
						'Total Like',
						'Total View',
						'Total Comment',
						'Video URL',
						'Youtube ID',
						'Status',
						'Submission Date'
					);

	fputcsv($output,$header);

	$where = "(p.post_type = 'ugc-post-photo' OR p.post_type = 'ugc-post-video' OR p.post_type = 'ugc-post-youtube')";
	if($type != 'all') {
		$where = "p.post_type = '".$type."'";
	}

	$status = "(p.post_status = 'draft' OR p.post_status = 'publish')";
	if($state != 'all') {
		$status = "p.post_status = '".$state."'";
	}

	$query = $wpdb->get_results("SELECT p.*, u.ID AS UID, u.display_name, u.user_email FROM ".$wpdb->prefix."posts p LEFT JOIN ".$wpdb->prefix."users u ON u.ID = p.post_author WHERE ".$where." AND ".$status);

	foreach ($query as $key => $value) {
		$metas = get_post_meta($value->ID);
		$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
		$yid = isset($metas['ugc_youtube_id'][0]) ? $metas['ugc_youtube_id'][0] : 0;
		$vid = isset($metas['ugc_video_path'][0]) ? $metas['ugc_video_path'][0] : 0;
		
		$view = get_stat_post($value->ID,'view');
		$like = get_stat_post($value->ID,'like');
		$comment = get_stat_post($value->ID,'comment');

		$row = array(
						$value->ID, 
						$value->UID, 
						$value->display_name,
						$value->user_email,
						$value->post_type,
						$value->post_title,
						$image,
						strip_tags($value->post_content),
						$like,
						$view,
						$comment,
						$vid,
						$yid,
						$value->post_status,
						'="'.$value->post_date.'"'
					);
		fputcsv($output,$row);
	}

	wp_die();
}

function set_stat_post($post_id = 0) {
	global $wpdb;
	return $wpdb->insert($wpdb->prefix."ugc_post_stat",array(
															'post_id' => $post_id,
															'view_count' => 0,
															'like_count' => 0,
															'comment_count' => 0,
															'share_count' => 0,
													   ));
}

function get_stat_post($post_id,$action) {
	global $wpdb;

	$current = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ugc_post_stat WHERE post_id = ".$post_id);

	if(!isset($current->id)) {
		$id = set_stat_post($post_id);
		if(isset($id)) {
			$current = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ugc_post_stat WHERE post_id = ".$post_id);			
		}
	}

	
	if($action == 'view') {
		return $current->view_count;
	} else if($action == 'like') {
		return $current->like_count;
	} else if($action == 'comment') {
		return $current->comment_count;
	} else if($action == 'share') {
		return $current->share_count;
	}

	return 0;
}

function has_like($post_id) {
	global $wpdb;

	$uid = (is_user_logged_in()) ? get_current_user_id() : 0;
	if($uid == 0) {
		return TRUE;
	}
	$check = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ugc_user_action WHERE post_id = ".$post_id." AND user_id = ".$uid." AND action_type = 'like'");
	return (isset($check->id)) ? TRUE : FALSE;
}

function update_stat_post($post_id, $action, $param_action) {
	global $wpdb;

	$current = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ugc_post_stat WHERE post_id = ".$post_id);

	if(!isset($current->id)) {
		$id = set_stat_post($post_id);
		if(isset($id)) {
			$current = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ugc_post_stat WHERE post_id = ".$post_id);			
		}
	}

	$data = array();
	$count = 0;
	
	if($action == 'view') {
		$count = $current->view_count+1;
		$data = array('view_count'=>$count);
	} else if($action == 'like') {
		$count = $current->like_count+1;
		$data = array('like_count'=>$count);
	} else if($action == 'comment') {
		$count = $current->comment_count+1;
		$data = array('comment_count'=>$count);
	} else if($action == 'share') {
		$count = $current->share_count+1;
		$data = array('share_count'=>$count);
	}

	$set = $wpdb->update($wpdb->prefix."ugc_post_stat",$data,array('post_id'=>$post_id));

	if(isset($set)) {
		$wpdb->insert($wpdb->prefix."ugc_user_action",$param_action);
		return $count;
	}

	return false;
}

function linkifyYouTubeURLs($url) {
	$url = preg_replace('~(?#!js YouTubeId Rev:20160125_1800)
	        # Match non-linked youtube URL in the wild. (Rev:20130823)
	        https?://          # Required scheme. Either http or https.
	        (?:[0-9A-Z-]+\.)?  # Optional subdomain.
	        (?:                # Group host alternatives.
	          youtu\.be/       # Either youtu.be,
	        | youtube          # or youtube.com or
	          (?:-nocookie)?   # youtube-nocookie.com
	          \.com            # followed by
	          \S*?             # Allow anything up to VIDEO_ID,
	          [^\w\s-]         # but char before ID is non-ID char.
	        )                  # End host alternatives.
	        ([\w-]{11})        # $1: VIDEO_ID is exactly 11 chars.
	        (?=[^\w-]|$)       # Assert next char is non-ID or EOS.
	        (?!                # Assert URL is not pre-linked.
	          [?=&+%\w.-]*     # Allow URL (query) remainder.
	          (?:              # Group pre-linked alternatives.
	            [\'"][^<>]*>   # Either inside a start tag,
	          | </a>           # or inside <a> element text contents.
	          )                # End recognized pre-linked alts.
	        )                  # End negative lookahead assertion.
	        [?=&+%\w.-]*       # Consume any URL (query) remainder.
	        ~ix', '$1',
	        $url);
	return $url;
}

function url_get_contents($Url) {
    if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function set_preoptions($options,$preoptions) {
	foreach ($options as $key => $value) {
		if(isset($preoptions[$key])) {
			$preoptions[$key] = $value;
		}
	}
	return $preoptions;
}

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}


function pagination($page = 1, $total_page = 0, $margin_display = 3) {
	$first = 1;
	$last = $total_page;
	$prev = ($page > 1) ? $page - 1 : NULL;
	$next = ($page < $total_page) ? $page + 1 : NULL;
		  	
	$start_endpoint = $page - $margin_display;
	$end_endpoint = $page + $margin_display;

	if($start_endpoint <= 0 and $end_endpoint <= $total_page) {
		$start_endpoint = 1;
		$end_endpoint = ($page + ($margin_display + ($margin_display - $page)));
	} else if($end_endpoint > $total_page and $start_endpoint >= 1) {
		$start_endpoint = ($page - ($margin_display + ($margin_display - ($total_page - ($page-1)))));
		$end_endpoint = $total_page;
	} else if($start_endpoint >= 1 and $end_endpoint <= $total_page) {
		$start_endpoint = $page - $margin_display;
		$end_endpoint = $page + $margin_display;
	} else {
		$start_endpoint = 1;
		$end_endpoint = $total_page;
	}

	return array(
				'page' => $page,
				'first' => $first,
				'last' => $last,
				'prev' => $prev,
				'next' => $next,
				'endpoint' => array($start_endpoint,$end_endpoint)
		   );
}

function render_submissions($options = array()) {
	global $wpdb;


	$preoptions = set_preoptions($options,array(
											'page' => 1,
											'max' => 20,
											'post_type' => array('ugc-post-video','ugc-post-photo','ugc-post-youtube'),
											'user_id' => 0,
											'post_status' => array('draft','publish'),
											'orderby' => 'date',
											'pagination' => TRUE,
											'upload_button' => FALSE,
											'enable_delete' => FALSE
										  ));

	$total_item_per_row = 4;

	$wp_options = array(
					'numberposts' => $preoptions['max'],
					'paged' => $preoptions['page'],
					'post_type' => $preoptions['post_type'],
					'post_status' => $preoptions['post_status'],
					'orderby' => $preoptions['orderby']
				  );

	if($preoptions['user_id'] != 0) {
		$wp_options['author'] = $preoptions['user_id'];
	}

	$detail_url = get_permalink(get_option('ugc-detail-pageid'));

	$offset = 0;
	$limit = $wp_options['numberposts'];

	if($wp_options['paged'] > 1) {
		$offset = ($wp_options['paged'] - 1) * $limit;
	}

	$i = 0;

	$types = array();
	foreach ($wp_options['post_type'] as $key => $value) {
		$types[] = ' p.post_type = "'.$value.'" ';
	}
	$types = implode(" OR ", $types);
	$statuses = array();
	foreach ($wp_options['post_status'] as $key => $value) {
		$statuses[] = ' p.post_status = "'.$value.'" ';
	}
	$statuses = implode(" OR ", $statuses);
	$userpost = (isset($wp_options['author'])) ? ' AND p.post_author = '.$wp_options['author'].' ' : '';
	$orderby = (isset($wp_options['orderby']) and $wp_options['orderby'] == 'date') ? ' ORDER BY p.post_date DESC ' : ' ORDER BY s.view_count DESC, s.comment_count DESC, s.like_count DESC ';

	$wpdb->get_results("SELECT p.ID FROM ".$wpdb->prefix."posts p WHERE (".$statuses.") AND (".$types.") ".$userpost);
	//$posts = get_posts($wp_options);
	$posts = $wpdb->get_results("SELECT p.*,s.view_count,s.like_count,s.comment_count FROM ".$wpdb->prefix."posts p LEFT JOIN ".$wpdb->prefix."ugc_post_stat s ON p.ID = s.post_id WHERE (".$statuses.") AND (".$types.") ".$userpost." ".$orderby." LIMIT ".$offset.",".$limit);

	$total_post = $wpdb->num_rows;
	$total_page = ceil($total_post / $wp_options['numberposts' ]);

	$pagination = pagination($wp_options['paged'],$total_page);

	$start_endpoint = $pagination['endpoint'][0];
	$end_endpoint = $pagination['endpoint'][1];
	$page_type = $options['page_type'];

	?>
	<div class="ugc-post-container">
		<?php if($preoptions['upload_button']) { $i++; ?>
		<button class="ugc-upload"><i class="fa fa-plus"></i><br>Upload New</button>
		<?php } ?>
		<?php 
		foreach($posts as $key => $value) { 
			$type = ucwords(str_replace('ugc-post-', '', $value->post_type));
			$content = substr(strip_tags($value->post_content), 0,70);
			$metas = get_post_meta($value->ID);
			$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
			$i++;
		?>
		<a href="<?php echo $detail_url."?id=".$value->ID; ?>">
			<div class="ugc-item"> 
				<div class="image-area maintainratio">
					<?php if($value->post_status == 'draft') { ?>
					<div class="flag">On Review</div>
					<?php } ?>
					<?php if($preoptions['enable_delete']) { ?>
					<div class="button-area"><button class="red trigger-remove-post" data-id="<?php echo $value->ID; ?>"><i class="fa fa-trash"></i></button></div>
					<?php } ?>
					
					<?php
					if($value->post_type == 'post') {
						echo get_the_post_thumbnail($value->ID);
					} else {
						echo '<img src="'.$image.'">';
					}
					?>

				</div>
				<div class="text-area">
					<div class="stat-area">
						<div><i class="fa fa-eye"></i> <?php echo (!isset($value->view_count)) ? 0 : $value->view_count; ?></div>
						<div><i class="fa fa-thumbs-up"></i> <?php echo (!isset($value->like_count)) ? 0 : $value->like_count; ?></div>
						<div><i class="fa fa-comment"></i> <?php echo (!isset($value->comment_count)) ? 0 : $value->comment_count; ?></div>
					</div>
					<div class="title"><?php echo $value->post_title; ?></div>
					<div class="label"><?php echo $type; ?></div>
					<!--<div class="desc"><?php echo $content; ?></div>-->
				</div>
			</div>
		</a>
		<?php 
		}
		
		if($i % $total_item_per_row > 0) {
			for($j = 1; $j <= ($i % $total_item_per_row); $j++) { 
		?>
		<a href="">
			<div class="ugc-item clear"></div>
		</a> 
		<?php } } ?>
	</div>

	<div class="ugc-pagination"> 
		<?php if($start_endpoint < $end_endpoint) { ?>
		<ul>
			<a href="?type=<?php echo $page_type; ?>&nav=<?php echo $pagination['first']; ?>"><li>First</li></a>
			<?php if(isset($pagination['prev'])) { ?>
			<a href="?type=<?php echo $page_type; ?>&nav=<?php echo $pagination['prev']; ?>"><li>Prev</li></a>
			<?php } ?>

			<?php for($i = $start_endpoint; $i <= $end_endpoint; $i++) { ?>
			<a href="?type=<?php echo $page_type; ?>&nav=<?php echo $i; ?>"><li <?php if($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></li></a>
			<?php } ?>

			<?php if(isset($pagination['next'])) { ?>
			<a href="?type=<?php echo $page_type; ?>&nav=<?php echo $pagination['next']; ?>"><li>Next</li></a>
			<?php } ?>
			<a href="?type=<?php echo $page_type; ?>&nav=<?php echo $pagination['last']; ?>"><li>Last</li></a>
		</ul>
		<?php } ?>
	</div>
	<?php 
}

function get_top_sidebar() {
	global $wpdb;
	//ORDER BY s.comment_count DESC, s.like_count DESC, view_count DESC
	$posts = $wpdb->get_results("SELECT p.*, s.* FROM ".$wpdb->prefix."posts p LEFT JOIN ".$wpdb->prefix."ugc_post_stat s ON p.ID = s.post_id WHERE p.post_status = 'publish' AND p.post_type != 'page' ORDER BY rand() LIMIT 0,5");
	$detail_url = get_permalink(get_option('ugc-detail-pageid'));

	$i = 0;
	foreach ($posts as $key => $value) {
		$metas = get_post_meta($value->ID);
		$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
		$user = get_user_by('id',$value->post_author);
		$i++;		
	?>
	<a href="<?php echo $detail_url."?id=".$value->ID; ?>">
	<div class="item-highlight">
		<div class="img-area">
			<?php
			if($value->post_type == 'post') {
				echo get_the_post_thumbnail($value->ID);
			} else {
				echo '<img src="'.$image.'">';
			}
			?>
			<div class="rank"><?php echo $i; ?></div>
		</div>
		<div class="text-area">
			<div class="post-name"><?php echo $value->post_title; ?></div>
			<div class="post-author"><?php echo $user->data->display_name; ?></div>
		</div>
	</div>
	</a>
	<?php 	
	}
}

function login_stat() {
	$uid = (is_user_logged_in()) ? get_current_user_id() : 0;
	if($uid == 0) {
		$login_url = get_permalink(get_option('ugc-login-pageid'));
		$register_url = get_permalink(get_option('ugc-register-pageid'));
		echo '<div class="header-stat"><a href="'.$login_url.'">Login</a><a href="'.$register_url.'">Register</a></div>';
	} else {
		$profile_url = get_permalink(get_option('ugc-profile-pageid'));
		$user = get_user_by('id',$uid);
		echo '<div class="header-stat"><a href="'.$profile_url.'">Hi, '.$user->data->display_name.'</a></div>';
	}
}

function slides_shortcode($attr,$content=null) {
	//print_r($attr);
	if(!isset($attr['post_type'])) {
		$attr['post_type'] = 'image';
	}
	?>
	<div  class="ugc-segment <?php echo (isset($attr['no_border'])) ? 'no-border' : ''; ?>" >
		<?php if(isset($attr['title'])): ?>
		<div class="ugc-segment-title"><?php echo $attr['title']; ?></div>
		<?php endif; ?>
		<div class="ugc-segment-content ugc-desktop">
			<div class="ugc-slider ugc-num-<?php echo (isset($attr['display'])) ? $attr['display'] : 1; ?>" data-display="<?php echo (isset($attr['display'])) ? $attr['display'] : 1; ?>">
				<?php do_shortcode($content);  ?>
				<?php 
				if($attr['post_type'] != 'image') {
					do_shortcode($content);
				}
				?>
			</div>
		</div>
		<div class="ugc-segment-content ugc-mobile ">
			<div class="ugc-slider ugc-num-1 <?php echo $attr['post_type']; ?>" data-display="1">
				<?php do_shortcode($content);  ?>
			</div>
		</div>
	</div>
	<?php
}

function slides_item_shortcode($attr,$content=null) {
	if(isset($attr['src'])) {
	?>
		<div class="slide-item" data-link="<?php echo $link; ?>"><img src="<?php echo $attr['src']; ?>"></div>
	
	<?php
	} else if(isset($attr['slug'])) {
		$args = array(
				  'name'        => $attr['slug'],
				  'post_type'   => 'post',
				  'post_status' => 'publish',
				  'numberposts' => 1
				);
		$my_posts = get_posts($args);
		$id = $my_posts[0]->ID;
		$detail_url = get_permalink(get_option('ugc-detail-pageid'));
		$link = $detail_url.'?id='.$id;
		$cats = get_the_category($id);

		?>
		<div class="slide-item" data-link="<?php echo $link; ?>">
			
				<?php echo get_the_post_thumbnail($id); ?>
				<div class="text-area">
					<?php foreach ($cats as $key => $value) { ?>
					<button><?php echo $value->name; ?></button>
					<?php } ?>
					<div class="title"><?php echo $my_posts[0]->post_title; ?></div>
				</div>		
			
		</div>
		<?php
	}
}

function slides_item_shortcode_old($attr,$content=null) {
	if(isset($attr['src'])) {
	?>
		<div class="slide-item"><a href="<?php echo $attr['link']; ?>"><img src="<?php echo $attr['src']; ?>"></a></div>
	
	<?php
	} else if(isset($attr['slug'])) {
		$args = array(
				  'name'        => $attr['slug'],
				  'post_type'   => 'post',
				  'post_status' => 'publish',
				  'numberposts' => 1
				);
		$my_posts = get_posts($args);
		$id = $my_posts[0]->ID;
		$detail_url = get_permalink(get_option('ugc-detail-pageid'));
		$link = $detail_url.'?id='.$id;
		$cats = get_the_category($id);

		?>
		<div class="slide-item">
			<a href="<?php echo $link; ?>">
				<?php echo get_the_post_thumbnail($id); ?>
				<div class="text-area">
					<?php foreach ($cats as $key => $value) { ?>
					<button><?php echo $value->name; ?></button>
					<?php } ?>
					<div class="title"><?php echo $my_posts[0]->post_title; ?></div>
				</div>		
			</a>
		</div>
		<?php
	}
}

function playlist_shortcode($attr,$content=null) {
	global $wpdb;
	$len = (isset($attr['display'])) ? $attr['display'] : 5;
	$embed = (isset($attr['embed'])) ? $attr['embed'] : FALSE;


	if($embed == FALSE) {

		$post_types = array();

		if(isset($attr['post_type'])) {
			$types = explode(",",$attr['post_type']);

			if(in_array("post", $types)) {
				$post_types[] = 'p.post_type = "post"';
			}  
			if(in_array("photo", $types)) {
				$post_types[] = 'p.post_type = "ugc-post-photo"';
			}
			if(in_array("video", $types)) {
				$post_types[] = 'p.post_type = "ugc-post-video"';
			}
			if(in_array("youtube", $types)) {
				$post_types[] = 'p.post_type = "ugc-post-youtube"';
			}		
		} 

		$post_type_str = (count($post_types) > 0) ? " AND (".implode(" OR ", $post_types).") " : "";
		$order = (isset($attr['order_by']) and $attr['order_by'] == 'date') ? "p.post_date DESC" : "s.comment_count DESC, s.like_count DESC, view_count DESC";
		$posts = $wpdb->get_results("SELECT p.*, s.* FROM ".$wpdb->prefix."posts p LEFT JOIN ".$wpdb->prefix."ugc_post_stat s ON p.ID = s.post_id WHERE p.post_status = 'publish' ".$post_type_str ." ORDER BY ".$order." LIMIT 0,".$len);
		$detail_url = get_permalink(get_option('ugc-detail-pageid'));
		?>
		<div class="ugc-segment <?php echo (isset($attr['no_border'])) ? 'no-border' : ''; ?>">
			<?php if(isset($attr['title'])): ?>
			<div class="ugc-segment-title"><?php echo $attr['title']; ?></div>
			<?php endif; ?>
			<div class="ugc-segment-content">
				<div class="ugc-playlist-box">
					<div class="ugc-main-area">
						<button class="loader playlist-loader"><i class="fa fa-spinner fa-spin"></i></button>
						<div>
						<?php
						$first = (isset($posts[0])) ?$posts[0] : NULL;

						if($first != NULL) {
							$metas = get_post_meta($first->ID);
							if($first->post_type == 'ugc-post-youtube') {
								$yid = isset($metas['ugc_youtube_id'][0]) ? $metas['ugc_youtube_id'][0] : 0;
								?>
								<iframe src="https://www.youtube.com/embed/<?php echo $yid; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
								<?php
							} else if($first->post_type == 'ugc-post-video') {
								$vid = isset($metas['ugc_video_path'][0]) ? $metas['ugc_video_path'][0] : 0;
								?>
								<video controls>
								  <source src="<?php echo $vid; ?>" type="video/mp4">
								Your browser does not support the video tag.
								</video>
								<?php
							} else if($first->post_type == 'ugc-post-photo') {
								$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
								?>
								<div class="ugc-playlist-main-image maintainratio">
									<img src="<?php echo $image; ?>" >
									<div class="text-area">
										<div class="title"><?php echo $first->post_title; ?></div>
										<div class="desc"><?php echo $first->post_content; ?></div>
										<div class="button"><a href="<?php echo $detail_url.'?id='.$first->ID; ?>">View Detail</a></div>
									</div>
								</div>
								<?php
							}
						}
						?>
						</div>
					</div>
					<div class="ugc-lists">
						<?php if(isset($attr['playlist_name'])): ?>
						<div class="ugc-playlist-name"><?php echo $attr['playlist_name']; ?></div>
						<?php endif; ?>
						<div class="ugc-playlist-list">
							<?php 
							$i = 0;
							foreach ($posts as $key => $value) { 
								$metas = get_post_meta($value->ID);
								$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
								$user = get_user_by('id',$value->post_author);
							?>
							<div class="ucg-item-list playlist-item <?php if($i == 0) echo 'active'; ?>" data-id="<?php echo $value->ID; ?>" data-embed="no">
								<div class="img-area">
									<img src="<?php echo $image; ?>">
								</div>
								<div class="text-area">
									<?php echo $value->post_title; ?><br>
									<span><?php echo $user->data->display_name; ?></span>
									<div class="stat-area">
										<div><i class="fa fa-eye"></i> <?php echo $value->view_count; ?></div>
										<div><i class="fa fa-thumbs-up"></i> <?php echo $value->like_count; ?></div>
										<div><i class="fa fa-comment"></i> <?php echo $value->comment_count; ?></div>
									</div>
								</div>
							</div>
							<?php $i++; } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php

	} else {
		$items = explode("|", $attr['items']);
		$youtubes = array();
		$api_key = get_option('ugc-youtube-key');

		foreach ($items as $k => $v) {
			$api_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails%2Cstatistics&id=' . $v . '&key=' . $api_key;
			$youtubes[] = file_get_contents($api_url);

		}

		?>
		<div class="ugc-segment <?php echo (isset($attr['no_border'])) ? 'no-border' : ''; ?>">
			<?php if(isset($attr['title'])): ?>
			<div class="ugc-segment-title"><?php echo $attr['title']; ?></div>
			<?php endif; ?>
			<div class="ugc-segment-content">
				<div class="ugc-playlist-box">
					<div class="ugc-main-area">
						<button class="loader playlist-loader"><i class="fa fa-spinner fa-spin"></i></button>
						<div>
							<iframe src="https://www.youtube.com/embed/<?php echo $items[0]; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
						</div>
					</div>
					<div class="ugc-lists">
						<?php if(isset($attr['playlist_name'])): ?>
						<div class="ugc-playlist-name"><?php echo $attr['playlist_name']; ?></div>
						<?php endif; ?>
						<div class="ugc-playlist-list">
							<?php 
							$i = 0;
							foreach ($youtubes as $key => $value) { 
								$json = json_decode($value);
								if(isset($json->pageInfo->totalResults)) {
							?>
							<div class="ucg-item-list playlist-item <?php if($i == 0) echo 'active'; ?>" data-id="<?php echo $json->items[0]->id; ?>" data-embed="yes">
								<div class="img-area">
									<img src="<?php echo $json->items[0]->snippet->thumbnails->high->url; ?>">
								</div>
								<div class="text-area">
									<?php echo $json->items[0]->snippet->title; ?><br>
									<span><?php echo $json->items[0]->snippet->channelTitle; ?></span>
								</div>
							</div>
							<?php $i++; } } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php

	}
}

function playlistitem_shortcode($attr,$content) {
	$type = (isset($attr['type'])) ? $attr['type'] : '';
	$src = (isst($attr['src'])) ? $attr['src'] : '';
	$name = (isst($attr['name'])) ? $attr['name'] : '';
	$title = (isst($attr['title'])) ? $attr['title'] : '';

	if($type == 'youtube') {
		$yid = linkifyYouTubeURLs($src);
	
	?>
	<div class="ucg-item-list playlist-item playlist-item-embed" data-id="<?php echo $yid; ?>">
		<div class="img-area">
			<img src="https://img.youtube.com/vi/<?php $yid; ?>/hqdefault.jpg">
		</div>
		<div class="text-area">
			<?php echo $title; ?><br>
			<span><?php echo $name; ?></span>
		</div>
	</div>
	<?php 
	}
}

function popular_shortcode($attr,$content=null) {
	global $wpdb;

	$len = (isset($attr['display'])) ? $attr['display'] : 5;
	$landing_url = get_permalink(get_option('ugc-landing-pageid'));
	$detail_url = get_permalink(get_option('ugc-detail-pageid'));
	$posts = array();

	if(isset($attr['post_type']) and $attr['post_type'] != 'embed') {	
		$post_types = array();

		if(isset($attr['post_type'])) {
			$types = explode(",",$attr['post_type']);

			if(in_array("post", $types)) {
				$post_types[] = 'p.post_type = "post"';
			}  
			if(in_array("photo", $types)) {
				$post_types[] = 'p.post_type = "ugc-post-photo"';
			}
			if(in_array("video", $types)) {
				$post_types[] = 'p.post_type = "ugc-post-video"';
			}
			if(in_array("youtube", $types)) {
				$post_types[] = 'p.post_type = "ugc-post-youtube"';
			}		
		} 

		$post_type_str = (count($post_types) > 0) ? " AND (".implode(" OR ", $post_types).") " : "";
		$order = (isset($attr['order_by']) and $attr['order_by'] == 'date') ? "p.post_date DESC" : "s.comment_count DESC, s.like_count DESC, view_count DESC";
		$posts = $wpdb->get_results("SELECT p.*, s.* FROM ".$wpdb->prefix."posts p LEFT JOIN ".$wpdb->prefix."ugc_post_stat s ON p.ID = s.post_id WHERE p.post_status = 'publish' ".$post_type_str ." ORDER BY ".$order." LIMIT 0,".$len);
	} else {
		$post_name = explode("|", $attr['slugs']);
		foreach ($post_name as $key => $value) {
			$post_name[$key] = "'".$value."'";
		}
		$post_name = implode(",", $post_name);
		$posts = $wpdb->get_results("SELECT p.*, s.* FROM ".$wpdb->prefix."posts p LEFT JOIN ".$wpdb->prefix."ugc_post_stat s ON p.ID = s.post_id WHERE p.post_status = 'publish' AND p.post_name IN (".$post_name.")");
	}

	
	?>
	<div class="ugc-segment ugc-desktop-flex <?php echo (isset($attr['no_border'])) ? 'no-border' : ''; ?>">
		<?php if(isset($attr['title'])): ?>
		<div class="ugc-segment-title"><?php echo $attr['title']; ?></div>
		<?php endif; ?>
		<div class="ugc-segment-button"><a href="<?php echo $landing_url; ?>">View All</a></div>
		<div class="ugc-segment-content">
			<div class="ugc-slider-container">
				<div class="ugc-slider-area">
					<?php 
					foreach ($posts as $key => $value) {
						$metas = get_post_meta($value->ID);
						$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
						$user = get_user_by('id',$value->post_author);


					?>
					<a href="<?php echo $detail_url."?id=".$value->ID; ?>">
						<div class="item-slide">
							<div class="image-area maintainratio">
								<?php
								if($value->post_type == 'post') {
									echo get_the_post_thumbnail($value->ID);
								} else {
									echo '<img src="'.$image.'">';
								}
								?>
							</div>
							<div class="text-area">
								<div class="stat-area">
									<div><i class="fa fa-eye"></i> <?php echo (!isset($value->view_count)) ? 0 : $value->view_count; ?></div>
									<div><i class="fa fa-thumbs-up"></i> <?php echo (!isset($value->like_count)) ? 0 : $value->like_count; ?></div>
									<div><i class="fa fa-comment"></i> <?php echo (!isset($value->comment_count)) ? 0 : $value->comment_count; ?></div>
								</div>
								<?php echo $value->post_title; ?><br><br>
								<span><?php echo $user->data->display_name; ?></span>
							</div>
						</div>
					</a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<div class="ugc-segment ugc-mobile-flex <?php echo (isset($attr['no_border'])) ? 'no-border' : ''; ?>">
		<?php if(isset($attr['title'])): ?>
		<div class="ugc-segment-title"><?php echo $attr['title']; ?></div>
		<?php endif; ?>
		<div class="ugc-segment-button"><a href="<?php echo $landing_url; ?>">View All</a></div>
		<div class="ugc-segment-content">
			<div class="ugc-slider-container">
				<div class="ugc-slider-area-mobile">
					<?php 
					foreach ($posts as $key => $value) {
						$metas = get_post_meta($value->ID);
						$image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
						$user = get_user_by('id',$value->post_author);
					?>
					<a href="<?php echo $detail_url."?id=".$value->ID; ?>">
						<div class="item-slide">
							<div class="image-area">
								<?php
								if($value->post_type == 'post') {
									echo get_the_post_thumbnail($value->ID);
								} else {
									echo '<img src="'.$image.'">';
								}
								?>
							</div>
							<div class="text-area">
								<?php echo $value->post_title; ?><br><br>
								<span><?php echo $user->data->display_name; ?></span>
							</div>
						</div>
					</a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function htmlembed_shortcode($attr,$content=null) {
	//print_r($attr);
	?>
	<div class="ugc-segment <?php echo (isset($attr['no_border'])) ? 'no-border' : ''; ?>">
		<?php if(isset($attr['title'])): ?>
		<div class="ugc-segment-title"><?php echo $attr['title']; ?></div>
		<?php endif; ?>
		<div class="ugc-segment-content">
			<?php echo $content; ?>
		</div>
	</div>
	<?php
	
}

function get_mailchimp_list() {
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
	curl_close($ch);

	$lists = json_decode($result);
	$name_lists = array();

	if(count($lists) > 0 and isset($lists->lists[0]->id)) {
		foreach ($lists->lists as $key => $value) {
			$name_lists[] = array(
								'id' => $value->id,
								'name' => $value->name,
								'count' => $value->stats->member_count
							);
		}
	}

	return $name_lists;

}

function count_submission($state,$keyword) {
	global $wpdb;
		
	$status = "(p.post_type = 'ugc-post-video' OR p.post_type = 'ugc-post-youtube' OR p.post_type = 'ugc-post-photo')";
	$search = "";

	if($state != 'all') {
		$status = "post_status = '".$state."'";			
	}

	if(!empty($keyword) and isset($keyword)) {
		$search = " AND (p.post_title LIKE '%".$keyword."%' OR p.post_content LIKE '%".$keyword."%' OR u.display_name LIKE '%".$keyword."%' OR u.ID LIKE '%".$keyword."%' OR u.user_email LIKE '%".$keyword."%' OR p.post_type LIKE '%".$keyword."%') ";
	}

	$wpdb->get_results("SELECT u.ID AS UID, u.user_email, u.display_name, p.*, s.* FROM ".$wpdb->prefix."posts p, ".$wpdb->prefix."users u, ".$wpdb->prefix."ugc_post_stat s WHERE p.post_author = u.ID AND s.post_id = p.ID AND ".$status." AND (post_status = 'draft' OR post_status = 'publish') ".$search);
	return $wpdb->num_rows;
}

function get_like_count($parent_id) {
	global $wpdb;

	$wpdb->get_results("SELECT id FROM ".$wpdb->prefix."ugc_user_action WHERE action_type = 'like' AND status = 1 AND post_id = '".$parent_id."'");
	return $wpdb->num_rows;
}

function get_post_detail($post_id) {
	global $wpdb;
	return $wpdb->get_row("SELECT u.ID AS UID, u.user_email, u.display_name, p.*, s.* FROM ".$wpdb->prefix."posts p, ".$wpdb->prefix."users u, ".$wpdb->prefix."ugc_post_stat s WHERE p.post_author = u.ID AND s.post_id = p.ID AND p.ID = ".$post_id);
}

function breadcrumbs($crumbs = array()) {
	$pages = array();
	$pages[] = '<li><a href="'.site_url().'"><i class="fa fa-home"></i> Homepage</a></li>';

	foreach ($crumbs as $key => $value) {
		if(isset($value['url']) and !empty($value['url'])) {
			$pages[] = '<li><a href="'.$value['url'].'">'.$value['caption'].'</a></li>';
		} else {
			$pages[] = '<li>'.$value['caption'].'</li>';
		}
	}

	$pages_str = implode("<li>&nbsp;/&nbsp;</li>", $pages);

	echo '<ul class="ugc-breadcrumbs">';
	echo $pages_str;
	echo '</ul>';
}




