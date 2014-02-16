<?php
//tagdata.php
//Handles getting and setting of post tags

/*
0=Happy
1=Sad
2=Interesting
3=Boring
4=Funny
5=Sorry
6=Idek
7=Love
8=Thumbs Up
9=Thumbs Down
*/

//Gets
function getTagList($id, $user) {
	$result = mysql_query("SELECT * FROM `tags` WHERE `postid` = '".mysql_real_escape_string($id)."';");
	$items = array();
	$i = 0;
	while($data = mysql_fetch_array($result)) {
		$items[$i] = new posttag;
		$items[$i]->postid = $data["postid"];
		$items[$i]->from = $data["from"];
		$items[$i]->type = $data["type"];
		
		$i+=1;
	}
	return $items;
}

//Actions
function addTag($id, $type, $from) {
	$result = mysql_query("INSERT INTO `tags` (`postid`, `from`, `type`)
VALUES ('".mysql_real_escape_string($id)."', '".mysql_real_escape_string($from)."', '".mysql_real_escape_string($type)."');");
}
function deleteTagList($user, $id) {
	$result = mysql_query("DELETE FROM `tags`
		WHERE (`postid` = '".mysql_real_escape_string($id)."';");
}
function removeTag($id, $cfrom) {
	$result = mysql_query("DELETE FROM `tags`
		WHERE (`postid` = '".mysql_real_escape_string($id)."') AND (`from` = '".mysql_real_escape_string($cfrom)."');");
}
class postTag {
	var $postid;
	var $from;
	var $type;
}
?>