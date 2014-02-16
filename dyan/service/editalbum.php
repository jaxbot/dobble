<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	$albumid = cleanString($_GET['id']);
	if ($_POST['albumNameTxt'] != "") {
		renamePhotoAlbum($_SESSION['P_usr'], $albumid, htmlspecialchars($_POST['albumNameTxt']));
	}
	$photos = getPicturesFromAlbum($albumid, $_SESSION['P_usr']);
	for($i=0;$i<count($photos);$i++) {
		if ($_POST['photoDesc'.$i] != "") {
			changePhotoDesc($_SESSION['P_usr'], $albumid, $photos[$i]->id, htmlspecialchars($_POST['photoDesc'.$i]));
		}
		if ($_POST['delete_photo_'.$i] != "") {
			deletePicture($_SESSION['P_usr'], $albumid, $photos[$i]->id);
		}
	}
	echo "<script>top.stopAlbumEdit();</script>";

	echo "<script>top.location.href = '../#pictures/?album=$albumid&from=".$_SESSION['P_usr']."';</script>";

}
?>