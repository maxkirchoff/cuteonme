<?php
	$unauthedAllowed = true;
	require('./template-header.php');
?>

<p>
<span class="sharer"><?= $_REQUEST['sharer'] ?></span> wants to know: <span class="message">&qout;<?= $_REQUEST['message'] ?>&quot;</span>
</p>

<p>
<input type="button" id="yes" value="Yes" onclick="AWESM.convert('goal_1',0);"/>
&nbsp;
<input type="button" id="no" value="No" onclick="AWESM.convert('goal_2',0);"/>
</p>


<iframe src="<?= $_REQUEST['url'] ?>"></iframe>




<script src="http://widgets.awe.sm/v3/widgets.js?key=103dbc7485b55313c91aa29176f8ee2ba3e95fe949c574aa5f2505e26a5bb743"></script>

<?php
	require('./template-footer.php');
?>