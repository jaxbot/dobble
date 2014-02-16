<?php
//Megan protocol

//Array to hold contextual conversations
$CONVERSATION = array();

//Array to hold user classes
$USERS = array();

//Array to hold callbacks for users
$FROMADDR = array();

function testProtocol() {
	global $USERS;
	$USERS["jaxbot"] = getUserFromPhone("9046146037");
	while (1) {
		//read a line
		echo "Say> ";
		$input = trim(fgets(STDIN));
		
		//process it
		processMessage($input,getLastMessage("jaxbot"),"jaxbot");
		
	}
}

function newMessage($user,$message,$from) {
	global $FROMADDR;
	//strip some footer stuff
	list($message,$crap) = explode("--\r\n", $message);
	$message = str_replace("\n", "", $message);
	$message = str_replace("\r", "", $message);
	
	$FROMADDR[$user] = $from;
	processMessage($message,$user);
}

//Here we handle the actual messages
function processMessage($input,$user) {
	global $CONVERSATION, $USERS;
	
	$last = getLastMessage($user);
	
	//if (!is_array($CONVERSATION[$user])) $CONVERSATION[$user] = array();
	
	$CONVERSATION[$user][] = $input;
	
	list($command,$data,$to) = interpretMessage($input,$last,$user);
	
	switch ($command) {
		case CHELLO:
			respond($user, "Hello! You've reached the Dobble.me texting service.");
			//TODO: add personal lookup, notification list, etc
		break;
		case CWHO:
			respond($user, "I'm Megan, the Dobble.me texting service! I'm currently ".$GLOBALS['$VERSION']." builds old. I'm here to assist you :)");
		break;
		case CUNDO:
			list($lastcommand) = interpretMessage($last);
			if ($lastcommand == CSETSTATUS) {
				deleteHistoryItem(getLastPost($user)->id);
				respond($user, "Oops, sorry about that. All gone now (:");
			} else {
				respond($user, "Not entirely sure what you're undoing here...");
			}
		break;
		case CSETSTATUS:
			addHistoryEvent($user, htmlspecialchars($data), 1); //1 is the flag for mobile
			respond($user, "Status shared ;) \n(You can also reply with undo if you didn't mean to do that.)");
		break;
		case CHELP:
			respond($user, "Try these:\nhello\nhelp");
		break;
		//Admin
		case CADMIN_UPTIME:
			respond($user, `uptime` . "\nLocally: " . getRelativeTime($GLOBALS['MEGAN_STARTTIME']));
		break;
		case CADMIN_STAT:
			respond($user, "Users: ".count($USERS). " Convos: " . count($CONVERSATION));
		break;
		case CADMIN_SERVER:
			respond($user, "My mem usage: ".memory_get_usage(true)."\nServer: ".`cat /proc/meminfo | grep Mem`);
		break;
		case CADMIN_HELP:
			respond($user, "uptime stat server kill");
		break;
		default: //CUNKNOWN
			respond($user, "Not sure I understand you ): \nTry saying help, or status followed by a message.");
		break;
	}
}

function interpretMessage($input,$last=null,$user=null) {
	$words = explode(" ", $input);
	
	$command = CUNKNOWN;
	
	switch (strtolower($words[0])) 
	{
		case "hello":
		case "hi":
		case "hey":
		case "sup":
			$command = CHELLO;
			break;
		case "who":
			if (stripos($input, "are you") !== false or strtolower($input) === "who")
			$command = CWHO;
			break;
		case "undo":
			$command = CUNDO;
		break;
		case "status":
			$command = CSETSTATUS;
			$data = substr($input, 0, 7);
		break;
		case "help":
		case "?":
			$command = CHELP;
		break;
		//Admin commands
		case "admin":
			if ($words[1] == "alpha0000") {
				//otherwise itll still be unknown
				switch (strtolower($words[2])) {
					case "uptime":
						$command = CADMIN_UPTIME;
					break;
					case "server":
						$command = CADMIN_SERVER;
					break;
					case "stat":
						$command = CADMIN_STAT;
					break;
					case "kill":
					case "die":
					case "stop":
						die("I was told to stop.");
						exit;
					break;
					case "help":
						$command = CADMIN_HELP;
					break;
				}
			}
		break;
		default:
			if (count($words) > 3) {
				$command = CSETSTATUS;
				$data = $input;
			}
		break;
	}
	return array($command, $data, $to);
}

function respond($user, $message) {
	global $FROMADDR;
	
	p("Reply to $user: $message");
	if (!$GLOBALS['ISDUMMY']) {
		//mail(str_replace("txt", "mms", $FROMADDR[$user]), "", $message, "From: txt@dobble.me\r\n");
		sendto($FROMADDR[$user], $message);
		p("Sent");
	}
	
}

function sendto($to,$message) {
	if (strlen($message) > 160) {
		p("WARNING: message > 160 (".strlen($message)."): $message");
	}
	
	//$pid = pcntl_fork();
	//if ($pid) {
		//I'm the parent. go about my business
	//} else {
		$to = str_replace("txt.att", "mms.att", $to);
		passthru("/usr/bin/php ext.mail.php ".escapeshellarg($to)." ".escapeshellarg($message)." 2>&1 &");
		//exit(0);
	//}
}

function sendIDKMessage($to) {
	$message = "Hello! I'm the Dobble.me texting service. I'd love to talk, but I don't know you! Get more info at http://dobble.me/\nI'll see you around :)";
	p("Sending: $message");
	sendto($to,$message);
}

function getLastMessage($user) {
	global $CONVERSATION;
	return @end($CONVERSATION[$user]);
}

function getUserFromGatewayEmail($email) {
	list($number) = explode("@", $email);
	
	return getUserFromPhone($number);
}
?>