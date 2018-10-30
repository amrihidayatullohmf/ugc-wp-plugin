<div class="blackbg" id="blackbg"></div>

<div class="profile-pop progress" id="sending-progress">
	<div class="box">
		<div class="cover-area">
			<div class="big name"><span id="counter">-/-</span> E-mail sent</div>
		</div>
		<div class="bar" id="basebar">
			<div class="progress" id="progressbar"></div>
		</div>
		<div class="button-area">
			<div class="log" id="sendlog">Preparing...</div>
		</div>
	</div>
</div>

<div class="wrap">

	<?php
	$k = "";
	if($_POST) {
		$k = $_POST['s'];
		$table->set_keyword($k); 
	}

	if(isset($_GET['orderby']) and isset($_GET['order'])) {
		$table->set_sortparam($_GET['orderby'],$_GET['order']);	
	}

	$status = (isset($_GET['status'])) ? $_GET['status'] : 'all';
	$totalqueue = count_post('all',0, "draft");
	$totalapprove = count_post('all',0, "publish");

	$table->set_filter(array('state'=>$status));
	$table->get_current_page();
	$table->prepare_items();
	?>

	<h1 class="wp-heading-inline">User Submission</h1>
	
	<div class="header-area-table">
		<div class="category-area">
			<a <?php if($status == 'all') echo 'class="active"'; ?> href="<?php echo site_url(); ?>/wp-admin/admin.php?page=ugc-submission&status=all">All (<?php echo ($totalqueue+$totalapprove); ?>)</a>
			&nbsp;|&nbsp;
			<a <?php if($status == 'publish') echo 'class="active"'; ?> href="<?php echo site_url(); ?>/wp-admin/admin.php?page=ugc-submission&status=publish">Approved List (<?php echo $totalapprove; ?>)</a>
			&nbsp;|&nbsp;
			<a <?php if($status == 'draft') echo 'class="active"'; ?>  href="<?php echo site_url(); ?>/wp-admin/admin.php?page=ugc-submission&status=draft">Pending List (<?php echo $totalqueue; ?>)</a>
		</div>
		<div class="search-area">
			<form method="post" action="">
			  	<input type="hidden" name="page" value="my_list_test" />
				<p class="search-box">
					<label class="screen-reader-text" for="search_id-search-input">
					search:</label> 
					<input id="search_id-search-input" type="text" name="s" value="<?php echo $k; ?>" /> 
					<input id="search-submit" class="button" type="submit" name="" value="search" />
				</p>
			</form>
		</div>
	</div>

	<input type="hidden" id="tabletype" value="submission">

	<?php 
	$table->display(); 
	?>


</div>