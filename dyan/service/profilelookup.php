<?php require('../system/service.php');
if (isLoggedIn()) {
	$user = cleanString($_GET['user']);
	$name = getUserDisplayName($user);
	
	
	if (!isFriends($_SESSION['P_usr'], $user) and $user != $_SESSION['P_usr']) {
		//not friends, so just send the avatar and name
		$avatar = getUserImage($user);
		echo "~p$user{{}$avatar{{}$name{{}0";
		exit;
	}
	$bio = getUserBio($user);
	$avatar = getUserThumbnail($user);
	
	$historyitems = getHistoryItems($user, false, 25);
	
	echo "~p$user{{}$avatar{{}$name{{}$bio{{}";
	
	foreach ($historyitems as $historyitem) {
		$time = getRelativeTime($historyitem->time);
		$id = $historyitem->id . "__" . $historyitem->time;
					
		$message = $historyitem->event;
		if ($historyitem->type != 0) {
			$message .= renderSmallEventMedia($historyitem, true);
		}
		$message = str_replace("'", "\\'", $message);
		$message = parse_links($message);
		$message = str_replace("\\'", "'", $message);
			
		echo "$id~seper~`$message~seper~`$time~seper~`$cnt~nextitem~`";
	}
	echo "{{}";
	$comments = getCommentsWrittenBy($user, 20);
	foreach ($comments as $comment) {
		$hisitem = getHistoryItem("", $comment->postid);
		echo $comment->postid . "~seper~`" . $comment->message . "~seper~`" . getRelativeTime($comment->time) . "~seper~`" . $hisitem->from . "~seper~`" . getUserDisplayName($hisitem->from) . "~nextitem~`";
	}
	echo "{{}";
	$photos = getPicturesFromUser($user, 23);
	foreach ($photos as $photo) {
		echo $photo->id . "~seper~`" . $photo->album . "~seper~`" . getPhotoThumbnail($photo->url) . "~nextitem~`";
	}
	/*echo "{{}";
	$statistics = getUserStatistics($user);
	echo $statistics['feedposts'] . ",";
	echo $statistics['responses'] . ",";
	echo $statistics['tags'] . ",";
	echo $statistics['stats'];*/
	updateIsActive(); //Update the chat activity, since we know the user is active.
}
?>