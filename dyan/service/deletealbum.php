<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();


	$albumid = cleanString($_GET['album']);
	$photos = getPicturesFromAlbum($albumid, $_SESSION['P_usr']);
	foreach ($photos as $photo) {
		deletePicture($_SESSION['P_usr'], $albumid, $photo->id);
	}
	deleteAlbum($_SESSION['P_usr'], $albumid);

	echo "~d<title>Deleted</title><message>The album has been successfully deleted.</message>";
}
?>