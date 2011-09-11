<?php
require('./template-header.php');

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
?>

<h2>Share</h2>

<form action="share-submit.php" method="post">
	<table>
		<tbody>
			<tr>
				<th>Link</th>
				<td><input type="text" name="url" /></td>
			</tr>
			<tr>
				<th>Whose opinion do you want?</th>
				<td>
<?php
	foreach($friends as $friend) {
?>
					<label>
						<input type="checkbox" name="friends[]" value="<?= $friend['id'] ?>" />
						<img src="<?= $friend['profile_image_url'] ?>" alt="" />
						<span title="@<?= $friend['screen_name'] ?>"><?= $friend['name'] ?></span>
					</label>
					<br />
					
<?php
	}
?>					
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td></td>
				<td><input type="submit" value="Share"/></td>
			</tr>
		</tfoot>
	</table>

</form>

<?php
require('./template-footer.php');	
?>