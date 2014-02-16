<?php

//Backend link to the Dobble.me frontend services

/*Globals*/
require_once("drebellous.globals.php");

$MY_ROOT = "../dyan/";
require($MY_ROOT."system/databasedata.php");
require($MY_ROOT."system/userdata.php");
require($MY_ROOT."system/historydata.php");
require($MY_ROOT."system/functiondata.php");
require($MY_ROOT."system/emaildata.php");
require($MY_ROOT."system/notificationdata.php");
require($MY_ROOT."system/photodata.php");
require($MY_ROOT."system/chatdata.php");

function attemptAuth($msg) {
	$msg = substr($msg, 1); //remove first character
	list($username, $key) = explode(":", $msg);

	$priv = getUserAuthKey($username);

	if ($key == $priv) {
		return true;
	}
	return false;
}

//Events
$EVENT_TIMELINE = "t";
$EVENT_COMMENTS = "c";
$EVENT_NOTIFICATIONS = "n";
$EVENT_CHAT = "C";
$EVENT_COMMENT = "s";
$EVENT_ONLINEFRIENDS = "f";

function subscribeEvents($user, $events) {
	$user->events = $events;
}

function unsubscribeEvent($user, $event) {
	$user->events = str_replace($event, "", $user->events);
}

function isSubscribedTo($user, $event) {
	if (strpos($user->events, $event) !== false) {
		return true;
	} else {
		return false;
	}
}

function handleEvents($user) {
	global $EVENT_COMMENTS, $EVENT_CHAT, $EVENT_COMMENT, $EVENT_NOTIFICATIONS, $EVENT_ONLINEFRIENDS, $EVENT_TIMELINE,
	$CACHE_USER_PROFILE, $CACHE_THUMBNAIL, $CACHE_DISPLAYNAME;


	if (isSubscribedTo($user, $EVENT_TIMELINE) and $user->timeline_lasttime) {
		//Derived from service timeline.php
		$output = _getHistoryData($user->username, $user->timeline_lasttime);
		if ($output != false) {
			p("Pushing new timeline event to " . $user->username);
			$user->timeline_lasttime = $output[1];
			send($user->socket, "~a" . $output[0]);
			//update comment cache
			$user->timeline_cache = $output[2];
			$user->comment_streams = getStreams($user->timeline_cache);
		}
	}
	if (isSubscribedTo($user, $EVENT_COMMENTS)) {
		if (!$user->timeline_cache) {
			//newcomer
			$user->timeline_cache = getHistoryItems($user->username, true, 20);
			$user->comment_streams = getStreams($user->timeline_cache);
			$user->comment_lastsum = calculateCommentSum($user->comment_streams);
		}
		$output = _getCommentCountData($user->comment_streams, $user->comment_lastsum);
		if ($output != false) {
			send($user->socket, $output[0]);
			$user->comment_lastsum = $output[1];
		}
	}
	if (isSubscribedTo($user, $EVENT_NOTIFICATIONS)) {
		$output = _getNotificationData($user->username, $user->notification_lasttime);
		if ($output != false) {
			if ($output == -1) {
				$user->notification_lasttime = 0;
			} else {
				send($user->socket, $output[0]);
				$user->notification_lasttime = $output[1];
			}
		}
	}
	if (isSubscribedTo($user, $EVENT_CHAT)) {
		foreach ($user->subscribed_chats as $chat) {
			//p("Getting chats..");
			$output = _getChatData($user->subscribed_chats_with[$chat], $chat, $user->subscribed_chat_times[$chat], $user->subscribed_chat_statuses[$chat], ($user->threscounter > 4));
			if ($output != false) {
				p("Pushing chat to ".$user->username);
				send($user->socket, $output[0]);
				$user->subscribed_chat_times[$chat] = $output[1];
				$user->subscribed_chat_statuses[$chat] = $output[2];
			}
		}
	}
	if (isSubscribedTo($user, $EVENT_COMMENT)) {
		foreach ($user->subscribed_comments as $comment) {
			$output = _getCommentData($comment, $user->subscribed_comments_counts[$comment], $user->username); //username is needed for sandboxing
			if ($output != false) {
				p("Pushing comment to ".$user->username);
				send($user->socket, $output[0]);
				$user->subscribed_comments_counts[$comment] = $output[1];
			}
		}
	}
	$user->threscounter++;
	if ($user->threscounter > 5) {
		updateUserIsOnline($user->username, false); //false = not mobile

		//Profile cache, for items that will never change will connected (or wont be a problem if stale)
		if (!isset($user->profileCache)) {
			$user->profileCache = getUserProfile($user->username);
		}
		if (isSubscribedTo($user, $EVENT_ONLINEFRIENDS)) {
			$output = _getOnlineFriends($user->username, $user->profileCache->friends, $user->onlineFriendCache);

			if ($output != false) {
				p($output[0]);
				send($user->socket, $output[0]);
				$user->onlineFriendCache = $output[1];
			}
		}
		$user->threscounter = 0;
	}
}

//Services
function _getHistoryData($username, $lasttime) {

	$lastHistoryTime = getLastHistoryTime($username, true);
	if ($lastHistoryTime <= $lasttime)
		return false;

	$historyitems = getHistoryItems($username, true, 20);

	$comments = getMassCommentCount(getCommentIdsArray($historyitems));

	$data = "";
	$c = 0;
	foreach ($historyitems as $historyitem) {
		$output = false;
		if ($historyitem->time > $lasttime) {
			$output = true;
		}

		if ($output) {
			$historyitem->message = renderEvent($historyitem);
			$cnt = $comments[$historyitem->id];
			if (!$cnt) $cnt = 0;
			$json = json_encode($historyitem);
			$data .= $json . "~s~" . $cnt . "~s~" . (time() - $historyitem->time) . "~nitem~";
			$c++;
		}
	}
	return array($data, $lastHistoryTime, $historyitems);
}

function _getCommentCountData($streams, $lastsum) {
	$sum = 0;
	$output = "";
	foreach ($streams as $stream) {
		$comments = getCommentStream($stream);
		$cnt = count($comments);

		$output .= "$stream~s~`$cnt~n~`";

		$sum += $cnt;
	}

	if ($sum == $lastsum) { //identical, nothing changed
		return false;
	}

	$output = str_replace("photo", "", $output);
	$output = "~c$sum:" . $output;

	return array($output, $sum);
}

function getStreams($historyitems) {
	$streams = array();
	$i = 0;
	foreach ($historyitems as $historyitem) {
		if ($historyitem->type == 1) { //Photo
			$photoids = explode(",", $historyitem->meta);
			if (count($photoids) == 1) {
				$streams[$i] = "photo" . $historyitem->meta;
			} else {
				$streams[$i] = $historyitem->id;
			}
		} else {
			$streams[$i] = $historyitem->id;
		}
		$i++;
	}
	return $streams;
}

function calculateCommentSum($streams) {
	$sum = 0;
	foreach ($streams as $stream) {
		$comments = getCommentStream($stream);
		$cnt = count($comments);

		$sum += $cnt;
	}
	return $sum;
}

//Notifications
function _getNotificationData($user, $time) {

	$data = newUserNotificationsEx($user, $time);

	if ($data === false)
	return false;
	if ($data === -1)
	$time = 0;

	$nots = getUserNotifications($user);

	$output = "~n" . (count($nots)) . ">>";
	foreach ($nots as $not) {
		$output .= "<not>" . $not->body . "~sp~`" . $not->time . "~sp~`" . $not->action . "~sp~`" . $not->type . "~sp~`" . $not->from . "~sp~`" . $not->id . "~sp~`" . (time() - $not->time);
		if ($not->time > $time) {
			$time = $not->time;
		}
	}

	return array($output, $time);
}

//Chat
function _getOnlineData($with) {
	if (isOnline($with)) {
		$status = 1; //Idle
		if (isActive($with)) {
			$status = 2; //Active
		}
	} else {
		$status = 0; //Offline
	}
	return $status;
}
function _getChatData($with, $id, $lastTime, $lastStatus) {

	$messages = getNewChatMessages($id, $lastTime);
	if (count($messages) > 0) {
		$output = "~h"; //Append the message

		foreach ($messages as $message) {
			$fromname = getUserDisplayName($message->from);
			$time = time() - $message->time;
			$msg = parse_links($message->message);
			$output .= $message->from."~seper~`$fromname~seper~`".$msg."~seper~`$time~seper~`".$message->time."~nextitem~`";
			if ($message->time > $lastTime) $lastTime = $message->time;
		}
	} else {
		if ($getStatus) {
			$status = _getOnlineData($with);
			if ($status == $lastStatus) {
				return false; //Piece that stops it if no change
			} else {
				$output = "~h"; //No new messages, but the status changed, so push the data
			}
		} else {
			return false; //return false since it's only supposed to check every 4 cycles
		}
	}

	if (!isset($status)) {
		$status = _getOnlineData($with);
	}
	$output .= "~~:$status";

	//Append the ID (in this case, the user)
	$output .= "~~:$with";

	return array($output, $lastTime, $status);

}

function _getCommentData($id, $lastcomment, $user) {
	$comments = getCommentStream($id);

	if (count($comments) == $lastcomment) {
		return false;
	}

	$output = "~C";
	$output .= $id; //Add the ID for sideframe verification
	$output .= "~c~`";

	foreach ($comments as $comment) {
		$time = time() - $comment->time;
		$fromname = getUserDisplayName($comment->from);
		$avatar = getUserThumbnail($comment->from);
		$msg = str_replace("'", "\\'", $comment->message);
		$msg = parse_links($msg);
		$msg = str_replace("\\'", "'", $msg);
		$rndid = $comment->id;
		$from = $comment->from;
		if (isFriends($user, $comment->from) or $comment->from == $user) {
			$output .= "$from~seper~`$fromname~seper~`$avatar~seper~`$rndid~seper~`$time~seper~`$msg~nextitem~`";
		} else {
			$output .= "H~seper~`".count(explode(" ", $msg))."~seper~`".md5($rndid)."~nextitem~`";
		}
	}

	return array($output, count($comments));
}
function _getOnlineFriends($user, $friends, $last) {
	$onlineFriends = getOnlineFriends($user, true, $friends); //true, friends) allows it to use cache
	if ($onlineFriends == $last) return false;

	$output = "~F".join($onlineFriends, ",");

	return array($output, $onlineFriends);
}
?>