<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();

	$pref = getUserPreferences($_SESSION['P_usr']);
	if ($_GET['color'] != "") {
		$pref->color = "#".cleanString(htmlspecialchars($_GET['color']));
	}
	setUserPreferences($_SESSION['P_usr'], $pref);
	if ($_GET['bio'] != "") {
		setUserBio($_SESSION['P_usr'], htmlspecialchars($_GET['bio']));
	}
	echo "~~";

}
?>