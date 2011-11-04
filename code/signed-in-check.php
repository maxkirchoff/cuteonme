<?php

/**
 * This is a helper script that is included on pages that you need to be signed-in
 * to view.  It checks session variables and sends a user back to the signin page
 * if they aren't logged in.  This code has been mostly copied from the twitteroauth
 * library (callback.php).  If the user just successfully signed in via
 * Twitter the user's OAuth token is requested to complete the OAuth flow.
 * The returned OAuth values are stored in the user's session so they can be 
 * accessed on all pages of this application.  Also, the user's Twitter icon 
 * is stored in the session to accompany the Twitter user ID and name which were 
 * fetched in the OAuth call.
 */

session_start();

require_once('./config.php');
require_once('./lib/twitteroauth/twitteroauth/twitteroauth.php');

if (!empty($_REQUEST['oauth_token'])
		&& !empty($_REQUEST['oauth_verifier'])
		&& empty($_SESSION['access_token'])) {

	$connection = new TwitterOAuth(
			CONSUMER_KEY,
			CONSUMER_SECRET,
			$_SESSION['oauth_token'],
			$_SESSION['oauth_token_secret']
	);

	// Request access tokens from twitter
	$accessToken = $connection->getAccessToken($_REQUEST['oauth_verifier']);

	// Fetch more user details
	$usersDetails = $connection->get(
			'users/lookup',
			array('user_id' => $accessToken['user_id'])
	);
	
	$accessToken['profile_image_url'] = $usersDetails[0]->profile_image_url;

	// Save the access tokens. Normally these would be saved in a database for future use.
	$_SESSION['access_token'] = $accessToken;


} elseif (empty($_SESSION['access_token'])) {
	// No session and the page requires a session
	if (!empty($_REQUEST['ref'])) {
		header('Location: ./signin.php?ref='.$_REQUEST['ref']);
	} else {
		header('Location: ./signin.php');
	}
	exit;
}
?>