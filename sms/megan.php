<?php
//Megan server
//handles SMS requests via email gateway

//Globals
error_reporting(E_ALL ^ E_NOTICE);

$VERSION = "0.3";

$DOMAIN = "{imap.gmail.com:993/imap/ssl}INBOX";
$USERNAME = "txt@dobble.me";
$PASSWORD = "";

//Should it actually connect to the server, or just use a dummy protocol?
$ISDUMMY = false; 

echo "Megan service $VERSION\n";
p("Starting up...");

$MEGAN_STARTTIME = time();

p("Grabbing the protocol...");

require_once("megan.protocol.php");

p("Linking the backend...");

require_once("megan.backend.php");

if ($ISDUMMY) {
	testProtocol();
	exit;
}

//Connect to the inbox
$connection = connectInbox($DOMAIN,$USERNAME,$PASSWORD);

//Main loop
while (1) {

//Fetch emails
$emails = imap_search($connection,"UNSEEN");
if ($emails) {
	
	foreach ($emails as $email) {
		p("Processing message $email...");
		
		list($overview) = imap_fetch_overview($connection,$email,0);
		
		$user = getUserFromGatewayEmail($overview->from);
		if ($user) {
			p("Message from $user");
		} else {
			p("I don't know who this is: ".$overview->from);
			
			sendIDKMessage($overview->from);
			continue; //don't process anything else
		}
		
		$structure = imap_fetchstructure($connection,$email);
		
		$i = 1;
		
		$attachments = null;
		$message = "";
		if ($structure->parts) {
		foreach ($structure->parts as $part) {
			if ($part->disposition == "ATTACHMENT" and $part->subtype == "JPEG") {
				p("Part " . $i . " is a JPG attachment!");
				$attachments[] = $i;
			}
			if ($part->subtype == "PLAIN") {
				$message .= imap_fetchbody($connection,$email,$i);
			}
			$i++;
		}
		} else {
			$message = imap_fetchbody($connection,$email,1);
		}
		
		//In the general case, handle the message
		if (!$attachments) {
			newMessage($user,$message,$overview->from);
		} else {
			//If there's a JPG attachment(s), upload it with the message as the caption
			$photos = array();
			
			foreach ($attachments as $i) {
				$attachment = imap_fetchbody($connection,$email,$i);//,FT_PEEK
				
				$file = "/tmp/volatile_attachment";
				
				//this gets overwritten, so don't get attached.
				file_put_contents($file, base64_decode($attachment));
				
				$photo = handleImageUpload($user, $file, $message);
				if ($photo) $photos[] = $photo;
			}
			$id = getAlbumIdFromName($user, "Feed Images");
			addPhotoHistoryEvent($user, $message, implode(",", $photos), $id, 1); //1 is the flag for mobile
			
		}
		
		
		//print_r($structure);
		//$message = imap_body($connection,$email,2);
		
		echo $email.": ".$message."\n";
		
		
	}
	imap_setflag_full($connection,join($emails,","),"\Seen");
	
	$curTimeout = 0;
} else {
	p("No new messages");
	if ($curTimeout < 20)
		$curTimeout += 1;
}
sleep(5) + $curTimeout + rand(0,5);

//Ping the connection (refreshes it as well as keeps it alive)
//Reconnect if it's gone out
if (!imap_ping($connection)) connectInbox($DOMAIN,$USERNAME,$PASSWORD);
}

imap_close($connection);

function connectInbox($host,$user,$pass,$attempts=0) {
	p("Attempting to connect to as $user...");
	$c = imap_open($host,$user,$pass);
	if (!$c) {
		p("Connect failed!");
		
		if ($attempts < 10) {
			$timeout = (1 + $attempts * $attempts);
			p("Retrying in $timeout...");
			sleep($timeout);
			return connectInbox($host,$user,$pass,$attempts+1);
		} else {
			p("I've tried $attempts times and can't seem to connect. Think I'm just going to die.");
			die("Failed to connect after $attempts attempts.");
		}
	}
	return $c;
}

function p($str) {
	echo date("m-d-y h:i:s a").">> ".$str."\n";
}
?>
