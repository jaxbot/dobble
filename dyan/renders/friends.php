<?php
	require("../system/service.php");
	if (!isLoggedIn()) {
		echo "~L";
		exit;
	}
	
	//Dismiss Friend notifications
	dismissFriendRequests($_SESSION['P_usr']);
	
	$username = $_SESSION['P_usr'];
	$script = "window.inviteCode = '';";
	//<script src='common/friends.js'></script>
	
	$content = "<span style='float:right;'><input type='button' class='button' value='Add a friend' onClick='showSearchPane();'></span>
	<h3>Friends</h3><br>";
	
	//Friend Requests
	$reqs = getFriendRequests($_SESSION['P_usr']);
	if (count($reqs) > 0) {
		$content .= "<div style='display:block;border:1px solid #676767; width: 500px; padding: 3px;border-radius:5px;box-shadow: 3px 3px 15px #676767;margin:10px;'><b>Requests</b><p>";
		foreach ($reqs as $req) {
			if ($req->to == $_SESSION['P_usr']) {
				$content .= "<span style='height:55px;display:block'><img src='".getUserThumbnail($req->from) . "' class='avatar'> " . getUserDisplayName($req->from) . " (".$req->from.")<br>
				<br>
				<input type='button' class='button' onClick='friendRequestResponse(\"".$req->from."\", 1);' value='Approve'> <input type='button' class='button' onClick='friendRequestResponse(\"".$req->from."\", 0);' value='Ignore'><br>
				
				<br>
				</span>";
			}
		}
		$content .= "</div><br><br>";
	}
	
	
	$script .= "var a = window.addFriendTile;";
	$content .= "<div id='friendTileContainer'></div>";
	
	$friends = getFriendsWithActivity($username);
	
	foreach ($friends as $friend) {
		$script .= "a('".$friend->name."',".(time() - $friend->time).");";
		$foundFriend=true;
	}
	if (!$foundFriend) {
		$content .= "You have no friends added, use the button above to find them!";
	}
	//renderPage("Friends", $content, true, 1);
				
			//$content .= "<div style='display:inline-block;height:105px;width:230px;background-size:230px;background-position:0% 50%;background-image:url(".getUserImage($friend).");'><div onClick='viewUserProfile(\"$friend\");' style='background:rgba(0,0,0,0.5);width:220px;height:25px;display:inline-block;color:white;font-size:20px;font-family:calibri;padding:5px;'>".getUserDisplayName($friend)."</div><br>
	
			
	//		$content .= "<br>
		//	<font color='#676767' size=1>Last activity: $last</font></div>";
	//Render prefix $
	echo "~$";
	
	echo $content;
	echo "<script>$script";
?>