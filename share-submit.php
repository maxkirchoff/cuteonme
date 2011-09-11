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
$apiKey = API_KEY;
$url = $_REQUEST['url'];
$sharerUserId = $_SESSION['access_token']['user_id'];
$sharerUsername = $_SESSION['access_token']['screen_name'];
$friendUserIds = $_REQUEST['friends'];
$message = $_REQUEST['message'];

// create awe.sm shares
$encodedUrl = urlencode($url);
$encodedMessage = urlencode($message);
$awesmApiURL = "http://api.awe.sm/url/batch.json?v=3&key={$apiKey}" .
	"&url={$encodedUrl}&channel=twitter&tool=tHSSFr&user_id={$sharerUserId}" . 
	"&notes={$encodedMessage}&user_id_username={$sharerUsername}&";
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
	$message = $awesmUrl['notes'];
	$text = "{$message} {$shareUrl}";
	$parameters = array('user_id' => $dmUserId, 'text' => $text);
	$method = 'direct_messages/new';
	$dm = $connection->post($method, $parameters);	
	echo "<br> dm'd {$shareUrl} for user {$dmUserId}";
}

echo "<br>Success";

	
?>
