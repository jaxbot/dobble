<?php
//Dobble.me Image Compression

function compressPNGImage($file, $out) {
	$image = imagecreatefrompng($file);
	imagesavealpha($image, true);
	imagepng($image, $out, 9, PNG_ALL_FILTERS);
	imagedestroy($image);
}

