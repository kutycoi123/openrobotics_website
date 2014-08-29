<?php
	//include our library and start drawing the page
	require_once("../../../php_include/functions.php");
	$page_name = "manage_project";
	print_header($page_name, true);
	print_navbar();
?>




<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h2>Manage Project</h2>	
		</div>
	</div>
	<?php
	
		$db = get_db();
		$project_id = intval(@$_GET['id']);
		
		if (!(canManageAllProjects() || canAddProjects())) {
			echo '<div class="row"><div class="col-md-12"><h3>You do not have permission to be here</h3></div></div>';
				print_footnote();
				echo "</div>";
				print_footer();
				exit();
		}
	
		if ($project_id == 0 && (canManageAllProjects() || canAddProjects())) {
			if ($db) {
				$now = date("Y-m-d");
				$query = "INSERT INTO `projects` (`created_by`, `start_time`) VALUES ('$user_id', '$now');";
				if ($db->query($query)) {
					$project_id = $db->insert_id;
					$db->close();
					echo '<script>location.replace("?id='.$project_id.'");</script>';
				}
			}
		} else if (!canManageAllProjects()) {
			$query = "SELECT `id` FROM `project_contributors` WHERE `user_id`='$user_id' AND `project_id`='$project_id';";
			$db->query($query);
			if (@$db->num_rows < 1) {
				echo '<div class="row"><div class="col-md-12"><h3>You do not have permission to be here</h3></div></div>';
				print_footnote();
				echo "</div>";
				print_footer();
				exit();
			}
		}
		
		$query = "SELECT * FROM `projects` WHERE `id`='$project_id';";
		
		$result = $db->query($query);
		$project_data = $result->fetch_assoc();
	?>
	<div class="row">
		<div class="col-md-6">
			<p class='text-danger' id="error-message">
			<?php
				if ($result->num_rows < 1) {
					echo "Invalid ID!";
				}
			?>
			</p>
			<div class="checkbox">
				<label>
					<input type="checkbox" id="form_visible" <?php if($project_data['visible']=='1')echo "checked";?>> Make project visible
				</label>
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox" id="form_featured" <?php if(!canManageAllProjects())echo "disabled";?> <?php if($project_data['is_featured']=='1')echo "checked";?>> Featured Project
				</label>	
			</div>
			<div class="form-group">
				<label for="form_start_time">Project Start Date</label>
				<input type="text" class="form-control" id="form_start_time" value="<?php echo $project_data['start_time'];?>">
			</div>
			<div class="checkbox">
				<label>
					<input type="checkbox" id="form_finished_project" <?php if(isset($project_data['finish_time']))echo "checked";?>> Finished Project
				</label>
			</div>
			<div class="form-group">
				<label for="form_finish_time">Project End Date</label>
				<input type="text" class="form-control" id="form_finish_time" value="<?php echo $project_data['finish_time'];?>" <?php if(!isset($project_data['finish_time']))echo "disabled";?>>
			</div>
			<div class="form-group">
				<label for="form_name">Name</label>
				<input type="text" class="form-control" id="form_name" placeholder="Name" value="<?php echo $project_data['name'];?>">
			</div>
			<div class="form-group">
				<label for="form_description">Description/Write Up</label>
				<textarea rows="10" class="form-control" id="form_description" placeholder="Description"><?php echo $project_data['description'];?></textarea>
			</div>
			<button class="btn btn-default" id="form_submit">Update</button>
			<button class="btn btn-default" data-container="body" id="delete-popover">Delete</button><br /><br />
		</div>		
		<div class="col-md-6">
			<p class="visible-lg visible-md">Contributors<br />All Users are listed on the left. Add those who are involved in this project to the right.</p>
			<p class="hidden-lg hidden-md">Contributors<br />All Users are listed on the top. Add those who are involved in this project to the bottom. (On Mobile, tap to bring up selection), then press the arrows.</p>
			<div class="row">
				<div class="col-md-4">
					<select multiple class="form-control" id="select-left">
					<?php
						if ($db) {
							$query = "SELECT `id`, `first_name`, `last_name` FROM `user_info` WHERE `id`!='$user_id' AND `id` NOT IN (SELECT `user_id` FROM `project_contributors` WHERE `project_id`='$project_id');";
							if ($result = $db->query($query)) {
								while ($row = $result->fetch_assoc()) {
									echo "<option value='".$row['id']."'>".$row['first_name'].' '.$row['last_name']."</option>";
								}	
							}								
						}
					?>
					</select>
				</div>
				<div class="col-md-2">
					<button class="btn btn-default" id="contributor-right"><span class="visible-lg visible-md glyphicon glyphicon-arrow-right"></span><span class="hidden-lg hidden-md glyphicon glyphicon-arrow-down"></span></button><br />
					<button class="btn btn-default" id="contributor-left"><span class="visible-lg visible-md glyphicon glyphicon-arrow-left"></span><span class="hidden-lg hidden-md glyphicon glyphicon-arrow-up"></span></button>
				</div>
				<div class="col-md-4">
					<select multiple class="form-control" id="select-right">
					<?php
						if ($db) {
							$query = "SELECT `id`, `first_name`, `last_name` FROM `user_info` WHERE `id` IN (SELECT `user_id` FROM `project_contributors` WHERE `project_id`='$project_id');";
							if ($result = $db->query($query)) {
								while ($row = $result->fetch_assoc()) {
									echo "<option value='".$row['id']."'>".$row['first_name'].' '.$row['last_name']."</option>";
								}
							}
							$db->close();
						}
					?>
					</select>
				</div>
			</div>
			<br />
			
			<p>Main Picture<p>
			<span class="btn btn-default fileinput-button">
				<i class="glyphicon glyphicon-plus"></i>
				<span>Upload...</span>
				<input id="main_image_upload" type="file" name="file" data-url="/manage/projects/project/assets/cgi/project.php?task=2">
			</span>
			<br />
			<br />
			<div id="main_image_upload_progress" class="progress">
				<div class="progress-bar progress-bar-striped active"></div>
			</div>
			
			<br />
			
			<p>Addition Pictures: (You can add multiple)<p>
			<span class="btn btn-default fileinput-button">
				<i class="glyphicon glyphicon-plus"></i>
				<span>Upload...</span>

				<input id="additional_image_upload" type="file" name="files[]" data-url="/manage/projects/project/assets/cgi/project.php?task=3" multiple>
			</span>
			<br />
			<br />
			<div id="additional_image_upload_progress" class="progress">
				<div class="progress-bar progress-bar-striped active"></div>
			</div>
			
			
		</div>
	</div>
	<br />
	<br />
	<div id="manage_images">
	<?php
	if (file_exists("../../../upload_content/project_images/$project_id/")) {
		$array = scandir("../../../upload_content/project_images/".$project_id."/");
		foreach ($array as $val) {
			$ext = strtolower(array_pop(explode('.', $val)));
			if ($ext == "png" || $ext == "jpg") {
				echo "<div class='row'><div class='col-md-6'><img class='img-responsive img-thumbnail' src='/upload_content/project_images/$project_id/$val'></div>
				<div class='col-md-6'><button class='btn btn-danger image_delete' data-file='$val'>Delete</button></div></div><br /><br />";
			}
		}
	}
	?>
	</div>
	<?php
		print_footnote();
	?>

</div><!-- /.container -->


<?php 
	//print the footer	
	print_footer();
?>