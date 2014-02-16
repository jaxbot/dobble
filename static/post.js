//post.js
//Comments, view in detail, etc

//Globals
var curCommentPane = ""; //Used to confirm if the current comment pane is to be updated via AJAX, as well as deleting comments
var commentFailedTimer;
var lastCommentCount = new Array(); //Used to update comments
var lastPostedByMe = false; //Used to nudge sideframe

var subscribedComments = new Array();

//Views
function viewInDetail(id, user) {
	if (addSideframe(id)) {
		showSideframe(id); //Prevents reshowing a open sideframe
	}
	document.getElementById("sideframe_content_"+id).innerHTML = '<center><img src="img/ajax.gif"></center>';
	sendData("service/eventlookup.php?user="+user+"&id="+id);
	
	//Auto dismiss notifications
	curAction = id;
	processNotifications();
}

//Callbacks
function viewEventCallback(user, fullname, avatar, event, eventtime, id, rawcomments) {
	curCommentPane = id;
	curEventUser = user;
	if (event == "") {
		document.getElementById("sideframe_content_"+id).innerHTML = "<center><img src='img/error.png'><br />Sorry, that post could not be found. It may have been deleted.</center>";
		return false;
	}
	var html = "<div id='eventpane_"+id+"' style='opacity: 0;'><div style='display:block;height: 30px'><a href='#' onClick='viewUserProfile(\""+user+"\");'><img src='"+avatar+"' class='miniavatar' style='width:25px;height:25px;'></a>"
	html += "<span style='cursor:pointer;font-size:18px' onClick='viewUserProfile(\""+user+"\");'>" + fullname + "</span><br /><div style='padding-top: 5px'>";
	
	html += "</div></div>";
	html += event + "<br><font size=1>" + eventtime + " ago ";
	if (user == curUser) {
		//Show delete button
		html += "- <a href='javascript:;' onClick='confirmDeleteEvent(\""+id+"\");'>delete</a>";
	}
	html += "</font>";
	
	html += "<hr style='width:88%'>";

	html += "<div id='commentcontainer_"+id+"' class='commentcontainer noBubbleScroll' data-distance='70'>\
	<div class='unconfirmedcomment_"+id+"'><img src='img/ajax.gif'></div>";
	
	lastCommentCount[id] = -1;
	
	//Add comment AJAX form
	html += "</div></span><span style='bottom:15px;position:absolute'><form name='commentform_"+id+"'>";
	html += "<textarea name='commentmessage' id='commentmessage_"+id+"' class='commentbox' onKeyDown='if (event.keyCode == 13) {if(validateComment(commentmessage)) { postComment(\""+user+"\", \""+id+"\", commentmessage.value); commentmessage.value=\"\"; }}' onClick='if (this.value == \"Write a response...\") { this.value=\"\";this.style.color=\"black\";}'>Write a response...</textarea>";
	html += "</form></span>";
	html += "</div>";
	document.getElementById("sideframe_content_"+id).innerHTML = html;
	fadeInElement(document.getElementById("eventpane_"+id));
	updateUI();

	if (isLegacy) {
		sendData("service/geteventcomments.php?user="+user+"&id="+id);
	} else {
		if (subscribedEvents[EVENT_COMMENT] != EVENT_COMMENT) {
			subscribeToEvent(EVENT_COMMENT);
			sendSubscriptions();
		}
		subscribeToComment(id);
	}
	setInterval('updateCommentStream("'+user+'", "'+id+'");', 800);
	
	//updateAutoResizeElements();
}
function updateCommentsCallback(id, comments) {
	var commentsDisplayed = false;
	var html = "";
	var lastPostedBy = "";
	var items;
	var all = false;
	if (lastCommentCount[id] > comments.length) {
		all = true; //a comment was deleted (or something), so reset the whole thing
	}
	html += "<table style='width:225px;'>";
	for (i=0;i<comments.length-1;i++) {	
		items = comments[i].split("~seper~`");
		//[comment_from, commment_fromname, comment_avatarsrc, comment_id, comment_time, comment_msg] = items;
		if (items[0] != "" && !_g("comment_"+items[3]) || all) {
			html += renderComment(items[0],items[1],items[2],items[3],items[4],items[5]);//comment_from, commment_fromname, comment_avatarsrc, comment_id, comment_time, comment_msg);
			commentsDisplayed = true;	
			lastPostedBy = items[0];
		}
	}
	html += "</table>";
	
	if (all) {
		document.getElementById("commentcontainer_"+id).innerHTML = html;
	} else {
		var ele = document.createElement("div");
		ele.innerHTML = html;
		_g("commentcontainer_"+id).appendChild(ele);
	}
	hideUnconfirmedComments(id);
	updateUI();
	if (lastCommentCount[id] != -1) {
		if (comments.length != lastCommentCount[id]) {
			if (curUser != lastPostedBy) {
				nudgeSideFrame(id);
			}
			document.getElementById("commentcontainer_"+id).scrollTop = 10000000;
		}
	}
	lastCommentCount[id] = comments.length;
}
function renderComment(comment_from, comment_fromname, comment_avatarsrc, comment_id, comment_time, comment_msg) {
	//[comment_from, commment_fromname, comment_avatarsrc, comment_id, comment_time, comment_msg] = items;
	var html = "";
	html += "<tr>";
	html += "<td style='width:35px;vertical-align:top;'><img src='" + comment_avatarsrc + "' class='miniavatar'></td>";
	html += "<td>";
	html += "<div id='comment_"+comment_id+"'>";
	//html += "<span style='font-size: 11px;color: #989898;padding:0px;display:inline-block; width: 235px'>";
	html += "<a href='javascript:viewUserProfile(\"" + comment_from + "\");' style='font-weight:bold'>" + comment_fromname + "</a>";
			
	html += "<br>" + comment_msg + "<br>";
	html += "<span style='font-size:9px;color:#989898'>";
	if (comment_from == curUser || curUser == curEventUser) {
		html += "<a href='javascript:confirmDeleteComment(\""+comment_id+"\", \""+comment_from+"\");''>delete</a>";
	}		
	if (comment_time >= 0) {
		html += "<span id='timebox_comment_"+comment_id+"' class='time_raw' style='float:right' data-time='"+comment_time+"'>"+getRelativeTime(comment_time)+" ago</span>";	
	}
	html += "<br><br></span>";
	html += "</div></td></tr>";
	
	return html;
}
function addUnconfirmedComment(from, message, id) {
	var ele = document.createElement("div");
	ele.className = "unconfirmedcomment_"+id;
	
	var html = "<table style='width:225px;'>"+renderComment(from,getUserDisplayName(from),userAvatar(from),"unconfirmed",0,message)+"</table>";
	
	ele.innerHTML = html;
	_g("commentcontainer_"+id).appendChild(ele);
	_g("commentcontainer_"+id).scrollTop = 10000000;
}
function hideUnconfirmedComments(id) {
	var arr = document.getElementsByTagName("div");
	var test = "unconfirmedcomment_"+id;
	for (i=0;i<arr.length;i++) {
		if (arr[i].className == test) {
			arr[i].style.display = "none";
		}
	}
}
function makeCommentBox(id,user,css) {
	
	var html = "<textarea id='commentmessage_"+id+"' data-id='" + id + "' data-user='"+user+"' class='commentbox' \
	onKeyDown='handleCommentEvent(event,this);' \
	onBlur='blurTextClick(this)' \
	onClick='textClickHandle(this)' style='color:#989898;";
	if (typeof(css) != "undefined")
		html += css;
	html += "'>" + STR_WRITECOMMENT + "</textarea>";
	
	return html;
}
function handleCommentEvent(e,o) {
	if (!e)
		e = window.event;
	if (e.keyCode != 13) return false;
	
	if (o.value != STR_WRITECOMMENT && o.value != "") {
		_postComment(o.getAttribute("data-user"), o.getAttribute("data-id"), o.value);
		setTimeout(function() { o.value = ""; }, 10);
	}
}
function postComment(user, id, message) {
	_postComment(user, id, message);
	addUnconfirmedComment(curUser, message.replace(/</g, "&lt;").replace(/>/g, "&gt;"), id);
}
function _postComment(user, id, message) {
	sendData("service/postcomment.php?user="+user+"&id="+id+"&commentmessage="+encodeURIComponent(message));
}
//Actions
function deleteComment(id, from) {
	sendData("service/deletecomment.php?id="+id+"&postid="+curCommentPane+"&user="+curEventUser+"&commentuser="+from);
}
function confirmDeleteComment(id, from) {
	showConfirm("Delete comment?", "Are you sure you wish to permanently delete this comment?", "deleteComment('"+id+"','"+from+"');");
}
function confirmDeleteEvent(id) {
	showConfirm("Delete post?", "Are you sure you wish to permanently delete this post?", "deleteEvent('"+id+"');");
}
function deleteEvent(id) {
	sendData("service/deleteevent.php?id="+id);
	setTimeout("location.href=window.location;", 200);
}

function updateCommentStream(user, id) {
	if (document.getElementById("commentcontainer_"+id) != null) {
		if (isLegacy) {
			sendData_Callback("service/geteventcomments.php?user="+user+"&id="+id+"&lc="+lastCommentCount[id], "comment_"+id);
		}
	} else {
		//It's null, so kill the connection
		if (ajaxCallbacks["comment_"+id] != 2) {
			unsubscribeComment(id);
			ajaxCallbacks["comment_"+id] = 2; //let it know this is intentional, not a d/c error
			if (isLegacy) {
				reqObjects["comment_"+id].abort();
			}
		}
	}
}

//Comments
function validateComment(txt) {
	if (txt.value != '' && txt.value != STR_WRITECOMMENT) {
		commentFailedTimer = setTimeout('commentFailed("'+txt.id+'");', 1000);
		return true;
	} else {
		return false;
	}
}
function commentCallback(id) {
	clearTimeout(commentFailedTimer);
	var e = _g('commentmessage_'+id);
	e.disabled = false;
	e.value = '';
	if (e.focused == false) {
		e.style.color='#989898';
		e.value = STR_WRITECOMMENT;
	}
	lastPostedByMe = true;
}
function commentFailed(id) {
	document.getElementById(id).disabled = false;
}