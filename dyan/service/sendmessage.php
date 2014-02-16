<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	$with = htmlspecialchars($_GET['user']);
	$message = htmlspecialchars($_REQUEST['message']);
	if (strlen($message) > 500) {
		echo "~SM2";
		exit;
	}
	if (isFriends($with, $_SESSION['P_usr'])) {
		sendMessageTo($with, $message);
		addUserNotification($with, getUserDisplayName($_SESSION['P_usr']) . " sent you a message.", "chatwith".$_SESSION['P_usr'], $_SESSION['P_usr'], $N_CHAT);
		echo "~SM1".$with;
	} else {
		echo "~SM3";
	}
	updateIsActive(); //Update the chat activity, since we know the user is active.
}
?>