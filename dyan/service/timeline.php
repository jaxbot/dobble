<?php require('../system/service.php');
if (isLoggedIn()) {

	//get the data passed via GET
	$lasttime = $_GET['last'];
	$isOld = isset($_GET['old']);
	
	//If there are new history items, fetch, iterate, and output them
	if (getLastHistoryTime($_SESSION['P_usr'], true) > $lasttime or $isOld) {
		
		if ($isOld) {
			$historyitems = getHistoryItems($_SESSION['P_usr'], true, 30, $lasttime); //higher limit
		} else {
			$historyitems = getHistoryItems($_SESSION['P_usr'], true, 20);
		}
		$data = "";
		$c = 0;
		
		$comments = getMassCommentCount(getCommentIdsArray($historyitems));
		
		foreach ($historyitems as $historyitem) {
			$output = false;
			if ($historyitem->time > $lasttime and $isOld == false) 
				$output = true;
		
			if ($historyitem->time < $lasttime and $isOld == true) 
				$output = true;
			
			if ($output) {
				$historyitem->message = renderEvent($historyitem);
				$cnt = $comments[$historyitem->id];
				if (!$cnt) $cnt = 0;
				$json = json_encode($historyitem);
				$data .= $json . "~s~" . $cnt . "~s~" . (time() - $historyitem->time) . "~nitem~";
				$c++;
			}
		}
		if ($isOld) {
			echo "~A" . $data;
		} else {
			echo "~a" . $data;
		}
	}
	
	//No new items, so we'll use this as a combined poll for comment counts
	$lastsum = $_GET['cid'];
	
	$historyitems = getHistoryItems($_SESSION['P_usr'], true, 20); 
	$streams = getCommentIdsArray($historyitems);
	$sum = 0;
		
	foreach ($streams as $stream) {
		$comments = getCommentStream($stream);
		$cnt = count($comments);
		
		$output .= "$stream~s~`$cnt~n~`";
		
		$sum += $cnt;
	}

		
	if ($sum == $lastsum) { //identical, nothing changed
		exit;
	}
		
	$output = str_replace("photo", "", $output);
	$output = "~c$sum:" . $output;
	echo $output;


} else echo "~L";
?>