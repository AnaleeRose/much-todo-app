<div class="container d-flex justify-content-between py-3 align-items-center border-bottom border-info">
		<p class="text-info font-weight-bold mb-0">Task Name</p>
		<p class="text-info font-weight-bold mb-0">Due Date</p>
</div>
<!-- displays the current page number -->
<?php if (isset($total_pages) && isset($pg_num)) { ?>
	<p class="bg-info mb-0 font-weight-bold text-white py-2 px-3">Page <?= $pg_num ?> of <?= $total_pages ?></p>

<?php } ?>