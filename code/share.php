<?php

/**
 * The share page provides a form to share a URL with specific Twitter friends.
 * The Twitter API is called a few times, to fetch friends that you follow and
 * you follow as well (aka mutual friends), as well as to fetch their user details.
 * The data from the form is sent to the share-submit page which handles the
 * rest of the sharing logic.
 */

$title = 'Is this cute on me?';
require('./signed-in-check.php');
require('./template/header.php');

// Setup Twitter Connection
$connection = new TwitterOAuth(
	CONSUMER_KEY,
	CONSUMER_SECRET,
	$_SESSION['access_token']['oauth_token'],
	$_SESSION['access_token']['oauth_token_secret']
);

// Get list of friends
$friends = array();

// Get the signed in user's Twitter friends IDs
$friendsIds = $connection->get(
		'friends/ids',
		array (
				'user_id' => $_SESSION['access_token']['user_id'],
				'cursor' => -1
		)
);

// Get the signed in user's Twitter followers IDs
$follwersIds = $connection->get(
		'followers/ids',
		array (
				'user_id' => $_SESSION['access_token']['user_id'],
				'cursor' => -1
		)
);

// Create the users that are followers and friends
$mutualFriends = array_intersect($friendsIds->ids, $follwersIds->ids);

// Find the information about those friends in a batched manner.
$friendsBatch = array_chunk($mutualFriends, 100);
foreach($friendsBatch as $batch) 
{
	// Get friend details
	$friendsDetails = $connection->get(
			'users/lookup',
			array('user_id' => implode(',', $batch))
	);

	// Save friend details
	foreach($friendsDetails as $friendDetails) 
	{
		$friendName = strlen($friendDetails->name) > 12 ?
				substr($friendDetails->name, 0, 11) . "..." : $friendDetails->name;
		$friends[] = array(
				'id' => $friendDetails->id,
				'profile_image_url' => $friendDetails->profile_image_url,
				'screen_name' => $friendDetails->screen_name,
				'name' => $friendDetails->name,
				'display_name' => $friendName
		);
	}
}
?>

<h1>Cute On Me?</h1>
<h2><span>Of course it is, but let&rsquo;s make sure&hellip;</span></h2>

<form action="share-submit.php" method="post">

<?php if (!empty($_REQUEST['ref'])): ?>
	<div class="hidden">
<?php endif; ?>
	<h3 class="bottomless">Link</h3>
	<p class="label">Paste the page where your friends can check it out</p>
	<p><input type="text" name="url" class="text"
		placeholder="http://amazon.com/item123" value="<?= @$_REQUEST['url'] ?>" /></p>
<?php if (!empty($_REQUEST['ref'])): ?>
	</div>
<?php endif; ?>
	<h3 class="bottomless">Message</h3>
	<p class="label">One sentence &mdash; this has to fit in a Twitter message</p>
	<p><textarea name="message" maxlength="120">Do you think this would be cute on me? </textarea></p>
	
	<h3 class="bottomless">Select your friends with great taste.</h3>
	<p class="label clearfix">We&rsquo;ll send an individual message to each one
		<input type="search" id="friendSearch" placeholder="Search for a friend" />
	</p>
	
	<ul class="friends">
		<?php foreach($friends as $friend) { ?>
			<li class="friend" data-name="@<?= htmlspecialchars(strtolower($friend['screen_name'].' '.$friend['name'])) ?>"><label>
				<input type="checkbox" name="friends[]" value="<?= $friend['id'] ?>" />
				<img src="<?= $friend['profile_image_url'] ?>" alt="" width="30" height="30" /> 
				<span title="@<?= $friend['screen_name'] ?>"><?= $friend['display_name'] ?></span>
			</label></li>
		<?php } ?>
	</ul>
	
<?php if (!empty($_REQUEST['ref'])): ?>
	<input type="hidden" name="ref" value="<?= @$_REQUEST['ref'] ?>" />
<?php endif; ?>
	
	<div class="span-16 clearfix">
		<div class="span-8">
		<?php if (empty($_REQUEST['ref'])): ?>
			<a href="/" id="cancel" class="buttonMinor">Never Mind</a>
		<?php else: ?>
			&nbsp;
		<?php endif; ?>
		</div>
		<div class="span-8 last right">
			<input type="submit" value="Ask For Advice" class="button" />
		</div>
	</div>
</form>

<script type="text/javascript">
// Disable enter/return key
$('#friendSearch').keypress(function(e) {
	if (e.which == 13) {
		e.preventDefault();
	}
});

// Perform search
$('#friendSearch').keyup(function(e) {
	if (e.target.value) {
		// Hide all friends
		$('.friend').addClass('hidden');
		
		// Show matching friends
		$('li[data-name*="' + e.target.value.toLowerCase() + '"]').removeClass('hidden');
	
	} else {
	
		// Show all friends
		$('.friend').removeClass('hidden');
	}
});

$('input[type="checkbox"]').change(function(e) {
	// Change containing <li> style to indicate selection
	if (e.target.checked) {
		$(this).parent().parent().addClass('friendSelected');
	} else {
		$(this).parent().parent().removeClass('friendSelected');
	}
});
</script>

<?php require('./template/footer.php'); ?>