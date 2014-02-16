<?php
	require("../system/service.php");
	if (!isLoggedIn()) {
		echo "~L";
		exit;
	}
	$username = $_SESSION['P_usr'];
	$displayName = getUserDisplayName($username);
	$avatar = getUserImage($username);
	$mini_avatar = getUserThumbnail($username);
	$bio = getUserBio($username);
	$prof = getUserProfile($_SESSION['P_usr']);
	$prefs = getUserPreferences($username);

	$script = "window.profile__UserDisplayName = '$displayName';
window.profile__UserEmail = '".$prof->email."';
window.profile__UserCanText = '".$prof->allowText."';
window.profile__UserCarrier = '".$prof->carrier."';
window.profile__UserPhone = '".$prof->phone."';
window.profile__Bio = '".str_replace("'", "\\'", $bio)."';
window.profile__Color = '".$prefs->color."';
window.profile__Gender = '".$prof->gender."';
window.profile__Avatar = '".$avatar."';
window.profile__allowMultiSignOn = '".$prof->allowMultiSignOn."';
";
	$script .= "fadeInElement(_g('topoverlay'));";
	$content = "
	<div id='topoverlay' style='padding:3px;opacity:0;background: url(\"$avatar\");background-size:230px;background-position:0% 0%; width:100%;height:140px;margin-left:-3px;margin-top:-3px;border-radius:10px;'>
	<div style='margin:-3px;padding:3px;padding-right:0px;border-radius:10px;float:right;width:502px;height:100%;;background:url(\"img/horifade.png?44444\");'>
	
	<div style='float:right;padding:3px;'>
		<input type='button' class='button' value='Edit' onClick='showPictureEditor();' id='editBtn'>
		<input type='button' class='button' value='Account Settings' onClick='showSettingsPane();'>
		<input type='button' class='button' value='Sign Out' onClick='location.href=\"logout.php\";'>
	</div>
	</div>
	<span style='color:white;background-color:rgba(0,0,0,0.4);font-size:15px;padding:3px;padding-top:0px;border-radius:3px;'>$displayName</span>
	</div><br>
	<div id='profiletimeline'>
	</div>
	
	<span id='pref_color'></span>
	";
	$historyitems = getUserHistoryEntries($username, 50, false);
	$comments = getMassCommentCount(getCommentIdsArray($historyitems));
	
	foreach ($historyitems as $historyitem) {
		$historyitem->message = renderEvent($historyitem);
		$time = time() - $historyitem->time;
		$json = json_encode($historyitem);
		$cnt = $comments[$historyitem->id];
		if (!$cnt) $cnt = 0;
		$script .= "addProfileTimeline($json,$time,$cnt);";
		
	}
	if (isset($_GET['settings'])) {
		$script .= "showSettingsPane();";
	}
	$script.="updateUI();";
	
	//Render prefix
	echo "~$";
	echo $content;
	echo "<script>$script";
?>