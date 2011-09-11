<?php
session_start();

require_once('./config.php');
require_once('./lib/twitteroauth/twitteroauth/twitteroauth.php');

if (!empty($_REQUEST['oauth_token']) && !empty($_REQUEST['oauth_verifier'])) {
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	
	// Request access tokens from twitter
	// Save the access tokens. Normally these would be saved in a database for future use.
	$_SESSION['access_token'] = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	
} elseif (empty($_SESSION['access_token'])) {
	header('Location: ./signin.php');
}
?>