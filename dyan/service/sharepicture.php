<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	require("system/imagedata.php");

	if (!is_uploaded_file($_FILES['photo']['tmp_name'])) {
			echo "<script>alert('Unknown error (caused by a file too large)');</script>";
			exit;
	}
	$albumid = getAlbumIdFromName($_SESSION['P_usr'], "Feed Images");
	if ($albumid == "") {
		createAlbum($_SESSION['P_usr'], "Feed Images");
		$albumid = getAlbumIdFromName($_SESSION['P_usr'], "Feed Images");
	}

	$photo = $_FILES['photo']['tmp_name'];
		$imginfo = getimagesize($photo);

		if (!$imginfo) {
			echo "<script>top.showDialog('Well, this is awkward.', 'The file uploaded was not an image. Please select a new one.');</script>";
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
			echo "<script>top.showDialog('Well, this is awkward.', 'The uploaded file needs to be a .JPG or .PNG image.');</script>";
			exit;
			break;
		}
		$imgid = rand(0,4000).substr(md5(rand(0,429029420)), 0, 13);
		$filename = $_SESSION['P_usr'].$imgid.$ext;
		$filepath = $STORAGE_DIR . "storage/photos/raw/";
		if (move_uploaded_file($photo, $filepath.$filename)) {

			resizeImg($filepath.$filename, 800, 600, $STORAGE_DIR . "storage/photos/disp/$filename");
			createThumbnail($filepath.$filename, 200, 200, $STORAGE_DIR . "storage/photos/thumb/$filename");

			$desc = htmlspecialchars($_POST['pictureDesc']);
			if ($desc == "Say something about it...") {
				$desc = "";
			}

			$photo = addImageToAlbum($_SESSION['P_usr'], $albumid, $filename, $desc);

		$msg = $desc;

		addPhotoHistoryEvent($_SESSION['P_usr'], $msg, $photo, $albumid);
		} else {
			echo "<script>top.showDialog('Well, this is awkward.','An unknown error occured. Try that again, and if the problem persists, our team is probably working on it. Hopefully.');</script>";
			exit;
		}



	echo "<script>top.composerSharePictureCallback();</script>";



}
?>