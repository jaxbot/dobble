function dumpHTML() {
	var data = "<!DOCTYPE HTML><html>";
	data += document.head.innerHTML;
	data += "<body>" + document.body.innerHTML + "</body>";
	data += "</html>";
	return data;
}
function showBugReport() {
	_g("bugReporter").style.display="block";
}
function hideBugReport() {
	_g("bugReporter").style.display="none";
}