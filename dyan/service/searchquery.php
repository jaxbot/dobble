<?php require('../system/service.php');
if (isLoggedIn()) {
	$results = searchQuery(htmlspecialchars($_GET['query']));
	echo "~q";
	$found = false;
	foreach ($results as $result) {
		if ($result != "") {
			$statuscode = "0";
			if (isFriends($result, $_SESSION['P_usr'])) {
				$statuscode = "1";
			}
			if (checkFriendRequestPending($result, $_SESSION['P_usr'])) {
				$statuscode = "P";
			}
			if ($_SESSION['P_usr'] == $result) {
				$statuscode = "U";
			}
			echo $result . "~seper~`" . getUserThumbnail($result). "~seper~`" . getUserDisplayName($result) . "~seper~`" . $statuscode . "~nextitem~`";
			$found = true;
		}
	}
	if (!$found) {
		echo "!";
	}
	
}
?>