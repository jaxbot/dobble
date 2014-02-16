<?php require('../system/service.php');
if (isLoggedIn()) {
	$with = htmlspecialchars($_GET['user']);
	$id = getChatId($_SESSION['P_usr'], $with);
	$lastTime = htmlspecialchars($_GET['time']);
	
	function _getOnlineData($with) {
		if (isOnline($with)) {
			$status = 1; //Idle
			if (isActive($with)) {
				$status = 2; //Active
			}
		} else {
			$status = 0; //Offline
		}
		return $status;
	}
	
	$lastStatus = _getOnlineData($with);
	
	function _getChatData() {
		global $with, $id, $lastTime, $lastStatus;
		
		$status = _getOnlineData($with);
		
		$messages = getNewChatMessages($id, $lastTime);
		if (count($messages) > 0) {
			if (isset($_GET['new'])) {
				$output = "~h"; //Append the message
			} else {
				$output = "~H"; //The following data will be the entire conversation
				$lastTime = time() - 86400;
			}
			foreach ($messages as $message) {
				$fromname = getUserDisplayName($message->from);
				$time = time() - $message->time;
				$msg = parse_links($message->message);
				$output .= $message->from."~seper~`$fromname~seper~`".$msg."~seper~`$time~seper~`".$message->time."~nextitem~`";
			}
		} elseif (isset($_GET['new'])) {
			if ($status == $lastStatus) {
				return false;
			} else {
				$output = "~h"; //No new messages, but the status changed, so push the data
			}
		} else {
			$output = "~H"; //The conversation is blank
		}
		
		
		$output .= "~~:$status";
		
		//Append the ID (in this case, the user)
		$output .= "~~:$with";
		return $output;

	}
	
	function _outputChatData() {
		global $startTime;
		$res = _getChatData();
		if ($res != false) {
			echo $res;
		} else {
			echo "~~"; //No data
			exit;
		}
	}
	$startTime = time();
	_outputChatData();
}
?>