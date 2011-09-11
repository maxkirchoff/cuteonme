<?php
require('./template-header.php');	

// build oauth object
$connection = new TwitterOAuth(
	CONSUMER_KEY,
	CONSUMER_SECRET,
	$_SESSION['access_token']['oauth_token'],
	$_SESSION['access_token']['oauth_token_secret']
);

// extract values from request
$apiKey = '103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743';
//$url = 'http://idunnowhatdoyouthink.com/?share_user=&url=http%3A%2F%2Ftechcrunch.com%2F2011%2F09%2F10%2Fthe-disrupt-hackathon-in-san-francisco-has-officially-begun%2F';
$url = $_REQUEST['url'];
$sharerUserId = '12345';
//$friendUserIds = array('testme1111', 'jeremiahlee');
$friendUserIds = $_REQUEST['friends'];

// create awe.sm shares
$encodedUrl = urlencode($url);
$awesmApiURL = "http://api.awe.sm/url/batch.json?v=3&key={$apiKey}&url={$encodedUrl}&channel=twitter&tool=tHSSFr&user_id{$sharerUserId}&";
foreach ($friendUserIds as $friendUserId)
{
	$awesmApiURL .= "tag[]={$friendUserId}&";
}
echo "<br>url called is " .print_r($awesmApiURL, true) . "<br>";
$ch = curl_init($awesmApiURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

$results = json_decode($response, true);
$awesmUrls = $results['awesm_urls'];
foreach ($awesmUrls as $awesmUrl)
{
	$shareUrl = $awesmUrl['awesm_url'];
	$dmUserId = $awesmUrl['tag'];
	$text = "Check out this link: {$shareUrl}";
	$parameters = array('screen_name' => $dmUserId, 'text' => $text);
	$method = 'direct_messages/new';
	$dm = $connection->post($method, $parameters);	
	echo "<br> dm'd {$shareUrl} for user {$dmUserId}";
	
	// Retry DM if failed
}

echo "<br>Success";

	
?>
