<?php
/*
Part of the Dobble.me testing system
Renders the initial screen, handles some functions
*/

$COLORS = array('PURPLE' => "0;35", 'RED' => '0;31', 'GREEN' => '0;32', 'YELLOW' => '1;33', 'DEFAULT' => '0');

$LETTERS = array('1' => ' d888   
d8888   
  888   
  888   
  888   
  888   
  888   
8888888',
'2' => ' .d8888b.  
d88P  Y88b 
       888 
     .d88P 
 .od888P"  
d88P"      
888"       
888888888 ',
'3' => ' .d8888b.  
d88P  Y88b 
     .d88P 
    8888"  
     "Y8b. 
888    888 
Y88b  d88P 
 "Y8888P"',
'4' => '    d8888  
   d8P888  
  d8P 888  
 d8P  888  
d88   888  
8888888888 
      888  
      888  ',
'5' => '888888888  
888        
888        
8888888b.  
     "Y88b 
       888 
Y88b  d88P 
 "Y8888P" ',
'6' => ' .d8888b.  
d88P  Y88b 
888        
888d888b.  
888P "Y88b 
888    888 
Y88b  d88P 
 "Y8888P" ',
'7' => '8888888888 
      d88P 
     d88P  
    d88P   
 88888888  
  d88P     
 d88P      
d88P ',
'8' => ' .d8888b.  
d88P  Y88b 
Y88b. d88P 
 "Y88888"  
.d8P""Y8b. 
888    888 
Y88b  d88P 
 "Y8888P"  ',
'9' => ' .d8888b.  
d88P  Y88b 
888    888 
Y88b. d888 
 "Y888P888 
       888 
Y88b  d88P 
 "Y8888P" ',
'0' => ' .d8888b.  
d88P  Y88b 
888    888 
888    888 
888    888 
888    888 
Y88b  d88P 
 "Y8888P"',
'%' => 'd88b   d88P 
Y88P  d88P  
     d88P   
    d88P    
   d88P     
  d88P      
 d88P  d88b 
d88P   Y88P ');

function clearScreen() {
	system("clear");
}

function drawHeader() {
clearScreen();
$t = '     888          888      888      888                                    
     888          888      888      888                                    
     888          888      888      888                                    
 .d88888  .d88b.  88888b.  88888b.  888  .d88b.     88888b.d88b.   .d88b.  
d88" 888 d88""88b 888 "88b 888 "88b 888 d8P  Y8b    888 "888 "88b d8P  Y8b 
888  888 888  888 888  888 888  888 888 88888888    888  888  888 88888888 
Y88b 888 Y88..88P 888 d88P 888 d88P 888 Y8b.    d8b 888  888  888 Y8b.     
 "Y88888  "Y88P"  88888P"  88888P"  888  "Y8888 Y8P 888  888  888  "Y8888  
 
 
 ';
 
drawText($t, 'PURPLE');

}

function drawText($text, $color = "") {
	if ($color != "") {
		$text = colorText($text, $color);
	}
	echo $text ."\n";
}
function colorText($text, $color) {
	global $COLORS;
	
	 return "\033[" . $COLORS[$color] . "m" . $text . "\033[" . $COLORS['DEFAULT'] . "m";
}

function toASCIILetters($str) {
	global $LETTERS;
	
	$output = "";
	
	for($i=0;$i<strlen($str);$i++)
	{
		$letter = $LETTERS[substr($str, $i, 1)];
		$output = combineMultilines(array($output, $letter));
	}
	
	return $output;
}
function combineMultilines($strs) {
	
	$lines = array();
	foreach ($strs as $str) {
		$n = 0;
		if ($str != "") {
		foreach (explode("\n", $str) as $line) {
			$lines[$n] .= vsprintf("%-13s", str_replace("\r", "", $line));
			$n++;
		}
		}
	}
	
	return join($lines, "\n");
}