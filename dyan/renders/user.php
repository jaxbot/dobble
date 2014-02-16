<?php
require("../system/service.php");
require_once("system/boarddata.php");

$username = cleanString($_GET['user']);
$profile = getUserProfile($username);
if ($profile->username == "") {
	header("Status: 404 Not Found");
	require("error/error_404.html");
	exit;
}
?>~$
<!DOCTYPE HTML>
   <html>
		<head>
			<base href='<?php echo $base; ?>'>
			<title>Dobble.me - <?php echo $username ?>'s Board</title>
			<script>
				var staticHost = "<?php echo $STATIC_IMG_HOST; ?>";
				var curUser = "<?php echo $_SESSION['P_usr']; ?>";
				var authKey = "<?php echo getUserAuthKey(); ?>";
				var boardUser = "<?php echo $username; ?>";
				var curuserAvatar = "<?php echo getUserThumbnail($username); ?>";
				var userHandwriting = <?php echo getUserHandwriting($_SESSION['P_usr']); ?>;
				var boardUserHandwriting = <?php echo getUserHandwriting($username); ?>;
			</script>
			<link href='http://fonts.googleapis.com/css?family=Crafty+Girls|Give+You+Glory|Reenie+Beanie|Just+Me+Again+Down+Here|Handlee|Cedarville+Cursive|Rock+Salt|Nothing+You+Could+Do' rel='stylesheet' type='text/css'>
			<?php
			//todo: make it only load needed files
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
			<script src='common/board.js'></script>
			<script src='common/timeline.js'></script>
			<script src='common/html.js'></script>
			<?php
			} else {
			//todo: make a custom JS for extern, compile with the other one
			?>
			<script src='<?php echo $COMMONHOST; ?>/###BUILD###.js'></script>
			<?php
			}
			?>
			<style>
				<?php
				echo getUserThemeCss($username);
				?>
			</style>

		</head>
	<body>
<div id='stickynoteEditor'>

			<textarea class='transparentEditor' onKeyDown='handleKeyPresses(this,320);' id='stickynoteTAEditor' style='width:340px;height:320px;'></textarea>
			<canvas id='stickynoteCanvas' width=349 height=312 style='display:none;cursor:pointer'>
			</canvas>
			<span class='href' style='float:right;position:absolute;right:20px;bottom:40px;' onClick='postStickyNote();'>Post it</span>
			<br>
			<a href='javascript:setColor("y");'><img src='img/sn_y.png' width=42 height=42></a>
			<a href='javascript:setColor("p");'><img src='img/sn_p.png' width=42 height=42></a>
			<a href='javascript:setColor("g");'><img src='img/sn_g.png' width=42 height=42></a>
			<a href='javascript:setColor("b");'><img src='img/sn_b.png' width=42 height=42></a>
			<a href='javascript:showCanvas();'><img src='img/btnpicture.png' width=32 height=32 style='vertical-align:top'></a></div>


			<div id='noteEditor'>
				<textarea class='transparentEditor' onKeyUp='handleScroll(this);' onKeyDown='handleKeyPresses(this,290);' id='noteTAEditor' style='width:340px;height:240px;line-height: 30px;'></textarea><br>
				<span style='cursor:pointer;' onClick='toggleCheckbox("isAnon");'><img src='img/check.png' style='vertical-align:bottom' data-checked='false' id='isAnon'>Anonymous</span>
				<span style='float:right;position:absolute;right:20px;bottom:40px;'>

				<span class='href' onClick='postNote();'>Post it</span>
				</span>
			</div>



		<?php
		if (!isLoggedIn())
			$private = true;
		else
		{
			if ($username != $_SESSION['P_usr']) {
				if (!isFriends($username, $_SESSION['P_usr']))
					$private = true;
			}
		}
		if ($private) { ?>
		<div class='boardphoto rotateleft'>
		<img src='<?php echo getUserImage($username); ?>' class='boardphoto'>
		</div>
		<div id='coverupWrapper'>
		<div id='noteEditor'>
				<textarea class='transparentEditor' onKeyUp='handleScroll(this);' onKeyDown='handleKeyPresses(this,290);' id='noteTAEditor' style='width:340px;height:240px;line-height: 30px;'></textarea><br>
				<span class='href' style='float:right;position:absolute;right:20px;bottom:40px;' onClick='postNote();'>Post it</span>
			</div>
		<div id='coverup'>
			<span style='font-size:80px;'>keep out!</span><br>
			<span style='font-size:30px;'>
			This content is only available to my friends.
			</span><br><br>
			<span style='font-size:22px;'><?php
			if (isLoggedIn()) {?>

			a. <a href='javascript:sendFriendRequest(boardUser);'>ask to be friends</a><br>
			b. <a href='javascript:showNote();'>leave a note</a>
			<?php
			} else { ?>
			Please <a href='signin'>sign in</a> or <a href='signup'>sign up</a> if you do not have a Dobble.me account.
			<?php
			}
			?>
			<br><br>
			Signed, <br>
			<?php echo getUserDisplayName($username); ?>
			</span>
		</div>
		</div>
		<script>_g("coverup").style.fontFamily = getFontFromHandwriting(boardUserHandwriting);</script>

		<?php
		exit;
		}
		?>

		<div id='contentwrapper'>
		<div id='toolbar'>

			<div style='float:right;position:fixed;right:40px;top:10px;'>
				<?php
				if ($_SESSION['P_usr'] != $username) {?>
				<input type='button' class='button' value='Buzz' onClick='buzzUser(boardUser);'>
				<input type='button' class='button' value='Unfriend' onClick='confirmRemoveFriend(boardUser);'>
				<?php
				} else {
				$theme = getUserTheme($username);
				?>
				<input type='button' class='button' value='Edit' id='boardEditButton' onClick='boardShowEdit();'>

				<div id='settingsPane'>
				<form action='service/boardsettings.php?' id='sp_form' method='POST' target='datapusher'>
				<input type='submit' class='button' value='Save' id='boardSaveButton' onClick='boardSave();' style='float: right;'>
				<span style='font-size: 20px;'>settings</span>

				<br><br>
				Colors:<br><br>
				<input class='swab' type='text' value='<?php echo $theme->backgroundColor; ?>' name='backgroundColor' onKeyUp='updateBgColor(this.value);'> <div id='sp_bgColor' class='swab'></div>

				Handwriting:<br><br>

				<div id='sp_handwriting' style='line-height: 20px;'></div>
				</form>
				</div>
				<?php
				}
				?>
			<?php
			/*
				} else {
					?>
					<input id='unFollowButton' type='button' class='button' value='Unfollow' onClick='unfollowUser(boardUser);'>
					<input id='followButton' type='button' class='button' value='Follow' onClick='followUser(boardUser);'>
					<script>boardFollowUpdate(<?php echo (isFollowing($_SESSION['P_usr'], $username) ? 1 : 0); ?>);</script>
					<?php


				}*/

			?>
			</div>
		</div>
			<div id='profileheader'>
				<img src='<?php echo getUserImage($username); ?>' id='profilepicture'>
				<div id='papertape'>

				</div>
				<span style='font-size:30px;'><?php echo getUserDisplayName($username); ?></span>
				<br>
				<span style='font-size:20px;'>@<?php echo $username; ?></span>
				<br><br>
				<span style='font-size:20px;'><?php echo getUserBio($username); ?></span>

			</div><div id='boardResponseContainer' onClick='event.cancelBubble=true;'></div>
			<div id='board'></div>

			<?php
			if (isLoggedIn()) {
			?>
			<div id='controlsbar'><a onClick='showStickyNote();'><img class='jumpanimate' src='img/sn_ys.png' width=42 height=42></a>
			<a onClick='showNote();'><img src='img/paper_s.png' width=42 height=42 class='jumpanimate'></a>
			</div>
			<?php
			}
			?>
			<script>
			<?php
				$entries = getUserBoardEntries($username);

				echo generateJSFromBoardEntries($entries);

			?>
updateUI();
positionBoardItems();
setInterval("updateBoard();", 1000);
setInterval("checkBoardPos();", 100);
</script>
		</div>
	</body>
</html>
