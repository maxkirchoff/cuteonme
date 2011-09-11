<?php
require('./template-header.php');

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
foreach ($results['groups'] as $originalUrls)
{
	$users = array();
	foreach ($originalUrls['pivots'] as $pivot)
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
	}

	$urlData[$originalUrls['original_url']] = array(
			'url' => $originalUrls['original_url'],
			'users' => $users);
} 

echo "User data is " . print_r($urlData, true);

?>

User authed. w00t.

<?php print_r($_SESSION); ?>

<?php
require('./template-footer.php');	
?>