<?php

/**
 * The opinion page is where a friend is redirected to after they click on a 
 * CuteOn.Me awe.sm URL that was sent to the via a Twitter direct message.
 * This page is run entirely by the values retreived from the query string.
 * The majority of page is an iframe displaying the URL the user shared. 
 * At the top of the page is the voting bar with the user's Twitter profile icon,
 * the message from the direct message, and a place for the friend to vote whether
 * they like or dislike the URL.  Selecting one or the other triggers a conversion
 * for that awe.sm URL so the friend's advice can be tracked.
 */

$unauthedAllowed = true;
require('./config.php');

if (empty($_REQUEST['sharer_icon_url']))
{
	$sharerIconUrl = 'http://geektyrant.com/storage/post-images-2011/the-lone-ranger_l.jpg';
}
else
{
	$sharerIconUrl = $_REQUEST['sharer_icon_url'];
}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Does this look cute on me?</title>
	<link rel="stylesheet" href="/static/css/screen.css" />
</head>

<body>

<div>
    <!--  Voting Bar  -->
	<div class="container" id="voteScreen">
		<div class="span-2">
			<img src="<?= $sharerIconUrl ?>" alt="" width="70" height="70" /> 
		</div>
		<div class="span-10">
			<p>
				<em>@<?= $_REQUEST['sharer'] ?> wants to know:</em><br/>
				&quot;<?= $_REQUEST['message'] ?>&quot;
			</p>
		</div>
		<div class="span-4 last right">
			<p>
				<input type="image" id="yes" value="Yes" onclick="voteYes()" src="/static/img/thumbs-up.png" width="70" height="70"/>
				<input type="image" id="no" value="No" onclick="voteNo()" src="/static/img/thumbs-down.png" width="70" height="70"/>
			</p>
		</div>
	</div>
	
	<div class="container" id="thankScreen" style="display: none">
		<div class="span-2">
			<img src="<?= $sharerIconUrl ?>" alt="" width="70" height="70" /> 
		</div>
		<div class="span-14 last">
			<p>
				Thanks for helping out <em>@<?= $_REQUEST['sharer'] ?></em>!<br /><a href="/">Need shopping advice yourself?</a>
			</p>
		</div>
	</div>
</div>

<!--  URL display   -->
<iframe src="<?= $_REQUEST['url'] ?>" width="100%" height="89%" frameborder="0"></iframe>

<!--  awe.sm Conversion Javascript  -->
<script src="http://widgets.awe.sm/v3/widgets.js?key=<?= API_KEY ?>"></script>
<script>
function voteYes() {
	AWESM.convert('goal_1',0);
	thank();
}
function voteNo() {
	AWESM.convert('goal_2',0);
	thank();
}
function thank() {
	document.getElementById('thankScreen').style.display = 'block';
	document.getElementById('voteScreen').style.display = 'none';
}
</script>

</body>
</html>