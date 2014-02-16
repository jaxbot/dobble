<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();

	if ($_GET['setImg'] != "") { //set the desired image
		$imgid = htmlspecialchars($_GET['setImg']);

		$ava = "storage/avatars/".$_SESSION['P_usr'].$imgid;

		unlink($STORAGE_DIR."storage/avatars/".$_SESSION['P_usr'].".jpg"); //delete old avatar
		//echo $STORAGE_DIR.$ava;

		rename($STORAGE_DIR.$ava, $STORAGE_DIR."storage/avatars/".$_SESSION['P_usr'].".jpg");
		$ava = "storage/avatars/".$_SESSION['P_usr'].".jpg";
		$imginfo = getimagesize($STORAGE_DIR.$ava);

		if (!$imginfo) {
			exit;
		}
		$imgid = explode(".", $imgid);
		$imgid = $imgid[0];
		$avamini = "storage/avatars/".$_SESSION['P_usr'] . "_thumb.jpg";

		require("system/imagedata.php");
		createThumbnail($STORAGE_DIR.$ava, 50, 50, $STORAGE_DIR . $avamini);
		setUserImage($_SESSION['P_usr'], $ava, $avamini);
		echo "~1";
		exit;
	}

	if (!is_uploaded_file($_FILES['picture']['tmp_name'])) {
		exit;
	}

	$imginfo = getimagesize($_FILES['picture']['tmp_name']);

	if (!$imginfo) {
		echo "<script>top.showDialog('Awkward...', \"I liked that, I really did but, that wasn't actually an image file. Try selecting something else.\");top.uploadFailed();</script>";
		exit;
	}

	echo $imginfo['mime'];
	switch ($imginfo['mime']) {
		case "image/jpeg":
			echo "OK";
			$ext = "jpg";
		break;
		case "image/png":
			echo "OK";
			$ext = "png";
		break;
		default:
			echo "<script>top.showDialog('By the way', 'Only JPG or PNG images work as pictures, sorry.');top.uploadFailed();</script>";
			exit;
		break;
	}

	$imgid = rand(0,4000).substr(md5(rand(0,429029420)), 0, 13);
	if ($_GET['firsttime'] != "1") {
		$uploadfile = $STORAGE_DIR . "storage/avatars/".$_SESSION['P_usr'].$imgid.".".$ext;
	} else {
		$uploadfile = $STORAGE_DIR . "storage/avatars/".$_SESSION['P_usr'].".jpg";
	}
	if (move_uploaded_file($_FILES['picture']['tmp_name'], $uploadfile.".tmp")) {

		require("system/imagedata.php");

		resizeImg($uploadfile.".tmp", 800, 600, $uploadfile); //this compresses the image, scrubs any bad data
		unlink($uploadfile.".tmp");

		$ava = "storage/avatars/".$_SESSION['P_usr'].$imgid.".".$ext;
		if ($_GET['firsttime'] != "1") {
			echo "<script>top.updatePictureCallback('".$STATIC_IMG_HOST.$ava."', '$imgid.$ext');</script>";
		} else {
		    $avamini = "storage/avatars/".$_SESSION['P_usr'] ."_thumb.jpg";
			$ava = "storage/avatars/".$_SESSION['P_usr'] .".jpg";
		    createThumbnail($STORAGE_DIR.$ava, 50, 50, $STORAGE_DIR . $avamini);
		    setUserImage($_SESSION['P_usr'], $ava, $avamini);
		   echo "<script>top.wizardStep(3);</script>";
		}
	} else {
		echo "An error has occered.";
		echo $_FILES['picture']['error'];
		echo "<script>top.showDialog('Awkward...', 'So um, something went wrong when uploading your picture. I guess just cross your fingers and try again.');top.uploadFailed();</script>";
	}

}
?>
