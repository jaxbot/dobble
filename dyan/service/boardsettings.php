<?php require('../system/service.php');

if (isLoggedIn()) {
	verifyAuth();
	require_once("system/boarddata.php");

	setUserHandwriting($_SESSION['P_usr'], intval($_POST['handwriting']));
}
?>