<?php
// generic error page
if (isset($error_code)) {
	switch ($error_code) {
		// case 0:
		// 	$error_text = "Something went wrong...please contact our service team.";
		// 	break;

		default:
			$error_text = "Something went wrong...please contact our service team.";
	}

} else {
	$error_text = "Something went wrong...please contact our service team.";

}

echo '<span id="generateNotice" data-notice-type="danger" data-notice-text="' . $error_text . '">' . $error_text . '</span>';
