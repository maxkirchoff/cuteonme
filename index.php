<?php
$title = 'Cute On Me Results';
require('./template-header.php');

// build oauth object
$connection = new TwitterOAuth(
	CONSUMER_KEY,
	CONSUMER_SECRET,
	$_SESSION['access_token']['oauth_token'],
	$_SESSION['access_token']['oauth_token_secret']
);

// extract values from the session
$apiKey = API_KEY;
$sharerUserId = $_SESSION['access_token']['user_id'];

// fetch original urls, users, and conversions from the awe.sm Stat API
$originalUrlApiCall = "http://api.awe.sm/stats/range.json?" .
        "v=3&" .
        "key=" . API_KEY . "&" .
        "user_id={$sharerUserId}&" .
        "group_by=original_url&" . 
        "pivot=tag&" .
        "with_metadata=true&" .
        "with_conversions=true&" .
        "sort_type=shared_at";
error_log("Fetch original_urls Stats API call is {$originalUrlApiCall}");
$ch = curl_init($originalUrlApiCall);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$results = json_decode($response, true);

// create a place to store url data
$urlData = array();

// create a list of all the twitter user_ids
$userIdList = array();

// iterate over the urls found
foreach ($results['groups'] as $originalUrlGroup)
{
    // extract the url 
    $url = $originalUrlGroup['original_url'];
      
    // extract url metadata
    $urlTitle = $originalUrlGroup['metadata']['title'];
    if (empty($urlTitle)) $urlTitle = $url;
    $urlIconUrl = $originalUrlGroup['metadata']['icon_url'];
    
    // create a place to store user data
    $users = array();

    // iterate over the users 
    $positiveResponses = 0;
    $negativeResponses = 0;
	foreach ($originalUrlGroup['pivots'] as $tagGroup)
	{
	    
	    // extract the user_id
	    $userId = $tagGroup['tag'];
	    
	    // calculate whether the user responsed positively or negatively
	    // goal_1 is positive, goal_2 is negative 
		if ($tagGroup['conversions']['goal_1']['count'] > 0)
		{
			$userResponse = 'src="/static/img/thumbs-up.png"';
			$positiveResponses++;
		}
		elseif ($tagGroup['conversions']['goal_2']['count'] > 0) 
		{
			$userResponse = 'src="/static/img/thumbs-down.png"';
			$negativeResponses++;
		}
		else
		{
			$userResponse = '';
		}
		
		// add the user's data to the user storage 
		$users[] = array(
				'user_id' => $userId,
				'response' => $userResponse);
		
		// add a user_id to the list
        $userIdList[] = $userId;
	}

	// calculate the percentage of positive/negative responses across 
	// all users for a url
	$percentPositive = 0;
    $percentNegative = 0;
	if (count($users) > 0 )
	{
	   $percentPositive = round(($positiveResponses * 100) / count($users));
	   $percentNegative = round(($negativeResponses * 100) / count($users));
	}
    
	// fetch the message that was shared with the url from the awe.sm Stats API
	$encodedUrl = urlencode($url);
    $messageApiCall = "http://api.awe.sm/stats/range.json?".
            "v=3&" .
            "key=" . API_KEY . "&" .
            "user_id={$sharerUserId}&" .
            "original_url={$encodedUrl}&" .
            "group_by=awesm_id&" .
            "with_metadata=true&" .
            "per_page=1"; 
    error_log("Fetch the url message Stats API call is {$messageApiCall}");
    $ch = curl_init($messageApiCall);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $results = json_decode($response, true);
    
    // extract the message
    $message = $results['groups'][0]['metadata']['notes'];
	
    // add all the data extracted and calculated to the url storage
	$urlData[] = array(
			'url' => $url,
			'users' => $users,
			'percent_positive' => $percentPositive,
			'percent_negative' => $percentNegative,
            'message' => $message,
	        'title' => $urlTitle,
	        'icon_url' => $urlIconUrl);
} 
//error_log("All the extracted data url data is: " . print_r($urlData, true));

// fetch twitter user information from the Twitter API
$friendsApiResults = $connection->get(
	'users/lookup',
	array(
		'user_id' => implode(',', array_unique($userIdList))
	)
);

// arrange twitter user data in an associative array for easy lookups
$friendsData = array();
foreach($friendsApiResults as $friendsApiResult) {
	$friendsData[$friendsApiResult->id] = array(
			'id' => $friendsApiResult->id,
			'profile_image_url' => $friendsApiResult->profile_image_url,
			'name' => $friendsApiResult->name,
			'screen_name' => $friendsApiResult->screen_name);
}

?>

<h1>Cute On Me?</h1>

<?php if (!empty($urlData)) {?>

<h2>Your Results</h2>

<!-- Iterate over urls -->
<?php
		foreach($urlData as $url) {
?>
<div class="span-16 clearfix result">
	<div class="span-10">
		<h3><a href="<?= $url['url'] ?>"><?=$url['title'] ?></a></h3>
		<p><?= $url['message'] ?></p>
		<?php foreach($url['users'] as $user){ ?>
			<p><img src="<?= $friendsData[$user['user_id']]['profile_image_url']?>" 
			     alt="" width="30" height="30" /> <?= $friendsData[$user['user_id']]['screen_name']?> 
			   <img <?= $user['response'] ?> alt="cute" width="30" height="30" />
		    </p>
		<?php } ?>
	</div>
	<div class="span-6 last">
		<p class="right">
			<img src="/static/img/thumbs-up.png" alt="cute" width="30" height="30" /> 
			<?= $url['percent_positive'] ?>%
			<img src="/static/img/thumbs-down.png" alt="not cute" width="30" height="30" /> 
			<?= $url['percent_negative'] ?>%
		</p>
	</div>
</div>
<?php
		}
	} else {
?>
<h3>You haven't asked any opinions yet.</h3>
<?php		
	}
?>	
<!-- Iterate End -->

<form action="share.php" method="get"><p class="right"><input type="submit" value="Ask For Advice" class="button" /></p></form>

<?php
require('./template-footer.php');	
?>