<?php
	$unauthedAllowed = true;
	require('./template-header.php');
?>

<div>
<p>
<span class="sharer"><img src="<?php if (isset($_REQUEST['sharer_icon_url'])){echo $_REQUEST['sharer_icon_url'];} else {echo 'http://geektyrant.com/storage/post-images-2011/the-lone-ranger_l.jpg';}?>" height="32"> @<?= $_REQUEST['sharer'] ?></span> wants to know: <span class="message">&quot;<?= $_REQUEST['message'] ?>&quot;</span>
</p>

<p>
<input type="button" id="yes" value="Yes" onclick="AWESM.convert('goal_1',0);"/>
&nbsp;
<input type="button" id="no" value="No" onclick="AWESM.convert('goal_2',0);"/>
</p>
</div>

<iframe src="<?= $_REQUEST['url'] ?>" width="100%" height="82%" frameborder="0"></iframe>

<script src="http://widgets.awe.sm/v3/widgets.js?key=103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743"></script>

<?php
	require('./template-footer.php');
?>