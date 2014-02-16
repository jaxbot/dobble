<?php
$CACHE_USER_PROFILE = array();
$CACHE_DISPLAYNAME = array();
$CACHE_THUMBNAIL = array();
function getUserProfile($username) {
	global $CACHE_USER_PROFILE; //cache the user profile (otherwise we'd be loading up duplicate data in one execution)
	if ($CACHE_USER_PROFILE[$username]) {
		return $CACHE_USER_PROFILE[$username];
	}
	$result = mysql_query("SELECT * FROM users WHERE username='".mysql_real_escape_string($username)."'");
	if ($result) {
		$data = mysql_fetch_array($result);
		$profile = new userprofile;
		$profile->username = $data["username"];
		$profile->password = $data["password"];
		$profile->email = $data["email"];
		$profile->info = $data["bio"];
		$profile->friends = explode(",", $data["friends"]);
		$profile->rank = $data["rank"];
		$profile->phone = $data["phone_number"];
		$profile->carrier = $data["phone_carrier"];
		$profile->allowText = $data["phone_allowtext"];
		$profile->displayname = $data["displayname"];
		$profile->gender = $data["gender"];
		$profile->allowMultiSignOn = $data["allowMultiSignOn"];
		$profile->handwriting = $data["handwriting"];
		$CACHE_USER_PROFILE[$username] = $profile;
		return $profile;
	} else {
		return false;
	}
}

function setUserProfile($user, $profile) {
	$query = "UPDATE `users` SET
`password` = '".mysql_real_escape_string($profile->password)."',
`email` = '".mysql_real_escape_string($profile->email)."',
`bio` = '".mysql_real_escape_string($profile->info)."',
`friends` = '".mysql_real_escape_string(join(array_filter($profile->friends), ","))."',
`rank` = '".mysql_real_escape_string($profile->rank)."',
`phone_number` = '".mysql_real_escape_string($profile->phone)."',
`phone_carrier` = '".mysql_real_escape_string($profile->carrier)."',
`phone_allowtext` = '".mysql_real_escape_string($profile->allowText)."',
`displayname` = '".mysql_real_escape_string($profile->displayname)."',
`gender` = '".mysql_real_escape_string($profile->gender)."',
`allowMultiSignOn` = '".mysql_real_escape_string($profile->allowMultiSignOn)."',
`handwriting` = '".mysql_real_escape_string($profile->handwriting)."'
WHERE `username` = '".mysql_real_escape_string($user)."'";

	$result = mysql_query($query);
	return $result;
}

function getUserBio($user) {
	$result = mysql_query("SELECT * FROM users WHERE username='".mysql_real_escape_string($user)."'");
	if ($result) {
		$data = mysql_fetch_array($result);
		return $data["bio"];
	} else {
		return false;
	}
}

function setUserBio($user, $bio) {
	$query = "UPDATE `users` SET
`bio` = '".mysql_real_escape_string($bio)."'
WHERE `username` = '".mysql_real_escape_string($user)."'";
	$result = mysql_query($query);
	return $result;
}

function getUserGender($user) {
	$prof = getUserProfile($user); //This has caching built in
	return $prof->gender;
}
function getUserGenderPronoun($user) {
	switch (getUserGender($user)) {
	case 1: return "his";
	case 2: return "her";
	}
	return "his/her";
}


function getUserImage($user) {
	global $STATIC_IMG_HOST;
	return $STATIC_IMG_HOST . "avatar/$user";
}

function getUserThumbnail($user) {
	global $STATIC_IMG_HOST;
	return $STATIC_IMG_HOST . "thumb/$user";
}

function setUserImage($user, $avatar, $avatarmini) {
	$query = "UPDATE `users` SET
`avatar_full` = '".mysql_real_escape_string($avatar)."',
`avatar_mini` = '".mysql_real_escape_string($avatarmini)."'
WHERE `username` = '".mysql_real_escape_string($user)."'";
	$result = mysql_query($query);
	return $result;
}

function getUserDisplayName($user) {
	global $CACHE_DISPLAYNAME;
	if ($CACHE_DISPLAYNAME[$user]) {
		return $CACHE_DISPLAYNAME[$user];
	}
	$result = mysql_query("SELECT `displayname` FROM `users` WHERE `username` = '".mysql_real_escape_string($user)."'");
	if ($result) {
		$data = mysql_fetch_array($result);

		$CACHE_DISPLAYNAME[$user] = $data["displayname"];
		return $data["displayname"];
	} else {
		return false;
	}
}

//Gets all the display names for $user's friends, caches them
function getMassUserDisplayName($user) {
	global $CACHE_DISPLAYNAME;

	$users = getFriends($user);
	$users[] = $user;

	$query = "SELECT `displayname`, `username` FROM `users` WHERE `username` IN (";
	foreach ($users as $friend) {
		$query .= "'".mysql_real_escape_string($friend)."',";
	}
	$query .= "'".mysql_real_escape_string($user)."');";

	$result = mysql_query($query);

	while ($data = mysql_fetch_row($result)) {
		$CACHE_DISPLAYNAME[$data[1]] = $data[0];
	}
}

function setUserPassword($user, $password) {
	$query = "UPDATE `users` SET
`password` = '".mysql_real_escape_string(md5($password))."'
WHERE `username` = '".mysql_real_escape_string($user)."'";
	$result = mysql_query($query);
	return $result;
}

function addFriends($user1, $user2) {
	if (isFriends($user1, $user2)) {
		return -1;
	}
	$pro = getUserProfile($user1);
	$pro->friends[count($pro->friends) + 1] = $user2;

	setUserProfile($user1, $pro);

	$pro = getUserProfile($user2);
	$pro->friends[count($pro->friends) + 1] = $user1;

	setUserProfile($user2, $pro);
}

function removeFriends($user1, $user2) {
	$pro = getUserProfile($user1);

	for ($i=0;$i<count($pro->friends) + 1;$i++) {
		if ($pro->friends[$i] == $user2) {
			$pro->friends[$i] = "";
		}
	}

	setUserProfile($user1, $pro);

	$pro = getUserProfile($user2);
	for ($i=0;$i<count($pro->friends) + 1;$i++) {
		if ($pro->friends[$i] == $user1) {
			$pro->friends[$i] = "";
		}
	}

	setUserProfile($user2, $pro);
}

function isFriends($user1, $user2) {
	$pro1 = getUserProfile($user1);

	foreach ($pro1->friends as $friend) {
		if ($friend == $user2) {
			return true;
		}
	}

	return false;
}

function getFriends($user) {
	$pro = getUserProfile($user);
	return $pro->friends;
}
function getFriendsWithActivity($user) {
	$friends = getFriends($user);
	
	$query = "SELECT username,status_active FROM `users` WHERE `username` IN (";
	$first = true;
	foreach ($friends as $friend)
	{
		if ($first) {
			$first = false;
		} else {
			$query .= ",";
		}
		$query .= "'$friend'";
	}
	$query .= ") ORDER BY `status_active` DESC;";
	$result = mysql_query($query);
	
	$friends = array();
	
	$i = 0;
	while ($data = mysql_fetch_array($result)) {
		
		$friends[$i]->name = $data["username"];
		$friends[$i]->time = $data["status_active"];
		$i++;
	}
	return $friends;
}

function getUserRegistrationTime($user) {
	$result = mysql_query("SELECT * FROM `statistics_regtime` WHERE `user` = '".mysql_real_escape_string($user)."'");
	if ($result) {
		$data = mysql_fetch_array($result);
		return $data["time"];
	} else {
		return false;
	}
}
function hasShownAllDone($user) {
	$result = mysql_query("SELECT * FROM `statistics_regtime` WHERE `user` = '".mysql_real_escape_string($user)."'");
	if ($result) {
		$data = mysql_fetch_array($result);
		return $data["alldone"];
	} else {
		return false;
	}
}
function setHasShownAllDone($user) {
	$query = "UPDATE `statistics_regtime` SET
`alldone` = '1'
WHERE `user` = '".mysql_real_escape_string($user)."'";
	$result = mysql_query($query);
}

function isLoggedIn() {
	if ($_SESSION['P_usr'] != "") {
		return true; //duh, because username will have a session id regardless. storing password wouldnt change that
	}
	return false;
}

function getUserAuthKey($username = "") {
	if ($username === "")
		$username = strtolower($_SESSION['P_usr']);
	$usr = getUserProfile($username);
	$priv = $usr->username . $usr->password;
	$priv = substr(md5($priv),0,7);
	return $priv;
}
class userprofile {
	var $username;
	var $password; //MUST md5!
	var $email;
	var $info; //Little description they can write
	var $friends = array(); //list of names
	var $rank;
	var $active;
	var $phone;
	var $carrier;
	var $allowText;
	var $displayname;
	var $avatar_mini;
	var $avatar_full;
	var $gender;
	var $allowMultiSignOn;
	var $followers = array();
	var $following = array();
}

/* Interactions */
function buzzUser($user) {
	global $N_BUZZ;
	$name = getUserDisplayName($_SESSION['P_usr']);

	//Email user

	$subject = "You've been Buzzed by ".$name;
	$html = "<html>
				<head>
					<title>$subject</title>
				</head>
			<body>
			Hi from Dobble.me!<br />
			You've been buzzed by $name, who is trying to get your attention. Head over to <a href='http://dobble.me/'>Dobble.me</a> to see what your friend wants.
			<br /><br />
			~Dobble.me
			</body>
			</html>
		";
	emailUser($user, $subject, $html);

	//Text user (if available)
	if (canSendText($user)) {
		sendTextToUser($user, "Buzz from $name!\r\nDobble.me");
	}

	//Notify
	addUserNotification($user, "You've been Buzzed by $name!", $name, $_SESSION['P_usr'], $N_BUZZ);
}

/* SMS */
function getUserFromPhone($phone) {
	$result = mysql_query("SELECT `username` FROM users WHERE phone_number='".mysql_real_escape_string($phone)."'");
	if (!$result) return false;

	$data = mysql_fetch_row($result);
	return $data[0];
}

/* Preferences */
function getUserPreferences($username) {

	$result = mysql_query("SELECT * FROM preferences WHERE user='".mysql_real_escape_string($username)."'");
	if ($result) {
		$data = mysql_fetch_array($result);
		$pref = new userpreferences;
		$pref->user = $data["user"];
		$pref->color = $data["color"];
		return $pref;
	} else {
		return false;
	}
}

function setUserPreferences($user, $pref) {

	$query = "UPDATE `preferences` SET
`about` = '".mysql_real_escape_string($pref->about)."',
`color` = '".mysql_real_escape_string($pref->color)."',
`gender` = '".mysql_real_escape_string($pref->gender)."'
WHERE `user` = '".mysql_real_escape_string($user)."'";

	$result = mysql_query($query);
	echo mysql_error();
	return $result;
}


class userpreferences {
	var $user;
	var $color;
}

//Searching
function searchQuery($q) {
	if (strlen($q) > 2) {

		$result = mysql_query("SELECT * FROM `users` WHERE (`username` LIKE '%".mysql_real_escape_string($q)."%') OR (`email` = '".mysql_real_escape_string($q)."') OR (`displayname` LIKE '%".mysql_real_escape_string($q)."%');");
		$results = array();
		$i = 0;
		while($data = mysql_fetch_array($result)) {
			$results[$i] = $data["username"];
			$i++;
		}
		return $results;
	} else {
		return -1;
	}
}

?>