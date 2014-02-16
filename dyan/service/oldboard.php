<?php require('../system/service.php');

if (isLoggedIn()) {
	require_once("system/boarddata.php");
	$user = cleanString($_GET['user']);
	$lasttime = intval($_GET['last']);

	echo "~?console.log($lasttime);";
	$entries = getUserBoardEntries($user, 30, 1, $lasttime);

	echo generateJSFromBoardEntries($entries, true);
	echo "positionBoardItems(); loadingNewPages = false;";
	exit;

}
?>