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
$statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&group_by=original_url&pivot=tag&with_conversions=true&user_id={$sharerUserId}";
//echo "<br>url called is " .print_r($statsApiCall, true) . "<br>";
$ch = curl_init($statsApiCall);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$results = json_decode($response, true);


$urlData = array();
$uniqueUserIds = array();

foreach ($results['groups'] as $originalUrl)
{
	$users = array();
	$positiveResponses = 0;
	$negativeResponses = 0;
	foreach ($originalUrl['pivots'] as $pivot)
	{
		if ($pivot['conversions']['goal_1']['count'] > 0)
		{
			$userResponse = 'src="/static/img/thumbs-up.png"';
			$positiveResponses++;
		}
		elseif ($pivot['conversions']['goal_2']['count'] > 0) 
		{
			$userResponse = 'src="/static/img/thumbs-down.png"';
			$negativeResponses++;
		}
		else
		{
			$userResponse = '';
		}
		
		
		$users[$pivot['tag']] = array(
				'user_id' => $pivot['tag'],
				'response' => $userResponse);
		$uniqueUserIds[$pivot['tag']] = $pivot['tag'];
	}

	$percentPositive = ($positiveResponses * 100) / count($users);
	$percentNegative = ($negativeResponses * 100) / count($users);
	$urlData[$originalUrl['original_url']] = array(
			'url' => $originalUrl['original_url'],
			'users' => $users,
			'percent_positive' => $percentPositive,
			'percent_negative' => $percentNegative);
} 

// fetch original url metadata
$statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&group_by=original_url&with_metadata=true&user_id={$sharerUserId}";
//echo "<br>url called is " .print_r($statsApiCall, true) . "<br>";
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

// foreach an awesm_id for the original urls
$awesmIds = array();
foreach ($urlData as &$url)
{
	$encodedUrl = urldecode($url['url']);
	$statsApiCall = "http://api.awe.sm/stats/range.json?v=3&key={$apiKey}&group_by=awesm_id&user_id={$sharerUserId}&original_url=$encodedUrl&per_page=1";
	//error_log("Stats api is {$statsApiCall}");
	$ch = curl_init($statsApiCall);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	$results = json_decode($response, true);
	
	$awesmIds[] = $results['groups'][0]['awesm_id'];
}

// fetch notes for the original_urls
$encodedUrl = urldecode($url['url']);
$statsApiCall = "http://api.awe.sm/stats/awesm_ids/batch.json?v=3&key={$apiKey}&with_metadata=true&awesm_ids=" . implode(',', $awesmIds) ;
//error_log("Stats api is {$statsApiCall}");
$ch = curl_init($statsApiCall);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$results = json_decode($response, true);

$notesData = array();
foreach ($results['awesm_ids'] as $awesmId)
{
	$notesData[$awesmId['metadata']['original_url']] = array(
			'url' => $awesmId['metadata']['original_url'],
			'notes' => $awesmId['metadata']['notes']);
}

//error_log("Awesm_ids are " . print_r($awesmIds, true));
//error_log("Notes data is " . print_r($notesData, true));
//echo "friends array is " . print_r($friendsData, true);
//error_log("Url data is " . print_r($urlData, true));
//echo "Url metdata is " . print_r($urlMetadata, true);

?>

<h1>Cute On Me?</h1>

<?php if ($results['total_results'] != 0) {?>

<h2><span>Your Results</h2>


<!-- Repeat Start -->
<?php
		foreach($urlData as $url) {
?>
<div class="span-16 clearfix result">
	<div class="span-10">
		<h3><a href="<?= $url['url'] ?>"><?= empty($urlMetadata[$url['url']]['title']) ? $url['url'] : $urlMetadata[$url['url']]['title'] ?></a></h3>
		<p><?= $notesData[$url['url']]['notes'] ?></p>
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