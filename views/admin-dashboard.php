<?php
$count_video = count_post('video');
$count_youtube = count_post('youtube');
$count_photo = count_post('photo');
$count_total = $count_photo + $count_youtube + $count_video;
?>

<div class="wrap">
	<h1>User Generated Content</h1>
	<h2>Dashboard</h2>

	<div class="panel-number">
		<div class="item">
			<button class="icon blue">
				<span class="dashicons dashicons-format-gallery"></span>
			</button>
			<div class="info">
				<h1><?php echo $count_total; ?></h1>
				<p>Total Contents</p>
			</div>
		</div>
		<div class="item">
			<button class="icon red">
				<span class="dashicons dashicons-format-status"></span>
			</button>
			<div class="info">
				<h1><?php echo $count_photo; ?></h1>
				<p>Total Photos</p>
			</div>
		</div>
		<div class="item">
			<button class="icon green">
				<span class="dashicons dashicons-video-alt2"></span>
			</button>
			<div class="info">
				<h1><?php echo $count_video; ?></h1>
				<p>Total Videos</p>
			</div>
		</div>
		<div class="item">
			<button class="icon orange">
				<span class="dashicons dashicons-video-alt3"></span>
			</button>
			<div class="info">
				<h1><?php echo $count_youtube; ?></h1>
				<p>Total Youtubes</p>
			</div>
		</div>
	</div>



	<div class="table-area-dash" style="padding: 0">
	

	
	
	
	<div class="header-area-table">
		<div class="category-area">
			<div class="title" style="font-weight: bold;">
				Most Liked Contents
			</div>
		</div>
		<div class="search-area">
			<form method="post">
			  	<input type="hidden" name="page" value="my_list_test" />
				<p class="search-box">
					<label class="screen-reader-text" for="search_id-search-input">
					search:</label> 
					<input id="search_id-search-input" type="text" name="s" value="" /> 
					<input id="search-submit" class="button" type="submit" name="" value="search" />
				</p>
			</form>
		</div>
	</div>

	<input type="hidden" id="tabletype" value="voter">
	<input type="hidden" id="parent_id" value="<?php echo $id; ?>">


	</div>

</div>