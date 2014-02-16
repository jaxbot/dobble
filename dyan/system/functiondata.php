<?php
	//functiondata.php
	//Contains functions for handling common string tasks
	
	//Split delimiters
	$sep = chr(20).chr(100)."::".chr(100).chr(20);
	$split = chr(30)."::".chr(100);
	
	//Returns a string with harmful characters stripped
	function cleanString($str) {
		$str = htmlspecialchars($str);
		$str = str_replace("\\", "", $str);
		$str = str_replace("/", "", $str);
		$str = str_replace("\"", "", $str);
		$str = str_replace("'", "", $str);
		$str = str_replace("!", "", $str);
		$str = str_replace("@", "", $str);
		$str = str_replace("#", "", $str);
		$str = str_replace("$", "", $str);
		$str = str_replace("%", "", $str);
		$str = str_replace("^", "", $str);
		$str = str_replace("&", "", $str);
		$str = str_replace("*", "", $str);
		$str = str_replace("(", "", $str);
		$str = str_replace(")", "", $str);
		$str = str_replace("-", "", $str);
		$str = str_replace("=", "", $str);
		$str = str_replace("+", "", $str);
		$str = str_replace("[", "", $str);
		$str = str_replace("]", "", $str);
		$str = str_replace("{", "", $str);
		$str = str_replace("}", "", $str);
		$str = str_replace("|", "", $str);
		$str = str_replace(",", "", $str);
		$str = str_replace(".", "", $str);
		$str = str_replace("<", "", $str);
		$str = str_replace(">", "", $str);
		$str = str_replace("", "", $str);
		$str = str_replace(":", "", $str);
		$str = str_replace(";", "", $str);
		$str = str_replace("?", "", $str);
		$str = str_replace("~", "", $str);
		$str = str_replace("`", "", $str);
		return $str;
	}
	//OBSOLETE: Just use the built in PHP function "stripslashes"
	function fixSlashBug($str) {
		$str = str_replace("\\\"", "\"", $str);
		$str = str_replace("\\'", "'", $str);
		return $str;
	}
	
	function jsClean($str) {
		//Cleans up stuff that normally breaks JS scripts
		$str = str_replace("\r", "", $str);
		$str = str_replace("'", "\\'", $str);
		$str = str_replace("\n", "", $str);
		return $str;
	}
	function removeNL($str) {
		//Deletes the annoying newline bugs
		$str = str_replace("\r", "", $str);
		$str = str_replace("\n", "", $str);
		return $str;
	}
	
	//Returns a string with the link content turned into HTML formatted links
	function parse_links($str, $escapeembed = false) {
		$GLOBALS['LINK_ESCAPE_EMBED'] = $escapeembed;
		$str = preg_replace_callback("{\\b((https?://)|(www\.))((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})(:[0-9]{1,5})?(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?(?=[?.!,;:\"]?(\s|$))}", "handleLink", $str);
		return $str;
	}
	function handleLink($s) {
		global $IS_MOBILE, $LINK_ESCAPE_EMBED;

		$url = $s[0];
		if (strlen($url) < 23) {
			$txt = $url;
		} else {

			if ($IS_MOBILE) {
				$txt = $s[2] . substr($s[4], 0, 15) . substr($s[6], 0, 5) . "...";
				$txt = str_replace("www.", "", $txt);
				$txt = str_replace("http://", "", $txt);
				if (strlen($url) > 23) {
					$txt = substr($txt, 0, 23);
				}
			} else {
				$txt = $s[2] . substr($s[4], 0, 25) . substr($s[6], 0, 15) . "...";
			}
			
		}
		if (stripos($url, "://") === false) {
			$url = "http://" . $url; //assume http, since no protocol was given
		}
		if (!$LINK_ESCAPE_EMBED) {
			$str = '<a href="'.$url.'" target="_blank" rel="nofollow" class="embedlink" data-stage=0>'.$txt.'</a>';
		} else { //used in source links, etc. don't JS process
			$str = '<a href="'.$url.'" target="_blank" rel="nofollow">'.$txt.'</a>';
		}
		return $str;
	}
	
	//Turns a PHP time value into relative time
	function getRelativeTime($time) {
		$distance = time() - $time;
		if ($distance < 60) {
			$distance_str = $distance . " seconds";
		} else {
			
			if ($distance / 60 < 60) {
				$distance_str = $distance / 60 . " minutes";
			} else {
				if (($distance / 60) / 60 < 24) { 
					$distance_str = (($distance / 60) / 60) . " hours";
				} else {
					$distance_str = ((($distance / 60) / 60) / 24) . " days";
				}
			}
		}
		
		$distance_str_round = explode(".", $distance_str);
		$distance_str_round = $distance_str_round[0];
		if ($distance_str != $distance_str_round) {
			$unit = explode(" ", $distance_str);
			$unit = $unit[1];
		
			if ($distance_str_round == 0) {
				$distance_str_round = "less than";
			}
		
			if ($unit != "days") {
				$distance_str = $distance_str_round . " " . $unit;
			} else {
				$distance_str = $distance_str_round . " " . $unit;
			}
		}
		if ($distance_str == "1 days") {
			$distance_str = "one day";
		}
		if ($distance_str == "1 hours") {
			$distance_str = "one hour";
		}
		if ($distance_str == "1 minutes") {
			$distance_str = "one minute";
		}
		return $distance_str;
	}
	
	//Clears up the headers to make the transfer smaller
	function cleanHeaders() {
		header("Date: ");
		header("Vary: ");
		header("Expires: ");
		header("Cache-Control: ");
		header("Pragma: ");
	}
	
?>