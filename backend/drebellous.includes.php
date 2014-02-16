<?php
//Miss Kay.Socket backend server
//Specifically designed for use with the Dobble.me backend
//Includes

//Allow the script to run forever
set_time_limit(0);

//Save log, if set
if ($SAVELOG) {
	//Open the log stream
	$log_stream = fopen($LOGPATH . date("m_d_y") . ".log", "at");
	echo "Saving logs.\n";
}
if ($DEBUGMODE) {
    error_reporting(E_ALL ^ E_NOTICE);
} else {
    error_reporting(E_NONE);
}

//Output/log
function p($msg) {
	global $SAVELOG, $log_stream;
	$msg = date("m-d-y h:i:s a") . "> " . $msg;
	echo $msg . "\n";
	if ($SAVELOG) {
		fwrite($log_stream, $msg . "\n");
	}
}

//====================================
//Socket related functions

//Connect a client (add it to the socket list)
function connectClient($socket) {
	global $select_sockets, $users;
	$user = new SocketUser;
	
	$user->isAuth = false;
	$user->socket = $socket;
	$user->hasHandshook = false;
	array_push($users,$user);
	array_push($select_sockets,$socket);
}
//Disconnect a client and remove it from the socket/user list
function disconnectClient($socket) {
	global $select_sockets, $users;
	
	//delete user entry
	for ($i=0;$i<count($users);$i++) {
		if ($users[$i]->socket == $socket) {
			array_splice($users, $i, 1);
			break;
		}
	}
	
	//find entry
	$n = array_search($socket, $select_sockets);
	
	//close socket
	socket_close($socket);
	
	//delete entry
    	if ($n >= 0) {	
		array_splice($select_sockets, $n, 1);
	}
}

//Send data to a client
function send($client,$msg) {
	$buffer = encodePacket($msg);
	socket_write($client,$buffer,strlen($buffer));
}

//Perform a handshake (complies with RFC6455)
function handshake($user,$buffer){
	list ($resource, $host, $connection, $version, $origin, $key, $upgrade) = getheaders ($buffer);
	
	p("Shaking hands...");
	
	$reply  = "HTTP/1.1 101 Switching Protocols\r\n".
	"Upgrade: {$upgrade}\r\n" .
	"Connection: {$connection}\r\n" .
	"Sec-WebSocket-Accept: " . calculateKey($key) . "\r\n" .
	"\r\n";
			
	// Closes the handshake
	socket_write ($user->socket, $reply, strlen ($reply));

	$user->hasHandshook = true;  
}
//Calculate the key
function calculateKey($key) {
	$key .= "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
	$key = sha1 ($key);
	$key = pack ('H*', $key);
	$key = base64_encode ($key);
	return $key;
}
//Get headers from the handshake 
function getheaders ($buffer) {
	$resource = $host = $connection = $version = $origin = $key = $upgrade = null;
			
	preg_match ('#GET (.*?) HTTP#', $buffer, $match) && $resource = $match[1];
	preg_match ("#Host: (.*?)\r\n#", $buffer, $match) && $host = $match[1];
	preg_match ("#Connection: (.*?)\r\n#", $buffer, $match) && $connection = $match[1];
	preg_match ("#Sec-WebSocket-Version: (.*?)\r\n#", $buffer, $match) && $version = $match[1];
	preg_match ("#Sec-WebSocket-Origin: (.*?)\r\n#", $buffer, $match) && $origin = $match[1];
	preg_match ("#Sec-WebSocket-Key: (.*?)\r\n#", $buffer, $match) && $key = $match[1];
	preg_match ("#Upgrade: (.*?)\r\n#", $buffer, $match) && $upgrade = $match[1];
	
	return array ($resource, $host, $connection, $version, $origin, $key, $upgrade);
}

//Decode hybi17
function decodePacket($buffer) {
	global $masks, $initFrame;
	
	$len = $masks = $data = $decoded = null;

	$len = ord ($buffer[1]) & 127;

	if ($len === 126) {
		$masks = substr ($buffer, 4, 4);
		$data = substr ($buffer, 8);
		$initFrame = substr ($buffer, 0, 4);
	}
	else if ($len === 127) {
		$masks = substr ($buffer, 10, 4);
		$data = substr ($buffer, 14);
		$initFrame = substr ($buffer, 0, 10);
	}
	else {
		$masks = substr ($buffer, 2, 4);
		$data = substr ($buffer, 6);
		$initFrame = substr ($buffer, 0, 2);
	}
	p("Length: $len");
	
	for ($index = 0; $index < $len; $index++) {
		$decoded .= $data[$index] ^ $masks[$index % 4];
	}
		
	return $decoded;
}

//Decode a hybi17 packet, turn into array of packets
function decodePacketA($buffer) {
	global $masks, $initFrame;
	/*$len = $data = $decoded = $index = null;
	$len = $msg[1] & 127;
	
	if ($len === 126) {
		$masks = substr ($msg, 4, 4);
		$data = substr ($msg, 8);
		$initFrame = substr ($msg, 0, 4);
	}
	else if ($len === 127) {
		$masks = substr ($msg, 10, 4);
		$data = substr ($msg, 14);
		$initFrame = substr ($msg, 0, 10);
	} else {
		$masks = substr ($msg, 2, 4);
		$data = substr ($msg, 6);
		$initFrame = substr ($msg, 0, 2);
	}
	
	for ($index = 0; $index < strlen ($data); $index++) {
		$decoded .= $data[$index] ^ $masks[$index % 4];
	}*/
	$len = $masks = $data = $decoded = null;

	$len = ord ($buffer[1]) & 127;

	if ($len === 126) {
		$masks = substr ($buffer, 4, 4);
		$data = substr ($buffer, 8);
		$initFrame = substr ($buffer, 0, 4);
	}
	else if ($len === 127) {
		$masks = substr ($buffer, 10, 4);
		$data = substr ($buffer, 14);
		$initFrame = substr ($buffer, 0, 10);
	}
	else {
		$masks = substr ($buffer, 2, 4);
		$data = substr ($buffer, 6);
		$initFrame = substr ($buffer, 0, 2);
	}
	p("Length: $len");
	
	$data_array = array();
	
	if (strlen($data) > $len) {
		//$data_array = decodePacket(substr($data, $len));
	}
	for ($index = 0; $index < $len; $index++) {
		$decoded .= $data[$index] ^ $masks[$index % 4];
	}
	
	array_push($data_array, $decoded);
		
	return $data_array;
}
//Encode a hybi17 packet
function encodedfPacket($msg) {
	global $masks, $initFrame;
	
	$index = $encoded = null;
		
	for ($index = 0; $index < strlen ($msg); $index++) {
		$encoded .= $msg[$index] ^ $masks[$index % 4];
	}
		
	$encoded = $initFrame . $masks . $encoded;
	
	return $encoded;	
}
function encodePacket($buffer) {
	global $masks, $initFrame;
	
	$b1 = 0x80 | (0x01 & 0x0f);
	
	$len = strlen($buffer);
	if ($len <= 125) {
		$header = pack("CC", $b1, $len);
	} elseif ($len > 125 and $len < 65536) {
		$header = pack("CCn", $b1, 126, $len);
	} else {
		$header = pack("CCN", $b1, 127, $len);
	}
	$buffer = $header.$buffer;
	return $buffer;
}
//lookup user based on socket
function lookupUser($socket) {
	global $users;
	for ($i=0;$i<count($users);$i++) {
		if ($users[$i]->socket == $socket) {
			return $users[$i];
		}
	}
}

//SocketUser class
class SocketUser {
	var $username;
	var $isAuth;
	var $socket;
	var $hasHandshook;
	var $events;
    var $timeline_lasttime;
    var $timeline_cache;
    var $comment_streams;
    var $comment_lastsum;
	var $notification_lasttime;
	var $subscribed_chats = array();
	var $subscribed_chats_with = array();
	var $subscribed_chat_times = array();
	var $subscribed_chat_statuses = array();
	var $subscribed_comments = array();
	var $subscribed_comments_counts = array();
	var $onlineFriendCache;
	var $profileCache;
	var $threscounter; //this counts up to 5 and is used for various things
}
?>