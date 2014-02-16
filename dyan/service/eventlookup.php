<?php require('../system/service.php');
if (isLoggedIn()) {
	$id = htmlspecialchars($_GET['id']);
	$user = htmlspecialchars($_GET['user']);
	$avatar = getUserThumbnail($user);
	$name = getUserDisplayName($user);
	
	$event = getHistoryItem($user, $id);

	$eventtime = getRelativeTime($event->time);
	$message = $event->message;
	if ($event->type != 0) {
		$message .= renderSmallEventMedia($event);
	}
	$message = str_replace("'", "\\'", $message);
	if ($event->type != 3) {
		$message = parse_links($message);	
	} else {
		$message = parse_links($message, true);	
	}
	if ($event->type == 6) {
		$message = "<img src='".$STATIC_IMG_HOST.$message."' style='max-height:200px'>";
	}
	if ($event->to != "") {
		$message .= "<br><span class='systemText' style='font-size:10px;'>on <a href='/".$event->to."'>".$event->to."'s board</a></span>";
	}
	$message = str_replace("\\'", "'", $message);
	
	echo "~e$user{{}$avatar{{}$name{{}$message{{}$eventtime{{}$id{{}";
	updateIsActive(); //Update the chat activity, since we know the user is active.
	
} else echo "~L";
?>