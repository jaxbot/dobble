<?php
session_start(); //we allow session here for protecting punchUses
require_once("system/globals.php");
initSession();

if ($_POST['un'] != "") {
	$_SESSION['punchUses'] += 1;

	if (time() - $_SESSION['punchTime'] > 60) {
		$_SESSION['punchUses'] = 0;
	}

	if ($_SESSION['punchUses'] > 10) {
		$error = "Too many requests. Try again in a few minutes.";
	}
	$_SESSION['punchTime'] = time();

	if ($error == "" and loginUser($_POST['un'], $_POST['pw'])) {
		//unset session
		session_destroy();
		setcookie("PHPSESSID", "", 0);
		
		header("Location: ./");
		exit;
	} else {
		if ($error == "") {
			$error = "Sorry, wrong password. Try again.";
		} //otherwise, its a punch uses violation
	}
	
}

if (isLoggedIn()) {
	header("Location: ./");
}
if ($_COOKIE['D'] != "" and $_GET['user'] != "") $expired = true;
$user = htmlspecialchars($_GET['user']);
	?>
	<!DOCTYPE HTML>
	<html>
		<head>
			<base href='<?php echo $base; ?>'>
			<title>Dobble.me: Sign In</title>
			<meta name="description" content="Sign In to Dobble.me">
			<link rel=StyleSheet href='<?php echo $COMMONHOST; ?>/home.css' type='text/css'>
		</head>
		<body>
			<center>
				<br>
				<div id='highlight_wrapper'><div id='highlight'></div></div>
				<a href='./'><img src='img/logo_bigger.png' border=0></a><br>
				<div id='centerpiece'>
					<br>
					<div class='roundedbox'>
			<?php
		if ($_GET['forgot'] == "1") {
		?>
		<span style='color:white;font-size:23px;'>Forgot my password</span><br><br>
		<span style='display:inline-block;padding:2px;color:white;text-align:center;width:350px'>
		<?php
			if ($_POST['un'] != "") {
				$user = getUserProfile(cleanString($_POST['un']));
				if ($user->email != "") {
					$pw = substr(md5(time()), 3, rand(5,8)).rand(0,1000);
					$user->password = md5($pw);
					setUserProfile(cleanString($_POST['un']), $user);
					$name = getUserDisplayName(cleanString($_POST['un']));
					$body = "
						Hey $name!<br><br>We've received a request to reset your password. Here's your new login information:<br>
						Password: $pw<br><br>
						Log in as soon as possible and change your password back to something you'll remember.<br>
						If you didn't intend to reset your password, log in and change your password, then contact our abuse team.<br>
						No worries!<br>
						-Dobble.me
					";
					emailUser(cleanString($_POST['un']), "Account recovery", $body);
				?>
				
				All done. Go check your email to finish recovering your account.<br>
				Once you're set, go ahead and <a href='signin'>login</a>.
				<?php	
				} else {
				?>
				
				We apparently don't have that username on file, sorry. <a href='signin?forgot=1'>Try again.</a>
				<?php
				}
			} else {
			?>
			
			It's ok, it happens to us all. <br>Type your username below,<br>and we'll handle the rest.<br><br>
			<form action='signin?forgot=1' method='POST'>
				<input type='text' value='<?php echo htmlspecialchars($_GET['user']); ?>' name='un' id='userbox' onKeyDown='this.style.backgroundImage="none"'><br><br>
				<input type='submit' value='Reset my password' id='signin_button'>
			</form>
			<script>document.getElementById("userbox").focus();</script>
			<?php
			}
			?>
			</span>
			<?php
		} else {
		?>
		<h2>sign in</h2>
				<?php
				if ($error != "") {
					echo "<br><span style='color: #f6b0b0;'>$error</span>";
				}
				if ($expired) {
					echo "<div style='display:block;color:white'>
					<b>You've signed in from another location.</b><br>
					<div style='display:block;padding:10px;'>
						<form method='POST'>
							<input type='hidden' name='un' value='$user'>
							<div style='padding-right:4px;float:left;display:block;height:50px;'>
								<img src='$STATIC_IMG_HOST/thumb/$user'>
							</div>
							<span style='font-size:17px;'>".getUserDisplayName($user)."</span><br>
							<input type='password' id='pwbox' value='' style='height:28px;width:160px;font-size:15px;padding-left:5px;border-radius:5px;border:none' name='pw'>
							<input type='submit' id='signin_button' value='Sign In' style='width:100px;height:33px'>
						</form>
					</div><script>document.getElementById('pwbox').focus();</script>
					<div style='width:350px;margin:10px;font-size:11px;'>
					If you would like to prevent this from happening, you may enable Multiple Signon in your account settings.
					</div>";
					
				} else {
				?>
				
				<span style='text-align:center;display:inline-block;width:370px;color:white'>
				<form action='signin' method='POST'>
					
					<input type='text' value='<?php echo htmlspecialchars($_GET['user']); ?>' name='un' id='userbox' onKeyDown='this.style.backgroundImage="none"'><br>
					<input type='password' value='' name='pw' id='passwordbox' onKeyDown='this.style.backgroundImage="none"'><br>
			
					<br>
				<input type='submit' id='signin_button' value='Sign In'>
				</form>
				<br>
				<a href='signin?forgot=1' style='color:white'>I forgot my password</a><br>
				
				</span>
				<?php
				if ($_GET['user'] != "") {
					?>
					<script>
						document.getElementById("passwordbox").focus();
					</script>
					<?php
				} else {
					?>
					<script>
						document.getElementById("userbox").focus();
					</script>
					<?php
				}
				}
				}
				?>
			</div>
			</div>
			</center>
		</body>
	</html>
