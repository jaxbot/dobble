<?php
//NotificationData
//Handles notifications and requests between users

//Types
$N_FRIEND = 0;
$N_EVENT = 1;
$N_PHOTO = 2;
$N_CHAT = 3;
$N_BUZZ = 4;
$N_SIGNON = 5;

	$friendReqCallbackPage = "javascript:showFriendRequests();";
	function sendFriendRequest($to, $from) {
		$fname = getUserDisplayName($from);
		$result = mysql_query("INSERT INTO `friend_requests` (`from`, `to`, `time`)
VALUES ('".mysql_real_escape_string($from)."', '".mysql_real_escape_string($to)."', '".time()."');");
		addUserNotification($to, "You have a new friend request from ".$fname.".", $friendReqCallbackPage, $from, 0);
		
		//Check if the user is online
		if (!isOnline($user)) {
			//Email the user
			$body = "<h3>Dobble.me</h3>
			Hello, ".getUserDisplayName($user)."!<br>
			".getUserDisplayName($from)." wants to be friends Dobble!<br>
			If you know this person, <a href='http://dobble.me/friends.php'>click here</a> to accept the request.<br>
			Otherwise, next time you log into your account, you may click the <i>Ignore</i> button on your Friends page to politely ignore the request.<br>
			Don't worry, nobody will know you declined being friends.<br><br>
			No pressure,<br>Dobble.me";
			emailUser($user, $fname . " sent your a friend request", $body);
			
		}
	}
	function checkFriendRequestPending($to, $from) {
		$reqs = getFriendRequests($to);
		
		foreach ($reqs as $req) {
			if ($req->from == $from) {
				return true;
			}
		}
		return false;
	}
	function addUserNotification($user, $notification, $action, $from, $type) {
		global $N_FRIEND,$N_EVENT,$N_PHOTO,$N_CHAT;

		$result = mysql_query("INSERT INTO `notifications` (`id`, `from`, `to`, `body`, `action`, `type`, `time`)
VALUES ('', 
'".mysql_real_escape_string($from)."',
'".mysql_real_escape_string($user)."',
'".mysql_real_escape_string($notification)."',
'".mysql_real_escape_string($action)."',
'".mysql_real_escape_string($type)."',
'".time()."');");
	}
	function getUserNotifications($user) {
		$result = mysql_query("SELECT * FROM `notifications` WHERE `to` = '".mysql_real_escape_string($user)."';");
		$items = array();
		$i = 0;
		while($data = mysql_fetch_array($result)) {
			$items[$i] = new notification;
			$items[$i]->id = $data["id"];
			$items[$i]->body = $data["body"];
			$items[$i]->time = $data["time"];
			$items[$i]->action = $data["action"];
			$items[$i]->from = $data["from"];
			$items[$i]->type = $data["type"];
			$i+=1;
		}
		return $items;
	}
	function newUserNotifications($user, $time) {
		$result = mysql_query("SELECT * FROM `notifications` WHERE `to` = '".mysql_real_escape_string($user)."' ORDER BY `time` DESC LIMIT 1;");
		$data = mysql_fetch_array($result);
		if ($data["time"] == "" and $time == 0) return false;
		if ($data["time"] != $time) return true;
		return false;
	}
	function newUserNotificationsEx($user, $time) {
		$result = mysql_query("SELECT * FROM `notifications` WHERE `to` = '".mysql_real_escape_string($user)."' ORDER BY `time` DESC LIMIT 1;");
		$data = mysql_fetch_array($result);

		if ($data["time"] == "" and $time != 0) {
		    return -1;
		   }
		if ($data["time"] != $time) { return true; }
		return false;
	}
	function removeUserNotification($user, $id) {
		$result = mysql_query("DELETE FROM `notifications`
		WHERE (`to` = '".mysql_real_escape_string($user)."') AND (`id` = '".mysql_real_escape_string($id)."');");
	}
	function dismissFriendRequests($user) {
		$result = mysql_query("DELETE FROM `notifications`
		WHERE (`type` = '0') AND (`to` = '".mysql_real_escape_string($user)."');");
	}
	function getFriendRequests($user) {
		$result = mysql_query("SELECT * FROM `friend_requests` WHERE `to` = '".mysql_real_escape_string($user)."';");
		$items = array();
		$i = 0;
		while($data = mysql_fetch_array($result)) {
			$items[$i] = new friendReq;
			$items[$i]->to = $data["to"];
			$items[$i]->from = $data["from"];
			$items[$i]->time = $data["time"];
			$i+=1;
		}
		return $items;
	}
	function removeFriendRequest($user, $from) {
		$result = mysql_query("DELETE FROM `friend_requests`
		WHERE (`from` = '".mysql_real_escape_string($from)."') AND (`to` = '".mysql_real_escape_string($user)."');");
	}

	class notification {
		var $body;
		var $time;
		var $action;
		var $type;
		var $from;
		var $id;
	}
	class friendReq {
		var $to;
		var $from;
		var $time;
	}
?>