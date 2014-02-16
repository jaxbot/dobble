<?php
/*

     _         _      _      _             _     
    ( )       ( )    ( )    (_ )  _       ( )    
   _| |   _   | |_   | |_    | | (_)  ___ | |__  
 /'_` | /'_`\ | '_`\ | '_`\  | | | |/',__)|  _ `\
( (_| |( (_) )| |_) )| |_) ) | | | |\__, \| | | |
`\__,_)`\___/'(_,__/'(_,__/'(___)(_)(____/(_) (_)
                                                 
The experimental brainchild of Jonathan Warner
*/

/* Turns an HTML file into a JS string */
function makeDobblish($in) {
	$data = file_get_contents($in);
	$data = str_replace("\"", "\\\"", $data);
	$data = str_replace("JS:{{", "\"+", $data);
	$data = str_replace("}}", "+\"", $data);
	$data = "document.write(\"$data\");";
	$data = str_replace("\n", "", $data);
	$data = str_replace("\r", "", $data);
	$data = str_replace("\t", "", $data);
	return $data;
}
function compileDobblish($in, $out, $func) {
	$data = "function $func(){" . makeDobblish($in) . "}";
	file_put_contents($out, $data);
}

//echo makeDobblish("../static/dobblish/home.html");