<?php
// bingo image
// bingoimage.php?loc=<location>&game=<game number>

$loc = $_GET['loc'];
$game = $_GET['game'];
$text = $_GET['text'];

$im = imagecreatefromjpeg("../$loc");
$white = imagecolorallocate($im, 255, 255, 255); // white
$black = imagecolorallocate($im, 0, 0, 0); // black
$font = '../fonts/ARIALBD.TTF';
imagettftext($im, 30, 0, 10, 50, $black, $font, "Bingo Game #$game\n$text");
imagettftext($im, 30, 0, 12, 52, $white, $font, "Bingo Game #$game\n$text");
imagettftext($im, 30, 90, 50, 450, $white, $font, "Bingo Game #$game\n$text");
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);

