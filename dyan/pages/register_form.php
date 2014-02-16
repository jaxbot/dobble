<!DOCTYPE HTML>
   <html>
		<head>
			<base href='<?php echo $base; ?>'>
			<title>Dobble.me</title>
			<link rel=StyleSheet href='common/home_s.css' type='text/css'>
			<meta name="description" content="Dobble.me brings simplicity and privacy back to social networking.">
			<meta name="keywords" content="dobble, social, network, connect, friends, instant, realtime">
			<?php //only show these invalid tags to facebook
			if (stripos($_SERVER['HTTP_USER_AGENT'], "facebook")) {?>
			<meta property="og:title" content="Dobble.me: Simple, private social networking."/> 
			<meta property="og:type" content="website"/> 
			<meta property="og:url" content="http://dobble.me/"/> 
			<meta property="og:site_name" content="Dobble.me"/> 
			<meta property="og:image" content="http://dobble.me/img/home/bubbles3.png"/>
			<?php } ?>
			<script src='common/home.js'></script>
			<script src='common/data.js'></script>
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
<script>
			//Color definitions
			var ERROR_RED = "#e63434";
			var ERROR_GREEN = "#3aac2b";
			var ERROR_YELLOW = "#e6c529";
			function checkUsername(name) {
				var s = document.getElementById("usernameStatus");
				if (name.length < 3) {
					s.innerHTML = "Too short.";
					s.style.color = ERROR_RED;
					return false;
				}
				if (!name.match(/^[a-zA-Z0-9]+$/)) {
					s.innerHTML = "Letters/numbers only, sorry";
					s.style.color = ERROR_RED;
					return false;
				}
				if (name.length > 20) {
					s.innerHTML = "Too long.";
					s.style.color = ERROR_RED;
					return false;
				}
				sendData("service/checkusername.php?username="+name);
				
			}
			function checkPassword(p1) {
				var s = document.getElementById("passwordStatus");
				if (p1.length < 3) {
					s.innerHTML = "It's a little on the short side.";
					s.style.color = ERROR_YELLOW;
				} else {
					s.innerHTML = "That'll work!";
					s.style.color = ERROR_GREEN;
				}
				
			}
			function checkEmail(email) {
				var s = document.getElementById("emailStatus");
				if (!email.match(/^[a-zA-Z0-9.]+[a-zA-Z0-9]+@[a-zA-Z0-9]+[a-zA-Z0-9.]+.[a-zA-Z0-9]+[a-zA-Z0-9.]+[a-zA-Z0-9]$/)) {
					s.innerHTML = "Doesn't look valid...";
					s.style.color = ERROR_RED;
				} else {
					document.getElementById("emailStatus").innerHTML = "Looking okay!";
					s.style.color = ERROR_GREEN;
				}
			}
			function registerCallback(error, status) {
				if (status == 1) {
					location.href='../?firsttime';
				} else {
					document.getElementById("errorList").innerHTML = error;
					fadeInElement(document.getElementById("errorList"));
				}
			}
			function usernameLookupCallback(status) {
				var s = document.getElementById("usernameStatus");
				if (status == "t") {
					s.innerHTML = "Taken :(";
					s.style.color = ERROR_RED;
				} else {
					s.innerHTML = "I like it!";
					s.style.color = ERROR_GREEN;
				}
			}
			</script>
			<style>
			
#createaccount_btn {
	background-color: #ffd600;
	background-image: url("../img/signup_btn.png");
	border: solid 1px #ffb400;
	border-radius: 5px;
	font-size: 18px;
	font-family: Verdana;
	width: 180px;
	height: 35px;
	cursor: pointer;
}
#createaccount_btn:hover {
	border-color: #ffd600;
}
div.shadowedbox {
	width: 600px;
	min-height: 360px;
	border: solid 1px #4a4a4a;
	display: block;
	margin-left: auto;
	margin-right: auto;
	background-color: white;
	border-radius: 15px; 
	box-shadow: 1px 1px 25px #000000;
	padding:10px;
	text-align:center;
}
input.regbox {
width: 200px; height: 21px;font-size:19px;
}</style>
		</head>
		<body>
				<div id='highlight_wrapper'>
					<div id='content'>
						<div style='margin-left:auto;margin-right:auto;position: relative;display:block;width:541px;padding-top:20px;'>
							<img src='img/logo_bigger.png' style='width:541px;height:115px;' alt='Dobble.me'>
						</div>
			<br>
			<iframe name='datawriter' style='display: none'></iframe>
			<div class='shadowedbox' style='min-height: 290px;'>
				<form action='service/registeruser.php' method='POST' target='datawriter'>
				<div id='errorList' style='color: red;'></div>
				<table cellspacing=3 cellpadding=3 width=600 style='text-align:left'>			
					<tr>
						<td width=110 valign='top'>Username:</td>
						<td width=200 valign='top'>
							<input type='text' value='' name='usernme' onKeyUp='checkUsername(this.value);' style='width: 200px; height: 21px;font-size:19px;'>
						</td>
						<td>
							<div id='usernameStatus' style='font-size: 13px;margin-left:2px;color:#676767;font-weight:bold'>It's your ID. Be unique.</div>
						</td>
					</tr>
					<tr>
						<td width=110 valign='top'>Email:</td>
						<td width=200 valign='top'>
							<input type='text' value='' name='email' onBlur='checkEmail(this.value);' style='width: 200px; height: 21px;font-size:19px;'>
						</td>
						<td>
							<div id='emailStatus' style='font-size: 13px;margin-left:2px;color:#676767;font-weight:bold'>Pretty typical.</div>
						</td>
					</tr>
					<tr>
						<td width=110 valign='top'>Password:</td>
						<td width=200 valign='top'>
							<input type='password' value='' id='pass' name='pass' class='regbox' onKeyUp='checkPassword(this.value);'>
						</td>
						<td>
							<div id='passwordStatus' style='font-size: 12px;margin-left:2px;color:#676767;font-weight:bold'>Keep it a secret, obviously.</div>
						</td>
					</tr>
				</table>
				<br>
				<h3>Who invited you?</h3>
				Ask your friend to give you the following information:
				<br><br>
				<table style='text-align:left'>
				<tr><td>Friend's username: </td><td><input type='text' name='referer' class='regbox'></td></tr>
				<tr><td>Friend's code: </td><td><input type='text' name='referercode' class='regbox'></td></tr>
				</table>
				<br>
				<input type='submit' value='Create Account' id='createaccount_btn'>
				</form>
				</div>
			</div>
				</div>
			<span id='footer'>&copy; 2012 Dobble.me. All Rights Reserved.</span>
		</body>
		
	</html>