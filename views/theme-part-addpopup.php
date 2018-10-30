<?php if(is_user_logged_in()) { ?>

<div class="ugc-form-hidden">
<form method="post" enctype="multipart/form-data" id="imageUploader" action="<?php echo admin_url('admin-ajax.php'); ?>">
	<input type="hidden" name="action" value="ugc_uploadImageHandler">
	<input type="file" name="meta_image" accept=".jpg,.png,.gif" id="ugcmetaimage">
</form>
</div>

<div class="ugc-black-bg" id="ugc-black-bg"></div>
<div class="ugc-popup-add" id="ugc-popup-add">
	<div class="ugc-popup-wrapper">
		<div class="ugc-popup-box">
			<div class="error-notif" id="error-notif">Lorem Ipsum Dolor sit Amet</div>
			<button type="button" class="retrieve-loader" id="retrieve-loader">
				<i class="fa fa-spinner fa-spin"></i>
				<span id="percentage"></span>
			</button>
			<div class="header-area" id="popheaderarea">
				<div class="tab-area">
					<?php if(get_option('ugc-enable-upload-photo') == 1) { ?><button type="button" class="ugc-tab-action active" data-rel="#ugc-photo">Photo</button><?php } ?>
					<?php if(get_option('ugc-enable-upload-video') == 1) { ?><button type="button" class="ugc-tab-action" data-rel="#ugc-video">Video</button><?php } ?>
					<?php if(get_option('ugc-enable-upload-youtube') == 1) { ?><button type="button" class="ugc-tab-action" data-rel="#ugc-youtube">Youtube</button><?php } ?>
				</div>
				<button class="ugc-close-popup" type="button">&times;</button>
			</div>
			<div class="form-area-content">
				<?php if(get_option('ugc-enable-upload-photo') == 1) { ?>
				<div class="ugc-form-segment active" id="ugc-photo">
					<form enctype="multipart/form-data" action="<?php echo site_url(); ?>/wp-admin/admin-ajax.php" method="post" id="ugc-form-photo">
						<input type="hidden" name="id" id="meta_id">
						<input type="hidden" class="skip-clear" name="action" value="ugc_submissionSaveHandler">
						<input type="hidden" class="skip-clear" name="type" value="photo">
						
						<div class="ugc-field">
							<div class="ugc-image-uploader" id="meta_image">
								<button type="button"><span class="dashicons dashicons-upload"></span></button>
								<img src="<?php echo plugins_url( 'ugc/images/dummy-image-square.jpg'); ?>" alt="<?php echo plugins_url( 'ugc/images/dummy-image-square.jpg'); ?>">
								<input type="hidden" name="meta_image">
							</div>
						</div>
						<div class="ugc-field">
							<input type="text" name="meta_title"  placeholder="Title">
						</div>
						<div class="ugc-field">
							<textarea name="meta_description"  placeholder="Short Description"></textarea>
						</div>
						<div class="ugc-field">
							<button type="submit" class="ugc-submit-submission" data-id="#ugc-form-photo">SUBMIT</button>
						</div>
					</form>
				</div>
				<?php } ?>
				
				<?php if(get_option('ugc-enable-upload-video') == 1) { ?>
				<div class="ugc-form-segment" id="ugc-video">
					<form enctype="multipart/form-data" action="<?php echo site_url(); ?>/wp-admin/admin-ajax.php" method="post" id="ugc-form-video">
						<input type="hidden" name="meta_id">
						<input type="hidden" class="skip-clear" name="action" value="ugc_submissionSaveHandler">
						<input type="hidden" class="skip-clear" name="type" value="video">
						<div class="ugc-field video-field">
							<input type="file" name="videopath"   class="special">
							<div class="ugc-helper">Video file with MP4 extentions (max 50 MB)</div>
						</div>
						<div class="ugc-field">
							<div class="ugc-image-uploader" id="meta_video_cover">
								<button type="button"><span class="dashicons dashicons-upload"></span></button>
								<img src="<?php echo plugins_url( 'ugc/images/dummy-image-square.jpg'); ?>" alt="<?php echo plugins_url( 'ugc/images/dummy-image-square.jpg'); ?>">
								<input type="hidden" name="meta_image">
							</div>
						</div>
						<div class="ugc-field">
							<input type="text" name="meta_title" placeholder="Title" >
						</div>
						<div class="ugc-field">
							<textarea name="meta_description" placeholder="Short Description" ></textarea>
						</div>
						<div class="ugc-field">
							<button type="submit" class="ugc-submit-submission" data-id="#ugc-form-video">SUBMIT</button>
						</div>
					</form>
				</div>
				<?php } ?>

				<?php if(get_option('ugc-enable-upload-youtube') == 1) { ?>
				<div class="ugc-form-segment" id="ugc-youtube">
					<form enctype="multipart/form-data" action="<?php echo site_url(); ?>/wp-admin/admin-ajax.php" method="post" id="ugc-form-youtube">
						<input type="hidden" name="meta_id" id="meta_id_youtube">
						<input type="hidden" class="skip-clear" name="action" value="ugc_submissionSaveHandler">
						<input type="hidden" name="youtube_id" value="" id="youtube_id">
						<input type="hidden" class="skip-clear" name="type" value="youtube">
						<div class="ugc-field">
							<input type="text" name="url"  placeholder="https://youtube.com/watch?v=aZsh83dN" class="special" id="youtube_url">
							<div class="ugc-helper">URL to your youtube video</div>
						</div>
						<div class="ugc-field">
							<div class="ugc-image-uploader" id="youtube_image">
								<button type="button"><span class="dashicons dashicons-upload"></span></button>
								<img src="<?php echo plugins_url( 'ugc/images/dummy-image-square.jpg'); ?>" alt="<?php echo plugins_url( 'ugc/images/dummy-image-square.jpg'); ?>">
								<input type="hidden" name="meta_image">
							</div>
						</div>
						<div class="ugc-field">
							<input type="text" name="meta_title" placeholder="Title" id="youtube_title">
						</div>
						<div class="ugc-field">
							<textarea name="meta_description" placeholder="Short Description" id="youtube_description"></textarea>
						</div>
						<div class="ugc-field">
							<button type="submit" class="ugc-submit-submission" data-id="#ugc-form-youtube">SUBMIT</button>
						</div>
					</form>
				</div>
				<?php } ?>

				<div class="ugc-form-segment" id="ugc-success">
					<div class="success-area">
						<button class="icon"><span class="dashicons dashicons-yes"></span></button>
						<br>
						<div class="title">
							Yeay!
						</div>
						<div class="message" id="ugc-submission-success-message"></div>
						<div class="button-area">
							<a href="<?php echo get_permalink(get_option('biugc-own-page-id')); ?>">
								<button>Go To My Submission</button>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>