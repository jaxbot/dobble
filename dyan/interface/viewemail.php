<?php
chdir('..'); //The system _must_ run off of the server root
require("system/page.php");

$username = cleanString($_GET['from']);
$name = getUserDisplayName($username);
if ($name != "") {
	$body = file_get_contents("pages/emails/invite.html");
	$body = str_replace("{0}", $name, $body);
	$body = str_replace("{VIEW_IN_BROWSER}", "", $body);
	echo $body;
} else {
	header("Status: 404 Not Found");
	require("error/error_404.php");
}
?>