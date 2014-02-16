<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();

	$id = htmlspecialchars($_GET['id']);
	$postid = htmlspecialchars($_GET['postid']);
	$from = htmlspecialchars($_GET['user']);
	if ($from == $_SESSION['P_usr'] or $_SESSION['P_usr'] == $_GET['commentuser']) {
		deleteComment($from, $postid, $id, $_GET['commentuser']);
	}
	echo "~~";
	updateIsActive(); //Update the chat activity, since we know the user is active.
}
?>