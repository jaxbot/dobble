<?php require('../system/service.php');
if (isLoggedIn()) {
	$step = $_GET['step'];
	if ($step == "1") { 
		$prof = getUserProfile($_SESSION['P_usr']);
		$prof->displayname = cleanString($_POST['displayname']);
		$prof->gender = cleanString($_POST['gender']);
		setUserProfile($_SESSION['P_usr'], $prof);
		echo "<script>top.wizardStep(2);</script>";
	}
	if ($step == "4") {
		$prof = getUserProfile($_SESSION['P_usr']);
		
		$prof->allowText = true;
		$prof->carrier = htmlspecialchars($_POST['carrier']);
		$prof->phone = htmlspecialchars($_POST['phone']);
		
		setUserProfile($_SESSION['P_usr'], $prof);
		echo "<script>top.wizardStep(5);</script>";
	}
}
?>