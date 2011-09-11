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
	<title><?= (!empty($title))? $title : 'Cute On Me?' ?></title>
	<link rel="stylesheet" href="/static/css/screen.css" />

</head>

<div>
<div class="container">
<div class="span-10">
	<p>
		<img src="<?= $sharerIconUrl ?>" alt="" width="50" height="50"> 
		@<?= $_REQUEST['sharer'] ?> wants to know: &quot;<?= $_REQUEST['message'] ?>&quot;
	</p>
</div>
<div class="span-6 last">
<p>
	<input type="image" id="yes" value="Yes" onclick="AWESM.convert('goal_1',0);" src="/static/img/thumbs-up.png" width="30" height="30">
	<input type="image" id="no" value="No" onclick="AWESM.convert('goal_2',0);" src="/static/img/thumbs-down.png" width="30" height="30"/>
</p>
</div>
</div>
</div>

<iframe src="<?= $_REQUEST['url'] ?>" width="100%" height="84%" frameborder="0"></iframe>

<script src="http://widgets.awe.sm/v3/widgets.js?key=103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743"></script>

<?php
	require('./template-footer.php');
?>