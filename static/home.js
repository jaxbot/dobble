//home.js
//Used by the external pages (simple UI stuff)

//Lifesaver
function _g(id) {
	return document.getElementById(id);
}
//Elements
function bob(top, velocity) {
	var e = _g("iceberg");
	var sleep = 50;
	
	top += velocity;
	
	if (top > 150 || top < 147) {
		velocity *= -1;
		sleep = 1000;
	}
		
	e.style.marginTop = top + "px";
	setTimeout("bob(" + top + "," + velocity + ");", sleep);
}