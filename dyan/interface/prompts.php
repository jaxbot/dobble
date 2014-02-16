<?php
	//To be imported only by home.php
	require_once("system/promptsdata.php");
	//Prompt #201
	//Halloween color question
	if (!hasReadPrompt($_SESSION['P_usr'], 201)) {
		/*$content .= "<div style='position:fixed;top:60px;left:820px;opacity:0' id='floating_bat'>
		<img src='img/seasonal/bat.png' style='max-width: 200px;'><br>
		<span style='display:inline-block;width:200px;border:1px solid #676767;padding:3px;'>Look! I made your theme more seasonal!<br>
		&nbsp;<b><a onClick='floatingBat_saveColor();' href='#'>I like it!</a></b><br>&nbsp;<a href='#' onClick='floatingBat_revert();'>Ugh, no, change it back.</a>
		</span>
		</div>
		<script>updateTheme('#ea5e00');setTimeout('fadeInElement(_g(\"floating_bat\"));', 700);</script>";
		$pref = getUserPreferences($user);*/
	}
?>