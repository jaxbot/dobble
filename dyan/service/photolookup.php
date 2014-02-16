<?php require('../system/service.php');
if (isLoggedIn()) {
	$id = htmlspecialchars($_GET['id']);
	$user = htmlspecialchars($_GET['user']);
	$avatar = getUserThumbnail($user);
	$name = getUserDisplayName($user);
	$photo = getPicture($id, $user);
	$eventtime = getRelativeTime($photo->time);
	$message = "<div style='display:inline-block;height:110px;width:100%'><a href='photos.php?album=".$photo->album."&from=".$user."&photo=".$photo->id."'><img src='".$photo->url."' style='width: 100px;height:100px;float:left;padding:3px;'></a>
	<b>".$photo->name."</b><br />".$photo->description."<br />
	<a href='photos.php?album=".$photo->album."&from=".$user."&photo=".$photo->id."'>(click to view in full)</a></div>";
	echo "~e$user{{}$avatar{{}$name{{}$message{{}$eventtime{{}$id{{}";
	updateIsActive(); //Update the chat activity, since we know the user is active.
	
}
?>