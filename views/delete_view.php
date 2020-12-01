
<h2 class="<?= H2_CLASSES; ?>">Delete Tasks</h2>
<p class="mb-0 alert alert-info my-2">Select all tasks to delete.</p>
<?php 
require './../html/assets/includes/task_list_header.php';

// displays each task in the task list
if (isset($task_list) && is_array($task_list)) {
	foreach ($task_list as $task) {
		if ($task['t_complete']) {
			$name =  $task['t_name'] . ' | ' . COMPLETED;
			$t_container_class = 'task_container_aqua';
		} else {
			$name = $task['t_name'];
			$t_container_class = false;
		}
		?>
		<div class="task_container container d-flex justify-content-between py-3 align-items-center border-bottom border-info <?php if (isset($t_container_class)) echo $t_container_class ?>">
			<div data-delete=0 data-key="t_<?= $task['t_key']; ?>">
				<p class="mr-3 border border-info rounded-circle d-inline mark-task-btn"><span class="sr-only">select this task</span></p>
				<p class="task_name d-inline"><?= $name; ?></p>
			</div>
			<div class="right_side">
				<p class="task_due_date mb-0"><?= $task['t_due_date']; ?></p>
			</div>
		</div>
	<?php

	}
}
require './../views/pg_btns.php';
?>


<div class="text-center text-md-left mt-4">
	<p class="mb-md-0 d-md-inline">
		<p class="btn btn-danger my-2" id="delete_tasks">Delete Selected Tasks</p>
	</p>
	<p class="mb-0 d-md-inline">
		<a href="./../user/managementView" class="f_link btn btn-secondary">Cancel</a>
	</p>
</div>