//firsttime.js
//Used only for the first time wizard.
function wizardStep(n) {
	switch (n) {
		case 1:
			document.getElementById("page1").style.display = "none";
			document.getElementById("page2").style.display = "block";
		break;
		case 2:
			document.getElementById("page2").style.display = "none";
			document.getElementById("page3").style.display = "block";
		break;
		case 3:
			document.getElementById("page3").style.display = "none";
			document.getElementById("page4").style.display = "block";
		break;
		case 4:
			document.getElementById("page4").style.display = "none";
			document.getElementById("page5").style.display = "block";
		break;
		case 5:
			document.getElementById("page5").style.display = "none";
			document.getElementById("page6").style.display = "block";
		break;
	}
}
function checkName(name) {
	document.getElementById("prefStatusName").innerHTML = "";
	var err = false;
	if (name.length < 3) {
		document.getElementById("prefStatusName").innerHTML = "Your name is too short.";
		err = true;
	}
	if (!name.match(/^[a-zA-Z ]+$/)) {
		document.getElementById("prefStatusName").innerHTML = "Your name contains some invalid characters.";
		err = true;
	}
	if (name.indexOf("  ") != -1) {
		document.getElementById("prefStatusName").innerHTML = "Your name contains some invalid characters.";
		err = true;
	}
	if (name.length > 30) {
		document.getElementById("prefStatusName").innerHTML = "Your name is too long.";
		err = true;
	}
	if (err) {
		document.getElementById("namebutton").disabled = true;
	} else {
		document.getElementById("namebutton").disabled = false;
	}
}
function checkPhone(phone) {
	document.getElementById("prefStatusPhone").innerHTML = "";
	var err = false;
	if (!phone.match(/^[0-9]+$/)) {
		document.getElementById("prefStatusPhone").innerHTML = "Phone number invalid. Please enter your ten-digit phone number with no dashes or spaces.";
		err = true;
	}
	if (phone.length != 10) {
		document.getElementById("prefStatusPhone").innerHTML = "Phone number invalid. Please enter your ten-digit phone number with no dashes or spaces.";
		err = true;
	}
	if (err) {
		document.getElementById("phonebutton").disabled = true;
	} else {
		document.getElementById("phonebutton").disabled = false;
	}
}
function inviteCallback(result) {
	if (result) {
		document.getElementById("invite_callback").innerHTML = "<font color='#22d40f'>Sent successfully!</font>";
	} else {
		document.getElementById("invite_callback").innerHTML = "<font color='#d4140f'>Sorry, something didn't work. Check the address and try again.</font>";
	}
}
function invitePending() {
	document.getElementById("invite_callback").innerHTML = "<img src='img/ajax.gif'>";
}