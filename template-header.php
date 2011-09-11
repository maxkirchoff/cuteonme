<?php
session_start();

require_once('./config.php');
require_once('./lib/twitteroauth/twitteroauth/twitteroauth.php');

if (!empty($_REQUEST['oauth_token']) && !empty($_REQUEST['oauth_verifier'])) {
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	
	// Request access tokens from twitter
	// Save the access tokens. Normally these would be saved in a database for future use.
	$_SESSION['access_token'] = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	
} elseif (empty($_SESSION['access_token']) && empty($unauthedAllowed)) {
	// No session and the page requires a session
	header('Location: ./signin.php');
} elseif (!empty($unauthedAllowed)) {
	// User has a session
	header('Location: ./dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Cute On Me?</title>
	<link rel="stylesheet" href="/static/css/screen.css" />

</head>
<body>
	<div class="container">