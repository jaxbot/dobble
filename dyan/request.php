<?php
//Todo: deprecate

require_once("system/page.php");

if (isLoggedIn()) {
	header("Location: ./");
	exit;
}
require("pages/requestbody.php");

?>
