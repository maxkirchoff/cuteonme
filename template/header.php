<?php

/**
 * The header checks session variables and sends a user back to the signin page 
 * if they aren't logged in.  This code has been mostly copied from the twitteroauth 
 * library (callback.php).  If the user just successfully signed in via 
 * Twitter the user's OAuth token is requested and stored in the user's session
 * so the value can be fetched across all pages of this application.  Also, 
 * the user's twitter icon is stored in the session to accompany the Twitter user
 * ID and name which were fetched in the OAuth call. Finally, some common HTML
 * is rendered whch is used across all pages of the application.  
 */

session_start();

require_once('./config.php');
require_once('./lib/twitteroauth/twitteroauth/twitteroauth.php');

if (!empty($_REQUEST['oauth_token']) && !empty($_REQUEST['oauth_verifier']) && empty($_SESSION['access_token'])) {
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	
	// Request access tokens from twitter
	$accessToken =  $connection->getAccessToken($_REQUEST['oauth_verifier']);
	
	// fetch more user details
	$usersDetails = $connection->get(
		'users/lookup',
		array('user_id' => $accessToken['user_id']));
	$accessToken['profile_image_url'] = $usersDetails[0]->profile_image_url;

	// Save the access tokens. Normally these would be saved in a database for future use.	
	$_SESSION['access_token'] = $accessToken;
				
	
} elseif (empty($_SESSION['access_token']) && empty($unauthedAllowed)) {
	// No session and the page requires a session
	header('Location: ./signin.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?= (!empty($title))? $title : 'Cute On Me?' ?></title>
	<link rel="stylesheet" href="/static/css/screen.css" />

</head>
<body>
	<div class="outerContainer"><div class="container">