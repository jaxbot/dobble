<?php
function resizeImg($img, $width, $height, $saveas) {

	$imginfo = getimagesize($img);
	list($sourceWidth, $sourceHeight) = $imginfo;	
	if ($imginfo['mime'] == "image/png") {
		$sourceImg = imagecreatefrompng($img);
	}
	if ($imginfo['mime'] == "image/jpeg") {
		$sourceImg = imagecreatefromjpeg($img);
	}
	$ratio = $sourceWidth / $sourceHeight;
	if ($sourceWidth < $width and $sourceHeight < $height) {
		if ($imginfo['mime'] == "image/png") {
			imagepng($sourceImg, $saveas);
		}
		if ($imginfo['mime'] == "image/jpeg") {
			imagejpeg($sourceImg, $saveas);
		}
		return 0;
	}
	if ($width/$height < $ratio) {
		$new_height = $width/$ratio;
		$new_width = $width;
	} else {
		$new_width = $height*$ratio;
		$new_height = $height;
	}

	$new = imagecreatetruecolor($new_width, $new_height);
	imagecopyresampled($new, $sourceImg, 0, 0, 0, 0, $new_width, $new_height, $sourceWidth, $sourceHeight);
	imagedestroy($sourceImg);
	
	if ($imginfo['mime'] == "image/png") {
		imagepng($new, $saveas, 9);
	}
	if ($imginfo['mime'] == "image/jpeg") {
		imagejpeg($new, $saveas, 100);
	}
	imagedestroy($new);

}

function createThumbnail($img, $width, $height, $saveas) {
	$imginfo = getimagesize($img);
	list($sourceWidth, $sourceHeight) = $imginfo;	
	if ($imginfo['mime'] == "image/png") {
		$sourceImg = imagecreatefrompng($img);
	}
	if ($imginfo['mime'] == "image/jpeg") {
		$sourceImg = imagecreatefromjpeg($img);
	}
	$ratio_orig = $sourceWidth/$sourceHeight;
	
	if ($width/$height > $ratio_orig) {
		$new_height = $width/$ratio_orig;
		$new_width = $width;
	} else {
		$new_width = $height*$ratio_orig;
		$new_height = $width;
	}
	$x_mid = $new_width/2;
	$y_mid = 0; //Top of the image looks better in most cases
	
	$process = imagecreatetruecolor(round($new_width), round($new_height)); 
    imagecopyresampled($process, $sourceImg, 0, 0, 0, 0, $new_width, $new_height, $sourceWidth, $sourceHeight);
	$thumb = imagecreatetruecolor($width, $height); 
	imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($width/2)), 0, $width, $height, $width, $height);

	imagejpeg($thumb, $saveas, 100);
	imagedestroy($sourceImg);
	imagedestroy($process);
	imagedestroy($thumb);
}
?>