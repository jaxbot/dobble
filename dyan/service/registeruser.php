<?php require('../system/service.php');
require("system/stagingdata.php"); 

	$username = cleanString(htmlspecialchars($_POST['usernme']));
	$username = str_replace(" ", "", $username); 
	$errors = array();
	if ($username != $_POST['usernme']) {
		array_push($errors, "Usernames can only contain letters or numbers.");
	}
	if (strlen($username) < 3) {
		array_push($errors, "Your username is too short.");
	}
	if (strlen($username) > 20) {
		array_push($errors, "Your username is too long.");
	}
	
	//check restrictions
	foreach ($RESTRICTED_NAMES as $user) {
		if ($username == $user) {
			array_push($errors, "The username is already taken.");
		}
	}
	
	$user = getUserProfile(strtolower($username));
	if ($user->username != "") {
		array_push($errors, "The username is already taken.");
	}
	
	if (strlen($_POST['pass']) < 3) {
		array_push($errors, "Your password is too short.");
	}
	
	$email = htmlspecialchars($_POST['email']);
	$password = md5($_POST['pass']);
	$username = strtolower($username);
	/*
	if (!isEligibleInvitation($email)) {
		array_push($errors, "Sorry, only invited users can register for now. You may request an invitation.");
	}*/
	
	//referer system
	$referer = cleanString($_POST['referer']);
	$referercode = $_POST['referercode'];
	
	if (strtolower(getInviteCode($referer)) != strtolower($referercode)) {
		array_push($errors, "Hm, that referer code doesn\\'t seem to be right. Dobble check that.");
	}
	
	//array_push($errors, $password);
	
	$error = join($errors, "<br>");
	
	if ($error != "") {
		echo "<script>top.registerCallback('$error', 0);</script>";
		exit;
	}
	
	
	
	//echo $email; exit;

		
		$result = mysql_query("INSERT INTO `users` (`username`, `password`, `email`, `displayname`, `friends`)
VALUES ('".mysql_real_escape_string($username)."', '".mysql_real_escape_string($password)."', '".mysql_real_escape_string($email)."', 
'".mysql_real_escape_string($username)."', '".mysql_real_escape_string($referer)."');");
		//VALUES ('".mysql_real_escape_string("YO")."', '".mysql_real_escape_string("i'm walkin")."', '".mysql_real_escape_string("over")."', '".mysql_real_escape_string("here")."');");
//		VALUES ('".mysql_real_escape_string($username)."', '".mysql_real_escape_string($password)."', '".mysql_real_escape_string($email)."', '".mysql_real_escape_string($username)."');");
		//VALUES ('trolol', 'olol', 'troll', 'lol');");

	if (!$result) {
		echo "<script>top.registerCallback('Uh oh, something went wrong. Try again.', 0);</script>";
		echo mysql_error();
		mail("jaxbot@gmail.com", "dobble.me Register error", mysql_error(), "From: noreply@dobble.me\r\n");
		exit;
	}
	newSession($username);
	
	
	$result = mysql_query("INSERT INTO `preferences` (`user`) 
VALUES ('".mysql_real_escape_string($username)."');");
	/*
	$_SESSION['P_usr'] = $username;
	$_SESSION['P_pass'] = $password;*/
	
	$headers = "From: Dobble.me <noreply@dobble.me>\r\nReply-To: jwarner@dobble.me\r\nContent-type: text/html\r\n";
		
	$msg = "<html>
	<head>
		<title>Dobble.me - Welcome!</title>
		<base href='http://dobble.me/'></base>
		<link rel='StyleSheet' href='common/00248.css' type='text/css'>
	</head>
	<body>
	<span style='display:inline-block;padding:3px'>
		
		<div style='display:inline-block;width:600px;border:solid 1px #4e4864;'>
			<img src='img/email/logo.png'><br>	
			<span style='display:inline-block;padding:3px;'>
				<center>
				<img src='img/dobble/happy.png' style='max-height:150px;'>
				<br>
				<span style='font-size:15px;'>Welcome!</span>
				</center>
				On behalf of the Dobble.me Team, welcome!  There's nothing to do here, your account is ready for use! Just head over to Dobble.me to get started.<br>
				Now that you're registered, you can also invite friends to join. Dobble is more fun with more people. Trust us ;)<br>
				If you have any issues, just reply to this email, and a member of our team will be there to assist you. It's that easy.
				<br>
				<br>Hope you enjoy!<br>
				<font color='#989898'>-The Dobble.me Team</font>
			</span>
		</div>
	</span>
	</body>
</html>";
		
	//mail($email, "Welcome to Dobble.me!", $msg, $headers);

	//Set registration date statistics
	$result = mysql_query("INSERT INTO `statistics_regtime` (`time`, `user`, `alldone`)
VALUES ('".time()."', '".mysql_real_escape_string($username)."', '0')");
	
	//add referer as friend
	//addFriends($username, $referer);
	
	echo "<script>top.registerCallback('', 1);</script>";