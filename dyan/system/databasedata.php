<?php
//databasedata.php
//Connects to MySQL based on global defined settings

$IS_DATABASE_READY = false;

if (mysql_pconnect($MYSQL_DB_HOST, $MYSQL_DB_USER, $MYSQL_DB_PW)) {
	if (mysql_select_db($MYSQL_DB)) {
		$IS_DATABASE_READY = true;
	}
}

function initSession() {
	if ($_COOKIE['D'] != "") {
		$query = "SELECT user FROM sessions WHERE id='".mysql_real_escape_string($_COOKIE['D'])."';";
		$res = mysql_query($query);
		if ($res) {
			$data = mysql_fetch_row($res);
			$_SESSION['P_usr'] = $data[0];
		}
	}
}
//Called after above, scrubs Mobile prefix. This allows for separate mobile and desktop sessions
function initMobileSession() {
	$_SESSION['P_usr'] = str_replace("m:/", "", $_SESSION['P_usr']);
}

function loginUser($user, $pass, $prefix = "") {
	$user = strtolower($user);
	$profile = getUserProfile($user);
	
	if ($profile->password != md5($pass)) return false;
	if ($profile->allowMultiSignOn) {
		if (impersonateSession($prefix . $profile->username)) return true;
	}
	
	newSession($prefix . $profile->username);
		
	return true;
}

function impersonateSession($user) {
	$query = "SELECT `id` FROM sessions WHERE user='".mysql_real_escape_string($user)."';";
	mysql_query($query);	
	$res = mysql_query($query);
	
	if (!$res) return false;
	
	$data = mysql_fetch_row($res);
	
	setcookie("D", $data[0], time() + 60 * 60 * 24 * 7 /* 7 days */);
	
	return true;
}

function newSession($user) {
	$id = uniqid(substr(md5($user),1,6));
	
	$query = "REPLACE INTO `sessions` (`user`,`id`) VALUES ('".mysql_real_escape_string($user)."','".mysql_real_escape_string($id)."');";
	mysql_query($query);
	
	setcookie("D", $id, time() + 60 * 60 * 24 * 7 /* 7 days */, "/");
	
	return $id;
}
?>