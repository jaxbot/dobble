<?php
require("../system/service.php");
if (!isLoggedIn()) {
		echo "~L";
		exit;
	}
$username = $_SESSION['P_usr'];
$albums = getPhotoAlbums($_SESSION['P_usr']);
foreach ($albums as $album) {
	$albumsstring .= $album->name.",";
}
$default = true;

$content = "";

$script = "var currentAlbums = '".jsClean($albumsstring)."';";
$script .= "imgIds = new Array();
imgDescs = new Array();
imgUrls = new Array();
imgUsers = new Array();
imgThumbs = new Array();";
		
		$content .= "<span style='float:right;'>
		<input type='button' class='button' value='Upload Pictures' onClick='fetchPage(PAGE_PICTURES,\"?uploader\");'> 
		<input type='button' class='button' value='My Pictures' onClick='fetchPage(PAGE_PICTURES,\"?my\");'> 
		<input type='button' class='button' value=\"Friends' Pictures\" onClick='fetchPage(PAGE_PICTURES, \"?friends\");'></span>";
if (isset($_GET['uploader'])) {
	$content .= "<h3>Upload Pictures</h3>
	
	<br><br>
	<div id='upload_error' style='display:none;position:relative;margin:auto;background-color:#ffbebe;border:solid 1px #ea5959;padding:4px;width:500px;margin-bottom:4px;'>
	<b>Well, this is awkward.</b><br>
	<span id='upload_errortext'>Error</span>
	</div>
	<form action='service/uploadphotos.php' enctype='multipart/form-data' method='POST' target='datapusher'>
	<div style='display:block;position:relative;margin:auto;width:500px;background-color:#fafafa;border:solid 1px #989898;padding:4px;'>
	<div id='uploadform'>Select your pictures for upload<br><br>
	<input type='file' id='uploadform_file' name='photos[]' multiple>
	<br>
	
	<br>
	<input type='radio' id='uploadform_newalbumcheck' name='albumcheck' value='new' checked onClick='if (this.checked) { document.getElementById(\"albumname\").style.display=\"block\";document.getElementById(\"albumselect\").style.display=\"none\"; } else { document.getElementById(\"albumname\").style.display=\"none\";document.getElementById(\"albumselect\").style.display=\"block\"; }'> Create a new album for these pictures:<br>
	<input type='text' name='albumname' value='".date("l, F jS (Y)")."' id='albumname' style='display: block;margin-top: 20px;margin-left: 20px;width:200px'>
	<br>";
	if (count($albums) > 0) {
		$content .= "<input type='radio' id='uploadform_albumcheck' name='albumcheck' value='ext' onClick='if (this.checked) { document.getElementById(\"albumname\").style.display=\"none\";document.getElementById(\"albumselect\").style.display=\"block\"; } else { document.getElementById(\"albumname\").style.display=\"block\";document.getElementById(\"albumselect\").style.display=\"none\"; }'> Add them to the following album:<br>
		<select name='albumselect' id='albumselect' style='display:none;margin-top: 20px;margin-left: 20px;'>";
		foreach ($albums as $album) {
			$content .= "<option value='".$album->name."'>".$album->name."</option>";
		}
		$content .= "</select>";
	} else {
		$content .= "You have no existing albums to add pictures to, so create a new one above!<br>";
	}
	$content .= "<br><input type='button' class='button' value='Start Upload' onClick='ajaxUploadAlbumPhotos();' id='uploadform_btn'>
	</div>
			<div id='uploadform_progress' style='display:none'>
				<div id='uploadbar_parent' style='width: 500px;height:22px;display:inline-block;background-color:#989898;'>
					<div id='uploadbar_progress' style='width: 0px;height:22px;display:inline-block;background-color:#56456b;background-image:url(img/accent.png);background-repeat:repeat-x'>
					</div>
				</div>
			</div>
		</form>";
	$default = false;
}
if (isset($_GET['photo']) and !isset($_GET['album']))
	$_GET['album'] = substr($_GET['photo'], strpos($_GET['photo'], "_") + 1);
	
if (isset($_GET['album'])) {
	$album = getPhotoAlbum($_GET['from'], $_GET['album']);
	if ($album->id == "") {
		$content .= "<h3>Uh oh</h3>Sorry, the album specified does not appear to be available at this time.";
	} else {
		$script .= "isAlbumView = true;";
		if ($_GET['from'] == $_SESSION['P_usr']) {
			//Owner
			$isOwner = true;
			
			$content .= "<form action='service/editalbum.php?id=".$album->id."' method='POST' target='datapusher'>";
			if (isset($_GET['edit'])) {
				$script .= "setTimeout('editAlbum();', 10);";
			}
		}
		$content .= "<h3><span id='albumTitle'>".$album->name."</span></h3><span style='padding-left:4px;'>By ".getUserDisplayName($_GET['from']);
		if ($isOwner) {
			$content .= "<span id='editBtn'> - <a href='javascript:;' onClick='editAlbum();'>Edit album</a></span>";
		}
		$content .= "</span><br>";
		
		$photos = getPicturesFromAlbum($album->id, $_GET['from']);
		
		if ($isOwner) {
			
			$i = 0;
			$content .= "<span id='editAlbumLayout' style='display:none'><input type='submit' class='button' value='Save'> <input type='button' class='button' value='Cancel edit' onClick='stopAlbumEdit();'>
				<input type='button' class='button' value='Delete album' onClick='deleteAlbum(\"".$album->id."\");'><table>";
			foreach ($photos as $photo) {
				$content .= "<tr><td><img src='".getPhotoThumbnail($photo->url)."' style='max-width: 200px'></td><td width=400><br><span id='photo_desc_$i'>".$photo->description."</span>";
				if ($isOwner) {
					//$content .= "<td><span id='delete_photo_$i' style='display:none'><input type='button' class='button' value='Delete' onClick='deleteImage(\"".$photo->id."\",\"".$album->id."\");'></span></tr>";
					$content .= "<br><input type='checkbox' name='delete_photo_$i' value='1'> Delete this picture";
				}
				$content .= "</td>";
				
				$i++;
			}
			$content .= "</table></span>";
			
			$content .= "<input type='submit' class='button' value='Save' style='display:none' id='savebtn2'></form>";
			
		}
		$content .= "<span id='mainAlbumLayout'>";
		if (count($photos) < 1) {
			$content .= "<br>This album is empty; there are no photos available for display.";
		}
		$content .= "</span>";

		$content .= "<div id='albumContainer'></div>";
		$script .= "countPictures = ".count($photos).";";
		//Add to Javascript array for slideshows, image viewing, etc
		foreach ($photos as $photo) {
			$script .= "imgIds.push('".$photo->id."');";
			$script .= 'imgDescs.push("'.jsClean($photo->description).'");'."\n";
			$script .= "imgUrls.push('".$photo->url."');";
			$script .= "imgUsers.push('".$photo->user."');";
			$script .= "imgThumbs.push('".getPhotoThumbnail($photo->url)."');";
		}
		
		if ($_GET['photo'] != "") {
			$script .= "photoFromId('".htmlspecialchars($_GET['photo'])."');";
		} else {
			$script .= "if (countPictures > 0) { viewAlbumImage(0); }";
		}

	}
	$default = false;
}
if (isset($_GET['my'])) {
	$viewUser = $username;
	$content .= "<h3>My Albums</h3>";
}
if ($_GET['friend'] != "") {
	//$viewUser = htmlspecialchars($_GET['friend']);
	$friends = getUserProfile($_SESSION['P_usr'])->friends;
	foreach ($friends as $friend) {
		if ($friend == $_GET['friend']) {
			$viewUser = $friend;
			$content .= "<h3>".getUserDisplayName($friend)."'s Albums</h3>";
			$albums = getPhotoAlbums($friend);
		}
	}
}
if ($viewUser != "") {
	$content .= "<br><table><tr>";
	if (count($albums) > 0) {
		$x = 0;
		foreach ($albums as $album) {
			$link = "javascript:fetchPage(PAGE_PICTURES, \"?album=".$album->id."&from=".$viewUser."\");";
			$x++;
			if ($x > 4) {
				$x = 0;
				$content .= "</tr><tr>";
			}
			$content .= "<td style='width:230px;vertical-align:top'><a href='$link'><img src='".getAlbumCover($viewUser, $album->id)."' class='thumb' style='max-width:160px;max-height:160px;'><br>
".$album->name . "</a><br><span style='font-size: 10px;'>Updated: ".getRelativeTime($album->time)." ago</span></td>";
			
		}
		$content .= "</tr>";
	} else {
		if ($viewUser == $username) {
			$content .= "You have no albums! Click 'Upload Pictures' above to create one and start sharing!";
		} else {
			$content .= getUserDisplayName($viewUser) . " has no albums to display.";
		}
	}
	$content .= "</table>";
	$default = false;
}
if (isset($_GET['friends'])) {
	$content .= "<h3>Friends' Albums</h3><br>";
	$friends = getUserProfile($_SESSION['P_usr'])->friends;
	foreach ($friends as $friend) {
		if ($friend != "") {
			$albums = getPhotoAlbums($friend);
			if  (count($albums) > 0) {
				$content .= "<img src='".getUserThumbnail($friend)."' class='avatar'><div style='display:block'><a href='javascript:viewUserProfile(\"$friend\");'><b>" . getUserDisplayName($friend) . "</b></a><br>
				<a href='javascript:;' onClick='fetchPage(PAGE_PICTURES, \"?friend=$friend\");'>" . count(getPhotoAlbums($friend)) . " albums</a></div><br>"; //OPTIMIZE
				$content .= "<table><tr>";
				$n = 0;
				foreach ($albums as $album) {
					if ($n > 4) {
						$n = 0;
						$content .= "</tr><tr>";
					}
					$link = "javascript:fetchPage(PAGE_PICTURES, \"?album=".$album->id."&from=".$friend."\");";
					$content .= "<td style='width:230px;vertical-align:top'><a href='$link'><img src='".getAlbumCover($friend, $album->id)."' class='thumb' style='max-width:160px;max-height:160px;'><br>
".$album->name . "</a><br><span style='font-size: 10px;'>Updated: ".getRelativeTime($album->time)." ago</span></td>";
					$n++;
				}
				$content .= "</tr></table><br><br>";
			}
		}
	}
	$default = false;
}/*
if ($_GET['friend'] != "") {
	$friend = htmlspecialchars($_GET['friend']);
	if (isFriends($friend, $_SESSION['P_usr'])) {
		$content .= "<b>".getUserDisplayName($friend)."'s Albums</b><br><br><table>";
		$albums = getPhotoAlbums($friend);
		if (count($albums) > 0) {
			foreach ($albums as $album) {
				$content .= "<tr><td width=230><a href='photos.php?album=".$album->id."&from=".$friend."'><img src='".getAlbumCover($friend, $album->id)."' class='thumb'></a></td><td valign='top'><a href='photos.php?album=".$album->id."&from=".$friend."'>".$album->name . "</a><br>Last updated: ".getRelativeTime($album->time)." ago</td></tr>";
			}
		} else {
			$content .= "This user has no albums to display.";
		}
	} else {
		$content .= "You do not have permission to view this page.";
	}
	$content .= "</table>";
	$default = false;
}*/

if ($default) {
	
	$content .= "<h3>Photo Stream</h3><br>
		<table><tr valign=top>
		";
	$photos = getPhotoStream($_SESSION['P_usr']);
	$i = 0;
	$z = 0;
	foreach ($photos as $photo) {
		$inter = getCommentStream("photo".$photo->id);
		$inter = count($inter);
		
		$content .= "<td><div id='photobox_$z' class='photobox' onClick='navigateToPhoto(\"".$photo->id."\",\"".$photo->user."\");' style='background-image:url(\"".getPhotoThumbnail($photo->url)."\");'>";
		if ($inter > 0) {
			$content .= "<span class='photocommentcount'>$inter</span>";
		}
		$content .= "</div></td>";
		$i++;
		if ($i > 3) {
			$i = 0;
			$content .= "</tr><tr valign=top>";
		}
		$z++;
	}
	$content .= "</tr></table>";
	$script .= "countPictures = ".$z.";";
		//Add to Javascript array for slideshows, image viewing, etc
		foreach ($photos as $photo) {
			$script .= 'imgIds.push("'.$photo->id.'");'."\n";
			$script .= 'imgDescs.push("'.jsClean($photo->description).'");'."\n";
			$script .= 'imgUrls.push("'.$photo->url.'");'."\n";
			$script .= 'imgUsers.push("'.$photo->user.'");'."\n";
			$foundPhoto = true;
		}
	
	$script .= "
	for(i=0;i<$z;i++) {
		document.getElementById('photobox_'+i).style.opacity=0;
		setTimeout('fadeInElement(_g(\"photobox_'+i+'\"));', i * 60);
	}
	";
	if (!$foundPhoto) {
		$content .= "It's pretty bland here, upload some pictures to share!";
	}
}

//Render prefix
echo "~$";

echo $content;

//Script splitter

echo "<script>".$script;
?>