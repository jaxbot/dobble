<?php
//Dobble.me build script
//"Build" is used loosely here; essentially, we're just taking the original source files
//and obfuscating/compressing them

require("dobble.build.inc.php");
require("dobble.build.images.php");
require("dobble.build.js.php");

echo "Dobble.me Build Script\n";
echo "=================\n";

if ($argv[1] == "deploy") {
	$COMMONHOST = "http://a.dobble.me/";
} else {
	$COMMONHOST = "http://test.static.dobble/";
}
$dir = "dyan/";
$staticdir = "build/out/static/";
$dyandir = "build/out/dyan/";

$curVersion = updateVersion();
echo "Version $curVersion\n\n";

$PHP_SOURCE_FILES = getFiles("build/out/dyan/", ".php");

if ($argv[1] == "all") {
	echo "Building image files...\n";

	$files = glob("static/img/*", GLOB_NOSORT); 
	foreach ($files as $file) {
		$name = justName($file);
		$to = $staticdir."img/" . $name;
		if (fileChanged($file, $to)) {
			if (stripos($file, "png") !== false) {
				
				echo "Compressing $file -> $name \n";
				compressPNGImage($file, $to);
				
			} else {
				echo "Some other image (gif?): $file\n";
				copy($file, $to);
			}
		} else {
			echo "File unchanged: $name\n";
		}
	}

	echo "Cleaning up old images...\n";

	cleanupDir($staticdir."img/", $files);
}

echo "Doing some Dobblish...\n";
require_once("dobblish.php");
compileDobblish("static/dobblish/home.html", "static/dobblish.out.home.js", "dobblishHome");

echo "Building the JS/CSS...\n";
cleanupDir($staticdir, array("img", "3rd"));
//$files = glob("static/*.js", GLOB_NOSORT);
$files = array("data", "sideframe", "user", "notification", "chat", "post", "ui", "photos", "profile", "friends", "bug", "timeline", "board", "html", "dobblish.out.home");
$data = buildJSFiles($files);
$data = str_replace("img/", "$COMMONHOST/img/", $data);
$data = str_replace("int.dobble", "dobble.me", $data);

$css = buildCSSFiles(array("static/board.css","static/00248.css"));
$css = str_replace("\"", "\\\"", $css);
$data = "/* Dobble.me */
var css = \"<style>$css</style>\";
css = css.replace(/img\//g, \"$COMMONHOST/img/\");\n
document.write(css);\n".$data;

file_put_contents("build/out/static/".dechex($curVersion).".js", $data);

echo "Building homepage css...\n";

$data = buildCSSFiles(array("static/home.css"));
file_put_contents("build/out/static/home.css", $data);

echo "Building homepage js...\n";
$data = buildJSFiles(array("home"));
file_put_contents("build/out/static/home.js", $data);

echo "Updating ###BUILD### flags...\n";
$files = array("interface/home.php","interface/user.php");
foreach ($files as $file) {
	$data = file_get_contents("build/out/dyan/". $file);
	$data = str_replace("###BUILD###", dechex($curVersion), $data);
	file_put_contents("build/out/dyan/".$file, $data);
}

echo "Updating img/ shortcuts...\n";
foreach ($PHP_SOURCE_FILES as $file) {
	$data = file_get_contents($file);
	$data = str_replace("img/", $COMMONHOST."/img/", $data);
	file_put_contents($file, $data);
}
