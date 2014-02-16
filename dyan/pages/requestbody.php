<!DOCTYPE HTML>
   <html>
		<head>
			<base href='<?php echo $base; ?>'>
			<title>Dobble.me: Request an Invitation</title>
			<link rel=StyleSheet href='common/home.css' type='text/css'>
			<script src='common/home.js'></script>
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
		function invitationCallback(result) {
			switch (result) {
				case -1:
					//Already requested
					_g("error_status").innerHTML = "Looks like you've already requested an invitation.<br>Don't worry, it'll come in due time!";
					fadeInElement(_g("error_status"));
				break;
				case 0:
					//Bad email
					_g("error_status").innerHTML = "Hmm, that email doesn't look valid. Dobble check it and try again.";
					fadeInElement(_g("error_status"));
				break;
				case 1:
					fadeOutElement(_g("formwrapper"));//moveElementToX("formwrapper", 200);
					setTimeout('var alldone = _g("alldone");alldone.style.display="inline-block";fadeInElement(alldone);', 270);
				break;
			}
		}	
		function checkEmail(email) {
			_g("error_status").innerHTML = "";
			if (!email.match(/^[a-zA-Z0-9.]+[a-zA-Z0-9]+@[a-zA-Z0-9]+[a-zA-Z0-9.]+.[a-zA-Z0-9]+[a-zA-Z0-9.]+[a-zA-Z0-9]$/)) {
				_g("error_status").innerHTML = "Hmm, that email doesn't look valid. Dobble check it and try again.";
				fadeInElement(_g("error_status"));
				return false;
			}
			return true;
		}
		</script>
		</head>
		<body>
                    <iframe src='about:blank' name='datapusher' id='datapusher' style="display:none"></iframe>
			<center>
				<br>
				<div id='highlight_wrapper'><div id='highlight'></div></div>
				<a href='./'><img src='img/logo_bigger.png' border=0></a><br>
				<div id='centerpiece'>
					<br><br>
					<span id='titletext'>Request an Invitation to Join Dobble.me</span><br><br>
					<div id='alldone' style='display: inline-block;opacity:0;display:none'>
						<span id='alldonetext'>Got it! Thanks!</span><br>
						<span class='paragraph'>Once Dobble is ready for more, you'll receive an early invitation.<br>Want to speed this up? Tell your friends about Dobble!<br>
						<br>
						Keep up to date at the <a href='http://blog.dobble.me/'>Dobble.me Blog</a></span>
					</div>
					<div id='formwrapper' style='display: inline-block; position: relative;left:0px;'>
						<form action='service/requestinvite.php' method='POST' target='datapusher' onSubmit='return checkEmail(email.value);'>
							<input type='text' name='email' value='Email address...' id='reqinv_email' onClick='if(this.value=="Email address..."){this.value="";this.style.color="#323232";}'> <input type='submit' value='Request Invitation' id='reqinv_submit'>
						</form>
						<span id='error_status' class='paragraph' style='font-size:17px;color:#ffe3e3'></span><br><br>
						<span class='paragraph'>Dobble.me brings simplicity back to social networking,<br>connecting you with who matter most:<br>Your Friends.</span>
					</div>
				</div>
				<img id='dobblemascot' src='img/home/dobble.png'>
				<script>fadeInElement(_g("dobblemascot"));</script>
				<span id='footer'>&copy; 2011 Dobble.me. All Rights Reserved. | <a href='http://blog.dobble.me' style='color:#efefef;'>Dobble Blog</a></span>
			</center>
		</body>
	</html>