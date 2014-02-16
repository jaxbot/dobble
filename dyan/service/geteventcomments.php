<?php require('../system/service.php');
if (isLoggedIn()) {
	$id = cleanString($_GET['id']);
	$user = cleanString($_GET['user']);
				
	$comments = getCommentStream($id);

	if (count($comments) == ($lastcomment - 1)) { //based off a 1-based JS array
		echo "~~";
		exit;
	}
	
	$output = "~C";
	$output .= $id; //Add the ID for sideframe verification
	$output .= "~c~`";
				
	foreach ($comments as $comment) {
		$time = time() - $comment->time;
		$fromname = getUserDisplayName($comment->from);
		$avatar = getUserThumbnail($comment->from);
		$msg = str_replace("'", "\\'", $comment->message);
		$msg = parse_links($msg);
		$msg = str_replace("\\'", "'", $msg);
		$rndid = $comment->id;
		$from = $comment->from;
		$output .= "$from~seper~`$fromname~seper~`$avatar~seper~`$rndid~seper~`$time~seper~`$msg~nextitem~`";
	}
	
	echo $output;
	
} else echo "~L";