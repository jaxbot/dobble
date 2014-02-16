<?php
/* Dobble.me Testing Library
For Network Communication
(aka a curl script)
*/

$BASE_URL = "http://int.dobble/";
$COOKIEJAR = "/tmp/cookies.".time().".txt";

function httpReq($page, $data = array()) {
	global $BASE_URL,$COOKIEJAR;
	
	$postdata = "";
	
	foreach ($data as $key => $value)
	{
		$postdata .= $key . "=" . urlencode($value) . "&";
	}
	rtrim($postdata,"&");
	
	//CURL
	$c = curl_init();
	
	curl_setopt($c,CURLOPT_URL,$BASE_URL . $page);
	curl_setopt($c,CURLOPT_POST,count($data));
	curl_setopt($c,CURLOPT_POSTFIELDS,$postdata);
	curl_setopt($c,CURLOPT_CONNECTTIMEOUT,3);
	curl_setopt($c,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($c,CURLOPT_COOKIEFILE,$COOKIEJAR);
	curl_setopt($c,CURLOPT_COOKIEJAR,$COOKIEJAR);
	
	$startTime = microtime(true);
	
	$result = curl_exec($c);
	curl_close($c);
	
	if (microtime(true) - $startTime > 0.4)
		trigger_error("Slow request $page");
		
	return $result;
	
}