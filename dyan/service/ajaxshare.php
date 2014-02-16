<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();
	if ($_GET['type'] == "1") {
		//Share the last X feed images to the feed
		$num = $_GET['num'];

		$id = getAlbumIdFromName($_SESSION['P_usr'], "Feed Images");

		$photos = getPicturesFromAlbum($id, $_SESSION['P_usr']);
		$photos = array_reverse($photos);
		array_splice($photos, $num);
		$photoids = array();
		$n = 0;
		foreach ($photos as $photo) {
			$photoids[$n] = $photo->id;
			$n++;
		}
		addPhotoHistoryEvent($_SESSION['P_usr'], "", implode(",", $photoids), $id);
		/*
		$msg = "";

		$msg .= "<a href='photos.php?album=".$albumid."&from=".$_SESSION['P_usr']."&photo=$photo'>";
		$msg .= "<img src='".$STATIC_IMG_HOST . "storage/photos/thumb/$filename"."' style='width: 100px; height: 100px; padding: 7px;'>";
		$msg .= "</a>";*/
		//addHistoryEvent($_SESSION['P_usr'], $msg);

		echo "~~";// . $STATIC_IMG_HOST . "storage/photos/thumb/$filename";
	}
	if ($_GET['type'] == "2") {
		$url = $_GET['url'];
		if ($_GET['via'] != "") {
			$via = htmlspecialchars($_GET['via']);
			$msg = "<span class='systemText' style='font-size:10px'>Via: $via </span><br>";
		}
		addLinkPhotoHistoryEvent($_SESSION['P_usr'], htmlspecialchars($url), $msg);
	}
	if ($_GET['type'] == "3") {
		$url = $_GET['url'];
		if (substr($url, 0, 4) == "http") {
		$msg = "";
		//Add some page info
		$domain = substr($url, 7);
		$domain = substr($domain, 0, strpos($domain, "/"));
		$ico = "http://" . $domain . "/favicon.ico";
		$img = file_get_contents($ico);
		if ($img != "") {
			$msg .= "<img src='$ico' width=16 height=16 class='icon'>";
		}
		/*$context = stream_context_create(array('http' => array('header'=>'Connection: close')));
		$data = file_get_contents($url, false, $context);*/

		}

		addLinkHistoryEvent($_SESSION['P_usr'], htmlspecialchars($url), $msg);
	}
} else {
	echo "-1";
}
?>