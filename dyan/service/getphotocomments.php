<?php require('../system/service.php');
if (isLoggedIn()) {
	$photoid = htmlspecialchars($_GET['id']);
	$callback = htmlspecialchars($_GET['callback']);
	$user = htmlspecialchars($_GET['user']);
	echo "~P$callback~nextitem~`";
	$comments = getCommentStream("photo".$photoid);
	foreach ($comments as $comment) {
		//list($from, $rndid, $time, $msg) = explode($sep, $comment);
		$time = getRelativeTime($comment->time);
		$fromname = getUserDisplayName($comment->from);
		$avatar = getUserThumbnail($comment->from);
		$msg = str_replace("'", "\\'", $comment->message);
		$msg = parse_links($msg);
		$msg = str_replace("\\'", "'", $msg);
		$rndid = $comment->id;
		$from = $comment->from;
		echo "$from~seper~`$fromname~seper~`$avatar~seper~`$rndid~seper~`$time~seper~`$msg~nextitem~`";
	}
	
}
?>