<?php
/* Main Dobble.me Testing Component
5-3-2012 */

ini_set("display_errors", 0);

function handler($errno, $errstr, $errfile, $errline) {
	global $errorBuf;
	if ($errno != E_NOTICE)
		$errorBuf .= wordwrap($errno . " " . $errfile . ":" . $errline . " " . $errstr . "\n", 70, "\n    ");
}
set_error_handler("handler");
function handleShutdown() {
	echo "shutdown";
	//lol no.
}
register_shutdown_function('handleShutdown');

$tests = array();
$testResults = array();

function startTest($desc) {
	global $tests, $errorBuf;
	$tests[] = $desc;
	
	$errorBuf = "";
	
	printf("%-70s", $desc);	
}

function testResult($res) {
	global $testResults, $errorBuf;
	if ($res) {
		if ($errorBuf == "")
			drawText("[OKAY]", 'GREEN');
		else {
			drawText("[WARN]", 'YELLOW');
			echo " => " . $errorBuf;
			
			$res = 0.5;
		}
	} else {
		drawText("[FAIL]", 'RED');
	}
	$testResults[] = $res;
	
	if ($errorBuf != "")
		echo " => " . $errorBuf;
}


function tallyTestResults() {
	global $testResults, $tests;
	
	drawText("\n\n======== Test Results ========\n", "PURPLE");
	
	$sum = 0;
	foreach ($testResults as $result)
		$sum += $result;
		
	$average = $sum / count($tests);
	$percent = floor($average * 100);
	
	$percentString = colorText(toASCIILetters($percent . "%"), (($average == 1) ? "GREEN" : "YELLOW"));
	
	$info = "Tests: " . count($tests);
	$info .= "\nSuccessful: " . $sum;
	$info .= "\nMemory: " . xdebug_peak_memory_usage();
	$results = combineMultilines(array($percentString, $info));
	
	drawText($results);
	
	echo "\n\n";
	
}
