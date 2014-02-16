<?php
	
	function isOnline($usr) {
		$result = mysql_query("SELECT * FROM `users` WHERE `username` = '".mysql_real_escape_string($usr)."';");
		if ($result) {
			$data = mysql_fetch_array($result);
			$f = $data["status_online"];
		} else {
			return false;
		}
		if (time() - $f < 15) { //15 seconds. This is updated via AJAX, which means the users have XX seconds of lag time before they are assumed offline.
			return true;
		} else {
			return false;
		}
	}
	function updateIsOnline() {
		global $IS_MOBILE, $N_SIGNON;
		if ($_SESSION['P_usr'] != "") {
			updateUserIsOnline($_SESSION['P_usr'], $IS_MOBILE);
		}
	}
	function updateUserIsOnline($user, $mobile) {
		$query = "UPDATE `users` SET
				`status_online` = '".time()."',
				`status_mobile` = '".($mobile ? 1 : 0)."'
				WHERE `username` = '".mysql_real_escape_string($user)."';";
		$result = mysql_query($query);	
	}
	function isActive($usr) {
		$result = mysql_query("SELECT * FROM `users` WHERE `username` = '".mysql_real_escape_string($usr)."';");
		if ($result) {
			$data = mysql_fetch_array($result);
			$f = $data["status_active"];
		} else {
			return false;
		}
		if (time() - $f < 60) { //1 minute. This is updated whenever a message is sent, a callback is requested, or a new page is loaded. 
			return true;
		} else {
			return false;
		}
	}
	function isMobile($usr) {
		$result = mysql_query("SELECT * FROM `users` WHERE `username` = '".mysql_real_escape_string($usr)."';");
		if ($result) {
			$data = mysql_fetch_array($result);
			return $data["status_mobile"];
		} else {
			return false;
		}
	}
	function getLastActivity($usr) {
		$result = mysql_query("SELECT * FROM `users` WHERE `username` = '".mysql_real_escape_string($usr)."';");
		if ($result) {
			$data = mysql_fetch_array($result);
			return $data["status_active"];
		} else {
			return 0;
		}
	}
	function updateIsActive() {
		if ($_SESSION['P_usr'] != "") {
			$query = "UPDATE `users` SET
`status_active` = '".time()."'
WHERE `username` = '".mysql_real_escape_string($_SESSION['P_usr'])."';";
			$result = mysql_query($query);
		}
	}
	
	function getOnlineFriends($user, $cached = false, $friends = null) {
		if (!$cached) $friends = getUserProfile($user)->friends;
		$query = "SELECT `username` FROM `users` WHERE `username` IN (";
		$query .= "'".join($friends, "','")."'";
		$query .= ") ";
		$query .= "AND `status_online` > " . (time() - 15) . ";";
		$result = mysql_query($query);
		
		$online = array();
		while ($data = mysql_fetch_row($result)) {
			$online[] = $data[0];
		}
		return $online;
	}
	
	function getChatMessages($id) {
		$result = mysql_query("SELECT * FROM `messages` WHERE `conversation_id` = '".mysql_real_escape_string($id)."' ORDER BY `time` DESC LIMIT 100;");
		$items = array();
		$i = 0;
		while($data = mysql_fetch_array($result)) {
			$items[$i] = new chatMessage;
			$items[$i]->conversation_id = $data["conversation_id"];
			$items[$i]->from = $data["from"];
			$items[$i]->message = $data["message"];
			$items[$i]->time = $data["time"];
			
			$i+=1;
		}
		return array_reverse($items); //chat messages appear at the bottom
	}
	
	
	function sendChatMessage($id,$msg) {
		$result = mysql_query("INSERT INTO `messages` (`conversation_id`, `from`, `message`, `time`)
VALUES ('".mysql_real_escape_string($id)."', 
'".mysql_real_escape_string($_SESSION['P_usr'])."', 
'".mysql_real_escape_string($msg)."', 
'".microtime(true)."');");
	}
	
	function sendMessageTo($to,$msg) {
		$id = getChatId($_SESSION['P_usr'], $to);
		sendChatMessage($id, $msg);
		
		//Check if the user is online
		if (!isOnline($to)) {
			//Email the user
			
			$fname = getUserDisplayName($_SESSION['P_usr']);
			
			$body = "<h3>Dobble.me</h3>
			Hello, ".getUserDisplayName($to)."!<br>
			".$fname." sent you the following message on Dobble:<br>
			".htmlspecialchars($msg)."<br>
			<br>Reply to it by logging in to <a href='http://dobble.me'>Dobble</a>.
			<br>
			Later!<br>Dobble.me";
			emailUser($to, $fname . " sent you a message", $body);
			
		}
	}
	
	function newChatMessages($usr, $lastTime) {
		global $sep;
		$messages = getChatMessages($usr);
		foreach ($messages as $message) {
			list($from, $msg, $time) = explode($sep, $message);
			if ($time > $lastTime) {
				return true;
			}
		}
		return false;
	}
	function getNewChatMessages($id, $lastTime) {
		$messages = getChatMessages($id);
		$i = 0;
		$items = array();
		foreach ($messages as $message) {
			if ($message->time > $lastTime) {
				$items[$i] = $message;
				$i++;
			}
		}
		return $items;
	}
	function getChatId($user1, $user2) {
		$arr = array($user1, $user2);
		sort($arr);
		return md5(join($arr));
	}
	
	class chatMessage {
		var $from;
		var $conversation_id;
		var $message;
		var $time;
		var $flags;
	}
	
	//Generally the case
	updateIsOnline();
?>