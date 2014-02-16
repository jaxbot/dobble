//data.js
//Handles AJAX sending and processing

//Globals
var ajaxCallbacks = new Array(); //Used to hold callbacks for different AJAX requests states, preventing duplicate requests
					//0 == Timeline
					//1 == Chat
					//2 == Notification
					//3 == Older posts
					//4 == Comment count

var ajaxErrorTimeout;

var reqObjects = new Array(); //Store the callback objects

var socket;		//The socket object
var socketBuffer; //Buffer for the socket

//Events
var EVENT_TIMELINE = "t";
var EVENT_COMMENTS = "c";
var EVENT_NOTIFICATIONS = "n";
var EVENT_CHAT = "C";
var EVENT_COMMENT = "s";
var EVENT_ONLINEFRIENDS = "f";

var subscribedEvents = new Array();


var isLegacy = false; //When true, the system will use polling.
					  //Automatically checked by checkForSocketSupport
var connectFailCount = 0; //Added to every time a connection fails, resets on success
					      //2 and over will force legacy

function readData(data) {

	if (data == "")
		return false;

	//Protocol: ~[1xHeader]
	if (data.substring(0, 1) == "~") {
		switch (data.substring(1, 2)) {
			case "a":
				//Add history item
				addTimelineFromServer(data,0);
			break;
			case "A":
				//Add history item (append)
				addTimelineFromServer(data,1);
				console.log("new posts");
			break;
			case "c":
				//Update comment count
				var raw = data.substring(2);
				updateCommentCountCallback(raw);
			break;
			case "e":
				//Event detail callback
				//user, avatar, fname, event, eventtime, id, comments
				var entries = data.substring(2).split("{{}");
				var user = entries[0];
				var avatar = entries[1];
				var fname = entries[2];
				var event = entries[3];
				var eventtime = entries[4];
				var id = entries[5];
				var comments = entries[6];

				viewEventCallback(user, fname, avatar, event, eventtime, id, comments);
			break;
			case "C":
				//Comment callback
				var items = data.substring(2).split("~c~`");
				var entries = items[1].split("~nextitem~`");
				var id = items[0];
				updateCommentsCallback(id, entries);
			break;
			case "d":
				//Show dialog
				var title = data.substring(2).split("<title>");
				title = title[1].split("</title>");
				title = title[0];
				var message = data.substring(2).split("<message>");
				message = message[1].split("</message>");
				message = message[0];

				showDialog(title, message);
			break;
			case "q":
				//Search query
				var items = data.substring(2).split("~nextitem~`");
				searchResultCallback(items);
			break;
			case "n":
				//Notifications callback
				newNotificationCallback(data.substring(2));
			break;
			case "f":
				//Friend request callback
				location.href="#friends";
			break;

			case "P":
				//Picture comments callback
				var entries = data.substring(2).split("~nextitem~`");

				getPhotoCommentsCallback(entries);
			break;
			case "H":
				//Chat callback
				var s = data.substring(2).split("~~:")
				var entries = s[0].split("~nextitem~`");

				updateChatCallback(entries, false, s[1], s[2]);
			break;
			case "h":
				//Chat callback (new message)
				var s = data.substring(2).split("~~:")
				var entries = s[0].split("~nextitem~`");

				updateChatCallback(entries, true, s[1], s[2]);
			break;
			case "R":
				//Username taken check
				var status = data.substring(2);
				usernameLookupCallback(status);
			break;
			case "F":
				//Friends online callback
				var friends = data.substring(2).split(",");
				updateFriendsOnlineCallback(friends);
			break;
			case "S":
				//Status callback
				switch (data.substring(2, 3)) {
					case "M": //Message callback
						var statusCode = data.substring(3, 4);
						if (statusCode == "1") {
							chatCallback(data.substring(4));
						} else {
							if (statusCode == "2") { //Message was 2 long. Get it?
								showDialog('Oops', 'Your message is a tad long, try shortening it a bit.');
							}
							if (statusCode == "3") { //Not friends
								showDialog('Oops', 'You need to be friends in order to send messages.');
							}
							//Otherwise, the message will just expire automatically, as if the connection failed.
						}
					break;
					case "C": //Comment callback
						var statusCode = data.substring(3, 4);
						if (statusCode == "1") {
							commentCallback(data.substring(4));
						} else {
							if (statusCode == "2") { //See previous joke
								showDialog('Oops', 'Your message is a tad long, try shortening it a bit.');
							}
						}
					break;
				}
			break;
			case "L":
				//Logged out error
				location.href='login.php?user='+curUser;
			break;
			case "1":
				//Restart the page
				window.location.reload();
			break;
			case "$":
				//Render the page
				var data = data.substring(2);
				data = data.split("<script>");

				renderPage(data[0]);
				if (data[1] != "") setTimeout(data[1],0);

			break;
			case "?":
				setTimeout(data.substring(2),0);
				break;
			case "+":
				boardFollowUpdate(data.substring(2));
			break;
			case "~":
				//General ignore case
			break;
			default:
				showAjaxError("Unknown header.");
				console.log(data);
			break;
		}
	} else {
		//Something bad happened in transmission
		//Disable this on production mode
		showAjaxError("Data transmission bad.");
		return false;
	}
}
function sendData(data) {
	sendData_Callback(data, data);
}
function sendData_Callback(data, callback) { //Send data, plus a callback variable is set to prevent duplicate requests.
	if (ajaxCallbacks[callback] == -1) {
		//still pending
		return false;
	}
	ajaxCallbacks[callback] = -1;
	try {
		reqObjects[callback] = new XMLHttpRequest();
	} catch (e) {
		return e;
	}
	var reqObj2 = reqObjects[callback];

	reqObj2.onreadystatechange = function() {
		if (reqObj2.readyState == 4) {
			if (reqObj2.status == 200) {
				ajaxCallbacks[callback] = 1; //in case the reading script blocks (aka crashes)
				readData(reqObj2.responseText);
			} else {
				if (reqObj2.status == 0) { //If the user loads a new page, this error will the thrown. Delay it with timeouts
					if (ajaxCallbacks[callback] != 2) { //set when aborted
						setTimeout('showAjaxError("Disconnected.");', 1000);
					}
				} else {
					setTimeout('showAjaxError("Service problem.");', 1000);
				}

			}
			reqObj2 = null;
			ajaxCallbacks[callback] = 1;

		}
	}
	if (data.indexOf("?") == -1)
		data += "?";
	else
		data += "&";
	//phase this out/make optional
	data += "a=" + authKey;
	reqObj2.open("GET", data, true);
	reqObj2.send(null);
}
function showAjaxError(txt) {	//display the friendly (well...yeah) overlay that tells the user what happened
	clearTimeout(ajaxErrorTimeout);
	var d = document.getElementById("ajaxerrorbox");
	d.style.display = "block";
	d.innerHTML = "Dobble connection error: "+txt+ "<br>Please refresh your browser.";
	ajaxErrorTimeout = setTimeout("_g(\"ajaxerrorbox\").style.display = \"none\";", 2000);
}

function openSocket() {
	socket = new WebSocket("ws://int.dobble:443/");
	socket.onopen = function(evt) { socketOpened(evt) };
	socket.onclose = function(evt) { socketClosed(evt) };
	socket.onmessage = function(evt) { socketMessage(evt) };
	socket.onerror = function(evt) { socketError(evt) };
}

function socketOpened(evt) {
	//Auth immediately
	socket.send("%" + curUser + ":" + authKey);
}
function socketClosed(evt) {
	//console.log("Closed.");
	//Reconnect in 1s
	setTimeout("openSocket();", 1000);
	connectFailCount++;
	if (connectFailCount > 0) {
		isLegacy = true;
	}
}
function socketMessage(evt) {
	isLegacy = false;
	connectFailCount = 0;

	console.log(evt.data);
	if (evt.data == "@") {
		//Send subscriptions. (Done this way so subscriptions are restored if the connection dies)
		sendSubscriptions();
		if (subscribedEvents[EVENT_CHAT]) {
			setTimeout("sendChatSubscriptions();", 10); //prevent blocking
		}

		if (subscribedEvents[EVENT_COMMENT]) {
			setTimeout("sendCommentSubscriptions();", 10);
		}
	} else {
		readData(evt.data);
	}
}
function socketError(evt) {
	console.log("Error: " + evt.data);
}
function socketSend(data) {
	//Add data to socket queue
	socketBuffer += data;
}
function socketWrite() {
	if (!isLegacy && typeof (socket) != 'undefined') {
	if (socket.readyState == 1) {
		if (socketBuffer != "") {
			socket.send(socketBuffer);
			socketBuffer = "";
		}
	}
	}
}
setInterval("socketWrite();", 10);
//Event framework
function subscribeToEvent(eventId) {
	subscribedEvents[eventId] = eventId;
}
function unsubscribeEvent(eventId) {
	subscribedEvents[eventId] = -1;
	socketSend("~u"+eventId);
}
function sendSubscriptions() {
	if (lasttime == 0) {
		setTimeout("sendSubscriptions();", 100); //if we don't have the data, wait
		return false;
	}
	var data = "";
	for (subscription in subscribedEvents) {
		if (subscription != -1) {
			data += subscription + ",";
		}
	}
	data = "~s" + data;
    if (subscribedEvents[EVENT_TIMELINE]) {
        data += "~t" + lasttime;
    }
    socketSend(data);
}
function sendChatSubscriptions() {
	var data = "";
	var i = 0;
	for (subscription in subscribedChats) {
		if (subscription != -1) {
			socketSend('~C0'+subscription+','+curChatTime[subscription]);
		}
		i++;
	}
}
function subscribeToChat(user) {
	subscribedChats[user] = user;
}
function unsubscribeChat(user) {
	subscribedChats[user] = -1;
	socketSend("~U0"+user);
}

function sendCommentSubscriptions() {
	var data = "";
	var i = 1;
	for (subscription in subscribedComments) {
		if (subscription != -1) {
			if (document.getElementById("commentcontainer_"+subscription) != null) {
				socketSend('~C1'+subscription);
			} else {
				subscribedComments[subscription] = -1;
			}
			i++;
		}
	}
}
function subscribeToComment(stream) {
	subscribedComments[stream] = stream;
	socketSend("~C1"+stream);
}
function unsubscribeComment(stream) {
	subscribedComments[stream] = -1;
	socketSend("~U1"+stream);
}

function checkForSocketSupport() {
	try {
		openSocket();
	} catch (e) {
		isLegacy = true;
	}
}
//Defer this
setTimeout("checkForSocketSupport();", 50);

