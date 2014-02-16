<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	if (strlen($_POST['composer_status']) > 500) {
		echo "<script>top.composerCallback(false);</script>";
		echo "<script>top.showDialog('Oops', 'Your message is a tad long, try shortening it a bit.');</script>";
		exit;
	}
	$event = htmlspecialchars($_POST['composer_status']);
	//$event = str_replace("\n", "", $event);

	$lastpost = getLastPost($_SESSION['P_usr']);
	if ($lastpost->event == $event) {
		echo "<script>top.showDialog('Oops', 'Your previous message is the same as this one.');</script>";
		echo "<script>top.composerCallback(false);</script>";
		exit;
	}
	if (time() - $lastpost->time < 2) {
		echo "<script>top.showDialog('Oops', 'Wait a bit before posting (the time it took you to read this is probably enough.)');</script>";
		echo "<script>top.composerCallback(false);</script>";
		exit;
	}

	addHistoryEvent($_SESSION['P_usr'], $event);
	echo "<script>top.composerCallback(true);</script>";
	updateIsActive(); //Update the chat activity, since we know the user is active.
}
?>