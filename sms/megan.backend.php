<?php
//Megan backend link

/*Globals*/
require("megan.globals.php");

$STORAGE_DIR = "/disk1/www/";

$MY_ROOT = "../dyan/";
require($MY_ROOT."system/databasedata.php");
require($MY_ROOT."system/userdata.php");
require($MY_ROOT."system/historydata.php");
require($MY_ROOT."system/functiondata.php");
require($MY_ROOT."system/emaildata.php");
require($MY_ROOT."system/notificationdata.php");
require($MY_ROOT."system/photodata.php");
require($MY_ROOT."system/chatdata.php");

function handleImageUpload($user, $file, $caption) {
	require_once("../dyan/system/imagedata.php");
	$imginfo = getimagesize($file);
		
	if (!$imginfo) {
		return false;
	}
		
	switch ($imginfo['mime']) {
		case "image/jpeg":
			$ext = ".jpg";
			break;
		case "image/png":
			$ext = ".png";
			break;
		default:
			return false;
			break;
	}
	
	$imgid = rand(0,4000).substr(md5(rand(0,429029420)), 0, 13);
	
	$filename = $user.$imgid.$ext;
	//$filepath = $GLOBALS['STORAGE_DIR'] . "storage/photos/raw/";

	
	resizeImg($file, 800, 600, $GLOBALS['STORAGE_DIR'] . "storage/photos/disp/$filename");
	createThumbnail($file, 200, 200, $GLOBALS['STORAGE_DIR'] . "storage/photos/thumb/$filename");
	
	$albumid = getAlbumIdFromName($user, "Feed Images");
	if ($albumid == "") {
		createAlbum($user, "Feed Images");
		$albumid = getAlbumIdFromName($user, "Feed Images");
	}
	
	$photo = addImageToAlbum($user,$albumid,$filename,$caption);
	
	return $photo;
}
?>