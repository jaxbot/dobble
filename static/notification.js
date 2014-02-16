//notification.js
//Handles loading, rendering, dismissing, handling, etc of notifications

//Globals
var newNotification = false;
var curNotificationSet = "";
var curNotificationId = 0;
var curNotificationCount = 0;
var lastCount = 0;
var curAction = ""; //Used to auto-dismiss notifications if a pane is already open
var bouncingFriends = new Array(); //The current friend-bounce notifications, to prevent another one from initating

var canHideNotifications = false;
var tickerText = "";
var tickerId = "";
var usedNotificationIds = new Array(); //Used to prevent duplicate desktop notifications

var waitForProcessNot = false;

//Types
var N_FRIEND = 0;
var N_EVENT = 1;
var N_PHOTO = 2;
var N_CHAT = 3;
var N_BUZZ = 4;
var N_SIGNON = 5;

//Animation
var notifTop = 5;
var notifDir = 1;
var stopBounce = true; //Force the animation to stop

//Updaters
function checkForNotifications() {
	if (isLegacy) {
		sendData_Callback("service/getnotifications.php?id="+curNotificationId, 2); //2 == Notification
	}
}
function checkForNotifications_Init() {
	sendData_Callback("service/getnotifications.php?id=-1", 2);
}
subscribeToEvent(EVENT_NOTIFICATIONS);

//Animation actions
function bounceNotification() {
	if (newNotification != true) {
		return false;
	}
	if (stopBounce == true) {
		return false;
	}
	//if (!bouncingFriends[friend]) return false;
	if (notifDir == 1) {
		notifTop -= 1;
		if (notifTop < -1) {
			notifDir = 0;
		}
	}
	if (notifDir == 0) {
		notifTop += 1;
		if (notifTop > 4) {
			notifDir = -1;
		}
	}
	if (notifDir == -1) {
		//sleep
		setTimeout("notifDir = 1; bounceNotification();", 1000);
	} else {
		document.getElementById("notificationDot").style.top = notifTop + "px";
		//alert(notifTop);
		setTimeout("bounceNotification();", 25);
	}
}
function bounceFriend(friend) {

	if (bouncingFriends[friend]) return false;
	bouncingFriends[friend] = 1;
	document.getElementById("friendItem_"+friend).style.color = "#e40b00";
	bounceFriendAnimation(friend, 0, 1, 0);

	//Webkit
	showDesktopNotification("Dobble.me", "New message from "+getUserDisplayName(friend), userAvatar(friend));

	//Sound
	playMessageSound();
}
function stopBouncingFriend(friend) {
	document.getElementById("friendItem_"+friend).style.color = "black";
	bouncingFriends[friend] = 0;
}
function bounceFriendAnimation(friend, left, direction, step) {

	if (!bouncingFriends[friend]) {
		document.getElementById("friendItem_"+friend).style.left = "0px";
		return false;
	}
	if (direction == 1) {
		left += 1 + (left / 2);
	}
	if (direction == 0) {
		left -= 1 + (left / 5);
	}
	if (direction == -1) {
		step++;
		if (step > 50) {
			step = 0;
			direction = 1;
		}
	}
	if (left < 0) {
		left = 0;
		direction = -1;
	}
	if (left > 20) {
		direction = 0;
	}

	document.getElementById("friendItem_"+friend).style.left = left + "px";
	setTimeout("bounceFriendAnimation('"+friend+"',"+left+","+direction+","+step+");", 30);
}
function isBouncingFriend(friend) {
	if (bouncingFriends.length > 0) {
		for(i=0;i<bouncingFriends.length;i++) {
			if (bouncingFriends[i] == friend) {
				return true;
			}
		}
	}
	return false;
}

//Callbacks
function newNotificationCallback(data) {
	var s = data.split(">>");
	curNotificationSet = s[1];
	var count = s[0];
	curNotificationCount = count;
	curNotificationId = 0;
	waitForProcessNot = true;
	processNotifications();

}

function processNotifications() { //Called by newNotificationCallback, an interval, and event/chat callbacks
	//Process the notifications and dismiss ones that have already been read
	var notifications = curNotificationSet.split("<not>");
	var s;
	var html = "";
	var ticker = "";
	var n = 0;
	var sbf = false;
	var count = curNotificationCount;
	var lastFrom;

	for(i=0;i<notifications.length;i++) {
		s = notifications[i].split("~sp~`");
		if (s[2]) {
			ticker = s[0];

			var id = s[2].split("::");
			id = id[0];

			if (id == curAction) {
				if (userIsActive) { //don't dismiss if the window is inactive
					count -= 1;
					dismissNotification(s[5]);
					if (s[3] == N_CHAT) {
						stopBouncingFriend(s[4]);
						sbf = true;
					}
				}

			} else {
				//Chat
				if (s[3] == N_CHAT) {
					if (!sbf) { //Prevent a critical bug where multiple messages would create an infinite loop between bouncing and stop bouncing.
						setTimeout("bounceFriend('"+s[4]+"');", 10);
					}
					count -= 1;
					if (isSideframeOpen(id)) {
						setNudgeSideFrame(id);
					}
				}
			}
			//Photos
			if (typeof(curPhoto) != 'undefined') {
				if (curPhoto != -1) {
					if (typeof(s[2]) != 'undefined') {
						var qq = s[2].split("::");

						if ("photo" + imgIds[curPhoto] == qq[0]) {
							count -= 1;
							dismissNotification(s[5]);
						}
					}
				}
			}
			//Buzz
			if (s[3] == N_BUZZ) {
				showDialog("Buzz!", s[0]);
				setTimeout("playBuzzSound();",10);
				dismissNotification(s[5]);
				count -= 1;
				
			}
			
			lastFrom = s[4];
			if (parseInt(s[1]) > curNotificationId) {
				curNotificationId = parseInt(s[1]);
			}
		}
		n++;
	}

	if (count > 0) {
		document.getElementById("notificationDot").innerHTML = count;
		document.getElementById("notificationDot").style.display = "inline-block";
		if (lastCount != count) {
			newNotification = true;
			if (stopBounce) { //Only bounce if it has been stopped
				stopBounce = false;
				bounceNotification();
			}
		}

		//Update title
		window.document.title = "Dobble.me (" + count + ")";
		//Show popup, if different and only one count
		if (count == 1) {
			if (ticker != tickerText) {
				tickerText = ticker;
				showPopupNotification(tickerText, userAvatar(s[4]), "showNotificationsList();");
			}
		}

	} else {
		document.getElementById("notificationDot").style.display = "none";
		newNotification = false;
		//Restore title
		window.document.title = "Dobble.me";
		//Reset ticker/popup
		tickerText = "";
	}
	lastCount = count;

}

//Views
function showNotificationsList() {
	var notifications = curNotificationSet.split("<not>");
	var s;
	var html = "";

	if (notifications.length == 2) {
		s = notifications[1].split("~sp~`");
		if (s[0] != "") {
			handleNotification(s[5],s[2],s[3]);
		}
	} else {
		for(i=0;i<notifications.length;i++) {
			s = notifications[i].split("~sp~`");
			if (s[0] != "") {
				if (s[3] != N_CHAT) {
					html += "<span class='notificationItem' onClick='handleNotification(\"" + s[5] + "\", \"" + s[2] + "\", \"" + s[3] + "\");'><img class='avatar' src='"+userAvatar(s[4])+"'>"+s[0] + "<br><span class='time' data-time='"+s[6]+"'>" + getRelativeTime(s[6]) + " ago</span></span><br />";
				}
			}
		}

		_g("notificationList").innerHTML = html;
		var e = _g("notificationContainer");
		e.style.display = "inline-block";
		if (isInline) {
			e.style.right = "35px";
			e.style.left = null;
			e.style.position = "fixed";
		}
		else
			e.style.left = getElementXY(_g("notificationDot")).x + "px";

		stopBounce = true;
		canHideNotifications = false;
		setTimeout("canHideNotifications = true;", 200);
	}

}
function hideNotificationList() {
	_g('notificationContainer').style.display = 'none';
}

//Events
function handleNotification(id, action, type) {
	if (type == N_FRIEND) {
		fetchPage(PAGE_FRIENDS); //Handles dismissing and handling of requests.
	}
	if (type == N_EVENT) {
		var s = action.split("::");

		viewInDetail(s[0], s[1]);
		setTimeout('document.getElementById("commentcontainer_'+s[0]+'").scrollTop = 10000000;', 500);
	}
	if (type == N_PHOTO) {
		var s = action.split("::");
		var r = s[0].split("_");
		fetchPage(PAGE_PICTURES, '?from='+s[1]+"&album="+r[1]+"&photo="+s[0].replace("photo", ""));
	}
}

//Actions
function dismissNotification(id) {
	sendData("service/dismissnotification.php?id="+id);
}
function dismissAll() {
	var notifications = curNotificationSet.split("<not>");
	var s;
	for(i=0;i<notifications.length;i++) {
		s = notifications[i].split("~sp~`");
		if (s[2]) {
			dismissNotification(s[5]);
		}
	}
}