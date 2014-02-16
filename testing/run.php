<?php
//Run the Dobble.me Testing Service
require("dobble.testing.php");
require("dobble.testing.ui.php");
require("dobble.testing.system.php");
require("dobble.testing.frontend.php");

drawHeader();
drawText("Welcome to the Dobble.me Testing System.\n");

runAllTests();
exit;

drawText("A: All tests");
drawText("A: All tests");
drawText("A: All tests");
drawText("A: All tests");
drawText("A: All tests");

echo "Option: ";
$input = trim(fgets(STDIN));
switch ($input)
{
	default:
	runAllTests();
	break;
}

function runAllTests() {
	
	runSystemTest();
	runFrontendTest();
	//runUserTest();
	runHistoryTest();
	
	tallyTestResults();
}