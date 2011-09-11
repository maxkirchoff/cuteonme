<?php
session_start();

require_once('./config.php');
require_once('./lib/twitteroauth/twitteroauth/twitteroauth.php');

if (!empty($_REQUEST['oauth_token']) && !empty($_REQUEST['oauth_verifier']) && empty($_SESSION['access_token'])) {
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	
	// Request access tokens from twitter
	$accessToken =  $connection->getAccessToken($_REQUEST['oauth_verifier']);
	
	// fetch more user details
	$usersDetails = $connection->get(
		'users/lookup',
		array('user_id' => $accessToken['user_id']));
	$accessToken['profile_image_url'] = $usersDetails[0]->profile_image_url;

	// Save the access tokens. Normally these would be saved in a database for future use.	
	$_SESSION['access_token'] = $accessToken;
				
	
} elseif (empty($_SESSION['access_token']) && empty($unauthedAllowed)) {
	// No session and the page requires a session
	header('Location: ./signin.php');
}

// Setup Twitter Connection
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['access_token']['oauth_token'], $_SESSION['access_token']['oauth_token_secret']);

// Get list of friends
$friends = array();

// Get the signed in user's Twitter friends' IDs
$friendsIds = $connection->get(
	'friends/ids',
	array (
		'user_id'	=> $_SESSION['access_token']['user_id']
	)
);
// TODO: $connection->http_code

// Now, find the information about those friends in a batched manner.
$friendsBatch = array_chunk($friendsIds, 100);

foreach($friendsBatch as $batch) {
	// Get friend details
	$friendsDetails = $connection->get(
		'users/lookup',
		array(
			'user_id' => implode(',', $batch)
		)
	);
	
	// Save select friend details
	foreach($friendsDetails as $friendDetails) {
		if ($friendDetails->following === true) {
			$friends[] = array(
				'id' => 				$friendDetails->id,
				'profile_image_url' =>	$friendDetails->profile_image_url,
				'name' =>				$friendDetails->name,
				'screen_name' =>		$friendDetails->screen_name
			);
		}
	}
}

$url = $_REQUEST['url'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?= (!empty($title))? $title : 'Cute On Me?' ?></title>
	<link rel="stylesheet" href="/static/css/screen.css" />

</head>
<body>
	<div><div class="container">


<form action="share-submit.php" method="post">
	<h3 class="bottomless">Link</h3>
	<p class="label">Paste the page where your friends can check it out</p>
	<p><input type="text" name="url" class="text" value="<?= $url ?>"/></p>
	
	<h3 class="bottomless">Message</h3>
	<p class="label">One sentence &mdash; this has to fit in a Twitter message</p>
	<p><textarea name="message" maxlength="120">Is this cute on me?</textarea></p>

	<h3 class="bottomless">Select your trusted friends.</h3>
	<p class="label">We&rsquo;ll send a custom message to each one</p>
	<ul class="friends">
<?php
	foreach($friends as $friend) {
?>
		<li class="friend">
			<label>
				<input type="checkbox" name="friends[]" value="<?= $friend['id'] ?>" />
				<img src="<?= $friend['profile_image_url'] ?>" alt="" width="30" height="30" />
				<span title="@<?= $friend['screen_name'] ?>"><?= 
					(strlen($friend['name']) > 12)? 
						substr($friend['name'], 0, 11) . "..."
					:
						$friend['name']
				?></span>
			</label>
		</li>
<?php
	}
?>					
	</ul>

	<p class="right"><input type="submit" value="Ask For Advice" class="button" /></p>
</form>

</div></div>
</body>
</html>