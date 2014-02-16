<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();
	$data = file_get_contents("php://input");

	if ($data != "") {

		$file = tempnam(sys_get_temp_dir(), "ajaxupload.".time());
		$f = fopen($file, "w");
		fwrite($f, $data);
		fclose($f);
		$imginfo = getimagesize($file);

		if (!$imginfo) {
			echo "~j0";
			unlink($file); //delete the temp file
			exit;
		}

		switch ($imginfo['mime']) {
		case "image/jpeg":
			$ext = ".jpg";
			break;
		case "image/png":
			$ext = ".png";
			break;
		default:
			echo "~j0";
			unlink($file); //delete the temp file
			exit;
			break;
		}
		$imgid = rand(0,4000).substr(md5(rand(0,429029420)), 0, 13);
		$filename = $_SESSION['P_usr'].$imgid.$ext;
		$filepath = $STORAGE_DIR . "storage/photos/raw/";
		$f = fopen($filepath.$filename, "w");
		fwrite($f, $data);
		fclose($f);

		$albumid = getAlbumIdFromName($_SESSION['P_usr'], "Feed Images");
		if ($albumid == "") {
			createAlbum($_SESSION['P_usr'], "Feed Images");
			$albumid = getAlbumIdFromName($_SESSION['P_usr'], "Feed Images");
		}

		require("system/imagedata.php");

		resizeImg($filepath.$filename, 800, 600, $STORAGE_DIR . "storage/photos/disp/$filename");
		createThumbnail($filepath.$filename, 200, 200, $STORAGE_DIR . "storage/photos/thumb/$filename");
		$photo = addImageToAlbum($_SESSION['P_usr'], $albumid, $filename);
		if ($_GET['webcam'] != "") {
			//Share it
			addPhotoHistoryEvent($_SESSION['P_usr'], "", $photo, $albumid);
		} //Otherwise, it'll be shared as a batch

		echo "~j1";// . $STATIC_IMG_HOST . "storage/photos/thumb/$filename";
		exit;
	}
}
?>