<?php
	//To be imported only by home.php
	
	$time = getUserRegistrationTime($_SESSION['P_usr']);
	if (time() - $time < (86400 * 7)) { //7 days
		//New user
		//Show the Getting Started pane
		
		$lastpost = getLastPost($_SESSION['P_usr']);
		if ($lastpost->event == "") {
			$hasPosted = 0;
		} else {
			$hasPosted = 1;
		}
		
		$hasFriends = 0;
		$friends = getFriends($_SESSION['P_usr']);
		foreach ($friends as $friend) {
			if ($friend != "") {
				$hasFriends = 1;
			}
		}
		
		$prof = getUserProfile($_SESSION['P_usr']);
		if ($prof->avatar_mini == "") {
			$hasAva = 0;
		} else {
			$hasAva = 1;
		}
		
		if ($hasAva == 0 or $hasFriends == 0 or $hasPosted == 0) {
			$content .= "<script src='common/gettingstarted.js'></script><script>showGettingStartedSideframe(".$hasAva.", ".$hasFriends.", ".$hasPosted.");</script>";
		} else {
			if (!hasShownAllDone($_SESSION['P_usr'])) {
				$content .= "<script src='common/gettingstarted.js'></script><script>showAllDoneSideframe();</script>";
				setHasShownAllDone($_SESSION['P_usr']);
			}
		}
		
	}
?>