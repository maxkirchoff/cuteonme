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

// extract values from and session
$apiKey = API_KEY;
$sharerUserId = $_SESSION['access_token']['user_id'];

// fetch original urls and conversions
$statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&group_by=original_url&" .
    "&sort_type=shared_at&user_id={$sharerUserId}";
error_log("Stats api is {$statsApiCall}");
$ch = curl_init($statsApiCall);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$results = json_decode($response, true);

$urlData = array();
$uniqueUserIds = array();

foreach ($results['groups'] as $originalUrlGroup)
{
    // extract the url 
    $url = $originalUrlGroup['original_url'];
    
    
    // fetch the users the url was shared to
    $encodedUrl = urlencode($url);
    $statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&original_url={$encodedUrl}&" .
        "group_by=tag&with_conversions=true&user_id={$sharerUserId}";
    error_log("Stats api call {$statsApiCall}");
    
    $ch = curl_init($statsApiCall);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $results = json_decode($response, true);
        
    // for each user, calculate if they said yes/no
	$users = array();
	$positiveResponses = 0;
	$negativeResponses = 0;
	foreach ($results['groups'] as $tagGroup)
	{
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
		
		
		$users[] = array(
				'user_id' => $tagGroup['tag'],
				'response' => $userResponse);
		$uniqueUserIds[$tagGroup['tag']] = $tagGroup['tag'];
	}

	if (count($users) > 0 )
	{
	   $percentPositive = round(($positiveResponses * 100) / count($users), 2);
	   $percentNegative = round(($negativeResponses * 100) / count($users), 2);
	}
	else 
	{
	   $percentPositive = 0;
	   $percentNegative = 0;    
	}
	
	// fetch metadata associated with a url
    $statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&original_url={$encodedUrl}&" .
            "with_metadata=true&user_id={$sharerUserId}&per_page=1";
    error_log("Stats api is {$statsApiCall}");
    $ch = curl_init($statsApiCall);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $results = json_decode($response, true);
	$urlTitle = $results['group'][0]['metadata']['title'];
	$urlIconUrl = $results['group'][0]['metadata']['icon_url'];
    
	// fetch the message that was shared with the url
    $statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&group_by=awesm_id&" .
           "user_id={$sharerUserId}&original_url={$encodedUrl}&per_page=1&with_metadata=true";
    error_log("Stats api is {$statsApiCall}");
    $ch = curl_init($statsApiCall);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $results = json_decode($response, true);
    $message = $results['groups'][0]['metadata']['notes'];
	
	// fetch metadata
    
	
	$urlData[] = array(
			'url' => $url,
			'users' => $users,
			'percent_positive' => $percentPositive,
			'percent_negative' => $percentNegative,
            'message' => $message,
	        'title' => $urlTitle,
	        'icon_url' => $urlIconUrl);
} 

error_log("Url data is " . print_r($urlData, true));

// fetch user information
$friendsData = array();
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

?>

<h1>Cute On Me?</h1>

<?php if (!empty($urlData)) {?>

<h2><span>Your Results</h2>


<!-- Repeat Start -->
<?php
		foreach($urlData as $url) {
?>
<div class="span-16 clearfix result">
	<div class="span-10">
		<h3><a href="<?= $url['url'] ?>"><?= empty($url['title']) ? $url['url'] : $url['title'] ?></a></h3>
		<p><?= $url['message'] ?></p>
		<?php foreach($url['users'] as $user){ ?>
			<p><img src="<?= $friendsData[$user['user_id']]['profile_image_url']?>" alt="" width="30" height="30" /> <?= $friendsData[$user['user_id']]['screen_name']?> <img <?= $user['response'] ?> alt="cute" width="30" height="30" /></p>
		<?php } ?>
	</div>
	<div class="span-6 last">
		<p class="right">
			<img src="/static/img/thumbs-up.png" alt="cute" width="30" height="30" /> <?= $url['percent_positive'] ?>%
			<img src="/static/img/thumbs-down.png" alt="not cute" width="30" height="30" /> <?= $url['percent_negative'] ?>%
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
<!-- Repeat End -->

<form action="share.php" method="get"><p class="right"><input type="submit" value="Ask For Advice" class="button" /></p></form>

<?php
require('./template-footer.php');	
?>