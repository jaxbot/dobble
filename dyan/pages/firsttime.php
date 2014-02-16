<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
   <html>
		<head>
			<base href='<?php echo $base; ?>'>
			<title>Dobble.me</title>
			<link rel=StyleSheet href='common/home.css' type='text/css'>
			<link rel=StyleSheet href='common/00248.css' type='text/css'>
			<script src='common/data.js'></script>
			<script src='common/sideframe.js'></script>
			<script src='common/ui.js'></script>
			<script src='common/user.js'></script>
			<script src='common/post.js'></script>
			<style>
			a:link,a:active,a:visited,a:hover { color: #252560; text-decoration: none}
			#header,a.highlight,span.commentcount,span.photocommentcount,#composer,input.button,#popupNotification,span.optionBarOption_selected,input.share,#userDropdown,div.tab_selected,div.tabcontainer,span.histogram {
				background-color:#252560;
			}
			</style>
			<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-25303672-1']);
  _gaq.push(['_setDomainName', '.dobble.me']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
		</head>
		<body>
			<iframe src='about:blank' name='datapusher' style='display:none'></iframe>
			<div id='messageBox'><div id='messageBoxCaption'>Caption</div><div id='messageBoxBody'>Body</div><input type='button' value='Ok' class='dialogButton' id='dialogOK' onClick='closeDialog(1);'><input type='button' value='Cancel' id='dialogCancel' class='dialogButton' onClick='closeDialog(0);'></div>
			<center><img src='img/logo_bigger.png'><br />
<?php

	$content = "
	<script src='common/firsttime.js'></script>
	<script>curUser = '".$_SESSION['P_usr']."';</script>
	<center>
		<div class='shadowedbox' id='page1' style='vertical-align: middle;min-height:300px;position:relative'>
			<center>
				<span style='font-size:27px;'>Welcome to Dobble.me</span>
			</center>
			
			<span style='font-size:14px;display:block;margin:auto;bottom:5px; right:5px;height:50%;width:50%;position:relative;'>
			<p>We'll let you dive in shortly, but first, let's get a couple things set up for you.</p>
			<center>
				<input type='button' value='Begin' class='wizardbutton' onClick='wizardStep(1);'>
			</center>
			</span>
			

		</div>
		<div class='shadowedbox' id='page2' style='display:none;text-align:left;min-height:300px;position:relative'>
			<span style='font-size: 19px;'>Step 1: Basics</span><br>
			<span style='padding:3px;margin-top:10px;display:block'>
				
				<form action='service/firsttime.php?step=1' method='POST' target='datapusher'>
					Name: <input type='text' value='' name='displayname' onKeyUp='checkName(this.value);'><br>
					<span style='display:inline-block;margin:4px;padding:2px;'>
						<span id='prefStatusName' style='font-size:11px;color:#676767;'>Eg: John Smith</span>
						<p>While your real name is encouraged, any display name will do.</p>
					</span><br>
					Gender: <select name='gender'>
							<option value=0>(not set)</option>
						<option value=1>Male</option>
						<option value=2>Female</option>
					</select>
					<br><span style='display:inline-block;margin:4px;padding:2px;'>
						This is optional, and only used for pronouns such as 'his message' or 'her photo'<br>
					</span>
					<input type='submit' value='Next' id='namebutton' class='wizardbutton' style='right:5px;bottom:5px;position:absolute' disabled>
					
				
				
				</form>
			</span>
		</div>
		<div class='shadowedbox' id='page3' style='display:none;text-align:left;min-height:300px;position:relative'>
			<span style='font-size:19px;'>Step 2: Display Picture</span><br>
			<span style='padding:3px;margin-top:10px;display:block'>
				Choose a picture below that will be used around the site.
				<form enctype='multipart/form-data' name='uploadform' method='POST' action='service/changepicture.php?firsttime=1' target='datapusher'>
					<input type='file' name='picture' onChange='uploadform.submit();pictureLoader.style.display=\"block\";'>
					<img name='pictureLoader' src='img/ajax.gif' style='display:none'>
					<br />
				</form>
				<p>This can be changed at any time, and if desired, you may skip this step.<br /></p>
				<input type='submit' value='Next' class='wizardbutton' onClick='wizardStep(3);' style='right:5px;bottom:5px;position:absolute'>
			</span>
		</div>
		<div class='shadowedbox' id='page4' style='display:none;text-align:left;min-height:300px;position:relative'>
			<span style='font-size:19px;'>Step 3: Add your friends</span><br>
			<span style='padding:3px;margin-top:10px;display:block'>
				If you know your friends' usernames, you can search for them below:
				<span style='padding-top:6px;display:block'>
				<form onSubmit=\"doSearch(query.value);document.getElementById('searchResults').style.display='block';return false;\">
					<input type=\"text\" value='Enter a username or email address...' onClick='this.value=\"\";this.style.color=\"black\";' style='color:#676767;width:250px;height:17px;' name=\"query\">
					<input type=\"button\" class=\"button\" value=\"Search\" onClick=\"doSearch(query.value);document.getElementById('searchResults').style.display='block';\">
				</form>
				</span>
				<div id='searchResults' style='display:none;width:96%;padding: 2px;margin:2px;height:150px;overflow:auto;'>
				</div>
				<p>You may also skip this step and add friends later.
				</p>
				<input type='submit' value='Next' class='wizardbutton' onClick='wizardStep(4);' style='right:5px;bottom:5px;position:absolute'>
				
			</span>
		</div>
		<div class='shadowedbox' id='page5' style='display:none;text-align:left;min-height:300px;position:relative'>
			<span style='font-size:19px;'>Optional: Invite friends</span>
			<span style='padding:3px;margin-top:10px;display:block'>
				Have friends you'd like to connect with who haven't joined yet? You may invite them below.
				<br />
				<span style='display:block;padding-top:5px;'>
				<form action='service/inviteuser.php?callback=f' target='datapusher' method='POST'>
				<input type='text' name='email' id='invitee_email' value='Enter an email address...' onFocus='if(this.value==\"Enter an email address...\"){this.value=\"\";this.style.color=\"black\";}' style='color:#676767'> <input type='submit' value='Send Invite' class='button' onClick='invitePending();'>
				<div id='invite_callback'>&nbsp;</div>
				</form>
				</span>
				
				After you're done, click Finish below to begin using Dobble.<br /><br />
				<center>
					<input type='button' value='Finish' class='wizardbutton' onClick='location.href=\"./\";' style='right:5px;bottom:5px;position:absolute'>
				</center>
			</span>
		</div>
		<div class='shadowedbox' id='page6' style='display:none;text-align:left;'>
			<center>
				<span style='font-size:22px;'>That's it!</span>
			</center>
			<span style='padding:3px;display:block'>
				<p>Everything is set up and ready to go.</p>
				<center>
					<input type='button' value='Start using Dobble.me!' class='button' onClick='location.href=\"./\";'>
				</center>
			</span>
		</div>
	</center>
	";
	
	echo $content;
?>
		</center>
		</body>
	</html>