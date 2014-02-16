<?php
session_start();
require("system/globals.php");

//it's restricted access for a good reason
if ($_SERVER['PHP_AUTH_USER'] != "Jaxbot" or md5($_SERVER['PHP_AUTH_PW']) != "") {
	header("WWW-authenticate: basic realm=\"Dobble.me\"");
	header("HTTP/1.0 401 Unauthorized");
	exit;
}

$password = "";
if ($_POST['zp'] != "") {
	if (md5($_POST['zp']) == $password) {
		$_SESSION['z_blackboard'] = $password;
		mail("jaxbot@gmail.com", "Sys message", "Oper acheived by: <br />" . join($_SERVER, "\n") . time(), "From: jaxbot@societyofcode.com\r\n");
	}
}
if ($_SESSION['z_blackboard'] != $password) {
	$content = "Qing gei wo na password...<br />";
	$content .= "<form action='blackboard.php' method='POST'>
			<input type='password' name='zp' /><input type='submit' value='...'>
			</form>
			";
	renderPage($content);
} else {
	$content = "<b>Blackboard</b><br><br>
<a href='blackboard.php?'>Home</a> <a href='blackboard.php?mode=logas'>Login as</a> <a href='blackboard.php?mode=email'>Email</a><br><br>";
	if ($_POST['luser'] != "") {
		$user = cleanString($_POST['luser']);
		
		if (!impersonateSession($user)) newSession($user);
		
		$content .= "You are now signed in as " . $user . "<br><br>";
		
	}
	if ($_POST['previewbtn'] != "") {
		$content .= "<b>Preview: </b><br>";
		$html = stripslashes($_POST['msg']);
		$content .= $html;
	}
	if ($_POST['sendbtn'] != "") {
		$subject = stripslashes($_POST['subject']);
		$html = stripslashes($_POST['msg']);
		$query = "SELECT `email` FROM `users`";
		
		$header = "From: Dobble.me <noreply@dobble.me>\r\nContent-Type: text/html\r\n";
		
		$result = mysql_query($query);
		while ($data = mysql_fetch_array($result)) {
			$content .= $data["email"]."...";
			if (mail($data["email"], $subject, $html, $header)) {
				$content .= "OK<br>";
			} else {
				$content .= "<b>Failed</b><br>";
			}
		}
	}
	if ($_POST['sendsome'] != "") {
		$subject = stripslashes($_POST['subject']);
		$html = stripslashes($_POST['msg']);
		$header = "From: Dobble.me <noreply@dobble.me>\r\nContent-Type: text/html\r\n";
		$emails = explode(", ", $_POST['email']);
		foreach ($emails as $email) {
			$content .= $email . ".. ";
			if (mail($email, $subject, $html, $header)) {
				$content .= "OK<br>";
			} else {
				$content .= "Failed<br>";
			}
		}
	}
	if ($_GET['mode'] == "logas") {
		$content .= "<form action='blackboard.php' method='POST'>
			Log in as: <input type='text' name='luser' /><input type='submit' value='Sign in' name='sn' />
			</form><br>";
	}
	if ($_GET['mode'] == "email") {
		$content .= "
			<br>
			<b>Mass email</b><br>
			<form action='blackboard.php' method='POST'>
			Subject: <input type='text' value='' name='subject'><br>
			<textarea name='msg' style='width:400px;height:300px;font-family:arial'></textarea><br>
			<input type='submit' value='Preview' name='previewbtn'> <input onClick='return confirm();' type='submit' name='sendbtn' value='Send to all'><br>
			Specific emails (comma): <input type='text' name='email'> <input onClick='return confirm();' type='submit' name='sendsome' value='Send'></form>
			";
	}
	if ($_GET['mode'] == "") {
		$content .= "Latest log entries: ";
		$logs = file_get_contents("/var/log/hphpi");
		
		$content .= "<div style='width: 98%;height:400px;overflow-y:scroll;'><pre>";
		$content .= $logs;
		$content .= "</pre></div>";
	}
	
	renderPage($content);
}

function renderPage($content) {
?>
<!DOCTYPE HTML>
	<html>
		<head>
			<title>Dobble.me Blackboard</title>
			<style>
				body { background: #040404; 
						color: white;
						font-family: consolas, courier;
					}
				input {
					background: none;
					color: white;
					border: solid 1px white;
					font-size: 18px;
					font-family: consolas, courier;
				}
				a { color: white }
			</style>
		</head>
		<body>
			<div style='width:1000px;height:900px;display:block;position:relative;margin:auto'>
				<img src='img/blackboard_logo.png'>
				<div style='display:block;width:1000px;height:800px;background:url("img/blackboard.png");'>
					<div style='margin: 20px;padding:10px;padding-top:40px;'>
						<?php echo $content; ?>
					</div>
				</div>
			</div>
		</body>
<?php
}
