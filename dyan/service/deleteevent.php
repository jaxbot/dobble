<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	$id = htmlspecialchars($_GET['id']);
	//Check for authorization when deleting comment rows
	$item = getHistoryItem("", $id);
	if ($item->from == $_SESSION['P_usr']) {
		deleteCommentStream($_SESSION['P_usr'], $id);
	} else {
		exit;
	}

	deleteHistoryItem($_SESSION['P_usr'], $id);

	echo "~~";
	updateIsActive(); //Update the chat activity, since we know the user is active.
}
?>