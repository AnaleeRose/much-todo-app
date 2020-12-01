<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<title><?= $title; ?></title>
    <meta name="description" content="To do list app">
    <meta name="author" content="Savannah Skinner">
    <link rel="icon" type="image/ico" href="assets/imgs/logo_icons/logo.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <script src="assets/js/scripts.js" defer></script>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <meta name="google-signin-client_id" content="1064160369089-250ib0m260el9sje97kqkp3lnptias55.apps.googleusercontent.com">
</head>
<body>
	<header class="bg-info">
		<nav class="navbar navbar-expand navbar-dark container flex-column flex-md-row px-3">
		<img src="assets/imgs/logo_icons/logo.svg" alt="Much To-do logo" class="d-inline logo pt-1">
		<a class="navbar-brand f_link pt-1" href="./../user/primaryView">much <span class="font-italic">to-do</span></a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link f_link" href="./../user/primaryView">Primary <span class="sr-only">Primary</span></a>
				</li>
				<li class="nav-item">
					<a class="nav-link f_link" href="./../user/historicalView">Completed</a>
				</li>
				<li class="nav-item">
					<a class="nav-link f_link" href="./../user/managementView">Manage</a>
				</li>
			</ul>
		</div>
		</nav>
	</header>
	<main class="main_content" id="main_content">
		<div id="notice_container"></div>
		<div id="content_container" class="container px-0 px-lg-2">

