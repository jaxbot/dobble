<?php
	//Controls prompts, tooltips, etc
	//Prompts in use:
	//201 == Halloween Colors
	
	function hasReadPrompt($user, $prompt) {
		$query = "SELECT * FROM statistics_prompts WHERE user='".mysql_real_escape_string($user)."';";
		$result = mysql_query($query);
		if ($result) {
			$data = mysql_fetch_array($result);
			if ($data["flag"] == 1) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	function setPromptFlag($user, $prompt, $flag) {
		$query = "REPLACE INTO `statistics_prompts` (`user`, `prompt`, `flag`)
		VALUE ('".mysql_real_escape_string($user)."',
		'".mysql_real_escape_string($prompt)."',
		'".mysql_real_escape_string($flag)."');";
		return mysql_query($query);
	}
?>