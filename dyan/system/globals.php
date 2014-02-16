<?php
	//URLs
	$STATIC_IMG_HOST = "http://int.static.dobble/";
	$base = "http://int.dobble/";
	$MOBILEBASE = "http://int.m.dobble/";
	$COMMONHOST = "http://int.s.dobble/";
	
	//STORAGE
	$STORAGE_DIR = "/disk1/www/"; //trailing slash
	
	//DATABASE
	$MYSQL_DB_HOST = "127.0.0.1";
	$MYSQL_DB_USER = "dobble";
	$MYSQL_DB_PW = "";
	$MYSQL_DB = "dobble";

	//DEBUG
	$TEST_MODE = true; //controls whether to load obfuscated & optimized code, or the development version

	//GLOBAL stuff
	$RESTRICTED_NAMES = array("misskay", "easteregg");
	
	require("system/databasedata.php");
	require("system/userdata.php");
	require("system/historydata.php");
	require("system/functiondata.php");
	require("system/emaildata.php");
	require("system/notificationdata.php");
	require("system/photodata.php");
	require("system/chatdata.php");
?>
