<?php
require('./template-header.php');


// build oauth object
$connection = new TwitterOAuth(
	CONSUMER_KEY,
	CONSUMER_SECRET,
	$_SESSION['access_token']['oauth_token'],
	$_SESSION['access_token']['oauth_token_secret']
);

// extract values from and session
$apiKey = API_KEY;
$sharerUserId = $_SESSION['access_token']['user_id'];

// fetch original urls and conversions
$statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&group_by=original_url&pivot=tag&with_conversions=true&user_id={$sharerUserId}";
echo "<br>url called is " .print_r($statsApiCall, true) . "<br>";
$ch = curl_init($statsApiCall);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$results = json_decode($response, true);

$urlData = array();
$uniqueUserIds = array();
foreach ($results['groups'] as $originalUrl)
{
	$users = array();
	foreach ($originalUrl['pivots'] as $pivot)
	{
		if ($pivot['conversions']['goal_1']['count'] > 0)
		{
			$userResponse = 'yes';
		}
		elseif ($pivot['conversions']['goal_2']['count'] > 0) 
		{
			$userResponse = 'no';
		}
		else
		{
			$userResponse = 'no response';
		}
		
		
		$users[$pivot['tag']] = array(
				'user_id' => $pivot['tag'],
				'response' => $userResponse);
		$uniqueUserIds[$pivot['tag']] = $pivot['tag'];
	}

	$urlData[$originalUrl['original_url']] = array(
			'url' => $originalUrl['original_url'],
			'users' => $users);
} 

// fetch original url metadata
$statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&group_by=original_url&with_metadata=true&user_id={$sharerUserId}";
echo "<br>url called is " .print_r($statsApiCall, true) . "<br>";
$ch = curl_init($statsApiCall);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$results = json_decode($response, true);

$urlMetadata = array();
foreach ($results['groups'] as $originalUrl)
{
	$urlMetadata[$originalUrl['original_url']] = array(
			'title' => $originalUrl['metadata']['title'],
			'icon_url' => $originalUrl['metadata']['icon_url']);
}

// fetch user information
$friendsDetails = $connection->get(
	'users/lookup',
	array(
		'user_id' => implode(',', array_values($uniqueUserIds))
	)
);

// arrange details in a fetchable fashion
$friendsData = array();
foreach($friendsDetails as $friendDetails) {
	$friendsData[$friendDetails->id] = array(
			'id' => 				$friendDetails->id,
			'profile_image_url' =>	$friendDetails->profile_image_url,
			'name' =>				$friendDetails->name,
			'screen_name' =>		$friendDetails->screen_name);
}

echo "friends array is " . print_r($friendsData, true);
echo "Url data is " . print_r($urlData, true);
echo "Url metdata is " . print_r($urlMetadata, true);

?>

User authed. w00t.

<?php print_r($_SESSION); ?>

<?php
require('./template-footer.php');	
?>