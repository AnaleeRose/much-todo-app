<?php
if (isset($no_tasks) && $no_tasks) {
?>

<p class="bg-secondary px-3 py-2 text-white">you don't have any tasks...why dont you <a class="f_link" href="<?= BASE_URL; ?>manage/managementView">create one</a>?</p>

<?php
} elseif (isset($task_list) && is_array($task_list)) {
	print_r($task_list);
}