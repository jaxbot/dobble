<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	$albumid = cleanString($_GET['album']);
	$id = cleanString($_GET['id']);
	deletePicture($_SESSION['P_usr'], $albumid, $id);
	echo "~d<title>Deleted</title><message>The photo has been successfully deleted.</message>";
}
?>