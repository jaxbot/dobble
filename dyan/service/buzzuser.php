<?php require('../system/service.php');
if (isLoggedIn()) {
	verifyAuth();

	$user = cleanString($_GET['user']);
	if (time() - $_SESSION['buzzUser__'.$user] < 30) {
		echo "~d<title>Buzzer</title><message>You already buzzed this person recently. Give them some time.</message>";
		exit;
	}
	if (time() - $_SESSION['buzzUser__'.$user] > 300 and $_SESSION['buzzRecs'] > 10) {
		//give buzz permission back
		$_SESSION['buzzRecs'] = 0;
	}

	$_SESSION['buzzRecs'] += 1;
	if ($_SESSION['buzzRecs'] > 20) {
		echo "~d<title>Buzzer</title><message>Sorry, you don't have permission to do that anymore. Try again later.</message>";
		exit;
	}
	$_SESSION['buzzUser__'.$user] = time();

	buzzUser($_GET['user']);
	echo "~d<title>Buzzer</title><message>You just buzzed " . getUserDisplayName(htmlspecialchars($_GET['user'])) . ", who will be paged across all connected platforms.</message>";
	updateIsActive(); //Update the chat activity, since we know the user is active.
}
?>