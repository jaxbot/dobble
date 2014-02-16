<?php
	//This will change the directory to the root, start the session, and load globals
	//Used for renders and services
	chdir("../");

	require("system/globals.php");
	initSession();

	//verify authkey for data that is modifiable
	function verifyAuth() {
		if ($_GET['a'] != getUserAuthKey())
			die("Service problem");
	}
