<h2 class="<?= H2_CLASSES; ?>">Completed Tasks</h2>
<div class="all_tasks_container">
<?php 
require './../html/assets/includes/task_list_header.php';
if (isset($task_list) && is_array($task_list)) {
	
	// displays all tasks in the task list
	foreach ($task_list as $task) {
		$date_prep = DateTime::createFromFormat('Y-m-d', $task['t_due_date']);
		$friendly_due_date = $date_prep->format('F j, Y');
	?>
		<div class="task_container container d-flex justify-content-between py-3 align-items-center border-bottom border-info">
			<div>
				<a class="mr-3 border border-info rounded-circle d-inline mark-task-btn complete bg-info f_link" href="./../task/markIncomplete/<?= $task['t_key']; ?>" title="Mark this task incomplete"><span class="sr-only">mark this task incompleted</span></a>
				<a class="task_name d-inline f_link text-info" title="Edit this task" href="./../task/update/<?= $task['t_key']; ?>"><?= $task['t_name']; ?></a>
			</div>
			<div class="right_side">
				<p class="task_due_date mb-0"><?= $friendly_due_date; ?></p>
			</div>
		</div>
	<?php
	}
}
require './../views/pg_btns.php';
?>

</div>