<!-- Common header HTML  -->
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?= (!empty($title))? $title : 'Cute On Me?' ?></title>
	<link rel="stylesheet" href="/static/css/screen.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.js"></script>
</head>
<body <?= (!empty($_REQUEST['ref']))? 'class="extension"' : '' ?>>
	<div class="outerContainer">
		<div class="container">