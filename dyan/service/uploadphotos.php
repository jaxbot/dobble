<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();

	require("system/imagedata.php");
	if ($_POST['isNew'] == "true") {
		$albumname = htmlspecialchars($_POST['albumname']);
		if ($albumname == "") {
			$albumname = date("m-d-y h:i:s a");
		}
		$albumid = getAlbumIdFromName($_SESSION['P_usr'], $albumname);

		//only create if the album doesnt already exist!
		if ($albumid == "") {
			createAlbum($_SESSION['P_usr'], $albumname);
		}

	} else {
		$albumname = $_POST['albumselect'];
	}
	$albumid = getAlbumIdFromName($_SESSION['P_usr'], $albumname);
	if ($albumid == "") {
		echo "Apparently too many files were uploaded. Try reducing that number. Sorry.";
		exit;
	}

	$numFiles = $_POST['numFiles']; //the reported number of files

	$photoIds = array();
	$photosAdded = 0;
	//foreach ($_FILES['photos']['tmp_name'] as $photo) {
	for ($i=0;$i<$numFiles;$i++) {
		$photo = $_FILES['file'.$i]['tmp_name'];
		if (!is_uploaded_file($photo)) {
			echo "Apparently a file was too big.. or something like that. Try again.";
			exit;
		}

		$imginfo = getimagesize($photo);

		if (!$imginfo) {
			echo "The file uploaded was not an image. Please select a new one.";
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
			echo "The uploaded file needs to be a .JPG or .PNG image.";
			exit;
			break;
		}
		$imgid = rand(0,4000).substr(md5(rand(0,429029420)), 0, 13);
		$filename = $_SESSION['P_usr'].$imgid.$ext;
		$filepath = $STORAGE_DIR . "storage/photos/raw/";
		if (move_uploaded_file($photo, $filepath.$filename)) {

			resizeImg($filepath.$filename, 800, 600, $STORAGE_DIR . "storage/photos/disp/$filename");
			createThumbnail($filepath.$filename, 200, 200, $STORAGE_DIR . "storage/photos/thumb/$filename");
			$photoIds[$photosAdded] = addImageToAlbum($_SESSION['P_usr'], $albumid, $filename);
			 //$STATIC_IMG_HOST . "storage/photos/thumb/$filename";
			$photosAdded += 1;

		} else {
			echo "An unknown error occured. Try that again, and if the problem persists, our team is probably working on it. Hopefully.";
			exit;
		}

	}
	if ($photosAdded != 1) {
		$msg = "Added $photosAdded photos to ";
	} else {
		$msg = "Added a photo to ";
	}

	$msg .= $albumname . "<br>";
	/*
	for ($i=0;$i<5;$i++) {
		if ($photoUrls[$i] != "") {
			$msg .= " <div style='display:inline-block; background-image: url(\"".$photoUrls[$i]."\"); width: 75px; height: 75px; padding: 3px;'>&nbsp;</div>";
		}
	}
	$msg .= "</a>";*/

	addPhotoHistoryEvent($_SESSION['P_usr'], $msg, implode(",", $photoIds), $albumid);
	echo "~$albumid,".$_SESSION['P_usr'];



}
?>