<?php
	function hasSentInvitationRequest($email) {
		$query = "SELECT * FROM statistics_reqinv WHERE email='".mysql_real_escape_string($email)."';";
		$result = mysql_query($query);
		$data = mysql_fetch_array($result);
		if ($data["email"] == $email) {
			return true;
		} else {
			return false;
		}
	}
	function isEligibleInvitation($email) {
		$query = "SELECT * FROM statistics_reqinv WHERE email='".mysql_real_escape_string($email)."';";
		$result = mysql_query($query);
		$data = mysql_fetch_array($result);
		if ($data["status"] == "1") {
			return true;
		}
		return false;
	}
	function addInvitationRequest($email) {
		$query = "REPLACE INTO `statistics_reqinv` (`email`, `time`) VALUE ('".mysql_real_escape_string($email)."','".time()."')";
		return mysql_query($query);
	}