<?php

	//Returns an array of album classes
	function getPhotoAlbums($user) {
		$arr = array();
		$i = 0;
		
		$result = mysql_query("SELECT * FROM `albums` WHERE `from` = '".mysql_real_escape_string($user)."';");
		while($data = mysql_fetch_array($result)) {
			$arr[$i] = new album;
			$arr[$i]->time = $data["time"];
			$arr[$i]->name = $data["name"];
			$arr[$i]->id = $data["id"];
			$arr[$i]->user = $user;
			$i++;
		}
		return $arr;
	}
	
	//Returns an album class
	function getPhotoAlbum($user, $albumid) {
		$albums = getPhotoAlbums($user);
		foreach ($albums as $album) {
			if ($album->id == $albumid) {
				return $album;
			}
		}
	}
	
	//Returns an array of picture objects
	function getPicturesFromAlbum($album, $user) {
		global $STATIC_IMG_HOST;
		
		$arr = array();
		$i = 0;
		
		$result = mysql_query("SELECT * FROM `photos` WHERE (`from` = '".mysql_real_escape_string($user)."') AND (`albumid` = '".mysql_real_escape_string($album)."') ORDER BY `time`;");
		while($data = mysql_fetch_array($result)) {
			$arr[$i] = new picture;
			$arr[$i]->user = $data["from"];
			$arr[$i]->time = $data["time"];
			$arr[$i]->id = $data["id"];
			$arr[$i]->description = $data["caption"];
			$arr[$i]->url = $STATIC_IMG_HOST . "storage/photos/disp/".$data["filepath"];
			$arr[$i]->album = $album;
			$i+=1;
		}
		return $arr;
	}
	
	//Returns a picture class based on id and user
	function getPicture($id, $user) {
		global $STATIC_IMG_HOST;
		
		$result = mysql_query("SELECT * FROM `photos` WHERE (`from` = '".mysql_real_escape_string($user)."') AND (`id` = '".mysql_real_escape_string($id)."');");
		$data = mysql_fetch_array($result);
		$arr = new picture;
		$arr->user = $data["from"];
		$arr->time = $data["time"];
		$arr->id = $data["id"];
		$arr->description = $data["caption"];
		$arr->url = $STATIC_IMG_HOST . "storage/photos/disp/".$data["filepath"];
		
		$album = explode("_", $arr->id);
		$album = $album[1];
		
		$arr->album = $album;
		
		return $arr;
	}
	
	//Returns id from name
	function getAlbumIdFromName($user, $aname) {
		$result = mysql_query("SELECT * FROM `albums` WHERE (`name` = '".mysql_real_escape_string($aname)."') AND (`from` = '".mysql_real_escape_string($user)."');");
		$data = mysql_fetch_array($result);
		return $data["id"];
	}
	
	//Returns a URL for a randomized image from an album
	function getAlbumCover($user, $album) {
		global $STATIC_IMG_HOST;

		$result = mysql_query("SELECT * FROM `photos` WHERE (`albumid` = '".mysql_real_escape_string($album)."') AND (`from` = '".mysql_real_escape_string($user)."');");
		$data = mysql_fetch_array($result);
		return $STATIC_IMG_HOST . "storage/photos/thumb/" . $data["filepath"];
	}
	
	//Replaces the url disp path with the storage thumb path
	function getPhotoThumbnail($_url) {
		return str_replace("disp", "thumb", $_url);
	}
	
	//Returns raw picture data from a user
	function getPicturesFromUser($user, $max = 10) {
		global $STATIC_IMG_HOST;
		
		
		$items = array();
		$i = 0;
		$result = mysql_query("SELECT * FROM `photos` WHERE `from` = '".mysql_real_escape_string($user)."' ORDER BY `time` DESC LIMIT " . intval($max) . ";");

		while($data = mysql_fetch_array($result)) {
			$items[$i] = new picture;
			$items[$i]->user = $data["from"];
			$items[$i]->time = $data["time"];
			$items[$i]->id = $data["id"];
			$items[$i]->description = $data["caption"];
			$items[$i]->url = $STATIC_IMG_HOST . "storage/photos/disp/".$data["filepath"];
			$items[$i]->album = $data["albumid"];
			$i+=1;
		}
		return $items;
	}
	
	//Returns a sorted array of the photo stream
	function getPhotoStream($user) {
		global $STATIC_IMG_HOST;
		/*
		$arr = getPicturesFromUser($user);
		$users = getFriends($user);
		foreach ($users as $_user) {
			$arr = array_merge($arr, getPicturesFromUser($_user));
		}*/
		
		$users = getFriends($user);
		
		$max = 20;
		
		//Using the more efficient version (derived from historydata.php)
		$query = "SELECT * FROM `photos` WHERE `from` IN (";
		foreach ($users as $friend) {
			$query .= "'".mysql_real_escape_string($friend)."',";
		}
		$query .= "'".mysql_real_escape_string($user)."'";
		//$query .= "'$user','".join($users, "','")."'";
		$query .= ") ";
		$query .= "ORDER BY `time` DESC LIMIT " . intval($max) . ";";
		$result = mysql_query($query);
		
		$items = array();
		$i = 0;
		
		while($data = mysql_fetch_array($result)) {
			$items[$i] = new picture;
			$items[$i]->user = $data["from"];
			$items[$i]->time = $data["time"];
			$items[$i]->id = $data["id"];
			$items[$i]->description = $data["caption"];
			$items[$i]->url = $STATIC_IMG_HOST . "storage/photos/disp/".$data["filepath"];
			$items[$i]->album = $data["albumid"];
			$i+=1;
		}
		
		//$arr = sortPhotos($arr);
		//$arr = array_slice($arr, 0, 20);
		return $items;
	}
	
	function sortPhotos($arr) {
		return sortEvents($arr); //Same dealio
	}
	
	function createAlbum($user, $name) {
		$result = mysql_query("INSERT INTO `albums` (`from`, `name`, `id`, `time`)
VALUES ('".mysql_real_escape_string($user)."', 
'".mysql_real_escape_string($name)."', 
'', 
'".time()."');");
		
	}
	
	function deleteAlbum($user, $albumid) {
		$result = mysql_query("DELETE FROM `albums`
		WHERE (`id` = '".mysql_real_escape_string($albumid)."') AND (`from` = '".mysql_real_escape_string($user)."');");
	}
	
	function renamePhotoAlbum($user, $albumid, $renameto) {
		$query = "UPDATE `albums` SET
`name` = '".mysql_real_escape_string($renameto)."'
WHERE (`from` = '".mysql_real_escape_string($user)."') AND (`id` = '".mysql_real_escape_string($albumid)."');";
		$result = mysql_query($query);
	}
	
	//Updates the "last updated" time on an album
	function updateAlbum($user, $albumid) {
		$query = "UPDATE `albums` SET
`time` = '".time()."'
WHERE (`from` = '".mysql_real_escape_string($user)."') AND (`id` = '".mysql_real_escape_string($albumid)."');";
		$result = mysql_query($query);
	}
	
	function addImageToAlbum($user, $album, $filename, $caption = "") {
		$id = md5(rand(0,99999999)).rand(0,9994230558) . "_" . $album;
		$result = mysql_query("INSERT INTO `photos` (`from`, `id`, `caption`, `filepath`, `albumid`, `time`)
VALUES ('".mysql_real_escape_string($user)."', 
'".mysql_real_escape_string($id)."', 
'".mysql_real_escape_string($caption)."', 
'".mysql_real_escape_string($filename)."',
'".mysql_real_escape_string($album)."',
'".time()."');");
		return $id;
	}
		
	function changePhotoDesc($user, $album, $id, $newdesc) {
		$query = "UPDATE `photos` SET
`caption` = '".mysql_real_escape_string($newdesc)."'
WHERE (`from` = '".mysql_real_escape_string($user)."') AND (`id` = '".mysql_real_escape_string($id)."');";
		$result = mysql_query($query);
	}
	
	
		
	function deletePicture($user, $album, $id) {
		$result = mysql_query("DELETE FROM `photos`
		WHERE (`id` = '".mysql_real_escape_string($id)."') AND (`from` = '".mysql_real_escape_string($user)."');");
		//Remove image from server by overwriting
		$f = fopen($STORAGE_DIR . "storage/photos/disp/".$ext, "w");
		fwrite($f, "Sorry, this image has been deleted. Time: " . date("m-d-y h:i:s a"));
		fclose($f);
		$f = fopen($STORAGE_DIR . "storage/photos/thumb/".$ext, "w");
		fwrite($f, "Sorry, this image has been deleted. Time: " . date("m-d-y h:i:s a"));
		fclose($f);
		$f = fopen($STORAGE_DIR . "storage/photos/raw/".$ext, "w");
		fwrite($f, "Sorry, this image has been deleted. Time: " . date("m-d-y h:i:s a"));
		fclose($f);
		
		//Delete photo history item
		deletePhotoHistoryItem($id, $user);
	}

	class picture {
		var $user;
		var $time;
		var $id;
		var $name;
		var $description;
		var $url;
		var $album;
	}
	class album {
		var $name;
		var $user;
		var $time;
		var $id;
	}

?>