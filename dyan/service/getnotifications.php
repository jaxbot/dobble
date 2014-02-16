<?php require('../system/service.php');
if (isLoggedIn()) {
	function _getNotificationData() {
		global $time;
		
		if (!newUserNotifications($_SESSION['P_usr'], $time)) return false;
		
		$nots = getUserNotifications($_SESSION['P_usr']);
		
		$output = "~n".(count($nots)) . ">>";
		foreach ($nots as $not) {
			$output .= "<not>" . $not->body . "~sp~`" . $not->time . "~sp~`" . $not->action . "~sp~`" . $not->type . "~sp~`" . $not->from .  "~sp~`" . $not->id . "~sp~`" . (time() - $not->time);
		}
		return $output;
	}
	
	$time = intval($_GET['id']);
	$res = _getNotificationData();
	if ($res != false) {
		echo $res;
	}

	updateIsOnline();
} else {
	echo "~L";
}
?>