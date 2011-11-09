<?php

/**
 * The share page provides a form to share a URL with specific Twitter friends.
 * The Twitter API is called a few times, to fetch friends that you follow and
 * you follow as well (aka mutual friends), as well as to fetch their user details.
 * The data from the form is sent to the share-submit page which handles the
 * rest of the sharing logic.
 */

$title = 'Is this cute on me?';
require('./signed-in-check.php');
require('./template/header.php');
?>

<h1>Cute On Me?</h1>
<h2><span>Of course it is, but let&rsquo;s make sure&hellip;</span></h2>

<form action="share-submit.php" method="post">

<?php if (!empty($_REQUEST['ref'])): ?>
	<div class="hidden">
<?php endif; ?>
	<h3 class="bottomless">Link</h3>
	<p class="label">Paste the page where your friends can check it out</p>
	<p><input type="text" name="url" class="text"
		placeholder="http://amazon.com/item123" value="<?= @$_REQUEST['url'] ?>" /></p>
<?php if (!empty($_REQUEST['ref'])): ?>
	</div>
<?php endif; ?>
	<h3 class="bottomless">Message</h3>
	<p class="label">One sentence &mdash; this has to fit in a Twitter message</p>
	<p><textarea name="message" maxlength="120">Do you think this would be cute on me? </textarea></p>
	
	<h3 class="bottomless">Select your friends with great taste.</h3>
	<p class="label clearfix">We&rsquo;ll send an individual message to each one</p>
	
	<div>
		<select id="listFilter">
			<option value="">All Friends</option>
			<optgroup id="lists" label="Or select a list:"></optgroup>
		</select>
		<input type="search" id="friendSearch" placeholder="Search for a friend" />
	</div>
	<ul id="friends" class="loading"></ul>
	
<?php if (!empty($_REQUEST['ref'])): ?>
	<input type="hidden" name="ref" value="<?= @$_REQUEST['ref'] ?>" />
<?php endif; ?>
	
	<div class="span-16 clearfix">
		<div class="span-8">
		<?php if (empty($_REQUEST['ref'])): ?>
			<a href="/" id="cancel" class="buttonMinor">Never Mind</a>
		<?php else: ?>
			&nbsp;
		<?php endif; ?>
		</div>
		<div class="span-8 last right">
			<input type="submit" value="Ask For Advice" class="button" />
		</div>
	</div>
</form>

<script type="text/javascript">

// Load friends and lists
$.ajax({
	type: "GET",
	url: "/xhr-friends.php",
	dataType: "json",
	success: function(r) {
		console.log(r);
		
		// Generate friends markup
		var friendsLis = "";
		
		for (var i = 0, iMax = r.friends.length; i < iMax; i++) {
			friendsLis += 
				'<li class="friend" data-name="'+ r.friends[i].search_name +'"><label>'
			+		'<input type="checkbox" id="friend-'+ r.friends[i].id +'" name="friends[]" value="'+ r.friends[i].id + '" />'
			+		'<img src="'+ r.friends[i].profile_image_url +'" alt="" width="30" height="30" /> '
			+		'<span title="@'+ r.friends[i].screen_name +'">'+ r.friends[i].display_name +'</span>'
			+	'</label></li>';
		}
		
		// Add to document. Remove 'loading' class.
		var friendsUlDom = document.getElementById("friends");
		friendsUlDom.innerHTML = friendsLis;
		friendsUlDom.className = "";
		
		// Register clicks on users to the underlying checkbox
		$('input[type="checkbox"]').change(function(e) {
			// Change containing <li> style to indicate selection
			if (e.target.checked) {
				$(this).parent().parent().addClass('friendSelected').removeClass("hidden");
			} else {
				$(this).parent().parent().removeClass('friendSelected');
			}
		});
		
		// Setup lists list
		var listsOptions = "";
		for (i = 0, iMax = r.lists.length; i < iMax; i++) {
			listsOptions += '<option value="'+ r.lists[i].friends.join(",") +'">'+ r.lists[i].name +'</option>';
		}
		
		// Using jQuery's html() here because of IE bug.
		$("#lists").html(listsOptions);
	},
	error: function() {
		alert("Twitter appears to be having trouble. Sorry about that. Please try again in a few minutes.");
	}
});

// Bind to friend list selector
$('#listFilter').change(function(e) {
	console.log(e);
	
	var searchDom = document.getElementById("friendSearch");
	
	if (e.target.selectedIndex === 0) {
		// Display all friends
		$(".friend").removeClass("hidden");
		
		// Deselect friends, trigger change event
		$("input:checked").attr("checked", false).trigger("change");
		
		// Show search box
		searchDom.className = "";
		
	} else {
		// Filter friends list
		
		// Disable, clear search
		searchDom.className = "hidden";
		searchDom.value = "";
		
		// Deselect any selected friends, trigger change event
		$("input:checked").attr("checked", false).trigger("change");
		
		// Hide all friends
		$(".friend").addClass("hidden");
		
		// Display and select friends on list
		// Explode value of friends list
		// Loop through, displaying and selecting friends in list
		var friendsToSelect = e.target.value.split(",");
		for (var i = 0, iMax = friendsToSelect.length; i < iMax; i++) {
			$("#friend-"+friendsToSelect[i]).attr("checked", true).trigger("change");
		}
	}
});

// Disable enter/return key
$('#friendSearch').keypress(function(e) {
	if (e.which == 13) {
		e.preventDefault();
	}
});

// Perform search
$('#friendSearch').keyup(function(e) {
	if (e.target.value) {
		// Hide all friends
		$('.friend').addClass('hidden');
		
		// Show matching friends
		$('li[data-name*="' + e.target.value.toLowerCase() + '"]').removeClass('hidden');
	
	} else {
	
		// Show all friends
		$('.friend').removeClass('hidden');
	}
});
</script>

<?php require('./template/footer.php'); ?>