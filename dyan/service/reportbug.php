<?php

require("../system/service.php");
if ($_POST['description'] != "" || $_POST['snapshot'] != "") {
	$id = substr(md5(time()), 5,8) . rand(0,1000000);
	$description = htmlspecialchars(stripslashes($_POST['description']));
	$details = htmlspecialchars(stripslashes($_POST['details']));
	$report = "Bug report @ " . date("m-d-y h:i:s a") . "<br>
From: ".$_SESSION['P_usr']."<br>
<br>
Description:<br>
$description<br>
<br>
Details:<br>
$details<br><br>
Browser: ".$_SERVER['HTTP_USER_AGENT'];

	if ($_POST['snapshot'] != "") {
		$snapshot = stripslashes($_POST['snapshot']);
		$snapshot = str_replace("<?", "", $snapshot); //just in case


		file_put_contents($STORAGE_DIR . "bugs/$id.html", $snapshot);

		$report .= "<br><br>A snapshot is available at <a href='http://dobble.me/bug.php?id=$id&user=".$_SESSION['P_usr']."'>$id</a>";
	}
	$headers = "From: Dobble Service <noreply@dobble.me>\r\nContent-Type: text/html\r\n";
	mail("jwarner@dobble.me", "Bug report " . $id, $report, $headers);
	echo "<script>top.showDialog('Thanks!', 'Your bug report has been received. Thanks!');top.hideBugReport();</script>";
}
?>