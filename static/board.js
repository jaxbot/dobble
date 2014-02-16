/* Physical board properties
For arragnement of the skeuomorphic board items */

//The current position Y for col 0, 1. This is used as addBoardItem is called
var curBoardY = [0,0];
//Determines which column is current (0 or 1)
var curBoard = 0;
//The number of items on the board, used to iteration and placement
var boardItems = 0;
//The last time on the board, used for AJAX updates
var boardLastTime = 0;
//Whether or not the board should prepend. This is false until the positionBoardItems call is made
var boardPrepend = 0;
//Whether or not the board is hidden
var boardHidden = false;
//The current looked up item
var boardCurItem = null;
var boardResponsesTimer = null;
var boardLastResponseTime = 0;

var boardHandwritingTypes = ["Crafty Girls", "Reenie Beanie", "Just Me Again Down Here", "Handlee","Cedarville Cursive","Rock Salt","Nothing You Could Do"];

function addStickyNote(id, message, from, time, color, handwriting) {

	var note = document.createElement("div");

	note.id = "stickynote_" + id;
	note.className = "stickynote";
	note.style.backgroundImage = "url(img/sn_" + color + ".png)";
	note.style.fontFamily = getFontFromHandwriting(handwriting);

	var info = "<div class='noteinfo'><a href='/"+from+"'>" + from + "</a>, <span class='time_raw' data-time='"+time+"'></span>";

	note.innerHTML = message + info;

	if (message.length > 195)
	note.style.fontSize = "20px";

	addBoardItem(note);

}

function addPhoto(id, img, from, time) {
	var photo = document.createElement("div");

	photo.id = id;
	photo.innerHTML = "<a href='/#pictures/?photo="+id+"&from="+from+"'><img src='"+img+"' class='boardphoto'></a><br><a href='/"+from+"'>" + from + "</a>, <span class='time_raw' data-time='"+time+"'></span>";
	photo.className = "boardphoto";

	addBoardItem(photo);

}

function addNote(id, message, from, time, handwriting) {
	var note = document.createElement("div");

	note.id = "note_" + id;
	note.className = "note";
	note.style.backgroundImage = "url(img/paper.png)";
	note.style.fontFamily = getFontFromHandwriting(handwriting);

	var info = "<div class='noteinfo'><a href='/"+from+"'>" + from + "</a>, <span class='time_raw' data-time='"+time+"'></span>";

	note.innerHTML = message + info;

	if (message.length > 175)
	note.style.fontSize = "20px";

	addBoardItem(note);

}

function addPost(id, message, time) {
	var note = document.createElement("div");

	note.id = "post_" + id;
	note.className = "post";
	note.style.fontFamily = getFontFromHandwriting(boardUserHandwriting);
	var info = "<br><br><span class='posttime'><span class='time_raw' data-time='"+time+"'></span></span>";

	note.innerHTML = "<img src='" + curuserAvatar + "' class='postphoto'>" + message + info;


	addBoardItem(note);
}

function addBoardItem(element) {
	boardItems++;

	var board = _g("board");

	if (boardPrepend)
		board.insertBefore(element, board.childNodes[0]);
	else
		board.appendChild(element);

	element.style.opacity = 0;

	if (element.className == "boardphoto")
	{
		if (boardItems % 2)
		element.className += " rotateleft";
		else
		element.className += " rotateright";
	}

	element.className += " easeanimate";
	element.onclick = function() { event.cancelBubble = true; hideBoard(); showBoardResponses(element); };

	setTimeout(function() { fadeInElement(element); }, 10 + boardItems * 100);

}

function positionBoardItems() {
	if (boardHidden)
		return false;

	var board = _g("board");
	var children = board.childNodes;

	curBoard = 0;
	curBoardY = [0,0];

	for (var i = 0; i < children.length; i++)
	{
		var height = 390;
		if (children[i].className.indexOf("post") != -1)
		height = children[i].offsetHeight + 80;
		if (children[i].className.indexOf("note") == 0)
		height = 350;


		children[i].style.top = curBoardY[curBoard] + "px";
		children[i].style.left = (curBoard ? 410 : 0) + "px";
		children[i].style.opacity = 1;

		curBoardY[curBoard] += height;
		curBoard = (curBoard ? 0 : 1);
	}

	boardPrepend = true; //finished initial placement
	boardItems = 0;

	//refresh time, etc
	updateUI();
}

function hideBoard() {
	var board = _g("board");
	var children = board.childNodes;

	for (var i = 0; i < children.length; i++)
	{
		if (i % 2) {
			children[i].style.left = "900px";
		} else {
			children[i].style.left = "-800px";
		}
		children[i].style.opacity = 0.3;
	}
	boardHidden = true;
}
function restoreBoard() {
	//alert("pre");
	top.handleClick();
	//alert("psot");
	boardHidden = false;
	positionBoardItems();
	_g("boardResponseContainer").innerHTML = "";


}
window.onclick = restoreBoard;
function showBoardResponses(element) {
	element.style.left = "50px";
	element.style.opacity = 1;

	var id = element.id.split("_");
	id = id[1];

	boardCurItem = element;

	_g("boardResponseContainer").style.top = element.style.top;
	_g("boardResponseContainer").style.opacity = 0;
	var html = "<div class='post' id='boardResponse'>" + makeCommentBox(id,boardUser,"width:345px") + "</div>";
	_g("boardResponseContainer").innerHTML = html;

	boardLastResponseTime = -1;

	clearInterval(boardResponsesTimer);
	boardResponsesTimer = setInterval("getBoardResponses('"+id+"');", 1000);

	getBoardResponses(id);
}
function getBoardResponses(id) {
	sendData("service/boardresponses.php?user="+boardUser+"&id=" + id + "&last=" + boardLastResponseTime);
}
function addBoardResponse(item) {
	var note = document.createElement("div");

	note.id = "comment_" + item.id;
	note.className = "post";

	var info = "<br><br><br><span class='posttime'><span class='time_raw' data-time='"+item.rtime+"'></span></span>";

	note.innerHTML = "<img src='" + item.avatar + "' class='postphoto'>" + item.message + info;
	note.style.fontSize = "15px";
	note.style.position = "relative";
	fadeInElement(note);
	_g("boardResponseContainer").insertBefore(note,_g("boardResponse"));

	boardLastResponseTime = item.time;
}
function boardResponseFinish() {
	if (boardLastResponseTime == -1)
	boardLastResponseTime = 0;
	updateUI();
	fadeInElement(_g('boardResponseContainer'));
}
function updateBoard() {
	if (boardHidden) return false;
	sendData("service/board.php?user="+boardUser+"&last="+boardLastTime);
}
function addOlderBoardItems() {
	if (boardHidden) return false;
	loadingNewPages = true;
	boardPrepend = false;
	sendData_Callback("service/oldboard.php?user="+boardUser+"&last="+oldestTime, 3);
}
function checkBoardPos() {
	if (!loadingNewPages && scrollApproachingBottom())
		addOlderBoardItems();
}

//UI stuff (perhaps this could be moved)

function getFontFromHandwriting(n) {
	return "'" + boardHandwritingTypes[n] + "', cursive";
}


/* Post items
Here is where sticky notes, notes are posted. */

var curColor = "y";
function setColor(c) {
	curColor = c;
	_g("stickynoteEditor").style.background = "url(img/sn_" + c + ".png)";
}

//Global post stuff
function handleScroll(element)
{
	element.scrollTop = 0;
}
function handleKeyPresses(element,max) {
	setTimeout(function(){handleScroll(element)},0);
	if (element.value.length > 160)
	element.style.fontSize = "20px";
	else
	element.style.fontSize = "30px";
	element.value = element.value.substring(0, max);
}


//Show

function showStickyNote() {
	_g("noteEditor").style.display = "none";
	_g("stickynoteEditor").style.display = "block";
	_g("stickynoteCanvas").style.display = "none";
	_g('stickynoteTAEditor').style.display = "inline-block";
	_g('stickynoteTAEditor').style.fontFamily = getFontFromHandwriting(userHandwriting);
	_g('stickynoteTAEditor').style.fontSize = "30px";
	_g('stickynoteTAEditor').value = "";
	_g('stickynoteTAEditor').focus();
}

function showNote() {
	_g("stickynoteEditor").style.display = "none";
	_g("noteEditor").style.display = "block";
	_g('noteTAEditor').style.fontFamily = getFontFromHandwriting(userHandwriting);
	_g('noteTAEditor').style.fontSize = "30px";
	_g('noteTAEditor').value = "";
	_g('noteTAEditor').focus();
}

function showLoggedOutNote() {
	_g("noteEditor").style.display = "block";
	_g('noteTAEditor').style.fontFamily = getFontFromHandwriting(userHandwriting);
	_g('noteTAEditor').style.fontSize = "30px";
	_g('noteTAEditor').value = "";
	_g('noteTAEditor').focus();
}

//Canvas stuff
var canvasClicksX = new Array();
var canvasClicksY = new Array();
var canvasIsDragging = new Array();
var canvasIsMouseDown = false;
var canvasIsEraser = false;
var canvaslastX = 0;
var canvaslastY = 0;
var context;

function showCanvas() {

	_g('stickynoteTAEditor').style.display = "none";

	var canvas = _g("stickynoteCanvas")
	canvas.width = canvas.width; //clear?
	canvas.style.display = "block";
	canvas.onmousedown = canvasMouseDown;
	canvas.onmousemove = canvasMouseMove;
	canvas.onmouseup = canvasMouseUp;
	canvas.onmouseout = canvasMouseUp;
	canvas.oncontextmenu = function(){return false;}
	context = canvas.getContext("2d");


	canvasClicksX = new Array();
	canvasClicksY = new Array();
	canvasIsDragging = new Array();
	canvasIsMouseDown = false;

	resetContext();

	canvaslastX = 0;
	canvaslastY = 0;
}
function canvasMouseDown(e)
{
	var x = -1 * (getElementXY(_g("stickynoteCanvas")).x - e.clientX);
	var y = -1 * (getElementXY(_g("stickynoteCanvas")).y - e.clientY);

	console.log(y);

	canvaslastX = x - 1;
		canvaslastY = y - 1;
	canvasIsMouseDown = true;
	if (e.button == 2) {
		canvasIsEraser = true;
	}
	else {
		canvasIsEraser = false;
		resetContext();

		drawPoint(x,y);
	}

}
function canvasMouseMove(e)
{
	if (!canvasIsMouseDown)
	return false;
	var x = -1 * (getElementXY(_g("stickynoteCanvas")).x - e.clientX);
	var y = -1 * (getElementXY(_g("stickynoteCanvas")).y - e.clientY);

	if (canvasIsEraser) {
		context.globalCompositeOperation = "destination-out";
		context.strokeStyle = "rgba(0,0,0,1)";
		context.lineWidth = 20;
	} else {
		resetContext();
	}
	if (canvaslastX != 0) {
		drawPoint(x,y);
	}
	canvaslastX = x;
	canvaslastY = y;

}
function canvasMouseUp(e)
{
	canvasIsMouseDown = false;
}
function resetContext() {
	context.strokeStyle = "#323232";
	context.lineJoin = "round";
	context.lineWidth = 8;
	context.globalCompositeOperation = "source-over";
}
function drawPoint(x,y) {
	context.beginPath();
	context.moveTo(canvaslastX, canvaslastY);
	context.lineTo(x,y);
	context.closePath();
	context.stroke();
}

//Communication
function postStickyNote() {
	_g("stickynoteEditor").style.display = "none";
	if (_g("stickynoteCanvas").style.display != "none")
		return postStickyNoteImage();
	sendData("service/postboard.php?to="+boardUser+"&type=0&color="+curColor+"&message=" + encodeURIComponent(_g('stickynoteTAEditor').value));
}
function postStickyNoteImage() {
	var data = _g("stickynoteCanvas").toDataURL("image/png");
	var req = new XMLHttpRequest();
	req.open("POST","service/postboard.php?to="+boardUser+"&type=1&color="+curColor+"&a="+authKey, false);
	req.setRequestHeader("Content-type", "application/upload");
	req.send(data);
}
function postNote() {
	_g("noteEditor").style.display = "none";
	var anon = "";
	if (_g("isAnon").getAttribute("data-checked") != "false")
		anon = "&anon";

	sendData("service/postboard.php?to="+boardUser+"&type=2&message=" + encodeURIComponent(_g('noteTAEditor').value) + anon);
}


//Following information

function boardFollowUpdate(state) {
	if (state == 1) {
		_g("followButton").style.display="none";
		_g("unFollowButton").style.display="inline-block";
	} else {
		_g("followButton").style.display="inline-block";
		_g("unFollowButton").style.display="none";
	}
}



//Edit board

function boardShowEdit() {
	_g("boardEditButton").style.display = "none";
	_g("settingsPane").style.display = "inline-block";
	_g("settingsPane").style.left = "0px";
	_g("settingsPane").style.opacity = 1;

	_g("sp_form").action = "service/boardsettings.php?a=" + authKey;

	var handwritinghtml = "";
	for (var i = 0; i < boardHandwritingTypes.length; i++) {
		handwritinghtml += "<div style='font-size: 20px; font-family: " + boardHandwritingTypes[i] + "'><input type='radio' id='handwriting"+i+"' name='handwriting' value='"+i+"'";
		if (boardUserHandwriting == i)
			handwritinghtml += " checked";
		handwritinghtml += "><label for='handwriting" + i + "'>  " + curUser + "</label></div><br>";

	}
	_g("sp_handwriting").innerHTML = handwritinghtml;
}
function boardSave() {
	_g("boardEditButton").style.display = "inline-block";
	_g("settingsPane").style.left = "-250px";
	_g("settingsPane").style.opacity = 0;
}

function updateBgColor(color) {
	_g("sp_bgColor").style.backgroundColor = color;
	document.body.style.backgroundColor = color;
}
