<?php require('../system/service.php');
$username = cleanString(htmlspecialchars($_GET['username']));
	foreach ($RESTRICTED_NAMES as $user) {
		if ($username == $user) {
			echo "~Rt"; //taken if restricted
			exit;
		}
	}
	
	$user = getUserProfile(strtolower($username));
	if ($user->username != "") {
		echo "~Rt";
	} else {
		echo "~Ra";
	}