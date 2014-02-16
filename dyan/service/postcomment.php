<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	if ($_GET['type'] == "photo") {
		$type = $N_PHOTO;
	} else {
		$type = $N_EVENT;
	}
	if (strlen($_REQUEST['commentmessage']) > 500) {
		echo "~SC2";
		exit;
	}
	$id = htmlspecialchars($_GET['id']);
	addComment(htmlspecialchars(stripslashes($_GET['user'])),$id,htmlspecialchars($_REQUEST['commentmessage']),$_SESSION['P_usr'], $type);
	//echo "<script>top.showDialog('debug', '$id');</script>";
	echo "~SC1$id";
	//Legacy purposes
	//echo "<script>top.commentCallback('$id');</script>";
	updateIsActive(); //Update the chat activity, since we know the user is active.
}
?>