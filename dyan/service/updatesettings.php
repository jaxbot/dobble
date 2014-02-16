<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();

	$prof = getUserProfile($_SESSION['P_usr']);
	$prof->email = htmlspecialchars($_POST['email']);
	$prof->displayname = cleanString($_POST['displayName']);
	if ($_POST['allowText'] == "true") {
		$prof->allowText = true;
	} else {
		$prof->allowText = false;
	}
	$prof->carrier = htmlspecialchars($_POST['carrier']);
	$prof->phone = str_replace(" ", "", cleanString($_POST['phone'])); //Remove ()- and " " from the phone number

	$prof->gender = cleanString($_POST['gender']);

	$prof->allowMultiSignOn = ($_POST['allowMultiSignOn'] == "true");

	if ($_POST['newPW1'] != "") {
		if (md5($_POST['curPassword']) == $prof->password) {
			if ($_POST['newPW1'] == $_POST['newPW2']) {
				$prof->password = md5($_POST['newPW1']);
				$_SESSION['P_pass'] = md5($_POST['newPW1']);
			} else {
				echo "<script>top.prefCallBack(-1);</script>";
				exit;
			}
		} else {
			echo "<script>top.prefCallBack(-2);</script>";
			exit;
		}
	}
	setUserProfile($_SESSION['P_usr'], $prof);
	echo "<script>top.prefCallBack(1);top.location.reload();</script>";
}
?>