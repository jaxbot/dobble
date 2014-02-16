<?php require('../system/service.php');

if (isLoggedIn()) {
	require_once("system/boarddata.php");
	$user = cleanString($_GET['user']);
	$id = cleanString($_GET['id']);
	$time = intval($_GET['last']);
	echo "~?";
	
	$comments = getCommentStream($id, ($time > 0 ? $time : 0));
	foreach ($comments as $comment) {
		$comment->rtime = time() - $comment->time;
		$comment->avatar = getUserThumbnail($comment->from);
		$json = json_encode($comment);
		echo "addBoardResponse($json);";
	}
	if ($time == -1) {
		echo "boardResponseFinish();";
	}
}
?>