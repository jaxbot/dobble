//chat.js
//Specific to the functions of chats, the chat form, and online users

//Globals
var curChatTime = new Array();
var chatFailedTimer;
var failedMessages = new Array();
var userFriends = new Array();
var onlineStatus = new Array();
var subscribedChats = new Array();


function addFriend(username, online, disp) {
	userNames[username] = disp;
	userFriends.push(username);
	onlineStatus[username] = online;
}
function friendsListHTML() {
	var html = "";
	for (i = 0; i < userFriends.length; i++) {
		if (onlineStatus[userFriends[i]]) {
			html += renderChatFriend(userFriends[i], 1);
		}
	}
	for (i = 0; i < userFriends.length; i++) {
		if (!onlineStatus[userFriends[i]]) {
			html += renderChatFriend(userFriends[i], 0.3);
		}
	}
	return html;	
}
function rebuildFriendsList() {
	_g("friendsList").innerHTML = friendsListHTML();
}
function renderChatFriend(user,op) {
	return "<div id='friendItem_"+user+"' class='chatfriend' style='opacity:"+op+";' onClick='handleFriendClick(\""+user+"\");'><img src='" + userAvatar(user) + "' class='chatavatar'> " + userNames[user] + "</div>";
}
//Views
function viewChat(user) {
	id = "chatwith"+user;
	if (addSideframe(id)) {
		showSideframe(id); //Prevents reshowing a open sideframe
	}
	var html = "<div onClick='viewUserProfile(\""+user+"\");' style='display:block;height:40px;cursor:pointer;font-size:18px;'>\
<img src='"+userAvatar(user)+"' class='miniavatar'>"+userNames[user]+"<br><div id='chatstatus_"+id+"' style='font-size:10px;'></div>\
</div>\
<div id='chatcontainer_"+id+"' class='commentcontainer noBubbleScroll' style='height:360px'>\
<center>\
<img src='img/ajax.gif'>\
</center>\
</div>\
<br>\
<span style='bottom:12px;position:absolute'>\
<form name='chatform_"+id+"'>\
<textarea name='message' id='chatmessage_"+id+"' class='commentbox' onBlur='resetMsgbox(this);' onKeyDown='return validateMsg(this, event, function() { frmSendChatMessage(message, \""+user+"\");});' onFocus='handleDefaultText(this);'>\
" + STR_WRITEMESSAGE + "</textarea>\
</form>\
</span>";
	_g("sideframe_content_"+id).innerHTML = html;
	
	updateAutoResizeElements();
	
	sendData("service/getchat.php?user="+user+"&time=0"); 
	//The socket will subscribe after this callback completes
	
	//Legacy support
	setInterval("updateChat('"+user+"');", 1600);
	
	//Auto dismiss notifications
	curAction = id;
	curChatTime[user] = 0;
	processNotifications();
}

//Actions
function setUserOnline(user) {
	onlineStatus[user] = true;
}
function setUserOffline(user) {
	onlineStatus[user] = false;
}

//A wrapper for send chat message that prepares the action
function frmSendChatMessage(txt, to) {
	chatFailedTimer = setTimeout('chatFailed("'+txt.id+'");', 6000); //Cleared if the chat sends, otherwise it fails and returns the old message
	failedMessages[txt.id] = txt.value;
	sendChatMessage(txt.value, to);
	txt.value = "";
}
function sendChatMessage(message, to) {
	sendData("service/sendmessage.php?user="+to+"&message="+encodeURIComponent(message));
	addUnconfirmedMessage(curUser, message.replace(/</g, "&lt;").replace(/>/g, "&gt;"), "chatwith"+to);
}

//Callbacks
function updateChatCallback(messages, isnew, status, id) {
	var user = id;
	id = "chatwith" + id;
	
	//Make sure the sideframe is open, otherwise the system will crash!
	if (!_g("chatcontainer_"+id)) {
		return false;
	}
	
	var html = "";
	var items;
	
	var messagesUpdated = false;
	for (i=0;i<messages.length-1;i++) {	
		items = messages[i].split("~seper~`");
		if (items[0] != "") {
			html += "<div style='display:inline-block;min-height:30px;width: 220px;font-family:calibri;font-size:15px;";
			if (items[0] == curUser)
				html += "color:#b2a7b7";
			html += "'>";
			
			//if (items[0] == curUser)
				
			html += "<img src='"+userAvatar(items[0])+"' class='miniavatar' style='width:22px;height:22px;border-radius:10px;'>";
			html += items[2];
			
			if (items[4] > curChatTime[user]) {
				curChatTime[user] = items[4];
			}
			html += "<br><span class='time' data-time='"+items[3]+"' onMouseOver='this.style.opacity=1' onMouseOut='this.style.opacity=0' style='font-size:9px;opacity:0'></span></div><br><br>";
			messagesUpdated = true;
		}
	}
	if (isnew) {
		var ele = document.createElement("div");
		ele.innerHTML = html;
		_g("chatcontainer_"+id).appendChild(ele);
	} else {
		_g("chatcontainer_"+id).innerHTML = html;
		//add socket subscription, even if legacy (because it goes legacy sometimes on DC)
		subscribeToEvent(EVENT_CHAT);
		subscribeToChat(user);
		if (!isLegacy) {
			sendSubscriptions();
			sendChatSubscriptions();
			
		}
	}
	if (messagesUpdated) {
		_g("chatcontainer_"+id).scrollTop = 10000000;
	}
	//Hide unconfirmed ones
	hideUnconfirmedMessages(id);
	
	//Update the UI to process time, images, and embeds
	updateUI();
	
	//Status
	html = "";
	if (status == "0") {
		html = "Offline";
	}
	if (status == "1") {
		html = "Idle";
	}
	if (status == "2") {
		html = "Online";
	}
	_g("chatstatus_"+id).innerHTML = html;
}
function chatCallback(id) { //The message was sent successfully. Reset the form
	id = "chatwith"+id;
	clearTimeout(chatFailedTimer); 
	_g('chatmessage_'+id).disabled = false;
	if (_g('chatmessage_'+id).focused == false) {
		_g('chatmessage_'+id).style.color='#989898';
		_g('chatmessage_'+id).value = '(click to type a message)';
	}
	
}
function updateFriendsOnlineCallback(friends) {
	
	for(i=0;i<friends.length;i++) {
		for (z=0;z<userFriends.length;z++) {
			if (userFriends[z] == friends[i]) {
				if (onlineStatus[friends[i]] == false) {
					console.log("showing notification for " + friends[i]);
					showPopupNotification(userNames[friends[i]]+" has signed on.", userAvatar(friends[i]), "viewChat('"+friends[i]+"');");
				}
				setUserOnline(friends[i]);
			}
		}
	}
	//set offline the unmentioned users
	for (i=0;i<userFriends.length;i++) {
		var found = false;
		for (z=0;z<friends.length;z++) {
			if (friends[z] == userFriends[i]) found = true;
		}
		if (!found) setUserOffline(userFriends[i]);
	}
	rebuildFriendsList();

}
function addUnconfirmedMessage(from, message, id) {
	var ele = document.createElement("div");
	ele.className = "unconfirmedmessage_"+id;
	var html = "<img src='"+userAvatar(from)+"' class='miniavatar'>";
	html += "<div style='display:block; min-height: 40px; width: 220px;'>";
	html += message;
	html += "<br>";
	
	ele.innerHTML = html;
	_g("chatcontainer_"+id).appendChild(ele);
	_g("chatcontainer_"+id).scrollTop = 10000000;
}
function hideUnconfirmedMessages(id) {
	var arr = document.getElementsByTagName("*");
	var teststr = "unconfirmedmessage_"+id;
	for (i=0;i<arr.length;i++) {
		if (arr[i].className == teststr) {
			arr[i].style.display = "none";
		}
		
	}
}

//Events
function handleFriendClick(user) { //Called from the sidebar. This can be used to display user profiles instead of chats, etc.
	viewChat(user);
	setTimeout('_g("chatcontainer_chatwith'+user+'").scrollTop = 10000000;', 300);
}

//Validaters

//Check the keycode, and if it's right, run the action, and return false.
function validateMsg(txt,e,action) {
	if (e.keyCode == 13) {
		if (txt.value != '') {
			action();
			return false;
		}
	}
	return true;
}
function resetMsgbox(ele) {
	ele.value = STR_WRITEMESSAGE;
	ele.style.color='#989898';
	ele.setAttribute("data-clicked", "false");
}
function handleDefaultText(txt) {
	if (txt.getAttribute("data-clicked") != "true") {
		txt.setAttribute("data-clicked", "true");
		txt.value = "";
		txt.style.color="#fff";
	}
}

function chatFailed(id) {
	_g(id).value = failedMessages[id];
}

//Updaters
function updateFriendsOnline() {
	if (isLegacy) {
		sendData("service/getonlinefriends.php");
	}
}
function updateChat(user) {
	//Don't waste bandwidth when the sideframe is closed/replaced
	if (_g("chatcontainer_chatwith"+user)) {
		if (isLegacy) sendData_Callback('service/getchat.php?user='+user+'&time='+curChatTime[user]+'&new', "chat_"+user);
	} else {
		if (ajaxCallbacks["chat_"+user] != 2) {
			unsubscribeChat(user);
			ajaxCallbacks["chat_"+user] = 2; //let it know this is intentional, not a d/c error
			var r = reqObjects["chat_"+user];
			if (r)
				r.abort();
		}
		
	}
}

