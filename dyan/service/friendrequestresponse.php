<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	if (checkFriendRequestPending($_SESSION['P_usr'], $_GET['from'])) {
		if ($_GET['resp'] == "1") {
			addFriends($_SESSION['P_usr'], cleanString($_GET['from']));
		}
		removeFriendRequest($_SESSION['P_usr'], cleanString($_GET['from']));
		echo "~1";
	}
}
?>