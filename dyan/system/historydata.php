<?php
//History Types
//0 == Status
//1 == Photo
//2 == Link
//3 == Linked Photo
//4 == Text (As opposed to a status)
//5 == Sticky note
//6 == Sticky note (picture)
//7 == Note

//Flags
//1 == Mobile SMS
//2 == Mobile web

function addHistoryEvent($user, $event, $flags) {
	$result = mysql_query("INSERT INTO `history` (`from`, `time`, `message`, `flags`)
VALUES ('".mysql_real_escape_string($user)."', '".time()."', '".mysql_real_escape_string($event)."','".mysql_real_escape_string($flags)."');");
}
function addPhotoHistoryEvent($user, $event, $photo_id, $album, $flags) {
	$result = mysql_query("INSERT INTO `history` (`from`, `time`, `message`, `type`, `meta`, `flags`)
VALUES ('".mysql_real_escape_string($user)."', '".time()."', '".mysql_real_escape_string($event)."', '1', '".mysql_real_escape_string($photo_id)."','".mysql_real_escape_string($flags)."');");
}
function addLinkHistoryEvent($user, $url, $event) {
	$result = mysql_query("INSERT INTO `history` (`from`, `time`, `message`, `type`, `meta`)
VALUES ('".mysql_real_escape_string($user)."', '".time()."', '".mysql_real_escape_string($event)."', '2', '".mysql_real_escape_string($url)."');");
}
function addLinkPhotoHistoryEvent($user, $url, $event) {
	$result = mysql_query("INSERT INTO `history` (`from`, `time`, `message`, `type`, `meta`)
VALUES ('".mysql_real_escape_string($user)."', '".time()."', '".mysql_real_escape_string($event)."', '3', '".mysql_real_escape_string($url)."');");
}
function addTextHistoryEvent($user, $event) {
	$result = mysql_query("INSERT INTO `history` (`from`, `time`, `message`, `type`)
VALUES ('".mysql_real_escape_string($user)."', '".time()."', '".mysql_real_escape_string($event)."', '4');");
}
function addBoardItem($user, $type, $event, $from, $meta) {
	$query = "INSERT INTO `history` (`from`,`time`,`to`,`type`";
	switch ($type)
	{
	case 5: //text sticky note
	case 6: //image sticky note
	case 7: //note
		$query .= ",`message`,`meta`) VALUES ('".mysql_real_escape_string($from)."',
			'".time()."',
			'".mysql_real_escape_string($user)."',
			'".mysql_real_escape_string($type)."',
			'".mysql_real_escape_string($event)."',
			'".mysql_real_escape_string($meta)."')";
		break;
	}
	$query .= ";";
	$result = mysql_query($query);
	echo mysql_error();
}

function deleteHistoryItem($user, $id) {
	$result = mysql_query("DELETE FROM `history`
		WHERE (`id` = '".mysql_real_escape_string($id)."') AND (`from` = '".mysql_real_escape_string($user)."');");
}
function deletePhotoHistoryItem($photo_id, $user) {
	$result = mysql_query("DELETE FROM `history`
		WHERE (`meta` = '".mysql_real_escape_string($photo_id)."') AND (`from` = '".mysql_real_escape_string($user)."');");
}

function getLastPost($user) {
	$items = getHistoryItems($user, false);
	return $items[0];
}

function getHistoryItem($user, $id) {
	$result = mysql_query("SELECT * FROM history WHERE id='".mysql_real_escape_string($id)."'");
	if ($result) {
		$data = mysql_fetch_array($result);

		return hisItemFromMysql($data);
	} else {
		return false;
	}
}

function hisItemFromMysql($data)
{
	$historyItem = new hisItem;
	$historyItem->id = $data["id"];
	$historyItem->time = $data["time"];
	$historyItem->from = $data["from"];
	$historyItem->to = $data["to"];
	$historyItem->message = $data["message"];
	$historyItem->type = $data["type"];
	$historyItem->flags = $data["flags"];
	$historyItem->meta = $data["meta"];
	return $historyItem;
}

function getHistoryItems($user, $all = true, $max = 30, $start = 0) {
	if ($all) {
		$users = getFriends($user);
		$users = array_filter($users); //delete blank entries
		/*foreach ($users as $_user) {
			$items = array_merge($items, getUserHistoryEntries($_user, $max));
		}*/ //Way too inefficient, phased out 10/19/11

		//This query grabs just what we need in one stroke. Way more efficient
		$query = "SELECT * FROM `history` WHERE (`to` = '".mysql_real_escape_string($user) . "') OR `from` IN (";
		foreach ($users as $friend) {
			$query .= "'".mysql_real_escape_string($friend)."',";
		}
		$query .= "'".mysql_real_escape_string($user)."'";
		//$query .= "'$user','".join($users, "','")."'";
		$query .= ") ";
		if ($start != 0)
			$query .= "AND `time` <= ".intval($start)." ";
		$query .= "ORDER BY `time` DESC LIMIT " . intval($max) . ";";
		$result = mysql_query($query);
		//echo $query;
		$items = array();
		$i = 0;
		while($data = mysql_fetch_array($result)) {
			$items[$i] = hisItemFromMysql($data);
			$i+=1;
		}
	} else {
		$items = getUserHistoryEntries($user, $max);
		if ($max != -1) {
			$items = array_splice($items, 0, $max);
		}
	}

	//$items = sortEvents($items);

	return $items;
}
function getLastHistoryTime($user, $incfriends = true) { //returns the last time of a user's stream (including friends)
	$users = getFriends($user);
	$users = array_filter($users); //delete blank entries

	$query = "SELECT `time` FROM `history` WHERE (`to` = '".mysql_real_escape_string($user) . "') OR `from` IN (";
	if ($incfriends) {
		foreach ($users as $friend) {
			$query .= "'".mysql_real_escape_string($friend)."',";
		}
	}
	$query .= "'".mysql_real_escape_string($user)."'";

	$query .= ") ";
	$query .= "ORDER BY `time` DESC LIMIT 1;";
	$result = mysql_query($query);

	$data = mysql_fetch_array($result);
	return $data["time"];
}
function getUserHistoryEntries($user, $max = 30, $public = false) {
	$items = array();
	$i = 0;
	$query = "SELECT * FROM `history` WHERE `from` = '".mysql_real_escape_string($user)."' ";
	if ($public) {
		$query .= "AND `flags` = '1' ";
	}
	$query .= "ORDER BY `time` DESC LIMIT " . intval($max) . ";";
	$result = mysql_query($query);

	while($data = mysql_fetch_array($result)) {
		$items[$i] = hisItemFromMysql($data);
		$i+=1;

	}
	return $items;
}
function getUserBoardEntries($user, $max = 30, $maxtime = 1, $starttime = -1) {
	$items = array();
	$i = 0;
	$query = "SELECT * FROM `history` WHERE ";
	if ($starttime !== -1)
		$query .= "(`time` < ".intval($starttime).")";
	else
		$query .= "(`time` > ".intval($maxtime).")";
	$query .= " AND ";
	$query .= "((`from` = '".mysql_real_escape_string($user)."' AND `to` = '') OR `to` = '".mysql_real_escape_string($user)."') ";
	//if ($maxtime)
	//$query .= "AND (`time` > ".intval($maxtime).") ";
	$query .= "ORDER BY `time` DESC LIMIT " . intval($max) . ";";
	$result = mysql_query($query);

	while($data = mysql_fetch_array($result)) {
		$items[$i] = hisItemFromMysql($data);
		$i+=1;

	}

	return $items;
}
function getLastBoardTime($user) {
	$users = getFriends($user);
	$users = array_filter($users); //delete blank entries

	$query = "SELECT `time` FROM `history` WHERE `from` IN (";
	if ($incfriends) {
		foreach ($users as $friend) {
			$query .= "'".mysql_real_escape_string($friend)."',";
		}
	}
	$query .= "'".mysql_real_escape_string($user)."'";

	$query .= ") ";
	$query .= "ORDER BY `time` DESC LIMIT 1;";
	$result = mysql_query($query);

	$data = mysql_fetch_array($result);
	return $data["time"];
}
//slow, use mysql
function historyItemCompare($a, $b) {
	if ($a == $b) {
		return 0;
	}
	if ($a->time > $b->time) {
		return -1;
	} else {
		return 1;
	}
}
//never. ever. EVER. use this.
function sortEvents($ev) {
	for ($n=0;$n<count($ev);$n++) {
		for ($i=0;$i<count($ev);$i++) {
			if ($ev[$i]->time < $ev[$i + 1]->time) {
				//swap
				$b = $ev[$i];
				$ev[$i] = $ev[$i + 1];
				$ev[$i + 1] = $b;
			}
		}
	}

	return $ev;
}

//*******
//Comments

function addComment($user, $id, $msg, $from, $type = 1) { //defaults to event
	$result = mysql_query("INSERT INTO `comments` (`postid`, `from`, `message`, `time`)
VALUES ('".mysql_real_escape_string($id)."', '".mysql_real_escape_string($from)."', '".mysql_real_escape_string($msg)."', '".time()."');");


	$action = $id . "::" . $user;
	notifyUsers($user, $id, $type, $from, $action, $msg);

}

function getCommentStream($stream, $time = 0) {
	$query = "SELECT * FROM `comments` WHERE `postid` = '".mysql_real_escape_string($stream)."'";
	if ($time > 0)
		$query .= " AND `time` > $time";
	$query .= " ORDER BY `time`;";
	$result = mysql_query($query);
	$items = array();
	$i = 0;
	while($data = mysql_fetch_array($result)) {
		$items[$i] = new comment;
		$items[$i]->id = $data["id"];
		$items[$i]->postid = $data["postid"];
		$items[$i]->from = $data["from"];
		$items[$i]->message = $data["message"];
		$items[$i]->time = $data["time"];

		$i+=1;
	}
	return $items;
}
function getCommentCount($stream) {
	$result = mysql_query("SELECT `postid` FROM `comments` WHERE `postid` = '".mysql_real_escape_string($stream)."';");
	return mysql_num_rows($result);
}
//Believe it or not, this is actually more efficient than above
function getMassCommentCount($streams) {
	$query = "SELECT `postid` FROM `comments` WHERE `postid` IN (";
	$query .= "'".join($streams, "','")."');";
	/*$last = end($streams);

	foreach ($streams as $stream) {
		$query .= "'".mysql_real_escape_string($stream)."'";
		if ($last !== $stream) $query.= ",";
	}
	$query .= "'".mysql_real_escape_string($last)."');";
	*/
	$result = mysql_query($query);

	$res = array();
	//print_r($streams);
	while($data = mysql_fetch_row($result)) {
		$res[] = $data[0];
	}
	//print_r($res);
	$res2 = array_count_values($res);
	return $res2;
}

function getCommentsWrittenBy($user, $max) {

	$result = mysql_query("SELECT * FROM `comments` WHERE `from` = '".mysql_real_escape_string($user)."' ORDER BY `time` DESC LIMIT " . intval($max) . ";");
	$items = array();
	$i = 0;
	while($data = mysql_fetch_array($result)) {
		$items[$i] = new comment;
		$items[$i]->id = $data["id"];
		$items[$i]->postid = $data["postid"];
		$items[$i]->from = $data["from"];
		$items[$i]->message = $data["message"];
		$items[$i]->time = $data["time"];

		$i+=1;
	}
	return $items;
}

function deleteCommentStream($user, $id) {

	$result = mysql_query("DELETE FROM `comments`
		WHERE (`postid` = '".mysql_real_escape_string($id)."');");
}

function deleteComment($user, $id, $commentid, $cfrom) {

	$result = mysql_query("DELETE FROM `comments`
		WHERE (`postid` = '".mysql_real_escape_string($id)."') AND (`id` = '".mysql_real_escape_string($commentid)."') AND (`from` = '".mysql_real_escape_string($cfrom)."');");

}

function notifyUsers($user, $id, $type, $from, $action, $rawmessage) {
	global $split, $sep, $N_EVENT, $N_PHOTO;
	$comments = getCommentStream($id);
	$fname = getUserDisplayName($from);

	if ($user == $from) {
		$name = getUserGenderPronoun($user);
	} else {
		$name = getUserDisplayName($user) . "'s";
	}
	if ($type == $N_EVENT) {
		$msg = $fname . " responded to your post.";
		$msgall = $fname . " responded to " . $name . " post.";
	}
	if ($type == $N_PHOTO) {
		$msg = $fname . " responded to your photo.";
		$msgall = $fname . " responded to " . $name . " photo.";
	}
	if ($user != $from) {

		addUserNotification($user, $msg, $action, $from, $type);
		$body = "<h3>Dobble.me</h3>
			$msg<br><br>He/she said: <br>
			\"".$rawmessage."\"<br>
			Head over to <a href='http://dobble.me/'>Dobble</a> to reply!<br>
			<br>
			Peace,
			<br>Dobble.me";
		if (!isOnline($user)) {
			emailUser($user, $msg, $body);
		}

	}
	$body = "<h3>Dobble.me</h3>
		Hey from Dobble!<br>
		<b>$msgall</b>
		<br>He/she said:<br>
		\"".$rawmessage."\"<br>
		Head over to <a href='http://dobble.me/'>Dobble</a> to reply!<br>
		<br>
		Ciao!<br>
		Dobble.me";

	$toNotify = array();
	$i = 0;
	foreach ($comments as $comment) {

		$skip = false;
		for ($z=0;$z<$i;$z++) {
			if ($toNotify[$z] == $comment->from) {
				$skip = true;
			}
		}
		if ($skip != true and $comment->from != $user and $comment->from != $from) {
			if (isFriends($comment->from, $from)) { //sandboxing
				$toNotify[$i] = $comment->from;
				addUserNotification($comment->from, $msgall, $action, $from, $type);
				//Email, if offline
				if (!isOnline($comment->from)) {
					emailUser($comment->from, $msgall, $body);
				}
				$i++;
			}
		}

	}

}

//UI Related (move elsewhere?)
function getCommentIdsArray($historyitems) {
	$comments = array();
	foreach ($historyitems as $historyitem) {
		if ($historyitem->type == 1) { //Photo
			$photoids = explode(",", $historyitem->meta);
			if (count($photoids) == 1) {
				$comments[] = "photo".$historyitem->meta;
			} else {
				$comments[] = $historyitem->id;
			}
		} else { //just regular
			$comments[] = $historyitem->id;
		}
	}

	return $comments;
}
function renderEvent($historyitem) {
	$event = $historyitem->message;
	$from = $historyitem->from;
	$avatar = getUserThumbnail($from);

	$time = time() - $historyitem->time;
	$cnt = 0;
	if ($historyitem->type == 1) { //Photo
		$photoids = explode(",", $historyitem->meta);
		if (count($photoids) == 1) {
			$photo = getPicture($historyitem->meta, $from);
			if ($photo->url != "") {
				$event .= "<br><img src='".getPhotoThumbnail($photo->url)."' style='max-width: 100px;padding-left:10px;'>";
			}
		} else {
			//Multiple photos, let them comment on the mass sharing
			$event .= "<div style='display:block;width:560px;'>";
			$q = 0;
			foreach ($photoids as $photoid) {
				$photo = getPicture($photoid, $from);
				if ($photo->url != "") {
					$event .= "<a href='#pictures/?photo=".$photo->id."&from=$from&album=".$photo->album."'><img src='".getPhotoThumbnail($photo->url)."' style='";
					if (count($photoids) < 3) {
						$event .= "max-width: 200px;padding-left:10px;";
					} else {
						if ($q == 0) {
							$event .= "max-width: 200px;padding:7px;";
						} else {
							if ($q < 2) {
								$event .= "max-width: 150px;padding:7px;";
							} else {
								if ($q < 3) {
									$event .= "max-width: 100px;padding:7px;padding-right:0px;";
								} else {
									$event .= "max-width: 70px;padding:2px;";
									if ($q == 3) {
										$event .= "padding-left: 62px;";
									}
								}
							}
						}
					}
					$event .= "'></a>";
					$q++;
				}

			}
			$event .= "</div>";
		}
	} else {
		$event = parse_links($event);
	}

		if ($historyitem->flags == "1") $event .= "<br><span class=systemText>via mobile device</span>";
		if ($historyitem->flags == "2") $event .= "<br><span class=systemText>via mobile web</span>";
	//$event = str_replace("\\", "\\\\", $event);
	//$event = str_replace("'", "\\'", $event);

	return $event;
}

function renderSmallEventMedia($historyitem, $small=false) {
	$event = ""; //The event is appended to
	$width = "45px";
	if ($small) $width = "20px";
	if ($historyitem->type == 1) { //Photo
		$photoids = explode(",", $historyitem->meta);
		if (count($photoids) == 1) {
			$photo = getPicture($historyitem->meta, $historyitem->from);
			if ($photo->url != "") {
				$event .= "<br><img src='".getPhotoThumbnail($photo->url)."' style='max-width: $width;padding-left:10px;'>";
			}
		} else {
			$q = 0;
			foreach ($photoids as $photoid) {
				$photo = getPicture($photoid, $historyitem->from);
				if ($photo->url != "" and $q < 8) {
					$event .= "<img src='".getPhotoThumbnail($photo->url)."' style='max-width:$width;padding:2px;";
					$event .= "'>";
					$q++;
				}

			}
			//$event .= "</div>";
		}
	} else {
		$comments = getCommentStream($historyitem->id, $historyitem->from);
	}
	if ($historyitem->type == 3) {
		$event .= "<img src='".$historyitem->link_url."' style='max-width:400px;max-height:150px;padding:5px;'><br><span class='systemText' style='font-size:10px'>Source: ".$historyitem->link_url." </span>";
	}
	if ($historyitem->type == 2) {
		$event .= $historyitem->link_url;
	}

	return $event;
}

class hisItem {
	var $time;
	var $from;
	var $to;
	var $message;
	var $id;
	var $type;
	var $flags;
	var $meta;
}
class comment {
	var $id;
	var $postid;
	var $from;
	var $message;
	var $time;
}
?>