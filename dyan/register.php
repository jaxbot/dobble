<?php
require_once("system/page.php");

if (isLoggedIn()) {
	header("Location: ./");
	exit;
}
require("pages/register_form.php");

?>
