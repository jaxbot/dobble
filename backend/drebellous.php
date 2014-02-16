<?php

/* Drebellous 
 * Dobble websocket backend
 */

$DEBUGMODE = true;
$SAVELOG = false;
$LOGPATH = "./";

$address = "0";
$port = 443;

echo("Drebellous\n");
echo("It is currently " . date("m-d-y h:i:s a") . " with a slight chance of no storms.\n");
echo "\n";

//requires
require("drebellous.includes.php");
require("drebellous.backend.php");

p("Server starting.");

$users = array(); //list of connected users
//Create server
$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($server, $address, $port); //Bind
socket_listen($server, 20); //Listen

p("Listening...");

$select_sockets = array($server);

$gc_cycle = 0;

//Benchmark
/*for ($i=0;$i<200;$i++) {
	$users[$i] = new SocketUser;
	$users[$i]->username = ($i < 100 ? "jaxbot" : "test");
	$users[$i]->isAuth = true;
	$users[$i]->events = "~sn,t,c,s";
	$users[$i]->timeline_lasttime = time();
}*/

//Main loop
while (1) {
	//$start = microtime(true);
    $toselect = $select_sockets; //because this is modified
	//if ($toselect =="") { $select_sockets = array($server); }

	socket_select($toselect, $write = null, $except = null, 1);
	if ($toselect) {
    foreach ($toselect as $socket) {
        if ($socket == $server) {
            $client = socket_accept($server);
            if ($client === false) {
                p("Failed to accept.");
            } else {

                if ($DEBUGMODE) {
                    socket_getpeername($client, $addr);
                    p("Connecting client " . $addr);
                } else {
                    p("Connecting client...");
                }
                connectClient($client);
            }
        } else {
            $bytes = socket_recv($socket, $buffer, 1024, 0);
            if ($bytes == 0) {
                p("Disconnecting client (bytes 0)");
                disconnectClient($socket);
            } else {
                $user = lookupUser($socket);
                if (!$user->hasHandshook) {
                    handshake($user, $buffer);
                } else {
                    processMessage($user, $buffer);
                }
            }
        }
    }
	}
    foreach ($users as $user) {
        handleEvents($user);
    }

	//Clear the cache, as it's only meant to be used once per cycle
	//otherwise, stale data may be served
	//Data is "stale" after 5 cycles
	//The optimization purpose here allows multiple clients to use the same data,
	//i.e. if people are friends with one people who updates his status, all friends receive
	//cached data
	if ($gc_cycle > 5) {
		unset($CACHE_USER_PROFILE);
		unset($CACHE_DISPLAYNAME);
		unset($CACHE_THUMBNAIL);
		$gc_cycle = 0;
	}
	$gc_cycle++;
	usleep(10000);

	//Reconnect if we've lost the connection
	if (!mysql_ping()) {
		mysql_pconnect($MYSQL_DB_HOST, $MYSQL_DB_USER, $MYSQL_DB_PW);
		mysql_select_db($MYSQL_DB);
	}


	//p("Cycle: " . (microtime(true) - $start));
}

function processMessage($user, $buffer) {
    $msg = decodePacket($buffer);

    if ($GLOBALS['DEBUGMODE']) {
        p("Received: " . $msg);
    }
    if ($user->isAuth) {
        $msgs = explode("~", $msg);

        foreach ($msgs as $msg) {
            $header = substr($msg, 0, 1);

            switch ($header) {
                case "s": subscribeEvents($user, substr($msg, 1));
                    break;
                case "u": unsubscribeEvent($user, substr($msg, 1));
                    break;
                case "t": $user->timeline_lasttime = substr($msg, 1);
                    break;
				case "U":
					if (substr($msg, 1, 1) == "0") {
						//unsubscribe from chat
						$index = array_search(substr($msg, 2), $user->subscribed_chats);
						array_splice($user->subscribed_chats, $index, 1);
						p($user->username . ">> Unsubscribed to chat");
					}
					if (substr($msg, 1, 1) == "1") {
						//unsubscribe from comment stream
						$index = array_search(substr($msg, 2), $user->subscribed_comments);
						array_splice($user->subscribed_comments, $index, 1);
						p($user->username . ">> Unsubscribed to comment");
					}
					break;
				case "C":
					if (substr($msg, 1, 1) == "0") {
						//subscribe to chat
						$data = substr($msg, 2);
						list($usr, $tme) = explode(",", $data);
						$id = getChatId($user->username, $usr);
						array_push($user->subscribed_chats, $id);
						$user->subscribed_chats_with[$id] = $usr;
						$user->subscribed_chat_times[$id] = $tme;
						p($user->username . ">> Subscribed to chat: $usr ($id)");
					}
					if (substr($msg, 1, 1) == "1") {
						//subscribe to comment stream
						$id = substr($msg, 2);

						array_push($user->subscribed_comments, $id);

						//reset last count
						$user->subscribed_comments_counts[$id] = -1;
						p($user->username . ">> Subscribed to comments: ($id)");
					}
					break;
            }
        }
    } else {

        if (substr($msg, 0, 1) == "%") {

            p("Attempting auth: " . $msg);

            if (attemptAuth($msg)) {

                $user->isAuth = true;

                $msg = substr($msg, 1); //remove first character

                list($username) = explode(":", $msg);

                $user->username = $username;
                p("Successful auth. Welcome, $username");

                //Send a message informing this new news (used as a request for subscribes)
                send($user->socket, "@");
            } else {
                p("Auth failed. Killing connection.");
                disconnectClient($user->socket);
            }
        } else {
            p("No auth and not auth. Killing connection.");

            disconnectClient($user->socket);
        }
    }
}

?>
