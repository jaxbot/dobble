<?php
/* Dobble.me Testing Component: Frontend
5-4-2012 
Tests the front facing interface */

require_once("dobble.testing.net.php");

function runFrontendTest() {
	drawText("============ Frontend ============");
	
	startTest("Frontend Load");
	if (httpReq("") != "")
		testResult(1);
	else
		testResult(0);
	
	startTest("Frontend Load Signup");
	if (httpReq("signup") != "")
		testResult(1);
	else
		testResult(0);
	
	startTest("Frontend Load User Page");
	if (stripos(file_get_contents("http://int.dobble/jaxbot"), "jaxbot") != "")
		testResult(1);
	else
		testResult(0);
	
	startTest("Sign In as test:test");
	$data = httpReq("signin",array("un" => "test", "pw" => "test"));
	if ($data == "") //should be a redir
		testResult(1);
	else
		testResult(0);
	
	startTest("Check logged in state");
	$data = httpReq("");
	if (stripos($data, "curUser = 'test'") !== false)
		testResult(1);
	else
		testResult(0);
	
	//parse auth key
	startTest("Parse authKey");
	$authkey = explode("authKey = '", $data);
	$authkey = explode("'", $authkey[1]);
	$authkey = $authkey[0];
	testResult(1);
	
	startTest("Post status");
	$testPost = 'Test post at ' . date("m-d-y h:i:s a");
	$res = httpReq("service/post.php?test=".$authkey, array('composer_status' => $testPost));
	if ($res == "<script>top.composerCallback(true);</script>")
		testResult(1);
	else {
		testResult(0);
	}
	
	startTest("Get timeline");
	$res = httpReq("service/timeline.php?test=".$authkey);
	if (strlen($res) > 100) 
		testResult(1);
	else
		testResult(0);
		
	startTest("Parse timeline");
	$timeline = explode("~nitem~", substr($res, 2));
	list($json) = explode("~s~", $timeline[0]);
	$timelineitem = json_decode($json);
	if ($timelineitem->message == $testPost)
		testResult(1);
	else
		testResult(0);
		
	startTest("Post Comment");
	$comment = "Test comment " . time();
	$res = httpReq("service/postcomment.php?user=".$timelineitem->from."&id=".$timelineitem->id."&test=".$authkey, array("commentmessage" => $comment));
	if (substr($res, 0, 4) == "~SC1")
		testResult(1);
	else
		testResult(0);
		
	/*startTest("New Session");
	$ses = newSession("jaxbot");
	if (mysql_error() == "")
		testResult(1);
	else
		testResult(0);
		
	startTest("Frontend Load Logged In");
	$opts = array('http' => array('header'=> 'Cookie: D='.$ses."\r\n"));
	$context = stream_context_create($opts);
	$data = file_get_contents("http://int.dobble/", false, $context);
	if (stripos($data, "jaxbot") !== false)
		testResult(1);
	else
		testResult(0);*/
}

