//HTML
//This file stores common HTML (duh)


//Globals

//data pusher
document.write("<iframe id='datapusher' name='datapusher' src='about:blank' style='display:none'></iframe><div id='ajaxerrorbox'></div>");

//notifications
document.write("<div id='notificationContainer' onClick='canHideNotifications = false;setTimeout(\"canHideNotifications = true;\", 200);'><div id='notificationList'></div><a href='javascript:;' onClick='dismissAll();hideNotificationList();'>Dismiss all</a></div>");

//sideframes
document.write("<div id='sideframe_container'></div>");

//corner stuff
document.write("<img src='img/happy.png' alt='legacybird' id='legacybird' style='display:none;opacity:0.1;position:fixed;right:10px;bottom:-65px;max-height:100px;'>\
<div id='popupNotification' onClick=''></div>\
<div id='popupFrame'></div>");

//bug
document.write("<span style='position:fixed;top:7px;right:10px;'><a href='javascript:showBugReport();' style='color:white;opacity:0.5'>Feedback</a></span>\
<div id='bugReporter' style='font-size:11px;padding:3px;border-radius:5px;display:none;width:200px;height:240px;background:white;border: solid 1px #676767;position:fixed;right:10px;top:20px;'>\
<h3>Bug Report</h3>\
<form action='service/reportbug.php' method='POST' target='datapusher'>\
Describe the problem:\
<br>\
<textarea name='description' style='font-family:arial;width:190px;height:50px;'></textarea><br>\
What were you doing? Any other details? (Be specific)<br>\
<textarea name='details' style='font-family:arial;width:190px;height:50px;'></textarea><br>\
<input type='checkbox' checked name='include'> Include a page snapshot<br><br>\
<textarea name='snapshot' style='display:none'></textarea>\
<input type='submit' value='Send report' class='button' onClick='if (include.checked) { snapshot.value = dumpHTML(); }'> <input type='button' class='button' value='Cancel' onClick='hideBugReport();'>\
</form></div>");

//inline pages
document.write("<iframe id='inlinepage'></iframe>");

//load swf object
function addJSElement(file) {
	var e = document.createElement("script");
	e.src = file;
	document.body.appendChild(e);
}
function deferJS(callback) {
	window.addEventListener("load", callback, false);
}
setTimeout("addJSElement('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');",500);

