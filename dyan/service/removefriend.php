<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	removeFriends($_SESSION['P_usr'], $_GET['user']);

	echo "~d<title>Information</title><message>You are no longer friends with " . getUserDisplayName(htmlspecialchars($_GET['user'])) . ".</message>";
}
?>