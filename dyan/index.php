<?php
	$LIBRARIES_LOADED = true;
	require_once("system/globals.php");
	initSession();

	$loggedin = isLoggedIn();

	if ($_GET['viewuser'] != "") {
		if (!$loggedin)
			require("interface/user.php");
		else
			header("Location: /#!/".cleanString($_GET['viewuser']));
		exit;
	}

	//Check if logged in
	if ($loggedin) {
		//Logged in

		//Is this right after registration?
		if (isset($_GET['firsttime'])) {
			require("pages/firsttime.php");
			exit;
		}

		//Load the main program
		require("interface/home.php");
	} else {
		if ($_GET['nomobile'] != "") {
			$_SESSION['nomobile'] = 1;
		}
		if (stripos($_SERVER['HTTP_USER_AGENT'], "mobile") !== false) {
			if ($_SESSION['nomobile'] != 1) {
				//header("Location: http://m.dobble.me/");
			}
		}
		if (!isset($_GET['goodbye'])) {
			require("pages/home_logged_out_staging.php");
		} else {
			require("pages/goodbye.php");
		}
	}
?>