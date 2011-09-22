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

// extract values from the request and session
$url = $_REQUEST['url'];
$sharerUserId = $_SESSION['access_token']['user_id'];
$sharerUsername = $_SESSION['access_token']['screen_name'];
$sharerIconUrl = $_SESSION['access_token']['profile_image_url'];
$friendUserIds = $_REQUEST['friends'];
$message = $_REQUEST['message'];

// set default values
$channel = 'twitter-message';
$tool = 'tHSSFr';

// create awe.sm shares using the awe.sm Create API
$encodedUrl = urlencode($url);
$encodedMessage = urlencode($message);
$encodedSharerIconUrl = urlencode($sharerIconUrl);
$createSharesApiUrl = "http://api.awe.sm/url/batch.json?" .
        "v=3&" . 
        "key=" . API_KEY . "&" .
        "url={$encodedUrl}&" . 
        "channel={$channel}&" . 
        "tool={$tool}&" . 
        "user_id={$sharerUserId}&" .
        "user_id_username={$sharerUsername}&" .
        "user_id_icon_url={$encodedSharerIconUrl}&" .  
        "notes={$encodedMessage}&";
// add all the friends to the URL
foreach ($friendUserIds as $friendUserId)
{
	$createSharesApiUrl .= "tag[]={$friendUserId}&";
}
error_log("Create awe.sm shares API URL is {$createSharesApiUrl}");
$ch = curl_init($createSharesApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$results = json_decode($response, true);

// iterate over the created awe.sm shares and send DMs to each friend
foreach ($results['awesm_urls'] as $awesmUrlObject)
{
    // extract values from the awe.sm share response
	$awesmUrl = $awesmUrlObject['awesm_url'];
	$dmUserId = $awesmUrlObject['tag'];
	$message = $awesmUrlObject['notes'];
	$awesmId = $awesmUrlObject['awesm_id'];
	$text = "{$message} {$awesmUrl}";
	
	// send a DM using the Twitter API
	$parameters = array('user_id' => $dmUserId, 'text' => $text);
	$method = 'direct_messages/new';
	$dm = $connection->post($method, $parameters);
	
	// extract the values from the DM
	$postId = $dm->id;
	$sharedAt = urlencode($dm->created_at);
	
	// update the share with the DM's information using the awe.sm Create API
	$updateShareApiUrl = "http://api.awe.sm/url/update/{$awesmId}.json?" . 
	       "v=3&" . 
	       "key=" . API_KEY . "&" .
	       "channel={$channel}&" . 
	       "tool={$tool}&" . 
	       "service_postid={$postId}&" .
	       "service_postid_shared_at={$sharedAt}&" .
	       "service_postid_reach=1&" .
	       "service_userid={$dmUserId}";
	error_log("Update awe.sm share API URL is {$updateShareApiUrl}");
    $ch = curl_init($updateShareApiUrl);
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