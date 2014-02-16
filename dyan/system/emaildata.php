<?php
	function emailUser($user, $subject, $body) {
		$headers = "From: Dobble.me <noreply@dobble.me>\nReply-To: noreply@dobble.me\nContent-type: text/html\n";
		$to = getUserProfile($user)->email;
		
		mail($to, $subject, $body, $headers);
	}
	function emailUserNotification($user, $msg) {
		$body = "Hello! You have received the following notification whilist you were offline:<br />
		$msg<br />
		Head over to <a href='http://dobble.me/'>Dobble</a> to view it!<br />~Dobble";
		emailUser($user, "New Notification", $body);
	}
	function canSendText($user) {
		if (getUserProfile($user)->allowText) {
			return true;
		}
		return false;
	}
	function sendTextToUser($user, $msg) {
		if (canSendText($user) != true) {
			return false;
		}
		$prof = getUserProfile($user);
		$carrier = $prof->carrier;
		$number = $prof->phone;
		
		switch ($carrier) {
			case "att":
				$address = "$number@mms.att.net";
			break;
			case "tmobile":
				$address = "$number@tmomail.net";
			break;
			case "verizon":
				$address = "$number@vtext.com";
			break;
			case "sprint":
				$address = "$number@messaging.sprintpcs.com";
			break;
			default:
				return -1;
			break;
		}
		return mail($address, " ", $msg, "From: txt@dobble.me\r\n");
	}
?>