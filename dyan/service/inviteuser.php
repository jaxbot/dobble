<?php require('../system/service.php');
if (isLoggedIn()) {
	$email = htmlspecialchars($_POST['email']);
	
	$fromname = getUserDisplayName($_SESSION['P_usr']);
	$username = $_SESSION['P_usr'];
	if (sendInvitation($username, $email)) {
		$subject = $fromname." Invited You to the Private Dobble.me Beta!";
		$headers = "From: Dobble.me <noreply@dobble.me>\r\nContent-Type: text/html\r\n";
		$body = file_get_contents("pages/emails/invite.html");
		$body = str_replace("{0}", $fromname, $body);
		$body = str_replace("{VIEW_IN_BROWSER}", "<font size=1>Trouble viewing this email? <a href='email/invite?from=$username'>View it in your browser.</a></font><br>", $body);
		
		if (mail($email, $subject, $body, $headers)) {
			echo "<script>top.inviteCallback(1);</script>";
		} else {
			echo "<script>top.inviteCallback(0);</script>";
		}
	}
}
?>