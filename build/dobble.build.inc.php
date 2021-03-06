<?php
$FILENAMECOUNTER = 0;
$USED_FILENAMES = array();

function updateVersion() {
	$curVersion = file_get_contents("build/build");
	$curVersion++;
	file_put_contents("build/build", $curVersion);
	return $curVersion;
}

function getFiles($dir, $ext) {
	$files = array();
	
	$contents = scandir($dir);
	foreach ($contents as $content) {
		if (is_file($dir . "/" . $content)) {
			if (preg_match("/\\".$ext."$/i", $content))
				$files[] = $dir . "/" . $content;
		} else {
			if ($content != ".." and $content != ".")
				$files = array_merge($files, getFiles($dir . "/" . $content, $ext));
		}
	}
	
	return $files;
}
function generateUniqueFilename($file) {
	global $USED_FILENAMES, $FILENAMECOUNTER;
	$file = explode("/", $file);
	$file = $file[count($file) - 1];
	$file = explode(".", $file);
	$file = $file[0];
	$file = substr($file, 0, 4);
	$file = str_replace("_", "", $file);
	
	if (!in_array($file, $USED_FILENAMES))
		$USED_FILENAMES[] = $file;
	else {
		$file .= $FILENAMECOUNTER;
		$USED_FILENAMES[] = $file;
		$FILENAMECOUNTER++;
	}
	return $file;
}
function justName($file) {
	$file = explode("/", $file);
	return $file[count($file) - 1];
}
function fileChanged($f1, $f2) {
	if (!file_exists($f2))
		return true;

	return md5_file($f1) != md5_file($f2);
}
function cleanupDir($dir, $files) {
	$cfiles = glob($dir."*", GLOB_NOSORT);
	$rawfiles = array();
	
	foreach ($files as $raw) {
		$rawfiles[] = justName($raw);
	}
	foreach ($cfiles as $file) {
		if (!in_array(justName($file),$rawfiles)) {
			echo "File deleted: $file\n";
			unlink($file);
		} 
	}
}
function rrmdir($dir) {
    $fp = opendir($dir);
    if ( $fp ) {
        while ( $f = readdir($fp) ) {
            $file = $dir . "/" . $f;
            if ( $f == "." || $f == ".." ) {
                continue;
            }
            else if ( is_dir($file) ) {
                rrmdir($file);
            }
            else {
                unlink($file);
            }
        }
        closedir($fp);
    
        rmdir($dir);
    }
}
function rcopy($src, $dst) {
  if (file_exists($dst)) rrmdir($dst);
  if (is_dir($src)) {
    mkdir($dst);
    $files = scandir($src);
    foreach ($files as $file)
    if ($file != "." && $file != "..") rcopy("$src/$file", "$dst/$file"); 
  }
  else if (file_exists($src)) copy($src, $dst);
}
