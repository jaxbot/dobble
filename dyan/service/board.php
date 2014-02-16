<?php require('../system/service.php');

if (isLoggedIn()) {
	require_once("system/boarddata.php");
	$user = cleanString($_GET['user']);
	$lasttime = cleanString($_GET['last']);
	$servlast = getLastHistoryTime($user, false);
	if ($servlast > $lasttime) {
		echo "~?";
		$entries = getUserBoardEntries($user, 30, $lasttime);

		echo generateJSFromBoardEntries($entries);
		echo "positionBoardItems();";
		exit;
	}

}
?>