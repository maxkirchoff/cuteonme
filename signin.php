<?php
	$unauthedAllowed = true;
	require('./template/header.php');
?>

<h1>Cute On Me?</h1>
<h2><span>Quick Fashion Advice From Your Friends</span></h2>

<div class="span-16 clearfix bottom">
	<div class="span-7 prepend-1">
		<p>When you're trying to decide what to buy, let your social network help you make up your mind.</p>
		
		<p>Share what you're looking to buy. Collect feedback from the people who know you best.</p>
		
		<p class="center h3">To begin <a href="signin-redirect.php"><img src="/static/img/darker.png" alt="Sign in with Twitter"/></a></p>
	</div>
	<div class="span-7 last"><img src="/static/img/home.jpg" alt=""/></div>
</div>

<?php
	require('./template/footer.php');
?>
