<div class="wrap">

	<h1>Export Submission</h1>

	<form action="<?php echo admin_url("admin-ajax.php?action=ugc_export_submission"); ?>" method="post">
		<input type="hidden" name="action" value="ugc_export_submission">
		<table class="form-table">
			
			<tr>
				<th scope="row">Filename</th>
				<td>
					<input type="text" name="file_name" class="regular-text" value="<?php echo "submission-".date('Ymd').".csv"; ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">Status </th>
				<td>
					<select name="state" class="select-long">
						<option value="all">All</option>
						<option value="publish">Approved</option>
						<option value="draft">Pending</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">Content Type</th>
				<td>
					<select name="type" class="select-long">
						<option value="all">All</option>
						<option value="ugc-post-video">Video</option>
						<option value="ugc-post-youtube">Youtube</option>
						<option value="ugc-post-photo">Photo</option>
					</select>
				</td>
			</tr>
			
		</table>
		
		<p class="submit">
			<button class="button button-primary" type="submit">Export CSV</button>
		</p>

	</form>

</div>