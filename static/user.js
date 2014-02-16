
//user.js
//Contains general user actions (friend lookup, buzzing, friend requests, etc)

//Globals
var curEventUser = "";
var userNames = new Array();
var staticHost;
var curUser;
var authKey;

function initUsers(myName) {
	userNames[curUser] = myName;
	userNames['anon'] = "Anon";
	//userNames.sort();
	userFriends.sort();
}

function getUserDisplayName(user) {
	if (userNames[user]) {
		return userNames[user];
	} else {
		return user;
	}
}
function userAvatar(user) {
	return staticHost + "/thumb/" + user;
}

//Actions
function viewUserProfile(user) {
	location.href="/#!/" + user;
	return false;
}
function buzzUser(user) {
	sendData("service/buzzuser.php?user="+user);
}
function confirmRemoveFriend(user, displayName) {
	showConfirm("Remove friend?", "Are you sure you want to remove "+user+" from your friends?", "removeFriend(\""+user+"\");");
}
function removeFriend(user) {
	sendData("service/removefriend.php?user="+user);
}
function sendFriendRequest(user) {
	sendData("service/sendfriendrequest.php?user="+user);
}
function friendRequestResponse(from, resp) {
	sendData("service/friendrequestresponse.php?from="+from+"&resp="+resp);
}