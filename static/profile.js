//profile.js
//Used by profile.php
//Photo editor, profile updates, settings, etc

//Globals
var settingsInit = false; //Prevents the user from reopening settings and losing changes

var uploadSuccessMessages = new Array("I like it!",
"Nice!",
"Looking good!",
"Tada!",
"¡Me gusta!");

//Timeline
function addProfileTimeline(item,time,comments) {
	var eparent = _g('profiletimeline');
	var newele = document.createElement('div');

	newele.id = "event_profile" + item.id;
	newele.innerHTML = eventHTML(item,time); //stored in timeline.js

	eparent.appendChild(newele);
	if (comments > 0) {
		showCommentCount(item.id, comments);
	}
}

//Picture editor
function showPictureEditor() {
	var html = "<div class='popup_title'>Display Picture</div><div class='sideframe_exit'><a href='javascript:;' onClick='hidePopupFrame();' class='close'><img src='img/exit.png'></a></div>\
	<div style='display:block;margin:auto;width:450px;position:relative'><span style='display:block;padding:4px'>\
			<div style='display:block;width:100%;text-align:center;'><img src='"+profile__Avatar+"' id='avatarImg' style='max-width:300px;max-height: 220px;'></div>\
		<div id='pictureEditorForm'><br><b>Change your Picture</b><br><br>\
		<form enctype='multipart/form-data' name='uploadform' method='POST' action='service/changepicture.php?a="+authKey+"' target='datapusher'>\
			<input type='file' name='picture' onChange='prepareUpload();uploadform.submit();'><img id='pictureLoader' src='img/ajax.gif' style='position:relative;display:none'>\
		</form></div>\
		<div id='pictureCallbackContent' style='text-align:center;width:450px;'></div></div></span>";

	showPopupFrame(html);
}
function updatePictureCallback(img,id) {
	_g('avatarImg').src = img;
	_g('avatarImg').setAttribute("data-imgid", id);
	fadeInElement(_g('avatarImg'));
	_g('pictureLoader').style.display="none";
	_g('pictureEditorForm').style.display="none";
	_g("pictureCallbackContent").style.display='inline-block';

	_g("pictureCallbackContent").innerHTML = uploadSuccessMessages[Math.floor(Math.random()*(uploadSuccessMessages.length-1))]+"<br><br>\
	<input type='button' class='button' value='Accept' onClick='saveCurrentPicture();'>\
	<input type='button' class='button' value='Try again' onClick='showPictureEditor();'>";
}
function uploadFailed() {
	showPictureEditor();
}
function prepareUpload() {
	_g('pictureLoader').style.display="inline-block";
	_g('avatarImg').style.opacity=0.7;
}
function saveCurrentPicture() {
	var id = _g('avatarImg').getAttribute("data-imgid");
	sendData("service/changepicture.php?setImg="+id);
	hidePopupFrame();
}

//Views
function editProfile() {
	if (_g("editBtn").value == "Edit") {
		_g("editBtn").value = 'Save';
		_g("bio").innerHTML = "<input type='text' value='' name='editBio' id='editBio'>";
		_g("editBio").value = profile__Bio;

		_g("pref_color").innerHTML = renderColorBoxes() + "<br><span id='advancedEditColor' style='display:none'>Hex color: <input type='text' value='"+profile__Color+"' id='profile__Color' onKeyUp='updateTheme(this.value);'><br>Darker colors match better with Dobble's layout.";

	} else {
		_g("editBtn").value = 'Edit';
		profile__Bio = _g("editBio").value.replace("<", "&lt;").replace(">", "&gt;");
		profile__Color = _g("profile__Color").value;
		_g("bio").innerHTML = profile__Bio;
		sendData("service/updateprofile.php?bio="+profile__Bio+"&color="+encodeURIComponent(profile__Color));
		_g("pref_color").innerHTML = "";

	}

}
function showSettingsPane() {
	setInterval("validateSettings();", 500);

	var html = "";
	html += "<div class='popup_title'>Account Settings<div class='sideframe_exit'><a href='javascript:;' onClick='hidePopupFrame();' class='close'><img src='img/exit.png'></a></div></div><br>";
	html += "<div class='tab_selected' id='tabid_settings::general-tab' onClick='tabHandler(this);'>General</div>\
	<div class='tab' id='tabid_settings::password-tab' onClick='tabHandler(this);'>Password</div>\
	<div class='tab' id='tabid_settings::sms-tab' onClick='tabHandler(this);'>SMS and Notifications</div>";
	html += "<form action='service/updatesettings.php?a="+authKey+"' method='POST' target='datapusher'>\
	<span style='display:inline-block;padding:10px;'><div id='tabid_settings::general'>\
	<table>\
	<tr><td>Name:</td><td><input type='text' value='"+profile__UserDisplayName+"' name='displayName' id='prefForm_displayName'><br /><div id='prefStatusName'></div></td></tr>\
	<tr><td>Email:</td><td><input type='text' value='"+profile__UserEmail+"' name='email' id='prefForm_email'><br /><div id='prefStatusEmail'></div></td></tr>\
	<tr><td>Gender: </td><td><select name='gender'>\
	<option value='0' " + (profile__Gender == '0' ? "selected" : "") + ">(not set)</option>\
	<option value='1' " + (profile__Gender == '1' ? "selected" : "") + ">Male</option>\
	<option value='2' " + (profile__Gender == '2' ? "selected" : "") + ">Female</option>\
	</select>\
	</table>\
	<br><input type='checkbox' name='allowMultiSignOn' id='prefForm_allowMultiSignOn' value='true' " + (profile__allowMultiSignOn == "1" ? "checked" : "") + ">\
	Allow multiple sign on<br>\
	<div style='font-size:11px;padding-left:24px;'>If checked, this allows your account to be logged in from multiple computers at the same time.</div>\
	</div>\
	<div id='tabid_settings::sms' style='display:none'>\
	<input type='checkbox' name='allowText' id='prefForm_allowText' value='true' " + (profile__UserCanText == "1" ? "checked" : "") + "> Allow Dobble.me to text me<br /><br /><div id='cellForm' style='padding-left: 5px; display: " + (profile__UserCanText == "1" ? "block" : "none") + "'>\
	<table>\
	<tr><td>Carrier:</td><td><select name='carrier'>\
	<option value='none' " + (profile__UserCarrier == "" || "none" ? "selected" : "") + ">(none)</option>\
	<option value='att' " + (profile__UserCarrier == "att" ? "selected" : "") + ">AT&T</option>\
	<option value='tmobile' " + (profile__UserCarrier == "tmobile" ? "selected" : "") + ">T-Mobile</option>\
	<option value='sprint' " + (profile__UserCarrier == "sprint" ? "selected" : "") + ">Sprint</option>\
	<option value='verizon' " + (profile__UserCarrier == "verizon" ? "selected" : "") + ">Verizon</option>\
	</select></td></tr>\
	<tr><td>Phone number:</td><td><input type='text' value='" + (profile__UserPhone) + "' name='phone' id='prefForm_phone'> <div id='prefStatusPhone' style='height: 16px; width: 9px; background-color: #cc0000; text-align: center; border-radius: 3px; color: white; display: none;'></div></td></tr>\
	</table>\
	</div></div>\
	<div id='tabid_settings::password' style='display:none'>\
	\
	<table>\
	<tr><td>Current Password:</td><td><input type='password' name='curPassword'></td></tr>\
	<tr><td>New Password:</td><td><input type='password' name='newPW1' id='prefForm_pw1'></td></tr>\
	<tr><td>Confirm Password:</td><td><input type='password' name='newPW2' id='prefForm_pw2'></td></tr>\
	</table></div>";
	html += "<br><br><input type='submit' class='button' value='Save' id='saveBtn'></form></span><div id='prefStatus'></div>";
	showPopupFrame(html);
}

//Validators
function validateSettings() {
	var err = false;

	var name = _g("prefForm_displayName").value;
	_g("prefStatusName").innerHTML = "";
	_g("prefStatusEmail").innerHTML = "";
	_g("prefStatusPhone").style.display = "none";
	if (name.length < 3) {
		_g("prefStatusName").innerHTML = "Your name is too short.";
		err = true;
	}
	if (!name.match(/^[a-zA-Z ]+$/)) {
		_g("prefStatusName").innerHTML = "Your name contains some invalid characters.";
		err = true;
	}
	if (name.indexOf("  ") != -1) {
		_g("prefStatusName").innerHTML = "Your name contains some invalid characters.";
		err = true;
	}
	if (name.length > 30) {
		_g("prefStatusName").innerHTML = "Your name is too long.";
		err = true;
	}
	var email = _g("prefForm_email").value;
	if (!email.match(/^[a-zA-Z0-9.]+[a-zA-Z0-9]+@[a-zA-Z0-9]+[a-zA-Z0-9.]+.[a-zA-Z0-9]+[a-zA-Z0-9.]+[a-zA-Z0-9]$/)) {
		_g("prefStatusEmail").innerHTML = "Email address is not valid.";
		err = true;
	}
	if (_g("prefForm_allowText").checked) {
		var phone = _g("prefForm_phone").value;
		phone = phone.replace("(", "");
		phone = phone.replace(")", "");
		phone = phone.replace(" ", "");
		phone = phone.replace("-", "");
		if (!phone.match(/^[0-9]+$/)) {
			_g("prefStatusPhone").innerHTML = "!";
			_g("prefStatusPhone").style.display = "inline-block";
			err = true;
		}
		if (phone.length != 10) {
			_g("prefStatusPhone").innerHTML = "!";
			_g("prefStatusPhone").style.display = "inline-block";
			err = true;
		}
		_g("cellForm").style.display = "block";
	} else {
		_g("cellForm").style.display = "none";
	}
	var e = _g("saveBtn");
	if (err) {
		e.disabled = true;
		e.style.opacity = 0.5;
	} else {
		e.disabled = false;
		e.style.opacity = 1;
	}

}

//Callbacks
function prefCallBack(n) {
	var d = _g("prefStatus");
	if (n == 1) {
		d.innerHTML = 'Saved!';
	}
	if (n == -1) {
		d.innerHTML = 'The two passwords did not match. Try again.';
	}
	if (n == -2) {
		d.innerHTML = 'Sorry, your current password does not match the one on file. Try again.';
	}
}
