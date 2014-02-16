//sideframe.js
//Handles the sideframe opening, closing, nudging, and rendering

//Globals
var sideframeVisible = false;
var numSideframes = 0;
var topMost = 5;
var sf_windows = new Array();
var sf_pos = new Array();
var maxSideframes = 4; //assume 4, will increase/decrease depending on innerWidth
var minSideframeLeft = 10;

//Actions
function addSideframe(id) { //id is used to know which post, chat, etc, it is.
	if (sf_windows[id] == 1) {
		focusSideframe(id, true);
		return false;
	}
	var newele;
	numSideframes++;
	if (numSideframes > maxSideframes) {
		numSideframes -= 1;
		sf_windows[sf_pos[sf_pos.length-1]] = -1;
		newele = _g("sideframe_window_"+sf_pos[sf_pos.length-1]);
		sf_pos[sf_pos.length-1] = id;
	} else {
		
		var sideframe_con = _g('sideframe_container'); 
		newele = document.createElement('div'); 
		sideframe_con.appendChild(newele);
	}
	var html = "";
	
	html += "<div class='sideframe_exit'><a href='javascript:;' onMouseDown='hideSideframe(\""+id+"\");' class='close'><img src='img/exit.png'></a></div>";
	
	html += "<div id='sideframe_content_"+id+"' class='sideframe_content' onMouseDown='focusSideframe(\""+id+"\", true);' onKeyDown='focusSideframe(\""+id+"\", true);' style='height:100%'></div>";
	
	newele.id = "sideframe_window_" + id;
	newele.innerHTML = html;
	newele.className = "sideframe";

	sf_windows[id] = 1;
	return true;
}
function showSideframe(id) {
	_g("sideframe_window_" + id).style.left = minSideframeLeft + "px";
	_g("sideframe_window_" + id).style.display = 'block';
	var o = openSideframes();
	if (o == 1) {
		focusSideframe(id, false);
	} else {
		_g("sideframe_window_" + id).style.left = minSideframeLeft + ((o-1) * 290) + 'px';
	}
	fadeInElement(_g("sideframe_window_"+id),5,4);
	sf_pos[o-1] = id;
	curCommentPane = ""; //Reset the comment pane
	sideframeVisible = true;
	focusSideframe(id, true);
	reorderSideframes();
}
function reorderSideframes() {
	var z = 0;
	console.log("anim");
	moveAnimId++;
	for (i=0;i<sf_pos.length;i++) {
		if (sf_pos[i]) {
			moveElementToX("sideframe_window_"+sf_pos[i], minSideframeLeft + (z * 290));
			z++;
		}
	}
}
function focusSideframe(id, reorder) {
	if (sf_windows[id] == -1) {
		return false;
	}
	if (!_g("sideframe_window_" + id)) {
		return false;
	}
	topMost++;
	_g("sideframe_window_" + id).style.zIndex = topMost;
	
	
	//_g("sideframe_window_" + id).style.backgroundImage = "url('img/blur.png')"; //Disable hue
	
	//Set current sideframe for auto-dismiss
	curAction = id;
}
function hideSideframe(id) {
	var q = 0;
	
	var k;
	for(i=0;i<openSideframes();i++) {
		if (sf_pos[i] == id) {
			k = i;
		}
	}
	sf_pos.splice(k, 1);
	
	for (i=100;i>0;i-=2) {
		q++;
		setTimeout("_g('sideframe_window_"+id + "').style.opacity = " + (i / 100) + ";", q * 4);
	}
	curCommentPane = ""; //Reset the comment pane
	curAction = ""; //Reset the current action (used to auto-dismiss)
	sideframeVisible = false;
	setTimeout("disposeSideframe('"+id+"');", q * 5);

	sf_windows[id] = -1;	

	numSideframes--;
	reorderSideframes();
	
}
function disposeSideframe(id) {
	var sideframe_con = _g('sideframe_container'); 
	sideframe_con.removeChild(_g("sideframe_window_"+id));
}
function nudgeSideFrame(id) {
	_g("sideframe_window_" + id).style.backgroundImage = "url('img/blur_attn.png')";
	var path = [2,4,6,8,10,8,6,4,2,0];
	
	var cur = 15;
	for(var x =0; x< path.length; x++) {
		setTimeout("_g('sideframe_window_"+id + "').style.bottom="+(cur+path[x])+"+'px';", x * 25);
	}
}
function setNudgeSideFrame(id) {
	if(_g('sideframe_window_'+id)) {
		_g("sideframe_window_" + id).style.backgroundImage = "url('img/blur_attn.png')";
	}
}
function openSideframes() {
	var c = 0;
	for (sf in sf_windows) {
		if (sf_windows[sf] == 1) {
			c++;
		}
	}
	return c;
}
function isSideframeOpen(id) {
	for (sf in sf_windows) {
		if (sf == id) {
			return true;
		}
	}
	return false;
}

function showPopupFrame(content) {
	var e = _g("popupFrame");
	e.innerHTML = content;
	fadeInElement(e);
	e.style.opacity=0;
	e.style.display="block";
}
function hidePopupFrame() {
	var e = _g("popupFrame");
	e.style.display="none";
}
//Checks the width and defines how many max sideframes there can be
function updateMaxSideframes() {
	if (window.innerWidth) {
		maxSideframes = Math.floor(window.innerWidth / 260) - 1;
	}
}

//Intervals
setInterval("updateMaxSideframes();", 500);

