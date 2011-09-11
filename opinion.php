<?php
	$unauthedAllowed = true;
	require('./template-header.php');
?>


<p>
Person who shared (sharer)
<?= $_REQUEST['sharer'] ?>
</p>

<p>
Message:
<?= $_REQUEST['message'] ?>
</p>

<p>Page:</p>
<iframe src="<?= $_REQUEST['url'] ?>"></iframe>

<input type="button" id="yes" value="Yes"/>

<input type="button" id="no" value="No" />

<?php
	require('./template-footer.php');
?>