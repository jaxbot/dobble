<?php
/* Dobble.me Testing Component: System
5-3-2012 
Tests the overall system */

function runSystemTest() {
	startTest("Load Dobble.me globals");
	try {
		chdir("../dyan");
		require("system/globals.php");
		testResult(1);
	} catch (Exception $e) {
		testResult(0);
	}
	
	//Test basic MySQL
	startTest("MySQL no errors");
	if (mysql_error() == "")
		testResult(1);
	else
		testResult(0);
	
	
}

function runUserTest() {
	drawText("============ Users ============");
	//Test user stuff
	startTest("Get user profile");
	$pro = getUserProfile("jaxbot");

	if ($pro->username == "jaxbot" and mysql_error() == "")
		testResult(1);
	else
		testResult(0);
		
	startTest("Set user profile");
	$time = time();
	$pro->gender = $time;
	setUserProfile("jaxbot", $pro);
	if (mysql_error() == "")
		testResult(1);
	else
		testResult(0);
		
	startTest("Verify database write");
	$pro = getUserProfile("jaxbot");
	if ($pro->gender == $time)
		testResult(1);
	else
		testResult(0);
	
	startTest("Befriend user");
	addFriends("jaxbot", "test");
	if (isFriends("jaxbot", "test"))
		testResult(1);
	else
		testResult(0);
	
	startTest("Unfriend user");
	removeFriends("jaxbot", "test");
	if (!isFriends("jaxbot", "test"))
		testResult(1);
	else
		testResult(0);
		
	startTest("Bio Write");
	setUserBio("jaxbot", "Assertion Test $time");
	if (mysql_error() == "")
		testResult(1);
	else
		testResult(0);
	
	startTest("Bio Verify");
	if (getUserBio("jaxbot") == "Assertion Test $time")
		testResult(1);
	else
		testResult(0);
	
	startTest("Follow Test");
	followUser("jaxbot", "test");
	if (isFollowing("jaxbot", "test"))
		testResult(1);
	else
		testResult(0);
	
	startTest("Unfollow Test");
	unfollowUser("jaxbot", "test");
	if (!isFollowing("jaxbot", "test"))
		testResult(1);
	else
		testResult(0);
}
function runHistoryTest() {
	drawText("============ History ============");
	
}