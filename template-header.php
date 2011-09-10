<?php
session_start();

require_once('./config.php');
require_once('./lib/twitteroauth/twitteroauth/twitteroauth.php');

if (!empty($_REQUEST['oauth_token']) && !empty($_REQUEST['oauth_verifier'])) {
	$_SESSION['access_token'] = array(
		'oauth_token' => $_REQUEST['oauth_token'],
		'oauth_token_secret' => $_REQUEST['oauth_verifier']
	);
} elseif (empty($_SESSION['access_token'])) {
	header('Location: ./signin.php');
}
?>