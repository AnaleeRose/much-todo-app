<h2 class="<?= H2_CLASSES; ?>">Create Task</h2>
<?php

// if there's a list of errors, generate error notices for each
if (isset($errors) && !empty($errors)) {
	echo '<p class="m-0 alert alert-danger removeable_BE_notice cursor-p" role="alert" id="ct_e_heading">Please fix the following:</p>';
	foreach ($errors as $key => $value) {
		$clean_name = substr($key, 2);
		?>
		<p class="m-0 alert alert-danger removeable_BE_notice cursor-p" id="ct_e_<?= $clean_name;?>" role="alert"><span class="text-capitalize"><?= $clean_name; ?></span> is not valid.</p>

		<?php
	}
}

// if this task got returned, we want to repopulate the inputs
if (isset($task_values) && !empty($task_values)) {
	if (isset($task_values['t_name'])) $name = $task_values['t_name'];
	if (isset($task_values['t_due_date'])) $due_date = $task_values['t_due_date'];
	if (isset($task_values['t_description'])) $description = $task_values['t_description'];
}

?>

<form class="form-container px-3 pt-1 pb-3 form-content" data-send-to="./../task/new/">
	<div class="my-2">
		<?= REQUIRED; ?>
		<?= TEXT_REQUIREMENTS; ?>
		<div class="form-group">
			<label for="t_name" class="mr-3 mb-0">Task Name</label>
			<input name="t_name" type="text" class="form-control border-info" id="t_name" placeholder="Enter task name..." value="<?php if (isset($name)) echo $name; ?>" required>
		</div>
	</div>

	<div class="my-2">
		<?= REQUIRED; ?>
		<div class="form-group">
			<label for="t_due_date" class="mr-3 mb-0">Due Date</label>
			<input name="t_due_date" type="date" class="form-control border-info" id="t_due_date" placeholder="Enter due date..." value="<?php if (isset($due_date)) echo $due_date; ?>" required>
		</div>
	</div>

	<div class="mt-4 mb-2">
		<div class="form-group">
			<label for="t_description" class="mr-3 mb-0">Description</label>
			<textarea name="t_description" class="form-control border-info" id="t_due_date" maxlength="1500" name="t_description" placeholder="Enter task description..."><?php if (isset($description)) echo $description; ?></textarea>
		</div>
	</div>

	<div class="text-center text-md-left mt-4">
		<p class="mb-md-0 d-md-inline">
		<button type="submit" class="btn btn-info text-center f_link form_submit" data-href="./../new/<?php ?>">Create Task</button>
		</p>
		<p class="mb-0 d-md-inline">
			<a href="./../user/managementView" class="f_link btn btn-danger cancel_btn">Cancel</a>
		</p>
	</div>
</form>