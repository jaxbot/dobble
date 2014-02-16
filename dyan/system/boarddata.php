<?php

function getUserTheme($username) {

	$query = "SELECT * FROM `themes` WHERE `user` = '" . $username . "';";
	$res = mysql_query($query);
	$data = mysql_fetch_array($res);


	return $data;
}

function getUserThemeCss($username) {

	$theme = getUserTheme($username);
	$output = "body {
		background-image: url(".$theme['background'].");
		background-repeat: repeat;
		background-color: #".dechex($theme['backgroundColor']).";
		}
		";

	$output = strip_tags($output);
	$output = htmlspecialchars($output);

	return $output;
}

function getUserHandwriting($username) {
	$pro = getUserProfile($username);
	return $pro->handwriting;
}

function setUserHandwriting($username, $handwriting) {
	$pro = getUserProfile($username);
	$pro->handwriting = $handwriting;
	setUserProfile($username,$pro);
}

function generateJSFromBoardEntries($entries, $old = false) {
	$data = "";
	$lasttime = 0;
	$oldesttime = MAX;
	foreach ($entries as $entry)
	{
		//list($id, $from, $displayName, $avatar, $event, $time, $type, $photoid, $albumid) = renderEvent($entry);
		$event = renderEvent($entry);
		$event = str_replace("\\", "\\\\", $event);
		$event = str_replace("'", "\\'", $event);
		$event = str_replace("\r", "", $event);
		$event = str_replace("\n", "<br>", $event);

		$time = (time()-$entry->time);
		switch ($entry->type) {
		case 1:
			$photoids = explode(",", $entry->meta);
			foreach ($photoids as $photoid)
			{
				$photo = getPicture($photoid, $entry->from);
				$data .= "addPhoto('".$photoid."','".$photo->url."','".$entry->from."',".(time()-$entry->time).");";
			}
			break;
		case 5:
			list($color,$handwriting) = explode(",", $entry->meta);
			$data .= "addStickyNote('".$entry->id."','".$event."','".$entry->from."',".$time.",'".$color."',$handwriting);";
			break;
		case 6:
			$data .= "addStickyNote('".$entry->id."','<img src=\"".$GLOBALS['STATIC_IMG_HOST'] . $event ."\">','".$entry->from."',".$time.",'".$entry->meta."',0);";
			break;
		case 7:
			$data .= "addNote('".$entry->id."','".$event."','".$entry->from."',".$time.",".$entry->meta.");";
			break;
		default:
			$data .= "addPost('".$entry->id."','".$event."',".$time."); \n";
			break;

		}
		if ($entry->time > $lasttime)
			$lasttime = $entry->time;
		if ($entry->time < $oldesttime)
			$oldesttime = $entry->time;

	}
	if ($lasttime != 0 and !$old)
		$data .= "boardLastTime = $lasttime;";
	$data .= "oldestTime = $oldesttime;";
	return $data;
}

?>
