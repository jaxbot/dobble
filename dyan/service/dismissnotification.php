<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	removeUserNotification(cleanString($_SESSION['P_usr']), cleanString($_GET['id']));
	echo "~~";
}
?>