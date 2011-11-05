<?php

/**
 * See the README.md for a guide to reading the code, because accessing this page
 * requires you to authenticate with Twitter which is a muli-step flow.
 * 
 * This is the dashboard for the application.  You are redirected here once
 * you have authenticated with Twitter and granted the awe.sm CuteOn.Me Twitter
 * application access.  This page displays all the URLs you have shared with
 * your friends as well as their responses.
 *
 * The included singed-in-check file completes the OAuth flow, confirms the user is
 * logged in, or redirects new users back to the login page.  The awe.sm Stats API
 * is called to fetch all of the urls you shared with your friends, what friends
 * you shared with, what their respones were, and additional metadata about the url.
 * The API response is traversed so that each friend's action can be calculated
 * from their conversion goals, data can be aggregated in a smaller data array,
 * and a list of Twitter friend user IDs can be collected. Also, for each url
 * shared an awe.sm Stats API is made to fetch the message that was passed to
 * their friends.  Using the list of Twitter friend user IDs, user details are
 * looked up.  Finally, the URLs collected are iterated over in HTML to display
 * the data collected in the dashboard's HTML.
 */

$title = 'Cute On Me Results';
require('./signed-in-check.php');
require('./template/header.php');

// Build OAuth object
$connection = new TwitterOAuth(
		CONSUMER_KEY,
		CONSUMER_SECRET,
		$_SESSION['access_token']['oauth_token'],
		$_SESSION['access_token']['oauth_token_secret']
);

// Extract values from the request and session
$sharerUserId = $_SESSION['access_token']['user_id'];

// Initial pagination setup
if (empty($_REQUEST['page'])) {
	$pageNumber = 1;
	$previousPageNumber = 0;
} else {
	$pageNumber = $_REQUEST['page'];
	$previousPageNumber = $pageNumber - 1;
}

// Fetch original URLs, users, and conversions from the awe.sm Stat API
$originalUrlApiUrl = "http://api.awe.sm/stats/range.json?" .
		"v=3&" .
		"key=" . API_KEY . "&" .
		"user_id={$sharerUserId}&" .
		"group_by=original_url&" . 
		"pivot=tag&" .
		"with_metadata=true&" .
		"with_conversions=true&" .
		"sort_type=shared_at&" .
		"page={$pageNumber}";
error_log("Fetch original_urls Stats API URL is {$originalUrlApiUrl}");
$ch = curl_init($originalUrlApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$results = json_decode($response, true);

// Finish setting up pagination
if ($results['last_offset'] == $results['total_results']) {
	// There are no more results
	$nextPageNumber = 0;
} else {
	// There are more results
	$nextPageNumber = $pageNumber + 1;
}

// Create a place to store url data
$urlData = array();

// Create a list of all the twitter user_ids
$userIdList = array();

// Create a string for Embed.ly
$embedlyUrls = '';

// Iterate over the URLs found
foreach ($results['groups'] as $originalUrlGroup)
{
	// Extract the URL
	$url = $originalUrlGroup['original_url'];
	
	// Add the URL to the Embed.ly string
	$embedlyUrls .= urlencode($url).',';

	// Extract URL metadata
	$urlTitle = $originalUrlGroup['metadata']['title'];
	if (empty($urlTitle)) $urlTitle = $url;
	$urlIconUrl = $originalUrlGroup['metadata']['icon_url'];

	// Create a place to store user data
	$users = array();

	// Iterate over the users
	$positiveResponses = 0;
	$negativeResponses = 0;
	foreach ($originalUrlGroup['pivots'] as $tagGroup)
	{  
		// Extract the user_id
		$userId = $tagGroup['tag'];
	  
		// Calculate whether the user responsed positively or negatively
		// goal_1 is positive
		// goal_2 is negative
		if ($tagGroup['conversions']['goal_1']['count'] > 0)
		{
			$userResponse = 'positive';
			$positiveResponses++;
		}
		elseif ($tagGroup['conversions']['goal_2']['count'] > 0)
		{
			$userResponse = 'negative';
			$negativeResponses++;
		}
		else
		{
			$userResponse = 'unknown';
		}

		// Add the user's data to the user storage
		$users[] = array(
				'user_id' => $userId,
				'response' => $userResponse
		);

		// Add a user_id to the list
		$userIdList[] = $userId;
	}

	// Calculate the percentage of positive/negative responses across all users 
	// for a URL
	$percentPositive = 0;
	$percentNegative = 0;
	if (count($users) > 0 )
	{
		$percentPositive = round(($positiveResponses * 100) / count($users));
		$percentNegative = round(($negativeResponses * 100) / count($users));
	}

	// Fetch the message that was shared with the URL from the awe.sm Stats API
	$encodedUrl = urlencode($url);
	$messageApiUrl = "http://api.awe.sm/stats/range.json?".
			"v=3&" .
			"key=" . API_KEY . "&" .
			"user_id={$sharerUserId}&" .
			"original_url={$encodedUrl}&" .
			"group_by=awesm_id&" .
			"with_metadata=true&" .
			"per_page=1"; 
	error_log("Fetch the url message Stats API URL is {$messageApiUrl}");
	$ch = curl_init($messageApiUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	$results = json_decode($response, true);

	// Extract the message
	$message = $results['groups'][0]['metadata']['notes'];

	// Add all the data extracted and calculated to the URL storage
	$urlData[] = array(
			'url' => $url,
			'users' => $users,
			'percent_positive' => $percentPositive,
			'percent_negative' => $percentNegative,
			'message' => $message,
			'title' => $urlTitle,
			'icon_url' => $urlIconUrl
	);
}
//error_log("All the extracted data url data is: " . print_r($urlData, true));

// Fetch Twitter user information from the Twitter API
$friendsApiResults = $connection->get(
		'users/lookup',
		array('user_id' => implode(',', array_unique($userIdList)))
);

// Arrange Twitter user data in an associative array for easy lookups
$friendsData = array();
foreach($friendsApiResults as $friendsApiResult) 
{
	$friendsData[$friendsApiResult->id] = array(
			'id' => $friendsApiResult->id,
			'profile_image_url' => $friendsApiResult->profile_image_url,
			'name' => $friendsApiResult->name,
			'screen_name' => $friendsApiResult->screen_name);
}

?>

<h1>Cute On Me?</h1>

<div class="span-16 clearfix">
	<div class="span-8">
		<a href="signout.php" class="buttonMinor">Sign Out</a>
	</div>
	<div class="span-8 last right relative extensionCalloutContainer">
		<a href="share.php" class="button">Ask for Advice</a>
		<?php
			/* Detect Chrome */
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false):
		?>
			<div class="extensionCallout">
				Try the <a href="/chrome/cuteonme.crx">Chrome extension</a> for faster sharing!
				<div class="extensionCalloutNotch"></div>
			</div>
		<?php
			endif;
		?>
	</div>
</div>
	
<?php if (!empty($urlData)) { ?>

<h2>Your Results</h2>

<!-- Iterate over urls -->
<?php
	$i = -1;
	foreach($urlData as $url) {
		$i++;
?>
	<div class="span-16 clearfix">
		<div class="span-12">
			<h3><a href="<?= $url['url'] ?>" id="pageTitle<?= $i ?>">
				<?= strlen($url['title']) > 30 ? substr($url['title'], 0, 30) . "..." : $url['title']; ?>
			</a></h3>
	<?php if (!empty($url['message'])): ?>
			<blockquote>&ldquo;<?= $url['message'] ?>&rdquo;</blockquote>
	<?php endif; ?>
		</div>
		<div class="span-4 last">
			<p class="right"><img src="/static/img/thumbs-up.png" alt="cute"
				width="30" height="30" /> <?= $url['percent_positive'] ?>% <img
				src="/static/img/thumbs-down.png" alt="not cute" width="30" height="30" />
			<?= $url['percent_negative'] ?>%</p>
		</div>
	</div>
	<div class="span-16 clearfix result">
		<div class="span-11">

			<?php
				foreach($url['users'] as $user){
					switch($user['response']) {
						case 'positive':
							$responseImage = '/static/img/thumbs-up.png';
							$responseText = $friendsData[$user['user_id']]['screen_name'].' likes';
							break;
						case 'negative':
							$responseImage = '/static/img/thumbs-down.png';
							$responseText = $friendsData[$user['user_id']]['screen_name'].' dislikes';
							break;
						default:
							$responseImage = '/static/img/qmark.png';
							$responseText = $friendsData[$user['user_id']]['screen_name'].' has not responded';
					}
			?>
				<p>
					<img src="<?= $responseImage ?>" alt="<?= $responseText ?>" width="30" height="30" />
					<img src="<?= $friendsData[$user['user_id']]['profile_image_url']?>"
					alt="" width="30" height="30" /> <?= $friendsData[$user['user_id']]['screen_name'] ?>
				</p>
			<?php } ?>

		</div>
		<div class="span-5 last" id="pagePreview<?= $i ?>"></div>
	</div>
<?php 
		}
	} else { ?>
	<h3>You haven't asked any opinions yet.</h3>
<?php 
	} 
?>
<!-- Iterate End -->

<!-- Pagination -->
<p class="right small">
<?php if ($previousPageNumber != 0): ?>
	<a href="/?page=<?= $previousPageNumber ?>">&laquo; Newer</a>
<?php endif; ?>
	Page <?= $pageNumber ?>
<?php if ($nextPageNumber != 0): ?>
	<a href="/?page=<?= $nextPageNumber ?>">Older &raquo;</a>
<?php endif; ?>
</p>

<?php if (!empty($embedlyUrls)): ?>
<script type="text/javascript">
// Update the original_urls with Embed.ly greatness
// Make a huge 'url' because jQuery incorrectly double encodes
$.ajax({
	type: "GET",
	url: "http://api.embed.ly/1/oembed?key=805bd726828511e088ae4040f9f86dcd&urls=<?= substr($embedlyUrls, 0, -1) ?>&maxwidth=390&maxheight=390",
	dataType: "jsonp",
	success: function(r) {
		for (var i = 0, iMax = r.length; i < iMax; i++) {
			if (r[i].title) {
				document.getElementById("pageTitle"+i).innerHTML = r[i].title;
			}
			if (r[i].thumbnail_url) {
				document.getElementById("pagePreview"+i).innerHTML = '<img src="'+r[i]["thumbnail_url"]+'" class="previewImg"/>';
			}
		}
	}
});

</script>
<?php endif; ?>

<?php require('./template/footer.php'); ?>