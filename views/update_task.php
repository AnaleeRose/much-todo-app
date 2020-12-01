<?php

// if there's a list of errors, generate error notices for each
if (isset($errors) && !empty($errors)) {
	echo '<p class="m-0 alert alert-danger" role="alert">Please fix the following:</p>';
	foreach ($errors as $key => $value) {
		?>
		<p class="m-0 alert alert-danger" role="alert"><span class="text-capitalize"><?= substr($key, 2); ?></span> is not valid.</p>

		<?php
	}
}

// if this task got returned, we want to repopulate the inputs
if (isset($task_values) && !empty($task_values)) {
	if (isset($task_values['t_name'])) $name = $task_values['t_name'];
	if (isset($name)) $clean_name = preg_replace("/[^a-zA-Z0-9_ ,\.-]/", '', $name);
	if (isset($task_values['t_due_date'])) $due_date = $task_values['t_due_date'];
	if (isset($task_values['t_description'])) $description = $task_values['t_description'];
	if (isset($task_values['t_key'])) $t_key = $task_values['t_key'];

}

?>

<h2 class="<?= H2_CLASSES; ?>"><?php echo (isset($clean_name) ? "$clean_name" : 'Edit Task' ); ?></h2>
<form class="form-container px-3 pt-1 pb-3 form-content" data-send-to="./../task/update/<?= $task_values['t_key'];?>">
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

	<input type="hidden" name="t_key" id="t_key" class="form-control" value="<?= $t_key;?>">

	<div class="text-center text-md-left mt-4">
		<p class="mb-md-0 d-md-inline">
			<button type="submit" class="btn btn-info text-center f_link form_submit">Edit Task</button>
		</p>
		<p class="mb-0 d-md-inline">
			<a href="./../user/primaryView" class="f_link btn btn-danger cancel_btn">Cancel</a>
		</p>
	</div>
</form>