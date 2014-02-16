//gettingstarted.js
//Imported when tasks have not been completed for getting started

//Globals
var isGettingStarted = true;

function showGettingStartedSideframe(hasBio, hasFriends, hasPosted) {
	var id = "gettingstarted";
	if (addSideframe(id)) {
		showSideframe(id); //Prevents reshowing a open sideframe
	}
	var html = "<div class='sideframe_title'>Getting Started</div><br><br>\
	<span style='display:inline-block;padding:4px;'><span style='font-size: 16px;'>First and foremost, welcome to Dobble! Complete this basic tasks to get started:<br><br>\
	\
	<img src='img/"+(hasPosted ? "check" : "pending")+".png' id='hasPosted' class='icon'> Share an update with your friends.<br>\
	<img src='img/"+(hasBio ? "check" : "pending")+".png' id='hasBio' class='icon'> Customize your profile.<br>\
	<img src='img/"+(hasFriends ? "check" : "pending")+".png' id='hasAddedFriends' class='icon'> Add a friend or two.<br>\
	</span>\
	<br><span style='font-size:14px;'><b>Tips</b></span><br><span style='padding:3px;display:inline-block'>\
	To add a friend, head over to <a href='friends.php'>Friends</a> and press <i>Add a Friend</i>. You may also invite an individual to join by clicking <i>Invite a friend</i><br>\
	<br>To share a message, just click the text box under <i>Home Feed</i> and type what you would like to say, then press <i>Share</i>!<br>\
	</span></span>";
	document.getElementById("sideframe_content_"+id).innerHTML = html;
}
function showAllDoneSideframe() {
	var id = "alldone";
	if (addSideframe(id)) {
		showSideframe(id); //Prevents reshowing a open sideframe
	}
	var html = "<center><img src='img/dobble/happy.png' style='max-width: 200px'><br />\
	<span style='font-size: 18px'><b>All Done!</b></span><br />You're all set and ready to go. Enjoy!</center>\
	";
	document.getElementById("sideframe_content_"+id).innerHTML = html;
	//setTimeout("if (document.getElementById('sideframe_content_"+id+"')) hideSideframe('"+id+"');", 6000);
}
