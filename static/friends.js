
function inviteCallback(result) {
	if (result) {
		var html = "<center><img src='img/dobble/happy.png' style='max-height:200px;'><br>\
		<span style='font-size:25px;'>Invitation sent!</span><br><a href='javascript:showInvitePane();'>Send another</a>\
		</center>";
		document.getElementById("sideframe_content_invite").innerHTML = html;
		fadeInElement(document.getElementById("sideframe_content_invite"));
		invites++;
	} else {
		document.getElementById("invite_callback").innerHTML = "<font color='#d4140f'>Sorry, something didn't work. Check the address and try again.</font>";
	}
}
function invitePending() {
	document.getElementById("invite_callback").innerHTML = "<img src='img/ajax.gif'>";
}
function showInvitePane() {
	var html = "";
	html += "<div class='popup_title'>Invite a friend to Dobble.me\
<div class='sideframe_exit'>\
<a href='javascript:;' onClick='hidePopupFrame();' class='close'>\
<img src='img/exit.png'>\
</a>\
</div>\
</div>\
<br>\
<span style='display:inline-block;padding:4px;width:500px;text-align:center'>\
To invite a friend to Dobble, tell your friend to go to\
<div style='font-size:20px;padding:12px;'>http://dobble.me/invite</div>\
and give your username\
<div style='font-size:25px;padding:12px;'>"+curUser+"</div>\
along with the code\
<div style='font-size:25px;padding:12px;'>"+inviteCode+"</div>\
Your friend will be ready to go within minutes.\
<br><br><br><div style='font-size:10px;text-align:left;'>Did you know: These code phrases are unique to you; only share them with people you \
want to be friends with!</div>";
	showPopupFrame(html);
	
}

function addFriendTile(friend,activity) {
	_g("friendTileContainer").innerHTML += "<div id='friendtile_"+friend+"' class='easeanimate' style='opacity:0;position:relative;display:inline-block;height:105px;width:230px;background-size:230px;background-position:0% 50%;background-image:url("+staticHost+"/avatar/"+friend+");'><div onClick='viewUserProfile(\""+friend+"\");' style='background:rgba(0,0,0,0.5);width:220px;height:25px;display:inline-block;color:white;font-size:20px;font-family:calibri;padding:5px;cursor:pointer'>"+userNames[friend]+"</div><br><span class='activitystatus'>last seen: <span class='time_raw' data-time='"+activity+"'></span></span></div>";
	setTimeout(function(){fadeInElement(_g("friendtile_"+friend));},Math.random() * 1000);
}