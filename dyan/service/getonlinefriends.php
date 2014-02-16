<?php require('../system/service.php');
if (isLoggedIn()) {

	echo "~F";
	$onlineFriends = getOnlineFriends($_SESSION['P_usr']);
	echo join($onlineFriends, ",");
	
}
?>