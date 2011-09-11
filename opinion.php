<?php
$unauthedAllowed = true;

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
				Thanks for helping out <em>@<?= $_REQUEST['sharer'] ?></em>!<br /><a href="/">Need fashion advice yourself?</a>
			</p>
		</div>
	</div>
</div>

<iframe src="<?= $_REQUEST['url'] ?>" width="100%" height="84%" frameborder="0"></iframe>

<script src="http://widgets.awe.sm/v3/widgets.js?key=103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743"></script>
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