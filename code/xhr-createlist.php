<?php

require('./signed-in-check.php');

// Setup Twitter Connection
$connection = new TwitterOAuth(
	CONSUMER_KEY,
	CONSUMER_SECRET,
	$_SESSION['access_token']['oauth_token'],
	$_SESSION['access_token']['oauth_token_secret']
);

// Verify that there's a name
if (empty($_REQUEST['listName'])) {
	header('HTTP/1.1 400 Bad Request');
	die('listName required.');
}

// Verify there are users
if (empty($_REQUEST['listUsers'])) {
	header('HTTP/1.1 400 Bad Request');
	die('listUsers required.');
}


// Create the list
$newList = $connection->post(
	'lists/create',
	array (
		'name' => $_REQUEST['listName'],
		'description' => 'from CuteOn.Me',
		'mode' => 'private'
	)
);

// Check for success
if (empty($newList->id)) {
	header('HTTP/1.1 400 Bad Request');
	die('Error creating list.');
}

// Add the users to the list
$addUsersRequest = $connection->post(
	'lists/members/create_all',
	array (
		'list_id' => $newList->id,
		'user_id' => $_REQUEST['listUsers']
	)
);

// Return JSON response
header('Content-type: application/json');
echo json_encode($addUsersRequest);