<?php
// grabs the url
$url = $_SERVER['SERVER_NAME'];

// checks if the site is online and sets the variables accordingly. It's an attempt to mitigate some of the risk of deployment & updates
if (strpos($url,'com') == false) {
    $online = false;
} else {
    $online = true;
}


// base url depending on whether we're online or not
if ($online) {
    define('BASE_URI', '/home/analeerose/savannahskinner.com/much-todo/');
    define('BASE_URL', 'https://savannahskinner.com//much-todo/');
} else {
    define('BASE_URI', 'C:/xampp/htdocs/much-todo/');
    define('BASE_URL', 'http://localhost/much-todo/');
}





// where to find mysql.php
if ($online) {
    define('MYSQL', $_SERVER['DOCUMENT_ROOT'] . '/p_includes/mysql.inc.php');
} else {
    define('MYSQL', BASE_URI . '/p_includes/mysql.inc.php');
}

// Creates the lil required tag underneath inputs
define('REQUIRED', '<small class="text-info required">Required</small>');
define('COMPLETED', '<small class="text-info">COMPLETED</small>');
define('TEXT_REQUIREMENTS', '<small class="text-info required">| Allowed Characters: letters, numbers, _, - , .</small>');
define('H2_CLASSES', 'border-bottom border-info p-3 text-info text-center text-md-left');
