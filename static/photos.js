//photos.js
//Handles all requests from the photos.php page

//Globals
var albumId = "";
var countPictures = 0;

var imgIds = new Array();
var imgDescs = new Array();
var imgUrls = new Array();
var imgUsers = new Array();
var imgThumbs = new Array();

var curPhoto = -1;
var curPhotoUser = "";

//Views

function editAlbum() {
	_g("savebtn2").style.display = "inline-block";
	_g("editBtn").style.display = "none";
	_g("albumContainer").style.display = "none";
	_g("editAlbumLayout").style.display = "block";
	_g("albumTitle").innerHTML = "<input type='text' value='"+_g("albumTitle").innerHTML+"' name='albumNameTxt' id='albumNameTxt'>";
	for (i=0;i<countPictures;i++) {
		_g("photo_desc_"+i).innerHTML = "<textarea name='photoDesc"+i+"' id='photoDesc"+i+"' style='font-family: arial;width: 248px;height: 70px' onClick='if(this.value==\"no description\"){this.value=\"\";}'>"+_g("photo_desc_"+i).innerHTML+"</textarea>";
	}

}
function stopAlbumEdit() {
	_g("savebtn2").style.display = "none";
	_g("albumTitle").innerHTML = _g("albumNameTxt").value;
	_g("editBtn").style.display = "block";
	_g("editAlbumLayout").style.display = "none";
	for (i=0;i<countPictures;i++) {
		_g("photo_desc_"+i).innerHTML = _g("photoDesc"+i).value;
	}
}

function viewAlbumImage(n) {
	_g("albumContainer").style.display = "block";
	_g("albumContainer").style.opacity = 0;
	fadeInElement(_g("albumContainer"));
	renderAlbumImg(n);
}

//Callbacks
function photoUploadCallback(album, user) {
	fetchPage(PAGE_PICTURES, "?album="+album+"&from="+user+"&edit");
}
function getPhotoCommentsCallback(data) {
	var callback = data[0];
	data.shift();
	var comments = data;
	var commentsDisplayed = false;
	var html = "";
	var items;
	for (i=0;i<comments.length-1;i++) {	
		items = comments[i].split("~seper~`");
		if (items[0] != "") {
			html += "<img src='" + items[2] + "' class='avatar'><div style='min-height: 70px'>" + items[1] + " (" + items[4] + " ago) ";
			if (items[0] == curUser || curUser == curPhotoUser) {
				html += "<a href='javascript:deleteComment(\""+items[3]+"\", \""+items[0]+"\");'>X</a>";
			}
			html += "<br>" + items[5] + "<br></div>";
			commentsDisplayed = true;
		}
	}
	
	if (!commentsDisplayed) {
		html += "There are no responses to this photo! Be the first!";
	}
	_g("responseContainer_"+callback).innerHTML = html;
}
function albumUploadProgressCallback(ev) {
	if (ev.lengthComputable) {
		var percent = (ev.loaded / ev.total);
		console.log("Percent: " + percent);
		_g("uploadbar_progress").style.width = (percent * 100) + "%";
	}
}

//Actions
function deleteImage(id, album) {
	sendData("service/deletephoto.php?id="+id+"&album="+album);
	setTimeout("location.href=window.location;",2000);
}
function deleteAlbum(album) {
	if (confirm("You sure you want to delete this album?\r\nThis action cannot be undone.")) {
		sendData("service/deletealbum.php?album="+album);
		setTimeout("location.href='#pictures';",1000);
	}
}

//Property Handlers
function photoFromId(id) {
	for (i=0;i<imgIds.length;i++) {
		if (imgIds[i] == id) {
			viewAlbumImage(i);
			return true;
		}
	}
	return false;
}

function renderAlbumImg(n) {
	var html = "";
	html += "<div style='display:block;height:525px;margin-top:2px;'>";
	if (n > 0) {
		html += "<span style='position:absolute;top:220px;left:10px;z-index:4;'><a href='javascript:renderAlbumImg("+(n-1)+")'><img src='img/left_arrow.png?' style='opacity:0.7'></a></span>";
	}
	if (n < countPictures-1) {
		html += "<span style='position:absolute;top:220px;right:10px;z-index:4;'><a href='javascript:renderAlbumImg("+(n+1)+")'><img src='img/right_arrow.png?' style='opacity:0.7'></a></span>";
	}

	html += "<span style='position:relative;width:700px;height:525px;display:block;background-color:black;'><table width=100% height=100% cellspacing=0 cellpadding=0><tr><td valign=center align=center><img src='"+imgUrls[n]+"' style='max-width: 700px; max-height: 525px;'>";
	
	if (imgDescs[n] == "no description") imgDescs[n] = "";
	html += "<span style='padding:2px;display:block;position:absolute;bottom: 5px;color:white;background:rgba(0,0,0,0.2);'>" + imgDescs[n] + "</span>";
	
	html += "</span>";
	
	html += "</span></td></tr></table>";
	html += "</div><br><div>";
	if (countPictures > 5) {
		var size = 74;
		if (countPictures > 18) {
			size = 37;
		}
		
		for (i=0;i<countPictures;i++) {
			html += "<a href='javascript:renderAlbumImg("+i+")'><img src='"+imgThumbs[i]+"' style='width: "+size+"px;height:"+size+"px;";
			if (i == n) {
				html += "opacity:1";
			} else {
				html += "opacity:0.5";
			}
			html += "'></a> ";
		}
	}
	html += "</div><br>";
	
	html += "<b>Responses</b><br><br><div id='responseContainer_"+n+"'><img src='img/ajax.gif'></div>";
	var id = "photo" + imgIds[n];
	var user = imgUsers[n];
	html += "<form name='commentform_"+id+"'>";
	html += "<textarea name='commentmessage' id='commentmessage_"+id+"' class='picturecommentbox' style='color: #989898' onKeyDown='if (event.keyCode == 13) {if(validateComment(commentmessage)) { postPhotoComment(\""+user+"\", \""+id+"\", commentmessage.value); commentmessage.value=\"\"; }}' onClick='if (this.value == \"(click to add a comment)\") { this.value=\"\";this.style.color=\"black\";}'>(click to add a comment)</textarea><input type='button' value='Post' style='width: 57px;height: 50px;border-style: solid;border-width: 1px;' onClick='if(validateComment(commentmessage)) { postPhotoComment(\""+user+"\", \""+id+"\", commentmessage.value); commentmessage.value=\"\"; return false; }'>";
	html += "</form>";
	html += "</div><br>";
	
	_g("albumContainer").innerHTML = html;
	getPhotoComments(n);
	curPhoto = n;
	curPhotoUser = imgUsers[n];
	curCommentPane = "photo"+imgIds[n];
	curEventUser = imgUsers[n];
	fadeInElement(_g("imgDescriptionBox"));
}

//Updaters
function getPhotoComments(n) {
	if (_g('responseContainer_'+n)) 
	sendData("service/getphotocomments.php?user="+imgUsers[n]+"&id="+imgIds[n]+"&callback="+n);
}
function updatePhotoComments() {
	if (curPhoto != -1) {
		getPhotoComments(curPhoto);
	}
}
function postPhotoComment(user, id, message) {
	sendData("service/postcomment.php?user="+user+"&id="+id+"&type=photo&commentmessage="+encodeURIComponent(message));
}

//Ajax upload
function ajaxUploadAlbumPhotos() {
	try {
		var data = new FormData();
	} catch (e) {
		showDialog("Oh, so about that...", "Looks like you're using an unsupported browser. You should give Chrome, Safari, or Firefox a try.<br>Dobble is standards compliant, and requires the browser to be such.");
		return false;
	}
	var countFiles = _g("uploadform_file").files.length;
	for (numFiles = 0; numFiles < countFiles; numFiles++) {
		data.append("file"+numFiles, _g("uploadform_file").files[numFiles]);
	}
	
	data.append("numFiles", numFiles);
	if (_g("uploadform_newalbumcheck").checked) {
		data.append("isNew", "true");
		data.append("albumname", _g("albumname").value);
	} else {
		data.append("isNew", "false");
		data.append("albumselect", _g("albumselect").value);
	}
	
	var req = new XMLHttpRequest();
	req.upload.addEventListener("progress", albumUploadProgressCallback, false);
	req.onreadystatechange = function()
	{
		if (req.readyState == 4 && req.status == 200) {
			var data = req.responseText;
			if (data.substring(0,1) == "~") {
				var s = data.substring(1).split(",");
				photoUploadCallback(s[0],s[1]);
			} else {
				showUploadError(data);
			}
			console.log(req.responseText);
		} else {
			if (req.readyState == 4) {
				showUploadError(STR_ERROR_PHOTOS_UPLOADFAILED);
				hideUploadProgressBar();
			}
		}
	}
	req.open("POST", "service/uploadphotos.php");
	req.send(data);
	
	showUploadProgressBar();
	hideUploadError();
}
function showUploadProgressBar() {
	_g("uploadform").style.display="none";
	_g("uploadform_progress").style.display="inline-block";
}
function hideUploadProgressBar() {
	_g("uploadform").style.display="inline-block";
	_g("uploadform_progress").style.display="none";
}
function showUploadError(err) {
	_g("upload_error").style.display="block";
	_g("upload_errortext").innerHTML = err;
}
function hideUploadError() {
	_g("upload_error").style.display="none";
}

//Navigation
function navigateToPhoto(id, from) {
	fetchPage(PAGE_PICTURES, '?photo='+id+'&from='+from);
}

//Intervals
setInterval("updatePhotoComments()", 1000);