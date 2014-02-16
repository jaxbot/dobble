<?php
$dir = opendir("cache");
while ($file = readdir($dir)) {
	$f = file_get_contents("cache/".$file);
	if ($f != "") {
		$f = str_replace("/var/", "S:/Source/", $f);
		file_put_contents("cache/$file", $f);
	}
}
closedir($dir);
?>