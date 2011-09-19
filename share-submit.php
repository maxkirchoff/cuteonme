<?php
$title = 'We&rsquo;ll see what your friends say&hellip;';
require('./template-header.php');	

// build oauth object
$connection = new TwitterOAuth(
	CONSUMER_KEY,
	CONSUMER_SECRET,
	$_SESSION['access_token']['oauth_token'],
	$_SESSION['access_token']['oauth_token_secret']
);

// extract values from request and session
$apiKey = API_KEY;
$url = $_REQUEST['url'];
$sharerUserId = $_SESSION['access_token']['user_id'];
$sharerUsername = $_SESSION['access_token']['screen_name'];
$sharerIconUrl = $_SESSION['access_token']['profile_image_url'];
$friendUserIds = $_REQUEST['friends'];
$message = $_REQUEST['message'];
$channel = 'twitter-message';
$tool = 'tHSSFr';

// create awe.sm shares
$encodedUrl = urlencode($url);
$encodedMessage = urlencode($message);
$encodedSharerIconUrl = urlencode($sharerIconUrl);
$awesmApiURL = "http://api.awe.sm/url/batch.json?v=3&key={$apiKey}" .
	"&url={$encodedUrl}&channel={$channel}&tool={$tool}&user_id={$sharerUserId}" . 
	"&notes={$encodedMessage}&user_id_username={$sharerUsername}&user_id_icon_url={$encodedSharerIconUrl}&";
foreach ($friendUserIds as $friendUserId)
{
	$awesmApiURL .= "tag[]={$friendUserId}&";
}
// echo "<br>url called is " .print_r($awesmApiURL, true) . "<br>";
error_log("Create API call: {$awesmApiURL}");
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
	$awesmId = $awesmUrl['awesm_id'];
	$text = "{$message} {$shareUrl}";
	$parameters = array('user_id' => $dmUserId, 'text' => $text);
	$method = 'direct_messages/new';
	$dm = $connection->post($method, $parameters);	
	error_log("DM is " . print_r($dm, true));
//	echo "<br> dm'd {$shareUrl} for user {$dmUserId}";

	// call update endpoint
	$postId = $dm->id;
	$sharedAt = date('Y-m-d\TH:i:s\Z');
	$awesmApiURL = "http://api.awe.sm/url/update/{$awesmId}.json?v=3&key={$apiKey}&" .
	   "channel={$channel}&tool={$tool}&service_postid={$postId}&" .
	   "service_postid_shared_at={$sharedAt}";
	error_log("Update API call: {$awesmApiURL}");
    $ch = curl_init($awesmApiURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
}
?>

<h1>Cute On Me?</h1>
<h2><span>We&rsquo;ll see what your friends say&hellip;</span></h2>

<p>A direct message has been sent to your selected friends on Twitter.</p>
<p>Return to <em>Cute On Me</em> to see the results.</p>

<div class="right bottom"><a href="/index.php" class="button">Alrighty Then</a></div>

<?php
require('./template-footer.php');
?>