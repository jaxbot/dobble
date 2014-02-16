//timeline.js
//Used only on the main interface page
//Contains composer and timeline related code

//Globals
var topElement;
var lastid = "";
var lasttime = 0;
var failedTimer;
var loadingNewPages = false; //Determines whether old posts are being loaded; it's a cooldown
var oldestTime = 0;
var curCommentCountId = 0;
var existantPosts = new Array();
var unreadTimeline = false; //if the page updated while in another tab
var homeUnreadCount = 0; //number of updates made when the timeline was inactive (e.g. the user was on the photos page)

function addTimelineEvent(item,comments,time,append) {
	var eparent = _g('events_container');

	if (item.message == null) return false;
	if (existantPosts[item.id]) return false;

	existantPosts[item.id] = 1;
	var newele = document.createElement('div');

	if (item.time > lasttime) {
		lasttime = item.time;
	}
	if (oldestTime == 0) {
		oldestTime = lasttime;
	}
	if (item.time < oldestTime) {
		oldestTime = item.time;
	}
	var id = item.id;
	newele.id = "event_" + id;

	newele.innerHTML = eventHTML(item,time);

	if (append) {
		eparent.appendChild(newele);
		if (topElement == null) {
			topElement = newele;
		}
	} else {
		newele.style.opacity = 0;
		eparent.insertBefore(newele, topElement);
		fadeInElement(newele);
		topElement = newele;
		updateUI();
	}
	if (comments > 0) {
		showCommentCount(id, comments);
	}

	if (!userIsActive) {
		unreadTimeline = true;
		blinkAttention();
	}
	if (CURRENT_PAGE != "home" && append == 0) {
		homeUnreadCount++;
		showTimelineUnreadCount();
	}

}
function eventHTML(item,time) {
	var avatar = userAvatar(item.from);
	var displayname = userNames[item.from];

	var html = "<div class='event'><img src='"+avatar+"' class='avatar'> <a href='#!/"+item.from+"'><b>"+displayname+"</b></a>";
	if (item.to != "" && item.to != item.from)
		html += " &#x2192; <a href='#!/"+item.to+"'><b>" + item.to + "</b></a>";
	html += "<span class='time' data-time='"+time+"'>"+getRelativeTime(time)+" ago</span>";

	var url = "viewInDetail(\""+item.id+"\",\""+item.from+"\");";

	if (item.type == 1 && item.meta.split(",").length == 1) {
		url = "navigateToPhoto(\""+item.meta+"\",\""+item.from+"\");";
	}
	html+="<div style='cursor: pointer; width: 100%;vertical-align:top;min-height:40px;' onClick='"+url+"'>\
		<span id='commentcount_"+item.id+"' class=\"commentcount\" onClick='"+url+"'>+</span>";


	switch (parseInt(item.type))
	{
		case 6:
			html+="<img src='"+staticHost+"/"+item.message+"' style='max-height:150px;'>";
		break;
		default:
			html+=item.message;
		break;
	}

		html+="</div>";
	/*if (type == 1 && !multiple) {
		html+="<div style='cursor: pointer; width: 100%;vertical-align:top;min-height:40px;' onClick='navigateToPhoto(\""+photo_id+"\",\""+photo_album+"\", \""+from+"\");'>"
		html+="<span id='commentcount_"+id+"' class=\"commentcount\" onClick='navigateToPhoto(\""+photo_id+"\",\""+photo_album+"\", \""+from+"\");'>+</span>";
		html+=event+"";
		html+="</div>";
	}*/
	html += "</div>"; //event class

	return html;
}
function addTimelineFromServer(data,append) {
	var events = data.substring(2).split("~nitem~");
	var items;
	for (i=0;i<events.length-1;i++) {
		events[i] = events[i].replace(/(\r\n|\n|\r)/gm, "");
		//events[i] = events[i].replace("\r", "");
		items = events[i].split("~s~");
		setTimeout("addTimelineEvent("+items[0]+","+items[1]+","+items[2]+","+append+");",10 * i);
	}
	if (events.length > 1 && append) { //first circut prevents loading no posts in an infinite loop
		setTimeout('loadingNewPages = false;', 500);
	}
}
function showTimelineUnreadCount() {
	_g("home_unread").innerHTML = (homeUnreadCount < 10 ? homeUnreadCount : "<b>!</b>");
	_g("home_unread").style.display="inline-block";
}
function hideTimelineUnreadCount() {
	_g("home_unread").style.display="none";
	homeUnreadCount = 0;
}
function showCommentCount(id, cnt) {
	c = _g("commentcount_"+id);
	if (!c) {
		return false;
	}
	if (cnt > 99) {
		cnt = "!";
	}
	c.innerHTML = cnt;
	c.style.visibility = "visible";
}
function hideCommentCount(id) {
	c = _g("commentcount_"+id);
	if (c) {
		c.style.visibility = "hidden";
	}
}
function updateCommentCountCallback(data) {
	var s = data.split(":");
	var id = s[0];
	var events = s[1].split("~n~`");
	var items;
	for (i=0;i<events.length-1;i++) {
		items = events[i].split("~s~`");
		if (items[1] > 0) {
			showCommentCount(items[0], items[1]);
		} else {
			hideCommentCount(items[0]);
		}

	}
	curCommentCountId = id;
}


//Composer
function validateComposer(text) {
	if (text.value != '' && text.style.color != "rgb(152, 152, 152)") {
		failedTimer = setTimeout('composerFailed()', 1000);
		return true;
	} else {
		return false;
	}
}
function composerCallback(shouldclear) {
	clearTimeout(failedTimer);
	_g('composer_status').disabled = false;
	_g('composer_longStory').disabled = false;
	if (shouldclear) {
		_g('composer_status').style.color='#989898';
		_g('composer_status').value = 'What are you up to?';
		hideOverlays();
	}

	timeline_checkForUpdates();

	//for getting started status
	if (typeof(isGettingStarted) == 'undefined') {
	} else {
		_g("hasPosted").src="img/check.png";
	}
}
function showLongStory() {
	var e = _g('composer_status');
	if (e.value.length > 65) {
		//show backdrop
		_g("backdrop").style.display="block";
		_g("textComposer").style.display="block";
		fadeInElement(_g("textComposer"));
		_g("composer_longStory").value = e.value;
		_g("composer_longStory").focus();
		_g("composer_longStory").selectionStart = e.value.length;
	}
}

function updateCharacterCount() {
	var val = _g("composer_longStory").value;
	var e = _g("characterCount");
	e.innerHTML = 500 - val.length;
	if (val.length > 245) {
		e.style.color = "rgb(" + (val.length - 230) + ", 50, 50)";
	} else {
		e.style.color = "#676767";
	}
	_g('composer_status').value = val; //keep them synced

}

function showComposerOptions(show) {
	if (show) {
		_g("composeroptions").style.display="inline-block";
	} else {
		setTimeout(function(){
		_g("composeroptions").style.display="none";},100); //because the click events need to fire
	}
}

function composerFailed() {
	_g('composer_status').disabled = false;
}
function showComposerAjax() {
	_g('composer_ajax').style.display = 'block';
}
function composerSharePictureCallback() {
	_g("timelinePhotoForm_file").value = "";
	hideOverlays();
}

//Updaters
function timeline_checkForUpdates() { //Callback #0
	if (isLegacy) { //only check for updates if not on sockets
		sendData_Callback("service/timeline.php?last="+lasttime+"&cid="+curCommentCountId, 0);
	}
}
function addOlderPosts() { //Callback #3
	sendData_Callback("service/timeline.php?old=1&last="+oldestTime, 3);
}


//Timeline scroll checker (for infinite scroll)
function checkTimelinePos() {
	if (!loadingNewPages && scrollApproachingBottom()) { //second circuit in ui.js
		addOlderPosts();
		loadingNewPages = true;
	}
}

//Check if the user has unread timeline events
function checkUnreadTimelineBlink() {
	if (userIsActive) unreadTimeline = false;
	else
		if (unreadTimeline) blinkAttention();
}

//Check timeline activity, stop blinking if true
function checkTimelineActivity() {
	if (userIsActive) unreadTimeline = false;
}

//Photo uploads for timeline
function showTimelinePhotoForm(message) {
	_g("timelinePhotoForm").style.display="block";
	_g("backdrop").style.display="block";
	_g("timelinePhotoForm_caption").value = message;
}

//Webcam


//Document writes
document.write("<div class='popboxForm' id='webcamPictureForm' style='display:none;height:320px;'>\
<h3>Take a Picture</h3>\
<div style='padding:4px;display:block;width:400px;height:250px;text-align:center;position:relative;margin:auto;'><object id='zflashWebcamUpload' width=333 height=250 type=\"application/x-shockwave-flash\" data=\"flash/webcamUpload.swf\">\
<param name='movie' value='flash/webcamUpload.swf'></param>\
<param name=\"allowscriptaccess\" value=\"always\"></param>\
</object></div><br><center>\
<input type='button' id='takePictureBtn' value='Take Picture' class='button' onClick='webcamTakeSnapshotDelay();'> \
<input type='button' id='uploadPictureBtn' value='Upload Picture' class='button' onClick='webcamUploadSnapshot();'> \
<input type='button' id='resetPictureBtn' value='Try again' class='button' onClick='webcamResetSnapshotForm();'> \
<div id='webcamAjaxStatus'><img src='img/ajax.gif'></div>\
</center>\
</div>");

function showWebcamPictureForm() {
	hideOverlays();
	_g("webcamPictureForm").style.display="inline-block";
	_g("backdrop").style.display="block";
	_g("uploadPictureBtn").style.display="none";
	_g("resetPictureBtn").style.display="none";
	_g("webcamAjaxStatus").style.display="none";
	_g("takePictureBtn").style.display="inline-block";
}
function hideWebcamPictureForm() { //called from flash
	hideOverlays();
}
function hideOverlays() {
	_g("webcamPictureForm").style.display="none";
	_g("timelinePhotoForm").style.display="none";
	_g("backdrop").style.display="none";
	_g("textComposer").style.display="none";
}
function webcamTakeSnapshot() {
	getFlashPictureEle().snapPicture();
}
function webcamTakeSnapshotDelay() {
	setTimeout("webcamTakeSnapshot();", 3000);
	_g("takePictureBtn").disabled = true;
	_g("takePictureBtn").value = "Snapping in 3...";
	setTimeout("_g('takePictureBtn').value = 'Snapping in 2...';", 1000);
	setTimeout("_g('takePictureBtn').value = 'Snapping in 1...';", 2000);
	setTimeout("_g('takePictureBtn').disabled = false;", 3000);
	setTimeout("_g('takePictureBtn').value = 'Take Picture';", 3000);
	setTimeout("_g('takePictureBtn').style.display='none';\
	_g('uploadPictureBtn').style.display='inline-block';\
	_g('resetPictureBtn').style.display='inline-block';\
	", 3000);
}
function webcamResetSnapshot() {
	getFlashPictureEle().resetPicture();
}
function webcamResetSnapshotForm() {
	webcamResetSnapshot();
	_g("uploadPictureBtn").style.display="none";
	_g("resetPictureBtn").style.display="none";
	_g("takePictureBtn").style.display="inline-block";
}
function webcamUploadSnapshot() {
	_g("webcamAjaxStatus").style.display="inline-block";
	_g("uploadPictureBtn").style.display="none";
	_g("resetPictureBtn").style.display="none";
	getFlashPictureEle().uploadPicture();
}
function getFlashPictureEle() {
	if (window["zflashWebcamUpload"]) {
		return window["zflashWebcamUpload"]; //for IE
	}
	return _g("flashWebcamUpload");
}
function flashUploadCompletedCallback(result) {
	if (result == 1) {
		hideWebcamPictureForm();
	}
	if (result != 200 && result != 1) {
		showDialog("Uh oh", "Something went wrong with the upload. Try again.");
		_g("uploadPictureBtn").style.display="inline-block";
		_g("webcamAjaxStatus").style.display="none";
	}
}

//Photoshare (from photoshare.js)
//Allows drag+drop uploads of images

//Globals
var photoshare_num_uploading = 0;
var totalNumUploaded = 0;
//Document writes
document.write("<div id='ajaxUploadIndicator'><img src='img/ajaxlarge.gif'><br>Uploading...</div>");

document.body.addEventListener("dragenter", evtStopHandler, false);
document.body.addEventListener("dragexit", evtStopHandler, false);
document.body.addEventListener("dragover", evtStopHandler, false);
document.body.addEventListener("drop", drop, false);
function evtStopHandler(evt) {
	evt.stopPropagation();
	evt.preventDefault();
}
function drop(evt) {
	evt.stopPropagation();
	evt.preventDefault();

	var files = evt.dataTransfer.files;
	var count = files.length;
	if (count > 0) {
		for (i=0;i<count;i++) {
			//console.log("Dropped: "+files[i].fileName);
			if (files[i].type == "image/png" || files[i].type == "image/jpeg") {
				//console.log("Ready for upload: " + files[i].fileName + " (" + files[i].type + ")");
				ajaxUpload(files[i]);
			} else {
				//console.log("Won't upload: " + files[i].fileName + " (" + files[i].type + ")");
			}
		}
		totalNumUploaded = photoshare_num_uploading;
	}
}
function ajaxSharePhotoUrl(url) {
	sendData("service/ajaxshare.php?type=2&url="+url);
}
function ajaxUpload(file) {
	var con = new XMLHttpRequest();
	con.open("post", "service/ajaxupload.php?a=" + authKey, true);
	con.setRequestHeader("Content-Type", "multipart/form-data");
	con.setRequestHeader("X-File-Name", file.fileName);
	con.setRequestHeader("X-File-Size", file.fileSize);
	con.setRequestHeader("X-File-Type", file.type);
	con.send(file);
	photoshare_num_uploading++;
	con.onreadystatechange = function() {
		if (con.readyState == 4) {
			photoshare_num_uploading--;
		}
	}
}

function updateUploadingProgress() {
	if (photoshare_num_uploading > 0) {
		_g("ajaxUploadIndicator").style.display='block';
	} else {
		if (totalNumUploaded) {
			_g("ajaxUploadIndicator").style.display='none';

			//Post the share info
			sendData("service/ajaxshare.php?type=1&num="+totalNumUploaded);
			totalNumUploaded = 0;
		}
	}
}
setInterval("updateUploadingProgress();", 200);
