<h2 class="<?= H2_CLASSES; ?>">Tasks Due</h2>
<div class="all_tasks_container">
<?php 


$f_today = true;
$f_tomorrow = true;
require './../html/assets/includes/task_list_header.php';

// displays all tasks in the task list with the correctly formatted text and date specific headings
if (isset($task_list) && is_array($task_list)) {
	foreach ($task_list as $task) {
		$date_prep = DateTime::createFromFormat('Y-m-d', $task['t_due_date']);
		$friendly_due_date = $date_prep->format('F j, Y');
		$container_bg = false;
		$past_due = false;
		$due_soon = false;
		if ($task['t_due_date'] == date('Y-m-d')) {
			$due_soon = true;
			if ($f_today) {
				$f_today = false;
				echo '<p class="bg-secondary px-4 py-3 mb-0 text-white font-weight-bold">Due Today</p>';
			}
		}

		if ($task['t_due_date'] == date("Y-m-d", strtotime("+1 day"))) {
			$due_soon = true;
			if ($f_tomorrow) {
				$f_tomorrow = false;
				echo '<p class="bg-secondary px-4 py-3 mb-0 text-white font-weight-bold">Due Tomorrow</p>';
			}
		}

		if ($task['t_due_date'] < date('Y-m-d')) {
			$past_due = 'past_due';
		}

		if ($due_soon) {
			$mark_btn_border = 'border-warning';
			$text_color = 'text-warning';
			$container_bg = 'border-warning due-soon';
		} elseif ($past_due) {
			$mark_btn_border = 'border-danger';
			$text_color = 'text-danger';
			$container_bg = 'border-danger past-due';
		} else {
			$mark_btn_border = 'border-info';
			$text_color = 'text-info';
			$container_bg = 'border-info';
		}
	?>
		<div class="task_container container d-flex justify-content-between py-3 align-items-center border-bottom <?php if ($container_bg) echo $container_bg; ?>">
			<div>
				<a class="mr-3 border border-info rounded-circle d-inline mark-task-btn f_link bg-white <?= $mark_btn_border;  ?>" href="./../task/markComplete/<?= $task['t_key']; ?>" title="Mark this task complete"><span class="sr-only">mark this task completed</span></a>
				<a class="task_name d-inline f_link <?= $text_color;  ?>" href="./../task/update/<?= $task['t_key']; ?>" title="Edit this task"><?= $task['t_name']; ?></a>
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