<?php

require('./signed-in-check.php');

// Setup Twitter Connection
$connection = new TwitterOAuth(
	CONSUMER_KEY,
	CONSUMER_SECRET,
	$_SESSION['access_token']['oauth_token'],
	$_SESSION['access_token']['oauth_token_secret']
);

// Get list of friends
$friends = array();

// Get the signed in user's Twitter friends IDs
$friendsIds = $connection->get(
		'friends/ids',
		array (
			'user_id' => $_SESSION['access_token']['user_id'],
			'cursor' => -1
		)
);

// TODO: Keep requesting until next_cursor === 0

// Get the signed in user's Twitter followers IDs
$followersIds = $connection->get(
	'followers/ids',
	array (
		'user_id' => $_SESSION['access_token']['user_id'],
		'cursor' => -1
	)
);

// Create the users that are followers and friends
$mutualFriends = array_intersect($friendsIds->ids, $followersIds->ids);
// Reset the array index in $mutualFriends
$mutualFriends = array_merge($mutualFriends, array());

// Find the information about those friends in a batched manner.
$friendsBatch = array_chunk($mutualFriends, 100);
foreach($friendsBatch as $batch) 
{
	// Get friend details
	$friendsDetails = $connection->get(
		'users/lookup',
		array('user_id' => implode(',', $batch))
	);

	// Save friend details
	foreach($friendsDetails as $friendDetails) 
	{
		$friendName = strlen($friendDetails->name) > 12 ?
			substr($friendDetails->name, 0, 11) . "..." : $friendDetails->name;
		$friends[] = array(
			'id' => $friendDetails->id,
			'profile_image_url' => $friendDetails->profile_image_url,
			'screen_name' => $friendDetails->screen_name,
			'name' => $friendDetails->name,
			'display_name' => $friendName,
			'search_name' => htmlspecialchars(strtolower('@'.$friendDetails->screen_name.' '.$friendDetails->name))
		);
	}
}

// Setup user's lists
$lists = array();	// Output array

// Retrieve list of signed in user's lists
$listsRequest = $connection->get(
	'lists',
	array (
		'user_id' => $_SESSION['access_token']['user_id'],
		'cursor' => -1
	)
);

// Trim down lists array to relevant data
// Retrieve the members in those lists
// Only add members that can be directly messaged
foreach($listsRequest->lists as &$list)
{
	$listFriends = $connection->get(
		'lists/members',
		array (
			'list_id' => $list->id,
			'include_entities' => false,
			'skip_status' => true
		)
	);
	
	// Loop through users array, save to array of ids
	$list->userIds = array();
	
	foreach($listFriends->users as $user) {
		array_push($list->userIds, $user->id);
	}
	
	// Intersect the lists to find which ones follow back and can be sent messages.
	$mutualFriends = array_intersect($list->userIds, $followersIds->ids);
	// Reset the array index in $mutualFriends
	$mutualFriends = array_merge($mutualFriends, array());
	
	// Save this list to the list output array if there are message-able friends
	if (!empty($mutualFriends)) {
		array_push($lists,
			array (
				'id' => $list->id_str,
				'name' => $list->name,
				'friends' => $mutualFriends
			)
		);
	}
}

// Prep JSON response
header('Content-type: application/json');
echo json_encode(
	array(
		'friends' => &$friends,
		'lists' => &$lists
	)
);
?>