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
				<div id='highlight_wrapper'>
					<div id='content'>
						<div style='margin-left:auto;margin-right:auto;position: relative;display:block;width:541px;padding-top:20px;'>
							<a href='./'><img src='img/logo_bigger.png' style='width:541px;height:115px;' alt='Dobble.me'></a>
						</div>
						<br><span class='text1'>Dobble is an idea</span><br>
						<div class='paddedbox'>
							An idea that sees simplicity and bliss in communicating with friends.<br>
							Real friends. Not people you meet on the street and feel guilted into interacting with.
						</div>
						<br><span class='text2'>seeking freedom</span><br>
						<div class='paddedbox'>
							Seeking freedom from the nonsense of the current ways of communication.
						</div>
						<br><span class='text2'>but most importantly</span><br>
						<div class='paddedbox'>
							And above all else
						</div>
						<br><span class='text2'>Dobble is about you.</span><br>
						<br><br><br>
						<div style='width:100%;text-align:center;color:white;font-size:16px;'>
						Dobble is currently in closed beta. You may <a href='request'>request</a> an invitation once it is ready.
						</div>
						</div>
					</div>
				</div>
			<span id='footer'>&copy; 2012 Dobble.me. All Rights Reserved.</span>
		</body>
		
	</html>