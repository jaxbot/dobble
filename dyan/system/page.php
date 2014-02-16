<?php

function renderPage($title, $body, $header) {
global $base, $CUSTOM_USER_LOOKUP;

if (!isLoggedIn()) {
	header("Location: login.php");
	exit;
}


$DisplayName = getUserDisplayName($_SESSION['P_usr']);
$Avatar = getUserThumbnail($_SESSION['P_usr']);

	?>
	<!DOCTYPE HTML>
   <html>
		<head>
			<base href='<?php echo $base; ?>'>
			<title>Dobble.me <?php echo $title; ?></title>
			<script>
			var staticHost = '<?php echo $GLOBALS['STATIC_IMG_HOST']; ?>';
			var curUser = '<?php echo $_SESSION['P_usr']; ?>';
			var authKey = '<?php echo getUserAuthKey(); ?>';
			var userAvatars = new Array();
			var userNames = new Array();
			<?php
				$friends = getUserProfile($_SESSION['P_usr'])->friends;
				sort($friends);
				foreach ($friends as $friend) {
					if ($friend != "") {
						echo "userAvatars['$friend'] = '".getUserThumbnail($friend)."';";
						echo "userNames['$friend'] = '".getUserDisplayName($friend)."';";
					}
				}
				echo "userAvatars['".$_SESSION['P_usr']."'] = '".$Avatar."';";
				echo "userNames['".$_SESSION['P_usr']."'] = '".$DisplayName."';";
				echo "userNames['anon'] = 'Anonymous';";
			?>
			function getUserThumbnail(user) {
				if (userAvatars[user]) {
					return userAvatars[user];
				} else {
					return -1;
				}
			}
			function getUserDisplayName(user) {
				if (userNames[user]) {
					return userNames[user];
				} else {
					return user;
				}
			}
			function addJSElement(src) {
				console.log("adding " + src);
				var e = document.createElement("script");
				e.src = src;
				document.body.appendChild(e);
			}
			function deferFile(src) {
				window.addEventListener("load", function() { addJSElement(src); }, false);
			}
			function deferJS(callback) {
				window.addEventListener("load", callback, false);
			}
			deferFile("common/3rd/swfobject.js");
			</script>
			<?php
			//Load individual files if development mode,
			//load OB file if production
			if ($GLOBALS['TEST_MODE']) {?>
			<link rel='StyleSheet' href='common/00248.css' type='text/css'>
			<link rel='StyleSheet' href='common/board.css' type='text/css'>
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
			<?php
			} else {?>
			<script language='javascript' src='<?php echo $GLOBALS['COMMONHOST']; ?>/###BUILD###.js'></script>
			<?php
			}
			?>
			<script>backgroundWork();</script>
			
		</head>
		<body>

			<div id='contentwrapper'>
			<table id='contenttable'>
				<tr>
					<td><div id='logocontainer'><a href='#' onClick='originalPage();'><img src='img/logo_small.png' style='position:relative;z-index:100' width='149' height='40' alt='Dobble.me'></a>
					<div id='notificationDot' style='position:absolute;z-index:100' onClick='showNotificationsList();'>0</div></div>
					</td>
					
					<td><div id='userInfo'>
							<a href='javascript:fetchPage(PAGE_PROFILE);'><img src='<?php echo $Avatar; ?>' width=30 height=30 style='border:none;border-radius:2px;' alt='You'></a>
						</div>
						<div id='header' style='color:white;'>
						<?php echo $header; ?>
						</div>

					</td>
				</tr>
				
				<tr>
					<td style='vertical-align:top;width:190px;'>
						<div class='sidebar'>
						
						<a href='javascript:originalPage();' onClick='highlightThis(this);' class='sidelink' style='opacity:1' id='page_home'>Dashboard <span class='dotcount' style='float:right;display:none' id='home_unread'></span></a><br>
						<a href='javascript:fetchPage(PAGE_FRIENDS);' onClick='highlightThis(this);' class='sidelink' id='page_friends'>Friends</a><br>
						<a href='javascript:fetchPage(PAGE_PICTURES);' onClick='highlightThis(this);' class='sidelink' id='page_pictures'>Pictures</a><br>
						<a href='javascript:fetchPage(PAGE_PROFILE);' onClick='highlightThis(this);' class='sidelink' id='page_profile'><?php echo $_SESSION['P_usr']; ?></a><br>
						<br><br><br>
						
						
						<div id='friendsList'>
						<?php
						$scriptOutput = "";
						//Prime the cache
						$onlinefriends = getOnlineFriends($_SESSION['P_usr'],false,null);
						
						//Friends previously declared
						foreach ($friends as $friend) {
							if ($friend !== "") {
								$avatar = getUserThumbnail($friend);
								$name = $GLOBALS['CACHE_DISPLAYNAME'][$friend]; //since it's already cached
								$status = in_array($friend, $onlinefriends);
								if ($status === false) $status = 0; else $status = 1;
								$scriptOutput .= "addFriend('$friend',".$status.");";
							}
						}
						$scriptOutput .= "rebuildFriendsList();";
						echo "<script>deferJS(function(){ $scriptOutput });</script>";
					
						?>
						</div>
						</div>
					</td>
					<td id='contentbody'>
						<div id='content'>
							
						</div>
						<div id='origcontent'>
							<?php echo $body; ?>
						</div>
					</td>
				</tr>
			</table>
			</div>
			
			<img alt='me' src='<?php echo $GLOBALS['STATIC_IMG_HOST'] . "avatar/".$_SESSION['P_usr']; ?>' style='display:none'>
		</body>
	</html>
	<?php
	ob_flush();
	
	//The user loaded a page, and is thus active
	updateIsActive();
	
}
?>
