<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	if (cleanString($_GET['user']) == $_SESSION['P_usr']) {
		exit;
	}
	if (checkFriendRequestPending(cleanString($_GET['user']), $_SESSION['P_usr'])) {
		echo "~d<title>Notification</title><message>You already sent a friend request, be patient!</message>";
		exit;
	}
	sendFriendRequest(cleanString($_GET['user']), $_SESSION['P_usr']);
	echo "~d<title>Notification</title><message>You sent " . getUserDisplayName(htmlspecialchars($_GET['user'])) . " a friend request, who may now choose whether or not to approve your message.</message>";
}
?>