<?php
// bingo image
// bingoend.php?loc=<location>&game=<game number>

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
  imagettftext($im, 30, 0, 150, 52, $white, $font, "Bingo Game #$game Over\n");
}else {
  imagettftext($im, 30, 0, 75, 200, $white, $font, "Bingo Game #$game Over\n");
  imagettftext($im, 20, 0, 75, 260, $white, $font, "Please play again.\n".
               "Go to\nhttp://go.myphotochannel.com/playbingo.php\n".
               "A new game will start in five minutes.");
}
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
?>
