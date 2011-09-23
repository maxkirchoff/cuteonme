<?php

/**
 * The signin-redirect page is where we start the OAuth flow to enable the 
 * awe.sm CuteOn.Me Twitter application to send direct messages to your friends.
 * This code has been copied from the twitteroauth library (redirect.php) 
 * which is included as a submodule to this project. A connection is 
 * made to twitter and the user is redirected to a Twitter URL so they 
 * can authenticate and grant the application access.  
 */

/* Start session and load library. */
session_start();
require_once('./config.php');
require_once('./lib/twitteroauth/twitteroauth/twitteroauth.php');

/* Build TwitterOAuth object with client credentials. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
 
/* Get temporary credentials. */
$request_token = $connection->getRequestToken(OAUTH_CALLBACK);

/* Save temporary credentials to session. */
$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
 
/* If last connection failed don't display authorization link. */
switch ($connection->http_code) {
  case 200:
    /* Build authorize URL and redirect user to Twitter. */
    $url = $connection->getAuthorizeURL($token);
    header('Location: ' . $url); 
    break;
  default:
    /* Show notification if something went wrong. */
    echo 'Could not connect to Twitter. Refresh the page or try again later.';
}
