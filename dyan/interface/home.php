<?php
$DisplayName = getUserDisplayName($_SESSION['P_usr']);

$friends = getUserProfile($_SESSION['P_usr'])->friends;

$scriptOutput = "var a = addFriend;";

//Prime the cache
$onlinefriends = getOnlineFriends($_SESSION['P_usr'],false,null);

foreach ($friends as $friend) {
	if ($friend != "") {
		$status = in_array($friend, $onlinefriends);
		if ($status === false) $status = 0; else $status = 1;
		$scriptOutput .= "a('$friend',$status,'".getUserDisplayName($friend)."');";
	}
}

$scriptOutput .= "initUsers('".getUserDisplayName($_SESSION['P_usr'])."');";

//Prime user display name cache
getMassUserDisplayName($_SESSION['P_usr']);

$historyitems = getHistoryItems($_SESSION['P_usr'], true, 20);

$comments = getMassCommentCount(getCommentIdsArray($historyitems));

$scriptTimeline = "a = addTimelineEvent;";

foreach ($historyitems as $historyitem) {
	$historyitem->message = renderEvent($historyitem);
	$cnt = $comments[$historyitem->id];
	if (!$cnt) $cnt = 0;
	$json = json_encode($historyitem);
	$scriptTimeline .= "a($json, $cnt, ".(time() - $historyitem->time).",1);";
}
//remove newlines that could kill javascript
$scriptTimeline = removeNL($scriptTimeline)."updateUI();";

///$scriptOutput = "";
//$scriptTimeline = "";
/*DOBBLISH:STARTHTML*/
?>
<!DOCTYPE HTML>
<html>
	<head>
		<base href='<?php echo $base; ?>'>
		<title>Dobble.me</title>
<?php
			//Load individual files if development mode,
			//load OB file if production
			if ($GLOBALS['TEST_MODE']) {
				require("../build/dobblish.php");
				compileDobblish("../static/dobblish/home.html", "../static/dobblish.out.home.js", "dobblishHome");
?>
		<link rel='stylesheet' href='common/00248.css' type='text/css'>
		<script src='common/data.js'></script>
		<script src='common/sideframe.js'></script>
		<script src='common/user.js'></script>
		<script src='common/notification.js'></script>
		<script src='common/chat.js'></script>
		<script src='common/post.js'></script>
		<script src='common/ui.js'></script>
		<script src='common/photos.js'></script>
		<script src='common/profile.js'></script>
		<script src='common/friends.js'></script>
		<script src='common/bug.js'></script>
		<script src='common/timeline.js'></script>
		<script src='common/html.js'></script>
		<script src='common/dobblish.out.home.js'></script>
<?php
			} else {?>
		<script language='javascript' src='<?php echo $GLOBALS['COMMONHOST']; ?>/###BUILD###.js'></script>
<?php
			}
?>
	</head>
	<body>
		<!-- Dobble.me is written with Dobblish, an experimental brainchild. -->
		<script>dobble('<?php echo $GLOBALS['STATIC_IMG_HOST']; ?>','<?php echo $_SESSION['P_usr']; ?>','<?php echo getUserAuthKey();?>', function () { <?php echo $scriptOutput; ?>});<?php echo $scriptTimeline; ?></script>
	</body>
</html>
	<?php
	ob_flush();

	//The user loaded a page, and is thus active
	updateIsActive();
