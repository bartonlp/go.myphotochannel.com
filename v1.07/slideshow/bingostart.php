<?php
// bingo image
// bingostart.php?loc=<location>&game=<game number>

$loc = $_GET['loc'];
$game = $_GET['game'];
if($loc) {
  $im = imagecreatefromjpeg("$loc");
} else {
  $im = imagecreatetruecolor(800,600);
}
$white = imagecolorallocate($im, 255, 255, 255); // white
$black = imagecolorallocate($im, 0, 0, 0); // black

$font = '../fonts/ARIALBD.TTF';
if($loc) {
  imagettftext($im, 30, 0, 150, 52, $white, $font, "Bingo Game #$game Starting\n");
} else {
  imagettftext($im, 30, 0, 75, 200, $white, $font, "Bingo Game #$game Starting");
  imagettftext($im, 20, 0, 75, 260, $white, $font, "Go to\n".
               "http://go.myphotochannel.com/playbingo.php\n");
}
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
?>
