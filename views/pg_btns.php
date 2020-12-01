<?php 
// displays the pagination buttons with the correct links
$prev_link = '';
$next_link = '';
$link_text = 'href="./../user/' . $c_view . '/pg_';
$first_link = $link_text . '1"';
$last_link =  $link_text . $total_pages . '"';

($pg_num > 1 ? $prev_status = 'text-info' : $prev_status = 'text-secondary');
($pg_num < $total_pages ? $next_status = 'text-info' : $next_status = 'text-secondary');

if($pg_num > 1) {
	$prev_link = $link_text . ($pg_num - 1) . '"';
}

if($pg_num < $total_pages) {
	$next_link = $link_text . ($pg_num + 1) . '"';
}


?>

<?php if ($total_pages > 1) { ?>
	<div class="f_link pg_btn_container text-center text-md-right pt-4 pt-md-3 pr-md-5 py-2 font-weight-bold">
		<a class="f_link text-info cursor-p" <?= $first_link; ?>>First</a>
		<a class="f_link pl-2 pr-1 cursor-p <?= $prev_status; ?>" <?= $prev_link; ?> title="Previous">&lt</a>
	<?php 
	if ($total_pages < 12 && $total_pages > 1) {
		// stands for individual page number, shortened so that the html is a bit more readable
		$i = 1;
		while ($i <= $total_pages) {
			if ($i == $pg_num) {
				$i_link = "" ;
				$i_classes = "bg-info text-white rounded pb-1" ;
			} else {
				$i_link = $link_text . $i . '"' ;
				$i_classes = "text-info";
			} 
			
			?>

		<a class="f_link px-1 pg_btn_numeric cursor-p <?= $i_classes; ?>" <?= $i_link; ?> title="Skip to page <?= $i; ?>"><?= $i; ?></a>

			<?php

			$i++;
		}
	}
	?>

		<a class="f_link pr-2 pl-1 cursor-p <?= $next_status; ?>" <?= $next_link; ?> title="Next">&gt</a>
		<a class="f_link text-info cursor-p" <?= $last_link; ?>>Last</a>
	</div>
<?php } ?>
