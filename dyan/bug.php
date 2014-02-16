<?php
//bug.php
//loads the bug report and session

//restricted access
if ($_SERVER['PHP_AUTH_USER'] != "Jaxbot" or md5($_SERVER['PHP_AUTH_PW']) != "") {
	header("WWW-authenticate: basic realm=\"Dobble.me\"");
	header("HTTP/1.0 401 Unauthorized");
	exit;
}

require("system/page.php");

$user = $_GET['user'];
$id = $_GET['id'];

$data = file_get_contents($STORAGE_DIR . "bugs/$id.html");

//restore the session
if (!impersonateSession($user)) newSession($user);

echo $data;
