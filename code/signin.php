<?php

/**
 * The signin page asks the user to authenticate with Twitter so that the
 * the awe.sm CuteOn.Me Twitter application can send direct messages to
 * the user's twitter friends.  The logic to do this is on the signin-redirect
 * page.
 */

require('./template/header.php');
?>

<h1>Cute On Me?</h1>
<h2><span>Quick Fashion Advice From Your Friends</span></h2>

<div class="span-16 clearfix bottom">
	<div class="span-7 prepend-1">
		<p>When you're trying to decide what to buy, let your social network
			help you make up your mind.</p>
		<p>Share what you're looking to buy. Collect feedback from the people
			who know you best.</p>
		<p class="center h3">To begin <a href="signin-redirect.php"> <img
			src="/static/img/darker.png" alt="Sign in with Twitter" /> </a></p>
	</div>
	<div class="span-7 last"><img src="/static/img/home.jpg" alt="" /></div>
</div>

<div class="demo-notice"><em>Cute On Me</em> is a demonstration of the awe.sm platform. <a href="http://developers.awe.sm/solutions/cuteonme/">Learn more &raquo;</a></div>

<?php require('./template/footer.php'); ?>