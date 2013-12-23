#! /usr/bin/php6 -q
<?php
   // Gather Photos Emailed to the Server by Customers
   // This is a CLI program run by CRON every minute.
   
// Also force our TOPFILE
define('TOPFILE', "/homepages/45/d454707514/htdocs/siteautoload.php");
// Now this looks like all the other files.
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else {
  echo "Can't find siteautoload.php";
  exit();
}

// During debug
Error::setDevelopment(true);
Error::setNoEmailErrs(true);
Error::setNoHtml(true);

$S = new Database($dbinfo);

$sql = "select concat(date,' ', time), subject, location from sportsschedule ".
       "where date > curdate() limit 3";

$S->query($sql);
$tbl = '';
$cnt = 0;

while(list($date, $subject, $location) = $S->fetchrow('num')) {
  $date = date("l F j", strtotime($date));
  // area to put line can take 51 caracters.
  $line = "$date, $subject $location";

  if(strlen($line) > 51) {
    $x = explode(" ", $line);
    $line = '';
    $l = '';
    foreach($x as $y) {
      if(strlen("$l $y") < 51) {
        $l .= "$y ";
      } else {
        $line .= rtrim($l, " ") . "\n";
        $l = "$y ";
      }
    }

    if($l) {
      $line .= "$l";
      ++$cnt;
    }
  }
  $tbl .= "$line\n\n";
}

$ypos = 300 - ($cnt * 17);

$im = @imagecreatefrompng("images/mlb.png");
$text_color = imagecolorallocate($im, 0, 0, 0);
$font = 'fonts/ARIALBD.TTF';

imagettftext($im, 30, 0, 80, $ypos, $text_color, $font, $tbl);
//imagepng($im);
imagepng($im, "adscontent/mlb.png");
imagedestroy($im);
?>