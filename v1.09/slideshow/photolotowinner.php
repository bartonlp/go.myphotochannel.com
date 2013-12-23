<?php
// bingo image
// bingoimage.php?loc=<location>&game=<game number>

$siteId = $_GET['siteId'];
$loc = $_GET['loc'];
$name = $_GET['name'];
$date = $_GET['date'];

$im = imagecreatetruecolor(800, 600);
$dest = imagecreatefromjpeg("../$loc");
list($width, $height) = getimagesize("../$loc");

$newH = $height/$width*200;
$white = imagecolorallocate($im, 255, 255, 255); // white
$black = imagecolorallocate($im, 0, 0, 0); // black
$font = '../fonts/ARIALBD.TTF';
$msg = <<<EOF
You too can win the PhotoLoto,
all you have to do is send in a photo.
Use your phone and take a picture.
Then send it to
felixs@myphotochannel.com.
It is that simple.
EOF;
$winner = "by: $name\non: $date";
if(empty($name)) $winner = "on: $date";

imagettftext($im, 30, 0, 50, 130, $white, $font, "The winning photo of the PhotoLotto:");
imagettftext($im, 30, 0, 260, 220, $white, $font, $winner);
imagettftext($im, 20, 0, 260, 320, $white, $font, "$msg");

imagecopyresampled($im, $dest, 50, 170, 0, 0, 200, $newH, $width, $height);

header('Content-Type: image/png');
imagepng($im);
imagepng($im, "../content/lottowinner$siteId.png");
imagedestroy($im);
?>
