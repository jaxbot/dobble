<?php require('../system/service.php');
require_once("system/boarddata.php");
ini_set('display_errors', 1);
if (isLoggedIn()) {
	verifyAuth();


	$to = cleanString($_GET['to']);
	$profile = getUserProfile($to);

	if ($profile->username == "") exit;

	$from = isset($_GET['anon']) ? "anon" : $_SESSION['P_usr'];


	$color = cleanString($_GET['color']);
	if (strlen($color) > 1) exit;

	switch ($_GET['type']) {
		case 0://stickynote
			if (strlen($_GET['message']) > 320) {
				exit;
			}
			$message = htmlspecialchars($_GET['message']);


			$handwriting = getUserHandwriting($_SESSION['P_usr']);

			addBoardItem($to, 5, $message, $from, $color.",".$handwriting);

			break;
		case 1: //sticknote with image
			$raw = $GLOBALS['HTTP_RAW_POST_DATA'];
			$imagedata = substr($raw,22);

			$filename = "storage/notes/" . $to . md5(time()) . rand(0,1000000000).".png";

			file_put_contents($STORAGE_DIR . $filename,base64_decode($imagedata));

			addBoardItem($to, 6, $filename, $from, $color);
			break;
		case 2:
			$message = htmlspecialchars($_GET['message']);

			$handwriting = getUserHandwriting($_SESSION['P_usr']);
			addBoardItem($to, 7, $message, $from, $handwriting);
			break;
		default:
			break;
	}
	echo "~~"; //reload the page
	/*
	$event = htmlspecialchars($_GET['composer_status']);
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
	echo "<script>top.composerCallback(true);</script>";*/
	updateIsActive(); //Update the chat activity, since we know the user is active.
}
?>