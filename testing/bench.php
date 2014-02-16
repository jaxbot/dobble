<?php
/* Dobble.me Bench */
require_once("dobble.testing.net.php");

$COOKIEJAR = "/tmp/cookies.bench.txt";

$USERNAME = "Jaxbot";
$PASSWORD = "";

echo "Signing in..";
$data = httpReq("signin",array("un" => $USERNAME, "pw" => $PASSWORD));
$data = httpReq("");
if (stripos($data, "curUser = '$USERNAME") === false)
	die("Failed to log in.");
	
//parse auth key
$authkey = explode("authKey = '", $data);
$authkey = explode("'", $authkey[1]);
$authkey = $authkey[0];

echo "\n\n";

$t = time();
$req = 0;
while (1) {
	$res = httpReq("/?".$USERNAME."=".$authkey);
	$req++;
	if ($t != time()) {
		
		echo "\r$req / s";
		$req = 0;
		$t = time();
	}
}
