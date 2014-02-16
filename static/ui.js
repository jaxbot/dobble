//ui.js
//UI related code

//Globals
var dialogCallback = ""; //Eval'd after confirmation dialog
var userIsActive = true;
var inactiveTimeout = setTimeout("userIsActive = false;", 2000); //Inital is 2000
var moveAnimId = 0;

//Preload sounds
var buzzSound = new Audio("common/n001.mp3");
var messageSound = new Audio("common/n002.mp3");

//Defined pages
var CURRENT_PAGE = "";
var PAGE_DEFHEADER = ""; //stores the default page header
var PAGE_HOME = ""; //currently does nothing
var PAGE_FRIENDS = "friends";
var PAGE_PICTURES = "pictures";
var PAGE_PROFILE = "profile";
var DEFINEDPAGES = [PAGE_FRIENDS,PAGE_PICTURES,PAGE_PROFILE];

//Event states
var isScrollHalted = false;
var isInline = false;

//Stores original values for textboxes, which are restored when blurred
var textHandle_OriginalText = new Array();

//Lifesavers
function _g(id) {
	return document.getElementById(id);
}

//Strings
var STR_WRITECOMMENT = "Write a response...";
var STR_WRITEMESSAGE = "Type your message...";
var STR_ERROR_PHOTOS_UPLOADFAILED = "The upload has failed. Please try again. If the same thing happens, try uploading just a few at a time.";

//Dialog
document.write("<div id='messageBox'><div id='messageBoxCaption'></div><div id='messageBoxBody'></div><span style='float:right'><input type='button' value='Ok' class='button' id='dialogOK' onClick='closeDialog(1);'>    <input type='button' value='Cancel' id='dialogCancel' class='button' onClick='closeDialog(0);'></span></div>");

function showDialog(title,message) {
	_g("dialogCancel").style.display = "none";
	_g("dialogOK").style.right = "5px";
	_g("messageBox").style.display = "block";
	_g("messageBoxCaption").innerHTML = title;
	_g("messageBoxBody").innerHTML = message;
	fadeInElement(_g("messageBox"));
	dialogCallback = "";
}
function showConfirm(title,message,callback) {
	_g("dialogCancel").style.display = "inline-block";
	_g("dialogOK").style.right = "75px";
	_g("dialogCancel").style.right = "5px";
	_g("messageBox").style.display = "block";
	_g("messageBoxCaption").innerHTML = title;
	_g("messageBoxBody").innerHTML = message;
	fadeInElement(_g("messageBox"));
	dialogCallback = callback;
}
function closeDialog(result) {
	_g("messageBox").style.display = "none";
	if (result == 1) {
		if (dialogCallback != "") {
			setTimeout(dialogCallback,0);
		}
	}
}

//Elements
function fadeInElement(ele, step, speed) {
	step = typeof(step) != 'undefined' ? step : 1;
	speed = typeof(speed) != 'undefined' ? speed : 5;
	for (var i=0;i<100;i=i+step) {
		setTimeout("_g('"+ele.id+"').style.opacity = " + (i / 100) + ";", i * speed);
	}
}
function fadeOutElement(ele) {
	var z = 0;
	for (i=100;i>0;i-=2) {
		setTimeout("_g('"+ele.id+"').style.opacity = " + (i / 100) + ";", z * 5);
		z++;
	}
	setTimeout("_g('"+ele.id+"').style.opacity = 0;", z * 5);
}
function popUpElement(ele, destbottom) {
	var c = destbottom - 100;
	for (i=0;i<100;i++) {
		setTimeout("_g('"+ele.id+"').style.bottom = " + (c + i) + " + 'px';", i * 5);
	}
}
function moveElementToX(id, x) {
	var cur = parseInt(_g(id).style.left.replace("px", ""));
	var d = cur - x;

	if (d > 0) {
		//Go left
		if (d < 11) {
			//It's not going anywhere useful
			return false;
		}
		var i;
		var c = 1;
		for (i=1;i<d;i++) {
			//anim id prevents two animations from occuring at the same time
			setTimeout("if(moveAnimId=="+moveAnimId+")_g('"+id+"').style.left="+(cur - c)+"+'px';", i * 3);
			if (c < d) {
				c += 1 + (((cur - c) - x) / (i * 4));
			}
		}

	} else {
		//Go right

		var i;
		d = d * -1;
		var c = 1;

		if (d < 5) {
			//It's not going anywhere useful
			return false;
		}

		for (i=1;i<d;i++) {
			setTimeout("_g('"+id+"').style.left="+(cur + c)+"+'px';", i * 5);
			c += ((d / i) / 4);
		}

	}
}
function moveElementToY(id, y) {
	var cur = parseInt(_g(id).style.top.replace("px", ""));
	var d = cur - y;

	if (d > 0) {
		//Go up
		var i;
		var c = 1;
		for (i=1;i<d;i++) {
			setTimeout("_g('"+id+"').style.top="+(cur - c)+"+'px';", i * 5);
			if (c < d) {
				c += 1 + (((cur - c) - y) / (i * 8));
			}
		}
	} else {
		//Go down
		var i;
		d = d * -1;
		var c = 1;

		for (i=1;i<d;i++) {
			setTimeout("_g('"+id+"').style.top="+(cur + c)+"+'px';", i * 5);
			c += ((d / i) / 5);
		}
	}
}

function getElementXY(ele) {

	var ox = 0;
	var oy = 0;
	while (true) {
		ox += ele.offsetLeft;
		oy += ele.offsetTop;
		if (ele.offsetParent) {
			ele = ele.offsetParent;
		} else {
			break;
		}
	}
	return { x: ox, y: oy }
}
function typeWriterIn(eid, txt) {
	for(i=0;i<txt.length;i++) {
		setTimeout('_g("'+eid+'").innerHTML = "'+txt+'".substr(0, '+i+');', i * 10);
	}
}

//Forms
function textClickHandle(ele) { //Resets the default value of a text box
	if (ele.style.color=="rgb(152, 152, 152)") { //#989898
		ele.style.color="black";
		textHandle_OriginalText[ele.id] = ele.value;
		ele.value="";
	}
}
function blurTextClick(ele,delay) {
	if (typeof(delay) == "undefined") delay = 0;
	//allow a delay, for event firing
	setTimeout(function() {
	if (ele.value == "") {
		ele.style.color="rgb(152, 152, 152)";
		ele.value = textHandle_OriginalText[ele.id];
	}
	}, delay);
}
function toggleCheckbox(id) {
	var e = _g(id);
	var val;
	if (e.getAttribute("data-checked") == "false")
		val = "true";
	else
		val = "false";

	e.setAttribute("data-checked", val);
	e.src = (val == "true" ? "img/checked.png" : "img/check.png");

}

//Notifications
function showPopupNotification(text, icon, action) {
	fadeInElement(_g("popupNotification"));
	_g("popupNotification").style.display = "block";
	_g("popupNotification").innerHTML = "<img src='"+icon+"' style='float:left;padding-right: 3px;vertical-align:top'>"+text;
	_g("popupNotification").onclick = function () { eval(action) };
	setTimeout("fadeOutElement(_g('popupNotification'));", 6000);
	setTimeout("_g('popupNotification').style.display='none';", 7000);
}

//Sounds
function playBuzzSound() {
	buzzSound.play();
}
function playMessageSound() {
	messageSound.play();
}

//Title notification
function showAttentionIcon(isOn) {
	if (isOn) {
		var e = document.createElement("link");
		e.type = "image/x-icon";
		e.rel = "icon";
		e.href = "img/favicon_attn.ico";
		document.getElementsByTagName("head")[0].appendChild(e);
	} else {
		var arr = document.getElementsByTagName("link");
		for (i=0;i<arr.length;i++) {
			if (arr.rel == "icon") {
				document.getElementsByTagName("head")[0].removeChild(arr[i]);
			}
		}
		var e = document.createElement("link");
		e.type = "image/x-icon";
		e.rel = "icon";
		e.href = "favicon.ico";
		document.getElementsByTagName("head")[0].appendChild(e);
	}
}
function blinkAttention() {
	setTimeout('showAttentionIcon(true);',10);
	setTimeout('showAttentionIcon(false);',600);
	setTimeout('showAttentionIcon(true);',1100);
	setTimeout('showAttentionIcon(false);',1700);
}


function getRelativeTime(time) {
	var str = "";
	if (time < 60) {
		str = "less than a minute";
	} else {
		if (time / 60 < 60) {
			str = Math.floor((time / 60)) + " minutes";
		} else {
			if (((time / 60) / 60) < 24) {
				str = Math.floor(((time / 60) / 60)) + " hours";
			} else {
				str = Math.floor((((time / 60) / 60) / 24)) + " days";
			}
		}
	}
	if (str == "1 days") {
		str = "one day";
	}
	if (str == "1 hours") {
		str = "one hour";
	}
	if (str == "1 minutes") {
		str = "one minute";
	}
	return str;
}

function updateEmbedLinkItems(arr) {
	for (i=0;i<arr.length;i++) {
		if (arr[i].className == "embedlink" && arr[i].href) {
			if (arr[i].getAttribute("data-stage") == "0") {
				arr[i].setAttribute("data-stage", "1"); //it has been processed
				if (arr[i].href.indexOf(".jpg") != -1 || arr[i].href.indexOf(".gif") != -1 || arr[i].href.indexOf(".png") != -1) {
					arr[i].innerHTML = "<img src='"+arr[i].href+"' style='max-width:150px;max-height:150px;display:none' onLoad='this.style.display=\"block\"'><br>" + arr[i].innerHTML;
				}
				if (arr[i].href.indexOf("youtube.com/watch?v=") != -1) {
					var vidID = arr[i].href.split("?v=");
					vidID = vidID[1].substring(0,11);
					var id = "ytvid_"+vidID+"_"+Math.floor(Math.random()*9999);
					arr[i].innerHTML = "<div style='margin:4px;display:inline-block;background-image:url(http://img.youtube.com/vi/"+vidID+"/default.jpg);width:120px;height:90px'>\
					<img src='img/play.png' style='position:relative;left:25px;top:10px;'>\
					</div>";
					arr[i].href="javascript:loadYTVideo('"+vidID+"','"+id+"');";
					arr[i].target="_self";
					//arr[i].onclick = "loadYTVideo('"+vidID+"','"+id+"')", false);
					arr[i].id = id;
				}
			}
		}
	}
}
function loadYTVideo(vidID, linkid) {
	var id = "ytvid_frame_"+vidID+"_"+Math.floor(Math.random()*1000);
	e = _g(linkid);
	e.innerHTML = "<div id='"+id+"' class='ytvid'></div>";
	var params = { allowScriptAccess: "always", wmode: "opaque" };
	var atts = { id: "vid_"+id };
	swfobject.embedSWF("http://www.youtube.com/e/"+vidID+"?version=3&enablejsapi=1&playerapiid=vid_"+id, id, "310", "233", "8", null, null, params, atts);
}
function onYouTubePlayerReady(playerID) {
	_g(playerID).playVideo();
}

function updateAutoResizeElements(arr) {
	if (!arr) arr = document.getElementsByTagName("div");
	for (i=0;i<arr.length;i++) {
		if (arr[i].getAttribute("data-distance") != "") {
			var height = (window.innerHeight - getElementXY(arr[i]).y - parseInt(arr[i].getAttribute("data-distance"))) + "px";
			arr[i].style.height = height;
		}
		if (arr[i].className.indexOf("noBubbleScroll") != -1) {
			arr[i].onmousewheel = function () { scrollElement(event,this); }
			arr[i].onmouseover = function () { isScrollHalted=true; }
			arr[i].onmouseout = function () { isScrollHalted=false; }
		}

	}
}
function updateUI() { //interval that scans DOM, updates UI
	var arr = document.getElementsByTagName("*");
	for (i=0;i<arr.length;i++) {
		if (arr[i].className == "time" || arr[i].className.indexOf("time_raw") != -1) {
			arr[i].setAttribute("data-time", parseInt(arr[i].getAttribute("data-time"))+1);
			arr[i].innerHTML = getRelativeTime(parseInt(arr[i].getAttribute("data-time"))) + " ago";
		}
	}
	updateEmbedLinkItems(arr);
	updateAutoResizeElements(arr); //this is last for a reason
}
setInterval("updateUI();", 1000);

//Event subscriptions
document.addEventListener("click", handleClick, false);
document.addEventListener("mousemove", handleMouseMove, false);
document.addEventListener("keydown", handleMouseMove, false); //close enough
document.addEventListener("blur", handleMouseMove, false);
window.onblur = function() { userIsActive = false; }
document.onblur = window.onblur;
document.onmousewheel = handleScroll;

window.addEventListener("hashchange",hashLocationChanged,false);

//Event handlers
function handleClick() {
	try {
	if (canHideNotifications) {
		hideNotificationList();
		if (canHideNotifications) {
			canHideNotifications = false;
		}
	}
	} catch (e) {
	}
}
function handleMouseMove(evt) {
	clearTimeout(inactiveTimeout);
	userIsActive = true;
	inactiveTimeout = setTimeout("userIsActive = false;", 30000);
}
var hashChangedCooldown = false;
function hashLocationChanged() {
	if (hashChangedCooldown) return true; //prevents navigating twice
	if (location.hash) {
		var s = location.hash.substring(1).split("/");

		//check for user pages
		if (location.hash.substring(1,2) === "!") {
			viewInlinePage("/interface/user.php?viewuser=" + s[1]);
			return true;
		}


		for (var i = 0; i < DEFINEDPAGES.length; i++) {
			if (DEFINEDPAGES[i] == s[0]) {
				fetchPage(s[0],s[1]);
				break;
			}
		}
		//nothing found
		if (isInline) {
			originalPage();
		}
	}
}
function handleScroll(e) {
	if (!e) e = window.event;

	if (isScrollHalted) {
		e.returnValue = false;
		e.cancel = true;
		return false;
	}
}

function scrollElement(e, o) {
	o.scrollTop -= e.wheelDeltaY;
	e.returnValue = false;
}
function scrollApproachingBottom() {
	return (document.body.scrollTop > document.documentElement.scrollHeight - (window.innerHeight) - 100);
}
window.addEventListener("load", hashLocationChanged, false); //call this on load so we know where we are

//Text sizing
function sizeComposer(e) {
	if (e.value.length > 3) {
		if (e.value.length < 200) e.style.height = ((e.value.length / 74) * 29) + "px";
	}
}

//Psuedo tab framework
function tabHandler(e) {
	var tabIdent = e.id.split("::");
	var namespace = tabIdent[1].split("-");
	namespace = namespace[0];
	tabIdent = tabIdent[0];

	var arr = document.getElementsByTagName("*");
	for (i=0;i<arr.length;i++) {
		if (arr[i].id.substring(0,tabIdent.length) == tabIdent) {
			if (arr[i].id.indexOf("-") == -1) {
				arr[i].style.display = "none";
			} else {
				arr[i].className = "tab";
			}
		}
	}
	e.className = "tab_selected";
	_g(tabIdent + "::" + namespace).style.display="inline-block";
}

//Page system
var state_isLoading;

function fetchPage(page,args) {

	if (CURRENT_PAGE == "home") PAGE_DEFHEADER = _g("header").innerHTML;
	CURRENT_PAGE = page;

	if (typeof(args) == "undefined") args = "";

	sendData("renders/"+page+".php" + args); //GET the page
	//This will async the request, which is then sent to renderPage by data.js

	//Change the hash, useful for state
	location.hash=page + (args != "" ? "/" + args : "");
	hashChangedCooldown = true;
	setTimeout("hashChangedCooldown = false;",100);

	//Set state to loading
	state_isLoading = true;

	setTimeout('if (state_isLoading) {showContentLoading();}', 500); //show loading, if it takes more than half a second

}

function renderPage(data) {
	state_isLoading = false;
	_g("content").innerHTML = data;
	_g("header").innerHTML = "";

	//Hide the original content
	_g("origcontent").style.display = "none";

	var e = _g("page_" + CURRENT_PAGE);
	if (e)
		highlightThis(e);
}
function originalPage() {
	if (CURRENT_PAGE == "home") return false;
	_g("origcontent").style.display = "inline-block";
	_g("header").innerHTML = PAGE_DEFHEADER;
	_g("content").innerHTML = "";
	CURRENT_PAGE = "home";

	var e = _g("page_" + CURRENT_PAGE);
	if (e)
		highlightThis(e);
	if (isInline) {
		hideInlinePage();
	}
	//go home
	location.hash = "";
	hashChangedCooldown = true;
	setTimeout("hashChangedCooldown = false;",100);

	//Timeline based, but should be safe
	hideTimelineUnreadCount();
}
function showContentLoading() {
	_g("content").innerHTML = "<img src='img/jumpingloader.png' id='jumpingloader'>";
	fadeInElement(_g("content"));

	state_isLoading = true;
	jumperAnimation(0,1);
}
function jumperAnimation(top,dir,cnt) {
	var sleepTime = 15;

	if (dir) {
		top++;
		if (top > 10) {
			dir = 0;
			sleepTime = 300;
		}
	} else {
		top--;
		if (top < 1) {
			dir = 1;
		}
	}

	if (state_isLoading) setTimeout('jumperAnimation('+top+','+dir+');',sleepTime);
	_g("jumpingloader").style.marginTop = top + "px";


}

function viewInlinePage(url) {
	var e = _g("inlinepage");
	e.addEventListener("load", function() { if (e.contentWindow.location == "about:blank") window.history.go(-1); }, true);

	e.src = url;
	e.style.display = "inline-block";
	fadeInElement(e);

	var s = _g("logocontainer").style;
	s.position = "fixed";
	s.left = "0px";
	s.top = "0px";

	s = _g("notificationDot").style;
	s.right = "25px";
	s.position = "fixed";

	document.documentElement.style.overflowY = "hidden";

	if (CURRENT_PAGE == "home") PAGE_DEFHEADER = _g("header").innerHTML;

	CURRENT_PAGE = "__inline";
	isInline = true;

}
function hideInlinePage() {
	isInline = false;
	var e = _g("inlinepage");
	e.src = "about:blank";
	e.style.display = "none";

	_g("logocontainer").style.position = "relative";

	var s = _g("notificationDot").style;
	s.right = null;
	s.position = "absolute";

	document.documentElement.style.overflowY = "scroll";
}

//Menu handler
function highlightToSidelink() {
	var arr = document.getElementsByTagName("*");
	for (i=0;i<arr.length;i++) {
		if (arr[i].className == "sidelink") arr[i].style.opacity = null;
	}
}
function highlightThis(e) {
	highlightToSidelink();
	e.style.opacity = 1;
}

function dobble(statichost, curuser, authkey, func) {
	curUser = curuser;
	staticHost = statichost;
	authKey = authkey;

	func();

	dobblishHome(); //from dobblish.out.html.js

	CURRENT_PAGE = 'home';

	//chat
	setInterval("updateFriendsOnline();", 15000); //15 seconds, because it doesn't update often
	subscribeToEvent(EVENT_ONLINEFRIENDS);


	//timeline
	setInterval('timeline_checkForUpdates();', 3000);
	window.addEventListener("mousewheel", checkTimelinePos, false);
	setInterval("checkUnreadTimelineBlink();", 5000);
	setInterval("checkTimelineActivity();", 100);

	subscribeToEvent(EVENT_TIMELINE); //subscribe to the socket timeline changed event
	subscribeToEvent(EVENT_COMMENTS);

	//notification
	setInterval("checkForNotifications();", 3000);
	setInterval("if (!waitForProcessNot) { processNotifications(); } else { waitForProcessNot = false; }", 1000);
	checkForNotifications_Init(); //Facilitate faster loading
}

//Search
//While not entirely UI related, this data needs to be global to the user
function showSearchPane() {
	html = "<div class='popup_title'>Search for Friends<div class='sideframe_exit'><a href='javascript:;' onClick='hidePopupFrame();' class='close'><img src='img/exit.png'></a></div></div>\
	<br>Enter a username or email address: \
	<form onSubmit='doSearch(query.value);return false;'>\
	<input type='text' name='query'> \
	<input type='button' class='button' value='Search' onClick='doSearch(query.value);'>\
	</form>\
	<br>\
	<div id='searchResults' style='max-height: 240px;overflow:auto'></div>";
	showPopupFrame(html);
}
function doSearch(query) {
	sendData("service/searchquery.php?query="+query);
}
function searchResultCallback(results) {
	if (results[0] == "!") {
		_g("searchResults").innerHTML = "Sorry, no results where found.";
		return false;
	}
	var html = "<table>";
	for (i=0;i<results.length-1;i++) {
		items = results[i].split("~seper~`");
		if (items[0] != "") {
			html += "<tr><td style='width:340px;'><img src='" + items[1] + "' class='avatar'><div class='user' style='height:60px;'><b>"+items[2]+" (" + items[0] + ")</b></div></td><td valign='top'><b>";
			if (items[3] == "0") {
				html += "<input type='button' class='button' value='Request friendship' onClick='sendFriendRequest(\""+items[0]+"\");'>";
			}
			if (items[3] == "1" ) {
				html += "Friend";
			}
			if (items[3] == "U") {
				html += "You";
			}
			if (items[3] == "P") {
				html += "Awaiting response";
			}
			html += "</b></td></tr>";
		}
	}
	html += "</table>";
	_g("searchResults").innerHTML = html;
	fadeInElement(_g("searchResults"));
}
