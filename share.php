<?php
require('./template-header.php');

// Setup Twitter Connection
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['access_token']['oauth_token'], $_SESSION['access_token']['oauth_token_secret']);

print_r($_SESSION);

// Get the signed in user's Twitter friend list
$user = $connection->get('account/verify_credentials');
echo 'asdfasdf';
print_r($user);

$friends = $connection->get(
	'friends/ids',
	array (
		'user_id'	=> $user['user_id']
	)
);

switch ($connection->http_code) {
	case 200:
	
		break;
	
	default:
	
		break;
}


// 

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
					(insert friend list)
					
					<input type="text" name="friends[]" />
					<input type="text" name="friends[]" />
					<input type="text" name="friends[]" />
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